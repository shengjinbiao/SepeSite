<?php
/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_html.php 11840 2009-03-26 06:53:36Z zhanglijun $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managehtml')) {
	showmessage('no_authority_management_operation');
}

include_once(S_ROOT.'/data/system/htmlcat.cache.php');

//资讯HTML存放路径
define('S_HTML_ROOT', S_ROOT.'/'.substr($_SCONFIG['newspath'], 2));

//变量
$op = empty($_GET['op'])?'makeindex':$_GET['op'];
$activearr = array('makeindex'=>'', 'makeall'=>'', 'makeitemid'=>'');
$activearr[$op] = ' class="active"';

$perlisthtml = postget('perlisthtml') ? intval(postget('perlisthtml')) : 20 ; 
$catid = intval(postget('catid')); 


//导航处理
if(!empty($catarr) && is_array($catarr)) {
	foreach($catarr as $value) {
		$value['url'] = gethtmlurl2($value['catid']).'/index.html';
		$category[] = $value;
	}
}

if(submitcheck('indexsubmit')) {
	//参数设置
	$setsqlarr[] = "('htmlopen', '".intval($_POST['htmlopen'])."')";
	$index_path = '';
	if(!empty($_POST['index_path'])) {
		if(is_writable(S_HTML_ROOT.substr($_POST['index_path'], 1))) {
			$index_path = S_HTML_ROOT.substr($_POST['index_path'], 1);
		} else {
			showmessage('iswrite_error');
		}
	} else {
		$index_path = S_HTML_ROOT.'/index.html';
	}
	$setsqlarr[] = "('index_path', '".$_POST['index_path']."')";

	if(!empty($_POST['index_domain'])) {
		if(substr($_POST['index_domain'], 0 , 7) != 'http://') {
			showmessage('category_domain_error');
		}
	}
	$setsqlarr[] = "('index_domain', '".$_POST['index_domain']."')";
	
	$_SGLOBAL['db']->query('REPLACE INTO '.tname('settings').' (variable, value) VALUES '.implode(',', $setsqlarr));
	//CACHE
	include_once(S_ROOT.'./function/cache.func.php');
	updatesettingcache();
	
	//图文资讯
	$spicnews = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." i WHERE  folder=1 AND picid != 0 ORDER BY dateline DESC LIMIT 0,11");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['subject'] = cutstr($value['subject'], 48, 0);
			$value['url'] = gethtmlurl2($value['catid']).'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
			if($value['picid'] && $value['hash']) {
					$aids[] = $value['picid'];
			}
			$spicnews[$value['itemid']] = $value;
		}
	if(!empty($aids)) {
		$_SGLOBAL['attachsql'] = 'a.aid AS a_aid, a.type AS a_type, a.itemid AS a_itemid, a.uid AS a_uid, a.dateline AS a_dateline, a.filename AS a_filename, a.subject AS a_subject, a.attachtype AS a_attachtype, a.isimage AS a_isimage, a.size AS a_size, a.filepath AS a_filepath, a.thumbpath AS a_thumbpath, a.downloads AS a_downloads';
		$query = $_SGLOBAL['db']->query('SELECT '.$_SGLOBAL['attachsql'].' FROM '.tname('attachments').' a WHERE a.aid IN (\''.implode('\',\'', $aids).'\') ORDER BY a.dateline');
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			//处理
			if(!empty($attacharr[$value['a_itemid']])) continue;
		
			$value['a_subjectall'] = $value['a_subject'];
			if(!empty($value['a_subject']) && !empty($paramarr['subjectlen'])) {
				$value['a_subject'] = cutstr($value['a_subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
			}
			//附件处理
			if(!empty($value['a_thumbpath'])) $value['a_thumbpath'] = A_URL.'/'.$value['a_thumbpath'];
			if(!empty($value['a_filepath'])) $value['a_filepath'] = A_URL.'/'.$value['a_filepath'];
			if(empty($value['a_thumbpath'])) {
			if(empty($value['a_filepath'])) {
				$value['a_thumbpath'] = S_URL.'/images/base/nopic.gif';
				} else {
					$value['a_thumbpath'] = $value['a_filepath'];
				}
			}
			if(empty($value['a_filepath'])) $value['a_filepath'] = $value['a_thumbpath'];
			$attacharr[$value['a_itemid']] = $value;
			$spicnews[$value['a_itemid']] = array_merge($spicnews[$value['a_itemid']], $value);
		}
	}
	//最新资讯
	$query = $_SGLOBAL['db']->query("SELECT i.*, sn.* FROM ".tname('spaceitems')." i, ".tname('spacenews')." sn  WHERE i.itemid = sn.itemid AND i.folder=1 ORDER BY i.dateline DESC LIMIT 0,3");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['subject'] = cutstr($value['subject'], 40);
		if(!empty($value['styletitle'])) {
			$value['subject'] = '<span style=\''.mktitlestyle($value['styletitle']).'\'>'.$value['subject'].'</span>';
		}
		$value['message'] = strip_tags(trim($value['message']));
		$value['message'] = trim(cutstr($value['message'], 30));
		$value['url'] = gethtmlurl2($value['catid']).'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
		$newnews1[] = $value;
	}
	
	//<!--各分类最新资讯列表-->
	$subnewlist = array();
	foreach($category as $ccat){
		$tparam['catids'] = getdotstring($ccat['subcatid'], 'int');
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE folder = 1 AND catid IN (".$tparam['catids'].") ORDER BY dateline DESC LIMIT 0, 6");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['subject'] = cutstr($value['subject'], 36, 0);
			//标题样式
			if(!empty($value['styletitle'])) {
				$value['subject'] = '<span style=\''.mktitlestyle($value['styletitle']).'\'>'.$value['subject'].'</span>';
			}
			$value['url'] = gethtmlurl2($value['catid']).'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
			$subnewlist[$value['catid']][] = $value;
		}
	}
	
	//热门标签
	$hottag = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tags')." ORDER BY spacenewsnum DESC LIMIT 0,30");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['url'] = geturl('action/tag/tagid/'.$value['tagid']);
		$hottag[] = $value;
	}
	
	//最新评论
	$newnews = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE folder=1 ORDER BY lastpost DESC LIMIT 0,10");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['subject'] = cutstr($value['subject'], 26, 0);
		//标题样式
		if(!empty($value['styletitle'])) {
			$value['subject'] = '<span style=\''.mktitlestyle($value['styletitle']).'\'>'.$value['subject'].'</span>';
		}
		$value['url'] = gethtmlurl2($value['catid']).'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
		$newnews[] = $value;
	}
	//月度评论
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE folder=1 AND lastpost >= .".($_SGLOBAL['timestamp']-2592000)." ORDER BY replynum DESC LIMIT 0,10");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['subject'] = cutstr($value['subject'], 26, 0);
		//标题样式
		if(!empty($value['styletitle'])) {
			$value['subject'] = '<span style=\''.mktitlestyle($value['styletitle']).'\'>'.$value['subject'].'</span>';
		}
		$value['url'] = gethtmlurl2($value['catid']).'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
		$replyhot[] = $value;
	}
	$fp = fopen($index_path, 'w');
	//清理输出
	obclean();
	include template('newsindex');
	$content = ob_get_contents();
	obclean();
	fwrite($fp, $content);
	fclose($fp);
	showmessage('make_index_success', $theurl);
} elseif(submitcheck('makeallsubmit')) {
	//静态文件名前缀
	if(!empty($_POST['pre_html'])) {
		if(!preg_match('/^[0-9a-z_]+$/i', $_POST['pre_html'])) {
			showmessage('pre_html_error');
		}
	}
	//全部更新
	$tcatidarr = array();
	if($catid == 0) {
		$_SGLOBAL['db']->query("UPDATE ".tname('categories')." SET perlisthtml = '$perlisthtml', pre_html='$_POST[pre_html]'");
		$query = $_SGLOBAL['db']->query("SELECT catid FROM ".tname('categories'));
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			$tcatidarr[] = $value['catid'];
		}
	} else {
		if($_POST['update_makeupid'] == 1) {
			$_SGLOBAL['db']->query("UPDATE ".tname('categories')." SET perlisthtml = '$perlisthtml', pre_html='$_POST[pre_html]' WHERE catid='$catid' OR upid='$catid'");
			$query = $_SGLOBAL['db']->query("SELECT catid FROM ".tname('categories')." WHERE catid='$catid' OR upid='$catid'");
			while($value = $_SGLOBAL['db']->fetch_array($query)){
				$tcatidarr[] = $value['catid'];
			}
		} else {
			$_SGLOBAL['db']->query("UPDATE ".tname('categories')." SET perlisthtml = '$perlisthtml', pre_html='$_POST[pre_html]' WHERE catid='$catid'");
			$query = $_SGLOBAL['db']->query("SELECT catid FROM ".tname('categories')." WHERE catid='$catid'");
			$value = $_SGLOBAL['db']->fetch_array($query);
			$tcatidarr[] = $value['catid'];
		}
	}
	if(!empty($tcatidarr) && is_array($tcatidarr)) {
		//取出其中一个catid生成html
		$catid = array_pop($tcatidarr);
		$jump = 'no';
		if(!empty($tcatidarr)) {
			//保存到临时文件下,用于跳转
			$jump = 'yes';
			$cachefile = S_ROOT.'./data/temp/catid.cache.php';
			$text = '$tcatidarr = '.arrayeval($tcatidarr).';';
			writefile($cachefile, $text, 'php');
		}
		updatehtmlpathcache();
		$_SGLOBAL['db']->query("DELETE FROM ".tname('spacepages')." WHERE catid='$catid'");
		$query = $_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('spaceitems')." WHERE catid='$catid'");
		$countnum = $_SGLOBAL['db']->result($query, 0);
		$nexturl = CPURL.'?action=makehtml&op=makecathtml&perlisthtml='.$perlisthtml.'&catid='.$catid.'&total='.$countnum.'&jump='.$jump;
		echo '<table style="width:98%;padding:0.2em;border: 1px solid #698CC3;font-size:14px;font-weight:bold;font-family: Trebuchet MS, Lucida Console, Lucida Sans, sans-serif;" align="center"><tr><td><a href='.$nexturl.'>'.$alang['making_page_info_0'].$batch_makehtml_count.$alang['making_page_info_1'].'0 / '.($perlisthtml).$alang['making_page_info_2'].'</a></td><td width="35%"><a href="'.$theurl.'&op=make">'.$alang['making_page_info_3'].'</a></td></tr></table>';
		jumpurl($nexturl, 1);
		exit;
	}
} elseif(submitcheck('makeitemidsubmit')) {
	$itemid1 = intval($_POST['itemid1']);
	$itemid2 = intval($_POST['itemid2']);
	$wheresql = '';
	if($itemid1) $wheresql .= " AND itemid >= '$itemid1' ";
	if($itemid2) $wheresql .= " AND itemid <= '$itemid2' ";
	$query = $_SGLOBAL['db']->query("SELECT itemid, catid FROM ".tname('spaceitems')." WHERE  type='news' $wheresql");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newidarr[] = $value['itemid'];
		$catidarr[$value['catid']][] = $value['itemid'];
	}
	$pagearr = getlistpage($catidarr);
	//保存到临时文件下
	$cachefile = S_ROOT.'./data/temp/pagearr.cache.php';
	$text = '$pagearr = '.arrayeval($pagearr).';';
	writefile($cachefile, $text, 'php');
				
	$cachefile = S_ROOT.'./data/temp/catidarr.cache.php';
	$text = '$catidarr = '.arrayeval($catidarr).';';
	writefile($cachefile, $text, 'php');
	$theurl = 'admincp.php?action=makehtml&op=updatehtml&do=updatecontenthtml';
}

