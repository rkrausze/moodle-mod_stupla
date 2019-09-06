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
 * This page is the protocol page for exercise content.
 *
 * @package    mod_stupla
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
echo $OUTPUT->header();

echo _('Under construction/migration.');

echo $OUTPUT->footer();

// TODO: port this
// <%@ language="javascript" %>
// <!--#include file="prot.inc"-->
// <!--#include file="data.js"-->
// <!--#include file="efk.js"-->
// <!--#include file="aufgaben/aufgaben.inc"-->

// <%
// var fs=Server.CreateObject("Scripting.FileSystemObject");
// var AppName = Request.QueryString("AppName");
// var ExNr = Request.QueryString("ExNr");
// %>

// <html>

// <head>
// <title>Protokoll - ExerciseContent, study2000: <% Response.Write(AppName) %></title>
// <meta http-equiv="expires" content="0">
// <style type="text/css"><!--
//   td, p { font-size:10pt;font-family:Arial;font-weight:bold }
//   body { font-size:10pt;font-family:Arial; }
//   .systerror { color:#2E8B57 }
// //--></style>
// </head>

// <body bgcolor="#FFFFFF">
// <form>
// <TABLE border=0 width="100%" cellpadding=0 cellspacing=0><TR>
// <%
// var sError = "";
// var User = GetUsers(AppName);

// function EntrySort(a,b)
// { return b[0]-a[0]; }

// var maxBar = 200;
// var count = ExNr;
// var s = "";
// var ExTopic = 0;
// var ExI = 0;
// for (var topic = 0; topic < Name.length; topic++)
//   if ( Media[M_AUFGABE][topic] )
//     for (var i = 0; i < Media[M_AUFGABE][topic].length; i++) {
//       if ( count == 0 )
//         Response.Write('<TD><B>'+Media[M_AUFGABE][ExTopic = topic][ExI = i][0]+"</B></TD>");
//       s += "<option"+(count-- == 0 ? " selected":"")+">"+Media[M_AUFGABE][topic][i][0]+"</option>";
//     }
// %>
// <TD align="right"><select name="ExNr" size=1 onChange="top.control.ExNr = document.forms[0].ExNr.selectedIndex; top.control.ExCont();">
// <% Response.Write(s); %>
// </select></TD>
// </TR></TABLE>
// <%
// stupla_prot_make_exercise_listStrict(AppName, ExTopic, ExI);


// var ProtAnswer = new Array(
//   ProtMCR, ProtMCP, ProtMCX, ProtVR,
//   ProtMEMO, ProtST, ProtLT,
//   ProtTF, ProtTFM, ProtTLS, ProtTLM,
//   ProtSLS, ProtSLM,
//   ProtZO, ProtFZO);

// var AddTypes = new Array(
//   AddTypesMCR, AddTypesMCP, AddTypesMCX, AddTypesVR,
//   AddTypesMEMO, AddTypesST, AddTypesLT,
//   AddTypesTF, AddTypesTFM, AddTypesTLS, AddTypesTLM,
//   AddTypesSLS, AddTypesSLM,
//   AddTypesZO, AddTypesFZO);

//   m = EF_M[ExNr];
//   d = EF_D[ExNr];
//   type = EF_Type[ExNr];

//   var j = 0;
//   var ges;
//   var max;
//   while ( j < EF_Type[ExNr].length )
//   {
//     if ( j != 0 )
//       Response.Write("<HR>");
//     Response.Write("<P>Eintrag "+(j+1)+":</P>");
//     if ( ExEntry[0][j] ) {
//       ExEntry[0][j].sort(EntrySort);
//       ges = 0;
//       max = 1;
//       for (var k = 0; k < ExEntry[0][j].length; k++) {
//         if ( max < ExEntry[0][j][k][0] )
//           max = ExEntry[0][j][k][0];
//         ges += ExEntry[0][j][k][0];
//       }
//       Response.Write(ProtAnswer[type[j]](ExNr, j, ExEntry[0][j]));
//     }
//     else
//       Response.Write("keine Eintr�ge");
//     j += AddTypes[type[j]](ExNr, j);
//     j++;
//   }

// /*function Bar(l, col)
// {
//   return '<img src="col/col'+col+'.gif" width="'+Math.round(l)+'" height=10>';
// }*/

// function Bar(n, col)
// {
//   return '<img src="col/col'+col+'.gif" width="'+Math.round(n*maxBar/User.length)+'" height=10>';
// }

