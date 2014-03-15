<?php
/********************************************
* NetMRG Integrator
*
* color_dialog.php
* Color Choosing Dialog
*
* Copyright (C) 2001-2008
*   Brady Alleman <brady@thtech.net>
*   Douglas E. Warner <silfreed@silfreed.net>
*   Kevin Bonner <keb@nivek.ws>
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
* based on Luis Romero's Color Picker
********************************************/

require_once "../include/config.php";

// Source of HTML/JS: http://www.js-examples.com
// Original Author: Luis Romero (luisromero7987@aol.com) - http://www.geocities.com/lr7987
?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Choose Color</title>
<script>
<!--
function showColor(val) {
    window.opener.document.editform.<?php echo $_REQUEST["field"]; ?>.value = val;
    window.close();
}
// -->
</script>
</head>
<body>
<div style="text-align:center;">
    <form name="colorform">
        <map name="colmap">
            <area shape="rect" alt="#00FF00" coords="1,1,7,10" href="javascript:showColor('#00FF00')">
            <area shape="rect" alt="#00FF33" coords="9,1,15,10" href="javascript:showColor('#00FF33')">
            <area shape="rect" alt="#00FF66" coords="17,1,23,10" href="javascript:showColor('#00FF66')">
            <area shape="rect" alt="#00FF99" coords="25,1,31,10" href="javascript:showColor('#00FF99')">
            <area shape="rect" alt="#00FFCC" coords="33,1,39,10" href="javascript:showColor('#00FFCC')">
            <area shape="rect" alt="#00FFFF" coords="41,1,47,10" href="javascript:showColor('#00FFFF')">
            <area shape="rect" alt="#33FF00" coords="49,1,55,10" href="javascript:showColor('#33FF00')">
            <area shape="rect" alt="#33FF33" coords="57,1,63,10" href="javascript:showColor('#33FF33')">
            <area shape="rect" alt="#33FF66" coords="65,1,71,10" href="javascript:showColor('#33FF66')">
            <area shape="rect" alt="#33FF99" coords="73,1,79,10" href="javascript:showColor('#33FF99')">
            <area shape="rect" alt="#33FFCC" coords="81,1,87,10" href="javascript:showColor('#33FFCC')">
            <area shape="rect" alt="#33FFFF" coords="89,1,95,10" href="javascript:showColor('#33FFFF')">
            <area shape="rect" alt="#66FF00" coords="97,1,103,10" href="javascript:showColor('#66FF00')">
            <area shape="rect" alt="#66FF33" coords="105,1,111,10" href="javascript:showColor('#66FF33')">
            <area shape="rect" alt="#66FF66" coords="113,1,119,10" href="javascript:showColor('#66FF66')">
            <area shape="rect" alt="#66FF99" coords="121,1,127,10" href="javascript:showColor('#66FF99')">
            <area shape="rect" alt="#66FFCC" coords="129,1,135,10" href="javascript:showColor('#66FFCC')">
            <area shape="rect" alt="#66FFFF" coords="137,1,143,10" href="javascript:showColor('#66FFFF')">
            <area shape="rect" alt="#99FF00" coords="145,1,151,10" href="javascript:showColor('#99FF00')">
            <area shape="rect" alt="#99FF33" coords="153,1,159,10" href="javascript:showColor('#99FF33')">
            <area shape="rect" alt="#99FF66" coords="161,1,167,10" href="javascript:showColor('#99FF66')">
            <area shape="rect" alt="#99FF99" coords="169,1,175,10" href="javascript:showColor('#99FF99')">
            <area shape="rect" alt="#99FFCC" coords="177,1,183,10" href="javascript:showColor('#99FFCC')">
            <area shape="rect" alt="#99FFFF" coords="185,1,191,10" href="javascript:showColor('#99FFFF')">
            <area shape="rect" alt="#CCFF00" coords="193,1,199,10" href="javascript:showColor('#CCFF00')">
            <area shape="rect" alt="#CCFF33" coords="201,1,207,10" href="javascript:showColor('#CCFF33')">
            <area shape="rect" alt="#CCFF66" coords="209,1,215,10" href="javascript:showColor('#CCFF66')">
            <area shape="rect" alt="#CCFF99" coords="217,1,223,10" href="javascript:showColor('#CCFF99')">
            <area shape="rect" alt="#CCFFCC" coords="225,1,231,10" href="javascript:showColor('#CCFFCC')">
            <area shape="rect" alt="#CCFFFF" coords="233,1,239,10" href="javascript:showColor('#CCFFFF')">
            <area shape="rect" alt="#FFFF00" coords="241,1,247,10" href="javascript:showColor('#FFFF00')">
            <area shape="rect" alt="#FFFF33" coords="249,1,255,10" href="javascript:showColor('#FFFF33')">
            <area shape="rect" alt="#FFFF66" coords="257,1,263,10" href="javascript:showColor('#FFFF66')">
            <area shape="rect" alt="#FFFF99" coords="265,1,271,10" href="javascript:showColor('#FFFF99')">
            <area shape="rect" alt="#FFFFCC" coords="273,1,279,10" href="javascript:showColor('#FFFFCC')">
            <area shape="rect" alt="#FFFFFF" coords="281,1,287,10" href="javascript:showColor('#FFFFFF')">
            <area shape="rect" alt="#00CC00" coords="1,12,7,21" href="javascript:showColor('#00CC00')">
            <area shape="rect" alt="#00CC33" coords="9,12,15,21" href="javascript:showColor('#00CC33')">
            <area shape="rect" alt="#00CC66" coords="17,12,23,21" href="javascript:showColor('#00CC66')">
            <area shape="rect" alt="#00CC99" coords="25,12,31,21" href="javascript:showColor('#00CC99')">
            <area shape="rect" alt="#00CCCC" coords="33,12,39,21" href="javascript:showColor('#00CCCC')">
            <area shape="rect" alt="#00CCFF" coords="41,12,47,21" href="javascript:showColor('#00CCFF')">
            <area shape="rect" alt="#33CC00" coords="49,12,55,21" href="javascript:showColor('#33CC00')">
            <area shape="rect" alt="#33CC33" coords="57,12,63,21" href="javascript:showColor('#33CC33')">
            <area shape="rect" alt="#33CC66" coords="65,12,71,21" href="javascript:showColor('#33CC66')">
            <area shape="rect" alt="#33CC99" coords="73,12,79,21" href="javascript:showColor('#33CC99')">
            <area shape="rect" alt="#33CCCC" coords="81,12,87,21" href="javascript:showColor('#33CCCC')">
            <area shape="rect" alt="#33CCFF" coords="89,12,95,21" href="javascript:showColor('#33CCFF')">
            <area shape="rect" alt="#66CC00" coords="97,12,103,21" href="javascript:showColor('#66CC00')">
            <area shape="rect" alt="#66CC33" coords="105,12,111,21" href="javascript:showColor('#66CC33')">
            <area shape="rect" alt="#66CC66" coords="113,12,119,21" href="javascript:showColor('#66CC66')">
            <area shape="rect" alt="#66CC99" coords="121,12,127,21" href="javascript:showColor('#66CC99')">
            <area shape="rect" alt="#66CCCC" coords="129,12,135,21" href="javascript:showColor('#66CCCC')">
            <area shape="rect" alt="#66CCFF" coords="137,12,143,21" href="javascript:showColor('#66CCFF')">
            <area shape="rect" alt="#99CC00" coords="145,12,151,21" href="javascript:showColor('#99CC00')">
            <area shape="rect" alt="#99CC33" coords="153,12,159,21" href="javascript:showColor('#99CC33')">
            <area shape="rect" alt="#99CC66" coords="161,12,167,21" href="javascript:showColor('#99CC66')">
            <area shape="rect" alt="#99CC99" coords="169,12,175,21" href="javascript:showColor('#99CC99')">
            <area shape="rect" alt="#99CCCC" coords="177,12,183,21" href="javascript:showColor('#99CCCC')">
            <area shape="rect" alt="#99CCFF" coords="185,12,191,21" href="javascript:showColor('#99CCFF')">
            <area shape="rect" alt="#CCCC00" coords="193,12,199,21" href="javascript:showColor('#CCCC00')">
            <area shape="rect" alt="#CCCC33" coords="201,12,207,21" href="javascript:showColor('#CCCC33')">
            <area shape="rect" alt="#CCCC66" coords="209,12,215,21" href="javascript:showColor('#CCCC66')">
            <area shape="rect" alt="#CCCC99" coords="217,12,223,21" href="javascript:showColor('#CCCC99')">
            <area shape="rect" alt="#CCCCCC" coords="225,12,231,21" href="javascript:showColor('#CCCCCC')">
            <area shape="rect" alt="#CCCCFF" coords="233,12,239,21" href="javascript:showColor('#CCCCFF')">
            <area shape="rect" alt="#FFCC00" coords="241,12,247,21" href="javascript:showColor('#FFCC00')">
            <area shape="rect" alt="#FFCC33" coords="249,12,255,21" href="javascript:showColor('#FFCC33')">
            <area shape="rect" alt="#FFCC66" coords="257,12,263,21" href="javascript:showColor('#FFCC66')">
            <area shape="rect" alt="#FFCC99" coords="265,12,271,21" href="javascript:showColor('#FFCC99')">
            <area shape="rect" alt="#FFCCCC" coords="273,12,279,21" href="javascript:showColor('#FFCCCC')">
            <area shape="rect" alt="#FFCCFF" coords="281,12,287,21" href="javascript:showColor('#FFCCFF')">
            <area shape="rect" alt="#009900" coords="1,23,7,32" href="javascript:showColor('#009900')">
            <area shape="rect" alt="#009933" coords="9,23,15,32" href="javascript:showColor('#009933')">
            <area shape="rect" alt="#009966" coords="17,23,23,32" href="javascript:showColor('#009966')">
            <area shape="rect" alt="#009999" coords="25,23,31,32" href="javascript:showColor('#009999')">
            <area shape="rect" alt="#0099CC" coords="33,23,39,32" href="javascript:showColor('#0099CC')">
            <area shape="rect" alt="#0099FF" coords="41,23,47,32" href="javascript:showColor('#0099FF')">
            <area shape="rect" alt="#339900" coords="49,23,55,32" href="javascript:showColor('#339900')">
            <area shape="rect" alt="#339933" coords="57,23,63,32" href="javascript:showColor('#339933')">
            <area shape="rect" alt="#339966" coords="65,23,71,32" href="javascript:showColor('#339966')">
            <area shape="rect" alt="#339999" coords="73,23,79,32" href="javascript:showColor('#339999')">
            <area shape="rect" alt="#3399CC" coords="81,23,87,32" href="javascript:showColor('#3399CC')">
            <area shape="rect" alt="#3399FF" coords="89,23,95,32" href="javascript:showColor('#3399FF')">
            <area shape="rect" alt="#669900" coords="97,23,103,32" href="javascript:showColor('#669900')">
            <area shape="rect" alt="#669933" coords="105,23,111,32" href="javascript:showColor('#669933')">
            <area shape="rect" alt="#669966" coords="113,23,119,32" href="javascript:showColor('#669966')">
            <area shape="rect" alt="#669999" coords="121,23,127,32" href="javascript:showColor('#669999')">
            <area shape="rect" alt="#6699CC" coords="129,23,135,32" href="javascript:showColor('#6699CC')">
            <area shape="rect" alt="#6699FF" coords="137,23,143,32" href="javascript:showColor('#6699FF')">
            <area shape="rect" alt="#999900" coords="145,23,151,32" href="javascript:showColor('#999900')">
            <area shape="rect" alt="#999933" coords="153,23,159,32" href="javascript:showColor('#999933')">
            <area shape="rect" alt="#999966" coords="161,23,167,32" href="javascript:showColor('#999966')">
            <area shape="rect" alt="#999999" coords="169,23,175,32" href="javascript:showColor('#999999')">
            <area shape="rect" alt="#9999CC" coords="177,23,183,32" href="javascript:showColor('#9999CC')">
            <area shape="rect" alt="#9999FF" coords="185,23,191,32" href="javascript:showColor('#9999FF')">
            <area shape="rect" alt="#CC9900" coords="193,23,199,32" href="javascript:showColor('#CC9900')">
            <area shape="rect" alt="#CC9933" coords="201,23,207,32" href="javascript:showColor('#CC9933')">
            <area shape="rect" alt="#CC9966" coords="209,23,215,32" href="javascript:showColor('#CC9966')">
            <area shape="rect" alt="#CC9999" coords="217,23,223,32" href="javascript:showColor('#CC9999')">
            <area shape="rect" alt="#CC99CC" coords="225,23,231,32" href="javascript:showColor('#CC99CC')">
            <area shape="rect" alt="#CC99FF" coords="233,23,239,32" href="javascript:showColor('#CC99FF')">
            <area shape="rect" alt="#FF9900" coords="241,23,247,32" href="javascript:showColor('#FF9900')">
            <area shape="rect" alt="#FF9933" coords="249,23,255,32" href="javascript:showColor('#FF9933')">
            <area shape="rect" alt="#FF9966" coords="257,23,263,32" href="javascript:showColor('#FF9966')">
            <area shape="rect" alt="#FF9999" coords="265,23,271,32" href="javascript:showColor('#FF9999')">
            <area shape="rect" alt="#FF99CC" coords="273,23,279,32" href="javascript:showColor('#FF99CC')">
            <area shape="rect" alt="#FF99FF" coords="281,23,287,32" href="javascript:showColor('#FF99FF')">
            <area shape="rect" alt="#006600" coords="1,34,7,43" href="javascript:showColor('#006600')">
            <area shape="rect" alt="#006633" coords="9,34,15,43" href="javascript:showColor('#006633')">
            <area shape="rect" alt="#006666" coords="17,34,23,43" href="javascript:showColor('#006666')">
            <area shape="rect" alt="#006699" coords="25,34,31,43" href="javascript:showColor('#006699')">
            <area shape="rect" alt="#0066CC" coords="33,34,39,43" href="javascript:showColor('#0066CC')">
            <area shape="rect" alt="#0066FF" coords="41,34,47,43" href="javascript:showColor('#0066FF')">
            <area shape="rect" alt="#336600" coords="49,34,55,43" href="javascript:showColor('#336600')">
            <area shape="rect" alt="#336633" coords="57,34,63,43" href="javascript:showColor('#336633')">
            <area shape="rect" alt="#336666" coords="65,34,71,43" href="javascript:showColor('#336666')">
            <area shape="rect" alt="#336699" coords="73,34,79,43" href="javascript:showColor('#336699')">
            <area shape="rect" alt="#3366CC" coords="81,34,87,43" href="javascript:showColor('#3366CC')">
            <area shape="rect" alt="#3366FF" coords="89,34,95,43" href="javascript:showColor('#3366FF')">
            <area shape="rect" alt="#666600" coords="97,34,103,43" href="javascript:showColor('#666600')">
            <area shape="rect" alt="#666633" coords="105,34,111,43" href="javascript:showColor('#666633')">
            <area shape="rect" alt="#666666" coords="113,34,119,43" href="javascript:showColor('#666666')">
            <area shape="rect" alt="#666699" coords="121,34,127,43" href="javascript:showColor('#666699')">
            <area shape="rect" alt="#6666CC" coords="129,34,135,43" href="javascript:showColor('#6666CC')">
            <area shape="rect" alt="#6666FF" coords="137,34,143,43" href="javascript:showColor('#6666FF')">
            <area shape="rect" alt="#996600" coords="145,34,151,43" href="javascript:showColor('#996600')">
            <area shape="rect" alt="#996633" coords="153,34,159,43" href="javascript:showColor('#996633')">
            <area shape="rect" alt="#996666" coords="161,34,167,43" href="javascript:showColor('#996666')">
            <area shape="rect" alt="#996699" coords="169,34,175,43" href="javascript:showColor('#996699')">
            <area shape="rect" alt="#9966CC" coords="177,34,183,43" href="javascript:showColor('#9966CC')">
            <area shape="rect" alt="#9966FF" coords="185,34,191,43" href="javascript:showColor('#9966FF')">
            <area shape="rect" alt="#CC6600" coords="193,34,199,43" href="javascript:showColor('#CC6600')">
            <area shape="rect" alt="#CC6633" coords="201,34,207,43" href="javascript:showColor('#CC6633')">
            <area shape="rect" alt="#CC6666" coords="209,34,215,43" href="javascript:showColor('#CC6666')">
            <area shape="rect" alt="#CC6699" coords="217,34,223,43" href="javascript:showColor('#CC6699')">
            <area shape="rect" alt="#CC66CC" coords="225,34,231,43" href="javascript:showColor('#CC66CC')">
            <area shape="rect" alt="#CC66FF" coords="233,34,239,43" href="javascript:showColor('#CC66FF')">
            <area shape="rect" alt="#FF6600" coords="241,34,247,43" href="javascript:showColor('#FF6600')">
            <area shape="rect" alt="#FF6633" coords="249,34,255,43" href="javascript:showColor('#FF6633')">
            <area shape="rect" alt="#FF6666" coords="257,34,263,43" href="javascript:showColor('#FF6666')">
            <area shape="rect" alt="#FF6699" coords="265,34,271,43" href="javascript:showColor('#FF6699')">
            <area shape="rect" alt="#FF66CC" coords="273,34,279,43" href="javascript:showColor('#FF66CC')">
            <area shape="rect" alt="#FF66FF" coords="281,34,287,43" href="javascript:showColor('#FF66FF')">
            <area shape="rect" alt="#003300" coords="1,45,7,54" href="javascript:showColor('#003300')">
            <area shape="rect" alt="#003333" coords="9,45,15,54" href="javascript:showColor('#003333')">
            <area shape="rect" alt="#003366" coords="17,45,23,54" href="javascript:showColor('#003366')">
            <area shape="rect" alt="#003399" coords="25,45,31,54" href="javascript:showColor('#003399')">
            <area shape="rect" alt="#0033CC" coords="33,45,39,54" href="javascript:showColor('#0033CC')">
            <area shape="rect" alt="#0033FF" coords="41,45,47,54" href="javascript:showColor('#0033FF')">
            <area shape="rect" alt="#333300" coords="49,45,55,54" href="javascript:showColor('#333300')">
            <area shape="rect" alt="#333333" coords="57,45,63,54" href="javascript:showColor('#333333')">
            <area shape="rect" alt="#333366" coords="65,45,71,54" href="javascript:showColor('#333366')">
            <area shape="rect" alt="#333399" coords="73,45,79,54" href="javascript:showColor('#333399')">
            <area shape="rect" alt="#3333CC" coords="81,45,87,54" href="javascript:showColor('#3333CC')">
            <area shape="rect" alt="#3333FF" coords="89,45,95,54" href="javascript:showColor('#3333FF')">
            <area shape="rect" alt="#663300" coords="97,45,103,54" href="javascript:showColor('#663300')">
            <area shape="rect" alt="#663333" coords="105,45,111,54" href="javascript:showColor('#663333')">
            <area shape="rect" alt="#663366" coords="113,45,119,54" href="javascript:showColor('#663366')">
            <area shape="rect" alt="#663399" coords="121,45,127,54" href="javascript:showColor('#663399')">
            <area shape="rect" alt="#6633CC" coords="129,45,135,54" href="javascript:showColor('#6633CC')">
            <area shape="rect" alt="#6633FF" coords="137,45,143,54" href="javascript:showColor('#6633FF')">
            <area shape="rect" alt="#993300" coords="145,45,151,54" href="javascript:showColor('#993300')">
            <area shape="rect" alt="#993333" coords="153,45,159,54" href="javascript:showColor('#993333')">
            <area shape="rect" alt="#993366" coords="161,45,167,54" href="javascript:showColor('#993366')">
            <area shape="rect" alt="#993399" coords="169,45,175,54" href="javascript:showColor('#993399')">
            <area shape="rect" alt="#9933CC" coords="177,45,183,54" href="javascript:showColor('#9933CC')">
            <area shape="rect" alt="#9933FF" coords="185,45,191,54" href="javascript:showColor('#9933FF')">
            <area shape="rect" alt="#CC3300" coords="193,45,199,54" href="javascript:showColor('#CC3300')">
            <area shape="rect" alt="#CC3333" coords="201,45,207,54" href="javascript:showColor('#CC3333')">
            <area shape="rect" alt="#CC3366" coords="209,45,215,54" href="javascript:showColor('#CC3366')">
            <area shape="rect" alt="#CC3399" coords="217,45,223,54" href="javascript:showColor('#CC3399')">
            <area shape="rect" alt="#CC33CC" coords="225,45,231,54" href="javascript:showColor('#CC33CC')">
            <area shape="rect" alt="#CC33FF" coords="233,45,239,54" href="javascript:showColor('#CC33FF')">
            <area shape="rect" alt="#FF3300" coords="241,45,247,54" href="javascript:showColor('#FF3300')">
            <area shape="rect" alt="#FF3333" coords="249,45,255,54" href="javascript:showColor('#FF3333')">
            <area shape="rect" alt="#FF3366" coords="257,45,263,54" href="javascript:showColor('#FF3366')">
            <area shape="rect" alt="#FF3399" coords="265,45,271,54" href="javascript:showColor('#FF3399')">
            <area shape="rect" alt="#FF33CC" coords="273,45,279,54" href="javascript:showColor('#FF33CC')">
            <area shape="rect" alt="#FF33FF" coords="281,45,287,54" href="javascript:showColor('#FF33FF')">
            <area shape="rect" alt="#000000" coords="1,56,7,65" href="javascript:showColor('#000000')">
            <area shape="rect" alt="#000033" coords="9,56,15,65" href="javascript:showColor('#000033')">
            <area shape="rect" alt="#000066" coords="17,56,23,65" href="javascript:showColor('#000066')">
            <area shape="rect" alt="#000099" coords="25,56,31,65" href="javascript:showColor('#000099')">
            <area shape="rect" alt="#0000CC" coords="33,56,39,65" href="javascript:showColor('#0000CC')">
            <area shape="rect" alt="#0000FF" coords="41,56,47,65" href="javascript:showColor('#0000FF')">
            <area shape="rect" alt="#330000" coords="49,56,55,65" href="javascript:showColor('#330000')">
            <area shape="rect" alt="#330033" coords="57,56,63,65" href="javascript:showColor('#330033')">
            <area shape="rect" alt="#330066" coords="65,56,71,65" href="javascript:showColor('#330066')">
            <area shape="rect" alt="#330099" coords="73,56,79,65" href="javascript:showColor('#330099')">
            <area shape="rect" alt="#3300CC" coords="81,56,87,65" href="javascript:showColor('#3300CC')">
            <area shape="rect" alt="#3300FF" coords="89,56,95,65" href="javascript:showColor('#3300FF')">
            <area shape="rect" alt="#660000" coords="97,56,103,65" href="javascript:showColor('#660000')">
            <area shape="rect" alt="#660033" coords="105,56,111,65" href="javascript:showColor('#660033')">
            <area shape="rect" alt="#660066" coords="113,56,119,65" href="javascript:showColor('#660066')">
            <area shape="rect" alt="#660099" coords="121,56,127,65" href="javascript:showColor('#660099')">
            <area shape="rect" alt="#6600CC" coords="129,56,135,65" href="javascript:showColor('#6600CC')">
            <area shape="rect" alt="#6600FF" coords="137,56,143,65" href="javascript:showColor('#6600FF')">
            <area shape="rect" alt="#990000" coords="145,56,151,65" href="javascript:showColor('#990000')">
            <area shape="rect" alt="#990033" coords="153,56,159,65" href="javascript:showColor('#990033')">
            <area shape="rect" alt="#990066" coords="161,56,167,65" href="javascript:showColor('#990066')">
            <area shape="rect" alt="#990099" coords="169,56,175,65" href="javascript:showColor('#990099')">
            <area shape="rect" alt="#9900CC" coords="177,56,183,65" href="javascript:showColor('#9900CC')">
            <area shape="rect" alt="#9900FF" coords="185,56,191,65" href="javascript:showColor('#9900FF')">
            <area shape="rect" alt="#CC0000" coords="193,56,199,65" href="javascript:showColor('#CC0000')">
            <area shape="rect" alt="#CC0033" coords="201,56,207,65" href="javascript:showColor('#CC0033')">
            <area shape="rect" alt="#CC0066" coords="209,56,215,65" href="javascript:showColor('#CC0066')">
            <area shape="rect" alt="#CC0099" coords="217,56,223,65" href="javascript:showColor('#CC0099')">
            <area shape="rect" alt="#CC00CC" coords="225,56,231,65" href="javascript:showColor('#CC00CC')">
            <area shape="rect" alt="#CC00FF" coords="233,56,239,65" href="javascript:showColor('#CC00FF')">
            <area shape="rect" alt="#FF0000" coords="241,56,247,65" href="javascript:showColor('#FF0000')">
            <area shape="rect" alt="#FF0033" coords="249,56,255,65" href="javascript:showColor('#FF0033')">
            <area shape="rect" alt="#FF0066" coords="257,56,263,65" href="javascript:showColor('#FF0066')">
            <area shape="rect" alt="#FF0099" coords="265,56,271,65" href="javascript:showColor('#FF0099')">
            <area shape="rect" alt="#FF00CC" coords="273,56,279,65" href="javascript:showColor('#FF00CC')">
            <area shape="rect" alt="#FF00FF" coords="281,56,287,65" href="javascript:showColor('#FF00FF')">
        </map>
    </form>
    <img usemap="#colmap" src="img/colortable.gif" alt="colortable" style="border:none;width:289px;height:67px;">
</div>
</body>
</html>

