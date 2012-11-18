<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_models.php 13489 2009-11-10 02:34:44Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managemodels')) {
	showmessage('no_authority_management_operation');
}

include_once(S_ROOT.'./function/model.func.php');
//初始化
$cpurl =  CPURL;
$s_url = S_URL;
$formhash = formhash();

if(submitcheck('thevaluesubmit')) {	//新建模型_处理程序

	//检测数据合法性
	$modelid = 0;
	$_POST['modelname'] = shtmlspecialchars(trim($_POST['modelname']));
	$_POST['modelalias'] = shtmlspecialchars(trim($_POST['modelalias']));
	$_POST['downloadinterval'] = !empty($_POST['downloadinterval']) ? intval($_POST['downloadinterval']) : 0;
	$_POST['allowfilter'] = !empty($_POST['allowfilter']) ? 1 : 0;
	$_POST['listperpage'] = !empty($_POST['listperpage']) ? intval($_POST['listperpage']) : 0;
	$_POST['seokeywords'] = !empty($_POST['seokeywords']) ? shtmlspecialchars(trim($_POST['seokeywords'])) : '';
	$_POST['seodescription'] = !empty($_POST['seodescription']) ? shtmlspecialchars(trim($_POST['seodescription'])) : '';
	$_POST['thumbarray'][0] = !empty($_POST['thumbarray'][0]) ? intval($_POST['thumbarray'][0]) : 400;
	$_POST['thumbarray'][1] = !empty($_POST['thumbarray'][1]) ? intval($_POST['thumbarray'][1]) : 300;
	$_POST['systemtpl'] = empty($_POST['systemtpl']) ? 0 : 1;
	$_POST['tpl'] = !empty($_POST['tpl']) ? shtmlspecialchars(trim($_POST['tpl'])) : '';
	$_POST['resettpl'] = !empty($_POST['resettpl']) ? 1 : 0;
	$customaddfeed = bindec(intval($_POST['allowfeed'][2]).intval($_POST['allowfeed'][1]));
	$_POST['allowfeed'] = empty($customaddfeed) ? 0 : $customaddfeed;
	$_POST['fielddefault'] = !empty($_POST['fielddefault']) ? shtmlspecialchars(trim($_POST['fielddefault'])) : '';
	$_POST['mid'] = !empty($_POST['mid']) ? intval($_POST['mid']) : 0;
	$todir = S_ROOT.'./model/data/'.$_POST['modelname'].'/';
	$resultmodels = array();

	//建立模型目录
	if(!is_dir($todir)) {
		@mkdir($todir, 0777);
	}
	if(!is_dir($todir.'images/')) {
		@mkdir($todir.'images/', 0777);
	}
	if(!preg_match("/^[a-z0-9]{2,20}$/i", $_POST['modelname'])) {
		showmessage('model_name_error');
	} else {
		if($_POST['mid'] == 0) {
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('models').' WHERE modelname = \''.$_POST['modelname'].'\'');
			$num = $_SGLOBAL['db']->num_rows($query);
			if($num > 0) {
				showmessage('model_exists_error');
			}
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('channels').' WHERE nameid = \''.$_POST['modelname'].'\'');
			$num = $_SGLOBAL['db']->num_rows($query);
			if($num > 0) {
				showmessage('model_system_exisis_error');
			}
		} else {
			$query = $_SGLOBAL['db']->query('SELECT modelname FROM '.tname('models').' WHERE mid = \''.$_POST['mid'].'\'');
			$resultmodels = $_SGLOBAL['db']->fetch_array($query);
			
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('models').' WHERE mid <> \''.$_POST['mid'].'\' AND modelname = \''.$_POST['modelname'].'\'');
			$num = $_SGLOBAL['db']->num_rows($query);
			if($num > 0) {
				showmessage('model_exists_error');
			}
		}
	}
	if(!preg_match("/^[\x80-\xff_a-z0-9]{2,60}$/i", $_POST['modelalias'])) {
		showmessage('model_other_name_error');
	}
	if(!empty($_POST['seokeywords']) && strlen($_POST['seokeywords']) > 200) {
		showmessage('model_key_name_error');
	}
	if(!empty($_POST['seodescription']) && strlen($_POST['seodescription']) > 200) {
		showmessage('model_key_name_error');
	}
	if(strlen($_POST['thumbarray'][0]) > 4) {
		showmessage('model_subject_pic_width_error');
	}
	if(strlen($_POST['thumbarray'][1]) > 4) {
		showmessage('model_subject_pic_height_error');
	}

	if(empty($_POST['mid']) || !empty($_POST['resettpl'])) {
		//检测模板是否存在，否则生成
		if(empty($_POST['tpl'])) {
			showmessage('select_model_tpl');
		}
		if(!empty($_POST['mid']) && !empty($_POST['resettpl'])) {
			deltree($todir, array('validate.js'));
		}
		if(empty($_POST['systemtpl'])) {
			inittemplate($todir, $_POST['tpl']);
			$_POST['tpl'] = '';
		}
	}

	$insertsqlarr = array(
			'modelname' => $_POST['modelname'],
			'modelalias' => $_POST['modelalias'],
			'downloadinterval' => $_POST['downloadinterval'],
			'allowfilter' => $_POST['allowfilter'],
			'listperpage' => $_POST['listperpage'],
			'seokeywords' => $_POST['seokeywords'],
			'seodescription' => $_POST['seodescription'],
			'thumbsize' => implode(',', $_POST['thumbarray']),
			'tpl' => $_POST['tpl'],
			'fielddefault' => $_POST['fielddefault'],
			'allowfeed' => $_POST['allowfeed']
	);

	if($_POST['mid'] == 0) {
		$modelid = inserttable('models', $insertsqlarr, 1);
		if($modelid <= 0) {
			deltree($todir);
			showmessage('create_model_tpl_error');
		}
	} else {
		$modelid = $_POST['mid'];
		if(empty($_POST['resettpl'])) {
			unset($insertsqlarr['tpl']);
		}
		$wheresqlarr = array('mid'=>$_POST['mid']);
		updatetable('models', $insertsqlarr, $wheresqlarr);
	}

	//建立数据表
	$sqlfile = S_ROOT.'./data/sql/models.sql';
	$newsql = '';
	if(@$fp = fopen($sqlfile, 'r')) {
		$readsql = fread($fp, filesize($sqlfile));
		fclose($fp);
	}
	if($tablepre != 'supe_') {
		$newsql = str_replace(array('supe_', '[models]'), array($tablepre, $_POST['modelname']), $readsql);	//替换表名前缀
	} else {
		$newsql = str_replace('[models]', $_POST['modelname'], $readsql);
	}

	preg_match_all("/CREATE TABLE ([a-z0-9_]+) *?\(/i", $newsql, $temp);
	$tablename = implode(',', $temp[1]);
	if($_POST['mid'] > 0) {
		//更新channel
		$sqlarr = array(
			'nameid' => $_POST['modelname'],
			'name' => $_POST['modelalias'],
			'url' => ''
		);
		updatetable('channels', $sqlarr, array('nameid' => $resultmodels['modelname']));
	} else {
		//建立初始表
		$succeedtable = $errortable = array();
		if(!empty($tablename)) {
			$namearr = explode(',', $tablename);
			foreach ($namearr as $value) {
				$creatsql = getcreatsql($newsql, $value);
				$_SGLOBAL['db']->query("DROP TABLE IF EXISTS $value", 'SILENT');
				$query = $_SGLOBAL['db']->query($creatsql, 'SILENT');
				if(!$query) {
					$errortable[] = $value;
				} else {
					$succeedtable[] = $value;
				}
			}
		}
		//数据库回滚
		if(!empty($errortable)) {
			if(!empty($succeedtable)) {
				foreach($succeedtable as $value) {
					$_SGLOBAL['db']->query("DROP TABLE IF EXISTS $value", 'SILENT');
				}
			}
			$_SGLOBAL['db']->query('DELETE FROM '.tname('models').' WHERE modelname =\''.$_POST['modelname'].'\'');
			
			deltree($todir);
			$strerrortable = implode(',', $errortable);
			showmessage($alang['table_initialization_error'].$strerrortable);
		}
		//添加channel
		$sqlarr = array(
			'nameid' => $_POST['modelname'],
			'name' => $_POST['modelalias'],
			'status' => 1,
			'type' => 'model',
			'url' => ''
		);
		inserttable('channels', $sqlarr);
	}
	
	//更新缓存
	updatesettingcache();
	updateuserspacemid();
	updatemodel('mid', $modelid);
	writemodelvalidate('mid', $modelid);
	
	if($_POST['mid'] == 0) {
		//模型建立成功
		$tmpurl =  S_URL.'/m.php?name='.$_POST['modelname'];
		$output = <<<EOF
			<p>$alang[create_model_suc]</p>
			<ul>
				<li>$alang[create_model_help_1]
					<a href="$tmpurl" target="_blank"><strong>$alang[model_index]</strong> $alang[click_to_enter]</a></li>
				<li>$alang[create_model_help_2]<a href="$cpurl?action=models&op=addfield&mid=$modelid">"$alang[new_create_field]"</a>.$alang[create_model_help_2_about]
					$alang[you_can]<a href="$cpurl?action=models&op=addfield&mid=$modelid">"$alang[new_create_field]"</a>$alang[in_new_customfield],
					$alang[aslo_through]<a href="$cpurl?action=models&op=field&mid=$modelid">"$alang[field_management]"</a>$alang[in_new_customfield_1]</li>
				<li>$alang[create_model_help_3]<a href="$cpurl?action=modelcategories&mid=$modelid">"$alang[classified_management]"</a>.$alang[create_model_help_3_1]
					$alang[create_model_help_3_2]<a href="$cpurl?action=modelcategories&mid=$modelid">"$alang[classified_management]"</a>$alang[create_model_help_3_3].</li>
				<li>$alang[create_model_help_4]</li>
			</ul>
			<div class="buttons">
				<input type="button" value="$alang[new_create_field]" onclick="window.location.href='$cpurl?action=models&op=addfield&mid=$modelid'"> 
				<input type="button" value="$alang[field_management]" onclick="window.location.href='$cpurl?action=models&op=field&mid=$modelid'"> 
				<input type="button" value="$alang[classified_management]" onclick="window.location.href='$cpurl?action=modelcategories&mid=$modelid'"> 
			</div>
EOF;
		showmessage($output);
	} else {
		$output = <<<EOF
			<p>$alang[edit_model_suc]</p>
			<div class="buttons">
				<input type="button" value="$alang[return_model_category]" onclick="window.location.href='$cpurl?action=models'"> 
				<input type="button" value="$alang[go_field_management]" onclick="window.location.href='$cpurl?action=models&op=field&mid=$modelid'"> 
				<input type="button" value="$alang[go_info_management]" onclick="window.location.href='$cpurl?action=modelmanages&mid=$modelid'">
			</div>
EOF;
		showmessage($output);
	}
	exit();
	
} elseif(submitcheck('theaddfieldform')) {	//字段提交

	include_once(S_ROOT.'./include/model_field.inc.php');
	//检验数据
	$_POST['mid'] = !empty($_POST['mid']) ? intval($_POST['mid']) : 0;
	$_POST['id'] = !empty($_POST['id']) ? intval($_POST['id']) : 0;
	$_POST['upid'] = !empty($_POST['upid']) && $_POST['formtype'] == 'linkage' ? intval($_POST['upid']) : 0;
	$resultmodels = $resultcolumns = array();
	$resultmodels = getmodelinfo($_POST['mid']);
	$_POST['fieldname'] = !empty($_POST['fieldname']) ? shtmlspecialchars(trim($_POST['fieldname'])) : '';
	$_POST['fieldcomment'] = !empty($_POST['fieldcomment']) ? shtmlspecialchars(trim($_POST['fieldcomment'])) : '';
	$_POST['fieldlength'] = !empty($_POST['fieldlength']) && intval($_POST['fieldlength']) > 0 && !preg_match("/^(TEXT|MEDIUMTEXT|LONGTEXT|FLOAT|DOUBLE)$/i", $_POST['fieldtype'])? intval($_POST['fieldlength']) : 0;
	$_POST['fielddefault'] = isset($_POST['fielddefault']) && !preg_match("/^(TEXT|MEDIUMTEXT|LONGTEXT)$/i", $_POST['fieldtype']) ? shtmlspecialchars(trim($_POST['fielddefault'])) : '';
	$_POST['allowindex'] = !empty($_POST['allowindex']) && intval($_POST['allowindex']) == 1 ? 1 : 0;
	$_POST['allowshow'] = !empty($_POST['allowshow']) && intval($_POST['allowshow']) == 1 ? 1 : 0;
	$_POST['allowlist'] = !empty($_POST['allowlist']) && intval($_POST['allowlist']) == 1 ? 1 : 0;
	$_POST['allowsearch'] = !empty($_POST['allowsearch']) && intval($_POST['allowsearch']) == 1 ? 1 : 0;
	$_POST['allowpost'] = !empty($_POST['allowpost']) && intval($_POST['allowpost']) == 1 ? 1 : 0;
	$_POST['isfixed'] = !empty($_POST['isfixed']) && intval($_POST['isfixed']) == 1 && !preg_match("/^(VARCHAR|TEXT|MEDIUMTEXT|LONGTEXT)$/i", $_POST['fieldtype']) ? 1 : 0;
	$_POST['ishtml'] = !empty($_POST['ishtml']) && intval($_POST['ishtml']) == 1 && preg_match("/^(text|textarea)$/i", $_POST['formtype']) ? 1 : 0;
	$_POST['isbbcode'] = !empty($_POST['isbbcode']) && intval($_POST['isbbcode']) == 1 && preg_match("/^(text|textarea)$/i", $_POST['formtype']) ? 1 : 0;
	$_POST['isrequired'] = !empty($_POST['isrequired']) && intval($_POST['isrequired']) == 1 ? 1 : 0;
	$_POST['fielddata'] = !empty($_POST['fielddata']) && preg_match("/^(select|linkage|radio|checkbox)$/i", $_POST['formtype']) ? shtmlspecialchars($_POST['fielddata']) : '';
	$_POST['isfile'] = !empty($_POST['formtype']) && $_POST['formtype'] == 'file' ? 1 : 0;
	$_POST['isimage'] = !empty($_POST['formtype']) && $_POST['formtype'] == 'img' ? 1 : 0;
	$_POST['isflash'] = !empty($_POST['formtype']) && $_POST['formtype'] == 'flash' ? 1 : 0;

	if(preg_match("/^(img|flash)$/i", $_POST['formtype'])) {
		$_POST['fieldtype'] = 'VARCHAR';
		$_POST['fieldlength'] = 150;
		$_POST['fielddefault'] = '';
		$_POST['allowindex'] = 0;
		$_POST['allowlist'] = 0;
		$_POST['allowsearch'] = 0;
		$_POST['isfixed'] = 0;
		$_POST['ishtml'] = 0;
	} elseif($_POST['formtype'] == 'file') {
		$_POST['fieldtype'] = 'INT';
		$_POST['fieldlength'] = 8;
		$_POST['fielddefault'] = 0;
		$_POST['allowindex'] = 0;
		$_POST['allowlist'] = 0;
		$_POST['allowsearch'] = 0;
		$_POST['isfixed'] = 0;
		$_POST['ishtml'] = 0;
	} elseif($_POST['formtype'] == 'timestamp') {
		$_POST['fieldtype'] = 'INT';
		$_POST['fieldlength'] = 10;
	}
	
	if(!preg_match("/^[a-z][a-z_0-9]{1,29}$/i", $_POST['fieldname'])) {
		jsshowmessage('fieldname_error');
	} else {
		if(!empty($systemfieldarr)) {
			foreach ($systemfieldarr as $value) {
				if($value['fieldname'] == $_POST['fieldname']) {
					jsshowmessage('field_is_system_key');	//字段名为系统字段
				}
			}
		}
		$sql = 'SELECT id FROM '.tname('modelcolumns').' WHERE mid = \''.$_POST['mid'].'\' AND fieldname = \''.$_POST['fieldname'].'\'';
		if(!empty($_POST['id'])) $sql .= ' AND id != \''.$_POST['id'].'\'';
		$query = $_SGLOBAL['db']->query($sql);
		$num = $_SGLOBAL['db']->num_rows($query);
		if($num > 0) {
			jsshowmessage('field_is_exists');	//字段名已经存在
		}
	}
	
	if(!preg_match("/.{2,60}$/i", $_POST['fieldcomment'])) {
		jsshowmessage('fieldcomment_error');	//字段说明不合法，字段说明长度2-60个字符
	}
	if(!preg_match("/^(VARCHAR|CHAR|TEXT|MEDIUMTEXT|LONGTEXT|TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT|FLOAT|DOUBLE)$/i", $_POST['fieldtype'])) {
		jsshowmessage('fieldtype_error');	//数据表字段类型不合法
	}
	if(!preg_match("/^(text|select|linkage|radio|checkbox|textarea|timestamp|img|flash|file)$/i", $_POST['formtype'])) {
		jsshowmessage('formtype_error');	//表单字段类型不合法
	}
	
	//编辑字段时
	if(!empty($_POST['id'])) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelcolumns').' WHERE id = \''.$_POST['id'].'\'');
		$resultcolumns = $_SGLOBAL['db']->fetch_array($query);
		if(empty($resultcolumns)) {
			jsshowmessage('edit_field_not_exists');	//您所编辑的字段不存在
		}
		if((preg_match("/^(img|flash|file)$/i", $_POST['formtype']) || preg_match("/^(img|flash|file)$/i", $resultcolumns['formtype'])) && $resultcolumns['formtype'] != $_POST['formtype']) {	//当img flash file类型时拒绝修改
			if($resultcolumns['formtype'] != $_POST['formtype']) {
				jsshowmessage('upload_fileext_error');	//修改前或修改后的表单字段类型为img、flash、file类型时，不允许修改表单字段类型
			}
		}
		if(!empty($resultcolumns['isfixed']) && preg_match("/^(VARCHAR|TEXT|MEDIUMTEXT|LONGTEXT)$/i", $_POST['fieldtype'])) {	//定长表中不允许出现变长字段
			jsshowmessage('edit_fieldtype_error');	//修改前的字段存在定长表中，因此不允许出现变长字段(VARCHAR|TEXT|MEDIUMTEXT|LONGTEXT)
		}
		if($resultcolumns['isfixed'] != $_POST['isfixed']) {
			jsshowmessage('edit_isfixed_error');	//字段建立后不允许修改定长与不定长
		}
		if(empty($resultcolumns['isfixed']) && $_POST['fieldtype'] == 'CHAR') {
			jsshowmessage('edit_isfixed_char_error');	//修改前的字段存在不定长表中，请将数据表字段类型CHAR修改成VARCHAR
		}
	}
	
	if(preg_match("/^(VARCHAR|CHAR)$/i", $_POST['fieldtype']) && $_POST['fieldlength'] > 255) {
		jsshowmessage($alang['fieldtype_length_1'].$_POST['fieldtype'].$alang['fieldtype_length_2']);	//当数据表字段类型为时，字段长度不能大于255
	}
	if($_POST['fieldtype'] == 'CHAR' && empty($_POST['isfixed'])) {
		jsshowmessage('edit_char_to_isfixed_error');	//当为不定长表时，请将数据表字段类型CHAR修改为VARCHAR
	}
	if($_POST['fieldtype'] == 'TINYINT') {	//TINYINT 判断
		if($_POST['fieldlength'] > 3) {
			jsshowmessage('tinyint_length_error');
		}
		if($_POST['fielddefault'] > 127 || $_POST['fielddefault'] < -128) {
			jsshowmessage('tinyint_default_length');
		}
	}
	if($_POST['fieldtype'] == 'SMALLINT') {	//SMALLINT 判断
		if($_POST['fieldlength'] > 5) {
			jsshowmessage('smallint_length_error');
		}
		if($_POST['fielddefault'] > 32767 || $_POST['fielddefault'] < -32768) {
			jsshowmessage('smallint_default_length');
		}
	}
	if($_POST['fieldtype'] == 'MEDIUMINT') {	//MEDIUMINT 判断
		if($_POST['fieldlength'] > 7) {
			jsshowmessage('mediumint_length_error');
		}
		if($_POST['fielddefault'] > 8388607 || $_POST['fielddefault'] < -8388608) {
			jsshowmessage('mediumint_default_length');
		}
	}
	if($_POST['fieldtype'] == 'INT') {	//INT 判断
		if($_POST['fieldlength'] > 10) {
			jsshowmessage('int_length_error');
		}
		if($_POST['fielddefault'] > 2147483647 || $_POST['fielddefault'] < -2147483648) {
			jsshowmessage('int_default_length');
		}
	}
	if($_POST['fieldtype'] == 'BIGINT') {	//BIGINT 判断
		if($_POST['fieldlength'] > 19) {
			jsshowmessage('bigint_length_error');
		}
		if($_POST['fielddefault'] > 9223372036854775807 || $_POST['fielddefault'] < -9223372036854775808) {
			jsshowmessage('bigint_default_length');
		}
	}
	if(!preg_match("/^(TEXT|MEDIUMTEXT|LONGTEXT|FLOAT|DOUBLE)$/i", $_POST['fieldtype']) && $_POST['fieldlength'] <= 0) {
		jsshowmessage($alang['the_fieldtype_1'].$_POST['fieldtype'].$alang['the_fieldtype_2']);
	} elseif(!preg_match("/^(FLOAT|DOUBLE)$/i", $_POST['fieldtype'])) {
		if(!preg_match("/^(VARCHAR|CHAR|TEXT|MEDIUMTEXT|LONGTEXT)$/i", $_POST['fieldtype'])) {	//数值型
			if(preg_match("/^-\d*$/i", intval($_POST['fielddefault']))) {	//当为负数时
				if(strlen($_POST['fielddefault']) > $_POST['fieldlength'] + 1) {
					jsshowmessage('default_length_error');	//初始化值为负数时长度不应长于字段长度值+1
				}
			} else {	//正数
				if(strlen($_POST['fielddefault']) > $_POST['fieldlength']) {
					jsshowmessage('fieldlength_ng_fielddefault');	//初始化值长度不应长于字段长度值
				}
			}
			
		} else {
			if(strlen($_POST['fielddefault']) > $_POST['fieldlength']) {
				jsshowmessage('fieldlength_ng_fielddefault');
			}
		}
	}

	if(preg_match("/^(TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT)$/i", $_POST['fieldtype'])) {
		if(!preg_match("/^[+-]?[0-9]*$/", $_POST['fielddefault'])) {
			jsshowmessage($alang['the_fieldtype_1'].$_POST['fieldtype'].$alang['the_fieldtype_3']);
		}
		$_POST['fielddefault'] = intval($_POST['fielddefault']);
	} elseif(preg_match("/^(FLOAT|DOUBLE)$/i", $_POST['fieldtype'])) {
		if(!preg_match("/^[+-]?[0-9]+[0-9.]*$/", $_POST['fielddefault'])) {
			jsshowmessage($alang['the_fieldtype_1'].$_POST['fieldtype'].$alang['the_fieldtype_4']);
		}
		$_POST['fielddefault'] = doubleval($_POST['fielddefault']);
	}
	
	//当数据表字段为数值型时，判断表单显示元素是否都是数值型
	if(!preg_match("/^(VARCHAR|CHAR|TEXT|MEDIUMTEXT|LONGTEXT)$/i", $_POST['fieldtype']) && preg_match("/^(select|linkage|radio|checkbox)$/i", $_POST['formtype'])) {
		$tmpfielddata = strim(explode("\r\n", $_POST['fielddata']));
		if(!empty($tmpfielddata)) {
			foreach($tmpfielddata as $tmpkey => $tmpvalue) {
				if(!empty($tmpvalue)) {
					if(strpos($tmpvalue, '=')) {
						if($_POST['formtype'] == 'linkage') {
							$tmplinkage = intval(trim(substr($tmpvalue, 0, strpos($tmpvalue, '='))));
							if(empty($tmplinkage)) {
								jsshowmessage('formtype_linkage_error');		//当为联动下拉框时,元素索引不正确.
							}
						}
						$tmpvalue = trim(substr($tmpvalue, strpos($tmpvalue, '=')+1));
					} else {
						if($_POST['formtype'] == 'linkage') {
							jsshowmessage('formtype_linkage_no_key');	//当为联动下拉框时,请为每个元素设置索引.
						}
					}
					if(preg_match("/^(FLOAT|DOUBLE)$/i", $_POST['fieldtype'])) {
						if(!preg_match("/^[+-]?[0-9]+[0-9.]*$/", $tmpvalue)) {
							jsshowmessage('table_type_float');	//当数据表字段类型为浮点类型时，表单显示元素也必须为浮点型
						}
						$tmpfielddata[$tmpkey] = $_POST['formtype'] != 'linkage' ? doubleval($tmpvalue) : trim(substr($tmpfielddata[$tmpkey], 0, strpos($tmpfielddata[$tmpkey], '='))).' = '.doubleval($tmpvalue);
					} else {
						if(!preg_match("/^[+-]?[0-9]*$/", $tmpvalue)) {
							jsshowmessage('table_type_int');	//当数据表字段类型为整型数值类型时，表单显示元素也必须为整型数值型
						}
						$tmpfielddata[$tmpkey] = $_POST['formtype'] != 'linkage' ? intval($tmpvalue) : trim(substr($tmpfielddata[$tmpkey], 0, strpos($tmpfielddata[$tmpkey], '='))).' = '.intval($tmpvalue);
					}
				}
			}
		}
		$_POST['fielddata'] = implode("\r\n", $tmpfielddata);
	}

	//检查是否有重复值
	if(!empty($_POST['fielddata'])) {
		$tmpfielddata = strim(explode("\r\n", $_POST['fielddata']));
		if(count($tmpfielddata) > count(array_unique($tmpfielddata))) {
			jsshowmessage('fielddata_repeat');
		}
		foreach($tmpfielddata as $tmpkey => $tmpvalue) {
			if(strlen($tmpvalue) <= 0) {
				unset($tmpfielddata[$tmpkey]);
			}
		}
		$_POST['fielddata'] = implode("\r\n", $tmpfielddata);
	}

	if(!empty($_POST['allowsearch']) && empty($_POST['allowshow'])) {
		jsshowmessage('required_allowsearch');	//当允许搜索时,必须选中允许显示
	}
	if(!empty($_POST['isrequired']) && empty($_POST['allowpost'])) {
		jsshowmessage('required_field');	//当字段必填时，必须选中允许投稿
	}
	if(!empty($_POST['allowlist']) && empty($_POST['allowshow'])) {
		jsshowmessage('required_allowshow');	//当字段允许列表显示时,必须选中允许显示
	}

	//修改表结构
	if(empty($_POST['id'])) {
		$sql = 'ALTER TABLE ';
		$sql .= $_POST['isfixed'] == 1 ? tname($resultmodels['modelname'].'items') : tname($resultmodels['modelname'].'message');
		$sql .= ' ADD `'.$_POST['fieldname'].'` '.$_POST['fieldtype'];
		$sql .= preg_match("/^(TEXT|MEDIUMTEXT|LONGTEXT|FLOAT|DOUBLE)$/i", $_POST['fieldtype']) ? '' : '('.$_POST['fieldlength'].') ';
		$sql .= ' NOT NULL ';
		$sql .= !empty($_POST['fielddefault']) ? ' DEFAULT \''.$_POST['fielddefault'].'\' ' : '';
	} else {
		$sql = 'ALTER TABLE ';
		$sql .= $resultcolumns['isfixed'] == 1 ? tname($resultmodels['modelname'].'items') : tname($resultmodels['modelname'].'message');
		$sql .= ' CHANGE `'.$resultcolumns['fieldname'].'`  `'.$_POST['fieldname'].'` '.$_POST['fieldtype'];
		$sql .= preg_match("/^(TEXT|MEDIUMTEXT|LONGTEXT|FLOAT|DOUBLE)$/i", $_POST['fieldtype']) ? '' : '('.$_POST['fieldlength'].') ';
		$sql .= ' NOT NULL ';
		$sql .= !empty($_POST['fielddefault']) ? ' DEFAULT \''.$_POST['fielddefault'].'\' ' : '';
	}
	
	if(!$_SGLOBAL['db']->query($sql, 'SILENT')) {
		$errorinfo = '<p>'.(empty($_POST['id']) ? $alang['space_add_tag']:$alang['ad_adtype_detail']).$alang['field_sql_error'];
		$errorinfo .= $sql.'</p>';
		$errorinfo .= '<p>MYSQL : <br />#'.$_SGLOBAL['db']->errno().' - '.str_replace(array($GLOBALS['tablepre'], $GLOBALS['tablepre_bbs']), '[Table]', $_SGLOBAL['db']->error()).'</p></div>';
		jsshowmessage($errorinfo);
	}

	//添加数据
	$insertsqlarr = array(
			'mid' => $_POST['mid'],
			'upid' => $_POST['upid'],
			'fieldname' => $_POST['fieldname'],
			'fieldcomment' => $_POST['fieldcomment'],
			'fieldtype' => $_POST['fieldtype'],
			'fieldlength' => $_POST['fieldlength'],
			'fielddefault' => $_POST['fielddefault'],
			'formtype' => $_POST['formtype'],
			'fielddata' => $_POST['fielddata'],
			'allowindex' => $_POST['allowindex'],
			'allowshow' => $_POST['allowshow'],
			'allowlist' => $_POST['allowlist'],
			'allowsearch' => $_POST['allowsearch'],
			'allowpost' => $_POST['allowpost'],
			'isfixed' => $_POST['isfixed'],
			'isbbcode' => $_POST['isbbcode'],
			'ishtml' => $_POST['ishtml'],
			'isrequired' => $_POST['isrequired'],
			'isfile' => $_POST['isfile'],
			'isimage' => $_POST['isimage'],
			'isflash' => $_POST['isflash']
		);
	if(empty($_POST['id'])) {
		$id = inserttable('modelcolumns', $insertsqlarr, 1);
		if(empty($id)) {
			$sql = 'ALTER TABLE ';
			$sql .= $_POST['isfixed'] == 1 ? tname($resultmodels['modelname'].'items') : tname($resultmodels['modelname'].'message');
			$sql .= ' DROP `'.$_POST['fieldname'].'` ';
			$_SGLOBAL['db']->query($sql);
			jsshowmessage('create_field_error');
		}
	} else {
		updatetable('modelcolumns', $insertsqlarr, array('id' => $_POST['id']));
	}
	updatemodel('mid', $_POST['mid']);
	writemodelvalidate('mid', $_POST['mid']);
	jsshowmessage($alang['field'].(empty($_POST['id']) ? $alang['space_add_tag']:$alang['ad_adtype_detail']).$alang['success'], CPURL.'?action=models&op=field&mid='.$_POST['mid']);

} elseif(submitcheck('delmodelconfirm')) {	//执行删除模型操作

	$_POST['mid'] = !empty($_POST['mid']) ? intval($_POST['mid']) : 0;
	$resultmodels = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('models').' WHERE mid = \''.$_POST['mid'].'\'');
	$resultmodels = $_SGLOBAL['db']->fetch_array($query);
	if(empty($resultmodels)) {
		showmessage('visit_the_channel_does_not_exist');
	}

	//删除数据库
	$sql = '';
	$tmpdelarr = array('items', 'message');
	foreach($tmpdelarr as $tmpkey => $tmpvalue) {
		$tmpdelarr[$tmpkey] = tname($resultmodels['modelname']).$tmpvalue;
	}
	$sql = implode(',', $tmpdelarr);
	if(!empty($sql)) {
		$sql = 'DROP TABLE IF EXISTS '.$sql;
		$_SGLOBAL['db']->query($sql);
	}
	deletetable('models', array('mid' => $resultmodels['mid']));	//删除模型表相关数据
	deletetable('modelcolumns', array('mid' => $resultmodels['mid']));	//删除模型字段表相关数据
	deletetable('categories', array('type' => $resultmodels['modelname']));	//删除分类表相关数据
	deletetable('modelfolders', array('mid' => $resultmodels['mid']));	//删除模型投稿表相关数据
	deletetable('spacecomments', array('type' => $resultmodels['modelname']));	//删除评论表相关数据
	deletetable('channels', array('nameid' => $resultmodels['modelname']));	//删除channels表相关数据
	$hash = 'm'.str_pad($_POST['mid'], 6, 0, STR_PAD_LEFT);
	delattachments($hash); //删除附件

	//删除模型目录及文件
	$todir = S_ROOT.'./model/data/'.$resultmodels['modelname'].'/';
	deltree($todir);
	
	//删除缓存文件
	$cachefile = S_ROOT.'./cache/model/model_'.$_POST['mid'].'.cache.php';
	if(file_exists($cachefile)) {
		@unlink($cachefile);
	}
	$cachefile = S_ROOT.'./cache/model/model_'.$resultmodels['modelname'].'.cache.php';
	if(file_exists($cachefile)) {
		@unlink($cachefile);
	}
	
	//更新缓存
	updatesettingcache();
	updateuserspacemid();

	showmessage($alang['model_del_suc'], CPURL.'?action=models');
	exit();
	
} elseif(submitcheck('delfieldconfirm')) {	//执行删除字段操作

	$_POST['mid'] = !empty($_POST['mid']) ? intval($_POST['mid']) : 0;
	$resultmodels = array();
	$resultmodels = getmodelinfo($_POST['mid']);
	$_POST['id'] = !empty($_POST['id']) ? intval($_POST['id']) : 0;
	$resultfield = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelcolumns').' WHERE id = \''.$_POST['id'].'\'');
	$resultfield = $_SGLOBAL['db']->fetch_array($query);
	if(empty($resultfield)) {
		showmessage('field_not_exists');
	}

	//删除数据库
	$sql = 'ALTER TABLE '.tname($resultmodels['modelname']);
	$sql .= $resultfield['isfixed'] == 1 ? 'items' : 'message';
	$sql .= ' DROP `'.$resultfield['fieldname'].'` ';
	$_SGLOBAL['db']->query($sql);
	deletetable('modelcolumns', array('id' => $resultfield['id']));
	updatemodel('mid', $_POST['mid']);
	showmessage('field_del_suc', CPURL.'?action=models&op=field&mid='.$resultmodels['mid']);
	
} elseif(submitcheck('delimportsubmit')) {	//执行删除备份

	$_POST['worddelete'] = !empty($_POST['worddelete']) ? intval($_POST['worddelete']) : 0;
	if(!empty($_POST['worddelete'])) {
		if(!empty($_POST['delexport'])) {
			foreach($_POST['delexport'] as $tmpvalue) {
				deltree(S_ROOT.'/data/model/'.$tmpvalue.'/');
			}
			showmessage('delete_success', CPURL.'?action=models&op=import');
		} else {
			showmessage('not_designated_backup_del', CPURL.'?action=models&op=import');
		}
	} else {
		showmessage('not_designated_op', CPURL.'?action=models&op=import');
	}

} elseif(submitcheck('theimportsubmit')) {	//导入模型

	if(!ckfounder($_SGLOBAL['supe_uid'])) {
		showmessage('no_authority_management_operation');
	}
	
	if(!preg_match("/^[a-z0-9]{2,20}$/i", $_POST['modelname'])) {
		showmessage('model_name_error');
	}
	if(!preg_match("/^[\x80-\xff_a-z0-9]{2,60}$/i", $_POST['modelalias'])) {
		showmessage('model_other_name_error');
	}
	
	$_POST['modelname'] = shtmlspecialchars(trim($_POST['modelname']));
	$_POST['modelalias'] = shtmlspecialchars(trim($_POST['modelalias']));
	$_POST['datafile'] = shtmlspecialchars(trim($_POST['datafile']));

	$datadir = S_ROOT.'./data/model/';
	$datafile = '';
	$implodefile = array();
	if(!empty($_POST['datafile'])) {
		$datadir .= $_POST['datafile'].'/';
		$_POST['datafile'] = $_POST['datafile'].'.zip';
		$implodefile = array($_POST['datafile']);
	} elseif(!empty($_FILES['zipfile']['name'])) {
		$random = 'import_'.$_SGLOBAL['timestamp'].'_'.random(6);
		$datadir .= $random .'/';
		$datafile = $random .'.'.fileext($_FILES['zipfile']['name']);
		
		if(!is_dir($datadir)) {
			@mkdir($datadir, 0777);
		}

		$tmpname = str_replace('\\', '\\\\', $_FILES['zipfile']['tmp_name']);
		if(@copy($tmpname, $datadir.$datafile)) {
		} elseif((function_exists('move_uploaded_file') && @move_uploaded_file($tmpname, $datadir.$datafile))) {
		} elseif(@rename($tmpname, $datadir.$datafile)) {
		}
		@unlink($tmpname);
		$_POST['datafile'] = $datafile;
	} else {
		showmessage('vars_error_return_try', CPURL.'?action=models&op=import');
	}

	$query = $_SGLOBAL['db']->query('SELECT mid FROM '.tname('models').' WHERE modelname = \''.$_POST['modelname'].'\'');
	$num = $_SGLOBAL['db']->num_rows($query);
	if($num > 0) {
		deltree($datadir, $implodefile);
		showmessage('model_name_existed');
	}

	//解压缩
	require_once S_ROOT .'./include/zip.lib.php';
	$unzip = new SimpleUnzip();
	$unzip->ReadFile($datadir.$_POST['datafile']);

	$zipfilearr = array();
	if($unzip->Count() != 0) {
		foreach($unzip->Entries as $entry) {
			$zipfilearr[] = $entry->Name;
			$fp = fopen($datadir.$entry->Name, 'w');
			fwrite($fp, $entry->Data);
			fclose($fp);
		}
	}

	$copyerrorarr = array();
	if(file_exists($datadir.'model.cache.php')) {	//检查主要sql文件是否存在
		//导入数据表
		include_once($datadir.'model.cache.php');
		$modelsql = $cacheinfo;
		if(count($modelsql) != 4) {
			deltree($datadir, $implodefile);
			showmessage('import_type_error');
		}
		if(empty($modelsql['info']['charset']) || $modelsql['info']['charset'] != $_SCONFIG['charset']) {
			deltree($datadir, $implodefile);
			showmessage('import_model_charset_error');
		}

		$modelsql = shtmlspecialchars($modelsql);
		
		$modelsql[0] = $modelsql['models'];
		$modelsql[1] = $modelsql['columns'];

		if(!is_array($modelsql[0]) || empty($modelsql[0]) || !is_array($modelsql[1])) {
			deltree($datadir, $implodefile);
			showmessage('import_type_error');
		}

		$oldmodelname = $modelsql[0]['modelname'];
		$modelsql[0]['modelalias'] = $_POST['modelalias'];
		foreach($modelsql[0] as $tmpkey => $tmpvalue) {
			$modelsql[0][$tmpkey] = str_replace($oldmodelname, $_POST['modelname'], $modelsql[0][$tmpkey]);
		}

		$mid = 0;
		$mid = inserttable('models', saddslashes($modelsql[0]), 1);
		if($mid <= 0) {
			showmessage('import_sql_error');
		}
		
		$linkagearr = array('id'=>array(), 'upfieldname'=>array());
		if(!empty($modelsql[1])) {
			foreach($modelsql[1] as $tmpvalue) {
				$tmpvalue['mid'] = $mid;
				if(!empty($tmpvalue['upid'])) {
					$linkagearr['upfieldname'][] = $tmpvalue['upid'];
					$tmpvalue['upid'] = 0;
					$linkagearr['id'][] = inserttable('modelcolumns', saddslashes($tmpvalue), 1);
				} else {
					inserttable('modelcolumns', saddslashes($tmpvalue));
				}
			}
		}
		$upfieldname = simplode($linkagearr['upfieldname']);
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('modelcolumns')." WHERE fieldname IN ($upfieldname) AND mid = '$mid'");
		$upfieldname = array();
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$upfieldname[$value['fieldname']] = $value['id'];
		}
		if(!empty($linkagearr['id'])) {
			foreach($linkagearr['id'] as $tmpkey => $tmpvalue) {
				if(!empty($upfieldname[$linkagearr['upfieldname'][$tmpkey]])) {
					updatetable('modelcolumns', array('upid'=>$upfieldname[$linkagearr['upfieldname'][$tmpkey]]), array('id' => $tmpvalue));
				}
			}
		}
		
		$newsql = '';
		$readsql = sreadfile($datadir.'table.sql', 'r');
		preg_match_all("/CREATE TABLE ([a-z0-9_]+)".$oldmodelname."items *?\(/i", $readsql, $temp);
		$newsql = str_replace($temp[1][0].$oldmodelname, $tablepre.$_POST['modelname'], $readsql);	//替换表名前缀
		preg_match_all("/CREATE TABLE ([a-z0-9_]+) *?\(/i", $newsql, $temp);
		$tablename = implode(',', $temp[1]);

		$succeedtable = $errortable = array();
		if(!empty($tablename)) {
			$namearr = explode(',', $tablename);
			$modeldbarr = array($tablepre.$_POST['modelname'].'items', $tablepre.$_POST['modelname'].'message');
			foreach ($namearr as $value) {
				if(!in_array($value, $modeldbarr)) continue;
				$creatsql = getcreatsql($newsql, $value);
				$_SGLOBAL['db']->query("DROP TABLE IF EXISTS $value", 'SILENT');
				$query = $_SGLOBAL['db']->query($creatsql, 'SILENT');
				if(!$query) {
					$errortable[] = $value;
				} else {
					$succeedtable[] = $value;
				}
			}
		}

		//错误时数据库回滚
		if(!empty($errortable)) {
			if(!empty($succeedtable)) {
				foreach($succeedtable as $value) {
					$_SGLOBAL['db']->query("DROP TABLE IF EXISTS $value", 'SILENT');
				}
			}
			$_SGLOBAL['db']->query('DELETE FROM '.tname('models').' WHERE mid =\''.$mid.'\'');
			$_SGLOBAL['db']->query('DELETE FROM '.tname('modelcolumns').' WHERE mid =\''.$mid.'\'');
			
			$strerrortable = implode(',', $errortable);
			deltree($datadir, $implodefile);
			showmessage($alang['table_initialization_error'].$strerrortable);
		}

		//添加channel
		$sqlarr = array(
			'nameid' => $_POST['modelname'],
			'name' => $_POST['modelalias'],
			'status' => 1,
			'type' => 'model',
			'url' => ''
		);
		
		inserttable('channels', $sqlarr);
		
		//增加默认分类
		if(!empty($modelsql['categories'])) {
			
			$catarr = array();
			foreach($modelsql['categories'] as $tmpvalue) {
				if(is_array($tmpvalue)) {
					$setarr = array(
						'name' => $tmpvalue['name'],
						'note' => $tmpvalue['note'],
						'displayorder' => $tmpvalue['displayorder'],
						'url' => $tmpvalue['url'],
						'subcatid' => $tmpvalue['subcatid'],
						'type' => $_POST['modelname']
					);
					$tmpvalue['newcatid'] = inserttable('categories', saddslashes($setarr), 1);
					$catarr[$tmpvalue['catid']] = $tmpvalue;
				} else {
					$setarr = array(
						'name' => $tmpvalue,
						'type' => $_POST['modelname']
					);
					$tmpvalue['newcatid'] = inserttable('categories', saddslashes($setarr), 1);
				}
			}
			
			if(empty($catarr)) {
				$_SGLOBAL['db']->query("UPDATE ".tname('categories')." SET subcatid=catid WHERE `type`='$_POST[modelname]'");
			} else {
				foreach($catarr as $value) {
					$setarr = $newsubarr = array();
					if(!empty($value['upid'])) {
						$setarr['upid'] = $catarr[$value['upid']]['newcatid'];
					}
					if($value['catid'] == $value['subcatid']) {
						$setarr['subcatid'] = $value['newcatid'];
					} else {
						$subarr = explode(',', $value['subcatid']);
						foreach($subarr as $val) {
							$newsubarr[] = $catarr[$val]['newcatid'];
						}
						$setarr['subcatid'] = implode(',', $newsubarr);
					}
					updatetable('categories', $setarr, array('catid'=>$value['newcatid']));
				}
			}
			
		}

		$tableinfo = array();
		if($_SGLOBAL['db']->version() > '4.1') {
			$query = $_SGLOBAL['db']->query("SHOW FULL COLUMNS FROM ".tname($_POST['modelname'].'items'), 'SILENT');
		} else {
			$query = $_SGLOBAL['db']->query("SHOW COLUMNS FROM ".tname($_POST['modelname'].'items'), 'SILENT');
		}
		while($field = @$_SGLOBAL['db']->fetch_array($query)) {
			$tableinfo[$field['Field']] = $field;
		}
		if(empty($tableinfo['hot'])) {
			@$_SGLOBAL['db']->query('ALTER TABLE '.tname($_POST['modelname'].'items').' ADD COLUMN hot mediumint(8) unsigned NOT NULL DEFAULT \'0\'');
		}
		
		//导入模型文件
		if(!empty($zipfilearr)) {
			$to = S_ROOT.'./model/data/'.$_POST['modelname'].'/';
			if(!is_dir($to)) {
				@smkdir($to);
				@smkdir($to.'images/');
			}
			foreach($zipfilearr as $tmpvalue) {
				if(!preg_match('/\.sql$/i', $tmpvalue)) {
					if(file_exists($datadir.$tmpvalue)) {
						if(preg_match('/\.html\.php$/i', $tmpvalue)) {
							$tofile = $to.$tmpvalue;
						} else {
							$tofile = $to.'images/'.$tmpvalue;
						}
						if(!@copy($datadir.$tmpvalue, $tofile)) {
							$copyerrorarr = 'write_error';
							break;
						}
					} else {
					   $copyerrorarr[] = $tmpvalue;
					}
				}
			}
			
			if(!empty($copyerrorarr)) {
				deltree($datadir, $implodefile);
				deltree($to);
				if(!empty($succeedtable)) {
					foreach($succeedtable as $value) {
						$_SGLOBAL['db']->query("DROP TABLE IF EXISTS $value", 'SILENT');
					}
				}
				$_SGLOBAL['db']->query('DELETE FROM '.tname('models').' WHERE mid =\''.$mid.'\'');
				$_SGLOBAL['db']->query('DELETE FROM '.tname('modelcolumns').' WHERE mid =\''.$mid.'\'');
				$_SGLOBAL['db']->query('DELETE FROM '.tname('channels').' WHERE nameid =\''.$_POST['modelname'].'\'');
				updatesettingcache();
				updateuserspacemid();
				
				if(!is_array($copyerrorarr)) {
					showmessage('file_write_error');
				} else {
					showmessage($alang['the_following_documents'].implode('<br />', $copyerrorarr));
				}
			}
		}

		//删除解压缩出来的文件
		deltree($datadir, $implodefile);
		
		//更新缓存
		updatesettingcache();
		updateuserspacemid();
		updatemodel('mid', $mid);
		writemodelvalidate('mid', $mid);
		
		$output = <<<EOF
			<p>$alang[import_model_suc]</p>
			<div class="buttons">
					<input type="button" value="$alang[return_model_category]" onclick="window.location.href='$cpurl?action=models'"> 
					<input type="button" value="$alang[go_field_management]" onclick="window.location.href='$cpurl?action=models&op=field&mid=$mid'"> 
					<input type="button" value="$alang[go_info_management]" onclick="window.location.href='$cpurl?action=modelmanages&mid=$mid'"> 
			</div>
