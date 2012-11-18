<?exit?>
<!--{eval include template($tpldir.'/header.html.php', 1);}-->
<!--{eval $ads2 = getad('system', $modelsinfoarr[modelname], '2'); }-->
<!--{if !empty($ads2['pageheadad']) }-->
<div class="ad_header">$ads2[pageheadad]</div>
<!--{/if}-->
</div>

<div id="nav">
	<div class="main_nav">
		<ul>
			<!--{if empty($_SCONFIG['defaultchannel'])}-->
			<li><a href="{S_URL}/">首页</a></li>
			<!--{/if}-->
			<!--{loop $channels['menus'] $key $value}-->
			<li <!--{if $key == $modelsinfoarr['modelname'] }--> class="current"<!--{/if}-->><a href="$value[url]">$value[name]</a></li>
			<!--{/loop}-->
		</ul>
	</div>
	<!--{if !empty($categories)}-->
	<ul class="ext_nav clearfix">
		<!--{eval $dot = '|'}-->
		<!--{eval $total = count($categories)}-->
		<!--{eval $i = 1;}-->
		<!--{loop $categories $key $value}-->
		 <li><a href="$siteurl/m.php?name=$modelsinfoarr[modelname]&mo_catid=$key" title="$value">$value</a><!--{if $total != $i}--> $dot <!--{/if}--></li>
		<!--{eval $i++;}-->
		<!--{/loop}-->
	</ul>
	<!--{/if}-->
</div><!--nav end-->

<div class="column">
	<div class="col1">

		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module3_caption"><h3>
			你的位置：<a href="{S_URL}/">$_SCONFIG[sitename]</a>
			<!--{loop $guidearr $value}-->
			&gt;&gt; <a href="$value[url]">$value[name]</a>
			<!--{/loop}-->
			<!--{if $posturl}-->
			<a href="$posturl" title="在线投稿" class="btn_capiton_op" target="_blank">在线投稿</a>
			<!--{/if}-->
			</h3></div>
			<script type="text/javascript" src="{S_URL}/model/data/$modelsinfoarr[modelname]/images/validate.js"></script>
			<script type="text/javascript" src="{S_URL}/include/js/selectdate.js"></script>
			<form method="get" name="modelsearch" id="modelsearch" action="{S_URL}/m.php">
			<div class="mldulebox_search">
			<ul id="search_box" class="fixoneline">
				<!--{loop $searchtable $value}-->
					<li>$value</li>
				<!--{/loop}-->
			</ul>
			<p>
			<input type="submit" value="搜索" class="input_search"/>
			<input type="reset" value="重置" class="input_reset"/>
			<input name="name" type="hidden" id="name" value="$_GET[name]" />
			<a id="more_search" href="javascript:;">更多搜索项</a>
			<a id="close_search" style="display:none" href="#">收回更多选项</a>
			$linkagestr
			</p>
			</div>
			</form>
			<!--{if !empty($listarr)}-->
			<!--{loop $listarr $key $value}-->
			<div class="mldulebox_list">
				<h4><em>#date("Y-m-d", $value[dateline])#</em><a href="$value[ss_url]">$value[subject]</a></h4>
				<!--{if !empty($columnsinfoarr)}-->
				<ul>
				<!--{loop $columnsinfoarr $tmpkey $tmpvalue}-->
					<!--{if !is_array($value[$tmpkey])}-->
					<!--{if strlen($value[$tmpkey]) > 0}-->
						<li><em>$tmpvalue[fieldcomment]:</em>
						<!--{if $tmpvalue[formtype]!='timestamp' }-->
						<a href="$siteurl/m.php?name=$modelsinfoarr[modelname]&mo_$tmpkey=<!--{eval echo rawurlencode($value[$tmpkey]);}-->">$value[$tmpkey]</a>
						<!--{else}-->
						#date("m月d日 H:i", $value[$tmpkey])#
						<!--{/if}-->
						</li>
					<!--{/if}-->
					<!--{else}-->
						<li class="maxcontent"><em>$tmpvalue[fieldcomment]:</em>
						<!--{loop $value[$tmpkey] $dkey $dvalue}-->
							<!--{if $tmpvalue[formtype]=='textarea' }-->
							$dvalue
							<!--{else}-->
							<a href="$siteurl/m.php?name=$modelsinfoarr[modelname]&mo_$tmpkey=<!--{eval echo rawurlencode($dvalue);}-->">$dvalue</a>
							<!--{/if}-->
						<!--{/loop}-->
						</li>
					<!--{/if}-->
				<!--{/loop}-->
				</ul>
				<!--{/if}-->
			</div>
			<!--{/loop}-->
			<!--{/if}-->
			<!--{if $multipage}-->
				$multipage
			<!--{/if}-->
		</div>

	</div><!--col1 end-->
	<div class="col2">
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->
		<!--{if !empty($childcategories)}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>$cacheinfo[categoryarr][$_GET[mo_catid]][name]</h3></div>
			<ul class="global_tx_list3">
				<!--{loop $childcategories $value}-->
				<li><a href="$siteurl/m.php?name=$modelsinfoarr[modelname]&mo_catid=$value[catid]">$value[name]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->
		<!--{if !empty($gatherarr)}-->
		<!--{loop $gatherarr $key $value}-->
		<!--{if !empty($value)}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>$cacheinfo[columns][$key][fieldcomment]</h3></div>
			<ul class="ext_li_short clearfix">
				<!--{loop $value $tmpvalue}-->
					<li><a href="$siteurl/m.php?name=$modelsinfoarr[modelname]&mo_$key=<!--{eval echo rawurlencode($tmpvalue);}-->">$tmpvalue</a></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->
		<!--{/loop}-->
		<!--{/if}-->

	</div><!--col2 end-->
</div><!--column end-->
<!--{if !empty($ads2['pagefootad'])}-->
<div class="ad_pagebody">
$ads2[pagefootad]
</div>
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
