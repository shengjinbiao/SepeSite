<?exit?>
<!--{template header}-->
<!--{eval $ads = getad('system', 'indexad', '1'); }-->
<!--{if !empty($ads['pageheadad']) }-->
	<div class="ad_header">$ads[pageheadad]</div>
<!--{/if}-->
</div><!--header end-->

<div id="nav">
	<div class="main_nav">
		<ul>
			<li class="current"><a href="{S_URL}/">首页</a></li>
			<!--{loop $channels['menus'] $key $value}-->
			<li><a href="$value[url]">$value[name]</a></li>
			<!--{/loop}-->
		</ul>
	</div>
	
	<div class="order_nav">
	<!--{loop $channels['menus'] $key $value}-->
		<!--{if $key == 'news' || $value['upnameid']=='news'}-->
		<!--{block name="category" parameter="type/$key/isroot/1/order/c.displayorder/limit/0,12/cachetime/80800/cachename/category"}-->
			<ul><li>
			<em><a href="$value[url]">$value['name']</a>: </em>
			<!--{eval $dot = '|'}-->
			<!--{eval $total = count($_SBLOCK['category'])}-->
			<!--{eval $i = 1;}-->
			<!--{loop $_SBLOCK['category'] $value}-->
			<a href="$value[url]">$value[name]</a><!--{if $total != $i}--> $dot <!--{/if}-->
			<!--{eval $i++;}-->
			<!--{/loop}-->
			</li></ul>
		<!--{elseif $key == 'bbs'}-->
			<!--{if $forumarr}-->
				<ul><li>
				<em><a href="$value[url]">$value['name']</a>: </em>
				<!--{eval $dot = '|'}-->
				<!--{eval $total = count($forumarr)}-->
				<!--{eval $i = 1;}-->
				<!--{loop $forumarr $value}-->
				<a href="$value[url]">$value[name]</a><!--{if $total != $i}--> $dot <!--{/if}-->
				<!--{eval $i++;}-->
				<!--{/loop}-->
				</li></ul>
			<!--{/if}-->
		<!--{elseif $value['type'] == 'model'}-->
			<!--{eval @include S_ROOT.'./cache/model/model_'.$value['nameid'].'.cache.php';}-->
			<!--{if !empty($cacheinfo['categories'])}-->
				<ul><li>
				<em><a href="$value[url]">$value['name']</a>: </em>
				<!--{eval $dot = '|'}-->
				<!--{eval $total = count($cacheinfo['categories'])}-->
				<!--{eval $i = 1;}-->
				<!--{loop $cacheinfo['categories'] $key $value}-->
				 <a href="$siteurl/m.php?name=$cacheinfo[models][modelname]&mo_catid=$key" title="$value">$value</a><!--{if $total != $i}--> $dot <!--{/if}-->
				<!--{eval $i++;}-->
				<!--{/loop}-->
				</li></ul>
			<!--{/if}-->
		<!--{/if}-->
	<!--{/loop}-->
	</div>
	
</div><!--nav end-->