$newnews = $hotnews = $hotnews2 = $newcomments = $picnews =  array();

//批量静态页生成跳转
if($op == 'makecathtml') {

	$countnum = intval($_GET['total']);

	$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
	$start = ($page - 1)*$perlisthtml;
	//列表总页数
	$listpages = @ceil($countnum/$perlisthtml);
	//判断是否刚好是分页数的整数倍
	$ischeck = $countnum % $perlisthtml > 0 ? true : false;
	$listpages = $ischeck ?  $listpages - 1 : $listpages;
	$listpages = $listpages < 1 ? 1 : $listpages;

	//静态链接处理
	if(!empty($catarr[$catid]['domain']) && substr($catarr[$catid]['domain'], 0, 7) == 'http://') {
		define('S_HTML_URL', $catarr[$catid]['domain']);
	} else {
		if(!empty($_SCONFIG['index_domain']) && substr($_SCONFIG['index_domain'], 0, 7) == 'http://') {
			define('S_HTML_URL', $_SCONFIG['index_domain'].'/'.substr($catarr[$catid]['htmlpath'], 2));
		} else {
			define('S_HTML_URL', S_URL_ALL.'/'.substr($_SCONFIG['newspath'], 2).'/'.substr($catarr[$catid]['htmlpath'], 2));
		}
	}
	$htmlpath = S_HTML_ROOT.'/'.substr($catarr[$catid]['htmlpath'], 2);
	if(!is_dir($htmlpath)) {
		@mkdir($htmlpath);  
	}
	$htmlurl = S_HTML_URL;

	$query = $_SGLOBAL['db']->query('SELECT f.*, ff.name AS upname FROM '.tname('categories').' f LEFT JOIN '.tname('categories').' ff ON ff.catid=f.upid WHERE f.catid=\''.$catid.'\'');
	$thecat = $_SGLOBAL['db']->fetch_array($query);
	
	$param = array();
	//内容页block处理
	$param['catids'] = getdotstring($thecat['subcatid'], 'int');

	$newnews = getnewnews($param['catids']);
	$hotnews = gethotnews();
	$hotnews2 = gethotnews2($param['catids']);
	$newcomments = getnewcommnet($param['catids']);
	$picnews = getpicnews($thecat['catid']);
	//如果是第一页
	if($page == $listpages) { 
		//子分类处理
		$subarr = $subnewlist = array();
		$subarr = getsubarr($thecat['catid']);
		if(!empty($subarr)) {
			foreach ($subarr as $cvalue) {
				$tparam['catids'] = getdotstring($cvalue['subcatid'], 'int');
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE folder = 1 AND catid IN (".$tparam['catids'].") ORDER BY dateline DESC LIMIT 0, 6");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$value['subject'] = cutstr($value['subject'], 36, 0);
					//标题样式
					if(!empty($value['styletitle'])) {
						$value['subject'] = '<span style=\''.mktitlestyle($value['styletitle']).'\'>'.$value['subject'].'</span>';
					}
					$value['url'] = gethtmlurl2($value['catid']).'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
					$subnewlist[] = $value;
				}
			}
		}
	}
	//查看内容处理
	$row = $attacharr = $news = $newslist = array();
	$perpage = '';
	if($ischeck && $page == $listpages) {
		$perpage = $perlisthtml * 2;
	} else {
		$perpage = $perlisthtml;
	}

	//标题处理
	$query =  $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE catid='$catid' ORDER BY itemid LIMIT $start, $perpage");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$itemarr[] = $value['itemid'];
		$row[$value['itemid']] = $value; 
	}
	
	$itemstr = simplode($itemarr);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spacenews')." WHERE itemid IN (".$itemstr.") ORDER BY itemid, pageorder");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$rowmsg[$value['itemid']][] = array_merge($value, $row[$value['itemid']]);
	}
	//逆向排序，时间在前面
	krsort($rowmsg);
	//此分类下的附件
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('attachments')." WHERE catid='$catid'");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		//附件处理
		$attacharr[$value['itemid']][] = $value;
	}
	$sitemid = $eitemid = '';
	
	foreach($rowmsg as $itemid => $multi_news) {
		//如果文章有分页
		$sitemid = empty($sitemid) ? $itemid : ($sitemid > $itemid ? $sitemid : $itemid);
		$eitemid = empty($eitemid) ? $itemid : ($eitemid < $itemid ? $eitemid : $itemid);
		$pages = count($multi_news);
		$commentlist = $relativeitem = array();
		if($pages > 1) {
			foreach($multi_news as $key=>$news) {
				$syear = sgmdate($news['dateline'], 'Y');
				$smoon = sgmdate($news['dateline'], 'n');
				//开始分页链接
				$furl = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'.html';
				//结束分页链接
				$eurl = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'_'.$pages.'.html';
				$multipage = '<div class="pages"><div>';
				//上一页
				$perurl = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'_'.$key.'.html';

				//下一页
				$nexturl = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'_'.($key+2).'.html';
				$multipage .= ((($key+1) > 1 && $pages > 10) ? '<a href="'.$furl.'">1...</a>' : '').(($key+1) > 1 ? '<a class="prev" href="'.$perurl.'">'.$lang['pre_page'].'</a>' : '');
				for($i = 1; $i <= $pages; $i++) {
					//正常连接
					if($i == 1) {
						$url = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'.html';
					} else {
						$url = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'_'.$i.'.html';
					}
					$multipage .= $i == ($key+1) ? '<strong>'.$i.'</strong>' : '<a href="'.$url.'">'.$i.'</a>';
				}
				$multipage .= (($key+1) < $pages && ($key+1) < $_SGLOBAL['maxpages'] ? '<a href="'.$eurl.'" target="_self">...'.$pages.'</a>' : '').
					(($key+1) < $pages ? '<a class="next" href="'.$nexturl.'">'.$lang['next_page'].'</a>' : '');
				$multipage .= '</div></div>';
				$news = makenews($news, $attacharr, $itemid);
				//查看页相关资讯处理
				if(!empty($news['relativeitemids'])) {
					$news['itemids'] = getdotstring($news['relativeitemids'], 'int');
					$rquery = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE itemid IN(".$news['itemids'].") ORDER BY dateline DESC LIMIT 0, 20");
					while($value = $_SGLOBAL['db']->fetch_array($rquery)) {
						//静态链接处理
						$value['url'] = $htmlurl.'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
						$relativeitem[] = $value;
					}
				}
				
				//评论处理
				$listcount = $news['replynum'];
				$_SCONFIG['viewspace_pernum'] = intval($_SCONFIG['viewspace_pernum']);
				if($listcount) {
					$query = $_SGLOBAL['db']->query('SELECT c.* FROM '.tname('spacecomments').' c WHERE c.itemid=\''.$news['itemid'].'\' ORDER BY c.dateline DESC LIMIT 0, '.$_SCONFIG['viewspace_pernum']);
					while ($value = $_SGLOBAL['db']->fetch_array($query)) {
						$value['message'] = snl2br($value['message']);
						if(empty($value['author'])) $value['author'] = 'Guest';
						$commentlist[] = $value;
					}
				}
				//子导航
				if(empty($newtagarr)) $newtagarr = array($news['subject'], $lang['news']);
				$keywords = implode(',', $newtagarr);
				
				$guidearr = array();
				$guidearr[] = array('url' => S_URL_ALL.'/'.substr($_SCONFIG['newspath'], 2), 'name' => $channels['menus']['news']['name']);
				if(!empty($thecat['upname'])) {
					$guidearr[] = array('url' =>S_HTML_URL.'/'.substr($catarr[$value['catid']]['htmlpath'], 2), 'name' => $thecat['upname']);
				}
				$guidearr[] = array('url' => $htmlurl, 'name' => $thecat['name']);
				$title = $news['subject'].' - '.$_SCONFIG['sitename'];

				//生成HTML文件
				if(!is_dir($htmlpath.'/'.$syear)) {
					@mkdir($htmlpath.'/'.$syear);
				}
				if(!is_dir($htmlpath.'/'.$syear.'/'.$smoon)) {
					@mkdir($htmlpath.'/'.$syear.'/'.$smoon);
				}

				if($key == 0) {
					$fp = fopen($htmlpath.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'.html', 'w');
				} else {
					$fp = fopen($htmlpath.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'_'.($key+1).'.html', 'w');
				}
				//清理输出
				obclean();
				include template('newsview');
				$content = ob_get_contents();
				obclean();
				fwrite($fp, $content);
				fclose($fp);
				unset($multipage);
				//列表处理
				if($news['newsurl']){
					$newslist[$news['itemid']]['url'] = $news['newsurl'];
				} else {
					$newslist[$news['itemid']]['url'] = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'_'.($key+1).'.html';
				}
				$newslist[$news['itemid']]['subject'] = $news['subject'];
				$newslist[$news['itemid']]['dateline'] = $news['dateline'];
			}
		} else {
			$news = makenews($multi_news[0], $attacharr, $itemid);
			//评论处理
			$listcount = $news['replynum'];
			$_SCONFIG['viewspace_pernum'] = intval($_SCONFIG['viewspace_pernum']);
			if($listcount) {
				$query = $_SGLOBAL['db']->query('SELECT c.* FROM '.tname('spacecomments').' c WHERE c.itemid=\''.$news['itemid'].'\' ORDER BY c.dateline DESC LIMIT 0, '.$_SCONFIG['viewspace_pernum']);
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$value['message'] = snl2br($value['message']);
					if(empty($value['author'])) $value['author'] = 'Guest';
					$commentlist[] = $value;
				}
			}
			//查看页相关资讯处理
			if(!empty($news['relativeitemids'])) {
				$news['itemids'] = getdotstring($news['relativeitemids'], 'int');
				$rquery = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE itemid IN(".$news['itemids'].") ORDER BY dateline DESC LIMIT 0, 20");
				while($value = $_SGLOBAL['db']->fetch_array($rquery)) {
					//静态链接处理
					$value['url'] = $htmlurl.'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
					$relativeitem[] = $value;
				}
			}
			
			//子导航
			if(empty($newtagarr)) $newtagarr = array($news['subject'], $lang['news']);
			$keywords = implode(',', $newtagarr);
				
			$guidearr = array();
			$guidearr[] = array('url' => S_URL_ALL.'/'.substr($_SCONFIG['newspath'], 2), 'name' => $channels['menus']['news']['name']);
			if(!empty($thecat['upname'])) {
				$guidearr[] = array('url' =>gethtmlurl2($thecat['upid']), 'name' => $thecat['upname']);
			}
			$guidearr[] = array('url' => $htmlurl, 'name' => $thecat['name']);
			$title = strip_tags($news['subject']).' - '.$_SCONFIG['sitename'];

			//生成内容HTML文件
			$syear = sgmdate($news['dateline'], 'Y');
			$smoon = sgmdate($news['dateline'], 'n');
			if(!is_dir($htmlpath.'/'.$syear)) {
				@mkdir($htmlpath.'/'.$syear);
			}
			if(!is_dir($htmlpath.'/'.$syear.'/'.$smoon)) {
				@mkdir($htmlpath.'/'.$syear.'/'.$smoon);
			}
			$fp = fopen($htmlpath.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'.html', 'w');
			//清理输出
			obclean();
			include template('newsview');
			$content = ob_get_contents();
			obclean();
			fwrite($fp, $content);
			fclose($fp);

			//列表处理
			$newslist[$news['itemid']]['subject'] = $news['subject'];
			$newslist[$news['itemid']]['dateline'] = $news['dateline'];
			if($news['newsurl']){
				$newslist[$news['itemid']]['url'] = $news['newsurl'];
			} else {
				$newslist[$news['itemid']]['url'] = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'.html';
			}
		}
	}
	unset($guidearr);

	//列表页的子导航
	$title = $thecat['name'].' - '.$_SCONFIG['sitename'];
	$keywords = $thecat['name'].','.$lang[$thecat['type']];
	$description = $thecat['name'].','.$lang[$thecat['type']];
	
	$guidearr = array();
	$guidearr[] = array('url' => S_URL_ALL.'/'.substr($_SCONFIG['newspath'], 2), 'name' => $channels['menus']['news']['name']);
	if(!empty($thecat['upname'])) {
		$guidearr[] = array('url' => gethtmlurl2($thecat['upid']), 'name' => $thecat['upname']);
	}
	$guidearr[] = array('url' => $htmlurl, 'name' => $thecat['name']);

	//清理输出
	//列表分页处理	
	if($listpages > 1) {
		$multipage = '<div class="pages"><div>';
		if($page == 1 ) {
			//最后一个列表页(分页只有上一页)
			unset($picnews);
			$multipage .= '<a class="prev" href="'.$htmlurl.'/list_'.($page+1).'.html">'.$lang['pre_page'].'</a>';
		} elseif($page == $listpages) {
			//第一个列表页(分页只有下一页)
			$multipage .= '<a class="next" href="'.$htmlurl.'/list_'.($page-1).'.html">'.$lang['next_page'].'</a>';
		} elseif($page == ($listpages-1)) {
			//第二个页面
			unset($picnews);
			$multipage .= '<a class="prev" href="'.$htmlurl.'/index.html">'.$lang['pre_page'].'</a><a class="next" href="'.$htmlurl.'/list_'.($page-1).'.html">'.$lang['next_page'].'</a>';
		} else {
			unset($picnews);
			$multipage .= '<a class="prev" href="'.$htmlurl.'/list_'.($page+1).'.html">'.$lang['pre_page'].'</a><a class="next" href="'.$htmlurl.'/list_'.($page-1).'.html">'.$lang['next_page'].'</a>';
		}
		$multipage .= '</div></div>';
	}
	
	//记录itemid对应的列表页
	$query = $_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('spacepages')." WHERE catid='$catid' AND pageid='$page'");
	$isupdate = $_SGLOBAL['db']->result($query);
	if($isupdate) {
		$_SGLOBAL['db']->query("UPDATE ".tname('spacepages')." SET sitemid='$sitemid', eitemid='$eitemid' WHERE catid='$catid' AND pageid='$page'");
	} else {
		$_SGLOBAL['db']->query("INSERT INTO ".tname('spacepages')." (`pageid`, `sitemid`, `eitemid`, `catid`) VALUES ('$page', '$sitemid', '$eitemid', '$catid')");	
	}

	$fp = fopen($htmlpath.'/list_'.$page.'.html', 'w');
	obclean();
	include template('newscategory');
	$content = ob_get_contents();
	obclean();
	fwrite($fp, $content);
	fclose($fp);
	if($page == $listpages) {
		$fp = fopen($htmlpath.'/index.html', 'w');
		fwrite($fp, $content);
		fclose($fp);
	}
	unset($multipage);
	if($page < $listpages) {
		++$page;
		$nexturl = CPURL.'?action=makehtml&op=makecathtml&perlisthtml='.$perlisthtml.'&catid='.$catid.'&total='.$countnum.'&page='.$page;
		echo '<table style="width:98%;padding:0.2em;border: 1px solid #698CC3;font-size:14px;font-weight:bold;font-family: Trebuchet MS, Lucida Console, Lucida Sans, sans-serif;" align="center"><tr><td><a href='.$nexturl.'>'.$alang['making_page_info_0'].$total.$alang['making_page_info_1'].$start.' / '.($start+$perlisthtml).$alang['making_page_info_2'].'</a></td><td width="35%"><a href="'.$theurl.'&op=make">'.$alang['making_page_info_3'].'</a></td></tr></table>';
		jumpurl($nexturl, 1);
	} else {
		if($_GET['jump'] == 'yes') {
			include_once(S_ROOT.'/data/temp/catid.cache.php');
			//取出其中一个catid生成html
			$catid = array_pop($tcatidarr);
			$jump = 'no';
			if(!empty($tcatidarr)) {
				//保存到临时文件下,用于跳转
				$jump = 'yes';
				$cachefile = S_ROOT.'./data/temp/catid.cache.php';
				$text = '$tcatidarr = '.arrayeval($tcatidarr).';';
				writefile($cachefile, $text, 'php');
			}
			updatehtmlpathcache();
			$_SGLOBAL['db']->query("DELETE FROM ".tname('spacepages')." WHERE catid='$catid'");
			$query = $_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('spaceitems')." WHERE catid='$catid'");
			$countnum = $_SGLOBAL['db']->result($query, 0);
			$nexturl = CPURL.'?action=makehtml&op=makecathtml&perlisthtml='.$perlisthtml.'&catid='.$catid.'&total='.$countnum.'&jump='.$jump;
			echo '<table style="width:98%;padding:0.2em;border: 1px solid #698CC3;font-size:14px;font-weight:bold;font-family: Trebuchet MS, Lucida Console, Lucida Sans, sans-serif;" align="center"><tr><td><a href='.$nexturl.'>'.$alang['making_page_info_0'].$batch_makehtml_count.$alang['making_page_info_1'].'0 / '.($perlisthtml).$alang['making_page_info_2'].'</a></td><td width="35%"><a href="'.$theurl.'&op=make">'.$alang['making_page_info_3'].'</a></td></tr></table>';
			jumpurl($nexturl, 1);
			exit;
		}
		showmessage('make_html_success', $theurl);
	}
} elseif($op == 'updatehtml') {
	include_once(S_ROOT.'/data/temp/catidarr.cache.php');
		
	//处理对应的分类block内容
	//静态变量, 在跳转中减少查询
	static $cnewnews, $chotnews, $chotnews2, $cnewcomments, $cpicnews, $csubarr;
	$newidarr = array();
	$itemidstr = '';
	if(!empty($catidarr) && is_array($catidarr)) {
		foreach($catidarr as $catid=>$itemidarr) {
			$query = $_SGLOBAL['db']->query('SELECT f.*, ff.name AS upname FROM '.tname('categories').' f LEFT JOIN '.tname('categories').' ff ON ff.catid=f.upid WHERE f.catid=\''.$catid.'\'');
			$thecat = $_SGLOBAL['db']->fetch_array($query);
					
			$param = array();
			//内容页block处理
			$param['catids'] = getdotstring($thecat['subcatid'], 'int');
			if(empty($cnewnews[$catid])) $cnewnews[$catid] = getnewnews($param['catids']);
			if(empty($chotnews[$catid])) $chotnews[$catid] = gethotnews();
			if(empty($chotnews2[$catid])) $chotnews2[$catid] = gethotnews2($param['catids']);
			if(empty($cnewcomments[$catid])) $cnewcomments[$catid] = getnewcommnet($param['catids']);
			if(empty($cpicnews[$catid]))  $cpicnews[$catid] = getpicnews($thecat['catid']);
			if(empty($csubarr[$catid])) $csubarr[$catid] = getsubarr($thecat['catid']);
			$newidarr = array_merge($itemidarr, $newidarr);
		}
	}
	$itemidstr = simplode($newidarr);
	if($_GET['do'] == 'updatecontenthtml') {
		//内容页更新
		$query = $_SGLOBAL['db']->query("SELECT si.*, sp.* FROM ".tname('spaceitems')." si, ".tname('spacenews')." sp WHERE si.itemid = sp.itemid AND si.itemid IN (".$itemidstr.") ORDER BY sp.pageorder");
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
				$row[$value['itemid']][] = $value;
		}
		ksort($row);
		foreach($row as $itemid => $multi_news) {
			//如果文章有分页
			$newnews = $cnewnews[$multi_news[0]['catid']];
			$hotnews = $chotnews[$multi_news[0]['catid']];
			$picnews = $cpicnews[$multi_news[0]['catid']];
			//静态链接处理
			if(!empty($catarr[$multi_news[0]['catid']]['domain'])) {
				define('S_HTML_URL', $catarr[$multi_news[0]['catid']]['domain']);
			} else {
				define('S_HTML_URL', S_URL_ALL.'/'.substr($_SCONFIG['newspath'], 2).'/'.substr($catarr[$multi_news[0]['catid']]['htmlpath'], 2));    
			}
			$htmlpath = S_HTML_ROOT.'/'.substr($catarr[$multi_news[0]['catid']]['htmlpath'], 2);
			if(!is_dir($htmlpath)) {
				@mkdir($htmlpath);  
			}
			$htmlurl = S_HTML_URL;
			$commentlist = $relativeitem = array();
			$pages = count($multi_news);
			if($pages > 1) {
				foreach($multi_news as $key=>$news) {
					$syear = sgmdate($news['dateline'], 'Y');
					$smoon = sgmdate($news['dateline'], 'n');
					//开始分页链接
					$furl = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'.html';
					//结束分页链接
					$eurl = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'_'.$pages.'.html';
					$multipage = '<div class="pages"><div>';
					//上一页
					$perurl = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'_'.$key.'.html';
	
					//下一页
					$nexturl = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'_'.($key+2).'.html';
					$multipage .= ((($key+1) > 1 && $pages > 10) ? '<a href="'.$furl.'">1...</a>' : '').(($key+1) > 1 ? '<a class="prev" href="'.$perurl.'">'.$lang['pre_page'].'</a>' : '');
					for($i = 1; $i <= $pages; $i++) {
						//正常连接
						if($i == 1) {
							$url = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'.html';
						} else {
							$url = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'_'.$i.'.html';
						}
						$multipage .= $i == ($key+1) ? '<strong>'.$i.'</strong>' : '<a href="'.$url.'">'.$i.'</a>';
					}
					$multipage .= (($key+1) < $pages && ($key+1) < $_SGLOBAL['maxpages'] ? '<a href="'.$eurl.'" target="_self">...'.$pages.'</a>' : '').
						(($key+1) < $pages ? '<a class="next" href="'.$nexturl.'">'.$lang['next_page'].'</a>' : '');
					$multipage .= '</div></div>';
					
					$news = makenews($news, $attacharr, $itemid);
					//查看页相关资讯处理
					if(!empty($news['relativeitemids'])) {
						$news['itemids'] = getdotstring($news['relativeitemids'], 'int');
						$rquery = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE itemid IN(".$news['itemids'].") ORDER BY dateline DESC LIMIT 0, 20");
						while($value = $_SGLOBAL['db']->fetch_array($rquery)) {
							//静态链接处理
							$value['url'] = $htmlurl.'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
							$relativeitem[] = $value;
						}
					}
					
					//评论处理
					$listcount = $news['replynum'];
					$_SCONFIG['viewspace_pernum'] = intval($_SCONFIG['viewspace_pernum']);
					if($listcount) {
						$query = $_SGLOBAL['db']->query('SELECT c.* FROM '.tname('spacecomments').' c WHERE c.itemid=\''.$news['itemid'].'\' ORDER BY c.dateline DESC LIMIT 0, '.$_SCONFIG['viewspace_pernum']);
						while ($value = $_SGLOBAL['db']->fetch_array($query)) {
							$value['message'] = snl2br($value['message']);
							if(empty($value['author'])) $value['author'] = 'Guest';
							$commentlist[] = $value;
						}
					}
					
					//子导航
					if(empty($newtagarr)) $newtagarr = array($news['subject'], $lang['news']);
					$keywords = implode(',', $newtagarr);
					
					$guidearr = array();
					$guidearr[] = array('url' => S_URL_ALL.'/'.substr($_SCONFIG['newspath'], 2), 'name' => $channels['menus']['news']['name']);
					if(!empty($thecat['upname'])) {
						$guidearr[] = array('url' =>S_HTML_URL.'/'.substr($catarr[$value['catid']]['htmlpath'], 2), 'name' => $thecat['upname']);
					}
					$guidearr[] = array('url' => $htmlurl, 'name' => $thecat['name']);
					$title = $news['subject'].' - '.$_SCONFIG['sitename'];
	
					//生成HTML文件
					if(!is_dir($htmlpath.'/'.$syear)) {
						@mkdir($htmlpath.'/'.$syear);
					}
					if(!is_dir($htmlpath.'/'.$syear.'/'.$smoon)) {
						@mkdir($htmlpath.'/'.$syear.'/'.$smoon);
					}
					if($key == 0) {
						$fp = fopen($htmlpath.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'.html', 'w');
					} else {
						$fp = fopen($htmlpath.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'_'.($key+1).'.html', 'w');
					}
					//清理输出
					obclean();
					include template('newsview');
					$content = ob_get_contents();
					obclean();
					fwrite($fp, $content);
					fclose($fp);
					unset($multipage);
				}
			} else {
				$news = makenews($multi_news[0], $attacharr, $itemid);
				//评论处理
				$listcount = $news['replynum'];
				$_SCONFIG['viewspace_pernum'] = intval($_SCONFIG['viewspace_pernum']);
				if($listcount) {
					$query = $_SGLOBAL['db']->query('SELECT c.* FROM '.tname('spacecomments').' c WHERE c.itemid=\''.$news['itemid'].'\' ORDER BY c.dateline DESC LIMIT 0, '.$_SCONFIG['viewspace_pernum']);
					while ($value = $_SGLOBAL['db']->fetch_array($query)) {
						$value['message'] = snl2br($value['message']);
						if(empty($value['author'])) $value['author'] = 'Guest';
						$commentlist[] = $value;
					}
				}
				//查看页相关资讯处理
				if(!empty($news['relativeitemids'])) {
					$news['itemids'] = getdotstring($news['relativeitemids'], 'int');
					$rquery = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE itemid IN(".$news['itemids'].") ORDER BY dateline DESC LIMIT 0, 20");
					while($value = $_SGLOBAL['db']->fetch_array($rquery)) {
						//静态链接处理
						$value['url'] = $htmlurl.'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
						$relativeitem[] = $value;
					}
				}
				
				//子导航
				if(empty($newtagarr)) $newtagarr = array($news['subject'], $lang['news']);
				$keywords = implode(',', $newtagarr);
					
				$guidearr = array();
				$guidearr[] = array('url' => S_URL_ALL.'/'.substr($_SCONFIG['newspath'], 2), 'name' => $channels['menus']['news']['name']);
				if(!empty($thecat['upname'])) {
					$guidearr[] = array('url' =>gethtmlurl2($thecat['upid']), 'name' => $thecat['upname']);
				}
				$guidearr[] = array('url' => $htmlurl, 'name' => $thecat['name']);
				$title = strip_tags($news['subject']).' - '.$_SCONFIG['sitename'];
	
				//生成内容HTML文件
				$syear = sgmdate($news['dateline'], 'Y');
				$smoon = sgmdate($news['dateline'], 'n');
				if(!is_dir($htmlpath.'/'.$syear)) {
					@mkdir($htmlpath.'/'.$syear);
				}
				if(!is_dir($htmlpath.'/'.$syear.'/'.$smoon)) {
					@mkdir($htmlpath.'/'.$syear.'/'.$smoon);
				}
				$fp = fopen($htmlpath.'/'.$syear.'/'.$smoon.'/'.$catarr[$news['catid']]['pre_html'].$itemid.'.html', 'w');
				//清理输出
				obclean();
				include template('newsview');
				$content = ob_get_contents();
				obclean();
				fwrite($fp, $content);
				fclose($fp);
			}
		}
		unset($guidearr);
		showmessage('update_html_success', 'admincp.php?action=makehtml&op=updatehtml&do=updatelisthtml');
	} elseif($_GET['do'] == 'updatelisthtml') {
		//列表页更新
		include_once(S_ROOT.'/data/temp/pagearr.cache.php');

		if(!empty($pagearr) && is_array($pagearr)) {
			foreach($pagearr as $row) {
				//静态链接处理
				$thecat = array();
				$htmlpath = $htmlurl = '';
				$query = $_SGLOBAL['db']->query('SELECT f.*, ff.name AS upname FROM '.tname('categories').' f LEFT JOIN '.tname('categories').' ff ON ff.catid=f.upid WHERE f.catid=\''.$row['catid'].'\'');
				$thecat = $_SGLOBAL['db']->fetch_array($query);
				if(!empty($catarr[$row['catid']]['domain'])) {
					$htmlurl = $catarr[$row['catid']]['domain'];
				} else {
					$htmlurl = S_URL_ALL.'/'.substr($_SCONFIG['newspath'], 2).'/'.substr($catarr[$row['catid']]['htmlpath'], 2);    
				}
				$htmlpath = S_HTML_ROOT.'/'.substr($catarr[$row['catid']]['htmlpath'], 2);
				if(!is_dir($htmlpath)) {
					@mkdir($htmlpath);  
				}
				$hotnews2 = $chotnews2[$row['catid']];
				$picnews = $cpicnews[$row['catid']];
				$newcomments = $cnewcomments[$row['catid']];

				//子分类处理
				$subnewlist = array();
				if(!empty($csubarr[$row['catid']])) {
					foreach ($csubarr[$row['catid']] as $cvalue) {
						$tparam['catids'] = getdotstring($cvalue['subcatid'], 'int');
						$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE folder = 1 AND catid IN (".$tparam['catids'].") ORDER BY dateline DESC LIMIT 0, 6");
						while ($value = $_SGLOBAL['db']->fetch_array($query)) {
							$value['subject'] = cutstr($value['subject'], 36, 0);
							//标题样式
							if(!empty($value['styletitle'])) {
								$value['subject'] = '<span style=\''.mktitlestyle($value['styletitle']).'\'>'.$value['subject'].'</span>';
							}
							$value['url'] = gethtmlurl2($value['catid']).'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
							$subnewlist[] = $value;
						}
					}
				}
		
				$newslist = array();
				$syear = $smoon = '';
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE catid = '$row[catid]' AND itemid >= '$row[eitemid]'  AND itemid <= '$row[sitemid]' ORDER BY dateline DESC");
				if($_SGLOBAL['db']->num_rows($query) == 0){
					$newslist[]['subject'] = $alang['page_no_info'];
				} else {
					while($value = $_SGLOBAL['db']->fetch_array($query)){
						//列表处理
						$syear = sgmdate($value['dateline'], 'Y');
						$smoon = sgmdate($value['dateline'], 'n');
						
						//标题样式
						if(!empty($value['styletitle'])) {
							$value['subject'] = '<span style=\''.mktitlestyle($value['styletitle']).'\'>'.$value['subject'].'</span>';
						}
						$newslist[$value['itemid']]['subject'] = $value['subject'];
						$newslist[$value['itemid']]['dateline'] = $value['dateline'];
						if($value['newsurl']){
							$newslist[$value['itemid']]['url'] = $value['newsurl'];
						} else {
							if(!empty($newslist[$value['itemid']]['url'])) {
								//内容分页第一页
								$newslist[$value['itemid']]['url'] = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'_1.html';
							} else {
								$newslist[$value['itemid']]['url'] = $htmlurl.'/'.$syear.'/'.$smoon.'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
							}
						}
					}
				}
				//列表页的子导航
				$title = $thecat['name'].' - '.$_SCONFIG['sitename'];
				$keywords = $thecat['name'].','.$lang[$thecat['type']];
				$description = $thecat['name'].','.$lang[$thecat['type']];
				
				$guidearr = array();
				$guidearr[] = array('url' => S_URL_ALL.'/'.substr($_SCONFIG['newspath'], 2), 'name' => $channels['menus']['news']['name']);
				if(!empty($thecat['upname'])) {
					$guidearr[] = array('url' => gethtmlurl2($thecat['upid']), 'name' => $thecat['upname']);
				}
				$guidearr[] = array('url' => $htmlurl, 'name' => $thecat['name']);
				$perlisthtml = $catarr[$row['catid']]['perlisthtml'];
				//列表总页数
				$listpages = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT pageid FROM ".tname('spacepages')." WHERE catid='$row[catid]' ORDER BY pageid DESC LIMIT 1"), 0);
				//列表分页处理	

				if($listpages > 1) {
					$multipage = '<div class="pages"><div>';
					if($row['pageid'] == 1 ) {
						//最后一个列表页(分页只有上一页)
						unset($picnews);
						$multipage .= '<a class="prev" href="'.$htmlurl.'/list_'.($row['pageid']+1).'.html">'.$lang['pre_page'].'</a>';
					} elseif($row['pageid'] == $listpages) {
						//第一个列表页(分页只有下一页)
						$multipage .= '<a class="next" href="'.$htmlurl.'/list_'.($row['pageid']-1).'.html">'.$lang['next_page'].'</a>';
					} elseif($row['pageid'] == ($listpages-1)) {
						//第二个页面
						unset($picnews);
						$multipage .= '<a class="prev" href="'.$htmlurl.'/index.html">'.$lang['pre_page'].'</a><a class="next" href="'.$htmlurl.'/list_'.($row['pageid']-1).'.html">'.$lang['next_page'].'</a>';
					} else {
						unset($picnews);
						$multipage .= '<a class="prev" href="'.$htmlurl.'/list_'.($row['pageid']+1).'.html">'.$lang['pre_page'].'</a><a class="next" href="'.$htmlurl.'/list_'.($row['pageid']-1).'.html">'.$lang['next_page'].'</a>';
					}
					$multipage .= '</div></div>';
				}
				//清理输出
				$fp = fopen($htmlpath.'/list_'.$row['pageid'].'.html', 'w');
				obclean();
				include template('newscategory');
				$content = ob_get_contents();
				obclean();
				fwrite($fp, $content);
				fclose($fp);

				if($row['pageid'] == $listpages) {
					$fp = fopen($htmlpath.'/index.html', 'w');
					fwrite($fp, $content);
					fclose($fp);
				}
			}
		}
	}
 	unset($cnewnews);
 	unset($chotnews);
 	unset($chotnews2);
 	unset($cnewcomments);
 	unset($cpicnews);
 	unset($csubarr);
 	showmessage('do_success', 'admincp.php?action=spacenews');
}
//显示导航菜单

