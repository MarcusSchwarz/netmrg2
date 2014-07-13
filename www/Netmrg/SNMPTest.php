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

class SNMPTest
{
    public $id = 0;
    public $name = '';
    public $oid = '';
    public $dev_type = 0;
    public $type = 0;
    public $subitem = 0;
    public static $availableTypes = array(
        0 => array('id' => 0, 'name' => 'Direct (Get)'),
        1 => array('id' => 1, 'name' => 'Nth Item (Walk)'),
        2 => array('id' => 2, 'name' => 'Count of Items (Walk)')
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
                $this->id       = $values['id'];
                $this->name     = $values['name'];
                $this->oid      = $values['oid'];
                $this->dev_type = $values['dev_type'];
                $this->type     = $values['type'];
                $this->subitem  = $values['subitem'];

                if (!empty($this->id)) {
                    $this->fromDatabase = true;
                }
            }
        }
        return $this;
    }

    private function load($id)
    {
        $s = getDatabase()->query('SELECT * FROM tests_snmp WHERE id = '.intval($id));

        if (empty($s)) {
            throw new BadRequestException;
        }
        $t              = $s->fetch(\PDO::FETCH_OBJ);
        $this->id       = $t->id;
        $this->name     = $t->name;
        $this->oid      = $t->oid;
        $this->dev_type = $t->dev_type;
        $this->type     = $t->type;
        $this->subitem  = $t->subitem;

        $this->fromDatabase = true;
    }

    public static function delete($id)
    {
        getDatabase()->exec('DELETE FROM tests_snmp WHERE id = '.intval($id));
    }

    public function save()
    {
        if (!$this->fromDatabase) {
            $s = getDatabase()->prepare(
                 'INSERT INTO tests_snmp (name, oid, dev_type, type, subitem) VALUES (:name, :oid, :dev_type, :type, :subitem)'
            );
        } else {
            // update
            $s = getDatabase()->prepare(
                 'UPDATE tests_snmp SET name = :name, oid = :oid, dev_type = :dev_type, type = :type, subitem = :subitem WHERE id = :id'
            );
            $s->bindParam(':id', $this->id);
        }
        $s->bindParam(':name', $this->name);
        $s->bindParam(':oid', $this->oid);
        $s->bindParam(':dev_type', $this->dev_type);
        $s->bindParam(':type', $this->type);
        $s->bindParam(':subitem', $this->subitem);
        $s->execute();
    }
} 
