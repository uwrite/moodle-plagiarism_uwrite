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
 * backup_plagiarism_uwrite_plugin.class.php
 *
 * @package     plagiarism_uwrite
 * @subpackage  plagiarism
 *
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Class backup_plagiarism_uwrite_plugin
 */
class backup_plagiarism_uwrite_plugin extends backup_plagiarism_plugin {
    /**
     * @return mixed
     */
    public function define_module_plugin_structure() {
        // Define the virtual plugin element without conditions as the global class checks already.
        $plugin = $this->get_plugin_element();

        // Create one standard named plugin element (the visible container).
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        $uwriteconfigs = new backup_nested_element('uwrite_configs');
        $uwriteconfig = new backup_nested_element('uwrite_config', array('id'), array('name', 'value'));
        $pluginwrapper->add_child($uwriteconfigs);
        $uwriteconfigs->add_child($uwriteconfig);
        $uwriteconfig->set_source_table(UWRITE_CONFIG_TABLE, array('cm' => backup::VAR_PARENTID));

        // Now information about files to module.
        $uwritefiles = new backup_nested_element('uwrite_files');
        $uwritefile = new backup_nested_element('uwrite_file', array('id'), array(
                'userid', 'identifier', 'filename', 'progress', 'reporturl', 'optout', 'statuscode',
                'similarityscore', 'errorresponse', 'timesubmitted',
        ));

        $pluginwrapper->add_child($uwritefiles);
        $uwritefiles->add_child($uwritefile);

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');
        if ($userinfo) {
            $uwritefile->set_source_table('plagiarism_uwrite_files', array('cm' => backup::VAR_PARENTID));
        }

        return $plugin;
    }

    /**
     * @return mixed
     */
    public function define_course_plugin_structure() {
        // Define the virtual plugin element without conditions as the global class checks already.
        $plugin = $this->get_plugin_element();

        // Create one standard named plugin element (the visible container).
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        // Save id from uwrite course.
        $uwriteconfigs = new backup_nested_element('uwrite_configs');
        $uwriteconfig = new backup_nested_element('uwrite_config', array('id'), array('plugin', 'name', 'value'));
        $pluginwrapper->add_child($uwriteconfigs);
        $uwriteconfigs->add_child($uwriteconfig);
        $uwriteconfig->set_source_table('config_plugins', array(
                'name' => backup::VAR_PARENTID, 'plugin' => backup_helper::is_sqlparam('plagiarism_uwrite_course'),
        ));

        return $plugin;
    }
}