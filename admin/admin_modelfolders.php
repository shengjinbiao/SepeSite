<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_modelfolders.php 13411 2009-10-22 03:13:01Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./function/model.func.php');

$_GET['mid'] = postget('mid');
$_GET['mid'] = !empty($_GET['mid']) ? intval($_GET['mid']) : 0;
$perpage = 20;			//每页显示列表数目

$resultmodels = array();
if(!empty($_GET['mid'])) {
	$cacheinfo = getmodelinfoall('mid', $_GET['mid']);
	if(empty($cacheinfo['models'])) {
		showmessage('exists_module_error');
	}
	$resultmodels = $cacheinfo['models'];
} else {
	showmessage('exists_module_error');
}

if(in_array($resultmodels['modelname'], $_SCONFIG['closechannels'])) {
	showmessage('usetype_no_open');
}

//获取的变量初始化
$_SGET['page'] = intval(postget('page'));
$_SGET['order'] = postget('order');
$_SGET['sc'] = postget('sc');
$_SGET['searchkey'] = stripsearchkey(postget('searchkey'));
$_SGET['folder'] = intval(postget('folder')) == 0 ? 1 : intval(postget('folder'));

($_SGET['page'] < 1) ? $_SGET['page'] = 1 : '';
if($_SGET['order'] != 'dateline') {
	$_SGET['order'] = '';
}
if(!in_array($_SGET['sc'], array('ASC', 'DESC'))) {
	$_SGET['sc'] = 'DESC';
}
$urlplus = '&order='.$_SGET['order'].'&sc='.$_SGET['sc'].'&searchkey='.rawurlencode($_SGET['searchkey']);
$newurl = $theurl.$urlplus.'&page='.$_SGET['page'];

$listarr = array();

//POST METHOD
if (submitcheck('listvaluesubmit')) {
	if(empty($_POST['item'])) {		//判断提交过来的是否存在待操作的记录，如果没有，则显示提示信息并退出
		showmessage('space_no_item');
	}
	$itemidstr = simplode($_POST['item']);	//用逗号链接所有的操作ID

	$newidarr = array();
	$foldersql = '';
	if(!checkperm('managefolder') && checkperm('manageundelete')) {
		$foldersql = "AND folder='2'";
	} elseif(!checkperm('managefolder')) {
		showmessage('no_authority_management_operation');
	}
	$query = $_SGLOBAL['db']->query("SELECT itemid FROM ".tname('modelfolders')." WHERE mid='$resultmodels[mid]' AND itemid IN ($itemidstr) $foldersql");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newidarr[] = $value['itemid'];
	}
	if(empty($newidarr)) {
		showmessage('space_no_item');
	}
	$itemidstr = simplode($newidarr);

	$_POST['operation'] = !empty($_POST['operation']) ? intval($_POST['operation']) : 0;

	changemodelfolder($resultmodels['modelname'], $itemidstr, $_POST['operation']);
	if($_POST['operation'] == 1) {
		if($_SGET['folder'] == 1) {
			showmessage('through_the_completion_of_audit', CPURL.'?action=modelfolders&mid='.$resultmodels['mid'].'&folder=1');
		} else {
			showmessage('information_reduction_success', CPURL.'?action=modelfolders&mid='.$resultmodels['mid'].'&folder=2');
		}
	} elseif($_POST['operation'] == 2) {
		showmessage('info_remove_waste_suc', CPURL.'?action=modelfolders&mid='.$resultmodels['mid']);
	} elseif($_POST['operation'] == 3) {
		showmessage('info_del_suc', CPURL.'?action=modelfolders&mid='.$resultmodels['mid'].'&folder=2');
	} else {
		showmessage('no_select_op');
	}
}

//GET METHOD
if (empty($_GET['op'])) {
	//LIST VIEW
	$wheresqlarr = checkperm('managefolder') ? array() : array('uid'=>$_SGLOBAL['supe_uid']);

	$wheresqlstr = getwheresql($wheresqlarr);
	if(!empty($_SGET['searchkey'])) {
		$wheresqlstr .= ' AND subject LIKE \'%'.$_SGET['searchkey'].'%\'';
	}

	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('modelfolders').' WHERE mid=\''.$resultmodels['mid'].'\' AND folder=\''.$_SGET['folder'].'\' AND '.$wheresqlstr);
	$listcount = $_SGLOBAL['db']->result($query, 0);
	$multipage = '';

	if($listcount) {
		if(empty($_SGET['order'])) {
			$order = 'dateline DESC';
		} else {
			$order = $_SGET['order'].' '.$_SGET['sc'];
		}
		$start = ($_SGET['page']-1)*$perpage;

		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelfolders').' WHERE mid=\''.$resultmodels['mid'].'\' AND folder=\''.$_SGET['folder'].'\' AND '.$wheresqlstr.' ORDER BY '.$order.' LIMIT '.$start.','.$perpage);
		while ($item = $_SGLOBAL['db']->fetch_array($query)) {
			$listarr[] = $item;
		}
		$multipage = multi($listcount, $perpage, $_SGET['page'], $theurl.$urlplus);
	}
}

