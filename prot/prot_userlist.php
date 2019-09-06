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
 * This page is the protocol page providing an overview of all users/subusers.
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');
require_once('data_load.php');
require_once('prot_util.php');

$PAGE->set_url('/mod/stupla/prot/prot_userlist.php', array('id' => $cm->id));
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('frametop');
$PAGE->blocks->show_only_fake_blocks();

$infostring = ''; // Result and error messages.

$table = new html_table();

$inarchive = false;
if ( optional_param('displayarchive', '', PARAM_ALPHANUM) != '' ||
    (optional_param('inArchive', '', PARAM_ALPHANUM) == '1' && optional_param('leavearchive', '', PARAM_ALPHANUM) == '')) {
    if ( isset($_POST['archivetag']) ) {
        $archivetag = $_POST['archivetag'];
        $cond = ' AND archivetag in(\''.implode('\', \'', $archivetag).'\')';
        $sessions = stupla_prot_get_sessions($stupla, $cond);
        $inarchive = true;
    } else {
        $infostring = get_string('noarchivetagsselected', 'stupla');
        $sessions = stupla_prot_get_sessions($stupla);
    }
} else {
    $sessions = stupla_prot_get_sessions($stupla);
}

$displayRegular = true;
// Special actions.
if ( optional_param('delete', '', PARAM_ALPHANUM) != '' ) {
    $delstring = '';
    $count = 0;
    foreach ($sessions as $session) {
        if ( optional_param('cb_'.$session->id, '', PARAM_ALPHANUM) != '' ) {
            $delstring .= '<tr><td>'.$session->displayname.'<input type = "hidden" name="cb_'.$session->id.'" value="1"/>'.
                '</td><td>'.strftime("%d.%m.%y", $session->starttime).
                '</td><td>'.strftime("%H:%M", $session->starttime).'</td></tr>';
            $count++;
        }
    }
    if ( $count == 0 ) {
        $infostring = get_string('deleteempty', 'stupla');
    } else {
        echo $OUTPUT->header(); ?>
    <form name="fm" method="POST" target="_self" action="prot_userlist.php">
        <input type="hidden" name="n" value="<?php echo $stupla->id ?>">
        <div>
            <?php print_string('deleteverify', 'stupla', $count) ?>
        </div>
        <table class="generaltable">
            <?php echo $delstring ?>
        </table>
        <p>
            <?php submit_button('deleteVerify'); ?>
            <?php submit_button('cancel'); ?>
        </p>
    </form>
        <?php echo $OUTPUT->footer();
        $displayRegular = false;
    }
} else if ( optional_param('deleteVerify', '', PARAM_ALPHANUM) != '' ) {
    $count = 0;
    foreach ($sessions as $session) {
        if ( optional_param('cb_'.$session->id, '', PARAM_ALPHANUM) != '' ) {
            $DB->delete_records('stupla_session', array('id' => $session->id));
            $DB->delete_records('stupla_action', array('session' => $session->id));
            $DB->delete_records('stupla_ex', array('session' => $session->id));
            $DB->delete_records('stupla_sheet', array('session' => $session->id));
            $DB->delete_records('stupla_plan', array('session' => $session->id));
            $DB->delete_records('stupla_action', array('session' => $session->id));
            $count++;
        }
    }
    $infostring = get_string('deletedone', 'stupla', $count);
    $sessions = stupla_prot_get_sessions($stupla);
} else if ( optional_param('archive', '', PARAM_ALPHANUM) != '' ) {
    $arcstring = '';
    $count = 0;
    foreach ($sessions as $session) {
        if ( optional_param('cb_'.$session->id, '', PARAM_ALPHANUM) != '' ) {
            $arcstring .= '<tr><td>'.$session->displayname.'<input type = "hidden" name="cb_'.$session->id.'" value="1"/>'.
                    '</td><td>'.strftime("%d.%m.%y", $session->starttime).
                    '</td><td>'.strftime("%H:%M", $session->starttime).'</td></tr>';
            $count++;
        }
    }
    if ( $count == 0 ) {
        $infostring = '<div>'.get_string('archiveempty', 'stupla').'</div>';
    } else {
        echo $OUTPUT->header(); ?>
        <form name="fm" method="POST" target="_self" action="prot_userlist.php">
            <input type="hidden" name="n" value="<?php echo $stupla->id ?>">
            <div>
                <?php print_string('archiveverify', 'stupla', $count) ?>
            </div>
            <table class="generaltable">
                <?php echo $arcstring ?>
            </table>
            <p>
                <?php print_string('archiveselecttag', 'stupla')?>
                <table>
                    <tr>
                        <td><?php print_string('archiveselecttagnew', 'stupla')?></td>
                        <td><input type="text" name="archivetagnew" value=""></td>
                    </tr>
        <?php
        $tags = $DB->get_records_sql('SELECT archivetag FROM '.$CFG->prefix.'stupla_session WHERE stupla = '.$stupla->id.
            ' AND NOT archivetag IS NULL GROUP BY archivetag');
        if ( count($tags) > 0 ) {
            echo '<tr><td>', get_string('archiveselecttagold', 'stupla'),
                '</td><td><select name="archivetagold">',
                '<option></option>';
            foreach ($tags as $tag) {
                echo '<option>', $tag->archivetag, '</option>';
            }
            echo '</select></td></tr>';
        }
        ?>
                </table>
                <p>
                    <?php submit_button('archiveVerify',
                        "if ( checkInput() ) document.forms['fm'].target = '_self'; else return false;"); ?>
                    <?php submit_button('cancel'); ?>
                </p>
            </p>
        </form>
        <script type="text/javascript">
        function checkInput()
        {
            var fm = document.forms['fm'];
            var c = 0;
            if ( (""+fm.archivetagnew.value).replace(/^\s+/, "").replace(/\s+$/, "") != "" )
                c++;
            if ( !!fm.archivetagold && fm.archivetagold.value != "" )
                c++;
            if ( c == 0 )
                alert("<?php print_string('archivetagneeded', 'stupla'); ?>");
            else if ( c == 2 )
                alert("<?php print_string('archivetagonlyone', 'stupla'); ?>");
            else
                return true;
            return false;
        }
        </script>
        <?php echo $OUTPUT->footer();
        $displayRegular = false;
    }
} else if ( optional_param('archiveVerify', '', PARAM_ALPHANUM) != '' ) {
    $archivetag = optional_param('archivetagnew', '', PARAM_TEXT);
    if ( $archivetag == '' ) {
        $archivetag = optional_param('archivetagold', '', PARAM_TEXT);
    }
    $count = 0;
    foreach ($sessions as $session) {
        if ( optional_param('cb_'.$session->id, '', PARAM_ALPHANUM) != '' ) {
            $DB->update_record('stupla_session', array('id' => $session->id, 'archivetag' => $archivetag));
            $count++;
        }
    }
    $infostring = get_string('archivedone', 'stupla', array('count' => $count, 'label' => $archivetag));
    // Reload the sessions.
    $sessions = stupla_prot_get_sessions($stupla);
}

