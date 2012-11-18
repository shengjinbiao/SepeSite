<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_spacetag_code.inc.php 13493 2009-11-11 06:15:33Z zhaofei $
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

	//指定tid
	$_POST['tagid'] = getdotstring($_POST['tagid'], 'int');
	if(empty($_POST['tagid'])) {
		showmessage('block_spacetag_code_tagid');
	} else {
		$blockcodearr[] = 'tagid/'.$_POST['tagid'];
	}

	if(!empty($_POST['type'])) {
		$_POST['type'] = getdotstring($_POST['type'], 'char', false, $_SGLOBAL['type'], 0);
		if(!empty($_POST['type'])) {
			$blockcodearr[] = 'type/'.$_POST['type'];
		}
	}
	
	if(!empty($_POST['dateline'])) {
		$blockcodearr[] = 'dateline/'.$_POST['dateline'];
	}

	if(!empty($_POST['haveattach'])) {
		$blockcodearr[] = 'haveattach/1';
	}

	if(!empty($_POST['digest'])) {
		$blockcodearr[] = 'digest/'.implode(',', $_POST['digest']);
	}

	if(!empty($_POST['lastpost'])) {
		$blockcodearr[] = 'lastpost/'.$_POST['lastpost'];
	}

	$_POST['uid'] = getdotstring($_POST['uid'], 'int');
	if(!empty($_POST['uid'])) {
		$blockcodearr[] = 'uid/'.$_POST['uid'];
	}

	$scopestring = getscopestring('viewnum', $_POST['viewnum']);
	if($scopestring) $blockcodearr[] = $scopestring;

	$scopestring = getscopestring('replynum', $_POST['replynum']);
	if($scopestring) $blockcodearr[] = $scopestring;

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