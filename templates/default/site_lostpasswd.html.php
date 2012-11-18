<?exit?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=$_SC[charset]" />
<title>找回密码 - $_SCONFIG[sitename] $_SCONFIG[seotitle]- Powered by SupeSite</title>
<meta name="keywords" content="$keywords $_SCONFIG[seokeywords]" />
<meta name="description" content="$description $_SCONFIG[seodescription]" />
<meta name="generator" content="SupeSite 7.5" />
<meta name="author" content="SupeSite Team and Comsenz UI Team" />
<meta name="copyright" content="2001-2009 Comsenz Inc." />
<link rel="stylesheet" type="text/css" href="templates/$_SCONFIG[template]/css/common.css" />
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
	<div class="global_module3_caption"><h3>你的位置：<a href="#action/site/type/lostpasswd#">找回密码</a></h3></div>
	
	<div class="lost_pw">
		<!--{if empty($_GET['op'])}-->
		<!--{if empty($_POST['username'])}-->
		<form name="lostpasswdform" action="{S_URL}/do.php?action=lostpasswd" method="post">
		<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
		<input type="hidden" value="true" name="lostpwsubmit"/>
		<table>
		<tbody>
		<tr>
		<th width="100"/>
		<td class="tip_tx">
		第一步，请输入在本站注册的用户名。
		</td>
		</tr>
		<tr>
		<th>用户名</th>
		<td><input type="text" value="" class="input_tx" name="username" size="30"/> </td>
		</tr>
		<tr>
		<th/>
		<td><input type="submit" value="提交" class="input_search"/></td>
		</tr>
		</tbody>
		</table>
		</form>
		<!--{elseif empty($_POST['email'])}-->
		<form method="post" action="{S_URL}/do.php?action=lostpasswd" name="lostpasswdform">
		<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
		<input type="hidden" name="username" value="$_POST[username]"  />
		<input type="hidden" name="lostpwsubmit" value="true"  />
		<table>
			<tbody>
				<tr>
					<th width="100"></th>
					<td class="tip_tx">
						第二步，请输入在本站注册的Email地址。
					</td>
				</tr>
				<tr>
					<th>邮箱</th>
					<td><input size="30" type="text" class="input_tx" name="email" value="$uemail" /> </td>
				</tr>
				<tr>
					<th></th>
					<td><input class="input_search" type="submit" value="提交"/></td>
				</tr>
			</tbody>
		</table>
		</form>
		<!--{/if}-->
		<!--{elseif $_GET['op'] == 'reset'}-->
		<form method="post" action="{S_URL}/do.php?action=lostpasswd" name="lostpasswdform">
		<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
		<input type="hidden" name="resetpasswd" value="true"  />
		<input type="hidden" name="uid" value="$_GET[uid]"  />
		<input type="hidden" name="email" value="$user[2]"  />
		<input type="hidden" name="id" value="$_GET[id]"  />
		<table>
			<tbody>
				<tr>
					<th width="100">用户名</th>
					<td class="tip_tx">
						$member[username]
					</td>
				</tr>
				<tr>
					<th>新密码</th>
					<td><input size="30" type="password" name="newpasswd" class="input_tx" value="" /> </td>
				</tr>
				<tr>
					<th>确认新密码</th>
					<td><input size="30" type="password" name="newpasswd_check" class="input_tx" value="" /> </td>
				</tr>
				<tr>
					<th></th>
					<td><input class="input_search" type="submit" value="提交"/></td>
				</tr>
			</tbody>
		</table>
		</form>
		<!--{/if}-->
	</div>

</div>

<!--{template footer}-->