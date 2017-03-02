<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace plagiarism_uwrite\classes\helpers;

use plagiarism_uwrite\classes\exception\uwrite_exception;
use plagiarism_uwrite\classes\uwrite_api;
use plagiarism_uwrite\classes\uwrite_plagiarism_entity;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class uwrite_progress
 *
 * @package plagiarism_uwrite\classes\helpers
 * @subpackage  plagiarism
 * @namespace plagiarism_uwrite\classes\helpers
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class uwrite_progress {
    /**
     * @param $record
     * @param $cid
     * @param $checkstatusforids
     * @return array|bool
     */
    public static function get_file_progress_info($record, $cid, &$checkstatusforids) {
        global $DB;

        $childs = array();

        if ($record->type == uwrite_plagiarism_entity::TYPE_ARCHIVE) {
            $childs = $DB->get_records_list(UWRITE_FILES_TABLE, 'parent_id', array($record->id));
        }

        if (empty($record->check_id) && empty($childs)) {
            return false;
        }

        if ($record->progress != 100) {
            if ($childs) {
                foreach ($childs as $child) {
                    if ($child->check_id) {
                        $checkstatusforids[$record->id][] = $child->check_id;
                    }
                }
            } else {
                if ($record->check_id) {
                    $checkstatusforids[$record->id][] = $record->check_id;
                }
            }
        }

        $info = array(
                'file_id' => $record->id,
                'statuscode' => $record->statuscode,
                'progress' => (int) $record->progress,
                'content' => self::gen_row_content_score($cid, $record),
        );
        return $info;
    }

    /**
     * @param $cid
     * @param $checkstatusforids
     * @param $resp
     *
     * @throws uwrite_exception
     */
    public static function check_real_file_progress($cid, $checkstatusforids, &$resp) {
        global $DB;

        $progressids = [];

        foreach ($checkstatusforids as $recordid => $checkids) {
            $progressids = array_merge($progressids, $checkids);
        }

        $progressids = array_unique($progressids);
        $progresses = uwrite_api::instance()->get_check_progress($progressids);

        if ($progresses->result) {
            foreach ($progresses->progress as $id => $val) {
                $val *= 100;
                $fileobj = self::update_file_progress($id, $val);
                $resp[$fileobj->id]['progress'] = $val;
                $resp[$fileobj->id]['content'] = self::gen_row_content_score($cid, $fileobj);
            }

            foreach ($checkstatusforids as $recordid => $checkids) {
                if (count($checkids) > 0) {
                    $childscount = $DB->count_records_select(UWRITE_FILES_TABLE, "parent_id = ? AND statuscode in (?,?,?)",
                        [
                            $recordid, UWRITE_STATUSCODE_PROCESSED, UWRITE_STATUSCODE_ACCEPTED,
                            UWRITE_STATUSCODE_PENDING
                        ]) ?: 1;

                    $progress = 0;

                    foreach ($checkids as $id) {
                        $progress += ($progresses->progress->{$id} * 100);
                    }

                    $progress = floor($progress / $childscount);
                    $fileobj = self::update_parent_progress($recordid, $progress);
                    $resp[$recordid]['progress'] = $progress;
                    $resp[$recordid]['content'] = self::gen_row_content_score($cid, $fileobj);
                }
            }
        }
    }

    /**
     * @param $cid
     * @param $fileobj
     * @return bool|mixed
     */
    public static function gen_row_content_score($cid, $fileobj) {
        if ($fileobj->progress == 100 && $cid) {
            return require(dirname(__FILE__) . '/../../views/view_tmpl_processed.php');
        } else {
            if ($fileobj->statuscode == UWRITE_STATUSCODE_INVALID_RESPONSE) {
                return require(dirname(__FILE__) . '/../../views/view_tmpl_invalid_response.php');
            }
        }

        return false;
    }

    /**
     * @param $id
     * @param $progress
     *
     * @return mixed
     * @throws uwrite_exception
     */
    private static function update_file_progress($id, $progress) {
        global $DB;

        $record = $DB->get_record(UWRITE_FILES_TABLE, array('check_id' => $id));
        if ($record->progress <= $progress) {
            $record->progress = $progress;

            if ($record->progress === 100) {
                $resp = uwrite_api::instance()->get_check_data($id);
                if (!$resp->result) {
                    throw new uwrite_exception($resp->errors);
                }

                uwrite_check_helper::check_complete($record, $resp->check);
            } else {
                $DB->update_record(UWRITE_FILES_TABLE, $record);
            }
        }

        return $record;
    }

    /**
     * @param $fileid
     * @param $progress
     * @return mixed
     */
    private static function update_parent_progress($fileid, $progress) {
        global $DB;

        $record = $DB->get_record(UWRITE_FILES_TABLE, array('id' => $fileid));
        if ($record->progress <= $progress) {
            $record->progress = $progress;
            if ($record->progress != 100) {
                $DB->update_record(UWRITE_FILES_TABLE, $record);
            }
        }

        return $record;
    }
}