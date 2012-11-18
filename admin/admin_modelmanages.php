<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_modelmanages.php 13493 2009-11-11 06:15:33Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('allowmanage')) {
	showmessage('no_authority_management_operation');
}

include_once(S_ROOT.'./function/model.func.php');
$_GET['mid'] = postget('mid');
$_GET['mid'] = !empty($_GET['mid']) ? intval($_GET['mid']) : 0;
$_GET['folder'] = isset($_GET['folder']) ? 1 : 0;
$resultmodels = array();
$cacheinfo = getmodelinfoall('mid', $_GET['mid']);
if(empty($cacheinfo['models'])) {
	showmessage('exists_module_error');
}
$resultmodels = $cacheinfo['models'];

if(in_array($resultmodels['modelname'], $_SCONFIG['closechannels'])) {
	showmessage('usetype_no_open');
}

$perpage = 20;
$catarr = array();
$havecheck = 0;
if(checkperm('managefolder') || checkperm('managemodpost')) {
	$havecheck = 1;
}

//获取的变量初始化
$_SGET['page'] = intval(postget('page'));
$_SGET['catid'] = intval(postget('catid'));
$_SGET['order'] = postget('order');
$_SGET['sc'] = postget('sc');
$_SGET['searchid'] = intval(postget('searchid'))==0 ? '' : intval(postget('searchid'));
$_SGET['searchkey'] = stripsearchkey(postget('searchkey'));
if(empty($_SGET['subtype'])) $_SGET['subtype'] = '';
($_SGET['page']<1)?$_SGET['page']=1:'';
if(!in_array($_SGET['order'], array('dateline', 'lastpost', 'uid', 'viewnum', 'replynum'))) {
	$_SGET['order'] = '';
}
if(!in_array($_SGET['sc'], array('ASC', 'DESC'))) {
	$_SGET['sc'] = 'DESC';
}
$urlplus = '&catid='.$_SGET['catid'].'&order='.$_SGET['order'].'&sc='.$_SGET['sc'].'&subtype='.$_SGET['subtype'].'&searchkey='.rawurlencode($_SGET['searchkey']);
$theurl = $theurl.'&mid='.$_GET['mid'];
$newurl = $theurl.$urlplus.'&page='.$_SGET['page'];

if(!empty($_GET['openwindow'])) setcookie('_openwindow', 1);
if(!empty($_COOKIE['_openwindow'])) {
	$_SGET['openwindow'] = 1;
} else {
	$_SGET['openwindow'] = 0;
}

$gradearr = array(
	'0' => $alang['general_state'],
	'1' => $alang['check_grade_1'],
	'2' => $alang['check_grade_2'],
	'3' => $alang['check_grade_3'],
	'4' => $alang['check_grade_4'],
	'5' => $alang['check_grade_5']
);

$listarr = array();
$thevalue = array();
$showurlarr = array();

//POST METHOD
if (submitcheck('listvaluesubmit')) {
	$_POST['operation'] = empty($_POST['operation']) ? '' : trim($_POST['operation']);
	
	if($_POST['operation'] == 'delete') {
		if(!(checkperm('managecheck') || checkperm('managemodpost') || checkperm('managedelpost'))) showmessage('spacenews_no_popedom_check');
	} else {
		if(!(checkperm('managecheck') || checkperm('managemodpost'))) showmessage('spacenews_no_popedom_check');
	}
		
	//判断提交过来的是否存在待操作的记录，如果没有，则显示提示信息并退出
	if(empty($_POST['item'])) {
		showmessage('space_no_item');
	}
	$itemidstr = simplode($_POST['item']);	//用逗号链接所有的操作ID

	$newidarr = array();
	$query = $_SGLOBAL['db']->query("SELECT itemid FROM ".tname($resultmodels['modelname'].'items')." WHERE itemid IN ($itemidstr)");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newidarr[] = $value['itemid'];
	}
	if(empty($newidarr)) {
		showmessage('space_no_item');
	}
	$itemidstr = simplode($newidarr);

	//跟据操作类型做相应的操作处理
	switch ($_POST['operation']) {
		case 'movecat':		//更改记录分类
			$_SGLOBAL['db']->query('UPDATE '.tname($resultmodels['modelname'].'items').' SET catid=\''.$_POST['opcatid'].'\' WHERE itemid IN ('.$itemidstr.')');
			break;
		case 'check':	//审核等级
			$_SGLOBAL['db']->query('UPDATE '.tname($resultmodels['modelname'].'items').' SET grade=\''.intval($_POST['opcheck']).'\' WHERE itemid IN ('.$itemidstr.')');
			break;
		case 'allowreply':	//是否允许评论
			$_SGLOBAL['db']->query('UPDATE '.tname($resultmodels['modelname'].'items').' SET allowreply=\''.$_POST['opallowreply'].'\' WHERE itemid IN ('.$itemidstr.')');
			break;
		case 'delete':		//删除操作
			
			//积分
			$uids = getuids($newidarr, $resultmodels['modelname'].'items');
			updatecredit('delinfo', $uids);
			
			deletemodelitems($resultmodels['modelname'], $itemidstr, $_GET['mid'], $_POST['opdelete']);
			break;
	}

} elseif(submitcheck('valuesubmit')) {
	if(!(checkperm('managemodpost') || checkperm('manageeditpost'))) {
		showmessage('no_authority_management_operation');
	}
	modelpost($cacheinfo);
}

