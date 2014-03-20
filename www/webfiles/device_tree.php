<?php
/********************************************
 * NetMRG Integrator
 *
 * device_tree.php
 * Device Tree
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

// require at least read
check_auth($GLOBALS['PERMIT']["SingleViewOnly"]);


// setup cookies
if (!isset($_COOKIE["netmrgDevTree"]) || !is_array($_COOKIE["netmrgDevTree"])) {
    $_COOKIE["netmrgDevTree"]              = array();
    $_COOKIE["netmrgDevTree"]["group"]     = array();
    $_COOKIE["netmrgDevTree"]["device"]    = array();
    $_COOKIE["netmrgDevTree"]["subdevice"] = array();
    $_COOKIE["netmrgDevTree"]["monitor"]   = array();
}
else {
    if (!empty($_COOKIE["netmrgDevTree"]["group"])) {
        $_COOKIE["netmrgDevTree"]["group"] = unserialize(urldecode($_COOKIE["netmrgDevTree"]["group"]));
    }
    else {
        $_COOKIE["netmrgDevTree"]["group"] = array();
    }

    if (!empty($_COOKIE["netmrgDevTree"]["device"])) {
        $_COOKIE["netmrgDevTree"]["device"] = unserialize(urldecode($_COOKIE["netmrgDevTree"]["device"]));
    }
    else {
        $_COOKIE["netmrgDevTree"]["device"] = array();
    }

    if (!empty($_COOKIE["netmrgDevTree"]["subdevice"])) {
        $_COOKIE["netmrgDevTree"]["subdevice"] = unserialize(urldecode($_COOKIE["netmrgDevTree"]["subdevice"]));
    }
    else {
        $_COOKIE["netmrgDevTree"]["subdevice"] = array();
    }

    if (!empty($_COOKIE["netmrgDevTree"]["monitor"])) {
        $_COOKIE["netmrgDevTree"]["monitor"] = unserialize(urldecode($_COOKIE["netmrgDevTree"]["monitor"]));
    }
    else {
        $_COOKIE["netmrgDevTree"]["monitor"] = array();
    }
}

// if we need to do something
if (!empty($_REQUEST["action"])) {
    if ($_REQUEST["action"] == "expand") {
        if (!empty($_REQUEST["groupid"])) {
            if (!in_array($_REQUEST["groupid"], $_COOKIE["netmrgDevTree"]["group"])) {
                array_push($_COOKIE["netmrgDevTree"]["group"], $_REQUEST["groupid"]);
            }
        }
        else {
            if (!empty($_REQUEST["deviceid"])) {
                if (!in_array($_REQUEST["deviceid"], $_COOKIE["netmrgDevTree"]["device"])) {
                    array_push($_COOKIE["netmrgDevTree"]["device"], $_REQUEST["deviceid"]);
                }
            }
            else {
                if (!empty($_REQUEST["subdevid"])) {
                    if (!in_array($_REQUEST["subdevid"], $_COOKIE["netmrgDevTree"]["subdevice"])) {
                        array_push($_COOKIE["netmrgDevTree"]["subdevice"], $_REQUEST["subdevid"]);
                    }
                }
                else {
                    if (!empty($_REQUEST["monid"])) {
                        if (!in_array($_REQUEST["monid"], $_COOKIE["netmrgDevTree"]["monitor"])) {
                            array_push($_COOKIE["netmrgDevTree"]["monitor"], $_REQUEST["monid"]);
                        }
                    }
                }
            }
        }
    }
    else {
        if ($_REQUEST["action"] == "collapse") {
            if (!empty($_REQUEST["groupid"])) {
                if (in_array($_REQUEST["groupid"], $_COOKIE["netmrgDevTree"]["group"])) {
                    unset($_COOKIE["netmrgDevTree"]["group"][array_search($_REQUEST["groupid"], $_COOKIE["netmrgDevTree"]["group"])]);
                }
            }
            else {
                if (!empty($_REQUEST["deviceid"])) {
                    if (in_array($_REQUEST["deviceid"], $_COOKIE["netmrgDevTree"]["device"])) {
                        unset($_COOKIE["netmrgDevTree"]["device"][array_search($_REQUEST["deviceid"], $_COOKIE["netmrgDevTree"]["device"])]);
                    }
                }
                else {
                    if (!empty($_REQUEST["subdevid"])) {
                        if (in_array($_REQUEST["subdevid"], $_COOKIE["netmrgDevTree"]["subdevice"])) {
                            unset($_COOKIE["netmrgDevTree"]["subdevice"][array_search($_REQUEST["subdevid"], $_COOKIE["netmrgDevTree"]["subdevice"])]);
                        }
                    }
                    else {
                        if (!empty($_REQUEST["monid"])) {
                            if (in_array($_REQUEST["monid"], $_COOKIE["netmrgDevTree"]["monitor"])) {
                                unset($_COOKIE["netmrgDevTree"]["monitor"][array_search($_REQUEST["monid"], $_COOKIE["netmrgDevTree"]["monitor"])]);
                            }
                        }
                    }
                }
            }
        }
    }
}
// set our new cookie to last for a week (7days * 24hrs * 60min * 60sec)
setcookie("netmrgDevTree[group]", urlencode(serialize($_COOKIE["netmrgDevTree"]["group"])), time() + 604800);
setcookie("netmrgDevTree[device]", urlencode(serialize($_COOKIE["netmrgDevTree"]["device"])), time() + 604800);
setcookie("netmrgDevTree[subdevice]", urlencode(serialize($_COOKIE["netmrgDevTree"]["subdevice"])), time() + 604800);
setcookie("netmrgDevTree[monitor]", urlencode(serialize($_COOKIE["netmrgDevTree"]["monitor"])), time() + 604800);


begin_page("Device Tree", 1);
?>
    <table style="border-collapse: collapse;" width="100%" border="0" cellspacing="2" cellpadding="2" align="center">
        <tr>
            <td class="editmainheader" colspan="6">
                Device Tree
            </td>
        </tr>

        <tr>
            <td class="editheader" width="">Group</td>
            <td class="editheader" width="">Device</td>
            <td class="editheader" width="">Sub-Device</td>
            <td class="editheader" width="">Monitors</td>
            <td class="editheader" width="">Events</td>
            <td class="editheader" width="">Status</td>
        </tr>

        <?php

        $rowcount = 0;
        draw_group($_SESSION["netmrgsess"]["group_id"], 0, $rowcount, true);
        ?>
    </table>
<?php
end_page();
?>


<?php

/***** FUCTIONS *****/
function draw_group($grp_id, $depth, &$rowcount, $init = false) {
    // for each group
    if ($init && $grp_id != 0) {
        $s = getDatabase()->prepare('SELECT * FROM groups WHERE id = :id ORDER BY name');
    }
    else {
        $s = getDatabase()->prepare('SELECT * FROM groups WHERE parent_id = :id ORDER BY name');
    }
    $s->bindValue(':id', $grp_id);
    $s->execute();


    while ($grp_row = $s->fetch(PDO::FETCH_ASSOC)) {
        $grp_id    = $grp_row["id"];
        $editgroup = ($_SESSION["netmrgsess"]["permit"] > 0) ? '<a class="editfield'.($rowcount % 2).'" href="grpdev_list.php?parent_id='.$grp_id.'">'.
                                                               '<img src="'.get_image_by_name("edit").'" width="15" height="15" border="0" alt="edit" title="edit" align="middle" />'.
                                                               '</a>'."\n" : "";

        // draw +- and create link for group to expand/collapse
        if (in_array($grp_id, $_COOKIE["netmrgDevTree"]["group"])) {
            $img        = get_image_by_name("hide");
            $grp_action = "collapse";
        }
        else {
            $img        = get_image_by_name("show");
            $grp_action = "expand";
        }

        // if > 0 associated items, display 'on' viewgraph
        if (GetNumAssocItems("group", $grp_id) > 0) {
            make_display_item("editfield".($rowcount % 2),
                array("text" =>
                          '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td>'."\n".
                          make_nbsp($depth * 4).
                          '<a class="editfield'.($rowcount % 2).'" href="'.$_SERVER["PHP_SELF"].'?action='.$grp_action.'&amp;groupid='.$grp_id.'">'.
                          '<img src="'.$img.'" border="0" width="9" height="9" alt="expand/collapse" />'."&nbsp;".$grp_row["name"]."\n".
                          '</a>'."\n".
                          '</td><td align="right">'."\n".
                          '<a class="editfield'.($rowcount % 2).'" href="view.php?action=view&amp;object_type=group&amp;object_id='.$grp_id.'">'.
                          '<img src="'.get_image_by_name("viewgraph-on").'" width="15" height="15" border="0" alt="View" title="View" align="middle" />'."\n".
                          '</a>'."\n".
                          '<a class="editfield'.($rowcount % 2).'" href="view.php?action=slideshow&amp;type=1&amp;group_id='.$grp_id.'">'.
                          '<img src="'.get_image_by_name("slideshow").'" width="15" height="15" border="0" alt="View" title="Slide Show" align="middle" />'."\n".
                          '</a>'."\n".
                          $editgroup.
                          '</td></tr></table>'."\n"
                ),
                array(),
                array(),
                array(),
                array(),
                array("text" => get_img_tag_from_status(get_group_status($grp_id)))
            );
        }
        // else, display 'off' viewgraph
        else {
            make_display_item("editfield".($rowcount % 2),
                array("text" =>
                          '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td>'."\n".
                          make_nbsp($depth * 4).
                          '<a class="editfield'.($rowcount % 2).'" href="'.$_SERVER["PHP_SELF"].'?action='.$grp_action.'&amp;groupid='.$grp_id.'">'.
                          '<img src="'.$img.'" border="0" width="9" height="9" alt="expand/collapse" />'."&nbsp;".$grp_row["name"].
                          '</a>'."\n".
                          '</td><td align="right">'."\n".
                          '<a class="editfield'.($rowcount % 2).'" href="view.php?action=view&amp;object_type=group&amp;object_id='.$grp_id.'">'.
                          '<img src="'.get_image_by_name("viewgraph-off").'" width="15" height="15" border="0" alt="View" title="View" align="middle" />'.
                          '</a>'."\n".
                          '<a class="editfield'.($rowcount % 2).'" href="view.php?action=slideshow&amp;type=1&amp;group_id='.$grp_id.'">'.
                          '<img src="'.get_image_by_name("slideshow").'" width="15" height="15" border="0" alt="View" title="Slide Show" align="middle" />'."\n".
                          '</a>'."\n".
                          $editgroup.
                          '</td></tr></table>'."\n"
                ),
                array(),
                array(),
                array(),
                array(),
                array("text" => get_img_tag_from_status(get_group_status($grp_id)))
            );
        }
        $rowcount++;

        // if group is expanded, show the devices
        if (in_array($grp_id, $_COOKIE["netmrgDevTree"]["group"])) {
            $grp_s = getDatabase()->prepare('SELECT dev_parents.dev_id AS id, devices.name AS name, devices.status AS status FROM dev_parents LEFT JOIN devices ON dev_parents.dev_id=devices.id WHERE grp_id = :id ORDER BY name');
            $grp_s->bindValue('id', $grp_id);
            $grp_s->execute();

            while ($dev_row = $grp_s->fetch(PDO::FETCH_ASSOC)) {
                $device_id  = $dev_row["id"];
                $editdevice = ($_SESSION["netmrgsess"]["permit"] > 0) ? '<a class="editfield'.($rowcount % 2).'" href="sub_devices.php?dev_id='.$device_id.'">'.
                                                                        '<img src="'.get_image_by_name("edit").'" width="15" height="15" border="0" alt="edit" title="edit" align="middle" />'.
                                                                        '</a>'."\n" : "";

                // draw +- and create link for device to expand/collapse
                if (in_array($device_id, $_COOKIE["netmrgDevTree"]["device"])) {
                    $img           = get_image_by_name("hide");
                    $device_action = "collapse";
                }
                else {
                    $img           = get_image_by_name("show");
                    $device_action = "expand";
                }

                // if > 0 associated items, display 'on' viewgraph
                if (GetNumAssocItems("device", $device_id) > 0) {
                    make_display_item("editfield".($rowcount % 2),
                        array(),
                        array("text" =>
                                  '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td>'."\n".
                                  '<a class="editfield'.($rowcount % 2).'" href="'.$_SERVER["PHP_SELF"].'?action='.$device_action.'&amp;deviceid='.$device_id.'">'.
                                  '<img src="'.$img.'" border="0" width="9" height="9" alt="expand/collapse" />'."&nbsp;".$dev_row["name"].
                                  '</a>'."\n".
                                  '</td><td align="right">'."\n".
                                  '<a class="editfield'.($rowcount % 2).'" href="view.php?action=view&amp;object_type=device&amp;object_id='.$device_id.'">'.
                                  '<img src="'.get_image_by_name("viewgraph-on").'" width="15" height="15" border="0" alt="View" title="View" align="middle" />'.
                                  '</a>'."\n".
                                  $editdevice.
                                  '</td></tr></table>'."\n"
                        ),
                        array(),
                        array(),
                        array(),
                        array("text" => get_img_tag_from_status($dev_row['status']))
                    );
                }
                // else, display 'off' viewgraph
                else {
                    make_display_item("editfield".($rowcount % 2),
                        array(),
                        array("text" =>
                                  '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td>'."\n".
                                  '<a class="editfield'.($rowcount % 2).'" href="'.$_SERVER["PHP_SELF"].'?action='.$device_action.'&amp;deviceid='.$device_id.'">'.
                                  '<img src="'.$img.'" border="0" width="9" height="9" alt="expand/collapse" />'."&nbsp;".$dev_row["name"].
                                  '</a>'."\n".
                                  '</td><td align="right">'."\n".
                                  '<a class="editfield'.($rowcount % 2).'" href="view.php?action=view&amp;object_type=device&amp;object_id='.$device_id.'">'.
                                  '<img src="'.get_image_by_name("viewgraph-off").'" width="15" height="15" border="0" alt="View" title="View" align="middle" />'.
                                  '</a>'."\n".
                                  $editdevice.
                                  '</td></tr></table>'."\n"
                        ),
                        array(),
                        array(),
                        array(),
                        array("text" => get_img_tag_from_status($dev_row['status']))
                    );
                }
                $rowcount++;

                // if this device is expanded, show the subdevices
                if (in_array($device_id, $_COOKIE["netmrgDevTree"]["device"])) {
                    $dev_s = getDatabase()->prepare('SELECT id, name, status FROM sub_devices WHERE dev_id = :id ORDER BY type, name');
                    $dev_s->bindValue(':id', $dev_row['id']);
                    $dev_s->execute();

                    while ($subdev_row = $dev_s->fetch(PDO::FETCH_ASSOC)) {
                        $subdev_id     = $subdev_row["id"];
                        $editsubdevice = ($_SESSION["netmrgsess"]["permit"] > 0) ? '<a class="editfield'.($rowcount % 2).'" href="monitors.php?sub_dev_id='.$subdev_id.'">'.
                                                                                   '<img src="'.get_image_by_name("edit").'" width="15" height="15" border="0" alt="edit" title="edit" align="middle" />'.
                                                                                   '</a>'."\n" : "";

                        // draw +- and create link for monitor expand/collapse
                        if (in_array($subdev_id, $_COOKIE["netmrgDevTree"]["subdevice"])) {
                            $img           = get_image_by_name("hide");
                            $subdev_action = "collapse";
                        }
                        else {
                            $img           = get_image_by_name("show");
                            $subdev_action = "expand";
                        } // end if M tree
                        // if > 0 associated items, display 'on' viewgraph
                        if (GetNumAssocItems("subdevice", $subdev_id) > 0) {
                            make_display_item("editfield".($rowcount % 2),
                                array(),
                                array(),
                                array("text" =>
                                          '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td>'."\n".
                                          '<a class="editfield'.($rowcount % 2).'" href="'.$_SERVER["PHP_SELF"].'?action='.$subdev_action.'&amp;subdevid='.$subdev_id.'">'.
                                          '<img src="'.$img.'" border="0" width="9" height="9" alt="expand/collapse" />'."&nbsp;".$subdev_row['name'].
                                          '</a>'."\n".
                                          '</td><td align="right">'."\n".
                                          '<a class="editfield'.($rowcount % 2).'" href="view.php?action=view&amp;object_type=subdevice&amp;object_id='.$subdev_id.'">'.
                                          '<img src="'.get_image_by_name("viewgraph-on").'" width="15" height="15" border="0" alt="View" title="View" align="middle" />'.
                                          '</a>'."\n".
                                          $editsubdevice.
                                          '</td></tr></table>'."\n"
                                ),
                                array(),
                                array(),
                                array("text" => get_img_tag_from_status($subdev_row['status']))
                            );
                        }
                        // else, display 'off' viewgraph
                        else {
                            make_display_item("editfield".($rowcount % 2),
                                array(),
                                array(),
                                array("text" =>
                                          '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td>'."\n".
                                          '<a class="editfield'.($rowcount % 2).'" href="'.$_SERVER["PHP_SELF"].'?action='.$subdev_action.'&amp;subdevid='.$subdev_id.'">'.
                                          '<img src="'.$img.'" border="0" width="9" height="9" alt="expand/collapse" />'."&nbsp;".$subdev_row['name'].
                                          '</a>'."\n".
                                          '</td><td align="right">'."\n".
                                          '<a class="editfield'.($rowcount % 2).'" href="view.php?action=view&amp;object_type=subdevice&amp;object_id='.$subdev_id.'">'.
                                          '<img src="'.get_image_by_name("viewgraph-off").'" width="15" height="15" border="0" alt="View" title="View" align="middle" />'.
                                          '</a>'."\n".
                                          $editsubdevice.
                                          '</td></tr></table>'."\n"
                                ),
                                array(),
                                array(),
                                array("text" => get_img_tag_from_status($subdev_row['status']))
                            );
                        }
                        $rowcount++;

                        // if this subdevice is expanded, show the monitors
                        if (in_array($subdev_id, $_COOKIE["netmrgDevTree"]["subdevice"])) {
                            $subdev_s = getDatabase()->prepare('SELECT id, status FROM monitors WHERE sub_dev_id = :id');
                            $subdev_s->bindParam(':id', $subdev_row['id']);
                            $subdev_s->execute();

                            // while we have monitors
                            while ($mon_row = $subdev_s->fetch(PDO::FETCH_ASSOC)) {
                                $mon_id      = $mon_row["id"];
                                $editmonitor = ($_SESSION["netmrgsess"]["permit"] > 0) ? '<a class="editfield'.($rowcount % 2).'" href="events.php?mon_id='.$mon_id.'">'.
                                                                                         '<img src="'.get_image_by_name("edit").'" width="15" height="15" border="0" alt="edit" title="edit" align="middle" />'.
                                                                                         '</a>'."\n" : "";

                                // draw +- and create link for monitor expand/collapse
                                if (in_array($mon_id, $_COOKIE["netmrgDevTree"]["monitor"])) {
                                    $img            = get_image_by_name("hide");
                                    $monitor_action = "collapse";
                                }
                                else {
                                    $img            = get_image_by_name("show");
                                    $monitor_action = "expand";
                                }
                                make_display_item("editfield".($rowcount % 2),
                                    array(),
                                    array(),
                                    array(),
                                    array("text" =>
                                              '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td>'."\n".
                                              '<a class="editfield'.($rowcount % 2).'" href="'.$_SERVER["PHP_SELF"].'?action='.$monitor_action.'&amp;monid='.$mon_id.'">'.
                                              '<img src="'.$img.'" border="0" width="9" height="9" alt="expand/collapse" />'."&nbsp;".get_short_monitor_name($mon_row["id"]).
                                              '</a>'."\n".
                                              '</td><td align="right">'."\n".
                                              '<a class="editfield'.($rowcount % 2).'" href="enclose_graph.php?type=mon&amp;id='.$mon_id.'">'.
                                              '<img src="'.get_image_by_name("viewgraph-on").'" width="15" height="15" border="0" alt="View" title="View" align="middle" />'.
                                              '</a>'."\n".
                                              $editmonitor.
                                              '</td></tr></table>'."\n"
                                    ),
                                    array(),
                                    array("text" => get_img_tag_from_status($mon_row['status']))
                                );
                                $rowcount++;

                                // if this monitor is expanded, show the events
                                if (in_array($mon_id, $_COOKIE["netmrgDevTree"]["monitor"])) {
                                    $evt_s = getDatabase()->prepare('SELECT * FROM events WHERE mon_id = :id');
                                    $evt_s->bindValue(':id', $mon_id);
                                    $evt_s->execute();

                                    $event_total = getDatabase()->prepare('SELECT COUNT (*) FROM events WHERE mon_id = :id')->fetchColumn();

                                    for ($event_count = 1; $event_count <= $event_total; ++$event_count) {
                                        $event_row = $evt_s->fetch(PDO::FETCH_ASSOC);
                                        $color     = get_color_from_situation($event_row["situation"]);

                                        if ($event_row["last_status"] == 1) {
                                            $img = ("<img src=\"".get_image_by_name($color."_led_on")."\" border=\"0\" />");
                                        }
                                        else {
                                            $img = ("<img src=\"".get_image_by_name($color."_led_off")."\" border=\"0\" />");
                                        }
                                        make_display_item("editfield".($rowcount % 2),
                                            array(),
                                            array(),
                                            array(),
                                            array(),
                                            array("text" => $event_row["name"]),
                                            array("text" => $img)
                                        );
                                        $rowcount++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // this is down here so each group's items show up with that group,
            //   and not putting all the sub groups together before the devices
            draw_group($grp_id, $depth + 1, $rowcount);
        }
    }
}
