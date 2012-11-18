<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_bbsmember_code.inc.php 10898 2008-12-31 02:58:50Z zhaofei $
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
		//指定tid
		$_POST['uid'] = getdotstring($_POST['uid'], 'int');
		if(empty($_POST['uid'])) {
			showmessage('block_member_code_uid');
		} else {
			$blockcodearr[] = 'uid/'.$_POST['uid'];
		}
	} else {
		
		if(!empty($_POST['groupid'])) {
			$_POST['groupid'] = getdotstring($_POST['groupid'], 'int');
			if(!empty($_POST['groupid'])) $blockcodearr[] = 'groupid/'.$_POST['groupid'];
		}
		
		if(!empty($_POST['regdate'])) $blockcodearr[] = 'regdate/'.$_POST['regdate'];

		if(!empty($_POST['lastvisit'])) $blockcodearr[] = 'lastvisit/'.$_POST['lastvisit'];

		if(!empty($_POST['lastpost'])) $blockcodearr[] = 'lastpost/'.$_POST['lastpost'];

		$scopestring = getscopestring('posts', $_POST['posts']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('digestposts', $_POST['digestposts']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('oltime', $_POST['oltime']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('pageviews', $_POST['pageviews']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('credits', $_POST['credits']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('credits1', $_POST['credits1']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('credits2', $_POST['credits2']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('credits3', $_POST['credits3']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('credits4', $_POST['credits4']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('credits5', $_POST['credits5']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('credits6', $_POST['credits6']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('credits7', $_POST['credits7']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('credits8', $_POST['credits8']);
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

if($_POST['showdetail']) {
	$blockcodearr[] = 'showdetail/'.$_POST['showdetail'];
	$_POST['signaturelen'] = intval($_POST['signaturelen']);
	if(!empty($_POST['signaturelen'])) {
		$blockcodearr[] = 'signaturelen/'.$_POST['signaturelen'];
		if(!empty($_POST['signaturedot'])) {
			$blockcodearr[] = 'signaturedot/'.$_POST['signaturedot'];
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