if (!empty($_GET['op']) && ($_GET['op'] == 'add' || $_GET['op'] == 'edit')) {
	if(!(checkperm('managemodpost') || checkperm('manageeditpost'))) {
		showmessage('no_authority_management_operation');
	}
	$resultmodelcolumns = array();
	if($_GET['mid'] > 0) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelcolumns').' WHERE mid = \''.$_GET['mid'].'\' ORDER BY displayorder, id');
		while ($result = $_SGLOBAL['db']->fetch_array($query)) {
			$resultmodelcolumns[] = $result;
		}
	}
}

$addclass = $viewclass = '';
$wheresqlarr = $havecheck ? array() : array('uid'=>$_SGLOBAL['supe_uid']);
if (empty($_GET['op'])) {
	if(empty($showurlarr)) {
		//CATEGORY
		$catarr = getmodelcategory($resultmodels['modelname']);
		$rtarr = array();
		if(!empty($_SGET['searchid'])) {
			$wheresqlstr = ' itemid = \''.$_SGET['searchid'].'\'';
		} else {
			if(!empty($_SGET['catid'])) {
				$wheresqlarr['catid'] = intval($_SGET['catid']);
			}
			if(!empty($_SGET['subtype'])) {
				$wheresqlarr['subtype'] = intval($_SGET['subtype']);
			}
		
			$wheresqlstr = getwheresql($wheresqlarr);
			if(!empty($_SGET['searchkey'])) {
				$wheresqlstr .= ' AND subject LIKE \'%'.$_SGET['searchkey'].'%\'';
			}
		}
	
		$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname($resultmodels['modelname'].'items').' WHERE '.$wheresqlstr);
		$listcount = $_SGLOBAL['db']->result($query, 0);
		$multipage = '';
		$listarr = array();
		if($listcount) {			
			if(empty($_SGET['order'])) {
				$order = 'dateline DESC';
			} else {
				$order = $_SGET['order'].' '.$_SGET['sc'];
			}
			$start = ($_SGET['page']-1)*$perpage;
			
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($resultmodels['modelname'].'items').' WHERE '.$wheresqlstr.' ORDER BY '.$order.' LIMIT '.$start.','.$perpage);
			while ($item = $_SGLOBAL['db']->fetch_array($query)) {
				$listarr[] = $item;
			}
			$multipage = multi($listcount, $perpage, $_SGET['page'], $theurl.$urlplus);
		}
		
		$rtarr['listcount'] = $listcount;
		$rtarr['multipage'] = $multipage;
		$rtarr['listarr'] = $listarr;
		
		$viewclass = ' class="active"';
	}
	
} elseif ($_GET['op'] == 'edit' || $_GET['op'] == 'view') {
	$itemid = intval($_GET['itemid']);
	$sqlplus = '';
	if(!empty($itemid)) {
		$wheresqlstr = getwheresql($wheresqlarr);
		if(!empty($_GET['folder']) && $_GET['op'] == 'view') {
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelfolders').' WHERE itemid=\''.$itemid.'\' AND mid=\''.$resultmodels['mid'].'\' AND '.$wheresqlstr);
			$thevalue = $_SGLOBAL['db']->fetch_array($query);
			$thevalue = sstripslashes(unserialize($thevalue['message']));
		} else {
			if ($wheresqlstr != 1) {
				$wheresqlstr = 'i.'.$wheresqlstr;
			}
			$query = $_SGLOBAL['db']->query('SELECT ii.*, i.* FROM '.tname($resultmodels['modelname'].'message').' ii '.
												'LEFT JOIN '.tname($resultmodels['modelname'].'items').' i ON i.itemid=ii.itemid '.
												'WHERE ii.itemid=\''.$itemid.'\' AND '.$wheresqlstr);
			$thevalue = $_SGLOBAL['db']->fetch_array($query);
		}
		
		if(empty($thevalue)) {
			showmessage('no_item_or_no_prem', S_URL.'/'.$theurl);
		}
		
		$tmpmessage = $thevalue['message'];
		if(!empty($thevalue)) {
			foreach($thevalue as $tmpkey => $tmpvalue) {
				if(!empty($cacheinfo['columns'][$tmpkey]['isbbcode'])) {
					$thevalue[$tmpkey] = modeldiscuzcode($tmpvalue, 'de');
				}
			}
		}
		$thevalue = shtmlspecialchars($thevalue);
		$thevalue['message'] = $tmpmessage;

	}

} elseif($_GET['op'] == 'add') {
	
	$thevalue = array(
		'itemid' => 0,
		'catid' => 0,
		'subject' => '',
		'dateline' => $_SGLOBAL['timestamp'],
		'allowreply' => '1',
		'replynum' => 0,
		'tid' => 0,
		'grade' => 0,
		'message' => '',
		'subjectimage' => ''
	);
	foreach ($resultmodelcolumns as $value) {
		if(!preg_match("/^(TEXT|MEDIUMTEXT|LONGTEXT)$/i", $value['fieldtype'])) {
				$thevalue[$value['fieldname']] = $value['formtype'] != 'timestamp' ? $value['fielddefault'] : $_SGLOBAL['timestamp'];
		} else {
			$thevalue[$value['fieldname']] = '';
		}
	}
	$thevalue['nid'] = 0;
	$addclass = ' class="active"';
}

