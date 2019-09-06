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
 * The main lib of the module.
 *
 * @package    mod
 * @subpackage stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Stupla constants.
define("STUPLA_NO",  "0");
define("STUPLA_YES", "1");

// Separators within imploded data fields.
define("STUPLA_X01", "$@STUPLAX01@$");
define("STUPLA_X02", "$@STUPLAX02@$");

// Stupla flags.
define("STUPLA_ISUTF8", 1);
define("STUPLA_USESUBUSERS", 2);

define("STUPLA_SESSIONSRESUMABLE", 4);
define("STUPLA_FORCEONESESSION", 8);

define("STUPLA_SESS_MASK", 4+8);
define("STUPLA_SESS_ONLYONE", 4+8);
define("STUPLA_SESS_PRIMARYONE", 8);
define("STUPLA_SESS_PRIMARYMULTI", 4);
define("STUPLA_SESS_ONLYMULTI", 0);


define ("STUPLA_TEXTSOURCE_FILENAME", "0");
define ("STUPLA_TEXTSOURCE_FILEPATH", "1");
define ("STUPLA_TEXTSOURCE_DATAJS", "2");
define ("STUPLA_TEXTSOURCE_SPECIFIC", "3");

define("STUPLA_LOCATION_COURSEFILES", "0");
define("STUPLA_LOCATION_SITEFILES",   "1");

$STUPLA_LOCATION = array (
    STUPLA_LOCATION_COURSEFILES => get_string("coursefiles"),
    STUPLA_LOCATION_SITEFILES   => get_string("sitefiles"),
);

define("STUPLA_SESSION_FLAGS_DELETED", 1);

define("STUPLA_LOGIN", 199);
define("STUPLA_NOTE", 198);
define("STUPLA_MARK", 197);
define("STUPLA_SHEET", 196);
define("STUPLA_PLAN", 195);
define("STUPLA_GENERIC", 194); // Holds "prot, "# ..." - Actions (Material, Protocol).
define("STUPLA_SYSTERROR", 180);

/**
 * Returns the information on whether the module supports a feature.
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function stupla_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod.html) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $instance An object from the form in mod.html
 * @return int The id of the newly inserted stupla record
 **/
function stupla_add_instance(stdClass $stupla, mod_stupla_mod_form $mform = null) {
    global $DB;

    $stupla->timemodified = time();
    $stupla->flags = 0;

    stupla_set_form_values($stupla, $mform);
    stupla_pack_subuserdata($stupla);

    if ( stupla_isUTF8($stupla) ) {
        $stupla->flags |= STUPLA_ISUTF8;
    }

    switch ($stupla->namesource) {
        case STUPLA_TEXTSOURCE_FILENAME:
            $stupla->name = basename($stupla->reference);
            break;
        case STUPLA_TEXTSOURCE_FILEPATH:
            $stupla->name = str_replace('/', ' ', $stupla->reference);
            break;
        case STUPLA_TEXTSOURCE_DATAJS:
            $stupla->name = '';
        default:
            if (empty($stupla->name)) {
                $datajs = stupla_load_data_js($stupla);
                if ( $datajs !== false ) {
                    if ( preg_match("/var Title = \"([^\"]*)\"/", $datajs, $m) ) {
                        $stupla->name = $m[1];
                    }
                }
            }
    }

    return $DB->insert_record('stupla', $stupla);
}

/**
 * Updates an instance of the stupla in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $stupla An object from the form in mod_form.php
 * @param mod_stupla_mod_form $mform
 * @return boolean Success/Fail
 */
function stupla_update_instance(stdClass $stupla, mod_stupla_mod_form $mform = null) {
    global $DB;

    $stupla->timemodified = time();
    $stupla->id = $stupla->instance;

    stupla_set_form_values($stupla, $mform);
    stupla_pack_subuserdata($stupla);

    return $DB->update_record('stupla', $stupla);
}

/**
 * Takes the values from the form and packs it into the stupla-object
 **/
