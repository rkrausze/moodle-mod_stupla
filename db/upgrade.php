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
 * This file keeps track of upgrades to the stupla module.
 *
 * Sometimes, changes between versions involve
 * alterations to database structures and other
 * major things that may break installations.
 *
 * The upgrade function in this file will attempt
 * to perform all the necessary actions to upgrade
 * your older installation to the current version.
 *
 * If there's something it cannot do itself, it
 * will tell you what you need to do.
 *
 * The commands in here will all be database-neutral,
 * using the methods of database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @package    mod_stupla
 * @copyright  2013 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrades the DB from older versions.
 * @param int $oldversion the currently installed version
 * @return bool sucess or failed
 */
function xmldb_stupla_upgrade($oldversion=0) {

    global $CFG, $THEME, $db; // From 1.9er Moodle.
    global $DB; // Fro 2.xer Moodle.

    $result = true;

    // Still from 1.9.
    if ($result && $oldversion < 2010110100) {

        // Add flags to stupla session.
        $table = new XMLDBTable('stupla_session');
        $field = new XMLDBField('flags');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0, 'starttime');
        $result = $result && add_field($table, $field);

    }

    // Despite everybody claimed that there is no need to migrate stuplas from Moodle 1.9 to 2.x
    // now afterwards everybody is shure that we need it.
    // So this is a soft migration od the DB structure.
    // So we use here 2.x code.
    if ($result && $oldversion < 2013092500) {

        $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

        // Still old 1.9er table fields?
        $table = new xmldb_table('stupla');
        $summary = new xmldb_field('summary', XMLDB_TYPE_TEXT, "small");

        if ($dbman->field_exists($table, $summary)) {
            // Convert summary to intro.
            $dbman->rename_field($table, $summary, 'intro');
            $intro = new xmldb_field('intro', XMLDB_TYPE_TEXT, "big");
            $dbman->change_field_precision($table, $intro);
            $dbman->change_field_notnull($table, $intro);
            // Add introfromat.
            $introformat = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, 4, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
            $dbman->add_field($table, $introformat);
            $DB->execute('update '.$CFG->prefix.'stupla set introformat = 1');
        }
    }

    // Add archivetag for sessions.
    if ($result && $oldversion < 2013092701) {

        $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

        $table = new xmldb_table('stupla_session');
        $archivetag = new xmldb_field('archivetag', XMLDB_TYPE_CHAR, 255);
        $dbman->add_field($table, $archivetag);
        $DB->execute('update '.$CFG->prefix.'stupla_session set archivetag = \'deleted\' where flags = 1');
    }

    // Introduce backupable separators.
    if ( $oldversion < 2013092702 ) {

        // In stupla subuserdata.
        $countstuplas = $DB->count_records('stupla');
        $stuplas = $DB->get_recordset('stupla');

        $pbar = new progress_bar('stuplaupdate1', 500, true);
        $i = 0;
        foreach ($stuplas as $stupla) {
            $i++;
            $stupla->subuserdata = str_replace("\x01", "$@STUPLAX01@$", $stupla->subuserdata);
            $stupla->subuserdata = str_replace("\x02", "$@STUPLAX02@$", $stupla->subuserdata);
            $DB->update_record('stupla', $stupla);
            $pbar->update($i, $countstuplas, "Updating separators in stuplas ($i/$countstuplas)");
        }
        $stuplas->close();

        // In session data.
        $countsessions = $DB->count_records('stupla_session');
        $sessions = $DB->get_recordset('stupla_session');

        $pbar = new progress_bar('stuplaupdate2', 500, true);
        $i = 0;
        foreach ($sessions as $session) {
            $i++;
            $session->data = str_replace("\x01", "$@STUPLAX01@$", $session->data);
            $DB->update_record('stupla_session', $session);
            $pbar->update($i, $countsessions, "Updating separators in sessions ($i/$countsessions)");
        }
        $sessions->close();
    }

    return $result;
}

