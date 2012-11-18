<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_freshhtml.php 13348 2009-09-17 01:42:21Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

if(empty($_SC['freshhtml'])) exit();

//权限
if(!checkperm('managehtml')) {
	showmessage('no_authority_management_operation');
}

//INIT RESULT VAR
$op = empty($_GET['op']) ? '' : trim($_GET['op']);
$defaultclass = array('index' => '');
$defaultclass[$op] = ' class="active"';
define('CREATEHTML', '1');

$otherchannels = array(
	'poll' => array('nameid' => 'poll', 'name' => $alang['poll_title']),
	'announcement' => array('nameid' => 'announcement', 'name' => $alang['block_type_announcement']),
	'map' => array('nameid' => 'map', 'name' => $alang['site_map']),
	'link' => array('nameid' => 'link', 'name' => $alang['xs_friendlinks'])
);

//POST METHOD
if(submitcheck('channelsubmit')) {
	
	if(empty($_POST['channels'][0])) {
		$_POST['channels'] = array_keys(array_merge($channels['menus'], $otherchannels));
	}
	$catarr = array();
	$makecache = array('channels' => $_POST['channels'], 'pro' => array('cha' => '', 'cat' => '', 'id' => ''), 'ele' => array());
	$cachefile = S_ROOT.'./data/temp/making.cache.php';
	$cachetext = '$makecache='.arrayeval($makecache);
	writefile($cachefile, $cachetext, 'php');
	
	$nexturl = CPURL.'?action=freshhtml&op=cache';
	jumpmessage($nexturl, $alang['making_create_cache']);

}

