<?php
/**
 * (c) Marcus Schwarz
 * Date: 15.03.14
 * Time: 14:45
 * Id:   $Id$
 * 
 * Powered by Alpen Yod'l Koffein
 */

function getService($servicename) {
    if (isset($GLOBALS['netmrg']['__'.$servicename])) {
        return $GLOBALS['netmrg']['__'.$servicename];
    }
    return null;
}

function setService($servicename, $value) {
    $GLOBALS['netmrg']['__'.$servicename] = $value;
}