<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_cache.php 13342 2009-09-16 05:43:20Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managecache')) {
	showmessage('no_authority_management_operation');
}

//POST METHOD
if (submitcheck('cachesubmit')) {

	if(empty($_POST['cachekind'])) $_POST['cachekind'] = array('cache', 'tagcache');
	$subs = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
	
	if(in_array('cache', $_POST['cachekind'])) {
		if($_SCONFIG['cachemode'] == 'file') {
			//文件存储模式=>全部删除
			$cachedir = S_ROOT.'./cache/block';
			$dirs = sreaddir($cachedir);
			foreach ($dirs as $value) {
				if(is_dir($cachedir.'/'.$value)) {
					$filearr = sreaddir($cachedir.'/'.$value, 'data');
					foreach ($filearr as $subvalue) {
						@unlink($cachedir.'/'.$value.'/'.$subvalue);
					}
				}
			}
		} else {
			if(empty($_POST['cachetype'])) {
				$_SGLOBAL['db']->query('TRUNCATE TABLE '.tname('cache'));
				//分表
				foreach ($subs as $tbl) {
					$_SGLOBAL['db']->query('TRUNCATE TABLE '.tname('cache_'.$tbl), 'SILENT');
				}
			} else {
				$_SGLOBAL['db']->query('DELETE FROM '.tname('cache').' WHERE cachename IN ('.simplode($_POST['cachetype']).')');
				//分表
				foreach ($subs as $tbl) {
					$_SGLOBAL['db']->query('DELETE FROM '.tname('cache_'.$tbl).' WHERE cachename IN ('.simplode($_POST['cachetype']).')', 'SILENT');
				}
			}
		}
	}

	if(in_array('tagcache', $_POST['cachekind'])) {
		if(empty($_POST['cachetype'])) {
			$_SGLOBAL['db']->query('TRUNCATE TABLE '.tname('tagcache'));
			//分表
			foreach ($subs as $tbl) {
				$_SGLOBAL['db']->query('TRUNCATE TABLE '.tname('tagcache_'.$tbl), 'SILENT');
			}
		} else {
			$_SGLOBAL['db']->query('DELETE FROM '.tname('tagcache').' WHERE cachename IN ('.simplode($_POST['cachetype']).')');
			//分表
			foreach ($subs as $tbl) {
				$_SGLOBAL['db']->query('DELETE FROM '.tname('tagcache_'.$tbl).' WHERE cachename IN ('.simplode($_POST['cachetype']).')', 'SILENT');
			}
		}
	}
	showmessage('spacecache_delete_success', $theurl.'&op=deleteall');
	
} elseif (submitcheck('filesubmit')) {
	
	if(empty($_POST['filekind'])) $_POST['filekind'] = array('js', 'robot', 'tpl', 'ad', 'announcement', 'category', 'config', 'crons', 'group', 'bbs_settings', 'bbs_bbcodes', 'censor', 'bbs_style', 'model');

	if(in_array('js', $_POST['filekind'])) {
		$cachedir = S_ROOT.'./cache/js';
		$filearr = sreaddir($cachedir);
		foreach ($filearr as $file) {
			@unlink($cachedir.'/'.$file);
		}
	}
	if(in_array('robot', $_POST['filekind'])) {
		$cachedir = S_ROOT.'./data/robot';
		$filearr = sreaddir($cachedir);
		foreach ($filearr as $file) {
			@unlink($cachedir.'/'.$file);
		}
	}
	if(in_array('tpl', $_POST['filekind'])) {
		$cachedir = S_ROOT.'./cache/tpl';
		$filearr = sreaddir($cachedir);
		foreach ($filearr as $file) {
			@unlink($cachedir.'/'.$file);
		}
	}
	if(in_array('model', $_POST['filekind'])) {
		$cachedir = S_ROOT.'./cache/model';
		$filearr = sreaddir($cachedir);
		if(!empty($filearr)) {
			foreach ($filearr as $file) {
				@unlink($cachedir.'/'.$file);
			}
		}
		include_once(S_ROOT.'/function/model.func.php');
		$query = $_SGLOBAL['db']->query('SELECT mid, modelname FROM '.tname('models'));
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$state = checkmodel($value['modelname']);
			if($state) {
				writemodelvalidate('mid', $value['mid']);
			}
		}
		
	}
	if(in_array('ad', $_POST['filekind'])) {
		updateadcache();
	}
	if(in_array('announcement', $_POST['filekind'])) {
		updateannouncementcache();
	}
	if(in_array('category', $_POST['filekind'])) {
		updatecategorycache();
	}
	if(in_array('config', $_POST['filekind'])) {
		updatesettingcache();
	}
	if(in_array('crons', $_POST['filekind'])) {
		updatecronscache();
	}
	if(in_array('group', $_POST['filekind'])) {
		updategroupcache();
	}
	if(in_array('bbs_settings', $_POST['filekind'])) {
		updatebbssetting();
	}
	if(in_array('bbs_bbcodes', $_POST['filekind'])) {
		updatebbsbbcode();
	}
	if(in_array('censor', $_POST['filekind'])) {
		updatecensorcache();
	}
	if(in_array('bbs_style', $_POST['filekind'])) {
		updatebbsstyle();
	}
	if(in_array('model', $_POST['filekind'])) {
		
	}
	showmessage('spacecache_delete_success', $theurl.'&op=deleteall');

}

//SHOW HTML
//MENU
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['spacecache_title'].'</h1></td>
		<td class="actions">
		</td>
	</tr>
</table>
';

$cachekind = array(
	'cache' => $alang['spacecache_cachekind_cache'],
	'tagcache' => $alang['spacecache_cachekind_tagcache']
);

