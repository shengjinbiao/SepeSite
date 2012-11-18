<?exit?>
<!--{template header}-->
<!--{eval $ads3 = getad('system', $modelsinfoarr[modelname], '3'); }-->
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

<div id="image_show" class="column global_module bg_fff">
	<div class="global_module3_caption">
	<h3>您的位置：
		<a href="{S_URL}/">首页</a>
		<!--{loop $guidearr $value}-->
			&gt;&gt; <a href="$value[url]">$value[name]</a>
		<!--{/loop}-->
		&gt;&gt; 查看评论
	</h3></div>
	<div id="article" class="comment_caption">
		<h1><a href="#action/model/name/$modelsinfoarr[modelname]/itemid/$itemid#">$item[subject]</a></h1>
		<p id="article_extinfo">查看数: $item[viewnum] |  评论数: $item[replynum]</p>
	</div>

	<div class="comment">
		<!--{loop $iarr $value}-->
		<div class="comment_list">
			<div class="comment_list_caption">
				<div class="box_l"><!--{if empty($value[authorid])}-->$value[author]<!--{else}--><a href="{S_URL}/space.php?uid=$value[authorid]" class="author">$value[author]</a><!--{/if}--> (#date("Y-n-d H:i:s", $value["dateline"])#)</div>
				<div class="box_r"><!--{if empty($value[authorid]) && $value[authorid] == $_SGLOBAL['supe_uid'] || $_SGLOBAL['member']['groupid'] == 1}-->
					<a href="#action/viewcomment/itemid/$value[itemid]/cid/$value[cid]/op/delete/php/1#">删除</a> | 
					<!--{/if}--><a href="javascript:;" onclick="getModelQuote('$modelsinfoarr[modelname]', '$value[cid]')">引用</a>
				</div>
			</div>
			<div class="comment_content">$value[message]</div>
		</div>
		<!--{/loop}-->
		<!--{if $multipage}-->
			$multipage
		<!--{/if}-->	
		
		<!--{if checkperm('allowcomment') && !empty($item[allowreply]) && !empty($_SCONFIG['commstatus'])}-->
		<div id="sign_msg">
			<form  action="#action/modelcomment/itemid/$item[itemid]/name/$modelsinfoarr[modelname]/php/1#" method="post">
			<script language="javascript" type="text/javascript" src="{S_URL}/batch.formhash.php?rand={eval echo rand(1, 999999)}"/></script>
			<fieldset>
			<legend>发表评论</legend>
			<textarea id="messagecomm" name="messagecomm" onfocus="showcode()" onkeydown="ctlent(event,'postcomm');"></textarea><br />
			<!--{if empty($_SCONFIG['noseccode'])}-->
			<div class="security_code">
				<label for="seccode">验证码：</label><input type="text" id="seccode" name="seccode" maxlength="4" style="width:85px;" /> <img id="xspace-imgseccode" src="{S_URL}/do.php?action=seccode" onclick="javascript:newseccode(this);" alt="seccode" title="看不清？点击换一个" /> <a class="c_blue" title="看不清？点击换一个" href="javascript:newseccode($('xspace-imgseccode'));">换一个</a>
			</div>
			<!--{/if}-->
			<!--{if $_SGLOBAL['supe_uid']&&$_SCONFIG['allowfeed']}-->
				<div id="add_event_box"><label for="add_event">加入事件</label>
				<input type="checkbox" name="addfeed" $addfeedcheck>		
				</div>
				<!--{/if}-->
			<input type="submit" value="提交" id="submit" class="input_search"/>
			<input type="hidden" value="submit" name="submitcomm" />
			<input type="hidden" id="itemid" name="itemid" value="$item[itemid]" />
			</fieldset>
			</form>
		</div><!--sign_msg end-->	
		<!--{/if}-->
	</div>
</div>

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