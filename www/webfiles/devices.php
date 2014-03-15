<?php
/********************************************
* NetMRG Integrator
*
* devices.php
* Monitored Devices Editing Page
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

if (!isset($_REQUEST['action'])) {
	$_REQUEST['action'] = "add";
}

switch ($_REQUEST["action"]) {
	case "doadd":
	case "doedit":
		doedit();
		break;
		
	case "delete":
	case "dodelete":
		dodelete();
		break;
		
	case "deletemulti" :
		if (isset($_REQUEST["dev_id"])) {
			foreach ($_REQUEST["dev_id"] as $key => $val) {
				delete_device($key, $_REQUEST["grp_id"]);
			}
		}
		display();
		break;
	
	case "doaddtogrp":
		doaddtogrp();
		break;
		
	case "addtogrp":
		displayaddtogrp();
		break;
		
	case "add":
		displayadd();
		break;
		
	case "addnew":
	case "edit":
		displayedit();
		break;
		
	case "duplicate":
		doduplicate();
		break;
}


/***** FUNCTIONS *****/
function doedit() {
	if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "doedit") {
        $devId = intval($_REQUEST['dev_id']);

		if (!isset($_REQUEST["disabled"])) {
            $_REQUEST["disabled"] = 0;
        }
		if (!isset($_REQUEST["snmp_version"])) {
            $_REQUEST["snmp_version"] = 0;
        }
		if (!isset($_REQUEST["no_snmp_uptime_check"])) {
            $_REQUEST["no_snmp_uptime_check"] = 0;
        }
		if (!isset($_REQUEST["unknowns_on_snmp_restart"])) {
            $_REQUEST["unknowns_on_snmp_restart"] = 0;
        }

		if ($devId == 0) {
            $s = getDatabase()->prepare('INSERT INTO devices (name, ip, snmp_read_community, snmp3_user, snmp3_seclev, snmp3_aprot, snmp3_apass, snmp3_pprot, snmp3_ppass, dev_type, snmp_recache_method, disabled, snmp_version, snmp_port, snmp_timeout, snmp_retries, no_snmp_uptime_check, unknowns_on_snmp_restart) VALUES (:name, :ip, :snmp_read_community, :snmp3_user, :snmp3_seclev, :snmp3_aprot, :snmp3_apass, :snmp3_pprot, :snmp3_ppass, :dev_type, :snmp_recache_method, :disabled, :snmp_version, :snmp_port, :snmp_timeout, :snmp_retries, :no_snmp_uptime_check, :unknowns_on_snmp_restart)');
			$just_now_disabled = false;
			$dev_type_changed = false;
		}
		else {
            $s = getDatabase()->prepare('UPDATE devices SET name = :name, ip = :ip, snmp_read_community = :snmp_read_community, snmp3_user = :snmp3_user, snmp3_seclev = :snmp3_seclev, snmp3_aprot = :snmp3_aprot, snmp3_apass = :snmp3_apass, snmp3_pprot = :snmp3_pprot, snmp3_ppass = :snmp3_ppass, dev_type = :dev_type, snmp_recache_method = :snmp_recache_method, disabled = :disabled, snmp_version = :snmp_version, snmp_port = :snmp_port, snmp_timeout = :snmp_timeout, snmp_retries = :snmp_retries, no_snmp_uptime_check = :no_snmp_uptime_check, unknowns_on_snmp_restart = :unknowns_on_snmp_restart WHERE id = :id');
            $s->bindValue(':id', $devId);
            $q = getDatabase()->prepare('SELECT disabled, dev_type FROM devices WHERE id = :id');
            $q->bindValue(':id', $devId);
            $q->execute();
			$r = $q->fetch(PDO::FETCH_ASSOC);
			$just_now_disabled = (($r['disable'] == 0) && ($_REQUEST['disabled'] == 1));
			$dev_type_changed = ($r['dev_type'] != $_REQUEST['dev_type']);
		}

        $s->bindValue(':dev_name', $_REQUEST['dev_name']);
        $s->bindValue(':dev_ip', $_REQUEST['dev_ip']);
        $s->bindValue(':snmp_read_community', $_REQUEST['snmp_read_community']);
        $s->bindValue(':snmp3_user', $_REQUEST['snmp3_user']);
        $s->bindValue(':snmp3_seclev', $_REQUEST['snmp3_seclev']);
        $s->bindValue(':snmp3_aprot', $_REQUEST['snmp3_aprot']);
        $s->bindValue(':snmp3_apass', $_REQUEST['snmp3_apass']);
        $s->bindValue(':snmp3_pprot', $_REQUEST['snmp3_pprot']);
        $s->bindValue(':snmp3_ppass', $_REQUEST['snmp3_ppass']);
        $s->bindValue(':dev_type', $_REQUEST['dev_type']);
        $s->bindValue(':snmp_recache_method', $_REQUEST['snmp_recache_method']);
        $s->bindValue(':disabled', $_REQUEST['disabled']);
        $s->bindValue(':snmp_version', $_REQUEST['snmp_version']);
        $s->bindValue(':snmp_port', $_REQUEST['snmp_port']);
        $s->bindValue(':snmp_timeout', $_REQUEST['snmp_timeout']);
        $s->bindValue(':snmp_retries', $_REQUEST['snmp_retries']);
        $s->bindValue(':no_snmp_uptime_check', $_REQUEST['no_snmp_uptime_check']);
        $s->bindValue(':unknowns_on_snmp_restart', $_REQUEST['unknowns_on_snmp_restart']);

        $s->execute();
        $lastId = getDatabase()->lastInsertId();

		if ($devId == 0) {
            $s = getDatabase()->prepare('INSERT INTO dev_parents (grp_id, dev_id) VALUES (:grp_id, :dev_id)');
            $s->bindValue('grp_id', $_REQUEST['grp_id']);
            $s->bindValue('dev_id', $lastId);
            $s->execute();
		}

		if ($just_now_disabled) {
            getDatabase()->exec('UPDATE devices SET status = 0 WHERE id = '.$devId);
            getDatabase()->exec('UPDATE sub_devices SET status = 0 WHERE dev_id = '.$devId);

            $q = getDatabase()->query('SELECT id FROM sub_devices WHERE dev_id = '.$devId);

			while ($r2 = $q->fetch(PDO::FETCH_ASSOC)) {
                getDatabase()->exec('UPDATE monitors SET status = 0 WHERE sub_dev_id = '.$r2['id']);

				$q1 = getDatabase()->query('SELECT id FROM monitors WHERE sub_dev_id = '.$r2['id']);
				while ($r1 = $q1->fetch(PDO::FETCH_ASSOC)) {
                    getDatabase()->exec('UPDATE events SET last_status = 0 WHERE mon_id = '.$r1['id']);
				}
			}
		}

		if ($dev_type_changed) {
            $old_props_query = getDatabase()->query('SELECT * FROM dev_props LEFT JOIN dev_prop_vals ON dev_props.id = dev_prop_vals.prop_id WHERE dev_type_id = '.$r['dev_type'].' AND dev_id = '.$devId);

			while ($old_prop_row = $old_props_query->fetch(PDO::FETCH_ASSOC)) {
				$new_props_query = getDatabase()->prepare('SELECT * FROM dev_props WHERE dev_type_id = :dev_type AND name = :name');
                $new_props_query->bindValue(':dev_type', $_REQUEST['dev_type']);
                $new_props_query->bindValue(':name', $old_prop_row['name']);
                $new_props_query->execute();
				if ($new_prop_row = $new_props_query->fetch(PDO::FETCH_ASSOC)) {
                    $t = getDatabase()->prepare('INSERT INTO dev_prop_vals (dev_id, prop_id, value) VALUES (:dev_id, :prop_id, :value)');
                    $t->bindValue(':dev_id', $_REQUEST['dev_id']);
                    $t->bindValue(':prop_id', $new_prop_row['id']);
                    $t->bindValue(':value', $old_prop_row['value']);
                    $t->execute();
				}
                $t = getDatabase()->prepare('DELETE FROM dev_prop_vals WHERE dev_id = :dev_id AND prop_id = :prop_id');
                $t->bindValue(':dev_id', $_REQUEST['dev_id']);
                $t->bindValue(':prop_id', $old_prop_row['prop_id']);
                $t->execute();
			}
		}
	}

	header("Location: grpdev_list.php?parent_id={$_REQUEST['grp_id']}&tripid={$_REQUEST['tripid']}");
	exit;
}