foreach ($_SGLOBAL['allblocktype'] as $value) {
	$cachetype[$value] = $alang['block_type_'.$value];
}

$filekind = array(
	'js' => $alang['spacecache_filekind_js'],
	'robot' => $alang['spacecache_filekind_robot'],
	'tpl' => $alang['spacecache_filekind_tpl'],
	'ad' => $alang['spacecache_filekind_ad'],
	'announcement' => $alang['spacecache_filekind_announcement'],
	'category' => $alang['spacecache_filekind_category'],
	'config' => $alang['spacecache_filekind_config'],
	'crons' => $alang['spacecache_filekind_crons'],
	'censor' => $alang['spacecache_filekind_censor'],
	'group' => $alang['spacecache_filekind_group'],
	'model' => $alang['spacecache_model']
);

if(discuz_exists()) {
	
	$filekind['bbs_settings'] = $alang['spacecache_filekind_bbs_settings'];
	$filekind['bbs_bbcodes'] = $alang['spacecache_filekind_bbs_bbcodes'];
	$filekind['bbs_style'] = $alang['spacecache_filekind_bbs_style'];
	
} else {
	
	unset($cachetype['bbsthread']);
	unset($cachetype['bbsannouncement']);
	unset($cachetype['bbsforum']);
	unset($cachetype['bbslink']);
	unset($cachetype['bbsmember']);
	unset($cachetype['bbsattachment']);
	unset($cachetype['bbspost']);

}

if(!uchome_exists()){

	unset($cachetype['uchblog']);
	unset($cachetype['uchphoto']);
	unset($cachetype['uchspace']);

}

echo label(array('type'=>'form-start', 'name'=>'cacheform', 'action'=>$theurl));
echo label(array('type'=>'help', 'text'=>$alang['help_cache']));
echo label(array('type'=>'div-start'));
echo label(array('type'=>'table-start'));
echo label(array('type'=>'checkbox', 'alang'=>'spacecache_cachekind', 'name'=>'cachekind', 'options'=>$cachekind));
echo label(array('type'=>'checkbox', 'alang'=>'spacecache_cachetype', 'name'=>'cachetype', 'options'=>$cachetype));
echo label(array('type'=>'table-end'));
echo label(array('type'=>'div-end'));

echo '<div class="buttons">';
echo label(array('type'=>'button-submit', 'name'=>'cachesubmitbtn', 'value'=>$alang['common_submit']));
echo label(array('type'=>'button-reset', 'name'=>'cachereset', 'value'=>$alang['common_reset']));
echo '<input name="cachesubmit" type="hidden" value="yes" />';
echo '</div>';
echo label(array('type'=>'form-end'));

echo label(array('type'=>'form-start', 'name'=>'fileform', 'action'=>$theurl));
echo label(array('type'=>'div-start'));
echo label(array('type'=>'title', 'alang'=>'spacecache_title_file'));
echo label(array('type'=>'table-start'));
echo label(array('type'=>'checkbox', 'alang'=>'spacecache_filekind', 'name'=>'filekind', 'options'=>$filekind));
echo label(array('type'=>'table-end'));
echo label(array('type'=>'div-end'));

echo '<div class="buttons">';
echo label(array('type'=>'button-submit', 'name'=>'filesubmitbtn', 'value'=>$alang['common_submit']));
echo label(array('type'=>'button-reset', 'name'=>'filereset', 'value'=>$alang['common_reset']));
echo '<input name="filesubmit" type="hidden" value="yes" />';
echo '</div>';
echo label(array('type'=>'form-end'));

/**
 * 检查模型状态
 */
function checkmodel($name) {
	$state = checkfdperm('./model/data/'.$name, 0);
	$tmpdelarr = array('items', 'message');
	foreach($tmpdelarr as $tmpkey => $tmpvalue) {
		if(!$tableinfo = loadtable($name.$tmpvalue)) {
			$state = false;
			break;
		}
	}
	return $state;
}

/**
 * 检测读写权限
 */
function checkfdperm($path, $isfile=0) {
	if($isfile) {
		$file = $path;
		$mod = 'a';
	} else {
		$file = $path.'./install_tmptest.data';
		$mod = 'w';
	}
	if(!@$fp = fopen($file, $mod)) {
		return false;
	}
	if(!$isfile) {
		//是否可以删除
		fwrite($fp, ' ');
		fclose($fp);
		if(!@unlink($file)) {
			return false;
		}
		//检测是否可以创建子目录
		if(is_dir($path.'./install_tmpdir')) {
			if(!@rmdir($path.'./install_tmpdir')) {
				return false;
			}
		}
		if(!@mkdir($path.'./install_tmpdir')) {
			return false;
		}
		//是否可以删除
		if(!@rmdir($path.'./install_tmpdir')) {
			return false;
		}
	} else {
		fclose($fp);
	}
	return true;
}

/**
 * 获取表信息
 */
function loadtable($table, $force = 0) {
	global $_SGLOBAL;
	$tables = array();

	if(!isset($tables[$table]) || $force) {
		if($_SGLOBAL['db']->version() > '4.1') {
			$query = $_SGLOBAL['db']->query("SHOW FULL COLUMNS FROM ".tname($table), 'SILENT');
		} else {
			$query = $_SGLOBAL['db']->query("SHOW COLUMNS FROM ".tname($table), 'SILENT');
		}
		while($field = @$_SGLOBAL['db']->fetch_array($query)) {
			$tables[$table][$field['Field']] = $field;
		}
	}
	if(isset($tables[$table])) {
		return $tables[$table];
	}
	return $tables;
}

?>