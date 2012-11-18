<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: uchome.func.php 13305 2009-08-31 05:33:01Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

function atturl($imageurl){
	global $_SC;
	return '<img src="'.$_SC['uchurl'].'/'.$imageurl.'">';
}

function addurlhttp($m) {
	global $_SC;
	
	if (preg_grep("/^http\:/", array($m[2]))) {
		return 'src="'.$m[2].'.'.$m[3].'"';
	} else {
		return 'src="'.$_SC['uchurl'].'/'.$m[2].'.'.$m[3].'"';
	}
		
}
?>