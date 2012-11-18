<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: block_sql.func.php 11881 2009-03-30 06:04:20Z zhanglijun $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

function runsql($paramarr, $bbsdb='') {
	global $_SGLOBAL, $_SGET;

	//处理SQL
	$sqlstring = getblocksql($paramarr['sql']);
	
	//初始化
	$listcount = 1;
	
	//连接数据库
	$thedb = empty($bbsdb)?$_SGLOBAL['db']:$bbsdb;
	
	//分页
	if(!empty($paramarr['perpage'])) {
		$countsql = '';
		if(empty($countsql)) {
			$countsql = getcountsql($sqlstring, 'SELECT(.+?)FROM(.+?)WHERE(.+?)ORDER', 2, 3);
		}
		if(empty($countsql)) {
			$countsql = getcountsql($sqlstring, 'SELECT(.+?)FROM(.+?)WHERE(.+?)LIMIT', 2, 3);
		}
		if(empty($countsql)) {
			$countsql = getcountsql($sqlstring, 'SELECT(.+?)FROM(.+?)WHERE(.+?)$', 2, 3);
		}
		if(empty($countsql)) {
			$countsql = getcountsql($sqlstring, 'SELECT(.+?)FROM(.+?)ORDER', 2, -1);
		}
		if(empty($countsql)) {
			$countsql = getcountsql($sqlstring, 'SELECT(.+?)FROM(.+?)LIMIT', 2, -1);
		}
		if(empty($countsql)) {
			$countsql = getcountsql($sqlstring, 'SELECT(.+?)FROM(.+?)$', 2, -1);
		}
		if(!empty($countsql)) {
			$query = $thedb->query($countsql);
			$listcount = $thedb->result($query, 0);
			if($listcount) {
				$paramarr['perpage'] = intval($paramarr['perpage']);
				if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;
		
				if(empty($_SGET['page'])) $_SGET['page'] = 1;
				$_SGET['page'] = intval($_SGET['page']);
				if($_SGET['page'] < 1) $_SGET['page'] = 1;
		
				$start = ($_SGET['page']-1)*$paramarr['perpage'];
	
				//SQL文
				$sqlstring = preg_replace("/ LIMIT(.+?)$/is", '', $sqlstring);
				$sqlstring .= ' LIMIT '.$start.','.$paramarr['perpage'];
			}
		}
	} elseif(!empty($paramarr['limit'])) {
	
		$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
		if($paramarr['limit']) {
			//SQL文
			$sqlstring = preg_replace("/ LIMIT(.+?)$/is", '', $sqlstring);
			$sqlstring .= ' LIMIT '.$paramarr['limit'];
		}
	}
	return array($sqlstring, $listcount);
}
//获取数量sql
function getcountsql($sqlstring, $rule, $tablename, $where) {
	preg_match("/$rule/i", $sqlstring, $mathes);
	if(empty($mathes)) {
		$countsql = '';
	} else {
		if($where < 0) $mathes[$where] = '1';//无限制条件
		$countsql = "SELECT COUNT(*) FROM {$mathes[$tablename]} WHERE {$mathes[$where]}";
	}
	return $countsql;
}



?>