<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_spaceitem.inc.php 11951 2009-04-16 01:34:12Z zhaolei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

$blockcodearr = array();

if(empty($_POST['blockname'])) {
	showmessage('not_exist_channel');
} else {
	$blockcodearr[] = 'type/'.trim($_POST['blockname']);
}

if($_POST['blockmodel'] == '2') {
	if(empty($_POST['sql'])) {
		showmessage('block_thread_code_sql');
	}
	$_POST['sql'] = getblocksql($_POST['sql']);
	$blockcodearr[] = 'sql/'.rawurlencode($_POST['sql']);
} else {
	if($_POST['setitemid']) {
		//Ö¸¶¨itemid
		$_POST['itemid'] = getdotstring($_POST['itemid'], 'int');
		if(empty($_POST['itemid'])) {
			showmessage('block_spaceitem_code_itemid');
		} else {
			$blockcodearr[] = 'itemid/'.$_POST['itemid'];
		}
	} else {
		if(!empty($_POST['catid'])) {
			$blockcodearr[] = 'catid/'.implode(',' ,$_POST['catid']);
		}
		
		$_POST['uid'] = getdotstring($_POST['uid'], 'int');
		if(!empty($_POST['uid'])) {
			$blockcodearr[] = 'uid/'.$_POST['uid'];
		}

		@include_once S_ROOT.'/data/system/click.cache.php';
		$clickgroupids = array_keys($_SGLOBAL['clickgroup']['spaceitems']);

		foreach ($_SGLOBAL['click'] as $key => $kvalue) {
			if(in_array($key, $clickgroupids)) {
				foreach ($kvalue as $value) {
					if(!is_int($value['name'])){
						$scopestring = getscopestring('click_'.$value['clickid'], $_POST['click_'.$value['clickid']]);
						if($scopestring) $blockcodearr[] = $scopestring;
					}
				}	
			}
		}
		
		$scopestring = getscopestring('hot', $_POST['hot']);
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