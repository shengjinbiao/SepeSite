<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_prefields.php 13489 2009-11-10 02:34:44Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//х╗оч
if(!checkperm('manageprefields')) {
	showmessage('no_authority_management_operation');
}

$perpage = 20;
empty($_GET['page']) ? $page = 1 : $page = intval($_GET['page']);
($page < 1) ? $page = 1 : '';
$type = postget('type');
if(empty($type)) $type = 'news';
$field = postget('field');
$newurl = $theurl.'&type='.$type.'&field='.$field;
$start = ($page - 1) * $perpage;
$listarr = array();
$thevalue = array();

//POST METHOD
if(submitcheck('valuesubmit')) {
	
	$_POST['value'] = shtmlspecialchars($_POST['value']);
	//ONE UPDATE OR ADD
	if(empty($_POST['id'])) {
		//ADD
		$insertsqlarr = array(
			'type' => $type,
			'field' => $_POST['field'],
			'value' => $_POST['value'],
			'isdefault' => $_POST['isdefault']
		);
		inserttable('prefields', $insertsqlarr, 0);
	} else {
		//UPDATE ONE
		$id = $_POST['id'];
		$setsqlarr = array(
			'field' => $_POST['field'],
			'value' => $_POST['value'],
			'isdefault' => $_POST['isdefault']
		);
		$wheresqlarr = array(
			'id' => $id
		);
		updatetable('prefields', $setsqlarr, $wheresqlarr);
	}
}

//GET METHOD
if($_GET['op'] == 'edit') {
	//ONE VIEW FOR UPDATE
	$id = intval($_GET['id']);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('prefields').' WHERE id=\''.$id.'\'');
	if($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
	} else {
		showmessage('prefield_none_exists');
	}

} elseif ($_GET['op'] == 'delete') {
	//ONE DELETE
	$id = intval($_GET['id']);
	$_SGLOBAL['db']->query('DELETE FROM '.tname('prefields').' WHERE id=\''.$id.'\'');
	showmessage($alang['prefield_delete_success'], $newurl);
}

//LIST VIEW
$wheresqlstr = '1';
if(!empty($type)) $wheresqlstr .= ' AND type=\''.$type.'\'';
if(!empty($field)) $wheresqlstr .= ' AND field=\''.$field.'\'';

$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('prefields').' WHERE '.$wheresqlstr);
$listcount = $_SGLOBAL['db']->result($query, 0);
$multipage = '';
if($listcount) {
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('prefields').' WHERE '.$wheresqlstr.' ORDER BY type, field, id LIMIT '.$start.','.$perpage);
	while ($prefield = $_SGLOBAL['db']->fetch_array($query)) {
		$listarr[] = $prefield;
	}
	$multipage = multi($listcount, $perpage, $page, $newurl);
}

//ONE ADD
if(empty($thevalue)) {
	if($type) {
		$thevalue = array(
			'id' => 0,
			'field' => '',
			'value' => '',
			'isdefault' => '0',
			'type' => $type
		);
	} else {
		$thevalue = array();
	}
}


//TYPE
$typearr = array(
	'news' => $alang['common_type_news']
);
//FIELD
$fieldarr = array(
	'news' => array(
		'newsauthor' => $alang['prefield_newsauthor'],
		'newsfrom' => $alang['prefield_newsfrom']
	)
);

//SHOW HTML
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td><h1>'.$alang['prefield_title'].'</h1></td>
<td class="actions">
<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
<tr>';

if(!empty($channels['types']) && is_array($channels['types'])) {
	foreach ($channels['types'] as $typeid => $typename) {
		if($type == $typeid) {
			$class = ' class="active"';
		} else {
			$class = '';
		}
		echo '<td'.$class.'><a href="'.$theurl.'&type='.$typeid.'">'.$typename['name'].'</a></td>';
	}
}
echo '</tr>
</table>
</td>
</tr>
</table>
';

//FIELD SHOW
if(!empty($type)) {
	$fieldtypearr = $fieldarr['news'];
} else {
	$fieldtypearr = array();
}

if(!empty($fieldtypearr) && is_array($fieldtypearr)) {
	echo label(array('type'=>'table-start', 'class'=>'listtable'));
	echo '<tr>';
	foreach ($fieldtypearr as $fieldid => $fieldname) {
		echo '<td><a href="'.$theurl.'&type='.$type.'&field='.$fieldid.'">'.$fieldname.'</a></td>';
	}
	echo '</tr>';
	echo label(array('type'=>'table-end'));
	echo '<br>';
}

//THE VALUE SHOW
if(is_array($thevalue) && $thevalue) {

	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$theurl, 'other'=>' onSubmit="return validate(this)"'));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'select', 'alang'=>'prefield_title_field', 'name'=>'field', 'options'=>$fieldarr['news'], 'width'=>'30%', 'value'=>$field));
	echo label(array('type'=>'input', 'alang'=>'prefield_title_value', 'name'=>'value', 'size'=>20, 'value'=>$thevalue['value']));

	$isdefaultarr = array(
		'0' => $alang['prefield_isdefault_0'],
		'1' => $alang['prefield_isdefault_1']
	);
	echo label(array('type'=>'radio', 'alang'=>'prefield_title_isdefault', 'name'=>'isdefault', 'options'=>$isdefaultarr, 'value'=>$thevalue['isdefault']));

	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo '<input name="id" type="hidden" value="'.$thevalue['id'].'" />';
	echo '<input name="type" type="hidden" value="'.$thevalue['type'].'" />';
	echo '<input name="valuesubmit" type="hidden" value="yes" />';
	echo label(array('type'=>'form-end'));
}

//LIST SHOW
if(is_array($listarr) && $listarr) {
	
	if(empty($_GET['type'])) echo label(array('type'=>'help', 'text'=>$alang['help_prefields']));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));
	echo '<tr>';
	echo '<th>'.$alang['prefield_header_type'].'</th>';
	echo '<th>'.$alang['prefield_header_field'].'</th>';
	echo '<th>'.$alang['prefield_header_value'].'</th>';
	echo '<th>'.$alang['prefield_header_isdefault'].'</th>';
	echo '<th>'.$alang['prefield_header_op'].'</th>';
	echo '</tr>';

	foreach ($listarr as $listvalue) {
		empty($class) ? $class=' class="darkrow"': $class='';

		$listvalue['field'] = $fieldarr['news'][$listvalue['field']];
		$listvalue['type'] = $channels['types'][$listvalue['type']]['name'];
		if(empty($listvalue['isdefault'])) {
			$listvalue['isdefault'] = $alang['prefield_isdefault_0'];
		} else {
			$listvalue['isdefault'] = $alang['prefield_isdefault_1'];
		}

		echo '<tr'.$class.'>';
		echo '<td>'.$listvalue['type'].'</td>';
		echo '<td>'.$listvalue['field'].'</td>';
		echo '<td>'.$listvalue['value'].'</td>';
		echo '<td>'.$listvalue['isdefault'].'</td>';
		echo '<td><a href="'.$newurl.'&op=edit&id='.$listvalue['id'].'">'.$alang['prefield_edit'].'</a> | <a href="'.$newurl.'&op=delete&id='.$listvalue['id'].'" onclick="return confirm(\''.$alang['delete_all_note'].'\');">'.$alang['prefield_delete'].'</a></td>';
		echo '</tr>';
	}
	echo label(array('type'=>'table-end'));

	if(!empty($multipage)) {
		echo label(array('type'=>'table-start', 'class'=>'listpage'));
		echo '<tr><td>'.$multipage.'</td></tr>';
		echo label(array('type'=>'table-end'));
	}
}

?>