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
 * uwrite_event_assessable_submited.class.php
 *
 * @package     plagiarism_uwrite
 * @subpackage  plagiarism
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_uwrite\classes\event;

use core\event\base;
use plagiarism_uwrite;
use plagiarism_uwrite\classes\entities\uwrite_archive;
use plagiarism_uwrite\classes\helpers\uwrite_check_helper;
use plagiarism_uwrite\classes\uwrite_assign;
use plagiarism_uwrite\classes\uwrite_core;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once(dirname(__FILE__) . '/../../locallib.php');

/**
 * Class uwrite_event_file_submited
 *
 * @package plagiarism_uwrite\classes\event
 */
class uwrite_event_assessable_submited extends uwrite_abstract_event {
    /**
     * @param uwrite_core $uwritecore
     * @param base        $event
     */
    public function handle_event(uwrite_core $uwritecore, base $event) {

        $this->uwritecore = $uwritecore;

        $submission = uwrite_assign::get_user_submission_by_cmid($event->contextinstanceid);
        $submissionid = (!empty($submission->id) ? $submission->id : false);

        $uwritefiles = plagiarism_uwrite::get_area_files($event->contextid, UWRITE_DEFAULT_FILES_AREA, $submissionid);
        $assignfiles = uwrite_assign::get_area_files($event->contextid, $submissionid);

        $files = array_merge($uwritefiles, $assignfiles);
        if (!empty($files)) {
            foreach ($files as $file) {
                $this->handle_file_plagiarism($file);
            }
        }
    }

    /**
     * @param \stored_file $file
     *
     * @return bool
     */
    private function handle_file_plagiarism(\stored_file $file) {
        if (\plagiarism_uwrite::is_archive($file)) {
            $uwritearchive = new uwrite_archive($file, $this->uwritecore);
            $uwritearchive->run_checks();

            return true;
        }

        $plagiarismentity = $this->uwritecore->get_plagiarism_entity($file);

        return uwrite_check_helper::upload_and_run_detection($plagiarismentity);
    }
}