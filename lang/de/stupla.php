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
 * Language strings for german.
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// General strings.
$string['stupla'] = 'stupla';

$string['modulenameplural'] = 'Studierplätze2000';
$string['modulename'] = 'Studierplatz2000';

$string['modulename_help'] = 'Das Studierplatz2000-Modul ermöglicht es, studierplätze in Moodle einzubinden. Der Nutzen ist

* Speichern/Laden der Studierplatz-Nutzerdaten/Verläufe
* Auswertung der Protokolle/Abläufe
* Zugangsbeschränkung/Nutzer-Administration

Informationen über den Studierplatz finden sie unter  '.'
[http://studierplatz2000.tu-dresden.de](http://studierplatz2000.tu-dresden.de "Studierplatz Homepage")';
$string['modulename_link'] = 'mod/stupla/view';

$string['pluginadministration'] = 'Studierplatz2000 Administration';
$string['pluginname'] = 'Studierplatz2000';

// Edit form.
$string['nameadd'] = 'Name des Studierplatzes';
$string['nameadd_help'] = 'Der Name kann explizit angegeben werden oder automatisch aus den hochgeladenen/referenzierten Dateien ermittelt werden.';

$string['textsourcefilename'] = 'Dateinamen verwenden';
$string['textsourcefilepath'] = 'Dateipfad verwenden';
$string['textsourcedatajs'] = 'aus dem Studierplatz';
$string['textsourcespecific'] = 'Spezifischer Text';

$string['alwaysopen'] = 'Jederzeit';
$string['neverclosed'] = 'Nie';
$string['specifictime'] = 'Spezifische Zeit';

$string['sessionhandling'] = 'Sessionhandling / Unternutzer';
$string['sessions'] = 'Sessions';
$string['sessionsresumable_onlyone'] = 'Nur eine Session, diese wird immer fortgesetzt.';
$string['sessionsresumable_primaryone'] = 'Eigentlich nur eine Session, explizit können aber weitere gestartet werden.';
$string['sessionsresumable_primarymulti'] = 'Eigentlich mehrere Sessions, explizit können aber auch Sessions fortgesetzt werden.';
$string['sessionsresumable_onlymulti'] = 'Bei jedem Start eine neue Session.';

$string['reference_stupla'] = 'Studierplatz referenzieren';
$string['reference_intern_extern'] = 'Art der Referenzierung';
$string['reference_intern_extern_help'] = 'Der Studierplatz selber kann intern oder extern referenziert (angegeben) werden.  '.'
Bei der **internen** Referenzierung wird der komplette Studierplatz in Moodle hochgeladen und dann die Start-Datei angegeben (als Hauptdatei).  '.'
Bei der **externen** Referenzierung liegt der Studierplatz auf einen normalen Webspace/Webverzeichnis des Servers und es wird nur (via *http://...*) die Startseite angegeben.  '.'
Bei der externen Referenzierung erfolgt der Seitenaufbau erfahrungsgemäß schneller als bei der internen, allerdings benötigt man natürlich (Zugang zum) separaten Webspace des Servers.';
$string['intern'] = 'Intern';
$string['extern'] = 'Extern';
$string['location'] = 'Startseite';
$string['reference_int'] = 'Interne Referenzierung';
$string['reference_int_help'] = 'Laden Sie hier den kompletten Studierplatz hoch (also ***Data***-Ordner, ***comp***-Ordner und evtl. separate Start-Datei) und markieren die Start-Datei ais Hauptdatei.  '.'
**Hinweis:** Sie können den Studierplatz gezippt hochladen und dann entpacken lassen (rechtsklick auf hochgeladene Zip-Datei).  '.'
Die Start-Datei ist entweder die ***Start.htm*** oder ***Start2.htm*** im ***comp***-Ordner (dann erscheint der Studierplatz mit seinem üblichen Spash-Screen) oder eine **name*_start.htm*** (dann öffnet sich der Studierplatz bei Starten gleich mit der ersten Seite).';
$string['reference_ext'] = 'Externe Referenzierung';
$string['reference_ext_help'] = 'Geben Sie hier die vollständige URL-Adresse zur Startseite des Studierplatz an, also z.B. *http://mymoodle.tu-dresden.de/stuplas/Bio2/comp/Start.htm*.  '.'
    Die Start-Datei ist entweder die ***Start.htm*** oder ***Start2.htm*** im comp-Ordner (dann erscheint der Studierplatz mit seinem üblichen Spash-Screen) oder eine **name*_start.htm*** (dann öffnet sich der Studierplatz bei Starten gleich mit der ersten Seite).';
$string['filename'] = 'Dateiname';
$string['reference'] = 'Url der Startseite';
$string['localpath'] = 'Lokaler Zielpfad';
$string['localpath_help']  = 'Eigentlich kann diese Angabe leer bleiben. Bei der externen Einbindung eines Studierplatz (also ohne hochladen in Moodle)
kann es unter Umständen zu Problemen mit dem Zugriff auf einzelne Dateien kommen.  '.'
In diesem Fall geben Sie hier den Pfad bis zum Zielordner an, so wie es dem Betriebssystem entspricht, also z.B.
***/usr/moodle/stuplas/Bio/comp*** oder ***c:\Inetpub\stuplas\Bio\comp***.';

// Subuser.
$string['subuser'] = 'Unternutzer';
$string['useSubuser'] = 'Unternutzer verwenden';
$string['useSubuser_help'] = 'Wenn mehrere Person einen Studierplatz über ein und dasselbe Login verwenden sollen
(z.B. auch beim Gastlogin), ist es sinnvoll zusätzliche Daten der Person zu erfassen.  '.'
Hier kann festgelegt werden, welche Daten dies sein sollen.
Wenn ein Nutzer dann den Studierplatz startet, wird er aufgefordert, diese Daten einzugeben.';
$string['subuserLogData'] = 'Daten die beim Starten erfasst werden sollen:';
$string['subuserFieldname'] = 'Bezeichnung';
$string['subuserFieldmust'] = 'Pflicht';
$string['subuserFieldtype'] = 'Typ';
$string['subuserFieldcomment'] = 'Kommentar';
$string['firstname'] = 'Vorname';
$string['lastname'] = 'Familienname';
$string['birthday'] = 'Geburtsdatum';
$string['(dd.mm.yyyy)'] = '(tt.mm.jjjj)';
$string['subuser_new'] = 'Hier können Sie ein neues Datenfeld anlegen.';
$string['textfield'] = 'Textfeld';
$string['datefield'] = 'Datum';
$string['combofield'] = 'Combobox';
$string['addsufield'] = 'Neues Datenfeld hinzufügen';
$string['delsufield'] = 'Datenfeld löschen';
$string['su_combodata'] = 'Einträge der Combobox';
$string['subuserdata'] = 'Daten der Unternutzer';
$string['subuserdata_help'] = 'Für die Daten können Sie jeweils angeben:

* den Namen
* ob die Angabe Pflicht sein soll
* den Typ (freies Textfeld, Datumsfeld oder Combobox)
* einen erläuternden Kommentar, der den Nutzern auch angezeigt wird

Vorname und Geburtsdatum sind standardmässig voreingestellt
(wenn Sie diese Daten nicht erheben wollen löschen Sie einfach entsprechend die Felder über den "Del"-Button vor der Zeile).  '.'
Um ein neues Daten-Feld hinzuzufügen tragen Sie die entsprechenden Werte in die weiter
unten dafür vorgesehene Zeile und bestätigen mit "Add".';
$string['combodef'] = 'Comboboxen definieren';
$string['combodef_help'] = 'Um Comboboxen zu definieren wählen Sie für ein Feld einfach den Typ "Combobox".  '.'
Tragen Sie dann im sich darunter neu öffnenden Textbereich einfach die Werte ein, die später in der Combobox erscheinen sollen.  '.'
Jede Zeile enspricht einem Eintrag';
$string['subuserprotname'] = 'Anzeige im Protokoll';
$string['subuserprotname_help'] = 'Hier können Sie angeben, aus welchen Unternutzerdaten der Anzeigename im Protokoll zusammengesetzt werden soll.
Geben Sie dazu die Namen der gewünschten Datenfelder in \'$\' eingefasst an.  '.'
Gibt es z.B. die Datenfelder \'Vorname\', \'Familienname\' und \'Klassenstufe\' und Sie geben für die Anzeige im Protokoll
> *$Familienname$, $Vorname$, ($Klassenstufe$)*

an, so werden die Nutzer entsprechend als z.B.
> Müller, Nina (4b)

angezeigt.  '.'
Wenn Sie *$USER$* einfügen, wird der Namen des Hauptnutzers eingefügt und bei *$SESSION$* die Session-Nummer.';

// Index page.
$string['subusers'] = 'Unternutzer/innen';
$string['sessions'] = 'Sessions';
$string['archivedusers'] = 'Archivierte Nutzer';

// Start page.
$string['start'] = 'Start';
$string['continuesessions'] = 'Vorhandene Session fortsetzen ...';
$string['resume'] ='Fortsetzen';
$string['newsession'] = 'Explizit neue Session';

// Subuserlogin.
$string['fillFields'] = 'Bitte füllen Sie die Felder aus.';
$string['dateComment'] = '(tt.mm.jjjj)';
$string['loginButton'] = 'Weiter';
$string['combo_please_select'] = 'Bitte auswählen!';
$string['missingField'] = 'Bitte füllen Sie das Feld \'{$a}\' aus.';
$string['formatField'] = 'Bitte prüfen Sie die Eingabe im Feld \'{$a}\'. Sie entspricht nicht dem erwartetem Format.';
$string['missingSelect'] = 'Bitte wählen Sie im Feld \'{$a}\' eine gültige Option aus.';

// Protocol.
$string['protocol'] = 'Protokoll';

$string['user_list'] = 'Nutzer-Liste';
$string['login_list'] = 'Login-Liste';
$string['walkthrough'] = 'Ablauf';
$string['questionaire'] = 'Fragebögen';
$string['exercises'] = 'Aufgaben';
$string['exercise_states'] = 'Aufgaben-Status';
$string['exercise_overview'] = 'Aufgaben-Übersicht';
$string['statistics'] = 'Auswertung';

$string['displayonpage'] = 'Anzeigen (HTML)';
$string['downloadtext'] = 'Text-Export (CSV)';
$string['downloadexcel'] = 'Excel-Export';
$string['refresh'] = 'Aktualisieren';

// User list.
$string['logins'] = 'Logins';
$string['fb_reqs'] = 'Fragebögen';
$string['texts'] = 'Texte';
$string['texts_time'] = 'Zeit in Texten';
$string['media'] = 'Medien';
$string['exercises'] = 'Aufgaben';
$string['time_total'] = 'Gesamtzeit';

$string['use_as_startdate'] = 'Dieses Datum als \'ab:\'-Datum zur Einschränkung der Nutzer verwenden.';
$string['use_as_enddate'] = 'Dieses Datum als \'bis:\'-Datum zur Einschränkung der Nutzer verwenden.';

$string['delete'] = 'Löschen';
$string['delete_explain'] = 'der markierten Sessions';
$string['deleteempty'] = 'Keine Session zum Löschen ausgewählt.';
$string['deleteverify'] = 'Soll(en) diese {$a} Session(s) wirklich (unwiederbringlich) gelöscht werden?';
$string['deletedone'] = '{$a} Session(s) wurden (unwiederbringlich) gelöscht.';
$string['cancel'] = 'Abbrechen';

$string['archivetag'] = 'Archivlabel';
$string['archive'] = 'Archivieren';
$string['archive_explain'] = 'der markierten Sessions';
$string['archiveempty'] = 'Keine Session zum Archivieren ausgewählt.';
$string['archiveverify'] = 'Soll(en) diese {$a} Session(s) wirklich archiviert werden (und von der aktuellen Liste entfernt)?';
$string['archiveselecttag'] = 'Bitte geben sie ein Label an, damit diese Session(s) später im Archiv bequem wiedergefunden werden können.';
$string['archiveselecttagnew'] = 'Geben Sie ein neues Label an:';
$string['archiveselecttagold'] = 'oder benutzen sie ein vorhandenes:';
$string['archivetagneeded'] = 'Bitte geben sie ein Archiv-Label an.';
$string['archivetagonlyone'] = 'Bitte geben sie ein neues Archiv-Label an oder wählen sie ein altes. Beides gleichzeitig geht nicht.';
$string['archivedone'] = '{$a->count} Session(s) wurden mit dem Label "{$a->label}" archiviert.';
$string['displayarchive'] = 'Archiv anzeigen';
$string['displayarchive_explain'] = 'für die folgenden Label:';
$string['noarchivetagsselected'] = 'Keine Archiv-Labels ausgewäht.';
$string['leavearchive'] = 'Archiv verlassen';
$string['leavearchive_explain'] = '';
$string['unarchive'] = 'Entarchivieren';
$string['unarchive_explain'] = 'der markierten Sessions';
$string['unarchiveempty'] = 'Keine Session zum Entarchivieren ausgewählt.';
$string['unarchiveverify'] = 'Soll(en) diese {$a} Session(s) wirklich entarchiviert werden?';
$string['unarchivedone'] = '{$a} Sessions wurden entarchiviert.';

$string['entry'] = 'Eintrag';
$string['solve'] = 'Solve';
$string['systerror'] = 'SystError';

$string['exercise'] = 'Aufgabe';
$string['state'] = 'Status';
$string['solved_correct'] = 'richtig gelöst';
$string['solved_wrong'] = 'falsch gelöst';
$string['unattempted'] = 'unbearbeitet';
$string['one_ex_att'] = '{$a->count} Aufgabe {$a->action}';
$string['mult_ex_att'] = '{$a->count} Aufgaben {$a->action}';

$string['statistic'] = 'Auswertung';
$string['code'] = 'Code';
$string['nlogin'] = 'nLogin';
$string['time'] = 't_ges';
$string['display_texts'] = 'Text-Zeiten anzeigen';
$string['display_media'] = 'Medien-Zeiten anzeigen';
$string['display_exercises'] = 'Aufgaben anzeigen';
$string['display_special_exercises'] = 'Spezial-Aufgaben anzeigen';
$string['use_media_exercise_file_names'] = 'Medien-/Aufgaben-Dateinamen verwenden';
$string['use_exercises_first_glance'] = 'bei Aufgaben nur allererste Bearbeitung auswerten';
$string['apply_changes'] = 'Änderungen anwenden';

// Rights.
$string['stupla:addinstance'] = 'Neuen Stupla einfügen';
$string['stupla:attempt'] = 'Einen Stupla benutzen könnnen';
$string['stupla:deleteallsessions'] = 'Sessions beliebiger Nutzer löschen';
$string['stupla:deletemysessions'] = 'Eigene Sessions löschen';
$string['stupla:preview'] = '(Vorschau, aktuell nicht genutzt)';
$string['stupla:reviewallprotocols'] = 'Protokolle aller Nutzer anschauen';
$string['stupla:reviewmyprotocols'] = 'Eigene Protokolle anschauen';

// Files.
$string['sourcefile'] = 'Quellen';
