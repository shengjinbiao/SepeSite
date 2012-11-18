<?exit?>
<!--{template header}-->
<!--{eval $ads = getad('system', 'bbs', '1'); }-->
<!--{if !empty($ads['pageheadad']) }-->
	<div class="ad_header">$ads[pageheadad]</div>
<!--{/if}-->
</div><!--header end-->

<div id="nav">
	<div class="main_nav">
		<ul>
			<!--{if empty($_SCONFIG['defaultchannel'])}-->
			<li><a href="{S_URL}/">首页</a></li>
			<!--{/if}-->
			<!--{loop $channels['menus'] $key $value}-->
			<li <!--{if $key == 'bbs' }--> class="current"<!--{/if}-->><a href="$value[url]">$value[name]</a></li>
			<!--{/loop}-->
		</ul>
	</div>

	<ul class="ext_nav clearfix">
		<!--{eval $dot = '|'}-->
		<!--{eval $total = count($forumarr)}-->
		<!--{eval $i = 1;}-->
		<!--{loop $forumarr $value}-->
		<li><a href="$value[url]">$value[name]</a><!--{if $total != $i}--> $dot <!--{/if}--></li>
		<!--{eval $i++;}-->
		<!--{/loop}-->
	</ul>
</div><!--nav end-->

<div class="column">
	<div class="col1">
		<div class="clearfix margin_bot10">
		<div class="col3">
			<!--图片附件-->
			<!--{block name="bbsattachment" parameter="filetype/image/t_lastpost/2592000/order/t.replies DESC/limit/0,11/cachetime/49900/cachename/picthread/tpl/data"}-->
			<!--{if !empty($_SBLOCK['picthread'])}-->
			<!--{eval $picthread = @array_slice($_SBLOCK['picthread'], 0 , 5)}-->
			<!--{/if}-->
			<div id="focus_turn">
				<!--{if !empty($picthread)}-->
				<ul id="focus_pic">
					<!--{eval $j = 0}-->
					<!--{loop $picthread $pkey $pvalue}-->
					<!--{eval $pcurrent = ($j == 0 ? 'current' : 'normal');}-->
					<li class="$pcurrent"><a href="$pvalue[url]" target="_blank"><img src="$pvalue['attachment']" alt="" /></a></li>
					<!--{eval $j++}-->
					<!--{/loop}-->
				</ul>
				<ul id="focus_tx">
					<!--{eval $i = 0}-->
					<!--{loop $picthread $key $value}-->
					<!--{eval $current = ($i == 0 ? 'current' : 'normal');}-->
					<li class="$current"><a href="$value[url]">$value[subject]</a></li>
					<!--{eval $i++}-->
					<!--{/loop}-->
				</ul>
				<div id="focus_opacity"></div>
				<!--{/if}-->
			</div><!--focus_turn end-->

			<!--{block name="bbsthread" parameter="dateline/2592000/order/replies DESC/limit/0,6/cachetime/82400/subjectlen/40/subjectdot/0/cachename/hotthreadmonth/tpl/data"}-->
			<div id="new_news">
				<h3>本月热点</h3>
				<ul>
					<!--{loop $_SBLOCK['hotthreadmonth'] $value}-->
					<li><span class="box_r"><a href="{S_URL}/space.php?uid=$value[authorid]">$value[author]</a></span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
					<!--{/loop}-->
				</ul>
			</div>
		</div><!--col3 end-->
		
		<div class="col4">
			<!--{block name="bbsthread" parameter="dateline/2592000/order/views DESC/limit/0,3/cachetime/87400/subjectlen/40/subjectdot/0/showdetail/1/messagelen/150/messagedot/1/cachename/hotthreadmonth/tpl/data"}-->
			<div id="hot_news" style=" height:320px; margin-bottom:7px;">
				<h3>热点内容</h3>
				<!--{loop $_SBLOCK['hotthreadmonth'] $value}-->
				<div class="hot_news_list">
					<h4><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></h4>
					<p>$value[message]</p>
				</div>
				<!--{/loop}-->
			</div><!--hot_news end-->
			
			<!--最新帖子-->
			<!--{block name="bbsthread" parameter="order/dateline DESC/limit/0,6/subjectlen/36/subjectdot/0/cachetime/21400/cachename/ratehot/tpl/data"}-->
			<div id="new_news">
				<h3>最新发表</h3>
				<ul>
					<!--{loop $_SBLOCK['ratehot'] $value}-->
					<li><span class="box_r"><a href="{S_URL}/space.php?uid=$value[authorid]">$value[author]</a></span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
					<!--{/loop}-->
				</ul>
			</div>
		</div><!--col4 end-->
		</div>
		
		<div class="global_module margin_bot10">
				<!--{if !empty($_SBLOCK['picthread'])}-->
				<!--{eval $picthread2 = array_slice($_SBLOCK['picthread'], 5 ,11);}-->
				<!--{/if}-->
				<div class="global_module1_caption"><h3>图片主题</h3></div>
				<ul class="globalnews_piclist clearfix">
					<!--{loop $picthread2 $value}-->
					<li><a href="$value[url]"><img alt="" src="$value[attachment]" alt="$value[subject]"/></a></li>
					<!--{/loop}-->
				</ul>
		</div>
		<!--{if !empty($ads['pagecenterad'])}-->
		<div class="ad_mainbody">$ads[pagecenterad]</div>
		<!--{/if}-->
		<div class="catalog_list clearfix">
		
			<!--各板块最新列表-->
			<!--{eval $i = 1;}-->
			<!--{loop $forumarr $ckey $cat}-->
			<!--{eval $ctime=3800+30*$ckey;}-->
			<!--{eval $cachetime=38000+30*$ckey;}-->
			<!--{block name="bbsthread" parameter="fid/$cat[fid]/order/dateline DESC/limit/0,6/cachetime/$ctime/subjectlen/40/subjectdot/0/cachename/threadlist/tpl/data"}-->
			 <!--{if ($i % 2) == 0}-->
			<div class="global_module box_r">
			<!--{else}-->
			<div class="global_module">
			<!--{/if}-->
				<div class="global_module1_caption"><h3>$cat[name]</h3><a class="more" href="#action/forumdisplay/fid/$cat[fid]#">更多&gt;&gt;</a></div>
				<ul class="global_tx_list1">
					<!--{loop $_SBLOCK['threadlist'] $value}-->
					<li><span class="box_r"><a href="{S_URL}/space.php?uid=$value[authorid]">$value[author]</a></span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
					<!--{/loop}-->
				</ul>
			</div>
			<!--{eval $i++;}-->
			<!--{/loop}-->

		</div>
		
	</div><!--col1 end-->
	
	<div class="col2">
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->

	<!--板块根据帖子数排行-->
	<!--{block name="bbsforum" parameter="type/forum/allowblog/1/order/posts DESC/limit/0,10/cachetime/14400/cachename/hotforums/tpl/data"}-->
		<div class="global_module global_tx_list5">
			<div class="global_module2_caption"><h3>版块帖子数排行</h3></div>
			<ul>
				<!--{loop $_SBLOCK['hotforums'] $value}-->
					<li><span class="box_r">$value[posts]</span><a href="$value[url]">$value[name]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
		
		<!--{block name="bbsmember" parameter="order/m.posts DESC/limit/0,10/cachetime/86400/cachename/hotmembers/tpl/data"}-->
		<div class="global_module global_tx_list5">
			<div class="global_module2_caption"><h3>用户发帖排行</h3></div>
			<ul>
				<!--{loop $_SBLOCK['hotmembers'] $value}-->
					<li><span class="box_r">$value[posts]</span><a href="$value[url]">$value[username]</a></li>
				<!--{/loop}-->

			</ul>
		</div>

		<!--{block name="bbsmember" parameter="order/m.oltime DESC/limit/0,10/cachetime/86400/cachename/toponline/tpl/data"}-->
		<div class="global_module global_tx_list5">
			<div class="global_module2_caption"><h3>用户在线排行</h3></div>
			<ul>
					<!--{loop $_SBLOCK['toponline'] $value}-->
					<li><span class="box_r">$value[oltime]小时</span><a href="$value[url]">$value[username]</a></li>
					<!--{/loop}-->
			</ul>
		</div>
		<!--最新更新帖子-->
		<!--{block name="bbsthread" parameter="order/lastpost DESC/limit/0,10/subjectlen/28/subjectdot/0/cachetime/11460/cachename/newpost/tpl/data"}-->
		<!--{if $_SBLOCK['newpost']}-->
		<div class="global_module global_tx_list5">
			<div class="global_module2_caption"><h3>最新评论</h3></div>
			<ul>
				<!--{loop $_SBLOCK['newpost'] $value}-->
					<li><span class="box_r">#date('m-d', $value[lastpost])#</span><a href="$value[url]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->
	</div><!--col2 end-->
