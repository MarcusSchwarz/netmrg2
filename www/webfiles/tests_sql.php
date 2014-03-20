<?php
/********************************************
 * NetMRG Integrator
 *
 * tests_sql.php
 * SQL Test Editing Page
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

if ((empty($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd") || ($action == "multidodelete")) {
    // Change databases if necessary and then display list
    if ($action == "doedit") {
        if ($_REQUEST["test_id"] == 0) {
            $s = getDatabase()->prepare('INSERT INTO tests_sql (name, sub_dev_type, host, user, password, query, column_num, timeout) VALUES (:name, :sub_dev_type, :host, :user, :password, :query, :column_num, :timeout)');
        }
        else {
            $s = getDatabase()->prepare('UPDATE tests_sql SET name = :name, sub_dev_type = :sub_dev_type, host = :host, user = :user, password = :password, query = :query, column_num = :column_num, timeout = :timeout WHERE id = :id');
            $s->bindValue(':id', $_REQUEST['test_id']);
        }
        $s->bindValue(':name', $_REQUEST['name']);
        $s->bindValue(':sub_dev_type', $_REQUEST['dev_type']);
        $s->bindValue(':host', $_REQUEST['host']);
        $s->bindValue(':user', $_REQUEST['sql_user']);
        $s->bindValue(':password', $_REQUEST['sql_password']);
        $s->bindValue(':query', $_REQUEST['query']);
        $s->bindValue(':column_num', $_REQUEST['column_num']);
        $s->bindValue(':timeout', $_REQUEST['timeout']);
        $s->execute();

        header("Location: {$_SERVER['PHP_SELF']}");
        exit;
    }

    if ($action == "dodelete") {
        getDatabase()->exec('DELETE FROM tests_sql WHERE id = '.intval($_REQUEST['test_id']));
        header("Location: {$_SERVER['PHP_SELF']}");
        exit;
    }

    if ($action == "multidodelete") {
        if (isset($_REQUEST['test'])) {
            $s = getDatabase()->prepare('DELETE FROM tests_sql WHERE id = :id');
            while (list($key, $value) = each($_REQUEST["test"])) {
                $s->bindValue(':id', $key);
                $s->execute();
            }
        }
        Header("Location: {$_SERVER['PHP_SELF']}");
        exit;
    }

    /** start page **/
    begin_page("tests_sql.php", "SQL - Tests");
    js_checkbox_utils();
    js_confirm_dialog("del", "Are you sure you want to delete SQL test ", " ? ", "{$_SERVER['PHP_SELF']}?action=dodelete&test_id=");
    ?>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
        <input type="hidden" name="action" value="">
        <?php

        make_display_table("SQL Tests", "",
            array("text" => checkbox_toolbar()),
            array("text" => "Name"),
            array("text" => "Host"),
            array("text" => "User"),
            array("text" => "Query")
        );

        $test_results = getDatabase()->query('SELECT * FROM tests_sql ORDER BY name');
        $test_total = getDatabase()
                      ->query('SELECT COUNT(*) FROM tests_sql')
                      ->fetchColumn();

        // For each test
        for ($test_count = 1; $test_count <= $test_total; ++$test_count) {
            $test_row = $test_results->fetch(PDO::FETCH_ASSOC);

            make_display_item("editfield".(($test_count - 1) % 2),
                array("checkboxname" => "test", "checkboxid" => $test_row['id']),
                array("text" => htmlspecialchars($test_row["name"])),
                array("text" => htmlspecialchars($test_row["host"])),
                array("text" => htmlspecialchars($test_row["user"])),
                array("text" => htmlspecialchars(paraphrase($test_row["query"], 75))),
                array("text" => formatted_link("Edit", "{$_SERVER["PHP_SELF"]}?action=edit&test_id=".$test_row["id"], "", "edit")."&nbsp;".
                                formatted_link("Delete", "javascript:del('".addslashes(htmlspecialchars($test_row["name"]))."', '".$test_row["id"]."')", "", "delete"))
            );
        }

        make_checkbox_command("", 6,
            array("text" => "Delete", "action" => "multidodelete", "prompt" => "Are you sure you want to delete the checked SQL tests?")
        );
        make_status_line("SQL test", $test_count - 1);
        ?>
        </table>
    </form>
    <?php
    end_page();
}

if (($action == "edit") || ($action == "add")) {
    /** start page **/
    begin_page("tests_sql.php", "SQL - Tests");
    js_confirm_dialog("del", "Are you sure you want to delete SQL test ", " ? ", "{$_SERVER['PHP_SELF']}?action=dodelete&test_id=");

    if ($action == "add") {
        $_REQUEST["test_id"] = 0;
    }

    $test_row = getDatabase()
                ->query('SELECT * FROM tests_sql WHERE id = '.intval($_REQUEST['test_id']))
                ->fetch(PDO::FETCH_ASSOC);

    make_edit_table("Edit SQL Test");
    make_edit_group("General");
    make_edit_text("Name:", "test_name", "25", "50", htmlspecialchars($test_row["name"]));
    make_edit_select_from_table("For use with this device:", "dev_type", "dev_types", $test_row["sub_dev_type"]);
    make_edit_group("SQL");
    make_edit_text("Host:", "host", "75", "200", htmlspecialchars($test_row["host"]));
    make_edit_text("User:", "sql_user", "75", "200", htmlspecialchars($test_row["user"]));
    make_edit_text("Password:", "sql_password", "75", "200", htmlspecialchars($test_row["password"]));
    make_edit_text("Query:", "query", "75", "255", htmlspecialchars($test_row["query"]));
    make_edit_text("Column Number:", "column_num", "2", "4", $test_row["column_num"]);
    make_edit_text("Timeout (seconds):", "timeout", "2", "4", $test_row["timeout"]);
    make_edit_hidden("action", "doedit");
    make_edit_hidden("test_id", $_REQUEST["test_id"]);
    make_edit_submit_button();
    make_edit_end();

    end_page();

}
