<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_model_code.inc.php 13493 2009-11-11 06:15:33Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

$blockcodearr = array();

if(empty($_POST['blockname'])) {
	showmessage('not_exist_module');
} else {
	$blockcodearr[] = 'name/'.trim($_POST['blockname']);
}
if($_POST['blockmodel'] == '2') {
	//高级模式
	if(empty($_POST['sql'])) {
		showmessage('block_thread_code_sql');
	}
	$_POST['sql'] = getblocksql($_POST['sql']);
	$blockcodearr[] = 'sql/'.rawurlencode($_POST['sql']);
} else {
	//向导模式
	if($_POST['setitemid']) {
		//指定itemid
		$_POST['itemid'] = getdotstring($_POST['itemid'], 'int');
		if(empty($_POST['itemid'])) {
			showmessage('block_spaceitem_code_itemid');
		} else {
			$blockcodearr[] = 'itemid/'.$_POST['itemid'];
		}
	} else {
		//模型分类
		if(!empty($_POST['catid'])) {									
			$blockcodearr[] = 'catid/'.implode(',' ,$_POST['catid']);
		}
		//审核等级
		if(!empty($_POST['grade'])) {									
			$blockcodearr[] = 'grade/'.implode(',' ,$_POST['grade']);
		}
		//作者ID
		if(!empty($_POST['uid'])) {
			$blockcodearr[] = 'uid/'.$_POST['uid'];
		}
		//发表时间
		if(!empty($_POST['dateline'])) {								
			$blockcodearr[] = 'dateline/'.$_POST['dateline'];
		}
		//最后发表时间
		if(!empty($_POST['lastpost'])) {								
			$blockcodearr[] = 'lastpost/'.$_POST['lastpost'];
		}

		//查看数
		$scopestring = getscopestring('viewnum', $_POST['viewnum']);	
		if($scopestring) $blockcodearr[] = $scopestring;

		//回复数
		$scopestring = getscopestring('replynum', $_POST['replynum']);	
		if($scopestring) $blockcodearr[] = $scopestring;

		$_POST['haveattach'] = getdotstring($_POST['haveattach'], 'int');
		if(!empty($_POST['haveattach'])) {
			$blockcodearr[] = 'haveattach/'.$_POST['haveattach'];
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

if($_POST['showdetail']) {
	$blockcodearr[] = 'showdetail/'.$_POST['showdetail'];
	$_POST['messagelen'] = intval($_POST['messagelen']);
	if(!empty($_POST['messagelen'])) {
		$blockcodearr[] = 'messagelen/'.$_POST['messagelen'];
		if(!empty($_POST['messagedot'])) {
			$blockcodearr[] = 'messagedot/'.$_POST['messagedot'];
		}
	}
}

if(!empty($_POST['showcategory'])) {
	$blockcodearr[] = 'showcategory/1';
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