if(empty($op)) {

	
	
	
} elseif($op == 'index') {
	
	$rs = createhtml(S_ROOT.'./index.php', 'action/index');
	if($rs === true) {
		showmessage('make_index_success', $theurl);
	} else {
		showmessage($rs);
	}

} elseif($op == 'channel') {
	
	$do = empty($_GET['do']) ? '' : trim($_GET['do']);
	
} elseif($op == 'cache') {
	
	$cachefile = S_ROOT.'./data/temp/making.cache.php';
	if(!file_exists($cachefile)) {
		showmessage('freshhtml_cache_error', CPURL.'?action=freshhtml&op=channel');
	}
	include_once($cachefile);

	//缓存文件
	$arrnum = 20;
	if(!empty($makecache['ele'])) {
		$nexturl = CPURL.'?action=freshhtml&op=making';
		jumpmessage($nexturl, $alang['making_create_cache_havedata']);
	}

	$proarr = array('cha' => '', 'cat' => '', 'id' => '');
	$elearr = array();
	foreach($makecache['channels'] as $key => $value) {
		if($arrnum<=0) break;
		if(empty($proarr['cha']) && !empty($makecache['pro']['cha']) && $value != $makecache['pro']['cha']) {
			//生成过的频道剔除
			unset($makecache['channels'][$key]);
			continue;
		} elseif(!empty($makecache['pro']['cha']) && $makecache['pro']['cha'] != $value) {
			$makecache['pro']['id'] = 0;
		}
		if(!empty($channels['menus'][$value])) {
			$proarr = array('cha' => $value, 'cat' => '', 'id' => '');
			$channel = $channels['menus'][$value];
			$catvalue = $item = array();
			if($value == 'news' || $channel['upnameid'] == 'news') {
				
				$elearr[] = 'action/'.$value;
				if(empty($catarr)) $catarr = getcategory();
				foreach($catarr[$value] as $catvalue) {
					if($arrnum<=0) break;
					if(empty($proarr['cat']) && !empty($makecache['pro']['cat']) && $catvalue['catid'] != $makecache['pro']['cat']) {
						//生成过的分类跳过
						continue;
					}
					$itemsql = '';
					$proarr['cat'] = $catvalue['catid'];
					if($catvalue['catid'] != $makecache['pro']['cat']) {
						//如果这个过程中生成过分类页，就不进行再次生成
						$elearr[] = 'action/category/catid/'.$catvalue['catid'];
					} else {
						$itemsql = ' AND itemid < \''.$makecache['pro']['id'].'\' ';
					}
					$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spaceitems').' WHERE catid=\''.$catvalue['catid'].'\''.$itemsql), 0);
					if($listcount) {
						if($catvalue['catid'] != $makecache['pro']['cat']) {
							$listpages = @ceil($listcount/$catvalue['perpage']);
							for($i=2; $i<=$listpages; $i++) {
								$elearr[] = 'action/category/catid/'.$catvalue['catid'].'/page/'.$i;
							}
						}
						$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spaceitems').' WHERE catid=\''.$catvalue['catid'].'\''.$itemsql.' ORDER BY itemid DESC');
						while($item = $_SGLOBAL['db']->fetch_array($query)) {
							if($arrnum<=0) break;
							$proarr['id'] = $item['itemid'];
							$elearr[] = 'action/viewnews/itemid/'.$item['itemid'];
							$arrnum--;
							if($arrnum<=0) break;
						}
					}
					if($arrnum<=0) break;
				}
				
			} elseif($channel['type'] == 'model') {

				$itemsql = '';
				if(!empty($makecache['pro']['id'])) {
					$itemsql = ' AND itemid < \''.$makecache['pro']['id'].'\' ';
				}
				if(empty($catarr)) $catarr = getcategory();
				$catvalue = $catarr[$value];
				$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname($value.'items').' WHERE 1 '.$itemsql), 0);
				if($listcount) {
					$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($value.'items').' WHERE 1 '.$itemsql.' ORDER BY itemid DESC');
					while($item = $_SGLOBAL['db']->fetch_array($query)) {
						if($arrnum<=0) break;
						$proarr['id'] = $item['itemid'];
						$elearr[] = 'action/model/name/'.$value.'/itemid/'.$item['itemid'];
						$arrnum--;
						if($arrnum<=0) break;
					}
				}

			} elseif($value == 'bbs') {
				
				$elearr[] = 'action/'.$value;
				if(empty($forumarr)) {
					@include_once S_ROOT.'/data/system/bbsforums.cache.php';
					if(!empty($_SGLOBAL['bbsforumarr']) && is_array($_SGLOBAL['bbsforumarr'])) {
						foreach($_SGLOBAL['bbsforumarr'] as $value) {
							if($value['allowshare'] == 1 && $value['type'] == 'forum') {
								$forumarr[$value['fid']] = $value;
							}
						}
					}
				}
				foreach($forumarr as $catvalue) {
					if($arrnum<=0) break;
					if(empty($proarr['cat']) && !empty($makecache['pro']['cat']) && $catvalue['fid'] != $makecache['pro']['cat']) {
						//生成过的分类跳过
						continue;
					}
					$itemsql = '';
					$proarr['cat'] = $catvalue['fid'];
					if($catvalue['fid'] != $makecache['pro']['cat']) {
						//如果这个过程中生成过分类页，就不进行再次生成
						$elearr[] = 'action/forumdisplay/fid/'.$catvalue['fid'];
					} else {
						$itemsql = ' AND tid < \''.$makecache['pro']['id'].'\' ';
					}
					dbconnect(1);
					$listcount = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query('SELECT COUNT(*) FROM '.tname('threads', 1).' WHERE fid=\''.$catvalue['fid'].'\''.$itemsql), 0);
					if($listcount) {
						if($catvalue['fid'] != $makecache['pro']['cat']) {
							$listpages = @ceil($listcount/$catvalue['perpage']);
							for($i=2; $i<=$listpages; $i++) {
								$elearr[] = 'action/forumdisplay/fid/'.$catvalue['fid'].'/page/'.$i;
							}
						}
						$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('threads', 1).' WHERE fid=\''.$catvalue['fid'].'\''.$itemsql.' ORDER BY tid DESC');
						while($item = $_SGLOBAL['db_bbs']->fetch_array($query)) {
							if($arrnum<=0) break;
							$proarr['id'] = $item['tid'];
							$elearr[] = 'action/viewthread/tid/'.$item['tid'];
							$arrnum--;
							if($arrnum<=0) break;
						}
					}
					if($arrnum<=0) break;
				}
				
			} elseif($value == 'uchblog') {
				
				$elearr[] = 'action/'.$value;
				$itemsql = '';
				if(!empty($makecache['pro']['id'])) {
					$itemsql = ' AND blogid < \''.$makecache['pro']['id'].'\' ';
				}
				dbconnect(2);
				$listcount = $_SGLOBAL['db_uch']->result($_SGLOBAL['db_uch']->query('SELECT COUNT(*) FROM '.tname('blog', 2).' WHERE 1 '.$itemsql), 0);
				if($listcount) {
					$query = $_SGLOBAL['db_uch']->query('SELECT * FROM '.tname('blog', 2).' WHERE 1 '.$itemsql.' ORDER BY blogid DESC');
					while($item = $_SGLOBAL['db_uch']->fetch_array($query)) {
						if($arrnum<=0) break;
						$proarr['id'] = $item['blogid'];
						$elearr[] = 'action/blogdetail/uid/'.$item['uid'].'/id/'.$item['blogid'];
						$arrnum--;
						if($arrnum<=0) break;
					}
				}
				
			} elseif($value == 'uchimage') {
				
				$elearr[] = 'action/'.$value;
				$itemsql = '';
				if(!empty($makecache['pro']['id'])) {
					$itemsql = ' AND albumid < \''.$makecache['pro']['id'].'\' ';
				}
				dbconnect(2);
				$listcount = $_SGLOBAL['db_uch']->result($_SGLOBAL['db_uch']->query('SELECT COUNT(*) FROM '.tname('album', 2).' WHERE 1 '.$itemsql), 0);
				if($listcount) {
					$perpage = 21;
					if(empty($makecache['pro']['id'])) {
						$listpages = @ceil($listcount/$perpage);
						for($i=2; $i<=$listpages; $i++) {
							$elearr[] = 'action/imagelist/page/'.$i;
						}
					}
					$query = $_SGLOBAL['db_uch']->query('SELECT * FROM '.tname('album', 2).' WHERE 1 '.$itemsql.' ORDER BY albumid DESC');
					while($item = $_SGLOBAL['db_uch']->fetch_array($query)) {
						if($arrnum<=0) break;
						$proarr['id'] = $item['albumid'];
						$elearr[] = 'action/imagelist/uid/'.$item['uid'].'/id/'.$item['albumid'];
						
						$albumquery = $_SGLOBAL['db_uch']->query("SELECT count(*) FROM".tname('pic', 2)." WHERE albumid='$item[albumid]' AND uid='$item[uid]'");
						$listcount = $_SGLOBAL['db_uch']->result($albumquery, 0);
						if($listcount) {
							$albumquery = $_SGLOBAL['db_uch']->query("SELECT * FROM ".tname('pic', 2)." WHERE albumid='$item[albumid]' AND uid='$item[uid]' ORDER BY picid DESC ");
							while($albumvalue = $_SGLOBAL['db_uch']->fetch_array($albumquery)) {
								$elearr[] = 'action/imagedetail/uid/'.$albumvalue['uid'].'/pid/'.$albumvalue['picid'];
							}
						}
						$arrnum--;
						if($arrnum<=0) break;
					}
				}
				
			} elseif($value == 'top') {
				
			} elseif($channel['type'] == 'user') {
				
			}
		} elseif(!empty($otherchannels[$value])) {
			
		}
		if($arrnum<=0) break;
	}
	
	$makecache['pro'] = $proarr;
	$makecache['ele'] = $elearr;
	$cachetext = '$makecache='.arrayeval($makecache);
	writefile($cachefile, $cachetext, 'php');
	if(empty($elearr)) {
		showmessage('make_channle_index_success', $theurl.'&op=channel');
	} else {
		$nexturl = CPURL.'?action=freshhtml&op=making';
		jumpmessage($nexturl, "[{$channel[name]} {$catvalue[name]} {$item[itemid]}] ".$alang['making_create_cache_update']);
	}
	
} elseif($op == 'making') {
	
	$cachefile = S_ROOT.'./data/temp/making.cache.php';
	if(!file_exists($cachefile)) {
		showmessage('freshhtml_cache_error', $theurl.'&op=channel');
	}
	include_once($cachefile);

	//缓存文件
	$perpage = 10;
	$page = empty($_GET['page']) && intval($_GET['page']) < 1 ? 1 : intval($_GET['page']);
	if(!empty($makecache['ele'])) {

		for($i=0; $i < $perpage; $i++) {
			if(empty($makecache['ele'])) break;
			$total = count($makecache['ele']);
			$ele = array();
			$ele = array_shift($makecache['ele']);
			$qarr = parseparameter($ele);
			$_SGET = $qarr;
			$_SGET['page'] = empty($_SGET['page']) || intval($_SGET['page']) < 1 ? 1 : intval($_SGET['page']);
			if($qarr['action'] == 'model') {
				$qarr['action'] = 'modelview';
			}
			if(empty($channels['menus'][$qarr['action']]['upnameid']) && $channels['menus'][$qarr['action']]['upnameid'] != 'news') {
				$rs = createhtml(S_ROOT."./$qarr[action].php", $ele);
			} else {
				$rs = createhtml(S_ROOT."./news.php", $ele);
			}
			if($rs != true) {
				showmessage($rs);
			}
		}
		
		$cachetext = '$makecache='.arrayeval($makecache);
		writefile($cachefile, $cachetext, 'php');
		$nexturl = CPURL.'?action=freshhtml&op=making&page='.(++$page);
		jumpmessage($nexturl, $alang['freshhtml_making_page_info_0'].$total.$alang['freshhtml_making_page_info_1'].$perpage.$alang['freshhtml_making_page_info_2'].$page.$alang['freshhtml_making_page_info_3']);

	} else {

		$nexturl = CPURL.'?action=freshhtml&op=cache';
		jumpmessage($nexturl, $alang['making_page_cache_update']);

	}

}

