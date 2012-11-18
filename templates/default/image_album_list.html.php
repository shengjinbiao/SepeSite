<?exit?>
<!--{template header}-->
<!--{eval $ads2 = getad('system', 'uchimage', '2'); }-->
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
			<li <!--{if $key == 'uchimage' }--> class="current"<!--{/if}-->><a href="$value[url]">$value[name]</a></li>
			<!--{/loop}-->
		</ul>
	</div>
</div><!--nav end-->
<!--{if !empty($ads2['pagecenterad'])}-->
<div class="ad_pagebody">$ads2[pagecenterad]</div>
<!--{/if}-->

<div class="column global_module bg_fff">
	<div class="global_module1_caption"><h3>精彩相册</h3></div>
	<div class="image_gallery_list clearfix">
		<!--{block name="uchphoto" parameter="order/updatetime DESC/perpage/21/subjectlen/20/subjectdot/0/cachetime/8200/cachename/uchimage"}--><!--uchimage-->
		<!--{loop $_SBLOCK['uchimage'] $key $value}-->
		<dl>
			<dt><div><a href="$value['url']"><img src="$value['pic']" alt="" /></a></div></dt>
			<dd>
				<h6><a href="$value['url']">$value['albumname']</a></h6>
				<p><a href="{S_URL}/space.php?uid=$value[uid]">$value['username']</a></p>
				<p>$value['picnum']张照片</p>
				<p>更新:
				<!--{if ($_SGLOBAL['timestamp'] - $value['updatetime']) > 86400}--> 
				#date("Y-m-d", $value[updatetime])#
				<!--{else}-->
				<!--{eval echo intval(($_SGLOBAL['timestamp'] - $value['updatetime']) / 3600 + 1);}-->小时之前
				<!--{/if}-->
				</p>
			</dd>
		</dl>
		<!--{/loop}-->

	</div>

	<!--{if $_SBLOCK[uchimage_multipage]}-->
		$_SBLOCK[uchimage_multipage]
	<!--{/if}-->
</div>

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