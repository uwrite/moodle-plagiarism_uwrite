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
 * autoloader.php
 *
 * @package     plagiarism_uwrite
 * @subpackage  plagiarism
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_uwrite\library;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class uwrite_autoloader
 *
 * @package plagiarism_uwrite\library
 */
class uwrite_autoloader {
    /**
     * @param $class
     */
    public static function init($class) {
        if (strpos($class, 'plagiarism_uwrite') === false) {
            return;
        }

        $class = str_replace('plagiarism_uwrite', '', $class);
        $class = str_replace('\\', '/', $class);

        $autoload = sprintf('%s%s.class.php', __DIR__, str_replace('plagiarism_uwrite', '', $class));
        require_once($autoload);
    }
}

spl_autoload_register(array('plagiarism_uwrite\library\uwrite_autoloader', 'init'));