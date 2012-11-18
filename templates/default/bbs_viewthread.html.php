<?exit?>
<!--{template header}-->
<!--{eval $ads3 = getad('system', 'bbs', '3'); }-->
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
			<li <!--{if $key == 'bbs' }--> class="current"<!--{/if}-->><a href="$value[url]">$value[name]</a></li>
			<!--{/loop}-->
		</ul>
	</div>

	<ul class="ext_nav clearfix">
	<!--{block name="bbsforum" parameter="type/forum/allowblog/1/order/displayorder/limit/0,100/cachetime/14400/cachename/forumarr/tpl/data"}-->
		<!--{eval $dot = '|'}-->
		<!--{eval $total = count($_SBLOCK['forumarr'])}-->
		<!--{eval $i = 1;}-->
		<!--{loop $_SBLOCK['forumarr'] $value}-->
		<li><a href="$value[url]">$value[name]</a><!--{if $total != $i}--> $dot <!--{/if}--></li>
		<!--{eval $i++;}-->
		<!--{/loop}-->
	</ul>
</div><!--nav end-->

<!--{if !empty($ads3['pagecenterad'])}-->
<div class="ad_pagebody">$ads3[pagecenterad]</div>
<!--{/if}-->

<div class="column">
	<div class="col1">

		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module3_caption"><h3>
			<em style="float:right; padding:0 5px 0 0;">
			<a href="{B_URL}/viewthread.php?tid=$tid" title="转至$channels[menus][bbs][name]" class="vote" target="_blank">转至$channels[menus][bbs][name]</a>
			</em>
			你的位置：<a href="{S_URL}/">$_SCONFIG[sitename]</a>
			<!--{loop $guidearr $value}-->
			&gt;&gt; <a href="$value[url]">$value[name]</a>
			<!--{/loop}--></h3></div>
			<div id="article">
				<h1>$thread[subject]</h1>
				<p id="article_extinfo">发布: #date('Y-n-d H:i', $thread["dateline"])# |  作者: <a href="{S_URL}/space.php?uid=$thread[authorid]&op=bbs">$thread[author]</a> |  来源: $_SCONFIG[sitename]</p>
				
				<div id="article_body">
					<div class="t_msgfontfix">
						<!--{if !empty($ads3['viewinad'])}-->
						<div class="ad_article">
							$ads3[viewinad]
						</div>
						<!--{/if}-->
						$thread[message]
					</div>
					<!--{if !empty($thread['attachments'])}-->
					<div class="imginlog">
						<!--{loop $thread['attachments'] $value}-->
						<!--{if ($value['isimage'])}-->
						<p><img src="$value[attachment]"><br />$value[filename]</p>
						<!--{else}-->
						<p><img src="{S_URL}/images/base/haveattach.gif" align="absmiddle" border="0"><a href="{B_URL}/attachment.php?aid=$value[aid]" target="_blank"><strong>$value[filename]</strong></a><br />($value[dateline], Size: $value[attachsize], Downloads: $value[downloads])</p>
						<!--{/if}-->
						<!--{/loop}-->
					</div>
					<!--{/if}-->
				</div>
				
			</div><!--article end-->
			
			<!--{if $iarr}-->
			<div class="comment">
				<!--{loop $iarr $key $post}-->
				<div class="comm_list">
					<div class="title">
						<div class ="from_info">
							<span class="author"><a class="author" href="{S_URL}/space.php?uid=$post[authorid]">$post[author]</a></span></div>
						<span class="post_time">#date("Y-n-d H:i:s", $post["dateline"])#</span>
					</div>
					<div id="cid_$value[cid]" class="body">
						$post[message]
					</div>
					<!--{if !empty($item['posts'][$post['pid']]['attachments'])}-->
					<div class="imginlog">
						<!--{loop $item['posts'][$post['pid']]['attachments'] $post}-->
						<!--{if ($post['isimage'])}-->
						<p><img src="$post[attachment]"><br />$post[filename]</p>
						<!--{else}-->
						<p><img src="{S_URL}/images/base/haveattach.gif" align="absmiddle" border="0"><a href="{B_URL}/attachment.php?aid=$post[aid]" target="_blank"><strong>$post[filename]</strong></a><br />($post[dateline], Size: $post[attachsize], Downloads: $post[downloads])</p>
						<!--{/if}-->
						<!--{/loop}-->
					</div>
					<!--{/if}-->
				</div>
				<!--{/loop}-->
			</div><!--comment end-->
			<!--{/if}-->
			<div id="comment_op"><a class="write" href="{B_URL}/post.php?action=reply&tid=$thread[tid]" target="_blank">我也来说两句</a> <a class="view" href="{B_URL}/viewthread.php?tid=$thread[tid]" target="_blank">查看全部回复</a></div>
			
	
		</div>

	</div><!--col1 end-->

	<div class="col2">
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->
		
		<!--{if !empty($ads3['siderad'])}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>网络资源</h3></div>
			<div class="ad_sidebar">
				$ads3[siderad]
			</div>

		</div>
		<!--{/if}-->

		<!--最新帖子-->
		<!--{block name="bbsthread" parameter="order/dateline DESC/limit/0,9/subjectlen/36/subjectdot/0/cachetime/21400/cachename/ratehot/tpl/data"}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>最新发表</h3></div>
			<ul class="global_tx_list3">
				<!--{loop $_SBLOCK['ratehot'] $value}-->
				<li><span class="box_r"><a href="#uid/$value[authorid]#">$value[author]</a></span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
		</div>

	</div><!--col2 end-->
</div><!--column end-->

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

<!--{template footer}-->