<?php
/********************************************
* NetMRG Integrator
*
* auth.php
* Authentication and Permissions Module
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


/**
* check_user($user)
*
* verifies a username (for external auth)
*   $user = username
*
*/
function check_user($user) {
    $s = getDatabase()->prepare('SELECT 1 FROM user WHERE user = :user');
    $s->bindValue(':user', $user);
    $s->execute();
    $result = $s->fetchColumn();

    return ($result == 1) ? true : false;
}


/**
* check_user_pass($user, $pass);
*
* verifies a username and password agains what's in the database
*   $user = username
*   $pass = password
*/
function check_user_pass($user, $pass) {
    $s = getDatabase()->prepare('SELECT 1 FROM user WHERE user = :user AND pass = :pass');
    $s->bindValue(':user', $user);
    $s->bindValue(':pass', generate_password_hash($pass));
    $s->execute();
    $result = $s->fetchColumn();

    if ($result == 1) {
        return true;
    }

    // test against old type password hashes
    $s->bindValue(':user', $user);
    $s->bindValue(':pass', md5($pass));
    $s->execute();
    $result = $s->fetchColumn();

    if ($result == 1) {
        //todo test database field length before trying to update
        // update old password hash
        $s = getDatabase()->prepare('UPDATE user SET pass = :pass WHERE user = :user');
        $s->bindValue(':user', $user);
        $s->bindValue(':pass', generate_password_hash($pass));
        $s->execute();
        return true;
    }

    return false;
}

function generate_password_hash($pass) {
    for ($i = 0; $i < 10240; $i++) {
        $pass = hash('sha512', $pass.HASHING_SECRET);
    }
    return $pass;
}

/**
* IsLoggedIn();
*
* verifies a username and password in the session
* against what's in the database
* and that the user isn't spoofing their ip
* and that they haven't been logged in too long
*/
function IsLoggedIn()
{
	if ((
			($GLOBALS["netmrg"]["externalAuth"] 
			&& (check_user($_SESSION["netmrgsess"]["username"])
				|| check_user($GLOBALS["netmrg"]["defaultMapUser"])))
			||
			(!$GLOBALS["netmrg"]["externalAuth"]
			&& check_user_pass($_SESSION["netmrgsess"]["username"], $_SESSION["netmrgsess"]["password"]))
		)
		&& $_SESSION["netmrgsess"]["remote_addr"] == $_SERVER["REMOTE_ADDR"]
		&& time() - $_SESSION["netmrgsess"]["accessTime"] <= $GLOBALS["netmrg"]["authTimeout"])
	{
		return true;
	}

	return false;
}


/**
* get_full_name($user)
*
* gets $user's full name
*/
function get_full_name($user) {
    $s = getDatabase()->prepare('SELECT fullname FROM user WHERE user = :user');
    $s->bindValue(':user', $user);
    $s->execute();
    return $s->fetchColumn();
}


/**
* check_auth($level)
*
* checks the logged in user's auth level to be sure they have
* at least auth level $level.  If not, send them away
*/
function check_auth($level) {
	// if they aren't logged in
	if (!IsLoggedIn()) {
		$_SESSION["netmrgsess"]["redir"] = $_SERVER["REQUEST_URI"];
		if ($GLOBALS["netmrg"]["externalAuth"]) {
			header("Location: {$GLOBALS['netmrg']['webroot']}/login.php");
			exit;
		}
		header("Location: {$GLOBALS['netmrg']['webroot']}/error.php?action=invalid");
		exit;
	}

	// if they don't have enough permissions
	else if ($_SESSION["netmrgsess"]["permit"] < $level) {
		header("Location: {$GLOBALS['netmrg']['webroot']}/error.php?action=denied");
		exit;
	}
}


/**
* viewCheckAuth($object_id, $object_type)
*
* called from the 'view.php' page
* checks that the user is allowed to see this page
*/
function viewCheckAuth($object_id, $object_type) {
	global $PERMIT;
	check_auth($GLOBALS['PERMIT']["SingleViewOnly"]);
	
	// the groups this object_id is in
	$object_id_groups = GetGroups($object_type,$object_id);
	
	if (!in_array($_SESSION["netmrgsess"]["group_id"], $object_id_groups) && $_SESSION["netmrgsess"]["permit"] == $PERMIT["SingleViewOnly"]) {
		return false;
	} // end if allowed group id is not in this objects groups and we're SVO
	
	return true;
}


/**
* viewCheckAuthRedirect($object_id, $object_type)
*
* called from the 'view.php' page
* checks that the user is allowed to see this page
* and redirects if they are not
*/
function viewCheckAuthRedirect($object_id, $object_type) {
	if (!viewCheckAuth($object_id, $object_type)) {
		$_SESSION["netmrgsess"]["redir"] = $_SERVER["REQUEST_URI"];
		header("Location: {$GLOBALS['netmrg']['webroot']}/error.php?action=denied");
		exit;
	}
}


