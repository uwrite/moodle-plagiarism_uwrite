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

namespace plagiarism_uwrite\classes\plagiarism;

use plagiarism_uwrite\classes\exception\uwrite_exception;
use plagiarism_uwrite\classes\uwrite_api;
use plagiarism_uwrite\classes\uwrite_core;
use plagiarism_uwrite\classes\uwrite_plagiarism_entity;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class uwrite_file
 *
 * @package   plagiarism_uwrite\classes\plagiarism
 * @namespace plagiarism_uwrite\classes\plagiarism
 *
 */
class uwrite_file extends uwrite_plagiarism_entity {
    /**
     * @var \stored_file
     */
    private $file;

    /**
     * uwrite_file constructor.
     *
     * @param uwrite_core  $core
     * @param \stored_file $file
     *
     * @throws uwrite_exception
     */
    public function __construct(uwrite_core $core, \stored_file $file) {
        if (!$file) {
            throw new uwrite_exception('Invalid argument file');
        }

        $this->core = $core;
        $this->file = $file;
    }

    /**
     * @return object
     */
    public function upload_file_on_uwrite_server() {
        global $DB;

        $internalfile = $this->get_internal_file();

        if (isset($internalfile->external_file_id)) {
            return $internalfile;
        }

        // Check if $internalfile actually needs to be submitted.
        if ($internalfile->statuscode !== UWRITE_STATUSCODE_PENDING) {
            return $internalfile;
        }

        // Increment attempt number.
        $internalfile->attempt++;

        $uploadedfileresponse = $this->upload();
        if ($uploadedfileresponse) {
            if ($uploadedfileresponse->result) {
                $internalfile->external_file_id = $uploadedfileresponse->file->id;
                $DB->update_record(UWRITE_FILES_TABLE, $internalfile);
            } else {
                $this->store_file_errors($uploadedfileresponse);
            }
        }

        return $internalfile;
    }

    /**
     * @return object
     */
    public function get_internal_file() {
        global $DB;

        if ($this->plagiarismfile) {
            return $this->plagiarismfile;
        }

        $plagiarismfile = null;
        try {
            $filedata = array(
                'cm'         => $this->cmid(),
                'userid'     => $this->userid(),
                'identifier' => $this->stored_file()->get_pathnamehash(),
            );

            if ($this->core->is_teamsubmission_mode()) {
                unset($filedata['userid']);
            }

            // Now update or insert record into uwrite_files.
            $plagiarismfile = $DB->get_record(UWRITE_FILES_TABLE, $filedata);

            if (empty($plagiarismfile)) {
                $plagiarismfile = $this->new_plagiarismfile(array(
                    'cm'         => $this->cmid(),
                    'userid'     => $this->userid(),
                    'identifier' => $this->stored_file()->get_pathnamehash(),
                    'filename'   => $this->stored_file()->get_filename(),
                ));

                if (\plagiarism_uwrite::is_archive($this->stored_file())) {
                    $plagiarismfile->type = uwrite_plagiarism_entity::TYPE_ARCHIVE;
                }

                if (!$pid = $DB->insert_record(UWRITE_FILES_TABLE, $plagiarismfile)) {
                    debugging("INSERT INTO {UWRITE_FILES_TABLE}");
                }

                $plagiarismfile->id = $pid;
            }
        } catch (\Exception $ex) {
            debugging("get internal file error: {$ex->getMessage()}");
        }

        $this->plagiarismfile = $plagiarismfile;

        return $this->plagiarismfile;
    }

    /**
     * @return \stored_file
     */
    public function stored_file() {
        return $this->file;
    }

    /**
     * @return \stdClass
     */
    private function upload() {
        $format = 'html';
        if ($source = $this->stored_file()->get_source()) {
            $format = pathinfo($source, PATHINFO_EXTENSION);
        }

        return uwrite_api::instance()->upload_file(
            $this->stored_file()->get_content(),
            $this->stored_file()->get_filename(),
            $format,
            $this->cmid(),
            uwrite_core::get_user($this->stored_file()->get_userid())
        );
    }
}
