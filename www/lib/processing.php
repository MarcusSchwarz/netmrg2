<?php
/********************************************
 * NetMRG Integrator
 *
 * processing.php
 * Internal Processing Functions
 *
 * Copyright (C) 2001-2008
 *   Brady Alleman <brady@thtech.net>
 *   Douglas E. Warner <silfreed@silfreed.net>
 *   Kevin Bonner <keb@nivek.ws>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 ********************************************/


// Simple Formatting Section

function format_time_elapsed($num_secs) {
    // Makes a string from a 'seconds elapsed' integer
    $the_secs = $num_secs;
    $new_secs = $num_secs % 86400;
    $days     = ($num_secs - $new_secs) / 86400;
    if ($days > 10000) {
        return "Never";
    }
    $num_secs = $new_secs;
    $new_secs = $num_secs % 3600;
    $hours    = ($num_secs - $new_secs) / 3600;
    $num_secs = $new_secs;
    $new_secs = $num_secs % 60;
    $mins     = ($num_secs - $new_secs) / 60;

    $res = "";
    if ($the_secs > 0) {
        if ($days > 0) {
            $res = sprintf("%d days, ", $days);
        }
        $res .= sprintf("%02d:%02d:%02d", $hours, $mins, $new_secs);
    }
    else {
        $res .= "Unavailable";
    }

    return $res;

} // end format_time_elapsed


function sanitize_number($number, $round_to = 2) {

    $format = "%4.".$round_to."f";

    if ($number < 1000) {
        return sprintf($format, $number);
    }
    elseif ($number < 1000000) {
        return sprintf("$format k", $number / 1000);
    }
    elseif ($number < 1000000000) {
        return sprintf("$format M", $number / 1000000);
    }
    elseif ($number < 1000000000000) {
        return sprintf("$format G", $number / 1000000000);
    }
    else {
        return sprintf("$format T", $number / 1000000000000);
    }

} // end sanitize_number

function paraphrase($string, $length, $etc = "...") {
    if (strlen($string) <= $length) {
        return $string;
    }

    return substr($string, 0, $length).$etc;
}

function make_spaces($length) {
    return str_repeat(" ", $length);
} // end make_spaces

function make_nbsp($length) {
    return str_repeat("&nbsp;", $length);
} // end make_nbsp


// prepends spaces to a string to cause it to be a certain length
function align_right($string, $length) {
    $space_length = $length - strlen($string);
    return (make_spaces($space_length).$string);
} // end align_right


function align_left($string, $length) {
    $space_length = $length - strlen($string);
    return ($string.make_spaces($space_length));
} // end align_left


function align_right_split($string, $length) {
    $space_length = $length - strlen($string);
    $pos          = strrchr($string, " ");
    return (substr($string, 0, -strlen($pos)).make_spaces($space_length).$pos);
} //end align_right_split


// manipulates a string by applying the appropriate padding method
function do_align($string, $length, $method) {
    if ($string == "") {
        return "";
    }

    switch ($method) {
        case 1:
            $result = align_left($string, $length);
            break;
        case 2:
            $result = align_right($string, $length);
            break;
        case 3:
            $result = align_right_split($string, $length);
            break;
    } // end switch($method)

    return ($result);
} // end do_align

function rrd_legend_escape($string) {
    if ($string == "") {
        return "";
    }
    $string = str_replace(":", "\:", $string);
    return (":".escapeshellarg($string));
}

function get_microtime() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function isin($haystack, $needle) {
    return is_integer(strpos($haystack, $needle));
}

function compare_interface_names($a, $b) {
    $astuff = preg_split("~([-/\. ])~", strtolower(trim($a)), 0, PREG_SPLIT_DELIM_CAPTURE);
    $bstuff = preg_split("~([-/\. ])~", strtolower(trim($b)), 0, PREG_SPLIT_DELIM_CAPTURE);
    for ($i = 0; $i < max(count($astuff), count($bstuff)); $i++) {
        if (isset($astuff[$i])) {
            if (isset($bstuff[$i])) {
                if ($astuff[$i] != $bstuff[$i]) {
                    if (is_numeric($astuff[$i]) && (is_numeric($bstuff[$i]))) {
                        return $astuff[$i] - $bstuff[$i];
                    }
                    else {
                        return strcmp($astuff[$i], $bstuff[$i]);
                    }
                }
            }
            else {
                return 1;
            }
        }
        else {
            return -1;
        }
    }
    return 0;
}

function compare_mac_addresses($a, $b) {
    $astuff = explode(":", $a);
    $bstuff = explode(":", $b);
    for ($i = 0; $i < 6; $i++) {
        if (strlen($astuff[$i]) == 1) {
            $astuff[$i] = "0".$astuff[$i];
        }
    }
    for ($i = 0; $i < 6; $i++) {
        if (strlen($bstuff[$i]) == 1) {
            $bstuff[$i] = "0".$bstuff[$i];
        }
    }
    $a1 = implode("", $astuff);
    $b1 = implode("", $bstuff);
    return strcmp($a1, $b1);
}

function compare_ip_addresses($a, $b) {
    $astuff = explode(".", $a);
    $bstuff = explode(".", $b);
    for ($i = 0; $i < 4; $i++) {
        while (strlen($astuff[$i]) != 3) {
            $astuff[$i] = "0".$astuff[$i];
        }
    }
    for ($i = 0; $i < 4; $i++) {
        while (strlen($bstuff[$i]) != 3) {
            $bstuff[$i] = "0".$bstuff[$i];
        }
    }
    $a1 = implode("", $astuff);
    $b1 = implode("", $bstuff);
    return strcmp($a1, $b1);
}

/**
 * simple_math_parse($input)
 *
 * $input - string to be parsed
 *
 * use eval to do math if everything looks safe.
 *
 *
 */

function simple_math_parse($input) {
    $val = 1;
    if (!preg_match("/[^012345467890.\/*\-+]/", $input)) {
        eval("\$val = $input;");
    }
    return $val;
}


// RRD Support Functions

/**
 * rrd_sum($mon_id, $start, $end, $resolution)
 *
 * $mon_id    = monitor id of RRD to sum
 * $start    = start time, formatted for RRDTOOL
 * $end        = end time, formatted for RRDTOOL (defaults to "now")
 * $resolution = resolution of data, formatted for RRDTOOL (defaults to 1 day)
 */
