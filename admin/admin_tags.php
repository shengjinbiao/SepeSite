<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_tags.php 11150 2009-02-20 01:35:59Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//х╗оч
if(!checkperm('managetags')) {
	showmessage('no_authority_management_operation');
}

$perpage = 30;

$_SGET['searchkey'] = trim(postget('searchkey'));
$_SGET['searchtype'] = trim(postget('searchtype'));
$_SGET['close'] = intval(postget('close'));
$_SGET['order'] = trim(postget('order'));
$_SGET['sc'] = trim(postget('sc'));
if(!in_array($_SGET['searchtype'], array('tagname', 'uid', 'tagid', 'username'))) {
	$_SGET['searchtype'] = '';
}
if(!in_array($_SGET['order'], array('dateline', 'uid', 'spacenewsnum'))) {
	$_SGET['order'] = '';
}
if(!in_array($_SGET['sc'], array('ASC', 'DESC'))) {
	$_SGET['sc'] = 'DESC';
}

$urlplus = '&searchkey='.rawurlencode($_SGET['searchkey']).'&searchtype='.$_SGET['searchtype'].'&close='.$_SGET['close'].'&order='.$_SGET['order'].'&sc='.$_SGET['sc'];
$newurl = $theurl.$urlplus;

$_SGET['searchkey'] = stripsearchkey($_SGET['searchkey']);

$page = intval(postget('page'));
($page<1)?$page=1:'';
$start = ($page-1)*$perpage;

$listarr = array();
$thevalue = array();

