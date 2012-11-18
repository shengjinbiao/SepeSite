<?exit?>
<!--{template header}-->
<!--{eval $ads = getad('system', 'space', '1'); }-->
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
			<li><a href="$value[url]">$value[name]</a></li>
			<!--{/loop}-->
		</ul>
	</div>
</div><!--nav end-->

<div class="column" id="blog_detail">
	<!--{if !empty($ads['pagecenterad'])}-->
	<div class="ad_mainbody">$ads[pagecenterad]</div>
	<!--{/if}-->
	<div class="box_l">
		<div class="global_module margin_bot10">
			<div class="global_module2_caption"><h3>作者</h3></div>
			<div class="blog_user">
				<a href="{S_URL}/space.php?uid=$member[uid]"><img src="{UC_API}/avatar.php?uid=$member[uid]" alt="" /></a><br/>
				<a href="{S_URL}/space.php?uid=$member[uid]">$member[username]</a><br />
				<div class="user_group">
				用户组：<!--{if $member['groupid']}--><!--{eval echo $_SGLOBAL['grouparr'][{$member['groupid']}]['grouptitle']}--><!--{else}-->-<!--{/if}--><br />
				开通时间：$member[dateline]<br />
				更新时间：$member[updatetime]<br />
				上次登录时间：$member[lastlogin]
				</div>
			</div>
		</div>
 		<!--{if !empty($ads['siderad'])}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>网络资源</h3></div>
			<div class="ad_sidebar">
				$ads[siderad]
			</div>

		</div>
		<!--{/if}-->

	</div><!--box_l end-->
	
	<div class="box_r bg_fff">
		<div id="user_tab_caption">
			<a<!--{if $_GET['op'] == 'news'}--> class="current"<!--{/if}--> href="#uid/$_GET[uid]/op/news#">资讯</a>
			<!--{if uchome_exists() && !in_array('uchblog', $_SCONFIG['closechannels'])}-->
			<a<!--{if $_GET['op'] == 'uchblog'}--> class="current"<!--{/if}--> href="#uid/$_GET[uid]/op/uchblog#">日志</a>
			<!--{/if}-->
			<!--{if uchome_exists() && !in_array('uchphoto', $_SCONFIG['closechannels'])}-->
			<a<!--{if $_GET['op'] == 'uchphoto'}--> class="current"<!--{/if}--> href="#uid/$_GET[uid]/op/uchphoto#">相册</a>
			<!--{/if}-->
			<!--{if discuz_exists() && !in_array('bbs', $_SCONFIG['closechannels'])}-->
			<a<!--{if $_GET['op'] == 'bbs'}--> class="current"<!--{/if}--> href="#uid/$_GET[uid]/op/bbs#">论坛</a>
			<!--{/if}-->
		</div>
	
		<!--{if $_GET['op'] == 'uchblog' && uchome_exists() && !in_array('uchblog', $_SCONFIG['closechannels'])}-->
			<!--{block name="uchblog" parameter="uid/$_GET['uid']/order/dateline DESC/perpage/20/cachetime/3600/showdetail/1/messagelen/200/messagedot/1/cachename/infobody"}-->
		<!--{elseif $_GET['op'] == 'uchphoto' && uchome_exists() && !in_array('uchphoto', $_SCONFIG['closechannels'])}-->
			<!--{block name="uchphoto" parameter="uid/$_GET['uid']/order/updatetime DESC/perpage/6/subjectlen/30/messagedot/0/cachetime/3600/cachename/infobody"}-->
		<!--{elseif $_GET['op'] == 'bbs' && discuz_exists() && !in_array('bbs', $_SCONFIG['closechannels'])}-->
			<!--{block name="bbsthread" parameter="authorid/$_GET['uid']/order/dateline DESC/perpage/20/cachetime/3600/showdetail/1/messagelen/200/messagedot/1/cachename/infobody"}-->
		<!--{else}-->
			<!--{block name="spacenews" parameter="uid/$_GET['uid']/order/i.dateline DESC/perpage/20/cachetime/3600/showdetail/1/messagelen/200/messagedot/1/cachename/infobody"}-->
		<!--{/if}-->
		
		<!--{if $_GET['op'] == 'uchphoto'}-->
		
		<div class="global_module user_photolist">
			<div class="clearfix">
			<!--{loop $_SBLOCK['infobody'] $key $value}-->
			<dl>
				<dt><div><a href="$value[url]"><img src="$value[pic]" alt="" /></a></div></dt>
				<dd>
					<h5><a href="$value[url]">$value[albumname]</a></h5>
					<p><a href="{S_URL}/space.php?uid=$value[uid]">$value[username]</a> $value[picnum]张照片</p>
					<p>
					创建：
					<!--{if ($_SGLOBAL['timestamp'] - $value['dateline']) > 86400}--> 
					#date("Y-m-d", $value[dateline])#
					<!--{else}-->
					<!--{eval echo intval(($_SGLOBAL['timestamp'] - $value['dateline']) / 3600 + 1);}-->小时之前
					<!--{/if}-->
					</p>
					<p>更新：
					<!--{if ($_SGLOBAL['timestamp'] - $value['dateline']) > 86400}--> 
					#date("Y-m-d", $value[updatetime])#
					<!--{else}-->
					<!--{eval echo intval(($_SGLOBAL['timestamp'] - $value['dateline']) / 3600 + 1);}-->小时之前
					<!--{/if}--></p>
				</dd>
			</dl>
			<!--{/loop}-->
			</div>
			
			<!--{if !empty($_SBLOCK[infobody_multipage])}-->
				$_SBLOCK[infobody_multipage]
			<!--{/if}-->
			
			<!--{if empty($_SBLOCK['infobody'])}-->
				<div class="user_no_body">此用户尚未发表信息</div>
			<!--{/if}-->
		</div>
		<!--{else}-->
		<div class="global_module user_blog">			
			<!--{loop $_SBLOCK['infobody'] $key $value}-->
			<!--{if $_GET['op'] == 'bbs'}--><!--{eval $value['replynum'] = $value['replies'];}--><!--{/if}-->
			<div class="user_blog_list">
				<h5><a href="$value['url']">$value['subject']</a></h5>
				<p>$value['message']</p>
				<p class="user_blog_op"><a class="more" href="$value['url']">点击此处查看原文</a> 
				<span>
					<!--{if ($_SGLOBAL['timestamp'] - $value['dateline']) > 86400}--> 
					#date("Y-m-d", $value[dateline])#
					<!--{else}-->
					<!--{eval echo intval(($_SGLOBAL['timestamp'] - $value['dateline']) / 3600 + 1);}-->小时之前
					<!--{/if}--> | 
					<!--{if $_GET['op'] == 'bbs'}-->
					评论($value['replies']) | 阅读($value['views'])
					<!--{else}-->
					评论($value['replynum']) | 阅读($value['viewnum'])
					<!--{/if}-->
					
				</span></p>
			</div>
			<!--{/loop}-->
			
			<!--{if !empty($_SBLOCK[infobody_multipage])}-->
				$_SBLOCK[infobody_multipage]
			<!--{/if}-->
			
			<!--{if empty($_SBLOCK['infobody'])}-->
				<div class="user_no_body">此用户尚未发表信息</div>
			<!--{/if}-->
		</div>
		<!--{/if}-->
	</div>
</div><!--column end-->
<!--{if !empty($ads['pagefootad'])}-->
<div class="ad_pagebody">$ads[pagefootad]</div>
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