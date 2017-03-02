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
 * Class uwrite_content
 *
 * @package   plagiarism_uwrite\classes\plagiarism
 * @namespace plagiarism_uwrite\classes\plagiarism
 *
 */
class uwrite_content extends uwrite_plagiarism_entity {
    /**
     * @var string
     */
    private $content;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $ext;
    /**
     * @var int
     */
    private $parentid;

    /**
     * uwrite_content constructor.
     *
     * @param uwrite_core $core
     * @param string      $content
     * @param             $name
     * @param null        $ext
     * @param null        $parentid
     *
     * @throws uwrite_exception
     */
    public function __construct(uwrite_core $core, $content = null, $name, $ext = null, $parentid = null) {
        if (!$ext) {
            $ext = 'html';
        }

        $this->core = $core;
        $this->name = $name;
        $this->ext = $ext;
        $this->parentid = $parentid;

        $this->set_content($content);
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

        $uploadedfileresponse = uwrite_api::instance()->upload_file(
            $this->content,
            $this->name,
            $this->ext,
            $this->cmid()
        );

        if ($uploadedfileresponse->result) {
            $internalfile->external_file_id = $uploadedfileresponse->file->id;
            $DB->update_record(UWRITE_FILES_TABLE, $internalfile);
        } else {
            $this->store_file_errors($uploadedfileresponse);
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
                'identifier' => sha1($this->name . $this->cmid() . UWRITE_DEFAULT_FILES_AREA . $this->parentid),
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
                    'identifier' => $filedata['identifier'],
                    'filename'   => $this->name,
                ));

                if ($this->parentid) {
                    $plagiarismfile->parent_id = $this->parentid;
                }

                if (!$pid = $DB->insert_record(UWRITE_FILES_TABLE, $plagiarismfile)) {
                    debugging("INSERT INTO {UWRITE_FILES_TABLE}");
                }

                $plagiarismfile->id = $pid;
            }
        } catch (\Exception $ex) {
            print_error($ex->getMessage());
        }

        $this->plagiarismfile = $plagiarismfile;

        return $this->plagiarismfile;
    }

    /**
     * @return string
     */
    public function get_content() {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function set_content($content) {
        $this->content = $content;
    }
}