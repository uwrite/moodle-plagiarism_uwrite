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
 * uwrite_form.php
 *
 * @package     plagiarism_uwrite
 * @subpackage  plagiarism
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use plagiarism_uwrite\classes\uwrite_settings;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

global $CFG;

require_once($CFG->libdir . '/formslib.php');

/**
 * Class uwrite_setup_form
 */
class uwrite_setup_form extends moodleform {
    // Define the form.
    /**
     * @throws coding_exception
     */
    public function definition() {
        $mform = &$this->_form;
        $mform->addElement('checkbox', 'uwrite_use', plagiarism_uwrite::trans('use_uwrite'));

        $settingstext = '<div id="fitem_id_uwrite_settings_link" class="fitem fitem_ftext ">
                            <div class="felement ftext">
                                <a href="' . UWRITE_DOMAIN . 'profile/apisettings" target="_blank"> ' .
            plagiarism_uwrite::trans('uwrite_settings_url_text') . '</a>
                            </div>
                        </div>';
        $mform->addElement('html', $settingstext);

        $mform->addElement('text', 'uwrite_client_id', plagiarism_uwrite::trans('uwrite_client_id'));
        $mform->addHelpButton('uwrite_client_id', 'uwrite_client_id', 'plagiarism_uwrite');
        $mform->addRule('uwrite_client_id', null, 'required', null, 'client');
        $mform->setType('uwrite_client_id', PARAM_TEXT);

        $mform->addElement('text', 'uwrite_api_secret', plagiarism_uwrite::trans('uwrite_api_secret'));
        $mform->addHelpButton('uwrite_api_secret', 'uwrite_api_secret', 'plagiarism_uwrite');
        $mform->addRule('uwrite_api_secret', null, 'required', null, 'client');
        $mform->setType('uwrite_api_secret', PARAM_TEXT);

        $mform->addElement('textarea', 'uwrite_student_disclosure', plagiarism_uwrite::trans('studentdisclosure'),
            'wrap="virtual" rows="6" cols="100"');
        $mform->addHelpButton('uwrite_student_disclosure', 'studentdisclosure', 'plagiarism_uwrite');
        $mform->setDefault('uwrite_student_disclosure', plagiarism_uwrite::trans('studentdisclosuredefault'));
        $mform->setType('uwrite_student_disclosure', PARAM_TEXT);

        $mods = core_component::get_plugin_list('mod');
        foreach (array_keys($mods) as $mod) {
            if (plugin_supports('mod', $mod, FEATURE_PLAGIARISM) && plagiarism_uwrite::is_support_mod($mod)) {
                $modstring = 'uwrite_enable_mod_' . $mod;
                $mform->addElement('checkbox', $modstring, plagiarism_uwrite::trans('uwrite_enableplugin', $mod));
            }
        }

        $this->add_action_buttons(true);
    }
}

/**
 * Class uwrite_defaults_form
 */
class uwrite_defaults_form extends moodleform {
    /** @var bool */
    private $internalusage = false;
    /** @var string */
    private $modname = '';

    /**
     * uwrite_defaults_form constructor.
     *
     * @param object|null $mform - Moodle form
     * @param string|null $modname
     */
    public function __construct($mform = null, $modname = null) {
        parent::__construct();

        if (!is_null($mform)) {
            $this->_form = $mform;
            $this->internalusage = true;
        }

        if (!is_null($modname) && is_string($modname) && plagiarism_plugin_uwrite::is_enabled_module($modname)) {
            $modname = str_replace('mod_', '', $modname);
            if (plagiarism_uwrite::is_support_mod($modname)) {
                $this->modname = $modname;
            };
        }
    }

    // Define the form.
    /**
     * @throws coding_exception
     */
    public function definition() {

        $defaultsforfield = function (MoodleQuickForm $mform, $setting, $defaultvalue) {
            if (!isset($mform->exportValues()[$setting]) || is_null($mform->exportValues()[$setting])) {
                $mform->setDefault($setting, $defaultvalue);
            }
        };

        $addyesnoelem = function (MoodleQuickForm $mform, $setting, $showhelpballoon = false) {
            $ynoptions = array(get_string('no'), get_string('yes'));
            $mform->addElement('select', $setting, plagiarism_uwrite::trans($setting), $ynoptions);
            if ($showhelpballoon) {
                $mform->addHelpButton($setting, $setting, 'plagiarism_uwrite');
            }
        };

        /** @var MoodleQuickForm $mform */
        $mform = &$this->_form;

        $mform->addElement('header', 'plagiarismdesc', plagiarism_uwrite::trans('uwrite'));

        if ($this->modname === UWRITE_MODNAME_ASSIGN) {
            $mform->addElement('static', 'use_uwrite_static_description', plagiarism_uwrite::trans('useuwrite_assign_desc_param'),
                plagiarism_uwrite::trans('useuwrite_assign_desc_value'));
        }

        $setting = uwrite_settings::USE_UWRITE;
        $addyesnoelem($mform, $setting);
        if ($this->modname === UWRITE_MODNAME_ASSIGN) {
            $mform->addHelpButton($setting, $setting, 'plagiarism_uwrite');
        }

        if (!in_array($this->modname, array(UWRITE_MODNAME_FORUM, UWRITE_MODNAME_WORKSHOP))) {
            $addyesnoelem($mform, uwrite_settings::CHECK_ALL_SUBMITTED_ASSIGNMENTS);
        }

        $setting = uwrite_settings::CHECK_TYPE;
        $mform->addElement('select', $setting, plagiarism_uwrite::trans($setting), array(
            UWRITE_CHECK_TYPE_WEB__LIBRARY => plagiarism_uwrite::trans(UWRITE_CHECK_TYPE_WEB__LIBRARY),
            UWRITE_CHECK_TYPE_WEB          => plagiarism_uwrite::trans(UWRITE_CHECK_TYPE_WEB),
            UWRITE_CHECK_TYPE_MY_LIBRARY   => plagiarism_uwrite::trans(UWRITE_CHECK_TYPE_MY_LIBRARY),
        ));

        $addyesnoelem($mform, uwrite_settings::SHOW_STUDENT_SCORE, true);
        $addyesnoelem($mform, uwrite_settings::SHOW_STUDENT_REPORT, true);

        $setting = uwrite_settings::SENSITIVITY_SETTING_NAME;
        $mform->addElement('text', $setting, plagiarism_uwrite::trans($setting));
        $mform->setType($setting, PARAM_TEXT);
        $defaultsforfield($mform, $setting, 0);

        $setting = uwrite_settings::EXCLUDE_CITATIONS;
        $addyesnoelem($mform, $setting);
        $defaultsforfield($mform, $setting, 1);

        if (!in_array($this->modname, array(UWRITE_MODNAME_FORUM, UWRITE_MODNAME_WORKSHOP))) {
            $addyesnoelem($mform, uwrite_settings::NO_INDEX_FILES);
        }

        if (!$this->internalusage) {
            $this->add_action_buttons(true);
        }
    }
}