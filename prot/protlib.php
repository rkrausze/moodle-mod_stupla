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
 * Library for protocol.
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Common data retrieval.
require_once("../../../config.php");
require_once("../lib.php");

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or ...
$n  = optional_param('n', optional_param('stupla', 0, PARAM_INT), PARAM_INT);  // ... stupla ID.

$sessionid = optional_param('session', 0, PARAM_INT);

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

$format = optional_param('format', 'showashtml', PARAM_ALPHA);
$sortinfo = optional_param('sortinfo', '', PARAM_TEXT);

require_login($course->id);
$coursecontext = @context_course::instance($course->id);

if ( ($stupla->flags & STUPLA_USESUBUSERS) ) {
    stupla_expand_subuserdata($stupla);
}

$sessionid = optional_param('sessionid', 0, PARAM_INT);

$from = optional_param('from', 0, PARAM_INT);
$till = optional_param('till', 0, PARAM_INT);

// Grouping information.
$groupsql = optional_param('groupSql', '', PARAM_TEXT); // The SQL-statement für the query.
$grouptext = optional_param('groupText', '', PARAM_TEXT); // Displayable Text.
$groupdata = optional_param('groupData', '', PARAM_TEXT); // Data for configuring.

// Helper functions.

function sec2str($s) {
    global $format;
    if ( $s*1 < 60 ) {
        return $format == 'showashtml' ? '<font color="#808080">'.$s.' sec.</font>' : $s.' sec';
    }
    $min = floor($s/60);
    $s = $s % 60;
    if ( $s < 10 ) {
        $s = '0'.$s;
    }
    if ( $min < 60 ) {
        return $min.':'.$s.' min.';
    }
    $h = floor($min/60);
    $min = $min % 60;
    if ( $min < 10 ) {
        $min = '0'.$min;
    }
    return $format == 'showashtml' ? '<b>'.$h.':'.$min.':'.$s.'h</b>' : $h.':'.$min.':'.$s.'h';
}

function stupla_get_studies() {
    global $stupla;
    $res = array();
    array_push($res, $stupla);
    return $res;
}

function stupla_prot_get_sessions($stupla, $add_condition = ' AND ses.archivetag IS NULL') {
    global $CFG, $USER, $DB, $from, $till, $groupsql, $grouptext;
    global $CFG, $USER, $DB, $coursecontext;
    $adduserlimit = ' AND u.id = -42'; // No one.
    if ( has_capability('mod/stupla:reviewallprotocols', $coursecontext) ) {
        $adduserlimit = ''; // All.
    } else if ( has_capability('mod/stupla:reviewmyprotocols', $coursecontext) ) {
        $adduserlimit = ' AND u.id = '.$USER->id; // Own only.
    }
    $sql = 'SELECT'.
        ' ses.*,'.
        ' u.id as userid,'.
        ' u.firstname,'.
        ' u.lastname,'.
        ' u.idnumber'.
        $groupsql.
        ' FROM '.$CFG->prefix.'user u'.
        ' JOIN '.$CFG->prefix.'stupla_session ses ON u.id = ses.userid'.
        ' WHERE ses.stupla = '.$stupla->id.
        $adduserlimit.
        ($from != 0 ? ' AND '.$from.' <= ses.starttime ' : '').
        ($till != 0 ? ' AND ses.starttime <= '.($till+86400) : '').
        $add_condition;
    if (!$sessions = $DB->get_records_sql($sql)) {
        $sessions = array(); // Tablelib will handle saying 'Nothing to display' for us.
    }
    if ( $grouptext != '' ) {
        prepare_grouping($sessions);
    }
    // Make displayname.
    foreach ($sessions as $id => $session) {
        stupla_displayname($stupla, $session, $session);
    }
    return $sessions;
}

function stupla_prot_get_session($sessionid, $stupla = '') {// If study == "", then for all.
    $sessions = stupla_prot_get_sessions($stupla, ' AND ses.id = '.$sessionid);
    foreach ($sessions as $session) {
        if ( $session->id == $sessionid ) {
            return $session;
        }
    }
    return null;
}

// For call backs.
function GetUserPath($study) {
    global $adapt;
    return $adapt[$study]["UserPath"];
}

