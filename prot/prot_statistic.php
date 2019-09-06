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
 * This page is the protocol page with summarising stytistics.
 *
 * @package    mod
 * @subpackage stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');
require_once('data_load.php');
require_once('prot_util.php');

define("STUPLA_DS_TEXTS", 1);
define("STUPLA_DS_MEDIA", 2);
define("STUPLA_DS_EX", 4);
define("STUPLA_DS_EX_SPECIAL", 8);
define("STUPLA_DS_USE_EX_NAMES", 16);
define("STUPLA_DS_FIRSTGLANCE", 32);

$PAGE->set_url('/mod/stupla/prot/prot_statistic.php', array('id' => $cm->id));
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('frametop');
$PAGE->blocks->show_only_fake_blocks();

$ds = 255;

if ( isset($_SESSION['stupla_ds']) ) {
    $ds = $_SESSION['stupla_ds'];
} else {
    $_SESSION['stupla_ds'] = $ds;
}

if ( optional_param('setDS', '', PARAM_ALPHA) == 'true' ) {
    $ds = 0;
    $ds |= optional_param('dsTexts', 0, PARAM_INT);
    $ds |= optional_param('dsMedia', 0, PARAM_INT);
    $ds |= optional_param('dsEx', 0, PARAM_INT);
    $ds |= optional_param('dsExSpecial', 0, PARAM_INT);
    $ds |= optional_param('dsUseExNames', 0, PARAM_INT);
    $ds |= optional_param('dsFirstGlance', 0, PARAM_INT);
    $_SESSION['stupla_ds'] = $ds;
}

// generate basic table data

$table = new html_table();

$table->name = get_string('statistic', 'stupla');
$table->head = array(
    get_string('code', 'stupla'),
    get_string('nlogin', 'stupla'),
    get_string('time', 'stupla')
);
$table->align = array('left', 'right', 'right');
$table->data = array();
$table->ordertype = array('', '', '');

// subuserdata
if ( $stupla->flags & STUPLA_USESUBUSERS ) {
    stupla_expand_subuserdata($stupla);
    foreach ($stupla->sudata as $field) {
        array_push($table->head, $field['name']);
        array_push($table->align, 'left');
        array_push($table->ordertype, '');
    }
}

// header: texts
if ( $ds & STUPLA_DS_TEXTS ) {
    for ($i = 0; $i < count($s2name); $i++) {
        array_push($table->head, 'tText'.($i+1));
        array_push($table->align, 'right');
        array_push($table->ordertype, '');
    }
}

// header: media
if ( $ds & STUPLA_DS_MEDIA ) {
    for ($topic = 0; $topic < count($s2name); $topic++) {
        // if ( isset($s2media[$topic]) )
        for ($type = 0; $type < count($s2mediatitle); $type++) {
            if ( isset($s2media[$type][$topic]) && $type != $s2maufgabe ) {
                for ($i = 0; $i < count($s2media[$type][$topic]); $i++) {
                    $medid = ($topic+1).(count($s2media[$type][$topic]) == 1 ? '' : '_'.($i+1));
                    $medname = ($ds & STUPLA_DS_USE_EX_NAMES) ? stupla_prot_pure_filename(stupla_prot_get_media($type, $topic, $i, 0)) : $s2mediatitleshort[$type].$medid;
                    array_push($table->head, 't'.$medname);
                    array_push($table->align, 'right');
                    array_push($table->ordertype, '');
                }
            }
        }
    }
}

