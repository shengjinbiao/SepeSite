<?exit?>
<!--{template header}-->
<!--{eval $ads = getad('system', 'uchimage', '1'); }-->
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
			<li <!--{if $key == 'uchimage' }--> class="current"<!--{/if}-->><a href="$value[url]">$value[name]</a></li>
			<!--{/loop}-->
		</ul>
	</div>
</div><!--nav end-->

<div class="column">
	<!--{block name="uchphoto" parameter="order/updatetime DESC/limit/0,6/cachetime/87480/cachename/uchphoto"}-->
	<div class="col1 global_module" id="image_focus">
		<div id="image_focus_big">
			<ul>	<!--{eval $i=0;}-->
				<!--{loop $_SBLOCK['uchphoto'] $key $value}-->
				<li <!--{if $i == 0}-->class="current"<!--{/if}-->s><a href="$value[url]">
				<!--{if substr($value['pic'], -10) == '.thumb.jpg'}-->
				<img src="<!--{eval echo substr($value['pic'], 0, -10)}-->" alt="$value[subject]" />
				<!--{else}-->
				<img src="$value['pic']" alt="$value[subject]" />
				<!--{/if}-->
				</a></li>
				<!--{eval $i++}-->
				<!--{/loop}-->
			</ul>
		</div>
		<div id="image_focus_small">
			<h3>最近更新</h3>
			<ul class="global_piclist">
				<!--{loop $_SBLOCK['uchphoto'] $key $bvalue}-->
				<li><div><a href="$bvalue[url]"><img src="$bvalue['pic']" alt="$bvalue[subject]" /></a></div></li>
				<!--{/loop}-->
			</ul>
		</div>

	</div>
	<div class="col2">
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?open=1&rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->

		<!--{block name="poll" parameter="order/dateline DESC/limit/0,3/cachetime/80000/cachename/poll"}-->
		<div class="super_notice margin_bot0">
			<h3>调查:</h3>
			<ul>
				<!--{if empty($_SBLOCK['poll'])}-->
				<li>暂时没有调查</li>
				<!--{else}-->
				<!--{loop $_SBLOCK['poll'] $value}-->
				<li><a href="$value[url]">$value[subject]</a></li>
				<!--{/loop}-->
				<!--{/if}-->
			</ul>
		</div><!--调查end-->

	</div>
</div><!--column end-->

<!--{block name="uchphoto" parameter="updatetime/2592000/order/picnum DESC/limit/0,12/cachetime/87480/subjectlen/12/subjectdot/0/cachename/uchphoto2"}-->
<div class="column global_module">
	<div class="global_module1_caption"><h3>本月相册达人</h3></div>
	<div class="image_user_list">
		<!--{loop $_SBLOCK['uchphoto2'] $key $value}-->
		<dl>
			<dt><a href="{S_URL}/space.php?uid=$value[uid]&op=uchphoto"><img src="{UC_API}/avatar.php?uid=$value[uid]&size=small" alt="" /></a></dt>
			<dd>
				<p><a href="$value[url]">$value[albumname]</a></p>
				<p><a class="color_black" href="{S_URL}/space.php?uid=$value[uid]&op=uchphoto">$value[username]</a></p>
			</dd>
		</dl>
		<!--{/loop}-->
	</div>
</div><!--column end-->

<!--{if !empty($ads['pagecenterad'])}-->
<div class="ad_pagebody">$ads[pagecenterad]</div>
<!--{/if}-->

<!--{block name="uchphoto" parameter="order/picnum DESC/limit/0,12/cachetime/87480/subjectlen/12/subjectdot/0/cachename/uchphototop"}-->
<div class="column global_module">
	<div class="global_module1_caption"><h3>精彩相册</h3><a class="more" href="#action/imagelist#">更多&gt;&gt;</a></div>
	<div class="image_gallery_list clearfix">
	<!--{loop $_SBLOCK['uchphototop'] $key $value}-->
		<dl>
		<dt><div><a href="$value[url]"><img src="$value['pic']" alt="" /></a></div></dt>
			<dd>
				<h6><a href="$value[url]">$value[albumname]</a></h6>
				<p><a href="{S_URL}/space.php?uid=$value[uid]&op=uchphoto">$value[username] 的相册</a></p>
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

</div>

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