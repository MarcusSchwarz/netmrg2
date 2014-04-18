<?php
/**
 * Session.php
 * Functions used for session access of NetMRG
 * Copyright (c) 2014
 *   Marcus Schwarz <msspamfang@gmx.de>
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * @author Marcus Schwarz <msspamfang@gmx.de>
 */

namespace Netmrg;

class Session
{

    public function __construct($sessionHandler = null)
    {
        if (!empty($sessionHandler)) {
            // todo
        } else {
            // todo if we configure parameters domain and ssl, we could make this more secure
            session_start();
        }
        $this->init();


    }

    public function get($field)
    {
        if (!isset($_SESSION['netmrgsess'])) {
            throw new NetmrgException('trying to access an uninitialized session');
        }
        return ((isset($_SESSION['netmrgsess'][$field])) ? $_SESSION['netmrgsess'][$field] : null);
    }

    public function set($field, $value = null)
    {
        if (empty($field) || is_array($field) || is_object($field)) {
            throw new NetmrgException('invalid session field type');
        }
        $_SESSION['netmrgsess'][$field] = $value;
    }

    private function init()
    {
        if (!isset($_SESSION['netmrgsess']) || !is_array($_SESSION['netmrgsess'])) {
            $this->reset();
        }
    }

    public function setSessionParameters(Auth $auth, $prettyusername, $user, $password = '')
    {
        $auth->resetLoggedInState();
        $this->set('prettyname', $prettyusername);
        $this->set('username', $user);
        $this->set('password', $password);
        $this->set('accessTime', time());
        $this->set('remote_addr', $_SERVER['REMOTE_ADDR']);
        $this->set('permit', $auth->getUsersPermissionLevel($user));
        $this->set('group_id', $auth->getUserGroupId($user));
    }

    public function reset()
    {
        $_SESSION['netmrgsess'] = array();
        $this->set('prettyname', '');
        $this->set('username', '');
        $this->set('password', '');
        $this->set('accessTime', '');
        $this->set('remote_addr', '');
        $this->set('permit', Auth::RIGHT_DISABLED);
        $this->set('group_id', '');
    }


} 
