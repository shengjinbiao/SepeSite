<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_robotmessages.php 13470 2009-11-04 07:19:53Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managemodrobotmsg')) {
	showmessage('no_authority_management_operation');
}

$perpage = 150;
$robotid = postget('robotid');
$isimport = postget('isimport');
empty($_GET['page']) ? $page = 1 : $page = intval($_GET['page']);
($page < 1) ? $page= 1 : '';
$start = ($page - 1) * $perpage;

$listarr = array();
$thevalue = array();
$showvalue = array();

//POST METHOD
if (submitcheck('listsubmit')) {
	//LIST UPDATE
	if(empty($_POST['catid'])) {
		$type = '';
		$catid = 0;
	} else {
		$catarr = explode('_', $_POST['catid']);
		$type = $catarr[0];
		$catid = intval($catarr[1]);
	}
	
	if($_POST['operation'] == 'delete') {
		if(!empty($_POST['item'])) {
			delrobotmsg($_POST['item']);
		}
	} elseif ($_POST['operation'] == 'import' && !empty($catid)) {

		if(empty($_POST['importall'])) {
			if(!empty($_POST['item'])) {
				$itemids = implode('\',\'', $_POST['item']);
				$itemidarr = array();
				//标题
				$query = $_SGLOBAL['db']->query('SELECT i.* FROM '.tname('robotitems').' i WHERE i.itemid IN (\''.$itemids.'\') AND i.isimport=0 ORDER BY i.robottime');
			} else {
				showmessage('robotmessage_op_success', $theurl);
			}
		} else {
			$query = $_SGLOBAL['db']->query('SELECT i.* FROM '.tname('robotitems').' i WHERE i.robotid=\''.$_POST['robotid'].'\' AND i.isimport=0 ORDER BY i.robottime');
		}
		$itemarr = $theitemidarr = array();
		while ($item = $_SGLOBAL['db']->fetch_array($query)) {
			$robotitemid = $theitemidarr[] = $item['itemid'];
			$item = saddslashes($item);
			$hashstr = smd5($_SGLOBAL['supe_uid'].'/'.rand(1000, 9999).$_SGLOBAL['timestamp'].$item['itemid']);
			
			$setsqlarr = array(
				'catid' => $catid,
				'uid' => $item['uid'],
				'username' => $item['username'],
				'type' => $type,
				'subject' => $item['subject'],
				'dateline' => $item['dateline'],
				'lastpost' => $item['dateline'],
				'fromtype' => 'robotpost',
				'fromid' => $item['robotid'],
				'hash' => $hashstr,
				'haveattach' => ($item['haveattach']==1?1:0)
			);

			$itemid = inserttable('spaceitems', $setsqlarr, 1);
			$robotid = $item['robotid'];
			
			$itemidarr[$item['itemid']] = $itemid;
			$itemarr[$item['itemid']] = $item;
			if($item['haveattach']) {
				$_SGLOBAL['db']->query("UPDATE ".tname('attachments')." SET itemid='$itemid', catid='$catid', uid='$item[uid]', hash='$hashstr' WHERE hash='R{$robotid}I{$robotitemid}'");
				//更新图文资讯
				$attvalue = $_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query("SELECT aid FROM ".tname('attachments')." WHERE itemid='$itemid' AND isimage='1' LIMIT 0 ,1"));
				$_SGLOBAL['db']->query("UPDATE ".tname('spaceitems')." SET picid='$attvalue[aid]' WHERE itemid='$itemid'");
			}
			
		}
		
		//内容
		if(empty($theitemidarr)) showmessage('robotmessage_op_success', $theurl);
	
		$itemids = implode('\',\'', $theitemidarr);
		$query = $_SGLOBAL['db']->query('SELECT ii.* FROM '.tname('robotmessages').' ii WHERE ii.itemid IN (\''.$itemids.'\') ORDER BY ii.msgid');
		while ($msg = $_SGLOBAL['db']->fetch_array($query)) {
			$msg = saddslashes($msg);
			if(empty($itemidarr[$msg['itemid']])) continue;
			$setsqlarr = array(
				'itemid' => $itemidarr[$msg['itemid']],
				'message' => $msg['message'],
				'newsauthor' => $itemarr[$msg['itemid']]['author'],
				'newsfrom' => $itemarr[$msg['itemid']]['itemfrom']
			);
			inserttable('spacenews', $setsqlarr);
		}

		//删除
		if($_POST['importdelete']) {
			delrobotmsg($theitemidarr);
		} else {
			$_SGLOBAL['db']->query('UPDATE '.tname('robotitems').' SET catid=\''.$catid.'\', isimport=1 WHERE itemid IN (\''.$itemids.'\')');
		}
	}

	showmessage('robotmessage_op_success', $theurl);

} elseif (submitcheck('thevaluesubmit')) {
	//ONE UPDATE OR ADD
	$itemid = intval($_POST['itemid']);
	if($itemid) {
		$setsqlarr = array(
		);
	}
}