function rrd_sum($mon_id, $start, $end = "now", $resolution = 86400) {
    $rrd_handle = popen($GLOBALS['netmrg']['rrdtool']." fetch ".$GLOBALS['netmrg']['rrdroot']."/mon_".
                        $mon_id.".rrd AVERAGE -r $resolution -s $start -e $end", "r");

    $row_count = 0;
    $sum       = 0;

    while ($row = fgets($rrd_handle)) {
        // the first two lines are of no use
        if ($row_count > 1) {
            // ignore missing data points
            if (!preg_match("/nan/i", $row)) {
                $row_val = preg_replace("/.*: /", "", $row);
                list($mantissa, $exponent) = preg_split("/e/i", $row_val);
                $row_value = $mantissa * pow(10, intval($exponent));
                $sum += $row_value;
            }
        }
        $row_count++;
    }

    $average   = $sum / ($row_count - 1);
    $total_sum = $average * $resolution;
    pclose($rrd_handle);
    return $total_sum;
}

/**
 * rrdtool_syntax_highlight($txt)
 *
 * $txt        = a string normally passed to rrdtool
 */
function rrdtool_syntax_highlight($txt) {
    $txt = preg_replace("/(#[0-9,a-f,A-F]+)/", "<span style='color:#0F4B47'>\\1</span>", $txt);
    $txt = preg_replace("/(\s)DEF:/", "\\1<span style='color:blue'>DEF</span>:", $txt);
    $txt = str_replace("\\n", "<span style='color:red'>\\n</span>", $txt);
    $txt = str_replace("CDEF", "<span style='color:green'>CDEF</span>", $txt);
    $txt = preg_replace("/(\s)(AREA|STACK|LINE1|LINE2|LINE3|HRULE|VRULE):/", "\\1<span style='color:orange'>\\2</span>:", $txt);
    $txt = preg_replace("/:(MAX|AVERAGE|LAST)/", ":<span style='color:brown'>\\1</span>", $txt);
    $txt = preg_replace("/(\s)(GPRINT|PRINT|COMMENT):/", "\\1<span style='color:red'>\\2</span>:", $txt);
    $txt = preg_replace("/(data\d+[lm]*)/", "<span style='color:#344D6C'>\\1</span>", $txt);
    //$txt = preg_replace("/=(.*\.rrd):/", "=<span style='color:grey'>\\1</span>:", $txt);
    //$txt = preg_replace("/(\s)(\-+)(\s)/", "\\1<span style='color:red'>\\2</span>\\3", $txt);
    //$txt = preg_replace("/:(\".*\") /", ":<span style='color:purple'>\\1</span>", $txt);
    return $txt;
}

// Templating Functions

function expand_parameters($input, $subdev_id) {
    $query = getDatabase()->query('SELECT * FROM sub_dev_variables WHERE type = "dynamic" AND sub_dev_id = '.intval($subdev_id));

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $input = str_replace("%".$row['name']."%", $row['value'], $input);
    }

    //$input = preg_replace("/\%..+\%/", "N/A", $input);

    return $input;
} // expand_parameters()

function apply_template($subdev_id, $template_id) {

    // add the appropriate monitors to the subdevice
    $q      = getDatabase()->query('SELECT monitors.id, data_type, test_id, test_type, test_params, min_val, max_val FROM graph_ds, monitors WHERE graph_ds.graph_id = '.intval($template_id).' AND graph_ds.mon_id = monitors.id');
    $q_rows = getDatabase()
              ->query('SELECT COUNT(monitors.id) FROM graph_ds, monitors WHERE graph_ds.graph_id = '.intval($template_id).' AND graph_ds.mon_id = monitors.id')
              ->fetchColumn();
    for ($i = 0; $i < $q_rows; $i++) {
        $row = $q->fetch(PDO::FETCH_ASSOC);
        if (empty($row['min_val'])) {
            $row['min_val'] = "NULL";
        }
        if (empty($row['max_val'])) {
            $row['max_val'] = "NULL";
        }

        // only add a new monitor if there is no existing one that matches the template
        if (dereference_templated_monitor($row['id'], $subdev_id) === false) {
            $s = getDatabase()->prepare('INSERT INTO monitors (sub_dev_id, data_type, test_id, test_type, test_params, min_val, max_val) VALUES (:sub_dev_id, :data_type, :test_id, :test_type, :test_params, :min_val, :max_val)');
            $s->bindValue(':sub_dev_id', $subdev_id);
            $s->bindValue(':data_type', $row['data_type']);
            $s->bindValue(':test_id', $row['test_id']);
            $s->bindValue(':test_type', $row['test_type']);
            $s->bindValue(':test_params', $row['test_params']);
            $s->bindValue(':min_val', $row['min_val']);
            $s->bindValue(':max_val', $row['max_val']);
            $s->execute();
        }
    }

    // add templated graph to the device's view
    $sd_row  = getDatabase()
               ->query('SELECT dev_id FROM sub_devices WHERE id = '.intval($subdev_id))
               ->fetchColumn();
    $pos_row = getDatabase()
               ->query('SELECT MAX(pos)+1 AS newpos FROM view WHERE object_type = "device" AND object_id = '.intval($sd_row))
               ->fetchColumn();
    if (empty($pos_row)) {
        $pos_row = 1;
    }

    $s = getDatabase()->prepare('INSERT INTO view SET object_id = :object_id, object_type = "device", graph_id = :graph_id, type = "template", pos = :pos, subdev_id = :subdev_id');
    $s->bindColumn(':object_id', $sd_row);
    $s->bindColumn(':graph_id', $template_id);
    $s->bindColumn(':pos', $pos_row);
    $s->bindColumn(':subdev_id', $subdev_id);
    $s->execute();

    // add templated graph to the sub-device's view
    $pos_row = getDatabase()
               ->query('SELECT MAX(pos)+1 AS newpos FROM view WHERE object_type = "subdevice" AND object_id = '.intval($subdev_id))
               ->fetchColumn();
    if (empty($pos_row)) {
        $pos_row = 1;
    }

    $s = getDatabase()->prepare('INSERT INTO view SET object_id = :object_id, object_type = "subdevice", graph_id = :graph_id, type = "template", pos = :pos, subdev_id = :subdev_id');
    $s->bindColumn(':object_id', $subdev_id);
    $s->bindColumn(':graph_id', $template_id);
    $s->bindColumn(':pos', $pos_row);
    $s->bindColumn(':subdev_id', $subdev_id);
    $s->execute();
}


// Recursive status determination section

