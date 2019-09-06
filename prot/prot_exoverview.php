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
 * This page is the protocol page for exercise overviews.
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');
require_once('data_load.php');

$groupnr = 0; // TODO? $_REQUEST ? $_REQUEST["GroupNr"] : $HTTP_GET_VARS["GroupNr"];
$width = $_REQUEST ? $_REQUEST["Width"] : $HTTP_GET_VARS["Width"];

/*$PAGE->set_url('/mod/stupla/prot/prot_exoverview.php', array('id' => $cm->id));
$PAGE->navbar->ignore_active();
echo $OUTPUT->header();*/
?>
<HTML>
<HEAD>
<meta http-equiv="expires" content="0">
<title>Protokoll - UserList, study2000: <?php echo $stupla->name ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="expires" content="0">
<style type="text/css">
<!--
body,td,p {
	font-size: 10pt;
	font-family: Arial
}

.username {
	font-weight: bold;
	color: #400000
}
//
-->
</style>
</head>

<body bgcolor="#FFFFFF">
	<form>
		<table width="100%">
			<tr>
				<td><b><?php print_string('exercise_overview', 'stupla') ?>, study 2000: </b> <?php echo $stupla->name ?>
<?php
if ( $s2maufgabe == -1 ) {
  ?></td>
			</tr>
		</table>
		<p>Keine Aufgaben eingebunden.</p><?php
} else {
  ?>
  </TD>
		<TD align=right><select name="GroupNr" size=1
			onChange="top.control.GroupNr = document.forms[0].GroupNr.selectedIndex; top.control.ExOverview();">
				<option <?php echo $groupnr == 0 ? " selected" : ""; ?>>normal
					sortiert</option><?php
/*  if ( array_key_exists("ExGroups", $adapt[$AppName]) )
    for ($i = 0; $i < count($adapt[$AppName]["ExGroups"]); $i++)
      echo "<option", ($groupnr == $i+1 ? " selected" : ""), ">", $adapt[$AppName]["ExGroups"][$i][0], "</option>";
*/
  ?></select></td>
		</tr>
		</table>

		<table border=0 cellpadding=4>
<?php
    // Assing exercises to block according the group.
    $indexblock = 7; // Hoffentlich unbenutzter Index bei Media[][][][?].
    $blockname = array();
    $nblock = 0;
    if ($groupnr == 0) { // Normal nach DeepNullTopics.
        $countex = 0;
        $oldtopic = 0;
        for ($i = 0; $i < count($s2name); $i++) {
            if ($s2deep[$i] == 0) {
                if ($countex > 0) { // Nur nehmen, wenn auch Aufgaben enthalten.
                    $blockname[$nblock++] = $s2name[$oldtopic];
                }
                $countex = 0;
                $oldtopic = $i;
            }
            if (array_key_exists($i, $s2media[$s2maufgabe])) {
                for ($k = 0; $k < count ($s2media[$s2maufgabe][$i]); $k++) {
                    $s2media[$s2maufgabe][$i][$k][$indexblock] = $nblock;
                    $countex++;
                }
            }
        }
        if ($countex > 0) {
            $blockname[$nblock++] = $s2name[$oldtopic];
        }
    } else {
        $group = $adapt[$AppName]['ExGroups'][$groupnr-1];
        // Use name.
        $nblock = count ( $group );
        for ($i = 0; $i < $nblock; $i++) {
            $blockname[$i] = $group[$i + 1][0];
        }
        // Read exercises and sort in.
        for ($i = 0; $i< count ( $s2name ); $i ++) {
                if (array_key_exists($i, $s2media[$s2maufgabe]) ) {
                for ($k = 0; $k < count($s2media[$s2maufgabe][$i]); $k++) {
                    // Get the block.
                    $bl = - 1;
                    for ($bl1 = 0; $bl1 < $nblock && $bl == - 1; $bl1++) {
                        for ($j = 1; $j < count($group[$bl1 + 1]) && $bl == -1; $j++) {
                            if ( preg_match($group[$bl1 + 1][$j], $s2media[$s2maufgabe][$i][$k][0]) ) {
                                $bl = $bl1;
                            }
                        }
                    }
                    $s2media[$s2maufgabe][$i][$k][$indexblock] = $bl;
                }
            }
        }
    }

    $errorstring = "";
    $sessions = stupla_prot_get_sessions($stupla);
    $i = 0;
    $l = 80;
    $b = 20;
    $ncol = floor(($width-100)/(57+36+($b+16)*$nblock));
    foreach ($sessions as $session) {
        if ( $i % $ncol == 0 ) {
            echo "<tr>";
        }
        stupla_prot_clear_hist();
        stupla_prot_read_hist($session, $stupla);
        echo '<td align="center"><span class=username>', $session->displayname,
            '<span><br/>', "\r\n", stupla_prot_diagram_hist($l, $b, 3, 0), '</td>';
        if ( ($i+1) % $ncol == 0 ) {
            echo "</tr>";
        }
        $i++;
    }

    for (; $i % $ncol != 0; $i++) {
        echo "<td><b></b></td>";
    }

  ?></table><?php
    echo $errorstring;
}
?>
</form>
</body>
</html>