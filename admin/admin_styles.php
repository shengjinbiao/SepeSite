<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_styles.php 13382 2009-10-09 07:06:41Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//х╗оч
if(!checkperm('managestyles')) {
	showmessage('no_authority_management_operation');
}

$perpage = 20;
$tplstylearr = array();
foreach ($_SGLOBAL['allblocktype'] as $blocktype) {
	$tplstylearr[$blocktype] = $alang['block_type_'.$blocktype];
}

//CHECK GET VAR
$tpltype = postget('tpltype');
if(!array_key_exists($tpltype, $tplstylearr)) {
	$tpltype = '';
}

$page = intval(postget('page'));
($page < 1) ? $page = 1 : '';
$start = ($page - 1) * $perpage;

//INIT RESULT VAR
$listarr = array();
$thevalue = array();

$newurl = $theurl.'&tpltype='.$tpltype;

//POST METHOD
if(submitcheck('valuesubmit')) {
	//ONE UPDATE OR ADD
	$_POST['tplname'] = shtmlspecialchars($_POST['tplname']);
	$_POST['tplnote'] = shtmlspecialchars($_POST['tplnote']);
	$_POST['tplfilepath'] = shtmlspecialchars($_POST['tplfilepath']);
	
	$tplfilepath = S_ROOT.'./styles/'.$_POST['tplfilepath'].'.html.php';
	if(!file_exists($tplfilepath)) {
		showmessage('style_tpl_file_not_exists');
	}

	$setsqlarr = array(
		'tplname' => $_POST['tplname'],
		'tpltype' => $_POST['tpltype'],
		'tplnote' => $_POST['tplnote'],
		'tplfilepath' => $_POST['tplfilepath']
	);

	if(empty($_POST['tplid'])) {
		//ADD
		$insertsqlarr = $setsqlarr;
		inserttable('styles', $insertsqlarr, 0);
		showmessage('style_add_success', $newurl);
	} else {
		//UPDATE
		$wheresqlarr = array(
			'tplid' => $_POST['tplid']
		);
		updatetable('styles', $setsqlarr, $wheresqlarr);
		showmessage('style_edit_success', $newurl);
	}
}