//Takes a grp_id and returns the current group aggregate status
function get_group_status($grp_id) {
    $status = -1;

    $grp_results = getDatabase()->query('SELECT id FROM groups WHERE parent_id = '.intval($grp_id));

    while ($grp_row = $grp_results->fetch(PDO::FETCH_ASSOC)) {
        $grp_status = get_group_status($grp_row["id"]);
        if (($grp_status > $status) && ($grp_status != 4)) {
            $status = $grp_status;
        }
    }

    $dev_row    = getDatabase()
                  ->query(' SELECT MAX(devices.status) AS status FROM dev_parents, devices WHERE grp_id = '.intval($grp_id).' AND dev_parents.dev_id = devices.id AND devices.status < 4 GROUP BY grp_id')
                  ->fetch(PDO::FETCH_ASSOC);
    $grp_status = $dev_row["status"];
    if ($grp_status > $status) {
        $status = $grp_status;
    }
    return $status;
}


// Uniform Name Creation Section

function get_short_monitor_name($mon_id) {

    $mon_row = getDatabase()
               ->query('SELECT test_id, test_params, test_type FROM monitors WHERE monitors.id = '.intval($mon_id))
               ->fetch(PDO::FETCH_ASSOC);

    return get_short_test_name($mon_row['test_type'], $mon_row['test_id'], $mon_row['test_params']);
}

function get_short_property_name($prop_id) {

    $prop_row = getDatabase()
                ->query('SELECT test_id, test_params, test_type FROM dev_props WHERE dev_props.id = '.intval($prop_id))
                ->fetch(PDO::FETCH_ASSOC);

    return get_short_test_name($prop_row['test_type'], $prop_row['test_id'], $prop_row['test_params']);
}

function get_short_test_name($test_type, $test_id, $test_params) {

    switch ($test_type) {
        case 1:
            $test_query = "SELECT name FROM tests_script   WHERE id = ".intval($test_id);
            break;
        case 2:
            $test_query = "SELECT name FROM tests_snmp     WHERE id = ".intval($test_id);
            break;
        case 3:
            $test_query = "SELECT name FROM tests_sql      WHERE id = ".intval($test_id);
            break;
        case 4:
            $test_query = "SELECT name FROM tests_internal WHERE id = ".intval($test_id);
            break;

    } // end switch test type


    $res = getDatabase()
           ->query($test_query)
           ->fetchColumn();

    if ($test_params != "") {
        $res .= " - ".$test_params;
    }

    return $res;
} // end get_short_test_name()


function get_monitor_name($mon_id) {

    $row = getDatabase()
           ->query('SELECT  devices.name AS dev_name, sub_devices.name AS sub_name FROM monitors LEFT JOIN sub_devices ON monitors.sub_dev_id = sub_devices.id LEFT JOIN devices ON sub_devices.dev_id = devices.id WHERE monitors.id = '.intval($mon_id))
           ->fetch(PDO::FETCH_ASSOC);

    return $row["dev_name"]." - ".$row["sub_name"]." (".get_short_monitor_name($mon_id).")";
} // end get_monitor_name()

function get_graph_name($graph_id) {
    return getDatabase()
           ->query('SELECT name FROM graphs WHERE id = '.intval($graph_id))
           ->fetchColumn();
}

function get_group_name($grp_id) {
    return getDatabase()
           ->query('SELECT name FROM groups WHERE id = '.intval($grp_id))
           ->fetchColumn();
}

function get_device_name($dev_id) {
    return getDatabase()
           ->query('SELECT name FROM devices WHERE id = '.intval($dev_id))
           ->fetchColumn();
}

function get_sub_device_name($sub_dev_id) {
    $dev_query = getDatabase()
                 ->query('SELECT name FROM sub_devices WHERE id = '.intval($sub_dev_id))
                 ->fetchColumn();
    if (empty($dev_query)) {
        return "Not Set";
    }
    return $dev_query;
}

function get_dev_sub_device_name($sub_dev_id) {
    $dev_query = getDatabase()
                 ->query('SELECT devices.name AS dev_name, sub_devices.name AS sub_name FROM sub_devices LEFT JOIN devices ON sub_devices.dev_id = devices.id WHERE sub_devices.id = '.intval($sub_dev_id))
                 ->fetch(PDO::FETCH_ASSOC);

    if (empty($dev_query)) {
        return "Not Set";
    }
    return $dev_query["dev_name"]." - ".$dev_query["sub_name"];
}

function get_event_name($event_id) {
    return getDatabase()
           ->query('SELECT name FROM events WHERE id = '.intval($event_id))
           ->fetchColumn();
}


/**
 * GetNumAssocItems($object_type, $object_id)
 *
 * $object_type = (group, device, monitor, event)
 * $object_id = id
 */
function GetNumAssocItems($object_type, $object_id) {

    $s = getDatabase()->prepare('SELECT COUNT(*) AS count FROM view, graphs WHERE view.graph_id = graphs.id AND object_type = :object_type AND object_id = :object_id');
    $s->bindValue(':object_type', $object_type);
    $s->bindValue(':object_id', $object_id);
    $s->execute();
    return $s->fetchColumn();
}


/**
 * GetGroupParents($group_id);
 *
 * returns all the groups parent group ids
 */
function GetGroupParents($group_id) {
    $group_arr  = array();
    $group_item = $group_id;
    $s          = getDatabase()->prepare('SELECT parent_id FROM groups WHERE id = :id');

    while ($group_item != 0) {
        $s->bindValue(':id', $group_item);
        $s->execute();
        $group_item = $s->fetchColumn();
        array_push($group_arr, $group_item);
    }

    return $group_arr;
}


/**
 * GetGroups($type,$id);
 *
 * returns an array of groups that this $type is in
 *
 */
function GetGroups($type, $id) {
    $group_arr = array();
    switch ($type) {
        case "group":
            $query = array('SELECT :id as group_id');
            break;
        case "device":
            $query = array('SELECT groups.id AS group_id FROM groups, dev_parents, devices WHERE devices.id = :id AND devices.id = dev_parents.dev_id AND dev_parents.grp_id = groups.id GROUP BY group_id');
            break;
        case "subdevice":
            $query = array(
                'SELECT groups.id AS group_id FROM groups, dev_parents, devices, sub_devices WHERE sub_devices.id = :id AND sub_devices.dev_id = devices.id AND devices.id = dev_parents.dev_id AND dev_parents.grp_id = groups.id GROUP BY group_id',
                'SELECT object_id AS group_id FROM view WHERE object_type = "group" AND subdev_id = :id GROUP BY group_id'
            );
            break;
        case "monitor":
            $query = array('SELECT groups.id AS group_id FROM groups, dev_parents, devices, sub_devices, monitors WHERE monitors.id = :id AND sub_devices.id = monitors.sub_dev_id AND sub_devices.dev_id = devices.id AND devices.id = dev_parents.dev_id AND dev_parents.grp_id = groups.id GROUP BY group_id');
            break;
        case "customgraph":
            $query = array('SELECT object_id, object_type FROM view WHERE type = "graph" AND graph_id = :id');
            break;
        default:
            // an unknown type should have no groups
            return $group_arr;
    } // end switch($type)

    foreach ($query as $sql_cmd) {
        $db_result = getDatabase()->prepare($sql_cmd);
        $db_result->bindValue(':id', $id);
        $db_result->execute();
        while ($r = $db_result->fetch(PDO::FETCH_ASSOC)) {
            if ($type == "customgraph") {
                $group_arr = array_merge($group_arr, GetGroups($r["object_type"], $r["object_id"]));
            }
            else {
                array_push($group_arr, $r["group_id"]);
                $group_arr = array_merge($group_arr, GetGroupParents($r["group_id"]));
            }
        }
    }
    return $group_arr;
}


