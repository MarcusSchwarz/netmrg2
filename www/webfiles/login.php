<?php
/********************************************
 * NetMRG Integrator
 *
 * login.php
 * Site Login Page
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

$login_error = "";

// if we've already seen this page, go away
if (IsLoggedIn()) {
    view_redirect();
}

/***** EXTERNAL AUTH *****/
// if external auth
if ($GLOBALS["netmrg"]["externalAuth"] && !empty($_SERVER["PHP_AUTH_USER"]) && check_user($_SERVER["PHP_AUTH_USER"])) {
    $_SESSION["netmrgsess"]["prettyname"]  = $_SERVER["PHP_AUTH_USER"];
    $_SESSION["netmrgsess"]["username"]    = $_SERVER["PHP_AUTH_USER"];
    $_SESSION["netmrgsess"]["password"]    = "";
    $_SESSION["netmrgsess"]["accessTime"]  = time();
    $_SESSION["netmrgsess"]["remote_addr"] = $_SERVER["REMOTE_ADDR"];
    $_SESSION["netmrgsess"]["permit"]      = get_permit($_SESSION["netmrgsess"]["username"]);
    $_SESSION["netmrgsess"]["group_id"]    = get_group_id();

    view_redirect();
}
// if external auth and default user exists
else {
    if ($GLOBALS["netmrg"]["externalAuth"] && !empty($_SERVER["PHP_AUTH_USER"]) && check_user($GLOBALS["netmrg"]["defaultMapUser"])) {
        $_SESSION["netmrgsess"]["prettyname"]  = $_SERVER["PHP_AUTH_USER"];
        $_SESSION["netmrgsess"]["username"]    = $GLOBALS["netmrg"]["defaultMapUser"];
        $_SESSION["netmrgsess"]["password"]    = "";
        $_SESSION["netmrgsess"]["accessTime"]  = time();
        $_SESSION["netmrgsess"]["remote_addr"] = $_SERVER["REMOTE_ADDR"];
        $_SESSION["netmrgsess"]["permit"]      = get_permit($GLOBALS["netmrg"]["defaultMapUser"]);
        $_SESSION["netmrgsess"]["group_id"]    = get_group_id($GLOBALS["netmrg"]["defaultMapUser"]);

        view_redirect();
    }
    else {
        if ($GLOBALS["netmrg"]["externalAuth"] && !empty($_SERVER["PHP_AUTH_USER"]) && !check_user($_SERVER["PHP_AUTH_USER"])) {
            header("Location: {$GLOBALS['netmrg']['webroot']}/error.php?action=denied");
            exit;
        }
    }
}

// if we need to login
if (!empty($_REQUEST["user_name"])) {
    if (!$GLOBALS["netmrg"]["externalAuth"] && check_user_pass($_REQUEST["user_name"], $_REQUEST["password"])) {
        $_SESSION["netmrgsess"]["prettyname"]  = $_REQUEST["user_name"];
        $_SESSION["netmrgsess"]["username"]    = $_REQUEST["user_name"];
        $_SESSION["netmrgsess"]["password"]    = $_REQUEST["password"];
        $_SESSION["netmrgsess"]["accessTime"]  = time();
        $_SESSION["netmrgsess"]["remote_addr"] = $_SERVER["REMOTE_ADDR"];
        $_SESSION["netmrgsess"]["permit"]      = get_permit($_SESSION["netmrgsess"]["username"]);
        $_SESSION["netmrgsess"]["group_id"]    = get_group_id();
        view_redirect();
    }
    else {
        $login_error = "Invalid Username or Password";
    }
}

begin_page("Login", 0, '', array("login_focus.js"));
?>
    <br><br>
    <span style="color:#000080;font-size:large;font-weight:bold;">User Login</span>
    <br><br>

<?php
if (!empty($login_error)) {
    ?>
    <div class="error">
        <?php echo "$login_error\n"; ?>
    </div>
<?php
}
?>
    <form action="./login.php" method="post" name="lif">
        <table>
            <tr>
                <td>User:</td>
                <td><input type="text" name="user_name"></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type="password" name="password"></td>
            </tr>
            <tr>
                <td></td>
                <td align="right"><input type="submit" value="Login"></td>
            </tr>
        </table>
    </form>

<?php
end_page();
