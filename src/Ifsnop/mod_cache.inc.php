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

$cache_content = "";
define("CACHE_TIMEOUT", 86400*365);
if (!isset($errors)) $errors = array();

function isCacheReady($cache_path = "./cache/", $cache_prefix = "", $cache_hash = "", $cache_timeout = CACHE_TIMEOUT, &$cache_content) {
    global $lpsf;
    
    $cache_content = "";
    if (substr($cache_path,-1) != "/") {
	$cache_path .= "/";
    }
    $cache_path_cmp = $cache_path . substr($cache_hash,0,1) . "/";
    $cache_file = $cache_path_cmp . $cache_prefix . "_" . $cache_hash;
    
    if (!is_dir($cache_path_cmp)) {
	if (mkdir($cache_path_cmp, 0755, true) === false) {
	    lpsf_log(Bnumber(), "$cache_file => status: *error* can't create path (mkdir error)");
	    return false;
	}
    }
    if (file_exists($cache_file) && is_file($cache_file)) {
	$cache_diff = time() - filemtime($cache_file);
	if ( $cache_diff <= $cache_timeout ) {
	    if ( ($cache_content = file_get_contents($cache_file)) === false ) {
		lpsf_log(Bnumber(), "$cache_file => status: *miss* invalidated cache (file_get_contents error)");
		return false;
	    } else {
		$cache_timestamp = date ("Y/m/d H:i:s T", filemtime($cache_file));
		lpsf_log(Bnumber(),"$cache_file => status: *hit* [$cache_timestamp]");
		$cache_content = unserialize($cache_content);
		return true;
	    }	
//	    $cache_status = "$cache_file => status: <strong>forced miss</strong><br />";
//	    $cache_ready = '0';
	} else {
	    // use delete cache
	    if (unlink($cache_file)) {
		lpsf_log(Bnumber(), "$cache_file => status: *force_delete*");
	    }
	}
    }
    lpsf_log(Bnumber(), "$cache_file => status: *miss*");
    return false;
}

function fillCache($cache_path = "./cache/", $cache_prefix = "", $cache_hash = "", $cache_content) {
    global $errors;

    if (substr($cache_path,-1) != "/") {
	$cache_path .= "/";
    }
    $cache_path_cmp = $cache_path . substr($cache_hash,0,1) . "/";
    $cache_file = $cache_path_cmp . $cache_prefix . "_" . $cache_hash;

    if (!is_dir($cache_path_cmp)) {
	if (mkdir($cache_path_cmp, 0755, true) === false) {
	    lpsf_log(Bnumber(), "$cache_file => status: *error* can't create path (mkdir error)");
	    return false;
	}
    }

    //clearstatcache();
    if (is_file($cache_file)) 
	unlink($cache_file);
    if ( ($fp = fopen($cache_file, "w")) !== false ) {
	if ( fwrite($fp, serialize($cache_content)) === false ) {
	    fclose($fp);
	    unlink($cache_file);
	    lpsf_log(Bnumber(), "$cache_file => status: *error* can't write cache file (fwrite error)");
	    return false;
	}
	fclose($fp);
    }
    return true;
}

