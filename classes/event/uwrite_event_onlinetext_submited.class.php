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
 * uwrite_event_onlinetext_submited.class.php
 *
 * @package     plagiarism_uwrite
 * @subpackage  plagiarism
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_uwrite\classes\event;

use core\event\base;
use plagiarism_uwrite\classes\uwrite_core;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class uwrite_event_onlinetext_submited
 *
 * @package plagiarism_uwrite\classes\event
 */
class uwrite_event_onlinetext_submited extends uwrite_abstract_event {
    /**
     * @param uwrite_core $uwritecore
     * @param base        $event
     */
    public function handle_event(uwrite_core $uwritecore, base $event) {
        if (empty($event->other['content'])) {
            return;
        }

        $file = $uwritecore->create_file_from_content($event);

        if (self::is_submition_draft($event)) {
            return;
        }

        if ($file) {
            $plagiarismentity = $uwritecore->get_plagiarism_entity($file);
            $plagiarismentity->upload_file_on_uwrite_server();
            $this->add_after_handle_task($plagiarismentity);
        }

        $this->after_handle_event();
    }
}