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
 * @package     plagiarism_uwrite
 * @subpackage  plagiarism
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'UWRITE plagiarism plugin';
$string['studentdisclosuredefault'] = 'All  uploaded files will be submitted to the plagiarism detection system UWRITE.';
$string['studentdisclosure'] = 'Familiarize students with the UWRITE Privacy Policy';
$string['studentdisclosure_help'] = 'This text will be displayed to all students on the file upload page.';
$string['uwrite'] = 'UWRITE plagiarism plugin';
$string['uwrite_settings_url_text'] = 'Open Uwrite.proctoru.com admin account to view/copy Client ID/API Secret';
$string['uwrite_client_id'] = 'Client ID';
$string['uwrite_client_id_help'] = 'ID of Client provided by UWRITE to access the API you can find it on <a href="https://uwrite.proctoru.com/profile/apisettings">https://uwrite.proctoru.com/profile/apisettings</a>';
$string['uwrite_lang'] = 'Language';
$string['uwrite_lang_help'] = 'Language code provided by UWRITE';
$string['uwrite_api_secret'] = 'API Secret';
$string['uwrite_api_secret_help'] = 'API Secret provided by UWRITE to access the API you can find it on <a href="https://uwrite.proctoru.com/profile/apisettings">https://uwrite.proctoru.com/profile/apisettings</a>';
$string['use_uwrite'] = 'Enable UWRITE';
$string['use_uwrite_help'] = 'To use Uwrite plugin, first set option Require students click submit button to Yes (Submissions settings).';
$string['useuwrite_assign_desc_param'] = 'To unlock Uwrite settings';
$string['useuwrite_assign_desc_value'] = 'Set Submissions settings → Require students click submit button = Yes';
$string['uwrite_enableplugin'] = 'Enable UWRITE for {$a}';
$string['savedconfigsuccess'] = 'Plagiarism detection settings saved';
$string['savedconfigfailed'] = 'An incorrect Client ID/API Secret combination has been entered. UWRITE has been disabled, please try again.';
$string['uwrite_show_student_score'] = 'Show similarity score to student';
$string['uwrite_show_student_score_help'] = 'The similarity score is the percentage of the submission that has been matched with other content.';
$string['uwrite_show_student_report'] = 'Show similarity report to student';
$string['uwrite_show_student_report_help'] = 'The similarity report gives a breakdown on what parts of the submission were plagiarised and the location that UWRITE found this content. ';
$string['uwrite_draft_submit'] = 'When should the file be submitted to UWRITE';
$string['showwhenclosed'] = 'When Activity closed';
$string['submitondraft'] = 'Submit file when first uploaded';
$string['submitonfinal'] = 'Submit file when student sends it for grading';
$string['defaultupdated'] = 'Default values updated';
$string['defaultsdesc'] = 'The following settings are the defaults set when enabling UWRITE within an Activity Module';
$string['uwritedefaults'] = 'UWRITE defaults';
$string['similarity'] = 'Similarity';
$string['processing'] = 'This file has been submitted to UWRITE, now waiting for the analysis to be available';
$string['pending'] = 'This file is pending submission to UWRITE';
$string['previouslysubmitted'] = 'Previously submitted as';
$string['report'] = 'Report';
$string['unknownwarning'] = 'An error occurred when trying to send this file to UWRITE';
$string['unsupportedfiletype'] = 'This filetype is not supported by UWRITE';
$string['toolarge'] = 'This file is too large for UWRITE to process';
$string['plagiarism'] = 'Potential plagiarism ';
$string['report'] = 'View full report';
$string['progress'] = 'Scan';
$string['uwrite_studentemail'] = 'Send email to Student';
$string['uwrite_studentemail_help'] = 'This will send an email to the student when a file has been processed to let them know that a report is available.';
$string['studentemailsubject'] = 'File processed by UWRITE';
$string['studentemailcontent'] = 'The file you submitted to {$a->modulename} in {$a->coursename} has already been processed by the plagiarism detection system UWRITE
{$a->modulelink}';

$string['filereset'] = 'A file has been reset for re-submission to UWRITE';
$string['noreceiver'] = 'No receiver address was specified';
$string['uwrite:enable'] = 'Allow the teacher to enable/disable UWRITE inside an activity';
$string['uwrite:resetfile'] = 'Allow the teacher to resubmit the file to UWRITE after an error occurred';
$string['uwrite:viewreport'] = 'Allow the teacher to view the full report from UWRITE';
$string['uwritedebug'] = 'Debugging';
$string['explainerrors'] = 'This page lists any files that are currently in an error state. <br/>When files are deleted on this page they will not be able to be resubmitted and errors will no longer display to teachers or students';
$string['id'] = 'ID';
$string['name'] = 'Name';
$string['file'] = 'File';
$string['status'] = 'Status';
$string['module'] = 'Module';
$string['resubmit'] = 'Resubmit';
$string['identifier'] = 'Identifier';
$string['fileresubmitted'] = 'File Queued for resubmission';
$string['filedeleted'] = 'File deleted from queue';
$string['cronwarning'] = 'The <a href="../../admin/cron.php">cron.php</a> maintenance script has not been run for at least 30 min - Cron must be configured to allow UWRITE to function correctly.';
$string['waitingevents'] = 'There are {$a->countallevents} events waiting for cron and {$a->countheld} events are being held for resubmission';
$string['deletedwarning'] = 'This file could not be found - it may have been deleted by the user';
$string['heldevents'] = 'Held events';
$string['heldeventsdescription'] = 'These are events that did not complete on the first attempt and were queued for resubmission - this prevents subsequent events from completing and may need further investigation. Some of these events may not be relevant to UWRITE.';
$string['uwritefiles'] = 'Uwrite Files';
$string['getscore'] = 'Get score';
$string['scorenotavailableyet'] = 'This file has not been processed by UWRITE yet.';
$string['scoreavailable'] = 'This file has been processed by UWRITE and a report is now available.';
$string['receivernotvalid'] = 'This is not a valid receiver address.';
$string['attempts'] = 'Attempts made';
$string['refresh'] = 'Refresh page to see results';
$string['delete'] = 'Delete';
$string['plagiarism_run_success'] = 'File sent for plagiarism scan';

$string['check_type'] = 'Check types for plagiarism';
$string['check_confirm'] = 'Are you sure you want start checking by UWRITE plagiarism plugin?';
$string['check_start'] = 'Uwrite originality grading in progress';
$string['check_file'] = 'Start a scan';

$string['web'] = 'Doc vs Internet';
$string['my_library'] = 'Doc vs Library';
$string['web_and_my_library'] = 'Doc vs Internet + Library';

$string['reportready'] = 'Report ready';
$string['generalinfo'] = 'General information';
$string['similarity_sensitivity'] = 'Hide sources with a match less then (%)';
$string['exclude_citations'] = 'Auto exclude Citations & Reserences';
$string['exclude_self_plagiarism'] = 'Exclude self-plagiarism';
$string['check_all_submitted_assignments'] = 'Check all submitted assignments';
$string['no_index_files'] = 'Exclude submissions from the Institutional Library';