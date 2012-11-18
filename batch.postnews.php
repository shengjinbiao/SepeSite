<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.formhash.php 11762 2009-03-24 05:34:09Z zhaolei $
*/

include_once('./common.php');
include_once(S_ROOT.'./language/batch.lang.php');

if(!checkperm('allowpostnews')) {
	exit();
}

@include_once(S_ROOT.'./data/system/postnews.cache.php');
$ac = empty($_GET['ac']) ? '' : trim($_GET['ac']);
$setid = empty($_POST['setid']) ? (empty($_GET['setid']) ? 0 : intval($_GET['setid'])) : intval($_POST['setid']);
$set = $_SGLOBAL['postnews_set'][$setid];
$set['setting'] = unserialize($set['setting']);

if(empty($_SCONFIG['allowpostnews'])) {
	exit();
}

if($ac == 'toss' && empty($set['setting']['setlive']) && !checkperm('allowpushin')) {
	exit();
}

if($ac == 'postto') {

	if(checkperm('allowpushout')) {
		exit();
	}
	if(submitcheck('submitpostnews')) {
		$itemid = intval($_POST['itemid']);
		if(empty($set)) {
			exit();
		}
		
		$query = $_SGLOBAL['db']->query('SELECT i.subject, i.uid, i.username, ii.message FROM '.tname('spaceitems').' i LEFT JOIN '.tname('spacenews')." ii USING (itemid) WHERE itemid='$itemid'");
		$news = $_SGLOBAL['db']->fetch_array($query);
		$news['message'] = preg_replace_callback("/src\=(.{1})([^\>\s]{10,105})\.(jpg|gif|png)/i", 'addurlhttp', $news['message']);
		
		$_SGLOBAL['db_temp'] = new dbstuff;
		$_SGLOBAL['db_temp']->charset = $_SC['dbcharset'];
		$_SGLOBAL['db_temp']->connect($set['setting']['setdbhost'], $set['setting']['setdbuser'], $set['setting']['setdbpwd'], $set['setting']['setdbname'], $set['setting']['setpconnect'] ,1);
		
		$insertsql = '';
		$insertarr = array();
		$fieldarr = array();
		
		if($set['setting']['posttype'] == 'uchome') {
			if($set['setting']['setctype'] == 'blog') {
				$insertarr[] = $_SGLOBAL['supe_username'];;
				$insertarr[] = $_SGLOBAL['supe_uid'];;
				$insertarr[] = $_SGLOBAL['timestamp'];
				$insertarr[] = $news['subject'];
				$insertarr[] = intval($_POST['cateid']);
				$insertsql = simplode($insertarr);
				$_SGLOBAL['db_temp']->query('INSERT INTO '.$set['setting']['setdbpre']."blog(username,uid,dateline, subject, classid) VALUES($insertsql)");
				$blogid = $_SGLOBAL['db_temp']->insert_id();
				
				$fieldarr[] = $blogid;
				$fieldarr[] = $_SGLOBAL['supe_uid'];;
				$fieldarr[] = $news['message'];
				$fieldarr[] =  $_SGLOBAL['onlineip'];
				$insertsql = simplode($fieldarr, ',');
				
				$_SGLOBAL['db_temp']->query('INSERT INTO '.$set['setting']['setdbpre']."blogfield(blogid, uid, message, postip) VALUES($insertsql)");
				$success = 1;
			} elseif ($set['setting']['setctype'] == 'thread') {
				
				$_POST['cateid'] = intval($_POST['cateid']);
				if(empty($_POST['cateid'])) {
					ajaxmsg('no_select_tagid');
				}
				$insertarr[] = $_POST['cateid'];
				$insertarr[] = $news['subject'];
				$insertarr[] = $_SGLOBAL['supe_uid'];
				$insertarr[] = $_SGLOBAL['supe_username'];
				$insertarr[] = $_SGLOBAL['timestamp'];
				$insertsql = simplode($insertarr, ',');
				$_SGLOBAL['db_temp']->query('INSERT INTO '.$set['setting']['setdbpre']."thread(tagid, subject, uid, username, dateline) VALUES($insertsql)");
				
				$tid = $_SGLOBAL['db_temp']->insert_id();
				
				$fieldarr[] = $_POST['cateid'];
				$fieldarr[] = $tid;
				$fieldarr[] = $_SGLOBAL['supe_uid'];;
				$fieldarr[] = $_SGLOBAL['supe_username'];;
				$fieldarr[] = $_SGLOBAL['onlineip'];
				$fieldarr[] = $_SGLOBAL['timestamp'];
				$fieldarr[] = $news['message'];
				
				$insertsql = simplode($fieldarr, ',');
				
				$_SGLOBAL['db_temp']->query('INSERT INTO '.$set['setting']['setdbpre']."post(tagid, tid, uid, username, ip, dateline, message) VALUES($insertsql)");
				$success = 1;
				
			}
		} elseif($set['setting']['posttype'] == 'bbs') {
			$_POST['cateid'] = intval($_POST['cateid']);
			if(empty($_POST['cateid'])) {
				ajaxmsg('no_select_forums');
			}
			$query = $_SGLOBAL['db_temp']->query('SELECT fid, type FROM '.$set['setting']['setdbpre']."forums WHERE fid='$_POST[cateid]'");
			$forum = $_SGLOBAL['db_temp']->fetch_array($query);
			if($forum['type'] == 'group') {
				ajaxmsg('this_forums_not_thread');
			}
			$insertarr[] = $_POST['cateid'];
			$insertarr[] = $_SGLOBAL['supe_username'];
			$insertarr[] = $_SGLOBAL['supe_uid'];
			$insertarr[] = $news['subject'];
			$insertarr[] = $_SGLOBAL['timestamp'];

			$insertsql = simplode($insertarr, ',');
			$_SGLOBAL['db_temp']->query('INSERT INTO '.$set['setting']['setdbpre']."threads(fid, author, authorid, subject, dateline) VALUES($insertsql)");
			
			$tid = $_SGLOBAL['db_temp']->insert_id();
			
			$fieldarr[] = $_POST['cateid'];
			$fieldarr[] = $tid;
			$fieldarr[] = 1;
			$fieldarr[] = $_SGLOBAL['supe_username'];
			$fieldarr[] = $_SGLOBAL['supe_uid'];
			$fieldarr[] = $news['subject'];
			$fieldarr[] = $_SGLOBAL['timestamp'];
			$news['message'] = imgtobbcode($news['message']);
			$fieldarr[] = strip_tags($news['message']);
			$fieldarr[] = $_SGLOBAL['onlineip'];
			
			$insertsql = simplode($fieldarr, ',');
				
			$_SGLOBAL['db_temp']->query('INSERT INTO '.$set['setting']['setdbpre']."posts(fid, tid, first, author, authorid, subject, dateline, message, useip) VALUES($insertsql)");
			$success = 1;
		}

		if($success){
			$logarr = array('uid' => $_SGLOBAL['supe_uid'],
							'username' => $_SGLOBAL['supe_username'],
							'itemid' =>$itemid,
							'id' => $tid,
							'dateline' => $_SGLOBAL['timestamp'],
							'setid' => $setid);
			inserttable('postlog', $logarr);
			$postok = $blang['post_ok'];
			echo '
			<script language="javascript">
			var ajaxdiv = parent.document.getElementById("xspace-ajax-div");
			ajaxdiv.style.display="none";
			alert("'.$postok.'");
			</script>';	
		} else {
			$posterror = $blang['post_error'];
			echo '
			<script language="javascript">
			var ajaxdiv = parent.document.getElementById("xspace-ajax-div");
			ajaxdiv.style.display="none";
			alert("'.$posterror.'");
			</script>';	
		}
		exit();
	}
	
	$itemid = intval($_GET['itemid']);
	
	$selectstr = '<option value="0">----</option>';
	foreach ($_SGLOBAL['postnews_set'] as $value) {
		$value['setting'] = unserialize($value['setting']);
		if($value['settype'] == 'fromss' && empty($value['setting']['setlive'])) {
			$selectstr .= '<option value="'.$value['setid'].'">'.$value['setname'].'</option>';
		}
	}
	$formhash = formhash();
	$html = <<<eof
<h5><a href="javascript:;" onclick="getbyid('xspace-ajax-div').style.display='none';" target="_self">$blang[post_close_div]</a>$blang[post_to_uchome_or_bbs]</h5>
	<div class="xspace-ajaxcontent">
		<form method="post" action="{S_URL}/batch.postnews.php?ac=postto" target="phpframe_post">
			<input type="hidden" name="itemid" value="$itemid" />
			<table width="100%" summary="" cellpadding="5" cellspacing="0" border="0">
				<tr>
					<td width="85">$blang[post_set]</td>
					<td>
					<select name="setid" onchange="getpostcate(this.value,$itemid);">
						$selectstr
					</select>
					</td>
				</tr>
				<tr>
					<td valign="top">$blang[post_category]</td>
					<td valign="top" id="cateselect">
						&nbsp;
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<input type="hidden" name="formhash" value="$formhash">
					<td><button type="submit" name="submitpostnews" value="true">$blang[put_to_news]</button></td>
				</tr>
			</table>
		</form>
	</div>
	<iframe id="phpframe_post" name="phpframe_post" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank"></iframe>
eof;
	showxml($html);
} elseif ($ac == 'getcate') {
	if($set['settype'] == 'fromss'){
	
		$strclass = '';
		$_SGLOBAL['db_temp'] = new dbstuff;
		$_SGLOBAL['db_temp']->charset = $_SC['dbcharset'];
		$_SGLOBAL['db_temp']->connect($set['setting']['setdbhost'], $set['setting']['setdbuser'], $set['setting']['setdbpwd'], $set['setting']['setdbname'], $set['setting']['setpconnect'] ,1);
		if( $set['setting']['posttype'] == 'uchome') {
			if($set['setting']['setctype'] == 'blog') {
				$query = $_SGLOBAL['db_temp']->query("SELECT * FROM `{$set['setting']['setdbname']}`.{$set['setting']['setdbpre']}class WHERE uid='$_SGLOBAL[supe_uid]'");
				$strclass = '<select name="cateid"><option value="0">'.$blang['default_value'].'</option>';
				while ($value = $_SGLOBAL['db_temp']->fetch_array($query)) {
					$strclass .= '<option value="'.$value['classid'].'">'.$value['classname'].'</option>';
				}
				$strclass .= '</select>';
			} elseif($set['setting']['setctype'] == 'thread') {
				$query = $_SGLOBAL['db_temp']->query("SELECT i.tagid, i.uid, i.username, ii.tagname FROM `{$set['setting']['setdbname']}`.{$set['setting']['setdbpre']}tagspace i LEFT JOIN `{$set['setting']['setdbname']}`.{$set['setting']['setdbpre']}mtag ii using (tagid) WHERE i.uid='$_SGLOBAL[supe_uid]' AND ii.close='0'");
				$strclass = '<select name="cateid">';
				$optionstr = '';
				while ($value = $_SGLOBAL['db_temp']->fetch_array($query)) {
					$optionstr .= '<option value="'.$value['tagid'].'">'.$value['tagname'].'</option>';
				}
				if(empty($optionstr)) {
					$strclass = '<font color="red">'.$blang['you_no_join_mtag'].'</font>';
				} else {
					$strclass = $strclass.$optionstr.'</select>';
				}
			}
			
		} elseif ($set['setting']['posttype'] == 'bbs'){
			$query = $_SGLOBAL['db_temp']->query('SELECT type, fup, fid, name FROM `'.$set['setting']['setdbname'].'`.'.$set['setting']['setdbpre'].'forums');
			while($forum = $_SGLOBAL['db_temp']->fetch_array($query)) {
				$_SGLOBAL['bbsforumarr'][] = $forum;
			}

			$forumselect = forumselect();
			$strclass = '<select name="cateid">'.$forumselect.'</select>';
		}
	}
	echo $strclass;
	exit();
} elseif ($ac == 'toss') {
	//ºÏ≤È÷ÿ∏¥
	$funpre = smd5($_SGLOBAL['timestamp'].random(5));
	
	if(empty($set)) {
		exit();
	}
	$message_id = trim($_GET['message']);
	$subject_id = trim($_GET['subject']);
	if(empty($set['setting']['seticon'])) {
		$strbtn = "<a href=\"javascript:;\" onclick=\"$(\\'theform_".$funpre."\\').submit();\">".$set['setname']."</a>";
	} else {
		$strbtn = "<a href=\"javascript:;\" onclick=\"$(\\'theform_".$funpre."\\').submit();\"><img src=\"$_SC[siteurl]/images/push/".$set['setting']['seticon']."\" title=\"".$set['setname']."\" /></a>";
	}
	
	echo <<<eof
document.write('<div id="ss_btn"></div>');
function geturl_$funpre() {
	urlstr = 'http://' + location.host;
	if(window.location.port) {
		urlstr += ':' + window.location.port; 
	}
	basearr = window.location.pathname.split('/');
	len = basearr.length;
	for(var i = 1; i< len-1; i++) {
		urlstr += '/' + basearr[i];
	}
	return urlstr;
}
function postmsg_$funpre()
{	
	if('$message_id') {
		var msg = $('$message_id').innerHTML;
	}
	
	if('$subject_id') {
		var subject = $('$subject_id').innerHTML;
	}
	var url = geturl_$funpre();
	var sbtn = '<form action="{$siteurl}/cp.php?ac=news&op=add" method="post" id="theform_$funpre">';
	sbtn += '<input type="hidden" value="$setid" name="setid" />';
	sbtn += '<input type="hidden" value="ok" name="postnews" />';
	sbtn += '<input type="hidden" value="' + url + '" name="url" />';
	if(subject) {
		sbtn += '<input type="hidden" value="'+subject+'" name="subject" />';
	}
	sbtn += '<textarea name="message" style="display:none">'+msg+'</textarea>';
	sbtn += '$strbtn';
	sbtn += '</form>';
	$('ss_btn').innerHTML = sbtn;
}
window.onload = postmsg_$funpre;
eof;
exit();
} elseif ($ac == 'fromss') {
	echo "document.write('<a href=\"javascript:;\" class=\"push\" onclick=\"showajaxdiv(\'".S_URL."/batch.postnews.php?ac=postto&amp;itemid=$itemid\',300);\">$blang[post_to_other]</a>');";
	exit();
}

function ajaxmsg($key) {
	global $blang;
	
	if(in_array($key, $blang)) {
		$lang = $blang[$key];
	} else {
		$lang = $key;
	}
	
	echo '<script language="javascript">alert("'.$lang.'");</script>';
	exit();
}

function addurlhttp($m) {
	global $_SC;
	
	if (preg_grep("/^http\:/", array($m[2]))) {
		return 'src="'.$m[2].'.'.$m[3].'"';
	} else {
		return 'src="'.$_SC['siteurl'].'/'.$m[2].'.'.$m[3].'"';
	}
}

function imgtobbcode($msg) {
	$msg = preg_replace_callback("/\<img(.*)src\=(.{1})([^\>\s]{10,105})\.(jpg|gif|png)(.*)\/?\>/i", 'tobbcode', $msg);
	return $msg;
}

function tobbcode($m) {
	return '[img]'.$m[3].'.'.$m[4].'[/img]';
}
?>