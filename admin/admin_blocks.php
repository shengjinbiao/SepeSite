<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks.php 12864 2009-07-23 08:19:03Z zhaolei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageblocks')) {
	showmessage('no_authority_management_operation');
}

$blocktype = postget('blocktype');

if(!in_array($blocktype, $_SGLOBAL['allblocktype'])) {
	$blocktype = '';
}

$_GET['name'] = trim($_GET['name']);

if($blocktype == 'spacenews') {
	$includetype = 'spaceitem';
	$type = empty($_GET['name']) ? 'news' : $_GET['name'];
}else {
	$includetype = $blocktype;
}

$newurl = $theurl.'&blocktype='.$blocktype;

$perpage = 5;

//CHECK GET VAR
$blockid = intval(postget('blockid'));
$page = intval(postget('page'));
($page<1)?$page=1:'';
$start = ($page-1)*$perpage;

//INIT RESULT VAR
$listarr = array();
$thevalue = array();
$addblocktypearr = array();

//POST METHOD
if (submitcheck('listsubmit')) {
	//LIST UPDATE
	if(!empty($_POST['item'])) {
		$blockidstr = implode('\',\'', $_POST['item']);
		$_SGLOBAL['db']->query('DELETE FROM '.tname('blocks').' WHERE blockid IN (\''.$blockidstr.'\')');
	}
	showmessage('block_op_success', $newurl);

} elseif (submitcheck('valuesubmit')) {
	//ONE UPDATE OR ADD
	
	$_POST['blockname'] = shtmlspecialchars($_POST['blockname']);
	
	if(!empty($_POST['tplname'])) $_POST['tpl'] = $_POST['tplname'];
	
	$postarr = array();
	foreach ($_POST as $pkey => $pvalue) {
		$postarr[$pkey] = shtmlspecialchars($pvalue);
	}
	$blocktext = addslashes(serialize($postarr));
	include_once(S_ROOT.'./admin/include/admin_blocks_'.$includetype.'_code.inc.php');
	
	$blockcode = '';
	$blockcode .= '<!--{block name="'.$_POST['blocktype'].'" parameter="'.implode('/', $blockcodearr).'"}-->';
	$blockcode .= '<!--'.$_POST['blockname'].'-->';
	$blockcode = addslashes($blockcode);
	
	if(empty($blockid)) {
		//ADD
		$insertsqlarr = array(
			'dateline' => $_SGLOBAL['timestamp'],
			'blocktype' => $_POST['blocktype'],
			'blockname' => $_POST['blockname'],
			'blocktext' => $blocktext,
			'blockcode' => $blockcode,
			'blockmodel' => $_POST['blockmodel']
		);
		$blockid = inserttable('blocks', $insertsqlarr, 1);
		showmessage('block_add_success', $newurl.'&blockid='.$blockid);
	} else {
		//UPDATE
		$setsqlarr = array(
			'blockname' => $_POST['blockname'],
			'blocktext' => $blocktext,
			'blockcode' => $blockcode,
			'blockmodel' => $_POST['blockmodel']
		);
		updatetable('blocks', $setsqlarr, array('blockid'=>$blockid));
		showmessage('block_update_success', $newurl.'&blockid='.$blockid);
	}
}

//GET METHOD
$addclass = $viewclass = '';

