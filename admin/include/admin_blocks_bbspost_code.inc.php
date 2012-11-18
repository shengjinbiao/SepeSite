<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_bbspost_code.inc.php 11157 2009-02-20 08:31:58Z zhaolei $
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
	if($_POST['setpid']) {
		//指定tid
		$_POST['pid'] = getdotstring($_POST['pid'], 'int');
		if(empty($_POST['pid'])) {
			showmessage('block_post_code_pid');
		} else {
			$blockcodearr[] = 'pid/'.$_POST['pid'];
		}
	} else {

		if(!empty($_POST['fid'])) {
			$blockcodearr[] = 'fid/'.implode(',' ,$_POST['fid']);
		}

		$_POST['tid'] = getdotstring($_POST['tid'], 'int');
		if(!empty($_POST['tid'])) {
			$blockcodearr[] = 'tid/'.$_POST['tid'];
		}

		if($_POST['first']) {
			$blockcodearr[] = 'first/1';
		}

		if($_POST['attachment']) {
			$blockcodearr[] = 'attachment/1';
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

$_POST['subjectlen'] = intval($_POST['subjectlen']);
if(!empty($_POST['subjectlen'])) {
	$blockcodearr[] = 'subjectlen/'.$_POST['subjectlen'];
	if(!empty($_POST['subjectdot'])) {
		$blockcodearr[] = 'subjectdot/'.$_POST['subjectdot'];
	}
}

$_POST['messagelen'] = intval($_POST['messagelen']);
if(!empty($_POST['messagelen'])) {
	$blockcodearr[] = 'messagelen/'.$_POST['messagelen'];
	if(!empty($_POST['messagedot'])) {
		$blockcodearr[] = 'messagedot/'.$_POST['messagedot'];
	}
}

if($_POST['tpl'] == 'data' && empty($_POST['cachename'])) {
	showmessage('block_thread_code_cachename');
}
if(!empty($_POST['cachename'])) {
	$blockcodearr[] = 'cachename/'.rawurlencode($_POST['cachename']);
}

$tplok = false;
if(!empty($_POST['tplname'])) $_POST['tpl'] = $_POST['tplname'];
if(!empty($_POST['tpl'])) {
	$tplfilepath = S_ROOT.'./styles/'.$_POST['tpl'].'.html.php';
	if(file_exists($tplfilepath)) {
		$tplok = true;
		$blockcodearr[] = 'tpl/'.rawurlencode($_POST['tpl']);
	}
}
if (!$tplok) {
	showmessage('block_thread_code_tpl');
}
?>