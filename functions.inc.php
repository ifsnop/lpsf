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

function parseGetVar($var, $default = NULL, $strip_tags = NULL) { 
// returns a GET variable clean, without slashes and/or stripped
    if (isset($_GET[$var])) {
	if (isset($strip_tags)) {
	    $res = strip_tags($_GET[$var]); 
	} else  {
	    $res = $_GET[$var];
	}
	if (get_magic_quotes_gpc()) {
	    return stripslashes($res); //$_GET[$var]);
	} else {
	    return $res; //$_GET[$var];
	}
    } else {
	return $default;
    }
}

function parsePostVar($var, $default = NULL, $strip_tags = NULL) { 
// returns a POST variable clean, without slashes and/or stripped
    if (isset($_POST[$var])) {
	if (isset($strip_tags)) {
	    $res = strip_tags($_POST[$var]); 
	} else  {
	    $res = $_POST[$var];
	}
	if (get_magic_quotes_gpc()) {
	    return stripslashes($res);
	} else {
	    return $res; 
	}
    } else {
	return $default;
    }
}

function quoteSmart($value = NULL, $db = NULL) { 
// quotes a string safely (ensures that it isn't double quoted)
    if (!isset($value)) return "''";
    if( is_array($value) ) { 
	return array_map("quoteSmart", $value, $db); // esto no funciona, porque array map no soporta 3 parametros!!
    } else { 
	$value = strip_tags($value);
	
	// stripslashes
	if (get_magic_quotes_gpc()) {
	    $value = stripslashes($value);
	}
	// Quote if not a number or a numeric string
	if (strlen($value)==0) {
	    $value = "''"; // better to be empty!
	} else {
	    $value = "'" . mysql_real_escape_string($value, $db) . "'";
	}
	/*if (!is_numeric($value) || $value[0] == '0' ) {
	    $value = "'" . mysql_real_escape_string($value, $db) . "'";
	}*/
	return $value;
    }
}

function BUG($error_number, $error_string, $db = NULL) {
    $ret_text = date("[Y-m-d H:i:s (T)]") . "${error_number}>";
    $ret_html = "<hr />BUG#${error_number}<br />\n";
    
    if (!is_null($db)) {
	$ret_text .= "ERROR#" . mysql_errno($db) . ":" . mysql_error($db) . "\n>${error_string}<\n";
	$ret_html .= "ERROR#" . mysql_errno($db) . ":" . mysql_error($db) . "<br />\n&gt;${error_string}&lt;<br />\n";
    } else {
	$ret_text .= "${error_string}\n";
	$ret_html .= "ERROR : ${error_string}<br />\n";
    }
//    error_log(hexdump($ret_text,false,false,true), 3, DIR_HOME . "/php-error.log");
    error_log($ret_text, 3, DIR_HOME . "/php-error.log");

    return $ret_html;
}

function BUG2($error_number, $error_string, $dbi = NULL) {
    $ret_text = date("[Y-m-d H:i:s (T)]") . "${error_number}>";
    $ret_html = "<hr />BUG2#${error_number}<br />\n";
    
    if (!is_null($dbi)) {
	$ret_text .= "ERROR#" . $dbi->error . ":" . "\n>${error_string}<\n";
	$ret_html .= "ERROR#" . $dbi->error . ":" . "<br />\n&gt;${error_string}&lt;<br />\n";
    } else {
	$ret_text .= "${error_string}\n";
	$ret_html .= "ERROR : ${error_string}<br />\n";
    }
//    error_log(hexdump($ret_text,false,false,true), 3, DIR_HOME . "/php-error.log");
    error_log($ret_text, 3, DIR_HOME . "/php-error.log");

    return $ret_html;
}

//example
//server.HTTP_X_FORWARDED_FOR=172.88.1.180, 194.224.177.198, 194.224.177.198
//server.REMOTE_ADDR=194.224.177.206
//apache log: 194.224.177.206
//$list_ip[] = 194.224.177.206 172.88.1.180 194.224.177.198

