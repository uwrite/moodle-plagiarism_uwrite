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

namespace plagiarism_uwrite\classes\helpers;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class uwrite_stored_file
 *
 * @package plagiarism_uwrite\classes\helpers
 * @namespace plagiarism_uwrite\classes\helpers
 *
 */
class uwrite_stored_file extends \stored_file {

    /**
     * @param \stored_file $file
     * @return string
     */
    public static function get_protected_pathname(\stored_file $file) {
        return $file->get_pathname_by_contenthash();
    }

    /**
     * @param $id
     *
     * @return array
     */
    public static function get_childs($id) {
        global $DB;

        return $DB->get_records_list(UWRITE_FILES_TABLE, 'parent_id', array($id));
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public static function get_uwrite_file($id) {
        global $DB;

        return $DB->get_record(UWRITE_FILES_TABLE, array('id' => $id), '*', MUST_EXIST);
    }
}