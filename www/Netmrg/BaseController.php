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

use Netmrg\Exception\BadRequestException;

/**
 * Class BaseController
 * @package Netmrg
 */
class BaseController
{
    /**
     * @var string|null
     */
    protected $templateName = null;
    /**
     * @var \Mustache_Engine|null
     */
    protected $mustache = null;
    /**
     * @var array
     */
    protected $debug = array('debug' => array());
    /**
     * @var \ArrayObject|null
     */
    protected $errors = null;
    /**
     * @var \ArrayObject|null
     */
    protected $success = null;
    /**
     * @var bool
     */
    private $debugmode = false;
    /**
     * @var Auth|null
     */
    protected $auth = null;
    /**
     * @var Session|null
     */
    protected $session = null;
    /**
     * @var array
     */
    private $variables = array();

    /**
     * @param \Mustache_Engine $mustache
     * @param Auth $auth
     * @param Session $session
     * @param bool $debugmode
     */
    public function __construct(
        \Mustache_Engine $mustache = null,
        Auth $auth = null,
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

    /**
     * @param null|string $templateName
     *
     * @return string
     */
    public function load($templateName = null)
    {
        $this->templateName = (empty($templateName)) ? $this->getClassName() : $templateName;
        return $this->templateName;
    }

    /**
     * @param string $newMessage
     */
    protected function addDebugMessage($newMessage)
    {
        $this->debug['messages'][] = $newMessage;

    }

    /**
     * @return array
     */
    private function getDefaults()
    {
        $tmp = array();

        $tmp['__tpl_webroot'] = $GLOBALS['netmrg']['webroot'];
        $tmp['__tpl_companylink'] = $GLOBALS['netmrg']['companylink'];
        $tmp['__tpl_companyname'] = $GLOBALS['netmrg']['company'];
        $tmp['__tpl_externalauth'] = $GLOBALS['netmrg']['externalAuth'];

        // hack to introduce template controlled variables
        $tmp['__tpl_vars'] = function($text, \Mustache_LambdaHelper $helper) {
            $tmp = explode(PHP_EOL, $text);
            if (empty($tmp)) {
                return;
            }
            foreach ($tmp as $v) {
                $v = trim($v);
                preg_match('/^\[\[(.*)\|(.*)]]$/', $v, $parts);
                if (!is_null($parts[1]) && !empty($parts[2])) {
                    $helper->context->push(array($parts[1] => $parts[2]));
                }
            }
            return;
        };

        $tmp['__tpl_isloggedin'] = $this->auth->userIsLoggedIn();
        $tmp['__tpl_username'] = $this->auth->getUsername();
        return $tmp;
    }

    protected final function hasErrors() {
        return ($this->errors->count() > 0);
    }
    /**
     * @return array
     */
    private function getErrors()
    {
        return $this->getMessages('errors');
    }

    /**
     * @return array
     */
    private function getSuccess()
    {
        return $this->getMessages('success');
    }

    /**
     * @param array $variables
     */
    protected final function render(array $variables = null)
    {
        $variables = (empty($variables)) ? array() : $variables;

        if ($this->debugmode) {
            if (isset($this->debug['messages']) && count($this->debug['messages']) > 0) {
                $variables['debug'] = $this->debug;
            }
            //$variables['debug']['sessiondata'] = $this->dump($_SESSION);
        }
        $variables += $this->getDefaults();
        $variables += $this->getVariables();
        $variables += $this->getErrors();
        $variables += $this->getSuccess();
        $variables += $this->setCsrfToken();

        $this->addMustacheFilters();

        if (empty($this->templateName)) {
            $this->load();
        }
        $tpl = $this->mustache->loadTemplate($this->templateName);

        echo $tpl->render($variables);
    }

    /**
     * @return array
     */
    protected final function setCsrfToken()
    {
        $crsftoken = uniqid();
        $this->session->set('csrftoken', $crsftoken);
        return array('csrftoken' => $crsftoken);
    }

    /**
     * @param  string $token
     * @return bool
     */
    protected final function isValidCsrfToken($token)
    {
        $tmp = $this->session->get('csrftoken');
        $this->setCsrfToken(/* empty it*/);
        return ($tmp == $token);
    }

    /**
     * @param mixed $what
     */
    protected function debug($what)
    {
        $this->debug['messages'][] = $this->dump($what);
    }

    /**
     * @param mixed $what
     * @return string
     */
    private function dump($what)
    {
        ob_start();
        var_dump($what);
        $result = ob_get_clean();
        return $result;
    }

    /**
     * @param  int $permission
     * @return bool
     */
    protected function minPermission($permission)
    {
        return $this->auth->userHasAtLeastPermissionLevel($permission);
    }

    /**
     *
     */
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
                return $GLOBALS['netmrg']['webroot'] . '/index' . $value;
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

    /**
     * @return mixed
     */
    private function getClassName()
    {
        $classString = strtolower(array_pop(explode('\\', get_class($this))));
        return str_replace('controller', '', $classString);
    }

    /**
     * @param string|int $key
     * @param mixed $value
     */
    protected function add($key, $value)
    {
        $this->variables[$key] = array($value => $value);
    }

    /**
     * @return array
     */
    private function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param string|null $target
     */
    protected function redirect($target = null)
    {
        if ($this->errors->count() > 0) {
            $this->session->set('errors', $this->errors);
        }
        if ($this->success->count() > 0) {
            $this->session->set('success', $this->success);
        }

        $redir = $this->session->get('redir');
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

    /**
     * @param array $values
     * @return bool
     * @throws BadRequestException
     */
    protected function testForPresence(array $values)
    {
        if (empty($values)) {
            return true;
        }
        if (isset($values['post'])) {
            foreach ($values['post'] as $test) {
                if (!isset($_POST[$test])) {
                    throw new BadRequestException('variable '.$test. ' not found');
                }
            }
        }
        if (isset($values['get'])) {
            foreach ($values['get'] as $test) {
                if (!isset($_GET[$test])) {
                    throw new BadRequestException('variable '.$test. ' not found');
                }
            }
        }
        return true;

    }

    /**
     * @param string $type
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