//GET METHOD
$addclass = $viewclass = '';
if (empty($_GET['op'])) {
	//LIST VIEW
	$wheresqlplus = '';
	if(!empty($tpltype)) {
		$wheresqlplus = ' WHERE tpltype=\''.$tpltype.'\'';
	}
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('styles').$wheresqlplus);
	$listcount = $_SGLOBAL['db']->result($query, 0);
	$multipage = '';
	if($listcount) {
		$query = $_SGLOBAL['db']->query('SELECT s.* FROM '.tname('styles').' s'.$wheresqlplus.' ORDER BY tpltype LIMIT '.$start.','.$perpage);
		while ($style = $_SGLOBAL['db']->fetch_array($query)) {
			$listarr[] = $style;
		}
		$multipage = multi($listcount, $perpage, $page, $newurl);
	}
	$viewclass = ' class="active"';

} elseif ($_GET['op'] == 'edit') {
	//ONE VIEW FOR UPDATE
	$_GET['tplid'] = intval($_GET['tplid']);
	$query = $_SGLOBAL['db']->query('SELECT s.* FROM '.tname('styles').' s WHERE s.tplid=\''.$_GET['tplid'].'\'');
	if ($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
	} else {
		showmessage('style_none_exists');
	}
	
} elseif ($_GET['op'] == 'add') {
	//ONE ADD
	$thevalue = array(
		'tplid' => '0',
		'tpltype' => $tpltype,
		'tplname' => '',
		'tplnote' => '',
		'tplfilepath' => ''
	);
	$addclass = ' class="active"';
		
} elseif ($_GET['op'] == 'delete') {
	//ONE DELETE
	$_GET['tplid'] = intval($_GET['tplid']);
	$_SGLOBAL['db']->query('DELETE FROM '.tname('styles').' WHERE tplid=\''.$_GET['tplid'].'\'');
	showmessage('style_delete_success', $newurl);
}
if(!discuz_exists()){
	foreach ($listarr as $tplk=>$tplv){
		if (substr($tplv['tpltype'], 0, 3) == 'bbs') {
			unset($listarr[$tplk]);
		}
	}
}
//MENU
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['style_title'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass.'><a href="'.$theurl.'" class="view">'.$alang['style_view_list'].'</a></td>
					<td'.$addclass.'><a href="'.$theurl.'&op=add&tpltype='.$tpltype.'" class="add">'.$alang['style_add'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';

//LIST SHOW
if(is_array($listarr) && $listarr) {
	echo label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$theurl));
	echo label(array('type'=>'help', 'text'=>$alang['help_styles']));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));

	echo '<tr>';
	echo '<th>'.$alang['style_title_tplname'].'</th>';
	echo '<th>'.$alang['style_title_tpltype'].'</th>';
	echo '<th>'.$alang['style_title_tplfilepath'].'</th>';
	echo '<th>'.$alang['style_title_op'].'</th>';
	echo '</tr>';
	
	foreach ($listarr as $listvalue) {
		empty($class) ? $class=' class="darkrow"': $class='';
		
		echo '<tr'.$class.'>';
		echo '<td>'.$listvalue['tplname'].'</td>';
		echo '<td><a href="'.$theurl.'&tpltype='.$listvalue['tpltype'].'">'.$alang['block_type_'.$listvalue['tpltype']].'</a></td>';
		echo '<td>'.$listvalue['tplfilepath'].'</td>';
		echo '<td><a href="'.$theurl.'&op=edit&tplid='.$listvalue['tplid'].'">'.$alang['common_edit'].'</a> | <a href="'.$theurl.'&op=delete&tplid='.$listvalue['tplid'].'" onclick="return confirm(\''.$alang['delete_all_note'].'\');">'.$alang['common_delete'].'</a></td>';
		echo '</tr>';
	}
	echo label(array('type'=>'table-end'));
	
	if(!empty($multipage)) {
		echo label(array('type'=>'table-start', 'class'=>'listpage'));
		echo '<tr><td>'.$multipage.'</td></tr>';
		echo label(array('type'=>'table-end'));
	}

	echo label(array('type'=>'form-end'));
}

//THE VALUE SHOW
if(is_array($thevalue) && $thevalue) {	

	$tplfilepatharr = array(''=>$alang['style_select_file']);
	$tpldir = S_ROOT.'./styles';
	if(is_dir($tpldir)) {
		$filedir = dir($tpldir);
		while(false !== ($entry = $filedir->read())) {
			if(strpos($entry, '.html.php') === false) {
			} else {
				$entrykey = str_replace('.html.php', '', $entry);
				$tplfilepatharr[$entrykey] = $entry;
			}
		}
	}

	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$theurl));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'select', 'alang'=>'style_tpltype', 'name'=>'tpltype', 'options'=>$tplstylearr, 'width'=>'30%', 'value'=>$thevalue['tpltype']));
	echo label(array('type'=>'input', 'alang'=>'style_tplname', 'name'=>'tplname', 'size'=>'30', 'value'=>$thevalue['tplname']));
	echo label(array('type'=>'textarea', 'alang'=>'style_tplnote', 'name'=>'tplnote', 'cols'=>'80', 'rows'=>'5', 'value'=>$thevalue['tplnote']));
	echo label(array('type'=>'text', 'alang'=>'style_tplfilepath', 'text'=>$alang['style_dir'].': styles (<a href="'.S_URL.'/admincp.php?action=styletpl&op=add" target="_blank"><strong>'.$alang['online_documentation_new_modular_style'].'</strong></a>)<br>'.$alang['style_file'].': '.getselectstr('tplfilepath', $tplfilepatharr, $thevalue['tplfilepath'])));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo '<input name="tplid" type="hidden" value="'.$thevalue['tplid'].'" />';
	echo '<input name="valuesubmit" type="hidden" value="yes" />';
	echo label(array('type'=>'form-end'));
}

?>