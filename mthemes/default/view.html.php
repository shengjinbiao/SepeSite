<?exit?>
<!--{eval include template($tpldir.'/header.html.php', 1);}-->
<!--{eval $ads3 = getad('system', $modelsinfoarr[modelname], '3'); }-->
<!--{if !empty($ads3['pageheadad']) }-->
<div class="ad_header">$ads3[pageheadad]</div>
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
</div>

<div class="column">
	<div class="col1">
		<!--{if !empty($ads3['pagecenterad'])}-->
		<div class="ad_pagebody">$ads3['pagecenterad']</div>
		<!--{/if}-->

		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module3_caption">
			<h3>您的位置：<a href="{S_URL}/">$_SCONFIG[sitename]</a>
			<!--{loop $guidearr $value}-->
			&gt;&gt; <a href="$value[url]">$value[name]</a>
			<!--{/loop}-->
			&gt;&gt; 详细信息
			<!--{if $posturl}-->
			<a href="$posturl" title="在线投稿" class="btn_capiton_op" target="_blank">在线投稿</a>
			<!--{/if}-->
			</h3></div>

			<div id="article">
				<h1>$item[subject]</h1>
				
				<div id="article_extinfo">
					<div><span>
						<a href="#action/top/idtype/hot#" target="_blank" class="add_top10">排行榜</a> 
						<a href="javascript:;" class="add_bookmark" onclick="bookmarksite(document.title, window.location.href);">收藏</a> 
						<a href="javascript:doPrint();" class="print">打印</a> 
						</span>
						发布者：<!--{if $item[uid]}--><a href="{S_URL}/space.php?uid=$item[uid]">$item[username]</a><!--{else}-->游客<!--{/if}--> </div>
					<div><span>热度{$item[hot]}票&nbsp;&nbsp;浏览{$item[viewnum]}次<!--{if !empty($_SCONFIG['commstatus']) && !empty($modelsinfoarr['allowcomment'])}--> 【<a class="color_red" href="#action/viewcomment/itemid/$item[itemid]/type/$modelsinfoarr[modelname]#" target="_blank" title="点击查看">共$item[replynum]条评论</a>】【<a class="color_red" href="#sign_msg">我要评论</a>】<!--{/if}--></span>
						时间：#date('Y年n月d日 H:i', $item["dateline"])#</div>
				</div>

				<div id="article_body" class="job_box">
					<!--{if !empty($item[subjectimage])}-->
						<a href="$item[subjectimage]" target="_blank"><img src="$item[subjectimage]" align="left" class="img_max300"/></a>
					<!--{/if}-->
                    <!--{if !empty($ads3[viewinad])}-->
					<div class="ad_article">
						$ads3[viewinad]
					</div>
					<!--{/if}-->
					<div>$item[message]</div>

					<!--{if !empty($columnsinfoarr[fixed])}-->
					<div class="job_requ">
						<ul>
						<!--{loop $columnsinfoarr[fixed] $ckey $cvalue}-->
							<li>
								<em>$cvalue[fieldcomment]: </em>
							<!--{if !is_array($cvalue[value])}-->
								<!--{if $cvalue[formtype]=='textarea' }-->
								$cvalue[value]
								<!--{elseif $cvalue[formtype]=='timestamp'}-->
								#date("m月d日 H:i", $cvalue[value])#
								<!--{else}-->
								<a href="$siteurl/m.php?name=$modelsinfoarr[modelname]&mo_$cvalue[fieldname]=<!--{eval echo rawurlencode($cvalue[value]);}-->">$cvalue[value]</a>
								<!--{/if}-->
							<!--{else}-->
								<!--{loop $cvalue[value] $dkey $dvalue}-->
									<a href="$siteurl/m.php?name=$modelsinfoarr[modelname]&mo_$cvalue[fieldname]=<!--{eval echo rawurlencode($dvalue);}-->">$dvalue</a>&nbsp;
								<!--{/loop}-->
							<!--{/if}-->
							</li>
						<!--{/loop}-->
						</ul>
					</div>
					<!--{/if}-->

					<!--{if !empty($moreurl)}-->
					<div class="more"><a href="$moreurl">查看详情</a>	</div>
					<!--{/if}-->

					<!--{if !empty($columnsinfoarr[message])}-->
					<!--{loop $columnsinfoarr[message] $ckey $cvalue}-->
					<!--{if !empty($cvalue[isflash])}-->
					<div class="media img_max400">
						<h5>$cvalue[fieldcomment]：</h5>
						<div>
						<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="400" height="300">
						<param name="movie" value="$cvalue[filepath]" />
						<param name="quality" value="high" />
						<embed src="$cvalue[filepath]" type="application/x-shockwave-flash" pluginspage=" http://www.macromedia.com/go/getflashplayer" width="400" height="300"/>
						</object>
						</div>
					</div>
					<!--{elseif !empty($cvalue[isfile])}-->
					<div class="media">
						<h5>$cvalue[fieldcomment]：</h5>
						<div><a href="$siteurl/batch.modeldownload.php?hash=$cvalue[filepath]">下载</a></div>
					</div>
					<!--{elseif !empty($cvalue[isimage])}-->
					<div class="media">
						<h5>$cvalue[fieldcomment]：</h5>
						<div><a href="$cvalue[filepath]" target="_blank"><img src="$cvalue[filepath]" /></a></div>
					</div>
					<!--{else}-->
					<div class="media">
						<h5>$cvalue[fieldcomment]：</h5>
						<div>
							<!--{if !is_array($cvalue[value])}-->
								$cvalue[value]
							<!--{else}-->
								<!--{loop $cvalue[value] $dkey $dvalue}-->
									$dvalue&nbsp;
								<!--{/loop}-->
							<!--{/if}-->
						</div>
					</div>
					<!--{/if}-->
					<!--{/loop}-->
					<!--{/if}-->

				</div>
			</div><!--article end-->
			
			<div id="click_div">
			<!--{template model_click}-->
			</div>

			<!--{if checkperm('allowcomment') && !empty($item[allowreply]) && !empty($_SCONFIG['commstatus'])}-->
			<!--{if !empty($_SCONFIG['viewspace_pernum'])}-->
			<div class="comment">
				<!--{if !empty($commentlist)}-->
				<!--{loop $commentlist $value}-->
				<div class="comm_list">
					<div class="title">
						<div class="from_info">
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
						 | <a href="{S_URL}/cp.php?action=click&op=add&clickid=33&groupid=3&idtype=comments&id=$value[cid]&hash=<!--{eval echo md5($value['authorid']."\t".$value['dateline']);}-->" id="click_comments_{$value[cid]}_33_3" onclick="ajaxmenu(event, this.id, 2000, 'show_clicknum')" class="up"><span class="color_red">支持</span><span class="color_gray">(<span id="click_{$value[cid]}_33">$value[click_33]</span>)</span></a>
						 | <a href="{S_URL}/cp.php?action=click&op=add&clickid=34&groupid=3&idtype=comments&id=$value[cid]&hash=<!--{eval echo md5($value['authorid']."\t".$value['dateline']);}-->" id="click_comments_{$value[cid]}_34_3" onclick="ajaxmenu(event, this.id, 2000, 'show_clicknum')" class="down">反对<span class="color_gray">(<span id="click_{$value[cid]}_34">$value[click_34]</span>)</span></a>
						<!--{/if}-->
						<!--{if empty($value[authorid]) && $value[authorid] == $_SGLOBAL['supe_uid'] || $_SGLOBAL['member']['groupid'] == 1}-->
						 | <a href="#action/viewcomment/itemid/$value[itemid]/cid/$value[cid]/op/delete/php/1/type/$modelsinfoarr[modelname]/ismodle/1#">删除</a>
						<!--{/if}-->
					</div>
				</div>
				<!--{/loop}-->
				<!--{/if}-->
			</div><!--comment end-->

			<div class="sign_msg">
			<a name="sign_msg"></a>
			<form  action="#action/viewcomment/itemid/$item[itemid]/php/1#" method="post">
			<script language="javascript" type="text/javascript" src="{S_URL}/batch.formhash.php?rand={eval echo rand(1, 999999)}"/></script>
			<fieldset>
			<legend>发表评论</legend>
				<textarea style="background:#F9F9F9 url({S_URL}/images/comment/$_SCONFIG[commicon]) no-repeat 50% 50%;" id="message" cols="60" rows="4" name="message" onclick="clearcommentmsg();hideelement('imgseccode');" onblur="addcommentmsg();" onkeydown="ctlent(event,'postcomm');" />$_SCONFIG[commdefault]</textarea>
				<div class="sign_msg_sub">
					<!--{if $_SGLOBAL['supe_uid']}-->
					<label for="signcheck_01"><input class="input_checkbox" type="checkbox" id="signcheck_01" name="hideauthor" value="1" />匿名</label>
					<label for="signcheck_02"><input class="input_checkbox" type="checkbox" id="signcheck_02" name="hideip" value="1" />隐藏IP</label>
					<label for="signcheck_03"><input class="input_checkbox" type="checkbox" id="signcheck_03" name="hidelocation" value="1" />隐藏位置</label>
					<!--{if $_SCONFIG['allowfeed']}-->
					<label for="signcheck_04"><input class="input_checkbox" type="checkbox" id="signcheck_04" name="addfeed"<!--{if ($_SCONFIG['customaddfeed']&2)}--> checked="checked"<!--{/if}-->>加入事件</label>
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
					<input type="hidden" id="itemid" name="itemid" value="$item[itemid]" />
					<input type="hidden" id="upcid" name="upcid" value="" size="5" />
					<input type="hidden" id="type" name="type" value="$modelsinfoarr[modelname]" size="5" />
					<input type="hidden" id="ismodle" name="ismodle" value="1" />
				</div>
	
			</fieldset>
			</form>
			<p class="sign_tip">网友评论仅供网友表达个人看法，并不表明本网同意其观点或证实其描述。</p>
			</div><!--sign_msg end-->
			<!--{/if}-->
			<div id="comment_op"><a href="#action/viewcomment/itemid/$item[itemid]/type/$modelsinfoarr[modelname]#" class="view" target="_blank">查看全部回复</a><span>【已有$item[replynum]位网友发表了看法】</span></div>
			
			<!--{/if}-->

		</div>
	</div>

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

<div class="clear"></div>
<!--{if !empty($ads3['pagefootad'])}-->
<div class="ad_pagebody">
$ads3[pagefootad]
</div>
<!--{/if}-->

<!--{if !empty($ads3['pagemovead']) || !empty($ads3['pageoutad'])}-->
<!--{if !empty($ads3['pagemovead'])}-->
<div id="coupleBannerAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
	<div style="position: absolute; left: 6px; top: 6px;">
		$ads3[pagemovead]
		<br />
		<img src="{S_URL}/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');" />
	</div>
	<div style="position: absolute; right: 6px; top: 6px;">
		$ads3[pagemovead]
		<br />
		<img src="{S_URL}/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');" />
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