function getRealIP() {
    // gives a list with all ips from which the connection is made.
    // first ip in list will be the latest ip the connection crossed.
    // pc ip from the original client will be the last (can be private address)
    $list_ip[0] = ( !empty($_SERVER['REMOTE_ADDR']) ) ?
            $_SERVER['REMOTE_ADDR'] : ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
            $_ENV['REMOTE_ADDR'] : "unknown" );

    if( (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) && ($_SERVER['HTTP_X_FORWARDED_FOR'] != '') ) {
        $entries = explode('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $i=1;
	$entries = array_reverse($entries); //entries in http_x_forwarded_for are reversed ordered (older first)
	foreach ($entries as $entry) {
	    $entry = trim($entry);
            if ( preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $ip_list) ) {
            // http://www.faqs.org/rfcs/rfc1918.html
                $private_ip = array('/^0\./', '/^127\.0\.0\.1/',
                    '/^192\.168\..*/', '/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
                    '/^10\..*/', '/unknown/');
                $found_ip = preg_replace($private_ip, $list_ip[0], $entry);
                if ($list_ip[$i-1] != $found_ip) {
                    $list_ip[$i] = $found_ip;
                    $i++;
                }
            }
        }
    }
//    $list_ip = array_unique($list_ip); // beware, it sorts the array first
    return $list_ip;
}

function printRealIP() {
    $list_ip = getRealIP();
    $listado = "";
    for($j=0;$j<count($list_ip);$j++) {
	$listado .= $list_ip[$j] . " ";
    }
    unset($list_ip);
    $listado = rtrim($listado);
    return $listado;
}

function check_email_address($email) {
    // First, we check that there's one @ symbol, and that the lengths are right
    if (!preg_match("/^[^@]{1,64}@[^@]{1,255}$/", $email)) {
	// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
//	print "EHO 1<br>";
	return false;
    }
    // Split it into sections to make life easier
    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);
    for ($i = 0; $i < sizeof($local_array); $i++) {
	if (!preg_match("/^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/", $local_array[$i])) {
//	print "EHO 2 ($i)<br>";
	    return false;
	}
    } 
    if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
	$domain_array = explode(".", $email_array[1]);
	if (sizeof($domain_array) < 2) {
//	print "EHO 3<br>";
	    return false; // Not enough parts to domain
	}
	for ($i = 0; $i < sizeof($domain_array); $i++) {
	    if (!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/", $domain_array[$i])) {
//		print "EHO 4 ($i)<br>";
		return false;
	    }
	}
    }
    return true;
}

function generateSelect($name, $id, $class, $options, $selected, $extras = "") {
    $control = "<select name='$name' id='$id' class='$class' $extras>";
    foreach($options as $value=>$text) {
	if (!strcmp($selected,$value)) {
	    $control .= "<option value='$value' selected='selected'>$text</option>";
	} else {
	    $control .= "<option value='$value'>$text</option>";
	}
    }

    $control .= "</select>";
    return $control;
}
// appends xor of string to string
function generateXOR($string) {
    global $login_password_salt;
    $res=0;
    $crc_string = sprintf("%08X", crc32($string . $login_password_salt . microtime(true)));
    for($i=0;$i<strlen($crc_string);$i++) {
	$c = hexdec($crc_string[$i]);
	$res ^= $c;
    }
    return $crc_string . strtoupper(dechex($res));
}
function generateXOR2($string) {
    global $login_password_salt;
    $res=0;
    $crc_string = hash("crc32", ($string . $login_password_salt . microtime(true)));
    $crc_string = sprintf("%012u", string2dec($crc_string, 16));
    print "0>" . $crc_string . "\n";
    
    
    for($i=0;$i<strlen($crc_string);$i++) {
	$c = $crc_string[$i];
	$res ^= $c;
    }
    print "1>" . $crc_string . "+" . $res . "\n";
    print "2>" . dec2string($crc_string,62) . "+" . dec2string($res,62) . "\n";
    return dec2string($crc_string,62) . dec2string($res,62);
}

