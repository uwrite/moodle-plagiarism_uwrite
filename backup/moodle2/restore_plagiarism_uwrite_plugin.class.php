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
 * restore_plagiarism_uwrite_plugin.class.php
 *
 * @package     plagiarism_uwrite
 * @subpackage  plagiarism
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Class restore_plagiarism_uwrite_plugin
 */
class restore_plagiarism_uwrite_plugin extends restore_plagiarism_plugin {
    /**
     * @param $data
     */
    public function process_uwriteconfig($data) {
        $data = (object) $data;

        set_config($this->task->get_courseid(), $data->value, $data->plugin);
    }

    /**
     * @param $data
     */
    public function process_uwriteconfigmod($data) {
        global $DB;

        $data = (object) $data;
        $data->cm = $this->task->get_moduleid();

        $DB->insert_record(UWRITE_CONFIG_TABLE, $data);
    }

    /**
     * @param $data
     */
    public function process_uwritefiles($data) {
        global $DB;

        $data = (object) $data;
        $data->cm = $this->task->get_moduleid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('plagiarism_uwrite_files', $data);
    }

    /**
     * Returns the paths to be handled by the plugin at question level.
     */
    protected function define_course_plugin_structure() {
        $paths = array();

        // Add own format stuff.
        $elename = 'uwriteconfig';
        $elepath = $this->get_pathfor('uwrite_configs/uwrite_config'); // We used get_recommended_name() so this works.
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.
    }

    /**
     * Returns the paths to be handled by the plugin at module level.
     */
    protected function define_module_plugin_structure() {
        $paths = array();

        // Add own format stuff.
        $elename = 'uwriteconfigmod';
        $elepath = $this->get_pathfor('uwrite_configs/uwrite_config'); // We used get_recommended_name() so this works.
        $paths[] = new restore_path_element($elename, $elepath);

        $elename = 'uwritefiles';
        $elepath = $this->get_pathfor('/uwrite_files/uwrite_file'); // We used get_recommended_name() so this works.
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.

    }
}