/**
 * GetSubdeviceParent($subdevice_id);
 *
 * returns the parent device of the $subdevice_id
 *
 * @param integer $subdevice_id
 *
 * @returns int
 */
function GetSubdeviceParent($subdevice_id) {
    return getDatabase()
           ->query('SELECT dev_id FROM sub_devices WHERE id = '.intval($subdevice_id))
           ->fetchColumn();
}


/**
 * GetUsername($uid)
 *
 * returns the username for a uid
 */
function GetUsername($uid) {
    return getDatabase()
           ->query('SELECT user FROM user WHERE id ='.intval($uid))
           ->fetchColumn();
}

// Recursive Duplication Section

function duplicate_device($dev_id) {
    // duplicate device
    getDatabase()->exec('CREATE TEMPORARY TABLE tmpdev SELECT id, name, ip, snmp_read_community, dev_type, snmp_recache_method, disabled, snmp_avoided, snmp_uptime, snmp_ifnumber, snmp_version, snmp_timeout, snmp_retries, snmp_port, no_snmp_uptime_check FROM devices WHERE id = '.intval($dev_id));
    getDatabase()->exec('INSERT INTO devices (name, ip, snmp_read_community, dev_type, snmp_recache_method, disabled, snmp_avoided, snmp_uptime, snmp_ifnumber, snmp_version, snmp_timeout, snmp_retries, snmp_port, no_snmp_uptime_check) SELECT concat(name, " (duplicate)"), ip, snmp_read_community, dev_type, snmp_recache_method, disabled, snmp_avoided, snmp_uptime, snmp_ifnumber, snmp_version, snmp_timeout, snmp_retries, snmp_port, no_snmp_uptime_check FROM tmpdev WHERE id='.intval($dev_id));
    $new_dev_id = getDatabase()->lastInsertId();
    getDatabase()->exec('DROP TABLE tmpdev');

    // duplicate parent associations
    getDatabase()->exec('CREATE TEMPORARY TABLE tmp_dev_parents SELECT grp_id, dev_id FROM dev_parents WHERE dev_id = '.intval($dev_id));
    getDatabase()->exec('INSERT INTO dev_parents (grp_id, dev_id) SELECT grp_id, $new_dev_id FROM tmp_dev_parents WHERE dev_id = '.intval($dev_id));
    getDatabase()->exec('DROP TABLE tmp_dev_parents');

    // duplicate view
    getDatabase()->exec('CREATE TEMPORARY TABLE tmp_view SELECT object_id, object_type, graph_id, type, pos, separator_text, subdev_id FROM view WHERE object_id = '.intval($dev_id).' AND object_type="device"');
    getDatabase()->exec('INSERT INTO view (object_id, object_type, graph_id, type, pos, separator_text, subdev_id) SELECT $new_dev_id, object_type, graph_id, type, pos, separator_text, subdev_id FROM tmp_view WHERE object_id = '.intval($dev_id).' AND object_type="device"');
    getDatabase()->exec('DROP TABLE tmp_view');

    // duplicate subdevices
    $res = getDatabase()->query('SELECT id FROM sub_devices WHERE dev_id = '.intval($dev_id));
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        duplicate_subdevice($row['id'], $new_dev_id);
    }
}

function duplicate_subdevice($subdev_id, $new_parent = -1) {
    if ($new_parent == -1) {
        $new_parent = "dev_id";
        $name       = "concat(name, ' (duplicate)')";
    }
    else {
        $name = "name";
    }

    // duplicate subdevice
    getDatabase()->exec('CREATE TEMPORARY TABLE tmp_sub_devices SELECT id, dev_id, type, name FROM sub_devices WHERE id = '.intval($subdev_id));
    getDatabase()->exec('INSERT INTO sub_devices (dev_id, type, name) SELECT $new_parent, type, '.$name.' FROM tmp_sub_devices WHERE id = '.intval($subdev_id));
    $new_subdev_id = getDatabase()->lastInsertId();
    getDatabase()->exec('DROP TABLE tmp_sub_devices');

    // duplicate parameters
    getDatabase()->exec('CREATE TEMPORARY TABLE tmp_sub_dev_variables SELECT sub_dev_id, name, value, type FROM sub_dev_variables WHERE sub_dev_id = '.intval($subdev_id));
    getDatabase()->exec('INSERT INTO sub_dev_variables (sub_dev_id, name, value, type) SELECT '.intval($new_subdev_id).', name, value, type FROM tmp_sub_dev_variables WHERE sub_dev_id = '.intval($subdev_id));
    getDatabase()->exec('DROP TABLE tmp_sub_dev_variables');

    if ($new_parent != "dev_id") {
        // translate subdevices on device view
        $res = getDatabase()->query('SELECT id FROM view WHERE object_id = '.intval($new_parent).' AND object_type = "device" AND type = "template" AND subdev_id = '.intval($subdev_id));
        $s   = getDatabase()->prepare('UPDATE view SET subdev_id = :subdev_id WHERE id = :id');
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $s->bindValue(':subdev_id', $new_subdev_id);
            $s->bindValue(':id', $row['id']);
            $s->execute();
        }
    }

    // duplicate monitors
    $res = getDatabase()->query('SELECT id FROM monitors WHERE sub_dev_id = '.intval($subdev_id));
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        duplicate_monitor($row['id'], $new_subdev_id);
    }
}

function duplicate_monitor($mon_id, $new_parent = "sub_dev_id") {
    // duplicate monitor
    getDatabase()->exec('CREATE TEMPORARY TABLE tmp_monitors SELECT id, sub_dev_id, data_type, min_val, max_val, test_type, test_id, test_params FROM monitors WHERE id = '.intval($mon_id));
    getDatabase()->exec('INSERT INTO monitors (sub_dev_id, data_type, min_val, max_val, test_type, test_id, test_params) SELECT '.$new_parent.', data_type, min_val, max_val, test_type, test_id, test_params FROM tmp_monitors WHERE id = '.intval($mon_id));
    $new_mon_id = getDatabase()->lastInsertId();
    getDatabase()->exec('DROP TABLE tmp_monitors');

    // duplicate events
    $res = getDatabase()->query('SELECT id FROM events WHERE mon_id = '.intval($mon_id));
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        duplicate_event($row['id'], $new_mon_id);
    }
}

