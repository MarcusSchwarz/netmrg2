<?php
/********************************************
 * NetMRG Integrator
 *
 * tests_snmp.php
 * SNMP Test Editing Page
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

// if no action (list), and do inserts/updates/deletes
if ((empty($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd") || ($action == "multidodelete")) {

    if ($action == "doedit") {
        if ($_REQUEST["test_id"] == 0) {
            $s = getDatabase()->prepare('INSERT INTO tests_snmp (name, oid, dev_type, type, subitem) VALUES (:name, :oid, :dev_type, :type, :subitem)');
        }
        else {
            $s = getDatabase()->prepare('UPDATE tests_snmp SET name = :name, oid = :oid, dev_type = :dev_type, type = :type, subitem = :subitem WHERE id = :id');
            $s->bindValue(':id', $_REQUEST['test_id']);
        }
        $s->bindValue(':name', $_REQUEST['test_name']);
        $s->bindValue(':oid', $_REQUEST['test_oid']);
        $s->bindValue(':dev_type', $_REQUEST['dev_type']);
        $s->bindValue(':type', $_REQUEST['type']);
        $s->bindValue(':subitem', $_REQUEST['subitem']);
        $s->execute();

        header("Location: {$_SERVER['PHP_SELF']}");
        exit;
    }

    if ($action == "dodelete") {
        getDatabase()->exec('DELETE FROM tests_snmp WHERE id = '.intval($_REQUEST['test_id']));
        header("Location: {$_SERVER['PHP_SELF']}");
        exit;
    }

    if ($action == "multidodelete") {
        if (isset($_REQUEST['test'])) {
            $s = getDatabase()->prepare('DELETE FROM tests_snmp WHERE id = :id');
            while (list($key, $value) = each($_REQUEST["test"])) {
                $s->bindValue(':id', $key);
                $s->execute();
            }
        }
        Header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }

    /** start page **/
    begin_page("tests_snmp.php", "SNMP - Tests");
    js_checkbox_utils();
    js_confirm_dialog("del", "Are you sure you want to delete SNMP test ", " ? ", "{$_SERVER['PHP_SELF']}?action=dodelete&test_id=");
    ?>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
        <input type="hidden" name="action" value="">
        <?php


        make_display_table("SNMP Tests", "",
            array("text" => checkbox_toolbar()),
            array("text" => "Name"),
            array("text" => "OID")
        );

        $test_results = getDatabase()->query('SELECT * FROM tests_snmp ORDER BY name');
        $test_total = getDatabase()
                      ->query('SELECT COUNT(*) FROM tests_snmp')
                      ->fetchColumn();

        for ($test_count = 1; $test_count <= $test_total; ++$test_count) {
            $test_row = $test_results->fetch(PDO::FETCH_ASSOC);

            make_display_item("editfield".(($test_count - 1) % 2),
                array("checkboxname" => "test", "checkboxid" => $test_row['id']),
                array("text" => htmlspecialchars($test_row["name"])),
                array("text" => htmlspecialchars($test_row["oid"])),
                array("text" => formatted_link("Edit", "{$_SERVER["PHP_SELF"]}?action=edit&test_id=".$test_row["id"], "", "edit")."&nbsp;".
                                formatted_link("Delete", "javascript:del('".addslashes(htmlspecialchars($test_row["name"]))."', '".$test_row["id"]."')", "", "delete"))
            );
        }

        make_checkbox_command("", 5,
            array("text" => "Delete", "action" => "multidodelete", "prompt" => "Are you sure you want to delete the checked SNMP tests?")
        ); // end make_checkbox_command
        make_status_line("SNMP test", $test_count - 1);
        ?>
        </table>
    </form>
    <?php
    end_page();
} // End if no action


// Display editing screen
if (($action == "edit") || ($action == "add")) {
    /** start page **/
    begin_page("tests_snmp.php", "SNMP - Tests");
    js_confirm_dialog("del", "Are you sure you want to delete SNMP test ", " ? ", "{$_SERVER['PHP_SELF']}?action=dodelete&test_id=");

    if ($action == "add") {
        $_REQUEST["test_id"] = 0;
    }

    $test_row = getDatabase()
                ->query('SELECT * FROM tests_snmp WHERE id = '.intval($_REQUEST['test_id']))
                ->fetch(PDO::FETCH_ASSOC);

    make_edit_table("Edit SNMP Test");
    make_edit_text("Name:", "test_name", "25", "50", htmlspecialchars($test_row["name"]));
    make_edit_text("SNMP OID:", "test_oid", "75", "200", htmlspecialchars($test_row["oid"]));
    make_edit_select_from_table("For use with this device:", "dev_type", "dev_types", $test_row["dev_type"]);
    make_edit_section("Advanced");
    make_edit_select_from_array("Type:", "type", array(0 => "Direct (Get)", 1 => "Nth Item (Walk)", 2 => "Count of Items (Walk)"), $test_row["type"]);
    make_edit_text("Item #:", "subitem", "3", "10", $test_row["subitem"]);
    make_edit_hidden("action", "doedit");
    make_edit_hidden("test_id", $_REQUEST["test_id"]);
    make_edit_submit_button();
    make_edit_end();

    end_page();
}
