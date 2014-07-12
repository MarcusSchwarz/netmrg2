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

class Graphs
{
    public $id = 0;
    public $name = '';
    public $width = null;
    public $height = null;
    public $comment = '';
    public $verticallabel = '';
    public $title = '';
    public $type = 'custom';
    public $options = '';
    public $base = null;
    public $min = null;
    public $max = null;

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
                $this->width = $values['width'];
                $this->height = $values['height'];
                $this->comment = $values['comment'];
                $this->verticallabel = $values['verticallabel'];
                $this->title = $values['title'];
                $this->type = $values['type'];
                $this->options = $values['options'];
                $this->base = $values['base'];
                $this->min = $values['min'];
                $this->max = $values['max'];
                if (!empty($this->id)) {
                    $this->fromDatabase = true;
                }
            }
        }
        return $this;
    }

    private function load($id)
    {
        $s = getDatabase()->query('SELECT * FROM graphs WHERE id = ' . intval($id));

        if (empty($s)) {
            throw new BadRequestException;
        }
        $t = $s->fetch(\PDO::FETCH_OBJ);
        $this->id = $t->id;
        $this->name = $t->name;
        $this->title = $t->title;
        $this->verticallabel = $t->vert_label;
        $this->comment = $t->comment;
        $this->width = $t->width;
        $this->height = $t->height;
        $this->type = $t->type;
        $this->options = $t->options;
        $this->base = $t->base;
        $this->min = $t->min;
        $this->max = $t->max;
        $this->fromDatabase = true;
    }

    public static function delete($id)
    {
        getDatabase()->exec('DELETE FROM graphs WHERE id = ' . intval($id));
    }

    public function duplicate()
    {
        $this->fromDatabase = false;
        $this->id = 0;
        $this->name = $this->name . ' (duplicate)';
        $this->save();
    }

    public function save()
    {
        if (!$this->fromDatabase) {
            $s = getDatabase()->prepare(
                'INSERT INTO graphs (name, title, comment, width, height, vert_label, type, options, base, max, min) VALUES (:name, :title, :comment, :width, :height, :vert_label, :type, :options, :base, :max, :min)'
            );
        } else {
            // update
            $s = getDatabase()->prepare(
                'UPDATE graphs SET name = :name, title = :title, comment= :comment, width = :width, height= :height, vert_label = :vert_label, type = :type, options = :options, base = :base, max = :max, min = :min WHERE id = :id'
            );
            $s->bindParam(':id', $this->id);
        }
        $s->bindParam(':name', $this->name);
        $s->bindParam(':title', $this->title);
        $s->bindParam(':comment', $this->comment);
        $s->bindParam(':width', $this->width);
        $s->bindParam(':height', $this->height);
        $s->bindParam(':vert_label', $this->verticallabel);
        $s->bindParam(':type', $this->type);
        $s->bindParam(':options', $this->options);
        $s->bindParam(':base', $this->base);
        $s->bindParam(':max', $this->max);
        $s->bindParam(':min', $this->min);
        $s->execute();
    }
} 
