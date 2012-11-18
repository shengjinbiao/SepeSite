<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.epitome.php 11192 2009-02-25 01:45:53Z zhaofei $
*/

include_once('./common.php');
include_once(S_ROOT.'./language/batch.lang.php');

//没有登录
if(empty($_SGLOBAL['supe_uid']) || empty($_SGLOBAL['member']['password'])) {
	setcookie('_refer', rawurlencode(S_URL_ALL.'/admincp.php?'.$_SERVER['QUERY_STRING']));
	showmessage('admincp_login', geturl('action/login'));
}

$defaultimagesize = 800;

//判断是否支持DG库
$loadGD = function_exists('imagejpeg');
if(!$loadGD){
	showmessage('GD_lib_no_load');
}

//判断数据合法性
if($_GET['imageauthcode'] != md5( $_GET['img'].$_SCONFIG['sitekey'].$_GET['imgw'].$_GET['id'].$_GET['imgh'].$_SGLOBAL['authkey'].$_GET['thumbimg'] )) {
	showmessage('parameter_chenged');
}

$getimagesize = $imageshow = $imageshowh = '';
$getimagesizebak = $getimagesize = @getimagesize($_GET['img']);
$_GETbak = $_GET;
$style = '';
if($getimagesize[0] > $getimagesize[1]) {
	if($getimagesize[0] > $defaultimagesize) {
		$style = 'width: '.$defaultimagesize.'px;';
		$getimagesize[1] = round($defaultimagesize/$getimagesize[0] * $getimagesize[1]);
		$getimagesize[0] = $defaultimagesize;
	}
} else {
	if($getimagesize[1] > $defaultimagesize) {
		$style = 'height: '.$defaultimagesize.'px;';
		$getimagesize[0] = round($defaultimagesize/$getimagesize[1] * $getimagesize[0]);
		$getimagesize[1] = $defaultimagesize;
	}
}

$_GET['imgw'] = round(($getimagesize[0] / $getimagesizebak[0]) * $_GET['imgw']);
$_GET['imgh'] = round(($getimagesize[1] / $getimagesizebak[1]) * $_GET['imgh']);
$loopimage =  $getimagesize[1] / $getimagesizebak[1];

if(($getimagesize[0] < $_GET['imgw']) || ($getimagesize[1] < $_GET['imgh'])) {
	showmessage('image_little');
}

$imageshow= $getimagesize[0]+100;
$arrowheadshow= $getimagesize[0]+24;
$imageshowh = $_GET['imgh'] + 60;

