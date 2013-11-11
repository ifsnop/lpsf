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

define('ERROR_LOG', DIR_HOME . "/php-error_devel.log");

function Bnumber() {
    $res = debug_backtrace();
    //return substr($res[0]['file'], strlen(DIR_HOME)) . ":" . $res[0]['line']; // since lib doesn't live with code, substr won't work
    return $res[0]['file'] . ":" . $res[0]['line'];
}

function own_error_handler($num_err, $mens_err, $nombre_archivo, $num_linea, $vars) {
    // marca de fecha/hora para el registro de error
    $dt = date("[Y-m-d H:i:s (T)]");
    // definir una matriz asociativa de cadenas de error
    // en realidad las unicas entradas que deberiamos
    // considerar son E_WARNING, E_NOTICE, E_USER_ERROR,
    // E_USER_WARNING y E_USER_NOTICE
    $tipo_error = array (
                E_ERROR           => "Error",
                E_WARNING         => "Advertencia",
                E_PARSE           => "Error de Interprete",
                E_NOTICE          => "Anotacion",
                E_CORE_ERROR      => "Error de Nucleo",
                E_CORE_WARNING    => "Advertencia de Nucleo",
                E_COMPILE_ERROR   => "Error de Compilacion",
                E_COMPILE_WARNING => "Advertencia de Compilacion",
                E_USER_ERROR      => "Error de Usuario",
                E_USER_WARNING    => "Advertencia de Usuario",
                E_USER_NOTICE     => "Anotacion de Usuario",
                E_STRICT          => "Anotacion de tiempo de ejecucion",
                E_RECOVERABLE_ERROR => "Catchable fatal error",
                E_DEPRECATED      => "Funcion deprecada"
            );
    // conjunto de errores de los cuales se almacenara un rastreo
    $errores_de_usuario = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE, E_WARNING, E_PARSE, E_NOTICE, E_STRICT, E_RECOVERABLE_ERROR, E_DEPRECATED);
    if (isset($tipo_error[$num_err])) {
	$err = $dt . " " . $tipo_error[$num_err] . "> " . $mens_err . "\n\tin " . $nombre_archivo . "(" . $num_linea . ")";
    } else {
	$err = $dt . " undefined_error[" . $num_err . "]> " . $mens_err . "\n\tin " . $nombre_archivo . "(" . $num_linea . ")";
    }

//    if (in_array($num_err, $errores_de_usuario)) {
//        $err .= "\n\t<vartrace>" . wddx_serialize_value($vars, "Variables") . "</vartrace>";
//    }

    $aCallstack=debug_backtrace();
    foreach($aCallstack as $aCall) {
	if (!isset($aCall['file'])) {
	    //$aCall['file'] = '[PHP Kernel]';
	    continue;
	}
	if (!isset($aCall['line'])) 
	    $aCall['line'] = '';
	$err .= "\n\t{$aCall['file']} >> {$aCall['line']} >> {$aCall['function']}";
    }
    
    $err .= "\n";
    error_log($err, 3, ERROR_LOG);
    return;
}
//    own_error_handler("1", "prueba de mensaje", "source.php", "123", NULL);
    date_default_timezone_set("Europe/Madrid");
//  "Advertencia  display_errors(true);
//    error_reporting(E_ALL ^ E_NOTICE);
//    log_errors(true);
//    error_reporting(E_ALL);
//    error_reporting(0);

    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    
    $old_error_handler = set_error_handler("own_error_handler");

// useful and comfortable debug function
// it's show memory usage and time flow between calls, so we can quickly find a block of code that need optimisation...
// example result:
/*
debug example.php> initialize
debug example.php> code-lines: 39-41 time: 2.0002 mem: 19 KB
debug example.php> code-lines: 41-44 time: 0.0000 mem: 19 KB
debug example.php> code-lines: 44-51 time: 0.6343 mem: 9117 KB
debug example.php> code-lines: 51-53 time: 0.1003 mem: 9117 KB
debug example.php> code-lines: 53-55 time: 0.0595 mem: 49 KB
*/

function bdp_debug()
{
   static $start_time = NULL;
   static $start_code_line = 0;

   $backtrace = debug_backtrace(); $call_info = array_shift($backtrace); unset($backtrace);
   $code_line = $call_info['line'];
   $exploded =  explode('/', $call_info['file']); $file = array_pop($exploded); unset($exploded);

   if( $start_time === NULL ) {
       print "debug ".$file."> initialize\n";
       $start_time = time() + microtime();
       $start_code_line = $code_line;
       return 0;
   }

   printf("debug %s> code-lines: %d-%d time: %.4f mem: %.2f KB\n", $file, $start_code_line, $code_line, (time() + microtime() - $start_time), memory_get_usage()/1024);
   $start_time = time() + microtime();
   $start_code_line = $code_line;
}

////////////////////////////////////////////////
// example:
// 
// debug();
// sleep(2);
// debug();
// // soft-code...
// $a = 3 + 5;
// debug();
// 
// // hard-code
// for( $i=0; $i<100000; $i++)
// {
//     $dummy['alamakota'.$i] = 'alamakota'.$i;
// }
// debug();
// usleep(100000);
// debug();
// unset($dummy);
// debug();
//// // // // // // // // // // // // // // // // 

