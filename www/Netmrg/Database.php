<?php
/**
 * Database.php
 *
 * Functions used for database access of NetMRG
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

use Netmrg\Exception\DatabaseException;

class Database extends \PDO
{

    private $dbversion = null;

    public function __construct($host, $dbname, $user, $password)
    {

        $dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . ';charset=UTF8';

        try {
            parent::__construct($dsn, $user, $password);
        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage());
        }

        $GLOBALS['netmrg']['__pdoconn'] = $this; //todo active while rewriting the whole thing
        return $this;
    }


    /**
     * Quotes a table name or field name
     *
     * @param  string $field the name of the field or table
     *
     * @return string        the quotes string
     */
    public function quoteIdent($field)
    {
        return "`" . str_replace("`", "``", $field) . "`";
    }

    public function getDBVersion()
    {
        if (empty($this->dbversion)) {
            $this->dbversion = $this->query('SELECT version FROM versioninfo WHERE module = "Main"')->fetchColumn();
        }
        return $this->dbversion;
    }
}
