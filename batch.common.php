<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.common.php 13368 2009-09-23 06:53:35Z zhaofei $
*/

include_once('./common.php');
include_once(S_ROOT.'./language/batch.lang.php');

$action = empty($_GET['action'])?'':$_GET['action'];

if ($action == 'emailfriend') {

	//推荐给好友
	if(submitcheck('submitemailfriend')) {
		//发送处理
		$_POST['sendtoemail'] = str_replace(array(' ', '|', ';'), ',', $_POST['sendtoemail']);
		$tomails = explode(',', $_POST['sendtoemail']);
		$email_to = $_POST['sendtoemail'];
		if(empty($email_to) || !isemail($email_to)) {
			echo '
					<script language="javascript">
					alert("'.$blang['error_email_empty'].'");
					</script>';
			exit;
		}

		$email_subject = $blang['mail_title'];
		$email_message = $_POST['message'];

		include(S_ROOT.'./function/sendmail.fun.php');
		if(sendmail($tomails, $email_subject, $email_message)) {
			echo '
			<script language="javascript">
			var ajaxdiv = parent.document.getElementById("xspace-ajax-div");
			ajaxdiv.style.display="none";
			alert("'.$blang['message_mail_ok'].'");
			</script>';	
		} else {
			echo '
			<script language="javascript">
			var ajaxdiv = parent.document.getElementById("xspace-ajax-div");
			ajaxdiv.style.display="none";
			alert("'.$blang['message_mail_error'].'");
			</script>';	
		}
		exit;
	}
	
	include_once(S_ROOT.'./data/system/config.cache.php');
	$item = array();
	$itemid = empty($_GET['itemid'])?0:intval($_GET['itemid']);
	$blogid = empty($_GET['blogid'])?0:intval($_GET['blogid']);
	$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
	if($itemid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE itemid='$itemid'");
		$item = $_SGLOBAL['db']->fetch_array($query);
		
		if(empty($item)) {
			$message = "{$blang[mail_c1]}\n\n$_SSCONFIG[sitename]\n{$blang[mail_c2]} ".S_URL_ALL."\n\n{$blang[mail_c3]}";
		} else {
			$url = S_URL_ALL.'/?viewnews-'.$item[itemid];
			$message = "{$blang[mail_c4]} $_SSCONFIG[sitename] {$blang[mail_c5]} $item[subject]\n{$blang[mail_c2]}: $url\n\n{$blang[mail_c6]}";
		}
	} else {
		$url = S_URL_ALL.'/action-blogdetail-uid-'.$uid.'-id-'.$blogid;
		$message = "{$blang[mail_c4]} $_SSCONFIG[sitename] {$blang[mail_c5]} $item[subject]\n{$blang[mail_c2]}: $url\n\n{$blang[mail_c6]}";
	}
	
	$html = '<h5><a href="javascript:;" onclick="getbyid(\'xspace-ajax-div\').style.display=\'none\';" target="_self">'.$blang['mail_close'].'</a>'.$blang['mail_submit'].'</h5>
	<div class="xspace-ajaxcontent">
		<form method="post" action="'.S_URL.'/batch.common.php?action=emailfriend" target="phpframe_emailfriend">
			<table width="100%" summary="" cellpadding="5" cellspacing="0" border="0">
				<tr>
					<td width="60">'.$blang['mail_add'].'</td>
					<td><input type="text" name="sendtoemail" id="sendtoemail" style="width: 98%;" value=""></td>
				</tr>
				<tr>
					<td valign="top">'.$blang['mail_content'].'</td>
					<td valign="top"><textarea rows="8" name="message" style="width: 98%;">'.$message.'</textarea></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<input type="hidden" name="formhash" value="'.formhash().'">
					<td><button type="submit" name="submitemailfriend" onclick="if($(\'sendtoemail\').value.length == 0 || !isEmail($(\'sendtoemail\').value)){ alert(\''.$blang['error_email_empty'].'\'); return false;}" value="true">'.$blang['mail_submit'].'</button></td>
				</tr>
			</table>
		</form>
	</div>
	<iframe id="phpframe_emailfriend" name="phpframe_emailfriend" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank"></iframe>';

	showxml($html);

} elseif ($action == 'viewnews') {

	//查看新闻前一个/后一个
	if(!empty($_GET['op']) && !empty($_GET['itemid']) && !empty($_GET['catid'])) {
		$itemid = intval($_GET['itemid']);
		$catid = intval($_GET['catid']);
		$newitemid = 0;
		if($itemid && $catid && $_GET['op'] == 'up') {
			$newitemid = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT itemid FROM '.tname('spaceitems').' WHERE itemid <\''.$itemid.'\' AND catid=\''.$catid.'\' ORDER BY itemid DESC LIMIT 1'), 0);
		} elseif($itemid && $catid) {
			$newitemid = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT itemid FROM '.tname('spaceitems').' WHERE itemid >\''.$itemid.'\' AND catid=\''.$catid.'\' ORDER BY itemid LIMIT 1'), 0);
		}
		if(!empty($newitemid)) {
			sheader(geturl('action/viewnews/itemid/'.$newitemid));
		} else {
			sheader(geturl('action/viewnews/itemid/'.$itemid));
		}
	}

} elseif ($action == 'quote') {
	
	//评论引用
	include_once(S_ROOT.'./function/misc.func.php');
	$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
	$html = false;
	if($cid) {
		$item = array();
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spacecomments').' WHERE cid=\''.$cid.'\'');
		if($item = $_SGLOBAL['db']->fetch_array($query)) {
			$currentmessage = array();
			preg_match_all ("/\<div class=\"new\">(.+)?\<\/div\>/is", $item['message'], $currentmessage, PREG_SET_ORDER);
			if(!empty($currentmessage)) $item['message'] = $currentmessage[0][0];
			$item['message'] = preg_replace("/\<div class=\"quote\"\>\<blockquote.+?\<\/blockquote\>\<\/div\>/is", '',$item['message']);
			
			$item['hideauthor'] = (!empty($item['hideauthor']) && !empty($_SCONFIG['commanonymous'])) ? 1 : 0;
			$item['hidelocation'] = (!empty($item['hidelocation']) && !empty($_SCONFIG['commhidelocation'])) ? 1 : 0;
			$item['iplocation'] = str_replace(array('-', ' '), '', convertip($item['ip']));
			$html = '[quote]'.$blang['from_the_original_note'].$_SCONFIG['sitename'];
			if (!$item['hidelocation']) {
				$html .= $item['iplocation']!='LAN' ? $item['iplocation'] : $blang['mars'];
			}
			$html .= $blang['visitor'];
			if (!empty($item['authorid']) && !$item['hideauthor']) $html .= " [{$item['author']}] ";
			$html .= $blang['at'].sgmdate($item['dateline']).$blang['released']."\n".cuthtml($item['message'], 100).'[/quote]';
			showxml($html);
		}
	}
	showxml($html);
	
	
} elseif ($action == 'getrobotmsg') {
	
	include_once(S_ROOT.'./function/robot.func.php');
	$arrayrobotmeg = array();
	if(isset($_POST['referurl']) && !empty($_POST['referurl'])){
		//萃取内容
		$robotlevel = intval(postget('robotlevel'));
		if($robotlevel > 2 || $robotlevel < 1) exit;
	
		$arrayrobotmeg = getrobotmeg($_POST['referurl'], $robotlevel);
	}

	//检查是否获取到信息
	if(!empty($arrayrobotmeg['leachmessage'])) {

		$pagebreak = isset($_POST['itemid']) && intval($_POST['itemid']) == 0 ? 1 : 0;
		empty($_POST['isfront']) ? $pagebreak : $pagebreak = 0 ;
		$arrayrobotmeg['leachsubject'] = preg_replace("/\r/", '', $arrayrobotmeg['leachsubject']);
		$arrayrobotmeg['leachmessage'] = addslashes($arrayrobotmeg['leachmessage']);

		print <<<EOF
			<script type="text/javascript">
			parent.document.getElementById("subject").value = '{$arrayrobotmeg['leachsubject']}';
			parent.document.getElementById("message").innerHTML = '';
			function init() {
				parent.et = new parent.word("message", "{$arrayrobotmeg['leachmessage']}", 0, {$pagebreak});
			}
			init();
			objCharset = parent.document.getElementById('scharset');
			objCharsetOption = parent.document.getElementById('charset').getElementsByTagName("option");
			for(i = 0; i < objCharsetOption.length; i++){
				if(objCharsetOption[i].value == '{$arrayrobotmeg['charset']}')
					objCharsetOption[i].selected = true;
			}
			objCharset.style.display = "";
			</script>
EOF;
		showrobotmsg($blang['extract_the_contents_of_success'], 'ok');
	} else {
		showrobotmsg($blang['extract_the_contents_of_failure']);
	}

} elseif ($action == 'report') {
	
	$itemid = empty($_GET['itemid'])?0:intval($_GET['itemid']);
	if(!empty($itemid)) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spaceitems').' WHERE itemid=\''.$itemid.'\'');
		if($item = $_SGLOBAL['db']->fetch_array($query)) {
			$query = $_SGLOBAL['db']->query('SELECT itemid, status FROM '.tname('reports').' WHERE itemid=\''.$itemid.'\'');
			$reportitem = $_SGLOBAL['db']->fetch_array($query);
			if($reportitem) {
				if($reportitem['status'] == '0') {
					showxml($blang['information_has_been_reported']);
				} else {
					showxml($blang['not_a_malicious_report']);
				}
			} else {
				
				if(empty($_SGLOBAL['supe_username'])) {
					$_SGLOBAL['supe_username'] = 'Guest';
				}
				$insertsqlarr = array(
					'itemid' => $itemid,
					'reportuid' => $_SGLOBAL['supe_uid'],
					'reporter' => empty($_SGLOBAL['supe_username'])?'Guest':$_SGLOBAL['supe_username'],
					'reportdate' => $_SGLOBAL['timestamp'],
					'status' => 0
				);
				inserttable('reports', $insertsqlarr);
				showxml($blang['reported_success']);
			}
		}
	}
} elseif ($action == 'modelquote') {
	
	//模型评论引用
	$name = empty($_GET['name'])?'':trim($_GET['name']);
	$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
	$html = false;
	if(!empty($name) && !empty($cid)) {
		$item = array();
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($name.'comments').' WHERE cid=\''.$cid.'\'');
		if($item = $_SGLOBAL['db']->fetch_array($query)) {
			$item['message'] = preg_replace("/<blockquote.+?<\/blockquote>/is", '',$item['message']);
			$html = '[quote]'.$blang['from_the_original_note'].$item['author'].$blang['at'].sgmdate($item['dateline']).$blang['released']."\n".cuthtml($item['message'], 100).'[/quote]';
			showxml($html);
		}
	}
	showxml($html);
	
} elseif($action == 'relatekw') {
	
	$subjectenc = rawurlencode(strip_tags($_GET['subjectenc']));
	$messageenc = rawurlencode(strip_tags($_GET['messageenc']));
	$return = '';
	$data = @implode('', file("http://keyword.discuz.com/related_kw.html?title=$subjectenc&content=$messageenc&ics=$charset&ocs=$charset"));
	if($data) {
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $data, $values, $index);
		xml_parser_free($parser);
	
		$kws = array();
	
		foreach($values as $valuearray) {
			if($valuearray['tag'] == 'kw' || $valuearray['tag'] == 'ekw') {
				if(PHP_VERSION > '5' && $charset != 'utf-8') {
					$valuearray['value'] = encodeconvert("UTF-8", $valuearray['value']);
				} else {
					$valuearray['value'] = trim($valuearray['value']);
				}
				$kws[] = $valuearray['value'];
			}
		}

		if($kws) {
			foreach($kws as $kw) {
				$kw = htmlspecialchars($kw);
				$return .= $kw.' ';
			}
			$return = htmlspecialchars($return);
		}
	
	}
	showxml($return);
}

function cuthtml($string, $length, $havedot=0) {
	$searcharr = array(
		"/\<img(.+?)\>/is",
		"/\<br.*?\>/is",
		"/\<p\>(.*?)\<\/p\>/is"
	);
	$replacearr = array(
		"[img\\1]",
		"[br]",
		"[p]\\1[/p]"
	);
	$string = preg_replace($searcharr, $replacearr, $string);
	$string = strip_tags($string);
	$searcharr = array(
		"/\[img(.+?)\]/s",
		"/\[br\]/",
		"/\[p\](.+?)\[\/p\]/s"
	);
	$replacearr = array(
		"<img\\1>",
		"<br />",
		"<p>\\1</p>"
	);
	$string = preg_replace($searcharr, $replacearr, $string);
	return trim(cutstr($string, $length, $havedot));
}
?>