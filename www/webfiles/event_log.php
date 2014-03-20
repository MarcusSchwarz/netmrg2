<?php
/********************************************
 * NetMRG Integrator
 *
 * event_log.php
 * Event Log Viewer
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
check_auth($GLOBALS['PERMIT']["ReadAll"]);

if (!isset($_REQUEST['index'])) {
    $_REQUEST['index'] = 0;
}

begin_page("event_log.php", "Event Log", 1);

$eventlog_handle = getDatabase()->query('SELECT event_id, date, time_since_last_change, situation, dev_id, devices.name AS dev_name, events.name AS ev_name FROM event_log, events, monitors, sub_devices, devices WHERE event_log.event_id = events.id AND events.mon_id = monitors.id AND monitors.sub_dev_id = sub_devices.id AND sub_devices.dev_id = devices.id ORDER BY event_log.id DESC');
$numrows         = $eventlog_handle->columnCount();

make_plain_display_table("Event Log", "Date/Time", "#", "Time Since Last Change", "#", "Event", "#");

if ($_REQUEST['index'] < $numrows) {
    $s->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, intval($_REQUEST['index']));
    //todo is this really the same? I've never used seek before
    //todo I think it would be by far better to include [index] and rowcount as LIMIT option; numrows therefor must be an extra SELECT COUNT()
    //db_data_seek($eventlog_handle, $_REQUEST['index']);
}
$rowcount = 0;
while (($row = $eventlog_handle->columnCount(PDO::FETCH_ASSOC)) && $rowcount < 25) {
    make_display_item("editfield".($rowcount % 2),
        array("text" => date("Y/m/d H:i:s", $row["date"])),
        array("text" => format_time_elapsed($row["time_since_last_change"])),
        array("text" => get_img_tag_from_status($row["situation"])." ".$row['dev_name'].": ".$row['ev_name'])
    );
    $rowcount++;
}
echo '</table>';
echo '<br>';

if ($_REQUEST['index'] >= 25) {
    echo '<a href="'.$_SERVER["PHP_SELF"].'?index='.($_REQUEST['index'] - 25).'">[<- Prev]</a>';
}
else {
    echo '<span style="disabled">[<- Prev]';
}

echo '&nbsp;&nbsp;';

if (($rowcount + $_REQUEST['index']) < $numrows) {
    echo '<a href="'.$_SERVER["PHP_SELF"].'?index='.($_REQUEST['index'] + 25).'">[Next ->]</a>';
}
else {
    echo '<span style="disabled">[Next ->]</span>';
}

end_page();
