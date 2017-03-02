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
 * uwrite_bulk_check_assign_files.class.php
 *
 * @package     plagiarism_uwrite
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_uwrite\classes\task;

use plagiarism_uwrite\classes\entities\uwrite_archive;
use plagiarism_uwrite\classes\uwrite_api;
use plagiarism_uwrite\classes\uwrite_assign;
use plagiarism_uwrite\classes\uwrite_core;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Class uwrite_bulk_check_assign_files
 * @package plagiarism_uwrite\classes\task
 */
class uwrite_bulk_check_assign_files extends uwrite_abstract_task {
    /** @var  uwrite_core */
    private $uwritecore;
    /** @var  \stored_file */
    private $assignfile;

    public function execute() {
        $data = $this->get_custom_data();

        $assignfiles = uwrite_assign::get_area_files($data->contextid);

        if (empty($assignfiles)) {
            return;
        }

        foreach ($assignfiles as $this->assignfile) {
            if (uwrite_assign::is_draft($this->assignfile->get_itemid())) {
                continue;
            }

            $this->uwritecore = new uwrite_core($data->cmid, $this->assignfile->get_userid());

            $pattern = '%s with uuid ' . $this->assignfile->get_pathnamehash() . ' ready to send';
            if (\plagiarism_uwrite::is_archive($this->assignfile)) {
                mtrace(sprintf($pattern, 'Archive'));
                $this->handle_archive($data->cmid);
            } else {
                mtrace(sprintf($pattern, 'File'));
                $this->handle_non_archive();
            }
        }
    }

    /**
     * @param $contextid
     */
    private function handle_archive($contextid) {
        if (!is_null(uwrite_core::get_file_by_hash($contextid, $this->assignfile->get_pathnamehash()))) {
            mtrace('... archive already sent to Uwrite');

            return;
        }

        $uwritearchive = new uwrite_archive($this->assignfile, $this->uwritecore);
        $uwritearchive->run_checks();
        mtrace('... archive send to Uwrite');
    }

    private function handle_non_archive() {
        $plagiarismentity = $this->uwritecore->get_plagiarism_entity($this->assignfile);
        $internalfile = $plagiarismentity->upload_file_on_uwrite_server();
        if (isset($internalfile->check_id)) {
            mtrace('... file already sent to Uwrite');
        } else if ($internalfile->external_file_id) {
            $checkresp = uwrite_api::instance()->run_check($internalfile);
            $plagiarismentity->handle_check_response($checkresp);
            mtrace('... file send to Uwrite');
        }
    }
}