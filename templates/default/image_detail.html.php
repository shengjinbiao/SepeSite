<?exit?>
<!--{template header}-->
<!--{eval $ads3 = getad('system', 'uchimage', '3'); }-->
<!--{if !empty($ads3['pageheadad']) }-->
	<div class="ad_header">$ads3[pageheadad]</div>
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

<!--{if !empty($ads3['pagecenterad'])}-->
<div class="ad_pagebody">$ads3[pagecenterad]</div>
<!--{/if}-->

<div id="image_show" class="column global_module bg_fff">
	<div class="global_module3_caption">
	<h3>
		<em style="float:right; padding:0 5px 0 0;">
		<a href="$_SC[uchurl]/space.php?uid=$pic[uid]&do=album&picid=$pic[picid]" title="转至$channels[menus][uchimage][name]" class="vote" target="_blank">转至$channels[menus][uchimage][name]</a>
		</em>
		您的位置：<a href="{S_URL}/">$_SCONFIG[sitename]</a>
		<!--{loop $guidearr $value}-->
		&gt;&gt; <a href="$value[url]">$value[name]</a>
		<!--{/loop}-->
		&gt;&gt; <a href="{S_URL}/space.php?uid=$_SGET[uid]&op=uchphoto">$albums[username]的相册</a>
		&gt;&gt; <a href="#action/imagelist/uid/$pic[uid]/id/$pic[albumid]#">$albums[albumname]</a>
		&gt;&gt; 查看图片
	</h3></div>

	<div class="image_caption"><div class="box_l">第 $sequence / $albums['picnum'] 张 <a href="#action/imagelist/uid/$pic[uid]/id/$pic[albumid]#">返回该相册</a></div>
	<!--{if !defined('CREATEHTML')}-->
	<div class="box_r"><a href="#action/imagedetail/pid/$pic[picid]/uid/$pic[uid]/goto/up#">上一张</a> <a href="#action/imagedetail/pid/$pic[picid]/uid/$pic[uid]/goto/down#">下一张</a></div>
	<!--{/if}-->
	</div>
	
	<div class="image_bigshow">
		<a href="#action/imagedetail/pid/$pic[picid]/uid/$pic[uid]/goto/down#"><img src="$pic[pic]" alt="" /></a>
		<h3>$pic[title]</h3>
	</div>
	<div id="prev_next_news" class="clearfix">
	</div>
	<div class="comment">
		<!--{if !empty($imagecomment) }-->
		<!--{eval $i = 0;}-->
		<!--{loop $imagecomment $value}-->
		<!--{if $i % 2 == 0}-->
		<div class="u_comment_list bg_f8">
		<!--{else}-->
		<div class="u_comment_list">
		<!--{/if}-->
			<div class="u_avatar"><a href="{S_URL}/space.php?uid=$blogdetail[uid]"><img src="{UC_API}/avatar.php?uid=$blogdetail[uid]&size=small" alt="" /></a></div>
			<div class="u_info">
				<p class="u_title"><a href="{S_URL}/space.php?uid=$value[authorid]">$value[author]</a>#date('Y-n-d H:i:s', $value["dateline"])#</p>
				<p class="u_txt">$value[message]</p>
			</div>
		</div>
		<!--{eval $i++;}-->
		<!--{/loop}-->
		<!--{/if}-->
	</div>
	
	<div id="comment_op"><a class="write" href="$_SC[uchurl]/space.php?uid=$pic[uid]&do=album&picid=$pic[picid]" target="_blank">我也来说两句</a> <a class="view" href="$_SC[uchurl]/space.php?uid=$pic[uid]&do=album&picid=$pic[picid]" target="_blank">查看全部回复</a></div>

</div>

<!--{if !empty($ads3['pagefootad'])}-->
<div class="ad_pagebody">$ads3['pagefootad']</div>
<!--{/if}-->

<!--{if !empty($ads3['pagemovead']) || !empty($ads3['pageoutad'])}-->
<!--{if !empty($ads3['pagemovead'])}-->
<div id="coupleBannerAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
	<div style="position: absolute; left: 6px; top: 6px;">
		$ads3[pagemovead]
		<br />
		<img src="{S_URL}/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
	</div>
	<div style="position: absolute; right: 6px; top: 6px;">
		$ads3[pagemovead]
		<br />
		<img src="{S_URL}/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
	</div>
</div>
<!--{/if}-->
<!--{if !empty($ads3['pageoutad'])}-->
<div id="floatAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
	<div id="floatFloor" style="position: absolute; right: 6px; bottom:-700px">
		$ads3[pageoutad]
	</div>
</div>
<!--{/if}-->
<script type="text/javascript" src="{S_URL}/include/js/floatadv.js"></script>
<script type="text/javascript">
<!--{if !empty($ads3['pageoutad'])}-->
var lengthobj = getWindowSize();
lsfloatdiv('floatAdv', 0, 0, 'floatFloor' , -lengthobj.winHeight).floatIt();
<!--{/if}-->
<!--{if !empty($ads3['pagemovead'])}-->
lsfloatdiv('coupleBannerAdv', 0, 0, '', 0).floatIt();
<!--{/if}-->
</script>
<!--{/if}-->

<!--{if !empty($ads3['pageoutindex'])}-->
$ads3[pageoutindex]
<!--{/if}-->
<!--{template footer}-->