// header: exercises
if ( $ds & (STUPLA_DS_EX | STUPLA_DS_EX_SPECIAL) ) {
    $specialex = array();
    $specialex_addheader = array(
        'NONEGAME0' => 'specialex_addheader_nonegame0',
        'NONEGAME1' => 'specialex_addheader_nonegame1',
        'NONEGAME2' => 'specialex_addheader_nonegame2',
        'NONEGAME3' => 'specialex_addheader_nonegame3'
    );
    $specialex_ = array(
        'NONEGAME0' => 'specialex_adddata_nonegame0',
        'NONEGAME1' => 'specialex_adddata_nonegame1',
        'NONEGAME2' => 'specialex_adddata_nonegame2',
        'NONEGAME3' => 'specialex_adddata_nonegame3'
    );
    $errorstring = "";
    stupla_prot_make_exercise_list($stupla, stupla_prot_get_sessions($stupla));
    for ($topic = 0; $topic < count($s2name); $topic++) {
        if ( array_key_exists($topic, $s2media[$s2maufgabe]) ) {
            for ($i = 0; $i < count($s2media[$s2maufgabe][$topic]); $i++) {
                $exid = ($topic+1).(count($s2media[$s2maufgabe][$topic]) == 1 ? '' : '_'.($i+1));
                $exname = ($ds & STUPLA_DS_USE_EX_NAMES) ? stupla_prot_pure_filename(stupla_prot_get_media($s2maufgabe, $topic, $i, 0)) : "ex".$exid;
                // entries
                // filename: $s2media[$s2maufgabe][$topic][$i][0]
                // special exercise types
                if ( isset($exentry[$topic][$i][0][0][0][1]) && false !== strpos($exentry[$topic][$i][0][0][0][1], '[NONEGAME1]') && ($ds & STUPLA_DS_EX_SPECIAL) ) {
                    $specialex[$exid] = 'NONEGAME1'; // EF-Version of Essay-Launcher (WPAL)
                    $specialex_addheader['NONEGAME1']($table, $exname);
                } else if ( isset($exentry[$topic][$i][0][0][0][1]) && false !== strpos($exentry[$topic][$i][0][0][0][1], '[NONEGAME2]') && ($ds & STUPLA_DS_EX_SPECIAL) ) {
                    $specialex[$exid] = 'NONEGAME2'; // Essay Launcher (WPAL)
                    $specialex_addheader['NONEGAME2']($table, $exname);
                } else if ( isset($exentry[$topic][$i][0][0][0][1]) && false !== strpos($exentry[$topic][$i][0][0][0][1], '[NONEGAME0]') && ($ds & STUPLA_DS_EX_SPECIAL) ) {
                    $specialex[$exid] = 'NONEGAME0'; // IW (WPAL)
                    $specialex_addheader['NONEGAME0']($table, $exname);
                } else if ( isset($exentry[$topic][$i][0][0][0][1]) && false !== strpos($exentry[$topic][$i][0][0][0][1], '[NONEGAME3]') && ($ds & STUPLA_DS_EX_SPECIAL) ) {
                    $specialex[$exid] = 'NONEGAME3'; // READING (WPAL)
                    $specialex_addheader['NONEGAME3']($table, $exname);
                } else if ( $ds & STUPLA_DS_EX ) {
                    // time
                    addheader($table, 't_'.$exname, 'right', 'n');
                    // grade
                    addheader($table, 'grade_'.$exname, 'right', 'n');
                    // percent
                    addheader($table, 'pct_'.$exname, 'right', 'n');
                    for ($j = 0; $j < count($exentry[$topic][$i][0]) || $j < count($exentry[$topic][$i][1]); $j++) {
                        // entries
                        addheader($table, 'ent_'.$exname.'_'.($j+1));
                    }
                }
            }
        }
    }
}

// fill with data
$sessions = stupla_prot_get_sessions($stupla);

$cursessionname = '';

foreach ($sessions as $session) {
    stupla_prot_clear_hist();
    stupla_prot_read_hist($session, $stupla, $ds & STUPLA_DS_FIRSTGLANCE);
    $cursessionname = $session->displayname;
    $d = array($format == "showashtml"
            ? '<a href="javascript:top.control.SelectHist(\''.$session->id.'\')" target="control">'.$session->displayname.'</a>'
            : $session->displayname,
            $logincount,
            $totaltime);
    // subuserdata
    if ( $stupla->flags & STUPLA_USESUBUSERS ) {
        $subdat = isset($session->data) ? explode(STUPLA_X01, $session->data) : array();
        for ($i = 0; $i < count($stupla->sudata); $i++) {
            array_push($d, isset($subdat[$i]) ? $subdat[$i] : '');
        }
    }

    // time text visited
    if ( $ds & STUPLA_DS_TEXTS ) {
        for ($i = 0; $i < count($s2name); $i++) {
            array_push($d, $textvisited[$i]);
        }
    }

    // time media
    if ( $ds & STUPLA_DS_MEDIA ) {
        for ($topic = 0; $topic < count($s2name); $topic++) {
            for ($type = 0; $type < count($s2mediatitle); $type++) {
                if ( isset($s2media[$type][$topic]) && $type != $s2maufgabe ) {
                    for ($i = 0; $i < count($s2media[$type][$topic]); $i++) {
                        array_push($d, $s2media[$type][$topic][$i][3] != -3 ? $s2media[$type][$topic][$i][3] : '');
                    }
                }
            }
        }
    }
    // data: exercises
    if ( $ds & (STUPLA_DS_EX | STUPLA_DS_EX_SPECIAL) ) {
        for ($topic = 0; $topic < count($s2name); $topic++) {
            if ( array_key_exists($topic, $s2media[$s2maufgabe]) ) {
                for ($i = 0; $i < count($s2media[$s2maufgabe][$topic]); $i++) {
                    $exid = ($topic+1).(count($s2media[$s2maufgabe][$topic]) == 1 ? '' : '_'.($i+1));
                    if ( isset($specialex[$exid]) ) {
                        if ( $ds & STUPLA_DS_EX_SPECIAL ) {
                            $specialex_[$specialex[$exid]]($session, $stupla, $d);
                        }
                    } else if ( $ds & STUPLA_DS_EX ) {
                        $mediaex = $s2media[$s2maufgabe][$topic][$i];
                        // time
                        array_push($d, isset($mediaex[5]) ? $mediaex[5]: '');
                        // grade
                        array_push($d, isset($mediaex[3]) && $mediaex[3] != -3 ? ($mediaex[3] >= 98 ? 1 : 0) : '');
                        // percent
                        array_push($d, isset($mediaex[3]) && $mediaex[3] != -3 ? $mediaex[3] : '');
                        // entries
                        // filename: $s2media[$s2maufgabe][$topic][$i][0]
                        $mediadata = isset($mediaex[4]) ? explode('|', $mediaex[4]) : array();
                        for ($j = 0; $j < count($exentry[$topic][$i][0]) || $j < count($exentry[$topic][$i][1]); $j++) {
                            // entries
                            $sdata = isset($mediadata[$j]) ? $mediadata[$j] : '';
                            if ( substr($sdata, 0, 4) == '[mc]' ) {
                                $sdata = substr($sdata, 4)*1;
                            }
                            $sdata = str_replace('_', ' ', $sdata);
                            $sdata = str_replace('////', $format == "showashtml" ? '<br/>' : "\r\n", $sdata);
                            array_push($d, $sdata);
                        }
                    }
                }
            }
        }
    }

    // append line to table
    $table->data[] = $d;
}

