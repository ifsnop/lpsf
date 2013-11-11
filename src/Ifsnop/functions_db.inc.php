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

// GENERICOS DE BBDD
/*
function databaseConnect($user = NULL, $password = NULL, $database = NULL) {
global $timer_db;

    if (is_null($user) || is_null($password) || is_null($database)) {
	$user = 'dbuser'; $password = 'dbpass'; $database = 'dbdb';
    }

    $timer_db_start = microtime(true);
    if ( ($db = mysql_connect('mysql01.azul.lan', $user, $password)) === false ) { 
	BUG(Bnumber(), "mysql_connect"); die();
    }
    if ( (mysql_select_db($database, $db)) === false ) { BUG(Bnumber(), "mysql_select_db", $db); die(); }
//    mysql_query("SET SESSION character_set_results = 'UTF8'");
//    mysql_query("SET CHARACTER SET utf8");

    $stmt = "SET NAMES utf8";
    if (mysql_query($stmt, $db) === false) { BUG(Bnumber(), $stmt, $db); die(); }
    $stmt = "SET collation_connection = 'utf8_spanish_ci'";
    if (mysql_query($stmt, $db) === false) { BUG(Bnumber(), $stmt, $db); die(); }

    $timer_db_stop = microtime(true);
    $timer_db += $timer_db_stop - $timer_db_start;

    return $db;
}

function executeQuery($error_number, $db, $stmt, $is_zero = NULL) {
global $timer_db;
//
// si pasamos "isZero" o algo, devolveremos false cuando mysql_num_rows de 0.
// si no pasamos nada, y no hay resultados, fallamos y morimos
// 
    if (is_null($db)) {
	BUG($error_number, "no db selected: $stmt"); die();
    }

    $timer_db_start = microtime(true);
    $rs = mysql_query($stmt, $db);
    $timer_db_stop = microtime(true);
    $timer_db += $timer_db_stop - $timer_db_start;
    if ($rs === false) { // ERROR EN LA CONSULTA
	BUG($error_number, $stmt, $db); die();
    } else {
	if ($rs && mysql_num_rows($rs) == 0) { 
	    if (is_null($is_zero)) { // esta consulta nunca deberia devolver 0
		// return false; 
		BUG($error_number, "(empty resultset) " . $stmt, $db); 
		gotoHomeIf(); exit;
	    } else { // si no hay resultados, devuelve false
		// return $rs;
		return false;
	    }
	} else {
	    return $rs;
	}
    }
    return $rs;
}

function executeNonQuery($error_number, $db, $stmt) {
global $timer_db;

    $timer_db_start = microtime(true);
    $rs = mysql_query($stmt, $db);
    $timer_db_stop = microtime(true);
    $timer_db += $timer_db_stop - $timer_db_start;
    if ($rs === false) { BUG($error_number, $stmt, $db); return false; }

    //not useful since this query doesn't return resultsets
    //if (mysql_free_result($rs) === false) { die(BUG($error_number . "a", $db, $stmt)); }

    return;
}
*/

function executeInsert2($where, $dbi, $table, $my_arr) {
    global $lpsf;

    if (is_null($where) || is_null($dbi) || is_null($table) || !is_array($my_arr)) {
        BUG2($where, "missing statments where($where), dbi, table($table), my_arr");
        die("missing statments where($where), dbi, table($table), my_arr" . PHP_EOL);
    }

    $timer_db_start = microtime(TRUE);
    $sql = array();
    foreach($my_arr as $key => $value){
        $sql[] = ($value=="now()" || is_numeric($value)) ? "`$key` = $value" : "`$key` = '" . $dbi->real_escape_string($value) . "'"; 
    }
    $sqlclause = implode(",", $sql);
    $stmt =  "INSERT INTO `$table` SET $sqlclause";
    $lpsf['config']['timer_db'] += microtime(TRUE) - $timer_db_start;

    return executeNonQuery2($where, $dbi, $stmt);
}

