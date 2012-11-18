<?exit?>
<!--{template header}-->
</div>
<!-- 调用站点header.html.php模板文件 -->

<div id="nav">
	<div class="main_nav">
		<ul>
			<!--{if empty($_SCONFIG['defaultchannel'])}-->
			<li><a href="{S_URL}/">首页</a></li>
			<!--{/if}-->
			<!--{loop $channels['menus'] $key $value}-->
			<li <!--{if $key == $_SGET['name'] }--> class="current"<!--{/if}-->><a href="$value[url]">$value[name]</a></li>
			<!--{/loop}-->
		</ul>
	</div>

</div><!--nav end-->
<div class="column">
<div class="col1">
	<!-- 模块使用范例开始 -->
<h3>欢迎使用SupeSite自定义频道</h3>
在频道模板页面中，您可以通过 SupeSite 强大的模块功能，进行自由组合，对Discuz!论坛、UCHome上面的数据信息，进行灵活聚合展示，来创建自己的频道页面。<br />
下面给出的是一个模块使用范例：<br />
显示的就是利用资讯模块，来分页显示站内所有资讯的模板范例(按发布时间递减排序，每页显示20条，并显示内容摘要)，供您参考。您可以使用Dreamweaver等编辑器对本模板进行可视化编辑。
	<!-- 模块代码 -->
	<!-- 这里是模块代码,将满足条件的数据获取到变量$_SBLOCK[变量名]中,使用模块生成向导来生成 -->
	<!--{block name="spacenews" parameter="perpage/20/showattach/1/showdetail/1/order/i.replynum DESC/limit/0,1/subjectlen/34/subjectdot/1/messagelen/220/messagedot/1/cachetime/18600/cachename/headnews"}-->
		<div class="global_module margin_bot10 bg_fff">
       <div class="global_module3_caption">
			<h3>资讯</h3>
       </div>
			<!-- 这里使用loop方法对模块获取的数据$_SBLOCK[变量名]进行循环显示 -->
			<!--{loop $_SBLOCK['headnews'] $value}-->
			<ul class="global_tx_list4">
				<h5><a target="_blank" href="$value[url]">$value[subject]</a></h5>
				<p>$value[message]</p>
			</ul>
			<!--{/loop}-->
			<!-- 这里为分页信息$_SBLOCK[变量名_multipage] -->
			$_SBLOCK[headnews_multipage]
			<!-- 模块使用范例结束 -->
	</div>
</div>
	<div class="col2">
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->
    </div>
</div>




<!-- 调用站点footer.html.php模板文件 -->
<!--{template footer}-->