//GET METHOD
$catarr = array();
$view0class = $view1class = '';
if (empty($_GET['op'])) {
	
	$robotarr = getrobot();

	//LIST VIEW
	$wheresqlarr = array();
	$newurl = $theurl;
	if(!empty($robotid)) {
		$wheresqlarr['robotid'] = $robotid;
		$newurl .= '&robotid='.$robotid;
	}

	if($isimport == 1) {
		$catarr = getcategory('news');
		$wheresqlarr['isimport'] = 1;
		$newurl .= '&isimport=1';
		$view1class = ' class="active"';
	} else {
		$wheresqlarr['isimport'] = 0;
		$newurl .= '&isimport=0';
		$view0class = ' class="active"';
	}
	$wheresqlstr = getwheresql($wheresqlarr);
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('robotitems').' WHERE '.$wheresqlstr);
	$listcount = $_SGLOBAL['db']->result($query, 0);
	$multipage = '';
	if($listcount) {
		
		$uplistarr = getcategory();
		
		$plussql = 'ORDER BY robottime LIMIT '.$start.','.$perpage;
		$listarr = selecttable('robotitems', array(), $wheresqlarr, $plussql);
		$multipage = multi($listcount, $perpage, $page, $newurl);
	}
	
} elseif ($_GET['op'] == 'edit') {
	//ONE VIEW FOR UPDATE
	$itemid = intval($_GET['itemid']);
	$query = $_SGLOBAL['db']->query('SELECT msg.*, item.* FROM '.tname('robotitems').' item LEFT JOIN '.tname('robotmessages').' msg ON item.itemid=msg.itemid WHERE item.itemid=\''.$itemid.'\'');
	if($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
	}
	
} elseif ($_GET['op'] == 'add') {
	//ONE ADD
	$thevalue = array(
		'itemid' => 0,
	);

} elseif ($_GET['op'] == 'viewmessage') {
	
	//ONE DELETE
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('robotitems').' WHERE itemid=\''.$_GET['itemid'].'\'');
	if($showvalue = $_SGLOBAL['db']->fetch_array($query)) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('robotmessages').' WHERE itemid=\''.$_GET['itemid'].'\' ORDER BY msgid');
		$showvalue['message'] = array();
		while ($rmsg = $_SGLOBAL['db']->fetch_array($query)) {
			$showvalue['message'][] = $rmsg;
		}
		$robotarr = getrobot();
		$catarr = getcategory('news');
	} else {
		showmessage('robotmessage_none_exists');
	}
}