if ( optional_param('unarchive', '', PARAM_ALPHANUM) != '' ) {
    $unarcstring = '';
    $count = 0;
    foreach ($sessions as $session) {
        if ( optional_param('cb_'.$session->id, '', PARAM_ALPHANUM) != '' ) {
            $unarcstring .= '<tr><td>'.$session->displayname.'<input type = "hidden" name="cb_'.$session->id.'" value="1"/>'.
                    '</td><td>'.strftime("%d.%m.%y", $session->starttime).
                    '</td><td>'.strftime("%H:%M", $session->starttime).'</td>'.
                    '</td><td>'.$session->archivetag.'</td></tr>';
            $count++;
        }
    }
    if ( $count == 0 ) {
        $infostring = get_string('unarchiveempty', 'stupla');
    } else {
        echo $OUTPUT->header(); ?>
    <form name="fm" method="POST" target="_self" action="prot_userlist.php">
        <input type="hidden" name="n" value="<?php echo $stupla->id ?>">
        <div>
            <?php print_string('unarchiveverify', 'stupla', $count) ?>
        </div>
        <table class="generaltable">
            <?php echo $unarcstring ?>
        </table>
        <p>
            <?php submit_button('unarchiveVerify'); ?>
            <?php submit_button('cancel'); ?>
        </p>
    </form>
        <?php echo $OUTPUT->footer();
        $displayRegular = false;
    }
} else if ( optional_param('unarchiveVerify', '', PARAM_ALPHANUM) != '' ) {
    $count = 0;
    foreach ($_POST as $key => $val) {
        if ( substr($key, 0, 3) == 'cb_' && $val == 1 ) {
            $sesid = substr($key, 3);
            if ( is_number($sesid) ) {
                $DB->update_record('stupla_session', array('id' => $sesid, 'archivetag' => null));
                $count++;
            }
        }
    }
    $infostring = get_string('unarchivedone', 'stupla', $count);
    // Reload the sessions.
    $sessions = stupla_prot_get_sessions($stupla);
}

