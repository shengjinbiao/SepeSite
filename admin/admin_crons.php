<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_crons.php 11176 2009-02-23 08:44:44Z zhaolei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managecrons')) {
	showmessage('no_authority_management_operation');
}

include_once(S_ROOT.'./function/cron.func.php');

$perpage = 20;
$page = intval(postget('page'));
($page < 1) ? $page= 1 : '';
$start = ($page - 1) * $perpage;
$newurl = $theurl.'&page='.$page;

//INIT RESULT VAR
$listarr = array();
$thevalue = array();

//POST METHOD
if (submitcheck('listsubmit')) {
	//LIST UPDATE
	updatecronscache();
	showmessage('cron_update_success', $newurl);
	
} elseif (submitcheck('valuesubmit')) {
	
	//ONE UPDATE OR ADD
	$_POST['cronid'] = intval($_POST['cronid']);
	$_POST['name'] = shtmlspecialchars($_POST['name']);

	if(!is_readable(S_ROOT.'./include/cron/'.$_POST['filename'])) {
		showmessage('cron_error_no_filename');
	}
				
	if($_POST['weekday'] != '-1') {
		$_POST['day'] = '-1';
	}

	if(is_array($_POST['minute']) && $_POST['minute']) {
		foreach($_POST['minute'] as $key => $var) {
			if($var < 0 || $var > 59) {
				unset($_POST['minute'][$key]);
			}
		}
		sort($_POST['minute']);
		$_POST['minute'] = sarray_unique($_POST['minute']);
	}
	$postminute = implode("\t", $_POST['minute']);
	
	if($_POST['weekday'] == -1 && $_POST['day'] == -1 && $_POST['hour'] == -1 && $postminute == '') {
		showmessage('cron_error_no_time');
	}
	
	$sqlarr = array(
		'name' => $_POST['name'],
		'filename' => $_POST['filename'],
		'available' => $_POST['available'],
		'weekday' => $_POST['weekday'],
		'day' => $_POST['day'],
		'hour' => $_POST['hour'],
		'minute' => $postminute
	);

	if(empty($_POST['cronid'])) {
		//ADD
		$insertsqlarr = $sqlarr;
		$insertsqlarr['type'] = 'user';
		$insertsqlarr['nextrun'] = $_SGLOBAL['timestamp'];
		inserttable('crons', $insertsqlarr);
		
		//更新缓存
		updatecronscache();
		updatecroncache();
		showmessage('cron_add_success', $newurl);
	} else {
		//UPDATE
		$setsqlarr = $sqlarr;
		updatetable('crons', $setsqlarr, array('cronid'=>$_POST['cronid']));
		
		//更新缓存
		updatecronscache();
		updatecroncache();
		showmessage('cron_update_success', $newurl);
	}
}

//GET METHOD
$addclass = $viewclass = '';
if (empty($_GET['op'])) {

	//LIST VIEW
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('crons'));
	$listcount = $_SGLOBAL['db']->result($query, 0);
	$multipage = '';
	if($listcount) {
		$plussql = 'LIMIT '.$start.','.$perpage;
		$listarr = selecttable('crons', array(), array(), $plussql);
		$multipage = multi($listcount, $perpage, $page, $theurl);
	}
	$viewclass = ' class="active"';

} elseif ($_GET['op'] == 'edit') {

	//ONE VIEW FOR UPDATE
	$_GET['cronid'] = intval($_GET['cronid']);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('crons').' WHERE cronid=\''.$_GET['cronid'].'\'');
	if($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
	}

} elseif ($_GET['op'] == 'add') {
	//ONE ADD
	$thevalue = array(
		'cronid' => 0,
		'name' => '',
		'available' => 1,
		'filename' => '',
		'weekday' => '-1',
		'day' => '-1',
		'hour' => '-1',
		'minute' => ''
	);
	$addclass = ' class="active"';
		
} elseif ($_GET['op'] == 'delete') {
	//ONE DELETE
	$_GET['cronid'] = intval($_GET['cronid']);
	$_SGLOBAL['db']->query('DELETE FROM '.tname('crons').' WHERE cronid=\''.$_GET['cronid'].'\'');
	
	updatecronscache();
	showmessage('cron_delete_success', $newurl);

} elseif ($_GET['op'] == 'run') {
	$_GET['cronid'] = intval($_GET['cronid']);
	runcron($_GET['cronid']);
	showmessage('cron_run_success', $newurl);
}