function duplicate_event($ev_id, $new_parent = -1) {
    if ($new_parent == -1) {
        $new_parent = "mon_id";
        $name       = "concat(name, ' (duplicate)')";
    }
    else {
        $name = "name";
    }

    // duplicate event
    getDatabase()->exec('CREATE TEMPORARY TABLE tmp_events SELECT id, mon_id, trigger_type, situation, name FROM events WHERE id = '.intval($ev_id));
    getDatabase()->exec('INSERT INTO events (mon_id, trigger_type, situation, name) SELECT '.$new_parent.', trigger_type, situation, '.$name.' FROM tmp_events WHERE id = '.intval($ev_id));
    $new_ev_id = getDatabase()->lastInsertId();
    getDatabase()->exec('DROP TABLE tmp_events');

    // duplicate conditions
    getDatabase()->exec('CREATE TEMPORARY TABLE tmp_conditions SELECT event_id, value, condition, logic_condition, value_type FROM conditions WHERE event_id = '.intval($ev_id));
    getDatabase()->exec('INSERT INTO conditions (event_id, value, condition, logic_condition, value_type) SELECT '.$new_ev_id.', value, condition, logic_condition, value_type FROM tmp_conditions WHERE event_id = '.intval($ev_id));
    getDatabase()->exec('DROP TABLE tmp_conditions');

    // duplicate responses
    $res = getDatabase()->query('SELECT id FROM responses WHERE event_id = '.intval($ev_id));
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        duplicate_response($row['id'], $new_ev_id);
    }
}

function duplicate_response($rsp_id, $new_parent = "event_id") {
    getDatabase()->exec('CREATE TEMPORARY TABLE tmp_responses SELECT id, event_id, notification_id, parameters FROM responses WHERE id = '.intval($rsp_id));
    getDatabase()->exec('INSERT INTO responses (event_id, notification_id, parameters) SELECT '.$new_parent.', notification_id, parameters FROM tmp_responses WHERE id = '.intval($rsp_id));
    getDatabase()->exec('DROP TABLE tmp_responses');
}

function duplicate_graph($graph_id) {
    getDatabase()->exec('CREATE TEMPORARY TABLE tmp_graphs SELECT id, name, title, comment, width, height, vert_label, type, base, options, max, min FROM graphs WHERE id = '.intval($graph_id));
    getDatabase()->exec('INSERT INTO graphs (name, title, comment, width, height, vert_label, type, base, options, max, min) SELECT concat(name, " (duplicate)"), title, comment, width, height, vert_label, type, base, options, max, min FROM tmp_graphs WHERE id = '.intval($graph_id));
    $new_grp_id = getDatabase()->lastInsertId();
    getDatabase()->exec('DROP TABLE tmp_graphs');

    $ds_handle = getDatabase()->query('SELECT * FROM graph_ds WHERE graph_id = '.intval($graph_id));
    while ($row = $ds_handle->fetch(PDO::FETCH_ASSOC)) {
        duplicate_graph_item($row['id'], $new_grp_id);
    }
}

function duplicate_graph_item($item_id, $new_parent = -1) {
    if ($new_parent == -1) {
        $new_parent  = "graph_id";
        $label       = "concat(label, ' (duplicate)')";
        $move_others = true;
    }
    else {
        $label       = "label";
        $move_others = false;
    }

    getDatabase()->exec('CREATE TEMPORARY TABLE tmp_graph_ds SELECT id, mon_id, color, type, graph_id, label, alignment, stats, position, multiplier, start_time, end_time FROM graph_ds WHERE id = '.intval($item_id));
    getDatabase()->exec('INSERT INTO graph_ds (mon_id, color, type, graph_id, label, alignment, stats, position, multiplier, start_time, end_time) SELECT mon_id, color, type, '.$new_parent.', '.$label.', alignment, stats, position, multiplier, start_time, end_time FROM tmp_graph_ds WHERE id = '.intval($item_id));
    $new_id = getDatabase()->lastInsertId();
    getDatabase()->exec('DROP TABLE tmp_graph_ds');
    if ($move_others) {
        $dq = getDatabase()->query('SELECT graph_id, position FROM graph_ds WHERE id = '.intval($item_id));
        $dr = $dq->fetch(PDO::FETCH_ASSOC);
        getDatabase()->exec('UPDATE graph_ds SET position = position + 1 WHERE graph_id = '.intval($dr['graph_id']).' AND position > '.intval($dr['position']));
        getDatabase()->exec('UPDATE graph_ds SET position = position + 1 WHERE id = '.intval($new_id));
    }
}

function move_graph_item($graph_id, $graph_item_id, $direction) {
    $query   = getDatabase()->query('SELECT id, position FROM graph_ds WHERE graph_id = '.intval($graph_id).' ORDER BY position');
    $numrows = getDatabase()
               ->query('SELECT COUNT(id) FROM graph_ds WHERE graph_id = '.intval($graph_id))
               ->fetchColumn();
    $row     = array("id" => 0, "position" => 0);
    for ($ds_count = 0; $ds_count < $numrows; $ds_count++) {
        $last_row = $row;
        $row      = $query->fetch(PDO::FETCH_ASSOC);

        if ($direction == "up") {
            if ($graph_item_id == $row['id']) {
                getDatabase()->exec('UPDATE graph_ds SET position = '.intval($last_row['position']).' WHERE id = '.intval($row['id']));
                getDatabase()->exec('UPDATE graph_ds SET position = '.intval($row['position']).' WHERE id = '.intval($last_row['id']));
                break;
            }
        }
        else {
            if ($graph_item_id == $row['id']) {
                $next_row = $query->fetch(PDO::FETCH_ASSOC);
                getDatabase()->exec('UPDATE graph_ds SET position = '.intval($next_row['position']).' WHERE id = '.intval($row['id']));
                getDatabase()->exec('UPDATE graph_ds SET position = '.intval($row['position']).' WHERE id = '.intval($next_row['id']));
                break;
            }
        }
    }
}