// function Abrev(s, max)
// {
//   if ( s.length < max )
//     return s;
//   s = s.substr(0, max-4);
//   var i = s.lastIndexOf(" ");
//   if ( i != -1 && i > max-15 )
//     s = s.substr(0, i);
//   return s += " ...";
// }


// // MCR - Callback ---------------------------------------------------

// function ProtMCR(nr, j, entry)
// {
//   var s = '<TABLE>';
//   if ( EF_MExt[nr][j][0] == "\x01MCS" ) // MCS
//   {
//     doc.writeln("<NOBR>", EF_MExt[nr][j][2]);
//     for (var j = 0; j < EF_MExt[nr][j][1]; j++)
//       doc.writeln(j != 0 ? EF_MExt[nr][j][4] : "",
//         '<INPUT type="radio" name=a"', nr, '" id=a', nr, '_', j, '>');
//     doc.writeln(EF_MExt[nr][j][3], "</NOBR>");
//   }
//   else {
//     var c = new Array();
//     for (var k = 0; k < EF_MExt[nr][j].length; k++)
//       c[k] = 0;
//     for (var k = 0; k < entry.length; k++)
//       c[entry[k][1].substr(4)-1] = entry[k][0];
//     for (var i = 0; i < EF_MExt[nr][j].length; i++)
//       s += '<TR><TD>'+Abrev(EF_MExt[nr][j][i], 60)+'</TD><TD><NOBR>'+
//            Bar(c[i]/**maxBar/max*/, d[j][i] == 1 ? 1 : 0)+
//            ' '+(c[i] ? c[i] : '-')+'</NOBR></TD></TR>';
//     s += '<TR><TD>[nicht bearbeitet]</TD><TD><NOBR>'+
//            Bar((User.length-ges)/**maxBar/max*/, 3)+
//            ' '+(User.length-ges ? User.length-ges : '-')+'</NOBR></TD></TR>';
//   }
//   return s+'</TABLE>';
// }

// function AddTypesMCR(nr, i)
// {
//   return 0;
// }

// function ProtMCP(nr, j, entry)
// {
//   return "noch nicht implementiert MCP"
// }

// // MCX - Callbacks --------------------------------------------------

// function ProtMCX(nr, j, entry)
// {
//   var s = '';
//   var c = new Array();
//   for (var k = 0; k < EF_MExt[nr][j].length; k++)
//     c[k] = 0;
//   for (var k = 0; k < entry.length; k++) {
//     arr = entry[k][1].split(",");
//     for (var l = 0; l < arr.length; l++)
//       c[l] += (arr[l] == 1) ? entry[k][0] : 0;
//   }
//   var max = 1;
//   for (var k = 0; k < c.length; k++)
//     if ( c[k] )
//       if ( max < c[k] )
//         max = c[k];
//   s += '<TABLE>';
//   for (var i = 0; i < EF_MExt[nr][j].length; i++)
//     s += '<TR><TD>'+Abrev(EF_MExt[nr][j][i], 60)+'</TD><TD><NOBR>'+
//          Bar(c[i]/**maxBar/ges*/, d[j][i] == 1 ? 1 : 0)+
//          Bar((ges-c[i])/**maxBar/ges*/, 2)+
//          ' '+(c[i] ? c[i] : '-')+'</NOBR></TD></TR>';
//   s += '<TR><TD>[nicht bearbeitet]</TD><TD><NOBR>'+
//          Bar((User.length-ges)/**maxBar/max*/, 3)+
//          ' '+(User.length-ges ? User.length-ges : '-')+'</NOBR></TD></TR>';
//   return s+'</TABLE>';
// }

// function AddTypesMCX(nr, i)
// {
//   return 0;
// }


// function ProtVR(nr, j, entry)
// {
//   return "noch nicht implementiert VR"
// }

// function ProtMEMO(nr, j, entry)
// {
//   return "noch nicht implementiert MEMO"
// }

// function ProtST(nr, j, entry)
// {
//   return "noch nicht implementiert ST"

// }

// // LT - Callbacks ---------------------------------------------------

// function ProtLT(nr, j, entry)
// {
//   var s = '<TABLE>';
//   for (var k = 0; k < entry.length; k++)
//     s += '<TR><TD>'+Abrev(entry[k][1].replace(/_/g," "), 60)+'</TD><TD><NOBR>'+
//          Bar(entry[k][0]/**maxBar/max*/, CheckLT(d[j], entry[k][1]) >= 0.99 ? 1 : 0)+
//          ' '+(entry[k][0] ? entry[k][0] : '-')+'</NOBR></TD></TR>';
//   s += '<TR><TD>[nicht bearbeitet]</TD><TD><NOBR>'+
//          Bar((User.length-ges)/**maxBar/max*/, 3)+
//          ' '+(User.length-ges ? User.length-ges : '-')+'</NOBR></TD></TR>';
//   return s+'</TABLE>';
// }

