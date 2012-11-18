<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_polls.php 11528 2009-03-09 06:24:30Z zhaolei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//х╗оч
if(!checkperm('managepolls')) {
	showmessage('no_authority_management_operation');
}

$perpage = 20;
$pollid = intval(postget('pollid'));
$page = intval(postget('page'));
($page < 1) ? $page = 1 : '';
$start = ($page - 1) * $perpage;
$listarr = array();
$thevalue = array();

//POST METHOD
if (submitcheck('listsubmit')) {

	//LIST UPDATE
	if(!empty($_POST['item'])) {
		$pollidstr = implode('\',\'', $_POST['item']);
		$_SGLOBAL['db']->query('DELETE FROM '.tname('polls').' WHERE pollid IN (\''.$pollidstr.'\')');
	}
	showmessage('poll_op_success', $theurl);

} elseif (submitcheck('valuesubmit')) {

	//ONE UPDATE OR ADD
	$_POST['subject'] = shtmlspecialchars(trim($_POST['subject']));
	if(strlen($_POST['subject']) < 2) {
		showmessage('poll_check_subject');
	}
	
	$_POST['summary'] = shtmlspecialchars($_POST['summary']);
	if(strlen($_POST['summary']) < 2) {
		showmessage('poll_check_summary');
	}
	
	$_POST['ismulti'] = intval($_POST['ismulti']);
	$pollid = intval($_POST['pollid']);
	$optionarr = array();
	$pollnum = 0;
	foreach ($_POST['optionname'] as $optionkey => $optionname) {
		$optionname = trim($optionname);
		if(!empty($optionname)) {
			$optionname = shtmlspecialchars($optionname);
			$optionnum = intval($_POST['optionnum'][$optionkey]);
			$pollnum = $pollnum + $optionnum;
			$optionarr[] = array('name'=>$optionname, 'num'=>$optionnum);
		}
	}
	if(empty($optionarr)) {
		showmessage('poll_check_option');
	}
	
	$options = addslashes(serialize($optionarr));
	if(empty($pollid)) {
		//ADD
		$insertsqlarr = array(
			'pollnum' => 0,
			'dateline' => $_SGLOBAL['timestamp'],
			'updatetime' => $_SGLOBAL['timestamp'],
			'subject' => $_POST['subject'],
			'pollsurl' => shtmlspecialchars(trim($_POST['pollsurl'])),
			'ismulti' => $_POST['ismulti'],
			'summary' => $_POST['summary'],
			'options' => $options
		);
		inserttable('polls', $insertsqlarr);
		showmessage('poll_add_success', $theurl);
	} else {
		//UPDATE
		$setsqlarr = array(
			'pollnum' => $pollnum,
			'subject' => $_POST['subject'],
			'pollsurl' => shtmlspecialchars(trim($_POST['pollsurl'])),
			'ismulti' => $_POST['ismulti'],
			'summary' => $_POST['summary'],
			'options' => $options
		);
		updatetable('polls', $setsqlarr, array('pollid'=>$pollid));
		showmessage('poll_update_success', $theurl);
	}
}

//GET METHOD
$addclass = $viewclass = '';
if (empty($_GET['op'])) {
	//LIST VIEW
	$wheresqlarr = array();
	$wheresqlstr = getwheresql($wheresqlarr);
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('polls').' WHERE '.$wheresqlstr);
	$listcount = $_SGLOBAL['db']->result($query, 0);
	$multipage = '';
	if($listcount) {	
		$plussql = 'ORDER BY dateline DESC LIMIT '.$start.','.$perpage;
		$listarr = selecttable('polls', array(), $wheresqlarr, $plussql);
		$multipage = multi($listcount, $perpage, $page, $theurl);
	}
	$viewclass = ' class="active"';
	
} elseif ($_GET['op'] == 'edit') {

	//ONE VIEW FOR UPDATE
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('polls').' WHERE pollid=\''.$pollid.'\'');
	if($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
	}
	
} elseif ($_GET['op'] == 'add') {
	//ONE ADD
	$thevalue = array(
		'pollid' => 0,
		'ismulti' => 0,
		'subject' => '',
		'summary' => '',
		'message' => ''
	);
	$addclass = ' class="active"';
		
}

