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
 * This is the protocol frame set.
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../../config.php");
$headsize = optional_param('headsize', -1, PARAM_INT);

if ( $headsize != -1 ) {
    echo '<frameset rows="', $headsize, ',*" border="0" frameborder="0" framespacing="0">',
    '<frame src="prot_head.php?', $_SERVER['QUERY_STRING'], '" name="control" scrolling="no">',
    '<frame src="prot_userlist.php?', $_SERVER['QUERY_STRING'], '" name="data">',
    '</frameset>';
}
else {
    require_once('protlib.php');
    require_login($course, true, $cm);
    $context = context_module::instance($cm->id);

    $PAGE->set_url('/mod/etest/prot/prot.php', array('id' => $cm->id));
    $PAGE->set_title(format_string($stupla->name));
    $PAGE->set_heading(format_string($course->fullname));
    $PAGE->set_context($context);
    $PAGE->navbar->add(get_string('protocol', 'stupla'));
    $PAGE->set_pagelayout('frametop');
    $PAGE->blocks->show_only_fake_blocks();

    echo str_replace('<a ', '<a target="_top" ', $OUTPUT->header());
    echo '<br /><span id="markHeight"></span>';
    echo $OUTPUT->footer();
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

    var headsize = absoluteTop(document.getElementById("markHeight"))+34
    window.location.href = window.location.href + "&headsize="+headsize;
    //]]>
    </script><?php
}