// For Hist.
$textvisited = array();
$fbrequestcount = 0;
$totaltime = 0;
$logincount = 0;

function stupla_prot_clear_hist() {
    global $textvisited, $s2name, $s2mediatitle, $s2media, $fbrequestcount, $logincount, $totaltime, $s2maufgabe;
    // Topics.
    $textvisited = array();
    for ($i = 0; $i < count($s2name); $i++) {
        $textvisited[$i] = 0;
    }
    // Logins.
    $logincount = 0;
    // Medias.
    for ($m = 0; $m < count($s2mediatitle); $m++) {
        for ($i = 0; $i < count($s2name); $i++) {
            if ( array_key_exists($i, $s2media[$m]) && array_key_exists(0, $s2media[$m][$i]) && count($s2media[$m][$i]) ) {
                for ($j = 0; $j < count($s2media[$m][$i]); $j++) {
                    $s2media[$m][$i][$j][3] = -3;
                    if ( $m == $s2maufgabe ) {
                        unset($s2media[$m][$i][$j][4]);
                        unset($s2media[$m][$i][$j][5]);
                    }
                }
            }
        }
    }
    // FB's.
    $fbrequestcount = 0;
    // Total time.
    $totaltime = 0;
}

function stupla_prot_read_hist($session, $stupla, $firstglance = false) {
    global $DB, $adapt, $textvisited, $s2name, $s2mediatitle, $s2media, $fbrequestcount, $logincount, $totaltime, $errorstring,
        $s2maufgabe;
    $topictreshold = -1; // For firstglance.
    $topicmax = -1; // For firstglance.
    if ($records = $DB->get_records('stupla_action', array('stupla' => $stupla->id, 'session' => $session->id), 'starttime ASC')) {
        foreach ($records as $a) {
            if ($a->media == STUPLA_LOGIN) {
                $logincount ++;
                if ($firstglance) {
                    $topictreshold = $topicmax;
                }
            }             // else if ( $arr[1] == "#fbrequest" )
            // $fbrequestcount++;
            else if ($a->topic != -99 && $a->topic > $topictreshold) {
                if ($a->topic > $topicmax) {
                    $topicmax = $a->topic;
                }
                $totaltime += $a->duration;
                $mode = $a->media;
                if ($mode == 100) {
                    if ($a->topic >= 0) {
                        $textvisited[$a->topic] += $a->duration;
                    }
                } else if ($a->media == $s2maufgabe) {
                    if (array_key_exists($mode, $s2media) && array_key_exists($a->topic, $s2media[$mode]) &&
                            array_key_exists($a->nr, $s2media[$mode][$a->topic])) {
                        $s2media[$mode][$a->topic][$a->nr][3] = round($a->result, 2);
                        $s2media[$mode][$a->topic][$a->nr][4] = isset($a->data) ? $a->data : ''; // [4] the exercise filed entries
                        if (!isset($s2media[$mode][$a->topic][$a->nr][5])) { // [5] the time in exercise
                            $s2media[$mode][$a->topic][$a->nr][5] = 0;
                        }
                        $s2media[$mode][$a->topic][$a->nr][5] += $a->duration;
                    }
                } else if ($mode < 100) {
                    if (array_key_exists($mode, $s2media) && array_key_exists($a->topic, $s2media[$mode]) &&
                            array_key_exists($a->nr, $s2media[$mode][$a->topic])) {
                        if ($s2media[$mode][$a->topic][$a->nr][3] == -3) {
                            $s2media[$mode][$a->topic][$a->nr][3] = 0;
                        }
                        $s2media[$mode][$a->topic][$a->nr][3] += $a->duration;
                    } else {
                        $errorstring .= "<br/>ERROR: Action for Media[".$mode."][".$a->topic."][".$a->nr."] at session: ".
                                $session->id." in ".$stupla->id;
                    }
                }
            }
        }
    }
    return true;
}

/**
 * Summary of currently loaded session.
 * @return array with nLogins, nFbReqs, nTexts, tTexts, nMedia, exercises, time total
 */