//POST METHOD
if (submitcheck('listsubmitok')) {
	
	if(empty($_POST['item'])) {
		showmessage('tag_no_item');
	}
	if($_POST['operation'] == 'close') {
		if($_POST['opclose'] == 1) {
			$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET close=1,spacenewsnum=0 WHERE tagid IN (\''.implode('\',\'', $_POST['item']).'\')');
			$_SGLOBAL['db']->query('DELETE FROM '.tname('spacetags').' WHERE tagid IN (\''.implode('\',\'', $_POST['item']).'\')');
		} else {
			$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET close=0 WHERE tagid IN (\''.implode('\',\'', $_POST['item']).'\')');
		}
	} elseif ($_POST['operation'] == 'merge') {
		include_once(S_ROOT.'./function/item.func.php');
		$_POST['taget'] = posttagcheck($_POST['taget']);
		if(empty($_POST['taget'])) showmessage('tag_tagname_error');
		
		//TAG
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('tags').' WHERE tagname=\''.$_POST['taget'].'\'');
		if($totag = $_SGLOBAL['db']->fetch_array($query)) {
			$totagid = $totag['tagid'];
		} else {
			$totagid = $_POST['item'][0];
			$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET tagname=\''.$_POST['taget'].'\' WHERE tagid=\''.$totagid.'\'');
		}

		$tagidarr = array();
		foreach ($_POST['item'] as $thetagid) {
			if($thetagid != $totagid) {
				$tagidarr[] = $thetagid;
			}
		}

		if(!empty($tagidarr)) {
			$tagidstr = implode('\',\'', $tagidarr);
			//ITEM NUM
			$spacenum = array('spacenewsnum'=>0);
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('tags').' WHERE tagid IN (\''.$tagidstr.'\')');
			while ($thetag = $_SGLOBAL['db']->fetch_array($query)) {
				$spacenum['spacenewsnum'] = $spacenum['spacenewsnum']+$thetag['spacenewsnum'];
			}
			
			//spacetags
			$itemidarr = array();
			$query = $_SGLOBAL['db']->query('SELECT itemid FROM '.tname('spacetags').' WHERE tagid=\''.$totagid.'\'');
			while ($itemid = $_SGLOBAL['db']->fetch_array($query)) {
				$itemidarr[] = $itemid['itemid'];
			}
			if(!empty($itemidarr)) {
				$_SGLOBAL['db']->query('DELETE FROM '.tname('spacetags').' WHERE tagid IN (\''.$tagidstr.'\') AND itemid IN (\''.implode('\',\'', $itemidarr).'\')');
			}
			$_SGLOBAL['db']->query('UPDATE '.tname('spacetags').' SET tagid=\''.$totagid.'\' WHERE tagid IN (\''.$tagidstr.'\')');
			
			//tags
			$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET spacenewsnum=spacenewsnum+'.$spacenum['spacenewsnum'].' WHERE tagid=\''.$totagid.'\'');
			$_SGLOBAL['db']->query('DELETE FROM '.tname('tags').' WHERE tagid IN (\''.$tagidstr.'\')');
		}
	} elseif($_POST['operation'] == 'delete') {
		$tagidstr = implode('\',\'', $_POST['item']);
		$_SGLOBAL['db']->query('DELETE FROM '.tname('spacetags').' WHERE tagid IN (\''.$tagidstr.'\')');
		$_SGLOBAL['db']->query('DELETE FROM '.tname('tags').' WHERE tagid IN (\''.$tagidstr.'\')');
	}
	showmessage('tag_batch_op_success', $newurl);
	
} elseif (submitcheck('valuesubmit')) {
	//ONE UPDATE OR ADD
	include_once(S_ROOT.'./function/item.func.php');
	$_POST['newmaintagname'] = posttagcheck($_POST['newmaintagname']);
	if(empty($_POST['newmaintagname'])) showmessage('tag_tagname_error');
	
	$thetagid = 0;
	$setsqlarr = array();
	
	//TAG NAME
	if($_POST['newmaintagname'] != $_POST['maintagname']) {
		if(strtolower($_POST['newmaintagname']) == strtolower($_POST['maintagname'])) {
			$thetagid = $_POST['tagid'];
			$setsqlarr['tagname'] = $_POST['newmaintagname'];
		} else {
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('tags').' WHERE tagname=\''.$_POST['newmaintagname'].'\'');
			if($thetag = $_SGLOBAL['db']->fetch_array($query)) {
				$thetagid = $thetag['tagid'];
				
				
				$itemidarr = array();
				$query = $_SGLOBAL['db']->query('SELECT itemid FROM '.tname('spacetags').' WHERE tagid=\''.$thetagid.'\'');
				while ($itemid = $_SGLOBAL['db']->fetch_array($query)) {
					$itemidarr[] = $itemid['itemid'];
				}
				if(!empty($itemidarr)) {
					$_SGLOBAL['db']->query('DELETE FROM '.tname('spacetags').' WHERE tagid = \''.$_POST['tagid'].'\' AND itemid IN (\''.implode('\',\'', $itemidarr).'\')');
				}
							
				$_SGLOBAL['db']->query('UPDATE '.tname('spacetags').' SET tagid='.$thetag['tagid'].' WHERE tagid=\''.$_POST['tagid'].'\'');
				$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET spacenewsnum=spacenewsnum+'.$_POST['spacenewsnum'].' WHERE tagid=\''.$thetag['tagid'].'\'');
				$_SGLOBAL['db']->query('DELETE FROM '.tname('tags').' WHERE tagid=\''.$_POST['tagid'].'\'');
			} else {
				$thetagid = $_POST['tagid'];
				$setsqlarr['tagname'] = $_POST['newmaintagname'];
			}
		}
	} else {
		$thetagid = $_POST['tagid'];
	}
	
	//RELATIVE TAGS
	if(!empty($_POST['tagname'])) {
		$setsqlarr['relativetags'] = implode("\t", $_POST['tagname']);
	} else {
		$setsqlarr['relativetags'] = '';
	}
	if(!empty($setsqlarr)) updatetable('tags', $setsqlarr, array('tagid'=>$thetagid));

	showmessage('tag_update_success', $newurl);

}

