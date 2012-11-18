<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: announcement.php 11524 2009-03-09 06:22:49Z zhaolei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$perpage = 10;
$id = empty($_SGET['id'])?0:intval($_SGET['id']);
$listvalue = array();
if(empty($id)) {

	$page = empty($_SGET['page'])?1:intval($_SGET['page']);
	($page<1)?$page=1:'';
	$start = ($page-1)*$perpage;

	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('announcements').' WHERE starttime <= \''.$_SGLOBAL['timestamp'].'\' AND (endtime >= \''.$_SGLOBAL['timestamp'].'\' OR endtime = 0)');
	$listcount = $_SGLOBAL['db']->result($query, 0);
	$multipage = '';
	
	if($listcount) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('announcements').' WHERE starttime <= \''.$_SGLOBAL['timestamp'].'\' AND (endtime >= \''.$_SGLOBAL['timestamp'].'\' OR endtime = 0) ORDER BY displayorder DESC, starttime DESC LIMIT '.$start.','.$perpage);
		while($item = $_SGLOBAL['db']->fetch_array($query)) {
			$item['starttime'] = $item['starttime'] ? sgmdate($item['starttime']) : '-';
			$item['endtime'] = $item['endtime'] ? sgmdate($item['endtime']) : '-';
			$item['url'] = geturl('action/announcement/id/'.$item['id']);
			$listvalue[] = $item;
		}
		$urlarr = array('action'=>'announcement');
		$multipage = multi($listcount, $perpage, $page, $urlarr, 0);
	}
	
} else {

	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('announcements').' WHERE id=\''.$id.'\'');
	if($announce = $_SGLOBAL['db']->fetch_array($query)) {
		$announce['starttime'] = $announce['starttime'] ? sgmdate($announce['starttime']) : '-';
		$announce['endtime'] = $announce['endtime'] ? sgmdate($announce['endtime']) : '-';

		if (empty($announce['announcementsurl'])) {
			$announce['url'] = geturl('action/announcement/id/'.$id);
		} else {
			sheader($announce['announcementsurl']);
		}
		
		$listvalue[] = $announce;
		$multipage = '<div class="anno_more"><a href="'.geturl('action/announcement').'">MORE</a></div>';
	} else {
		showmessage('not_found');
	}

}

$title = $lang['announcement'];

include template('site_announcement');

?>