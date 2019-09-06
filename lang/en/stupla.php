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
 * Language strings for english.
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// General strings.
$string['stupla'] = 'stupla';

$string['modulenameplural'] = 'Studierplatz2000s';
$string['modulename'] = 'Studierplatz2000';

$string['modulename_help'] = 'The Studierplatz2000-Module enables the teacher to embed a Studierplatz(-Environment) into Moodle so that it can benefit from

* storing / restoring user data
* evaluate using statistics
* user administration / access restriction

Information about Studierplatz can be found under  '.'
[http://studierplatz2000.tu-dresden.de](http://studierplatz2000.tu-dresden.de "Studierplatz Homepage")';
$string['modulename_link'] = 'mod/stupla/view';

$string['pluginadministration'] = 'Studierplatz2000 administration';
$string['pluginname'] = 'Studierplatz2000';

// Edit form.
$string['nameadd'] = 'Name of Studierplatzes';
$string['nameadd_help'] = 'The name can be given explicitely or automatically derived from teh uploaded/referenced files.';

$string['textsourcefilename'] = 'Use file name';
$string['textsourcefilepath'] = 'Use file path';
$string['textsourcedatajs'] = 'From Studierplatz';
$string['textsourcespecific'] = 'Specific text';

$string['alwaysopen'] = 'Always open';
$string['neverclosed'] = 'Never closed';
$string['specifictime'] = 'Specific time';

$string['sessionhandling'] = 'Session handling / subusers';
$string['sessions'] = 'Sessions';
$string['sessionsresumable_onlyone'] = 'Only one session which will always be continued.';
$string['sessionsresumable_primaryone'] = 'Primary only one session, but users can explicitely start another sessions.';
$string['sessionsresumable_primarymulti'] = 'Primary multiple sessions, but users can continue existing ones explicitely.';
$string['sessionsresumable_onlymulti'] = 'With every start a new session.';

$string['reference_stupla'] = 'Reference Studierplatz2000';
$string['reference_intern_extern'] = 'Type of reference';
$string['reference_intern_extern_help'] = 'The studierplatz itself can be referenced internal or external.  '.'
If you choose *internal* then you have to upload the complete studierplatz and mark the start file as main file.  '.'
If you choose *external* then the studierplatz has to reside at a regular web folder of your server and the start fil ewill be referenced as url (via *http://...*).  '.'
Using external referencing loads the web pages from experience noticable faster, but of course you need (access) separate web space at your server.';
$string['intern'] = 'Internal';
$string['extern'] = 'External';
$string['location'] = 'Startpage';
$string['reference_int'] = 'Internal reference';
$string['reference_int_help'] = 'Upload your complete studierplatz (this means the Data-folder, comp-folder and may be a separate start file) and mark the start file as main file.  '.'
**Hint:** You may upload the studierplatz as zip-file and then unzip (right click at the uploaded zip-file).
The start file can be the ***Start.htm*** or ***Start2.htm*** from within comp-folder (then the studierplatz start with the usual splash screen) or a **name*_start.htm***  (then the studierplatz opens directly with the first page).';
$string['reference_ext'] = 'External reference';
$string['reference_ext_help'] = 'Please enter here the complete URL of the start page of your studierplatz, e.g. *http://mymoodle.tu-dresden.de/stuplas/Bio2/comp/Start.htm*.  '.'
The start page can be the ***Start.htm*** or ***Start2.htm*** from within comp-folder (then the studierplatz start with the usual splash screen) or a **name*_start.htm***  (then the studierplatz opens directly with the first page).';
$string['filename'] = 'File name';
$string['reference'] = 'Url of start page';
$string['localpath'] = 'Locale target path';
$string['localpath_help']  = 'This field can be left empty. But when you use external referencing sometimes there are problems accessing several files.  '.'
In this cases enter here the complete path to the target folder as your (server) operationg system expects, e.g.
***/usr/moodle/stuplas/Bio/comp*** or ***c:\Inetpub\stuplas\Bio\comp***.';

// Subuser.
$string['subuser'] = 'Subuser';
$string['useSubuser'] = 'Use subuser';
$string['useSubuser_help'] = 'If several persons shall use a Studierplatz via only one login
(i.e. also guest login), it makes sense to collect additional data about the persons.  '.'
Here you can define which additional data has to be entered.
When somebody starts the Studierplatz, then he/she will be requested to enter these data.';
$string['subuserLogData'] = 'Data that shall be collected at the start:';
$string['subuserFieldname'] = 'Description';
$string['subuserFieldmust'] = 'Required';
$string['subuserFieldtype'] = 'Type';
$string['subuserFieldcomment'] = 'Comment';
$string['firstname'] = 'First name';
$string['lastname'] = 'Last name';
$string['birthday'] = 'Birthday';
$string['dd.mm.yyyy'] = '(dd.mm.yyyy)';
$string['subuser_new'] = 'Here you can define a new data field.';
$string['textfield'] = 'Textfield';
$string['datefield'] = 'Date';
$string['combofield'] = 'Combobox';
$string['addsufield'] = 'Add new data field';
$string['delsufield'] = 'Erase data field';
$string['su_combodata'] = 'Entries of combobox';
$string['subuserdata'] = 'Data of subusers';
$string['subuserdata_help'] = 'For these data you can define:

* the description (short name)
* whether the entry is required or optional
* the type (free text field, date field or combobox)
* an explaining comment, that is displayed to the users

Firts name, last name and birthday are provided by default
(if you don\'t want to collect these data simply delete these field via the "Del"-button at the beginning of the line).  '.'
To define a new data field enter the appropriate values in the lower line and confirm by "Add"-button.';
$string['combodef'] = 'Define comboboxes';
$string['combodef_help'] = 'To define comboboxes simply select the type "Combobox".  '.'
Now you can enter the values within the new opening text area below, that will be displayed as entries of the combobox.  '.'
Every line represents an entry';
$string['subuserprotname'] = 'Display within protocol';
$string['subuserprotname_help'] = 'Here you can define from wich subuser data the display name of a person is composed.
Simply enter the names of the desired data fields surrounded by \'$\'.  '.'
If you have e.g. the data fields \'First name\', \'Last name\' and \'Form\' and you provide for the display
> *$Last name$, $First name$, ($Form$)*

then the users will be displayed as e.g.
> Mueller, Nina (4b)

.  '.'
If you insert *$USER$*, then you get the name of the "main user" und *$SESSION$* deliveres the session-number/id.';

// Index page.
$string['subusers'] = 'Subusers';
$string['sessions'] = 'Sessions';
$string['archivedusers'] = 'Archived users';

// Start page.
$string['start'] = 'Start';
$string['continuesessions'] = 'Continue existing sessions ...';
$string['resume'] = 'Continue';
$string['newsession'] = 'Start new session explicitely';

// Subuserlogin.
$string['fillFields'] = 'Please fill in the form fields.';
$string['dateComment'] = '(dd.mm.yyyy)';
$string['loginButton'] = 'Next';
$string['combo_please_select'] = 'Please choose!';
$string['missingField'] = 'Please fill in the form field \'{$a}\'.';
$string['formatField'] = 'Please check the form field \'{$a}\'. It does not match the expected format.';
$string['missingSelect'] = 'Please select in the form field \'{$a}\' a valid option.';

// Protocol.
$string['protocol'] = 'Protocol';

$string['user_list'] = 'User list';
$string['login_list'] = 'Login list';
$string['walkthrough'] = 'User history';
$string['questionaire'] = 'Questionaire';
$string['exercises'] = 'Exercises';
$string['exercise_states'] = 'Exercise states';
$string['exercise_overview'] = 'Execise overview';
$string['statistics'] = 'Statistics';

$string['displayonpage'] = 'Display (HTML)';
$string['downloadtext'] = 'Text-Export (CSV)';
$string['downloadexcel'] = 'Excel-Export';
$string['refresh'] = 'Refresh';

// User list.
$string['logins'] = 'Logins';
$string['fb_reqs'] = 'Questionaires';
$string['texts'] = 'Texts';
$string['texts_time'] = 'Time in texts';
$string['media'] = 'Media';
$string['exercises'] = 'Exercises';
$string['time_total'] = 'Time total';

$string['use_as_startdate'] = 'Use this date as \'from:\'-date to limit the users.';
$string['use_as_enddate'] = 'Use thsi date as \'to:\'-date to limit the users.';

$string['delete'] = 'Delete';
$string['delete_explain'] = 'the marked sessions';
$string['deleteempty'] = 'No session selected for deletion.';
$string['deleteverify'] = 'Should this {$a} sessions really be (unrecoverably) deleted?';
$string['deletedone'] = '{$a} sessions have been (unrecoverably) deleted.';
$string['cancel'] = 'Cancel';

$string['archivetag'] = 'Archive label';
$string['archive'] = 'Archive';
$string['archive_explain'] = 'the marked sessions';
$string['archiveempty'] = 'No session selected for archivation.';
$string['archiveverify'] = 'Should this {$a} sessions really be archived and removed from the list?';
$string['archiveselecttag'] = 'Please label these session(s) with a tag, so that these sessions can be easily found again in archive.';
$string['archiveselecttagnew'] = 'Create a new tag:';
$string['archiveselecttagold'] = 'or use an existing:';
$string['archivetagneeded'] = 'Please specify an archive label.';
$string['archivetagonlyone'] = 'You can specify a new archive label or select an existing, bot not both at the same time.';
$string['archivedone'] = '{$a->count} sessions have been archived with the label "{$a->label}".';
$string['displayarchive'] = 'Display archive';
$string['displayarchive_explain'] = 'for the following tag(s):';
$string['noarchivetagsselected'] = 'No archive tags selected.';
$string['leavearchive'] = 'Leave archive';
$string['leavearchive_explain'] = '';
$string['unarchive'] = 'Unarchive';
$string['unarchive_explain'] = 'the marked sessions';
$string['unarchiveempty'] = 'No session selected for unarchivation.';
$string['unarchiveverify'] = 'Should this {$a} sessions really be unarchived?';
$string['unarchivedone'] = '{$a} sessions have been unarchived.';


$string['entry'] = 'Entry';
$string['solve'] = 'Solve';
$string['systerror'] = 'SystError';

$string['exercise'] = 'Execise';
$string['state'] = 'State';
$string['solved_correct'] = 'solved corect';
$string['solved_wrong'] = 'solved wrong';
$string['unattempted'] = 'unattempted';
$string['one_ex_att'] = '{$a->count} exercise {$a->action}';
$string['mult_ex_att'] = '{$a->count} exercises {$a->action}';


$string['statistic'] = 'Statistic';
$string['code'] = 'Code';
$string['nlogin'] = 'nLogin';
$string['time'] = 't_ges';
$string['display_texts'] = 'Display test time';
$string['display_media'] = 'Display media time';
$string['display_exercises'] = 'Display exercises';
$string['display_special_exercises'] = 'Display special exercises';
$string['use_media_exercise_file_names'] = 'Use file names for media / exercises';
$string['use_exercises_first_glance'] = 'use only first attempt for exercises';
$string['apply_changes'] = 'apply changes';

// Rights.
$string['stupla:addinstance'] = 'Add a new Stupla';
$string['stupla:attempt'] = 'Use a Stupla';
$string['stupla:deleteallsessions'] = 'Delete sessions of any user';
$string['stupla:deletemysessions'] = 'Delete own sessions';
$string['stupla:preview'] = '(Preview, not used yet)';
$string['stupla:reviewallprotocols'] = 'Display protocol of any user';
$string['stupla:reviewmyprotocols'] = 'Display own protocol';

// Files.
$string['sourcefile'] = 'Sources';
