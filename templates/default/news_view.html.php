<?exit?>
<!--{template header}-->
<!--{eval $ads3 = getad('system', $channel, '3'); }-->
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
			<div class="global_module3_caption"><h3>你的位置：<a href="{S_URL}">$_SCONFIG[sitename]</a>
				<!--{loop $guidearr $value}-->
				&gt;&gt; <a href="$value[url]">$value[name]</a>
				<!--{/loop}-->
				&gt;&gt; 详细内容
				<a href="{S_URL}/cp.php?ac=news&op=add&type=$channel" title="在线投稿" class="btn_capiton_op btn_capiton_op_r40" target="_blank">在线投稿</a>
				</h3>
			</div>
			<div id="article">
				<h1>$news[subject]</h1>

				<div id="article_extinfo">
					<div><span>
						<a href="#action/top/idtype/hot#" target="_blank" class="add_top10">排行榜</a> 
						<a href="javascript:;" class="add_bookmark" onclick="bookmarksite(document.title, window.location.href);">收藏</a> 
						<a href="javascript:doPrint();" class="print">打印</a> 
						<a href="javascript:;" class="send_frinend" onclick="showajaxdiv('{S_URL}/batch.common.php?action=emailfriend&amp;itemid=$news[itemid]', 400);">发给朋友</a> 
						<a href="javascript:;" class="report" onclick="report($news[itemid]);">举报</a>
						<script src="{S_URL}/batch.postnews.php?ac=fromss&amp;itemid=$news[itemid]"></script>
						</span>
						<!--{if !empty($news['newsfrom'])}-->来源：<!--{if !empty($news[newsfromurl])}--><a href="$news[newsfromurl]" target="_blank" title="$news[newsfrom]">$news[newsfrom]</a><!--{else}-->$news[newsfrom]<!--{/if}-->&nbsp;&nbsp;<!--{/if}-->
						发布者：<a href="{S_URL}/space.php?uid=$news[uid]&op=news">$news[newsauthor]</a> </div>
					<div><span>热度{$news[hot]}票&nbsp;&nbsp;浏览{$news[viewnum]}次<!--{if !empty($_SCONFIG['commstatus'])}--> 【<a class="color_red" href="#action/viewcomment/itemid/$news[itemid]#" target="_blank" title="点击查看">共$news[replynum]条评论</a>】【<a class="color_red" href="#action/viewcomment/itemid/$news[itemid]#">我要评论</a>】<!--{/if}--></span>
						时间：#date('Y年n月d日 H:i', $news["dateline"])#</div>
				</div>

				<div id="article_body">
					<!--{if !empty($news[custom][name])}-->
					<div id="article_summary">
						<!--{loop $news[custom][key] $ckey $cvalue}-->
						<h6>$news[custom][name]</h6>
						<p>$cvalue[name]:$news[custom][value][$ckey]</p>
						<!--{/loop}-->
					</div>
					<!--{/if}-->
					<!--{if !empty($ads3[viewinad])}-->
					<div class="ad_article">
						$ads3[viewinad]
					</div>
					<!--{/if}-->
					$news[message]
					<!--{if empty($multipage)}-->
					<!--{loop $news['attacharr'] $attach}-->
					<!--{if $attach['isimage']}-->
					<p class="article_download"><a href="$attach[url]" target="_blank"><img src="$attach[thumbpath]" alt="$attach[subject]" /><span>$attach[subject]</span></a></p>
					<!--{else}-->
					<p class="article_download"><a href="$attach[url]" target="_blank">$attach[filename]</a>(<!--{eval echo formatsize($attach[size]);}-->)</p>
					<!--{/if}-->
					<!--{/loop}-->
					<!--{/if}-->
				</div>
			</div><!--article end-->

			<!--{if !empty($relativetagarr)}-->
			<div id="article_tag">
				<strong>TAG:</strong> 
				<!--{loop $relativetagarr $value}-->
				<!--{eval $svalue = rawurlencode($value);}-->
				<a href="#action/tag/tagname/$svalue#">$value</a>
				<!--{/loop}-->
			</div>
			<!--{/if}-->

			<!--{if $multipage}-->
				$multipage
			<!--{/if}-->

			<div id="click_div">
			<!--{template news_click}-->
			</div>

			<div id="article_pn"><a class="box_l" href="{S_URL}/batch.common.php?action=viewnews&amp;op=up&amp;itemid=$news[itemid]&amp;catid=$news[catid]">上一篇</a> <a class="box_r" href="{S_URL}/batch.common.php?action=viewnews&amp;op=down&amp;itemid=$news[itemid]&amp;catid=$news[catid]">下一篇</a></div>

			<!--{if !empty($_SCONFIG['commstatus'])}-->
			<!--{if !empty($_SCONFIG['viewspace_pernum'])}-->
			<div class="comment">
				<!--{if !empty($commentlist)}-->
				<!--{loop $commentlist $value}-->
				<div class="comm_list">
					<div class="title">
						<div class ="from_info">
							<span class="author">$_SCONFIG[sitename]<!--{if !$value[hidelocation]}--><!--{if $value[iplocation]!='LAN'}-->$value[iplocation]<!--{else}-->火星<!--{/if}--><!--{/if}-->网友
							<!--{if !empty($value[authorid]) && !$value[hideauthor]}--><a href="{S_URL}/space.php?uid=$value[authorid]">[{$value[author]}]</a><!--{/if}--></span>
							<!--{if $_SCONFIG[commshowip]}-->ip:<!--{if $value[hideip]}-->*.*.*.*<!--{else}-->$value[ip]<!--{/if}--><!--{/if}--></div>
						<span class="post_time">#date("Y-m-d H:i:s", $value["dateline"], 1)#</span>
						<a name="cid_$value[cid]"></a>
					</div>
					<div id="cid_$value[cid]" class="body">
						$value[message]
					</div>
					<div class="comm_op">
						<a href="javascript:;" onclick="clearcommentmsg();getQuote($value[cid]);">引用</a>
						 | <a href="javascript:;" onclick="clearcommentmsg();$('message').focus();addupcid($value[cid]);" class="replay">回复</a>
						<!--{if $gv[status]}-->
						 | <a href="{S_URL}/do.php?action=click&op=add&clickid=33&id=$value[cid]&hash=<!--{eval echo md5($value['authorid']."\t".$value['dateline']);}-->" id="click_{$value[cid]}_33" onclick="ajaxmenu(event, this.id, 2000, 'show_clicknum')" class="up"><span class="color_red">支持</span><span class="color_gray">(<span id="clicknum_{$value[cid]}_33">$value[click_33]</span>)</span></a>
						 | <a href="{S_URL}/do.php?action=click&op=add&clickid=34&id=$value[cid]&hash=<!--{eval echo md5($value['authorid']."\t".$value['dateline']);}-->" id="click_{$value[cid]}_34" onclick="ajaxmenu(event, this.id, 2000, 'show_clicknum')" class="down">反对<span class="color_gray">(<span id="clicknum_{$value[cid]}_34">$value[click_34]</span>)</span></a>
						<!--{/if}-->
						<!--{if empty($value[authorid]) && $value[authorid] == $_SGLOBAL['supe_uid'] || $_SGLOBAL['member']['groupid'] == 1}-->
						 | <a href="#action/viewcomment/itemid/$value[itemid]/cid/$value[cid]/op/delete/php/1#">删除</a>
						<!--{/if}-->
					</div>
				</div>
				<!--{/loop}-->
				<!--{/if}-->
			</div><!--comment end-->

			<!--{if checkperm('allowcomment')}-->
			<div class="sign_msg">
				<a name="sign_msg"></a>
				<form  action="#action/viewcomment/itemid/$news[itemid]/php/1#" method="post">
				<script language="javascript" type="text/javascript" src="{S_URL}/batch.formhash.php?rand={eval echo rand(1, 999999)}"/></script>
				<fieldset>
				<legend>发表评论</legend>
				<div class="sign_msg_login">
					
				</div>
				<textarea style="background:#F9F9F9 url({S_URL}/images/comment/$_SCONFIG[commicon]) no-repeat 50% 50%;" id="message" cols="60" rows="4" name="message" onclick="clearcommentmsg();hideelement('imgseccode');" onblur="addcommentmsg();" onkeydown="ctlent(event,'postcomm');" />$_SCONFIG[commdefault]</textarea>
				<div class="sign_msg_sub">
					<!--{if $_SGLOBAL['supe_uid']}-->
						<!--{if checkperm('allowanonymous')}-->
						<label for="signcheck_01"><input class="input_checkbox" type="checkbox" id="signcheck_01" name="hideauthor" value="1" />匿名</label>
						<!--{/if}-->
						<!--{if checkperm('allowhideip')}-->
						<label for="signcheck_02"><input class="input_checkbox" type="checkbox" id="signcheck_02" name="hideip" value="1" />隐藏IP</label>
						<!--{/if}-->
						<!--{if checkperm('allowhidelocation')}-->
						<label for="signcheck_03"><input class="input_checkbox" type="checkbox" id="signcheck_03" name="hidelocation" value="1" />隐藏位置</label>
						<!--{/if}-->
						<!--{if $_SCONFIG['allowfeed']}-->
						<label for="signcheck_04"><input class="input_checkbox" type="checkbox" id="signcheck_04" name="addfeed" checked="checked">加入事件</label>
						<!--{/if}-->
					<!--{/if}-->
					<!--{if empty($_SCONFIG['noseccode'])}-->
					<span class="authcode_sub"><label style="margin-right:0;" for="seccode">验证码：</label> 
					<input type="text" class="input_tx" size="10" id="seccode" name="seccode" maxlength="4" onfocus="showelement('imgseccode')" /> 
					<img style="display:none;" id="imgseccode" class="img_code" src="{S_URL}/do.php?action=seccode" onclick="newseccode('imgseccode');" alt="seccode" title="看不清？点击换一张" />
					<a class="changcode_txt" title="看不清？点击换一张" href="javascript:showelement('imgseccode');newseccode('imgseccode');">换一张</a>
					</span>
					<!--{/if}-->
					
					<input type="submit" value="发表" name="searchbtn" onclick="return submitcheck();" class="input_search"/>
					<input type="hidden" value="$news[type]" name="type" />
					<input type="hidden" value="submit" name="submitcomm" />
					<input type="hidden" id="itemid" name="itemid" value="$news[itemid]" />
					<input type="hidden" id="upcid" name="upcid" value="" size="5" />
					<input type="hidden" id="type" name="type" value="$news[type]" size="5" />
	
				</div>
	
				</fieldset>
				</form>
				<p class="sign_tip">网友评论仅供网友表达个人看法，并不表明本网同意其观点或证实其描述。</p>
			</div><!--sign_msg end-->
			<!--{/if}-->
			<!--{/if}-->

			<div id="comment_op"><a href="#action/viewcomment/itemid/$news[itemid]#" class="view" target="_blank">查看全部回复</a><span>【已有$news[replynum]位网友发表了看法】</span></div>
			
			<!--{/if}-->
			
		</div>
		<!--{if !empty($ads3['pagecenterad'])}-->
		<div class="ad_mainbody">$ads3[pagecenterad]</div>
		<!--{/if}-->
		<!--图文资讯显示-->
		<!--{block name="spacenews" parameter="type/$channel/haveattach/2/showattach/1/order/i.lastpost DESC/limit/0,12/subjectlen/14/subjectdot/0/cachetime/8000/cachename/picnews"}-->
		<!--{if $_SBLOCK['picnews']}-->
		<div class="global_module margin_bot10">
			<div class="global_module1_caption"><h3>图文资讯</h3></div>
			<ul class="globalnews_piclist clearfix">
				<!--{loop $_SBLOCK['picnews'] $value}-->
				<li><a href="$value[url]" title="$value[subjectall]"><img src="$value[a_thumbpath]" alt="$value[subjectall]" /></a><span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></span></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->
	</div><!--col1 end-->

	<div class="col2">
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->
		

		<!--{block name="spacenews" parameter="catid/$thecat[subcatid]/order/i.dateline DESC/limit/0,10/subjectlen/26/subjectdot/0/cachetime/13800/cachename/newnews"}-->
		<!--{if !empty($_SBLOCK['newnews'])}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>最新报道</h3></div>
			<ul class="global_tx_list3">
				<!--{loop $_SBLOCK['newnews'] $value}-->
				<li><span class="box_r">#date('m-d', $value['dateline'])#</span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->

		<!--{block name="spacenews" parameter="type/$channel/dateline/2592000/digest/1,2,3/order/i.viewnum DESC,i.dateline DESC/limit/0,20/cachetime/89877/subjectlen/30/subjectdot/0/cachename/hotnews2"}-->
		<!--{if $_SBLOCK['hotnews2']}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>精彩推荐</h3></div>
			<ul class="global_tx_list3">
				<!--{loop $_SBLOCK['hotnews2'] $value}-->
					<li><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->

		<!--相关资讯-->
		<!--{if !empty($news[relativeitemids])}-->
		<!--{block name="spacenews" parameter="itemid/$news[relativeitemids]/order/i.dateline DESC/limit/0,20/cachetime/17680/cachename/relativeitem/tpl/data"}-->
		<!--{if !empty($_SBLOCK['relativeitem']) }-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>相关资讯</h3></div>
			<ul class="global_tx_list3">
			<!--{loop $_SBLOCK['relativeitem'] $ikey $value}-->
			<li><span class="box_r">#date('m-d', $value['dateline'])#</span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
			<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->
		<!--{/if}-->

		<!--{if !empty($ads3['siderad'])}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>网络资源</h3></div>
			<div class="ad_sidebar">
				$ads3[siderad]
			</div>

		</div>
		<!--{/if}-->

	</div><!--col2 end-->
</div><!--column end-->
<!--{if !empty($ads3['pagefootad'])}-->
<div class="ad_pagebody">$ads3[pagefootad]</div>
<!--{/if}-->

<script type="text/javascript">
<!--
	function clearcommentmsg() {
		if($('message').value == '$_SCONFIG[commdefault]') $('message').value = '';
	}
	function addcommentmsg() {
		if($('message').value == '') $('message').value = '$_SCONFIG[commdefault]';
	}
//-->
</script>
<script language="javascript" type="text/javascript">
<!--
	addMediaAction('article_body');
	addImgLink("article_body");
//-->
</script>
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
<!--{template footer}-->