//MENU
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['rototmessage_title'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$view0class.'><a href="'.$theurl.'" class="view">'.$alang['robotmessage_view_list_0'].'</a></td>
					<td'.$view1class.'><a href="'.$theurl.'&isimport=1" class="view">'.$alang['robotmessage_view_list_1'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';


//ROBOT SHOW
if(!empty($robotarr) && is_array($robotarr)) {
	echo label(array('type'=>'table-start', 'class'=>'listtable'));
	$i = 0;
	foreach ($robotarr as $robotid => $robot) {
		echo '<td><a href="'.$theurl.'&robotid='.$robotid.'&isimport='.$isimport.'">'.$robot['name'].'</a></td>';
		if($i % 5 == 4) echo '</tr><tr>';
		$i++;
	}
	echo '</tr></table>';
	echo '<br>';
}

//LIST SHOW
if(is_array($listarr) && $listarr) {
	
	$adminmenu = $comma = '';
	$adminmenuarr = array(
		'import' => $alang['robotmessage_import'],
		'delete' => $alang['robotmessage_delete']
	);
	foreach ($adminmenuarr as $key => $value) {
		$adminmenu .= $comma.'<input type="radio" name="operation" value="'.$key.'" onClick="jsop(this.value)"> '.$value;
		$comma = '&nbsp;&nbsp;';
	}
	
	$importdeletearr = array(
		'0' => $alang['robotmessage_import_delete_0'],
		'1' => $alang['robotmessage_import_delete_1']
	);
	
	$importallarr = array(
		'1' => $alang['into_a_one_time'],
		'0' => $alang['only_selected_items']
	);

	$robotid = intval(postget('robotid'));
	
	$importcats = label(array('type'=>'select-div', 'alang'=>'robotmessage_import_category', 'name'=>'catid', 'radio'=>1, 'options'=>$uplistarr, 'display'=>'none'));
	$importcats = '';
	$importcats = '<tr id="tr_catid" style="display:none"> <th>选择分类</th> <td><select name="catid" id="catid">';
	foreach ($uplistarr as $key=>$cvalue) {
		if(empty($channels['types'][$key])) continue;
		$importcats .='<optgroup label="'.$channels['types'][$key]['name'].'">';
		foreach ($cvalue as $value) {
			$importcats .= '<option value="'.$key.'_'.$value['catid'].'"'.$checkstr.'>'.$value['pre'].$value['name'].'</option>';
		}
		$importcats .= '</optgroup>';
	}
	$importcats .= '</select></td></tr>';
	if($robotid) $importcats .= label(array('type'=>'radio', 'alang'=> $alang['into_a_one_off'], 'name'=>'importall', 'options'=>$importallarr, 'value'=>'1', 'display'=>'none'));
	$importcats .= label(array('type'=>'radio', 'alang'=>'robotmessage_import_delete', 'name'=>'importdelete', 'options'=>$importdeletearr, 'value'=>'0', 'display'=>'none'));
	
	echo '<script language="javascript">
	<!--
	function jsop(radionvalue) {
		var i1 = document.getElementById(\'tr_catid\');
		var i2 = document.getElementById(\'tr_importdelete\');
		var i3 = document.getElementById(\'tr_importall\');
		if(radionvalue == \'import\') {
			if(i1) i1.style.display = "";
			if(i2) i2.style.display = "";
			if(i3) i3.style.display = "";
		} else {
			if(i1) i1.style.display = "none";
			if(i2) i2.style.display = "none";
			if(i3) i3.style.display = "none";
		}
	}
	//-->
	</script>';
	
	echo label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$newurl));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));

	echo '<tr>';
	echo '<th>'.$alang['robotmessage_select'].'</th>';
	echo '<th>'.$alang['robotmessage_subject'].'</th>';
	echo '<th>'.$alang['robotmessage_catid'].'</th>';
	echo '<th>'.$alang['robotmessage_robotid'].'</th>';
	echo '<th>'.$alang['robotmessage_robottime'].'</th>';
	echo '<th>'.$alang['robotmessage_author'].'</th>';
	echo '</tr>';

	foreach ($listarr as $listvalue) {

		if(!empty($listvalue['haveattach'])) {
			$subjectpre = '<img src="'.S_URL.'/images/base/haveattach.gif" align="absmiddle" alt="'.$alang['admin_func_attachment'].'"> ';
		}
		empty($class) ? $class=' class="darkrow"': $class='';
		echo '<tr'.$class.'>';
		echo '<td><input type="checkbox" name="item[]" value="'.$listvalue['itemid'].'" /></td>';
		echo '<td>'.$subjectpre.'<a href="'.$theurl.'&op=viewmessage&itemid='.$listvalue['itemid'].'">'.$listvalue['subject'].'</a></td>';
		echo '<td>'.getarraykeyname($catarr, $listvalue['catid'], 'name').'</td>';
		echo '<td><a href="'.$theurl.'&robotid='.$listvalue['robotid'].'&isimport='.$isimport.'">'.getarraykeyname($robotarr, $listvalue['robotid'], 'name').'</a></td>';
		echo '<td>'.sgmdate($listvalue['robottime']).'</td>';
		echo '<td>'.$listvalue['author'].'</td>';
		echo '</tr>';
	}
	echo label(array('type'=>'table-end'));
	
	echo label(array('type'=>'table-start', 'class'=>'btmtable'));
	echo '<tr><th width="12%">'.$alang['space_batch_op'].'</th><th><input type="checkbox" name="chkall" onclick="checkall(this.form, \'item\')">'.$alang['space_select_all'].' '.$adminmenu.'</th></tr>';	
	echo $importcats;
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
	echo '<input name="robotid" type="hidden" value="'.$robotid.'" />';
	echo label(array('type'=>'form-end'));
}