if (empty($_GET['op'])) {

	$addblocktypearr = $_SGLOBAL['allblocktype'];
	
	//LIST VIEW
	$wheresqlarr = array();
	if(!empty($blockid)) $wheresqlarr['blockid'] = $blockid;
	if(!empty($blocktype)) {
		$wheresqlarr['blocktype'] = $blocktype;
	} elseif(!empty($_GET['viewblocktype'])) {
		$wheresqlarr['blocktype'] = $_GET['viewblocktype'];
	}

	$wheresqlstr = getwheresql($wheresqlarr);
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('blocks').' WHERE '.$wheresqlstr);
	$listcount = $_SGLOBAL['db']->result($query, 0);
	$multipage = '';
	if($listcount) {	
		$plussql = 'ORDER BY dateline DESC LIMIT '.$start.','.$perpage;
		$listarr = selecttable('blocks', array(), $wheresqlarr, $plussql);
		$multipage = multi($listcount, $perpage, $page, $newurl);
	}
	$viewclass = ' class="active"';

} elseif ($_GET['op'] == 'edit') {
	//ONE VIEW FOR UPDATE
	$typearr = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('blocks').' WHERE blockid=\''.$blockid.'\'');
	if($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
	}
	if($_GET['blocktype'] == 'model') {
		$_GET['name'] = $thevalue['blockname'];
		$query = $_SGLOBAL['db']->query('SELECT modelname, modelalias FROM '.tname('models'));
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			$typearr[$value['modelname']] = $value['modelalias'];
		}
	}elseif($_GET['blocktype'] == 'spacenews' || $_GET['blocktype'] == 'postitem') {
		foreach ($channels['types'] as $key=>$value) {
			$typearr[$key] = $value['name'];
		}
	}

} elseif ($_GET['op'] == 'add') {

	$typearr = array();
	$addblocktypearr = $_SGLOBAL['allblocktype'];
	if(empty($_GET['addblocktype']) && !empty($_GET['blocktype'])) $_GET['addblocktype'] = $_GET['blocktype'];
	if (!empty($_GET['addblocktype'])) {
		if(!in_array($_GET['addblocktype'], $_SGLOBAL['allblocktype'])) {
			$_GET['addblocktype'] = '';
		}
	}

	if($_GET['blocktype'] == 'model') {
		$query = $_SGLOBAL['db']->query('SELECT modelname, modelalias FROM '.tname('models'));
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			$typearr[$value['modelname']] = $value['modelalias'];
		}
		if(!empty($typearr) ) {
			if(empty($_GET['name'])) {
				$_GET['name'] = key(array_slice($typearr, 0, 1));
			} else {
				$_GET['name'] = trim($_GET['name']);
			}
		} else {
			$_GET['name'] = '';
		}

	} elseif($_GET['blocktype'] == 'spacenews' || $_GET['blocktype'] == 'postitem') {
		foreach ($channels['types'] as $key=>$value) {
			$typearr[$key] = $value['name'];
		}
	}

	if(!empty($_GET['addblocktype'])) {
		//ONE ADD
		$thevalue = array(
			'blockid' => 0,
			'blocktype' => $_GET['addblocktype'],
			'blockname' => $_GET['name'],
			'blocktext' => '',
			'blockcode' => '',
			'blockmodel' => '1'
		);
	}
	$addclass = ' class="active"';

}

//SHOW HTML
//MENU
$blocktypetitle = '';
if(!empty($alang['block_type_'.$blocktype])) $blocktypetitle = '('.$alang['block_type_'.$blocktype].')';
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['block_title'].$blocktypetitle.'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass.'><a href="'.$theurl.'" class="view">'.$alang['block_view_list'].'</a></td>
					<td'.$addclass.'><a href="'.$newurl.'&op=add" class="add">'.$alang['block_add'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';

//ADD TYPE
if(empty($_GET['op'])) {
	$urltype = 'viewblocktype';
	$urloptype = '';
	$opexplainstr = $alang['help_blocks_view'];
	$imgstr = '<img src="'.S_URL.'/admin/images/icon_arrow.gif" align="absmiddle">';
} else {
	$urltype = 'addblocktype';
	$urloptype = 'add';
	$opexplainstr = $alang['help_blocks_add'];
	$imgstr = '<img src="'.S_URL.'/admin/images/action_icon_add.gif" align="absmiddle">';
}

if(is_array($addblocktypearr) && $addblocktypearr) {
	
	$addblocktypestr = '<table cellspacing="0" cellpadding="0" width="100%"><tr>';
	foreach ($addblocktypearr as $bkey => $btype) {
		if((!empty($_GET['blocktype']) && $btype == $_GET['blocktype']) || (!empty($_GET['addblocktype']) && $btype == $_GET['addblocktype'])) {
			$addblocktypestr .= '<td style="border: 0px;">'.$imgstr.' <a href="'.$theurl.'&'.$urltype.'='.$btype.'&blocktype='.$btype.'&op='.$urloptype.'"><strong>'.$alang['block_type_'.$btype].'</strong></a></td>';
		} else {
			$addblocktypestr .= '<td style="border: 0px;">'.$imgstr.' <a href="'.$theurl.'&'.$urltype.'='.$btype.'&blocktype='.$btype.'&op='.$urloptype.'">'.$alang['block_type_'.$btype].'</a></td>';
		}
		if($bkey % 6 == 5) $addblocktypestr .= '</tr><tr>';
	}
	$addblocktypestr .= '</tr></table>';

	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'help', 'text'=>$opexplainstr));

	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'text', 'alang'=>'block_title_addblocktype', 'width'=>'30%', 'text'=>$addblocktypestr));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	echo '<br>';
}