//MENU
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$resultmodels['modelname'].' ('.$resultmodels['modelalias'].') '.$alang['spaces_spacecp'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
';
echo '<td'.$viewclass.'><a href="'.$theurl.'" class="view">'.$alang['spaces_spacecp'].'</a></td>';
echo '<td'.$addclass.'><a href="'.$newurl.'&op=add" class="add">'.$alang['release_information'].'</a></td>';
echo '<td><a href="'.CPURL.'?action=modelfolders&mid='.$_GET['mid'].'">'.$alang['pending_box_management'].'</a></td>
	  <td><a href="'.CPURL.'?action=modelfolders&mid='.$_GET['mid'].'&folder=2">'.$alang['waste_management_bins'].'</a></td>';
echo '
				</tr>
			</table>
		</td>
	</tr>
</table>
';

//FILTER SHOW
if (!empty($catarr)) {
	$orderarr = array(
		'' => $alang['space_order_default'],
		'dateline' => $alang['space_order_dateline'],
		'lastpost' => $alang['space_order_lastpost'],
		'viewnum' => $alang['space_order_viewnum'],
		'replynum' => $alang['space_order_replynum']
	);
	$scarr = array(
		'ASC' => $alang['space_sc_asc'],
		'DESC' => $alang['space_sc_desc']
	);
	
	$catselectstr = '<select name="catid">';
	$catselectstr .= '<option value="">'.$alang['space_all_catid'].'</option>';
	foreach ($catarr as $key => $value) {
		$checkstr = postget('catid') == $value['catid']?' selected':'';
		$catselectstr .= '<option value="'.$value['catid'].'"'.$checkstr.'>'.$value['pre'].$value['name'].'</option>';
	}
	$catselectstr .= '</select>';

	$orderselectstr = getselectstr('order', $orderarr);
	$scselectstr = getselectstr('sc', $scarr);
	
	$htmlstr = label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$newurl));
	$htmlstr .= label(array('type'=>'table-start', 'class'=>'toptable'));
	$htmlstr .= '<tr><td>';
	$htmlstr .= 'itemid:</label> <input type="text" name="searchid" id="searchid" value="'.$_SGET['searchid'].'" size="5" /> ';
	$htmlstr .= $lang['subject'].':</label> <input type="text" name="searchkey" id="searchkey" value="" size="10" /> ';
	$htmlstr .= $alang['space_select_filter'].': '.$catselectstr.' '.$alang['space_order_filter'].': '.$orderselectstr.' '.$scselectstr.' <input type="hidden" name="mid" value="'.$_GET['mid'].'"><input type="submit" name="filtersubmit" value="GO">';
	$htmlstr .= '</td></tr>';
	$htmlstr .= label(array('type'=>'table-end'));
	$htmlstr .= label(array('type'=>'form-end'));
	echo $htmlstr;
}