include template('admin/tpl/freshhtml.htm', 1);

//信息提示
function jumpmessage($url, $message, $time=1) {
	global $acid, $alang, $menus;
	obclean();
	include template('admin/tpl/header.htm', 1);
	echo '<table style="width:98%;padding:0.2em;border: 1px solid #698CC3;font-size:14px;font-weight:bold;font-family: Trebuchet MS, Lucida Console, Lucida Sans, sans-serif;" align="center"><tr><td><a href='.$url.'>'.$message.'</a></td><td width="35%"><a href="'.CPURL.'?action=freshhtml&op=channel">'.$alang['making_page_info_3'].'</a></td></tr></table>';
	include template('admin/tpl/footer.htm', 1);
	jumpurl($url.'&'.random(10, 1), $time);
}


//生成html函数
function createhtml($setphp, $ele) {
	global $lang, $alang, $_SGET, $_SCONFIG, $_SGLOBAL, $_SBLOCK, $_SHTML, $_DCACHE, $_SC;
	
	$channels = getchannels();
	obclean();
	@include($setphp);
	$content = ob_get_contents();
	obclean();

	$dir = gethtmlurl($ele, 1);
	$file = substr($dir, strrpos($dir, '/')+1);
	$file = empty($file) ? './index.html' : $file;
	$dir = substr($dir, 0, strrpos($dir, '/'));

	//权限验证
	if(file_exists($dir.'/'.$file)) {
		if(!checkfdperm($dir.'/'.$file, 1)) {
			return $file.$alang['iswrite_file_error'];
		}
	} else {
		if(!checkfdperm($dir)) {
			$dirarr = explode("/", $dir);
			$dirstr = '';
			foreach($dirarr as $key) {
				if(!is_dir($dirstr.$key)) {
					@mkdir($dirstr.$key, 0777);
				} elseif(!checkfdperm($dirstr.$key)) {
					return $dirstr.$key.$alang['iswrite_error'];
				}
				$dirstr .= $key.'/';
			}
		}
	}
	
	
	writefile($dir.'/'.$file, $content);

	return true;
}

//检查权限
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
?>