//MENU
$folderarr = array('1'=>'', '2'=>'');
$folderarr[$_SGET['folder']] = 'class="active"';
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$resultmodels['modelname'].' ('.$resultmodels['modelalias'].') '.$alang['folder_management'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td><a href="'.CPURL.'?action=modelmanages&mid='.$_GET['mid'].'" class="view">'.$alang['spaces_spacecp'].'</a></td>
					<td><a href="'.CPURL.'?action=modelmanages&op=add&mid='.$_GET['mid'].'" class="add">'.$alang['release_information'].'</a></td>
					<td '.$folderarr[1].'><a href="'.CPURL.'?action=modelfolders&mid='.$_GET['mid'].'">'.$alang['pending_box_management'].'</a></td>
					<td '.$folderarr[2].'><a href="'.CPURL.'?action=modelfolders&mid='.$_GET['mid'].'&folder=2">'.$alang['waste_management_bins'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';

//FILTER SHOW
$orderarr = array(
	'' => $alang['space_order_default'],
	'dateline' => $alang['days']
);
$scarr = array(
	'ASC' => $alang['space_sc_asc'],
	'DESC' => $alang['space_sc_desc']
);

$orderselectstr = getselectstr('order', $orderarr);
$scselectstr = getselectstr('sc', $scarr);

$htmlstr = label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$newurl));
$htmlstr .= label(array('type'=>'table-start', 'class'=>'toptable'));
$htmlstr .= '<tr><td>';
$htmlstr .= $lang['subject'].':</label> <input type="text" name="searchkey" id="searchkey" value="'.$_SGET['searchkey'].'" size="10" /> ';
$htmlstr .= $alang['space_order_filter'].': '.$orderselectstr.' '.$scselectstr.' <input type="hidden" name="mid" value="'.$_GET['mid'].'"><input type="hidden" name="folder" value="'.$_SGET['folder'].'"><input type="submit" name="filtersubmit" value="GO">';
$htmlstr .= '</td></tr>';
$htmlstr .= label(array('type'=>'table-end'));
$htmlstr .= label(array('type'=>'form-end'));
echo $htmlstr;

//LIST SHOW
echo label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$newurl, 'other'=>' onSubmit="return listsubmitconfirm(this)"'));
echo label(array('type'=>'table-start', 'class'=>'listtable'));
echo '<tr>';
echo '<th width="40">'.$alang['space_select'].'</th>';
echo '<th>'.$alang['spaceblog_subject'].'</th>';
echo '<th width="140">'.$alang['days'].'</th>';
echo '</tr>';

if($listarr) {
	foreach ($listarr as $listvalue) {
		empty($class) ? $class=' class="darkrow"': $class='';
		$listvalue['dateline'] = sgmdate($listvalue['dateline']);
		echo '<tr'.$class.'>';
		echo '<td><input name="item[]" type="checkbox" value="'.$listvalue['itemid'].'" /></td>';
		echo '<td><a href="'.CPURL.'?action=modelmanages&mid='.$_GET['mid'].'&op=view&itemid='.$listvalue['itemid'].'&folder=1" target="_blank">'.$listvalue['subject'].'</a></td>';
		echo '<td>'.$listvalue['dateline'].'</td>';
		echo '</tr>';
	}
} else {
	echo '<tr><td align="center" colspan="3">'.$alang['search_not_info'].'</td></tr>';
}

echo label(array('type'=>'table-end'));

if(checkperm('managefolder') || checkperm('manageundelete')) {

	if($_SGET['folder'] == 2 && (checkperm('manageundelete') || checkperm('managefolder'))) {
		print <<<EOF
			<table cellspacing="0" cellpadding="0" width="100%"  class="btmtable">
			<tr>
				<th width="12%">$alang[ad_title_operate]</th>
				<th>
					<input type="checkbox" name="chkall" onclick="checkall(this.form, 'item')">$alang[space_select_all]
					<input type="radio" name="operation" value="1">$alang[reduction]
					<input type="radio" name="operation" value="3">$alang[completely_erased]
					</th>
			</tr>
			</table>
EOF;
	} elseif(checkperm('managefolder')) {
		print <<<EOF
			<table cellspacing="0" cellpadding="0" width="100%"  class="btmtable">
			<tr>
				<th width="12%">$alang[ad_title_operate]</th>
				<th>
					<input type="checkbox" name="chkall" onclick="checkall(this.form, 'item')">$alang[space_select_all]
					<input type="radio" name="operation" value="1">$alang[through_audit]
					<input type="radio" name="operation" value="2">$alang[moved_to_waste_bins]
					<input type="radio" name="operation" value="3">$alang[directly_delete]
					</th>
			</tr>
			</table>
EOF;
	}
}

if(!empty($multipage)) {
	echo label(array('type'=>'table-start', 'class'=>'listpage'));
	echo '<tr><td>'.$multipage.'</td></tr>';
	echo label(array('type'=>'table-end'));
}
if(checkperm('managefolder') || (checkperm('manageundelete') && $_SGET['folder'] == 2)) {
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'listsubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'listreset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo '<input name="mid" type="hidden" value="'.$_GET['mid'].'" />';
	echo '<input name="folder" type="hidden" value="'.$_SGET['folder'].'" />';
	echo '<input name="listvaluesubmit" type="hidden" value="yes" />';
}

echo label(array('type'=>'form-end'));

?>