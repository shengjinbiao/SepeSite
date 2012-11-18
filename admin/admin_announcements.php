<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_announcements.php 13470 2009-11-04 07:19:53Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageannouncements')) {
	showmessage('no_authority_management_operation');
}

$perpage = 20;

//CHECK GET VAR
$page = intval(postget('page'));
($page < 1) ? $page= 1 : '';
$start = ($page - 1) * $perpage;

//初始化数组
$thevalue = array();
$listarr = array();

//得到POST过来的参数变量
if(submitcheck('listvaluesubmit')) {
	
	if(!empty($_POST['delete']) && is_array($_POST['delete'])) {
		$ids = implode('\',\'', $_POST['delete']);
		$_SGLOBAL['db']->query('DELETE FROM '.tname('announcements').' WHERE id IN (\''.$ids.'\')');
	}

	if(!empty($_POST['displayordernew']) && is_array($_POST['displayordernew'])) {
		foreach($_POST['displayordernew'] as $id => $displayorder) {
			$_SGLOBAL['db']->query('UPDATE '.tname('announcements').' SET displayorder=\''.intval($displayorder).'\' WHERE id=\''.$id.'\'');
		}
	}
	updateannouncementcache();
	showmessage('announcements_list_update', $theurl);
	
} elseif(submitcheck('valuesubmit')) {
	
	$_POST['subject'] = shtmlspecialchars(trim($_POST['subject']));
	if($_SCONFIG['charset'] == 'utf-8') {
		$subjectlen = strlen(utf8_decode($_POST['subject']));
	} else {
		$subjectlen = strlen($_POST['subject']);
	}
	if($subjectlen < 2 || $subjectlen > 80) {
		showmessage('space_suject_length_error');
	}
	if(empty($_POST['starttime']) || (!empty($_POST['endtime']) && sstrtotime($_POST['endtime']) <= sstrtotime($_POST['starttime']))) {
		showmessage('announcements_time_error');
	} 
	if(empty($_POST['message'])) {
		showmessage('announcements_no_message');
	} 

	$setsqlarr = array();
	$setsqlarr['subject'] = addslashes($_POST['subject']);
	$setsqlarr['starttime'] = sstrtotime($_POST['starttime']);
	if(!empty($_POST['endtime'])) {
		$setsqlarr['endtime'] = sstrtotime($_POST['endtime']);
	} else {
		$setsqlarr['endtime'] = '';
	}
	$setsqlarr['announcementsurl'] = shtmlspecialchars(trim($_POST['announcementsurl']));
	$setsqlarr['message'] = $_POST['message'];
	
	if(empty($_POST['id'])) {
		$setsqlarr['uid'] = $_SGLOBAL['supe_uid'];
		$setsqlarr['author'] = $_SGLOBAL['supe_username'];
		inserttable('announcements', $setsqlarr);
		updateannouncementcache();
		showmessage('announcements_add_succeed', $theurl);
	} else {
		$wheresqlarr = array('id'=>$_POST['id']);
		updatetable('announcements', $setsqlarr, $wheresqlarr);
		updateannouncementcache();
		showmessage('announcements_update_succeed', $theurl);
	}
	
}

//得到GET过来的参数变量
$addclass = $viewclass = $overdueclass = '';
if(empty($_GET['op'])) {
	
	//LIST VIEW
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('announcements').' WHERE starttime <= \''.$_SGLOBAL['timestamp'].'\' AND (endtime >= \''.$_SGLOBAL['timestamp'].'\' OR endtime = 0)');
	$listcount = $_SGLOBAL['db']->result($query, 0);
	$multipage = '';
	if($listcount) {	
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('announcements').' WHERE starttime <= \''.$_SGLOBAL['timestamp'].'\' AND (endtime >= \''.$_SGLOBAL['timestamp'].'\' OR endtime = 0) ORDER BY displayorder, starttime DESC, id DESC LIMIT '.$start.','.$perpage);
		while($item = $_SGLOBAL['db']->fetch_array($query)) {
			$listarr[] = $item;
		} 
		$multipage = multi($listcount, $perpage, $page, $theurl);
	}
	$viewclass = ' class="active"';

} elseif($_GET['op'] == 'overdue') {
	
	//LIST VIEW
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('announcements').' WHERE starttime > \''.$_SGLOBAL['timestamp'].'\' OR (endtime < \''.$_SGLOBAL['timestamp'].'\' AND endtime != 0)');
	$listcount = $_SGLOBAL['db']->result($query, 0);
	$multipage = '';
	if($listcount) {	
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('announcements').' WHERE starttime > \''.$_SGLOBAL['timestamp'].'\' OR (endtime < \''.$_SGLOBAL['timestamp'].'\' AND endtime != 0) ORDER BY displayorder, starttime DESC, id DESC LIMIT '.$start.','.$perpage);
		while($item = $_SGLOBAL['db']->fetch_array($query)) {
			$listarr[] = $item;
		} 		
		$multipage = multi($listcount, $perpage, $page, $theurl);
	}
	$overdueclass = ' class="active"';

} elseif($_GET['op'] == 'edit') {
	
	if(!empty($_GET['id'])) {
		$_GET['id'] = intval($_GET['id']);
	} 
	if(empty($_GET['id'])) {
		showmessage('announcements_no_id');
	}
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('announcements').' WHERE id=\''.$_GET['id'].'\'');
	if(!$thevalue = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('announcements_no_id');
	}
	
} elseif($_GET['op'] == 'add') {

	//ONE ADD
	$thevalue = array(
		'id' => 0,
		'subject' => '',
		'starttime' => $_SGLOBAL['timestamp'],
		'endtime' => '',
		'message' => ''
	);
	$addclass = ' class="active"';
}

