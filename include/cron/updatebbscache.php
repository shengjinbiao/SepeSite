<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: updatebbscache.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./function/cache.func.php');

//缓存论坛设置
updatebbssetting();
//缓存论坛风格设置
updatebbsstyle();
//缓存语言屏蔽
updatecensorcache();
//缓存论坛bbcode/smiles
updatebbsbbcode();

?>