$tables = array($table);

if ( $format == "showashtml" ) {
    echo stupla_safe_header()
?>
    <form>
        <input type="hidden" name="n" value="<?php echo $stupla->id ?>"/>
        <input type="hidden" name="setDS" value="true"/>
        <input type="checkbox" name="dsTexts" value="<?php echo STUPLA_DS_TEXTS ?>" <?php if ( $ds & STUPLA_DS_TEXTS ) echo ' checked="checked"'; ?>/><?php print_string('display_texts', 'stupla') ?>
        <input type="checkbox" name="dsMedia" value="<?php echo STUPLA_DS_MEDIA ?>" <?php if ( $ds & STUPLA_DS_MEDIA ) echo ' checked="checked"'; ?>/><?php print_string('display_media', 'stupla') ?>
        <input type="checkbox" name="dsEx" value="<?php echo STUPLA_DS_EX ?>" <?php if ( $ds & STUPLA_DS_EX ) echo ' checked="checked"'; ?>/><?php print_string('display_exercises', 'stupla') ?>
        <input type="checkbox" name="dsExSpecial" value="<?php echo STUPLA_DS_EX_SPECIAL ?>" <?php if ( $ds & STUPLA_DS_EX_SPECIAL ) echo ' checked="checked"'; ?>/><?php print_string('display_special_exercises', 'stupla') ?>
        <input type="checkbox" name="dsUseExNames" value="<?php echo STUPLA_DS_USE_EX_NAMES ?>" <?php if ( $ds & STUPLA_DS_USE_EX_NAMES ) echo ' checked="checked"'; ?>/><?php print_string('use_media_exercise_file_names', 'stupla') ?>
        <input type="checkbox" name="dsFirstGlance" value="<?php echo STUPLA_DS_FIRSTGLANCE ?>" <?php if ( $ds & STUPLA_DS_FIRSTGLANCE ) echo ' checked="checked"'; ?>/><?php print_string('use_exercises_first_glance', 'stupla') ?>
        <input type="submit" name="submit" value="<?php print_string('apply_changes', 'stupla') ?>"/>
    </form>
<?php
    print_tables_html($tables);
    echo $errorstring;
    echo stupla_safe_footer();
} else if ( $format == "downloadascsv" ) {
    print_tables_csv("StatisticList", $tables);
} else if ( $format == "downloadasexcel" ) {
    print_tables_xls("StatisticList", $tables);
}

// ------------------------------------------------------------------
// Helper functions
// ------------------------------------------------------------------

function addheader(&$table, $head, $align = 'left', $ordertype = '') {
    array_push($table->head, $head);
    array_push($table->align, $align);
    array_push($table->ordertype, $ordertype);

}

// ------------------------------------------------------------------
// Special exercises
// ------------------------------------------------------------------


// Writing
function specialex_addheader_nonegame0(&$table, $exname) {
    // time
    addheader($table, 't_'.$exname, 'right', 'n');
    // grade
    addheader($table, 'grade_'.$exname, 'right', 'n');
    // percent
    addheader($table, 'pct_'.$exname, 'right', 'n');
    // solved blocks - whether the exercise was solved more than one time
    addheader($table, 'n_used_'.$exname, 'right', 'n');
    for ($i = 1; $i <= 4; $i++) {
        addheader($table, 't_promt_'.$i, 'right', 'n');
        addheader($table, 'cont_'.$i, 'left'. '');
        addheader($table, 'n_help'.$i, 'right', 'n');
        addheader($table, 't_help'.$i, 'right', 'n');
    }
}

function specialex_addheader_nonegame1(&$table, $exname) {
    // time
    addheader($table, 't_'.$exname, 'right', 'n');
    // grade
    addheader($table, 'grade_'.$exname, 'right', 'n');
    // percent
    addheader($table, 'pct_'.$exname, 'right', 'n');
    // solved blocks - whether the exercise was solved more than one time
    addheader($table, 'n_used_'.$exname, 'right', 'n');
    for ($i = 1; $i < 5; $i++) {
        addheader($table, 'trial_'.$i, 'right'. 'n');
        addheader($table, 'left_'.$i, 'right'. 'n');
        addheader($table, 'n_prompt_task'.$i, 'right'. 'n');
        for ($j = 1; $j < 6; $j++) {
            addheader($table, 't_prompt'.$j.'_task'.$i, 'n');
            addheader($table, 'n_trial_prompt'.$j.'_task'.$i, 'n');
            addheader($table, 'prompt'.$j.'_task'.$i, 'n');
            addheader($table, 'n_help'.$j.'_task'.$i, 'n');
            addheader($table, 't_help'.$j.'_task'.$i, 'n');
        }
    }
}

