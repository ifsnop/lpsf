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

include_once(dirname(__FILE__) . '/main.inc.php');
include_once(DIR_HOME . '/header_account.inc.php');
include_once(DIR_HOME . '/functions.inc.php');
include_once(DIR_HOME . '/external/forceutf8/src/ForceUTF8/Encoding.php');

define('BIN_PATH', "/usr/bin");

function get_user_agent() {
    $user_agent_array = array(
    "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0",
    "Mozilla/5.0 (X11; Linux x86_64; rv:16.0) Gecko/20100101 Firefox/16.0 Iceweasel/16.0.2",
    "Mozilla/5.0 (Windows; U; Windows NT 5.1; es-ES; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6",
    "Mozilla/5.0 (Windows; U; Windows NT 5.1; es-ES; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13",
    "Mozilla/5.0 (Windows; U; Windows NT 5.2; es-ES; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 (.NET CLR 3.5.30729)",
    "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)",
    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)",
    "Mozilla/5.0 (Macintosh; U; Intel Mac OS X; es) AppleWebKit/522.11 (KHTML, like Gecko) Safari/3.0.2",
    "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.4; es-ES; rv:1.9b5) Gecko/2008032619 Firefox/3.0b5",
    "Opera/9.00 (Windows NT 5.1; U; es)",
    "Opera/9.60 (Windows NT 5.1; U; es) Presto/2.1.1",
    "Opera/5.12 (Windows 98; U) [es]");
    //$user_agent = $user_agent_array[rand(0,count($user_agent_array)-1)];
    $user_agent = $user_agent_array[0];
    
    return $user_agent;
}

/*    
    case "ISO88591":
	$cmd = BIN_PATH . "/iconv -c -f ISO-8859-1 -t UTF-8 \"$return_file\" > \"$return_file2\" 2> /dev/null";
	$ret = system($cmd, $retCode);
	//print $cmd . "\n";
	unlink($return_file);
	rename($return_file2, $return_file);
	break;
    case "UTF8":
	break;
    default:
    clearstatcache();

*/
/*
    if ($strip_crlf) {
	$h = fopen($return_file, "rb");
	$file_content = fread($h, filesize($return_file));
	str_replace("\r\n", "\n", $file_content);
	fclose($h);
	$h = fopen($return_file2, "wb");
	fwrite($h, $file_content);
	fclose($h);
	if (is_file($return_file)) { unlink($return_file); }
	rename($return_file2, $return_file);
    }
*/

function br2nl($string) {
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}
function is_good_post($str) {
    if (!is_null($str) && str_word_count_utf8($str)>=15) {
	return true;
    }
}
function clean_web_input($str) {
    return str_replace("\r\n", "\n", trim(strip_tags(html_entity_decode(mb_ereg_replace("&nbsp;", " ", br2nl($str)), ENT_COMPAT, "UTF-8"))));
}

function links_web_input($str) {
    $ret=$res="";$i=$k=0;
    if (!is_array($str)) { $str = array($str); }
    while ( ($rc = extract_field_ext2($str, $i, $k, "<a href=", "\"", "\"", "palopero", $res, false,false)) !== FALSE) {
	$ret .= $res . "\n";
    }
    return $ret;
}


function to7bit($text,$from_enc) {
    $text = mb_convert_encoding($text,'HTML-ENTITIES',$from_enc);
    $text = preg_replace(
	array('/&szlig;/','/&(..)lig;/',
	'/&([aouAOU])uml;/','/&(.)[^;]*;/'),
	array('ss',"$1","$1".'e',"$1"),
	$text);
    $text = iconv("UTF-8", "ASCII//TRANSLIT", $text);
    return $text;
}

function stripNonUTF8($str) {
    return iconv("UTF-8", "UTF-8//TRANSLIT//IGNORE", $str);
}

function parseUrl($url) {
    $parseUrl = parse_url(trim($url));
    if ($parseUrl===FALSE) return FALSE;
    if ($parseUrl['host']=="") {
        $parseUrl['host'] = array_shift(explode('/', $parseUrl['path'], 2));
	list($nada, $parseUrl['path']) = (explode('/', $parseUrl['path'], 2));
    	$parseUrl['path'] = '/' . $parseUrl['path'];
        $parseUrl['scheme'] = "http";
    }
    return $parseUrl;
}

function downloadContentMemory($url, &$content, $strip_crlf = false, $split_crlf = false, $data = array(), $cache = true, $debug = false, &$from_cache) {
    global $lpsf;

    if ($cache) {
	$cache_content = "";
	$cache_path = "./cache/";
	$cache_prefix = "curl";
	$cache_hash = hash("sha256", $url . serialize($data) . $strip_crlf . $split_crlf);
	$cache_hash = str_pad(dec2string(string2dec($cache_hash, 16), 62), 43, '0', STR_PAD_LEFT);
	if (($from_cache = isCacheReady($cache_path, $cache_prefix, $cache_hash, 86400*365, $content)) === TRUE) {
	    return true;
	}
    }
    $host = parseUrl($url);
    $ua = get_user_agent($host['host']);
    $options = array(
        CURLOPT_RETURNTRANSFER => true,         // return web page
        CURLOPT_HEADER         => false,        // don't return headers
        CURLOPT_FOLLOWLOCATION => true,         // follow redirects
	CURLOPT_ENCODING       => "gzip,deflate",	// handle all encodings
        CURLOPT_USERAGENT      => $ua,		// who am i
        CURLOPT_AUTOREFERER    => true,         // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect
        CURLOPT_TIMEOUT        => 120,          // timeout on response
        CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects
        CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
        CURLOPT_SSL_VERIFYPEER => false,        //
        CURLOPT_VERBOSE        => ($debug?1:0), //
    );
    if (isset($data['httpheader'])) {
	$options[CURLOPT_HTTPHEADER] = $data['httpheader'];
    }
    if (isset($data['poststring'])) {
	$options[CURLOPT_POST] = substr_count($data['poststring'], "&") + 1; // i am sending post data
    	$options[CURLOPT_POSTFIELDS] = $data['poststring']; // this are my post vars
    }
    if (getenv('http_proxy')!="") {
	$options[CURLOPT_PROXY] = getenv('http_proxy');
	// $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
    }
    $ch      = curl_init($url);
    curl_setopt_array($ch,$options);
    $content = curl_exec($ch);
    $err     = curl_errno($ch);
    $errmsg  = curl_error($ch) ;
    $header  = curl_getinfo($ch);
    curl_close($ch);

    //$header['errno']   = $err; $header['errmsg']  = $errmsg; $header['content'] = $content;
    //$content = iconv($encoding, "UTF-8//TRANSLIT", $content);

    //$content = ForceUTF8\Encoding::toUTF8($content);

    if ($split_crlf) { // result becomes array
        $content = preg_split("/[\r\n|\n\r|\n]/", $content, NULL, PREG_SPLIT_NO_EMPTY);
    } 
    if ($strip_crlf) {
        $content = str_replace(array("\r\n", "\n\r", "\n"), array("","",""), $content);
    }
    if (!is_array($content)) $content = array($content);

    if ($err==0) {
        if ($cache) {
	    fillCache($cache_path, $cache_prefix, $cache_hash, $content);
	}
    }
    
    return true;
} 

