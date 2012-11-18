<?exit?>
<!--{template header}-->
<!--{eval $ads = getad('system', $channel, '1'); }-->
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
			<li <!--{if $key == $channel }--> class="current"<!--{/if}-->><a href="$value[url]">$value[name]</a></li>
			<!--{/loop}-->
		</ul>
	</div>

	<!--{block name="category" parameter="type/$channel/isroot/1/order/c.displayorder/limit/0,100/cachetime/80800/cachename/category"}-->
	<ul class="ext_nav clearfix">
		<!--{eval $dot = '|'}-->
		<!--{eval $total = count($_SBLOCK['category'])}-->
		<!--{eval $i = 1;}-->
		<!--{loop $_SBLOCK['category'] $value}-->
		<li><a href="$value[url]">$value[name]</a><!--{if $total != $i}--> $dot <!--{/if}--></li>
		<!--{eval $i++;}-->
		<!--{/loop}-->
	</ul>
</div><!--nav end-->

<!--{block name="spacenews" parameter="type/$channel/haveattach/2/showattach/1/showdetail/1/order/i.dateline DESC/limit/0,11/subjectlen/48/subjectdot/0/messagelen/170/messagedot/1/cachetime/11930/cachename/picnews"}-->
<div class="column">
	<div class="col1">
		<div class="clearfix">
			<div class="col3">
			
			<div id="focus_turn">
			<!--{if !empty($_SBLOCK['picnews'])}-->
			<!--{eval $picnews = @array_slice($_SBLOCK['picnews'], 0, 5);}-->
				<ul id="focus_pic">
					<!--{eval $j = 0}-->
					<!--{loop $picnews $pkey $pvalue}-->
					<!--{eval $pcurrent = ($j == 0 ? 'current' : 'normal');}-->
					<li class="$pcurrent"><a href="#action/viewnews/itemid/$pvalue[itemid]#"><img src="$pvalue['a_filepath']" alt="$pvalue[subject]" /></a></li>
					<!--{eval $j++}-->
					<!--{/loop}-->
				</ul>
				<ul id="focus_tx">
					<!--{eval $i = 0}-->
					<!--{loop $picnews $key $value}-->
					<!--{eval $current = ($i == 0 ? 'current' : 'normal');}-->
					<li class="$current"><a href="$value[url]">$value[subject]</a></li>
					<!--{eval $i++}-->
					<!--{/loop}-->
				</ul>
				<div id="focus_opacity"></div>
			<!--{/if}-->
			</div><!--focus_turn end-->
	
			</div><!--col3 end-->
		<!--最新资讯-->
		<!--{block name="spacenews" parameter="type/$channel/order/i.dateline DESC/limit/0,3/cachetime/85400/subjectlen/40/subjectdot/0/showdetail/1/messagelen/150/messagedot/1/cachename/newnews1"}-->
			<div class="col4" id="hot_news">
				<h3>最新资讯</h3>
				<!--{loop $_SBLOCK['newnews1'] $value}-->
				<div class="hot_news_list">
					<h4><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></h4>
					<p>$value[message]</p>
				</div>
				<!--{/loop}-->
			</div><!--col4 end-->
		</div>

		<!--图文资讯显示-->
		<!--{if !empty($_SBLOCK['picnews'])}-->
		<!--{eval $picnews2 = @array_slice($_SBLOCK['picnews'], 5, 11);}-->
		<!--{/if}-->
		<div class="global_module margin_bot10">
			<div class="global_module1_caption"><h3>图文资讯</h3></div>
			<ul class="globalnews_piclist clearfix">
				<!--{loop $picnews2 $value}-->
				<li><a href="$value[url]"><img src="$value[a_thumbpath]" alt="$value[subjectall]"/></a><span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></span></li>
				<!--{/loop}-->
			</ul>
		</div>

		<!--{if !empty($ads['pagecenterad'])}-->
		<div class="ad_pagebody">$ads[pagecenterad]</div>
		<!--{/if}-->

		<div class="catalog_list clearfix">
		<!--各分类最新资讯列表-->
		<!--{eval $i = 1;}-->
		<!--{loop $_SBLOCK['category'] $ckey $cat}-->
		<!--{eval $cachetime = 1800+30*$ckey;}-->
		<!--{block name="spacenews" parameter="catid/$cat[subcatid]/order/i.dateline DESC/limit/0,6/cachetime/$cachetime/subjectlen/36/subjectdot/0/cachename/newslist"}-->
			 <!--{if ($i % 2) == 0}-->
			<div class="global_module box_r">
			<!--{else}-->
			<div class="global_module">
			<!--{/if}-->
				<div class="global_module1_caption"><h3>$cat[name]</h3><a href="#action/category/catid/$cat[catid]#" class="more">更多&gt;&gt;</a></div>
				<ul class="global_tx_list1">
					<!--{loop $_SBLOCK['newslist'] $value}-->
					<li><span class="box_r">#date('m-d', $value['dateline'])#</span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
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
		<!--{block name="tag" parameter="order/spacenewsnum DESC/limit/0,30/cachetime/88008/cachename/hottag/tpl/data"}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>热门标签</h3></div>
			<ul class="tag_list clearfix">
					<!--{loop $_SBLOCK['hottag'] $value}-->
					<li><a href="$value[url]">$value[tagname]</a>($value[spacenewsnum])</li>
					<!--{/loop}-->
			</ul>
		</div>

		<!--最新评论资讯显示-->
		<!--{block name="spacenews" parameter="type/$channel/order/i.lastpost DESC/limit/0,10/subjectlen/26/subjectdot/0/cachetime/7500/cachename/newnews"}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>最新评论</h3></div>
			<ul class="global_tx_list3">
					<!--{loop $_SBLOCK['newnews'] $value}-->
					<li><span class="box_r">#date('m-d', $value['lastpost'])#</span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
					<!--{/loop}-->
			</ul>
		</div>

		<!--月度评论热点-->
		<!--{block name="spacenews" parameter="type/$channel/lastpost/2592000/order/i.replynum DESC/limit/0,10/cachetime/75400/subjectlen/26/subjectdot/0/cachename/replyhot"}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>月度评论</h3></div>
			<ul class="global_tx_list3">
					<!--{loop $_SBLOCK['replyhot'] $value}-->
					<li><span class="box_r">$value[replynum]</span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
					<!--{/loop}-->
			</ul>
		</div>

	</div><!--col2 end-->
</div><!--column end-->

<!--{if !empty($ads['pagefootad'])}-->
<div class="ad_pagebody">$ads[pagefootad]</div>
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