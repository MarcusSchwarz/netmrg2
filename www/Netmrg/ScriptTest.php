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
 *
 * @author Marcus Schwarz <msspamfang@gmx.de>
 */

namespace Netmrg;

use Netmrg\Exception\BadRequestException;

class ScriptTest
{
    public $id = 0;
    public $name = '';
    public $cmd = '';
    public $data_type = '';
    public $data_type_name = '';
    public $dev_type = '';

    const DATA_TYPE_ERRORCODE   = 1;
    const DATA_TYPE_STANDARDOUT = 2;
    public static $dataTypeNames = array(
        0 => array('id' => 1, 'name' => 'Error Code'),
        1 => array('id' => 2, 'name' => 'Standard Out')
    );
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
                $this->id        = $values['id'];
                $this->name      = $values['name'];
                $this->cmd       = $values['cmd'];
                $this->data_type = intval($values['data_type']);
                $this->dev_type  = $values['dev_type'];

                if (!empty($this->id)) {
                    $this->fromDatabase = true;
                }
                $this->data_type_name = self::getDataTypeName($this->data_type);
            }
        }
        return $this;
    }

    public static function getDataTypeName($datatype)
    {
        foreach (self::$dataTypeNames as $type) {
            if ($type['id'] == $datatype) {
                return $type['name'];
            }
        }
    }

    private function load($id)
    {
        $s = getDatabase()->query('SELECT * FROM tests_script WHERE id = '.intval($id));

        if (empty($s)) {
            throw new BadRequestException;
        }
        $t               = $s->fetch(\PDO::FETCH_OBJ);
        $this->id        = $t->id;
        $this->name      = $t->name;
        $this->cmd       = $t->cmd;
        $this->data_type = intval($t->data_type);
        $this->dev_type  = intval($t->dev_type);

        $this->fromDatabase = true;

        $this->data_type_name = self::getDataTypeName($this->data_type);
    }

    public static function delete($id)
    {
        getDatabase()->exec('DELETE FROM tests_script WHERE id = '.intval($id));
    }

    public function save()
    {
        if (!$this->fromDatabase) {
            $s = getDatabase()->prepare(
                 'INSERT INTO tests_script (name, cmd, data_type, dev_type) VALUES (:name, :cmd, :data_type, :dev_type)'
            );
        } else {
            // update
            $s = getDatabase()->prepare(
                 'UPDATE tests_script SET name = :name, cmd = :cmd, data_type = :data_type, dev_type = :dev_type WHERE id = :id'
            );
            $s->bindParam(':id', $this->id);
        }
        $s->bindParam(':name', $this->name);
        $s->bindParam(':cmd', $this->cmd);
        $s->bindParam(':data_type', $this->data_type);
        $s->bindParam(':dev_type', $this->dev_type);
        $s->execute();
    }
} 
