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

namespace plagiarism_uwrite\classes;

use plagiarism_uwrite\classes\helpers\uwrite_stored_file;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class uwrite_plagiarism_entity
 *
 * @package plagiarism_uwrite\classes
 */
abstract class uwrite_plagiarism_entity {
    const TYPE_ARCHIVE = 'archive';
    const TYPE_DOCUMENT = 'document';
    /** @var uwrite_core */
    protected $core;
    /** @var \stdClass */
    protected $plagiarismfile;

    /**
     * @return object
     */
    abstract public function upload_file_on_uwrite_server();

    /**
     * @return object
     */
    abstract public function get_internal_file();

    /**
     * @return integer
     */
    protected function cmid() {
        return $this->core->cmid;
    }

    /**
     * @return integer
     */
    protected function userid() {
        return $this->core->userid;
    }

    /**
     * @param \stdClass $response
     *
     * @return bool
     */
    protected function store_file_errors(\stdClass $response) {
        global $DB;

        $plagiarismfile = $this->get_internal_file();
        $plagiarismfile->statuscode = UWRITE_STATUSCODE_INVALID_RESPONSE;
        $plagiarismfile->errorresponse = json_encode($response->errors);

        $result = $DB->update_record(UWRITE_FILES_TABLE, $plagiarismfile);

        if ($result && $plagiarismfile->parent_id) {
            $hasgoodchild = $DB->count_records_select(UWRITE_FILES_TABLE, "parent_id = ? AND statuscode in (?,?,?)",
                array(
                    $plagiarismfile->parent_id, UWRITE_STATUSCODE_PROCESSED, UWRITE_STATUSCODE_ACCEPTED,
                    UWRITE_STATUSCODE_PENDING,
                ));

            if (!$hasgoodchild) {
                $parentplagiarismfile = uwrite_stored_file::get_uwrite_file($plagiarismfile->parent_id);
                $parentplagiarismfile->statuscode = UWRITE_STATUSCODE_INVALID_RESPONSE;
                $parentplagiarismfile->errorresponse = json_encode($response->errors);

                $DB->update_record(UWRITE_FILES_TABLE, $parentplagiarismfile);
            }
        }

        return $result;
    }

    /**
     * @param \stdClass $checkresp
     */
    public function handle_check_response(\stdClass $checkresp) {
        if ($checkresp->result === true) {
            $this->update_file_accepted($checkresp->check);
        } else {
            $this->store_file_errors($checkresp);
        }
    }

    /**
     * @param $check
     *
     * @return bool
     */
    protected function update_file_accepted($check) {
        global $DB;

        $plagiarismfile = $this->get_internal_file();
        $plagiarismfile->attempt = 0; // Reset attempts for status checks.
        $plagiarismfile->check_id = $check->id;
        $plagiarismfile->statuscode = UWRITE_STATUSCODE_ACCEPTED;
        $plagiarismfile->errorresponse = null;

        return $DB->update_record(UWRITE_FILES_TABLE, $plagiarismfile);
    }

    /**
     * @param $data
     *
     * @return null|\stdClass
     */
    public function new_plagiarismfile($data) {

        foreach (array('cm', 'userid', 'identifier', 'filename') as $key) {
            if (empty($data[$key])) {
                print_error($key . ' value is empty');

                return null;
            }
        }

        $plagiarismfile = new \stdClass();
        $plagiarismfile->cm = $data['cm'];
        $plagiarismfile->userid = $data['userid'];
        $plagiarismfile->identifier = $data['identifier'];
        $plagiarismfile->filename = $data['filename'];
        $plagiarismfile->statuscode = UWRITE_STATUSCODE_PENDING;
        $plagiarismfile->attempt = 0;
        $plagiarismfile->progress = 0;
        $plagiarismfile->timesubmitted = time();
        $plagiarismfile->type = self::TYPE_DOCUMENT;

        return $plagiarismfile;
    }
}