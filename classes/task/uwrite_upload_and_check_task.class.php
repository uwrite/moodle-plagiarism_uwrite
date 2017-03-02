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
 * uwrite_upload_and_check_task.class.php
 *
 * @package     plagiarism_uwrite
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_uwrite\classes\task;

use plagiarism_uwrite\classes\helpers\uwrite_check_helper;
use plagiarism_uwrite\classes\plagiarism\uwrite_content;
use plagiarism_uwrite\classes\uwrite_api;
use plagiarism_uwrite\classes\uwrite_assign;
use plagiarism_uwrite\classes\uwrite_core;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class uwrite_upload_and_check_task
 *
 * @package   plagiarism_uwrite\classes\task
 * @namespace plagiarism_uwrite\classes\task
 *
 */
class uwrite_upload_and_check_task extends uwrite_abstract_task {
    public function execute() {
        $data = $this->get_custom_data();
        if (file_exists($data->tmpfile)) {
            $ucore = new uwrite_core($data->uwritecore->cmid, $data->uwritecore->userid);

            if ((bool) uwrite_assign::get_by_cmid($ucore->cmid)->teamsubmission) {
                $ucore->enable_teamsubmission();
            }

            $content = file_get_contents($data->tmpfile);
            $plagiarismentity = new uwrite_content($ucore, $content, $data->filename, $data->format, $data->parent_id);

            unset($content, $ucore);

            if (!unlink($data->tmpfile)) {
                mtrace('Error deleting ' . $data->tmpfile);
            }

            uwrite_check_helper::upload_and_run_detection($plagiarismentity);

            unset($internalfile, $plagiarismentity, $checkresp);
        } else {
            mtrace('file ' . $data->tmpfile . 'not exist');
        }
    }
}