//LIST SHOW
if($listarr) {
	global $gradearr;
	if(checkperm('managefolder') || checkperm('managemodpost')) {
		$adminmenuarr['noop'] = $alang['space_no_op'];
		$adminmenuarr['check'] = $alang['grades_audit'];
		$adminmenuarr['movecat'] = $alang['space_move_cat'];
		$adminmenuarr['allowreply'] = $alang['space_allowreply'];
	}
	$adminmenuarr['delete'] = $alang['space_delete'];
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
	$admintbl['movecat'] = label(array('type'=>'select-div', 'alang'=>'space_op_category', 'name'=>'opcatid', 'id'=>"divmovecat", 'radio'=>1, 'options'=>$catarr, 'display'=>'none'));
	$checkarr = $gradearr;
	$admintbl['check'] = label(array('type'=>'radio', 'alang'=>'examination_grades', 'name'=>'opcheck', 'id'=>"divcheck", 'options'=>$checkarr, 'value'=>'0', 'display'=>'none'));

	$spacedeletearr = array(
		'0' => $alang['space_delete_0'],
		'1' => $alang['model_category_delete']
	);

	$admintbl['delete'] = label(array('type'=>'radio', 'alang'=>'space_delete', 'name'=>'opdelete', 'id'=>"divdelete", 'options'=>$spacedeletearr, 'value'=>'0', 'display'=>'none'));
	$spaceallowreplyarr = array(
		'1' => $alang['space_allowreply_1'],
		'0' => $alang['space_allowreply_0']
	);	
	$admintbl['allowreply'] = label(array('type'=>'radio', 'alang'=>'space_allowreply', 'name'=>'opallowreply', 'id'=>"divallowreply", 'options'=>$spaceallowreplyarr, 'value'=>'1', 'display'=>'none'));
	
	$htmlarr = array();
	$htmlarr['js'] = '
	<script language="javascript">
	<!--
	function jsop(radionvalue) {'."\n";
	foreach ($adminmenuarr as $adminkey => $adminvalue) {
		$htmlarr['js'] .= 'document.getElementById(\'div'.$adminkey.'\').style.display = "none";'."\n";
	}
	$htmlarr['js'] .= '
	if(radionvalue == \'noop\') {
	} else {
		document.getElementById(\'div\'+radionvalue).style.display = "";
	}
	}
	//-->
	</script>
	';
	
	$htmlarr['html'] = '<tr><th width="12%">'.$adminmenu.'</th></tr>';
	foreach ($adminmenuarr as $adminkey => $adminvalue) {
		$htmlarr['html'] .= $admintbl[$adminkey];
	}
	$adminhtmlarr = $htmlarr;
	
	echo $adminhtmlarr['js'];
	echo label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$newurl, 'other'=>' onSubmit="return listsubmitconfirm(this)"'));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));
	echo '<tr>';
	echo '<th width="30">'.$alang['space_select'].'</th>';
	echo '<th width="50">itemid</th>';
	echo '<th>'.$alang['spaceblog_subject'].'</th>';
	echo '<th width="100">'.$alang['spaceblog_title_catid'].'</th>';
	echo '<th width="80">'.$alang['spacenews_title_author'].'</th>';
	echo '<th width="140">'.$alang['space_dateline'].'</th>';
	echo '<th width="70">'.$alang['audit_level'].'</th>';
	echo '<th width="60">'.$alang['space_op'].'</th>';
	echo '</tr>';
	if(!empty($listarr)) {
		foreach ($listarr as $listvalue) {
			empty($class) ? $class=' class="darkrow"': $class='';
			$listvalue['dateline'] = sgmdate($listvalue['dateline']);
			$listvalue['lastpost'] = sgmdate($listvalue['lastpost']);
			$listvalue['grade'] = $gradearr[$listvalue['grade']];
			echo '<tr'.$class.'>';
			echo '<td><input name="item[]" type="checkbox" value="'.$listvalue['itemid'].'" /></td>';
			echo '<td>'.$listvalue['itemid'].'</td>';
			echo '<td><a href="'.$theurl.'&op=view&itemid='.$listvalue['itemid'].'" target="_blank">'.$listvalue['subject'].'</a></td>';
			echo '<td align="center"><a href="'.$theurl.'&catid='.$listvalue['catid'].'">'.(!empty($catarr[$listvalue['catid']]['name']) ? $catarr[$listvalue['catid']]['name'] : '').'</a></td>';
			echo '<td align="center">'.($listvalue['uid'] ? '<a href="'.S_URL.'/space.php?uid='.$_SGLOBAL['supe_uid'].'" target="_blank" >'.$listvalue['username'].'</a>' : $alang['check_guest']).'</td>';
			echo '<td>'.$listvalue['dateline'].'</td><td>'.$listvalue['grade'].'</td>';
			echo '<td align="center"><img src="'.S_URL.'/images/base/icon_edit.gif" align="absmiddle"> <a href="'.$newurl.'&op=edit&itemid='.$listvalue['itemid'].'">'.$alang['space_edit'].'</a></td>';
			echo '</tr>';
		}
	} else {
		echo '<tr><td align="center" colspan="8">'.$alang['search_not_info'].'</td></tr>';
	}
	echo label(array('type'=>'table-end'));

	echo label(array('type'=>'table-start', 'class'=>'btmtable'));
	echo $adminhtmlarr['html'];
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
	echo '<input name="mid" type="hidden" value="'.$_GET['mid'].'" />';
	echo '<input name="listvaluesubmit" type="hidden" value="yes" />';

	echo label(array('type'=>'form-end'));
}

