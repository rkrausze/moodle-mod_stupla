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
 * Helper lib for prot.
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function get_anchor($table) {
    return isset($table->anchor) ? $table->anchor : $table->name;
}

// Ordering of tables.

function order_tables(&$tables, $sortinfo) {
    $info = explode(',', $sortinfo); // Anchor, column, direction, type.
    for ($i = 0; $i < count($tables); $i++) {
        if ( get_anchor($tables[$i]) == $info[0] ) {
            order_table($tables[$i], $info[1], $info[2], $info[3]);
        }
    }
}

function order_table(&$table, $col, $dir, $type) {
    $help = array();
    foreach ($table->data as $line) {
        $v = $line[$col];
        if ( $type == 'n' ) { // Numeric.
            $v = 0+$v;
        } else if ( $type == 'p' ) { // Percent.
            $v = 0+substr($v, 0, -1);
        } else if ( $type == 'a' ) { // Text with links.
            $v = strtolower(preg_replace("/(<\/?)(\w+)([^>]*>)/", "", $v));
        } else if ( $type == 'd' ) { // Date.
            $s = preg_replace("/(<\/?)(\w+)([^>]*>)/", "", $v);
            $s = preg_replace("/[\[\]]/", "", $s);
            $d = explode('.', $s);
            $v = count($d) > 2 ? mktime(0, 0, 0, 0+$d[1], 0+$d[0], 2000+$d[2]) : '';
        } else if ( $type == 't' ) { // Time.
            $d = explode(':', $v);
            $v = $d[0]*60+$d[1];
        } else if ( is_string($v) ) {
            $v =strtolower($v);
        }
        $help[] = $v;
    }
    array_multisort($help, $dir == "d" ? SORT_DESC : SORT_ASC, $table->data);
}

// Order headers.

function makeorderhead_tables(&$tables, $order_info) {
    $info = explode(',', $order_info.",,,,,"); // Anchor, column, direction, type.
    for ($i = 0; $i < count($tables); $i++) {
        makeorderhead_table($tables[$i], get_anchor($tables[$i]) == $info[0] ? $info[1] : -1, $info[2], $info[3]);
    }
}

function makeorderhead_table(&$table, $col, $dir, $type) {
    $anchor = get_anchor($table);
    for ($i = 0; $i < count($table->head); $i++) {
        $add = "";
        if ( $col == $i && $dir == 'u' ) {
            $add .= '<a href="javascript:top.control.TableSort(\''.$anchor.','.$i.',u,'.
                   (isset($table->ordertype) ? $table->ordertype[$i] : '').'\')" target="control" style="color:red">&uarr;</a>';
        } else {
            $add .= '<a href="javascript:top.control.TableSort(\''.$anchor.','.$i.',u,'.
                   (isset($table->ordertype) ? $table->ordertype[$i] : '').'\')" target="control">&uarr;</a>';
        }
        if ( $col == $i && $dir == 'd' ) {
            $add .= '<a href="javascript:top.control.TableSort(\''.$anchor.','.$i.',d,'.
                   (isset($table->ordertype) ? $table->ordertype[$i] : '').'\')" target="control" style="color:red">&darr;</a>';
        } else {
            $add .= '<a href="javascript:top.control.TableSort(\''.$anchor.','.$i.',d,'.
                   (isset($table->ordertype) ? $table->ordertype[$i] : '').'\')" target="control">&darr;</a>';
        }
        $table->head[$i] .= $add;
    }
}

// Print tables.

function print_tables_html($tables) {
    global $sortinfo;
    if ( $sortinfo != '' ) {
        order_tables($tables, $sortinfo);
    }
    makeorderhead_tables($tables, $sortinfo);

    foreach ($tables as $table) {
        ?><p style="text-align:center"><a name="<?php echo get_anchor($table) ?>"></a><b><?php echo $table->name; ?></b></p><?php
        echo html_writer::table($table);
    }
}

define('STUPLA_EXCELROWS', 65535);
define('STUPLA_FIRSTUSEDEXCELROW', 3);