function specialex_addheader_nonegame2(&$table, $exname) {
    // time
    addheader($table, 't_'.$exname, 'right', 'n');
    // grade
    addheader($table, 'grade_'.$exname, 'right', 'n');
    // percent
    addheader($table, 'pct_'.$exname, 'right', 'n');
    // solved blocks - whether the exercise was solved more than one time
    addheader($table, 'n_used_'.$exname, 'right', 'n');
    for ($i = 1; $i < 5; $i++) {
        addheader($table, 'trial_'.$i, 'right'. 'n');
        addheader($table, 'left_'.$i, 'right'. 'n');
        addheader($table, 'n_prompt_task'.$i, 'right'. 'n');
        for ($j = 1; $j < 6; $j++) {
            addheader($table, 't_prompt'.$j.'_task'.$i, 'n');
            addheader($table, 'n_trial_prompt'.$j.'_task'.$i, 'n');
            addheader($table, 'prompt'.$j.'_task'.$i, 'n');
            addheader($table, 'n_help'.$j.'_task'.$i, 'n');
            addheader($table, 't_help'.$j.'_task'.$i, 'n');
        }
    }
}

// Reading
function specialex_addheader_nonegame3(&$table, $exname) {
    // time
    addheader($table, 't_'.$exname, 'right', 'n');
    // grade
    addheader($table, 'grade_'.$exname, 'right', 'n');
    // percent
    addheader($table, 'pct_'.$exname, 'right', 'n');
    // solved blocks - whether the exercise was solved more than one time
    addheader($table, 'n_used_'.$exname, 'right', 'n');
    for ($i = 1; $i < 5; $i++) {
        for ($j = 1; $j < 6; $j++) {
            addheader($table, 't_prompt'.$j.'_task'.$i, 'n');
            addheader($table, 'n_help'.$j.'_task'.$i, 'n');
            addheader($table, 't_help'.$j.'_task'.$i, 'n');
        }
    }
}

$nsolved = 0;

function read_nonegame($session, $stupla, $nonegametype) {
    global $nsolved, $cursessionname;
    $entry = array();
    $reading = false;
    $nsolved = 0;
    $prefix = '[NONEGAME'.$nonegametype.']';
    if ($records = $DB->get_records_select("stupla_action", "stupla='$stupla->id' AND session='$session->id'", "starttime ASC", "*")) {
        foreach ($records as $a) {
            if ( isset($a->data) ) {
                if ( substr($a->data, 0, 23) == $prefix.'[1,0] submit' ) {
                    $keep = (count($entry) > 0) ? true : false;
                    for ($i = 0; $i < count($entry); $i++) {
                        if ( strpos($entry[$i]->data, '[1,0] showed') === false && strpos($entry[$i]->data, '[1,1] showed') === false ) {
                            $keep = false;
                        }
                    }
                    if ( !$keep ) {
                        $entry = array();
                        $reading = true;
                        $nsolved++;
                    }
                }
                if ( $reading && (substr($a->data, 0, 11) == $prefix) ) {
                    array_push($entry, $a);
                    if ( $nsolved == 0 ) {
                        $nsolved = 1;
                    }
                } else {
                    $reading = false;
                }
            } else {
                $reading = true;
            }
        }
    }

    // reorder strange entries
    for ($i = 0; $i < count($entry)-1; $i++) {
        if ( preg_match('/^\[NONEGAME\d\]\[(\d+),([\d\-]+)\] (\S+)\s*(.*)$/', $entry[$i]->data, $b1) &&
            preg_match('/^\[NONEGAME\d\]\[(\d+),([\d\-]+)\] (\S+)\s*(.*)$/', $entry[$i+1]->data, $b2)) {
                if ( $b1[2] != -1 && $b2[2] != -1 && $b1[1] == $b2[1] && $b1[2] > $b2[2] ) {
                    // echo "inversion[", $cursessionname, "][", $entry[$i]->data, "][", $entry[$i+1]->data, "][Delta: ", $entry[$i+1]->start - $entry[$i]->start,"]", '<br>'; // debud
                    // swap
                    $swap = $entry[$i];
                    $entry[$i] = $entry[$i+1];
                    $entry[$i+1] = $swap;
                }
        }
    }
    return $entry;
}