// xor is the last char
function validateXOR($string) {
//    if (ereg("([0-9A-F]{9})", $string)===FALSE) //deprecated
    if (preg_match("/[0-9A-F]{9}/", $string) == 0)
	return false;

    $res=0;
    for($i=0;$i<strlen($string)-1;$i++) {
	$c = hexdec($string[$i]);
	$res ^= $c;
    }
    $res = strtoupper(dechex($res));

    if ($res == $string[strlen($string)-1]) return true; else return false;
}
function generatePassword ($length = 8) {
    $password = "";
    // define possible characters
    $possible = "0123456789bcdfghjkmnpqrstvwxyz";
    $i = 0;
    while ($i < $length) {
        $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
        //we don't want this character if it's already in the password NOT!
        //if (!strstr($password, $char)) {
            $password .= $char;
            $i++;
        //}
    }
    return $password;
}
function formatText($text) {
// converts [n] [/n] (custom tags) to <b></b> (html tags), to use in the comment box
    if ($text=="") return "";
    rtrim($text);

    $custom_tags = array("[n]", "[/n]", "[i]", "[/i]", "[s]", "[/s]", "\n");
    $html_tags  = array("<b>", "</b>", "<i>", "</i>", "<u>", "</u>", "<br />");
    
    return str_replace($custom_tags, $html_tags, $text);
}
function insertJS($string) {
    $ret = "<script type='text/javascript' charset='utf-8'>\n//<![CDATA[\n";
    $ret .= $string;
    $ret .= "\n//]]>\n</script>\n";
return $ret;
}

function generateSparseArray($url, $rows, $pageNow = 1, $nbTotalPage = 1,
    $showAll = 10, $sliceStart = 5, $sliceEnd = 5, $percent = 20,
    $range = 10, $prompt = '') {
    
    $nbTotalPage = ceil($nbTotalPage/$rows); //rowsPerPage
    $gotopage = $prompt . 
    '<script type="text/javascript">
	function jumpURL(o, url) {
	    var p=o.options[o.selectedIndex].value;
	    window.location=url+p;
	}
    </script>' .
    "<select name='p' onchange='jumpURL(this, \"$url\");'>\n";
    if ($nbTotalPage <= $showAll) {
	$pages = range(0, $nbTotalPage - 1);
    } else {
        $pages = array(); // Always show first X pages
        for ($i = 0; $i < $sliceStart; $i++) {
    	    $pages[] = $i;
	}
	// Always show last X pages
	for ($i = $nbTotalPage - $sliceEnd; $i < $nbTotalPage; $i++) {
    	    $pages[] = $i;
	}
	// garvin: Based on the number of results we add the specified
	// $percent percentate to each page number,
	// so that we have a representing page number every now and then to
	// immideately jump to specific pages.
	// As soon as we get near our currently chosen page ($pageNow -
	// $range), every page number will be
	// shown.
	$i = $sliceStart;
	$x = $nbTotalPage - $sliceEnd;
	$met_boundary = false;
	while ($i <= $x) {
//	    BUG("XXX", "pagenow($pageNow) range($range) slicestart($sliceStart) sliceend($sliceEnd) i($i) x($x)\n");
    	    if ($i >= ($pageNow - $range) && $i <= ($pageNow + $range)) {
    		// If our pageselector comes near the current page, we use 1
    		// counter increments
    		$i++;
    		$met_boundary = true;
    	    } else {
		// We add the percentate increment to our current page to
		// hop to the next one in range
		$i = $i + ceil($nbTotalPage / $percent); // el original hacer floor, pero a veces la division es 0.x, y no se avanza
//		BUG("YYY","nbtotalpage($nbTotalPage) percent($percent) floor(" . floor($nbTotalPage/$percent) . ")\n");
        	// Make sure that we do not cross our boundaries.
        	if ($i > ($pageNow - $range) && !$met_boundary) {
        	    $i = $pageNow - $range;
//		    BUG("ZZZ","i($i) pageNow($pageNow) range($range)\n");
    		}
	    }
    	    if ($i > 0 && $i <= $x) {
        	$pages[] = $i;
    	    }
	}
	// Since because of ellipsing of the current page some numbers may be double,
	// we unify our array:
	sort($pages);
	$pages = array_unique($pages);
    }

    foreach ($pages as $i) {
        if ($i == $pageNow) {
	    $selected = 'selected="selected" style="font-weight: bold"';
	} else {
            $selected = '';
        }
        $gotopage .= '<option ' . $selected . ' value="' . ($i) . '">' . $i . '</option>' . "\n";
    }
    $gotopage .= '</select><noscript><input type="submit" value="enviar" /></noscript>';
    return $gotopage;
}
function is_empty_dir($dir)
{
    if (is_dir($dir)) {
	if ($dh = @opendir($dir)) {
	    while ($file = readdir($dh)) {
		if ($file != '.' && $file != '..') {
	    	    closedir($dh);
	    	    return false;
		}
	    }
    	    closedir($dh);
    	    return true;
	}
    } else 
	return false; // whatever the reason is : no such dir, not a dir, not readable
}

