<?exit?>
<!--{template header}-->
<!--{eval $ads2 = getad('system', $channel, '2'); }-->
<!--{if !empty($ads2['pageheadad']) }-->
	<div class="ad_header">$ads2[pageheadad]</div>
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

<div class="column">
	<div class="col1">

		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module3_caption"><h3>
				你的位置：<a href="{S_URL}">$_SCONFIG[sitename]</a>
				<!--{loop $guidearr $value}-->
				&gt;&gt; <a href="$value[url]">$value[name]</a>
				<!--{/loop}-->
				&nbsp;<a href="#action/rss/catid/$thecat[subcatid]#"><img src="{S_URL}/templates/$_SCONFIG[template]/images/icon_rss_ext.gif"></a>
				<!--{if checkperm('allowpost')}-->
				<a href="{S_URL}/cp.php?ac=news&op=add&type=$channel" title="在线投稿" class="btn_capiton_op" target="_blank">在线投稿</a>
				<!--{/if}-->
				</h3>
				</div>
			<!--根分类最新日志列表-->
			<!--{if $_SGET['page'] < 2 || empty($_SGET['mode'])}-->
			<!--{block name="spacenews" parameter="perpage/20/catid/$thecat[subcatid]/order/i.dateline DESC/cachename/newlist"}-->
			<!--{if $_SBLOCK['newlist']}-->
			<ul class="global_tx_list4">
				<!--{loop $_SBLOCK['newlist'] $value}-->
				<li><span class="box_r">#date('m-d', $value['dateline'])#</span><a href="$value[url]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
			<!--{/if}-->
			<!--{if $_SBLOCK[newlist_multipage]}-->
				$_SBLOCK[newlist_multipage]
			<!--{/if}-->
			<!--{/if}-->
		</div>
		<!--论坛资源列表-->
		<!--{if !empty($thecat['bbsmodel'])}-->
		<!--{if $_SGET['page']<2 || !empty($_SGET['mode'])}-->
		<!--{eval $_SGET['mode']='bbs';}-->
		<!--{block name="bbsthread" parameter="perpage/20/$thecat[blockparameter]/cachename/bbsthreadlist/tpl/data"}-->
		<!--{if $_SBLOCK['bbsthreadlist']}-->
		<div class="global_module margin_bot10 bg_fff">
		<div class="global_module1_caption"><h3>论坛资源</h3></div>
			<ul class="global_tx_list4">
				<!--{loop $_SBLOCK['bbsthreadlist'] $value}-->
				<li><span class="box_r">#date('m-d', $value['dateline'])#</span><a href="$value[url]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
			<!--{if $_SBLOCK[bbsthreadlist_multipage]}-->
				$_SBLOCK[bbsthreadlist_multipage]
			<!--{/if}-->
		</div>
		<!--{/if}-->
		<!--{/if}-->
		<!--{/if}-->



		<!--{block name="category" parameter="upid/$thecat[catid]/order/c.displayorder/limit/0,100/cachetime/10900/cachename/subarr/tpl/data"}-->
		<!--{if $_SGET['page']<2}-->
		<!--{eval $i = 1;}-->
		<!--{if !empty($_SBLOCK['subarr'])}-->
		<div class="catalog_list clearfix">
			<!--{loop $_SBLOCK['subarr'] $ckey $cat}-->
			<!--{eval $ctime=1800+30*$ckey;}-->
			<!--{block name="spacenews" parameter="catid/$cat[subcatid]/order/i.dateline DESC/limit/0,6/subjectlen/36/subjectdot/0/cachetime/$ctime/cachename/subnewlist/tpl/data"}-->
			<!--{if $_SBLOCK['subnewlist']}-->
			<!--{if ($i % 2) == 0}-->
			<div class="global_module box_r">
			<!--{else}-->
			<div class="global_module">
			<!--{/if}-->
				<div class="global_module1_caption"><h3>$cat[name]</h3><a href="#action/category/catid/$cat[catid]#" class="more">更多&gt;&gt;</a></div>
				<ul class="global_tx_list1">
					<!--{loop $_SBLOCK['subnewlist'] $value}-->
					<li><span class="box_r">#date("m-d", $value["dateline"])#</span><a href="$value[url]">$value[subject]</a></li>
					<!--{/loop}-->
				</ul>
			</div>
			<!--{eval $i++;}-->
			<!--{/loop}-->
			<!--{/if}-->
		</div>
		<!--{/if}-->
		<!--{/if}-->

		<!--{if !empty($ads2['pagecenterad'])}-->
		<div class="ad_mainbody">$ads2[pagecenterad]</div>
		<!--{/if}-->

		<!--{if $_SGET['page']< 2}-->
		<!--图文资讯显示-->
		<!--{block name="spacenews" parameter="catid/$thecat[subcatid]/haveattach/2/showattach/1/order/i.lastpost DESC/limit/0,12/subjectlen/14/subjectdot/0/cachetime/8000/cachename/picnews"}-->
		<!--{if $_SBLOCK['picnews']}-->
		<div class="global_module margin_bot10">
			<div class="global_module1_caption"><h3>图文资讯</h3></div>
			<ul class="globalnews_piclist clearfix">
				<!--{loop $_SBLOCK['picnews'] $value}-->
				<li><a href="$value[url]" title="$value[subjectall]"><img src="$value[a_thumbpath]" alt="$value[subjectall]" /></a><span><a href="$value[url]">$value[subject]</a></span></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->
		<!--{/if}-->


	</div><!--col1 end-->
	<div class="col2">
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->
		
		<!--{if $_SBLOCK['subarr']}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>子分类</h3></div>
			<ul class="special_activity clearfix">
				<!--{loop $_SBLOCK['subarr'] $value}-->
				<li><a href="$value[url]">$value[name]</a></li>
				<!--{/loop}-->

			</ul>
		</div>
		<!--{/if}-->
       	<!--{if $thecat['thumb'] || $thecat['note']}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>$thecat[name]</h3></div>
				<div class="sidebar_album_info">
				<!--{if $thecat['thumb']}-->
				<p><img src="{A_URL}/$thecat[thumb]" alt="" /></p>
				<!--{/if}-->
				<!--{if $thecat['note']}-->
				<p>$thecat[note]</p>
				<!--{/if}-->
				</div>
		</div>
		<!--{/if}-->
	
		<!--{block name="spacenews" parameter="catid/$thecat[subcatid]/order/i.dateline DESC/limit/0,10/subjectlen/26/subjectdot/0/cachetime/13800/cachename/newnews"}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>最新评论</h3></div>
			<ul class="global_tx_list3">
				<!--{loop $_SBLOCK['newnews'] $value}-->
					<li><span class="box_r">#date('m-d', $value['dateline'])#</span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
		</div>

		<!--{block name="spacenews" parameter="catid/$thecat[subcatid]/digest/1,2,3/order/i.viewnum DESC,i.dateline DESC/limit/0,10/cachetime/89877/subjectlen/26/subjectdot/0/cachename/hotnews2"}-->
		<!--{if $_SBLOCK['hotnews2']}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>精华推荐</h3></div>
			<ul class="global_tx_list3">
					<!--{loop $_SBLOCK['hotnews2'] $value}-->
					<li><span class="box_r">$value[viewnum]</span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
					<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->

		<!--{if !empty($ads2['siderad'])}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>网络资源</h3></div>
			<div class="ad_sidebar">
				$ads2[siderad]
			</div>

		</div>
		<!--{/if}-->


	</div><!--col2 end-->
