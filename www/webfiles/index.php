<?php
/********************************************
 * NetMRG Integrator
 *
 * index.php
 * Site Index Page
 *
 * Copyright (C) 2001-2014
 *   Brady Alleman <brady@thtech.net>
 *   Douglas E. Warner <silfreed@silfreed.net>
 *   Kevin Bonner <keb@nivek.ws>
 *   Marcus Schwarz <msspamfang@gmx.de>
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

if (isset($_GET['controller'])) {
    $classname = 'Netmrg\Controller\\'.ucfirst($_GET['controller']).'Controller';  // todo test for remote file inclusion!

    try {
        $controller = new $classname($mustache, $auth, $session, true); // todo true activates the debug mode
    }
    catch (Exception $e) {
        throw new \Netmrg\Netmrg404Exception;
    }

    if (!isset($_GET['action']) || empty($_GET['action'])) {
        $desiredAction = 'index';
    }
    else {
        $desiredAction = strtolower($_GET['action']);
    }

    $action = $desiredAction.'Action';

    if (is_callable(array($controller, $action))) {
        $controller->$action();
    }
    else {
        throw new \Netmrg\Netmrg404Exception();
    }

}
else {
    header("Location: index/login");
}