function executeQuery2($where, $dbi, $stmt, $canBeZero = NULL) {
    global $lpsf;
//
// si pasamos "isZero" o algo, devolveremos false cuando mysql_num_rows de 0.
// si no pasamos nada, y no hay resultados, fallamos y morimos
//
    if (is_null($dbi)) {
        BUG2($where, "no db selected: $stmt"); die();
    }
    if (is_null($stmt)) {
        BUG2($where, "no stmt selected"); die();
    }

    $timer_db_start = microtime(TRUE);
    $rs = $dbi->query($stmt);
    $lpsf['config']['timer_db'] += microtime(TRUE) - $timer_db_start;
    if ($rs === FALSE) { // ERROR EN LA CONSULTA
        BUG2($where, $stmt, $dbi); die($where . " error en la consula stmt($stmt)" . PHP_EOL);
    } else {
        if ($rs && ($rs->num_rows == 0)) {
	    if (is_null($canBeZero)) { // esta consulta nunca deberia devolver 0
		// return false; 
		BUG2($where, "(empty resultset) " . $stmt, $dbi); die("(empty resultset) " . $stmt . PHP_EOL);
		//gotoHomeIf(); exit;
	    } else { // si no hay resultados, devuelve false
		// return $rs;
		return FALSE;
	    }
	} else {
	    return $rs;
	}
    }
    return FALSE; //not reached
}


function executeNonQuery2($where, $dbi, $stmt) {
    global $lpsf;

    $timer_db_start = microtime(TRUE);
    $rs = $dbi->query($stmt);
    $lpsf['config']['timer_db'] += microtime(TRUE) - $timer_db_start;
    if ($rs === FALSE) { BUG2($where, $stmt, $dbi); return FALSE; }
    return TRUE;
}

function databaseConnect2() {
    global $lpsf;

    $timer_db_start = microtime(TRUE);

    $dbi = new mysqli($lpsf['config']['db']['host'], 
        $lpsf['config']['db']['user'],
	$lpsf['config']['db']['pass'],
	$lpsf['config']['db']['db']);
    if ($dbi->connect_error) {
	BUG(Bnumber(), "new mysqli", $dbi);
	die("error mysqli (" . $dbi->connect_errno . ") " . $dbi->connect_error);
    }
    $dbi->set_charset('utf8');
    $dbi->query("SET NAMES 'utf8'");
    $dbi->query("SET collation_connection = 'utf8_spanish_ci'");
    
//    if ( (mysql_select_db($db, $dbc)) === false ) { BUG(Bnumber(), "mysql_select_db", $dbc); die(); }
//    mysql_query("SET SESSION character_set_results = 'UTF8'"); mysql_query("SET CHARACTER SET utf8");

//    $stmt = "SET NAMES utf8";
//    if (mysql_query($stmt, $dbc) === false) { BUG(Bnumber(), $stmt, $dbc); die(); }
//    $stmt = "SET collation_connection = 'utf8_spanish_ci'";
//    if (mysql_query($stmt, $dbc) === false) { BUG(Bnumber(), $stmt, $dbc); die(); }

    $lpsf['config']['timer_db'] += microtime(TRUE) - $timer_db_start;

    return $dbi;
}

// ESPECIFICOS DE 3BDP

