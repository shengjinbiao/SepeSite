<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.insertimage.php 11124 2009-02-18 08:56:10Z zhaolei $
*/

include_once('./common.php');
include_once(S_ROOT.'./language/batch.lang.php');

$perpage = 15;
$page = empty($_GET['page'])?1:intval($_GET['page']);
$page = ($page < 1)?1:$page;
$start = ($page - 1)*$perpage;

if(empty($_SGLOBAL['supe_uid'])) exit('No login');
$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('attachments').' WHERE uid=\''.$_SGLOBAL['supe_uid'].'\' AND isavailable=\'1\'');
$listcount = $_SGLOBAL['db']->result($query, 0);
?>
<!doctype html public "-//w3c//dtd html 4.0 transitional//en">
<html>
<head>
<title>Insert Image</title>
<meta http-equiv="content-type" content="text/html; charset=<?=$_SCONFIG['charset']?>">
<style type="text/css">
table{
	font-size:12px;
}

body{
	overflow:hidden;
	margin:0px;
	padding:0px;
	padding-top:1px;
	background:url("<?=S_URL?>/images/edit/bgcolor.gif")
}
ul.page {
	list-style: none;
	margin: 0.4em 0;
}
ul.page li {
	display: inline;
	margin-right: 0.1em;
	line-height: 2em;
	height: 2em;
	text-align: center;
}
ul.page li a {
	background: #FFF;
	padding: 0.3em 0.5em;
	border: 1px solid #D9E1F1;
	height: 2em;
}
ul.page li a:hover {
	background: #D9E1F1;
	text-decoration: none;
}
ul.page li.totle, ul.page li.pages, ul.page li.current {
	background: #FFF;
	padding: 0.3em 0.5em;
	border: 1px solid #D9E1F1;
	margin-right: 0.3em;
}
ul.page>li.totle, ul.page>li.pages, ul.page>li.current {
	margin-right: 0.1em;
}
ul.page li.current {
	color: #007EA8;
	background: #D9E1F1;
}
/* ·ÖÒ³ */
.xspace-page { float: right; margin: 5px 5px 0 0; }
	.xspace-page a, .xspace-page span { float: left; display: inline; text-decoration: none; margin-right: 3px; line-height: 20px; padding: 0 5px; border: 1px solid; }
			span.xspace-totlerecord { margin-right: 0; border-right: none; }
		span.xspace-current { background: #F90; font-weight: bold; }

</style>
<script type="text/javascript">
function _cancel() {
	window.close();
}
function insetIMG(_sVal) {
	if(_sVal == "") return;
	var html = "<img src='" + _sVal + "' />";
	insertHtml(html);
}

function insertHtml(html) {

	if(window.Event){
		var oRTE = opener.getFrameNode(opener.sRTE);
		oRTE.document.execCommand('insertHTML', false, html);
	} else {
		var oRTE = opener.getFrameNode(opener.sRTE);
		oRTE.focus();
		var oRng = oRTE.document.selection.createRange();
		oRng.pasteHTML(html);
		oRng.collapse(false);
		oRng.select();
	}
	window.close();
}

</script>
</head>
<body style="margin: 0.5em;">
<table border="0" cellpadding="3" cellspacing="3">
  <tr>
    <td><?=$blang['insert_photo_url']?>: <input id="wordEditer_IMG_SRC" value="http://"></td>
    <td>
    <a onclick="insetIMG(document.getElementById('wordEditer_IMG_SRC').value)">
    <img src="<?=S_URL?>/images/edit/tools_bt_ok.gif" width="42" height="19" border="0">
    </a>
    <a onclick="_cancel();"><img src="<?=S_URL?>/images/edit/tools_bt_no.gif" width="42" height="19" border="0"></a>
    </td>
  </tr>
</table>
<?php
if($listcount) {
?>
<hr size="1">
<table border="0" cellpadding="3" cellspacing="3">
<tr><td colspan="5"><?=$blang['insert_photo_upload']?></td></tr>
<tr>
<?php
$i = 0;
$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('attachments').' WHERE uid=\''.$_SGLOBAL['supe_uid'].'\' AND isavailable=\'1\' ORDER BY dateline DESC LIMIT '.$start.','.$perpage);
while ($attach = $_SGLOBAL['db']->fetch_array($query)) {
	if($attach['isimage']) {
		$attach['thumbpath'] = A_URL.'/'.$attach['thumbpath'];
		$thehtml = '<img src="'.$attach['thumbpath'].'" border="0">';
	} else {
		$attach['thumbpath'] = S_URL.'/images/base/attachment.gif';
		$thehtml = '<img src="'.S_URL.'/images/base/haveattach.gif" border="0">'.$attach['subject'];
	}
	$inserthtml = '<a href="'.S_URL.'/batch.download.php?aid='.$attach['aid'].'" target="_blank">'.$thehtml.'</a>';
	$inserthtml = shtmlspecialchars($inserthtml);
	$attach['subjectall'] = $attach['subject'];
	$attach['subject'] = cutstr($attach['subject'], 10);
	$attach['url'] = '<a onclick="insertHtml(\''.$inserthtml.'\');" href="javascript:;" title="'.$attach['subjectall'].'">';
?>
<td><?=$attach['url']?><img src="<?=$attach['thumbpath']?>" width="60" height="60" border="0"></a><br><?=$attach['url']?><?=$attach['subject']?></a></td>
<?php
if($i%5==4) echo '</tr><tr>';
$i++;
}
?>
</tr>
<tr><td colspan="5"><?echo multi($listcount, $perpage, $page, S_URL.'/batch.insertimage.php');?></td></tr>
</table>
<?php
}
?>

</body>
</html>
