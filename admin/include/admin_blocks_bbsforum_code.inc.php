<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_bbsforum_code.inc.php 11157 2009-02-20 08:31:58Z zhaolei $
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
	if($_POST['setfid']) {
		//指定tid
		$_POST['fid'] = getdotstring($_POST['fid'], 'int');
		if(empty($_POST['fid'])) {
			showmessage('block_forum_code_fid');
		} else {
			$blockcodearr[] = 'fid/'.$_POST['fid'];
		}
	} else {
		
		if(!empty($_POST['fup'])) {
			$_POST['fup'] = getdotstring($_POST['fup'], 'int', true);
			if($_POST['fup'] != '') $blockcodearr[] = 'fup/'.$_POST['fup'];
		}
		
		if(!empty($_POST['type'])) {
			$blockcodearr[] = 'type/'.implode(',' ,$_POST['type']);
		}
		
		$scopestring = getscopestring('threads', $_POST['threads']);
		if($scopestring) $blockcodearr[] = $scopestring;
		
		$scopestring = getscopestring('posts', $_POST['posts']);
		if($scopestring) $blockcodearr[] = $scopestring;
		
		$scopestring = getscopestring('todayposts', $_POST['todayposts']);
		if($scopestring) $blockcodearr[] = $scopestring;
		
		if($_POST['allowblog']) {
			$blockcodearr[] = 'allowblog/1';
		}
		
		if($_POST['allowtrade']) {
			$blockcodearr[] = 'allowtrade/1';
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

if(!empty($_POST['bbsurltype'])) {
	$_POST['bbsurltype'] = getdotstring($_POST['bbsurltype'], 'char', false, array('bbs', 'site'), 0);
	if(!empty($_POST['bbsurltype'])) {
		$blockcodearr[] = 'bbsurltype/'.$_POST['bbsurltype'];
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