print<<<END
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>$alang[admincp_header_html]</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td$activearr[makeindex]><a href="$theurl&op=makeindex">$alang[make_index_html]</a></td>
					<td$activearr[makeall]><a href="$theurl&op=makeall">$alang[make_html_all]</a></td>
					<td$activearr[makeitemid]><a href="$theurl&op=makeitemid">$alang[make_html_for_itemid]</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table cellspacing="2" cellpadding="2" class="helptable">
<tr><td><ul>
$alang[admincp_html_note]
</ul></td></tr>
</table>
END;

if($op == 'makeindex') {

	$htmlopen = array('0'=>'', '1'=>'');
	if(empty($_SCONFIG['htmlopen'])) $_SCONFIG['htmlopen'] = 0;
	$htmlopen[$_SCONFIG['htmlopen']] = 'checked="true"';
	
	if(empty($_SCONFIG['index_path'])) $_SCONFIG['index_path'] = './index.html';
	$formhash = formhash();
	print<<<END
	<form method="post" name="theform" id="theform" action="$theurl">
	<input type="hidden" name="formhash" value="$formhash">
	<table cellspacing="0" cellpadding="0" width="100%" class="maintable">
	<tr>
		<th>$alang[admincp_html_opening]</th>
		<td>
			<input type="radio" name="htmlopen" value="0" {$htmlopen[0]} >$alang[do_not_open]
			<input type="radio" name="htmlopen" value="1" {$htmlopen[1]} >$alang[block_forum_allow_1]
		</td>
	</tr>
	<tr>
		<th>$alang[make_index_path]</th>
		<td>
			<input type="input" name="index_path"  size="30" value="$_SCONFIG[index_path]">
		</td>
	</tr>
	<tr>
		<th>$alang[index_domain]</th>
		<td><input type="input"  name="index_domain" value="$_SCONFIG[index_domain]" size="30"></td>
	</tr>
	</table>
	<div class="buttons">
	<input type="hidden" name="indexsubmit" value="true" />
	<input type="submit" name="submit" id="submit" value="$alang[save_setup_submit]" class="submit">
	<input type="reset" name="indexreset" value="$alang[common_reset]">
	</div>
	</form>
	
END;
} elseif($op ==  'makeall') {
	$clistarr = getcategory('news');
	$categorylistarr = array('0'=>array('pre'=>'', 'name'=>$alang['make_all_news']));
	foreach ($clistarr as $key => $value) {
		$categorylistarr[$key] = $value;
	}
	$thevalue = array('perlisthtml'=>'20', 'pre_html'=>'');
	$formhash = formhash();
	print<<<END
	<form method="post" name="theform" id="theform" action="$theurl">
	<input type="hidden" name="formhash" value="$formhash">
	<table cellspacing="0" cellpadding="0" width="100%" class="maintable">
	<tr>
		<th>$alang[news_cat]</th>
		<td>
			<select name="catid">
END;
	foreach($categorylistarr as $key => $value) {
		echo "<option value='$key'>".$value['pre'].$value['name']."</option>";
	}
	print<<<END
			</select>
		</td>
	</tr>
	<tr>
		<th>$alang[perlisthtml]</th>
		<td><input type="input"  name="perlisthtml" value="$thevalue[perlisthtml]" size="10"></td>
	</tr>
	<tr>
		<th>$alang[pre_html]</th>
		<td><input type="input"  name="pre_html" value="$thevalue[pre_html]" size="10"></td>
	</tr>
	<tr>
		<th>$alang[update_makeupid]</th>
		<td>
			<input type="radio" checked="true" name="update_makeupid" value="0" >$alang[update_makeupid_no]
			<input type="radio" name="update_makeupid" value="1" >$alang[update_makeupid_yes]
			
		</td>
	</tr>
	</table>
	<div class="buttons">
	<input type="hidden" name="makeallsubmit" value="true" />
	<input type="submit" name="submit" id="submit" value="$alang[save_setup_submit]" class="submit">
	<input type="reset" name="indexreset" value="$alang[common_reset]">
	</div>
	</form>
END;
} elseif($op == 'makeitemid') {
	$formhash = formhash();
	print<<<END
	<form method="post" name="theform" id="theform" action="$theurl">
	<input type="hidden" name="formhash" value="$formhash">
	<table cellspacing="0" cellpadding="0" width="100%" class="maintable">
	<tr>
		<th>$alang[id_information_designated_areas]</th>
		<td>
			<input type="text" name="itemid1" value="0" size="10"> ~  <input type="text" name="itemid2" value="0" size="10">
		</td>
	</tr>
	</table>
	<div class="buttons">
	<input type="hidden" name="makeitemidsubmit" value="true" />
	<input type="submit" name="submit" id="submit" value="$alang[save_setup_submit]" class="submit">
	<input type="reset" name="indexreset" value="$alang[common_reset]">
	</div>
	</form>
	
END;
}


