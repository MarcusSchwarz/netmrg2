<?php
/********************************************
 * NetMRG Integrator
 *
 * mon_notify.php
 * Event Notification Editing Page
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

if (!empty($_REQUEST["action"])) {
    $action = $_REQUEST["action"];
}
else {
    $action = "";
}

switch ($action) {
    case "doedit":
    case "doadd":
        check_auth($GLOBALS['PERMIT']["ReadWrite"]);
        do_addedit();
        break;

    case "add":
        check_auth($GLOBALS['PERMIT']["ReadWrite"]);
    case "edit":
        addedit();
        break;

    case "dodelete":
        check_auth($GLOBALS['PERMIT']["ReadWrite"]);
        do_delete();
        break;

    case "duplicate":
        check_auth($GLOBALS['PERMIT']["ReadWrite"]);
        duplicate();
        break;

    default:
        display();
        break;
}

function duplicate() {
    $q = getDatabase()->query('SELECT * FROM notifications WHERE id = '.intval($_REQUEST['id']));
    $r = $q->fetch(PDO::FETCH_ASSOC);

    $s = getDatabase()->prepare('INSERT INTO notifications (name, command, disabled) VALUES (:name, :command, :disabled)');
    $s->bindValue(':name', $r['name'].' (duplicate)');
    $s->bindValue(':command', $r['command']);
    $s->bindValue(':disabled', $r['disabled']);
    $s->execute();

    header("Location: {$_SERVER['PHP_SELF']}");
}

function do_addedit() {
    if ($_REQUEST["id"] == 0) {
        $s = getDatabase()->prepare('INSERT INTO notifications (name, command, disabled) VALUES (:name, :command, :disabled)');
    }
    else {
        $s = getDatabase()->prepare('UPDATE notifications SET name = :name, command = :command, disabled = :disabled WHERE id = :id');
        $s->bindValue(':id', $_REQUEST['id']);
    }
    if (empty($_REQUEST["disabled"])) {
        $_REQUEST["disabled"] = "";
    }

    $s->bindValue(':name', $_REQUEST['name']);
    $s->bindValue(':command', $_REQUEST['command']);
    $s->bindValue(':disabled', $_REQUEST['disabled']);
    $s->execute();

    header("Location: {$_SERVER['PHP_SELF']}");
}

function do_delete() {
    getDatabase()->exec('DELETE FROM notifications WHERE id = '.intval($_REQUEST['id']));
    header("Location: {$_SERVER['PHP_SELF']}");
}

function display() {
    // Display a list
    begin_page("notifications.php", "Notifications");
    js_confirm_dialog("del", "Are you sure you want to delete notification ", " ?", "{$_SERVER['PHP_SELF']}?action=dodelete&id=");

    make_display_table("Notifications", "",
        array("text" => "Name"),
        array("text" => "Disabled"),
        array("text" => "Command")
    );

    $res = getDatabase()->query('SELECT * FROM notifications');

    $i = 0;
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        make_display_item("editfield".($i % 2),
            array("text" => $row["name"]),
            array("text" => ($row['disabled'] == 1 ? "Yes" : "No")),
            array("text" => $row["command"]),
            array("text" => formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&id={$row['id']}", "", "edit")."&nbsp;".
                            formatted_link("Duplicate", "{$_SERVER["PHP_SELF"]}?action=duplicate&id=".$row['id'], "", "duplicate")."&nbsp;".
                            formatted_link("Delete", "javascript:del('".addslashes($row['name'])."', '{$row['id']}')", "", "delete"))
        );
        $i++;
    }

    ?>
    </table>
    <?php
    end_page();
}

function addedit() {
    GLOBAL $action;
    if (!empty($action) && ($action == "edit" || $action == "add")) {
        begin_page("notifications.php", "Notifications");

        $id  = ($action == "add") ? 0 : $_REQUEST["id"];
        $res = getDatabase()->query('SELECT * FROM notifications WHERE id = '.intval($id));
        $row = $res->fetch(PDO::FETCH_ASSOC);

        make_edit_table("Edit Notificiation Method");
        make_edit_text("Name:", "name", "50", "100", $row["name"]);
        make_edit_textarea("Command:", "command", "1", "40", $row["command"]);
        make_edit_label("You may use keywords %dev_name%, %ip%, %event_name%, %situation%, %current_value%, %delta_value%, %rate_value%, and %last_value% in your command parameters.  See the documentation for details.");
        make_edit_checkbox("Disabled", "disabled", $row["disabled"]);
        make_edit_hidden("id", $id);
        make_edit_hidden("action", "doedit");
        make_edit_submit_button();
        make_edit_end();
    }

    end_page();
}
