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
 * JavaScript library for the quiz module editing interface.
 *
 * @package    mod
 * @subpackage stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// sudata as JS
// name, must, type, comment, data
var suData = new Array();
var str = new Array();

// Initialise everything on the stupla settings form.
function stupla_settings_init(Y, str1, data) {
    str = str1;
    // switching og intern / extern reference
    Y.on('change', function(e) {
        var sel = Y.one('#id_refInternExtern').get('value');
        Y.one('#ref'+sel).setStyle('display', '');
        Y.one('#ref'+(1-sel)).setStyle('display', 'none');
    }, '#id_refInternExtern');
    var sel = Y.one('#id_refInternExtern').get('value');
    Y.one('#ref'+sel).setStyle('display', '');
    Y.one('#ref'+(1-sel)).setStyle('display', 'none');
    // subuser
    Y.on('change', function(e) {
        Y.one('#id_subuserPanel').setStyle('display', Y.one('#id_useSubusers').get('checked') ? '' : 'none');
    }, '#id_useSubusers');
    Y.one('#id_subuserPanel').setStyle('display', Y.one('#id_useSubusers').get('checked') ? '' : 'none');
    suData = data;
    // show
    show_suarea();
}

// Subuser area

function stupla_suArea() {
    var s = "";
    for (var i = 0; i < suData.length; i++) {
        s += "<tr><td>";
        s += '<input type="button" name="su_del_'+i+'" value="Del" onclick="su_del('+i+')" title="'+str['delsufield']+'"></td><td>'+
             '<input type="text" name="su_name_'+i+'" value="'+suData[i]['name']+'"></td><td>'+
             '<input type="checkbox" name="su_must_'+i+'"'+(suData[i]['must'] == true ? ' checked' : '')+'></td><td>'+
             '<select name="su_type_'+i+'" onchange="su_switch(this.value, '+i+')">'+
             '  <option value="text"'+(suData[i]['type'] == "text" ? ' selected' : '')+'>'+str['textfield']+'</option>'+
             '  <option value="date"'+(suData[i]['type'] == "date" ? ' selected' : '')+'>'+str['datefield']+'</option>'+
             '  <option value="combo"'+(suData[i]['type'] == "combo" ? ' selected' : '')+'>'+str['combofield']+'</option>'+
             '</select></td><td>'+
             '<input type="text" name="su_comment_'+i+'" size="50" value="'+suData[i]['comment']+'" />'+
             '</td></tr><tr id="su_combotr_'+i+'" style="display:'+((suData[i]['type'] == "combo") ? "table-row" : "none")+'">'+
             '<td colspan="4" valign="top" align="right">'+str['su_combodata']+':</td>'+
             '<td><textarea name="su_data_'+i+'" cols="40" rows="5"  class="form=textarea">'+(""+suData[i]['data']).replace(/<br>/g, "\r\n")+'</textarea>';
        s += '</td></tr>';
    }
    return s;
}

function su_switch(value, id) {
    // doppelt wg. IE vs. FF
    document.getElementById('su_combotr_'+id).style.display = (value == "combo") ? "block" : "none";
    document.getElementById('su_combotr_'+id).style.display = (value == "combo") ? "table-row" : "none";
}

function show_suarea() {
    document.getElementById('subuserFields').innerHTML =
        '<table style="border:solid 1px lightgray;border-collapse:collapse" rules="all">'+
        '    <tr style="background-color:lightgray">'+
        '        <th>&nbsp;</td>'+
        '        <td>'+str['subuserFieldname']+'</td>'+
        '        <td>'+str['subuserFieldmust']+'</td>'+
        '        <td>'+str['subuserFieldtype']+'</td>'+
        '        <td>'+str['subuserFieldcomment']+'</td>'+
        '    </tr>'+
        stupla_suArea()+
        ' <tr><td colspan="5"><i>'+str['subuser_new']+':</i></td></tr>'+
        '  <tr>'+
        '    <td>&nbsp;</td>'+
        '  <td><input type="text" name="newsu_name" size="14" value="" /></td>'+
        '  <td><input type="checkbox" name="newsu_must" /></td>'+
        '  <td>'+
        '    <select name="newsu_type"  onchange="su_switch(this.value, \'new\')">'+
        '        <option value="text">'+str['textfield']+'</option>'+
        '        <option value="date">'+str['datefield']+'</option>'+
        '        <option value="combo">'+str['combofield']+'</option>'+
        '    </select>'+
        '     </td>'+
        '  <td>'+
        '    <nobr><input type="text" name="newsu_comment" size="45" value=""/>'+
        '    <input type="button" name="newsu_add" value="Add" onclick="su_addnew()" title="'+str['addsufield']+'"></nobr>'+
        '  </td>'+
        ' </tr>'+
        ' <tr id="su_combotr_new" style="display:none">'+
        '  <td colspan="4" valign="top" align="right">'+str['su_combodata']+':<br></td>'+
        '  <td><textarea name="newsu_data" cols="40" rows="5"  class="form=textarea"></textarea></td>'+
        ' </tr>'+
        '</table>';
}

function su_del(id)
{
    su_makesuData();
    suData.splice(id, 1);
    show_suarea();
}

function su_addnew()
{
    su_makesuData();
    var newSu = new Array();
    newSu['name'] = document.forms.mform1['newsu_name'].value;
    newSu['must'] = document.forms.mform1['newsu_must'].checked;
    newSu['type'] = document.forms.mform1['newsu_type'].value;
    newSu['comment'] = document.forms.mform1['newsu_comment'].value;
    newSu['data'] = document.forms.mform1['newsu_data'].value;
    suData[suData.length] = newSu;
    show_suarea();
}

function su_makesuData()
{
    for (var i = 0; i < suData.length; i++)
    {
        suData[i]['name'] = document.forms.mform1['su_name_'+i].value;
        suData[i]['must'] = document.forms.mform1['su_must_'+i].checked;
        suData[i]['type'] = document.forms.mform1['su_type_'+i].value;
        suData[i]['comment'] = document.forms.mform1['su_comment_'+i].value;
        suData[i]['data'] = document.forms.mform1['su_data_'+i].value;
    }
}