//MENU
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['poll_title'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass.'><a href="'.$theurl.'" class="view">'.$alang['poll_view_list'].'</a></td>
					<td'.$addclass.'><a href="'.$theurl.'&op=add" class="add">'.$alang['poll_add'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';

//LIST SHOW
if(is_array($listarr) && $listarr) {
	
	$adminmenu = '<input name="importdelete" type="radio" value="1" checked /> '.$alang['poll_delete'];
	
	
	echo label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$theurl));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));

	echo '<tr>';
	echo '<th>'.$alang['poll_select'].'</th>';
	echo '<th>'.$alang['poll_subject'].'</th>';
	echo '<th>'.$alang['poll_dateline'].'</th>';
	echo '<th>'.$alang['poll_updatetime'].'</th>';
	echo '<th>'.$alang['poll_op'].'</th>';
	echo '</tr>';

	foreach ($listarr as $listvalue) {
		empty($class) ? $class=' class="darkrow"': $class='';
		echo '<tr'.$class.'>';
		echo '<td><input type="checkbox" name="item[]" value="'.$listvalue['pollid'].'" /></td>';
		echo '<td><a href="'.geturl('action/poll/pollid/'.$listvalue['pollid']).'">'.$listvalue['subject'].'</a></td>';
		echo '<td>'.sgmdate($listvalue['dateline']).'</td>';
		echo '<td>'.sgmdate($listvalue['updatetime']).'</td>';
		echo '<td><a href="'.$theurl.'&op=edit&pollid='.$listvalue['pollid'].'">'.$alang['space_edit'].'</a></td>';
		echo '</tr>';
	}
	echo label(array('type'=>'table-end'));
	
	echo label(array('type'=>'table-start', 'class'=>'btmtable'));
	echo '<tr><th><input type="checkbox" name="chkall" onclick="checkall(this.form, \'item\')">'.$alang['space_select_all'].' '.$adminmenu.'</th></tr>';	
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

//THE VALUE SHOW
if(is_array($thevalue) && $thevalue) {
	
	$ismultiarr = array(
		'0' => $alang['poll_ismulti_0'],
		'1' => $alang['poll_ismulti_1']
	);
	
	$jsoption = '<table class="freetable"><tr valign="top">';
	$jsoption .= '<td><input type="text" name="optionname[]" size="60" value="" /><input type="hidden" name="optionnum[]" value="0" /></td>';
	$jsoption .= '</tr></table>';
	
	$optiontext = '<div id="div_option">';
	$optionarr = array(
		array('name'=>'', 'num'=>0)
	);
	if(!empty($thevalue['options'])) {
		$optionarr = unserialize($thevalue['options']);
	}
	foreach ($optionarr as $optionvalue) {
		$optiontext .= '<table class="freetable"><tr valign="top">';
		$optiontext .= '<td><input type="text" name="optionname[]" size="60" value="'.$optionvalue['name'].'" /><input type="hidden" name="optionnum[]" value="'.$optionvalue['num'].'" /></td>';
		$optiontext .= '</tr></table>';
	}
	$optiontext .= '</div><table class="freetable"><tr><td><input type="button" name="Submit" value="'.$alang['poll_add_option'].'" onClick="adddivoption()" /></td></tr></table>';

	echo '
	<script language="javascript">
	<!--
	function adddivoption() {
		var oDiv=document.createElement("DIV");
		document.getElementById("div_option").appendChild(oDiv);
		oDiv.innerHTML = "'.addcslashes($jsoption, '"').'";
	}
	function thevalidate(theform) {
		return true;
	}
	//-->
	</script>
	';
	
	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$theurl, 'other'=>' onSubmit="return validate(this)"'));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'input', 'alang'=>'poll_title_subject', 'name'=>'subject', 'size'=>60, 'value'=>$thevalue['subject']));
	echo label(array('type'=>'input', 'alang'=>'spacepolls_title_pollsurl', 'name'=>'pollsurl', 'size'=>60, 'value'=>$thevalue['pollsurl']));
	echo label(array('type'=>'radio', 'alang'=>'poll_title_ismulti', 'name'=>'ismulti', 'options'=>$ismultiarr, 'value'=>$thevalue['ismulti']));
	echo label(array('type'=>'textarea', 'alang'=>'poll_title_summary', 'name'=>'summary', 'cols'=>104, 'rows'=>10, 'value'=>$thevalue['summary']));
	echo label(array('type'=>'text', 'alang'=>'poll_title_options', 'text'=>$optiontext));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo '<input name="pollid" type="hidden" value="'.$thevalue['pollid'].'" />';
	echo '<input name="valuesubmit" type="hidden" value="yes" />';
	echo label(array('type'=>'form-end'));
}

?>