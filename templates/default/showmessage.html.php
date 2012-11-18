<?exit?>
<!--{if empty($_SGLOBAL['inajax'])}-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=$_SC[charset]" />
<title>消息提示 - $_SCONFIG[sitename] $_SCONFIG[seotitle]- Powered by SupeSite</title>
<meta name="keywords" content="$keywords $_SCONFIG[seokeywords]" />
<meta name="description" content="$description $_SCONFIG[seodescription]" />
<meta name="generator" content="SupeSite 7.5" />
<meta name="author" content="SupeSite Team and Comsenz UI Team" />
<meta name="copyright" content="2001-2009 Comsenz Inc." />
<link rel="stylesheet" type="text/css" href="{S_URL}/templates/$_SCONFIG[template]/css/common.css" />
$_SCONFIG[seohead]
<script type="text/javascript">
var siteUrl = "{S_URL}";
</script>
<script src="{S_URL}/include/js/ajax.js" type="text/javascript" language="javascript"></script>
<script src="{S_URL}/include/js/common.js" type="text/javascript" language="javascript"></script>
<script type="text/javascript" src="{S_URL}/templates/$_SCONFIG[template]/js/common.js"></script>
</head>

<body>

<div id="header">
	<h2><a href="{S_URL}"><img src="{S_URL}/images/logo.gif" alt="$_SCONFIG[sitename]" /></a></h2>
</div><!--header end-->

<div id="nav">
	<div class="main_nav">
		<ul>
			<!--{if empty($_SCONFIG['defaultchannel'])}-->
			<li><a href="{S_URL}/">首页</a></li>
			<!--{/if}-->
			<!--{loop $channels['menus'] $key $value}-->
			<li><a href="$value[url]">$value[name]</a></li>
			<!--{/loop}-->
		</ul>
	</div>
</div><!--nav end-->

<div class="column global_module bg_fff">
	<div class="global_module3_caption"><h3><a href="{S_URL}/">信息提示</a></h3></div>
	
	<div id="infopage">
		<div class="infopage_content">
		<div>
<!--{/if}-->
			<!--{if empty($_SGLOBAL['inajax'])}--><h1><!--{/if}-->
			<!--{if $url_forward}--><a href="$url_forward">$message</a><!--{else}-->$message<!--{/if}-->
<!--{if empty($_SGLOBAL['inajax'])}-->
			</h1>
			<!--{if $url_forward}-->
			<a href="$url_forward">确定</a>
			<!--{else}-->
			<a href="javascript:history.back();">返回上一页</a>
			<!--{/if}-->
		</div>
		</div>
	</div><!--infopage end-->

</div>

<!--{/if}-->
<!--{template footer}-->