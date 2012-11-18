<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: rss.php 13359 2009-09-22 09:06:19Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$catid = empty($_SGET['catid'])?0:intval($_SGET['catid']);
$rssdateformat = 'D, d M Y H:i:s T';

$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('categories')." WHERE catid='$catid'");
$catvalue = $_SGLOBAL['db']->fetch_array($query);
if(empty($catvalue)) {
	$catid = 0;
}

$itemsarr = $wherearr = array();
$rssarr = array(
	'charset' => $_SCONFIG['charset'],
	'title' => $_SCONFIG['sitename'],
	'link' => S_URL_ALL,
	'description' => $_SCONFIG['sitename'],
	'copyright' => 'Copyright(C) '.$_SCONFIG['sitename'],
	'generator' => 'SupeSite',
	'lastBuildDate' => sgmdate($_SGLOBAL['timestamp'], $rssdateformat),
	'items' => array()
);

if(!empty($catid)) {
	$rssarr['title'] = $catvalue['name'];
	$rssarr['link'] = geturl('action/category/catid/'.$catvalue['catid'], 1);
	$rssarr['description'] = $catvalue['name'];
	$rssarr['copyright'] = 'Copyright(C) '.$_SCONFIG['sitename'];
}

$itemsarr = getrss($catid);
if(!empty($itemsarr)) {
	foreach($itemsarr as $key => $value) {
		$rssarr['items'][] = array(
			'title' => $value['subject'],
			'link' => geturl('action/viewnews/itemid/'.$value['itemid'], 1),
			'description' => $value['message'],
			'category' => $value['name'],
			'author' => $value['username'],
			'pubDate' => sgmdate($value['dateline'], $rssdateformat)
		);	
	}
}

showrss($rssarr);

function showrss($rssarr) {
	header("Content-type: application/xml");
	echo '<?xml version="1.0" encoding="'.$rssarr['charset'].'"?>
			<rss version="2.0">
			  <channel>
			    <title>'.$rssarr['title'].'</title>
			    <link>'.$rssarr['link'].'</link>
			    <description>'.$rssarr['description'].'</description>
			    <copyright>'.$rssarr['copyright'].'</copyright>
			    <generator>'.$rssarr['generator'].'</generator>
			    <lastBuildDate>'.$rssarr['lastBuildDate'].'</lastBuildDate>';
			    if(!empty($rssarr['items'])) {
				    foreach($rssarr['items'] as $key => $value) {
				    	echo '<item>
								<title>'.$value['title'].'</title>
								<link>'.$value['link'].'</link>
								<description><![CDATA['.$value['description'].']]></description>
								<category>'.$value['category'].'</category>
								<author>'.$value['author'].'</author>
								<pubDate>'.$value['pubDate'].'</pubDate>
							</item>
							';
				    }
			    }
	echo '
		</channel>
	</rss>';
}

function getrss($catid) {
	global $_SGLOBAL, $_SCONFIG ;
	$rssarr = array();
	$attacharr = array();
	if(empty($_SCONFIG['rssnum'])) $_SCONFIG['rssnum'] = 10;
	$sql = "SELECT si.itemid, si.uid, si.username, si.subject, sn.*, si.dateline, c.name FROM ".tname('spaceitems')." si INNER JOIN ".tname('categories')." c ON si.catid = c.catid LEFT JOIN ".tname('spacenews')." sn ON si.itemid = sn.itemid WHERE si.type='news' ";
	if(!empty($catid)) {
		$sql .= " AND si.catid='$catid' ";
	}
	$sql .= " ORDER BY si.dateline DESC LIMIT 100";
	$query = $_SGLOBAL['db']->query($sql);
	while($items = $_SGLOBAL['db']->fetch_array($query)) {
		$othermsgarr = array();
		$items['message'] = cutstr($items['message'], 255, 1);
		if(!empty($othermsgarr)) $items['message'] = implode('<br>', $othermsgarr).'<br>'.$items['message'];
		if(!empty($items['hash'])) $attacharr[] = trim($items['hash']);
		$rssarr[$items['itemid']] = $items;
	}

	return $rssarr;
}
?>