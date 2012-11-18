<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_check.php 13470 2009-11-04 07:19:53Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//È¨ÏÞ
if(!checkperm('managecheck')) {
	showmessage('no_authority_management_operation');
}

if(!empty($_GET['opuid'])) {
	$opuid = intval($_GET['opuid']);
} else {
	$opuid = 0;
}
$theurl = $theurl.'&opuid='.$opuid;

//ÉóºË¼¶±ð
$gradearr = array(
	'1' => $alang['check_grade_1'],
	'2' => $alang['check_grade_2'],
	'3' => $alang['check_grade_3'],
	'4' => $alang['check_grade_4'],
	'5' => $alang['check_grade_5']
);
if(!empty($_SCONFIG['checkgrade'])) {
	$newgradearr = explode("\t", $_SCONFIG['checkgrade']);
	for($i=0; $i<5; $i++) {
		if(!empty($newgradearr[$i])) $gradearr[$i+1] = $newgradearr[$i];
	}
}

//¹Ø±ÕÖ÷Ìâ
$allowreplyarr = array(
	'1' => $alang['space_allowreply_1'],
	'-1' => $alang['space_allowreply_0']
);

$perpage = 50;

//CHECK GET VAR
$type = postget('type');
if(!in_array($type, $_SGLOBAL['type'])) $type = 'news';
$grade = intval(postget('grade'));
if($grade < -1 || $grade > 5) $grade = 0;
if($grade == -1) $status = 'ban';
$status = postget('status');
if(!in_array($status, array('no', 'yes', 'help', 'delete', 'ban'))) $status = 'no';
$searchkey = trim(postget('searchkey'));
$searchtype = postget('searchtype');

$page = intval(postget('page'));
($page<1)?$page=1:'';
$start = ($page-1)*$perpage;

//INIT RESULT VAR
$listarr = array();
$thevalue = array();

//POST METHOD
if(submitcheck('listsubmit')) {

	if(!empty($_POST['item'])) {
		$itemidstr = implode('\',\'', $_POST['item']);
	} else {
		showmessage('space_no_item');
	}

	$tablename = $_GET['status'] == 'delete' ? 'postitems' : 'spaceitems';
	switch ($_POST['operation']) {
		case 'check':
			if($_GET['status'] == 'delete') showmessage('no_action_item');
			$_SGLOBAL['db']->query('UPDATE '.tname($tablename).' SET grade=\''.intval($_POST['opgrade']).'\' WHERE itemid IN (\''.$itemidstr.'\')');
			break;
		case 'ban':
			if($_GET['status'] == 'delete') showmessage('no_action_item');
			$_SGLOBAL['db']->query('UPDATE '.tname($tablename).' SET grade=\'-1\' WHERE itemid IN (\''.$itemidstr.'\')');
			break;
		case 'reply':
			$_SGLOBAL['db']->query('UPDATE '.tname($tablename).' SET allowreply=\''.intval($_POST['allowreply']).'\' WHERE itemid IN (\''.$itemidstr.'\')');
			break;
		case 'delete':
			//É¾³ýhtml
			deleteitems('itemid', '\''.$itemidstr.'\'', 1);
			break;
		case 'movecat':
			$catarr = explode('_', $_POST['opcatid']);
			$_SGLOBAL['db']->query('UPDATE '.tname($tablename).' SET type=\''.$catarr[0].'\', catid=\''.$catarr[1].'\' WHERE itemid IN (\''.$itemidstr.'\')');
			break;
		case 'republish':
			moveitemfolder('\''.$itemidstr.'\'', 2, 0);
			break;
		case 'remove':
			if($_GET['status'] != 'delete') {	//·¢²¼Ïä->É¾³ý
				deleteitems('itemid', $itemidstr, 0);
			} else {	//´ýÉóÏä->É¾³ý
				deleteitems('itemid', $itemidstr, 0, 1);
			}
			break;
	}
	showmessage('check_op_ok', $theurl.'&page='.$page.'&type='.$type.'&grade='.$grade.'&status='.$status.'&searchtype='.$searchtype.'&searchkey='.rawurlencode($searchkeys));
}