function sjammer($str) {
	global $_SGLOBAL, $_SCONFIG;

	$randomstr = '';
	for($i = 0; $i < mt_rand(5, 15); $i++) {
		$randomstr .= chr(mt_rand(0, 59)).chr(mt_rand(63, 126));
	}
	return mt_rand(0, 1) ? '<span style="display:none">'.$_SCONFIG['sitename'].$randomstr.'</span>'.$str :
		$str.'<span style="display:none">'.$randomstr.$_SGLOBAL['supe_uid'].'</span>';
}

//处理资讯内容和附件
function makenews($news, $attacharr, $itemid) {
	global $_SGLOBAL, $_SSCONFIG ;
	//附件处理
	if($news['haveattach']) {
		foreach($attacharr[$itemid] as $attach) {
		if(strpos($news['message'], $attach['thumbpath']) === false && strpos($news['message'], $attach['filepath']) === false && strpos($news['message'], 'batch.download.php?aid='.$attach['aid']) === false) {
			$attach['filepath'] = A_URL.'/'.$attach['filepath'];
			$attach['thumbpath'] = A_URL.'/'.$attach['thumbpath'];
			$attach['url'] = S_URL.'/batch.download.php?aid='.$attach['aid'];
			$news['attacharr'][] = $attach;
			}
		}
	}
	if(empty($news['newsauthor'])) $news['newsauthor'] = $news['username'];

	$description = str_replace(array('&nbsp;', "\r", "\n", '\'', '"'), '', cutstr(trim(strip_tags($news['message'])), 200));
				
	//相关文章
	if($_SSCONFIG['newsjammer']) {
		mt_srand((double)microtime() * 1000000);
		$news['message'] = preg_replace("/(\<br\>|\<br\ \/\>|\<br\/\>|\<p\>|\<\/p\>)/ie", "sjammer('\\1')", $news['message']);
	}
	$newtagarr = array();
	if(!empty($news['includetags'])) {	
		$newtagarr = explode("\t", $news['includetags']);
		if(!empty($_SCONFIG['allowtagshow'])) $news['message'] = tagshow($news['message'], $newtagarr);
	}
	$relativetagarr = array();
	if(!empty($news['relativetags'])) {	
		$relativetagarr = unserialize($news['relativetags']);
	}
				
	//自定义字段
	$news['custom'] = array('name'=>'', 'key'=>array(), 'value'=>array());
	if(!empty($news['customfieldid'])) {
		$news['custom']['value'] = unserialize($news['customfieldtext']);
		if(!empty($news['custom']['value'])) {
			foreach ($news['custom']['value'] as $key => $value) {
				if(is_array($value)) {
						$news['custom']['value'][$key] = implode(', ', $value);
				}
			}
		}
		$query = $_SGLOBAL['db']->query('SELECT name, customfieldtext FROM '.tname('customfields').' WHERE customfieldid=\''.$news['customfieldid'].'\'');
		$value = $_SGLOBAL['db']->fetch_array($query);
		$news['custom']['name'] = $value['name'];
		$news['custom']['key'] = unserialize($value['customfieldtext']);

	}
	//标题样式
	if(!empty($news['styletitle'])) {
		$news['subject'] = '<span style=\''.mktitlestyle($news['styletitle']).'\'>'.$news['subject'].'</span>';
	}
	return $news;
}

