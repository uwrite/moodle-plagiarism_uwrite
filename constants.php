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
 * constants.php
 *
 * @package     plagiarism_uwrite
 * @subpackage  plagiarism
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

define('UWRITE_PLAGIN_NAME', 'plagiarism_uwrite');
define('UWRITE_DOMAIN', 'https://uwrite.proctoru.com/');
define('UWRITE_API_URL', UWRITE_DOMAIN . 'api/v2/');
define('UWRITE_CALLBACK_URL', '/plagiarism/uwrite/ajax.php?action=uwrite_callback');

define('UWRITE_PROJECT_PATH', dirname(__FILE__) . '/');

define('UWRITE_DEFAULT_FILES_AREA', 'assign_submission');
define('UWRITE_WORKSHOP_FILES_AREA', 'workshop_submissions');
define('UWRITE_FORUM_FILES_AREA', 'forum_posts');

/** TABLES **/
define('UWRITE_FILES_TABLE', 'plagiarism_uwrite_files');
define('UWRITE_USER_DATA_TABLE', 'plagiarism_uwrite_user_data');
define('UWRITE_CONFIG_TABLE', 'plagiarism_uwrite_config');

define('UWRITE_CHECK_TYPE_WEB', 'web');
define('UWRITE_CHECK_TYPE_MY_LIBRARY', 'my_library');
define('UWRITE_CHECK_TYPE_WEB__LIBRARY', 'web_and_my_library');

define('UWRITE_STATUSCODE_PENDING', 'pending');
define('UWRITE_STATUSCODE_PROCESSED', 200);
define('UWRITE_STATUSCODE_ACCEPTED', 202);
define('UWRITE_STATUSCODE_UNSUPPORTED', 415);
define('UWRITE_STATUSCODE_INVALID_RESPONSE', 613); // Invalid response received from UWRITE.

define('UWRITE_UPLOAD_TIME_LIMIT', 5 * 60); // Time limit for upload file.

define('UWRITE_WORKSHOP_SETUP_PHASE', 10);
define('UWRITE_WORKSHOP_SUBMISSION_PHASE', 20);
define('UWRITE_WORKSHOP_ASSESSMENT_PHASE', 30);
define('UWRITE_WORKSHOP_GRADING_PHASE', 40);

define('UWRITE_MODNAME_WORKSHOP', 'workshop');
define('UWRITE_MODNAME_FORUM', 'forum');
define('UWRITE_MODNAME_ASSIGN', 'assign');