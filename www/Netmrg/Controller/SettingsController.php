<?php
/**
 * Part of NetMRG2
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

namespace Netmrg\Controller;

use Netmrg\BaseController;
use Netmrg\Auth;
use Netmrg\NetmrgPermissionException;

class SettingsController extends BaseController
{

    public function indexAction()
    {
        $this->minPermission(Auth::RIGHT_READALL);

        $this->add('menu', 'settingsindex');

        $this->load('settings/index');
        $this->render();
    }

    public function devicesAction()
    {
        $this->minPermission(Auth::RIGHT_READALL);

        $this->add('menu', 'settingsdevices');

        $this->load('settings/index');
        $this->render();
    }


    public function usersAction()
    {
        $this->minPermission(Auth::RIGHT_ADMIN);
        $this->add('menu', 'settingsusers');


        $users = getDatabase()->query('SELECT id, user, fullname, IF(disabled = 0, permit, '.Auth::RIGHT_DISABLED.') AS permit FROM user ORDER BY user')->fetchAll(\PDO::FETCH_ASSOC);

        $users = $this->mapPermissions($users);

        $this->load('settings/users');
        $this->render(array('users' => $users, 'csrftoken' => $this->csrfToken()));
    }

    public function users_deleteAction() {
        $this->minPermission(Auth::RIGHT_ADMIN);
        $this->testForPresence(
            array(
                'post' => array('csrftoken'),
                'get' => array('uid')
            )
        );

        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new NetmrgPermissionException();
        }
        else {
            $this->auth->deleteUser($_GET['uid']);
            $this->redirect('/settings/users');
        }
    }

    private function mapPermissions(array $users) {
        $tmp = array();
        foreach ($users as $key => $val) {
            $val['permit'] = $GLOBALS['PERMIT_TYPES'][$val['permit']];
            $tmp[$key] = $val;
        }

        return $tmp;
    }

}