$footerheight = $getimagesize[1] + 150;
$s_ver = S_VER;
$formhash = formhash();
print <<<END
<HTML xmlns:v>
<BODY onLoad="resizeTo(screen.availWidth,screen.availHeight);moveTo(0,0);">
<style>
#tbHole td{background:white;filter:alpha(opacity=50);-moz-opacity:.5}
#tbHole img{width:1;height:1;visibility:hidden}
v\:*{behavior:url(#default#vml)}
body { background-color: #FFF; font-size: 12px;color: #BC7D07;}
.arrowhead{width:60px; height:100px;background:url(images/base/ico_arrowhead.jpg) no-repeat center bottom;position:absolute;left:$arrowheadshow;top:100;}
#footer{border-top:1px solid #CCC;overflow:hidden;position:absolute;top:$footerheight; width:100%; left:0; background-color:#EEE;}
#footer p{ text-align:center; height:40px;line-height:40px;}
.copyright {font-family:Verdana,Arial,Helvetica,sans-serif;font-size:0.83em;margin:0pt;}
.copyright strong {color:#ED1C24;text-transform:uppercase;}
.copyright strong span {color:#0954A6;}
.copyright em {color:#96A800;font-style:normal;font-weight:bold;}
</style>
<div style="position:absolute;left:0;top:0;">
<img src="$_GET[img]" style="$style">
</div>
<div id=bxHole onselectstart=return(false) ondragstart=return(false) onmousedown=return(false) oncontextmenu=return(false) style="position:absolute;left:0;top:0;width:{$getimagesize[0]}px; height: {$getimagesize[1]}px;border:2px solid #F5A40C;">
    <table id=tbHole cellpadding=0 cellspacing=0 width=100% height=100% style=position:absolute>
        <tr height=1><td width=1><img></td><td width=$_GET[imgw]><img></td><td><img></td></tr>
        <tr height=$_GET[imgh]>
            <td><img></td>
            <td onmousedown=$('bxHole').dragStart(event,0) style="background:transparent;filter:;-moz-opacity:1;cursor:move;border:1px solid white !important" id=lu><img></td>
            <td id=ru><img></td>
        </tr>
        <tr><td><img></td><td id=ld><img></td><td id=rd><img></td></tr>
    </table>	
    <img id=bxHoleMove1 src=$_GET[img] onmousedown=$('bxHole').dragStart(event,1) style="cursor:nw-resize;position:absolute;width:1;height:1;border:2px solid white;background:#BCBCBC">
    <img id=bxHoleMove2 src=$_GET[img] onmousedown=$('bxHole').dragStart(event,2) style="cursor:sw-resize;position:absolute;width:1;height:1;border:2px solid white;background:#BCBCBC">
    <img id=bxHoleMove3 src=$_GET[img] onmousedown=$('bxHole').dragStart(event,3) style="cursor:nw-resize;position:absolute;width:1;height:1;border:2px solid white;background:#BCBCBC">
    <img id=bxHoleMove4 src=$_GET[img] onmousedown=$('bxHole').dragStart(event,4) style="cursor:sw-resize;position:absolute;width:1;height:1;border:2px solid white;background:#BCBCBC">
</div>
<div class="arrowhead"></div>
<div id=bxImgHoleShow style="position:absolute;left:$imageshow;top:50;width:$_GET[imgw];height:$_GET[imgh];border:2px solid #FFCC66;overflow:hidden;"></div>
<div id=bxImgHoleShow style="position:absolute;left:$imageshow;top:$imageshowh;width:$_GET[imgw];height:140;border:0px solid #FFCC66;">
<form name="hiddenform" id="hiddenform" method="post" action="./batch.thumb.php">
	  <input type="hidden" name="formhash" value="$formhash">
      <input type="image" src="images/base/btn_cut.jpg" name="Submit" title="$blang[is_image_ok]" onclick="setallpostvar();"/>
      <input type="hidden" name="lux" id="lux" />
      <input type="hidden" name="luy" id="luy" />
      <input type="hidden" name="ldx" id="ldx" />
      <input type="hidden" name="ldy" id="ldy" />
      <input type="hidden" name="rux" id="rux" />
      <input type="hidden" name="ruy" id="ruy" />
      <input type="hidden" name="rdx" id="rdx" />
      <input type="hidden" name="rdy" id="rdy" />
      <input type="hidden" name="loop" id="loop" value="$loopimage" />
      <input type="hidden" name="imagepath" id="imagepath" value="$_GET[img]"/>
      <input type="hidden" name="imagewidth" id="imagewidth" value="$_GETbak[imgw]"/>
      <input type="hidden" name="imageheight" id="imageheight" value="$_GETbak[imgh]"/>
      <input type="hidden" name="thumbimg" id="thumbimg" value="$_GET[thumbimg]"/>
      <input type="hidden" name="imageauthcode" id="imageauthcode" value="$_GET[imageauthcode]"/>
      <input type="hidden" name="imageid" id="imageid" value="$_GET[id]"/>
<br />$blang[ps_image_msg]
</form>
</div>
<div id="footer">
	<p class="copyright">Powered by <a target="_blank" href="http://www.supesite.com"><strong><span>SupeSite</span></strong></a> <em>$s_ver</em> &copy; 2001-2008 <a target="_blank" href="http://www.comsenz.com">Comsenz Inc.</a></p>
</div>
</BODY>
</html>
<script type="text/javascript" language="javascript">
	var squitimage = "$_GET[img]"
	var imagesizew=$getimagesize[0]
	var imagesizeh=$getimagesize[1]
</script>

<script src="include/js/squit.js" language="javascript" type="text/javascript"></script>

END;

?>