//SHOW HTML
//MENU
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['cron_title'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass.'><a href="'.$newurl.'" class="view">'.$alang['cron_view_list'].'</a></td>
					<td'.$addclass.'><a href="'.$newurl.'&op=add" class="add">'.$alang['cron_add'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';

//LIST SHOW
if(is_array($listarr) && $listarr) {
	
	echo label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$theurl));
	echo label(array('type'=>'help', 'text'=>$alang['help_crons']));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));

	echo '<tr">';
	echo '<th>'.$alang['cron_name'].'</th>';
	echo '<th>'.$alang['cron_type'].'</th>';
	echo '<th>'.$alang['cron_available'].'</th>';
	echo '<th>'.$alang['cron_lastrun'].'</th>';
	echo '<th>'.$alang['cron_nextrun'].'</th>';
	echo '<th>'.$alang['space_op'].'</th>';
	echo '</tr>';

	foreach ($listarr as $listvalue) {
		if((!discuz_exists()) && ($listvalue['filename'] == 'updatebbsforums.php' || $listvalue['filename'] == 'updatebbscache.php')){
			continue;
		}
		empty($class) ? $class=' class="darkrow"': $class='';
		$listvalue['lastrun'] = sgmdate($listvalue['lastrun'], '', 0);
		$listvalue['nextrun'] = sgmdate($listvalue['nextrun'], '', 0);

		if(!$listvalue['available']) {
			$trbgcolor = '#CCCCCC';
			$listvalue['nextrun'] = '-';
		}
		
		echo '<tr'.$class.' align="center">';
		echo '<td align="left"><b>'.$listvalue['name'].'</b></td>';
		echo '<td>'.$alang['cron_type_'.$listvalue['type']].'</td>';
		echo '<td>'.$alang['cron_available_'.$listvalue['available']].'</td>';
		echo '<td>'.$listvalue['lastrun'].'</td>';
		echo '<td>'.$listvalue['nextrun'].'</td>';
		echo '<td align="left"><a href="'.$newurl.'&op=run&cronid='.$listvalue['cronid'].'">'.$alang['cron_run'].'</a> | <a href="'.$newurl.'&op=edit&cronid='.$listvalue['cronid'].'">'.$alang['space_edit'].'</a>';
		if($listvalue['type'] == 'user') echo ' | <a href="'.$newurl.'&op=delete&cronid='.$listvalue['cronid'].'">'.$alang['space_delete'].'</a>';
		echo '</td>';
		echo '</tr>';
	}
	echo label(array('type'=>'table-end'));
	
	if(!empty($multipage)) {
		echo label(array('type'=>'table-start', 'class'=>'listpage'));
		echo '<tr><td>'.$multipage.'</td></tr>';
		echo label(array('type'=>'table-end'));
	}
}

//THE VALUE SHOW
if(is_array($thevalue) && $thevalue) {
	
	$availablearr = array(
		'1' => $alang['cron_available_1'],
		'0' => $alang['cron_available_0']
	);
	
	$weekdayarr = array(
		'-1' => '*',
		'0' => $alang['cron_weekday_0'],
		'1' => $alang['cron_weekday_1'],
		'2' => $alang['cron_weekday_2'],
		'3' => $alang['cron_weekday_3'],
		'4' => $alang['cron_weekday_4'],
		'5' => $alang['cron_weekday_5'],
		'6' => $alang['cron_weekday_6']
	);
	
	$dayarr = array('-1'=>'*');
	for($i=1;$i<32;$i++) {
		$dayarr[$i] = $i;
	}
	
	$hourarr = array('-1'=>'*');
	for($i=0;$i<24;$i++) {
		$hourarr[$i] = $i;
	}
	
	$minuteselect = '';
	$cronminutearr = explode("\t", trim($thevalue['minute']));
	for($i = 0; $i < 12; $i++) {
		$minuteselect .= '<select name="minute[]"><option value="-1">*</option>';
		for($j = 0; $j <= 59; $j++) {
			$selected = '';
			if(isset($cronminutearr[$i]) && $cronminutearr[$i] == $j) {
				$selected = ' selected';
			}
			$minuteselect .= '<option value="'.$j.'"'.$selected.'>'.sprintf("%02d", $j).'</option>';
		}
		$minuteselect .= '</select>'.($i == 5 ? '<br>' : ' ');
	}
	
	$filearr = sreaddir(S_ROOT.'./include/cron/', 'php');
	
	echo '
	<script language="javascript">
	<!--
	function thevalidate(theform) {
		theform.thevaluesubmit.disabled = true;
		return true;
	}
	//-->
	</script>
	';
	
	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$theurl, 'other'=>' onSubmit="return thevalidate(this)"'));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'input', 'alang'=>'cron_title_name', 'name'=>'name', 'size'=>'30', 'width'=>'30%', 'value'=>$thevalue['name']));
	echo label(array('type'=>'radio', 'alang'=>'cron_title_available', 'name'=>'available', 'options'=>$availablearr, 'value'=>$thevalue['available']));
	echo label(array('type'=>'select', 'alang'=>'cron_title_weekday', 'name'=>'weekday', 'options'=>$weekdayarr, 'value'=>$thevalue['weekday']));
	echo label(array('type'=>'select', 'alang'=>'cron_title_day', 'name'=>'day', 'options'=>$dayarr, 'value'=>$thevalue['day']));
	echo label(array('type'=>'select', 'alang'=>'cron_title_hour', 'name'=>'hour', 'options'=>$hourarr, 'value'=>$thevalue['hour']));
	echo label(array('type'=>'text', 'alang'=>'cron_title_minute', 'text'=>$minuteselect));
	echo label(array('type'=>'select', 'alang'=>'cron_title_filename', 'name'=>'filename', 'options'=>$filearr, 'value'=>$thevalue['filename']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo '<input name="cronid" type="hidden" value="'.$thevalue['cronid'].'" />';
	echo '<input name="valuesubmit" type="hidden" value="yes" />';
	echo label(array('type'=>'form-end'));
}

?>