function db_delete_pic($mod_delete_post_xid, $mod_delete_pic_xid, $db_query, $mod_delete_post_id = NULL, $mod_delete_pic_id = NULL) {

    $refresh = false;
    $db_query = databaseConnect('fotos_root_usr','fotos_root_pass','fotos');
    $mod_delete_post_xid_quoted = "'" . mysql_real_escape_string($mod_delete_post_xid, $db_query) . "'";
    $mod_delete_pic_xid_quoted = "'" . mysql_real_escape_string($mod_delete_pic_xid, $db_query) . "'";

    if (!isset($mod_delete_pic_id)) {
	// averiguamos el true id
	$delete_pic_stmt = "SELECT id FROM pics WHERE xid=$mod_delete_pic_xid_quoted AND is_deleted='0'";
	//print $delete_pic_stmt . "<br>\n";
	$delete_rs = executeQuery(Bnumber(), $db_query, $delete_pic_stmt); // deberia de haber un fallback para volver al post!
	$delete_row = mysql_fetch_row($delete_rs);
	$mod_delete_pic_id = $delete_row[0];
	$refresh = true;
    }

    // marcamos la foto como borrada
    $delete_pic_stmt = "UPDATE pics SET is_deleted='1',delete_date=now() WHERE id='$mod_delete_pic_id' AND is_deleted='0'";
    //print $delete_pic_stmt . "<br>\n";
    executeNonQuery(Bnumber(), $db_query, $delete_pic_stmt);

    // marcamos como borradas las asociaciones que hagan todos los posts a esa foto
    $delete_pic_stmt = "UPDATE posts_pics SET is_deleted='1',delete_date=now() WHERE pic_id='$mod_delete_pic_id' AND is_deleted='0'";
    //print $delete_pic_stmt . "<br>\n";
    executeNonQuery(Bnumber(), $db_query, $delete_pic_stmt);

    // hay que revisar todos los posts que referencien esta foto para marcar el post como vacio si se diese el caso
    $delete_pic_stmt = "SELECT pp.post_id FROM posts_pics pp WHERE pp.pic_id='$mod_delete_pic_id'";
    //print $delete_pic_stmt . "<br>\n";
    $delete_rs = executeQuery(Bnumber(), $db_query, $delete_pic_stmt, "canBeZero");
    if ($delete_rs) {
        while ($delete_row = mysql_fetch_row($delete_rs)) {
    	    $empty_stmt = "SELECT COUNT(post_id) FROM posts_pics WHERE post_id = '$delete_row[0]' AND is_deleted='0'";
	    //print $empty_stmt . "<br>\n";
	    $empty_rs = executeQuery(Bnumber(), $db_query, $empty_stmt);
	    $empty_row = mysql_fetch_row($empty_rs);
	    if ($empty_row[0] == 0) {
	        // el post no tiene pics, marcar como vacio
	        $empty_stmt = "UPDATE posts SET is_empty='1' WHERE id='$delete_row[0]'";
	        //print $empty_stmt . "<br>\n";
	        executeNonQuery(Bnumber(), $db_query, $empty_stmt);
	    }
	    mysql_free_result($empty_rs);
	}
    }



    //$link = substr($_SERVER['HTTP_REFERER'], strlen("http://" . $_SERVER['HTTP_HOST']), strlen($_SERVER['HTTP_REFERER']) - strlen("http://" . $_SERVER['HTTP_HOST']));
    if (isset($mod_delete_post_xid)) {
	deleteCache("cache/${mod_delete_post_xid}");
	if ($refresh) {
    	    header("Refresh: 0; URL=" . URL_HOME . "/a/display/$mod_delete_post_xid"); die();
//	    exit;
	}
    }
}

function db_delete_post($mod_delete_post_xid, $db_query) {

    $mod_delete_post_xid_quoted = "'" . mysql_real_escape_string($mod_delete_post_xid, $db_query) . "'";
    $db = databaseConnect('fotos_root_usr','fotos_root_pass','fotos');

    // averiguamos el true id
    $delete_post_stmt = "SELECT id FROM posts WHERE xid=$mod_delete_post_xid_quoted AND is_deleted='0'";

//    print $delete_post_stmt . "<br>\n";
    $delete_rs = executeQuery(Bnumber(), $db, $delete_post_stmt, "canBeZero");
    if ($delete_rs === FALSE) gotoHomeIf(NULL,NULL,'redirect');

    $delete_row = mysql_fetch_row($delete_rs);
    $mod_delete_post_id = $delete_row[0];
    // seleccionamos las fotos, y las borramos una a una
    $delete_pic_stmt = "SELECT pic_id FROM posts_pics pp WHERE pp.post_id = '$mod_delete_post_id' AND pp.is_deleted='0'";
    //print $delete_pic_stmt . "<br>\n";
    $pics_rs = executeQuery(Bnumber(), $db, $delete_pic_stmt, "canBeZero");
    if ($pics_rs) {
	while ($pic_row = mysql_fetch_row($pics_rs)) {
	    //print $pic_row[0] . "<br>\n";
	    db_delete_pic($mod_delete_post_xid, "000000000", $db, $mod_delete_post_id, $pic_row[0]);
	}
	mysql_free_result($pics_rs);
    }

    // quitamos las asociaciones a tags
    $delete_tags_stmt = "SELECT tp.tag_id FROM tags_posts tp WHERE tp.post_id = '$mod_delete_post_id' AND tp.is_deleted='0'";
    //print $delete_tags_stmt . "<br>\n";
    $tags_rs = executeQuery(Bnumber(), $db, $delete_tags_stmt, "canBeZero");
    if ($tags_rs) {
	while ($tag_row = mysql_fetch_row($tags_rs)) {
	    //print "tag: " . $tag_row[0] . "<br>\n";
	    db_delete_tag($tag_row[0], $mod_delete_post_id, $db);
	}
    }

    // marcamos los tags como borrados si no apuntan a nadie mas

    // lo ultimo es marcar el post como borrado, para poder repetir en caso de error
    $delete_post_stmt = "UPDATE posts SET is_deleted='1',delete_date=now() WHERE id='$mod_delete_post_id' AND is_deleted='0'";
    //print $delete_post_stmt . "<br>\n";
    executeNonQuery(Bnumber(), $db, $delete_post_stmt);

    //$link = substr($_SERVER['HTTP_REFERER'], strlen("http://" . $_SERVER['HTTP_HOST']), strlen($_SERVER['HTTP_REFERER']) - strlen("http://" . $_SERVER['HTTP_HOST']));
    deleteCache(DIR_HOME . "/cache/${mod_delete_post_xid}");
//    header("Refresh: 0; URL=" URL_HOME . "/a/display/$mod_delete_post_xid"); 
    gotoHomeIf(NULL,NULL,'redirect');
//    exit;
}