EOF;
		showmessage($output);
		exit();

	} else {
		showmessage('import_model_error');
	}
	
} elseif(submitcheck('fieldlistsubmit')) {	//字段顺序

	$_POST['mid'] = !empty($_POST['mid']) ? intval($_POST['mid']) : 0;
	$fieldarr = array();
	$query = $_SGLOBAL['db']->query('SELECT id, displayorder FROM '.tname('modelcolumns').' WHERE mid = \''.$_POST['mid'].'\'');
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$fieldarr[$value['id']] = $value['displayorder'];
	}
	if(is_array($_POST['displayorder']) && $_POST['displayorder']) {
		foreach ($_POST['displayorder'] as $postcatid => $postdisplayorder) {
			$postcatid = intval($postcatid);
			$postdisplayorder = intval($postdisplayorder);
			if(!empty($postcatid)) {
				if($fieldarr[$postcatid] != $postdisplayorder) {
					updatetable('modelcolumns', array('displayorder'=>$postdisplayorder), array('id' => $postcatid));
				}
			}
		}
	}
	
	updatemodel('mid', $_POST['mid']);
	showmessage('fieldlistsubmit_success', CPURL.'?action=models&op=field&mid='.$_POST['mid']);

}

if (empty($_GET['op'])) {	//模型管理_列表页
	
	$modellist = '';
	$query = $_SGLOBAL['db']->query('SELECT m.*, c.status FROM '.tname('models').' m LEFT JOIN '.tname('channels').' c ON (m.modelname = c.nameid) ORDER BY m.mid DESC');
	while ($temp = $_SGLOBAL['db']->fetch_array($query)) {
		$tmpurl = S_URL.'/m.php?name='.$temp['modelname'];
		$temp['status'] = $temp['status'] > 0 ? $alang['setting_allowtagshow_1'] : $alang['setting_allowtagshow_0'];
		$modellist .= <<<EOF
		<tr class="darkrow" id="ns">
			<td align="center"><a href="$tmpurl" target="_blank">$temp[modelname] ($temp[modelalias])</a></td>
			<td align="center"><a href="$cpurl?action=channel" target="_blank">$temp[status]</a></td>
			<td align="center">
				<a href="?action=models&op=edit&mid=$temp[mid]">$alang[robot_robot_op_edit]</a>&nbsp;|&nbsp;
				<a href="?action=models&op=export&mid=$temp[mid]">$alang[export_model]</a>&nbsp;|&nbsp;
				<a href="?action=models&op=delmodel&mid=$temp[mid]">$alang[delete_model]</a>
			</td>
			<td align="center">
				<a href="?action=models&op=field&mid=$temp[mid]">$alang[field_management]</a>&nbsp;|&nbsp;
				<a href="?action=models&op=addfield&mid=$temp[mid]">$alang[new_create_field]</a>
			</td>
			<td>
				<a href="$cpurl?action=modelmanages&mid=$temp[mid]">$alang[spaces_spacecp]</a>&nbsp;|&nbsp;
				<a href="$cpurl?action=modelmanages&op=add&mid=$temp[mid]">$alang[release_information]</a>&nbsp;|&nbsp;
				<a href="$cpurl?action=modelcategories&mid=$temp[mid]">$alang[classified_management]</a>&nbsp;|&nbsp;
				<a href="$cpurl?action=modelfolders&mid=$temp[mid]">$alang[pending_box_management]</a>&nbsp;|&nbsp;
				<a href="$cpurl?action=modelfolders&mid=$temp[mid]&folder=2">$alang[waste_management_bins]</a>
			</td>
		</tr>
EOF;
	}
		
	$output = <<<EOF
	<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td><h1>$alang[management_model]</h1></td>
			<td class="actions">
				<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
					<tr>
						<td class="active"><a href="{$cpurl}?action=models">$alang[browser_model]</a></td>
						<td><a href="{$cpurl}?action=models&op=add" class="add">$alang[new_model]</a></td>
						<td><a href="{$cpurl}?action=models&op=import">$alang[view_backup]</a></td>
						<td><a href="{$cpurl}?action=models&op=import&do=start" class="add">$alang[import_model]</a></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	
	<div class="colorarea01">
		<table cellspacing="2" cellpadding="2" class="helptable">
			<tr>
				<td>
					<ul>
						<li>$alang[you_can]<a href="$cpurl?action=models&op=add">"$alang[new_model]"</a>$alang[add_myself_model]</li>
						<li style="color: #F00">$alang[when_create_model]<a href="$cpurl?action=channel">"$alang[admincp_header_channel]"</a>$alang[when_create_model_1]<a href="$cpurl?action=channel">$alang[when_create_model_2]</li>
						<li>$alang[when_create_model_3]</li>
					</ul>
				</td>
			</tr>
		</table>
	</div>

	<table cellspacing="0" cellpadding="0" width="100%" class="listtable">
		<tr>
			<th>$alang[model_info]</th>
			<th>$alang[is_use_model]</th>
			<th>$alang[management_model]</th>
			<th>$alang[field_management]</th>
			<th>$alang[spaces_spacecp]</th>
		</tr>
			$modellist
	</table>
EOF;
	echo $output;	

} elseif ($_GET['op'] == 'add' || $_GET['op'] == 'edit') {	//新建模型_提交页面

	$_GET['mid'] = !empty($_GET['mid']) ? intval($_GET['mid']) : 0;
	$tpllist = tpllist($_GET['mid']);
	$checkadd = 'checked="checked"';
	$checkedit = '';
	
	//编辑配置时
	$resultmodels = array('mid' => '', 'modelname' => '', 'modelalias' => '', 'downloadinterval' => '30', 'allowfilter' => '', 
							'listperpage' => '20', 'seokeywords' => '', 'seodescription' => '', 'thumbsize' => '', 'tpl' => '', 'systemtpl' => 'checked="checked"',
							'fielddefault' => '', 'allowfeed' => '1');

	if($_GET['mid'] > 0) {
		$resultmodels = getmodelinfo($_GET['mid']);
		$resultmodels['allowfilter'] = !empty($resultmodels['allowfilter']) ? 'checked="checked"' : '';
		$resultmodels['systemtpl'] = !empty($resultmodels['tpl']) ? 'checked="checked"' : '';
		$resultmodels['allowfeed'] = !empty($resultmodels['allowfeed']) ? intval($resultmodels['allowfeed']) : 0;
		$checkedit = $checkadd;
		$checkadd = '';
	}
	$resultmodels['subjectimagewidth'] = 400;
	$resultmodels['subjectimageheight'] = 300;
	if(!empty($resultmodels['thumbsize'])) {
		$resultmodels['thumbsize'] = explode(',', trim($resultmodels['thumbsize']));
		$resultmodels['subjectimagewidth'] = $resultmodels['thumbsize'][0];
		$resultmodels['subjectimageheight'] = $resultmodels['thumbsize'][1];
	}
	
	$resettpl = '<tbody id="choosetpl" style="display: ;">';
	if($_GET['mid'] > 0) {
		$readonly = 'readonly style="color: #ccc"';
		$resettpl = <<<EOF
			<tr>
				<th>$alang[select_changle_model]</th>
				<td colspan="2"><input type="checkbox" name="resettpl" value="1" onclick="$('choosetpl').style.display = $('choosetpl').style.display == 'none' ? '' : 'none';" />
					$alang[topic_reselect_template]</td>
			</tr>
			<tbody id="choosetpl" style="display: none;">
EOF;
	}
	$output = <<<EOF
	<style type="text/css">
	<!--
		.help { padding-left: 5px; }
		.tpl ul {  margin: 0; padding: 0; }
			.tpl li { float: left; text-align: center; padding: 10px 10px 10px 10px; }
	-->
	</style>
	<script language="javascript">
	<!--
		function autocreate(str) {
			objinput = $(str);
			objcheck = $('is' + str);
			objtr = $('tr_' + str);
			
			objinput.readOnly = objcheck.checked;
			objinput.value = '';
			objinput.focus();
			objinput.style.backgroundColor = objcheck.checked ? '#eee' : '';
			objtr.style.display = objcheck.checked ? '' : 'none';
		}
		
		function showtpl(str) {
			$('tplimage').src = str;
		}
	//-->
	</script>


	<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td><h1>$alang[management_model]</h1></td>
			<td class="actions">
				<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
					<tr>
						<td><a href="{$cpurl}?action=models">$alang[browser_model]</a></td>
						<td class="active"><a href="{$cpurl}?action=models&op=add" class="add">$alang[new_model]</a></td>
						<td><a href="{$cpurl}?action=models&op=import">$alang[view_backup]</a></td>
						<td><a href="{$cpurl}?action=models&op=import&do=start" class="add">$alang[import_model]</a></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	
	<form method="post" name="thevalueform" id="theform" action="$cpurl?action=models">
	<input type="hidden" name="formhash" value="$formhash">
	<a name="base"></a>
	<div class="colorarea02">
		<h2>$alang[model_settings]</h2>
		<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
			<tr id="tr_modelname">
				<th>$alang[model_name_about]</th>
				<td><input name="modelname" type="text" id="modelname" size="30" value="$resultmodels[modelname]" $readonly />
					<input name="mid" type="hidden" id="mid" value="$resultmodels[mid]" />
					$alang[for_example]:shop</td>
			</tr>
			<tr id="tr_modelalias">
				<th>$alang[model_other_name_about]</th>
				<td><input name="modelalias" type="text" id="modelalias" size="30" value="$resultmodels[modelalias]" />
					$alang[model_for_example]</td>
			</tr>
			<tr id="tr_downloadinterval">
				<th>$alang[model_downloadinterval_about]</th>
				<td><input name="downloadinterval" type="text" id="downloadinterval" size="5" value="$resultmodels[downloadinterval]" />$alang[seconds]</td>
			</tr>
			<tr id="tr_allowfilter">
				<th>$alang[model_allowfilter_about]</th>
				<td><input name="allowfilter" type="checkbox" id="allowfilter" value="1" $resultmodels[allowfilter] />$alang[model_allowfilter]</td>
			</tr>
			<tr id="tr_listperpage">
				<th>$alang[model_listperpage]</th>
				<td><input name="listperpage" type="text" id="listperpage" size="5" value="$resultmodels[listperpage]" /></td>
			</tr>
			<tr id="tr_listperpage">
				<th>$alang[model_subject_pic]</th>
				<td>$alang[width] <input type="text" name="thumbarray[0]" value="$resultmodels[subjectimagewidth]" size="5"> $alang[pixel], $alang[height] <input type="text" name="thumbarray[1]" value="$resultmodels[subjectimageheight]" size="5"> $alang[pixel]</td>
			</tr>
			<tr id="tr_fielddefault">
				<th>$alang[model_fielddefault]</th>
				<td><textarea name="fielddefault" rows="8" id="fielddefault" cols="37">$resultmodels[fielddefault]</textarea></td>
			</tr>
			<tr id="tr_seokeywords">
				<th>$alang[model_seokeywords]</th>
				<td><textarea name="seokeywords" id="seokeywords" cols="100" rows="3">$resultmodels[seokeywords]</textarea></td>
			</tr>
			<tr id="tr_seodescription">
				<th>$alang[model_seodescription]</th>
				<td><textarea name="seodescription" id="seodescription" cols="100" rows="3">$resultmodels[seodescription]</textarea></td>
			</tr>
EOF;
	if(allowfeed()) {
		$feedchecks = array();
		$feedchecks[1] = ($resultmodels['allowfeed'] & 1) ? 'checked="checked"' : '';
		$feedchecks[2] = ($resultmodels['allowfeed'] & 2) ? 'checked="checked"' : '';

		$output .= <<<EOF
				<tr id="tr_allowfeed">
					<th>$alang[model_allowfeed_about]</th>
					<td>
						<input type="checkbox" name="allowfeed[1]" value="1" $feedchecks[1] /> $alang[model_allowfeed_item]
						<input type="checkbox" name="allowfeed[2]" value="1" $feedchecks[2] /> $alang[model_allowfeed_re]
					</td>
				</tr>
			</table>
		</div>
EOF;
	}

	$output .= <<<EOF
	<div class="colorarea02">
		<h2>$alang[model_select_template]</h2>
		<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
			$resettpl
			<tr>
				<th>$alang[model_select_tpl]</th>
				<td valign="top">$tpllist</td>
				<td style="width: 350px; height: 360px;" valign="top">
					<div align="center">
						$alang[model_select_header_view]<br />
						<img id="tplimage" src="$s_url/images/base/nopic.gif" style="width: 350px;">
					</div>
				</td>
			</tr>
			<tr>
				<th>$alang[model_systemtpl_1]</th>
				<td colspan="3"><input name="systemtpl" type="checkbox" id="systemtpl" value="1" $resultmodels[systemtpl] />$alang[model_systemtpl_2]</td>
			</tr>
			</tbody>
		</table>
	</div>
	<div class="buttons">
		<input type="submit" name="thevaluesubmit" value="$alang[common_submit]" class="submit">
		<input type="reset" name="thevaluereset" value="$alang[common_reset]">
	</div>
	</form>
EOF;
	
	echo $output;
	
} elseif ($_GET['op'] == 'field') {	//管理字段_列表页

	include_once(S_ROOT.'./include/model_field.inc.php');
	
	$systemfieldlist = $simplefieldlist = $userfieldlist = '';
	$_GET['mid'] = !empty($_GET['mid']) && intval($_GET['mid']) > 0 ? intval($_GET['mid']) : 0;
	
	$cacheinfo = getmodelinfoall('mid', $_GET['mid']);
	$resultmodels = $cacheinfo['models'];
	if($_GET['mid'] > 0) {
		if(!empty($systemfieldarr)) {
			foreach ($systemfieldarr as $value) {
				$systemfieldlist .= fieldlist($value, 'systemfield');
			}
		}
		if(!empty($simplefieldarr)) {
			foreach ($simplefieldarr as $value) {
				$simplefieldlist .= fieldlist($value, 'simplefield');
			}
		}
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelcolumns').' WHERE mid = \''.$_GET['mid'].'\' ORDER BY displayorder, id');
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$userfieldlist .= fieldlist($value, 'userfield');
		}
	}
	
	$output = <<<EOF
	<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td><h1>$resultmodels[modelname] ($resultmodels[modelalias]) $alang[field_management]</h1></td>
			<td class="actions">
				<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
					<tr>
						<td class="active"><a href="$cpurl?action=models&op=field&mid=$_GET[mid]">$alang[view_field]</a></td>
						<td><a href="$cpurl?action=models&op=addfield&mid=$_GET[mid]" class="add">$alang[new_create_field]</a></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	
	<div class="colorarea01">
		<table cellspacing="2" cellpadding="2" class="helptable">
			<tr>
				<td>
					<ul>
						$alang[model_system_template]
						<li>$alang[aslo_through]<a href="$cpurl?action=models&op=addfield&mid=$_GET[mid]">"$alang[new_create_field]"</a>$alang[add_myself_field]</li>
					</ul>
				</td>
			</tr>
		</table>
	</div>

	<form method="post" name="listform" id="theform" action="$cpurl?action=models">
	<input type="hidden" name="formhash" value="$formhash">
	<table cellspacing="0" cellpadding="0" width="100%" class="listtable">
		<tr>
			<th>$alang[prefield_title_field]</th>
			<th>$alang[field_note]</th>
			<th>$alang[customfield_customfield_title2]</th>
			<th width="30">$alang[length]</th>
			<th>$alang[initialization_value]</th>
			<th width="60">$alang[table_field_type]</th>
			<th width="30">$alang[block_announcement_order_displayorder]</th>
			<th width="20">$alang[model_show]</th>
			<th width="20">$alang[table_shows]</th>
			<th width="20">$alang[check_search]</th>
			<th width="20">$alang[contribution]</th>
			<th width="20">$alang[fixed_length]</th>
			<th width="20">BBCODE</th>
			<th width="20">html</th>
			<th width="20">$alang[userprofile_bitian]</th>
			<th width="20">$alang[block_attachment_filetype_file]</th>
			<th width="20">$alang[block_type_spaceimage]</th>
			<th width="20">flash</th>
			<th>$alang[ad_title_operate]</th>
		</tr>
		<tr class="darkrow">
			<th colspan="18">$alang[system_field]	</th>
			<th><a href="javascript:;" onclick="fieldnav(this, 'systemfield');">$alang[start]</a></th>
		</tr>
		$systemfieldlist
		<tr class="darkrow">
			<th colspan="18">$alang[fast_create]	</th>
			<th><a href="javascript:;" onclick="fieldnav(this, 'simplefield');">$alang[start]</a></th>
		</tr>
		$simplefieldlist
		<tr class="darkrow">
			<th colspan="18">$alang[user_defined_fields]	</th>
			<th><a href="javascript:;" onclick="fieldnav(this, 'userfield');">$alang[away]</a></th>
		</tr>
		$userfieldlist
	</table>
	<div class="buttons">
		<input type="submit" name="fieldlistsubmit" value="$alang[common_submit]" class="submit">
		<input type="reset" name="fieldlistreset" value="$alang[common_reset]">
		<input type="hidden" name="mid" value="$_GET[mid]">
	</div>
	</form>
	<script language="javascript">
	<!--
		function getElementsByName_iefix(tag, name) { 
			var elem = document.getElementsByTagName(tag); 
			var arr = new Array(); 
			for(i = 0, iarr = 0; i < elem.length; i++) { 
				att = elem[i].getAttribute("name"); 
				if(att == name) { 
					arr[iarr] = elem[i]; 
					iarr++; 
				} 
			} 
			return arr; 
		}
		
		function fieldnav(obj, fieldtype) {
			objfieldtype = getElementsByName_iefix('tr', fieldtype);
			for(i = 0; i < objfieldtype.length; i++) {
				if(objfieldtype[i].style.display == 'none') {
					objfieldtype[i].style.display = '';
					obj.innerHTML = '$alang[away]';
				} else {
					objfieldtype[i].style.display = 'none';
					obj.innerHTML = '$alang[start]';
				}
			}
		}
	//-->
	</script>

