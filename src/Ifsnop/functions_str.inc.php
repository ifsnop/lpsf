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

function extract_field_ext2($lines, &$i, &$j, $needle, $open_tag, $close_tag, $guard, &$res, $notrim = false, $debug = false) {

    $found = false; $res = ""; $original_i = $i; $original_j = $j;
    if (!is_array($lines)) {
        $lines = array($lines);
    }

    if ($debug) { print "init extract_field_ext2:\n\ti)$i j)$j\n\tN)$needle\n\tO)$open_tag\n\tC)$close_tag\n\tG)$guard\n"; }
    for($line_ptr=$i; $line_ptr<count($lines); $line_ptr++) {
        $line = trim($lines[$line_ptr]);
        $last_concat_line_length = strlen(trim($lines[$line_ptr]));

        if ( (strlen($line) === FALSE) || ($j>=strlen($line)) || ($j<0) || ($needle_pos = strpos($line, $needle, $j)) === FALSE ) {
            if ($debug) print "next line ($line_ptr)\n";
            if (strpos($line, $guard)!==FALSE) { if ($debug) print "guard0\n"; $i = $original_i; $j = $original_j; return FALSE; } // guard check!
            $j=0;
            continue;
        }
        $needle_pos += strlen($needle);

        while ( ($str_ini = strpos($line, $open_tag, $needle_pos)) === FALSE ) {
            if ( (strpos($line, $guard)!==FALSE) || ($line_ptr >= (count($lines)/*+1*/)) ) {
                if ($debug) print "guard1\n";
                $i = $original_i; $j = $original_j; return FALSE; 
            } // guard check!

            $line_ptr++; 
            if (!isset($lines[$line_ptr])) {
                print "$line_ptr out of range: " . count($lines) . PHP_EOL;
                print "$line" . PHP_EOL;
            }
            $line .= trim($lines[$line_ptr]);
            $last_concat_line_length += strlen(trim($lines[$line_ptr]));
        }
        $str_ini += strlen($open_tag);
        //$str_ini++;

        if ($debug) print "1>>$line<<\n";
        if ($debug) print "pos open)" . $str_ini . "\n";
        if ($debug) print "pos close)" . strpos($line, $close_tag, $str_ini) . "\n";
        if ($debug) { print "N)$needle\n\tO)$open_tag\n\tC)$close_tag\n"; }
        while ( ($str_fin = strpos($line, $close_tag, $str_ini)) === FALSE) {
            if ( strpos($line, $guard, $str_ini)!==FALSE || ($line_ptr>=(count($lines)+1)) ) {
                if ($debug) { print "guard2 (" . strpos($line, $guard, $str_ini) . ") (${j}>=" . (count($lines)+1) . ")\n"; }
                $i = $original_i; $j = $original_j; return FALSE; } // guard check!
            $line_ptr++; $line .= trim($lines[$line_ptr]); 
            $last_concat_line_length += strlen($lines[$line_ptr]);
        }
        if ($debug) print "2>>$line<<\n";
        if ($str_ini===FALSE || $str_fin===FALSE || $str_fin < $str_ini) {
            $i = $original_i; $j = $original_j; return FALSE;
        }

        $str = substr($line, $str_ini, $str_fin - $str_ini);
        if (!$notrim)
            $str = clean_web_input($str);

        $res = $str;
        $found = true;
        $i = $line_ptr;
        $last_concat_line_length -= strlen($lines[$line_ptr]);
        //$j = $str_fin; // position relative to concatenated strings, lets fix it.
        $j = $str_fin - $last_concat_line_length;

        if ($debug) print "i)$i\n";
        if ($debug) print "3>>$res<<\n";
        return TRUE;
    }
    if ($debug) { print "end extract_field_ext2: full array parsed, needle not found\n"; }
    $i = $original_i; $j = $original_j; return FALSE;
}


