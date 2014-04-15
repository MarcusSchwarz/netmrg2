<?php
/********************************************
 * NetMRG Integrator
 *
 * events.php
 * Events Editing Page
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

switch ($_REQUEST['action']) {
    case "add":
        $auth->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["ReadWrite"]);
    case "edit":
        display_edit();
        break;

    case "doedit":
        $auth->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["ReadWrite"]);
        do_edit();
        break;

    case "multidodelete":
    case "dodelete":
        $auth->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["ReadWrite"]);
        do_delete();
        break;

    default:
        do_display();
}

function do_display() {

    // Display a list
    begin_page("Events");
    js_checkbox_utils();
    ?>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
        <input type="hidden" name="action" value="">
        <input type="hidden" name="mon_id" value="<?php echo $_REQUEST['mon_id']; ?>">
        <input type="hidden" name="tripid" value="<?php echo $_REQUEST['tripid']; ?>">
        <?php
        PrepGroupNavHistory("monitor", $_REQUEST["mon_id"]);
        DrawGroupNavHistory("monitor", $_REQUEST["mon_id"]);

        $title = "Events for ".get_monitor_name($_REQUEST['mon_id']);
        js_confirm_dialog("del", "Are you sure you want to delete event ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&mon_id={$_REQUEST['mon_id']}&tripid={$_REQUEST['tripid']}&id=");
        make_display_table($title, "{$_SERVER['PHP_SELF']}?action=add&mon_id={$_REQUEST['mon_id']}&tripid={$_REQUEST['tripid']}",
            array("text" => checkbox_toolbar()),
            array("text" => "Name"),
            array("text" => "Trigger Options"),
            array("text" => "Situation"),
            array("text" => "Status")
        );

        $s = getDatabase()->prepare('SELECT * FROM events WHERE mon_id = :id ORDER BY name');
        $s->bindValue(':id', $_REQUEST['mon_id']);
        $s->execute();


        $rowcount = 0;
        while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
            if ($row['last_status'] == 1) {
                $triggered = "<b>Triggered</b>";
                $name      = "<b>".$row['name']."</b>";
            }
            else {
                $triggered = "Not Triggered";
                $name      = $row['name'];
            }

            make_display_item("editfield".($rowcount % 2),
                array("checkboxname" => "event", "checkboxid" => $row['id']),
                array("text" => $name, "href" => "responses.php?event_id={$row['id']}&tripid={$_REQUEST['tripid']}"),
                array("text" => $GLOBALS['TRIGGER_TYPES'][$row['trigger_type']]),
                array("text" => $GLOBALS['SITUATIONS'][$row['situation']]),
                array("text" => $triggered),
                array("text" => formatted_link("Modify Conditions", "conditions.php?event_id={$row['id']}&tripid={$_REQUEST['tripid']}")."&nbsp;".
                                formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&id={$row['id']}&tripid={$_REQUEST['tripid']}", "", "edit")."&nbsp;".
                                formatted_link("Delete", "javascript:del('".addslashes($row['name'])."','".$row['id']."')", "", "delete"))
            );
            $rowcount++;
        }
        make_checkbox_command("", 6,
            array("text" => "Delete", "action" => "multidodelete", "prompt" => "Are you sure you want to delete the checked events?")
        );
        make_status_line("event", $rowcount);
        ?>
        </table>
    </form>
    <?php
    end_page();
}

function display_edit() {
    begin_page("Edit Event");

    if ($_REQUEST['action'] == "add") {
        $row['id']           = 0;
        $row['mon_id']       = $_REQUEST['mon_id'];
        $row['name']         = "";
        $row['trigger_type'] = 2;
        $row['situation']    = 1;
    }
    else {
        $query = getDatabase()->query('SELECT * FROM events WHERE id = '.intval($_REQUEST['id']));
        $row   = $query->fetch(PDO::FETCH_ASSOC);
    }

    make_edit_table("Edit Event");
    make_edit_text("Name:", "name", "25", "100", $row['name']);
    make_edit_select_from_array("Trigger Type:", "trigger_type", $GLOBALS['TRIGGER_TYPES'], $row['trigger_type']);
    make_edit_select_from_array("Situation:", "situation", $GLOBALS['SITUATIONS'], $row['situation']);
    make_edit_hidden("mon_id", $row['mon_id']);
    make_edit_hidden("id", $row['id']);
    make_edit_hidden("tripid", $_REQUEST['tripid']);
    make_edit_hidden("action", "doedit");
    make_edit_submit_button();
    make_edit_end();
    end_page();
}

function do_edit() {
    if ($_REQUEST['id'] == 0) {
        $s = getDatabase()->prepare('INSERT INTO events (name, trigger_type, situation, mon_id) VALUES (:name, :trigger_type, :situation, :mon_id)');
        $s->bindValue(':mon_id', $_REQUEST['mon_id']);
    }
    else {
        $s = getDatabase()->prepare('UPDATE events SET name = :name, trigger_type = :trigger_type, situation = :situation WHERE id = :id');
        $s->bindValue(':id', $_REQUEST['id']);
    }
    $s->bindValue(':name', $_REQUEST['name']);
    $s->bindValue(':trigger_type', $_REQUEST['trigger_type']);
    $s->bindValue(':situation', $_REQUEST['situation']);
    $s->execute();
    header("Location: {$_SERVER['PHP_SELF']}?mon_id={$_REQUEST['mon_id']}&tripid={$_REQUEST['tripid']}");
}

function do_delete() {
    if (isset($_REQUEST['event'])) {
        while (list($key, $value) = each($_REQUEST["event"])) {
            delete_event($key);
        }
    }
    else {
        delete_event($_REQUEST['id']);
    }
    header("Location: {$_SERVER['PHP_SELF']}?mon_id={$_REQUEST['mon_id']}&tripid={$_REQUEST['tripid']}");
}