</div><!--column end-->

<!--{if !empty($ads2['pagefootad'])}-->
<div class="ad_pagebody">$ads2[pagefootad]</div>
<!--{/if}-->

<!--{if !empty($ads2['pagemovead']) || !empty($ads2['pageoutad'])}-->
<!--{if !empty($ads2['pagemovead'])}-->
<div id="coupleBannerAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
	<div style="position: absolute; left: 6px; top: 6px;">
		$ads2[pagemovead]
		<br />
		<img src="{S_URL}/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
	</div>
	<div style="position: absolute; right: 6px; top: 6px;">
		$ads2[pagemovead]
		<br />
		<img src="{S_URL}/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
	</div>
</div>
<!--{/if}-->
<!--{if !empty($ads2['pageoutad'])}-->
<div id="floatAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
	<div id="floatFloor" style="position: absolute; right: 6px; bottom:-700px">
		$ads2[pageoutad]
	</div>
</div>
<!--{/if}-->
<script type="text/javascript" src="{S_URL}/include/js/floatadv.js"></script>
<script type="text/javascript">
<!--{if !empty($ads2['pageoutad'])}-->
var lengthobj = getWindowSize();
lsfloatdiv('floatAdv', 0, 0, 'floatFloor' , -lengthobj.winHeight).floatIt();
<!--{/if}-->
<!--{if !empty($ads2['pagemovead'])}-->
lsfloatdiv('coupleBannerAdv', 0, 0, '', 0).floatIt();
<!--{/if}-->
</script>
<!--{/if}-->

<!--{template footer}-->