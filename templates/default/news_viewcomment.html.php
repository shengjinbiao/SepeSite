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
		<li><a href="<!--{if $channels['menus'][$type]['type'] == 'model'}-->$siteurl/m.php?name=$modelsinfoarr[modelname]&mo_catid=$value[catid]<!--{else}-->$value[url]<!--{/if}-->">$value[name]</a><!--{if $total != $i}--> $dot <!--{/if}--></li>
		<!--{eval $i++;}-->
		<!--{/loop}-->
	</ul>
</div><!--nav end-->

<!--{if !empty($ads3['pagecenterad'])}-->
<div class="ad_pagebody">$ads3[pagecenterad]</div>
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

<div class="column">
<div class="col1">
	<div class="comment_caption">
		<ul>
			<li<!--{if !$order}--> class="current"<!--{/if}-->><a href="#action/viewcomment/type/$type/itemid/$item[itemid]#"><div class="tab_all">全部评论<!--{if !$order}--><em>(评论共<span class="color_red">$listcount</span>条,显示<span class="color_red">$perpage</span>条)</em><!--{/if}--></div></a></li>
			<li<!--{if $order==1}--> class="current"<!--{/if}-->><a href="#action/viewcomment/type/$type/itemid/$item[itemid]/order/1#"><div class="tab_up"><span>最新支持</span><!--{if $order==1}--><em>（支持数大于2的评论）</em><!--{/if}--></div></a></li>
			<li<!--{if $order==2}--> class="current"<!--{/if}-->><a href="#action/viewcomment/type/$type/itemid/$item[itemid]/order/2#"><div class="tab_up"><span>最多支持</span><!--{if $order==2}--><em>（支持数最多的评论）</em><!--{/if}--></div></a></li>
			<li<!--{if $order==3}--> class="current"<!--{/if}-->><a href="#action/viewcomment/type/$type/itemid/$item[itemid]/order/3#"><div class="tab_down"><span>最新反对</span><!--{if $order==3}--><em>（反对数大于支持数的评论）</em><!--{/if}--></div></a></li>
			<li<!--{if $order==4}--> class="current"<!--{/if}-->><a href="#action/viewcomment/type/$type/itemid/$item[itemid]/order/4#"><div class="tab_down"><span>最多反对</span><!--{if $order==4}--><em>（反对数最多的评论）</em><!--{/if}--></div></a></li>
		</ul>
	</div><!--comment_caption end-->
	<div class="comment_cont">
		<div class="arti_title"><h1>评论：$item[subject]</h1><a class="color_red" href="<!--{if $channels['menus'][$type]['type'] == 'model'}-->#action/model/name/$modelsinfoarr[modelname]/itemid/$item[itemid]#<!--{else}-->#action/viewnews/itemid/$item[itemid]#<!--{/if}-->" target="_blank">[查看全文]</a></div>
		<div class="arti_summary">$item[message]</div>
		
		<!--{eval $gv = $clickgroups[3];}-->
		<!--{loop $iarr $value}-->
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

			</div>
		</div>
		<!--{/loop}-->
		
		<!--{if $multipage}-->
			$multipage
		<!--{/if}-->
	
		<!--{if checkperm('allowcomment')}-->
		<div class="sign_msg">
		<form  action="#action/viewcomment/itemid/$item[itemid]/php/1#" method="post">
		<script language="javascript" type="text/javascript" src="{S_URL}/batch.formhash.php?rand={eval echo rand(1, 999999)}"/></script>
		<fieldset>
		<legend>发表评论</legend>
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
				<input type="hidden" value="submit" name="submitcomm" />
				<input type="hidden" value="$item[type]" name="type" />
				<input type="hidden" id="itemid" name="itemid" value="$item[itemid]" />
				<input type="hidden" id="upcid" name="upcid" value="" size="5" />
				<input type="hidden" id="type" name="type" value="$channel" size="5" />
				<input type="hidden" id="ismodle" name="ismodle" value="$ismodle" />
			</div>

		</fieldset>
		</form>
		<p class="sign_tip">网友评论仅供网友表达个人看法，并不表明本网同意其观点或证实其描述。</p>
		</div><!--sign_msg end-->
		<!--{/if}-->
	
	
	</div><!--comment_cont end-->
</div><!--col1 end-->

<div class="col2">
	<div id="user_login">
		<script src="{S_URL}/batch.panel.php?rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
	</div><!--user_login end-->
	
	<!--{block name="spacecomment" parameter="type/$channel/itemid/$item[itemid]/click_33/2/order/click_33 DESC, dateline DESC/limit/0,10/cachetime/900/cachename/hotcomment/tpl/data"}-->
	<!--{if $_SBLOCK[hotcomment]}-->
	<div id="hot_comment">
		<h3>热门评论</h3>
		<!--{loop $_SBLOCK[hotcomment] $value}-->
		<!--{eval $value = formatcomment($value, array(), 1);}-->
		<div class="comm_list">
			<div class="title">
				<div class ="from_info"><span class="author">网友<!--{if !empty($value[authorid]) && !$value[hideauthor]}--><a href="{S_URL}/space.php?uid=$value[authorid]">[{$value[author]}]</a><!--{/if}--></span></div>
				<span class="post_time">#date("m-d H:i", $value[dateline], 1)#</span>
			</div>
			<div class="body">
				<p class="new">$value[message]</p>
			</div>
			<div class="comm_op">
				 <a href="javascript:;" onclick="clearcommentmsg();$('message').focus();addupcid($value[cid]);" class="replay">回复</a>
				 <!--{if $gv[status]}-->
				 | <a href="{S_URL}/do.php?action=click&op=add&clickid=33&id=$value[cid]&hash=<!--{eval echo md5($value['authorid']."\t".$value['dateline']);}-->" id="click_{$value[cid]}_33_hot" onclick="ajaxmenu(event, this.id, 2000, 'show_clicknum')" class="up"><span class="color_red">支持</span><span class="color_gray">(<span id="clicknum_{$value[cid]}_33_hot">$value[click_33]</span>)</span></a>
				 | <a href="{S_URL}/do.php?action=click&op=add&clickid=34&id=$value[cid]&hash=<!--{eval echo md5($value['authorid']."\t".$value['dateline']);}-->" id="click_{$value[cid]}_34_hot" onclick="ajaxmenu(event, this.id, 2000, 'show_clicknum')" class="down">反对<span class="color_gray">(<span id="clicknum_{$value[cid]}_34_hot">$value[click_34]</span>)</span></a>
				<!--{/if}-->
			</div>
		</div>
		<!--{/loop}-->
	</div><!--hot_comment end-->
	<!--{/if}-->

</div><!--col2 end-->
</div>

<!--{if !empty($ads3['pagefootad'])}-->
<div class="ad_pagebody">$ads3[pagefootad]</div>
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