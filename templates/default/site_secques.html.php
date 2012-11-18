<?exit?>
<!--{if empty($_SGLOBAL['inajax'])}-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=$_SC[charset]" />
<title>$title $_SCONFIG[seotitle] - Powered by SupeSite</title>
<meta name="keywords" content="$keywords $_SCONFIG[seokeywords]" />
<meta name="description" content="$description $_SCONFIG[seodescription]" />
<meta name="generator" content="SupeSite 7.5" />
<meta name="author" content="SupeSite Team and Comsenz UI Team" />
<meta name="copyright" content="2001-2008 Comsenz Inc." />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<link rel="stylesheet" type="text/css" href="{S_URL}/templates/$_SCONFIG[template]/css/common.css" />
$_SCONFIG[seohead]
<script type="text/javascript" src="{S_URL}/templates/$_SCONFIG[template]/js/common.js"></script>
<script type="text/javascript">
var siteUrl = "{S_URL}";
</script>
<script src="{S_URL}/include/js/ajax.js" type="text/javascript" language="javascript"></script>
<script src="{S_URL}/include/js/common.js" type="text/javascript" language="javascript"></script>
<script>
var lastSecCode = '';
function checkSeccode() {
	var seccodeVerify = $('seccode').value;
	if(seccodeVerify == lastSecCode) {
		return;
	} else {
		lastSecCode = seccodeVerify;
	}
	ajaxresponse('checkseccode', 'op=checkseccode&seccode=' + (is_ie && document.charset == 'utf-8' ? encodeURIComponent(seccodeVerify) : seccodeVerify));
}
function ajaxresponse(objname, data) {
	var x = new Ajax('XML', objname);
	x.get('{S_URL_ALL}/do.php?action=register&' + data, function(s){
		var obj = $(objname);
		if(trim(s) == 'succeed') {
			obj.style.display = '';
			obj.innerHTML = '<img src="images/base/check_right.gif" width="13" height="13">';
			obj.className = "warning";
		} else {
			warning(obj, s);
		}
	});
}
function warning(obj, msg) {
	if((ton = obj.id.substr(5, obj.id.length)) != 'password2') {
		$(ton).select();
	}
	obj.style.display = '';
	obj.innerHTML = '<img src="images/base/check_error.gif" width="13" height="13"> &nbsp; ' + msg;
	obj.className = "warning";
}
</script>
</head>
<!--{/if}-->
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
	<div class="global_module3_caption"><h3>你的位置：用户登录</h3></div>		
	<div id="login">
		<div class="box_l">
				<form id="loginform" action="{S_URL}/batch.login.php?action=login" method="post">
				<table>
					<tbody>
					<!--{if empty($_SCONFIG['noseccode'])}-->
					<tr>
					<th width="50">验证码</th>
					<td>
						<script>seccode();</script> 
						<p style="padding-bottom:6px;">
						请输入上面的4位字母或数字，看不清可<a style="color:#2C629E;" href="javascript:updateseccode()">更换一张</a></p>
						<input type="text" class="input_tx" size="10" id="seccode" name="seccode" value="" onblur="checkSeccode()"/>
						<span id="checkseccode" class="warning">&nbsp;</span>
					</td>
					</tr>
					<tr>
						<th>&nbsp;</th>
						<td>
						<input class="input_search" type="submit" id="securitysubmit" value="登录" name="securitysubmit"/>
						<input type="hidden" name="username" value="$username">
						<input type="hidden" name="password" value="$password" />
						<input type="hidden" name="cookietime" value="$cookietime" />
						<input type="hidden" name="loginsubmit" value="yes" />
						<input type="hidden" name="refer" value="$refer" />
						<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
						</td>
					</tr>
					<!--{/if}-->
					</tbody>
				</table>					
				</form>
		</div>
		<div class="box_r">
			<h2>提示</h2>
			<ul>
					<!--{if empty($_SCONFIG['noseccode'])}-->
					<li>由于管理员启用了验证码功能，因此需要您进行第二次安全验证方可登录。</li>
					<li>启用验证码功能，可以加强防灌水机制。</li>
					<!--{/if}-->
			</ul>
		</div>
	</div><!--login end-->

</div><!--column end-->

<!--{template footer}-->