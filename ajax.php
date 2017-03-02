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
 * ajax.php
 *
 * @package     plagiarism_uwrite
 * @subpackage  plagiarism
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

$action = required_param('action', PARAM_ALPHAEXT);
$data = optional_param('data', array(), PARAM_RAW);
$token = optional_param('token', '', PARAM_RAW);

if (!$token) {
    require_login();
    require_sesskey();
}
$uwrite = new plagiarism_uwrite();
if (!is_callable(array($uwrite, $action))) {
    echo json_encode('Called method does not exists');

    return null;
}

if ($token) {
    $data = $token;
}

echo $uwrite->{$action}($data);
die;