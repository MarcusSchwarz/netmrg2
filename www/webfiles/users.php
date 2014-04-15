<?php
/********************************************
 * NetMRG Integrator
 *
 * users.php
 * User Access Administration
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
$auth->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["Admin"]);

if (empty($_REQUEST["action"])) {
    $_REQUEST["action"] = "";
}

switch ($_REQUEST["action"]) {

    case "edit":
    case "add":
        display_edit();
        break;

    case "doedit":
    case "doadd":
        do_edit();
        break;

    case "dodelete":
        do_delete();
        break;

    case "deletemulti":
        do_deletemulti();
        break;

    default:
        display_page();

}


function do_edit() {
    if (!empty($_REQUEST["pass"])) {
        if ($_REQUEST["pass"] != $_REQUEST["vpass"]) {
            begin_page("User Management - Error");
            echo "<div>Error: your passwords don't match; please go back and try again</div>";
            end_page();
            exit;
        }
    }


    if ($_REQUEST["user_id"] == 0) {
        if (!empty($_REQUEST["pass"])) {
            $s = getDatabase()->prepare('INSERT INTO user (user, fullname, permit, group_id, disabled, pass) VALUES (:user, :fullname, :permit, :group_id, :disabled, :pass)');
        }
        else {
            $s = getDatabase()->prepare('INSERT INTO user (user, fullname, permit, group_id, disabled) VALUES (:user, :fullname, :permit, :group_id, :disabled)');
        }
    }
    else {
        if (!empty($_REQUEST["pass"])) {
            $s = getDatabase()->prepare('UPDATE user SET user = :user, fullname = :fullname, permit = :permit, group_id = :group_id, disabled = :disabled, pass = :pass WHERE id = :id');
        }
        else {
            $s = getDatabase()->prepare('UPDATE user SET user = :user, fullname = :fullname, permit = :permit, group_id = :group_id, disabled = :disabled WHERE id = :id');
        }
        $s->bindValue(':id', $_REQUEST['user_id']);
    }

    if (!empty($_REQUEST["pass"])) {
        global $auth;
        // todo everything here should be pushed to auth
        $s->bindValue(':pass', $auth->generate_password_hash($_REQUEST['pass']));
    }

    if (empty($_REQUEST["group_id"])) {
        $_REQUEST["group_id"] = 0;
    }

    if (empty($_REQUEST["disabled"])) {
        $_REQUEST["disabled"] = 0;
    }

    $s->bindValue(':user', $_REQUEST['user']);
    $s->bindValue(':fullname', $_REQUEST['fullname']);
    $s->bindValue(':permit', $_REQUEST['permit']);
    $s->bindValue(':group_id', $_REQUEST['group_id']);
    $s->bindValue(':disabled', $_REQUEST['disabled']);
    $s->execute();

    header("Location: {$_SERVER['PHP_SELF']}");
}

function do_delete() {
    getDatabase()->exec('DELETE FROM user WHERE id = '.intval($_REQUEST['user_id']));
    header("Location: {$_SERVER['PHP_SELF']}");
}

function do_deletemulti() {
    if (isset($_REQUEST["user"])) {
        $s = getDatabase()->prepare('DELETE FROM user WHERE id = :id');
        while (list($key, $value) = each($_REQUEST["user"])) {
            $s->bindValue(':id', $key);
            $s->execute();
        }
    }
    header("Location: {$_SERVER['PHP_SELF']}");
}

function display_edit() {

    begin_page("User Management", 0, 'onLoad="enableGroup(document.editform.permit.value)"');
    echo '
<script language="JavaScript">
<!--
function enableGroup(val) {
if (val == 0) { // Single View Only
document.editform.group_id.disabled=false;
} else {
document.editform.group_id.disabled=true;
document.editform.group_id.value=0; // Root Group
}
}
-->
</script>
';

    $user_id  = intval($_REQUEST["user_id"]);
    $user_row = getDatabase()
                ->query('SELECT * FROM user WHERE id = '.$user_id)
                ->fetch(PDO::FETCH_ASSOC);

    make_edit_table("Edit User");
    make_edit_text("User ID:", "user", "25", "50", $user_row["user"]);
    make_edit_text("Full Name", "fullname", "25", "75", $user_row["fullname"]);
    if (!$GLOBALS["netmrg"]["externalAuth"]) {
        make_edit_password("Password:", "pass", "25", "50", "");
        make_edit_password("Verify Password:", "vpass", "25", "50", "");
    }
    make_edit_select_from_array("Permit Type:", "permit", $GLOBALS['PERMIT_TYPES'], $user_row["permit"], 'onChange="enableGroup(this.value)"');
    make_edit_select_from_table("Group:", "group_id", "groups", $user_row["group_id"], "", array(0 => "-Root-"));
    make_edit_checkbox("Disabled", "disabled", $user_row["disabled"]);
    make_edit_hidden("action", "doedit");
    make_edit_hidden("user_id", $user_id);
    make_edit_submit_button();
    make_edit_end();
    end_page();
}

function display_page() {
    global $auth;
    begin_page("User Management");
    js_checkbox_utils();
    ?>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
        <?php
        make_edit_hidden("action", "");
        make_display_table("Users", "",
            array("text" => checkbox_toolbar()),
            array("text" => "User ID"),
            array("text" => "Name"),
            array("text" => "Permissions")
        );

        $user_total = getDatabase()
                      ->query('SELECT COUNT(*) FROM user')
                      ->fetchColumn();
        $user_results = getDatabase()->query('SELECT * FROM user ORDER BY user');

        js_confirm_dialog("del", "Are you sure you want to delete user ", " ?", "{$_SERVER['PHP_SELF']}?action=dodelete&user_id=");

        for ($user_count = 1; $user_count <= $user_total; ++$user_count) {
            $user_row = $user_results->fetch(PDO::FETCH_ASSOC);
            $user_id  = $user_row["id"];

            make_display_item("editfield".(($user_count - 1) % 2),
                array("checkboxname" => "user", "checkboxid" => $user_id),
                array("text" => $user_row["user"]),
                array("text" => $user_row["fullname"]),
                array("text" => ($auth->getUsersPermissionLevel($user_row["user"]) == $GLOBALS['PERMIT']["Disabled"]) ? 'Disabled' : $GLOBALS['PERMIT_TYPES'][$user_row['permit']]),
                array("text" => formatted_link("Prefs", "user_prefs.php?uid=$user_id")."&nbsp;".
                                formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&user_id=$user_id", "", "edit")."&nbsp;".
                                formatted_link("Delete", "javascript:del('".addslashes($user_row['user'])."', '{$user_row['id']}')", "", "delete")
                )
            );
        }

        make_checkbox_command("", 5,
            array("text" => "Delete", "action" => "deletemulti", "prompt" => "Are you sure you want to delete the checked users?")
        );
        make_status_line("user", $user_count - 1);
        ?>
        </table>
    </form>
    <?php
    end_page();
}
