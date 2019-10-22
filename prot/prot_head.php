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
 * This is the protocol haeder frame.
 *
 * @package    mod
 * @subpackage stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$eventdata = array();
$eventdata['objectid'] = $stupla->id;
$eventdata['context'] = $context;
$eventdata['courseid'] = $course->id;

$event = \mod_stupla\event\protocol_viewed::create($eventdata);
$event->trigger();


$PAGE->set_url('/mod/stupla/prot/prot.php', array('id' => $cm->id));
$PAGE->set_title(format_string($stupla->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->navbar->add(get_string('protocol', 'stupla'));
$PAGE->set_pagelayout('standard');
$PAGE->blocks->show_only_fake_blocks();
$PAGE->requires->js('/mod/stupla/prot/ef_restore.js');

$sOut = $OUTPUT->header();
$i = strpos($sOut, '</header>');
$sOut = substr($sOut, 0, $i+9);
echo str_replace('<a ', '<a target="_top" ', $sOut);

$studies = stupla_get_studies();
$sessions = stupla_prot_get_sessions($stupla);

?>
<style type="text/css">
    td, p { font-size:10pt;font-family:Arial;font-weight:bold }
    body, .path-mod-stupla-prot, #page-header, #page-content { background-color:#FFFF99; }
    #page-content .region-content { overflow: hidden; padding: 0; }
    body.drawer-open-left { margin-left: 0; }
    footer { display: none; }
</style>
<form name="f" method="POST" target="data" action="prot_userlist.php">
<input type="hidden" name="n" value="<?php echo $stupla->id ?>"/>
<input type="hidden" name="sortinfo" value="<?php echo $sortinfo ?>"/>
<input type="hidden" name="groupSql" value="<?php echo $groupsql ?>">
<input type="hidden" name="groupText" value="<?php echo $grouptext ?>">
<input type="hidden" name="groupData" value="<?php echo $groupdata ?>">
<input type="hidden" name="inArchive" value="">

<table border="0" width="100%" style="margin-top:50px">
    <tr>
        <td valign="top">
            <select name="protaction" onchange="Action(this.value)">
                <option value="UserList"><?php print_string('user_list', 'stupla'); ?></option>
                <option value="LoginList"><?php print_string('login_list', 'stupla'); ?></option>
                <option value="UserHist"><?php print_string('walkthrough', 'stupla'); ?></option>
                <option value="FB"><?php print_string('questionaire', 'stupla'); ?></option>
                <option value="Exercises"><?php print_string('exercises', 'stupla'); ?></option>
                <option value="ExState"><?php print_string('exercise_states', 'stupla'); ?></option>
                <option value="ExOverview"><?php print_string('exercise_overview', 'stupla'); ?></option>
                <option value="Statistic"><?php print_string('statistics', 'stupla'); ?></option>
            </select>
            <select name="format" onchange="f.submit()">
                <option value="showashtml"><?php print_string('displayonpage', 'stupla'); ?></option>
                <option value="downloadascsv"><?php print_string('downloadtext', 'stupla'); ?></option>
                <option value="downloadasexcel"><?php print_string('downloadexcel', 'stupla'); ?></option>
            </select>
            <input type="button" value="<?php print_string("refresh", 'stupla')?>" onclick="Action(f.protaction.value)"/>
  </td>
  <td align="right" valign="bottom">
   <nobr>study2000
   <select size="1" name="stupla" onchange="Action(Mode)">
<?php
foreach ($studies as $stu) {
    echo "<option value=\"$stu->id\" ".($stu->id == $stupla->id ? ' selected="selected"' : '').">$stu->name</option>";
}
if ( count($studies) > 1 ) {
    echo "<option>gesamt</option>";
}
?>
   </select></nobr>
   <nobr>
   <?php echo get_string("users")?>:
   <select size="1" name="session" onchange="Action(Mode)">
<?php
foreach ($sessions as $session) {
    echo "<option value=\"$session->id\" ".($session->id == $sessionid ? ' selected="selected"' : '').">$session->displayname</option>";
}
?>
   </select></nobr>
  </td>
 </tr>
</table>

</form>
<script type="text/javascript">
//<![CDATA[

var f = document.forms[0];
var PHP = "";
var s2_short = new Array(
<?php
$first = true;
foreach ($studies as $stu) {
    echo $first ? "" : ",", "new Array($stu->id, '$stu->name', '".stupla_www_prefix($stu).str_replace("\\", "/", stupla_compdirreference($stu))."')";
    $first = false;
}
if ( count($studies) > 1 ) {
    echo ", new Array(-1, 'gesamt', '')";
}
?>
);
var Session = new Array(
<?php
$first = true;
foreach ($sessions as $session) {
    echo $first ? "" : ",", "new Array($session->id, '$session->displayname')";
    $first = false;
}
?>
);

var Mode = "UserList";
var ListHeight = "66%";
//var Actions = new Array(UserList, LoginList, Ablauf, FB, Exercises, ExState, ExOverview, Statistic);

function Action(mode)
{
    SaveListHeight();
    Mode = mode;
    eval(Mode+"()");
}

function SaveListHeight()
{
  if ( top.data.list )
    ListHeight = document.all ? top.data.list.document.body.offsetHeight : top.data.list.innerHeight;
}

function TableSort(info) {
    f.sortinfo.value = info;
    f.target = "data";
    f.submit();
}

// Login -----------------------

function UserList()
{
/*  if ( s2_short[f.s2.selectedIndex] == "gesamt" )
    window.open(PHP+"prot_userlistges.php", "data");
  else
    window.open(PHP+"prot_userlist.php?stupla="+f.s2.value, "data");*/
    f.action = s2_short[f.stupla.selectedIndex] == "gesamt" ? "prot_userlistges.php" : "prot_userlist.php";
    f.target = "data";
    f.sortinfo.value = "";
    f.submit();
}

// Login-Liste ------------------

function LoginList()
{
/*  if ( s2_short[f.s2.selectedIndex] == "gesamt" )
    window.open(PHP+"prot_loginlistges.php", "data");
  else
    window.open(PHP+"prot_loginlist.php?stupla="+f.s2.value, "data");*/
    f.action = s2_short[f.stupla.selectedIndex] == "gesamt" ? "prot_loginlistges.php" : "prot_loginlist.php";
    f.target = "data";
    f.sortinfo.value = "";
    f.submit();
}

// UserHist -----------------------

function UserHist()
{
  var doc = top.data.document;
  doc.close();
  doc.open();
  doc.writeln('<frameset rows="', ListHeight, ',*" border="2" frameborder="1" framespacing="2">',
    '<frame src="'+PHP+"prot_hist.php?sessionid="+f.session.value+"&stupla="+f.stupla.value+'" name="list" frameborder="1" framespacing="2">',
    '<frame src="about:blank" name="display">',
    '</frameset>');
  doc.close();
  f.action = "prot_hist.php";
  f.target = "data";
  }

// FB -----------------------

function FB()
{
//  window.open(PHP+"prot_fb.php?session="+f.session.value+"&stupla="+f.s2.value, "data");
    f.action = "prot_fb.php";
    f.target = "data";
    f.sortinfo.value = "";
    f.submit();
}

// Exercises -----------------------

function Exercises()
{
//  window.open(PHP+"prot_exlist.php?stupla="+f.stupla.value, "data");
    f.action = "prot_exlist.php";
    f.target = "data";
    f.sortinfo.value = "";
    f.submit();
}

// Exercises Content -----------------------

var ExNr = 0;
function ExCont()
{
  SaveListHeight();
/*  alert(Name);
  var src = "";
  var count = ExNr;
  for (var topic = 0; topic < Name.length; topic++)
    if ( Media[M_AUFGABE][topic] )
      for (var i = 0; i < Media[M_AUFGABE][topic].length; i++)
        if ( count-- == 0 )
          src = Media[M_AUFGABE][topic][i][0];*/
  var doc = top.data.document;
  doc.close();
  doc.open();
  doc.writeln('<FRAMESET rows="', ListHeight, ',*" border="2" frameborder="1" framespacing="2">',
    '<FRAME src="'+PHP+'prot_excont.php?AppName='+s2_short[f.stupla.selectedIndex]+'&ExNr='+ExNr+'" name="list" frameborder="1" framespacing="2">',
    '<FRAME src="about:blank" name="display">',
    '</FRAMESET>');
  doc.close();
}

// ExState -----------------------

function ExState()
{
  //SaveListHeight();
  window.open(PHP+"prot_exstate.php?stupla="+f.stupla.value+'&GroupNr='+GroupNr, "data");
}

// ExOverview -----------------------

var GroupNr = 0;
function ExOverview()
{
  SaveListHeight();
  window.open(PHP+"prot_exoverview.php?stupla="+f.stupla.value+'&GroupNr='+GroupNr+'&Width='+
    (document.layer ? self.innerWidth : self.document.body.offsetWidth), "data");
}

// Statistic -----------------

function Statistic()
{
    f.action = "prot_statistic.php";
    f.target = "data";
    f.sortinfo.value = "";
    f.submit();
}

//SwitchAll

function SwitchAll(chk) {
    var inp = top.data.document.getElementsByTagName("input");
    for (var i = 0; i < inp.length; i++)
        if ( (""+inp[i].name).substr(0, 3) == 'cb_' )
            inp[i].checked = chk;
}

// Start/Enddate

function StartDate(date) {
  for (var i = 0; i < f.from.options.length; i++)
    if ( date == f.from.options[i].value )
    {
      f.from.selectedIndex = i;
      if ( f.action )
      {
        f.target = "data";
        f.submit();
      }
      break;
    }
}

function EndDate(date) {
  for (var i = 0; i < f.till.options.length; i++)
    if ( date == f.till.options[i].value )
    {
      f.till.selectedIndex = i;
      if ( f.action )
      {
        f.target = "data";
        f.submit();
      }
      break;
    }
}

// aus den Tabellen

function SelectHist(sessionid)
{
    SelectOption(f.session, sessionid);
    SelectOption(f.protaction, "UserHist");
    Action("UserHist");
}

function SelectOption(obj, value)
{
  for (var i = 0; i < obj.options.length; i++)
    if ( value == obj.options[i].value )
    {
      obj.selectedIndex = i;
      break;
    }
}

function empty()
{
  var doc = top.data.document;
  doc.close();
  doc.open();
  doc.write("under construction");
  doc.close();
}

// EF-Zeug fuer Vorschau Aufgabe II

// aufgabe ---------------------------------------------------------------------------------

/*function HintWindow(title, text)
{
  NewDia(title,
    '<FONT color="#CC0000"><B>' + title + ':</B></FONT><P>' +
    text +
    '<P align=right>\r\n' +
    '<INPUT type="BUTTON" value="Schließen" onClick="self.close()"></P>',
    " ",
    420, 250);
}*/

var DIALOG_HEADER = '<BODY text="#003366" bgcolor="#F5F5DC" link="#000055" vlink="#550000">\r\n';

var fromHint = 0;

function HintWindow(title, text, but)
{
  fromHint = 1;
  NewDia(title,
    '<FONT color="#CC0000"><B>' + title + ':</B></FONT><P>' +
    text +
    '<p align=right>\r\n' + (but ? but : "") +
    '<input type="button" value="Schließen" onClick="self.close()"></p>',
    "",
    420, 250, 100, 100, 0);
}

function EFSolvedOutfit(nr)
{
  if ( top.data.display.document.fm.EndButton)
    top.data.display.document.fm.EndButton.value = "Beenden";
//  if ( top.data.display.CrashDown && Flight != 0 )
//    top.data.display.CrashDown();
}

function EFStartOutfit()
{
  var fm = top.data.display.document.fm;
  if ( navigator.userAgent.indexOf("MSIE 5.22; Mac") != -1 ) // Mac mit Hacke
  {
    var inp = top.data.display.document.all.tags("input");
    for (var i = 0; i < inp.length; i++)
      if ( inp[i].type == "button" )
        inp[i].style.width="";
  }
  if ( fm.ConfirmButton.length )
    for (var i = 0; i < fm.ConfirmButton.length; i++)
      fm.ConfirmButton[i].value = "Eingabe bestätigen";
  else
    fm.ConfirmButton.value = "Eingabe bestätigen";
  if ( fm.HintButton )
    if ( fm.HintButton.length )
      for (var i = 0; i < fm.HintButton.length; i++)
        fm.HintButton[i].value = "Hinweis";
    else if ( fm.HintButton )
      fm.HintButton.value = "Hinweis";
  if ( fm.SolveButton )
    if ( fm.SolveButton.length )
      for (var i = 0; i < fm.SolveButton.length; i++)
        fm.SolveButton[i].value = "Lösung";
    else
      fm.SolveButton.value = "Lösung";
  if ( fm.NextButton )
    fm.NextButton.value = "Weiter";
  if ( fm.EndButton )
    fm.EndButton.value = "Abbrechen";
  if ( fm.JumpOverButton )
    fm.JumpOverButton.value = "Überspringen";
  if ( top.data.display.iFHint )
    for (var i = 0; i < top.data.display.FHint.length; i++)
      top.data.display.iFHint[i] = 0;
  if ( fm.mailanswerdo )
  {
    fm.appname.value = AppName;
    fm.mailanswerdo.checked = mailanswerdo;
    fm.mailansweraddress.value = mailansweraddress;
  }
  if ( showHistFlag == 1 )
  {
    showHistFlag = 0;
    setTimeout("FillHistExercise()", 50);
  }
}

function EFHTML2Text(s)
{
  var res = "";
  s = s + "";
  while ( true )
  {
    var i = s.search(/</);
    if  ( i == -1 )
    {
      res += s;
      break;
    }
    res += s.slice(0, i);
    s = s.slice(i+1, s.length);
    i = s.search(/>/);
    if  ( i == -1 )
      break;
    s = s.slice(i+1, s.length);
  }
  res = res.replace(/&auml;/g, "ä");
  res = res.replace(/&ouml;/g, "ö");
  res = res.replace(/&uuml;/g, "ü");
  res = res.replace(/&Auml;/g, "Ä");
  res = res.replace(/&Ouml;/g, "Ö");
  res = res.replace(/&Uuml;/g, "Ü");
  res = res.replace(/&szlig;/g, "ß");
  return res;
}

function AddHist(s) // Abwärtskompatibilitaet für Aufgabenschablonen
{
}

//------------
var NoDiaFlag = 0;
var DiaWin;

var DoFocus = -1;

function NewDia(title, body, script, width, height, x, y, doFocus)
{
  if ( NoDiaFlag == 1 )
  {
    NoDiaFlag = 0;
    return;
  }
  DiaWin = window.open("", "DiaWin", (fromHint == 1 ? "scrollbars=yes," : "")+"resizable=yes,width=" + width + ",height=" + height);
  var doc = DiaWin.document;
  doc.close();
  doc.open();
  doc.writeln(
    '<HEAD><TITLE>' + title + '</TITLE></HEAD>\r\n',
    DIALOG_HEADER,
    '<DIV id=dia><font face="Arial">\r\n',
    '<FORM name="frm" onsubmit="return false;">' +  body, '\r\n</FORM>',
    '</font></DIV>',
    '</BODY>\r\n',
    '<SC' + 'RIPT LANGUAGE="JavaScript"><!-- \r\n',
    '  setTimeout("window.resizeTo(dia.offsetWidth < 410 && dia.offsetHeight > 410 ? 430 : dia.offsetWidth+32, dia.offsetHeight+58)", 200);\r\n',
    '  setTimeout("moveTo(', x, ', ', y, ')", 400);\r\n',
    '  setTimeout("window.resizeTo(dia.offsetWidth < 410 && dia.offsetHeight > 410 ? 430 : dia.offsetWidth+32, dia.offsetHeight+58)", 500);\r\n',
    '  setTimeout("if ( dia.offsetHeight+'+y+' > screen.availHeight ) window.resizeTo(screen.availWidth-'+(x+30)+', screen.availHeight-'+(y+30)+')", 600);\r\n',
    '  var fm = document.frm;\r\n',
    '  var ReturnWin = "1";\r\n',
    script,
    '\r\n',
    '--></SC', 'RIPT>\r\n',
    '</HTML>');
  doc.close();
  DiaWin.focus();
  DoFocus = doFocus;
  if ( script != "" && fromHint == 0 )
    self.setTimeout('SetDiaReturnWin()', 500);
  fromHint = 0;
}

function SetDiaReturnWin()
{
  if ( !DiaWin.closed )
    if ( DiaWin.ReturnWin == "1")
    {
      DiaWin.ReturnWin = top.control;
      if ( DoFocus != -1 )
        DiaWin.document.frm.elements[DoFocus].focus();
    }
    else
      setTimeout('SetDiaReturnWin()', 200);
}

// ShowHistExercise ----------------------------------------------------------------
var showHistFlag = 0;

function ShowHistExercise(ex, m1)
{
  showHistFlag = 1;
  mStore = m1.replace(/###/g, "%");
  window.open(s2_short[f.stupla.selectedIndex][2]+'/'+ex, "display");
}

function FillHistExercise()
{
  if ( mStore != "" ) {
      mStore = decodeURIComponent(mStore);
    Restore(top.data.display);
  }
}

function ShowCont()
{
    // dummy for some bloking/unblocking exercises
}

var curNr = 0;
var AvailField = new Array();
var SpecialTopic = new Array();
//]]>
</script>
<div>&nbsp;</div>
<?php
$sOut = $OUTPUT->footer();
$i = strpos($sOut, '<footer');
$sOut = substr($sOut, $i);
echo $sOut;
