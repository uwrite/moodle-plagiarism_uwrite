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

namespace plagiarism_uwrite\classes;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class uwrite_settings
 *
 * @package     plagiarism_uwrite\classes
 * @subpackage  plagiarism
 * @namespace   plagiarism_uwrite\classes
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class uwrite_settings {
    const SENSITIVITY_SETTING_NAME = 'similarity_sensitivity';
    const USE_UWRITE = 'use_uwrite';
    const SHOW_STUDENT_SCORE = 'uwrite_show_student_score';
    const SHOW_STUDENT_REPORT = 'uwrite_show_student_report';
    const DRAFT_SUBMIT = 'uwrite_draft_submit';
    const CHECK_TYPE = 'check_type';
    const EXCLUDE_CITATIONS = 'exclude_citations';
    const EXCLUDE_SELF_PLAGIARISM = 'exclude_self_plagiarism';
    const CHECK_ALL_SUBMITTED_ASSIGNMENTS = 'check_all_submitted_assignments';
    const NO_INDEX_FILES = 'no_index_files';

    /**
     * @param      $cmid
     * @param null $name
     *
     * @param      $assoc
     *
     * @return \stdClass|array
     */
    public static function get_assign_settings($cmid, $name = null, $assoc = null) {
        global $DB;

        $condition = array(
            'cm' => $cmid,
        );

        if (isset($name)) {
            $condition['name'] = $name;
        }

        $data = $DB->get_records(UWRITE_CONFIG_TABLE, $condition, '', 'name,value');
        $data = array_map(function($item) {
            return $item->value;
        }, $data);

        if (is_bool($assoc) && $assoc) {
            return $data;
        }

        if (isset($data[$name])) {
            return $data[$name];
        }

        return [];
    }

    /**
     * This function should be used to initialise settings and check if plagiarism is enabled.
     *
     * @param null $key
     *
     * @return array|bool
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_settings($key = null) {
        static $settings;

        if (!empty($settings)) {
            return self::get_settings_item($settings, $key);
        }

        $settings = (array) get_config('plagiarism');

        // Check if enabled.
        if (isset($settings['uwrite_use']) && $settings['uwrite_use']) {
            // Now check to make sure required settings are set!
            if (empty($settings['uwrite_api_secret'])) {
                throw new \coding_exception('UWRITE API Secret not set!');
            }

            return self::get_settings_item($settings, $key);
        } else {
            return false;
        }
    }

    /**
     * @param      $settings
     * @param null $key
     *
     * @return null
     */
    private static function get_settings_item($settings, $key = null) {
        if (is_null($key)) {
            return $settings;
        }

        $key = 'uwrite_' . $key;

        return isset($settings[$key]) ? $settings[$key] : null;
    }
}