function delete_view_item($item_id) {
    $r = getDatabase()
         ->query('SELECT pos, object_id, object_type FROM view WHERE id = '.intval($item_id))
         ->fetch(PDO::FETCH_ASSOC);

    $pos = $r["pos"];

    getDatabase()->exec('DELETE FROM view WHERE id='.intval($item_id));

    $s = getDatabase()->prepare('UPDATE view SET pos = pos - 1 WHERE object_id = :object_id AND object_type = :object_type AND pos > :pos');
    $s->bindValue(':object_id', $r["object_id"]);
    $s->bindValue(':object_type', $r["object_type"]);
    $s->bindValue(':pos', $pos);
    $s->execute();

}

function is_view_item_extreme($object_id, $object_type, $item_id, $which) {
    $query = getDatabase()->prepare('SELECT id, pos FROM view WHERE object_id = :object_id AND object_type = :object_type ORDER BY pos :which');
    $query->bindValue(':object_id', $object_id);
    $query->bindValue(':object_type', $object_type);
    $query->bindValue(':which', $which);
    $query->execute();

    $row = $query->fetch(PDO::FETCH_ASSOC);

    return ($row['id'] == $item_id);
}

function is_view_item_top($object_id, $object_type, $item_id) {
    return is_view_item_extreme($object_id, $object_type, $item_id, "ASC");
}

function is_view_item_bottom($object_id, $object_type, $item_id) {
    return is_view_item_extreme($object_id, $object_type, $item_id, "DESC");
}

function move_view_item($object_id, $object_type, $item_id, $direction) {

    $numrows = getDatabase()->prepare('SELECT COUNT(id) FROM view WHERE object_id = :object_id AND object_type = :object_type');
    $numrows->bindValue(':object_id', $object_id);
    $numrows->bindValue(':object_type', $object_type);
    $numrows->execute();
    $numrows = $numrows->fetchColumn();
    $query   = getDatabase()->prepare('SELECT id, pos FROM view WHERE object_id = :object_id AND object_type = :object_type ORDER BY pos');
    $query->bindValue(':object_id', $object_id);
    $query->bindValue(':object_type', $object_type);
    $query->execute();

    $row = array("id" => 0, "pos" => 0);

    $s = getDatabase()->prepare('UPDATE view SET pos = :pos WHERE object_id = :object_id AND object_type = :object_type AND id = :id');
    for ($i = 0; $i < $numrows; $i++) {
        $last_row = $row;
        $row      = $query->fetch(PDO::FETCH_ASSOC);

        if ($direction == "up") {
            if ($item_id == $row['id']) {
                //$next_row = db_fetch_array($query); todo I think this is a bug, so I will remove it here, but I leave this notice, you know, why ;)
                $s->bindValue(':pos', $last_row['pos']);
                $s->bindValue(':object_id', $object_id);
                $s->bindValue(':object_type', $object_type);
                $s->bindValue(':id', $row['id']);
                $s->execute();
                $s->bindValue(':pos', $row['pos']);
                $s->bindValue(':object_id', $object_id);
                $s->bindValue(':object_type', $object_type);
                $s->bindValue(':id', $last_row['id']);
                $s->execute();
                break;
            }
        }
        else {
            if ($item_id == $row['id']) {
                $next_row = $query->fetch(PDO::FETCH_ASSOC);
                $s->bindValue(':pos', $next_row['pos']);
                $s->bindValue(':object_id', $object_id);
                $s->bindValue(':object_type', $object_type);
                $s->bindValue(':id', $row['id']);
                $s->execute();
                $s->bindValue(':pos', $row['pos']);
                $s->bindValue(':object_id', $object_id);
                $s->bindValue(':object_type', $object_type);
                $s->bindValue(':id', $next_row['id']);
                $s->execute();

                break;
            }
        }
    }
}

function move_view_item_top($object_id, $object_type, $item_id) {
    while (!is_view_item_top($object_id, $object_type, $item_id)) {
        move_view_item($object_id, $object_type, $item_id, "up");
    }
}

function move_view_item_bottom($object_id, $object_type, $item_id) {
    while (!is_view_item_bottom($object_id, $object_type, $item_id)) {
        move_view_item($object_id, $object_type, $item_id, "down");
    }
}

// Recursive Deletion Section (for orphan prevention if nothing else)

function delete_group($group_id) {
    // get group info
    $grp_info_handle = getDatabase()->query('SELECT * FROM groups WHERE id = '.intval($group_id));
    $grp_info        = $grp_info_handle->fetch(PDO::FETCH_ASSOC);

    // reparent children groups
    $s = getDatabase()->prepare('UPDATE groups SET parent_id = :parent_id_new WHERE parent_id = :parent_id');
    $s->bindValue(':parent_id_new', $grp_info['parent_id']);
    $s->bindValue(':parent_id', $group_id);
    $s->execute();

    // delete the group
    getDatabase()->exec('DELETE FROM groups WHERE id = '.getDatabase()->quote($group_id));

    // delete the associated graphs
    getDatabase()->exec('DELETE FROM view WHERE object_type = "group" AND object_id = '.getDatabase()->quote($group_id));

    // get devices in this group
    $devs_in_grp_handle = getDatabase()->query('SELECT dev_id FROM dev_parents WHERE grp_id = '.getDatabase()->quote($group_id));
    $devs_in_grp        = array();
    while ($r = $devs_in_grp_handle->fetch(PDO::FETCH_ASSOC)) { // fetchAll()??
        array_push($devs_in_grp, $r["dev_id"]);
    }

    // delete devices from this group
    getDatabase()->exec('DELETE FROM dev_parents WHERE grp_id = '.getDatabase()->quote($group_id));

    // for each device we had, if it no longer has parents, delete it
    $s = getDatabase()->prepare('SELECT 1 FROM dev_parents WHERE dev_id = :dev_id');
    foreach ($devs_in_grp as $device_id) {
        $s->bindValue(':dev_id', $device_id);
        $s->execute();
        $dev_res = $s->fetchColumn();
        if ($dev_res != 1) {
            delete_device($device_id);
        }
    }
}


