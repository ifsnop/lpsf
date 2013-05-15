<?php

function lpsf_log($where, $str) {
    global $lpsf;
    
    if (is_null($where) || is_null($str) || $str == "")
	die(Bnumber());

    insert_at_top($lpsf['errors'], $str, $lpsf['config']['errors_count']);
    
    return;
}