function gethtmlurl2($catid) {
	global $catarr, $_SCONFIG;
	if(!empty($catarr[$catid]['domain']) && substr($catarr[$catid]['domain'], 0, 7) == 'http://') {
		return $catarr[$catid]['domain'];
	} else {
		if(!empty($_SCONFIG['index_domain']) && substr($_SCONFIG['index_domain'], 0, 7) == 'http://') {
			return $_SCONFIG['index_domain'].'/'.substr($catarr[$catid]['htmlpath'], 2);
		} else {
			return S_URL_ALL.'/'.substr($_SCONFIG['newspath'], 2).'/'.substr($catarr[$catid]['htmlpath'], 2);
		}
	}
}

//最新报道
function getnewnews($catids) {
	global $_SGLOBAL, $catarr;
	//最新报道
	$newnews = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE catid IN (".$catids.") AND folder = 1 ORDER BY dateline DESC LIMIT 0, 10");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['subject'] = cutstr($value['subject'], 26, 0);
		//标题样式
		if(!empty($value['styletitle'])) {
			$value['subject'] = '<span style=\''.mktitlestyle($value['styletitle']).'\'>'.$value['subject'].'</span>';
		}
		$value['url'] = gethtmlurl2($value['catid']).'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
		$newnews[] = $value;
	}
	return $newnews;
}

