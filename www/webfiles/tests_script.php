<?php
/********************************************
 * NetMRG Integrator
 *
 * tests_script.php
 * Script Test Editing Page
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
check_auth($GLOBALS['PERMIT']["ReadWrite"]);

if (empty($_REQUEST["action"])) {
    $_REQUEST["action"] = "";
}
$action = $_REQUEST["action"];

// if no action (list) or perfoming an insert/update/delete
if ((empty($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd") || ($action == "multidodelete")) {
    if ($action == "doedit") {
        if ($_REQUEST["test_id"] == 0) {
            $s = getDatabase()->prepare('INSERT INTO tests_script (name, cmd, data_type, dev_type) VALUES (:name, :cmd, :data_type, :dev_type)');
        }
        else {
            $s = getDatabase()->prepare('UPDATE tests_script SET name = :name, cmd = :cmd, data_type = :data_type, dev_type = :dev_type WHERE id = :id');
            $s->bindValue(':id', $_REQUEST['test_id']);
        }
        $s->bindValue(':name', $_REQUEST['test_name']);
        $s->bindValue(':cmd', $_REQUEST['test_cmd']);
        $s->bindValue(':data_type', $_REQUEST['data_type']);
        $s->bindValue(':dev_type', $_REQUEST['dev_type']);
        $s->execute();

        Header("Location: {$_SERVER['PHP_SELF']}");
        exit;
    }

    if ($action == "dodelete") {
        getDatabase()->exec('DELETE FROM tests_script WHERE id = '.intval($_REQUEST['test_id']));
        Header("Location: {$_SERVER['PHP_SELF']}");
        exit;
    }

    if ($action == "multidodelete") {
        if (isset($_REQUEST['test'])) {
            $s = getDatabase()->prepare('DELETE FROM tests_script WHERE id = :id');
            while (list($key, $value) = each($_REQUEST["test"])) {
                $s->bindValue(':id', $key);
                $s->execute();
            }
        }
        Header("Location: {$_SERVER['PHP_SELF']}");
        exit;
    }


    /** start the page **/
    begin_page("Scripts - Tests");
    js_checkbox_utils();
    js_confirm_dialog("del", "Are you sure you want to delete script test ", " ?", "{$_SERVER['PHP_SELF']}?action=dodelete&test_id=");
    ?>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
        <input type="hidden" name="action" value="">
        <?php

        // Display a list
        make_display_table("Script Tests", "",
            array("text" => checkbox_toolbar()),
            array("text" => "Name"),
            array("text" => "Command"),
            array("text" => "Data Type")
        );

        $test_results = getDatabase()->query('SELECT id, name, cmd, data_type FROM tests_script ORDER BY name');

        $test_total = getDatabase()
                      ->query('SELECT COUNT(id) FROM tests_script')
                      ->fetchColumn();

        for ($test_count = 1; $test_count <= $test_total; ++$test_count) {
            $test_row = $test_results->fetch(PDO::FETCH_ASSOC);
            $test_id  = $test_row["id"];

            make_display_item("editfield".(($test_count - 1) % 2),
                array("checkboxname" => "test", "checkboxid" => $test_id),
                array("text" => htmlspecialchars($test_row["name"])),
                array("text" => htmlspecialchars($test_row["cmd"])),
                array("text" => $GLOBALS["SCRIPT_DATA_TYPES"][$test_row["data_type"]]),
                array("text" => formatted_link("Edit", "{$_SERVER["PHP_SELF"]}?action=edit&test_id=$test_id", "", "edit")."&nbsp;".
                                formatted_link("Delete", "javascript:del('".addslashes(htmlspecialchars($test_row["name"]))."', '".$test_row["id"]."')", "", "delete"))
            );
        }
        make_checkbox_command("", 5,
            array("text" => "Delete", "action" => "multidodelete", "prompt" => "Are you sure you want to delete the checked Script tests?")
        );
        make_status_line("script test", $test_count - 1);
        ?>
        </table>
    </form>
    <?php
    end_page();
}


// Display editing screen
if (($action == "edit") || ($action == "add")) {
    /** start the page **/
    begin_page("Scripts - Tests");
    js_confirm_dialog("del", "Are you sure you want to delete script test ", " ?", "{$_SERVER['PHP_SELF']}?action=dodelete&test_id=");

    if ($action == "add") {
        $_REQUEST["test_id"] = 0;
    }

    $test_row  = getDatabase()
                 ->query('SELECT * FROM tests_script WHERE id = '.intval($_REQUEST['test_id']))
                 ->fetch(PDO::FETCH_ASSOC);
    $test_name = $test_row["name"];
    $test_cmd  = $test_row["cmd"];

    make_edit_table("Edit Script Test");
    make_edit_text("Name:", "test_name", "25", "50", htmlspecialchars($test_row["name"]));
    make_edit_text("Command:", "test_cmd", "75", "200", htmlspecialchars($test_row["cmd"]));
    make_edit_select_from_array("Data Type:", "data_type", $GLOBALS["SCRIPT_DATA_TYPES"], $test_row["data_type"]);
    make_edit_select_from_table("For use with this device:", "dev_type", "dev_types", $test_row["dev_type"]);
    make_edit_hidden("action", "doedit");
    make_edit_hidden("test_id", $_REQUEST["test_id"]);
    make_edit_submit_button();
    make_edit_end();

    end_page();
}