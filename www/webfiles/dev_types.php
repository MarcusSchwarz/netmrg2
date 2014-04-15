<?php
/********************************************
 * NetMRG Integrator
 *
 * dev_types.php
 * Device Types Editing Page
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


if ((!isset($_REQUEST["action"])) || ($_REQUEST["action"] == "doedit") || ($_REQUEST["action"] == "dodelete") || ($_REQUEST["action"] == "doadd")) {
    $auth->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["ReadWrite"]);

    if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "doedit") {
        if ($_REQUEST["id"] == 0) {
            $s = getDatabase()->prepare('INSERT INTO dev_types (name, comment) VALUES (:name, :comment)');
        }
        else {
            $s = getDatabase()->prepare('UPDATE dev_types SET name = :name, comment = :comment WHERE id = :id');
            $s->bindValue(':id', $_REQUEST['id']);
        }
        $s->bindValue(':name', $_REQUEST['name']);
        $s->bindValue(':comment', $_REQUEST['comment']);
        $s->execute();
    }

    if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "dodelete") {
        $s = getDatabase()->prepare('DELETE FROM dev_types WHERE id = :id');
        $s->bindValue(':id', $_REQUEST['id']);
        $s->execute();
    }

    # Display a list
    begin_page("Device Types");
    js_confirm_dialog("del", "Are you sure you want to delete device type ", " ? ", "{$_SERVER['PHP_SELF']}?action=dodelete&id=");
    make_display_table("Device Types", "",
        array("text" => "Name", "href" => "{$_SERVER['PHP_SELF']}?orderby=name"),
        array("text" => "Comment", "href" => "{$_SERVER['PHP_SELF']}?orderby=comment")
    );

    if (!isset($_REQUEST["orderby"])) {
        $orderby = "name";
    }
    else {
        $orderby = $_REQUEST["orderby"];
    }

    $s = getDatabase()->prepare('SELECT * FROM dev_types ORDER BY :order');
    $s->bindValue(':order', $orderby);
    $s->execute();

    $grp_total = getDatabase()->prepare('SELECT COUNT (*) FROM dev_types')->fetchColumn();

    for ($grp_count = 1; $grp_count <= $grp_total; ++$grp_count) {
        $row = $s->fetch(PDO::FETCH_ASSOC);
        $id  = $row["id"];

        make_display_item("editfield".(($grp_count - 1) % 2),
            array("text" => $row["name"], "href" => "dev_props.php?dev_type=$id"),
            array("text" => $row["comment"]),
            array("text" => formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&id=$id")."&nbsp;".
                            formatted_link("Delete", "javascript:del('".addslashes($row["name"])."', '".$id."')"))
        );
    }
    ?>
    </table>
<?php
}

if (!empty($_REQUEST["action"]) && ($_REQUEST["action"] == "edit" || $_REQUEST["action"] == "add")) {
    // Display editing screen
    $auth->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["ReadWrite"]);
    begin_page("Device Types");

    $id = ($_REQUEST["action"] == "add") ? 0 : $_REQUEST["id"];

    $s = getDatabase()->prepare('SELECT * FROM dev_types WHERE id = :id');
    $s->bindValue(':id', $id);
    $s->execute();

    $row     = $s->fetch(PDO::FETCH_ASSOC);
    $name    = $row["name"];
    $comment = $row["comment"];

    make_edit_table("Edit Group");
    make_edit_text("Name:", "name", "25", "100", $name);
    make_edit_text("Comment:", "comment", "50", "200", $comment);
    make_edit_hidden("id", $id);
    make_edit_hidden("action", "doedit");
    make_edit_submit_button();
    make_edit_end();

}

end_page();