</div><!--column end-->


<!--{if !empty($ads['pagefootad'])}-->
<div class="ad_pagebody">$ads['pagefootad']</div>
<!--{/if}-->

<!--{if !empty($ads['pagemovead']) || !empty($ads['pageoutad'])}-->
<!--{if !empty($ads['pagemovead'])}-->
<div id="coupleBannerAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
	<div style="position: absolute; left: 6px; top: 6px;">
		$ads[pagemovead]
		<br />
		<img src="{S_URL}/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
	</div>
	<div style="position: absolute; right: 6px; top: 6px;">
		$ads[pagemovead]
		<br />
		<img src="{S_URL}/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
	</div>
</div>
<!--{/if}-->
<!--{if !empty($ads['pageoutad'])}-->
<div id="floatAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
	<div id="floatFloor" style="position: absolute; right: 6px; bottom:-700px">
		$ads[pageoutad]
	</div>
</div>
<!--{/if}-->
<script type="text/javascript" src="{S_URL}/include/js/floatadv.js"></script>
<script type="text/javascript">
<!--{if !empty($ads['pageoutad'])}-->
var lengthobj = getWindowSize();
lsfloatdiv('floatAdv', 0, 0, 'floatFloor' , -lengthobj.winHeight).floatIt();
<!--{/if}-->
<!--{if !empty($ads['pagemovead'])}-->
lsfloatdiv('coupleBannerAdv', 0, 0, '', 0).floatIt();
<!--{/if}-->
</script>
<!--{/if}-->

<!--{if !empty($ads['pageoutindex'])}-->
$ads[pageoutindex]
<!--{/if}-->

<!--{template footer}-->