//HTML
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['announcements_title'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass.'><a href="'.$theurl.'" class="view">'.$alang['announcements_list'].'</a></td>
					<td'.$overdueclass.'><a href="'.$theurl.'&op=overdue" class="view">'.$alang['announcements_overdue_list'].'</a></td>
					<td'.$addclass.'><a href="'.$theurl.'&op=add" class="add">'.$alang['announcements_add'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';

if(empty($_GET['op'])) {
	echo label(array('type'=>'help', 'text'=>$alang['help_announcements']));
}

//显示公告列表数据
if(is_array($listarr) && $listarr) {

	echo label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$theurl));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));
	echo '<tr>';
	echo '<th>'.$alang['announcements_select'].'</th>';
	echo '<th>'.$alang['announcements_author'].'</th>';
	echo '<th>'.$alang['announcements_subject'].'</th>';
	echo '<th>'.$alang['announcements_starttime'].'</th>';
	echo '<th>'.$alang['announcements_endtime'].'</th>';
	echo '<th>'.$alang['announcements_displayorder'].'</th>';
	echo '<th>'.$alang['announcements_option'].'</th>';
	echo '</tr>';
	
	$adminmenu = '<input name="del" type="radio" value="1" checked> '.$alang['poll_delete'];
	
	foreach ($listarr as $listvalue) {
		empty($class) ? $class=' class="darkrow"': $class='';
		$listvalue['starttime'] = $listvalue['starttime'] ? sgmdate($listvalue['starttime']) : $alang['announcements_no_limit'];
		$listvalue['endtime'] = $listvalue['endtime'] ? sgmdate($listvalue['endtime']) : $alang['announcements_no_limit'];
		echo '<tr'.$class.'>';
		echo '<td><input type="checkbox" name="delete[]" value="'.$listvalue['id'].'" /></td>';
		echo '<td><a href="'.S_URL.'/space.php?uid='.$listvalue['uid'].'" target="_blank">'.$listvalue['author'].'</a></td>';
		echo '<td><a href="'.geturl('action/announcement/id/'.$listvalue['id']).'" target="_blank">'.$listvalue['subject'].'</a></td>';
		echo '<td>'.$listvalue['starttime'].'</td>';
		echo '<td>'.$listvalue['endtime'].'</td>';
		echo '<td><input type="text" size="2" name="displayordernew['.$listvalue['id'].']" value="'.$listvalue['displayorder'].'" ></td>';
		echo '<td><a href="'.$theurl.'&op=edit&id='.$listvalue['id'].'">'.$alang['space_edit'].'</a></td>';
		echo '</tr>';
	}
	
	echo label(array('type'=>'table-start', 'class'=>'btmtable'));
	echo '<tr><th><input type="checkbox" name="chkall" onclick="checkall(this.form, \'delete\')">'.$alang['space_select_all'].' '.$adminmenu.'</th></tr>';	
	echo label(array('type'=>'table-end'));
	
	if(!empty($multipage)) {
		echo label(array('type'=>'table-start', 'class'=>'listpage'));
		echo '<tr><td>'.$multipage.'</td></tr>';
		echo label(array('type'=>'table-end'));
	}
	
	echo label(array('type'=>'table-end'));
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'listsubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'listreset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo '<input name="listvaluesubmit" type="hidden" value="yes" />';
	echo label(array('type'=>'form-end'));
}

if(is_array($thevalue) && $thevalue) {
	//新增或修改表单
	echo '<script language="javascript" src="'.S_URL.'/include/js/selectdate.js"></script>';
	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$theurl));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'input', 'alang'=>$alang['announcements_subject'], 'name'=>'subject', 'value'=>$thevalue['subject']));
	echo label(array('type'=>'input', 'alang'=>'spaceannouncements_title_announcementsurl', 'name'=>'announcementsurl', 'size'=>60, 'value'=>$thevalue['announcementsurl']));
	echo label(array('type'=>'text', 'alang'=>$alang['announcements_starttime'], 'text'=>'<input name="starttime" readonly type="text" id="starttime" value="'.sgmdate($thevalue['starttime']).'"><img src="'.S_URL.'/admin/images/time.gif" onClick="getDatePicker(\'starttime\', event, 21)"/>'));
	echo label(array('type'=>'text', 'alang'=>$alang['announcements_endtime_message'], 'text'=>'<input name="endtime" readonly type="text" id="endtime" value="'.($thevalue['endtime']?sgmdate($thevalue['endtime']):'').'"><img src="'.S_URL.'/admin/images/time.gif" onClick="getDatePicker(\'endtime\', event, 21)"/>'));
	echo label(array('type'=>'table-end'));
	
	echo label(array('type'=>'table-start', 'class'=>'edittable'));
	echo label(array('type'=>'edit', 'alang'=>$alang['announcements_message'], 'name'=>'message', 'value'=>$thevalue['message'], 'op'=>0));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit'], 'other'=>' onclick="publish_article();"'));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo '<input name="id" type="hidden" value="'.$thevalue['id'].'" />';
	echo '<input name="valuesubmit" type="hidden" value="yes" />';
	echo label(array('type'=>'form-end'));
}
?>