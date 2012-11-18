<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_bbslink_code.inc.php 11157 2009-02-20 08:31:58Z zhaolei $
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
	if($_POST['setid']) {
		//指定tid
		$_POST['id'] = getdotstring($_POST['id'], 'int');
		if(empty($_POST['id'])) {
			showmessage('block_link_code_tid');
		} else {
			$blockcodearr[] = 'id/'.$_POST['id'];
		}
	} else {
		if($_POST['note']) {
			$blockcodearr[] = 'note/1';
		}
		
		if($_POST['logo']) {
			$blockcodearr[] = 'logo/1';
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

$_POST['namelen'] = intval($_POST['namelen']);
if(!empty($_POST['namelen'])) {
	$blockcodearr[] = 'namelen/'.$_POST['namelen'];
	if(!empty($_POST['namedot'])) {
		$blockcodearr[] = 'namedot/'.$_POST['namedot'];
	}
}

$_POST['notelen'] = intval($_POST['notelen']);
if(!empty($_POST['notelen'])) {
	$blockcodearr[] = 'notelen/'.$_POST['notelen'];
	if(!empty($_POST['notedot'])) {
		$blockcodearr[] = 'notedot/'.$_POST['notedot'];
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