function delete_device($device_id, $group_id = false) {
    if ($group_id !== false) {
        // 'unparent' the device
        $s = getDatabase()->prepare('DELETE FROM dev_parents WHERE dev_id = :dev_id AND grp_id = :grp_id');
        $s->bindValue(':dev_id', $device_id);
        $s->bindValue(':grp_id', $group_id);
        $s->execute();

        /** If this device is not part of any groups anymore, finish deleting it **/
        $dev_parent_row = getDatabase()
                          ->query('SELECT count(*) AS count FROM dev_parents WHERE dev_id = '.intval($device_id))
                          ->fetch(PDO::FETCH_ASSOC);
    }
    else {
        $dev_parent_row["count"] = 0;
    }

    if ($dev_parent_row["count"] == 0) {
        // delete the device
        getDatabase()->exec('DELETE FROM devices WHERE id = '.intval($device_id));

        // remove the interface for the device
        getDatabase()->exec('DELETE FROM snmp_interface_cache WHERE dev_id = '.intval($device_id));

        // remove the disk cache for the device
        getDatabase()->exec('DELETE FROM snmp_disk_cache WHERE dev_id = '.intval($device_id));

        // remove the device properties
        getDatabase()->exec('DELETE FROM dev_prop_vals WHERE dev_id = '.intval($device_id));

        // remove associated graphs
        getDatabase()->exec('DELETE FROM view WHERE object_type = "device" AND object_id = '.intval($device_id));

        $subdev_handle = getDatabase()->query('SELECT id FROM sub_devices WHERE dev_id = '.intval($device_id));

        while ($subdev_row = $subdev_handle->fetch(PDO::FETCH_ASSOC)) {
            delete_subdevice($subdev_row["id"]);
        }
    }
}


function delete_subdevice($subdev_id) {
    // delete the subdevice
    getDatabase()->exec('DELETE FROM sub_devices WHERE id = '.intval($subdev_id));

    // delete the subdevice parameters
    getDatabase()->exec('DELETE FROM sub_dev_variables WHERE sub_dev_id = '.intval($subdev_id));

    $monitors_handle = getDatabase()->query('SELECT id FROM monitors WHERE sub_dev_id = '.intval($subdev_id));

    while ($monitor_row = $monitors_handle->fetch(PDO::FETCH_ASSOC)) {
        delete_monitor($monitor_row["id"]);
    }

    $view_handle = getDatabase()->query('SELECT id FROM view WHERE type = "template" AND subdev_id = '.intval($subdev_id));

    while ($view_row = $view_handle->fetch(PDO::FETCH_ASSOC)) {
        delete_view_item($view_row['id']);
    }
}


function delete_monitor($monitor_id) {
    // check things that depend on this
    // * custom graphs
    // * template graphs
    getDatabase()->exec('UPDATE graph_ds SET mon_id = -1, type = 4, multiplier = 0 WHERE mon_id = '.intval($monitor_id));
    getDatabase()->exec('DELETE FROM monitors WHERE id = '.intval($monitor_id));

    $events_handle = getDatabase()->query('SELECT id FROM events WHERE mon_id = '.intval($monitor_id));
    while ($event_row = $events_handle->fetch(PDO::FETCH_ASSOC)) {
        delete_event($event_row["id"]);
    }
}


function delete_event($event_id) {

    getDatabase()->exec('DELETE FROM events WHERE id = '.intval($event_id));
    getDatabase()->exec('DELETE FROM conditions WHERE event_id = '.intval($event_id));

    $responses_handle = getDatabase()->query('SELECT id FROM responses WHERE event_id = '.intval($event_id));

    while ($response_row = $responses_handle->fetch(PDO::FETCH_ASSOC)) {
        delete_response($response_row["id"]);
    }
}


function delete_response($response_id) {
    getDatabase()->exec('DELETE FROM responses WHERE id = '.intval($response_id));
}


function delete_graph($graph_id) {
    // delete the graph
    getDatabase()->exec('DELETE FROM graphs WHERE id = '.intval($graph_id));

    // delete the graphs from associated graphs
    getDatabase()->exec('DELETE FROM view WHERE graph_id = '.intval($graph_id).' AND (type = "graph" OR type = "template")');

    $ds_handle = getDatabase()->query('SELECT id FROM graph_ds WHERE graph_id = '.intval($graph_id));

    while ($ds_row = $ds_handle->fetch(PDO::FETCH_ASSOC)) {
        delete_ds($ds_row["id"]);
    }
}


function delete_ds($ds_id) {
    $r = getDatabase()
         ->query('SELECT graph_id, position FROM graph_ds WHERE id = '.intval($ds_id))
         ->fetch(PDO::FETCH_ASSOC);

    getDatabase()->exec('DELETE FROM graph_ds WHERE id = '.intval($ds_id));
    getDatabase()->exec('UPDATE graph_ds SET position = position - 1 WHERE graph_id = '.intval($r['graph_id']).' AND position > '.intval($r['position']));
}


function create_group($grp_name, $grp_comment, $parent_id) {
    $s = getDatabase()->prepare('INSERT INTO groups (name, comment, parent_id) VALUES (:name, :comment, :parent_id)');
    $s->bindValue(':name', $grp_name);
    $s->bindValue(':comment', $grp_comment);
    $s->bindValue(':parent_id', $parent_id);
    $s->execute();
}


function update_group($id, $grp_name, $grp_comment, $parent_id) {
    $s = getDatabase()->prepare('UPDATE groups SET name = :name, comment = :comment, parent_id = :parent_id WHERE id = :id');
    $s->bindValue(':name', $grp_name);
    $s->bindValue(':comment', $grp_comment);
    $s->bindValue(':parent_id', $parent_id);
    $s->bindValue(':id', $id);
    $s->execute();
}

/**
 * CreateLocalMenu():
 *
 * creates a local version of the menu w/ only the user's authorized items
 */
function CreateLocalMenu() {
    global $MENU, $LOCAL_MENU, $LOCAL_MENU_CURTREE, $LOCAL_MENU_CURITEM, $session;

    while (list($menuname, $menuitems) = each($MENU)) {
        // foreach menu item
        $authorized_subitems = array();
        foreach ($menuitems as $menuitem) {
            if (basename($_SERVER["SCRIPT_NAME"]) == $menuitem["link"]) {
                $LOCAL_MENU_CURTREE = $menuname;
                $LOCAL_MENU_CURITEM = $menuitem["link"];
            } // end if we're in this group, display its menu items

            if ($session->get('permit') >= $menuitem["authLevelRequired"]
                && $menuitem["display"] !== false
            ) {
                array_push($authorized_subitems, $menuitem);
            } // end if we have enough permissions to view this subitem
        } // end foreach menu item

        // if we had some item output (ie, we had auth to view at least ONE item in this submenu)
        // and we're under this current menu heading
        if (count($authorized_subitems)) {
            $LOCAL_MENU[$menuname] = $authorized_subitems;
        } // end if item output wasn't empty
    } // end while we still have menu items
} // end CreateLocalMenu();


/**
 * GetUserPref($module, $pref)
 *
 * returns the value for the $module and $pref wanted for user $uid
 */
function GetUserPref($uid, $module, $pref) {
    $s = getDatabase()->prepare('SELECT user_prefs.value FROM user_prefs WHERE user_prefs.uid = :uid AND user_prefs.module = :module AND user_prefs.pref = :pref');
    $s->bindValue(':uid', $uid);
    $s->bindValue(':module', $module);
    $s->bindValue(':pref', $pref);
    $s->execute();

    $row = $s->fetch(PDO::FETCH_ASSOC);

    if (!empty($row)) {
        return $row["value"];
    }
    return "";
}


