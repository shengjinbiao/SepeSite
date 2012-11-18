<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: modelview.php 13493 2009-11-11 06:15:33Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./function/model.func.php');

$_SGET['name'] = !empty($_SGET['name']) ? trim($_SGET['name']) : '';

$channel = $_SGET['name'];
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

$cacheinfo = getmodelinfoall('modelname', $_SGET['name']);
if(empty($cacheinfo['models'])) {
	showmessage('visit_the_channel_does_not_exist', S_URL);
}
$modelsinfoarr = $cacheinfo['models'];
$categories = $cacheinfo['categories'];
$itemid = empty($_SGET['itemid']) ? 0 : intval($_SGET['itemid']);

//导航
$channelsmore = array();
if(!empty($channels['menus']) && count($channels['menus']) > 12) {
	$channelsmore = $channels['menus'];
	for($i = 0; $i < 12; $i++) {
		array_shift($channelsmore);
	}
}

$item = $gatherarr = $columnsallinfoarr = array();
$isshowmore = 0;
$columnsinfoarr = array('fixed'=>array(), 'message'=>array());
if($itemid) {
	$query = $_SGLOBAL['db']->query('SELECT i.*, ii.* FROM '.tname($_SGET['name'].'items').' i, '.tname($_SGET['name'].'message').' ii WHERE i.itemid=\''.$itemid.'\' AND i.itemid=ii.itemid');
	$item = $_SGLOBAL['db']->fetch_array($query);
	if(defined('CREATEHTML')) {
		$_SGLOBAL['item_cache']['model_'.$_SGET['name'].'_'.$item['itemid']] = array('dateline' => $item['dateline']);
	}
}

//查询自定义字段内容
if(empty($item)) {
	showmessage('not_found', S_URL);
} else {
	if(!empty($item['subjectimage'])) {
		$item['subjectimage'] = A_URL.'/'.$item['subjectimage'];
	}
	if(!empty($cacheinfo['columns'])) {
		foreach($cacheinfo['columns'] as $temp) {
			if(empty($temp['allowshow']) && (!checkperm('managemodelfolders') || !isset($_SGET['more']))) {
				unset($cacheinfo[$temp['fieldname']]);
				$isshowmore++;
			} else {
				$tmpvalue = trim($item[$temp['fieldname']]);
				if((empty($temp['isfile']) && strlen($tmpvalue) > 0) || (!empty($temp['isfile']) && $tmpvalue != 0)) {
					if(preg_match("/^(select|radio)$/i", $temp['formtype']) || (preg_match("/^(VARCHAR|CHAR)$/i", $temp['fieldtype']) && $temp['fieldlength'] <= 20) || (preg_match("/^(TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT|FLOAT|DOUBLE)$/i", $temp['fieldtype']) && $temp['formtype'] != 'file')) {
						$arraytype = 'fixed';
					} else {
						$arraytype = 'message';
					}
					
					if($temp['formtype'] == 'checkbox') {
						$tmpvalue = explode("\n", $item[$temp['fieldname']]);
					} elseif($temp['formtype'] == 'textarea' && empty($temp['ishtml'])) {
						if($arraytype == 'fixed') {
							$tmpvalue = str_replace("\n", '&nbsp;', $item[$temp['fieldname']]);
						} else {
							$tmpvalue = str_replace("\n", '<br />', $item[$temp['fieldname']]);
						}
					}
			
					$temp['filepath'] = '';
					if(!empty($temp['isimage']) || !empty($temp['isflash'])) {
						$temp['filepath'] = A_URL.'/'.$tmpvalue;
					} elseif(!empty($temp['isfile'])) {
						$temp['filepath'] = rawurlencode(authcode($_SGET['name'].','.$tmpvalue, 'ENCODE'));
					}
					$columnsallinfoarr[$temp['fieldname']] = $columnsinfoarr[$arraytype][] = array(
							'fieldname'	=>	$temp['fieldname'],
							'fieldcomment'	=>	$temp['fieldcomment'],
							'fieldtype'	=>	$temp['fieldtype'],
							'formtype'	=>	$temp['formtype'],
							'ishtml'	=>	$temp['ishtml'],
							'isfile'	=>	$temp['isfile'],
							'isimage'	=>	$temp['isimage'],
							'isflash'	=>	$temp['isflash'],
							'filepath'	=>	$temp['filepath'],
							'value'	=>	$tmpvalue
					);
				}
			}
		}
	}
}

//header
$title = $item['subject'].' - '.$modelsinfoarr['modelalias'].' - '.$_SCONFIG['sitename'];
if(empty($newtagarr)) $newtagarr = array($item['subject'], $modelsinfoarr['modelalias']);
$keywords = implode(',', $newtagarr);
$keywords = !empty($modelsinfoarr['seokeywords']) ? $keywords.','.$modelsinfoarr['seokeywords'] : $keywords;
$description = str_replace(array('&nbsp;', "\r", "\n", '\'', '"'), '', cutstr(trim(strip_tags($item['message'])), 200));
$description = !empty($modelsinfoarr['seodescription']) ? $description.','.$modelsinfoarr['seodescription'] : $description;
$title = strip_tags($title);
$keywords = strip_tags($keywords);
$description = strip_tags($description);