function db_delete_tag($tag_id, $post_id, $db_query) {

    $tag_stmt = "UPDATE tags_posts SET is_deleted='1', delete_date=now() WHERE tag_id='$tag_id' AND post_id='$post_id'";
    //print $tag_stmt . "<br>\n";
    executeNonQuery(Bnumber(), $db_query, $tag_stmt);

    // se cuentan los posts que tengan este tag y que no esten borrados
    $empty_stmt = "SELECT COUNT(post_id) FROM tags_posts WHERE tag_id='$tag_id' AND is_deleted='0'";
    //print $empty_stmt . "<br>\n";
    $empty_rs = executeQuery(Bnumber(), $db_query, $empty_stmt);
    $empty_row = mysql_fetch_row($empty_rs);
    //print $empty_row[0] . "<br>\n";
    if ($empty_row[0] == 0) {
	$tag_stmt = "UPDATE tags SET is_deleted='1', delete_date=now() WHERE id='$tag_id'";
	//print $tag_stmt . "<br>\n";
	executeNonQuery(Bnumber(), $db_query, $empty_stmt);
    }
    
}

function db_delete_pic_local($pic_id, $db_query) {
global $DIR_HOME, $DIR_TH, $DIR_IMAGE;

    $stmt = "SELECT image_local_path, th_local_path FROM pics_local WHERE pic_id=$pic_id";
    $rs = executeQuery(Bnumber(), $db_query, $stmt);
    $row = mysql_fetch_row($rs);
    $image_local_path = $row[0];
    $th_local_path = $row[1];

    // DELETE THUMBNAIL
/*
    print "\n";
    print "0_$image_local_path\n";
    print "1_" . DIR_IMAGES . $image_local_path . "\n"; 
    print "2_" . DIR_IMAGES . dirname($image_local_path) . "\n";
    print "3_$th_local_path\n";
    print "4_" . DIR_THUMBS . $th_local_path . "\n";
    print "5_" . DIR_THUMBS . dirname($th_local_path) . "\n"; 
*/    
    if ( ($th_local_path != "") && (is_file(DIR_THUMBS . $th_local_path)) ) {
	if ( unlink(DIR_THUMBS . $th_local_path) === FALSE ) { BUG("044", "unlink"); }
	if ( is_empty_dir(DIR_THUMBS . dirname($th_local_path)) )
	    if ( rmdir(DIR_THUMBS . dirname($th_local_path)) === FALSE ) { BUG("020", "rmdir"); }
    }
    // DELETE IMAGE
    if ( ($image_local_path != "") && (is_file(DIR_IMAGES . $image_local_path)) ) {
	if ( unlink(DIR_IMAGES . $image_local_path) === FALSE ) { BUG("045", "unlink"); }
	if ( is_empty_dir(DIR_IMAGES . dirname($image_local_path)) )
	    if ( rmdir(DIR_IMAGES . dirname($image_local_path)) === FALSE ) { BUG("021", "rmdir"); }
    }

    $stmt = "DELETE FROM pics_local WHERE pic_id=$pic_id";
    executeNonQuery(Bnumber(), $db_query, $stmt);
    return TRUE;
}

function updateRuidos($link, $valoracion, $fabricante, $db_query) {

    $stmt = "UPDATE ruidos SET valoracion=$valoracion, is_alerta='1', fabricante=$fabricante WHERE link=$link";
    executeNonQuery(Bnumber(), $db_query, $stmt);

}
