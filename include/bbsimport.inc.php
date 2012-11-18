<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: bbsimport.inc.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

dbconnect(1);

$query = $_SGLOBAL['db_bbs']->query('SELECT t.*, p.attachment, p.pid, p.bbcodeoff, p.smileyoff, p.htmlon, p.subject AS psubject, p.message, f.allowhtml FROM '.tname('threads', 1).' t ,'.tname('posts', 1).' p ,'.tname('forums', 1).' f WHERE t.tid=\''.$tid.'\' AND p.tid=\''.$tid.'\' AND p.first=\'1\' AND f.fid=t.fid');
if($post = $_SGLOBAL['db_bbs']->fetch_array($query)) {
	if($type == 'blog' && $post['authorid'] != $_SGLOBAL['supe_uid']) {
		showmessage($lang['bbsimport_self']);
	}
	if(!empty($post['psubject'])) $post['subject'] = $post['psubject'];
	$thevalue['digest'] = $post['digest'];
	$thevalue['top'] = $post['displayorder'];
	
	include_once(S_ROOT.'/include/bbcode.inc.php');
	if($post['attachment']) {
		$query = $_SGLOBAL['db_bbs']->query("SELECT * FROM ".tname('attachments', 1)." WHERE pid='$post[pid]'");
		if($_SGLOBAL['db_bbs']->num_rows($query)) {
			while($attach = $_SGLOBAL['db_bbs']->fetch_array($query)) {
				$attach['dateline'] = sgmdate($attach['dateline']);
				$attach['attachicon'] = S_URL.'/images/base/download.gif';
				$attach['attachsize'] = formatsize($attach['filesize']);
				$attach['attachimg'] = $attach['isimage'];
				$attach['attachment'] = getbbsattachment($attach);
				$post['attachments'][$attach['aid']] = $attach;
			}
		}
	}
	
	if(empty($post['htmlon'])) $post['htmlon'] = $post['allowhtml'];
	$post['message'] = bbcode($post['message'], $post['bbcodeoff'], $post['smileyoff'], $post['htmlon'], 0);
	
	if(!empty($post['attachments'])) {
		if(preg_match("/\[attach\](\d+)\[\/attach\]/i", $post['message'])) {
			$post['message'] = preg_replace("/\[attach\](\d+)\[\/attach\]/ie", "postattachtag(\\1)", $post['message']);
		}
	}
	//帖子附件处理
	if(!empty($post['attachments'])) {
		foreach ($post['attachments'] as $attach) {
			$attachstr = '<br>';
			if($attach['attachimg']) {
				$attachstr .= "<a href=\"".B_URL."/attachment.php?aid=$attach[aid]\" target=\"_blank\"><img src=\"$attach[attachment]\" border=\"0\"><br>$attach[filename]</a>";
			} else {
				$attachstr .= "<p><img src=\"".S_URL."/images/base/haveattach.gif\" align=\"absmiddle\" border=\"0\"><a href=\"".B_URL."/attachment.php?aid=$attach[aid]\" target=\"_blank\"><strong>$attach[filename]</strong></a><br />($attach[dateline], Size: $attach[attachsize], Downloads: $attach[downloads])</p>";
			}
			$post['message'] .= $attachstr;
		}
	}

	$thevalue['message'] = $post['message'];
	$thevalue['subject'] = $post['subject'];
	$thevalue['tid'] = $post['tid'];
} else {
	showmessage($alang['bbsimport_no_exist']);
}

function postattachtag($aid) {
	global $post;

	if(isset($post['attachments'][$aid])) {
		$attach = $post['attachments'][$aid];
		unset($post['attachments'][$aid]);

		$replacement = '<br>';
		if($attach['attachimg']) {
			$replacement .= "<a href=\"".B_URL."/attachment.php?aid=$attach[aid]\" target=\"_blank\"><img src=\"$attach[attachment]\" border=\"0\"><br>$attach[filename]</a>";
		} else {
			$replacement .= "<p><img src=\"".S_URL."/images/base/haveattach.gif\" align=\"absmiddle\" border=\"0\"><a href=\"".B_URL."/attachment.php?aid=$attach[aid]\" target=\"_blank\"><strong>$attach[filename]</strong></a><br />($attach[dateline], Size: $attach[attachsize], Downloads: $attach[downloads])</p>";
		}

		return $replacement;
	} else {
		return '<strike>[attach]'.$aid.'[/attach]</strike>';
	}
	
}

?>