//GET METHOD
$viewclass = '';
if (empty($_GET['op'])) {
	
	$wheresqlarr = array();
	if(empty($_SGET['searchkey'])) {
		if(empty($_GET['close']) && empty($_POST['close'])) {
			$wheresqlarr[] = '1';
			$_SGET['close'] = '';
		} else {
			$wheresqlarr[] = 'close=\''.$_SGET['close'].'\'';
		}
	} else {
		$searchkey = $_SGET['searchkey'];
		switch ($_SGET['searchtype']) {
			case 'tagname':
				$wheresqlarr[] = 'tagname LIKE \''.$searchkey.'\'';
				break;
			case 'tagid':
				$wheresqlarr[] = 'tagid = \''.$searchkey.'\'';
				break;
			case 'username':
				$wheresqlarr[] = 'username LIKE \''.$searchkey.'\'';
				break;
			case 'uid':
				$wheresqlarr[] = 'uid = \''.$searchkey.'\'';
				break;
			default:
				$wheresqlarr[] = 'tagname LIKE \''.$searchkey.'\'';
				break;
		}
	}
	$wheresqlstr = implode(' OR ', $wheresqlarr);
	
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('tags').' WHERE '.$wheresqlstr);
	$listcount = $_SGLOBAL['db']->result($query, 0);
	$multipage = '';
	if($listcount) {
		if(!empty($_SGET['order'])) {
			$order = $_SGET['order'].' '.$_SGET['sc'];
		} else {
			$order = 'dateline DESC';
		}
		$plussql = 'ORDER BY '.$order.' LIMIT '.$start.','.$perpage;
		$listarr = selecttable('tags', array(), $wheresqlstr, $plussql);
		$multipage = multi($listcount, $perpage, $page, $newurl);
	}
	$viewclass = ' class="active"';
	
} elseif ($_GET['op'] == 'edit') {

	//ONE VIEW FOR UPDATE
	$_GET['tagid'] = intval($_GET['tagid']);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('tags').' WHERE tagid=\''.$_GET['tagid'].'\'');
	if($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
		$thevalue['relativetags'] = implode(',', explode("\t", $thevalue['relativetags']));
	} else {
		showmessage('tag_no_tagid');
	}
	
}

