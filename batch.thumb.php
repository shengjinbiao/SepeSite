<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.thumb.php 11124 2009-02-18 08:56:10Z zhaolei $
*/

include_once('./common.php');
include_once(S_ROOT.'./function/upload.func.php');
include_once(S_ROOT.'./language/batch.lang.php');

//没有登录
if(empty($_SGLOBAL['supe_uid']) || empty($_SGLOBAL['member']['password'])) {
	setcookie('_refer', rawurlencode(S_URL_ALL.'/admincp.php?'.$_SERVER['QUERY_STRING']));
	showmessage('admincp_login', geturl('action/login'));
}

//判断是否支持DG库
if(!(function_exists('imageCreateFromJPEG') && function_exists('imageCreateFromPNG') && function_exists('imageCopyMerge'))) {
	showmessage($blang['GD_lib_no_load']);
}

//判断数据合法性
if($_POST['imageauthcode'] != md5( $_POST['imagepath'].$_SCONFIG['sitekey'].$_POST['imagewidth'].$_POST['imageid'].$_POST['imageheight'].$_SGLOBAL['authkey'].$_POST['thumbimg'] )){
	showmessage('parameter_chenged');
}

if(!empty($_POST['loop'])) {
$looparray = array('lux','luy','ldx','ldy','rux','ruy','rdx','rdy');
	foreach ($looparray as $imagemsg) {
			$_POST[$imagemsg] = round($_POST[$imagemsg] / $_POST['loop']);
	}
}

$width = $height = $image = $imagesrc = $swapfile = $makethumb  = '';

$picfiletype = fileext($_POST['imagepath']);

$pictypes = array();
$pictypes['gif'] = array('imagecreatefromgif','imagegif');
$pictypes['png'] = array('imagecreatefrompng','imagepng');
$pictypes['jpeg'] = array('imagecreatefromjpeg','imagejpeg');
$pictypes['jpg'] = array('imagecreatefromjpeg','imagejpeg');

$contenttypes = array();
$contenttypes['gif'] = 'image/gif';
$contenttypes['png'] = 'image/png';
$contenttypes['jpeg'] = 'image/jpg';
$contenttypes['jpg'] = 'image/jpg';

$width = $_POST['rdx'] - $_POST['ldx'];
$height = $_POST['rdy'] - $_POST['ruy'];

$image = @imagecreatetruecolor($width, $height);
$imagesrc = @$pictypes[$picfiletype][0]($_POST['imagepath']);

@imagecopy($image, $imagesrc, 0, 0, $_POST['ldx'], $_POST['luy'], $width, $height);

$swapfile = S_ROOT.'data/temp/swappic_'.$_SGLOBAL['supe_uid'].'.'.$picfiletype;

@$pictypes[$picfiletype][1]($image, $swapfile);

$makethumb = makethumb($swapfile, array($_POST['imagewidth'], $_POST['imageheight']), A_DIR.'/'.$_POST['thumbimg']);

@unlink ($swapfile);

$charset = $_SC['charset'];
print <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=$charset" />
<title>$blang[thumb_image_ok]</title>
</head>
<script language="JavaScript">
<!--
function refreshParentImage(iamgeid) {
	var imagereload = opener.document.getElementById(iamgeid)
	imagereload.src = imagereload.src
	window.close();
}
//-->
</script>

<body onLoad="refreshParentImage('img$_POST[imageid]')">
<a href="./attachments/$_POST[thumbimg]"><img src="./attachments/$_POST[thumbimg]" border="0" /></a>
$blang[thumb_image_ok]<input type=button value="$blang[close_windows]" onClick="window.close();">
</body>
</html>
END;

?>