function stupla_prot_summary_hist($ashtml) {
    global $adapt, $textvisited, $s2name, $s2mediatitle, $s2media, $fbrequestcount, $logincount, $totaltime, $errorstring,
            $s2maufgabe;
    $res = array($logincount, $fbrequestcount);
    $ntextvisited = 0;
    $timetextvisited = 0;
    for ($i = 0; $i < count($s2name); $i++) {
        if ( $textvisited[$i] != 0 ) {
            $ntextvisited++;
        }
        $timetextvisited += $textvisited[$i];
    }
    array_push($res, $ntextvisited, $ashtml ? sec2str($timetextvisited) : $timetextvisited);
    $nexok = 0; // OK.
    $nexwr = 0; // Wrong.
    $nexun = 0; // Unatempted.
    $nmedia = 0;
    for ($m = 0; $m < count($s2mediatitle); $m++) {
        for ($i = 0; $i < count($s2name); $i++) {
            if ( array_key_exists($i, $s2media[$m]) && array_key_exists(0, $s2media[$m][$i]) && count($s2media[$m][$i]) ) {
                for ($j = 0; $j < count($s2media[$m][$i]); $j++) {
                    $v = $s2media[$m][$i][$j][3];
                    if ( $m != $s2maufgabe ) {
                        $nmedia += ( $v != -3 ) ? 1 : 0;
                    } else if ( $v < -1 ) {
                        $nexun++;
                    } else if ( $v >= 99 ) {
                        $nexok++;
                    } else {
                        $nexwr++;
                    }
                }
            }
        }
    }
    array_push($res, $nmedia, $ashtml ? '<font color="#006600">'.$nexok.
         '</font>/<font color="#CC3300">'.$nexwr.
         '</font>/<font color="#3333CC">'.$nexun.'</font>' : $nexok.' / '.$nexwr.' / '.$nexun,
         $ashtml ? sec2str($totaltime) : $totaltime);
    return $res;
}

// ExerciseList.

$exentry = array();

function stupla_prot_make_exercise_list($stupla, $sessions) {
    global $exentry, $s2name, $s2maufgabe, $s2media;
    // Clear field.
    for ($topic = 0; $topic < count($s2name); $topic++) {
        if ( array_key_exists($topic, $s2media[$s2maufgabe]) ) {
            $exentry[$topic] = array();
            for ($i = 0; $i < count($s2media[$s2maufgabe][$topic]); $i++) {
                $exentry[$topic][$i] = array(array(), array());
            }
        }
    }
    // Read data.
    foreach ($sessions as $session) {
        stupla_prot_read_exercise_hist($session, $stupla);
    }
}

function stupla_prot_read_exercise_hist($session, $stupla) {
    global $DB, $adapt, $s2maufgabe, $s2media;
    if ($records = $DB->get_records('stupla_action', array('stupla' => $stupla->id, 'session' => $session->id), 'starttime ASC')) {
        foreach ($records as $r) {
            if ( $r->media == STUPLA_SYSTERROR ) {
                addexentryat($r->topic, $r->nr, 1, $arr[3], $arr[6]); // TODO was sind die restlichen Werte?
            } else {
                if ( array_key_exists($s2maufgabe, $s2media) && array_key_exists($r->topic, $s2media[$s2maufgabe]) &&
                        array_key_exists($r->nr, $s2media[$s2maufgabe][$r->topic]) ) {
                    addexentry($r->topic, $r->nr, 0, isset($r->data) ? $r->data : "");
                }
            }
        }
    }
    return true;
}

function addexentry($topic, $i, $kind, $s) {
    $arr = explode("|", $s);
    for ($j = 0; $j < count($arr); $j++) {
        if ( $arr[$j] != "" ) {
            addexentryat($topic, $i, $kind, $j, $arr[$j]);
        }
    }
}

function addexentryat($topic, $i, $kind, $j, $s) {
    global $exentry;
    if ( !array_key_exists($j, $exentry[$topic][$i][$kind]) ) {
        $exentry[$topic][$i][$kind][$j] = array();
    }
    $done = false;
    $k = 0;
    for ($k = 0; $k < count($exentry[$topic][$i][$kind][$j]); $k++) {
        if ( $exentry[$topic][$i][$kind][$j][$k][1] == $s ) {
            $exentry[$topic][$i][$kind][$j][$k][0]++;
            $done = true;
            break;
        }
    }
    if ( $done == false ) {
        $exentry[$topic][$i][$kind][$j][$k] = array(1, $s);
    }
}

