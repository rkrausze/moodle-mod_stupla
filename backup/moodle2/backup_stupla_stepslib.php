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
 * The implementation steps of backup.
 *
 * @package    mod_stupla
 * @subpackage backup-moodle2
 * @category   backup
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define all the backup steps that will be used by the backup_stupla_activity_task
 */

/**
 * Define the complete stupla structure for backup, with file and id annotations
 *
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_stupla_activity_structure_step extends backup_activity_structure_step {

    /**
     * Overwritten definition of structure.
     * @see backup_structure_step::define_structure()
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $stupla = new backup_nested_element('stupla', array('id'), array(
            'name', 'intro', 'introformat',
            'timeopen', 'timeclose',
            'location', 'reference', 'localpath',
            'password', 'subnet', 'timecreated', 'timemodified',
            'flags', 'subuserdata', 'subuserprotname'));

        $sessions = new backup_nested_element('sessions');

        $session = new backup_nested_element('session', array('id'), array(
            'userid', 'data', 'starttime', 'flags', 'archivetag'));

        $actions = new backup_nested_element('actions');

        $action = new backup_nested_element('action', array('id'), array(
            'starttime', 'media', 'topic', 'nr', 'timestamp', 'duration', 'result', 'data'));

        $exs = new backup_nested_element('exs');

        $ex = new backup_nested_element('ex', array('id'), array(
            'starttime', 'media', 'topic', 'nr', 'ex'));

        $sheets = new backup_nested_element('sheets');

        $sheet = new backup_nested_element('sheet', array('id'), array(
            'starttime', 'sheet'));

        $plans = new backup_nested_element('plans');

        $plan = new backup_nested_element('plan', array('id'), array(
            'starttime', 'plan'));

        // Build the tree.
        $stupla->add_child($sessions);
        $sessions->add_child($session);

        $session->add_child($actions);
        $actions->add_child($action);

        $session->add_child($exs);
        $exs->add_child($ex);

        $session->add_child($sheets);
        $sheets->add_child($sheet);

        $session->add_child($plans);
        $plans->add_child($plan);

        // Define sources.
        $stupla->set_source_table('stupla', array('id' => backup::VAR_ACTIVITYID));

        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {
            $session->set_source_table('stupla_session', array('stupla' => backup::VAR_ACTIVITYID));

            $action->set_source_table('stupla_action', array('session' => backup::VAR_PARENTID));
            $ex->set_source_table('stupla_ex', array('session' => backup::VAR_PARENTID));
            $sheet->set_source_table('stupla_sheet', array('session' => backup::VAR_PARENTID));
            $plan->set_source_table('stupla_plan', array('session' => backup::VAR_PARENTID));
        }

        // Define id annotations.
        $session->annotate_ids('user', 'userid');

        // Define file annotations
        $stupla->annotate_files('mod_stupla', 'sourcefile', null); // Intern files.

        // Return the root element (stupla), wrapped into standard activity structure.
        return $this->prepare_activity_structure($stupla);
    }
}
