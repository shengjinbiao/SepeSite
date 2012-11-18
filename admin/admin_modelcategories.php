<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_modelcategories.php 13442 2009-10-26 08:48:59Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//CHECK GET VAR
$_GET['mid'] = postget('mid');
$_GET['mid'] = !empty($_GET['mid']) ? intval($_GET['mid']) : 0;

$resultmodels = array();
if($_GET['mid'] > 0) {
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('models').' WHERE mid = \''.$_GET['mid'].'\'');
	$resultmodels = $_SGLOBAL['db']->fetch_array($query);
	if(empty($resultmodels['mid'])) {
		showmessage('not_exist_module');
	}
}

if(in_array($resultmodels['modelname'], $_SCONFIG['closechannels'])) {
	showmessage('usetype_no_open');
}

$urlplus = '&mid='.$_GET['mid'];
$newurl = $theurl.$urlplus;

//INIT RESULT VAR
$listarr = array();
$thevalue = array();
$delvalue = array();

//POST METHOD
if(submitcheck('listsubmit')) {

	//权限
	if(!(checkperm('managemodcat') || checkperm('manageeditcat'))) {
		showmessage('no_authority_management_operation');
	}

	if(is_array($_POST['displayorder']) && !empty($_POST['displayorder'])) {
		foreach ($_POST['displayorder'] as $postcatid => $postdisplayorder) {
			updatetable('categories', array('displayorder'=>$postdisplayorder), array('catid' => $postcatid));
		}
	}

	updatemodel('modelname', $resultmodels['modelname']);
	showmessage('category_update_success', $newurl);

} elseif(submitcheck('valuesubmit')) {

	//权限
	if(!(checkperm('managemodcat') || checkperm('manageeditcat'))) {
		showmessage('no_authority_management_operation');
	}
	
	$_POST['catid'] = !empty($_POST['catid']) ? intval($_POST['catid']) : 0;

	$_POST['name'] = trim($_POST['name']);
	if(strlen($_POST['name']) < 2 || strlen($_POST['name']) > 50) {
		showmessage('category_size_error');
	}

	$subcatidarr = array();
	if(!empty($_POST['subcatid'])) $subcatidarr = $_POST['subcatid'];

	$setsqlarr = array(
		'upid' => intval($_POST['upid']),
		'name' => shtmlspecialchars($_POST['name']),
		'type' => shtmlspecialchars($resultmodels['modelname']),
		'note' => shtmlspecialchars(trim($_POST['note'])),
		'displayorder' => intval($_POST['displayorder']),
		'url' => trim($_POST['url'])
	);

	if($_POST['catid'] > 0) {
		//UPDATE
		$subcatidarr[] = $_POST['catid'];
		$subcatidarr = array_unique($subcatidarr);
		$setsqlarr['subcatid'] = implode(',', $subcatidarr);
		updatetable('categories', $setsqlarr, array('catid' => $_POST['catid']));

		updatemodel('modelname', $resultmodels['modelname']);
		showmessage('category_update_success', $newurl);
	} else {
		$catid = inserttable('categories', $setsqlarr, 1);
		$subcatidarr[] = $catid;
		$subcatidarr = array_unique($subcatidarr);
		$subcatid = implode(',', $subcatidarr);

		$_SGLOBAL['db']->query('UPDATE '.tname('categories').' SET subcatid=\''.$subcatid.'\' WHERE catid=\''.$catid.'\'');

		updatemodel('modelname', $resultmodels['modelname']);
		showmessage('category_add_success', $newurl);
	}

} elseif(submitcheck('delvaluesubmit')) {

	//权限
	if(!(checkperm('managemodcat') || checkperm('managedelcat'))) {
		showmessage('no_authority_management_operation');
	}

	$catid = intval($_POST['catid']);
	$newcatid = intval($_POST['newcatid']);

	if($newcatid == $catid) {
		showmessage('category_del_catid_error');
	}
	if(empty($newcatid)) {
		$data = $ids = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname($resultmodels['modelname'].'items')." i, ".tname($resultmodels['modelname'].'message')." m WHERE i.catid='$catid' AND i.itemid = m.itemid");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['catid'] = 0;
			$setsqlarr = array(
					'subject'	=>	addslashes($value['subject']),
					'message'	=> addslashes(serialize($value)),
					'dateline'	=>	$_SGLOBAL['timestamp'],
					'mid'	=>	$resultmodels['mid'],
					'folder'	=>	2
			);
			$ids[] = $value['itemid'];
			inserttable('modelfolders', $setsqlarr);
		}
		$ids = simplode($ids);
		$_SGLOBAL['db']->query("DELETE FROM ".tname($resultmodels['modelname'].'items')." WHERE catid='$catid'");
		$_SGLOBAL['db']->query("DELETE FROM ".tname($resultmodels['modelname'].'message')." WHERE itemid IN ($ids)");
		$_SGLOBAL['db']->query('DELETE FROM '.tname('categories').' WHERE catid=\''.$catid.'\'');
	} else {
		$_SGLOBAL['db']->query('DELETE FROM '.tname('categories').' WHERE catid=\''.$catid.'\'');
		$_SGLOBAL['db']->query('UPDATE '.tname($resultmodels['modelname'].'items').' SET catid=\''.$newcatid.'\' WHERE catid=\''.$catid.'\'');
	}

	updatemodel('modelname', $resultmodels['modelname']);
	showmessage('category_delete_success', $newurl);

}