<div class="column">
	<div class="col1">
		<div class="col3">
		<!--{block name="spacenews" parameter="haveattach/2/order/i.dateline DESC/limit/0,4/cachetime/83400/subjectlen/40/subjectdot/0/cachename/hotnewspic"}-->
		<div id="focus_turn">
			<!--{if !empty($_SBLOCK['hotnewspic'])}-->
			<ul id="focus_pic">
				<!--{eval $j = 0}-->
				<!--{loop $_SBLOCK['hotnewspic'] $pkey $pvalue}-->
				<!--{eval $pcurrent = ($j == 0 ? 'current' : 'normal');}-->
				<li class="$pcurrent"><a href="$pvalue[url]"><img src="$pvalue['a_filepath']" alt="" /></a></li>
				<!--{eval $j++}-->
				<!--{/loop}-->
			</ul>
			<ul id="focus_tx">
				<!--{eval $i = 0}-->
				<!--{loop $_SBLOCK['hotnewspic'] $key $value}-->
				<!--{eval $current = ($i == 0 ? 'current' : 'normal');}-->
				<li class="$current"><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{eval $i++}-->
				<!--{/loop}-->
			</ul>
			<div id="focus_opacity"></div>
			<!--{/if}-->
		</div><!--focus_turn end-->

		<!--{block name="spacenews" parameter="order/i.viewnum DESC/limit/0,17/cachetime/86900/subjectlen/40/subjectdot/0/showdetail/1/messagelen/100/messagedot/1/cachename/hotnews"}-->
		<!--{if !empty($_SBLOCK['hotnews'])}-->
		<!--{eval $hotnews = @array_slice($_SBLOCK['hotnews'], 0, 5)}-->
		<!--{/if}-->
			<div id="new_news">
				<h3>热点内容</h3>
				<ul>
					<!--{loop $hotnews $value}-->
					<li><span class="box_r">#date('m-d',$value['dateline'])#</span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
					<!--{/loop}-->
				</ul>
			</div>
		</div><!--col3 end-->

		<!--最新资讯-->
		<!--{block name="spacenews" parameter="order/i.dateline DESC/limit/0,5/cachetime/85400/subjectlen/46/subjectdot/0/showdetail/1/messagelen/150/messagedot/1/cachename/newnews1"}-->
		<div class="col4" id="hot_news">
			<h3>最新资讯</h3>
			<!--{loop $_SBLOCK['newnews1'] $value}-->
			<div class="hot_news_list">
				<h4><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></h4>
				<p>$value[message]</p>
			</div>
			<!--{/loop}-->
		</div><!--col4 end-->
	</div><!--col1 end-->
	<div class="col2">
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?open=1&rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->
		
		<!--{block name="poll" parameter="order/dateline DESC/limit/0,3/cachetime/80000/subjectlen/36/cachename/poll"}-->
		<div class="super_notice">
			<h3>调查:</h3>
			<ul>
				<!--{if empty($_SBLOCK['poll'])}-->
				<li>暂时没有调查</li>
				<!--{else}-->
				<!--{loop $_SBLOCK['poll'] $value}-->
				<li><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
				<!--{/if}-->
			</ul>
		</div><!--调查end-->

		<!--{block name="announcement" parameter="order/displayorder DESC,starttime DESC/limit/0,3/cachetime/96400/subjectlen/34/subjectdot/0/cachename/announce"}-->
		<div class="super_notice">
			<h3>公告:</h3>
			<ul>
				<!--{if empty($_SBLOCK['announce'])}-->
				<li>暂时没有公告</li>
				<!--{else}-->
				<!--{loop $_SBLOCK['announce'] $value}-->
				<li><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
				<!--{/if}-->
			</ul>
		</div><!--公告end-->
		<div class="search_bar margin_bot0">
			<h3>站内搜索</h3>
			<div class="search_content">
				<form action="{S_URL}/batch.search.php" method="post">
				<input type="text" class="input_tx" size="23" name="searchkey" value="$searchkey" /> <input type="submit" class="input_search" name="authorsearchbtn" value="搜索" />
				<div class="search_catalog">
					<!--{if empty($searchname)}-->
					<!--{eval $searchname = 'subject'}-->
					<!--{/if}-->
					<input id="title" name="searchname" type="radio" value="subject" <!--{if $searchname == 'subject'}-->checked="checked" <!--{/if}-->/><label for="title">标题</label>
					<input id="content" name="searchname" type="radio" value="message" <!--{if $searchname == 'message'}-->checked="checked" <!--{/if}-->/><label for="content">内容</label>
					<input id="author" name="searchname" type="radio" value="author" <!--{if $searchname == 'author'}-->checked="checked" <!--{/if}-->/><label for="author">作者</label>
					<!--{if !empty($channels['menus']['bbs'])}-->
					<a class="search_bbs" title="搜索论坛" href="$bbsurl/search.php" target="_blank">搜索论坛</a>
					<!--{/if}-->
					<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
				</div>
				</form>
			</div>
		</div><!--站内搜索-->
	</div><!--col2 end-->
</div><!--column end-->

<!--{if !empty($ads['pagecenterad'])}-->
<div class="ad_pagebody">$ads[pagecenterad]</div>
<!--{/if}-->