// function CheckKlLT(nr, i, d_)
// {
//   var c = 0;
//   if ( CheckLT(d_, GetElA(i).value) >= 0.99 ) {
//     c = 100;
//     GetElA(i).value = GetLTBestSolve(d_, GetElA(i).value);
//   }
//   return c;
// }

// function AddTypesLT(nr, i)
// {
//   return 0;
// }

// function ProtTF(nr, j, entry)
// {
//   return "noch nicht implementiert TF"
// }

// // TFM - Callbacks ---------------------------------------------------

// function CheckTFMProt(d_, s)
// {
//   for (var k = 1; k < d_.length; k++)
//     for (var i = 0; i < d_[k].length; i++)
//       if ( StrCmp(s, d_[k][i]) >= 0.9 )
//         return 1;
//   return 0;
// }

// function ProtTFM(nr, j, entry)
// {
//   var cont = new Array();
//   for (var k = 0; k < entry.length; k++) {
//     // feld trennen
//     var s = entry[k][1].replace(/_/g, " ").replace(/\r/g, " ").replace(/\n/g, " ");
//     if ( s.indexOf(";") == -1 ) {
//       /(\s*)(.*)(\s*),*(.*),\2([^;]*)$/.exec(s);
//       s = RegExp.$2+(RegExp.$4 != '' ? ','+RegExp.$4 : '');
//     }
//     else
//     {
//       /(\s*)(.*)(\s*);(.*),\2([^;]*)$/.exec(s);
//       s = RegExp.$2+';'+RegExp.$4;
//     }
//     var tok = s.split(";");
//     for (var l = 0; l < tok.length; l++)
//       AddList1(cont, CivilizeBlanks(tok[l]), entry[k][0]);
//   }
//   var s = "<TABLE>";
//   for (var v in cont)
//     s += '<TR><TD>'+Abrev(v, 60)+'</TD><TD><NOBR>'+
//          Bar(cont[v]/**maxBar/max*/, CheckTFMProt(d[j], v))+
//          ' '+(cont[v] ? cont[v] : '-')+'</NOBR></TD></TR>';
//   s += '<TR><TD>[nicht bearbeitet]</TD><TD><NOBR>'+
//          Bar((User.length-ges)/**maxBar/max*/, 3)+
//          ' '+(User.length-ges ? User.length-ges : '-')+'</NOBR></TD></TR>';
//   return s+'</TABLE>';
// }


// function AddTypesTFM(nr, i)
// {
//   return 0;
// }

// function ProtTLS(nr, j, entry)
// {
//   return "noch nicht implementiert TLS"
// }

// function ProtTLM(nr, j, entry)
// {
//   return "noch nicht implementiert TLM"
// }

// function ProtSLS(nr, j, entry)
// {
//   return "noch nicht implementiert SLS"
// }

// function ProtSLM(nr, j, entry)
// {
//   return "noch nicht implementiert SLM"
// }

// function ProtZO(nr, j, entry)
// {
//   return "noch nicht implementiert ZO"
// }

// // FZO - Callbacks ---------------------------------------------------

// var Names = new Array();
// var FZOnTarget = new Array();
// var FZOVar = new Array();
// var FZOVarTar = new Array();
// var FZOVarInfo = new Array();
// var FZOVarCoord = new Array();
// var FZOVarText = new Array();
// var FZOVarSubType = new Array();
// var FZOVarListIndex = new Array();
// var FZOVarValue = new Array();

// var FZO_f = 4;
// var FZO_m = 8;
// var FZO_u = 32;
// var FZO_fm = FZO_f + FZO_m;

// var HardCheck = new Array(HardCheckTLS, HardCheckTF, HardCheckTLM, HardCheckTFM);
// var ClearM = new Array(ClearMTLS, ClearMTF, ClearMTLM, ClearMTFM);
// var HardCheckSolveText = new Array(HardCheckSolveTextTLS, HardCheckTF, HardCheckTLM, HardCheckTFM);

// function AddList1(list, n, val)
// {
//   if ( !list[n] )
//     list[n] = val;
//   else
//     list[n] += val;
// }

// function AddList2(list, n1, n2, val)
// {
//   if ( !list[n1] )
//     list[n1] = new Array();
//   if ( !list[n1][n2] )
//     list[n1][n2] = val;
//   else
//     list[n1][n2] += val;
// }

