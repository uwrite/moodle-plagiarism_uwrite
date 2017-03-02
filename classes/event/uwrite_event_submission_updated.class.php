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
 * uwrite_event_file_submited.class.php
 *
 * @package     plagiarism_uwrite
 * @subpackage  plagiarism
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_uwrite\classes\event;

use core\event\base;
use plagiarism_uwrite\classes\uwrite_assign;
use plagiarism_uwrite\classes\uwrite_core;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class uwrite_event_submission_updated
 *
 * @package   plagiarism_uwrite\classes\event
 * @namespace plagiarism_uwrite\classes\event
 *
 */
class uwrite_event_submission_updated extends uwrite_abstract_event {
    const DRAFT_STATUS = 'draft';

    /**
     * @param uwrite_core $uwritecore
     * @param base        $event
     *
     * @return bool
     */
    public function handle_event(uwrite_core $uwritecore, base $event) {

        global $DB;
        if (!isset($event->other['newstatus'])) {
            return false;
        }
        $newstatus = $event->other['newstatus'];
        $uwritecore->userid = $event->relateduserid;
        if ($newstatus == self::DRAFT_STATUS) {
            $uwritefiles = \plagiarism_uwrite::get_area_files($event->contextid, UWRITE_DEFAULT_FILES_AREA, $event->objectid);
            $assignfiles = uwrite_assign::get_area_files($event->contextid, $event->objectid);

            $files = array_merge($uwritefiles, $assignfiles);

            $ids = array();
            foreach ($files as $file) {
                $plagiarismentity = $uwritecore->get_plagiarism_entity($file);
                $internalfile = $plagiarismentity->get_internal_file();
                $ids[] = $internalfile->id;
            }

            $allrecordssql = implode(',', $ids);
            $DB->delete_records_select(UWRITE_FILES_TABLE, "id IN ($allrecordssql) OR parent_id IN ($allrecordssql)");
        }

        return true;
    }
}