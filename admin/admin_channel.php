<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_channel.php 13489 2009-11-10 02:34:44Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managechannel')) {
	showmessage('no_authority_management_operation');
}

//INIT RESULT VAR
$listarr = array();
$thevalue = array();
$protect_channel = array('bbs', 'news', 'uchblog', 'uchimage', 'sample');

//POST METHOD
if(submitcheck('listsubmit')) {
	
	foreach ($_POST['nameid'] as $nameid => $channeltype) {
		$status = intval($_POST['show'][$nameid]);
		if($_POST['default'] == $nameid) $status = 2;
		if($channeltype == 'user') {
			if(!empty($_POST['delete'][$nameid])) {
				//删除频道文件
				@unlink(S_ROOT.'./channel/channel_'.$nameid.'.php');
				@unlink(S_ROOT.'./templates/'.$_SCONFIG['template'].'/channel_'.$nameid.'.html.php');
				deletetable('channels', array('nameid' => $nameid));
				deletetable('spaceitems', array('type' => $nameid));
				deletetable('spacecomments', array('type' => $nameid));
				deletetable('postitems', array('type' => $nameid));
				deletetable('customfields', array('type' => $nameid));
				deletetable('categories', array('type' => $nameid));
				deletetable('attachments', array('type' => $nameid));
			} else {
				$setsqlarr = array(
					'name' => $_POST['name'][$nameid],
					'status' => $status,
					'path' => $_POST['path'][$nameid],
					'displayorder' => $_POST['displayorder'][$nameid]
				);
				updatetable('channels', $setsqlarr, array('nameid' => $nameid));
			}
		} else {
			$setsqlarr = array(
				'name' => $_POST['name'][$nameid],
				'url' => $_POST['url'][$nameid],
				'status' => $status,
				'displayorder' => $_POST['displayorder'][$nameid]
			);
			updatetable('channels', $setsqlarr, array('nameid' => $nameid));
		}
	}

	//更新缓存
	updatesettingcache();
	updateuserspacemid();

	showmessage('channel_update_ok', $theurl);

} elseif (submitcheck('valuesubmit')) {

	$nameid = trim(strtolower($_POST['nameid']));
	$_POST['op'] = empty($_POST['op']) ? 'add' : trim($_POST['op']);
	if(empty($nameid) || !ereg("^[a-zA-Z]+$", $nameid)) {
		showmessage('channel_action_error');
	}

    if($_POST['op'] == 'add' && in_array($nameid, $protect_channel)) {
        showmessage('channel_action_protect');
    }
	
	$viewstr = implode("\t", $_POST['viewperm']);
	$poststr = implode("\t", $_POST['postperm']);
	$replystr = implode("\t", $_POST['commentperm']);
	$getattachstr = implode("\t", $_POST['getattachperm']);
	$postattachstr = implode("\t", $_POST['postattachperm']);
	$managestr = implode("\t", $_POST['manageperm']);
    
    $tpl_query = $_SGLOBAL['db']->query("SELECT type,tpl,upnameid FROM ".tname('channels')." WHERE nameid='$nameid'");
    $tpl_channel = $_SGLOBAL['db']->fetch_array($tpl_query);
    $tpl_channel['type']=='user' && empty($tpl_channel['upnameid']) ? $_POST['tpl'] = $tpl_channel['tpl'] : '';
    
	$_POST['tpl'] = empty($_POST['tpl']) ? '' : trim(str_replace(array('..', '/', '\\'), array('', '', ''), $_POST['tpl']));
	$_POST['categorytpl'] = empty($_POST['categorytpl']) ? '' : trim(str_replace(array('..', '/', '\\'), array('', '', ''), $_POST['categorytpl']));
	$_POST['viewtpl'] = empty($_POST['viewtpl']) ? '' : trim(str_replace(array('..', '/', '\\'), array('', '', ''), $_POST['viewtpl']));

	//添加
	$sqlarr = array(
		'name' => $_POST['name'],
		'status' => 1,
		'url' => $_POST['url'],
		'path' => $_POST['path'],
		'domain' => $_POST['domain'],
		'upnameid' => $_POST['type']=='news' ? 'news' : '',
		'tpl' => !empty($_POST['tpl']) || $_POST['type']!='channel' ? $_POST['tpl'] : 'channel_'.$nameid,
		'categorytpl' => $_POST['categorytpl'],
		'viewtpl' => $_POST['viewtpl'],
		'allowview' => $viewstr,
		'allowpost' => $poststr,
		'allowcomment' => $replystr,
		'allowgetattach' => $getattachstr,
		'allowpostattach' => $postattachstr,
		'allowmanage' => $managestr
	);

	if($_POST['op']!='edit') {

		if($_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('channels')." WHERE nameid='$nameid'"), 0)) {
			showmessage('channel_action_exist');
		}
	
		if($_POST['type'] == 'channel' && !empty($_POST['usesample'])) {
			//复制程序文件
			$src = S_ROOT.'./channel/channel_sample.php';
			$obj = S_ROOT.'./channel/channel_'.$nameid.'.php';
			if(!file_exists($src)) {
				showmessage('channel_php_src_error');
			}
			if(!@copy($src, $obj)) {
				$data = implode('', file ($src));
				writefile($obj, $data);
			}
			//复制模板
			$src = S_ROOT.'./templates/'.$_SCONFIG['template'].'/channel_sample.html.php';
			$obj = S_ROOT.'./templates/'.$_SCONFIG['template'].'/channel_'.$nameid.'.html.php';
			if(!file_exists($src)) {
				showmessage('channel_tpl_src_error');
			}
			if(!@copy($src, $obj)) {
				$data = implode('', file ($src));
				writefile($obj, $data);
			}
		} elseif($_POST['type'] == 'news') {
			$_POST['category'] = trim($_POST['category']);
			$datas = array();
			if(empty($_POST['category'])) {
				$datas = array(
					"'$alang[channel_category_1]', '$nameid'",
					"'$alang[channel_category_2]', '$nameid'",
					"'$alang[channel_category_3]', '$nameid'",
					"'$alang[channel_category_4]', '$nameid'",
					"'$alang[channel_category_5]', '$nameid'",
					"'$alang[channel_category_6]', '$nameid'",
					"'$alang[channel_category_7]', '$nameid'",
					"'$alang[channel_category_8]', '$nameid'",
					"'$alang[channel_category_9]', '$nameid'"
				);
			} else {
				$_POST['category'] = explode("\n", $_POST['category']);
				foreach($_POST['category'] as $value) {
					$value = saddslashes(shtmlspecialchars(trim($value)));
					if($value) {
						$datas[] = "'$value', '$nameid'";
					}
				}
			}
			if(!empty($datas)) {
				$_SGLOBAL['db']->query("INSERT INTO ".tname('categories')." (`name`, `type`) VALUES (".implode('),(', $datas).")");
				$_SGLOBAL['db']->query("UPDATE ".tname('categories')." SET subcatid=catid");
			}
		}
		
		//添加
		$sqlarr['nameid'] = $nameid;
		$sqlarr['type'] = user;
		inserttable('channels', saddslashes($sqlarr));
	
	} else {

		updatetable('channels', $sqlarr, array('nameid' => $nameid));
		
	}

	//更新缓存
	updatesettingcache();
	updateuserspacemid();
	
	showmessage('do_success', $theurl);

}

