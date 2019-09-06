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
 * Prints a list of stupla instances.
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);   // The course.

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course);

$eventdata = array('context' => context_course::instance($id));
$event = \mod_stupla\event\course_module_instance_list_viewed::create($eventdata);
$event->add_record_snapshot('course', $course);
$event->trigger();

$coursecontext = context_course::instance($course->id);

// Moodle 1.4+ requires sesskey to be passed in forms.
if (isset($USER->sesskey)) {
    $sesskey = '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';
} else {
    $sesskey = '';
}

$PAGE->set_url('/mod/stupla/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($coursecontext);
$PAGE->navbar->add(get_string('modulenameplural', 'stupla'));

echo $OUTPUT->header();

// Get all required strings.

$strstuplas = get_string("modulenameplural", "stupla");
$strstupla  = get_string("modulename", "stupla");

// Get all the appropriate data.

if (! $stuplas = get_all_instances_in_course("stupla", $course)) {
    notice("There are no stuplas", "../../course/view.php?id=$course->id");
    die;
}

// Print the list of instances.

$table = new html_table();

if ($course->format == 'weeks') {
    $table->head  = array(get_string('week'));
    $table->align = array('center');
} else if ($course->format == 'topics') {
    $table->head  = array(get_string('topic'));
    $table->align = array('center');
} else {
    $table->head  = array();
    $table->align = array();
}

$strupdate = get_string('update');

$useupdatecolumn = has_capability('moodle/course:manageactivities', $coursecontext);
$usereportcolumn = has_capability('moodle/site:viewreports', $coursecontext) ||
                    has_capability('mod/stupla:reviewallprotocols', $coursecontext) ||
                    has_capability('mod/stupla:reviewmyprotocols', $coursecontext);

if ( $useupdatecolumn ) {
    array_push($table->head, $strupdate);
    array_push($table->align, "center");
}

array_push($table->head,
    get_string('name'),
    get_string('users').' / '.get_string('subusers', 'stupla').' / '.get_string('sessions', 'stupla'),
    get_string('archivedusers', 'stupla').' / '.get_string('subusers', 'stupla').' / '.get_string('sessions', 'stupla')
);
array_push($table->align,
    'left', 'left', 'left'
);

if ( $usereportcolumn ) {
    array_push($table->head, get_string('report'));
    array_push($table->align, 'center');
}

foreach ($stuplas as $stupla) {
    if (!$stupla->visible) {
        // Show dimmed if the mod is hidden.
        $link = "<a class=\"dimmed\" href=\"view.php?id=$stupla->coursemodule\">$stupla->name</a>";
    } else {
        // Show normal if the mod is visible.
        $link = "<a href=\"view.php?id=$stupla->coursemodule\">$stupla->name</a>";
    }

    $data = array ();

    if ($course->format=="weeks" || $course->format=="topics") {
        array_push($data, $stupla->section);
    }

    if ( $useupdatecolumn ) {
        $updatebutton = ''
        .   '<form '.(isset($CFG->framename) ? 'target="'.$CFG->framename.'"' : '')
        .   ' method="get" action="'.$CFG->wwwroot.'/course/mod.php">'
        .   '<input type="hidden" name="update" value="'.$stupla->coursemodule.'" />'
        .   $sesskey
        .   '<input type="submit" value="'.$strupdate.'" />'
        .   '</form>';
        array_push($data, $updatebutton);
    }

    array_push($data, $link);

    // Number of users.
    array_push($data,
        $DB->count_records_select("stupla_session", "stupla = $stupla->id AND archivetag IS NULL", null,
            "COUNT(DISTINCT userid)")." / ".
        $DB->count_records_select("stupla_session", "stupla = $stupla->id AND archivetag IS NULL", null,
            "COUNT(DISTINCT data)")." / ".
        $DB->count_records_select("stupla_session", "stupla = $stupla->id AND archivetag IS NULL"));
    // Number of archived users.
    array_push($data,
        $DB->count_records_select("stupla_session", "stupla = $stupla->id AND archivetag IS NOT NULL", null,
            "COUNT(DISTINCT userid)")." / ".
        $DB->count_records_select("stupla_session", "stupla = $stupla->id AND archivetag IS NOT NULL", null,
            "COUNT(DISTINCT data)")." / ".
        $DB->count_records_select("stupla_session", "stupla = $stupla->id AND archivetag IS NOT NULL"));

    if ( $usereportcolumn ) {
        array_push($data,
            "<a href=\"prot/prot.php?id=$stupla->coursemodule\">".get_string("protocol", "stupla")."</a>");
    }

    $table->data[] = $data;
}

echo '<br /><span id="markHeight"></span>';

echo $OUTPUT->heading(get_string('modulenameplural', 'stupla'), 2);
echo html_writer::table($table);
?>
<script type="text/javascript">
//<![CDATA[
  function absoluteTop(obj)
  {
    var w = obj.offsetTop;
    while ( obj.offsetParent )
    {
      obj = obj.offsetParent;
      w += obj.offsetTop;
    }
    return w;
  }

  var headsize = absoluteTop(document.getElementById("markHeight"))+30;
  for (var i = 0; i < document.links.length; i++)
    if ( document.links[i].href.search(/prot\/prot.php/) != -1 )
      document.links[i].href = document.links[i].href+"&headsize="+headsize;

//]]>
</script>
<?php

// Finish the page.
echo $OUTPUT->footer();