function storeReferer() {

    if ( !isset($_SESSION['referer'])) {
	$referer_arr = array();
    } else {
	$referer_arr = $_SESSION['referer'];
    }

    if ( isset($_SERVER['HTTP_REFERER']) ) {
	$referer_arr[] = $_SERVER['HTTP_REFERER'];
	// si venimos de nuestra pagina (comparamos la parte del host)
	//if ( ( substr($link_referer, 0, (strlen($_SERVER['HTTP_HOST'])+7)) == "http://" . $_SERVER['HTTP_HOST']) )
	$_SESSION['referer'] = $referer_arr;
    }
    
    return;
}

function getReferer() {
    if ( !isset($_SESSION['referer']) ) { return false; }

    $referer_arr = $_SESSION['referer'];
    // REVIEW / REVISAR / IFSNOP
    $link = array_pop($referer_arr);
    //$link = $referer_arr[count($referer_arr)];

    $link = "coming from <a href='${link}'>${link}</a><br />\n";
    $_SESSION['referer'] = $referer_arr;

    return $link;
}


$charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
function dec2string($decimal, $base) {
global $charset;
    // convert a decimal number into a string using $base

    $string = null;
    $base = (int)$base;
    $charset_size = strlen($charset); 

    // echo 'BASE must be in the range 2-9 or 11-62';
    if ($base < 2 | $base > $charset_size) return false;
    if ($base == 10) return $decimal;

    // strip off excess characters (anything beyond $base)
    $charset_local = substr($charset, 0, $base);
    
    // $error['dec_input'] = 'Value must be a positive integer with < 80 digits we are using bcmod!';
    if (!is_numeric(trim($decimal))) return false; // || strlen(trim($decimal))>80) return false; 

    do {
	// get remainder after dividing by BASE
	$remainder = my_bcmod($decimal, $base);
	$char      = substr($charset_local, $remainder, 1);   // get CHAR from array
	$string    = "${char}${string}";
	$decimal   = bcdiv(bcsub($decimal, $remainder), $base);
    } while ($decimal > 0);
    return $string;
}

/**
 * my_bcmod - get modulus (substitute for bcmod)
 * string my_bcmod ( string left_operand, int modulus )
 * left_operand can be really big, but be carefull with modulus :(
 * by Andrius Baranauskas and Laurynas Butkus :) Vilnius, Lithuania
 * necesaria para dec2string realmente grandes (mas de 50 chars)
 **/
function my_bcmod( $x, $y ) {
    // how many numbers to take at once? carefull not to exceed (int)

    if ( ($pos = strpos($x, ".")) !== false )
	$x = substr($x,0, $pos);
    $take = 5;
    $mod = '';
    do {
	$a = (int)$mod.substr( $x, 0, $take );
	$x = substr( $x, $take );
	$mod = $a % $y;
    }
    while ( strlen($x) );
    return (int)$mod;
}

// ****************************************************************************
function string2dec ($string, $base) {
global $charset;
    // convert a string into a decimal number using $base

    $decimal = 0;
    $base = (int)$base;
    $charset_size = strlen($charset);

    // BASE must be in the range 2-9 or 11-36
    if ($base < 2 | $base > $charset_size) return false;
    if ($base == 10) return $string;
    // strip off excess characters (anything beyond $base)
    $charset_local = substr($charset, 0, $base);

    $string = trim($string);
    // Input string is empty
    
    if (strlen($string)==0) return false;

    do {
	$char   = substr($string, 0, 1);    // extract leading character
	$string = substr($string, 1);       // drop leading character
	$pos = strpos($charset_local, $char);     // get offset in $charset
	// Illegal character ($char) in INPUT string

	if ($pos === false) return false;
	$decimal = bcadd(bcmul($decimal, $base), $pos);
   } while($string <> null);

    if (($pos = strpos($decimal,'.')) !== false)
	$decimal = substr($decimal, 0, $pos);
   return $decimal;

} // string2dec

