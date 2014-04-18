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

namespace Netmrg;


class BaseController
{
    protected $templateName = null;
    protected $mustache = null;
    protected $debug = array('debug' => array());
    protected $errors = null;
    protected $success = null;

    private $debugmode = false;
    protected $auth = null;
    protected $session = null;

    public function __construct(
        \Mustache_Engine $mustache,
        Auth $auth,
        Session $session = null,
        $debugmode = false
    ) {
        $this->mustache = $mustache;
        $this->debugmode = $debugmode;
        $this->auth = $auth;
        $this->session = $session;
        $this->errors = new \ArrayObject(array());
        $this->success = new \ArrayObject(array());
    }

    public function load($templateName = null)
    {
        $this->templateName = (empty($templateName)) ? $this->getClassName() : $templateName;
    }

    public function addDebugMessage($newMessage)
    {
        $this->debug['messages'][] = $newMessage;

    }

    private function getDefaults()
    {
        $tmp = array();

        $tmp['__tpl_webroot'] = $GLOBALS['netmrg']['webroot'];
        $tmp['__tpl_companylink'] = $GLOBALS['netmrg']['companylink'];
        $tmp['__tpl_companyname'] = $GLOBALS['netmrg']['company'];

        $tmp['__tpl_isloggedin'] = $this->auth->userIsLoggedIn();
        $tmp['__tpl_username'] = $this->auth->getUsername();
        return $tmp;
    }

    private function getErrors()
    {
        return $this->getMessages('errors');
    }

    private function getSuccess()
    {
        return $this->getMessages('success');
    }

    protected function render(array $variables = null)
    {
        $variables = (empty($variables)) ? array() : $variables;

        if ($this->debugmode) {
            if (isset($this->debug['messages']) && count($this->debug['messages']) > 0) {
                $variables['debug'] = $this->debug;
            }
            $variables['debug']['sessiondata'] = $this->dump($_SESSION);
        }
        $variables += $this->getDefaults();
        $variables += $this->getErrors();
        $variables += $this->getSuccess();

        $this->addMustacheFilters();

        if (empty($this->templateName)) {
            $this->load();
        }
        $tpl = $this->mustache->loadTemplate($this->templateName);

        echo $tpl->render($variables);
    }

    private function dump($what)
    {
        ob_start();
        var_dump($what);
        $result = ob_get_clean();
        return $result;
    }

    protected function minPermission($permission)
    {
        if ($this->session->get('permit') < $permission) {
            throw new NetmrgPermissionException();
        }
    }

    private function addMustacheFilters()
    {
        $this->mustache->addHelper(
            'case',
            array(
                'lower' => function ($value) {
                        return strtolower((string)$value);
                    },
                'upper' => function ($value) {
                        return strtoupper((string)$value);
                    },
            )
        );
        $this->mustache->addHelper(
            'route',
            function ($value) {
                #todo ./index for testing purposes right now
                return $GLOBALS['netmrg']['webroot'] . '/index' . $value; // todo $this is not available in 5.3
            }
        );
        $this->mustache->addHelper(
            'image',
            function ($value) {
                $imagepath = $GLOBALS['netmrg']['staticimagedir'] . '/' . $value;
                return '<img src="' . $imagepath . '" alt="' . $value . '" title="' . $value . '">';
            }
        );
    }

    private function getClassName()
    {
        $classString = strtolower(array_pop(explode('\\', get_class($this))));
        return str_replace('controller', '', $classString);
    }

    protected function redirect($target = null)
    {
        if ($this->errors->count() > 0) {
            $this->session->set('errors', $this->errors);
        }
        if ($this->success->count() > 0) {
            $this->session->set('success', $this->success);
        }

        $redir = $this->session->get('redir'); // 5.3 does not allow empty(func())
        if (empty($redir)) { // removed permission, this should not be handled here  || ($this->session->get('permit') == 0)
            if (stripos($target, '.php') !== false) {
                $location = $GLOBALS['netmrg']['webroot'] . '/' . $target;
            } else {
                #todo ./index for testing purposes right now
                $location = $GLOBALS['netmrg']['webroot'] . '/index' . $target;
            }

        } else {
            $this->session->set('redir', null);
            $location = $redir;
        }
        header('Location: ' . $location);
        exit;
    }

    protected function testForPresence(array $values)
    {
        if (empty($values)) {
            return true;
        }
        if (isset($values['post'])) {
            foreach ($values['post'] as $test) {
                if (!isset($_POST[$test])) {
                    throw new NetmrgMissingException($test);
                }
            }
        }
        if (isset($values['get'])) {
            foreach ($values['get'] as $test) {
                if (!isset($_GET[$test])) {
                    throw new NetmrgMissingException($test);
                }
            }
        }
        return true;

    }

    /**
     * @param $type
     * @return array
     */
    private function getMessages($type)
    {
        $result = array();
        if (!empty($this->session)) {
            $sessionErrors = $this->session->get($type);
            if (!empty($sessionErrors) && $sessionErrors->count() > 0) {
                $this->session->set($type, null);
                $result += $sessionErrors->getArrayCopy();
            }
        }
        if ($this->$type->count() > 0) {
            $result += $this->$type->getArrayCopy();
        }
        return array($type => $result);
    }
} 
