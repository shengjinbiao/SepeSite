<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	模块语句生成：UCH日志
	$Id: admin_blocks_uchblog_code.inc.php 11594 2009-03-11 05:44:43Z zhanglijun $
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
	if($_POST['setblogid']) {
		//指定aid
		$_POST['blogid'] = getdotstring($_POST['blogid'], 'int');
		if(empty($_POST['blogid'])) {
			showmessage('block_uchblog_code_blogid');
		} else {
			$blockcodearr[] = 'blogid/'.$_POST['blogid'];
		}
	} else {
		
		if(!empty($_POST['picflag'])) {
			$blockcodearr[] = 'picflag/'.$_POST['picflag'];
		}
				
		$_POST['uid'] = getdotstring($_POST['uid'], 'int');
		if(!empty($_POST['uid'])) {
			$blockcodearr[] = 'uid/'.$_POST['uid'];
		}
		
		if(!empty($_POST['dateline'])) $blockcodearr[] = 'dateline/'.$_POST['dateline'];

		$scopestring = getscopestring('viewnum', $_POST['viewnum']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('replaynum', $_POST['replaynum']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('tracenum', $_POST['tracenum']);
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
	if(!empty($_POST['messagelen'])) {
		$blockcodearr[] = 'messagelen/'.$_POST['messagelen'];
		if(!empty($_POST['messagedot'])) {
			$blockcodearr[] = 'messagedot/'.$_POST['messagedot'];
		}
	}
}

?>