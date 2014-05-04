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

class SQLTest
{
    public $id = 0;
    public $name = '';
    public $sub_dev_type = 0;
    public $host = '';
    public $user = '';
    public $password = '';
    public $query = ''; // todo maxlength in db = 255
    public $column_num = 0;
    public $timeout = 10;

    private $fromDatabase = false;

    /**
     * @param $values array|int
     */
    public function __construct($values = null)
    {
        if (!empty($values)) {
            if (is_numeric($values)) {
                $this->load($values);
            } elseif (is_array($values)) {
                $this->id = $values['id'];
                $this->name = $values['name'];
                $this->user = $values['user'];
                $this->password = $values['password'];
                $this->sub_dev_type = $values['sub_dev_type'];
                $this->host = $values['host'];
                $this->password = $values['password'];
                $this->column_num = $values['column_num'];
                $this->timeout = $values['timeout'];
                $this->query = $values['query'];
                if (!empty($this->id)) {
                    $this->fromDatabase = true;
                }
            }
        }
        return $this;
    }

    private function load($id)
    {
        $s = getDatabase()->query('SELECT * FROM tests_sql WHERE id = ' . intval($id));

        if (empty($s)) {
            throw new BadRequestException;
        }
        $t = $s->fetch(\PDO::FETCH_OBJ);
        $this->id = $t->id;
        $this->name = $t->name;
        $this->user = $t->user;
        $this->query = $t->query;
        $this->password = $t->password;
        $this->sub_dev_type = $t->sub_dev_type;
        $this->host = $t->host;
        $this->column_num = $t->column_num;
        $this->timeout = $t->timeout;
        $this->fromDatabase = true;
    }

    public static function delete($id)
    {
        getDatabase()->exec('DELETE FROM tests_sql WHERE id = ' . intval($id));
    }

    public function save()
    {
        if (!$this->fromDatabase) {
            $s = getDatabase()->prepare(
                'INSERT INTO tests_sql (name, user, password, sub_dev_type, host, column_num, timeout, query) VALUES (:name, :user, :password, :sub_dev_type, :host, :column_num, :timeout, :query)'
            );
        } else {
            // update
            $s = getDatabase()->prepare(
                'UPDATE tests_sql SET name = :name, user = :user, password = :password, sub_dev_type = :sub_dev_type, host = :host, column_num = :column_num, timeout = :timeout, query = :query WHERE id = :id'
            );
            $s->bindParam(':id', $this->id);
        }
        $s->bindParam(':name', $this->name);
        $s->bindParam(':user', $this->user);
        $s->bindParam(':password', $this->password);
        $s->bindParam(':sub_dev_type', $this->sub_dev_type);
        $s->bindParam(':host', $this->host);
        $s->bindParam(':column_num', $this->column_num);
        $s->bindParam(':timeout', $this->timeout);
        $s->bindParam(':query', $this->query);
        $s->execute();

    }
} 
