<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: updatebbscache.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./function/cache.func.php');

//������̳����
updatebbssetting();
//������̳�������
updatebbsstyle();
//������������
updatecensorcache();
//������̳bbcode/smiles
updatebbsbbcode();

?>