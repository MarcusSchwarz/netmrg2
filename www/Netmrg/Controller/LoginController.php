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
use Netmrg\Exception\ForbiddenException;

class LoginController extends BaseController
{

    public function indexAction()
    {
        if ($GLOBALS["netmrg"]["externalAuth"] && !empty($_SERVER["PHP_AUTH_USER"])) {
            // If the user is known to us, let him in, known as the specific user
            if ($this->auth->userExists($_SERVER['PHP_AUTH_USER'])) {
                $this->session->setSessionParameters(
                    $this->auth,
                    $_SERVER['PHP_AUTH_USER'],
                    $_SERVER['PHP_AUTH_USER']
                );
                $this->redirect('device_tree.php');
            }

            // If the user is not known to us, but someone added a default user
            // we let the user in with a generic user name und setting
            if ($this->auth->defaultUserExists()) {
                $this->session->setSessionParameters(
                    $this->auth,
                    $_SERVER["PHP_AUTH_USER"],
                    $GLOBALS["netmrg"]["defaultMapUser"]
                );
                $this->redirect('device_tree.php');
            }

            // If we don't know the user, and there was no generic user
            // don't let him in
            if (!$this->auth->userExists($_SERVER["PHP_AUTH_USER"])) {
                throw new ForbiddenException();
            }
        }

        if (!empty($_POST['username'])) {
            if (
                !$GLOBALS["netmrg"]["externalAuth"] &&
                $this->auth->userHasCorrectPassword($_POST['username'], $_POST['password'])
            ) {
                $this->session->setSessionParameters(
                    $this->auth,
                    $_POST['username'],
                    $_POST['username'],
                    $_POST['password']
                );
                $this->redirect('device_tree.php');
            } else {
                $this->errors->append('Invalid username or password');
            }
        }

        $this->render(array());
    }

    public function logoutAction()
    {
        $this->auth->logoutAndResetSession();
        $this->redirect('/login');
    }

}
