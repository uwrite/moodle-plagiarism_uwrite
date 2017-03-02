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
 * default_settings.php - Displays default values to use inside assignments for UWRITE
 *
 * @package     plagiarism_uwrite
 * @subpackage  plagiarism
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use plagiarism_uwrite\classes\uwrite_notification;
use plagiarism_uwrite\classes\uwrite_settings;

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/uwrite_form.php');

global $CFG, $DB, $OUTPUT;

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');

require_login();
admin_externalpage_setup('plagiarismuwrite');

$context = context_system::instance();

$mform = new uwrite_defaults_form(null);
// The cmid(0) is the default list.
$uwritedefaults = $DB->get_records_menu(UWRITE_CONFIG_TABLE, array('cm' => 0), '', 'name, value');
if (!empty($uwritedefaults)) {
    $mform->set_data($uwritedefaults);
}
echo $OUTPUT->header();
$currenttab = 'uwritedefaults';
require_once(dirname(__FILE__) . '/views/view_tabs.php');

if (($data = $mform->get_data()) && confirm_sesskey()) {
    $plagiarismelements = plagiarism_plugin_uwrite::config_options();
    foreach ($plagiarismelements as $element) {
        if (isset($data->$element)) {
            if ($element == uwrite_settings::SENSITIVITY_SETTING_NAME
                && (!is_numeric($data->$element)
                    || $data->$element < 0
                    || $data->$element > 100)) {
                if (isset($uwritedefaults[$element])) {
                    continue;
                }

                $data->$element = 0;
            }

            $newelement = new Stdclass();
            $newelement->cm = 0;
            $newelement->name = $element;
            $newelement->value = $data->$element;

            if (isset($uwritedefaults[$element])) {
                $newelement->id = $DB->get_field(UWRITE_CONFIG_TABLE, 'id', (array('cm' => 0, 'name' => $element)));
                $DB->update_record(UWRITE_CONFIG_TABLE, $newelement);
            } else {
                $DB->insert_record(UWRITE_CONFIG_TABLE, $newelement);
            }
        }
    }

    uwrite_notification::success('defaultupdated', true);
}
echo $OUTPUT->box(plagiarism_uwrite::trans('defaultsdesc'));

$mform->display();
echo $OUTPUT->footer();