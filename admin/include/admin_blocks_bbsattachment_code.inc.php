<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_bbsattachment_code.inc.php 11157 2009-02-20 08:31:58Z zhaolei $
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
	if($_POST['setaid']) {
		//指定aid
		$_POST['aid'] = getdotstring($_POST['aid'], 'int');
		if(empty($_POST['aid'])) {
			showmessage('block_attachment_code_aid');
		} else {
			$blockcodearr[] = 'aid/'.$_POST['aid'];
		}
	} else {
		
		if(!empty($_POST['filetype'])) {
			$blockcodearr[] = 'filetype/'.implode(',' ,$_POST['filetype']);
		}
		if(!empty($_POST['dateline'])) $blockcodearr[] = 'dateline/'.$_POST['dateline'];

		$scopestring = getscopestring('readperm', $_POST['readperm']);
		if($scopestring) $blockcodearr[] = $scopestring;
		$scopestring = getscopestring('downloads', $_POST['downloads']);
		if($scopestring) $blockcodearr[] = $scopestring;
				
		if(!empty($_POST['t_fid'])) {
			$blockcodearr[] = 't_fid/'.implode(',' ,$_POST['t_fid']);
		}
		
		if(!empty($_POST['t_typeid'])) {
			$blockcodearr[] = 't_typeid/'.implode(',' ,$_POST['t_typeid']);
		}
		
		$_POST['t_authorid'] = getdotstring($_POST['t_authorid'], 'int');
		if(!empty($_POST['t_authorid'])) {
			$blockcodearr[] = 't_authorid/'.$_POST['authorid'];
		}
		
		if(!empty($_POST['t_dateline'])) $blockcodearr[] = 't_dateline/'.$_POST['t_dateline'];
		if(!empty($_POST['t_lastpost'])) $blockcodearr[] = 't_lastpost/'.$_POST['t_lastpost'];
		
		$scopestring = getscopestring('t_readperm', $_POST['t_readperm']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('t_price', $_POST['t_price']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('t_views', $_POST['t_views']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('t_replies', $_POST['t_replies']);
		if($scopestring) $blockcodearr[] = $scopestring;

		$scopestring = getscopestring('t_rate', $_POST['t_rate']);
		if($scopestring) $blockcodearr[] = $scopestring;
		
		if(!empty($_POST['t_digest'])) {
			$blockcodearr[] = 't_digest/'.implode(',', $_POST['t_digest']);
		}

		if($_POST['t_blog']) {
			$blockcodearr[] = 't_blog/1';
		}

		if($_POST['t_closed']) {
			$blockcodearr[] = 't_closed/1';
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