// var HardCheck = new Array(HardCheckTLS, HardCheckTF, HardCheckTLM, HardCheckTFM);

// function ProtFZO(nr, j, entry)
// {
// //function PrintFZO(nr, nx, names, subType, dim, fzoVar)
//   var mx = EF_MExt[nr][j];
//   nx = mx[0];
//   names = mx[1];
//   if ( mx.length <= 2 || !mx[2].length )
//     SubType[j] = 0;
//   else
//     for (var i = 0; i < mx[2].length; i++)
//       SubType[i+j] = mx[2][i];
//   dim = mx.length <= 3 ? -1 : mx[3];
//   cols = Math.floor(SubType[nr] / 2);
//   SubType[j] %= 2;
//   Names[j] = names;
//   FZOnTarget[j] = names.length-1;
//   if ( mx.length > 4 )
//   {
//     FZOVarInfo[j] = mx[4][0];
//     FZOVar[j] = mx[4][1];
//     FZOVarTar[j] = new Array();
//     for (var i = 0; i < FZOVar[j].length; i++)
//       FZOVarTar[j][i] = new Array();
//     FZOVarCoord[j] = mx[4][2];
//     FZOVarText[j] = mx[4][3];
//     FZOVarSubType[j] = mx[4][4];
//     FZOVarListIndex[j] = mx[4][5];
//     FZOVarValue[j] = new Array();
//     for (var i = 0; i < FZOVarInfo[j][0]; i++)
//       FZOVarValue[j][i] = -1;
//   }
//   // names zusammenstauchen
//   for (var i = 0; i < names.length; i++) {
//     s = names[i];
//     s = s.replace(/ id=a(\d*) onclick="InTLS\((\d*), this\)"/g, "");
//     names[i] = s;
//   }
//   var s = "";
//   // zuordnungstabelle erzeugen
//   var fzoProtPos = new Array();
//   var fzoProtUsed = new Array();
//   for (var i = 1; i < names.length; i++) {
//     var en = ExEntry[0][j+i];
//     for (var k = 0; k < en.length; k++)
//       if ( (SubType[i+j] & FZO_m) != 0 ) { // multi target
//         var val = en[k][1].split(",");
//         for (var l = 1; l <= val[0]; l++)
//           if ( val[l] == "\x01-1" ) {
//           }
//           else if ( val[l].charCodeAt(0) == 1 ) {
//             var a = val[l].split("\x01");
//             AddList2(fzoProtPos, d[j][a[1]], i, en[k][0]);
//             if ( (""+m[j]) != "-1" )
//               AddList1(fzoProtUsed, d[j][a[1]], en[k][0]);
//           }
//           else {
//             AddList2(fzoProtPos, val[l], i, en[k][0]);
//             if ( (""+m[j]) != "-1" )
//               AddList1(fzoProtUsed, val[l], en[k][0]);
//           }
//       }
//       else { // single target
//         if ( en[k][1] == "\x01-1" ) {
//         }
//         else if ( en[k][1].charCodeAt(0) == 1 ) {
//           var a = en[k][1].split("\x01");
//           AddList2(fzoProtPos, d[j][a[1]], i, en[k][0]);
//           if ( (""+m[j]) != "-1" )
//             AddList1(fzoProtUsed, d[j][a[1]], en[k][0]);
//         }
//         else {
//           AddList2(fzoProtPos, en[k][1], i, en[k][0]);
//           if ( (""+m[j]) != "-1" )
//             AddList1(fzoProtUsed, en[k][1], en[k][0]);
//         }
//       }
//   }
//   // ausgeben
//   for (var v in fzoProtPos) {
//     s += '<TABLE><TR><TD>'+names[0];
//     for (var i = 1; i < names.length; i++)
//     {
//       // Farbe
//       if ( (SubType[j+i] & FZO_u) == 0 )
//       {
//         col = HardCheck[(SubType[j+i] >> 2) & 3](j, j+i, v, false);
//         //if ( col != 0 || fzoProtPos[v][i] )
//           s = s.substr(0, s.length-1)+' background="col/col'+col+'.gif">';
//       }
//       // Wert
//       s += fzoProtPos[v][i] ? fzoProtPos[v][i] : 0;
//       s += names[i];
//     }
//     if ( (""+m[j]) != "-1" )
//       s += "[nicht zugeordnet]: "+(User.length-fzoProtUsed[v]);
//     s += '</TD><TD> </TD><TD>'+v+'</TD></TR></TABLE>'
//   }
//   return s;
// }

