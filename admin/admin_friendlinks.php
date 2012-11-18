<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_friendlinks.php 11192 2009-02-25 01:45:53Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managefriendlinks')) {
	showmessage('no_authority_management_operation');
}

$perpage = 20;
$linkid = intval(postget('linkid'));
$page = intval(postget('page'));
($page < 1 ) ? $page = 1 : '';
$start = ($page-1) * $perpage;

$listarr = array();
$thevalue = array();
$displayorderarr = array();	//批量修改显示顺序

//SUBMIT
if(submitcheck('listsubmit')) {
	if(!empty($_POST['displayorderarr'])) {
		foreach($_POST['displayorderarr'] as $key=>$value) {
			if(empty($_POST['linkidarr']) || !in_array($key, $_POST['linkidarr'])){
				$_SGLOBAL['db']->query('UPDATE '.tname('friendlinks')." SET displayorder='$value' WHERE id='$key'");
			}
		}
	}

	if(!empty($_POST['linkidarr'])) {
		$linkidstr = implode('\',\'', $_POST['linkidarr']);
		$_SGLOBAL['db']->query('DELETE FROM '.tname('friendlinks').' WHERE id IN (\''.$linkidstr.'\')');
	}
	showmessage('links_op_success', $theurl);
} elseif (submitcheck('thevaluesubmit')){
	if(strlen($_POST['name']) < 2){
		showmessage('link_check_name');
	} else {
		$_POST['name'] = shtmlspecialchars($_POST['name']);
	}

	if(empty($_POST['url']) || strrpos($_POST['url'], '://') === false) {
		showmessage('link_check_url');
	} else {
		$_POST['url'] = shtmlspecialchars($_POST['url']);
	}
	$_POST['displayorder'] = intval($_POST['displayorder']);
	$_POST['description'] = shtmlspecialchars(cutstr($_POST['description'],'100'));
	$_POST['logo'] = empty($_POST['logo']) ? '' : shtmlspecialchars($_POST['logo']);
	$thevaluesqlarr = array(
		'name' => $_POST['name'],
		'url' => $_POST['url'],
		'displayorder' => $_POST['displayorder'],
		'description' => $_POST['description'],
		'logo' => $_POST['logo']
		);
	if(empty($_POST['linkid'])) {
		inserttable('friendlinks', $thevaluesqlarr);
		showmessage('link_add_success', $theurl);
	} else {
		updatetable('friendlinks', $thevaluesqlarr, array('id'=>$linkid));
		showmessage('link_update_success', $theurl);
	}
}

$addclass = $viewclass = '';
$_GET['op'] = empty($_GET['op'])?'':$_GET['op'];

//OP
if(empty($_GET['op'])) {
	$viewclass = ' class="active"';
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('friendlinks'));
	$listcount = $_SGLOBAL['db']->result($query, 0);
	$multipage = '';
	if($listcount) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('friendlinks').' ORDER BY displayorder LIMIT '.$start.','.$perpage);
		while ($link = $_SGLOBAL['db']->fetch_array($query)) {
			$listarr[] = $link;
		}
		$multipage = multi($listcount, $perpage, $page, $theurl);
	}
} elseif ($_GET['op'] == 'add'){
	$addclass = ' class="active"';
	$thevalue = array('linkid'=>'', 'displayorder'=>'0', 'name'=>'', 'url'=>'', 'description'=>'', 'logo'=>'');
} elseif ($_GET['op'] == 'edit'){
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('friendlinks').' WHERE id=\''.$linkid.'\'');
	$thevalue = $_SGLOBAL['db']->fetch_array($query);
}

//HEAD
print<<<END
	<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
	<td><h1>$alang[xs_friendlinks]</h1></td>
	<td class="actions">
		<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
		<tr>
		<td$viewclass><a href="$theurl" class="view">$alang[link_title_view]</a></td>
		<td$addclass><a href="$theurl&op=add" class="add">$alang[link_title_add]</a></td>
		</tr>
		</table>
	</td>
	</tr>
	</table>
	<table cellspacing="2" cellpadding="2" class="helptable"><tr><td>$alang[help_link_view]</td></tr></table>
END;

