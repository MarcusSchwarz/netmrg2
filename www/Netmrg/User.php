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

class User
{

    public $id = 0;
    public $user = '';
    public $pass = '';
    public $permit = -1;
    public $fullname = '';
    public $group_id = -1;
    public $disabled = false;
    public $slideshow = false;


    public function __construct($userid = 0)
    {
        if (!empty($userid)) {
            $this->load($userid);
        }
        return $this;
    }


    protected function load($userid)
    {
        if (intval($userid) == $userid) {
            $s = getDatabase()->query('SELECT * FROM user WHERE id = ' . intval($userid));
        } else {
            $s = getDatabase()->query(
                'SELECT * FROM user WHERE user = ' . getDatabase()->quote($userid)
            );
        }
        if (empty($s)) {
            throw new BadRequestException;
        }
        $t = $s->fetch(\PDO::FETCH_OBJ);
        $this->id = $t->id;
        $this->user = $t->user;
        $this->pass = $t->pass;
        $this->permit = $t->permit;
        $this->fullname = $t->fullname;
        $this->group_id = $t->group_id;
        $this->disabled = ($t->disabled == 0 || $t->permit == -1) ? false : true;
        $this->slideshow = $this->getPref('SlideShow', 'Autoscroll');
    }

    /**
     * GetUserPref($module, $pref)
     * @todo cloned
     * returns the value for the $module and $pref wanted for user $uid
     */
    public function getPref($module, $pref)
    {
        $s = getDatabase()->prepare(
            'SELECT user_prefs.value FROM user_prefs WHERE user_prefs.uid = :uid AND user_prefs.module = :module AND user_prefs.pref = :pref'
        );
        $s->bindValue(':uid', $this->id);
        $s->bindValue(':module', $module);
        $s->bindValue(':pref', $pref);
        $s->execute();

        $row = $s->fetch(\PDO::FETCH_ASSOC);

        return (!empty($row) && !empty($row['value'])) ? true : false;
    }

    public function create(array $patchvars)
    {
        $args = $this->getQueryVars($patchvars);
        if (!empty($args['arguments'])) {
            $query = 'INSERT INTO user SET ' . implode(
                    ', ',
                    $args['arguments']
                ); // I don't like that style of INSERT INTO, but though it's quite handy
            getDatabase()->prepare($query)->execute($args['values']);
            $lastInsertId = getDatabase()->lastInsertId();
            SetUserPref($lastInsertId, 'SlideShow', 'AutoScroll', $patchvars['slideshow']);
        }
    }

    private function getQueryVars(array $patchvars)
    {
        $arguments = array();
        $values = array();

        if (trim($patchvars['user']) != $this->user) {
            $arguments[] = 'user = :user';
            $values[':user'] = trim($patchvars['user']);
        }
        if (trim($patchvars['fullname']) != $this->fullname) {
            $arguments[] = 'fullname = :fullname';
            $values[':fullname'] = trim($patchvars['fullname']);
        }
        if (trim($patchvars['permit']) != $this->permit) {
            $arguments[] = 'permit = :permit';
            $values[':permit'] = trim($patchvars['permit']);
        }
        if (trim($patchvars['group_id']) != $this->group_id) {
            $arguments[] = 'group_id = :group_id';
            $values[':group_id'] = trim($patchvars['group_id']);
        }

        $password = trim($patchvars['password']);
        if (!Configuration::externalAuth() && !empty($password)) {
            $password = Auth::getPasswordHash(trim($patchvars['password']));
            $arguments[] = 'pass = :pass';
            $values[':pass'] = $password;
        }
        return array('arguments' => $arguments, 'values' => $values);

    }

    public function patch(array $patchvars)
    {
        $args = $this->getQueryVars($patchvars);

        if (!empty($args['arguments'])) {
            $query = 'UPDATE user SET ' . implode(
                    ', ',
                    $args['arguments']
                ) . ' WHERE id = ' . $this->id;
            getDatabase()->prepare($query)->execute($args['values']);
        }
        if ($this->slideshow != $patchvars['slideshow']) {
            SetUserPref($this->id, 'SlideShow', 'AutoScroll', $patchvars['slideshow']);
        }
    }

} 