//GET METHOD
$viewclass_help = $viewclass_no = $viewclass_ban = $viewclass_delete = $viewclass_yes = '';
if($status == 'yes') {
	$viewclass_yes = ' class="active"';
} elseif($status == 'help') {
	$viewclass_help = ' class="active"';
} elseif($status == 'delete') {
	$viewclass_delete = ' class="active"';
} elseif($status == 'ban') {
	$viewclass_ban = ' class="active"';
} else {
	$viewclass_no = ' class="active"';
}
if (empty($_GET['op']) && ($status == 'no' || $status == 'yes' || $status == 'delete' || $status == 'ban')) {
	//LIST VIEW
	$wheresqlarr = array();
	if(!empty($opuid)) $wheresqlarr[] = 'i.uid=\''.$opuid.'\'';
	if(!empty($type)) $wheresqlarr[] = 'i.type=\''.$type.'\'';
	if($status == 'delete') {
		$wheresqlarr[] = 'i.folder=2';
		$tablename = 'postitems';
	} else {
		$tablename = 'spaceitems';
		if($status == 'yes') {
			if(empty($grade)) {
				$wheresqlarr[] = 'i.grade>0';
			} else {
				$wheresqlarr[] = 'i.grade=\''.$grade.'\'';
			}
		} elseif($status == 'ban') {
			$wheresqlarr[] = 'i.grade=\'-1\'';
		} else {
			$wheresqlarr[] = 'i.grade=0';
		}
	}
	if($searchkeys = stripsearchkey($searchkey)) {
		if($searchtype == 'username') {
			$wheresqlarr[] = 'i.username LIKE \'%'.$searchkeys.'%\'';
		} else {
			$wheresqlarr[] = 'i.subject LIKE \'%'.$searchkeys.'%\'';
		}
	}
	$wheresqlstr = implode(' AND ', $wheresqlarr);
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname($tablename).' i WHERE '.$wheresqlstr);
	$listcount = $_SGLOBAL['db']->result($query, 0);
	$multipage = '';
	if($listcount) {
		$query = $_SGLOBAL['db']->query('SELECT i.*, f.name FROM '.tname($tablename).' i LEFT JOIN '.tname('categories').' f ON f.catid=i.catid WHERE '.$wheresqlstr.' ORDER BY i.dateline DESC LIMIT '.$start.','.$perpage);
		while ($item = $_SGLOBAL['db']->fetch_array($query)) {
			$item['url'] = geturl('action/viewnews/itemid/'.$item['itemid']);
			$listarr[] = $item;
		}
		$multipage = multi($listcount, $perpage, $page, $theurl.'&type='.$type.'&grade='.$grade.'&status='.$status.'&searchtype='.$searchtype.'&searchkey='.rawurlencode($searchkeys));
	}
}

//SHOW HTML
//MENU
if(empty($type)) {
	$alang_type = $alang['check_view_all'];
} else {
	$alang_type = $alang['common_type_'.$type];
}

echo '
<div id="newslisttab">
	<ul>
		<li>'.$alang['channel_name'].'</li>';
		foreach($channels['types'] as $value) {
			echo '<li'.($type == $value['nameid'] ? ' class="active"' : '').'><a href="'.$theurl.'&type='.$value['nameid'].'&folder='.$_SGET['folder'].'">'.$value['name'].'</a></li>';
		}
echo '
	</ul>
</div>
';
//ËÑË÷
if(empty($_GET['op']) && ($status == 'no' || $status == 'yes')) {
	echo label(array('type'=>'form-start', 'name'=>'searchform', 'action'=>$theurl.'&type='.$type.'&grade='.$grade.'&status='.$status));
	echo label(array('type'=>'table-start', 'class'=>'toptable'));
	echo '<tr><td>';
	echo '<input type="text" name="searchkey" size="20" value="" /> ';
	echo '<select name="searchtype"><option value="subject">'.$alang['spacenews_title_subject'].'</option><option value="username">'.$alang['check_username'].'</option></select> ';
	echo '<input type="submit" name="viewsubmit" value="'.$alang['check_search'].'" />';
	echo '</td></tr>';
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'form-end'));
}
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['admincp_header_check'].'('.$alang_type.')</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass_no.'><a href="'.$theurl.'&type='.$type.'&status=no" class="view">'.$alang['check_no'].'</a></td>
					<td'.$viewclass_yes.'><a href="'.$theurl.'&type='.$type.'&grade='.$grade.'&status=yes" class="view">'.$alang['check_yes'].'</a></td>
					<td'.$viewclass_delete.'><a href="'.$theurl.'&type='.$type.'&status=delete">'.$alang['check_delete'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';
if($status == 'no' && $page == 1) echo label(array('type'=>'help', 'text'=>$alang['help_check']));
//MENU
if(empty($_GET['op']) && $status == 'yes') {
	$active = '';
	if(empty($_GET['grade'])) $active = ' class="active"';
	echo '<div id="listtab">';
	echo '<div class="listtab"><a href="'.$theurl.'&type='.$type.'&status='.$status.'"'.$active.'>'.$alang['check_view_all'].'</a></div>';
	foreach ($gradearr as $gkey => $gvalue) {
		$active = '';
		if(!empty($_GET['grade']) && $_GET['grade'] == $gkey) {
			$active = ' class="active"';
		}
		echo '<div class="listtab"><a href="'.$theurl.'&type='.$type.'&grade='.$gkey.'&status='.$status.'"'.$active.'>'.$gvalue.'</a></div>';
	}
	echo '</div>';
}