// function AddTypesFZO(nr, i)
// {
//   return EF_MExt[nr][i][1].length-1;
// }

// // check fzo

// function HardCheckTLM(fzoNr, nr, value, addMissing)
// {
//   for (var i = 0; i < d[nr][2].length; i++)
//     if ( Entry2CmpText(value) == Entry2CmpText(d[nr][2][i]) )
//       return 1;
//   return 0;
// }

// function HardCheckTLS(fzoNr, nr, value, addMissing)
// {
//   if ( Entry2CmpText(value) == Entry2CmpText(d[nr][2]) )
//     return 1;
//   return 0;
// }

// function HardCheckTF(fzoNr, nr, value, addMissing)
// {
//   for (i = 0; i < d[nr].length; i++)
//     if ( StrCmp(val, d[nr][i]) >= 0.9 )
//       return 1;
//   return 0;
// }

// function HardCheckTFM(fzoNr, nr, value, addMissing)
// {
//   Check[nr] = new Array("check");
//   value = new Array(value.replace(/\s*\[\+\]\s*/g, "").replace(/\s*\[-\]\s*/g, "").replace(/\s*\[=\]\s*/g, ";").replace(/;+/g, ";"));  // restliche evtl. vorhandene Felder l�schen
//   // zerlegen in Token
//   var tok = value.replace(/\r/g, " ").replace(/\n/g, " ").split(";");
//   for (var i = 0; i < tok.length; i++)
//     m[nr][i+1] = CivilizeBlanks(tok[i]);
//   Check[nr][0] = tok.length;
//   // AbstreichFeld f�r die harten Synonymgruppen
//   var HardTFMRegoniced = new Array(); // nr. des passenden Wortes, 0 .. noch frei
//   for (var k = 0; k < d[nr].length; k++)
//     HardTFMRegoniced[k] = 0;
//   // einzelne m-Werte durchchecken
//   for (var j = 1; j < m[nr].length; j++) {
//     // Harter Wert?
//     var run = true;
//     TFSynUsed[nr][j] = 0;
//     // noch frei Syn-Listen
//     for (var k = 0; k < d[nr].length && run; k++)
//       if ( HardTFMRegoniced[k] == 0 )
//         for (i = 0; i < d[nr][k].length && run; i++)
//           if ( StrCmp(m[nr][j], d[nr][k][i]) >= 0.9 )
//           {
//             Check[nr][j] = 2;
//             HardTFMRegoniced[k] = j;
//             TFSynUsed[nr][j] = -k;
//             m[nr][j] = d[nr][k][i]; // Rechtschreibkorr.
//             run = false;
//           }
//     // bereits benutzte Syn-Listen
//     for (var k = 0; k < d[nr].length && run; k++)
//       if ( HardTFMRegoniced[k] != 0 )
//         for (i = 0; i < d[nr][k].length && run; i++)
//           if ( StrCmp(m[nr][j], d[nr][k][i]) >= 0.9 )
//           {
//             Check[nr][j] = 2;
//             TFSynUsed[nr][j] = HardTFMRegoniced[k];
//             m[nr][j] = d[nr][k][i]; // Rechtschreibkorr.
//             run = false;
//           }
//     // Var-Wert?
//     if ( run )
//       Check[nr][j] = OfferTextToVarsF(fzoNr, nr, m[nr][j], j) ? 1 : 0;
//   }
//   if ( addMissing )
//     for (var k = 0; k < d[nr].length; k++)
//       if ( HardTFMRegoniced[k] == 0 ) {
//         var j = m[nr].length;
//         m[nr][j] = d[nr][k][0];
//         Check[nr][j] = 2;
//         TFSynUsed[nr][j] = -k;
//       }
// }

// // MCP - Callbacks --------------------------------------------------

// function WriteMCP(nr, i, doc)
// {
//   doc.writeln('<SELECT name="a', i, '" size="1">\n');
//   for (var j = 0; j < EF_MExt[nr][i].length; j++)   // ', (j==0) ? ' selected' : '', '
//     doc.writeln('  <OPTION>', EF_MExt[nr][i][j], '</OPTION>\n');
//   doc.writeln('</SELECT>\n');
// }

// function CheckKlMCP(nr, i, d_)
// {
//   var c = 0;
//   if ( GetElA(i).options.selectedIndex == d_ )
//     c = 100;
//   return c;
// }

// /*function SolveMCP(nr, i, doc)
// {
//   eval('top.data.display.document.forms[0].a'+(i*1)+'.options.selectedIndex = '+EF_D[nr][i]);
// }*/