function doaddtogrp() {
    $s = getDatabase()->prepare('INSERT INTO dev_parents (grp_id, dev_id) VALUES (:grp_id, :dev_id)');
    $s->bindValue(':grp_id', $_REQUEST['grp_id']);
    $s->bindValue(':dev_id', $_REQUEST['dev_id']);

	header("Location: grpdev_list.php?parent_id={$_REQUEST['grp_id']}");
	exit;
}

function dodelete() {
	delete_device($_REQUEST["dev_id"], $_REQUEST["grp_id"]);
	header("Location: grpdev_list.php?parent_id={$_REQUEST['grp_id']}&tripid={$_REQUEST['tripid']}");
	exit;
}

function doduplicate() {
	duplicate_device($_REQUEST['dev_id']);
	header("Location: grpdev_list.php?parent_id={$_REQUEST['grp_id']}&tripid={$_REQUEST['tripid']}");
	exit;
}

function displayadd() {
	begin_page("devices.php", "Add Device");
	echo "<span style=\"font-weight:bold;font-size:xlarge;\">\n";
	echo '<a href="';
	echo "devices.php?grp_id={$_REQUEST['grp_id']}&action=addnew&tripid={$_REQUEST['tripid']}";
	echo '">Create a new device</a><br><br>'."\n";
	echo '<a href="';
	echo "devices.php?grp_id={$_REQUEST['grp_id']}&action=addtogrp&tripid={$_REQUEST['tripid']}";
	echo '">Add an existing device to this group</a>'."\n";
	echo "</span>\n";
	end_page();
}

