<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	模块语句生成：论坛附件
	$Id: admin_blocks_uchphoto_code.inc.php 11594 2009-03-11 05:44:43Z zhanglijun $
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
	if($_POST['setalbumid']) {
		//指定aid
		$_POST['albumid'] = getdotstring($_POST['albumid'], 'int');
		if(empty($_POST['albumid'])) {
			showmessage('block_uchphoto_code_albumid');
		} else {
			$blockcodearr[] = 'albumid/'.$_POST['albumid'];
		}
	} else {

		if(!empty($_POST['picnum'])) {
			$blockcodearr[] = 'isstar/'.intval($_POST['picnum']);
		}

		if(!empty($_POST['dateline'])) $blockcodearr[] = 'dateline/'.$_POST['dateline'];

		if(!empty($_POST['updatetime'])) $blockcodearr[] = 'updatetime/'.$_POST['updatetime'];

		$_POST['uid'] = getdotstring($_POST['uid'], 'int');
		if(!empty($_POST['uid'])) {
			$blockcodearr[] = 'uid/'.$_POST['uid'];
		}
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