$addclass = $viewclass = '';
if (empty($_GET['op'])) {

	//LIST VIEW
	$listarr = getmodelcategory($resultmodels['modelname']);
	$viewclass = ' class="active"';

} elseif ($_GET['op'] == 'edit') {

	//权限
	if(!(checkperm('managemodcat') || checkperm('manageeditcat'))) {
		showmessage('no_authority_management_operation');
	}

	$_GET['catid'] = intval($_GET['catid']);
	//ONE VIEW FOR UPDATE
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('categories').' WHERE catid=\''.$_GET['catid'].'\' AND `type`=\''.$resultmodels['modelname'].'\'');
	if($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($thevalue['subcatid'])) $thevalue['subcatid'] = $thevalue['catid'];
	} else {
		showmessage('category_catid_no_exists');
	}

} elseif ($_GET['op'] == 'add') {

	//权限
	if(!(checkperm('managemodcat') || checkperm('manageeditcat'))) {
		showmessage('no_authority_management_operation');
	}

	$thevalue = array(
		'catid' => '0',
		'upid' => 0,
		'name' => '',
		'note' => '',
		'displayorder' => '0',
		'url' => '',
		'subcatid' => 0
	);
	if(!empty($_GET['upid'])) {
		$thevalue['upid'] = intval($_GET['upid']);
	}
	$addclass = ' class="active"';

} elseif ($_GET['op'] == 'delete') {
	//权限
	if(!(checkperm('managemodcat') || checkperm('managedelcat'))) {
		showmessage('no_authority_management_operation');
	}
	$_GET['catid'] = intval($_GET['catid']);
	$query = $_SGLOBAL['db']->query('SELECT catid FROM '.tname('categories').' WHERE upid=\''.$_GET['catid'].'\' AND `type`=\''.$resultmodels['modelname'].'\'');
	if($catid = $_SGLOBAL['db']->result($query, 0)) {
		showmessage('category_sub_cat_exists');
	} else {
		$delvalue['catid'] = $_GET['catid'];
	}
}

//MENU
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$resultmodels['modelname'].' ('.$resultmodels['modelalias'].') '.$alang['category_type'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass.'><a href="'.$newurl.'" class="view">'.$alang['category_view_list'].'</a></td>
					<td'.$addclass.'><a href="'.$newurl.'&op=add" class="add">'.$alang['category_add'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';

//LIST SHOW
if(is_array($listarr) && $listarr) {
	echo label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$newurl));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));
	echo '<tr><th>'.$alang['category_title_name'].'</th>';
	echo '<th width="10%">'.$alang['category_displayorder'].'</th>';
	echo '<th width="40%">'.$alang['space_op'].'</th></tr>';
	foreach ($listarr as $listvalue) {
		empty($class) ? $class=' class="darkrow"': $class='';

		echo '<tr'.$class.'><td>';
		echo $listvalue['pre'];
		echo '<b>'.$listvalue['name'].'</b></td>';
		echo '<td align="center"><input name="displayorder['.$listvalue['catid'].']" type="text" id="displayorder['.$listvalue['catid'].']" size="5" maxlength="" value="'.$listvalue['displayorder'].'" /></td>';
		echo '<td align="center">';
		echo '[<a href="'.$newurl.'&op=add&upid='.$listvalue['catid'].'">'.$alang['category_add_sub'].'</a>] &nbsp; ';
		echo '[<a href="'.$newurl.'&op=edit&catid='.$listvalue['catid'].'">'.$alang['common_edit'].'</a>] &nbsp; ';
		echo '[<a href="'.$newurl.'&op=delete&catid='.$listvalue['catid'].'">'.$alang['merger_deletion'].'</a>] &nbsp; ';
		echo '</td>';
		echo '</tr>';
	}
	echo label(array('type'=>'table-end'));
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'listsubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'listreset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo label(array('type'=>'form-end'));
}

