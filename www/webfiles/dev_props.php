<?php
/********************************************
 * NetMRG
 *
 * dev_props.php
 * Device Properties Editing Page
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

// set default action
if (empty($_REQUEST["action"])) {
    $_REQUEST["action"] = "list";
}

switch ($_REQUEST["action"]) {
    case "doedit":
        $auth->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["ReadWrite"]);
        do_edit();
        redirect();
        break;

    case "dodelete":
        $auth->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["ReadWrite"]);
        $_REQUEST['prop_id'] *= 1;
        $s = getDatabase()->prepare('DELETE FROM dev_props WHERE id = :id');
        $s->bindValue(':id', $_REQUEST['prop_id']);
        $s->execute();
        $s = getDatabase()->prepare('DELETE FROM dev_prop_vals WHERE prop_id = :id');
        $s->bindValue(':id', $_REQUEST['prop_id']);
        $s->execute();
        redirect();
        break;

    case "multidodelete":
        $auth->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["ReadWrite"]);
        $s = getDatabase()->prepare('DELETE FROM dev_props WHERE id = :id');
        while (list($key, $value) = each($_REQUEST["devprop"])) {
            $s->bindValue(':id', $key);
            $s->execute();
        }
        redirect();
        break;

    case "add":
        $auth->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["ReadWrite"]);
    case "edit":
        edit();
        break;

    default:
    case "list":
        do_list();
        break;
}

/***** FUNCTIONS *****/
function do_list() {
    $s = getDatabase()->prepare('SELECT name FROM dev_types WHERE id = :id');
    $s->bindValue(':id', $_REQUEST['dev_type']);
    $s->execute();
    $r = $s->fetch(PDO::FETCH_ASSOC);

    begin_page("Device Properties for {$r['name']}");
    js_checkbox_utils();
    ?>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
        <input type="hidden" name="action" value="">
        <input type="hidden" name="dev_type" value="<?php echo $_REQUEST['dev_type']; ?>">
        <input type="hidden" name="tripid" value="<?php echo $_REQUEST['tripid']; ?>">
        <?php

        js_confirm_dialog("del", "Are you sure you want to delete device property ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&dev_type={$_REQUEST['dev_type']}&tripid={$_REQUEST['tripid']}&prop_id=");
        make_display_table("Device Properties for {$r['name']}", "{$_SERVER['PHP_SELF']}?action=add&dev_type={$_REQUEST['dev_type']}&tripid={$_REQUEST['tripid']}",
            array("text" => checkbox_toolbar()),
            array("text" => "Name"),
            array("text" => "Test")
        );

        $prop_count = 0;

        $s = getDatabase()->prepare('SELECT * FROM dev_props WHERE dev_type_id = :id');
        $s->bindValue(':id', $_REQUEST['dev_type']);
        $s->execute();

        while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
            $test_name = get_short_test_name($row['test_type'], $row['test_id'], $row['test_params']);
            $prop_id   = $row['id'];

            make_display_item("editfield".($prop_count % 2),
                array("checkboxname" => "devprop", "checkboxid" => $row['id']),
                array("text" => $row['name']),
                array("text" => $test_name),
                array("text" => formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&prop_id=$prop_id&dev_type={$_REQUEST['dev_type']}&tripid={$_REQUEST['tripid']}", "", "edit")."&nbsp;".
                                formatted_link("Delete", "javascript:del('$test_name', '$prop_id')", "", "delete"))
            );

            $prop_count++;

        }
        make_checkbox_command("", 5,
            array("text" => "Delete", "action" => "multidodelete", "prompt" => "Are you sure you want to delete the checked properties?")
        );
        make_status_line("property", $prop_count, "properties");
        ?>
        </table>
    </form>
    <?php

    end_page();

}


function edit() {
    begin_page("Device Properties");

    // if we're editing a property
    if ($_REQUEST["action"] == "edit") {
        make_edit_table("Edit Device Property");

        $s = getDatabase()->prepare('SELECT id, name, test_type, test_id, test_params FROM dev_props WHERE dev_props.id = :id');
        $s->bindValue(':id', $_REQUEST['prop_id']);
        $s->execute();

        $row = $s->fetch(PDO::FETCH_ASSOC);
    }
    // if we're adding a property
    else {
        make_edit_table("Add Device Property");
        $row["test_id"] = 1;
        if (!empty($_REQUEST["type"])) {
            $row["test_type"] = $_REQUEST["type"];
        }
        else {
            $row["test_type"] = 0;
        }
        $row["test_params"]  = "";
        $_REQUEST["prop_id"] = 0;
    }

    make_edit_group("General Parameters");
    make_edit_text("Name:", "name", "25", "200", $row['name']);
    make_edit_select_test($row['test_type'], $row['test_id'], $row['test_params']);

    make_edit_hidden("action", "doedit");
    make_edit_hidden("prop_id", $_REQUEST["prop_id"]);
    make_edit_hidden("dev_type", $_REQUEST['dev_type']);
    make_edit_hidden("tripid", $_REQUEST["tripid"]);

    make_edit_submit_button();
    make_edit_end();

    end_page();
}

function redirect() {
    header("Location: dev_props.php?dev_type={$_REQUEST['dev_type']}&tripid={$_REQUEST['tripid']}");
}

function do_edit() {
    if ($_REQUEST["prop_id"] == 0) {
        $s = getDatabase()->prepare('INSERT INTO dev_props (name, test_type, test_id, test_params, dev_type_id) VALUES (:name, :type, :testid, :params, :typeid)');
    }
    else {
        $s = getDatabase()->prepare('UPDATE dev_props SET name = :name, test_type = :type, test_id = :testid, test_params = :params, dev_type_id = :typeid WHERE id = :propid');
        $s->bindParam(':propid', $_REQUEST['prop_id']);
    }
    $s->bindValue(':name', $_REQUEST['name']);
    $s->bindValue(':type', $_REQUEST['test_type']);
    $s->bindValue(':testid', $_REQUEST['test_id']);
    $s->bindValue(':params', $_REQUEST['test_params']);
    $s->bindValue(':typeid', $_REQUEST['dev_type']);

    $s->execute();
    redirect();
}
