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

use plagiarism_uwrite\classes\helpers\uwrite_stored_file;
use plagiarism_uwrite\classes\uwrite_core;
use plagiarism_uwrite\classes\uwrite_language;

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once(dirname(__FILE__) . '/lib.php');

global $PAGE, $CFG, $OUTPUT, $USER;

$cmid = required_param('cmid', PARAM_INT); // Course Module ID.
$cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);
require_login($cm->course, true, $cm);

$pf = required_param('pf', PARAM_INT); // Plagiarism file id.
$childs = uwrite_stored_file::get_childs($pf);

$modulecontext = context_module::instance($cmid);

$pageparams = array('cmid' => $cmid, 'pf' => $pf);
$cpf = optional_param('cpf', null, PARAM_INT); // Plagiarism child file id.
if ($cpf !== null) {
    $current = uwrite_stored_file::get_uwrite_file($cpf);
    $currenttab = 'uwrite_file_id_' . $current->id;
    $pageparams['cpf'] = $cpf;
} else {
    $currenttab = 'uwrite_files_info';
}

$PAGE->set_pagelayout('report');
$pageurl = new \moodle_url('/plagiarism/uwrite/reports.php', $pageparams);
$PAGE->set_url($pageurl);

echo $OUTPUT->header();

$tabs = array();
$fileinfos = array();
foreach ($childs as $child) {

    switch ($child->statuscode) {
        case UWRITE_STATUSCODE_PROCESSED :

            $url = new \moodle_url('/plagiarism/uwrite/reports.php', array(
                'cmid' => $cmid,
                'pf'   => $pf,
                'cpf'  => $child->id,
            ));

            if ($child->check_id !== null && $child->progress == 100) {

                $tabs[] = new tabobject('uwrite_file_id_' . $child->id, $url->out(), $child->filename, '', false);

                $link = html_writer::link($url, $child->filename);
                $fileinfos[] = array(
                    'filename' => html_writer::tag('div', $link, array('class' => 'edit-link')),
                    'status'   => $OUTPUT->pix_icon('i/valid', plagiarism_uwrite::trans('reportready')) .
                        plagiarism_uwrite::trans('reportready'),
                );
            }
            break;
        case UWRITE_STATUSCODE_INVALID_RESPONSE :

            $erroresponse = plagiarism_uwrite::error_resp_handler($child->errorresponse);
            $fileinfos[] = array(
                'filename' => $child->filename,
                'status'   => $OUTPUT->pix_icon('i/invalid', $erroresponse) . $erroresponse,
            );
            break;
    }
};

$generalinfourl = new \moodle_url('/plagiarism/uwrite/reports.php', array(
    'cmid' => $cmid,
    'pf'   => $pf,
));

array_unshift($tabs,
    new tabobject('uwrite_files_info', $generalinfourl->out(), plagiarism_uwrite::trans('generalinfo'), '', false));

print_tabs(array($tabs), $currenttab);

echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');

if ($cpf !== null) {
    $reporturl = $current->reporturl;
    if (uwrite_core::is_teacher($cmid)) {
        $reporturl = $current->reportediturl;
    }
    uwrite_core::inject_comment_token($reporturl, $cmid);
    uwrite_language::inject_language_to_url($reporturl);

    echo '<iframe src="' . $reporturl . '" frameborder="0" id="_uwrite_report_frame" style="width: 100%; height: 750px;"></iframe>';
} else {
    $table = new html_table();
    $table->head = array('Filename', 'Status');
    $table->align = array('left', 'left');

    foreach ($fileinfos as $fileinfo) {
        $linedata = array($fileinfo['filename'], $fileinfo['status']);
        $table->data[] = $linedata;
    }

    echo html_writer::table($table);
}
echo $OUTPUT->box_end();

echo $OUTPUT->footer();