function displayaddtogrp() {
	begin_page("devices.php", "Add Device Group");
	make_edit_table("Add Existing Device to a Group");
	make_edit_select_from_table("Device:","dev_id","devices",-1);
	make_edit_hidden("action","doaddtogrp");
	make_edit_hidden("grp_id",$_REQUEST["grp_id"]);
	make_edit_hidden("tripid",$_REQUEST["tripid"]);
	make_edit_submit_button();
	make_edit_end();
	end_page();
}

function displayedit() {
	begin_page("devices.php", "Edit Device");

    $dev_id = ($_REQUEST["action"] == "addnew") ? 0 : $_REQUEST["dev_id"];

    $s = getDatabase()->prepare('SELECT * FROM devices WHERE dev_id = :dev_id');
    $s->bindValue(':dev_id', $dev_id);
    $s->execute();

	$dev_row = $s->fetch(PDO::FETCH_ASSOC);
	$dev_name = $dev_row["name"];
	$dev_ip = $dev_row["ip"];
	if ($_REQUEST["action"] == "addnew") {
		$dev_row["dev_type"] = "";
		$dev_row["disabled"] = 0;
		$dev_row["snmp_version"] = 0;
		$dev_row["snmp_read_community"] = "";
		$dev_row["snmp3_user"] = "";
		$dev_row["snmp3_seclev"] = 0;
		$dev_row["snmp3_aprot"] = 0;
		$dev_row["snmp3_apass"] = "";
		$dev_row["snmp3_pprot"] = 0;
		$dev_row["snmp3_ppass"] = "";
		$dev_row["snmp_recache_method"] = 3;
		$dev_row["snmp_port"] = 161;
		$dev_row["snmp_timeout"] = 1000000;
		$dev_row["snmp_retries"] = 3;
		$dev_row["no_snmp_uptime_check"] = 0;
		$dev_row["unknowns_on_snmp_restart"] = 1;
	}

	make_edit_table("Edit Device");
	make_edit_group("General");
	make_edit_text("Name:", "dev_name", "25", "100", $dev_name);
	make_edit_text("IP or Host Name:", "dev_ip", "25", "100", $dev_ip);
	make_edit_select_from_table("Device Type:", "dev_type", "dev_types", $dev_row["dev_type"]);
	make_edit_checkbox("Disabled (do not monitor this device)", "disabled", $dev_row["disabled"]);
	make_edit_group("SNMP");
	make_edit_select_from_array("SNMP Support:", "snmp_version", $GLOBALS["SNMP_VERSIONS"], $dev_row["snmp_version"]);
	make_edit_group("SNMP v1/v2c");
	make_edit_text("Read Community:", "snmp_read_community", 50, 200, $dev_row["snmp_read_community"]);
	make_edit_group("SNMP v3");
	make_edit_text("User:", "snmp3_user", "25", "100", $dev_row["snmp3_user"]);
	make_edit_select_from_array("Security Level:", "snmp3_seclev", $GLOBALS['SNMP_SECLEVS'], $dev_row["snmp3_seclev"]);
	make_edit_select_from_array("Authentication Protocol:", "snmp3_aprot", $GLOBALS['SNMP_APROTS'], $dev_row["snmp3_aprot"]);
	make_edit_text("Authentication Password", "snmp3_apass", "25", "100", $dev_row["snmp3_apass"]);
	make_edit_select_from_array("Privacy Protocol:", "snmp3_pprot", $GLOBALS['SNMP_PPROTS'], $dev_row["snmp3_pprot"]);
	make_edit_text("Privacy Password", "snmp3_ppass", "25", "100", $dev_row['snmp3_ppass']);
	make_edit_group("Advanaced SNMP Options");
	make_edit_select_from_array("Recaching Method:", "snmp_recache_method", $GLOBALS["RECACHE_METHODS"], $dev_row["snmp_recache_method"]);
	make_edit_checkbox("Disable Uptime Check", "no_snmp_uptime_check", $dev_row["no_snmp_uptime_check"] == 1);
	make_edit_checkbox("Unknowns on Agent Restart", "unknowns_on_snmp_restart", $dev_row["unknowns_on_snmp_restart"] == 1);
	make_edit_text("UDP Port", "snmp_port", 5, 5, $dev_row["snmp_port"]);
	make_edit_text("Timeout (microseconds):", "snmp_timeout", 10, 20, $dev_row["snmp_timeout"]);
	make_edit_text("Retries:", "snmp_retries", 3, 10, $dev_row["snmp_retries"]);
	make_edit_hidden("dev_id", $dev_id);
	make_edit_hidden("action", "doedit");
	make_edit_hidden("grp_id", $_REQUEST["grp_id"]);
	make_edit_hidden("tripid",$_REQUEST["tripid"]);
	make_edit_submit_button();
	make_edit_end();
	end_page();
}

function display() {
	header("Location: grpdev_list.php?parent_id={$_REQUEST['grp_id']}&tripid={$_REQUEST['tripid']}");
	exit;
}
