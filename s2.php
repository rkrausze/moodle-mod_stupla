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
 * This page is the connection to the stupla
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('course', 0, PARAM_INT); // Course_module ID, or ...
$n  = optional_param('stupla', 0, PARAM_INT);  // stupla instance ID.

if ($id) {
    $cm         = get_coursemodule_from_id('stupla', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $stupla  = $DB->get_record('stupla', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $stupla  = $DB->get_record('stupla', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $stupla->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('stupla', $stupla->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course->id);
$context = context_module::instance($cm->id);

$action = optional_param('action', '', PARAM_ACTION); // Action. TODO evtl. loggen

if ($action == 'START' || $action == 'CONTINUE' || $action == 'NEWSESSION') {
    if ($action == 'CONTINUE') {
        $sessionid = optional_param('sessionid', -1, PARAM_INT);
    } else {
        $sessions = false;
        $sudata = false;
        if ($stupla->flags & STUPLA_USESUBUSERS) {
            stupla_expand_subuserdata($stupla);
            foreach ($stupla->sudata as $fieldid => $field) {
                $sudata .= ($sudata == "" ? "" : STUPLA_X01).optional_param('data'.$fieldid, '', PARAM_RAW);
            }
        }
        if (($stupla->flags & STUPLA_FORCEONESESSION) && $action != 'NEWSESSION') {
            if ( $sudata !== false ) {
                $sessions = $DB->get_records_select('stupla_session',
                   'stupla=:stupla AND '.$DB->sql_compare_text('data').' = :sudata  AND archivetag IS NULL',
                    array('stupla' => $stupla->id, 'sudata' => $sudata));
            } else {
                $sessions = $DB->get_records_select('stupla_session',
                    "stupla='$stupla->id' AND userid='$USER->id' AND archivetag IS NULL", null, "starttime DESC", "*");
            }
        }
        if ( $sessions ) {
            $sessionid = current($sessions)->id;
        } else {
            // Generate session.
            $sessiondata = new stdClass;
            $sessiondata->stupla = $stupla->id;
            $sessiondata->userid = $USER->id;
            $sessiondata->starttime = time();
            $sessiondata->data = $sudata;
            $sessionid = $DB->insert_record('stupla_session', $sessiondata);
        }
    }

    $eventdata = array();
    $eventdata['objectid'] = $stupla->id;
    $eventdata['context'] = $context;
    $eventdata['courseid'] = $course->id;
    $eventdata['other'] = array();
    $eventdata['other']['sessionid'] = $sessionid;

    $event = \mod_stupla\event\attempt_started::create($eventdata);
    $event->trigger();

    if ( stupla_is_extern($stupla) ) {
        $filepath = "$stupla->reference";
    } else {
        // Set filedir, filename and filepath.
        switch ($stupla->location) {
            case STUPLA_LOCATION_SITEFILES :
                $site = get_site();
                $filedir = $site->id;
                break;
            case STUPLA_LOCATION_COURSEFILES :
            default :
                $filedir = $stupla->course;
                break;
        }
        $filesubdir = dirname($stupla->reference);
        if ($filesubdir == '.') {
            $filesubdir = '';
        }
        if ($filesubdir) {
            $filesubdir .= '/';
        }
        $filename = basename($stupla->reference);
        $fileroot = "$CFG->dataroot/$filedir";
        $filepath = "$fileroot/$stupla->reference";
    }

    // Short start without splash.
    if ( stupla_is_short_start($stupla) ) {
        header('Location: '.stupla_www_prefix($stupla).$stupla->reference.
            "?load=$CFG->wwwroot/mod/stupla/s2_load.php&course=$id&stupla=$n&user=$sessionid");
        exit;
    }

    // Long start with splash screen.
    if ( stupla_is_extern($stupla) ) {
        $source = file_get_contents($stupla->localpath == '' ? $filepath : $stupla->localpath.'/Start.htm');
    } else {
        $file = stupla_get_file_int($stupla, $stupla->reference);
        $source = $file->get_content();
    }
    $source = str_replace("<title>",
        "<base href=\"".stupla_www_prefix($stupla).stupla_compdirreference($stupla)."/\"><title>", $source);
    $source = str_replace("</body>",
        "<form name=\"fm\" method=\"GET\" action=\"$CFG->wwwroot/mod/stupla/s2.php\">".hid("course", $id).
        hid("stupla", $stupla->id).hid("user", 0).hid("action", "FRAME").hid('sessionid', $sessionid).
        hid("frame", "")."</form></body>", $source);
    $source = preg_replace("/top\.location\.replace\('([^']*)'\+s\);/",
        "document.forms['fm'].frame.value='\\1'; document.forms['fm'].submit()", $source);
    echo $source;
} else if ($action == 'FRAME') {
    $eventdata = array();
    $eventdata['objectid'] = $stupla->id;
    $eventdata['context'] = $context;
    $eventdata['courseid'] = $course->id;
    if ( optional_param('sessionid', -1, PARAM_INT) != -1 ) {
        $eventdata['other'] = array();
        $eventdata['other']['sessionid'] = $sessionid;
    }

    $event = \mod_stupla\event\attempt_started::create($eventdata);
    $event->trigger();

    $filepath = stupla_www_prefix($stupla).$stupla->reference;
    header("Location: ".dirname($filepath)."/".required_param('frame', PARAM_TEXT).
        "?load=$CFG->wwwroot/mod/stupla/s2_load.php&course=$id&stupla=$n&user=".
        optional_param('sessionid', -1, PARAM_INT));
}

function hid($name, $value) {
    return "<input type=\"hidden\" name=\"$name\" value=\"$value\">";
}
