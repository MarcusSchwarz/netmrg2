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

namespace Netmrg;


class BaseController
{
    protected $templateName = null;
    protected $mustache = null;
    protected $debug = array('debug' => array());

    private $debugmode = false;

    public function __construct(\Mustache_Engine $mustache, $debugmode = false)
    {
        $this->mustache = $mustache;
        $this->debugmode = $debugmode;
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

        return $tmp;
    }

    protected function render(array $variables = null)
    {
        $variables = (empty($variables)) ? array() : $variables;

        if ($this->debugmode && isset($this->debug['messages']) && count($this->debug['messages']) > 0) {
            $variables['debug'] = $this->debug;
        }
        $variables += $this->getDefaults();

        $this->addMustacheFilters();

        if (empty($this->templateName)) {
            $this->load();
        }
        $tpl = $this->mustache->loadTemplate($this->templateName);


        echo $tpl->render($variables);
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
                return '<img src="'.$imagepath.'" alt="'.$value.'" title="'.$value.'">';
            }
        );
    }

    private function getClassName()
    {
        $classString = strtolower(array_pop(explode('\\', get_class($this))));
        return str_replace('controller', '', $classString);
    }
} 