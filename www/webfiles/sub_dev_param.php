<?php
/********************************************
 * NetMRG Integrator
 *
 * sub_dev_param.php
 * Sub-Devices Parameters Page
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
$auth->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["ReadAll"]);

if (empty($_REQUEST["action"])) {
    // Display the list of sub-devices for a particular device.

    begin_page("Sub Device Parameters");
    PrepGroupNavHistory("sub_device", $_REQUEST["sub_dev_id"]);
    DrawGroupNavHistory("sub_device", $_REQUEST["sub_dev_id"]);
    js_confirm_dialog("del", "Are you sure you want to delete subdevice parameter ", "", "{$_SERVER['PHP_SELF']}?action=dodelete&sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}&name=");

    $results  = getDatabase()->query('SELECT name, value FROM sub_dev_variables WHERE type = "static" AND sub_dev_id = '.intval($_REQUEST['sub_dev_id']));
    $rowcount = getDatabase()
                ->query('SELECT COUNT(name) FROM sub_dev_variables WHERE type = "static" AND sub_dev_id = '.intval($_REQUEST['sub_dev_id']))
                ->fetchColumn();

    make_display_table("Configured Parameters for ".get_dev_sub_device_name($_REQUEST["sub_dev_id"]),
        "{$_SERVER['PHP_SELF']}?action=add&sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}",
        array("text" => "Name"),
        array("text" => "Value")
    );

    for ($i = 0; $i < $rowcount; $i++) {
        $row = $results->fetch(PDO::FETCH_ASSOC);
        make_display_item("editfield".($i % 2),
            array("text" => $row["name"]),
            array("text" => $row["value"]),
            array("text" => formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}&name=".$row["name"])."&nbsp;".
                            formatted_link("Delete", "javascript:del('".addslashes(htmlspecialchars($row['name']))."', '".addslashes(htmlspecialchars($row['name']))."')"), "")
        );
    }

    ?>
    </table><br><br>
    <?php

    $results  = getDatabase()->query('SELECT name, value FROM sub_dev_variables WHERE type = "dynamic" AND sub_dev_id = '.intval($_REQUEST['sub_dev_id']));
    $rowcount = getDatabase()
                ->query('SELECT COUNT(name) FROM sub_dev_variables WHERE type = "dynamic" AND sub_dev_id = '.intval($_REQUEST['sub_dev_id']))
                ->fetchColumn();

    make_display_table("Dynamic Parameters for ".get_dev_sub_device_name($_REQUEST["sub_dev_id"]), "#",
        array("text" => "Name"),
        array("text" => "Value")
    );

    for ($i = 0; $i < $rowcount; $i++) {
        $row = $results->fetch(PDO::FETCH_ASSOC);
        make_display_item("editfield".($i % 2),
            array("text" => $row["name"]),
            array("text" => $row["value"]),
            array("text" => "")
        );
    }

    ?>
    </table>
    <?php

    end_page();
}
elseif ($_REQUEST["action"] == "doedit") {
    $auth->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["ReadWrite"]);
    if ($_REQUEST["type"] == "add") {
        $s = getDatabase()->prepare('INSERT INTO sub_dev_variables (name, value, sub_dev_id) VALUES (:name, :value, :sub_dev_id)');
    }
    else {
        $s = getDatabase()->prepare('UPDATE sub_dev_variables SET name = :name, value = :value, sub_dev_id = :sub_dev_id WHERE name = :oldname AND sub_dev_id = :sub_dev_id');
        $s->bindValue(':oldname', $_REQUEST['oldname']);
    }
    $s->bindValue(':name', $_REQUEST['name']);
    $s->bindValue(':value', $_REQUEST['value']);
    $s->bindValue(':sub_dev_id', $_REQUEST['sub_dev_id']);
    $s->execute();

    header("Location: ".$_SERVER["PHP_SELF"]."?sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}");
}
elseif (($_REQUEST["action"] == "edit") || ($_REQUEST["action"] == "add")) {
    $auth->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["ReadWrite"]);
    begin_page("Add/Edit Sub Device Parameter");
    make_edit_table("Sub-Device Parameter");

    if ($_REQUEST["action"] == "edit") {
        $query = getDatabase()->prepare('SELECT * FROM sub_dev_variables WHERE sub_dev_id = :sub_dev_id AND name = :name');
        $query->bindValue(':sub_dev_id', $_REQUEST['sub_dev_id']);
        $query->bindValue(':name', $_REQUEST['name']);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);

        if (!empty($row)) {
            make_edit_hidden("oldname", $row['name']);
        }
    }
    else {
        $row["name"]  = "";
        $row["value"] = "";
    }

    make_edit_text("Name:", "name", 40, 80, $row["name"]);
    make_edit_text("Value:", "value", 40, 80, $row["value"]);
    make_edit_hidden("type", $_REQUEST['action']);
    make_edit_hidden("action", "doedit");
    make_edit_hidden("sub_dev_id", $_REQUEST["sub_dev_id"]);
    make_edit_hidden("tripid", $_REQUEST["tripid"]);
    make_edit_submit_button();
    make_edit_end();
    end_page();

}
elseif ($_REQUEST["action"] == "dodelete") {
    $auth->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["ReadWrite"]);
    $s = getDatabase()->prepare('DELETE FROM sub_dev_variables WHERE sub_dev_id = :sub_dev_id AND name = :name AND type = "static"');
    $s->bindValue(':sub_dev_id', $_REQUEST['sub_dev_id']);
    $s->bindValue(':name', $_REQUEST['name']);
    $s->execute();
    header("Location: ".$_SERVER["PHP_SELF"]."?sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}");
}
