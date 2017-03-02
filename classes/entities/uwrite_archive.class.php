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
/**
 * uwrite_plagiarism_entity.class.php
 *
 * @package     plagiarism_uwrite
 * @subpackage  plagiarism
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_uwrite\classes\entities;

use plagiarism_uwrite\classes\exception\uwrite_exception;
use plagiarism_uwrite\classes\helpers\uwrite_stored_file;
use plagiarism_uwrite\classes\plagiarism\uwrite_content;
use plagiarism_uwrite\classes\task\uwrite_upload_and_check_task;
use plagiarism_uwrite\classes\uwrite_api;
use plagiarism_uwrite\classes\uwrite_core;
use plagiarism_uwrite\classes\uwrite_notification;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class uwrite_archive
 *
 * @package   plagiarism_uwrite\classes\entities
 * @namespace plagiarism_uwrite\classes\entities
 *
 */
class uwrite_archive {
    /**
     * @var \stored_file
     */
    private $file;
    /**
     * @var uwrite_core
     */
    private $uwritecore;

    /**
     * uwrite_archive constructor.
     *
     * @param \stored_file $file
     * @param uwrite_core  $core
     *
     * @throws uwrite_exception
     */
    public function __construct(\stored_file $file, uwrite_core $core) {
        $this->file = $file;
        $this->uwritecore = $core;
    }

    /**
     * @return bool
     */
    public function run_checks() {
        global $DB;

        $archiveinternalfile = $this->uwritecore->get_plagiarism_entity($this->file)->get_internal_file();

        $ziparch = new \zip_archive();
        $pathname = uwrite_stored_file::get_protected_pathname($this->file);
        if (!$ziparch->open($pathname, \file_archive::OPEN)) {
            $this->invalid_response($archiveinternalfile, "Can't open zip archive");

            return false;
        }

        $fileexist = false;
        foreach ($ziparch as $file) {
            if (!$file->is_directory) {
                $fileexist = true;
                break;
            }
        }

        if (!$fileexist) {
            $this->invalid_response($archiveinternalfile, "Empty archive");

            return false;
        }

        try {
            $this->process_archive_files($ziparch, $archiveinternalfile->id);
        } catch (\Exception $e) {
            mtrace('Archive error ' . $e->getMessage());
        }

        $archiveinternalfile->statuscode = UWRITE_STATUSCODE_ACCEPTED;
        $archiveinternalfile->errorresponse = null;

        $DB->update_record(UWRITE_FILES_TABLE, $archiveinternalfile);

        $ziparch->close();

        return true;
    }

    /**
     * @param \zip_archive $ziparch
     * @param null         $parentid
     */
    private function process_archive_files(\zip_archive&$ziparch, $parentid = null) {
        global $CFG;

        $processed = array();
        foreach ($ziparch as $file) {
            if ($file->is_directory) {
                continue;
            }

            $size = $file->size;
            $name = fix_utf8($file->pathname);
            $format = pathinfo($name, PATHINFO_EXTENSION);

            $content = '';
            $tmpfile = tempnam($CFG->tempdir, 'uwrite_unzip');

            if (!$fp = fopen($tmpfile, 'wb')) {
                $this->unlink($tmpfile);
                $processed[$name] = 'Can not write temp file';
                continue;
            }

            if ($name === '' or array_key_exists($name, $processed)) {
                $this->unlink($tmpfile);
                continue;
            }

            if (!$fz = $ziparch->get_stream($file->index)) {
                $this->unlink($tmpfile);
                $processed[$name] = 'Can not read file from zip archive';
                continue;
            }

            $bytescopied = stream_copy_to_stream($fz, $fp);

            fclose($fz);
            fclose($fp);

            if ($bytescopied != $size) {
                $this->unlink($tmpfile);
                $processed[$name] = 'Can not read file from zip archive';
                continue;
            }

            $plagiarismentity = new uwrite_content($this->uwritecore, null, $name, $format, $parentid);
            $plagiarismentity->get_internal_file();

            uwrite_upload_and_check_task::add_task(array(
                'tmpfile'    => $tmpfile,
                'filename'   => $name,
                'uwritecore' => $this->uwritecore,
                'format'     => $format,
                'parent_id'  => $parentid,
            ));

            unset($content);
        }
    }

    public function restart_check() {
        global $DB;

        $internalfile = $this->uwritecore->get_plagiarism_entity($this->file)->get_internal_file();
        $childs = $DB->get_records_list(UWRITE_FILES_TABLE, 'parent_id', array($internalfile->id));
        if ($childs) {
            foreach ((object)$childs as $child) {
                if ($child->check_id) {
                    uwrite_api::instance()->delete_check($child);
                }
            }

            uwrite_notification::success('plagiarism_run_success', true);

            $this->run_checks();
        }
    }

    /**
     * @param $file
     */
    private function unlink($file) {
        if (!unlink($file)) {
            mtrace('Error deleting ' . $file);
        }
    }

    /**
     * @param \stdClass $archivefile
     * @param string    $reason
     */
    private function invalid_response($archivefile, $reason) {
        global $DB;

        $archivefile->statuscode = UWRITE_STATUSCODE_INVALID_RESPONSE;
        $archivefile->errorresponse = json_encode(array(
            array("message" => $reason),
        ));

        $DB->update_record(UWRITE_FILES_TABLE, $archivefile);
    }
}
