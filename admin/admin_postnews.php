<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_css.php 11150 2009-02-20 01:35:59Z zhaolei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//х╗оч
if(!checkperm('managepostnews')) {
	showmessage('no_authority_management_operation');
}

$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
$op = empty($_GET['op']) ? '' : trim($_GET['op']);

if(submitcheck('postvaluesubmit')) {
	$setarr = array();
	$datarr = array();
	$setarr['settype'] = 'fromss';
	$setid = empty($_POST['setid']) ? 0 :intval($_POST['setid']);
	$_POST['posttype'] = trim($_POST['posttype']);
	$datarr['setlive'] = intval($_POST['setlive']);
	if(empty($_POST['setname'])) {
		showmessage('set_info_is_empty');
	}
	if($_POST['sethave']) {
		if($_POST['posttype'] == 'uchome') {
			if(uchome_exists()) {
				$datarr['setdbhost'] = $_SC['dbhost_uch'];
				$datarr['setdbname'] = $_SC['dbname_uch'];
				$datarr['setdbuser'] = $_SC['dbuser_uch'];
				$datarr['setdbpwd'] = $_SC['dbpw_uch'];
				$datarr['setdbpre'] = $_SC['tablepre_uch'];
				$datarr['setdbpconnect'] = $_SC['pconnect_uch'];
			} else {
				showmessage('not_uchome_exists');
			}
		} elseif ($_POST['posttype'] == 'bbs') {
			if(discuz_exists()) {
				$datarr['setdbhost'] = $_SC['dbhost_bbs'];
				$datarr['setdbname'] = $_SC['dbname_bbs'];
				$datarr['setdbuser'] = $_SC['dbuser_bbs'];
				$datarr['setdbpwd'] = $_SC['dbpw_bbs'];
				$datarr['setdbpre'] = $_SC['tablepre_bbs'];
				$datarr['setdbpconnect'] = $_SC['pconnect_bbs'];
			} else {
				showmessage('not_discuz_exists');
			}
		}
	}else {
		$_POST['setdbhost'] = trim($_POST['setdbhost']);
		$_POST['setdbname'] = trim($_POST['setdbname']);
		$_POST['setdbuser'] = trim($_POST['setdbuser']);
		$_POST['setdbpwd'] = trim($_POST['setdbpwd']);
		$_POST['setdbpre'] = trim($_POST['setdbpre']);
		$_POST['setdbpconnect'] = intval($_POST['setdbpconnect']);
		if(empty($_POST['setdbhost'])) {
			showmessage('set_info_is_empty');
		}
		if($link = @mysql_connect($_POST['setdbhost'], $_POST['setdbuser'], $_POST['setdbpwd'])) {
			
			if($_POST['posttype'] == 'bbs') {
				$tab = $_POST['setdbpre'].'members';
			} elseif ($_POST['posttype'] == 'uchome') {
				$tab = $_POST['setdbpre'].'member';
			}
	
			$query = @mysql_query('SELECT * FROM '.'`'.$_POST['setdbname'].'`.'.$tab.' LIMIT 1',$link);
			$data = mysql_fetch_array($query);
			if(empty($data)) {
				showmessage('dbname_error', CPURL.'?action=postnews');
			}
		} else {
			showmessage('db_error', CPURL.'?action=postnews');
		}
		$datarr = array_merge($datarr, array('setdbhost' => $_POST['setdbhost'],
						  'setdbname' => $_POST['setdbname'],
						  'setdbuser' => $_POST['setdbuser'],
						  'setdbpwd' => $_POST['setdbpwd'],
						  'setdbpre' => $_POST['setdbpre'],
						  'setdbpconnect' => $_POST['setdbpconnect'],
						  'posttype'=> $_POST['posttype']));
	}
	
	$_POST['setcatid'] = intval($_POST['setcatid']);

	if($_POST['posttype'] == 'uchome') {
		$datarr['setctype'] = empty($_POST['setctype']) ? 'blog' : trim($_POST['setctype']);
	} 
	
	$setarr['setname'] =trim($_POST['setname']);
	$setarr['settype'] =trim($_POST['settype']);
	
	$datarr['posttype'] = $_POST['posttype'];
	$setarr['setting'] =serialize($datarr);
	
	if(empty($setid)) {
		inserttable('postset', $setarr);
		$setid = $_SGLOBAL['db']->insert_id();
	} else {
		updatetable('postset', $setarr, array('setid'=>$setid));
	}
			 
	postnews_cache();
	
	showmessage('add_set_success', CPURL.'?action=postnews', 1);
} elseif(submitcheck('posttosssubmit')) {
	$setarr = array();
	$setid = empty($_POST['setid']) ? 0 :intval($_POST['setid']);
	$setarr['setname'] = trim($_POST['setname']);
	
	if(empty($setarr['setname'])) {
		showmessage('set_info_is_empty');
	}
	$setarr['settype'] = 'toss';
	$idarr = array();
	$idarr['seticon'] = trim($_POST['seticon']);
	$idarr['setlive'] = intval($_POST['setlive']);
	$idarr['subject_id'] = trim($_POST['subject_id']);
	$idarr['message_id'] = trim($_POST['message_id']);
	$setarr['setting'] = serialize($idarr);

	if(empty($setid)) {
		inserttable('postset', $setarr);
		$setid = $_SGLOBAL['db']->insert_id();
	} else {
		updatetable('postset', $setarr, array('setid'=>$setid));
	}

	postnews_cache();
	
	showmessage('add_set_success', CPURL.'?action=postnews&op=getcode&id='.$setid, 1);
} elseif (submitcheck('deletepostsubmit')) {
	if(empty($_POST['setid'])) {
		showmessage('no_setid_select');
	}
	
	$delarr = array();
	foreach ($_POST['setid'] as $value) {
		$delarr[] = intval($value);
	}
	$delsql = simplode($delarr);

	$_SGLOBAL['db']->query('DELETE FROM '.tname('postset').' WHERE setid IN('.$delsql.')');
	
	showmessage('delete_set_sucess', CPURL.'?action=postnews', 1);
}

if(empty($op)) {
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('postset'));
	$list = array();
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}
} elseif($op == 'getcode' || $op == 'edit') {
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('postset')." WHERE setid='$id'");
	$set = $_SGLOBAL['db']->fetch_array($query);

	$set['setting'] = unserialize($set['setting']);
	if($set['settype'] == 'fromss' && $op == 'getcode') {
		showmessage('this_set_no_code');
	}
	$subject_id = empty($set['setting']['subject_id']) ? '{subject}' : trim($set['setting']['subject_id']);
	$message_id = empty($set['setting']['message_id']) ? '{message}' : trim($set['setting']['message_id']);
	$code = '<script src="'.$siteurl.'/batch.postnews.php?ac='.$set['settype'].'&amp;setid='.$id.'&amp;subject='.$subject_id.'&amp;message='.$message_id.'"></script>';

}

if($op == 'add' || $op == 'edit') {
	$selecttype = array();
	if($set['settype'] == 'fromss') {
		$selecttype[$set['setting']['posttype']] = 'checked="checked"';
	}
		
	if($set['setting']['posttype'] == 'uchome') {
		$setctype = array();
		$setctype[$set['setting']['setctype']] = 'checked="checked"';
	}

	$type = empty($_GET['type']) ? $set['settype'] : trim($_GET['type']);	
}

include template('admin/tpl/postnews.htm', 1);
?>