function mysql2unixTimestamp($timestamp) {
// eg. 2008-12-29 05:05:35
    list($fecha, $hora) = explode(' ', $timestamp);
    list($year, $month, $day) = explode('-', $fecha);
    list($hour, $minute, $second) = explode(':', $hora);
    $newtimestamp = mktime($hour, $minute, $second, $month, $day, $year);
    return $newtimestamp;
}
// Using this for SEO urls. I had to modify it a bit to get through the word wrap.  Pretty sure you can one line it a lot of it.
function encodeUrlParam ( $string )
{
  $string = trim($string);
  if ( ctype_digit($string) ) {
    return $string;
  } else {
    // replace accented chars
    $accents = '/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/';
    $string_encoded = htmlentities($string,ENT_NOQUOTES,'UTF-8');

    $string = preg_replace($accents,'$1',$string_encoded);
     
    // clean out the rest
    $replace = array('([\40])','([^a-zA-Z0-9-])','(-{2,})');
    $with = array('-','','-');
    $string = preg_replace($replace,$with,$string);
  }

  return strtolower($string);
}
function trimString($string, $maxChars) {

    if (!isset($string)) return "";
    if (!isset($maxChars) || $maxChars == 0) return $string;
    if (strlen($string)<=$maxChars) return $string;
    
    $string = substr($string, 0, $maxChars - 3) . "...";
    return $string;
}

define("WORD_COUNT_MASK", "/\p{L}[\p{L}\p{Mn}\p{Pd}'\x{2019}]*/u");
function str_word_count_utf8($str) {
    return preg_match_all(WORD_COUNT_MASK, $str, $matches);
}

/**
 * View any string as a hexdump.
 *
 * This is most commonly used to view binary data from streams
 * or sockets while debugging, but can be used to view any string
 * with non-viewable characters.
 *
 * @version     1.3.2
 * @author      Aidan Lister <aidan@php.net>
 * @author      Peter Waller <iridum@php.net>
 * @link        http://aidanlister.com/repos/v/function.hexdump.php
 * @param       string  $data        The string to be dumped
 * @param       bool    $htmloutput  Set to false for non-HTML output
 * @param       bool    $uppercase   Set to true for uppercase hex
 * @param       bool    $return      Set to true to return the dump
 */
function hexdump ($data, $htmloutput = true, $uppercase = false, $return = false)
{
    // Init
    $hexi   = '';
    $ascii  = '';
    $dump   = ($htmloutput === true) ? '<pre>' : '';
    $offset = 0;
    $len    = strlen($data);

    // Upper or lower case hexadecimal
    $x = ($uppercase === false) ? 'x' : 'X';

    // Iterate string
    for ($i = $j = 0; $i < $len; $i++)
    {
        // Convert to hexidecimal
        $hexi .= sprintf("%02$x ", ord($data[$i]));

        // Replace non-viewable bytes with '.'
        if (ord($data[$i]) >= 32) {
            $ascii .= ($htmloutput === true) ?
                            htmlentities($data[$i]) :
                            $data[$i];
        } else {
            $ascii .= '.';
        }

        // Add extra column spacing
        if ($j === 7) {
            $hexi  .= ' ';
            $ascii .= ' ';
        }

        // Add row
        if (++$j === 16 || $i === $len - 1) {
            // Join the hexi / ascii output
            $dump .= sprintf("%04$x  %-49s  %s", $offset, $hexi, $ascii);

            // Reset vars
            $hexi   = $ascii = '';
            $offset += 16;
            $j      = 0;

            // Add newline
            if ($i !== $len - 1) {
                $dump .= "\n";
            }
        }
    }

    // Finish dump
    $dump .= $htmloutput === true ?
                '</pre>' :
                '';
    $dump .= "\n";

    // Output method
    if ($return === false) {
        echo $dump;
    } else {
        return $dump;
    }
}

