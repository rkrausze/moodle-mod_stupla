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
 * This page is the protocol page for questionaires (german: fragebogen).
 *
 * @package    mod
 * @subpackage stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');
require_once('data_load.php');

$PAGE->set_url('/mod/stupla/prot/prot_userlist.php', array('id' => $cm->id));
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('frametop');
$PAGE->blocks->show_only_fake_blocks();
echo stupla_safe_header();

echo _('Under construction/migration.');

echo stupla_safe_footer();

// TODO: port this
// <%@ language="javascript" %>
// <!--#include file="prot.inc"-->
// <!--#include file="data.js"-->

// <%
// var fs=Server.CreateObject("Scripting.FileSystemObject");
// var User = Request.QueryString("User");
// var AppName = Request.QueryString("AppName");
// %>

// <html>

// <head>
// <title>Fragebogen-Protokoll, User: <% Response.Write(User) %>, study2000: <% Response.Write(AppName) %></title>
// <meta http-equiv="expires" content="0">
// <style type="text/css"><!--
// td, p { font-size:10pt;font-family:Arial }
// //--></style>
// </head>

// <body>
// <table border="1">
// <tr>
// <td>
// Datum
// </td>
// <td>
// Fragebogen
// </td>
// <td>
// Werte
// </td>
// </tr>
// <%

// var time = new Date();
// if ( fs.FileExists(Server.MapPath(UserPath+User+"_fb.txt")) )
// {
//     var f=fs.OpenTextFile(Server.MapPath(UserPath+User+"_fb.txt"), 1);
//     while ( f.AtEndOfStream == false )
//     {
//         var arr = f.ReadLine().split(' ');
//         //    if ( arr[1] == "#login" )
//         time.setTime(Number(arr[1])*1000);
//         Response.Write('<tr><td>'+time.toLocaleString()+'</td><td><B>'+arr[0]+'</B></td><td>'+
//                         arr.slice(2, arr.length).join(' ')+'</td></tr>');
//     }
//     f.Close();
//     f=0;
// }
// else
//     Response.Write("<tr><td colspan=3>Keine Fragebogen-Datei vorhanden.</td></tr>");

// fs = null;

// %>
// </table>
// </body>
// </html>