//分类
$query = $_SGLOBAL['db']->query('SELECT f.*, ff.name AS upname FROM '.tname('categories').' f LEFT JOIN '.tname('categories').' ff ON ff.catid=f.upid WHERE f.catid=\''.$item['catid'].'\'');
$thecat = $_SGLOBAL['db']->fetch_array($query);
$guidearr = array();
$guidearr[] = array('url' => empty($channels['menus'][$modelsinfoarr['modelname']]) ? S_URL.'/m.php?name='.$modelsinfoarr['modelname'] : $channels['menus'][$modelsinfoarr['modelname']]['url'],'name' => $modelsinfoarr['modelalias']);
if(!empty($thecat['upname'])) {
	$guidearr[] = array('url' => S_URL.'/m.php?name='.$modelsinfoarr['modelname'].'&mo_catid='.$thecat['upid'], 'name' => $thecat['upname']);
}
$guidearr[] = array('url' => S_URL.'/m.php?name='.$modelsinfoarr['modelname'].'&mo_catid='.$thecat['catid'], 'name' => $thecat['name']);

if(!empty($cacheinfo['fielddefault']['subject'])) $lang['subject'] = $cacheinfo['fielddefault']['subject'];
if(!empty($cacheinfo['fielddefault']['subjectimage'])) $lang['photo_title'] = $cacheinfo['fielddefault']['subjectimage'];
if(!empty($cacheinfo['fielddefault']['catid'])) $lang['system_catid'] = $cacheinfo['fielddefault']['catid'];
if(!empty($cacheinfo['fielddefault']['message'])) $lang['content'] = $cacheinfo['fielddefault']['message'];

//自定义类别
if(!empty($cacheinfo['columns'])) {
	foreach($cacheinfo['columns'] as $tmpvalue) {
		if(!empty($tmpvalue['fielddata'])) {
			$temparr = explode("\r\n", $tmpvalue['fielddata']);
			if($tmpvalue['formtype'] != 'linkage') {
				$gatherarr[$tmpvalue['fieldname']] = $temparr;
			}
		}
	}
}

//投稿链接
$posturl = '';
if(checkperm('allowpost')) {
	$posturl = S_URL.'/cp.php?ac=models&op=add&nameid='.$channel;
}
$moreurl = !empty($isshowmore) && !empty($_SGLOBAL['supe_uid']) && (checkperm('managemodelfolders') && !isset($_SGET['more'])) ? geturl('action/model/name/'.$modelsinfoarr['modelname'].'/itemid/'.$itemid.'/more/1') : '';

//更新统计数
$_SGLOBAL['db']->query('UPDATE '.tname($modelsinfoarr['modelname'].'items').' SET viewnum=viewnum+1 WHERE itemid=\''.$itemid.'\'');

//评论
$listcount = $item['replynum'];

$_SCONFIG['viewspace_pernum'] = intval($_SCONFIG['viewspace_pernum']);
if(!empty($_SCONFIG['viewspace_pernum']) && $listcount) {

	$repeatids = array();
	$j = 1;
	$sql = "SELECT c.* FROM ".tname('spacecomments')." c WHERE c.itemid='$item[itemid]' AND c.type='".$modelsinfoarr['modelname']."' AND status='1' ORDER BY c.dateline ".($_SCONFIG['commorderby']?'DESC':'ASC')." LIMIT 0, $_SCONFIG[viewspace_pernum]";
	$query = $_SGLOBAL['db']->query($sql);
	while ($comment = $_SGLOBAL['db']->fetch_array($query)) {
		$comment = formatcomment($comment, $repeatids);
		$comment['num'] = $j++;
		$commentlist[] = $comment;
		if(!empty($comment['firstcid']) && !in_array($comment['firstcid'], $repeatids)) {
			$repeatids[] = $comment['firstcid'];
		}
	}
}

if(allowfeed()) {
	$addfeedcheck = addfeedcheck(8) ? 'checked="checked"' : '';
}

$a_url = A_URL;

//点击器相关
@include_once(S_ROOT.'./data/system/click.cache.php');
$clicks = empty($_SGLOBAL['click'])?array():$_SGLOBAL['click'];
$clickgroups = empty($_SGLOBAL['clickgroup']['models'])?array():$_SGLOBAL['clickgroup']['models'];
foreach($clickgroups as $key => $value) {
	if($value['mid'] != $modelsinfoarr['mid']) unset($clickgroups[$key]);
}
$hash = md5($item['uid']."\t".$item['dateline']);
$clickcounts = array();
foreach ($clicks as $k => $v) {
	if(!empty($clickgroups[$k])) {
		$clicknum = $total = $average = $maxclicknum = $minclicknum = 0;
		foreach ($v as $key => $value) {
			if(empty($value['status'])) {
				unset($clicks[$k][$key]);
				continue;
			}
			$value['clicknum'] = $item["click_$key"];
			//统计
			$clicknum += $value['clicknum'];	//点击数
			$total += $value['clicknum']*$value['score'];	//总分
			if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];	//最大点击数
			if($value['clicknum'] < $minclicknum) $minclicknum = $value['clicknum'];	//最小点击数
			$clicks[$k][$key] = $value;
		}
		$average = $clicknum?round($total/$clicknum,2):0;	//平均分
		$clickcounts[$k]['clicknum'] = $clicknum;
		$clickcounts[$k]['total'] = $total;
		$clickcounts[$k]['average'] = $average;
		$clickcounts[$k]['maxclicknum'] = $maxclicknum;
		$clickcounts[$k]['minclicknum'] = $minclicknum;
	} else {
		unset($clicks[$k]);
	}
}

//模板
if(empty($modelsinfoarr['tpl'])) {
	$tpldir = 'model/data/'.$modelsinfoarr['modelname'];
} else {
	$tpldir = 'mthemes/'.$modelsinfoarr['tpl'];
}

include template($tpldir.'/view.html.php', 1);

ob_out();

?>