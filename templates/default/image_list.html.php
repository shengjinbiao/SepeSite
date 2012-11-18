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


<!--{block name="uchphoto" parameter="albumid/$aid/order/updatetime/cachename/uch_photo"}--><!--uch_photo-->
<div class="column global_module bg_fff">
	<div class="global_module3_caption">
	<!--{eval $values = $_SBLOCK['uch_photo'][0];}-->
	<h3>
		<em style="float:right; padding:0 5px 0 0;">
		<a href="$_SC[uchurl]/space.php?uid=$values[uid]&do=album&id=$values[albumid]" title="转至$channels[menus][uchimage][name]" class="vote" target="_blank">转至$channels[menus][uchimage][name]</a>
		</em>
		您的位置：<a href="{S_URL}/">$_SCONFIG[sitename]</a>
		<!--{loop $guidearr $value}-->
		&gt;&gt; <a href="$value[url]">$value[name]</a>
		<!--{/loop}-->
		&gt;&gt; <a href="{S_URL}/space.php?uid=$values[uid]&op=uchphoto">$values[username]的相册</a>
		&gt;&gt; 浏览相册
	</h3></div>
	
	<div class="image_list_userinfo">
		<dl>
			<dt><div><img src="$values[pic]" alt="$values[albumname]"></div></dt>
			<dd>
				<h2>$values[albumname]</h2>
				<h4><a href="{S_URL}/space.php?uid=$values[uid]&op=uchphoto">查看 $values[username] 的全部相册</a></h4>
				<p>$values[picnum]张照片 | 更新
					<!--{if ($_SGLOBAL['timestamp'] - $values['updatetime']) > 86400}--> 
						#date("Y-m-d", $values[updatetime])#
						<!--{else}-->
						<!--{eval echo intval(($_SGLOBAL['timestamp'] - $values['updatetime']) / 3600 + 1);}-->小时之前
					<!--{/if}-->
				</p>
			</dd>
		</dl>
	</div><!--image_list_userinfo end-->
	
	<ul class="global_piclist image_list clearfix">
		<!--{if $photolist}-->
		<!--{loop $photolist $pkey $pvalue}-->
		<li><div><a href="$pvalue[url]"><img alt="" src="$pvalue[pic]"/></a></div></li>
		<!--{/loop}-->
		<!--{/if}-->
	</ul>
	<!--{if !empty($multipage)}-->
	$multipage
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

<!--{if !empty($ads2['pageoutindex'])}-->
$ads2[pageoutindex]
<!--{/if}-->
<!--{template footer}-->