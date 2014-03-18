<?php
/********************************************
* NetMRG Integrator
*
* $Id$
* search.php
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
check_auth($GLOBALS['PERMIT']["SingleViewOnly"]);

if (empty($_REQUEST["action"])) {
	$_REQUEST["action"] = "search";
}

if (empty($_REQUEST["query"])) {
	$_REQUEST["query"] = "";
}

switch ($_REQUEST['action']) {
	case "search":
	default:
		display($_REQUEST["query"]);
		break;
}


/***** FUNCTIONS *****/

/**
* display($query)
*
* displays the search results
*
* @param string $query the search query
*/
function display($query) {
	begin_page("search.php", 'Search ('.htmlspecialchars(stripslashes($query)).')');
	$search_result = perform_search($query);
	$search_result_count = 0;
	foreach ($search_result as $search_items) {
		$search_result_count += count($search_items);
	}
	reset($search_result);
	
	echo '<div class="search-header">'."\n";
	echo 'Your search for <i>'.htmlspecialchars(stripslashes($query)).'</i> produced <b>' . $search_result_count . '</b> result(s)';
	echo "</div>\n";
	
	while (list($obj_type, $s_result) = each($search_result)) {
		foreach($s_result as $sitem) {
			display_result($obj_type, $sitem["id"], $sitem["name"], $sitem["groups"], $sitem["parent"]);
		}
	}
	
	end_page();
}


/**
* display_result();
*
* a wrapper for the search results
*
* @param string $object_type type of object (group, device, subdevice)
* @param integer $object_id id of object
* @param string $object_value value of object
* @param array $object_groups group ids and names object is in
* @param array $object_parent parent name and id
*/
function display_result($object_type, $object_id, $object_value, $object_groups, $object_parent) {
	global $SEARCH_ITEMS;
?>
<div class="search-result">
<h3><?php echo $SEARCH_ITEMS[$object_type]["name"]; ?></h3>
<?php 
	// display groups
	echo '[<span class="search-group">';
	$loopcount = 0;
	foreach ($object_groups as $group_id => $group_name) {
		if ($loopcount != 0) {
            echo ", ";
        }
		echo $group_name;
		if (GetNumAssocItems("group", $group_id) > 0) {
			echo "\n".
				'<a class="search-group" href="view.php?action=view&amp;object_type=group&amp;object_id='.$group_id.'">'.
				'<img src="'.get_image_by_name("viewgraph-on").'" width="15" height="15" border="0" alt="View" title="View" align="baseline" />'."\n".
				'</a>'."\n";
		}
		$loopcount++;
	}
	echo "</span>]\n";
	
	if (isset($object_parent)) {
		echo ' : <span class="search-parent">' . $object_parent["name"] . "</span>\n";
		if (GetNumAssocItems($object_parent["type"], $object_parent["id"]) > 0) {
			echo "\n".
				'<a class="search-parent" href="view.php?action=view&amp;object_type='.$object_parent["type"].'&amp;object_id='.$object_parent["id"].'">'.
				'<img src="'.get_image_by_name("viewgraph-on").'" width="15" height="15" border="0" alt="View" title="View" align="baseline" />'.
				'</a>'."\n";
		}
	}
	
	echo ' : <span class="search-object">';
	echo $object_value;
	if (GetNumAssocItems($object_type, $object_id) > 0) {
		echo "\n".
			'<a class="search-parent" href="view.php?action=view&amp;object_type='.$object_type.'&amp;object_id='.$object_id.'">'.
			'<img src="'.get_image_by_name("viewgraph-on").'" width="15" height="15" border="0" alt="View" title="View" align="baseline" />'.
			'</a>'."\n";
	}
	echo "</span><br />\n";
?>
</div>
<?php
}


/**
* perform_search($query);
*
* performs the search of $query over $SEARCH_ITEMS
*
* @param string $query the search query
*
* @returns array
*/
function perform_search($query) {
	global $SEARCH_ITEMS;
	$result = array();
	
	while (list($sname, $sitem) = each($SEARCH_ITEMS)) {
		foreach ($sitem['sql'] as $sql_query) {
            $sql_query = getDatabase()->prepare($sql_query);
            $sql_query->bindValue(':pattern', '%'.$query.'%');
			$tempres = $sql_query->fetch(PDO::FETCH_ASSOC);
			if ($tempres !== false) {
                $result[$sname] = $tempres;
            }
		}
	}
	
	// now we want to make sure the user is authorized to see these items
	while (list($obj_type, $s_result) = each($result)) {
		while (list($key, $sitem) = each($s_result)) {
			switch ($obj_type) {
				case "group" :
				case "device" :
				case "subdevice" :
				default : 
					$allowed_to_view = viewCheckAuth($sitem["id"], $obj_type);
					break;
			}
			
			// if we're not allowed to see this object, don't go any farther
			if (!$allowed_to_view) {
				unset($result[$obj_type][$key]);
			}
			// otherwise, we need to store some additional info
			else {
				// find the groups for this item
				$group_ids = array_unique(GetGroups($obj_type, $sitem["id"]));
				$result[$obj_type][$key]["groups"] = array();
				foreach($group_ids as $group_id) {
					if ($group_id != 0) {
						if (viewCheckAuth($group_id, "group")) {
							$result[$obj_type][$key]["groups"][$group_id] = get_group_name($group_id);
						}
					}
				}
				
				// get the device name for the subdevice
				if ($obj_type == "subdevice") {
					$parent_id = GetSubdeviceParent($sitem["id"]);
					if (viewCheckAuth("device", $parent_id)) {
						$parent_name = get_device_name($parent_id);
						$result[$obj_type][$key]["parent"] = array(
							"id" => $parent_id, 
							"name" => $parent_name, 
							"type" => "device");
					}
				}
			}
		}
	}
	
	// clear out any empty results
	reset($result);
	while (list($obj_type, $s_result) = each($result)) {
		if (!is_array($s_result) || count($s_result) == 0 || empty($s_result)) {
			unset($result[$obj_type]);
		}
	}
	
	reset($result);
	return $result;
}
