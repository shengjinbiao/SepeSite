<?exit?>
<!--{template header}-->
<!--{if empty($_SGLOBAL['inajax'])}-->
</div>
<!--{/if}-->

<!--{if $_GET['op'] == 'show'}-->
<!--{if empty($_SGLOBAL['inajax'])}-->
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

<div class="column">
	<div class="col1">
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module3_caption"><h3>你的位置：<a href="{S_URL}">$_SCONFIG[sitename]</a>
				<!--{loop $guidearr $value}-->
				&gt;&gt; <a href="$value[url]">$value[name]</a>
				<!--{/loop}-->
				&gt;&gt; 表态查看
				</h3>
			</div>
			<div id="click_div">
<!--{/if}-->
			<!--{if $idtype == 'models'}-->
			<!--{template model_click}-->
			<!--{else}-->
			<!--{template news_click}-->
			<!--{/if}-->

<!--{if empty($_SGLOBAL['inajax'])}-->
			</div>
		</div>
	</div><!--col1 end-->

	<div class="col2">
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->
	</div><!--col2 end-->
</div><!--column end-->
<!--{/if}-->
<!--{/if}-->

<!--{template footer}-->