EOF;
	echo $output;	
	
} elseif ($_GET['op'] == 'addfield' || $_GET['op'] == 'editfield' || $_GET['op'] == 'copyfield' || $_GET['op'] == 'simplefield') {	//字段编辑
	
	include_once(S_ROOT.'./include/model_field.inc.php');
	$resultfield = array('id' => '', 'upid' => '', 'mid' => '', 'fieldname' => '', 'fieldcomment' => '', 'fieldtype' => '', 'fieldlength' => '', 
						'fielddefault' => '', 'formtype' => '', 'fielddata' => '', 'displayorder' => '', 'allowindex' => '', 'allowshow' => 'checked="checked"', 
						'allowlist' => '', 'allowsearch' => '', 'allowpost' => '', 'isfixed' => '', 'isbbcode' => '', 'ishtml' => '',
						'isrequired' => '', 'isfile' => '', 'isimage' => '', 'isflash' => '');
	$formtype = array('text' => '', 'select' => '', 'linkage' => '', 'radio' => '', 'checkbox' => '', 
						'textarea' => '', 'img' => '', 'flash' => '', 'file' => '');
	//编辑时
	$_GET['id'] = !empty($_GET['id']) ? intval($_GET['id']) : 0;

	if($_GET['id'] > 0) {
		if($_GET['op'] == 'editfield' || $_GET['op'] == 'copyfield') {
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelcolumns').' WHERE id = \''.$_GET['id'].'\'');
			$resultfield = $_SGLOBAL['db']->fetch_array($query);
			if(empty($resultfield['id'])) {
				showmessage('field_not_exists');
			} else {
				$formtype[$resultfield['formtype']] = 'selected="selected"';
				$resultfield['allowindex'] = !empty($resultfield['allowindex']) ? 'checked="checked"' : '';
				$resultfield['allowshow'] = !empty($resultfield['allowshow']) ? 'checked="checked"' : '';
				$resultfield['allowlist'] = !empty($resultfield['allowlist']) ? 'checked="checked"' : '';
				$resultfield['allowsearch'] = !empty($resultfield['allowsearch']) ? 'checked="checked"' : '';
				$resultfield['allowpost'] = !empty($resultfield['allowpost']) ? 'checked="checked"' : '';
				$resultfield['isfixed'] = !empty($resultfield['isfixed']) ? 'checked="checked"' : '';
				$resultfield['ishtml'] = !empty($resultfield['ishtml']) ? 'checked="checked"' : '';
				$resultfield['isbbcode'] = !empty($resultfield['isbbcode']) ? 'checked="checked"' : '';
				$resultfield['isrequired'] = !empty($resultfield['isrequired']) ? 'checked="checked"' : '';
			}
			
		} elseif($_GET['op'] == 'simplefield') {
			if(empty($simplefieldarr[$_GET['id']])) {
				showmessage('field_not_exists');
			} else {
				$resultfield = $simplefieldarr[$_GET['id']];
				$resultfield['id'] = $resultfield['mid'] = $resultfield['displayorder'] = '';
				$formtype[$resultfield['formtype']] = 'selected="selected"';
				$resultfield['allowindex'] = !empty($resultfield['allowindex']) ? 'checked="checked"' : '';
				$resultfield['allowshow'] = !empty($resultfield['allowshow']) ? 'checked="checked"' : '';
				$resultfield['allowlist'] = !empty($resultfield['allowlist']) ? 'checked="checked"' : '';
				$resultfield['allowsearch'] = !empty($resultfield['allowsearch']) ? 'checked="checked"' : '';
				$resultfield['allowpost'] = !empty($resultfield['allowpost']) ? 'checked="checked"' : '';
				$resultfield['isfixed'] = !empty($resultfield['isfixed']) ? 'checked="checked"' : '';
				$resultfield['ishtml'] = !empty($resultfield['ishtml']) ? 'checked="checked"' : '';
				$resultfield['isbbcode'] = !empty($resultfield['isbbcode']) ? 'checked="checked"' : '';
				$resultfield['isrequired'] = !empty($resultfield['isrequired']) ? 'checked="checked"' : '';
			}
		}
	}
	
	if($_GET['op'] != 'editfield') {
		$resultfield['id'] = '';
		if($_GET['op'] != 'addfield') {
			$resultfield['fieldname'] .= '_'.$_SGLOBAL['timestamp'];
		}
	}

	//字段管理_新建字段
	$_GET['mid'] = !empty($_GET['mid']) ? intval($_GET['mid']) : 0;
	$cacheinfo = getmodelinfoall('mid', $_GET['mid']);
	if(empty($cacheinfo['models'])) {
		showmessage('visit_the_channel_does_not_exist');
	}
	$resultmodels = $cacheinfo['models'];
	$columnsinfoarr = $cacheinfo['columns'];
	$upidoption = '<option value=""></option>'."\n";

	if(!empty($columnsinfoarr)) {
		foreach($columnsinfoarr as $tmpvalue) {
			if($tmpvalue['formtype'] == 'linkage') {
				$upidoption .= '<option value="'.$tmpvalue['id'].'" '.($tmpvalue['id'] == $resultfield['upid'] ? 'selected="selected"' : '').'>'.$tmpvalue['fieldname'].' ('.$tmpvalue['fieldcomment'].')'.'</option>'."\n";
			}
		}
	}
	
	$output = <<<EOF
	<style type="text/css">
	<!--
		.help { padding-left: 5px; }
		.w200 { width: 200px; }
	-->
	</style>
	
	<script language="javascript" type="text/javascript" src="$s_url/include/js/model.js"></script>
	<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td><h1>$resultmodels[modelname] ($resultmodels[modelalias]) $alang[new_create_field]</h1></td>
			<td class="actions">
				<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
					<tr>
						<td><a href="{$cpurl}?action=models&op=field&mid=$_GET[mid]">$alang[view_field]</a></td>
						<td class="active"><a href="{$cpurl}?action=models&op=addfield&mid=$_GET[mid]" class="add">$alang[new_create_field]</a></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<form method="post" name="thevalueform" id="theform" action="$cpurl?action=models&op=addfield&mid=$_GET[mid]" target="phpframe">
	<input type="hidden" name="formhash" value="$formhash">
	<a name="base"></a>
	<div class="colorarea02">
		<h2>$alang[add_new_field]</h2>
		<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
			<tr id="tr_sitename">
				<th>$alang[model_name]</th>
				<td>$resultmodels[modelname] ($resultmodels[modelalias])
					<input name="mid" type="hidden" id="mid" value="$_GET[mid]" />
					<input name="id" type="hidden" id="id" value="$resultfield[id]" /></td>
			</tr>
	
			<tr id="tr_closesite">
				<th>$alang[field_name_about]</th>
				<td><input name="fieldname" type="text" id="fieldname" value="$resultfield[fieldname]" class="w200" />
					$alang[for_example]:author</td>
			</tr>

			<tr id="tr_closesite">
				<th>$alang[field_note_about]</th>
				<td><input name="fieldcomment" type="text" id="fieldcomment" value="$resultfield[fieldcomment]" class="w200" />
					$alang[for_example_author]</td>
			</tr>
			
			<tr id="tr_closesite">
				<th>$alang[table_field_about]</th>
				<td>
					<select name="formtype" id="formtype" style="width: 207px;" onchange="choosetype('formtype');">
						<option value="text" $formtype[text]>$alang[one_way_text_box]</option>
						<option value="select" $formtype[select]>$alang[the_drop_down_box]</option>
						<option value="linkage" $formtype[linkage]>$alang[the_linkage_drop_down_box]</option>
						<option value="radio" $formtype[radio]>$alang[single_radio]</option>
						<option value="checkbox" $formtype[checkbox]>$alang[checkbox]</option>
						<option value="textarea" $formtype[textarea]>$alang[textarea]</option>
						<option value="timestamp" $formtype[timestamp]>$alang[field_timestamp]</option>
						<option value="img" $formtype[img]>$alang[block_type_spaceimage](img)</option>
						<option value="flash" $formtype[flash]>$alang[flash]</option>
						<option value="file" $formtype[file]>$alang[block_attachment_filetype_file](file)</option>
					</select>
				</td>
			</tr>

			<tr id="tr_upid">
				<th>$alang[field_linkage]</th>
				<td><select name="upid" id="upid">$upidoption</select></td>
			</tr>
			
			<tr id="tr_fielddata">
				<th>$alang[fielddata]</th>
				<td><img src="$s_url/admin/images/zoomin.gif" onmouseover="this.style.cursor='pointer'" onclick="zoomtextarea('fielddata', 1)"> 
					<img src="$s_url/admin/images/zoomout.gif" onmouseover="this.style.cursor='pointer'" onclick="zoomtextarea('fielddata', 0)"><br>
					<textarea name="fielddata" rows="8" id="fielddata" cols="37">$resultfield[fielddata]</textarea></td>
			</tr>
			
			<tr id="tr_fieldtype">
				<th>$alang[field_types_of_data_tables] *</th>
				<td>
					<select name="fieldtype" id="fieldtype" style="width: 207px;" onchange="choosetype('fieldtype');">
						<option value="VARCHAR">$alang[varchar_name]</option>
						<option value="CHAR">$alang[char_name]</option>
						<option value="TEXT">$alang[text_name]</option>
						<option value="MEDIUMTEXT">$alang[mediumtext_name]</option>
						<option value="LONGTEXT">$alang[longtext_name]</option>
						<option value="TINYINT">$alang[tinyint_name]</option>
						<option value="SMALLINT">$alang[smallint_name]</option>
						<option value="MEDIUMINT">$alang[mediumint_name]</option>
						<option value="INT">$alang[int_name]</option>
						<option value="BIGINT">$alang[bigint_name]</option>
						<option value="FLOAT">$alang[float_name]</option>
						<option value="DOUBLE">$alang[double_name]</option>
					</select>&nbsp;&nbsp;
					<span id="dp_length">
					$alang[field_length] <input name="fieldlength" type="text" id="fieldlength" size="2" value="$resultfield[fieldlength]" />
					$alang[for_example]:20</span></td>
			</tr>
			
			<tr id="tr_fielddefault">
				<th>$alang[field_default_value]</th>
				<td><input name="fielddefault" type="text" id="fielddefault" value="$resultfield[fielddefault]" class="w200" />
					$alang[for_example]:123456</td>
			</tr>
			
			<tr id="tr_allowshow">
				<th>$alang[allowshow_about]</th>
				<td><input type="checkbox" name="allowshow" value="1" id="allowshow" $resultfield[allowshow] />
					<label for="allowshow">$alang[allow]</label></td>
			</tr>
			
			<tr id="tr_allowlist">
				<th>$alang[allowlist_about]</th>
				<td><input type="checkbox" name="allowlist" value="1" id="allowlist" $resultfield[allowlist] />
					<label for="allowlist">$alang[allow]</label></td>
			</tr>
			
			<tr id="tr_allowsearch">
				<th>$alang[allowsearch_about]</th>
				<td><input type="checkbox" name="allowsearch" value="1" id="allowsearch" $resultfield[allowsearch] />
					<label for="allowsearch">$alang[allow]</label></td>
			</tr>
			
			<tr id="tr_closesite">
				<th>$alang[isrequired]</th>
				<td><input type="checkbox" name="isrequired" value="1" id="isrequired" $resultfield[isrequired] />
					<label for="isrequired">$alang[userprofile_bitian]</label></td>
			</tr>
			
			<tr id="tr_closesite">
				<th>$alang[allow_post_about_1]</th>
				<td><input type="checkbox" name="allowpost" value="1" id="allowpost" $resultfield[allowpost] />
					<label for="allowpost">$alang[allow]</label></td>
			</tr>
			
			<tr id="tr_isfixed">
				<th>$alang[isfixed]</th>
				<td><input type="checkbox" name="isfixed" value="1" id="isfixed" $resultfield[isfixed] />
					<label for="isfixed">$alang[prefield_isdefault_1]</label></td>
			</tr>
			
			<tr id="tr_isbbcode">
				<th>$alang[isbbcode]</th>
				<td><input type="checkbox" name="isbbcode" value="1" id="isbbcode" $resultfield[isbbcode] />
					<label for="isbbcode">$alang[block_isbbcode]</label></td>
			</tr>
			
			<tr id="tr_ishtml">
				<th>$alang[ishtml_about]</th>
				<td><input type="checkbox" name="ishtml" value="1" id="ishtml" $resultfield[ishtml] />
					<label for="ishtml">$alang[ishtml]</label></td>
			</tr>
		</table>
	</div>

	<div class="buttons">
		<input type="submit" name="theaddfieldform" value="$alang[common_submit]" class="submit">
		<input type="reset" name="thevaluereset" value="$alang[common_reset]">
	</div>
	
	<script language="javascript">
	<!--
		function choosetype(str) {
			var objformtype = document.getElementById('formtype');

			if(str == null || str == 'formtype') {
				removenodebyclass('fieldtype', 'option');
			}
			var varr = Array('VARCHAR','CHAR','TEXT',  'MEDIUMTEXT','LONGTEXT','TINYINT',  'SMALLINT','MEDIUMINT','INT',  'BIGINT','FLOAT','DOUBLE');
			var tarr = Array('$alang[varchar_name]','$alang[char_name]','$alang[text_name]','$alang[mediumtext_name]','$alang[longtext_name]','$alang[tinyint_name]',  '$alang[smallint_name]','$alang[mediumint_name]','$alang[int_name]','$alang[bigint_name]','$alang[float_name]','$alang[double_name]');

			strdisplay = 'dp_length,tr_fielddefault,tr_upid,tr_fielddata,tr_allowlist,tr_allowsearch,tr_isfixed,tr_ishtml,tr_isbbcode,tr_fieldtype';
			trdisplay(strdisplay, '');

			strdisplay = '';
			optvaluearr = varr;
			opttextarr = tarr;

			switch(objformtype.value) {
				case 'text':
					strdisplay = 'tr_fielddata,tr_upid';
					optvaluearr = Array(varr[0], varr[1], varr[2], varr[5], varr[6], varr[7], varr[8], varr[9], varr[10], varr[11]);
					opttextarr = Array(tarr[0], tarr[1], tarr[2], tarr[5], tarr[6], tarr[7], tarr[8], tarr[9], tarr[10], tarr[11]);
					break;
				case 'select':
				case 'radio':
				case 'checkbox':
					strdisplay = 'tr_ishtml,tr_upid,tr_isbbcode';
					optvaluearr = Array(varr[0], varr[1], varr[2], varr[5], varr[6], varr[7], varr[8], varr[9], varr[10], varr[11]);
					opttextarr = Array(tarr[0], tarr[1], tarr[2], tarr[5], tarr[6], tarr[7], tarr[8], tarr[9], tarr[10], tarr[11]);
					break;
				case 'linkage':
					strdisplay = 'tr_ishtml,tr_isbbcode';
					optvaluearr = Array(varr[0], varr[1], varr[2], varr[5], varr[6], varr[7], varr[8], varr[9], varr[10], varr[11]);
					opttextarr = Array(tarr[0], tarr[1], tarr[2], tarr[5], tarr[6], tarr[7], tarr[8], tarr[9], tarr[10], tarr[11]);
					break;
				case 'timestamp':
					strdisplay = 'tr_fielddata,tr_upid,dp_length,tr_fielddefault,tr_ishtml,tr_fieldtype,tr_isbbcode';
					optvaluearr = Array();
					opttextarr = Array();
					break;
				case 'textarea':
					strdisplay = 'tr_fielddata,tr_upid';
					optvaluearr = Array(varr[0], varr[1], varr[2], varr[3], varr[4]);
					opttextarr = Array(tarr[0], tarr[1], tarr[2], tarr[3], tarr[4]);
					break;
				case 'img':
				case 'flash':
				case 'file':
					strdisplay = 'tr_fielddata,tr_upid,dp_length,tr_fielddefault,tr_allowlist,tr_allowsearch,tr_isfixed,tr_ishtml,tr_fieldtype,tr_isbbcode';
					optvaluearr = Array();
					opttextarr = Array();
					break;
				default:
					break;
			}

			trdisplay(strdisplay, 'none');
			if(str == 'formtype' || str == null) {
				addselectnodebyclass('fieldtype', optvaluearr, opttextarr, '$resultfield[fieldtype]');
			}

			var objfieldtype = document.getElementById('fieldtype');
			switch(objfieldtype.value) {
				case 'VARCHAR':
					strdisplay = 'tr_isfixed';
					break;
				case 'TEXT':
				case 'MEDIUMTEXT':
				case 'LONGTEXT':
					strdisplay = 'dp_length,tr_isfixed,tr_fielddefault';
					break;
				case 'TINYINT':
				case 'SMALLINT':
				case 'MEDIUMINT':
				case 'INT':
				case 'BIGINT':
					strdisplay = 'tr_ishtml,tr_isbbcode';
					break;
				case 'FLOAT':
				case 'DOUBLE':
					strdisplay = 'dp_length,tr_ishtml,tr_isbbcode';
					break;
				default:
					break;
			}
			trdisplay(strdisplay, 'none');
		}
		
	//-->
	</script>
	
	<script language="javascript">choosetype();</script>
	</form>