function fixDateCommon($str, $provider) 
{
    $meses_completos  = array( 1 => "enero", "febrero", "marzo", "abril", 
        "mayo", "junio", "julio", "agosto", "septiembre", "octubre", 
        "noviembre", "diciembre");
    $meses_completos = array_flip($meses_completos);
    $meses_completos_ingles  = array( 1 => "january", "february", "march", "april", 
        "may", "june", "july", "augost", "september", "october",
        "november", "december");
    $meses_completos_ingles = array_flip($meses_completos_ingles);
    $meses_abre  = array( 1 => "ene", "feb", "mar", "abr",
        "may", "jun", "jul", "ago", "sep", "oct",
        "nov", "dic");
    $meses_abre = array_flip($meses_abre);
    $meses_abre2  = array( 1 => "ene", "febr", "mar", "abr",
        "may", "jun", "jul", "ago", "sept", "oct",
        "nov", "dic");
    $meses_abre2 = array_flip($meses_abre2);
    $meses_abre_ingles  = array( 1 => "jan", "feb", "mar", "apr",
        "may", "jun", "jul", "aug", "sep", "oct",
        "nov", "dec");
    $meses_abre_ingles = array_flip($meses_abre_ingles);

    switch($provider) {
        case "Ciao": //15 de Jun de 2009
            if (strlen($str)<15) return "";
            preg_match("/(\d+)\s+de\s(\w+)\s+de\s+(\d+)(.*)$/",$str, $m);
            $res = $m[3] . "-" . $meses_abre2[mb_strtolower($m[2])] . "-" . $m[1];
            break;
        case "movilzona": //27 Nov 2011, 17:11
            $str_arr=explode(', ', $str);
            if (strlen($str)<15) return "";
            preg_match("/(\d+)\s+(\w+)\s+(\d+)(.*)$/",$str_arr[0], $m);
            $res = $m[3] . "-" . $meses_abre[mb_strtolower($m[2])] . "-" . $m[1] . " " . $str_arr[1];
            break;
        case "ActiveHotels":
        case "Booking": //15 de junio de 2009
            if (strlen($str)<15) return "";
            preg_match("/(\d+)\sde\s(\w+)\sde\s(\d+)$/",$str, $m);
            $res = $m[3] . "-" . $meses_completos[mb_strtolower($m[2])] . "-" . $m[1];
            break;
        case "TripAdvisorES": //14 jun 2010
            preg_match("/(\d+)\s(\w+)\s(\d+).*/",$str, $m);
            $res= $m[3] . "-" . $meses_abre[mb_strtolower($m[2])] . "-" . $m[1];
            break;
        case "TripAdvisor": //May 12, 2010 //15 dec 2009
            //preg_match("/(\d+)\s(\w+)\s(\d+).*/",$str, $m);
            preg_match("/(\w+)\s(\d+),\s(\d+).*/",$str, $m);
            if ($meses_abre_ingles[mb_strtolower($m[1])]=="") {$mes=date("m"); } else { $mes=$meses_abre_ingles[mb_strtolower($m[1])]; }
            $res = $m[3] . "-" . $mes . "-" . $m[2];
            break;
        case "Venere": //Jan 2006
            preg_match("/(\w+)\s(\d+).*/",$str, $m);
            //$res = $m[2] . "-" . $meses_abre_ingles[mb_strtolower($m[1])] . "-01";
            $res = $m[2] . "-" . $meses_completos_ingles[mb_strtolower($m[1])] . "-01";
            break;
        case "Ulises"://6-07-2006 ó 07-07-2006
            preg_match("/(\d+).(\d+).(\d+).*/",$str, $m);
            $res = $m[3] . "-" . $m[2] . "-" . $m[1];
            break;
        case "QuieroHotel": // 05/03/2008
        case "Trivago": // 05/03/2008
        case "Ciao": // 05.03.2009
        case "QueHoteles": //05/03/2008
        case "Atrapalo": //05-01-2010
            $res = substr($str, 6,4) . "-" . substr($str, 3,2) . "-" . substr($str, 0,2);
            break;
        default :
            $res = "";
    }
    if (strlen($res)<8) {
        print "ERROR: str($str)res($res)provider($provider)\n";
    }
    return $res;
}

function fixDate($last_update_d) 
{
    $last_update_arr = explode(' ', trim($last_update_d));
    $last_update_d = $last_update_arr[0];

    if (strpos($last_update_d,"Hace")!==false) { 
        $last_update_d = date("Y-m-d"); $last_update_arr[1] = date("H:i");
    } else if (strpos($last_update_d,"Hoy")!==false) { 
        $last_update_d = date("Y-m-d"); 
    } else if (strpos($last_update_d,"Ayer")!==false) { 
        $last_update_d = date("Y-m-d", mktime(0,0,0,date("m"), date("d")-1, date("Y")));
    } else {
        if ( strlen($last_update_d)>=10 ) {
            //07-04-2009
            $last_update_d = date("Y-m-d", mktime(0, 0, 0, substr($last_update_d, 3,2), substr($last_update_d,0,2),
                substr($last_update_d,6,4)));
        } else if ( strlen($last_update_d)==8 ) { 
            // 15/01/09 14:13
            $last_update_d = date("Y-m-d", mktime(0, 0, 0, substr($last_update_d, 3,2), substr($last_update_d,0,2),
                "20" . substr($last_update_d,6,2)));
        } else {
            print "ERROR fecha: >" . $last_update_d . "<\n";
            $last_update_d = "1999-01-01 00:00:00";
        }
    }
    if (count($last_update_arr)>1) {
        if (strlen($last_update_arr[1])<6) {
            $time = $last_update_arr[1] . ":00";
        } else {
            $time = $last_update_arr[1];
        }
    } else {
        $time = "00:00:00"; 
    }
    return $last_update_d . " " . $time;
}