/*
// ExerciseList f�r ExCont (jeder Nutzer nur einmal und mit seinem besten Wert)

function stupla_prot_make_exercise_listStrict(study, ExTopic, ExI)
{
  // Feld leeren
  ExEntry = new Array(new Array(), new Array());
  // Daten lesen
  for (var i = 0; i < User.length; i++)
    stupla_prot_read_exercise_histStrict(User[i], study, ''+M_AUFGABE+'.'+ExTopic+'.'+ExI);
}

function stupla_prot_read_exercise_histStrict(user, study, ExId)
{
  if ( !fs.FileExists(Server.MapPath(UserPath+user+"_"+study+"_prot.txt")) )
    return false;
  var f=fs.OpenTextFile(Server.MapPath(UserPath+user+"_"+study+"_prot.txt"), 1);
  var vMax = -3;
  var sMaxVal;
  while ( f.AtEndOfStream == false )
  {
    var arr = f.ReadLine().split(' ');
    if ( arr.length < 2 )
      continue;
    if ( arr[1] == "#systerror" && arr2 == ExId) {
      var arr2 = arr[2].split('.');
      addexentryatStrict(1, arr[3], arr[6]);
    }
    else if ( arr[1].charAt(0) != '#' )
    {
      var arr1 = arr[1].split('&');
      if ( arr1[0] == ExId )
        if ( Number(arr1[3]) > vMax )
        {
          vMax = Number(arr1[3]);
          sMaxVal = arr.length > 2 ? arr[2] : "";
        }
    }
  }
  f.Close();
  f=0;
  if ( vMax != -3 )
    addexentryStrict(0, sMaxVal);
  return true;
}

function addexentryStrict(kind, s)
{
  var arr = s.split('|');
  for (var j = 0; j < arr.length; j++)
    if ( arr[j] != "" )
      addexentryatStrict(kind, j, arr[j]);
}

function addexentryatStrict(kind, j, s)
{
  if ( !ExEntry[kind][j] )
    ExEntry[kind][j] = new Array();
  var done = false;
  var k = 0;
  for (k = 0; k < ExEntry[kind][j].length; k++)
    if ( ExEntry[kind][j][k][1] == s ) {
      ExEntry[kind][j][k][0]++;
      done = true;
      break;
    }
  if ( done == false )
     ExEntry[kind][j][k] = new Array(1, s);
}
*/
// Diagram graphic.

