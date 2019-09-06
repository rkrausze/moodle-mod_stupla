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
 * This page is the back link for the stupla to save protocol actions.
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

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

$type = required_param('type', PARAM_TEXT);
$data = optional_param('data', '', PARAM_TEXT);
$data2orig = optional_param('data2', '', PARAM_TEXT);
$data2 = str_replace('\"', '"', $data2orig);
if (!($stupla->flags & STUPLA_ISUTF8) && optional_param('ajax', '0', PARAM_INT) != 1) {
    $data = utf8_encode($data);
    $data2orig = utf8_encode($data2orig);
    $data2 = utf8_encode($data2);
}
?>
  <body bgcolor="#008000">
<?php

$javascript = "";

if ($type == "mark") {
    stupla_add_extra_action(STUPLA_MARK, $data);
} else if ($type == "note") {
    stupla_add_extra_action(STUPLA_NOTE, $data);
} else if ($type == "ex") {
    stupla_add_action($data);
    $b = explode(" ", $data2, 2);
    $d = explode(".", $b[0]);
    $ex = new object();
    $ex->stupla = $stupla->id;
    $ex->session = $sessionid;
    $ex->starttime = time();
    $ex->media = $d[0];
    $ex->topic = $d[1];
    $ex->nr = $d[2];
    $ex->ex = $b[1];
    $DB->insert_record('stupla_ex', $ex);
} else if ($type == "sheet") {
    stupla_add_extra_action(STUPLA_SHEET, $data);
    $sheet = new object();
    $sheet->stupla = $stupla->id;
    $sheet->session = $sessionid;
    $sheet->starttime = time();
    $sheet->sheet = $data2orig;
    $DB->insert_record('stupla_sheet', $sheet);
} else if ($type == "plan") {
    stupla_add_extra_action(STUPLA_PLAN, $data);
    $plan = new object();
    $plan->stupla = $stupla->id;
    $plan->session = $sessionid;
    $plan->starttime = time();
    $plan->plan = $data2orig;
    $DB->insert_record('stupla_plan', $plan);
} else if ( substr($data, 0, 1) == '#') {
    stupla_add_extra_action(STUPLA_GENERIC, $data);
} else {
    // Regular protocol.
    stupla_add_action($data);
}

// Write back HTML.
echo $javascript;
?>
  <form method="POST" action="<?php echo $CFG->wwwroot; ?>/mod/stupla/s2_prot.php">
      <input type="hidden" name="course" value="<?php echo $id; ?>">
      <input type="hidden" name="stupla" value="<?php echo $a; ?>">
      <input type="hidden" name="user" value="<?php echo $sessionid; ?>">
      <input type="hidden" name="type" value="">
      <input type="hidden" name="data" value="">
      <input type="hidden" name="marks" value="">
      <input type="hidden" name="notes" value="">
      <input type="hidden" name="data2" value="">
      <input type="hidden" name="prot" value="">
  </form>
</body>
