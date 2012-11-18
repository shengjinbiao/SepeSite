<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_spacecomment_code.inc.php 10898 2008-12-31 02:58:50Z zhaofei $
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
	if($_POST['setcid']) {
		//指定tid
		$_POST['cid'] = getdotstring($_POST['cid'], 'int');
		if(empty($_POST['cid'])) {
			showmessage('block_spacecomment_code_cid');
		} else {
			$blockcodearr[] = 'cid/'.$_POST['cid'];
		}
	} else {
		if(!empty($_POST['type'])) {
			$_POST['type'] = getdotstring($_POST['type'], 'char', false, $_SGLOBAL['type'], 0);
			if(!empty($_POST['type'])) {
				$blockcodearr[] = 'type/'.$_POST['type'];
			}
		}
		$_POST['itemid'] = getdotstring($_POST['itemid'], 'int');
		if(!empty($_POST['itemid'])) {
			$blockcodearr[] = 'itemid/'.$_POST['itemid'];
		}
		$_POST['uid'] = getdotstring($_POST['uid'], 'int');
		if(!empty($_POST['uid'])) {
			$blockcodearr[] = 'uid/'.$_POST['uid'];
		}
		$_POST['authorid'] = getdotstring($_POST['authorid'], 'int');
		if(!empty($_POST['authorid'])) {
			$blockcodearr[] = 'authorid/'.$_POST['authorid'];
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