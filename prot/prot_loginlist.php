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
 * This page is the protocol page for all logins.
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

$PAGE->set_url('/mod/stupla/prot/prot_loginlist.php', array('id' => $cm->id));
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('frametop');
$PAGE->blocks->show_only_fake_blocks();

$table = new html_table();

$table->name = get_string('login_list', 'stupla');
$table->head = array(
                get_string('date'),
                get_string('time'),
                get_string('user')
);
$table->align = array('left', 'left', 'left');
$table->data = array();
$table->ordertype = array('d', 't', 'a');

$sessions = stupla_prot_get_sessions($stupla);
foreach ($sessions as $session) {
    $table->data[] = array(strftime("%d.%m.%y", $session->starttime), strftime("%H:%M:%S", $session->starttime),
                        $format == "showashtml"
                            ? '<a href="javascript:top.control.SelectHist(\''.$session->id.'\')" target="control">'.
                                $session->displayname.'</a>'
                            : $session->displayname);
}
$tables = array($table);

if ( $format == "showashtml" ) {
    echo stupla_safe_header();
    print_tables_html($tables);
    echo $errorstring;
    echo stupla_safe_footer();
} else if ( $format == "downloadascsv" ) {
    print_tables_csv('LoginList', $tables);
} else if ( $format == "downloadasexcel" ) {
    print_tables_xls('LoginList', $tables);
}