function stupla_prot_diagram_hist($l, $b, $ishort, $rightscale) {
    global $s2name, $nblock, $s2media, $s2maufgabe, $blockname, $indexblock;
    $s = '<table border="0" bgcolor="#DCDCDC"><tr><td><table border=0 cellpadding=2 width="100%"><tr>';
    // Scale.
    $s .= '<td align="right" valign="bottom" style="white-space:nowrap" width="39">'.
        '<NOBR><img src="col/ps_100.gif" height="11" width="30"><img src="col/ps_t.gif" height="11" width="5"></NOBR><br>'.
        '<img src="col/ps_line.gif" width="5" height="'.($l/4-11).'"><br>'.
        '<img src="col/ps_75.gif" height="11" width="26"><img src="col/ps_m.gif" height="11" width="5"><br>'.
        '<img src="col/ps_line.gif" width="5" height="'.($l/4-11).'"><br>'.
        '<img src="col/ps_50.gif" height="11" width="26"><img src="col/ps_m.gif" height="11" width="5"><br>'.
        '<img src="col/ps_line.gif" width="5" height="'.($l/4-11).'"><br>'.
        '<img src="col/ps_25.gif" height="11" width="26"><img src="col/ps_m.gif" height="11" width="5"><br>'.
        '<img src="col/ps_line.gif" width="5" height="'.($l/4-11).'"><br>'.
        '<img src="col/ps_b.gif" width="5" height="6">'.
        '</td>';
    $blockvals = array();
    for ($i = 0; $i < $nblock; $i++) {
        $blockvals[$i] = array(0, 0, 0);
    }
    for ($i = 0; $i < count($s2name); $i ++) {
        if ( array_key_exists($i, $s2media[$s2maufgabe]) ) {
            for ($k = 0; $k < count($s2media[$s2maufgabe][$i]); $k++) {
                $bl = $s2media[$s2maufgabe][$i][$k][$indexblock];
                if ($bl == - 1) {
                    continue;
                }
                if ($s2media [$s2maufgabe] [$i] [$k] [3] == - 3) {
                    $blockvals [$bl] [2] ++;
                } else if ($s2media [$s2maufgabe] [$i] [$k] [3] >= 98) {
                    $blockvals [$bl] [0] ++;
                } else {
                    $blockvals [$bl] [1] ++;
                }
            }
        }
    }
    $sumvals = array(0, 0, 0);
    // Print blocks.
    for ($i = 0; $i < $nblock; $i ++) {
        $s .= '<td width="'.(100 / ($nblock + 1)).'%" valign="bottom">';
        $s .= prot_pillar($blockvals[$i], $l, $b);
        $s .= '</td>';
        $sumvals[0] += $blockvals[$i][0];
        $sumvals[1] += $blockvals[$i][1];
        $sumvals[2] += $blockvals[$i][2];
    }
    $s .= '<td width="'.(100/($nblock+1)).'%" valign="bottom">';
    $s .= prot_pillar($sumvals, $l, $b);
    // Scale.
    $s .= '</td>';
    if ( $rightscale ) {
        $s .=
            '<td align="left" valign="bottom" style="white-space:nowrap">'.
            '<img src="col/ps_t.gif" height="11" width="5"><img src="col/ps_100.gif" height="11" width="30"><br>'.
            '<img src="col/ps_line.gif" width="5" height="'.($l/4-11).'"><br>'.
            '<img src="col/ps_m.gif" height="11" width="5"><img src="col/ps_75.gif" height="11" width="26"><br>'.
            '<img src="col/ps_line.gif" width="5" height="'.($l/4-11).'"><br>'.
            '<img src="col/ps_m.gif" height="11" width="5"><img src="col/ps_50.gif" height="11" width="26"><br>'.
            '<img src="col/ps_line.gif" width="5" height="'.($l/4-11).'"><br>'.
            '<img src="col/ps_m.gif" height="11" width="5"><img src="col/ps_25.gif" height="11" width="26"><br>'.
            '<img src="col/ps_line.gif" width="5" height="'.($l/4-11).'"><br>'.
            '<img src="col/ps_b.gif" width="5" height="6">'.
            '</td>';
    }
    $s .= '</tr><tr><td>&nbsp;</td>';
    for ($i = 0; $i < $nblock; $i++) {
        $s .= '<td width="'.(100/($nblock+1)).'%" valign="top" bgcolor="#FFFF80" title="'.$blockname[$i].'">'.
            stupla_prot_short($blockname[$i], $ishort).'</td>';
    }
    $s .= '<td valign="top" bgcolor="#FFFF80"><font face="Arial"><strong>ges.</strong></font></td>'.
        ($rightscale ? '<td>&nbsp;</td>' : '').'</tr></table></td></tr></table>';
/*    '<table border="0" cellpadding="3"><tr>\r\n',
    '<td><img src="'col/pc_gruen.gif" width="20" height="20"></td>\r\n',
    '<td><font face="Arial">Richtig gel&ouml;ste Aufgaben.</font></td>\r\n',
    '</tr><tr>\r\n',
    '<td><img src="'col/pc_rot.gif" width="20" height="20"></td>\r\n',
    '<td><font face="Arial">Falsch gel&ouml;ste Aufgaben.</font></td>\r\n',
    '</tr><tr>\r\n',
    '<td><img src="'col/pc_blau.gif" width="20" height="20"></td>\r\n',
    '<td><font face="Arial">Noch nicht bearbeitete Aufgaben.</font></td>\r\n',
    '</tr></table></body></HTML>'*/;
    return $s;
}

