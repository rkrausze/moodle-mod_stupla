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
 * The implementation of the steps for restore.
 *
 * @package    mod_stupla
 * @subpackage backup-moodle2
 * @category   backup
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define all the restore steps that will be used by the restore_stupla_activity_task
 */

/**
 * Structure step to restore one stupla activity
 *
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_stupla_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines the structur for restoring.
     * @see restore_structure_step::define_structure()
     */
    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('stupla', '/activity/stupla');

        if ($userinfo) {
            $paths[] = new restore_path_element('stupla_session', '/activity/stupla/sessions/session');
            $paths[] = new restore_path_element('stupla_action', '/activity/stupla/sessions/session/actions/action');
            $paths[] = new restore_path_element('stupla_ex', '/activity/stupla/sessions/session/exs/ex');
            $paths[] = new restore_path_element('stupla_sheet', '/activity/stupla/sessions/session/sheets/sheet');
            $paths[] = new restore_path_element('stupla_plan', '/activity/stupla/sessions/session/plans/plan');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restore the Stupla-Data for table stupla.
     * @param unknown $data
     */
    protected function process_stupla($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the stupla record.
        $newitemid = $DB->insert_record('stupla', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Restore the Stupla-Data for table stupla_session.
     * @param unknown $data
     */
    protected function process_stupla_session($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->stupla = $this->get_new_parentid('stupla');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->starttime = $this->apply_date_offset($data->starttime);

        $newitemid = $DB->insert_record('stupla_session', $data);
        $this->set_mapping('stupla_session', $oldid, $newitemid);
    }

    /**
     * Restore the Stupla-Data for table stupla_action.
     * @param unknown $data
     */
    protected function process_stupla_action($data) {
        global $DB;

        $data = (object)$data;

        $data->stupla = $this->get_new_parentid('stupla');
        $data->session = $this->get_new_parentid('stupla_session');
        $data->starttime = $this->apply_date_offset($data->starttime);

        $newitemid = $DB->insert_record('stupla_action', $data);
    }

    /**
     * Restore the Stupla-Data for table stupla_action.
     * @param unknown $data
     */
    protected function process_stupla_ex($data) {
        global $DB;

        $data = (object)$data;

        $data->stupla = $this->get_new_parentid('stupla');
        $data->session = $this->get_new_parentid('stupla_session');
        $data->starttime = $this->apply_date_offset($data->starttime);

        $newitemid = $DB->insert_record('stupla_ex', $data);
    }

    /**
     * Restore the Stupla-Data for table stupla_action.
     * @param unknown $data
     */
    protected function process_stupla_sheet($data) {
        global $DB;

        $data = (object)$data;

        $data->stupla = $this->get_new_parentid('stupla');
        $data->session = $this->get_new_parentid('stupla_session');
        $data->starttime = $this->apply_date_offset($data->starttime);

        $newitemid = $DB->insert_record('stupla_sheet', $data);
    }

    /**
     * Restore the Stupla-Data for table stupla_action.
     * @param unknown $data
     */
    protected function process_stupla_plan($data) {
        global $DB;

        $data = (object)$data;

        $data->stupla = $this->get_new_parentid('stupla');
        $data->session = $this->get_new_parentid('stupla_session');
        $data->starttime = $this->apply_date_offset($data->starttime);

        $newitemid = $DB->insert_record('stupla_plan', $data);
    }

    /**
     * Overwrite the work over after pure restoring.
     * We have to add the related files.
     * @see restore_structure_step::after_execute()
     */
    protected function after_execute() {
        // Add stupla related files.
        // Intern files.
        $this->add_related_files('mod_stupla', 'sourcefile', null);
    }
}
