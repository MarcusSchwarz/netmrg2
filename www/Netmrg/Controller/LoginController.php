<?php
/**
 * Part of NetMRG2
 * Copyright (c) 2014
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
 *
 * @author Marcus Schwarz <msspamfang@gmx.de>
 */

namespace Netmrg\Controller;
use Netmrg\BaseController;

class LoginController extends BaseController {

    public function indexAction() {
        if (!empty($_POST['username'])) {
            if (
                !$GLOBALS["netmrg"]["externalAuth"] &&
                $this->auth->userHasCorrectPassword($_POST['username'], $_POST['password'])
            ) {
                $this->session->setSessionParameters($this->auth, $_POST['username'], $_POST['username'], $_POST['password']);
                $this->redirect('device_tree.php');
            }
            else {
                $this->errors->append('Invalid username or password');
            }
        }
        $this->render(array());
    }

    public function logoutAction() {
        $this->auth->logoutAndResetSession();
        $this->redirect('/login');
    }

}