//MENU
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>TAG</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass.'><a href="'.$theurl.'" class="view">'.$alang['tag_view_list'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';

//LIST SHOW
if(is_array($listarr) && $listarr) {

	//FILTER
	$searcharr = array(
		'tagname' => $alang['tag_search_tagname'],
		'tagid' => $alang['tag_search_tagid'],
		'username' => $alang['tag_search_username'],
		'uid' => $alang['tag_search_uid']
	);
	$closearr = array(
		'0' => $alang['tag_close_0'],
		'1' => $alang['tag_close_1']
	);
	$orderarr = array(
		'' => $alang['space_order_default'],
		'dateline' => $alang['tag_order_dateline'],
		'uid' => $alang['tag_order_uid'],
		'spacenewsnum' => $alang['tag_order_spacenewsnum'],
	);
	$scarr = array(
		'DESC' => $alang['space_sc_desc'],
		'ASC' => $alang['space_sc_asc']
	);
	
	echo label(array('type'=>'form-start', 'name'=>'searchform', 'action'=>$theurl));
	echo label(array('type'=>'help', 'text'=>$alang['help_tag']));
	echo label(array('type'=>'table-start', 'class'=>'toptable'));
	echo '<tr><td>';
	echo ' '.$alang['tag_search'].': '.getselectstr('searchtype', $searcharr).' <input type="text" name="searchkey" size="20" value="" /> ';
	echo ' '.$alang['tag_close'].': '.getselectstr('close', $closearr);
	echo ' '.$alang['tag_order'].': '.getselectstr('order', $orderarr).' '.getselectstr('sc', $scarr);
	echo ' <input type="submit" name="viewsubmit" value="'.$alang['tag_view_submit'].'" />';
	echo '</td></tr>';
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'form-end'));
		
	$adminmenuarr = array(
		'noop' => $alang['space_no_op'],
		'close' => $alang['tag_op_close'],
		'merge' => $alang['tag_op_merge'],
		'delete' => $alang['poll_delete']
	);
	$adminmenu = $alang['space_batch_op'].'</th><th>';
	$adminmenu .= '<input type="checkbox" name="chkall" onclick="checkall(this.form, \'item\')">'.$alang['space_select_all'];
	foreach ($adminmenuarr as $key => $value) {
		if($key == 'noop') {
			$acheck = ' checked';
		} else {
			$acheck = '';
		}
		$adminmenu .= '<input type="radio" name="operation" value="'.$key.'" onClick="jsop(this.value)"'.$acheck.'> '.$value;
	}
	
	$admintbl['noop'] = '<tr id="divnoop" style="display:none"><td></td><td></td></tr>';
	$admintbl['close'] = label(array('type'=>'radio', 'alang'=>'tag_close_exlain', 'name'=>'opclose', 'id'=>'divclose', 'options'=>$closearr, 'value'=>'1', 'display'=>'none'));
	$admintbl['merge'] = label(array('type'=>'input', 'alang'=>'tag_target', 'name'=>'taget', 'id'=>'divmerge', 'display'=>'none'));
	$admintbl['delete'] = '<tr id="divdelete" style="display:none"><td></td><td></td></tr>';
	
	$htmlarr = array();
	$htmlarr['js'] = '
	<script language="javascript">
	<!--
	function jsop(radionvalue) {'."\n";
	foreach ($adminmenuarr as $adminkey => $adminvalue) {
		$htmlarr['js'] .= 'document.getElementById(\'div'.$adminkey.'\').style.display = "none";'."\n";
	}
	$htmlarr['js'] .= '
	document.getElementById(\'div\'+radionvalue).style.display = "";
	}
	//-->
	</script>
	';
	
	$htmlarr['html'] = '<tr><th width="12%">'.$adminmenu.'</th></tr>';
	foreach ($adminmenuarr as $adminkey => $adminvalue) {
		$htmlarr['html'] .= $admintbl[$adminkey];
	}
	
	echo $htmlarr['js'];
	echo label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$theurl));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));

	echo '<tr>';
	echo '<th width="50">'.$alang['space_select'].'</th>';
	echo '<th>'.$alang['tag_tagname'].'/'.$alang['tag_relativetags'].'</th>';
	echo '<th width="80">'.$alang['tag_creat_dateline'].'</th>';
	echo '<th width="80">'.$alang['tag_username'].'</th>';
	echo '<th width="70">'.$alang['tag_spacenewsnum'].'</th>';
	echo '<th width="50">'.$alang['tag_close'].'</th>';
	echo '<th width="50">'.$alang['tag_op'].'</th>';
	echo '</tr>';

	foreach ($listarr as $listvalue) {
		empty($class) ? $class=' class="darkrow"': $class='';
		$listvalue['dateline'] = sgmdate($listvalue['dateline'], $_SGLOBAL['member']['dateformat']);
		if($listvalue['close']) {
			$listvalue['tagname'] = '<a href="'.geturl('action/tag/tagid/'.$listvalue['tagid']).'" target="_blank">'.$listvalue['tagname'].'</a>';
		} else {
			$listvalue['tagname'] = '<a href="'.geturl('action/tag/tagid/'.$listvalue['tagid']).'" target="_blank"><b>'.$listvalue['tagname'].'</b></a>';
		}
		echo '<tr'.$class.'>';
		echo '<td><input name="item[]" type="checkbox" value="'.$listvalue['tagid'].'" /></td>';
		echo '<td>'.$listvalue['tagname'].'<p class="relativetags">'.$listvalue['relativetags'].'</p></td>';
		echo '<td>'.$listvalue['dateline'].'</td>';
		echo '<td>'.$listvalue['username'].'</td>';
		echo '<td>'.$listvalue['spacenewsnum'].'</td>';
		echo '<td>'.$alang['tag_close_'.$listvalue['close']].'</td>';
		echo '<td><a href="'.$theurl.'&op=edit&tagid='.$listvalue['tagid'].'">'.$alang['space_edit'].'</a></td>';
		echo '</tr>';
	}
	echo label(array('type'=>'table-end'));
	
	echo label(array('type'=>'table-start', 'class'=>'btmtable'));
	echo $htmlarr['html'];
	echo label(array('type'=>'table-end'));

	if(!empty($multipage)) {
		echo label(array('type'=>'table-start', 'class'=>'listpage'));
		echo '<tr><td>'.$multipage.'</td></tr>';
		echo label(array('type'=>'table-end'));
	}	

	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'listsubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'listreset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo '<input name="listsubmitok" type="hidden" value="yes" />';
	echo label(array('type'=>'form-end'));
}

//THE VALUE SHOW
if(is_array($thevalue) && $thevalue) {

	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$theurl, 'other'=>' onSubmit="return validate(this)"'));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'input', 'alang'=>'tag_title_tagname', 'name'=>'newmaintagname', 'size'=>20, 'width'=>'30%', 'value'=>$thevalue['tagname']));
	echo label(array('type'=>'tag', 'alang'=>'tag_title_relativetags', 'values'=>$thevalue['relativetags']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo '<input name="tagid" type="hidden" value="'.$thevalue['tagid'].'" />';
	echo '<input name="valuesubmit" type="hidden" value="yes" />';
	echo '<input name="maintagname" type="hidden" value="'.shtmlspecialchars($thevalue['tagname']).'" />';
	echo '<input name="spacenewsnum" type="hidden" value="'.$thevalue['spacenewsnum'].'" />';
	echo label(array('type'=>'form-end'));

}

?>