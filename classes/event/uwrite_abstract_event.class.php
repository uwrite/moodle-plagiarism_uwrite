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
 * uwrite_abstract_event.class.php
 *
 * @package     plagiarism_uwrite
 * @subpackage  plagiarism
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace plagiarism_uwrite\classes\event;

use core\event\base;
use plagiarism_uwrite\classes\uwrite_api;
use plagiarism_uwrite\classes\uwrite_assign;
use plagiarism_uwrite\classes\uwrite_core;
use plagiarism_uwrite\classes\uwrite_plagiarism_entity;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class uwrite_abstract_event
 *
 * @package plagiarism_uwrite\classes\event
 */
abstract class uwrite_abstract_event {
    /** @var */
    protected static $instance;
    /** @var array */
    protected $tasks = array();
    /** @var uwrite_core */
    protected $uwritecore;

    /**
     * @return static
     */
    public static function instance() {
        $class = get_called_class();

        if (!isset(static::$instance[$class])) {
            static::$instance[$class] = new static;
        }

        return static::$instance[$class];
    }

    /**
     * @param base $event
     *
     * @return bool
     */
    public static function is_submition_draft(base $event) {
        global $CFG;

        if ($event->objecttable != 'assign_submission') {
            return false;
        }

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $submission = uwrite_assign::get_user_submission_by_cmid($event->contextinstanceid);
        if (!$submission) {
            return true;
        }

        return ($submission->status !== 'submitted');
    }

    /**
     *
     */
    protected function after_handle_event() {
        if (empty($this->tasks)) {
            // Skip this file check cause assign is draft.
            return;
        }

        foreach ($this->tasks as $plagiarismentity) {
            if ($plagiarismentity instanceof uwrite_plagiarism_entity) {
                $internalfile = $plagiarismentity->get_internal_file();
                if (isset($internalfile->external_file_id) && !isset($internalfile->check_id)) {
                    $checkresp = uwrite_api::instance()->run_check($internalfile);
                    $plagiarismentity->handle_check_response($checkresp);
                }
            }
        }
    }

    /**
     * @param $plagiarismentity
     */
    protected function add_after_handle_task($plagiarismentity) {
        array_push($this->tasks, $plagiarismentity);
    }

    /**
     * @param uwrite_core $uwritecore
     * @param base        $event
     */
    abstract public function handle_event(uwrite_core $uwritecore, base $event);
}