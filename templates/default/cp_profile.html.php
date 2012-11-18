<?exit?>
<!--{template cp_header}-->

	<ul class="ext_nav clearfix">
		<li><a href="cp.php?ac=profile">个人概括</a></li>
		<li><a href="cp.php?ac=profile&op=avatar">头像管理</a></li>
		<li><a href="cp.php?ac=profile&op=email">邮箱管理</a></li>
		<li><a href="cp.php?ac=profile&op=pwd">密码管理</a></li>
	</ul>
</div>

<div class="column">
	<div class="col1" >
		<!--{if empty($op)}-->
			<div class="global_module margin_bot10 bg_fff userpanel">
				<div class="global_module3_caption"><h3>你的位置：<a href="cp.php?ac=profile">个人概括</a></h3></div>
				<div class="sumup">
					<table><tbody>
						<tr><td width="200">积分数</td>
							<td><span class="big_red">{$_SGLOBAL['member'][credit]}</span></td></tr>
						<tr><td>经验值</td>
							<td><span class="big_red">{$_SGLOBAL['member'][experience]}</span></td></tr>
						<tr><td>所在用户组</td>
							<td>{$_SGLOBAL['grouparr'][$_SGLOBAL['member']['groupid']]['grouptitle']}</td></tr>
						<tr><td>创建时间</td>
							<td>#date('Y-m-d',$_SGLOBAL['member']['dateline'])#</td></tr>
						<tr><td>上次登录</td>
							<td>
							<!--{if ($_SGLOBAL['timestamp'] - $_SGLOBAL['member']['lastlogin']) > 86400}--> 
							#date("Y-m-d", $value[updatetime])#
							<!--{else}-->
							<!--{eval echo intval(($_SGLOBAL['timestamp'] - $_SGLOBAL['member']['lastlogin']) / 3600 + 1);}-->小时之前
							<!--{/if}-->
							</td></tr>
						<tr><td>最后更新</td>
							<td>#date('Y-m-d',$_SGLOBAL['member']['updatetime'])#</td></tr>
					</tbody></table>
				</div>
			</div>
		<!--{elseif $op=='avatar'}-->
			<div class="global_module margin_bot10 bg_fff userpanel">
				<div class="global_module3_caption"><h3>你的位置：<a href="cp.php?ac=profile&op=avatar">头像管理</a></h3></div>
				<div class="upavatarbox">
					<table>
						<tbody>
							<tr class="font_weight">
								<td width="170">当前我的头像</td>
								<td>更改我的头像</td>
							</tr>
							<tr>
								<td><img src="{UC_API}/avatar.php?uid=$_SGLOBAL[supe_uid]&rand=$_SGLOBAL[timestamp]" id="avatar" /></td>
								<td>$uc_avatarflash</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		<!--{elseif $op=='email'}-->
			<form action="cp.php?ac=profile" method="post">
				<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
				<div class="global_module margin_bot10 bg_fff userpanel">
					<div class="global_module3_caption"><h3>你的位置：<a href="cp.php?ac=profile&amp;op=email">邮箱管理</a></h3></div>
					<div class="setmail">
						你填写的邮箱是保密的，取回密码都将发送到该邮箱。<br />
						<table><tbody>
							<tr>
								<td width="70"><label for="setmail_pass">登录密码:</label></td>
								<td><input class="input_tx" id="setmail_pass" type="password" size="30" name="password"/></td>
							</tr>
							<tr>
								<td><label for="setmail_mail">真实邮箱:</label></td>
								<td><input class="input_tx" id="setmail_mail" type="text" size="30" value="$email" name="email"/></td>
							</tr>
							<tr>
								<td></td>
								<td><input class="input_search" type="submit" name="updateemailvalue" value="确定"/>
									<input class="input_search" type="reset" name="searchbtn" value="重置"/></td>
							</tr>
						</tbody></table>	
					</div>
				</div>
			</form>
		<!--{elseif $op=='pwd'}-->
			<form action="$cpurl?action=profile" method="post">
				<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
				<div class="global_module margin_bot10 bg_fff userpanel">
					<div class="global_module3_caption"><h3>你的位置：<a href="cp.php?ac=profile&amp;op=pwd">密码管理</a></h3></div>
					<div class="setmail">
						<table><tbody>
							<tr>
								<td width="70"><label for="setmail_pass">登陆用户名:</label></td>
								<td>$_SGLOBAL[supe_username]</td>
							</tr>
							<tr>
								<td><label for="setmail_pass">旧密码:</label></td>
								<td><input class="input_tx" id="setmail_pass" type="password" size="30" name="password"/></td>
							</tr>
							<tr>
								<td><label for="setmail_mail">新密码:</label></td>
								<td><input class="input_tx" id="setmail_mail" type="password" size="30" value="" name="newpasswd1"/></td>
							</tr>
							<tr>
								<td><label for="setmail_mail">确认新密码:</label></td>
								<td><input class="input_tx" id="setmail_mail" type="password" size="30" value="" name="newpasswd2"/></td>
							</tr>
							<tr>
								<td></td>
								<td><input class="input_search" type="submit" name="pwdsubmit" value="确定"/>
									<input class="input_search" type="reset" name="searchbtn" value="重置"/></td>
							</tr>
						</tbody></table>
					</div>
				</div>
			</form>
		<!--{/if}-->
	</div>
	
	<div class="col2" >
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->

		<div id="contribute" class="global_module bg_fff margin_bot10">
			<div class="global_module2_caption"><h3>频道</h3></div>
			<ul>
				<!--{loop $channels['menus'] $value}-->
					<!--{if $value[type]=='type' || $value[upnameid]=='news'}-->
					<li<!--{if $value[nameid]==$type}--> class="current"<!--{/if}--> onclick="window.location.href='{S_URL}/cp.php?ac=news&op=list&do=$do&type=$value[nameid]&{eval echo rand(1, 999999)}';">
						<span><!--{if $value[nameid]==$type}-->共($listcount)条 当前频道<!--{else}-->浏览<!--{/if}--></span>
						<a>$value[name]</a></li>
					<!--{elseif $value[type]=='model'}-->
					<li onclick="window.location.href='{S_URL}/cp.php?ac=models&op=list&do=$do&nameid=$value[nameid]&{eval echo rand(1, 999999)}';">
						<span>浏览</span>
						<a>$value[name]</a></li>
					<!--{/if}-->
				<!--{/loop}-->
			</ul>
		</div>
	</div><!--col2-->
</div>
<!--{template cp_footer}-->