function print_tables_xls($filenameprefix, $tables) {
    global $CFG;
    global $sortinfo;
    if ( $sortinfo != '' ) {
        order_tables($tables, $sortinfo);
    }

    // Create Excel workbook.
    if (file_exists("$CFG->libdir/excellib.class.php")) {
        // Moodle >= 1.6.
        require_once("$CFG->libdir/excellib.class.php");
        $workbook = new MoodleExcelWorkbook("-");
        $wsnamelimit = 0; // No limit.
    } else {
        // Moodle <= 1.5.
        require_once("$CFG->libdir/excel/Worksheet.php");
        require_once("$CFG->libdir/excel/Workbook.php");
        $workbook = new Workbook("-");
        $wsnamelimit = 31; // Max length in chars.
    }

    $strftimedatetime = get_string("strftimedatetime");

    $nro_pages = 0;
    foreach ($tables as $table) {
        $nro_pages += ceil(count($table->data)/(STUPLA_EXCELROWS-STUPLA_FIRSTUSEDEXCELROW+1));
    }
    $filename = $filenameprefix.'_'.userdate(time(), '%Y%m%d-%H%M', 99, false);
    $filename .= '.xls';

    if (method_exists($workbook, 'send')) {
        // Moodle >=1.6.
        $workbook->send($filename);
    } else {
        // Moodle <=1.5.
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$filename" );
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");
    }

    $worksheet = array();

    $formatbold =& $workbook->add_format(array('bold'=>1));

    // Creating worksheets.
    $wsnumber = 1;
    foreach ($tables as $table) {
        $npagetable = ceil(count($table->data)/(STUPLA_EXCELROWS-STUPLA_FIRSTUSEDEXCELROW+1));
        for ($j = 1; $j <= $npagetable; $j++) {
            $sheettitle = isset($table->shortname) ? $table->shortname : $table->name;
            if ( $npagetable > 1 ) {
                $sheettitle .= "_".$j;
            }
            $worksheet[$wsnumber] =& $workbook->add_worksheet($sheettitle);
            $worksheet[$wsnumber]->set_column(1, 1, 30);
            $worksheet[$wsnumber]->write_string(0, 0, get_string('savedat').
                                        userdate(time(), $strftimedatetime));
            $worksheet[$wsnumber]->write_string(1, 0, $table->name);
            $col = 0;
            foreach ($table->head as $item) {
                $worksheet[$wsnumber]->write(STUPLA_FIRSTUSEDEXCELROW-1, $col, $item, $formatbold);
                $col++;
            }
            $wsnumber++;
        }
    }

    $row = STUPLA_FIRSTUSEDEXCELROW;
    $wsnumber = 1;
    $myxls =& $worksheet[$wsnumber];
    foreach ($tables as $table) {
        foreach ($table->data as $line) {
            if ($row > STUPLA_EXCELROWS) {
                $wsnumber++;
                $myxls =& $worksheet[$wsnumber];
                $row = STUPLA_FIRSTUSEDEXCELROW;
            }
            for ($i = 0; $i < count($line); $i++) {
                $myxls->write($row, $i, $line[$i], '');
            }
            $row++;
        }
        $wsnumber++;
        $myxls =& $worksheet[$wsnumber];
        $row = STUPLA_FIRSTUSEDEXCELROW;
    }

    $workbook->close();
    exit;
}

function print_tables_csv($filenameprefix, $tables) {
    global $sortinfo;
    if ( $sortinfo != '' ) {
        order_tables($tables, $sortinfo);
    }

    $filename = $filenameprefix.'_'.userdate(time(), '%Y%m%d-%H%M', 99, false);
    $filename .= '.txt';
    header("Content-Type: application/download\n");
    header("Content-Disposition: attachment; filename=$filename");
    header("Expires: 0");
    header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");

    $strftimedatetime = get_string("strftimedatetime");
    echo get_string('savedat').userdate(time(), $strftimedatetime)."\r\n";

    $first = true;
    foreach ($tables as $table) {
        if ( $first ) {
            $first = false;
        } else {
            echo "==================================================================================\r\n";
        }
        echo implode("\t", $table->head), "\r\n";
        foreach ($table->data as $line) {
            echo implode("\t", array_map("safe_csv_text", $line)), "\r\n";
        }
    }
    exit;
}

function safe_csv_text($s) {
    $has_nls = (strpos($s, "\n") !== false || strpos($s, "\r") !== false);
    $has_quotes = (strpos($s, '"') !== false);
    if ( $has_nls || $has_quotes ) {
        if ( $has_quotes ) {
            $s = str_replace('"', '\\"', $s);
        }
        if ( $has_nls ) {
            $s = preg_replace("/(\r|)\n/", '////', $s);
        }
        return '"'.$s.'"';
    }
    return $s;
}
