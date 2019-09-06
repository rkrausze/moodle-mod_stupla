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
 * This page is the protocol page providing a list of the actions of a session.
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');
require_once('data_load.php');

$session = stupla_prot_get_session($sessionid, $stupla);
$cursessionname = $session->displayname;

$PAGE->set_url('/mod/stupla/prot/prot_hist.php', array('id' => $cm->id));
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('frametop');
$PAGE->blocks->show_only_fake_blocks();
echo stupla_safe_header();
?>
<style type="text/css"><!--
  td.login { font-weight:bold; color:#333333 }
  td.prot { font-weight:bold; color:#8A2BE2 }
  td.sheet { font-weight:bold; color:#7CFC00 }
  td.plan { font-weight:bold; color:#C000C0 }
  td.exercise { font-weight:bold; color:#228B22 }
  td.medium { font-weight:bold; color:#FF4500 }
  td.text { font-weight:bold; color:#000000 }
  td.textaction { font-weight:bold; color:#000000 }
  td.fb { font-weight:bold; color:#8B4513 }
  td.systerror { font-weight:bold; color:#2E8B57 }
  td.material { font-weight:bold; color:#FF008B }
  .exgood { font-weight:bold; color:#008000 }
  .exbad { font-weight:bold; color:#C00000 }
  .exshow { font-weight:bold; color:#404040 }
  a:active { font-weight:bold; color:#E00000; background-color:#8080F0; }
//--></style>

<table border="1">
 <tr>
  <td>
   t<sub>delta</sub> (in s)
  </td>
  <td>
   Aktion
  </td>
  <td>
   Bemerkung
  </td>
 </tr>
<?php

$errorstring = "";

function abbridge($s, $n) {
    return ( !empty($s) && strlen($s) > $n ) ? '<span title="'.$s.'">'.substr($s, 0, $n-3).'...</span>' : $s;
}

function maketopicname($topic, $nr = -1) {
    global $s2name;
    return "<b>".abbridge($s2name[$topic], 40)."</b> [".$topic.($nr != -1 && $nr != -99 ? ", ".$nr : "")."]";
}

function makemedianame($type, $topic, $nr = -1) {
    global $s2name;
    $sfile = stupla_prot_get_media($type, $topic, $nr, 0);
    $j = strrpos($sfile, "/");
    if ( $j !== false ) {
        $sfile = substr($sfile, $j+1);
    }
    if ( strlen($sfile) > 20 ) {
        $sfile = '<span title="'.$sfile.'">...'.substr($sfile, strlen($sfile)-18).'</span>';
    }
    return "<b>".abbridge($s2name[$topic], 20).", ".abbridge(stupla_prot_get_media($type, $topic, $nr, 1), 30).
        '</b> ['.$topic.($nr != -1 && $nr != -99 ? ", ".$nr : "")."] <i>(".$sfile.")</i>";
}

$time = 0;
if ($records = $DB->get_records('stupla_action', array('stupla' => $stupla->id, 'session' => $sessionid), 'starttime ASC')) {
    enrich_nonegame1($records);
    foreach ($records as $r) {
        $date1 = strftime("%a %d %b %y, %H:%M:%S", $r->starttime).' dur:'.$r->duration;
        if ( $r->media == STUPLA_LOGIN ) {
            echo '<tr style="background-color:#CCCCCC"><td align="right" title="'.$date1.'">0</td><td class="login">Login</td><td><b>'.
                $date1.'</b> '.$r->data."</td></tr>\r\n";
        } else {
            echo '<tr><td align="right" title="', $date1, '">', $r->starttime-$time, '</td>';
            if ( $r->media == STUPLA_GENERIC ) {
                if ( substr($r->data, 0, 5) == '#prot' ) {
                    echo '<td class="prot">Protokoll</td><td>'.$r->data;
                } else {
                    echo '<td class="material">Material</td><td>'.$r->data;
                }
            } else if ( $r->media == STUPLA_SHEET ) {
                $arr = explode(' ', $r->data);
                echo '<td class="sheet">Sammelm.</td><td>'.$r->data;
            } else if ( $r->media == STUPLA_PLAN ) {
                $arr = explode(' ', $r->data);
                echo '<td class="plan">Lernplan.</td><td>'.$r->data;
            }
    /*      else if ( $arr[1] == "#material" )
            echo '<td class="material">Material</td><td>'.$arrRest2;*/
            else if ( $r->media == STUPLA_MARK ) {
                $arr = explode(' ', $r->data);
                echo '<td class="textaction">Markieren</td><td>'.maketopicname($arr[0]).', Worte: <b>'.$arr[1].' &ndash; '.$arr[2].
                        '</b>, Farbe: <b>'.$arr[3].'</b>';
            } else if ( $r->media == STUPLA_NOTE ) {
                $arr = explode(' ', $r->data);
                $s = '';
                $i = 0;
                if ( $arr[$i] == 'ERASE' ) {
                    $s = "<b>L&ouml;schen</b> ";
                    $i++;
                }
                if ( $arr[$i] == "EDIT" ) {
                    $s = "<b>Bearbeiten</b> ";
                    $i++;
                }
                echo '<td class="textaction">Notiz</td><td>'.$s.maketopicname($arr[$i]).', Positions-Wort: <b>'.$arr[$i+1].
                        '</b>, Inhalt: <b>&quot;'.join(" ", array_slice($arr, $i+2)).'&quot;</b>';
            }
    /*      else if ( $arr[1] == "#fbrequest" )
            echo '<td class="fb">Fragebogen</td><td>Anforderung Fragebogen Nr. <b>'.$arr[2].'</b>';
          else if ( $arr[1] == "#systerror" )
          {
            $arr2 = explode(".", $arr[2]);
            echo '<td class="systerror">Syst. Fehler</td><td>'.makemedianame($arr2[0], $arr2[1], $arr2[2])." ".join(" ", array_slice($arr, 3));
          }*/
            else {// normale Aktion
                $mode = "Text";
                $s = "";
                if ( $r->result ) {
                    $s = $r->result.' ';
                }
                if ( $r->data ) {
                    $s .= $r->data;
                }
                if ( $r->media < 100 ) { // Medium.
                    $mode = $s2mediatitleshort[$r->media];
                    if ( $r->media != $s2maufgabe ) {
                        echo '<td class="medium">'.$mode.'</td><td>'.makemedianame($r->media, $r->topic, $r->nr).' '.$s;
                    } else {
                        $s1 = "";
                        if ( isset($r->result) ) {
                            $s = round($r->result, 2);
                            if ( isset($r->data) ) {
                                $s1 = $r->data;
                            }
                        }
                        if ( $s == -1 ) {
                            echo '<td class="exercise">'.$mode.'</td><td>'.makemedianame($s2maufgabe, $r->topic, $r->nr).' <span class="exshow">Lösung angezeigt</span> <a href="javascript:top.control.ShowHistExercise(\''.stupla_prot_get_media($s2maufgabe, $r->topic, $r->nr, 0).'\',\'\')">=></a> '.str_replace("_", " ", str_replace("|", " | ", $s1));
                        } else {
                            echo '<td class="exercise">'.$mode.'</td><td>'.makemedianame($s2maufgabe, $r->topic, $r->nr).' <span class="ex'.($s > 98 ? "good" : "bad").'">'.$s.'%</span> <a href="javascript:top.control.ShowHistExercise(\''.stupla_prot_get_media($s2maufgabe, $r->topic, $r->nr, 0).'\',\''.str_replace("%", "###", urlencode($s1)).'\')">=></a> '.str_replace("_", " ", str_replace("|", " | ", $s1));
                        }
                    }
                } else if ( $r->media >= M_TOCMEDIA && $r->media < STUPLA_SYSTERROR ) { // InTOC.
                    $mode = $s2intocmediatitleshort[$r->media-M_TOCMEDIA];
                    echo '<td class="medium">'.$mode.'</td><td>'.makemedianame($r->media, $r->topic, $r->nr).' '.$s;
                } else { // Text.
                    echo '<td class="Text">'.$mode.'</td><td>'.maketopicname($r->topic).' '.$s;
                }
            }
            echo "</td></tr>\r\n";
        }
        $time = $r->starttime;
    }
}

if ( $time == 0 ) {
    echo '<tr><td colspan="3">Keine Datei vorhanden.</td></tr>';
}

?>
</table>
<?php
if ( $errorstring != "" ) {
    echo '<div class="errorbox">', $errorstring, '</div>';
}

echo stupla_safe_footer();