function specialex_adddata_nonegame0($session, $stupla, &$d) {
    global $nsolved;
    // find entries
    $entry = read_nonegame($session, $stupla, '0');

    // collect data
    $res = array();
    for ($i = 0; $i < 4; $i++) {
        $res[$i] = array(0, false, 0, 0); // time, contents, n_hints, t_hints
    }

    // process entries
    $lasttime = 0;
    $last_was_hint = false;
    foreach ($entry as $a) {
        // get position and type
        if ( preg_match('/^\[NONEGAME0\]\[(\d+),([\d\-]+)\] (\S+)\s*(.*)$/', $a->data, $b) ) {
            $iraw = $b[1]-1;
            $ifine = $b[2]+0;
            $type = $b[3];
            $data = $b[4];
            if ( $iraw == 0 ) {
                $ifine -= 1;
            }
            // ignore very first screen
            if  ( $ifine < 0 ) {
                continue;
            }

            if ( $type == 'showed' ) {
                if ( $res[$iraw][0] == 0 ) {
                    $res[$iraw][0] = $a->starttime;
                }
                if ( $last_was_hint ) {
                    $res[$iraw][3] += $a->duration;
                    $last_was_hint = false;
                }
            } else if ( $type == 'confirm' ) {
                if ( $res[$iraw][0] == 0 ) {
                    $res[$iraw][0] = $a->starttime;
                }
                $res[$iraw][0] = $a->starttime - $res[$iraw][0];
                $res[$iraw][1] = $data;
            } else if ( $type == 'submit' ) {
                if ( $res[$iraw][0] == 0 ) {
                    $res[$iraw][0] = $a->starttime;
                }
                $res[$iraw][0] = $a->starttime - $res[$iraw][0];
            } else if ( $type == 'nextExercise' ) {

            } else if ( $type == 'overview' ) {

            } else if ( $type == 'returnMenue' ) {

            } else if ( $type == 'hint' ) {
                $res[$iraw][2]++;
                $last_was_hint = true;
            } else {
                echo "Unexpected type in NONEGAME0: ", $type;
            }
        } else {
            echo "Unknown NONEGAME0: ", $a->data;
        }
        $lasttime = $a->starttime;
    }

    // write out
    array_push($d, $entry[count($entry)-1]->starttime-$entry[0]->starttime); // time
    array_push($d, 'TODO'); // grade
    array_push($d, 'TODO'); // pct
    array_push($d, $nsolved); // nSolved
    for ($i = 0; $i < 4; $i++) {
        if ( $res[$i][1] !== false ) {
            array_push($d, $res[$i][0]);
            array_push($d, str_replace('_', ' ', $res[$i][1]));
            array_push($d, $res[$i][2]);
            array_push($d, $res[$i][3]);
        } else {
            array_push($d, '');
            array_push($d, '');
            array_push($d, '');
            array_push($d, '');
        }
    }
}