//完成后的url
if(is_array($showurlarr) && $showurlarr) {
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	if(!empty($uploadfilearr)) {
		foreach($uploadfilearr as $tmpkey => $tmpvalue) {
			if(!empty($tmpvalue['error'])) {
				echo '<tr><td>'.$tmpvalue['fieldcomment'].$tmpvalue['error'].'</td></tr>';
			}
		}
	}
	foreach ($showurlarr as $url) {
		$turl = geturl($url[0]);
		echo '<tr><td><a href="'.$turl.'" target="_blank"><strong>'.$url[1].'</strong> '.$alang['spaceblog_viewpage_success'].'</a></td></tr>';
	}
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	
	echo '
	<div class="buttons">
	<input type="button" name="continuesubmit4" value="'.$alang['continue_to_release_new_information'].'" onclick="window.location.href=\''.$theurl.'&op=add\'"> 
	<input type="button" name="continuesubmit2" value="'.$alang['edit_this_page'].'" onclick="window.location.href=\''.$theurl.'&op=edit&itemid='.$itemid.'\'"> 
	<input type="button" name="continuesubmit1" value="'.$alang['list_info'].'" onclick="window.location.href=\''.$theurl.'\'"> 
	</div>';
}

//THE VALUE SHOW
if($thevalue) {
	if(!empty($cacheinfo['fielddefault']['subject'])) $alang['spacenews_title_subject'] = $cacheinfo['fielddefault']['subject'].'*';
	if(!empty($cacheinfo['fielddefault']['subjectimage'])) $alang['subject_pic'] = $cacheinfo['fielddefault']['subjectimage'];
	if(!empty($cacheinfo['fielddefault']['catid'])) $alang['space_title_catid'] = $cacheinfo['fielddefault']['catid'].'*';
	if(!empty($cacheinfo['fielddefault']['message'])) $alang['content_title'] = $cacheinfo['fielddefault']['message'];
	
	if($_GET['op'] == 'edit' || $_GET['op'] == 'add') {
		$linkagestr = '';
		//JAVASCRIPT
		echo '<script src="'.S_URL.'/include/js/selectdate.js"></script>'."\n";
		echo '<script src="'.S_URL.'/model/data/'.$resultmodels['modelname'].'/images/validate.js"></script>'."\n";

		//CATEGORIES
		$clistarr = getmodelcategory($resultmodels['modelname']);
		$categorylistarr = array(''=>array('pre'=>'', 'name'=>'------'));
		foreach ($clistarr as $key => $value) {
			$categorylistarr[$key] = $value;
		}
		
		$spaceallowreplyarr = array(
			'1' => $alang['space_allowreply_1'],
			'0' => $alang['space_allowreply_0']
		);
		
		//页面显示
		echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$newurl, 'other'=>' onSubmit="return validate(this)"'));
		echo label(array('type'=>'div-start'));
		echo label(array('type'=>'table-start'));
		echo label(array('type'=>'input', 'alang'=>'spacenews_title_subject', 'name'=>'subject', 'width'=>'30%', 'size'=>'60', 'value'=>shtmlspecialchars($thevalue['subject'])));
		echo label(array('type'=>'file', 'alang'=>$alang['subject_pic'], 'name'=>'subjectimage', 'width'=>'30%', 'size'=>'60', 'value'=>$thevalue['subjectimage'], 'fileurl'=>A_URL.'/'.$thevalue['subjectimage']));
		$thevalue['dateline'] = sgmdate($thevalue['dateline']);
		print <<<EOF
		<tr>
		<th>$alang[select_info_date]</th>
		<td>
		<input type="text" name="dateline" id="dateline" readonly="readonly" value="$thevalue[dateline]" /><img src="$siteurl/admin/images/time.gif" onClick="getDatePicker('dateline', event, 21)" />
		</td>
		</tr>