// function SolveTextKlMCP(nr, i, d_)
// {
//    return "<B>"+EF_MExt[nr][i][d_]+"</B>";
// }

// function AddTypesMCP(nr, i)
// {
//   return 0;
// }

// // VR - Callbacks ---------------------------------------------------

// function WriteVR(nr, i, doc)
// {
//   doc.writeln('<P>\n',
//     '  <INPUT type="radio" name="a', i, '"> '+EF_MExt[nr][i][0]+' &nbsp; &nbsp; &nbsp;\n',
//     '  <INPUT type="radio" name="a', i, '"> '+EF_MExt[nr][i][1]+'\n',
//     '</P>\n');
// }

// function CheckKlVR(nr, i, d_)
// {
//   var c = 0;
//   if ( GetElAI(i, 1-d_).checked )
//     c = 100;
//   return c;
// }

// /*
// function SolveVR(nr, i, doc)
// {
//   eval('top.data.display.document.forms[0].a'+(i*1)+'['+(1-EF_D[nr][i])+'].click()');
// }*/

// function SolveTextKlVR(nr, i, d_)
// {
//   var s;
//   if ( d_ == 1 )
//     s = '<INPUT type="radio" checked disabled> <B>'+EF_MExt[nr][i][0]+'</B> &nbsp; &nbsp; &nbsp;\n'+
//         '<INPUT type="radio" disabled> '+EF_MExt[nr][i][1]+'\n';
//   else
//     s = '<INPUT type="radio" disabled> '+EF_MExt[nr][i][0]+' &nbsp; &nbsp; &nbsp;\n'+
//         '<INPUT type="radio" checked disabled> <B>'+EF_MExt[nr][i][1]+'</B>\n';
//   return '<P>\n'+s+'</P>\n';
// }

// function AddTypesVR(nr, i)
// {
//   return 0;
// }

// function FilledVR(nr, i, doc)
// {
//   for (var j = 0; j < 2; j++)
//     if ( GelElAI(i, j).checked )
//       return true;
//   return false;
// }

// // MEMO - Callbacks ---------------------------------------------------

// function WriteMEMO(nr, i, doc)
// {
//   doc.writeln('<textarea name="a', i, '" rows="', EF_MExt[nr][i][1],
//     '" cols="', EF_MExt[nr][i][0], '" wrap="virtual"></textarea>');
// }

// function CheckKlMEMO(nr, i, d_)
// {
//   if ( d_.length > 1 ) {
//     s = GetElA(i).value;
//     var max = 0;
//     for (var i1 = 1; i1 < d_.length && max != 1; i1++) {
//       var nOk = 0;
//       for (var j = 0; j < d_[i1].length; j++)
//         for (var k = 0; k < d_[i1][j].length; k++)
//           if ( s.indexOf(d_[i1][j][k]) != -1 ) {
//             nOk++;
//             break;
//           }
//       if ( nOk/d_[i1].length > max )
//         max = nOk/d_[i1].length;
//     }
//     var CheckTok = (max > 0.99) ? true : false;
//     return max*100;
//   }
//   else
//     return 100;
// }

// /*function SolveMEMO(nr, i, doc)
// {
//   GetEl('a'+i).value = HTML2Text(EF_Solution[nr]);
// }*/

// function SolveTextKlMEMO(nr, i, d_)
// {
//   return '<B>'+d_[0]+'</B>';
// }

// function AddTypesMEMO(nr, i)
// {
//   return 0;
// }

// // ST - Callbacks ---------------------------------------------------

// function WriteST(nr, i, doc)
// {
//   doc.writeln('<input type="text" name="a', i, '" size="', EF_MExt[nr][i], '">');
// }

// function CheckKlST(nr, i, d_)
// {
//   var c = 0, v = 0, v1, j1;
//   var s = GetElA(i).value;
//   for (var j = 2; j < d_.length; j++)
//     if ( d_[j].substring(0,1) == '!' )
//     {
//       if ( StrCmp(s, d_[j].substring(1)) > 0.99 )
//       {
//         v = 0;
//         break;
//       }
//     }
//     else
//     {
//       v1 = StrCmp(s, d_[j]);
//       if ( v1 > v )
//       {
//         v = v1;
//         j1 = j;
//       }
//     }
//   if ( v > d_[1] )
//   {
//     GetElA(i).value = HTML2Text(d_[j1]);
//     c = 100;
//   }
//   return c;
// }

