<?exit?>
<!--{template header}-->
<!--{eval $ads2 = getad('system', 'bbs', '2'); }-->
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
		
<!--{if !empty($ads2['pagecenterad'])}-->
<div class="ad_pagebody">$ads2[pagecenterad]</div>
<!--{/if}-->

<div class="column">
	<div class="col1">

		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module3_caption">
			<h3>
			<em style="float:right; padding:0 5px 0 0;">
			<a href="{B_URL}/forumdisplay.php?fid=$fid" title="转至$channels[menus][bbs][name]" class="vote" target="_blank">转至$channels[menus][bbs][name]</a>
			</em>
			您的位置：<a href="{S_URL}/">$_SCONFIG[sitename]</a>
			<!--{loop $guidearr $value}-->
			&gt;&gt; <a href="$value[url]">$value[name]</a>
			<!--{/loop}--></h3></div>

			<!--根分类最新帖子列表-->
			<!--{if $forum['type'] != 'group'}-->
			<!--{block name="bbsthread" parameter="perpage/20/fid/$fid/showdetail/1/messagelen/160/messagedot/1/order/dateline DESC/cachetime/44400/cachename/newlist/tpl/data"}-->
			<!--{if $_SBLOCK['newlist']}-->
			
			<ul class="news_list">
				<!--{loop $_SBLOCK['newlist'] $value}-->
				<li>
					<h4><a href="$value[url]">$value[subject]</a></h4>
					<p class="news_list_caption"><a href="#uid/$value[authorid]#">$value[author]</a> 发表于:#date("Y-m-d", $value["dateline"])# 回复:$value[replies]</p>
					<p>$value[message]</p>
				</li>
				<!--{/loop}-->
			</ul>
			<!--{if $_SBLOCK[newlist_multipage]}-->
				$_SBLOCK[newlist_multipage]
			<!--{else}-->
			<div class="pages">
			当前只有一页
			</div>
			<!--{/if}-->
			<!--{/if}-->
			<!--{/if}-->
		</div>

        <!--{block name="bbsforum" parameter="fup/$fid/allowblog/1/order/displayorder/limit/0,100/cachetime/28800/cachename/subarr/tpl/data"}-->
		<!--{if $_SGET['page']<2 && !empty($_SBLOCK['subarr'])}-->
		<!--{eval $i = 1;}-->
		<div class="catalog_list clearfix">
		<!--{loop $_SBLOCK['subarr'] $key $value}-->
		<!--{eval $cachetime=1800+$key*5;}-->
		<!--{block name="bbsthread" parameter="fid/$value[fid]/order/dateline DESC/limit/0,6/cachetime/$cachetime/cachename/subthreadlist/tpl/data"}-->
		<!--{if $_SBLOCK['subthreadlist']}-->
					<!--{if ($i % 2) == 0}-->
			<div class="global_module box_r">
			<!--{else}-->
			<div class="global_module">
			<!--{/if}-->
			<div class="global_module2_caption"><h3><a href="#action/forumdisplay/fid/$value[fid]#">$value[name]</a></h3></div>
			<ul class="global_tx_list1">
				<!--{loop $_SBLOCK['subthreadlist'] $value}-->
				<li><span class="box_r">#date("m-d", $value["dateline"])# </cite><a href="$value[url]" target="_blank">$value[subject]</a></span></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{eval $i++;}-->
		<!--{/if}-->
		<!--{/loop}-->
		</div>
		<!--{/if}-->

	</div><!--col1 end-->

	<div class="col2">
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->

        <div class="global_module global_tx_list5">
			<div class="global_module2_caption"><h3>$forum[name]</h3></div>
			<!--{if $_SBLOCK['subarr']}-->
			<ul>
				<!--{loop $_SBLOCK['subarr'] $value}-->
				<li><a href="$value[url]">$value[name]</a></li>
				<!--{/loop}-->
			</ul>
			<!--{/if}-->
		</div>

	<!--最新更新帖子-->
	<!--{block name="bbsthread" parameter="fid/$fid/order/lastpost DESC/limit/0,10/subjectlen/30/subjectdot/0/cachetime/11460/cachename/newpost/tpl/data"}-->
		<div class="global_module global_tx_list5">
			<div class="global_module2_caption"><h3>最新评论</h3></div>
			<ul>
				<!--{loop $_SBLOCK['newpost'] $value}-->
					<li><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
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
<div class="ad_pagebody">$ads2['pagefootad']</div>
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