/**
 * SetUserPref($uid, $module, $pref, $value)
 *
 * sets the $value for the $module and $pref for user $uid
 */
function SetUserPref($uid, $module, $pref, $value) {
    $s = getDatabase()->prepare('SELECT user_prefs.id FROM user_prefs WHERE user_prefs.uid = :uid AND user_prefs.module = :module AND user_prefs.pref = :pref');
    $s->bindValue(':uid', $uid);
    $s->bindValue(':module', $module);
    $s->bindValue(':pref', $pref);
    $s->execute();

    $row = $s->fetch(PDO::FETCH_ASSOC);

    if (!empty($row)) {
        $s = getDatabase()->prepare('UPDATE user_prefs SET value = :value WHERE id = :id');
        $s->bindValue(':value', $value);
        $s->bindValue(':id', $row['id']);

    } // end if a result
    else {
        $s = getDatabase()->prepare('INSERT INTO user_prefs (uid, module, pref, value) VALUES (:uid, :module, :pref, :value)');
        $s->bindValue(':value', $value);
        $s->bindValue(':uid', $uid);
        $s->bindValue(':module', $module);
        $s->bindValue(':pref', $pref);
    } // end no result
    $s->execute();
} // end SetUserPref();


/**
 * UpdaterNeedsRun()
 *
 * returns true if the updater needs run
 */
function UpdaterNeedsRun() {
    return ($GLOBALS["netmrg"]["verhist"][$GLOBALS["netmrg"]["version"]] > $GLOBALS["netmrg"]["verhist"][getDatabase()->getDBVersion()]);
} // end UpdaterNeedsRun();


/**
 * UpdateDBVersion($ver)
 *
 * updates the version the database is in
 */
function UpdateDBVersion($ver) {
    $s = getDatabase()->prepare("UPDATE versioninfo SET version = :ver WHERE module = 'Main'");
    $s->bindValue(':ver', $ver);
    $s->execute();
}


/**
 * GetXMLConfig()
 *
 * reads xml config file and puts values in config array
 */
function GetXMLConfig() {
    $xmlconfig = GetXMLTree($GLOBALS["netmrg"]["xmlfile"]);

    // cosmetic variables
    $GLOBALS["netmrg"]["company"]     = $xmlconfig["NETMRG"][0]["WEBSITE"][0]["COMPANY"][0]["VALUE"];
    $GLOBALS["netmrg"]["companylink"] = $xmlconfig["NETMRG"][0]["WEBSITE"][0]["COMPANYLINK"][0]["VALUE"];
    $GLOBALS["netmrg"]["webhost"]     = $xmlconfig["NETMRG"][0]["WEBSITE"][0]["WEBHOST"][0]["VALUE"];
    $GLOBALS["netmrg"]["webroot"]     = $xmlconfig["NETMRG"][0]["WEBSITE"][0]["WEBROOT"][0]["VALUE"];
    if (!isset($xmlconfig["NETMRG"][0]["WEBSITE"][0]["EXTERNALAUTH"])) {
        $xmlconfig["NETMRG"][0]["WEBSITE"][0]["EXTERNALAUTH"][0]["VALUE"] = false;
    } // end set default for external auth
    if ($xmlconfig["NETMRG"][0]["WEBSITE"][0]["EXTERNALAUTH"][0]["VALUE"] == "true") {
        $GLOBALS["netmrg"]["externalAuth"] = true;
    } // end if true
    else {
        $GLOBALS["netmrg"]["externalAuth"] = false;
    } // end else false


    // DB Config
    $GLOBALS["netmrg"]["dbhost"] = $xmlconfig["NETMRG"][0]["DATABASE"][0]["HOST"][0]["VALUE"];
    $GLOBALS["netmrg"]["dbname"] = $xmlconfig["NETMRG"][0]["DATABASE"][0]["DB"][0]["VALUE"];
    $GLOBALS["netmrg"]["dbuser"] = $xmlconfig["NETMRG"][0]["DATABASE"][0]["USER"][0]["VALUE"];
    $GLOBALS["netmrg"]["dbpass"] = $xmlconfig["NETMRG"][0]["DATABASE"][0]["PASSWORD"][0]["VALUE"];
    $GLOBALS["netmrg"]["dbsock"] = $xmlconfig["NETMRG"][0]["DATABASE"][0]["SOCKET"][0]["VALUE"];
    $GLOBALS["netmrg"]["dbport"] = $xmlconfig["NETMRG"][0]["DATABASE"][0]["PORT"][0]["VALUE"];

    // Path Config
    $GLOBALS["netmrg"]["rrdtool"]  = $xmlconfig["NETMRG"][0]["PATHS"][0]["RRDTOOL"][0]["VALUE"];
    $GLOBALS["netmrg"]["rrdroot"]  = $xmlconfig["NETMRG"][0]["PATHS"][0]["RRDS"][0]["VALUE"];
    $GLOBALS["netmrg"]["fileroot"] = $xmlconfig["NETMRG"][0]["PATHS"][0]["WEBFILEROOT"][0]["VALUE"];
    $GLOBALS["netmrg"]["locale"]   = $xmlconfig["NETMRG"][0]["PATHS"][0]["LOCALE"][0]["VALUE"];

    // RRDTool Config
    $GLOBALS["netmrg"]["rrdtool_version"] = $xmlconfig["NETMRG"][0]["RRDTOOL"][0]["VERSION"][0]["VALUE"];
    if (empty($GLOBALS["netmrg"]["rrdtool_version"])) {
        $GLOBALS["netmrg"]["rrdtool_version"] = "1.0";
    }

} // end GetXMLConfig();


/**
 * PrereqsMet()
 *
 * checks if the prerequisits for running NetMRG are met
 *
 * @returns array of errors
 */
function PrereqsMet() {
    $errors = array();

    if (!version_compare(phpversion(), "5.3.0", ">=")) {
        array_push($errors, "PHP Version 5.3.0 or higher required");
    }

    // PHP Safe Mode == off; it is deprecated since 5.3
    if (ini_get("safe_mode")) {
        array_push($errors, "PHP Safe Mode not supported");
    }

    if (!is_executable($GLOBALS["netmrg"]["rrdtool"])) {
        array_push($errors, "RRD Tool not found or is not executable");
    }

    if (!is_executable($GLOBALS["netmrg"]["binary"])) {
        array_push($errors, "NetMRG Gatherer not found or not executable");
    }

    return $errors;
}
