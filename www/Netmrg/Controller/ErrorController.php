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

class ErrorController extends BaseController
{

    public function notfoundAction($errormessage = null)
    {
        $this->renderErrorTemplate('exceptions/404notfound', $errormessage);
    }

    /**
     * @param $template
     * @param $errormessage
     */
    private function renderErrorTemplate($template, $errormessage)
    {
        $this->load($template);
        $this->render(array('errors' => $errormessage));
    }

    public function forbiddenAction($errormessage = null)
    {
        $this->renderErrorTemplate('exceptions/403forbidden', $errormessage);
    }

    public function missingAction($errormessage = null)
    {
        $this->renderErrorTemplate('exceptions/400badrequest', $errormessage);
    }

    public function databaseAction($errormessage = null)
    {
        $this->renderErrorTemplate('exceptions/500database', $errormessage);
    }

    public function internalerrorAction($errormessage = null)
    {
        $this->renderErrorTemplate('exceptions/500error', $errormessage);
    }
} 