EOF;
	echo $output;

} elseif ($_GET['op'] == 'delmodel') {	//删除模型

	//检查模型是否存在
	$_GET['mid'] = postget('mid');
	$_GET['mid'] = !empty($_GET['mid']) ? intval($_GET['mid']) : 0;
	$resultmodels = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('models').' WHERE mid = \''.$_GET['mid'].'\'');
	$resultmodels = $_SGLOBAL['db']->fetch_array($query);
	if(empty($resultmodels)) {
		showmessage('visit_the_channel_does_not_exist');
	}

	$dbtable = tname($resultmodels['modelname']);
	print <<<EOF
	<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td><h1>$resultmodels[modelname]($resultmodels[modelalias])$alang[model_del_confirm]</h1></td>
			<td class="actions">
				<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
					<tr>
						<td><a href="{$cpurl}?action=models">$alang[browser_model]</a></td>
						<td class="active"><a href="{$cpurl}?action=models&op=delmodel&mid=$_GET[mid]">$alang[model_del_confirm]</a></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	
	<div class="colorarea01">
		<table cellspacing="2" cellpadding="2" class="helptable">
			<tr><td>
				<ul><li><span style="color: #F00">$alang[model_del_confirm_about]</span></li></ul>
			</td></tr>
		</table>
	</div>
	
	<div class="colorarea01">
		<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
			<tr><td>$alang[will_del] <strong>{$dbtable}items</strong>($resultmodels[modelalias]$alang[information_index_table]).</td></tr>
			<tr><td>$alang[will_del] <strong>{$dbtable}message</strong>($resultmodels[modelalias]$alang[information_table]).</td></tr>
			<tr><td>$alang[will_del] <strong>model/data/{$resultmodels['modelname']}/</strong>$alang[model_dir_file]</td></tr>
		</table>
	</div>

	<form method="post" name="thevalueform" id="theform" action="$cpurl?action=models">
	<input type="hidden" name="formhash" value="$formhash">
	<div class="buttons">
		<input id="delmodelconfirm" type="submit" name="delmodelconfirm" value="$alang[delete_model]"> 
		<input name="mid" type="hidden" id="mid" value="$resultmodels[mid]" />
	</div>
	</form>
