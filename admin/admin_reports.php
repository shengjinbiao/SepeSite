<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_reports.php 13368 2009-09-23 06:53:35Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managereports')) {
	showmessage('no_authority_management_operation');
}

$perpage = 50;
$status = postget('status');
if(!in_array($status, array('no', 'yes'))) $status = 'no';
$page = intval(postget('page'));
($page < 1) ? $page = 1 : '';
$start = ($page - 1) * $perpage;
$listarr = array();

if(submitcheck('listsubmit')) {
	
	if(!empty($_POST['item'])) {
		$itemidstr = implode('\',\'', $_POST['item']);
	} else {
		showmessage('space_no_item');
	}
	
	switch ($_POST['operation']) {
		case 'noproblem':
			$_SGLOBAL['db']->query('UPDATE '.tname('reports').' SET status=\'1\' WHERE itemid IN (\''.$itemidstr.'\')');
			break;
		case 'remove':
			//积分 和 经验
			$uids = getuids($_POST['item'], 'reports', 'reportuid');
			deleteitems('itemid', $_POST['item']);
			$_SGLOBAL['db']->query('DELETE FROM '.tname('reports').' WHERE itemid IN (\''.$itemidstr.'\')');
			foreach($uids as $value) {
				getreward('report', 1, $value);
			}
			break;
		case 'rush':
			deleteitems('itemid', $_POST['item'], 1);
			$_SGLOBAL['db']->query('DELETE FROM '.tname('reports').' WHERE itemid IN (\''.$itemidstr.'\')');
			break;
	}
	showmessage('report_op_ok', $theurl.'&page='.$page.'&status='.$status);
}
//GET METHOD
$viewclass_no = $viewclass_yes = '';
if($status == 'yes') {
	$viewclass_yes = ' class="active"';
} else {
	$viewclass_no = ' class="active"';
}


//LIST VIEW
$wheresqlarr = array();
if($status == 'yes') {
	$wheresqlarr[] = 'r.status>0';
} else {
	$wheresqlarr[] = 'r.status=0';
}
$wheresqlstr = implode(' AND ', $wheresqlarr);
$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('reports').' r WHERE '.$wheresqlstr);
$listcount = $_SGLOBAL['db']->result($query, 0);
$multipage = '';
if($listcount) {
	$query = $_SGLOBAL['db']->query('SELECT i.*, r.* FROM '.tname('reports').' r LEFT JOIN '.tname('spaceitems').' i ON i.itemid=r.itemid WHERE '.$wheresqlstr.' ORDER BY r.reportid DESC LIMIT '.$start.','.$perpage);
	while ($item = $_SGLOBAL['db']->fetch_array($query)) {
		$item['url'] = S_URL.'/admincp.php?action=reportmanage&uid='.$item['uid'];
		$listarr[] = $item;
	}
	$multipage = multi($listcount, $perpage, $page, $theurl.'&status='.$status);
}

echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['report_title'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass_no.'><a href="'.$theurl.'&status=no" class="view">'.$alang['not_translated_report'].'</a></td>
					<td'.$viewclass_yes.'><a href="'.$theurl.'&status=yes" class="view">'.$alang['translated_report'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';

echo label(array('type'=>'help', 'text'=>$alang['help_report']));

//LIST SHOW
if(is_array($listarr) && $listarr) {

	$adminmenuarr = array(
		'rush' => $alang['space_delete_1'],
		'remove' => $alang['completely_erased']
	);
	
	if($status == 'no') $adminmenuarr['noproblem'] = $alang['cancel_report'];
	
	$adminmenu = '<input type="checkbox" name="chkall" onclick="checkall(this.form, \'item\')">'.$alang['space_select_all'];
	foreach ($adminmenuarr as $key => $value) {
		$adminmenu .= '<input type="radio" name="operation" value="'.$key.'"> '.$value;
	}
	
	echo label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$theurl));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));

	echo '<tr>';
	echo '<th width="40">'.$alang['space_select'].'</th>';
	echo '<th width="40">'.$alang['check_type'].'</th>';
	echo '<th>'.$alang['spacenews_title_subject'].'</th>';
	echo '<th width="100">'.$alang['check_username'].'</th>';
	echo '<th width="100">'.$alang['reporter'].'</th>';
	echo '<th width="140">'.$alang['check_dateline'].'</th>';
	echo '<th width="60">'.$alang['translated_status'].'</th>';
	echo '</tr>';

	foreach ($listarr as $listvalue) {
		empty($class) ? $class=' class="darkrow"': $class='';
		$subjectpre = getsubjectpre($listvalue);

		echo '<tr'.$class.'>';
		echo '<td align="center"><input type="checkbox" name="item[]" value="'.$listvalue['itemid'].'" /></td>';
		echo '<td align="center">'.$alang['common_type_'.$listvalue['type']].'</td>';
		echo '<td>'.$subjectpre.'<a href="'.$listvalue['url'].'" target="_blank">'.$listvalue['subject'].'</a></td>';
		echo '<td align="center"><a href="'.S_URL.'/space.php?uid='.$listvalue['uid'].'" target="_blank">'.$listvalue['username'].'</a></td>';
		if($listvalue['reportuid'] > 0){
			echo '<td align="center"><a href="'.S_URL.'/space.php?uid='.$listvalue['reportuid'].'" target="_blank">'.$listvalue['reporter'].'</a></td>';
		} else {
			echo '<td align="center">'.$listvalue['reporter'].'</a></td>';
		}
		echo '<td align="center">'.sgmdate($listvalue['reportdate']).'</td>';
		echo '<td align="center">'.($status == 'no'?$alang['not_translated']:$alang['translated']).'</td>';
		echo '</tr>';
	}
	echo label(array('type'=>'table-end'));
	
	echo label(array('type'=>'table-start', 'class'=>'btmtable'));
	echo '<tr><th>'.$alang['space_batch_op'].'</th><td>';
	echo $adminmenu;
	echo '</td></tr>';
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
	echo label(array('type'=>'form-end'));
}
?>