// /*function SolveST(nr, i, doc)
// {
//   var d1 = EF_D[nr][i];
//   for (var j = 0; j < d1.length; j++)
//     if ( d1[j].substring(0,1) != '!' )
//     {
//       GetEl('a'+i).value = HTML2Text(d1[j]);
//       break;
//     }
// }*/

// function SolveTextKlST(nr, i, d_)
// {
//   var j;
//   for (j = 2; j < d_.length; j++)
//     if ( d_[j].charAt(0) != "!" )
//       break;
//   return '<B>'+GetBestInList(GetElA(i).value, d_.slice(j, d_.length))+'</B>';
// }

// function AddTypesST(nr, i)
// {
//   return 0;
// }

// function FilledST(nr, i, doc)
// {
//   return (GetEl('a'+i).value != "") ? true : false;
// }

// // TF - Callbacks ---------------------------------------------------

// function WriteTF(nr, i, doc)
// {
//   PrintTF(i, EF_MExt[nr][i][0], EF_MExt[nr][i][1]);
// }

// function CheckKlTF(nr, i, d_)
// {
//   for (var j = 0; j < d_.length; j++)
//     if ( StrCmp(m[i], d_[j]) >= 0.9 )
//     {
//       GetElA(i).value = HTML2Text(d_[j]);
//       return 100;
//     };
//   return 0;
// }

// /*function SolveTF(nr, i, doc)
// {
//   SetT(i, EF_D[nr][i][0]);
// }*/

// function SolveTextKlTF(nr, i, d_)
// {
//   return '<B>'+GetBestInList(m[i], d_)+'</B>';
// }

// function AddTypesTF(nr, i)
// {
//   return 0;
// }

// /*
// // TS - Callbacks ---------------------------------------------------

// function WriteTS(nr, i, doc)
// {
//   PrintTS(i, EF_MExt[nr][i][0], EF_MExt[nr][i][1]);
// }

// function CheckKlTS(nr, i, doc)
// {
//   var d1 = EF_D[nr][i];
//   for (var j = 0; j < d1.length; j++)
// //    if ( StrCmp(CivilizeBlanks(m[i]), d1[j]) >= 0.9 )
//     if ( CivilizeBlanks(m[i]).toUpperCase() == d1[j].toUpperCase() )
//       {
//       SetT(i, d1[j]);
//       return 100;
//     }
//   return 0;
// }

// function SolveTS(nr, i, doc)
// {
//   SetT(i, EF_D[nr][i][0]);
// }

// function AddTypesTS(nr, i)
// {
//   return 0;
// }

// // TM - Callbacks ---------------------------------------------------

// function WriteTM(nr, i, doc)
// {
//   PrintTM(i, EF_MExt[nr][i][0], EF_MExt[nr][i][1]);
//   m[i] = new Array();
// }

// function CheckKlTM(nr, i, doc)
// {
//   return DoCheckTM(i) * 100;
// }

// function SolveTM(nr, i, doc)
// {
//   DoSolveTM(i);
// }

// function AddTypesTM(nr, i)
// {
//   return 0;
// }*/

// // TLS - Callbacks ---------------------------------------------------

// function WriteTLS(nr, i, doc)
// {
//   PrintTLS(i, EF_MExt[nr][i][0], EF_MExt[nr][i][1], EF_MExt[nr][i][2], EF_MExt[nr][i][3]);
// }

// function CheckKlTLS(nr, i, d_)
// {
//   return CheckTLS(i) * 100;
// }

// /*function SolveTLS(nr, i, doc)
// {
//   SetTLS(i, EF_D[nr][i][2]);
// }*/

// function SolveTextKlTLS(nr, i, d_)
// {
//   return '<B>'+SolveTextTLS(i, EF_MExt[nr][i][0], EF_MExt[nr][i][1], EF_MExt[nr][i][2], EF_MExt[nr][i][3])+'</B>';
// }

// function AddTypesTLS(nr, i)
// {
//   return 0;
// }

// // TLM - Callbacks ---------------------------------------------------

// function WriteTLM(nr, i, doc)
// {
//   PrintTLM(i, EF_MExt[nr][i][0], EF_MExt[nr][i][1], EF_MExt[nr][i][2], EF_MExt[nr][i][3]);
// }

// function CheckKlTLM(nr, i, d_)
// {
//   return CheckTLM(i) * 100;
// }

// /*function SolveTLM(nr, i, doc)
// {
//   DoSolveTLM(i);
// }*/

// function SolveTextKlTLM(nr, i, d_)
// {
//   return '<B>'+SolveTextTLM(i, EF_MExt[nr][i][0], EF_MExt[nr][i][1], EF_MExt[nr][i][2], EF_MExt[nr][i][3])+'</B>';
// }

