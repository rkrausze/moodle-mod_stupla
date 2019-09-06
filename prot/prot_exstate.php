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
 * This page is the protocol page for exercise states.
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');
require_once('data_load.php');

$groupnr = 0 //TODO? $_REQUEST ? $_REQUEST["GroupNr"] : $HTTP_GET_VARS["GroupNr"];

?>
<HTML>
  <HEAD>
    <meta http-equiv="expires" content="0">
    <title>Protokoll - Exercise States, study2000: <?php echo $stupla->name ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="expires" content="0">
<style type="text/css"><!--
  body, td, p { font-size:10pt;font-family:Arial }
  .username { font-weight:bold; color:#400000 }
//--></style>
</head>

<body bgcolor="#FFFFFF">
<form>
<table width="100%"><tr><td><b><?php print_string('exercise_states', 'stupla') ?>, study 2000: </b> <?php echo $stupla->name ?>
<?php
if ( $s2maufgabe == -1 ) {
  ?></td></tr></table><p>Keine Aufgaben eingebunden.</p><?php
} else {
  ?>
  </td><td align=right><select name="GroupNr" size=1 onChange="top.control.GroupNr = document.forms[0].GroupNr.selectedIndex; top.control.ExOverview();">
    <option<?php echo $groupnr == 0 ? " selected" : ""; ?>>normal sortiert</option><?php
/*  if ( array_key_exists("ExGroups", $adapt[$AppName]) )
    for ($i = 0; $i < count($adapt[$AppName]["ExGroups"]); $i++)
      echo "<option", ($groupnr == $i+1 ? " selected" : ""), ">", $adapt[$AppName]["ExGroups"][$i][0], "</option>";
*/
  ?></select></TD></TR></TABLE>

  <TABLE border=0 cellpadding=4>
<?php
    // Aufgaben laut group den Blöcken zuordnen
    $indexblock = 7; // hoffentlich unbenutzter Index bei Media[][][][?]
    $indexcollect = 8; // dort Feld[richtig, falsch, unbenutzt]
    $blockname = array();
    $nblock = 0;
    if ( $groupnr == 0 ) { // Normal nach DeepNullTopics.
        $countex = 0;
        $oldtopic = 0;
        for ($i = 0; $i < count($s2name); $i++) {
            if ( $s2deep[$i] == 0 ) {
                if ( $countex > 0 ) { // Use only if exercises conained.
                    $blockname[$nblock++] = $s2name[$oldtopic];
                }
                $countex = 0;
                $oldtopic = $i;
            }
            if ( array_key_exists($i, $s2media[$s2maufgabe]) ) {
                for ($k = 0; $k < count($s2media[$s2maufgabe][$i]); $k++) {
                    $s2media[$s2maufgabe][$i][$k][$indexblock] = $nblock;
                    $s2media[$s2maufgabe][$i][$k][$indexcollect] = array(0, 0, 0);
                    $countex++;
                }
            }
        }
        if ( $countex > 0 ) {
            $blockname[$nblock++] = $s2name[$oldtopic];
        }
    } else {
        $group = $adapt[$AppName]['ExGroups'][$groupnr-1];
        // Take names.
        $nblock = count($group);
        for ($i = 0; $i < $nblock; $i++) {
            $blockname[$i] = $group[$i+1][0];
        }
        // Read exercises and sort in.
        for ($i = 0; $i < count($s2name); $i++) {
            if ( array_key_exists($i, $s2media[$s2maufgabe]) ) {
                for ($k = 0; $k < count($s2media[$s2maufgabe][$i]); $k++) {
                    // Get block.
                    $bl = -1;
                    for ($bl1 = 0; $bl1 < $nblock && $bl == -1; $bl1++) {
                        for ($j = 1; $j < count($group[$bl1+1]) && $bl == -1; $j++) {
                            if ( preg_match($group[$bl1+1][$j], $s2media[$s2maufgabe][$i][$k][0]) ) {
                                $bl = $bl1;
                            }
                        }
                    }
                    $s2media[$s2maufgabe][$i][$k][$indexblock] = $bl;
                    $s2media[$s2maufgabe][$i][$k][$indexcollect] = array(0, 0, 0);
                }
            }
        }
    }

    $errorstring = '';
    // Collect data.
    $sessions = stupla_prot_get_sessions($stupla);
    foreach ($sessions as $session) {
        stupla_prot_clear_hist();
        stupla_prot_read_hist($session, $stupla);
        stupla_prot_collect_ex_status($indexcollect);
    }
    // Sum up blocks.
    $blockvals = array();
    for ($i = 0; $i < $nblock; $i++) {
        $blockvals[$i] = array(0, 0, 0);
    }
    for ($i = 0; $i < count($s2name); $i++) {
        if ( array_key_exists($i, $s2media[$s2maufgabe]) ) {
            for ($k = 0; $k < count($s2media[$s2maufgabe][$i]); $k++) {
                $bl = $s2media[$s2maufgabe][$i][$k][$indexblock];
                if ( $bl == -1 ) {
                    continue;
                }
                $blockvals[$bl][0] += $s2media[$s2maufgabe][$i][$k][$indexcollect][0];
                $blockvals[$bl][1] += $s2media[$s2maufgabe][$i][$k][$indexcollect][1];
                $blockvals[$bl][2] += $s2media[$s2maufgabe][$i][$k][$indexcollect][2];
            }
        }
    }
    // Write back data.
    echo '<table><tr><td>', get_string('exercise', 'stupla'), '</td><td>', get_string('state', 'stupla'), '</td></tr>';
    $sumvals = array(0, 0, 0);
    $l = 500;
    $h = 16;
    // Print blocks.
    for ($bl = 0; $bl < $nblock; $bl++) {
        echo '<tr bgcolor="#C0C0C0"><td title="', isset($blockname[$bl]) ? $blockname[$bl] : '', '">',
             stupla_prot_short($blockname[$bl], 40), '</td><td>',
             stupla_prot_ex_state_bar($blockvals[$bl], $l, $h), "</td></tr>\r\n";
        for ($i = 0; $i < count($s2name); $i++) {
            if ( array_key_exists($i, $s2media[$s2maufgabe]) ) {
                for ($k = 0; $k < count($s2media[$s2maufgabe][$i]); $k++) {
                    if ( $s2media[$s2maufgabe][$i][$k][$indexblock] == $bl ) {
                        echo '<tr><td title="', isset($blockname[$i]) ? $blockname[$i] : '', '">',
                             stupla_prot_short($s2media[$s2maufgabe][$i][$k][0], 40), '</td><td>',
                             stupla_prot_ex_state_bar($s2media[$s2maufgabe][$i][$k][$indexcollect], $l, $h), "</td></tr>\r\n";
                    }
                }
            }
        }
        $sumvals[0] += $blockvals[$bl][0];
        $sumvals[1] += $blockvals[$bl][1];
        $sumvals[2] += $blockvals[$bl][2];
    }
?></table><?php
    echo $errorstring;
}
?>
</form>
</body>
</html>