<?php
/**
 * DB.php
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


class MyPDO extends PDO {

    /**
     * Quotes a table name or field name
     *
     * @param  string $field the name of the field or table
     *
     * @return string        the quotes string
     */
    public function quoteIdent($field) {
        return "`".str_replace("`","``",$field)."`";
    }
}


/**
 * @return \MyPDO
 */
function getDatabase() {
    if (isset($GLOBALS['netmrg']['__pdoconn'])) {
        return $GLOBALS['netmrg']['__pdoconn'];
    }
    return null;
}

/**
 *
 */
function initDatabaseConnection() {
    if ($GLOBALS["netmrg"]["dbsock"] != "") {
        $dbhost = $GLOBALS["netmrg"]["dbhost"].":".$GLOBALS["netmrg"]["dbsock"];
    }
    elseif ($GLOBALS["netmrg"]["dbport"] > 0) {
        $dbhost = $GLOBALS["netmrg"]["dbhost"].":".$GLOBALS["netmrg"]["dbport"];
    }
    else {
        $dbhost = $GLOBALS["netmrg"]["dbhost"];
    }

    $dsn      = 'mysql:dbname='.$GLOBALS["netmrg"]["dbname"].';host='.$dbhost;
    $user     = $GLOBALS["netmrg"]["dbuser"];
    $password = $GLOBALS["netmrg"]["dbpass"];

    $GLOBALS['netmrg']['__pdoconn'] = new MyPDO($dsn, $user, $password);
}