EOF;

} elseif ($_GET['op'] == 'delfield') {//删除字段

	//检查模型是否存在
	$_GET['mid'] = postget('mid');
	$_GET['id'] = postget('id');
	$_GET['mid'] = !empty($_GET['mid']) ? intval($_GET['mid']) : 0;
	$_GET['id'] = !empty($_GET['id']) ? intval($_GET['id']) : 0;
	$resultmodels = array();
	$resultmodels = getmodelinfo($_GET['mid']);
	$resultfield = array();
	if($_GET['mid'] > 0) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelcolumns').' WHERE id = \''.$_GET['id'].'\'');
		$resultfield = $_SGLOBAL['db']->fetch_array($query);
		if(empty($resultfield)) {
			showmessage('field_not_exists');
		}
	} else {
		showmessage('field_not_exists');
	}

	print <<<EOF
	<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td><h1>$alang[field_del_confirm]</h1></td>
			<td class="actions">
				<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
					<tr>
						<td><a href="{$cpurl}?action=models&op=field&mid=$_GET[mid]&id=$_GET[id]">$alang[view_field]</a></td>
						<td class="active"><a href="{$cpurl}?action=models&op=delfieldl&mid=$_GET[mid]&id=$_GET[id]">$alang[field_del_confirm]</a></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	
	<div class="colorarea01">
		<table cellspacing="2" cellpadding="2" class="helptable">
			<tr><td>
				<ul><li><span style="color: #F00">$alang[field_del_confirm_about]</span></li></ul>
			</td></tr>
		</table>
	</div>


	<form method="post" name="thevalueform" id="theform" action="$cpurl?action=models">
	<input type="hidden" name="formhash" value="$formhash">
	<div class="buttons">
		<input id="delfieldconfirm" type="submit" name="delfieldconfirm" value="$alang[del_field]"> 
		<input name="mid" type="hidden" id="mid" value="$resultmodels[mid]" />
		<input name="id" type="hidden" id="id" value="$resultfield[id]" />
	</div>
	</form>