function specialex_adddata_nonegame1($session, $stupla, &$d) {
    global $cursessionname, $nsolved, $format;
    // find entries
    $entry = read_nonegame($session, $stupla, '1');

    // collect data
    $resraw = array();
    for ($i = 0; $i < 4; $i++) {
        $resraw[$i] = array(0, 10, 0, ''); // trial(= number of corect solved paragraphs), left (of the 10 trial), n_promt task, skip-log (how the next promt was entered)
    }
    $resfine = array();

    // Reorder strange entries.
    for ($i = 0; $i < count($entry)-1; $i++) {
        if ( preg_match('/^\[NONEGAME1\]\[(\d+),([\d\-]+)\] (\S+)\s*(.*)$/', $entry[$i]->data, $b1) &&
            preg_match('/^\[NONEGAME1\]\[(\d+),([\d\-]+)\] (\S+)\s*(.*)$/', $entry[$i+1]->data, $b2)) {
                if ( $b1[2] != -1 && $b2[2] != -1 && $b1[1] == $b2[1] && $b1[2] > $b2[2] ) {
                    //echo "inversion[", $cursessionname, "][", $entry[$i]->data, "][", $entry[$i+1]->data, "][Delta: ", $entry[$i+1]->start - $entry[$i]->start,"]", '<br>';
                    // swap
                    $swap = $entry[$i];
                    $entry[$i] = $entry[$i+1];
                    $entry[$i+1] = $swap;
                }
        }
    }

    enrich_nonegame1($entry);

    // process entries
    $lastraw = -1; $lastfine = -1;
    $last_was_hint = false;
    $lastendtime = 0;
    foreach ($entry as $a) {
        // get position and type
        if ( preg_match('/^\[NONEGAME1\]\[(\d+),([\d\-]+)\] (\S+)\s*(.*)$/', $a->data, $b) ) {
            $iraw = $b[1]-1;
            $ifine = $b[2]+0;
            $type = $b[3];
            $data = $b[4];
            // correction for first raw:
            if ( $iraw == 0 ) {
                $ifine -= 2;
            }

            if ( $type == 'showed' ) {
                if ( !isset($resfine[$iraw]) ) {
                    $resfine[$iraw] = array();
                }
                if ( $ifine >= 0 && !isset($resfine[$iraw][$ifine]) ) {
                    $resfine[$iraw][$ifine] = array($a->starttime, 0, '', 0, 0); // time/duration, n_trials, promt_contents, n_hints, t_hints
                    $resraw[$iraw][2]++;
                    $resraw[$iraw][3] .= 's'.$ifine;
                }
                if ( $last_was_hint ) {
                    $resfine[$iraw][$ifine][4] += $a->duration;
                    $last_was_hint = false;
                }
            } else if ( ($type == 'confirm') || ($type == '*confirm') || ($type == 'systerror') ) {
                // secure for missing showed event
                if ( !isset($resfine[$iraw]) ) {
                    $resfine[$iraw] = array();
                }
                if ( $ifine >= 0 && !isset($resfine[$iraw][$ifine]) ) {
                    $resfine[$iraw][$ifine] = array($lastendtime, 0, '', 0, 0); // time/duration, n_trials, promt_contents, n_hints, t_hints
                    $resraw[$iraw][2]++;
                    $resraw[$iraw][3] .= 'c'.$ifine;
                }
                // do the regular stuff
                if ( $a->result == 100 ) {
                    $resraw[$iraw][0]++;
                }
                $resraw[$iraw][1]--;
                //echo "[[", $iraw, ", ", $ifine, "]]";
                $resfine[$iraw][$ifine][1]++;
                // content
                $scont = '';
                $c = explode('|', str_replace(' ', '', str_replace('[mc]', '', mc_part($a))));
                $scont .= $c[$ifine*2].','.$c[$ifine*2+1];
                $resfine[$iraw][$ifine][2] .= ($resfine[$iraw][$ifine][2] == '' ? '' : '/').$scont;
            } else if ( $type == 'hint' || $type == '*hint') {
                // secure for missing showed event
                if ( !isset($resfine[$iraw]) ) {
                    $resfine[$iraw] = array();
                }
                if ( $ifine >= 0 && !isset($resfine[$iraw][$ifine]) ) {
                    $resfine[$iraw][$ifine] = array($lastendtime, 0, '', 0, 0); // time/duration, n_trials, promt_contents, n_hints, t_hints
                    $resraw[$iraw][2]++;
                    $resraw[$iraw][3] .= 'h'.$ifine;
                }
                // do the regular stuff
                $resfine[$iraw][$ifine][3]++;
                $last_was_hint = true;
            } else if ( $type == 'submit' ) {

            } else if ( $type == 'overview' ) {

            } else if ( $type == 'nextExercise' ) {
                $iraw++;
                $ifine = 0;
                $resfine[$iraw] = array();
                $resfine[$iraw][$ifine] = array($a->starttime, 0, '', 0, 0); // time/duration, n_trials, ?, ?
                $resraw[$iraw][2]++;
                $resraw[$iraw][3] .= 'n'.$ifine;
            } else if ( $type == 'returnMenue' ) {

            } else {
                echo "Unexpected type in NONEGAME1[", $cursessionname, "]: ", $type;
            }

            if ( $lastraw >= 0 && $lastfine >= 0 && ($iraw != $lastraw || $ifine != $lastfine) ) {
                if ( $resfine[$lastraw][$lastfine][0] > 1000 ) {
                    $resfine[$lastraw][$lastfine][0] = $a->starttime - $resfine[$lastraw][$lastfine][0]; // strftime("%a %d %b %y, %H:%M:%S", $resfine[$lastraw][$lastfine][0]).' '.strftime("%a %d %b %y, %H:%M:%S", $a->starttime);
                }
            }
            $lastraw = $iraw;
            $lastfine = $ifine;
            $lastendtime = $a->starttime + $a->duration; // used for missing start events
        } else {
            echo "Unknown NONEGAME1[", $cursessionname, "]: ", $a->data;
        }
    }

    // write out
    array_push($d, $entry[count($entry)-1]->starttime-$entry[0]->starttime); // time
    array_push($d, 'TODO'); // grade
    array_push($d, 'TODO'); // pct
    array_push($d, $nsolved); // nSolved
    for ($i = 0; $i < 4; $i++) {
        if ( $resraw[$i][2] > 0 ) {
            array_push($d, $resraw[$i][0]);
            array_push($d, $resraw[$i][1].($format == "showashtml" && $resraw[$i][1] > 0 && $resraw[$i][0] < 5 ? '(ERROR?)' : ''));
            array_push($d, $resraw[$i][2].($format == "showashtml" && $resraw[$i][0] > $resraw[$i][2] ? '(ERROR?)' : ''));
        } else {
            array_push($d, '');
            array_push($d, '');
            array_push($d, '');
        }
        for ($j = 0; $j < 5; $j++) {
            if ( isset($resfine[$i][$j]) ) {
                array_push($d, $resfine[$i][$j][0]);
                array_push($d, $resfine[$i][$j][1]);
                array_push($d, $resfine[$i][$j][2]);
                array_push($d, $resfine[$i][$j][3]);
                array_push($d, $resfine[$i][$j][4]);
            } else {
                array_push($d, '');
                array_push($d, '');
                array_push($d, '');
                array_push($d, '');
                array_push($d, '');
            }
        }
    }
}