//本月精彩
function gethotnews() {
	global $_SGLOBAL, $catarr;
	//本月精彩推荐
	$hotnews = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE folder =1 AND digest IN (1,2,3) AND dateline >= ".($_SGLOBAL['timestamp'] - 2592000)." ORDER BY viewnum DESC, dateline DESC LIMIT 0, 10");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['subject'] = cutstr($value['subject'], 30, 0);
		//标题样式
		if(!empty($value['styletitle'])) {
			$value['subject'] = '<span style=\''.mktitlestyle($value['styletitle']).'\'>'.$value['subject'].'</span>';
		}
		$value['url'] = gethtmlurl2($value['catid']).'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
		$hotnews[] = $value;
	}
	return $hotnews;
}

//图文资讯
function getpicnews($catid) {
	global $_SGLOBAL, $catarr;
	$aids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE catid = '$catid' AND folder = 1 AND picid !=0 ORDER BY lastpost DESC LIMIT 0, 12");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['subject'] = cutstr($value['subject'], 12, 0);
		$value['url'] = gethtmlurl2($value['catid']).'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
		if($value['picid'] && $value['hash']) {
				$aids[] = $value['picid'];
		}
		$picnews[$value['itemid']] = $value;
	}
	if(!empty($aids)) {
		$_SGLOBAL['attachsql'] = 'a.aid AS a_aid, a.type AS a_type, a.itemid AS a_itemid, a.uid AS a_uid, a.dateline AS a_dateline, a.filename AS a_filename, a.subject AS a_subject, a.attachtype AS a_attachtype, a.isimage AS a_isimage, a.size AS a_size, a.filepath AS a_filepath, a.thumbpath AS a_thumbpath, a.downloads AS a_downloads';
		$query = $_SGLOBAL['db']->query('SELECT '.$_SGLOBAL['attachsql'].' FROM '.tname('attachments').' a WHERE a.aid IN (\''.implode('\',\'', $aids).'\') ORDER BY a.dateline');
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			//处理
			if(!empty($attacharr[$value['a_itemid']])) continue;
	
			$value['a_subjectall'] = $value['a_subject'];
			if(!empty($value['a_subject']) && !empty($paramarr['subjectlen'])) {
				$value['a_subject'] = cutstr($value['a_subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
			}
			//附件处理
			if(!empty($value['a_thumbpath'])) $value['a_thumbpath'] = A_URL.'/'.$value['a_thumbpath'];
			if(!empty($value['a_filepath'])) $value['a_filepath'] = A_URL.'/'.$value['a_filepath'];
			if(empty($value['a_thumbpath'])) {
			if(empty($value['a_filepath'])) {
				$value['a_thumbpath'] = S_URL.'/images/base/nopic.gif';
				} else {
					$value['a_thumbpath'] = $value['a_filepath'];
				}
			}
			if(empty($value['a_filepath'])) $value['a_filepath'] = $value['a_thumbpath'];
			$attacharr[$value['a_itemid']] = $value;
			$picnews[$value['a_itemid']] = array_merge($picnews[$value['a_itemid']], $value);
		}
	}
	return $picnews;
}

