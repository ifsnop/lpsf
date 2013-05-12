<?php
/*
    Light PHP scrapper framework. Several php utilities to scrap sites.
    Copyright (C) 2013 Diego Torres <diego dot torres at gmail dot com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

define('DIR_HOME', dirname(__FILE__));
#define('DIR_IMAGES', "/var/www/pics");
#define('DIR_THUMBS', "/var/www/3bdp/thmbs");
#define('URL_THUMBS', "/thmbs");
#define('FONTS_HOME', DIR_HOME . "/fonts");

include_once(DIR_HOME . '/config.inc.php');
include_once(DIR_HOME . '/header_account.inc.php');
include_once(DIR_HOME . '/functions.inc.php');
include_once(DIR_HOME . '/functions_str.inc.php');
include_once(DIR_HOME . '/functions_web.inc.php');
include_once(DIR_HOME . '/mod_cache.inc.php');

if (isset($_SERVER['HTTP_HOST'])) {
    define('URL_HOME', "https://" . $_SERVER['HTTP_HOST'] . "/");
    define('URLS_HOME', "https://" . $_SERVER['HTTP_HOST'] . "/");
}

//else
//    define('URL_HOME', "http://devel.vipnet.ggharo.es:1080"); //"http://web01.azul.lan/3bdp");
//define('URLS_HOME', "/"); //"https://web01.azul.lan/3bdp");


// sanity check for mod_action. if it doesn't exist, do action (redirect or refresh).
// if mod_action exists, ensure it is in the available_actions array. returns true if 
// not founded or false if founded.
function gotoHomeIf($mod_action = NULL, $available_actions = array(), $access_level_req = 0, $action = 'redirect') { 
    global $login;
    $jump = false;

    if ($access_level_req > 0) {
	if (!(isset($login) &&
	    isset($login['is_logged']) &&
	    isset($login['access_level']) &&
	    $login['is_logged']=='1' &&
	    $login['access_level']>=$access_level_req) ) {
	    $jump = true;
	}
    }
    if (is_array($available_actions)) {
	if (count($available_actions)==0 || !in_array($mod_action, $available_actions)) {
	    $jump = true;
	}
    } else {
	if ($mod_action != $available_actions) {
	    $jump = true;
	}
    }
    if ($jump) {
        switch ($action) {
	    case 'refresh':
	        header("Refresh: 0; URL=" . URL_HOME . "/a");exit;
	        break;
	    case 'redirect':
		default :
		header("Location: " . URL_HOME . "/a");exit;
		break;
	}
    }

    return true;
}

