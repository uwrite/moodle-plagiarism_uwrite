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

namespace plagiarism_uwrite\classes\event;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once(dirname(__FILE__) . '/../../lib.php');

use core\event\base;
use plagiarism_plugin_uwrite;

/**
 * Class uwrite_event_validator
 *
 * @package     plagiarism_uwrite\classes\event
 * @subpackage  plagiarism
 * @namespace   plagiarism_uwrite\classes\event
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class uwrite_event_validator {
    /**
     * @var array
     */
    private static $allowedcomponents = array(
        'mod_assign',
        'mod_forum',
        'mod_workshop',
        'assignsubmission_file',
        'assignsubmission_onlinetext',
    );

    /**
     * @param base $event
     *
     * @return bool
     */
    public static function validate_event(base $event) {
        global $DB;

        if (self::is_allowed_component($event->component)) {
            $cmid = $event->contextinstanceid;

            if (!self::is_mod_enabled($cmid)) {
                // Moodle mod inactive - return.
                return false;
            }

            $plagiarismvalues = $DB->get_records_menu(UWRITE_CONFIG_TABLE, array('cm' => $cmid), '', 'name, value');
            if (empty($plagiarismvalues['use_uwrite'])) {
                // Uwrite not in use for this cm - return.
                return false;
            }

            // Check if the module associated with this event still exists.
            if (!$DB->record_exists('course_modules', array('id' => $cmid))) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @param $component
     *
     * @return bool
     */
    private static function is_allowed_component($component) {
        return in_array($component, self::$allowedcomponents);
    }

    /**
     * @param $cmid
     *
     * @return bool
     */
    private static function is_mod_enabled($cmid) {
        $cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);
        if (plagiarism_plugin_uwrite::is_enabled_module('mod_' . $cm->modname)) {
            return true;
        }

        return false;
    }
}