// To convert NCR format to UTF-8, I use the following codes
// http://stackoverflow.com/questions/1593046/how-to-convert-unicode-ncr-form-to-its-original-form-in-php
function html_entity_decode_ncr2utf8($string)
{
    static $trans_tbl;

    $string = html_entity_decode($string, ENT_COMPAT, "UTF-8");

    // replace numeric entities
    $string = preg_replace('~&#x([0-9a-f]+);~ei', 'code2utf(hexdec("\\1"))',
        $string);
    $string = preg_replace('~&#([0-9]+);~e', 'code2utf(\\1)', $string);

    // replace literal entities
    if (!isset($trans_tbl)) {
        $trans_tbl = array();

        foreach (get_html_translation_table(HTML_ENTITIES) as $val => $key) {
            $trans_tbl[$key] = utf8_encode($val);
        }
    }

    return strtr($string, $trans_tbl);
}

function code2utf($num) 
{
    if ($num < 128) {
        return chr($num);
    }

    if ($num < 2048) {
        return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
    }

    if ($num < 65536) {
        return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) .
            chr(($num & 63) + 128);
    }

    if ($num < 2097152) {
        return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) .
            chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    }

    return '';
}

/**
 * Format bytes to display it using smart suffix (b, kb, mb, gb, tb or pb)
 *
 * Returns true if both arrays have the same elements,
 * false otherwise or if one parameter is not an array.
 *
 * @version     20140705120000
 * @author      Diego Torres <diego.torres@gmail.com>
 * @link        https://github.com/ifsnop/lpsf
 * @param       int $size    Number to format
 * @return      string       Formatted string
 */
function display_bytes($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}


/*
$teststr = "";
for ( $i = 0; $i <= 255; $i++ )
  $teststr .= chr( $i );

$teststr .= "blah";

print makehex( $teststr );
*/
/* outputs
  00 01 02 03 04 05 06 07 08 09 0A 0B 0C 0D 0E 0F  : ................
  10 11 12 13 14 15 16 17 18 19 1A 1B 1C 1D 1E 1F  : ................
  20 21 22 23 24 25 26 27 28 29 2A 2B 2C 2D 2E 2F  :  !"#$%&'()*+,-./
  30 31 32 33 34 35 36 37 38 39 3A 3B 3C 3D 3E 3F  : 0123456789:;<=>?
  40 41 42 43 44 45 46 47 48 49 4A 4B 4C 4D 4E 4F  : @ABCDEFGHIJKLMNO
  50 51 52 53 54 55 56 57 58 59 5A 5B 5C 5D 5E 5F  : PQRSTUVWXYZ[\]^_
  60 61 62 63 64 65 66 67 68 69 6A 6B 6C 6D 6E 6F  : `abcdefghijklmno
*etc*, there was more but I think you get the idea
*/

/**
 * Function that outputs binary data in a readable form. 
 * Useful for debugging. "Beefed up" version of what Aiden posted below.
 * http://titanic.fauser.edu/php/function.bin2hex.php.htm
 *
 * @version     20140912 225600
 * @author      Diego Torres <diego.torres@gmail.com>
 * @link        https://github.com/ifsnop/lpsf
 * @param       string $data Number to format
 * @return      string       Formatted string
 */
function makehex( $data )
{
  $rawdat = $decdat = "";
  $return = "<pre>";
  $lines = array();
  while ( $i * 16 < strlen( $data ) )
   $lines[] = substr( $data, 16 * $i++, 16 );
  foreach ( $lines as $line )
  {
   for ( $i = 0; $i < strlen( $line ); $i++ )
   {
     $rawdat .= sprintf( "%02X ", ord( $line{ $i } ) );
     $decdat .= ( ord( $line{ $i } ) >= 32 ? $line{ $i } : "." );
   }
   $return .= sprintf( "%-48s : %s\n", $rawdat, $decdat );
   $rawdat = $decdat = "";
  }
  return $return . "</pre>";
}
