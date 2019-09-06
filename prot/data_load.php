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
 * Load data of a Stupla for protocol.
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$s2name = array();
$s2deep = array();
$s2media = array();
$s2mediatitle = array();
$s2mediatitleshort = array();
$s2intocmedia = array();
$s2intocmediatitleshort = array();
$s2maufgabe = 0;

function dataloadstudy(&$stupla) {
    return dataloadfile(stupla_load_data_js($stupla));
}

function dataloadfile($datafilecontens) {
    global $s2name, $s2deep,  $s2mediatitle, $s2media, $s2mediatitleshort,
        $s2intocmedia, $s2intocmediatitleshort, $s2maufgabe, $s2captopic;
    // Reset.
    $s2name = array();
    $s2media = array();
    $s2mediatitle = array();
    $s2mediatitleshort = array();
    $s2intocmedia = array();
    $s2intocmediatitleshort = array();
    $s2maufgabe = 0;
    if ( $datafilecontens === false ) {
        return;
    }
    // Split to lines.
    $dat = explode("\n", str_replace("\r", '', $datafilecontens));
    $m = array();
    $n = array();
    $line = 0;
    while ( $line < count($dat) ) {
        $s = $dat[$line++];
        if ( preg_match("/^var Name = new Array\(\"([^\"]*)\"/", $s, $m) ) {
            array_push($s2name, $m[1]);
            while ( preg_match("/^\s*\"([^\"]*)\"(.)/", $s = $dat[$line++], $m) ) {
                array_push($s2name, $m[1]);
                if ( $m[2] == ")" ) {
                    break;
                }
            }
        } else if ( preg_match("/Deep = new Array\(/", $s, $m) ) {
            $s = '';
            while ( !preg_match("/\);\s*$/", $s) ) {
                $s .= $dat[$line++];
            }
            $s = trim(substr($s, 0, strpos($s, ")")));
            $s2deep = preg_split("/\s*,\s*/", $s);
        } else if ( preg_match("/Media\[(\d*)\]\[(\d*)\] = new Array\(/", $s, $m) ) {
            if ( !array_key_exists($m[1], $s2media) ) {
                $s2media[$m[1]] = array();
            }
            $s2media[0+$m[1]][0+$m[2]] = array();
        } else if ( preg_match("/Media\[(\d*)\]\[(\d*)\]\[(\d*)\] = new Array\(/", $s, $m) ) {
            while ( preg_match("/\);\s*$/", $s) == 0 ) {
                $s = preg_replace("/\"\+\s*$/", "", $s).preg_replace("/^\s*\"/", "", $dat[$line++]);
            }
            if ( preg_match("/Media\[(\d*)\]\[(\d*)\]\[(\d*)\] = new Array\(\"(.*)\", \"(.*)\", \"(.*)\", (\d+)\)/", $s, $m) ) {
                $s2media[0+$m[1]][0+$m[2]][0+$m[3]] = array($m[4], $m[5], $m[6], $m[6]);
            }
        } else if ( preg_match("/^var MediaTitle = new Array\(\"(.*)\"\);\s*$/", $s, $m) ) {
            $s2mediatitle = explode('", "', $m[1]);
        } else if ( preg_match("/^var MediaTitleShort = new Array\(\"(.*)\"\);\s*$/", $s, $m) ) {
            $s2mediatitleshort = explode('", "', $m[1]);
        } else if ( preg_match("/^var InTOCMedia = new Array\(/", $s, $m) ) {
            $s2intocmedia = array();
            do {
                $s = $dat[$line++];
                if ( preg_match("/^\s*new Array\((.*)\)(,|\);)\s*$/", $s, $m) ) {
                    $a = array();
                    $s1 = $m[1];
                    if ( preg_match("/new Array\(\"(.*)\"\)/", $s1, $n) ) {
                        for ($i=1; $i< count($n); $i++) {
                            $a[] = explode('", "', $n[$i]);
                        }
                    }
                    $s2intocmedia[] = $a;
                }
            } while ( preg_match("/\);\s*$/", $s) == 0 );
        } else if ( preg_match("/^var InTOCMediaTitleShort = new Array\(\"(.*)\"\);\s*$/", $s, $m) ) {
            $s2intocmediatitleshort = explode('", "', $m[1]);
        } else if ( preg_match("/^var M_AUFGABE = (\d*);/", $s, $m)) {
            $s2maufgabe = 1*$m[1];
        }
    }
    return true;
}

if ( !empty($stupla) ) {
    dataloadstudy($stupla);
}