//LIST
if(!empty($listarr)) {
	$formhash = formhash();
	print<<<END
	<form method="post" action="$theurl" name="thevalueform" enctype="multipart/form-data" onSubmit="return listsubmitconfirm(this)">
	<input type="hidden" name="formhash" value="$formhash">
	<table cellspacing="0" cellpadding="0" width="100%"  class="listtable">
	<tr>
	<th>$alang[link_del]</th>
	<th>$alang[link_displayorder]</th>
	<th>$alang[link_name]</th>
	<th>$alang[link_logo]</th>
	<th>$alang[link_op]</th>
	</tr>
END;
	foreach($listarr as $value) {
		echo '<tr>';
		echo '<td><input type="checkbox" class="checkbox" name="linkidarr[]" value="'.$value['id'].'"></td>';
		echo '<td align="center"><input type="text" name="displayorderarr['.$value['id'].']" size="5" value="'.$value['displayorder'].'"></td>';
		echo '<td><a href="'.$value['url'].'" target=_blank>'.$value['name'].'</a><br />'.cutstr($value['description'], '20', '1').'</td>';
		if(!empty($value['logo'])){
		echo '<td align="center"><img style="width:88px;height:31px;" src="'.$value['logo'].'"></td>';
		} else {
			echo '<td align="center"></td>';
		}
		echo '<td align="center"><img src="'.S_URL.'/images/base/icon_edit.gif" align="absmiddle"> <a href="'.$theurl.'&op=edit&linkid='.$value['id'].'">'.$alang['common_edit'].'</a></td>';
		echo '</tr>';
	}
	$adminmenu = '<input name="importdelete" type="radio" value="1" checked /> '.$alang['common_delete'];
	echo '</table>';
	echo '<table cellspacing="0" cellpadding="0" width="100%"  class="btmtable">';
	echo '<tr><th><input type="checkbox" name="chkall" onclick="checkall(this.form, \'linkid\')">'.$alang['space_select_all'].' '.$adminmenu.'</th></tr>';
	echo '</table>';
	if(!empty($multipage)) {
		echo '<table cellspacing="0" cellpadding="0" width="100%"  class="listpage">';
		echo '<tr><td>'.$multipage.'</td></tr></table>';
	}
	echo '<div class="buttons">';
	echo '<input type="submit" name="listsubmit" value="'.$alang['common_submit'].'" class="submit"> ';
	echo '<input type="reset"  value="'.$alang['common_reset'].'">';
	echo '</div>';
	echo '</form>';
}

if(!empty($thevalue)) {
	echo '<form method="post" action="'.$theurl.'" name="thevalueform" enctype="multipart/form-data">';
	echo '<input type="hidden" name="formhash" value="'.formhash().'">';
	echo '<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">';
	echo '<tr id="tr_subject"><th>'.$alang['link_name_about'].'</th><td><input type="text" size="30" name="name" value="'.$thevalue['name'].'"></td>';
	echo '<tr id="tr_subject"><th>'.$alang['link_url_about'].'</th><td><input type="text" size="40" name="url" value="'.$thevalue['url'].'"></td>';
	echo '<tr id="tr_subject"><th>'.$alang['link_logo_about'].'</th><td><input type="text" size="40" name="logo" value="'.$thevalue['logo'].'"></td>';
	echo '<tr id="tr_subject"><th>'.$alang['link_displayorder_about'].'</th><td><input type="text" size="10" name="displayorder" value="'.$thevalue['displayorder'].'"></td>';
	echo '<tr id="tr_subject"><th>'.$alang['link_description_about'].'</th><td><textarea  rows=4 style="width:98%;" name="description" >'.$thevalue['description'].'</textarea></td>';
	echo '</tr>';
	echo '</table>';
	echo '<div class="buttons">';
	if(!empty($linkid)) {
		echo '<input type="hidden" name="linkid" value="'.$linkid.'">';
	}
	echo '<input type="submit" name="thevaluesubmit" value="'.$alang['common_submit'].'" class="submit"> ';
	echo '<input type="reset"  value="'.$alang['common_reset'].'">';
	echo '</div>';
	echo '</form>';
}
?>
