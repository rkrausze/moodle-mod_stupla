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
 * This page is the protocol page for exercise lists.
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');
require_once('data_load.php');

?>
<HTML>
<HEAD>
<meta http-equiv="expires" content="0">
<title>Protokoll - ExerciseList, study2000: <?php echo $stupla->name ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="expires" content="0">
<style type="text/css">
<!--
td,p {
	font-size: 10pt;
	font-family: Arial;
	font-weight: bold
}

body {
	font-size: 10pt;
	font-family: Arial;
}

.systerror {
	color: #2E8B57
}
//
-->
</style>
</head>

<body bgcolor="#FFFFFF">
<?php
$errorstring = "";
stupla_prot_make_exercise_list($stupla, stupla_prot_get_sessions($stupla));

function entrysort($a, $b) {
    return $b[0]-$a[0];
}

for ($topic = 0; $topic < count($s2name); $topic ++) {
    if (array_key_exists($topic, $s2media[$s2maufgabe])) {
        for ($i = 0; $i < count($s2media[$s2maufgabe][$topic]); $i ++) {
            echo '<table border="0" width="100%" bgcolor="#CCCCCC"><tr><td><b>', $s2media[$s2maufgabe][$topic][$i][0],
                "</b></td></tr></table>";
            if (count($exentry[$topic][$i][0]) > 0 || count($exentry[$topic][$i][1]) > 0) {
                for ($j = 0; $j < count($exentry[$topic][$i][0]) || $j < count($exentry[$topic][$i][1]); $j ++) {
                    if (array_key_exists($j, $exentry[$topic][$i][0]) || array_key_exists($j, $exentry[$topic][$i][1])) {
                        echo '<b>', get_string('entry', 'stupla'), ' ', $j, ':</b><br/>';
                        if (array_key_exists($j, $exentry[$topic][$i][0])) {
                            echo '&nbsp; ', get_string('solve', 'stupla'), ':<br/>';
                            uksort($exentry[$topic][$i][0][$j], "entrysort");
                            for ($k = 0; $k < count($exentry[$topic][$i][0][$j]); $k ++) {
                                echo "&nbsp; &nbsp; (", $exentry[$topic][$i][0][$j][$k][0], "x) ",
                                    str_replace("_", " ", $exentry[$topic][$i][0][$j][$k][1]), '<br/>';
                            }
                        }
                        if (array_key_exists($j, $exentry[$topic][$i][1])) {
                            echo '<span class=systerror>&nbsp; ', get_string('systerror', 'stupla'), ':<br/>';
                            uksort($exentry[$topic][$i][1][$j], "entrysort");
                            for ($k = 0; $k < count($exentry[$topic][$i][1][$j]); $k ++) {
                                echo "&nbsp; &nbsp; (", $exentry[$topic][$i][1][$j][$k][0], "x) ",
                                    str_replace("_", " ", $exentry[$topic][$i][1][$j][$k][1]), '<br/>';
                            }
                            echo "</span>";
                        }
                    }
                }
            } else {
                echo "-<br/>";
            }
        }
    }
}

echo $errorstring;
?>
Done.
</body>
</html>