//最新评论
function getnewcommnet($catids) {
	global $_SGLOBAL, $catarr;

	$newcomments = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE folder = 1 AND catid IN (".$catids.") ORDER BY dateline DESC LIMIT 0, 10");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['subject'] = cutstr($value['subject'], 26, 0);
		//标题样式
		if(!empty($value['styletitle'])) {
			$value['subject'] = '<span style=\''.mktitlestyle($value['styletitle']).'\'>'.$value['subject'].'</span>';
		}
		$value['url'] = gethtmlurl2($value['catid']).'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
		$newcomments[] = $value;
	}
	
	return $newcomments;
}

//精彩推荐
function gethotnews2($catids) {
	global $_SGLOBAL, $catarr;

	$hotnews2 = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE folder =1 AND digest IN (1,2,3) AND catid IN (".$catids.") ORDER BY viewnum DESC, dateline DESC LIMIT 0, 10");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['subject'] = cutstr($value['subject'], 30, 0);
		//标题样式
		if(!empty($value['styletitle'])) {
			$value['subject'] = '<span style=\''.mktitlestyle($value['styletitle']).'\'>'.$value['subject'].'</span>';
		}
		$value['url'] = gethtmlurl2($value['catid']).'/'.sgmdate($value['dateline'], 'Y').'/'.sgmdate($value['dateline'], 'n').'/'.$catarr[$value['catid']]['pre_html'].$value['itemid'].'.html';
		$hotnews2[] = $value;
	}
	return $hotnews2;
}

//获取子类
function getsubarr($catid) {
	global $_SGLOBAL;
	
	$subarr = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('categories')." WHERE upid='$catid' ORDER BY displayorder LIMIT 0,100");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['url'] = gethtmlurl2($value['catid']).'/index.html';
		$subarr[] = $value;
	}
	return $subarr;
}
?>