<div class="column">
	<div class="col1">
		<div class="global_module">
			<div class="global_module1_caption"><h3>资讯</h3><a class="more" href="#action/news#">更多&gt;&gt;</a></div>
			<ul class="global_tx_list1">
				<!--{if !empty($_SBLOCK['hotnews'])}-->
				<!--{eval $hotnews2 = @array_slice($_SBLOCK['hotnews'], 5, 17)}-->
				<!--{/if}-->
				<!--{loop $hotnews2 $value}-->
				<li><span class="box_r">#date('m-d',$value['dateline'])#</span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
	</div><!--col1 end-->

	<!--{block name="spacenews" parameter="type/news/digest/1,2,3/order/i.viewnum DESC,i.dateline DESC/limit/0,6/cachetime/89877/subjectlen/34/subjectdot/0/cachename/hotnews2"}-->
	<div class="col2">
		<div class="global_module bg_fff">
			<div class="global_module2_caption"><h3>本月精华</h3></div>
			<ul class="global_tx_list2">
				<!--{loop $_SBLOCK['hotnews2'] $value}-->
				<li><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
	</div><!--col2 end-->
</div><!--column end-->

<!--{loop $channels['menus'] $key $value}-->
	<!--{if $value['upnameid']=='news'}-->
	<div class="column">
		<div class="col1">
			<div class="global_module">
				<div class="global_module1_caption"><h3>$value[name]</h3><a class="more" href="#action/$key#">更多&gt;&gt;</a></div>
				<ul class="global_tx_list1">
					<!--{block name="spacenews" parameter="type/$key/order/i.dateline DESC/limit/0,12/cachetime/86900/subjectlen/40/subjectdot/0/showdetail/1/messagelen/100/messagedot/1/cachename/hotnews"}-->
					<!--{loop $_SBLOCK['hotnews'] $value}-->
					<li><span class="box_r">#date('m-d',$value['dateline'])#</span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
					<!--{/loop}-->
				</ul>
			</div>
		</div><!--col1 end-->
	
		<!--{block name="spacenews" parameter="type/$key/digest/1,2,3/order/i.viewnum DESC,i.dateline DESC/limit/0,6/cachetime/89877/subjectlen/34/subjectdot/0/cachename/hotnews2"}-->
		<div class="col2">
			<div class="global_module bg_fff">
				<div class="global_module2_caption"><h3>本月精华</h3></div>
				<ul class="global_tx_list2">
					<!--{loop $_SBLOCK['hotnews2'] $value}-->
					<li><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
					<!--{/loop}-->
				</ul>
			</div>
		</div><!--col2 end-->
	</div><!--column end-->
	<!--{/if}-->
<!--{/loop}-->

<!--{if !empty($channels['menus']['uchimage'])}-->
<div class="column">
	<!--{block name="uchphoto" parameter="updatetime/604800/order/updatetime DESC/limit/0,6/cachetime/86585/subjectlen/12/cachename/uchphoto"}-->
	<div class="col1">
		<div class="global_module">
			<div class="global_module1_caption"><h3>相册</h3><a class="more" href="#action/uchimage#">更多&gt;&gt;</a></div>
			<ul class="global_piclist">
				<!--{loop $_SBLOCK['uchphoto'] $key $value}-->
				<li><div><a href="$value[url]"><img alt="" src="$value['pic']"/></a></div><span><a href="$value[url]">$value[albumname]</a></span></li>
				<!--{/loop}-->
			</ul>
		</div>
	</div><!--col1 end-->
	
	<!--{block name="uchphoto" parameter="dateline/604800/order/picnum DESC,updatetime DESC/limit/0,2/cachetime/89477/subjectlen/14/subjectdot/0/cachename/uchphototop"}-->
	<div class="col2">
		<div class="global_module bg_fff">
			<div class="global_module2_caption"><h3>精彩推荐</h3></div>
			<ul class="global_piclist">
				<!--{loop $_SBLOCK['uchphototop'] $key $value}-->
				<li><div><a href="$value[url]"><img alt="" src="$value['pic']"/></a></div><span><a href="$value[url]">$value[albumname]</a></span></li>
				<!--{/loop}-->
			</ul>
		</div>
	</div><!--col2 end-->
</div><!--column end-->
<!--{/if}-->