function specialex_adddata_nonegame2($session, $stupla, &$d) {
    global $nsolved;

    // find entries
    $entry = read_nonegame($session, $stupla, '2');

    // collect data
    $resraw = array();
    for ($i = 0; $i < 4; $i++) {
        $resraw[$i] = array(0, 10, 0); // trial(= number of corect solved paragraphs), left (of the 10 trial), n_promt task
    }
    $resfine = array();

    // process entries
    $lastraw = -1; $lastfine = -1;
    $last_was_hint = false;
    foreach ($entry as $a) {
        // get position and type
        if ( preg_match('/^\[NONEGAME2\]\[(\d+),([\d\-]+)\] (\S+)\s*(.*)$/', $a->data, $b) ) {
            $iraw = $b[1]-1;
            $ifine = $b[2];
            $type = $b[3];
            $data = $b[4];
            // correction for first raw:
            if ( $iraw == 0 ) {
                $ifine -= 2;
            }

            if ( $type == 'showed' ) {
                if ( !isset($resfine[$iraw]) ) {
                    $resfine[$iraw] = array();
                }
                if ( !isset($resfine[$iraw][$ifine]) ) {
                    $resfine[$iraw][$ifine] = array($a->starttime, 0, '', 0, 0); // time/duration, n_trials, promt_contents, n_hints, t_hints
                    $resraw[$iraw][2]++;
                }
                if ( $last_was_hint ) {
                    $resfine[$iraw][$ifine][4] += $a->duration;
                    $last_was_hint = false;
                }
            } else if ( $type == 'confirm' ) {
                if ( $a->result == 100 ) {
                    $resraw[$iraw][0]++;
                }
                $resraw[$iraw][1]--;
                //echo "[[", $iraw, ", ", $ifine, "]]";
                $resfine[$iraw][$ifine][1]++;
                // content
                $scont = '';
                if ( preg_match('/^TaskID:(\d+)/', $data, $b) ) {
                    if ( $resfine[$iraw][$ifine][2] == '' ) {
                        $scont .= 'TaskID:'.$b[1].': ';
                    }
                }
                if ( preg_match('/\s+(\S+\s*\|\s*\d+)\s*$/', $data, $b) ) {
                    $scont .= $b[1];
                }
                if ($scont == '') {
                    $scont = "Error: unparseable";
                }
                $resfine[$iraw][$ifine][2] .= ($resfine[$iraw][$ifine][2] == '' ? '' : '/').$scont;
            } else if ( $type == 'hint' ) {
                $resfine[$iraw][$ifine][3]++;
                $last_was_hint = true;
            } else if ( $type == 'submit' ) {

            } else if ( $type == 'overview' ) {

            } else if ( $type == 'nextExercise' ) {
                $iraw++;
                $ifine = 0;
                $resfine[$iraw] = array();
                $resfine[$iraw][$ifine] = array($a->starttime, 0, '', 0, 0); // time/duration, n_trials, ?, ?
            } else if ( $type == 'returnMenue' ) {

            } else {
                echo "Unexpected type in NONEGAME2: ", $type;
            }

            if ( $lastraw >= 0 && $lastfine >= 0 && ($iraw != $lastraw || $ifine != $lastfine) ) {
                if ( $resfine[$lastraw][$lastfine][0] > 1000 ) {
                    $resfine[$lastraw][$lastfine][0] = $a->starttime - $resfine[$lastraw][$lastfine][0];
                }
            }
            $lastraw = $iraw;
            $lastfine = $ifine;
        } else {
            echo "Unknown NONEGAME2: ", $a->data;
        }
    }

    // write out
    array_push($d, /*strftime("%a %d %b %y, %H:%M:%S", $entry[0]->starttime)*/$entry[count($entry)-1]->starttime-$entry[0]->starttime); // time
    array_push($d, 'TODO'); // grade
    array_push($d, 'TODO'); // pct
    array_push($d, $nsolved); // nSolved
    for ($i = 0; $i < 4; $i++) {
        if ( $resraw[$i][2] > 0 ) {
            array_push($d, $resraw[$i][0]);
            array_push($d, $resraw[$i][1]);
            array_push($d, $resraw[$i][2] > 5 ? 5 : $resraw[$i][2]); // Could be 1 to much (additional "showed" at the end).
        } else {
            array_push($d, '');
            array_push($d, '');
            array_push($d, '');
        }
        for ($j = 0; $j < 5; $j++) {
            if ( isset($resfine[$i][$j]) ) {
                array_push($d, $resfine[$i][$j][0]);
                array_push($d, $resfine[$i][$j][1]);
                array_push($d, $resfine[$i][$j][2]);
                array_push($d, $resfine[$i][$j][3]);
                array_push($d, $resfine[$i][$j][4]);
            } else {
                array_push($d, '');
                array_push($d, '');
                array_push($d, '');
                array_push($d, '');
                array_push($d, '');
            }
        }
    }
}

