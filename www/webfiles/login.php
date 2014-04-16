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
if ($auth->userIsLoggedIn()) {
    $auth->view_redirect();
}

/***** EXTERNAL AUTH *****/
if ($GLOBALS["netmrg"]["externalAuth"] && !empty($_SERVER["PHP_AUTH_USER"])) {
    if ($auth->userExists($_SERVER['PHP_AUTH_USER'])) {
        $session->setSessionParameters($auth, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_USER']);
        $auth->view_redirect();
    }

    if ($auth->defaultUserExists()) {
        $session->setSessionParameters($auth, $_SERVER["PHP_AUTH_USER"], $GLOBALS["netmrg"]["defaultMapUser"]);
        $auth->view_redirect();
    }

    if (!$auth->userExists($_SERVER["PHP_AUTH_USER"])) {
        $auth->redirectErrorDenied();
    }
}

// if we need to login
if (!empty($_REQUEST["user_name"])) {
    if (!$GLOBALS["netmrg"]["externalAuth"] && $auth->userHasCorrectPassword($_REQUEST["user_name"], $_REQUEST["password"])) {
        $session->setSessionParameters($auth, $_REQUEST['user_name'], $_REQUEST['user_name'], $_REQUEST['password']);

        $auth->view_redirect();
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
