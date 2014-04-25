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
use Netmrg\Exception\ForbiddenException;

class PreferencesController extends BaseController
{

    public function indexAction()
    {
        $this->minPermission(Auth::RIGHT_SINGLEVIEWONLY);
        if (isset($_GET['uid']) && !$this->auth->userHasAtLeastPermissionLevel(Auth::RIGHT_ADMIN)) {
            throw new ForbiddenException();
        }
        if ($this->session->get('username') == $GLOBALS['netmrg']['defaultMapUser']) {
            throw new ForbiddenException();
        }
        $userid = (isset($_GET['uid']) && intval($_GET['uid']) == $_GET['uid'])
            ? $_GET['uid']
            : $this->auth->getUserID();
        $username = $this->auth->getUsername($userid);
        $setting = GetUserPref($userid, 'SlideShow', 'AutoScroll');
        $checked = (!empty($setting)) ? 'checked' : '';
        $this->render(
            array(
                'username' => $username,
                'userid' => $userid,
                'checked' => $checked
            )
        );
    }

    public function updateAction()
    {
        $changePassword = true;
        $this->minPermission(Auth::RIGHT_SINGLEVIEWONLY);
        $this->testForPresence(
            array('post' => array('csrftoken', 'userid', 'password1', 'password2'))
        );

        if ($this->session->get('username') == $GLOBALS['netmrg']['defaultMapUser']) {
            throw new ForbiddenException();
        }

        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            $this->errors->append('Token mismatch');
        }

        if (trim($_POST['password1']) != trim($_POST['password2'])) {
            $this->errors->append('The passwords do not match');
        }

        if (strlen(trim($_POST['password1'])) == 0) {
            $this->errors->append('Empty passwords are not allowed, did not change the password');
            $changePassword = false;
        }

        if (intval($_POST['userid']) != $this->auth->getUserId()) {
            if (!$this->auth->userHasAtLeastPermissionLevel(Auth::RIGHT_ADMIN)) {
                throw new ForbiddenException();
            }
        }

        $checkboxChanged = SetUserPref(
            $_POST['userid'],
            'SlideShow',
            'AutoScroll',
            !empty($_POST['slideshow'])
        );

        if ($checkboxChanged > 0) {
            $this->success->append('The slideshow setting has been updated');
        }
        if ($this->errors->count() == 0) {
            if ($changePassword) {
                $this->auth->updatePassword($_POST['userid'], $_POST['password1']);
                $this->success->append(
                    'The password for user ' . $this->auth->getUsername(
                        $_POST['userid']
                    ) . ' has been updated'
                );
            }
        }

        $this->redirect('/preferences');
    }

}