EOF;
		echo label(array('type'=>'select-div', 'alang'=>'space_title_catid', 'name'=>'catid', 'radio'=>1, 'options'=>$categorylistarr, 'width'=>'30%', 'value'=>$thevalue['catid']));
		if(!empty($resultmodels['allowcomment'])) echo label(array('type'=>'radio', 'alang'=>'space_allowreply', 'name'=>'allowreply', 'id'=>"divallowreply", 'options'=>$spaceallowreplyarr, 'value'=>$thevalue['allowreply']));

		if(checkperm('managefolder') || checkperm('managemodpost')) {
			echo label(array('type'=>'radio', 'alang'=>'examination_grades', 'name'=>'grade', 'options'=>$gradearr, 'value'=>$thevalue['grade']));	
		}
		
		echo '<tr id="tr_message">'.
				'<th>'.$alang['content_title'].'</th>'.
				'<td><table style="width: 100%"><tr><td>';
		echo label(array('type'=>'edit', 'alang'=>$alang['content_title'], 'name'=>'message', 'value'=>$thevalue['message'], 'op'=>0));
		echo '</td></tr></table></td></tr>';
		echo label(array('type'=>'table-end'));
		echo label(array('type'=>'div-end'));
		
		echo label(array('type'=>'div-start'));
		echo label(array('type'=>'table-start'));
	
		foreach ($resultmodelcolumns as $value) {
			if(!(checkperm('managefolder') || checkperm('managemodpost')) && empty($value['allowpost'])) continue;
			$temparr = $temparr2 = array();
			$other = '';
			if($value['formtype'] == 'select') {
				$temparr2 = array(''=>'');
			}

			$temparr = explode("\r\n", $value['fielddata']);
			foreach($temparr as $value2) {
				$temparr2[$value2] = $value2;
			}
	
			//整理提示信息
			if(!empty($value['isrequired'])) {
				$value['fieldcomment'] .= '<span style="color: #F00">*</span>';
			}
			$value['fieldcomment'] .= '<p>';
			if(!empty($value['ishtml'])) {
				$value['fieldcomment'] .= $alang['fieldcomment_help'];
			}
			if(!empty($value['isbbcode'])) {
				$value['fieldcomment'] .= $alang['fieldcomment_discuz_code'];
			}
			if(preg_match("/^(TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT)$/i", $value['fieldtype']) && !preg_match("/^(select|radio|checkbox|file|img|flash)$/i", $value['formtype'])) {
				$value['fieldcomment'] .= $alang['field_only_int'];
			}
			if(preg_match("/^(FLOAT|DOUBLE)$/i", $value['fieldtype']) && !preg_match("/^(select|radio|checkbox|file|img|flash)$/i", $value['formtype'])) {
				$value['fieldcomment'] .= $alang['field_only_float'];
			}
			if(preg_match("/^(text|textarea)$/i", $value['formtype']) && !preg_match("/^(TEXT|MEDIUMTEXT|LONGTEXT|FLOAT|DOUBLE)$/i", $value['fieldtype'])) {
				$value['fieldcomment'] .= $alang['field_length_1'].$value['fieldlength'].$alang['field_length_2'];
			}
			$value['fieldcomment'] .= '</p>';
	
			if($value['formtype'] == 'linkage') {
				$temparr2 = array();
				if(!empty($cacheinfo['linkage']['down'][$value['id']])) {
					$downfieldname = $cacheinfo['columnids'][$cacheinfo['linkage']['down'][$value['id']]];
					$other = ' onchange="fill(\''.$downfieldname.'\', \''.$value['fieldname'].'\', '.$downfieldname.'arr);"';
				}
				if($value['upid'] == '0') {
					$linkagestr .= 'fill(\''.$value['fieldname'].'\', \'\', '.$value['fieldname'].'arr, \''.$thevalue[$value['fieldname']].'\');';
				} else {
					$linkagestr .= 'fill(\''.$value['fieldname'].'\', \''.$cacheinfo['columnids'][$value['upid']].'\', '.$value['fieldname'].'arr, \''.$thevalue[$value['fieldname']].'\');';
				}
			}
			
			$value['formtype'] = $value['formtype'] == 'text' ? 'input' : $value['formtype'];
			$value['formtype'] = $value['formtype'] == 'linkage' ? 'select' : $value['formtype'];
			if($value['formtype'] == 'checkbox') {
				$thevalue[$value['fieldname']] = explode("\n", $thevalue[$value['fieldname']]);
			}
			if($value['formtype'] == 'file') {
				$fileurl = S_URL.'/batch.modeldownload.php?hash='.rawurlencode(authcode($resultmodels['modelname'].','.$thevalue[$value['fieldname']], 'ENCODE'));
			} else {
				$fileurl = A_URL.'/'.$thevalue[$value['fieldname']];
			}
			if(preg_match("/^(img|flash|file)$/i", $value['formtype'])) {
				$value['formtype'] = 'file';
			}
			
			if($value['formtype'] != 'timestamp') {
				echo label(array('type'=>$value['formtype'], 'alang'=>$value['fieldcomment'], 'name'=>$value['fieldname'], 'options'=>$temparr2, 'rows'=>10, 'width'=>'30%', 'size'=>'60', 'value'=>$thevalue[$value['fieldname']],  'other'=>$other, 'fileurl'=>$fileurl));
			} else {
				$thevalue[$value['fieldname']] = sgmdate($thevalue[$value['fieldname']]);
				print <<<EOF
				<tr>
				<th>$value[fieldcomment]</th>
				<td>
				<input type="text" name="$value[fieldname]" id="$value[fieldname]" readonly="readonly" value="{$thevalue[$value['fieldname']]}" /><img src="$siteurl/admin/images/time.gif" onClick="getDatePicker('$value[fieldname]', event, 21)" />
				</td>
				</tr>
EOF;
			}
			
		}
		if($_SGLOBAL['supe_uid'] && allowfeed()) {
			$feedchecked = ($resultmodels['allowfeed'] & 1) ? array(1=>'checked="checked"') : array(0=>'checked="checked"');
			echo <<<EOF
			 <tr>
				<th>$lang[pushed_to_the_feed]</th>
				<td>
				<input type="radio" $feedchecked[1] value="1" name="addfeed"/>$lang[yes]
				<input type="radio" $feedchecked[0] value="0" name="addfeed"/>$lang[no]
				</td>
				</tr>
EOF;
		}
		echo label(array('type'=>'table-end'));
		echo label(array('type'=>'div-end'));
		
		echo '<div class="buttons">';
		echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'other'=>' onclick="publish_article();"', 'value'=>$alang['common_submit']));
		echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
		echo '</div>';
		
		echo '<input name="itemid" type="hidden" value="'.$thevalue['itemid'].'" />';
		echo '<input name="nid" type="hidden" value="'.$thevalue['nid'].'" />';
		echo '<input name="tid" type="hidden" value="'.$thevalue['tid'].'" />';
		echo '<input name="mid" type="hidden" value="'.$_GET['mid'].'" />';
		echo '<input name="valuesubmit" type="hidden" value="yes" />';
		echo label(array('type'=>'form-end'));
		echo "<script>$linkagestr</script>\n";
	} else {
		//CATEGORIES
		$clistarr = getmodelcategory($resultmodels['modelname']);
		$categorylistarr = array('0'=>array('pre'=>'', 'name'=>'------'));
		foreach ($clistarr as $key => $value) {
			$categorylistarr[$key] = $value;
		}
		
		$spaceallowreplyarr = array(
			'1' => $alang['space_allowreply_1'],
			'0' => $alang['space_allowreply_0']
		);

		if(!empty($thevalue['subjectimage'])) {
			$fileext = fileext($thevalue['subjectimage']);
			$thevalue['subjectimage'] = $thevalue['subjectthumb'] = A_URL.'/'.$thevalue['subjectimage'];
			if(preg_match("/^(jpg|jpeg|png)$/i", $fileext)) {
				$thevalue['subjectthumb'] = substr($thevalue['subjectimage'], 0, strrpos($thevalue['subjectimage'], '.')).'.thumb.jpg';
			}
		}
		
		$output = '<style type="text/css">img {float:left; border:1px solid #CCC; padding:2px; margin:6px 12px 0px 0px; max-width: 300px; max-height: 300px; width: expression(this.width > 300 && this.width > this.height ? 300 : true); height: expression(this.height > 300 ? 300 : true); font-size: 12px; }</style>';
		$output .= '<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">';
		$output .= '<tr>'."\n";
		$output .= '<th>'.$alang['spacenews_title_subject'].'</th>'."\n";
		$output .= '<td>'."\n";
		$output .= shtmlspecialchars($thevalue['subject'])."\n";
		$output .= '</td>'."\n";
		$output .= '</tr>'."\n";
		$output .= '<tr>'."\n";
		$output .= '<th>'.$alang['subject_pic'].'</th>'."\n";
		$output .= '<td>'."\n";
		$output .= empty($thevalue['subjectimage']) ? '' : '<a href="'.$thevalue['subjectimage'].'" target="_blank"><img src="'.$thevalue['subjectthumb'].'"></a>'."\n";
		$output .= '</td>'."\n";
		$output .= '</tr>'."\n";
		$output .= '<tr>'."\n";
		$output .= '<th>'.$alang['select_info_date_title'].'</th>'."\n";
		$output .= '<td>'."\n";
		$output .= sgmdate($thevalue['dateline'])."\n";
		$output .= '</td>'."\n";
		$output .= '</tr>'."\n";
		$output .= '<tr>'."\n";
		$output .= '<th>'.$alang['space_title_catid'].'</th>'."\n";
		$output .= '<td>'."\n";
		$output .= empty($categorylistarr[$thevalue['catid']]) ? '' : $categorylistarr[$thevalue['catid']]['name']."\n";
		$output .= '</td>'."\n";
		$output .= '</tr>'."\n";
		$output .= '<tr>'."\n";
		$output .= '<th>'.$alang['content_title'].'</th>'."\n";
		$output .= '<td>'."\n";
		$output .= $thevalue['message']."\n";
		$output .= '</td>'."\n";
		$output .= '</tr>'."\n";
		if(!empty($cacheinfo['columns'])) {
			foreach($cacheinfo['columns'] as $temp) {
				$tmpvalue = trim($thevalue[$temp['fieldname']]);
				if((empty($temp['isfile']) && strlen($tmpvalue) > 0) || (!empty($temp['isfile']) && $tmpvalue != 0)) {
					if($temp['formtype'] == 'checkbox') {
						$tmpvalue = explode("\n", $thevalue[$temp['fieldname']]);
					} elseif($temp['formtype'] == 'textarea' && empty($temp['ishtml'])) {
						$tmpvalue = str_replace("\n", '<br />', $thevalue[$temp['fieldname']]);
					}
			
					$temp['filepath'] = '';
					if(!empty($temp['isimage']) || !empty($temp['isflash'])) {
						$temp['filepath'] = A_URL.'/'.$tmpvalue;
					} elseif(!empty($temp['isfile'])) {
						$temp['filepath'] = rawurlencode(authcode($resultmodels['modelname'].','.$tmpvalue, 'ENCODE'));
					}
					$columnsinfoarr[] = array(
							'fieldname'	=>	$temp['fieldname'],
							'fieldcomment'	=>	$temp['fieldcomment'],
							'fieldtype'	=>	$temp['fieldtype'],
							'formtype'	=>	$temp['formtype'],
							'ishtml'	=>	$temp['ishtml'],
							'isbbcode'	=>	$temp['isbbcode'],
							'isfile'	=>	$temp['isfile'],
							'isimage'	=>	$temp['isimage'],
							'isflash'	=>	$temp['isflash'],
							'filepath'	=>	$temp['filepath'],
							'value'	=>	$tmpvalue
					);
				}
			}

			$value['fieldcomment'] .= '<p>';
			if(!empty($value['ishtml'])) {
				$value['fieldcomment'] .= $alang['fieldcomment_help'];
			}

			if(preg_match("/^(TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT|FLOAT|DOUBLE)$/i", $value['fieldtype']) && !preg_match("/^(select|radio|checkbox|file|img|flash)$/i", $value['formtype'])) {
				$value['fieldcomment'] .= $alang['field_only_int'];
			}
			if(preg_match("/^(text|textarea)$/i", $value['formtype']) && !preg_match("/^(TEXT|MEDIUMTEXT|LONGTEXT)$/i", $value['fieldtype'])) {
				$value['fieldcomment'] .= $alang['field_length_1'].$value['fieldlength'].$alang['field_length_2'];
			}
			$value['fieldcomment'] .= '</p>';
			
			if(!empty($columnsinfoarr)) {
				foreach($columnsinfoarr as $tmpkey => $tmpvalue) {
					$output .= '<tr>'."\n";
					$output .= '<th>'.$tmpvalue['fieldcomment'].'</th>'."\n";
					$output .= '<td>'."\n";
					if(!empty($tmpvalue['isflash'])) {
						$output .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="400" height="300">';
						$output .= '<param name="movie" value="'.$tmpvalue['filepath'].'" />';
						$output .= '<param name="quality" value="high" />';
						$output .= '<embed src="'.$tmpvalue['filepath'].'" type="application/x-shockwave-flash" pluginspage=" http://www.macromedia.com/go/getflashplayer" width="400" height="300"/>';
						$output .= '</object>';
					} elseif(!empty($tmpvalue['isfile'])) {
						$output .= '<a href="'.$siteurl.'/batch.modeldownload.php?hash='.$tmpvalue['filepath'].'">'.$alang['download_title'].'</a>';
					} elseif(!empty($tmpvalue['isimage'])) {
						$output .= '<a href="'.$tmpvalue['filepath'].'" target="_blank"><img src="'.$tmpvalue['filepath'].'"></a>';
					} else {
						if($tmpvalue['formtype'] == 'timestamp') {
							$tmpvalue['value'] = sgmdate($tmpvalue['value']);
						}
						$output .= !is_array($tmpvalue['value']) ? $tmpvalue['value'] : implode(', ', $tmpvalue['value']);
					}
					$output .= '</td>'."\n";
					$output .= '</tr>'."\n";
				}
			}
		}
		$output .= '</table>';
		if($_GET['folder']) {
			$output .= label(array('type'=>'form-start', 'name'=>'listform', 'action'=>CPURL.'?action=modelfolders', 'other'=>' onSubmit="return listsubmitconfirm(this)"'));
			$output .= '<input value="3" name="operation" type="hidden" /><input type="hidden" name="mid" value="'.$_GET['mid'].'"><input name="listvaluesubmit" type="hidden" value="yes" /><input type="hidden" value="'.$itemid.'" name="item[]"/>';
			$output .= '<div class="buttons">';
			$output .= label(array('type'=>'button-submit', 'name'=>'listsubmit', 'value'=>$alang['completely_erased']));
			$output .= '</div>'."\n";
			$output .= label(array('type'=>'form-end'));
		}
		echo $output;
	}
}

?>