<!--{if !empty($channels['menus']['bbs'])}-->
<div class="column">
	<div class="col1">
		<!--{block name="bbsthread" parameter="order/t.dateline DESC/subjectlen/36/subjectdot/0/limit/0,12/cachetime/5630/cachename/newthread"}-->
		<div class="global_module">
			<div class="global_module1_caption"><h3>论坛</h3><a class="more" href="#action/bbs#">更多&gt;&gt;</a></div>
			<ul class="global_tx_list1">
				<!--{loop $_SBLOCK['newthread'] $value}-->
				<li><span class="box_r"><a href="{S_URL}/space.php?uid=$value[authorid]">$value[author]</a></span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
	</div><!--col1 end-->
	
	<!--{block name="bbsforum" parameter="type/forum/allowblog/1/order/posts DESC/limit/0,6/cachetime/14672/cachename/hotforums"}-->
	<div class="col2">
		<div class="global_module bg_fff">
			<div class="global_module2_caption"><h3>版块帖子数排行</h3></div>
			<ul class="global_tx_list2">
				<!--{loop $_SBLOCK['hotforums'] $value}-->
					<li><span class="box_r">$value[posts]</span><a href="$value[url]">$value[name]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
	</div><!--col2 end-->
</div><!--column end-->
<!--{/if}-->

<!--{if !empty($channels['menus']['uchblog'])}-->
<!--{block name="uchblog" parameter="order/dateline DESC/subjectlen/36/subjectdot/0/limit/0,12/cachetime/18400/cachename/hotblog"}-->
<div class="column">
	<div class="col1">
		<div class="global_module">
			<div class="global_module1_caption"><h3>日志</h3><a class="more" href="#action/uchblog#">更多&gt;&gt;</a></div>
			<ul class="global_tx_list1">
				<!--{loop $_SBLOCK['hotblog'] $value}-->
				<li><span class="box_r"><a href="{S_URL}/space.php?uid=$value[uid]">$value[username]</a></span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
	</div><!--col1 end-->
	
	<!--{block name="uchspace" parameter="order/updatetime DESC/limit/0,8/cachetime/86430/cachename/uchspace"}-->
	<div class="col2">
		<div class="global_module bg_fff">
			<div class="global_module2_caption"><h3>最近更新</h3></div>
			<ul class="global_avatar_list new_avatar">
				<!--{loop $_SBLOCK['uchspace'] $value}-->
					<li><a href="{S_URL}/space.php?uid=$value[uid]"><img src="{UC_API}/avatar.php?uid=$value[uid]&size=small" alt="$value[username]" /></a><span><a href="{S_URL}/space.php?uid=$value[uid]">$value[username]</a></span></li>
				<!--{/loop}-->
			</ul>
		</div>
	</div><!--col2 end-->
</div><!--column end-->
<!--{/if}-->

<!--{if !empty($ads['pagefootad'])}-->
<div class="ad_pagebody">$ads[pagefootad]</div>
<!--{/if}-->

<!--{if !empty($_SCONFIG['showindex'])}-->
<!--{block name="friendlink" parameter="order/displayorder/limit/0,$_SCONFIG['showindex']/cachetime/11600/namelen/32/cachename/friendlink/tpl/data"}-->
<div id="links">
	<h3>友情链接</h3>
	<!--{eval $imglink=$txtlink="";}-->
	<!--{loop $_SBLOCK['friendlink'] $value}-->
	<!--{if $value[logo]}-->
	<!--{eval $imglink .= "<a href=\"".$value[url]."\" target=\"_blank\" title=\"".$value[description]."\"><img src=\"".$value[logo]."\" alt=\"".$value[description]."\" border=\"0\" /></a>\n";}-->
	<!--{else}-->
	<!--{eval $txtlink .= "<li><a href=\"".$value[url]."\" title=\"".$value[description]."\" target=\"_blank\">".$value[name]."</a></li>\n";}-->
	<!--{/if}-->
	<!--{/loop}-->
	<!--{if !empty($imglink)}-->
	<div class="links_img">
		$imglink
	</div>
	<!--{/if}-->
	<!--{if !empty($txtlink)}-->
	<div class="links_tx">
		<ul class="s_clear">
			$txtlink
		</ul>
	</div>
	<!--{/if}-->
</div>
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