function prot_pillar($vals, $l, $b) {
    $s = '';
    $first = 1;
    $sumall = 0;
    for ($i = 0; $i < count ( $vals ); $i ++) {
        $sumall += $vals [$i];
    }
    $colorname = array("gruen", "rot", "blau");
    $colorcomment = array("richtig gelöst", "falsch gelöst", "unbearbeitet");
    $pix = 0;
    $sum = 0;
    $s .= '<table border="0" cellspacing="0" width="' . ($b + 8) . '"><tr><td align="left" valign="top" width="40">';
    for ($i = count ( $vals ) - 1; $i >= 0; $i --) {
        if ($vals [$i] != 0) {
            $sum += $vals [$i];
            $sumpix = round ( $sum * $l / $sumall );
            $s .= (($first == 1) ? "" : "<br>").'<img src="col/pc_'.$colorname[$i].'.gif" width="'.$b.'" height="'.($sumpix - $pix).
                    '" alt="'.$vals[$i].(($vals[$i] == 1) ? ' Aufgabe ' : ' Aufgaben ').$colorcomment[$i].'">';
            $first = 0;
            $pix = $sumpix;
        }
    }
    return $s.'</td></tr></table>';
}

function stupla_prot_short($s, $ishort) {
    return strlen($s) < $ishort+1 ? $s : substr($s, 0, $ishort)."..";
}

function stupla_prot_collect_ex_status($indexcollect) {
    global $s2name, $s2media, $s2maufgabe;
    for ($i = 0; $i < count($s2name); $i ++) {
        if (array_key_exists($i, $s2media[$s2maufgabe])) {
            for ($k = 0; $k < count($s2media[$s2maufgabe][$i]); $k ++) {
                if ($s2media[$s2maufgabe][$i][$k][3] == -3) {
                    $s2media[$s2maufgabe][$i][$k][$indexcollect][2] ++;
                } else if ($s2media[$s2maufgabe][$i][$k][3] >= 98) {
                    $s2media[$s2maufgabe][$i][$k][$indexcollect][0] ++;
                } else {
                    $s2media[$s2maufgabe][$i][$k][$indexcollect][1] ++;
                }
            }
        }
    }
}

function stupla_prot_ex_state_bar($vals, $l, $h) {
    $s = '';
    $sumall = 0;
    for ($i = 0; $i < count($vals); $i++) {
        $sumall += $vals[$i];
    }
    $colorname = array("gruen", "rot", "blau");
    $coloraction = array(get_string('solved_correct', 'stupla'), get_string('solved_wrong', 'stupla'),
            get_string('unattempted', 'stupla'));
    $pix = 0;
    $sum = 0;
    $s .= '<nobr>';
    for ($i = 0; $i < count($vals); $i++) {
        if ( $vals[$i] != 0 ) {
            $sum += $vals[$i];
            $sumpix = round($sum*$l/$sumall);
            $comment = get_string(($vals[$i] == 1) ? 'one_ex_att' : 'mult_ex_att', 'stupla',
                    array('count' => $vals[$i], 'action' => $coloraction[$i]));
            $s .= '<img src="col/pc_'.$colorname[$i].'.gif" height="'.$h.'" width="'.($sumpix-$pix).'" alt="'.$comment.
                    '" title="'.$comment.'">';
            $pix = $sumpix;
        }
    }
    return $s.'</nobr>';
}

define("M_TOCMEDIA", 105);
define("M_MEDIAOPEN", 200);

// Aktuelle Medienliste generieren, (rekursiv, da von allgemein zu speziell)

function getmedia($mode, $topic)
{
    global $s2intocmedia, $s2media;
    $m = $mode % M_MEDIAOPEN;
    if ( $m >= M_TOCMEDIA ) {
        return $s2intocmedia[$m-M_TOCMEDIA];
    } else {
        return $s2media[$m][$topic];
    }
}

function stupla_prot_get_media($type, $topic, $i, $what) {
    global $errorstring;
    $v = getmedia($type, $topic);
    if ( !empty($v) && array_key_exists($i, $v) && array_key_exists($what, $v[$i]) ) {
        return $v[$i][$what];
    }

    $errorstring .= "Error: Media[".$type."][".$topic."][".$i."][".$what."] does not exist.<br/>";
    return "";
}

function stupla_prot_pure_filename($s) {
    $i = strrpos($s, "/");
    $s = ($i === false) ? $s : substr($s, $i+1);
    $i = strrpos($s, ".");
    return ($i === false) ? $s : substr($s, 0, $i);
}

