<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	模块语句生成：UCH个人空间
	$Id: admin_blocks_uchspace_code.inc.php 13411 2009-10-22 03:13:01Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

$blockcodearr = array();
if($_POST['blockmodel'] == '2') {
	//高级模式
	if(empty($_POST['sql'])) {
		showmessage('block_thread_code_sql');
	}
	$_POST['sql'] = getblocksql($_POST['sql']);
	$blockcodearr[] = 'sql/'.rawurlencode($_POST['sql']);
} else {
	//向导模式
	if($_POST['setuid']) {
		//指定aid
		$_POST['uid'] = getdotstring($_POST['uid'], 'int');
		if(empty($_POST['uid'])) {
			showmessage('block_uchblog_code_uid');
		} else {
			$blockcodearr[] = 'uid/'.$_POST['uid'];
		}
	} else {
		
		if(!empty($_POST['avatar'])) {
			$blockcodearr[] = 'avatar/'.$_POST['avatar'];
		}
		
		if(!empty($_POST['dateline'])) $blockcodearr[] = 'dateline/'.$_POST['dateline'];

		if(!empty($_POST['updatetime'])) $blockcodearr[] = 'updatetime/'.$_POST['updatetime'];

		$scopestring = getscopestring('viewnum', $_POST['viewnum']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('friendnum', $_POST['friendnum']);
		if($scopestring) $blockcodearr[] = $scopestring;
	}
		
	$orderarr = array();
	if($_POST['order']) {
		foreach ($_POST['order'] as $okey => $order) {
			if(!empty($order)) {
				$sc = $_POST['sc'][$okey];
				if(!empty($sc)) $sc = ' '.$sc;
				$orderarr[] = $order.$sc;
			}
		}
	}
	if(!empty($orderarr)) $blockcodearr[] = 'order/'.implode(',', $orderarr);

}

//multi
if(empty($_POST['showmultipage'])) {
	$_POST['start'] = intval($_POST['start']);
	$_POST['limit'] = intval($_POST['limit']);
	if($_POST['limit'] < 1) {
		showmessage('block_thread_code_limit');
	} else {
		$blockcodearr[] = 'limit/'.$_POST['start'].','.$_POST['limit'];
	}
} else {
	$_POST['tplname'] = $_POST['tpl'] = 'data';
	$_POST['perpage'] = intval($_POST['perpage']);
	$_POST['pageurl'] = trim($_POST['pageurl']);
	if(empty($_POST['perpage'])) $_POST['perpage'] = 20;
	$blockcodearr[] = 'perpage/'.$_POST['perpage'];
}

$_POST['cachetime'] = intval($_POST['cachetime']);
if(empty($_POST['perpage']) && !empty($_POST['cachetime'])) {
	$blockcodearr[] = 'cachetime/'.$_POST['cachetime'];
}

if(empty($_POST['cachename'])) {
	showmessage('block_thread_code_cachename');
}

if(!empty($_POST['cachename'])) {
	$blockcodearr[] = 'cachename/'.rawurlencode($_POST['cachename']);
}
$_POST['subjectlen'] = intval($_POST['subjectlen']);
if(!empty($_POST['subjectlen'])) {
	$blockcodearr[] = 'subjectlen/'.$_POST['subjectlen'];
	if(!empty($_POST['subjectdot'])) {
		$blockcodearr[] = 'subjectdot/'.$_POST['subjectdot'];
	}
}

if($_POST['showdetail']) {
	$blockcodearr[] = 'showdetail/'.$_POST['showdetail'];
	$_POST['descriptionlen'] = intval($_POST['descriptionlen']);
	if(!empty($_POST['descriptionlen'])) {
		$blockcodearr[] = 'descriptionlen/'.$_POST['descriptionlen'];
		if(!empty($_POST['descriptiondot'])) {
			$blockcodearr[] = 'descriptiondot/'.$_POST['descriptiondot'];
		}
	}
}

?>