EOF;

} elseif($_GET['op'] == 'export') {	//导出模型

	//检验数据
	$_GET['mid'] = !empty($_GET['mid']) ? intval($_GET['mid']) : 0;
	$resultmodels = array();
	$cacheinfo = getmodelinfoall('mid', $_GET['mid']);
	if(empty($cacheinfo['models'])) {
		showmessage('visit_the_channel_does_not_exist');
	}
	$resultmodels = $cacheinfo['models'];

	//初始化目录导出目录
	$datadir = S_ROOT.'./data/model';
	$backupdir = 'model_'.$resultmodels['modelname'].'_'.random(6);
	$backupfile = $datadir.'/'.$backupdir.'/'.$backupdir.'.zip';
	$modelsqlfile = $datadir.'/'.$backupdir.'/model.cache.php';
	$tablesqlfile = $datadir.'/'.$backupdir.'/table.sql';
	if(!is_dir($datadir.'/'.$backupdir)) {
		@mkdir($datadir.'/'.$backupdir, 0777);
	}
	
	//确认模型文件是否存在，并复制模型文件
	$modelfilearr = array();
	$modeldir = S_ROOT.'./model/data/'.$resultmodels['modelname'].'/';
	if(empty($resultmodels['tpl'])) {
		$tplarr = sreaddir($modeldir);
		if(!empty($tplarr)) {
			foreach($tplarr as $value) {
				if(is_file($modeldir.$value)) {
					$modelfilearr[] = $modeldir.$value;
				}
			}
		} else {
			showmessage($alang['model_dir_1'].$modeldir.$alang['model_dir_2']);
		}
	} else {
		$modelfilearr[] = $modeldir.'validate.js';
		$modeldir = S_ROOT.'./mthemes/'.$resultmodels['tpl'].'/';
		$tplarr = sreaddir($modeldir);
		if(!empty($tplarr)) {
			foreach($tplarr as $value) {
				if(is_file($modeldir.$value)) {
					$modelfilearr[] = $modeldir.$value;
				}
			}
		} else {
			showmessage($alang['model_dir_1'].$modeldir.$alang['model_dir_2']);
		}
	}
	$modeldir .= 'images/';
	$tplarr = sreaddir($modeldir);
	if(!empty($tplarr)) {
		foreach($tplarr as $value) {
			if(is_file($modeldir.$value)) {
				$modelfilearr[] = $modeldir.$value;
			}
		}
	} else {
		showmessage($alang['model_dir_1'].$modeldir.$alang['model_dir_2']);
	}

	$modeldbarr = array('items', 'message');	//模型表
	
	$copyerrorarr = $zipfilearr = array();
	$zipfilestr = '';
	foreach($modelfilearr as $tmpvalue) {
		$to = substr(strrchr($tmpvalue, '/'), 1);
		if(is_file($tmpvalue)) {
		   if(!@copy($tmpvalue, $datadir.'/'.$backupdir.'/'.$to)) {
				$copyerrorarr = 'write_error';
				break;
			} else {
				$zipfilearr[] = $datadir.'/'.$backupdir.'/'.$to;
			}
		}
	}
	if(!empty($copyerrorarr)) {
		if(!is_array($copyerrorarr)) {
			showmessage('file_write_error');
		} else {
			deltree($datadir.'/'.$backupdir.'/');
			showmessage($alang['list_file_not_exists'].implode('<br />', $copyerrorarr));
		}
	}
	
	//整理数据库
	$modelsql = '';
	unset($resultmodels['mid']);
	$resultcolumns = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelcolumns').' WHERE mid = \''.$_GET['mid'].'\' ORDER BY displayorder, id');
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		unset($value['id']);
		unset($value['mid']);
		if(!empty($value['upid']) && $value['formtype'] == 'linkage') {
			$value['upid'] = $cacheinfo['columnids'][$value['upid']];
		}
		$resultcolumns[] = $value;
	}
	$resultmodels['tpl'] = '';
	$tarr = array(
		'info'	=>	array('version'=>S_VER, 'charset'=>$_SCONFIG['charset']),
		'models'	=>	$resultmodels,
		'columns'	=>	$resultcolumns,
		'categories'	=>	$cacheinfo['categoryarr']
	);
	$modelsql = "/** SupeSite Dump\r\n".
	" * Version: SupeSite ".S_VER."\r\n".
	" * Charset: ".$_SCONFIG['charset']."\r\n".
	" * Time: $time\r\n".
	" * From: $_SCONFIG[sitename] (".S_URL.")\r\n".
	" * \r\n".
	" * SupeSite: http://www.supesite.com\r\n".
	" * Please visit our website for latest news about SupeSite\r\n".
	" * --------------------------------------------------------*/\r\n\r\n\r\n";
	$modelsql .= '$cacheinfo = '.arrayeval($tarr).';';
	if(!writefile($modelsqlfile, $modelsql, 'php')) {
		fclose($fp);
		deltree($datadir.'/'.$backupdir.'/');
		showmessage('file_write_error');
	} else {
		fclose($fp);
		$zipfilearr[] = $modelsqlfile;
	}
	
	$dberrorarr = array();
	$createtable = $tabledump = '';
	$_SGLOBAL['db']->query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');//无报错执行关闭我的创建表和列时不加引号
	
	foreach($modeldbarr as $tmpvalue) {
		$createtable = $_SGLOBAL['db']->query('SHOW CREATE TABLE '.tname($resultmodels['modelname'].$tmpvalue), 'SILENT');
		if(!$_SGLOBAL['db']->errno()) {
			$tabledump .= 'DROP TABLE IF EXISTS '.tname($resultmodels['modelname'].$tmpvalue).";\n";
			$create = $_SGLOBAL['db']->fetch_row($createtable);
			if($_SGLOBAL['db']->version() >= '4.1') {
				$create[1] = preg_replace('/ENGINE=([^\s]+) [a-zA-Z].+/', "TYPE=\\1", $create[1]);
			}
			$tabledump .= $create[1].";\n\n";
		} else {
			$dberrorarr[] = tname($resultmodels['modelname'].$tmpvalue);
		}
	}
	
	$fp = fopen($tablesqlfile, 'wb');
	@flock($fp, 2);
	if(!fwrite($fp, $tabledump)) {
		fclose($fp);
		deltree($datadir.'/'.$backupdir.'/');
		showmessage('file_write_error');
	} else {
		fclose($fp);
		$zipfilearr[] = $tablesqlfile;
	}
	
	if(!empty($dberrorarr)) {
		deltree($datadir.'/'.$backupdir.'/');
		showmessage($alang['list_table_not_exists'].implode('<br />', $dberrorarr));
	}
	
	//打包
	require_once S_ROOT .'./include/zip.lib.php';
	$zipfile = new Zip($backupfile);
	$zipfilestr = implode(',', $zipfilearr);
	$zipfile->create($zipfilestr, PCLZIP_OPT_REMOVE_PATH, $datadir.'/'.$backupdir);
	
	if(!empty($zipfilearr)) {
		foreach($zipfilearr as $tmpvalue) {
			@unlink($tmpvalue);
		}
	}
	showmessage('model_export_suc', CPURL.'?action=models&op=import');

} elseif($_GET['op'] == 'import') {
	
	if(!ckfounder($_SGLOBAL['supe_uid'])) {
		showmessage('no_authority_management_operation');
	}
	$backupdir = S_ROOT.'/data/model';
	$exportlog = array();
	$_GET['datafile'] = !empty($_GET['datafile']) ? trim($_GET['datafile']) : '';
	$_GET['do'] = !empty($_GET['do']) ? trim($_GET['do']) : '';
	
	if(empty($_GET['do']) || $_GET['do'] != 'start') {
		$dir = dir($backupdir);
		while(FALSE !== ($entry = $dir->read())){
			$filename = $backupdir.'/'.$entry.'/'.$entry.'.zip';
			if(is_file($filename)) {
				$exportlog[] = array(
						'filename'	=>	$entry,
						'size'	=>	filesize($filename),
						'dateline'	=>	filemtime($filename)
				);
			}
		}
		$dir->close();
		print <<<EOF
			<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td><h1>$alang[model_backup_manager]</h1></td>
					<td class="actions">
						<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
							<tr>
								<td><a href="{$cpurl}?action=models">$alang[browser_model]</a></td>
								<td><a href="{$cpurl}?action=models&op=add" class="add">$alang[new_model]</a></td>
								<td class="active"><a href="{$cpurl}?action=models&op=import">$alang[view_backup]</a></td>
								<td><a href="{$cpurl}?action=models&op=import&do=start" class="add">$alang[import_model]</a></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			
			<form method="post" action="$theurl" name="thevalueform" enctype="multipart/form-data">
			<input type="hidden" name="formhash" value="$formhash">
			<h2>$alang[model_list]</h2>
			<table cellspacing="0" cellpadding="0" width="100%"  class="listtable">
				<tr>
				<th width="5%">$alang[poll_delete]</th>
				<th width="">$alang[database_export_filename]</th>
				<th width="20%">$alang[database_export_dateline]</th>
				<th width="10%">$alang[database_export_filesize]</th>
				<th width="10%">$alang[effect_title_op]</th>
				</tr>
EOF;
		$exportinfo = '';
		foreach($exportlog as $tmpvalue) {
			$tmpvalue['dateline'] = sgmdate($tmpvalue['dateline']);
			$tmpvalue['size'] = formatsize($tmpvalue['size']);
			$exportinfo .= '<tr><td align="center"><input type="checkbox" name="delexport[]" value="'.$tmpvalue['filename'].'"></td>
								<td><a href="'.S_URL.'/data/model/'.$tmpvalue['filename'].'/'.$tmpvalue['filename'].'.zip'.'">'.$tmpvalue['filename'].'</a></td>
								<td>'.$tmpvalue['dateline'].'</td><td align="center">'.$tmpvalue['size'].'</td>
								<td align="center"><a href="'.$theurl.'&op=import&do=start&datafile='.$tmpvalue['filename'].'">'.$alang['database_import_import'].'</a></td>';
		}
		print <<<EOF
		$exportinfo
		<table cellspacing="0" cellpadding="0" width="100%"  class="btmtable">
		<tr><th><input type="checkbox" name="chkall" onclick="checkall(this.form, 'delexport')">$alang[space_select_all]<input name="worddelete" type="radio" value="1" checked /> $alang[common_delete]</th></tr>
		</table>
		</table>
		<div class="buttons">
			<input type="submit" name="delimportsubmit" value="$alang[common_submit]" class="submit">
		</div>
		</form>
EOF;
	} else {
		$upload = '';
		if(empty($_GET['datafile'])) {
			$upload = '
					<tr id="tr_closesite">
						<th>'.$alang['model_compression_package'].' *</th>
						<td><input name="zipfile" type="file" id="zipfile" size="30" value="" /></td>
					</tr>';
		} else {
			$upload = '
					<tr id="tr_closesite">
						<th>'.$alang['model_compression_package'].' *</th>
						<td>'.$_GET['datafile'].'.zip</td>
					</tr>';
		}
		print <<<EOF
			<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td><h1>$alang[import_model]</h1></td>
					<td class="actions">
						<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
							<tr>
								<td><a href="{$cpurl}?action=models">$alang[browser_model]</a></td>
								<td><a href="{$cpurl}?action=models&op=add" class="add">$alang[new_model]</a></td>
								<td><a href="{$cpurl}?action=models&op=import">$alang[view_backup]</a></td>
								<td class="active"><a href="{$cpurl}?action=models&op=import&do=start" class="add">$alang[import_model]</a></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			
			<form method="post" name="thevalueform" id="theform" action="$cpurl?action=models" enctype="multipart/form-data">
			<input type="hidden" name="formhash" value="$formhash">
			<a name="base"></a>
			<div class="colorarea02">
				<h2>$alang[import_model]</h2>
				<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
					<tr id="tr_sitename">
						<th>$alang[model_name_about]</th>
						<td><input name="modelname" type="text" id="modelname" size="30" value="" />
							<input name="datafile" type="hidden" id="datafile" value="$_GET[datafile]" />
							$alang[for_example]:shop</td>
					</tr>
					<tr id="tr_closesite">
						<th>$alang[model_other_name_about]</th>
						<td><input name="modelalias" type="text" id="modelalias" size="30" value="" />
							$alang[model_for_example]</td>
					</tr>
					$upload
				</table>
			</div>
		
			<div class="buttons">
				<input type="submit" name="theimportsubmit" value="$alang[common_submit]" class="submit">
				<input type="reset" name="thevaluereset" value="$alang[common_reset]">
			</div>
			</form>
EOF;
	}

}