function specialex_adddata_nonegame3($session, $stupla, &$d) {
    global $cursessionname, $nsolved, $format;
    // Find entries.
    $entry = read_nonegame($session, $stupla, '3');

    // Collect data.
    $resraw = array();
    for ($i = 0; $i < 4; $i++) {
        $resraw[$i] = array(0, 10, 0, ''); // Trial(= number of corect solved paragraphs), left (of the 10 trial), n_promt task, skip-log (how the next promt was entered).
    }
    $resfine = array();

    // process entries
    $lastraw = -1; $lastfine = -1;
    $last_was_hint = false;
    $lastendtime = 0;
    foreach ($entry as $a) {
        // get position and type
        if ( preg_match('/^\[NONEGAME3\]\[(\d+),([\d\-]+)\] (\S+)\s*(.*)$/', $a->data, $b) ) {
            $iraw = $b[1]-1;
            $ifine = $b[2]+0;
            $type = $b[3];
            $data = $b[4];
            // correction for first raw:
            if ( $iraw == 0 ) {
                $ifine -= 1;
            }

            if ( $type == 'showed' ) {
                if ( !isset($resfine[$iraw]) ) {
                    $resfine[$iraw] = array();
                }
                if ( $ifine >= 0 && !isset($resfine[$iraw][$ifine]) ) {
                    $resfine[$iraw][$ifine] = array($a->starttime, 0, '', 0, 0); // Values time/duration, n_trials, promt_contents, n_hints, t_hints.
                    $resraw[$iraw][2]++;
                    $resraw[$iraw][3] .= 's'.$ifine;
                }
                if ( $last_was_hint ) {
                    $resfine[$iraw][$ifine][4] += $a->duration;
                    $last_was_hint = false;
                }
            } else if ( ($type == 'confirm') || ($type == '*confirm') || ($type == 'systerror') ) {
                // secure for missing showed event
                if ( !isset($resfine[$iraw]) ) {
                    $resfine[$iraw] = array();
                }
                if ( $ifine >= 0 && !isset($resfine[$iraw][$ifine]) ) {
                    $resfine[$iraw][$ifine] = array($lastendtime, 0, '', 0, 0); // Values time/duration, n_trials, promt_contents, n_hints, t_hints.
                    $resraw[$iraw][2]++;
                    $resraw[$iraw][3] .= 'c'.$ifine;
                }
                // do the regular stuff
                if ( $a->result == 100 ) {
                    $resraw[$iraw][0]++;
                }
                $resraw[$iraw][1]--;
                //echo "[[", $iraw, ", ", $ifine, "]]";
                $resfine[$iraw][$ifine][1]++;
                // content
                $scont = '';
                $c = explode('|', str_replace(' ', '', str_replace('[mc]', '', mc_part($a))));
                $scont .= $c[$ifine*2].','.$c[$ifine*2+1];
                $resfine[$iraw][$ifine][2] .= ($resfine[$iraw][$ifine][2] == '' ? '' : '/').$scont;
            } else if ( $type == 'hint' || $type == '*hint') {
                // secure for missing showed event
                if ( !isset($resfine[$iraw]) ) {
                    $resfine[$iraw] = array();
                }
                if ( $ifine >= 0 && !isset($resfine[$iraw][$ifine]) ) {
                    $resfine[$iraw][$ifine] = array($lastendtime, 0, '', 0, 0); // Values time/duration, n_trials, promt_contents, n_hints, t_hints.
                    $resraw[$iraw][2]++;
                    $resraw[$iraw][3] .= 'h'.$ifine;
                }
                // do the regular stuff
                $resfine[$iraw][$ifine][3]++;
                $last_was_hint = true;
            } else if ( $type == 'submit' ) {
            } else if ( $type == 'overview' ) {
            } else if ( $type == 'nextExercise' ) {
                $iraw++;
                $ifine = 0;
                $resfine[$iraw] = array();
                $resfine[$iraw][$ifine] = array($a->starttime, 0, '', 0, 0); // Values time/duration, n_trials, ?, ?.
                $resraw[$iraw][2]++;
                $resraw[$iraw][3] .= 'n'.$ifine;
            }
            else if ( $type == 'returnMenue' ) {
            } else {
                echo "Unexpected type in NONEGAME3[", $cursessionname, "]: ", $type;
            }

            if ( $lastraw >= 0 && $lastfine >= 0 && ($iraw != $lastraw || $ifine != $lastfine) ) {
                if ( $resfine[$lastraw][$lastfine][0] > 1000 ) {
                    $resfine[$lastraw][$lastfine][0] = $a->starttime - $resfine[$lastraw][$lastfine][0]; // strftime("%a %d %b %y, %H:%M:%S", $resfine[$lastraw][$lastfine][0]).' '.strftime("%a %d %b %y, %H:%M:%S", $a->starttime);
                }
            }
            $lastraw = $iraw;
            $lastfine = $ifine;
            $lastendtime = $a->starttime + $a->duration; // Used for missing start events.
        } else {
            echo "Unknown NONEGAME3[", $cursessionname, "]: ", $a->data;
        }
    }

    // write out
    array_push($d, $entry[count($entry)-1]->starttime-$entry[0]->starttime); // Value time.
    array_push($d, 'TODO'); // Value grade.
    array_push($d, 'TODO'); // Value pct.
    array_push($d, $nsolved); // Value nSolved.
    for ($i = 0; $i < 4; $i++) {
        for ($j = 0; $j < 5; $j++) {
            if ( isset($resfine[$i][$j]) ) {
                array_push($d, $resfine[$i][$j][0]);
                array_push($d, $resfine[$i][$j][3]);
                array_push($d, $resfine[$i][$j][4]);
            } else {
                array_push($d, '');
                array_push($d, '');
                array_push($d, '');
            }
        }
    }
}