//GET METHOD
$addclass = $viewclass = '';
if (empty($_GET['op'])) {

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('channels')." ORDER BY displayorder");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($value['name'])) $value['name'] = $lang[$value['nameid']];
		$value['visit'] = $value['url'];
		if(empty($value['visit'])) {
			if($value['type'] == 'user' && $value['upnameid']!='news') {
				$value['visit'] = geturl("action/channel/name/$value[nameid]");
			} elseif($value['type'] == 'model') {
				$value['visit'] = S_URL.'/m.php?name='.$value['nameid'];
			} else {
				$value['visit'] = geturl("action/$value[nameid]");
			}
		}
		$listarr[$value['nameid']] = $value;
	}
	$viewclass = ' class="active"';
	
} elseif ($_GET['op'] == 'add') {
	
	$thevalue = array(
		'name' => '',
		'nameid' => '',
		'usesample' => 1
	);
	$addclass = ' class="active"';
	@include_once(S_ROOT.'./data/system/group.cache.php');
	
	$viewarr = $postarr = $replyarr = $getattacharr = $postattacharr = $managearr = array();
	
} elseif ($_GET['op'] == 'edit') {
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('channels')." WHERE nameid='$_GET[nameid]'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		$thevalue = $value;
		$addclass = ' class="active"';
	} else {
		showmessage('channel_no_exists');
	}
	$viewarr = explode("\t", $thevalue['allowview']);
	$postarr = explode("\t", $thevalue['allowpost']);
	$replyarr = explode("\t", $thevalue['allowcomment']);
	$getattacharr = explode("\t", $thevalue['allowgetattach']);
	$postattacharr = explode("\t", $thevalue['allowpostattach']);
	$managearr = explode("\t", $thevalue['allowmanage']);
	
	@include_once(S_ROOT.'./data/system/group.cache.php');
} elseif ($_GET['op'] == 'edittpl') {
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('channels')." WHERE nameid='$_GET[nameid]'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['type'] == 'model') {
			showmessage('channel_is_model');
		}
		if(empty($value['tpl'])) {
			if($value['upnameid']=='news') {
				$value['nameid'] = 'news';
			} elseif(in_array($value['nameid'], array('uchblog', 'uchimage'))) {
				$value['nameid'] = substr($value['nameid'], 3);
			}
			$value['tpl'] = "{$value['nameid']}_index";
		}
		header("Location: admincp.php?action=tpl&op=edit&filename=$value[tpl]");
		exit;
	} else {
		showmessage('channel_no_exists');
	}
}

include template('admin/tpl/channel.htm', 1);
?>