/**
 * 生成模板名字函数
 * @param string $name
 * @return 整理后的模板名字
 */
function tplname($modelname, $name) {
	return S_ROOT.'./model/data/'.$modelname.'/'.$name.'.html.php';
}

/**
 * 生成模版列表
 * @param array $arraytpl
 * @return string
 */
function tpllist($edit=0) {
	global $alang;
	$themearr = array();
	$dirarr = sreaddir(S_ROOT.'./mthemes');
	$checked = empty($edit) ? 'checked="checked"' : '';
	$i = 0;
	foreach ($dirarr as $dirname) {
		$themefile = S_ROOT.'./mthemes/'.$dirname.'/theme.php';
		if(file_exists($themefile)) {
			include_once($themefile);
			$themes['dirname'] = $dirname;
			$themes['thumb'] = S_URL.'/mthemes/'.$dirname.'/'.$themes['thumb'];
			$themes['preview'] = S_URL.'/mthemes/'.$dirname.'/'.$themes['preview'];
			$themearr[] = $themes;
		}
	}
	$themestr = '<table summary="" cellpadding="0" cellspacing="0" border="0" width="100%"><tr>';
	foreach ($themearr as $key => $value) {
		$themestr .= '<td align="center">
			<p class="avatar">
			<a href="'.$value['preview'].'" target="_blank"><img src="'.$value['thumb'].'" border="0" width="110" height="120" onmousemove="showtpl(\''.$value['preview'].'\');"/></a>
			<br>'.$value['name'].'<br>
			<input name="tpl" type="radio" id="tpl" value="'.$value['dirname'].'" '.$checked.'/>'.$alang['identify_options'].' 
			<a href="'.$value['preview'].'" target="_blank" onmousemove="showtpl(\''.$value['preview'].'\');">'.$alang['tpl_title_preview'].'</a>
			</p>
			</td>';
		if($key%3 == 2) $themestr .= '</tr><tr>';
		if($i == 0) $checked = '';
	}
	$themestr .= '</tr></table>';
	return $themestr;
}

