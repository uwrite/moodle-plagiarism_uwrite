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
use plagiarism_uwrite\classes\uwrite_core;
use plagiarism_uwrite\classes\uwrite_plagiarism_entity;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class uwrite_event_file_submited
 *
 * @package plagiarism_uwrite\classes\event
 */
class uwrite_event_file_submited extends uwrite_abstract_event {
    /**
     * @param uwrite_core $uwritecore
     * @param base        $event
     */
    public function handle_event(uwrite_core $uwritecore, base $event) {
        if (self::is_submition_draft($event) ||
            !isset($event->other['pathnamehashes']) || empty($event->other['pathnamehashes'])
        ) {
            return;
        }

        $this->uwritecore = $uwritecore;

        foreach ($event->other['pathnamehashes'] as $pathnamehash) {
            $this->add_after_handle_task($this->handle_uploaded_file($pathnamehash));
        }

        $this->after_handle_event();
    }

    /**
     * @param $pathnamehash
     *
     * @return null|uwrite_plagiarism_entity
     */
    private function handle_uploaded_file($pathnamehash) {
        $file = get_file_storage()->get_file_by_hash($pathnamehash);
        if ($file->is_directory()) {
            return null;
        }
        $plagiarismentity = $this->uwritecore->get_plagiarism_entity($file);
        $plagiarismentity->upload_file_on_uwrite_server();

        return $plagiarismentity;
    }
}