/**
* EncloseGraphCheckAuth()
*
* makes sure that the logged in user can view a graph
*
* type = template, custom, mon, tinymon
* id = id of item
*
*/
function EncloseGraphCheckAuth($type, $id) {
	global $PERMIT;
	check_auth($GLOBALS['PERMIT']["SingleViewOnly"]);

	// the groups this object_id is in
	$object_id_groups = array();

	switch ($type) {
		case "mon" :
		case "tinymon" :
			$object_id_groups = GetGroups("monitor",$id);
			break;

		case "template" :
			$object_id_groups = GetGroups("subdevice",$id);
			break;

		case "custom" :
			$object_id_groups = GetGroups("customgraph",$id);
			break;
	}

	if (!in_array($_SESSION["netmrgsess"]["group_id"], $object_id_groups) && $_SESSION["netmrgsess"]["permit"] == $PERMIT["SingleViewOnly"]) {
		$_SESSION["netmrgsess"]["redir"] = $_SERVER["REQUEST_URI"];
		header("Location: {$GLOBALS['netmrg']['webroot']}/error.php?action=denied");
		exit;
	}
}


/**
* GraphCheckAuth()
*
* makes sure that the logged in user can view a graph
*
* type = template, custom, mon, tinymon
* id = id of item
*
*/
function GraphCheckAuth($type, $id) {
	global $PERMIT;
	check_auth($GLOBALS['PERMIT']["SingleViewOnly"]);

	// the groups this object_id is in
	$object_id_groups = array();

	switch ($type) {
		case "mon" :
		case "tinymon" :
			$object_id_groups = GetGroups("monitor",$id);
			break;

		case "template" :
		case "template_item" :
			$object_id_groups = GetGroups("subdevice",$id);
			break;

		case "custom" :
		case "custom_item" :
			$object_id_groups = GetGroups("customgraph",$id);
			break;
	}

	if (!in_array($_SESSION["netmrgsess"]["group_id"], $object_id_groups) && $_SESSION["netmrgsess"]["permit"] == $PERMIT["SingleViewOnly"]) {
		readfile($GLOBALS["netmrg"]["fileroot"]."/webfiles/img/access_denied.png");
		exit;
	}
}


/**
* ResetAuth()
*
* reset authentication variables
*/
function ResetAuth() {
	if (isset($_SESSION["netmrgsess"])) {
		unset($_SESSION["netmrgsess"]);
		$_SESSION["netmrgsess"] = array();
		$_SESSION["netmrgsess"]["username"] = "";
		$_SESSION["netmrgsess"]["password"] = "";
		$_SESSION["netmrgsess"]["remote_addr"] = "";
		$_SESSION["netmrgsess"]["permit"] = "";
		$_SESSION["netmrgsess"]["accessTime"] = "";
	}
}


/**
* get_permit($user)
*
* gets the user's permission level
*/
function get_permit($user) {
	if (IsLoggedIn()) {
		global $PERMIT;
		if ($GLOBALS["netmrg"]["verhist"][$GLOBALS["netmrg"]["dbversion"]] >= $GLOBALS["netmrg"]["verhist"]["0.17"]) {
            $s = getDatabase()->prepare('SELECT IF(disabled = 0, permit, :permit) AS permit FROM user WHERE user = :user');
            $s->bindValue(':permit', $PERMIT['Disabled']);
		}
		else {
            //kinda deprecated, maybe we should simply remove it?
            $s = getDatabase()->prepare('SELECT permit FROM user WHERE user = :user');
		}
        $s->bindValue(':user', $user);
        $s->execute();
        return $s->fetchColumn();
	}

	return false;
}

/**
* GetUserID()
*
* gets the user id of the logged in user
*/
function GetUserID() {
	if (IsLoggedIn()) {
        $s = getDatabase()->prepare('SELECT id FROM user WHERE user= :user');
        $s->bindValue(':user', $_SESSION["netmrgsess"]["username"]);
        $s->execute();
        return $s->fetchColumn();
	}

	return false;
}


/**
* get_group_id()
*
* gets the group id of the logged in user
* $user = the username of get info on
*/
function get_group_id($user = "") {
	if (empty($user)) {
		$user = $_SESSION["netmrgsess"]["username"];
	}

    if (IsLoggedIn()) {
        $s = getDatabase()->prepare('SELECT group_id FROM user WHERE user = :user');
        $s->bindValue(':user', $user);
        $s->execute();

        return $s->fetchColumn();
	}
	
	return false;
}


/**
* view_redirect()
*
* redirects the logged in user to the 'view' page
* if they only have 'single view' priviledges or they
* weren't on their way to somewhere else
*/
function view_redirect() {
	if (empty($_SESSION["netmrgsess"]["redir"]) || ($_SESSION["netmrgsess"]["permit"] == 0)) {
		header("Location: {$GLOBALS['netmrg']['webroot']}/device_tree.php");
		exit;
	}
	else {
		$redir = $_SESSION["netmrgsess"]["redir"];
		unset($_SESSION["netmrgsess"]["redir"]);
		header("Location: $redir");
		exit;
	}
}