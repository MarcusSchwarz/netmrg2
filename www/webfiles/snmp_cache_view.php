<?php
/********************************************
 * NetMRG Integrator
 *
 * snmp_cache_view.php
 * SNMP Cache Viewer
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

require_once "../include/config.php";
check_auth($GLOBALS['PERMIT']["ReadAll"]);

switch ($_REQUEST['action']) {
    case "view":
        view_cache();
        break;

    case "graph":
        check_auth($GLOBALS['PERMIT']["ReadWrite"]);
        make_graph();
        break;

    case "graphmultiint":
        check_auth($GLOBALS['PERMIT']["ReadWrite"]);
        if (isset($_REQUEST["iface"])) {
            while (list($key, $value) = each($_REQUEST["iface"])) {
                make_interface_graph($_REQUEST["dev_id"], $key);
            }
        }
        // redirect to keep us from doing this again
        header("Location: snmp_cache_view.php?dev_id={$_REQUEST['dev_id']}&tripid={$_REQUEST['tripid']}&type=interface&action=view");
        exit;

    case "graphmultidisk":
        check_auth($GLOBALS['PERMIT']["ReadWrite"]);
        if (isset($_REQUEST["dindex"])) {
            while (list($key, $value) = each($_REQUEST["dindex"])) {
                make_disk_graph($_REQUEST["dev_id"], $key);
            }
        }
        // redirect to keep us from doing this again
        header("Location: snmp_cache_view.php?dev_id={$_REQUEST['dev_id']}&tripid={$_REQUEST['tripid']}&type=disk&action=view");
        exit;
}

function view_cache() {
    switch ($_REQUEST['type']) {
        case "interface":
            view_interface_cache();
            break;
        case "disk":
            view_disk_cache();
            break;
    }
}

function make_graph() {
    switch ($_REQUEST['type']) {
        case "interface":
            make_interface_graph($_REQUEST["dev_id"], $_REQUEST["index"]);
            header("Location: snmp_cache_view.php?dev_id={$_REQUEST['dev_id']}&tripid={$_REQUEST['tripid']}&type=interface&action=view");
            exit;
        case "disk":
            make_disk_graph($_REQUEST["dev_id"], $_REQUEST["index"]);
            header("Location: snmp_cache_view.php?dev_id={$_REQUEST['dev_id']}&tripid={$_REQUEST['tripid']}&type=disk&action=view");
            exit;
    }
}

function int_column_unique($dev_id, $column) {
    $distinct = getDatabase()->prepare('SELECT COUNT(DISTINCT :column) FROM snmp_interface_cache WHERE dev_id = :dev_id');
    $distinct->bindValue(':column', $column);
    $distinct->bindValue(':dev_id', $dev_id);
    $distinct->execute();
    $distinct = $distinct->fetchColumn();

    $overall = getDatabase()->prepare('SELECT COUNT(:column) FROM snmp_interface_cache WHERE dev_id = :dev_id');
    $overall->bindValue(':column', $column);
    $overall->bindValue(':dev_id', $dev_id);
    $overall->execute();
    $overall = $overall->fetchColumn();

    return ($distinct == $overall);
}

function make_interface_graph($dev_id, $index) {
    // get snmp index data
    $r_snmp = getDatabase()
              ->query('SELECT * FROM snmp_interface_cache WHERE dev_id = '.intval($dev_id).' AND ifIndex = '.intval($index))
              ->fetch(PDO::FETCH_ASSOC);

    if (isset($r_snmp["ifName"]) && !empty($r_snmp["ifName"]) && int_column_unique($dev_id, "ifName")) {
        $index_type = "ifName";
    }
    elseif (isset($r_snmp["ifDescr"]) && !empty($r_snmp["ifDescr"]) && int_column_unique($dev_id, "ifDescr")) {
        $index_type = "ifDescr";
    }
    elseif (isset($r_snmp["ifIP"]) && !empty($r_snmp["ifIP"]) && int_column_unique($dev_id, "ifIP")) {
        $index_type = "ifIP";
    }
    else {
        $index_type = "ifIndex";
    }

    $index_value = $r_snmp[$index_type];
    $s           = getDatabase()->prepare('INSERT INTO sub_devices (dev_id, type, name) VALUES (:dev_id, :type, :name)');
    $s->bindValue(':dev_id', $dev_id);
    $s->bindValue(':type', 2);
    $s->bindValue(':name', $index_value);
    $s->execute();

    $sd_id = getDatabase()->lastInsertId();
    $s     = getDatabase()->prepare('INSERT INTO sub_dev_variables (sub_dev_id, name, value, type) VALUES (:sub_dev_id, :name, :value, :type)');
    $s->bindValue(':sub_dev_id', $sd_id);
    $s->bindValue(':name', $index_type);
    $s->bindValue(':value', $index_value);
    $s->bindValue(':type', 'static');
    $s->execute();

    $s->bindValue(':sub_dev_id', $sd_id);
    $s->bindValue(':name', 'ifIndex');
    $s->bindValue(':value', $index);
    $s->bindValue(':type', 'dynamic');
    $s->execute();

    // add monitors and associate template
    apply_template($sd_id, $_REQUEST["graph_template_id"]);

}

function make_disk_graph($dev_id, $index) {
    // get snmp index data
    $r_snmp = getDatabase()
              ->query('SELECT * FROM snmp_disk_cache WHERE dev_id = '.intval($dev_id).' AND disk_index = '.intval($index))
              ->fetch(PDO::FETCH_ASSOC);

    if (isset($r_snmp["disk_path"]) && !empty($r_snmp["disk_path"])) {
        $index_type  = "dskPath";
        $index_value = $r_snmp["disk_path"];
    }
    elseif (isset($r_snmp["disk_device"]) && !empty($r_snmp["disk_device"])) {
        $index_type  = "dskDevice";
        $index_value = $r_snmp["disk_device"];
    }
    else {
        $index_type  = "dskIndex";
        $index_value = $r_snmp["disk_index"];
    }

    $s = getDatabase()->prepare('INSERT INTO sub_devices (dev_id, type, name) VALUES (:dev_id, :type, :name)');
    $s->bindValue(':dev_id', $dev_id);
    $s->bindValue(':type', 3);
    $s->bindValue(':name', $index_value);
    $s->execute();

    $sd_id = getDatabase()->lastInsertId();
    $s     = getDatabase()->prepare('INSERT INTO sub_dev_variables (sub_dev_id, name, value, type) VALUES (:sub_dev_id, :name, :value, :type)');
    $s->bindValue(':sub_dev_id', $sd_id);
    $s->bindValue(':name', $index_type);
    $s->bindValue(':value', $index_value);
    $s->bindValue(':type', 'static');
    $s->execute();

    $s->bindValue(':sub_dev_id', $sd_id);
    $s->bindValue(':name', 'dskIndex');
    $s->bindValue(':value', $index);
    $s->bindValue(':type', 'dynamic');
    $s->execute();

    // add monitors and associate template
    apply_template($sd_id, $GLOBALS["netmrg"]["disktemplateid"]);

}

function view_disk_cache() {
    $query    = getDatabase()->query('SELECT * FROM snmp_disk_cache WHERE dev_id = '.intval($_REQUEST['dev_id']).' ORDER BY disk_index');
    $rowcount = getDatabase()
                ->query('SELECT COUNT(*) FROM snmp_disk_cache WHERE dev_id = '.intval($_REQUEST['dev_id']))
                ->fetchColumn();
    $dev_name = get_device_name($_REQUEST['dev_id']);

    begin_page("$dev_name - Disk Cache");
    js_checkbox_utils();
    PrepGroupNavHistory("device", $_REQUEST["dev_id"]);
    DrawGroupNavHistory("device", $_REQUEST["dev_id"]);

    ?>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
    <?php
    make_edit_hidden("action", "");
    make_edit_hidden("dev_id", $_REQUEST["dev_id"]);
    make_edit_hidden("tripid", $_REQUEST["tripid"]);
    make_plain_display_table("$dev_name - Disk Cache",
        checkbox_toolbar(), "",
        "Index", "",
        "Device", "",
        "Path", "",
        "", "");

    for ($i = 0; $i < $rowcount; $i++) {
        $row     = $query->fetch(PDO::FETCH_ASSOC);
        $links   = "";
        $s_query = getDatabase()->query('SELECT sub.id AS id FROM sub_devices sub, sub_dev_variables var WHERE sub.dev_id = '.intval($_REQUEST['dev_id']).' AND sub.id = var.sub_dev_id AND var.name = "dskIndex" AND var.value = '.intval($row['disk_index']));
        $s_row   = $s_query->fetch(PDO::FETCH_ASSOC);
        if (isset($s_row['id'])) {
            $links .= formatted_link("View", "view.php?action=view&object_type=subdevice&object_id={$s_row['id']}", "", "view");
            $links .= "&nbsp;";
            $links .= formatted_link("Monitors", "monitors.php?dev_id={$_REQUEST['dev_id']}&sub_dev_id={$s_row['id']}&tripid={$_REQUEST['tripid']}");
            $links .= "&nbsp;";
            $links .= formatted_link_disabled("Monitor/Graph");
        }
        else {
            $links .= formatted_link_disabled("View", "view");
            $links .= "&nbsp";
            $links .= formatted_link_disabled("Monitors");
            $links .= "&nbsp;";
            $links .= formatted_link("Monitor/Graph", "snmp_cache_view.php?action=graph&type=disk&dev_id=".$row["dev_id"]."&index=".$row["disk_index"]."&tripid={$_REQUEST['tripid']}");
        }

        make_display_item("editfield".($i % 2),
            array("checkboxname" => "dindex", "checkboxid" => $row["disk_index"], "checkdisabled" => isset($s_row['id'])),
            array("text" => $row['disk_index']),
            array("text" => $row['disk_device']),
            array("text" => $row['disk_path']),
            array("text" => $links)
        );
    }

    make_checkbox_command("", 9,
        array("text" => "Monitor/Graph All Checked", "action" => "graphmultidisk")
    );
    echo("</table>\n");
    echo("</form>\n");
    end_page();
}

function view_interface_cache() {
    $sort_href = "{$_SERVER['PHP_SELF']}?action=view&type=interface&dev_id={$_REQUEST['dev_id']}&order_by";
    $handle    = getDatabase()->query('SELECT * FROM snmp_interface_cache snmp WHERE snmp.dev_id = '.intval($_REQUEST['dev_id']));
    $results   = array();
    while ($row = $handle->fetch(PDO::FETCH_ASSOC)) { //fetchAll()?
        array_push($results, $row);
    }

    function sortme($a, $b) {
        $ob = $_REQUEST['order_by'];
        switch ($ob) {
            case "ifName":
            case "ifDescr":
                return compare_interface_names($a[$ob], $b[$ob]);
            case "ifAlias":
                return strcmp($a['ifAlias'], $b['ifAlias']);
            case "ifIP":
                return compare_ip_addresses($a['ifIP'], $b['ifIP']);
            case "ifMAC":
                return compare_mac_addresses($a['ifMAC'], $b['ifMAC']);
            default:
                return ($a['ifIndex'] - $b['ifIndex']);
        }
    }

    usort($results, 'sortme');

    $dev_name = get_device_name($_REQUEST['dev_id']);

    begin_page("$dev_name - Interface Cache");
    js_checkbox_utils();
    PrepGroupNavHistory("device", $_REQUEST["dev_id"]);
    DrawGroupNavHistory("device", $_REQUEST["dev_id"]);


    ?>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
    <?php
    make_edit_hidden("action", "");
    make_edit_hidden("dev_id", $_REQUEST["dev_id"]);
    make_edit_hidden("tripid", $_REQUEST["tripid"]);
    make_edit_hidden("type", "interface");
    make_edit_hidden("index", "");
    make_plain_display_table("$dev_name - Interface Cache",
        checkbox_toolbar(), "",
        "Index", "$sort_href=ifIndex",
        "Status", "",
        "Name", "$sort_href=ifName",
        "Description", "$sort_href=ifDescr",
        "Alias", "$sort_href=ifAlias",
        "IP Address", "$sort_href=ifIP",
        "MAC Address", "$sort_href=ifMAC",
        "", "");

    $s_query = getDatabase()->prepare('SELECT sub.id AS id FROM sub_devices sub, sub_dev_variables var WHERE sub.dev_id = :dev_id AND sub.id = var.sub_dev_id AND var.name="ifIndex" AND var.value = :value');

    for ($i = 0; $i < count($results); $i++) {
        $row    = $results[$i];
        $status = "";
        if (isset($row['ifAdminStatus'])) {
            $status .= $GLOBALS['INTERFACE_STATUS'][$row['ifAdminStatus']]."/";
        }
        if (isset($row['ifOperStatus'])) {
            $status .= $GLOBALS['INTERFACE_STATUS'][$row['ifOperStatus']]."&nbsp;";
        }
        if (isset($row['ifType'])) {
            if (isset($GLOBALS['INTERFACE_TYPE'][$row['ifType']])) {
                $status .= space_to_nbsp($GLOBALS['INTERFACE_TYPE'][$row['ifType']]);
            }
        }
        $links = "";

        $s_query->bindValue('dev_id', $_REQUEST['dev_id']);
        $s_query->bindValue('value', $row['ifIndex']);
        $s_query->execute();

        $s_row = $s_query->fetch(PDO::FETCH_ASSOC);
        if (isset($s_row['id'])) {
            $links .= formatted_link("View", "view.php?action=view&object_type=subdevice&object_id={$s_row['id']}", "", "view");
            $links .= "&nbsp;";
            $links .= formatted_link("Monitors", "monitors.php?dev_id={$_REQUEST['dev_id']}&sub_dev_id={$s_row['id']}&tripid={$_REQUEST['tripid']}");
            $links .= "&nbsp;";
            $links .= formatted_link_disabled("Monitor/Graph");
        }
        else {
            $links .= formatted_link_disabled("View", "view");
            $links .= "&nbsp";
            $links .= formatted_link_disabled("Monitors");
            $links .= "&nbsp;";
            $links .= '&lt;<a  onclick="document.form.action.value=\'graph\';document.form.index.value=\''.$row["ifIndex"].'\';document.form.submit();" href="#">Monitor/Graph</a>&gt;';
        }

        make_display_item("editfield".($i % 2),
            array("checkboxname" => "iface", "checkboxid" => $row["ifIndex"], "checkdisabled" => isset($s_row['id'])),
            array("text" => $row["ifIndex"]),
            array("text" => $status),
            array("text" => $row["ifName"]),
            array("text" => $row["ifDescr"]),
            array("text" => $row["ifAlias"]),
            array("text" => $row["ifIP"]),
            array("text" => $row["ifMAC"]),
            array("text" => $links)
        );
    }
    echo "<tr>\n";
    echo '<td colspan="5" class="editheader" nowrap="nowrap">';
    echo "Checked Items:&nbsp;&nbsp;\n";
    echo '&lt;<a class="editheaderlink" onclick="document.form.action.value=\'graphmultiint\';document.form.submit();" href="#">Monitor/Graph All Checked</a>&gt;</td>';
    echo '<td colspan="4" class="editheader" nowrap="nowrap" align="right">'."\n";
    echo '<select name="graph_template_id">'."\n";
    DrawSelectOptionsFromSQL("graphs", $GLOBALS["netmrg"]["traffictemplateid"]);
    echo "</select>\n";
    echo "</td>\n";
    echo "</tr>\n";
    echo "</table>\n";
    echo "</form>\n";
    end_page();
}