//LIST SHOW
if(is_array($listarr) && $listarr) {

	$adminmenuarr = array(
		'noop' => $alang['space_no_op'],
		'check' => $alang['check_op_check'],
		'reply' => $alang['management_comments'],
		'delete' => $alang['space_delete_1'],
		'remove' => $alang['completely_erased']
	);
	
	$uplistarr = array();
	if(!empty($type)) {
		$uplistarr = getcategory($type);
		$adminmenuarr['movecat'] = $alang['check_op_movecat'];
	}

	$adminmenu = $alang['space_batch_op'].'</th><th>';
	$adminmenu .= '<input type="checkbox" name="chkall" onclick="checkall(this.form, \'item\')">'.$alang['space_select_all'];
	
	if($status == 'delete') {
		$adminmenu .= '<input type="radio" name="operation" value="remove" checked>'.$alang['directly_delete'];
		$adminmenu .= '<input type="radio" name="operation" value="republish">'.$alang['check_folder'];
		$htmlarr['html'] = '<tr><th width="12%">'.$adminmenu.'</th></tr>';
	} else {
		foreach ($adminmenuarr as $key => $value) {
			if($key == 'noop') {
				$acheck = ' checked';
			} else {
				$acheck = '';
			}
			$adminmenu .= '<input type="radio" name="operation" value="'.$key.'" onClick="jsop(this.value)"'.$acheck.'> '.$value;
		}
		
		$admintbl['noop'] = '<tr id="divnoop" style="display:none"><td></td><td></td></tr>';
		$admintbl['delete'] = '<tr id="divdelete" style="display:none"><td></td><td></td></tr>';
		$admintbl['remove'] = '<tr id="divdelete" style="display:none"><td></td><td></td></tr>';
		$admintbl['ban'] = '<tr id="divban" style="display:none"><td></td><td></td></tr>';
		$admintbl['remove'] = '<tr id="divremove" style="display:none"><td></td><td></td></tr>';
		$admintbl['reply'] = label(array('type'=>'radio', 'alang'=>'check_op_check', 'name'=>'allowreply', 'id'=>"divreply", 'options'=>$allowreplyarr, 'value'=>'1', 'display'=>'none'));
		$gradearr[0] = $alang['general_state'];
		$admintbl['check'] = label(array('type'=>'radio', 'alang'=>'check_op_check', 'name'=>'opgrade', 'id'=>"divcheck", 'options'=>$gradearr, 'value'=>'0', 'display'=>'none'));
		if(!empty($uplistarr)) {
			$admintbl['movecat'] = label(array('type'=>'select-div', 'alang'=>'space_op_category', 'name'=>'opcatid', 'id'=>"divmovecat", 'radio'=>1, 'options'=>$uplistarr, 'display'=>'none'));
		}
		
		$htmlarr = array();
		$htmlarr['js'] = '
		<script language="javascript">
		<!--
		function jsop(radionvalue) {'."\n";
		foreach ($adminmenuarr as $adminkey => $adminvalue) {
			$htmlarr['js'] .= 'document.getElementById(\'div'.$adminkey.'\').style.display = "none";'."\n";
		}
		$htmlarr['js'] .= '
		if(radionvalue == \'noop\' || radionvalue == \'delete\' || radionvalue == \'ban\' || radionvalue == \'remove\') {
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
		
		echo $htmlarr['js'];
	}
	
	echo label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$theurl.'&page='.$page.'&type='.$type.'&grade='.$grade.'&status='.$status.'&searchtype='.$searchtype.'&searchkey='.rawurlencode($searchkeys)));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));

	echo '<tr>';
	echo '<th width="40">'.$alang['space_select'].'</th>';
	echo '<th width="40">'.$alang['check_type'].'</th>';
	echo '<th>'.$alang['spacenews_title_subject'].'</th>';
	echo '<th width="100">'.$alang['check_catname'].'</th>';
	echo '<th width="100">'.$alang['check_username'].'</th>';
	echo '<th width="140">'.$alang['check_dateline'].'</th>';
	echo '<th width="60">'.$alang['check_grade'].'</th>';
	echo '</tr>';

	foreach ($listarr as $listvalue) {
		empty($class) ? $class=' class="darkrow"': $class='';
		$subjectpre = getsubjectpre($listvalue);

		echo '<tr'.$class.'>';
		echo '<td align="center"><input type="checkbox" name="item[]" value="'.$listvalue['itemid'].'" /></td>';
		echo '<td align="center">'.$channels['types'][$listvalue['type']]['name'].'</td>';
		echo '<td>'.$subjectpre.'<a href="'.$listvalue['url'].'" target="_blank">'.$listvalue['subject'].'</a></td>';
		echo '<td align="center">'.$listvalue['name'].'</td>';
		echo '<td align="center">'.($listvalue['uid'] ? '<a href="'.S_URL.'/space.php?uid='.$listvalue['uid'].'" target="_blank">'.$listvalue['username'].'</a>' : $alang['check_guest']).'</td>';
		echo '<td align="center">'.sgmdate($listvalue['dateline']).'</td>';
		echo '<td align="center">'.($status == 'delete'?'-':$gradearr[$listvalue['grade']]).'</td>';
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
	echo label(array('type'=>'hidden', 'name'=>'type', 'value'=>$type));
	echo '</div>';
	echo label(array('type'=>'form-end'));
}

?>