//LIST SHOW
if(is_array($listarr) && $listarr) {
	
	$adminmenu = '<input name="importdelete" type="radio" value="1" checked /> '.$alang['block_delete'];
	
	echo label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$newurl));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));

	echo '<tr>';
	echo '<th>'.$alang['block_select'].'</th>';
	echo '<th>'.$alang['block_blockname'].'</th>';
	echo '<th>'.$alang['block_op'].'</th>';
	echo '</tr>';
	
	foreach ($listarr as $listvalue) {
		
		$listvalue['jscode'] = '';
		preg_match("/parameter\=\"(.*?)\"/is", $listvalue['blockcode'], $matches);
		if(!empty($matches[1]) && strpos($matches[1], 'tpl/data') === false) {
			$listvalue['jscode'] = '<script language="JavaScript" src="'.S_URL_ALL.'/batch.javascript.php?param='.rawurlencode(passport_encrypt('blocktype/'.$listvalue['blocktype'].'/'.$matches[1], $_SCONFIG['sitekey'])).'"></script>';
		}
		
		empty($class) ? $class=' class="darkrow"': $class='';
		echo '<tr'.$class.'>';
		echo '<td><input type="checkbox" name="item[]" value="'.$listvalue['blockid'].'" /></td>';
		echo '<td>';
		echo '<table>';
		echo '<tr><td><b>'.$listvalue['blockname'].'</b> ('.sgmdate($listvalue['dateline']).')<br>'.$alang['block_basic_type'].': '.$alang['block_type_'.$listvalue['blocktype']].'</td></tr>';
		echo '<tr><td>'.$alang['block_code_1'].'<br><textarea name="blcokcode[]" rows="5" cols="100">'.shtmlspecialchars($listvalue['blockcode']).'</textarea>';
		if(!empty($listvalue['jscode'])) echo '<br>'.$alang['block_code_2'].'<br><textarea name="blcokcode[]" rows="5" cols="100">'.shtmlspecialchars($listvalue['jscode']).'</textarea>';
		echo '</td></tr>';
		echo '</table>';
		echo '</td>';
		echo '<td><img src="'.S_URL.'/images/base/icon_edit.gif" align="absmiddle"> <a href="'.$theurl.'&blocktype='.$listvalue['blocktype'].'&op=edit&blockid='.$listvalue['blockid'].'">'.$alang['space_edit'].'</a></td>';
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
	
	$stylearr = getstyle($blocktype);
	
	if(!empty($thevalue['blocktext'])) {
		$theblcokvalue = unserialize($thevalue['blocktext']);
	} else {
		$theblcokvalue = array('cachetime'=>'900', 'start'=>'0', 'limit'=>'10', 'tpl'=>'data', 'tplname'=>'');
	}

	$cachetimearr = array(
		'' => $alang['block_cachetime_null'],
		'900' => $alang['block_cachetime_900'],
		'1800' => $alang['block_cachetime_1800'],
		'3600' => $alang['block_cachetime_3600'],
		'7200' => $alang['block_cachetime_7200'],
		'43200' => $alang['block_cachetime_43200'],
		'86400' => $alang['block_cachetime_86400']
	);
	
	include_once(S_ROOT.'./admin/include/admin_blocks_'.$includetype.'.inc.php');
	
	//COMMON
	$blockarr['cache'] = array(
		'cachetime' => array(
			'type' => 'select-input',
			'alang' => 'block_title_cachetime',
			'options' => $cachetimearr,
			'size' => '10',
			'width' => '30%'
		)
	);

	$blockarr['template'] = array(
		'cachename' => array(
			'type' => 'input',
			'alang' => 'block_title_cachename'
		),
		'tpl' => array(
			'type' => 'select-div-preview',
			'alang' => 'block_title_skinid',
			'options' => $stylearr
		)
	);

	if(!in_array($blocktype, array('uchblog', 'uchphoto', 'uchspace'))) {
		$blockarr['template']['tplname'] = array(
			'type' => 'text',
			'alang' => 'block_title_tplname',
			'text' => $alang['style_dir'].': styles (<a href="'.S_URL.'/admincp.php?action=styletpl&op=add" target="_blank"><strong>'.$alang['online_documentation_new_modular_style'].'</strong></a>)<br>'.$alang['style_file'].': <input name="tplname" size="30" value="'.$theblcokvalue['tplname'].'" />.html.php'
		);
	}

	$blockmodelarr = array(
		'1' => $alang['block_model_1'],
		'2' => $alang['block_model_2']
	);

	echo '
	<script language="javascript">
	<!--
	function jssettid(value) {
		document.getElementById(\'divsettid1\').style.display = \'none\';
		document.getElementById(\'divsettid2\').style.display = \'none\';
		if(value == \'1\') {
			document.getElementById(\'divsettid1\').style.display = \'\';
		} else {
			document.getElementById(\'divsettid2\').style.display = \'\';
		}

	}
	function jsshowdetail(value) {
		document.getElementById(\'divshowdetail\').style.display = \'none\';
		if(value == \'1\') {
			document.getElementById(\'divshowdetail\').style.display = \'\';
		}

	}
	function jsblockmodel(value) {
		document.getElementById(\'divblockmodel1\').style.display = \'none\';
		document.getElementById(\'divblockmodel11\').style.display = \'none\';
		document.getElementById(\'divblockmodel2\').style.display = \'none\';
		if(value == \'1\') {
			document.getElementById(\'divblockmodel1\').style.display = \'\';
			document.getElementById(\'divblockmodel11\').style.display = \'\';
		} else {
			document.getElementById(\'divblockmodel2\').style.display = \'\';
		}
	}
	function jsshowmulti(value) {
		var showmulti1 = document.getElementById(\'divshowmulti1\');
		var showmulti2 = document.getElementById(\'divshowmulti2\');
		showmulti1.style.display = \'none\';
		showmulti2.style.display = \'none\';
		if(value == \'1\') {
			showmulti2.style.display = \'\';
		} else {
			showmulti1.style.display = \'\';
		}		
	}
	function formvalidate(theform) {
		var btn = document.getElementById("thevaluesubmit");
		if(btn) btn.disabled = true;
		var blockname = document.getElementById("blockname");
		if(blockname) {
			if (blockname.value.length < 1 || blockname.value.length > 50) {
				alert("'.$alang['block_name_length_error'].'");
				if(btn) btn.disabled = false;
				return false;
			}
		}
	}
	//-->
	</script>
	';
	
	if($thevalue['blockmodel'] == '1') {
		$divblockmodel1display = '';
		$divblockmodel2display = 'none';
	} else {
		$divblockmodel1display = 'none';
		$divblockmodel2display = '';
	}
	if(empty($theblcokvalue['sql'])) {
		$theblcokvalue['sql'] = '';
	} else {
		$theblcokvalue['sql'] = stripslashes($theblcokvalue['sql']);
	}
	
	if(empty($theblcokvalue['sql'])) {
		$theblcokvalue['sql'] = 'SELECT * FROM '.$alang['block_table'].' WHERE 1';
	}
	
	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$newurl, 'other'=>' onSubmit="return formvalidate(this)"'));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'title', 'alang'=>'block_type_'.$blocktype));
	echo label(array('type'=>'table-start'));
	echo $blcoktype;

	if($blocktype == 'model' || $blocktype == 'spacenews' || $blocktype == 'postitem') {
		echo label(array('type'=>'select', 'alang'=>'block_title_blockname_'.$blocktype, 'name'=>'blockname', 'size'=>'60', 'width'=>'30%', 'options'=>$typearr, 'value'=>$thevalue['blockname'], 'other'=>' onchange="javascript:window.location.replace(\''.$theurl.'\'+\'&addblocktype='.$blocktype.'&blocktype='.$blocktype.'&op=add&name=\'+this.value);"'));
	} else {
		echo label(array('type'=>'input', 'alang'=>'block_title_blockname', 'name'=>'blockname', 'size'=>'60', 'width'=>'30%', 'value'=>$thevalue['blockname']));
	}
	echo label(array('type'=>'radio', 'alang'=>'block_title_blockmodel', 'name'=>'blockmodel', 'options'=>$blockmodelarr, 'value'=>$thevalue['blockmodel'], 'other'=>' onclick="jsblockmodel(this.value)"'));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	echo '<div class="colorarea01" id="divblockmodel1" style="display:'.$divblockmodel1display.'">';
	echo label(array('type'=>'title', 'alang'=>'block_filter'));
	echo label(array('type'=>'table-start'));

	echolabel($blockarr['where'], $theblcokvalue);
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	echo '<div class="colorarea01" id="divblockmodel11" style="display:'.$divblockmodel1display.'">';
	echo label(array('type'=>'title', 'alang'=>'block_order'));
	echo label(array('type'=>'table-start'));
	echolabel($blockarr['order'], $theblcokvalue);
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	echo '<div class="colorarea01" id="divblockmodel2" style="display:'.$divblockmodel2display.'">';
	echo label(array('type'=>'title', 'alang'=>'block_model_2'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'textarea', 'alang'=>'block_title_sql', 'name'=>'sql', 'cols'=>104, 'rows'=>10, 'width'=>'30%', 'value'=>$theblcokvalue['sql']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	
	//个数限制
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'title', 'alang'=>'block_limit'));
	echo label(array('type'=>'table-start'));
	echolabel($blockarr['limit'], $theblcokvalue);
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'title', 'alang'=>'block_cache'));
	echo label(array('type'=>'table-start'));
	echolabel($blockarr['cache'], $theblcokvalue);
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	if(!empty($blockarr['batch'])) {
		echo label(array('type'=>'div-start'));
		echo label(array('type'=>'title', 'alang'=>'block_batch'));
		echo label(array('type'=>'table-start'));
		echolabel($blockarr['batch'], $theblcokvalue);
		echo label(array('type'=>'table-end'));
		echo label(array('type'=>'div-end'));
	}
	
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'title', 'alang'=>'block_template'));
	echo label(array('type'=>'table-start'));
	echolabel($blockarr['template'], $theblcokvalue);
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	foreach ($blockarr as $bkey => $bvalue) {
		if(!isset($bvalue['type'])) $bvalue['type'] = 'eval';
		if(!isset($bvalue['alang'])) $bvalue['alang'] = '';
		if(!isset($bvalue['options'])) $bvalue['options'] = array();
		if(!isset($bvalue['other'])) $bvalue['other'] = '';
		if(!isset($bvalue['text'])) $bvalue['text'] = '';
		if(!isset($bvalue['check'])) $bvalue['check'] = '';
		if(!isset($bvalue['radio'])) $bvalue['radio'] = '';
		if(!isset($bvalue['size'])) $bvalue['size'] = '';
		if(!isset($theblcokvalue[$bkey])) $theblcokvalue[$bkey] = '';
		if(!isset($bvalue['width'])) $bvalue['width'] = '';
		$labelarr = array('type'=>$bvalue['type'], 'alang'=>$bvalue['alang'], 'name'=>$bkey, 'size'=>$bvalue['size'], 'text'=>$bvalue['text'], 'check'=>$bvalue['check'], 'radio'=>$bvalue['radio'], 'options'=>$bvalue['options'], 'other'=>$bvalue['other'], 'width'=>$bvalue['width'], 'value'=>$theblcokvalue[$bkey]);
		if($bkey == 'order') {
			if(!isset($theblcokvalue['order'])) $theblcokvalue['order'] = '';
			if(!isset($theblcokvalue['sc'])) $theblcokvalue['sc'] = '';
			$labelarr['order'] = $theblcokvalue['order'];
			$labelarr['sc'] = $theblcokvalue['sc'];
		}
		echo label($labelarr);
	}
	
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo '<input name="blockid" type="hidden" value="'.$thevalue['blockid'].'" />';
	echo '<input name="blocktype" type="hidden" value="'.$thevalue['blocktype'].'" />';
	echo '<input name="valuesubmit" type="hidden" value="yes" />';
	echo label(array('type'=>'form-end'));
}

//加密函数
function passport_encrypt($txt, $key) {
	srand((double)microtime() * 1000000);
	$encrypt_key = md5(rand(0, 32000));
	$ctr = 0;
	$tmp = '';
	for($i = 0; $i < strlen($txt); $i++) {
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
	}
	return base64_encode(passport_key($tmp, $key));
}

//加密函数
function passport_key($txt, $encrypt_key) {
	$encrypt_key = md5($encrypt_key);
	$ctr = 0;
	$tmp = '';
	for($i = 0; $i < strlen($txt); $i++) {
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
	}
	return $tmp;
}
?>