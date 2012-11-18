<?exit?>
<!--{template header}-->
<!--{eval $ads2 = getad('system', 'uchblog', '2'); }-->
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
			<li <!--{if $key == 'uchblog' }--> class="current"<!--{/if}-->><a href="$value[url]">$value[name]</a></li>
			<!--{/loop}-->
		</ul>
	</div>
</div><!--nav end-->
<!--{if !empty($ads2['pagecenterad'])}-->
<div class="ad_pagebody">$ads2[pagecenterad]</div>
<!--{/if}-->
	<div class="column global_module margin_bot10 bg_fff">
			<div class="global_module3_caption"><h3>
			<em style="float:right; padding:0 5px 0 0;">
			<a href="$_SC[uchurl]/network.php?ac=blog" title="转至$channels[menus][uchblog][name]" class="vote" target="_blank">转至$channels[menus][uchblog][name]</a>
			</em>
			你的位置：<a href="{S_URL}/">$_SCONFIG[sitename]</a>
			<!--{loop $guidearr $value}-->
			&gt;&gt; <a href="$value[url]">$value[name]</a>
			<!--{/loop}-->
			<!--{if $_SGET['order'] == 'viewnum'}-->
			(以浏览量进行排列)
			<!--{elseif $_SGET['order'] == 'replynum'}-->
			(以评论数进行排列)
			<!--{else}-->
			(以发布时间进行排列)
			<!--{/if}--></h3></div>
			
			<!--{block name="uchblog" parameter="perpage/15/order/$order/cachetime/7200/showdetail/1/messagelen/380/messagedot/1/cachename/uchbloghot"}--><!--uchblog-->
			<!--{loop $_SBLOCK['uchbloghot'] $key $value}-->
			<div class="blog_info_list">
				<div class="box_l">
					<a href="{S_URL}/space.php?uid=$value[uid]"><img src="{UC_API}/avatar.php?uid=$value[uid]&size=small" alt=""/></a><br/>
					<a href="{S_URL}/space.php?uid=$value[uid]">$value[username]</a><br/>
					<!--{if ($_SGLOBAL['timestamp'] - $value['dateline']) > 86400}--> 
					#date("Y-m-d", $value[dateline])#
					<!--{else}-->
					<!--{eval echo intval(($_SGLOBAL['timestamp'] - $value['dateline']) / 3600 + 1);}-->小时之前
					<!--{/if}-->
				</div>
				<div class="box_r">
					<h5><a href="$value[url]">$value[subject]</a></h5>
					<div class="blog_signtx">$value[message]</div>
					<p class="blog_info">浏览($value[viewnum])&nbsp;|&nbsp;评论($value[replynum])</p>
				</div>
			</div>
			<!--{/loop}-->
			<!--{if !empty($_SBLOCK[uchbloghot_multipage])}-->
				$_SBLOCK[uchbloghot_multipage]
			<!--{/if}-->
		
	</div>


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