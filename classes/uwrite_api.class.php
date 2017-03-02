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
 * uwrite_api.class.php - SDK for working with uwrite api.
 *
 * @package     plagiarism_uwrite
 * @subpackage  plagiarism
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_uwrite\classes;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class uwrite_api
 *
 * @package plagiarism_uwrite\classes
 */
class uwrite_api {
    const ACCESS_SCOPE_WRITE = 'w';
    const ACCESS_SCOPE_READ = 'r';
    const CHECK_PROGRESS = 'check/progress';
    const CHECK_GET = 'check/get';
    const FILE_UPLOAD = 'file/upload';
    const CHECK_CREATE = 'check/create';
    const CHECK_DELETE = 'check/delete';
    const USER_CREATE = 'user/create';
    /**
     * @var null|uwrite_api
     */
    private static $instance = null;

    /**
     * @return null|static
     */
    final public static function instance() {
        return isset(self::$instance) ? self::$instance : self::$instance = new uwrite_api();
    }

    /**
     * @param string      $content
     * @param string      $filename
     * @param string      $format
     * @param integer     $cmid
     * @param object|null $owner
     *
     * @return \stdClass
     */
    public function upload_file($content, $filename, $format = 'html', $cmid, $owner = null) {

        set_time_limit(UWRITE_UPLOAD_TIME_LIMIT);

        $postdata = array(
            'format'    => $format,
            'file_data' => base64_encode($content),
            'name'      => $filename,
            'options'   => array(
                'utoken'        => uwrite_core::get_external_token($cmid, $owner),
                'submission_id' => $cmid,
            ),
        );

        if ($noindex = uwrite_settings::get_assign_settings($cmid, uwrite_settings::NO_INDEX_FILES)) {
            $postdata['options']['no_index'] = $noindex;
        }

        return uwrite_api_request::instance()->http_post()->request(self::FILE_UPLOAD, $postdata);
    }

    /**
     * @param \stdClass $file
     *
     * @return \stdClass
     */
    public function run_check(\stdClass $file) {
        global $CFG;

        if (empty($file)) {
            throw new \InvalidArgumentException('Invalid argument $file');
        }

        $checktype = uwrite_settings::get_assign_settings($file->cm, 'check_type');

        $options = array();
        $this->advanced_check_options($file->cm, $options);

        $postdata = array(
            'type'         => is_null($checktype) ? UWRITE_CHECK_TYPE_WEB : $checktype,
            'file_id'      => $file->external_file_id,
            'callback_url' => sprintf('%1$s%2$s&token=%3$s', $CFG->wwwroot, UWRITE_CALLBACK_URL, $file->identifier),
            'options'      => $options,
        );

        if (uwrite_settings::get_assign_settings($file->cm, 'exclude_citations')) {
            $postdata = array_merge($postdata, array('exclude_citations' => 1, 'exclude_references' => 1));
        }

        return uwrite_api_request::instance()->http_post()->request(self::CHECK_CREATE, $postdata);
    }

    /**
     * @param array $checkids
     *
     * @return mixed
     */
    public function get_check_progress(array $checkids) {
        if (empty($checkids)) {
            throw new \InvalidArgumentException('Invalid argument $checkids');
        }

        return uwrite_api_request::instance()->http_get()->request(self::CHECK_PROGRESS, array(
            'id' => implode(',', $checkids),
        ));
    }

    /**
     * @param $id
     *
     * @return \stdClass
     */
    public function get_check_data($id) {
        if (empty($id)) {
            throw new \InvalidArgumentException('Invalid argument id');
        }

        return uwrite_api_request::instance()->http_get()->request(self::CHECK_GET, array(
            'id' => $id,
        ));
    }

    /**
     * @param \stdClass $file
     *
     * @return mixed
     */
    public function delete_check(\stdClass $file) {
        if (empty($file->check_id)) {
            throw new \InvalidArgumentException('Invalid argument check_id');
        }

        return uwrite_api_request::instance()->http_post()->request(self::CHECK_DELETE, array(
            'id' => $file->check_id,
        ));
    }

    /**
     * @param      $user
     * @param bool $cancomment
     *
     * @return mixed
     */
    public function user_create($user, $cancomment = false) {
        $postdata = array(
            'sys_id'    => $user->id,
            'email'     => $user->email,
            'firstname' => $user->firstname,
            'lastname'  => $user->lastname,
            'scope'     => $cancomment ? self::ACCESS_SCOPE_WRITE : self::ACCESS_SCOPE_READ,
        );

        return uwrite_api_request::instance()->http_post()->request(self::USER_CREATE, $postdata);
    }

    /**
     * @param $cmid
     * @param $options
     */
    private function advanced_check_options($cmid, &$options) {
        $options['exclude_self_plagiarism'] = 1;

        $similaritysensitivity = uwrite_settings::get_assign_settings($cmid, uwrite_settings::SENSITIVITY_SETTING_NAME);
        if (!empty($similaritysensitivity)) {
            $options['sensitivity'] = $similaritysensitivity / 100;
        }
    }
}