// function AddTypesTLM(nr, i)
// {
//   return 0;
// }

// // SLS - Callbacks ---------------------------------------------------

// function WriteSLS(nr, i, doc)
// {
//   PrintSLS(i, EF_MExt[nr][i]);
// }

// function CheckKlSLS(nr, i, d_)
// {
//   return 0;
// }

// /*function SolveSLS(nr, i, doc)
// {
//   RefreshSLS(i)
// }*/

// function SolveTextKlSLS(nr, i, d_)
// {
//   return '';
// }

// function AddTypesSLS(nr, i)
// {
//   return 0;
// }

// // SLM - Callbacks ---------------------------------------------------

// function WriteSLM(nr, i, doc)
// {
//   PrintSLS(i, EF_MExt[nr][i]);
// }

// function CheckKlSLM(nr, i, d_)
// {
//   return 0;
// }

// /*function SolveSLM(nr, i, doc)
// {
//   RefreshSLS(i)
// }*/

// function SolveTextKlSLM(nr, i, d_)
// {
//   return '';
// }

// function AddTypesSLM(nr, i)
// {
//   return 0;
// }

// // ZO - Callbacks ---------------------------------------------------

// function WriteZO(nr, i, doc)
// {
//   PrintZO(i, EF_MExt[nr][i][0], EF_MExt[nr][i][1]);
// }

// function CheckKlZO(nr, i, d_)
// {
//   var c = 0;
//   for (j = i+1; j <= i + EF_MExt[nr][i][0].length; j++)
//     if ( m[j] == d[j][2] )
//       c += 100 / EF_MExt[nr][i][0].length;
//   return c;
// }

// /*function SolveZO(nr, i, doc)
// {
//   ResetSLS(i);
//   for (var j = i+1; j <= i + EF_MExt[nr][i].length; j++)
//     SetTLS(j, d[j][2]);
//   RefreshSLS(i);
// }*/

// function SolveTextKlZO(nr, i, d_)
// {
//   return SolveTextZO(i);
// }

// function AddTypesZO(nr, i)
// {
//   return EF_MExt[nr][i][0].length;
// }

// //---------
// /*
// for (var topic = 0; topic < Name.length; topic++)
//   if ( Media[M_AUFGABE][topic] ) {
//     for (var i = 0; i < Media[M_AUFGABE][topic].length; i++) {
//       Response.Write('<TABLE border=0 width="100%" bgcolor="#CCCCCC"><TR><TD><B>'+Media[M_AUFGABE][topic][i][0]+"</B></TD></TR></TABLE>");
//       if ( ExEntry[topic][i][0].length > 0 || ExEntry[topic][i][1].length > 0 ) {
//         for (var j = 0; j < ExEntry[topic][i][0].length || j < ExEntry[topic][i][1].length; j++)
//           if ( ExEntry[topic][i][0][j] || ExEntry[topic][i][1][j] ) {
//             Response.Write("<B>Eintrag "+j+":</B><BR>");
//             if ( ExEntry[topic][i][0][j] ) {
//               Response.Write("&nbsp; Solve:<BR>");
//               ExEntry[topic][i][0][j].sort(EntrySort);
//               for (var k = 0; k < ExEntry[topic][i][0][j].length; k++)
//                 Response.Write("&nbsp; &nbsp; ("+ExEntry[topic][i][0][j][k][0]+"x) "+ExEntry[topic][i][0][j][k][1].replace(/_/g, " ")+"<BR>");

//             }
//             if ( ExEntry[topic][i][1][j] ) {
//               Response.Write("<span class=systerror>&nbsp; SystError:<BR>");
//               ExEntry[topic][i][1][j].sort(EntrySort);
//               for (var k = 0; k < ExEntry[topic][i][1][j].length; k++)
//                 Response.Write("&nbsp; &nbsp; ("+ExEntry[topic][i][1][j][k][0]+"x) "+ExEntry[topic][i][1][j][k][1].replace(/_/g, " ")+"<BR>");
//               Response.Write("</span>");
//             }
//           }
//       }
//       else
//         Response.Write("-<BR>");
//     }
//   }*/
// %>
// <SCRIPT LANGUAGE="JavaScript"><!-- Hide script
//   window.open("<% Response.Write(ExercisePath+Media[M_AUFGABE][ExTopic][ExI][0]) %>", "display");
// // End script hiding --></SCRIPT>
// </form>
// </body>
// </html>
// <%
//  fs=0;
// %>
