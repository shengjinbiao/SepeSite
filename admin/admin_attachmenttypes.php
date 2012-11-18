<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_attachmenttypes.php 13382 2009-10-09 07:06:41Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//х╗оч
if(!checkperm('manageattachmenttypes')) {
	showmessage('no_authority_management_operation');
}

$perpage = 20;
$urlplus = '';
$newurl = $theurl.$urlplus;
$page = intval(postget('page'));
($page<1)?$page=1:'';
$start = ($page-1)*$perpage;

//INIT RESULT VAR
$listarr = array();
$thevalue = array();

//POST METHOD
if(submitcheck('valuesubmit')) {

	//ONE UPDATE OR ADD
	$_POST['fileext'] = shtmlspecialchars(trim($_POST['fileext']));
	if(strlen($_POST['fileext']) < 1 || strlen($_POST['fileext']) > 10) {
		showmessage('attachmenttype_check_fileext');
	}

	$_POST['maxsize'] = intval($_POST['maxsize']);
	$_POST['maxsize'] = $_POST['maxsize'] * 1024;
	$sqlarr = array(
		'fileext' => $_POST['fileext'],
		'maxsize' => intval($_POST['maxsize'])
	);
	if(empty($_POST['id'])) {
		//ADD
		$insertsqlarr = $sqlarr;
		inserttable('attachmenttypes', $insertsqlarr);
		showmessage('attachmenttype_add_success', $newurl);
	} else {
		//UPDATE
		$setsqlarr = $sqlarr;
		updatetable('attachmenttypes', $setsqlarr, array('id'=>$_POST['id']));
		showmessage('attachmenttype_update_success', $newurl);
	}

}

//GET METHOD
$addclass = $viewclass = '';
if(empty($_GET['op'])) {

	//LIST VIEW
	$wheresqlarr = array();
	$wheresqlstr = getwheresql($wheresqlarr);
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('attachmenttypes').' WHERE '.$wheresqlstr);
	$listcount = $_SGLOBAL['db']->result($query, 0);
	$multipage = '';
	if($listcount) {	
		$plussql = 'LIMIT '.$start.','.$perpage;
		$listarr = selecttable('attachmenttypes', array(), $wheresqlarr, $plussql);
		$multipage = multi($listcount, $perpage, $page, $theurl);
	}
	$viewclass = ' class="active"';
	
} elseif($_GET['op'] == 'edit') {

	//ONE VIEW FOR UPDATE
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('attachmenttypes').' WHERE id=\''.$_GET['id'].'\'');
	if($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
		$thevalue['maxsize'] = $thevalue['maxsize']/1024;
	}
	
} elseif ($_GET['op'] == 'add') {

	//ONE ADD
	$thevalue = array(
		'id' => 0,
		'fileext' => '',
		'maxsize' => '512'
	);
	$addclass = ' class="active"';
	
} elseif ($_GET['op'] == 'delete') {

	//ONE DELETE
	$_GET['id'] = intval($_GET['id']);
	$_SGLOBAL['db']->query('DELETE FROM '.tname('attachmenttypes').' WHERE id=\''.$_GET['id'].'\'');
	showmessage('attachmenttype_delete_success', $newurl);
}

//SHOW HTML
//MENU
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['attachmenttype_title'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass.'><a href="'.$newurl.'" class="view">'.$alang['attachmenttype_view_list'].'</a></td>
					<td'.$addclass.'><a href="'.$newurl.'&op=add" class="add">'.$alang['attachmenttype_add'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';

//LIST SHOW
if(is_array($listarr) && $listarr) {

	echo label(array('type'=>'help', 'text'=>$alang['help_attachmenttypes']));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));

	echo '<tr>';
	echo '<th>'.$alang['attachmenttype_fileext'].'</th>';
	echo '<th>'.$alang['attachmenttype_maxsize'].'</th>';
	echo '<th>'.$alang['attachmenttype_op'].'</th>';
	echo '</tr>';

	foreach ($listarr as $listvalue) {
		empty($class) ? $class=' class="darkrow"': $class='';
		if($listvalue['maxsize']) {
			$listvalue['maxsize'] = formatsize($listvalue['maxsize']);
		} else {
			$listvalue['maxsize'] = $alang['attachmenttype_maxsize_0'];
		}
		
		echo '<tr'.$class.'>';
		echo '<td>'.$listvalue['fileext'].'</td>';
		echo '<td>'.$listvalue['maxsize'].'</td>';
		echo '<td align="center"><img src="'.S_URL.'/images/base/icon_edit.gif" align="absmiddle"> <a href="'.$newurl.'&op=edit&id='.$listvalue['id'].'">'.$alang['space_edit'].'</a> <img src="'.S_URL.'/images/base/icon_delete.gif" align="absmiddle"><a href="'.$newurl.'&op=delete&id='.$listvalue['id'].'" onclick="return confirm(\''.$alang['delete_all_note'].'\');">'.$alang['space_delete'].'</a></td>';
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
	
	$maxsizearr = array(
		'512' => $alang['attachmenttype_maxsize_0_5'],
		'1024' => $alang['attachmenttype_maxsize_1'],
		'1536' => $alang['attachmenttype_maxsize_1_5'],
		'2048' => $alang['attachmenttype_maxsize_2'],
		'0' => $alang['attachmenttype_maxsize_0']
	);
	
	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$theurl, 'other'=>' onSubmit="return validate(this)"'));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'input', 'alang'=>'attachmenttype_title_fileext', 'name'=>'fileext', 'size'=>10, 'width'=>'30%', 'value'=>$thevalue['fileext']));
	echo label(array('type'=>'select-input', 'alang'=>'attachmenttype_title_maxsize', 'name'=>'maxsize', 'options'=>$maxsizearr, 'size'=>10, 'value'=>$thevalue['maxsize']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo '<input name="id" type="hidden" value="'.$thevalue['id'].'" />';
	echo '<input name="valuesubmit" type="hidden" value="yes" />';
	echo label(array('type'=>'form-end'));

}

?>