function enrich_nonegame1(&$records) {
    $records = array_values($records);

    for ($i = 0; $i < count($records)-1; $i++) {
        if ( substr($records[$i]->data, 0, 11) == '[NONEGAME1]'
            && substr($records[$i+1]->data, 0, 11) == '[NONEGAME1]'
            && (strpos($records[$i+1]->data, 'showed') !== false || strpos($records[$i+1]->data, 'overview') )
            && ($records[$i]->starttime + $records[$i]->duration < $records[$i+1]->starttime - 5
                || mc_part($records[$i]) != mc_part($records[$i+1]) ) ) {
            $datanew = str_replace('showed', '****', $records[$i+1]->data);
            $datanew = str_replace('overview', '****', $datanew);
            $mcp1 = mc_part($records[$i]);
            $mcp2 = mc_part($records[$i+1]);
            $is_confirm = ($mcp1 != $mcp2);
            $datanew = str_replace('****', $is_confirm ? '*confirm' : '*hint', $datanew);
            $result = 0;
            // Decide, whether confirm belongs to first ord second $ifine.
            if ( $is_confirm ) {
                if ( preg_match('/^\[NONEGAME1\]\[(\d+),([\d\-]+)\]/', $records[$i]->data, $b1) &&
                        preg_match('/^\[NONEGAME1\]\[(\d+),([\d\-]+)\]/', $records[$i+1]->data, $b2)) {
                    if ( $b1[2] != $b2[2] ) {
                        $ifine = $b2[2]+0; // The iFine of second.
                        if ( $b2[1] == 1 ) {
                            $ifine -= 2;
                        }
                        $c = explode('|', str_replace(' ', '', str_replace('[mc]', '', $mcp2)));
                        if ( ($ifine > 0 || $b2[2] == -1) && $c[$ifine*2] == 0 && $c[$ifine*2+1] == 0 ) {
                            // Belongs to first, so modify the iFine in $datanew ...
                            $datanew = str_replace('[NONEGAME1]['.$b2[1].','.$b2[2].']', '[NONEGAME1]['.$b1[1].','.$b1[2].']',
                                    $datanew);
                            // ... and must have been 100%.
                            if ( $b2[2] != -1 ) {
                                $result = 100;
                            } else { // Last promt in task, so could be "consumed all trials"".
                                $iraw = $b1[1]-1;
                                $ifine = $b1[2]+0;
                                // Correction for first task.
                                if ( $iraw == 0 ) {
                                    $ifine -= 2;
                                }
                                $correctsolutions = array( // The lst entries missing.
                                    '[mc]3 | [mc]1 | [mc]2 | [mc]4 | [mc]3 | [mc]2 | [mc]1 | [mc]5 | [mc]1 | [mc]3',
                                    '[mc]2 | [mc]3 | [mc]2 | [mc]1 | [mc]3 | [mc]4 | [mc]2 | [mc]5 | [mc]3 | [mc]2',
                                    '[mc]1 | [mc]3 | [mc]2 | [mc]2 | [mc]1 | [mc]1 | [mc]3 | [mc]5 | [mc]2 | [mc]4',
                                    '[mc]3 | [mc]4 | [mc]2 | [mc]3 | [mc]2 | [mc]2 | [mc]0 | [mc]0 | [mc]0 | [mc]0');
                                $ccorr = explode('|', str_replace(' ', '', str_replace('[mc]', '', $correctsolutions[$iraw])));
                                $result = ($c[$ifine*2] == $ccorr[$ifine*2] ? 50 : 0) +
                                          ($c[$ifine*2+1] == $ccorr[$ifine*2+1] ? 50 : 0);
                            }
                        }
                    }
                }
            }
            $reconst = new stdClass;
            $reconst->starttime = $records[$i]->starttime + $records[$i]->duration;
            $reconst->duration = $records[$i+1]->starttime - $reconst->starttime;
            $reconst->stupla = $records[$i]->stupla;
            $reconst->session = $records[$i]->session;
            $reconst->media = $records[$i]->media;
            $reconst->topic = $records[$i]->topic;
            $reconst->nr = $records[$i]->nr;
            $reconst->data = $datanew;
            $reconst->result = $result;
            array_splice($records, $i+1, 0, array($reconst));
            $i++;
        }
    }
}

function mc_part($entry) {
    $data1 = $entry->data;
    $j = strpos($data1, '[mc]');
    if ( $j !== false ) {
        $data1 = substr($data1, $j);
    }
    return $data1;
}