function stupla_set_form_values(&$stupla, mod_stupla_mod_form $mform = null) {
    // Open sand close time.
    if ( !isset($stupla->enabletimeopen) || $stupla->enabletimeopen == 0 ) {
        $stupla->timeopen = 0;
    } else {
        $stupla->timeopen = make_timestamp(
            $stupla->openyear, $stupla->openmonth, $stupla->openday,
            $stupla->openhour, $stupla->openminute, 0
        );
    }

    if ( !isset($stupla->enabletimeclose) || $stupla->enabletimeclose == 0 ) {
        $stupla->timeclose = 0;
    } else {
        $stupla->timeclose = make_timestamp(
            $stupla->closeyear, $stupla->closemonth, $stupla->closeday,
            $stupla->closehour, $stupla->closeminute, 0
        );
    }

    // Sessions resumable.
    $stupla->flags &= ~STUPLA_SESS_MASK;
    $stupla->flags |= $stupla->sessionsResumable;

    // Subuserdata.
    if ( isset($stupla->useSubusers) ) {
        $stupla->flags |= STUPLA_USESUBUSERS;
    }

    $stupla->sudata = $mform->get_subuserdata();

    if ( $stupla->refInternExtern == 0 ) { // Is intern, overwrite reference.
        $stupla->reference = '';
        $context = context_module::instance($stupla->coursemodule);
        $sourcefile = null;

        if ($stupla->referenceInt) {
            $options = array('subdirs' => 1, 'maxbytes' => 0, 'maxfiles' => -1);
            file_save_draft_area_files($stupla->referenceInt, $context->id, 'mod_stupla', 'sourcefile', 0, $options);

            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'mod_stupla', 'sourcefile');

            // Do we need to remove the draft files ?
            // otherwise the "files" table seems to get full of "draft" records
            // $fs->delete_area_files($context->id, 'user', 'draft', $stupla->sourceitemid);

            foreach ($files as $hash => $file) {
                if ($file->get_sortorder()==1) {
                    $stupla->reference = $file->get_filepath().$file->get_filename();
                    $sourcefile = $file;
                    break;
                }
            }
            unset($fs, $files, $file, $hash, $options);

        }
        if (is_null($sourcefile) || $stupla->reference=='' ) {
            // Sourcefile was missing or not a recognized type - shouldn't happen !!
        }

    }
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 **/
function stupla_delete_instance($id) {
    global $DB;

    if (! $stupla = $DB->get_record("stupla", array('id' => $id)) ) {
        return false;
    }

    $result = true;

    // Delete any dependent records here.

    if (! $DB->delete_records('stupla', array('id' => $stupla->id)) ) {
        $result = false;
    }
    if (! $DB->delete_records('stupla_session', array('stupla' => $stupla->id)) ) {
        $result = false;
    }
    if (! $DB->delete_records('stupla_action', array('stupla' => $stupla->id)) ) {
        $result = false;
    }
    if (! $DB->delete_records('stupla_ex', array('stupla' => $stupla->id)) ) {
        $result = false;
    }
    if (! $DB->delete_records('stupla_sheet', array('stupla' => $stupla->id)) ) {
        $result = false;
    }
    if (! $DB->delete_records('stupla_plan', array('stupla' => $stupla->id)) ) {
        $result = false;
    }

    return $result;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function stupla_user_outline($course, $user, $mod, $stupla) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = 'not implementet yet'; // TODO.
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $stupla the module instance record
 * @return void, is supposed to echp directly
 */
function stupla_user_complete($course, $user, $mod, $stupla) {
    // TODO.
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in stupla activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function stupla_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false TODO.
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link stupla_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function stupla_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
    // TODO.
}

/**
 * Prints single activity item prepared by {@see stupla_get_recent_mod_activity()}
 * @return void
 */
function stupla_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function stupla_cron () {
    global $CFG;

    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function stupla_get_extra_capabilities() {
    return array(); // TODO.
}

// Gradebook API.

/**
 * Is a given scale used by the instance of stupla?
 *
 * This function returns if a scale is being used by one stupla
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $stuplaid ID of an instance of this module
 * @return bool true if the scale is used by the given stupla instance
 */
function stupla_scale_used($stuplaid, $scaleid) {
    global $DB;

    /* @example */
    if ($scaleid and $DB->record_exists('stupla', array('id' => $stuplaid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of stupla.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any stupla instance
 */
function stupla_scale_used_anywhere($scaleid) {
    global $DB;

    /* @example */
    if ($scaleid and $DB->record_exists('stupla', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give stupla instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $stupla instance object with extra cmidnumber and modname property
 * @return void
 */
function stupla_grade_item_update(stdClass $stupla) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    /* @example */
    $item = array();
    $item['itemname'] = clean_param($stupla->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax']  = $stupla->grade;
    $item['grademin']  = 0;

    grade_update('mod/stupla', $stupla->course, 'mod', 'stupla', $stupla->id, 0, null, $item);
}

/**
 * Update stupla grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $stupla instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function stupla_update_grades(stdClass $stupla, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    /* @example */
    $grades = array(); // Populate array of grade objects indexed by userid.

    grade_update('mod/stupla', $stupla->course, 'mod', 'stupla', $stupla->id, 0, $grades);
}

// File API.

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function stupla_get_file_areas($course, $cm, $context) {
    return array(
        'sourcefile' => get_string('sourcefile', 'stupla')
    );
}

/**
 * File browsing support for stupla file areas
 *
 * @package mod_stupla
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function stupla_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;
    if (has_capability('moodle/course:managefiles', $context)) {
        // No peaking here for students!!
        return null;
    }

    $fs = get_file_storage();
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;
    $urlbase = $CFG->wwwroot.'/pluginfile.php';
    if (!$storedfile = $fs->get_file($context->id, 'mod_stupla', $filearea, $itemid, $filepath, $filename)) {
        return null;
    }
    return new file_info_stored($browser, $context, $storedfile, $urlbase, $filearea, $itemid, true, true, false);
}

/**
 * Serves the files from the stupla file areas
 *
 * @package mod_stupla
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the stupla's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function stupla_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    if (!$stupla = $DB->get_record('stupla', array('id'=>$cm->instance))) {
        send_file_not_found();
    }

    require_course_login($course, true, $cm);

    array_shift($args); // Ignore itemid - caching only.
    $fullpath = "/$context->id/mod_stupla/$filearea/0/".implode('/', $args);

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        send_file_not_found();
    }

    // Finally send the file.
    send_stored_file($file, 0, 0, $forcedownload); // Download MUST be forced - security!
}

// Navigation API.

/**
 * Extends the global navigation tree by adding stupla nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the stupla module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function stupla_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
    global $CFG, $DB;

    $stupla = $DB->get_record('stupla', array('id' => $cm->instance), '*', MUST_EXIST);

    if (has_capability('mod/stupla:reviewallprotocols', $cm->context) || has_capability('mod/stupla:reviewmyprotocols', $cm->context)) {
        $navref->add(
            get_string('protocol', 'stupla'),
            new moodle_url('/mod/stupla/prot/prot.php', array('stupla' => $stupla->id)),
            navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * Extends the settings navigation with the stupla settings
 *
 * This function is called when the context for the page is a stupla module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $stuplanode {@link navigation_node}
 */
function stupla_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $stuplanode=null) {
}


// Any other stupla functions go here.

function stupla_add_action($data) {
    global $stupla, $coure, $a, $sessionid, $DB;
    $b = explode(" ", $data);
    $c = explode("&", $b[0]);
    $d = explode(".", $c[0]);
    $action = new object();
    $action->stupla = $stupla->id;
    $action->session = $sessionid;
    $action->starttime = time();
    $action->media = $d[0];
    $action->topic = $d[1];
    $action->nr = $d[2];
    $action->timestamp = $c[1];
    $action->duration = $c[2];
    if ( isset($c[3]) ) {
        $action->result = round($c[3]);
    }
    if ( isset($b[1]) ) {
        $action->data = substr($data, strlen($b[0])+1);
    }
    $DB->insert_record('stupla_action', $action);
}

function stupla_add_extra_action($media, $data) {
    global $stupla, $coure, $a, $sessionid, $DB;
    $actiondata = new stdClass;
    $actiondata->stupla = $stupla->id;
    $actiondata->session = $sessionid;
    $actiondata->starttime = time();
    $actiondata->media = $media;
    $actiondata->topic = -99;
    $actiondata->nr = -99;
    $actiondata->data = $data;
    $DB->insert_record('stupla_action', $actiondata);
}

function stupla_load_data_js(&$stupla) {
    global $CFG;
    $comppath = stupla_compdirreference($stupla);
    if ( stupla_is_extern($stupla) ) {
        $res = @file_get_contents($comppath.'/data.js');
        if ( $res === false ) {
            $res = @file_get_contents($comppath.'/data8.js', "r");
        }
        return $res;
    } else {
        $file = stupla_get_file_int($stupla, 'data.js', $comppath);
        if ( $file === false ) {
            $file = stupla_get_file_int($stupla, 'data8.js', $comppath);
        }
        return ($file !== false) ? $file->get_content() : false;
    }
}

function stupla_isUTF8($stupla) {
    $comppath = stupla_compdirreference($stupla);
    if ( stupla_is_extern($stupla) ) {
        if ( ($handle = fopen($comppath."/data.js", "r")) !== false ) {
            fclose($handle);
            return false;
        }
        if ( ($handle = fopen($comppath."/data8.js", "r")) !== false ) {
            fclose($handle);
            return true;
        }
    } else {
        $file = stupla_get_file_int($stupla, 'data.js', $comppath);
        if ( $file !== false ) {
            return false;
        }
        $file = stupla_get_file_int($stupla, 'data8.js', $comppath);
        if ( $file !== false ) {
            return true;
        }
    }
    return false;
}

// Subuserdata.

function stupla_expand_subuserdata(&$stupla) {
    $s = isset($stupla->subuserdata) && $stupla->subuserdata != '' ? $stupla->subuserdata :
        get_string('firstname', 'stupla').STUPLA_X02.'text'.STUPLA_X02.'true'.STUPLA_X02.STUPLA_X02.STUPLA_X01.
        get_string('lastname', 'stupla').STUPLA_X02.'text'.STUPLA_X02.'true'.STUPLA_X02.STUPLA_X02.STUPLA_X01.
        get_string('birthday', 'stupla').STUPLA_X02.'date'.STUPLA_X02.'false'.STUPLA_X02.
        get_string('dd.mm.yyyy', 'stupla').STUPLA_X02.STUPLA_X01;
    if ( !isset($stupla->subuserdata) || $stupla->subuserdata == '') {
        $stupla->subuserprotname = '$'.get_string('firstname', 'stupla').'$ $'.get_string('lastname', 'stupla').'$';
    }
    $a = explode(STUPLA_X01, $s);
    $stupla->sudata = array();
    $c = 0;
    foreach ($a as $ai) {
        if ( $ai == "" ) {
            continue;
        }
        $b = explode(STUPLA_X02, $ai.STUPLA_X02.STUPLA_X02);
        $stupla->sudata[$c++] = array(
            "name" => $b[0],
            "type" => $b[1],
            "must" => ($b[2] == "true"),
            "comment" => $b[3],
            "data" => $b[4]);
    }
}

function stupla_pack_subuserdata(&$stupla) {
    if ( !isset($stupla->sudata) ) {
        return;
    }
    $a = array();
    foreach ($stupla->sudata as $id => $data) {
        $s = "";
        $s = $data["name"].STUPLA_X02.$data["type"].STUPLA_X02.($data["must"] ? "true" : "false").STUPLA_X02.$data["comment"].STUPLA_X02;
        if ( $data["type"] == "combo" ) {
            $s .= $data["data"];
        }
        $a[] = $s;
    }
    $stupla->subuserdata = join(STUPLA_X01, $a);
}

function stupla_get_cmid(&$stupla) {
    if ( isset($stupla->coursemodule) ) {
        return context_module::instance($stupla->coursemodule)->id;
    }
    return context_module::instance(get_coursemodule_from_instance('stupla', $stupla->id, $stupla->course, false, MUST_EXIST)->id)->id;
}

function stupla_get_file_int($stupla, $filename, $path = false) {
    if ( $path !== false ) {
        $dir = $path;
        $base = $filename;
    } else {
        $dir = dirname($filename);
        $base = basename($filename);
    }
    // Check trailing '/' at dir.
    if ( substr($dir, -1) != '/' && substr($dir, -1) != '\\' ) {
        $dir .= '/';
    }
    $fs = get_file_storage();
    return $fs->get_file(
        stupla_get_cmid($stupla),
        'mod_stupla',
        'sourcefile',
        0,
        $dir,
        $base);
}

/**
 * Delivers the url-dir to the comp-folder. Used by prot. With NO TRAILING "/".
 */
function stupla_compdirreference(&$stupla) {
    $compdir = dirname($stupla->reference);
    if ( stupla_is_short_start($stupla) ) {
        if ( $stupla->localpath != '' ) {
            if ( preg_match('/[\\\\\/]([^\\\\\/]+)$/', $stupla->localpath, $b) ) {
                $compdir .= '/'.$b[1];
            }
        } else { // Try to read this ..._start.htm.
            if ( !stupla_is_extern($stupla) ) {
                $file = stupla_get_file_int($stupla, $stupla->reference);
                $source = $file->get_content();
                if ( preg_match("/<frame src=\"([^\"]*?)\/Con_d\.htm/", $source, $b) ) {
                    $compdir .= ($compdir != '' ? '/' : '').$b[1];
                }
            }
        }
    }
    return $compdir;
}

function stupla_is_extern(&$stupla) {
    return preg_match('/^http(s|):\/\//', is_object($stupla) ? $stupla->reference : $stupla);
}

function stupla_is_short_start(&$stupla) {
    if ( stupla_is_extern($stupla) ) {
        $filename= $stupla->reference;
    } else {
        $file = stupla_get_file_int($stupla, $stupla->reference);
        $filename = $file->get_filename();
    }
    return strcasecmp(substr($filename, strlen($filename)-10), '_start.htm') == 0;
}

function stupla_www_prefix(&$stupla) {
    global $CFG;
    if ( stupla_is_extern($stupla) ) {
        return '';
    }
    return $CFG->wwwroot.'/pluginfile.php/'.stupla_get_cmid($stupla).'/mod_stupla/sourcefile/0';
}

function stupla_displayname(stdClass &$stupla, stdClass &$session, stdClass $user) {
    if ( ($stupla->flags & STUPLA_USESUBUSERS) && isset($stupla->subuserprotname) && $stupla->subuserprotname != '' && isset($session->data)) {
        $s = $stupla->subuserprotname;
        $s = str_replace('$USER$', $user->firstname.' '.$user->lastname, $s);
        $s = str_replace('$SESSION$', $session->id, $s);
        $d = explode(STUPLA_X01, $session->data);
        foreach ($stupla->sudata as $fieldid => $field) {
            $s = str_replace('$'.$field['name'].'$', isset($d[$fieldid]) ? $d[$fieldid] : '?', $s);
        }
    } else {
        $s = $user->firstname.' '.$user->lastname.' ('.$session->id.')';
    }
    $session->displayname = $s;
    return $s;
}

$errorstring = "";

// Sometimes moodle design do strange things to modest emmbedd-page.

/**
 * Used to avoid undesired frames and margins at embedded or frametop pages implied by selected moodle desing.
 *
 * @return string the resulting header
 */
function stupla_safe_header() {
    return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">'.
                '<html>'.
                '<head>'.
                '<title>Moodle STUPLA PROT</title>'.
                '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.
                '<style>'.
                ' p, td, th, input, div, span, a { font-family: Arial, Helvetica; }'.
                ' table { border-collapse: collapse; border-spacing: 0; }'.
                ' table.generaltable { border: 1px solid #DDD; }'.
                ' th.header { vertical-align: top; background-color: #EEE; border: 1px solid #EEE; font-weight: bold; padding: .5em; vertical-align:top; font-size: 13px; }'.
                ' th.header a { text-decoration: none; }'.
                '.generaltable .cell { background-color: #FFF; border: 1px solid #EEE; border-collapse: collapse; padding: .5em; vertical-align:top; font-size: 13px; }'.
                '</style>'.
                '</head>'.
                '<body>';
}

/**
 * Used to avoid undesired frames and margins at embedded or frametop pages implied by selected moodle desing.
 *
 * @return string the resulting footer
 */

function stupla_safe_footer() {
    return '</body></html>';
}