//THE MESSAGE SHOW
if(is_array($showvalue) && $showvalue) {
	
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'text', 'alang'=>'robotmessage_subject', 'text'=>$showvalue['subject']));
	echo label(array('type'=>'text', 'alang'=>'robotmessage_catid', 'width'=>'100', 'text'=>getarraykeyname($catarr, $showvalue['catid'], 'name')));
	echo label(array('type'=>'text', 'alang'=>'robotmessage_robotid', 'text'=>'<a href="'.$theurl.'&robotid='.$showvalue['robotid'].'">'.getarraykeyname($robotarr, $showvalue['robotid'], 'name').'</a>'));
	echo label(array('type'=>'text', 'alang'=>'robotmessage_robottime', 'text'=>sgmdate($showvalue['robottime'])));
	echo label(array('type'=>'text', 'alang'=>'robotmessage_title_author', 'text'=>$showvalue['author']));
	echo label(array('type'=>'text', 'alang'=>'robotmessage_title_itemfrom', 'text'=>$showvalue['itemfrom']));
	echo label(array('type'=>'text', 'alang'=>'robotmessage_title_dateline', 'text'=>$showvalue['dateline']));
	echo label(array('type'=>'text', 'alang'=>'robotmessage_title_isimport', 'text'=>$showvalue['isimport']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	foreach ($showvalue['message'] as $showmessage) {
		if(!empty($showmessage['picurls'])) {
			$showmessage['picarr'] = unserialize($showmessage['picurls']);
			$showmessage['picurls'] = implode('<br>', $showmessage['picarr']);
		}
		if(!empty($showmessage['flashurls'])) {
			$showmessage['flasharr'] = unserialize($showmessage['flashurls']);
			$showmessage['flashurls'] = implode('<br>', $showmessage['flasharr']);
		}
		
		echo label(array('type'=>'text', 'alang'=>'robotmessage_title_message', 'text'=>$showmessage['message']));
		echo label(array('type'=>'text', 'alang'=>'robotmessage_title_picurls', 'text'=>$showmessage['picurls']));
		echo label(array('type'=>'text', 'alang'=>'robotmessage_title_flashurls', 'text'=>$showmessage['flashurls']));
	}
	
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
}

function getrobot() {
	global $_SGLOBAL;
	
	$robotarr = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('robots'));
	while ($robot = $_SGLOBAL['db']->fetch_array($query)) {
		$robotarr[$robot['robotid']] = $robot;
	}
	return $robotarr;
}

//返回数组的键名
function getarraykeyname($array, $key, $name) {
	if(isset($array[$key][$name])) {
		return $array[$key][$name];
	} else {
		return '';
	}
}

?>