//获取表的创建sql文
function getcreatsql($sql, $tablename) {
	global $_SC;

	preg_match("/(CREATE TABLE `?$tablename`? .*?)\s+[TYPE|ENGINE]+\=/is", $sql, $maths);
	$type = mysql_get_server_info() > '4.1' ? " ENGINE=MYISAM".(empty($_SC['dbcharset'])?"":" DEFAULT CHARSET=$_SC[dbcharset]" ): " TYPE=MYISAM";
	return $maths[1].$type;
}

//整理字段
function fieldlist($fieldarr, $type) {
	global $alang;
	$fieldarr['allowindex'] = $fieldarr['allowindex'] == 1 ? $alang['y'] : '';
	$fieldarr['allowshow'] = $fieldarr['allowshow'] == 1 ? $alang['y'] : '';
	$fieldarr['allowlist'] = $fieldarr['allowlist'] == 1 ? $alang['y'] : '';
	$fieldarr['allowsearch'] = $fieldarr['allowsearch'] == 1 ? $alang['y'] : '';
	$fieldarr['allowpost'] = $fieldarr['allowpost'] == 1 ? $alang['y'] : '';
	$fieldarr['isfixed'] = $fieldarr['isfixed'] == 1 ? $alang['y'] : '';
	$fieldarr['isbbcode'] = $fieldarr['isbbcode'] == 1 ? $alang['y'] : '';
	$fieldarr['ishtml'] = $fieldarr['ishtml'] == 1 ? $alang['y'] : '';
	$fieldarr['isrequired'] = $fieldarr['isrequired'] == 1 ? $alang['y'] : '';
	$fieldarr['isfile'] = !empty($fieldarr['isfile']) ? $alang['y'] : '';
	$fieldarr['isimage'] = !empty($fieldarr['isimage']) ? $alang['y'] : '';
	$fieldarr['isflash'] = !empty($fieldarr['isflash']) ? $alang['y'] : '';
	
	$operate = '';
	$displayorder = '';
	$style = $type == 'userfield' ? '' : 'display: none';
	if($type == 'systemfield') {
		$operate = $alang['system_field'];
		$displayorder = $fieldarr['displayorder'];
	} elseif($type == 'simplefield') {
		$operate = '<a href="?action=models&op=simplefield&mid='.$_GET['mid'].'&id='.$fieldarr['id'].'">'.$alang['field_copy'].'</a>';
		$displayorder = $fieldarr['displayorder'];
	} elseif($type == 'userfield') {
		$operate = <<<EOF
					<a href="?action=models&op=editfield&mid=$_GET[mid]&id=$fieldarr[id]">$alang[ad_adtype_detail]</a>
					<a href="?action=models&op=copyfield&mid=$_GET[mid]&id=$fieldarr[id]">$alang[field_copy]</a>
					<a href="?action=models&op=delfield&mid=$_GET[mid]&id=$fieldarr[id]">$alang[poll_delete]</a>
EOF;
		$displayorder = '<input name="displayorder['.$fieldarr['id'].']" type="text" id="displayorder['.$fieldarr['id'].']" size="2" maxlength="" value="'.$fieldarr['displayorder'].'" />';
	}

	$returnstr = <<<EOF
			<tr name="$type" class="darkrow" style="$style">
				<td>$fieldarr[fieldname]</td>
				<td>$fieldarr[fieldcomment]</td>
				<td>$fieldarr[fieldtype]</td>
				<td>$fieldarr[fieldlength]</td>
				<td>$fieldarr[fielddefault]</td>
				<td>$fieldarr[formtype]</td>
				<td>$displayorder</td>
				<td>$fieldarr[allowshow]</td>
				<td>$fieldarr[allowlist]</td>
				<td>$fieldarr[allowsearch]</td>
				<td>$fieldarr[allowpost]</td>
				<td>$fieldarr[isfixed]</td>
				<td>$fieldarr[isbbcode]</td>
				<td>$fieldarr[ishtml]</td>
				<td>$fieldarr[isrequired]</td>
				<td>$fieldarr[isfile]</td>
				<td>$fieldarr[isimage]</td>
				<td>$fieldarr[isflash]</td>
				<td align="center">
					$operate
				</td>
			</tr>

EOF;
	return $returnstr;
}

function smkdir($dirname, $ismkindex=1) {
	$mkdir = false;
	if(!is_dir($dirname)) {
		if(@mkdir($dirname, 0777)) {
			if($ismkindex) {
				@fclose(@fopen($dirname.'/index.htm', 'w'));
			}
			$mkdir = true;
		}
	} else {
		$mkdir = true;
	}
	return $mkdir;
}

function jsshowmessage($message, $gotourl='') {
	global $_SCONFIG, $alang, $amlang ;
	include_once(S_ROOT.'./language/admincp_message.lang.php');
	obclean();
	if(!empty($amlang[$message])) $message = $amlang[$message];
	if(!empty($gotourl)) {
		$gotourl = 'parent.location.replace(\''.$gotourl.'\');';
	}
	
	print <<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=$_SCONFIG[charset]" />
	<title>Admin CP Message - Powered by SupeSite</title>
	</head>
	<body>
	<script>alert('$message');$gotourl</script>
	</body>
	</html>
END;
	exit;
}

//初始化模版
function inittemplate($todir, $tpl) {
	$fromdir = S_ROOT.'./mthemes/'.$tpl.'/';
	$tplarr = array();
	$tplarr = sreaddir($fromdir);
	if(!empty($tplarr)) {
		foreach($tplarr as $value) {
			if(is_file($fromdir.$value) && ($value != 'theme.php' && $value != 'thumb_preview.jpg' && $value != 'preview.jpg')) {
				@copy($fromdir.$value, $todir.$value);
			}
		}
	}
	$fromdir = S_ROOT.'./mthemes/'.$tpl.'/images/';
	$todir .= 'images/';
	$tplarr = array();
	$tplarr = sreaddir($fromdir);
	if(!empty($tplarr)) {
		foreach($tplarr as $value) {
			if(is_file($fromdir.$value)) {
				@copy($fromdir.$value, $todir.$value);
			}
		}
	}
}

?>