if ( $displayRegular ) {
    // Regular page.

    // Table.
    $table->name = get_string('user_list', 'stupla');
    if ( $inarchive ) {
        $table->name .= ' ('.implode(', ', $archivetag).')';
    }
    $table->head = array();
    $table->align = array();
    $table->ordertype = array();

    if ( $format == 'showashtml' ) {
        array_push($table->head, '<input type="checkbox" name="cbAll" onclick="top.control.SwitchAll(this.checked)"/>');
        array_push($table->align, 'center');
        array_push($table->ordertype, '');
    }

    array_push($table->head,
        get_string('user'),
        get_string('logins', 'stupla'),
        get_string('fb_reqs', 'stupla'),
        get_string('texts', 'stupla'),
        get_string('texts_time', 'stupla'),
        get_string('media', 'stupla'),
        get_string('exercises', 'stupla'),
        get_string('time_total', 'stupla')
    );
    array_push($table->align, 'left', 'right', 'right', 'right', 'right', 'right', 'right', 'right');
    array_push($table->ordertype, 'a', 'n', 'n', 'n', '', 'n', '', '');

    if ( $inarchive ) {
        array_push($table->head, get_string('archivetag', 'stupla'));
        array_push($table->align, 'center');
        array_push($table->ordertype, '');
    }

    $table->data = array();

    // Subuser data.
    if ( $stupla->flags & STUPLA_USESUBUSERS ) {
        stupla_expand_subuserdata($stupla);
        foreach ($stupla->sudata as $field) {
            array_push($table->head, $field['name']);
            array_push($table->align, 'left');
            array_push($table->ordertype, '');
        }
    }

    foreach ($sessions as $session) {
        stupla_prot_clear_hist();
        stupla_prot_read_hist($session, $stupla);
        $line = array();
        if ( $format == "showashtml" ) {
            array_push($line, '<input type="checkbox" name="cb_'.$session->id.'" value="'.$session->displayname.'"'.
                (optional_param('cb_'.$session->id, '', PARAM_ALPHANUM) != '' ? ' checked="checked"' : '').'/>');
        }
        array_push($line,
            $format == "showashtml"
            ? '<a href="javascript:parent.control.SelectHist(\''.$session->id.'\')" target="control">'.$session->displayname.'</a>'
            : $session->displayname);
        $line = array_merge($line, stupla_prot_summary_hist($format == "showashtml"));

        if ( $inarchive ) {
            array_push($line, $session->archivetag);
        }

        // Subuserdata.
        if ( $stupla->flags & STUPLA_USESUBUSERS ) {
            $subdat = isset($session->data) ? explode(STUPLA_X01, $session->data) : array();
            for ($i = 0; $i < count($stupla->sudata); $i++) {
                array_push($line, isset($subdat[$i]) ? $subdat[$i] : '');
            }
        }

        // Append line to table.
        $table->data[] = $line;
    }

    $tables = array($table);

    if ( $format == "showashtml" ) {
        echo stupla_safe_header();
        if ( $infostring != '' ) {
            echo $OUTPUT->box($infostring, 'noticebox');
        }
    ?>
    <form name="fm" method="POST" target="_self" action="prot_userlist.php">
        <input type="hidden" name="n" value="<?php echo $stupla->id ?>">
        <input type="hidden" name="inArchive" value="<?php echo $inarchive ? '1' : ''; ?>">
        <input type="hidden" name="sortinfo" value="<?php echo $sortinfo ?>">
        <?php print_tables_html(array($table)); ?>
    <p>
        <?php submit_button('delete'); echo '<br />';
        if ( !$inarchive ) {
            submit_button('archive'); echo '<br />';
        } else {
            submit_button('unarchive'); echo '<br />';
        }

        $tags = $DB->get_records_sql('SELECT archivetag, count(1) as cnt FROM '.$CFG->prefix.'stupla_session WHERE stupla = '.
            $stupla->id.' AND NOT archivetag IS NULL GROUP BY archivetag');
        if ( count($tags) > 0 ) {
            submit_button('displayarchive');
            echo '<select name="archivetag[]" size="1" multiple="multiple" style="height:35px;">';
            foreach ($tags as $tag) {
                echo '<option value="', $tag->archivetag, '"',
                    $inarchive && array_search($tag->archivetag, $archivetag) !== false ? ' selected="selected"' : '',
                    '>', $tag->archivetag, '  (#', $tag->cnt, ')</option>';
            }
            echo '</select><br />';
        }
        if ( $inarchive ) {
            submit_button('leavearchive'); echo '<br />';
        }
        ?>
        </p>
    </form>
    </p>
    <?php
        echo stupla_safe_footer();
    } else if ( $format == "downloadascsv" ) {
        print_tables_csv('UserList', $tables);
    } else if ( $format == "downloadasexcel" ) {
        print_tables_xls('UserList', $tables);
    }
}

function submit_button($id, $onclick = "document.forms['fm'].target = '_self';") {
    echo '<input type="submit" name="', $id, '" onclick="', $onclick, '" value="',
    get_string(str_replace('Verify', '', $id), 'stupla'), '" target="data"> ';
    if (get_string_manager()->string_exists($id.'_explain', 'stupla')) {
        print_string($id.'_explain', 'stupla');
    }
}
