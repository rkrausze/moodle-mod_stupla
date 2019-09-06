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
 * This page is the connection to the stupla to load user data
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('course', 0, PARAM_INT); // Course_module ID, or ...
$n  = optional_param('stupla', 0, PARAM_INT);  // stupla instance ID.

$sessionid = optional_param('user', 0, PARAM_INT);

if ($id) {
    $cm      = get_coursemodule_from_id('stupla', $id, 0, false, MUST_EXIST);
    $course  = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $stupla  = $DB->get_record('stupla', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $stupla  = $DB->get_record('stupla', array('id' => $n), '*', MUST_EXIST);
    $course  = $DB->get_record('course', array('id' => $stupla->course), '*', MUST_EXIST);
    $cm      = get_coursemodule_from_instance('stupla', $stupla->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

$action  = optional_param('action', '', PARAM_ACTION);  // Action.

header("Expires: 0");

require_once('prot/data_load.php');

?>
<html>
  <head>
    <meta http-equiv="expires" content="0">
  </head>
  <body bgcolor="#FFFF80">
  <script type="text/javascript">
<?php

// Compare fuctions.

function marksort($a, $b) {
    if ( $a[0] == $b[0] ) {
        return $a[1] - $b[1];
    } else {
        return $a[0] - $b[0];
    }
}

function notesort($a, $b) {
    if ( $a[0] == $b[0] ) {
        return $a[1] - $b[1];
    } else {
        return $a[0] - $b[0];
    }
}

// Read sheets.

$sheets = "new Array()";
if ($sheetsrec = $DB->get_record_select("stupla_sheet", "stupla='$stupla->id' AND session='$sessionid' ORDER BY starttime DESC", null, '*', IGNORE_MULTIPLE) ) {
    $sheets = $sheetsrec->sheet;
}

// Read plan.

$plan = "new Array()";
if ($planrec = $DB->get_record_select("stupla_plan", "stupla='$stupla->id' AND session='$sessionid' ORDER BY starttime DESC", null, '*', IGNORE_MULTIPLE) ) {
    $plan = $planrec->plan;
}

// Read protocol.

$protocol = '';

dataloadstudy($stupla);

$marks = array();
$notes = array();
$n_notes = 0;

if ($records = $DB->get_records('stupla_action', array('stupla' => $stupla->id, 'session' => $sessionid), 'starttime ASC')) {
    foreach ($records as $r) {
        if ( $r->media == STUPLA_MARK ) {
            $marks[] = explode(" ", $r->data);
        } else if ( $r->media == STUPLA_NOTE ) {
            $help = explode(" ", $r->data);
            if ( count($help) < 3 ) {
                continue;
            }
            if ( $help[0] == "ERASE" ) {
                $i = 0;
                for ($i = $n_notes-1; $i >= 0; $i--) {
                    if ( $notes[$i][0] == $help[1] && $notes[$i][1] == $help[2] && $notes[$i][2] == $help[3] ) {
                        break;
                    }
                }
                if ( $i >= 0 ) {
                    for (; $i < $n_notes-1; $i++) {
                        $notes[$i] = $notes[$i+1];
                    }
                    $n_notes--;
                }
            } else if ( $help[0] == "EDIT" ) {
                for ($i = $n_notes-1; $i >= 0; $i--) {
                    if ( $notes[$i][0] == $help[1] && $notes[$i][1] == $help[2] ) {
                        $notes[$i][2] = join(' ', array_slice($help, 3));
                        break;
                    }
                }
            } else {
                $notes[$n_notes++] = array($help[0], $help[1], join(' ', array_slice($help, 2)));
            }
        } else if ( $r->topic != -99 ) {
            $sdata = $r->data;
            if ( $r->media == $s2maufgabe ) {
                if ( $r->result == 0 && $sdata == '' ) { // Only visited.
                    $r->result = '';
                }
                $sdata = ''; // Omit the remaining.
            } else {
                $sdata = ' '.$sdata;
            }
            $protocol .= ( $protocol == '' ? "\"" : ",\r\n  \"" ).
                            "$r->media.$r->topic.$r->nr&$r->timestamp&$r->duration&$r->result".str_replace('"', '\"', $sdata)."\"";
        }
    }
}

usort($marks, "marksort");
usort($notes, "notesort");

// Read exercises.

$ex = "new Array()";
$exhash = array();

if ($records = $DB->get_records_select("stupla_ex", "stupla='$stupla->id' AND session='$sessionid'", null, "starttime ASC", "*")) {
    foreach ($records as $r) {
        $exhash["$r->media.$r->topic.$r->nr"] = $r->ex;
    }
    $ex = "new Array(";
    $add = '';
    foreach ($exhash as $key => $val) {
        $ex .= $add."'".$key."', unescape('".rawurlencode($val)."')";
        $add = ",\r\n ";
    }
    $ex .= ");";
}

stupla_add_extra_action(STUPLA_LOGIN, optional_param('browser', '', PARAM_TEXT));

// Write back HTML.

echo "var Marks = new Array(";

for ($i = 0; $i < count($marks); $i++) {
    echo $i != 0 ? ",\r\n  " : '',  "new Array(", join(',', $marks[$i]), ")";
}

echo ");\r\nvar Notes = new Array(";

for ($i = 0; $i < $n_notes; $i++) {
    echo $i != 0 ? ",\r\n  " : '', "new Array(", $notes[$i][0], ", ", $notes[$i][1], ', "',
        str_replace(array('"', "\r", "\n"), array('\"', '\r', '\n'),
        ($stupla->flags & STUPLA_ISUTF8) ? $notes[$i][2] : utf8_decode($notes[$i][2])), '")';
}
?>
  );
  var Ex = <?php echo ($stupla->flags & STUPLA_ISUTF8) ? $ex : utf8_decode($ex) ?>;
  var Sheets = <?php echo ($stupla->flags & STUPLA_ISUTF8) ? $sheets : utf8_decode($sheets) ?>;
  var Plan = <?php echo ($stupla->flags & STUPLA_ISUTF8) ? $plan : utf8_decode($plan) ?>;
  var Protocol = new Array(<?php echo ($stupla->flags & STUPLA_ISUTF8) ? $protocol : utf8_decode($protocol)?>);
  var NewUser = 0;
  var FBLoad = '';
  top.control.S2sLoadBack();
  </script>
    <form method="POST" action="<?php echo $CFG->wwwroot; ?>/mod/stupla/s2_prot.php">
      <input type="hidden" name="course" value="<?php echo $id; ?>"/>
      <input type="hidden" name="stupla" value="<?php echo $n; ?>"/>
      <input type="hidden" name="user" value="<?php echo $sessionid; ?>"/>
      <input type="hidden" name="type" value=""/>
      <input type="hidden" name="data" value=""/>
      <input type="hidden" name="marks" value=""/>
      <input type="hidden" name="notes" value=""/>
      <input type="hidden" name="data2" value=""/>
      <input type="hidden" name="prot" value=""/>
    </form>
  </body>
</html>