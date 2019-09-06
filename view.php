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
 * Prints a particular instance of stupla
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or ...
$n  = optional_param('n', 0, PARAM_INT);  // stupla instance ID.

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

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$eventdata = array();
$eventdata['objectid'] = $stupla->id;
$eventdata['context'] = $context;

$event = \mod_stupla\event\course_module_viewed::create($eventdata);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->trigger();

// Print the page header.
$PAGE->set_url('/mod/stupla/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($stupla->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here.
echo $OUTPUT->header();

echo '<form action="s2.php" target="_blank" name="fm" method="post">
<input type="hidden" name="action" value="START"/>
<input type="hidden" name="course" value="', $id, '"/>
<input type="hidden" name="stupla" value="', $stupla->id, '"/>
<input type="hidden" name="user" value="0"/>';

echo $OUTPUT->heading($stupla->name);

if ($stupla->intro) { // Conditions to show the intro can change to look for own settings or whatever.
    echo $OUTPUT->box(format_module_intro('stupla', $stupla, $cm->id), 'generalbox mod_introbox', 'stuplaintro');
}

if ($stupla->flags & STUPLA_USESUBUSERS) {
    stupla_expand_subuserdata($stupla);
    echo '<table border="0" cellspacing="0" cellpadding="2" align="center">';
    foreach ($stupla->sudata as $fieldid => $field) {
        $name = 'data'.$fieldid;
?>
        <tr>
            <td style="white-space:nowrap; font-weight:bold;"><?php echo $field['name'] ?></td>
            <td><?php
        if ($field['type'] != 'combo') {
            ?><input type="text" name="<?php echo $name ?>" size="29"/><?php
        } else {
            ?><select name="<?php echo $name ?>">
                <option value=""><?php print_string('combo_please_select', 'stupla') ?></option><?php
            foreach (preg_split("/(\r|)\n/", $field["data"]) as $val) {
                echo '<option value="', $val, '">', $val, '</option>';
            }
            ?></select><?php
        }
?>
            </td>
            <td style="text-align:left; font-style:italic;">
                <?php echo $field['comment'] == '' && $field['type'] == 'date' ? get_string('dateComment', 'stupla'): $field['comment']?>
            </td>
        </tr><?php
    }
?>
    </table>
<p>
<script type="text/javascript">
//<![CDATA[

function CivilizeBlanks(s)
{
  return (""+s).replace(/^\s+/, "").replace(/\s+$/, "").replace(/\s+/, " ");
}

function Start()
{
<?php

    foreach ($stupla->sudata as $fieldid => $field) {
        $name = 'data'.$fieldid;
        if ($field["type"] == "text") {
?>
            document.forms["fm"].<?php echo $name ?>.value = CivilizeBlanks(document.forms["fm"].<?php echo $name ?>.value);
<?php
            if ( $field["must"] ) {?>
                if ( document.forms["fm"].<?php echo $name ?>.value == "" ) {
                    alert("<?php echo get_string("missingField", 'stupla', $field['name']); ?>");
                    document.forms["fm"].<?php echo $name ?>.focus();
                    return false;
                }
<?php
            }
        } else if ($field["type"] == "date") {
?>
            document.forms["fm"].<?php echo $name ?>.value = CivilizeBlanks(document.forms["fm"].<?php echo $name ?>.value);
<?php
            if ( $field["must"] ) {
?>
                if ( document.forms["fm"].<?php echo $name ?>.value == "" ) {
                    alert("<?php echo get_string("missingField", 'stupla', $field['name']); ?>");
                    document.forms["fm"].<?php echo $name ?>.focus();
                    return false;
                }
<?php
            }
?>
            if ( document.forms["fm"].<?php echo $name ?>.value != "" && !(/\d{1,2}\.\d{1,2}\.\d{2,4}/.test(document.forms["fm"].<?php echo $name ?>.value)) ) {
                alert("<?php echo get_string("formatField", 'stupla', $field['name']); ?>");
                document.forms["fm"].<?php echo $name ?>.focus();
                return false;
            }
<?php
        } else if ($field["type"] == "combo") {
            if ($field["must"]) {
?>
                if ( document.forms["fm"].<?php echo $name ?>.value == "" ) {
                    alert("<?php echo get_string("missingSelect", 'stupla', $field['name']); ?>");
                    document.forms["fm"].<?php echo $name ?>.focus();
                    return false;
                }
<?php
            }
        }
    }
?>
    document.forms["fm"].submit();
}

//]]>
</script>
</p><?php

}
?>
<div align="center"><b>
<?php

if ($stupla->flags & STUPLA_USESUBUSERS) {
?>
    <input type="button" value="<?php print_string('start', 'stupla') ?>" onclick="return Start();"/>
<?php
} else { ?>
    <input type="submit" value="<?php print_string('start', 'stupla') ?>"/>
<?php
} ?>
</b></div>
</form>
<?php
$sessflags = ($stupla->flags & STUPLA_SESS_MASK);

if ($sessflags == STUPLA_SESS_PRIMARYMULTI || $sessflags == STUPLA_SESS_PRIMARYONE
    || ($sessflags == STUPLA_SESS_ONLYONE && ($stupla->flags & STUPLA_USESUBUSERS))) {
    if ($sessions = $DB->get_records_select('stupla_session',
        "stupla='$stupla->id' AND userid='$USER->id' AND archivetag IS NULL", null, "starttime DESC")) {
?>
        <div align="center">
            <br/><br/>
            <a onclick="document.getElementById('sessions').style.display='inline'">
                <?php print_string('continuesessions', 'stupla') ?>
            </a>
        </div>
        <div id="sessions" style="display:none;">
            <table class="generaltable" style="margin-left:auto; margin-right:auto;"><?php

        foreach ($sessions as $session) {
            echo '<tr><td class="cell">', strftime("%a %d.%m.%y</td><td class=\"cell\">%H:%M:%S", $session->starttime),
                '</td><td class="cell"><a href="s2.php?action=CONTINUE&amp;course='.
                $id.'&amp;stupla='.$stupla->id.'&amp;sessionid='.$session->id.'" target="_blank">',
                print_string('resume', 'stupla'), '</a>';
            if ($stupla->flags & STUPLA_USESUBUSERS) {
                echo '</td><td class="cell">', stupla_displayname($stupla, $session, $USER);
            }
            echo '</td></tr>';
        }
?>
            </table>
        </div><?php
        if ($sessflags == STUPLA_SESS_PRIMARYONE) {
            echo '<div align="center"><a href="s2.php?action=NEWSESSION&amp;course='.
                $id.'&amp;stupla='.$stupla->id.'&amp;sessionid='.$session->id.'" target="_blank">',
                print_string('newsession', 'stupla'), '</a></div>';
        }
    }
}

// Finish the page.
echo $OUTPUT->footer();
