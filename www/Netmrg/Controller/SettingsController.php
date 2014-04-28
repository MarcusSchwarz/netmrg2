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
use Netmrg\Configuration;
use Netmrg\Exception\ForbiddenException;
use Netmrg\User;

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
        $this->render(array('users' => $users, 'sumusers' => count($users)));
    }

    public function users_editAction() {
        $this->minPermission(Auth::RIGHT_ADMIN);
        $this->add('menu', 'settingsusers');

        $this->testForPresence(array('get' => array('uid')));

        $user = new User($_GET['uid']);
        $this->load('settings/edituser');

        $values = array(
            'permittypes' => Auth::getPermissionTypes($user->permit),
            'groups' => Auth::getGroups($user->group_id),
            'user' => $user,
            'slideshow' => ($user->slideshow) ? 'checked="checked"' : ''
        );

        $this->render($values);
    }

    public function users_patchAction() {
        $this->minPermission(Auth::RIGHT_ADMIN);
        $this->testForPresence(
            array(
                'post' => array('csrftoken', 'username', 'prettyname', 'password', 'password2', 'permit', 'group', 'userid')
            )
        );
        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new ForbiddenException();
        }
        if (!empty($_POST['password'])) {
            if (!$_POST['password'] == $_POST['password2']) {
                $this->errors->append('The passwords did not match');
            }
        }

        if (!$this->hasErrors()) {
            $user = new User($_POST['userid']);
            $user->patch(
                array(
                    'user' => $_POST['username'],
                    'fullname' => $_POST['prettyname'],
                    'password' => $_POST['password'],
                    'permit' => $_POST['permit'],
                    'group_id' => $_POST['group'],
                    'slideshow' => isset($_POST['slideshow']) ? true : false
                )
            );
            $this->success->append('The user '.$_POST['prettyname'].' has been updated');
        }

        $this->redirect('/settings/users');
    }

    public function users_createAction() {
        $this->minPermission(Auth::RIGHT_ADMIN);
        $this->testForPresence(
            array(
                'post' => array('csrftoken', 'username', 'prettyname', 'password', 'password2', 'permit', 'group', 'userid')
            )
        );
        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new ForbiddenException();
        }
        if (!Configuration::externalAuth() && empty($_POST['password'])) {
            $this->errors->append('the password must not be empty unless external Auth is active');
        }
        if (!empty($_POST['password'])) {
            if (!$_POST['password'] == $_POST['password2']) {
                $this->errors->append('The passwords did not match');
            }
        }
        if ($this->auth->userExists(trim($_POST['username']))) {
            $this->errors->append('There already exists an user with the name '.$_POST['username']);
        }

        if (!$this->hasErrors()) {
            $user = new User();
            $user->create(
                array(
                    'user' => $_POST['username'],
                    'fullname' => $_POST['prettyname'],
                    'password' => $_POST['password'],
                    'permit' => $_POST['permit'],
                    'group_id' => $_POST['group'],
                    'slideshow' => isset($_POST['slideshow']) ? true : false
                )
            );
            $this->success->append('The user '.$_POST['prettyname'].' has been created');
        }

        $this->redirect('/settings/users');
    }

    public function users_addAction() {
        $this->minPermission(Auth::RIGHT_ADMIN);
        $this->add('menu', 'settingsusers');


        $this->load('settings/adduser');
        $this->render(array('permittypes' => Auth::getPermissionTypes(), 'groups' => Auth::getGroups()));
    }

    public function users_deleteAction() {
        $this->minPermission(Auth::RIGHT_ADMIN);
        $this->testForPresence(
            array(
                'post' => array('csrftoken')
            )
        );

        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new ForbiddenException();
        }

        $deleteIds = array();
        if (isset($_GET['uid'])) {
            $deleteIds[] = $_GET['uid'];
        }
        elseif (isset($_POST['delids'])) {
            $deleteIds = explode(',', $_POST['delids']);
        }

        foreach($deleteIds as $id) {
            $this->auth->deleteUser($id);
        }
        $this->redirect('/settings/users');
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
