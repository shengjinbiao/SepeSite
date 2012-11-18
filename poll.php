<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: poll.php 13342 2009-09-16 05:43:20Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

//权限
if(!checkperm('allowvote')) {
	showmessage('no_permission');
}

$pollid = empty($_SGET['pollid'])?0:intval($_SGET['pollid']);
if(empty($pollid)) $pollid = intval(postget('pollid'));
if(empty($pollid)) showmessage('not_found', S_URL);

$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('polls').' WHERE pollid=\''.$pollid.'\'');
if(!$poll = $_SGLOBAL['db']->fetch_array($query)) showmessage('not_found', S_URL);

if(!empty($poll['pollsurl'])) {
	sheader($poll['pollsurl']);
}

if(submitcheck('pollsubmit')) {

	if(empty($_POST['votekey'])) showmessage('no_votekey');

	if(empty($_SGLOBAL['supe_uid'])) {
		$ip = $_SGLOBAL['onlineip'];
	} else {
		$ip = $_SGLOBAL['supe_uid'];
	}	
	$votekeys = $_POST['votekey'];
	$options = unserialize($poll['options']);
	if(empty($poll['voters'])) {
		$voters = array();
	} else {
		$voters = unserialize($poll['voters']);
	}
	
	if(!empty($voters) && in_array($ip, $voters)) {
		showmessage('poll_repeat');
	}
	
	$pollnum = 0;
	foreach ($votekeys as $votekey) {
		if(isset($options[$votekey]['num'])) {
			$options[$votekey]['num']++;
			$pollnum++;
		} else {
			showmessage('no_votekey');
		}
	}
	$options = addslashes(serialize($options));
	
	$voters[] = $ip;
	$voters = addslashes(serialize($voters));

	$_SGLOBAL['db']->query('UPDATE '.tname('polls').' SET pollnum=pollnum+'.$pollnum.', updatetime='.$_SGLOBAL['timestamp'].', options=\''.$options.'\', voters=\''.$voters.'\' WHERE pollid=\''.$pollid.'\'');

	//积分 和 经验
	getreward('postvote');
	
	showmessage('do_success', geturl('action/poll/pollid/'.$pollid));
}

$poll['options'] = unserialize($poll['options']);
if(empty($poll['voters'])) {
	$poll['voters'] = array();
} else {
	$poll['voters'] = unserialize($poll['voters']);
}
//投票人数
$poll['votersnum'] = count($poll['voters']);
$poll['dateline'] = sgmdate($poll['dateline'],'Y-m-d H:i:s');
$poll['updatetime'] = sgmdate($poll['updatetime'],'Y-m-d H:i:s');
foreach ($poll['options'] as $key => $options) {
	$options['percent'] = @sprintf ("%01.2f", $options['num'] * 100 / $poll['pollnum']);
	$poll['options'][$key] = $options;
}
$poll['votecount'] = count($poll['voters']);

$title = $lang['poll'];

include template('site_poll');

?>