if(!empty($delvalue)) {

	$catlistarr = getmodelcategory($resultmodels['modelname']);
	$catlistarr[0] = array('catid' => 0, 'upid' => 0, 'name' => $alang['model_category_delete'], 'pre'=>'');

	echo label(array('type'=>'form-start', 'name'=>'delvalueform', 'action'=>$theurl));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'select-div', 'alang'=>'category_title_newcatid', 'name'=>'newcatid', 'radio'=>'1', 'options'=>$catlistarr, 'value'=>'0'));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	echo label(array('type'=>'eval', 'text'=>'<br><center>'));
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo label(array('type'=>'eval', 'text'=>'</center>'));
	echo '<input name="catid" type="hidden" value="'.$delvalue['catid'].'" />';
	echo '<input name="mid" type="hidden" value="'.$_GET['mid'].'" />';
	echo '<input name="delvaluesubmit" type="hidden" value="yes" />';
	echo label(array('type'=>'form-end'));
}

//THE VALUE SHOW
if(is_array($thevalue) && $thevalue) {

	$stylearr = array();
	$catlistarr = getmodelcategory($resultmodels['modelname']);
	$upcatname = '';
	if(!empty($thevalue['upid']) && !empty($catlistarr[$thevalue['upid']]['name'])) {
		$upcatname = $catlistarr[$thevalue['upid']]['name'];
	} else {
		$thevalue['upid'] = 0;
		$upcatname = $alang['category_root'];
	}

	echo '
	<script language="javascript">
	<!--
	function jsfunction(value) {
		if(value == 0) {
			document.getElementById("tr_upid").style.display="none";
		} else {
			document.getElementById("tr_upid").style.display="";
		}
	}
	function thevalidate(theform) {
		theform.thevaluesubmit.disabled = true;
		return true;
	}
	//-->
	</script>
	';
	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$theurl, 'other'=>' onSubmit="return thevalidate(this)"'));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'help', 'text'=>$alang['model_help_categories_add']));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'text', 'alang'=>'category_title_upid', 'text'=>'<strong>'.$upcatname.'</strong>'));
	echo label(array('type'=>'input', 'alang'=>'category_title_name', 'name'=>'name', 'size'=>'30', 'width'=>'30%', 'value'=>$thevalue['name']));

	echo label(array('type'=>'input', 'alang'=>'category_title_url', 'name'=>'url', 'size'=>'30', 'value'=>$thevalue['url']));
	echo label(array('type'=>'select-div', 'alang'=>'category_title_subcatid', 'name'=>'subcatid', 'options'=>$catlistarr, 'value'=>$thevalue['subcatid']));
	echo label(array('type'=>'table-end'));

	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'textarea', 'alang'=>'category_title_note', 'name'=>'note', 'cols'=>'80', 'rows'=>'5', 'value'=>shtmlspecialchars($thevalue['note'])));
	echo label(array('type'=>'table-end'));

	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'input', 'alang'=>'category_title_displayorder_title', 'name'=>'displayorder', 'size'=>'10', 'value'=>$thevalue['displayorder']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	echo label(array('type'=>'eval', 'text'=>'<br><center>'));
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo label(array('type'=>'eval', 'text'=>'</center>'));
	echo '<input name="catid" type="hidden" value="'.$thevalue['catid'].'" />';
	echo '<input name="upid" type="hidden" value="'.$thevalue['upid'].'" />';
	echo '<input name="valuesubmit" type="hidden" value="yes" />';
	echo '<input name="mid" type="hidden" value="'.$_GET['mid'].'" />';
	echo label(array('type'=>'form-end'));
}

?>