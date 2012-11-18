<?exit?>
<!--{template header}-->
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
<script>
function viewpoll(id) {
	if(id == 'viewpoll') {
		$('poll').style.display = 'none';
		$('poll_value').style.display = '';
	} else {
		$('poll').style.display = '';
		$('poll_value').style.display = 'none';
	}
}
</script>
<div class="column">
	<div class="col1">

		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module3_caption">
			<h3>你的位置
				<a href="{S_URL}/">$_SCONFIG[sitename]</a>
				&gt;&gt; $title
			</h3></div>
			<div id="article">
				<h1>$poll[subject]</h1>
				<p id="article_extinfo">投票人数：$poll[votersnum] | 开始时间：$poll[dateline] | 更新时间：$poll[updatetime]</p>
				<div id="article_body">
					<p>投票说明:$poll[summary]</p>
					<form id="pollform" action="#action/poll/php/1#" method="post">
					<div id="poll">
						<ul>
							<!--{loop $poll[options] $okey $options}-->
							<li>
							<!--{if $poll[ismulti]}-->
							<input type="checkbox" id="votekey-$okey" name="votekey[]" value="$okey" class="votekey" />
							<!--{else}-->
							<input type="radio" id="votekey-$okey" name="votekey[]" value="$okey" class="votekey"/>
							<!--{/if}-->
							<label for="votekey-$okey">$options[name]</label></li>
							<!--{/loop}-->
						</ul>
						<div class="poll_op">
							<input type="hidden" name="pollid" value="$poll[pollid]" />
							<input type="hidden" name="pollsubmit" value="yes" />
							<input class="input_search" id="dovote" name="pollbtn" type="submit" value="投票"/>
							<input class="input_search" type="button" value="查看" onclick="javascript:viewpoll('viewpoll');"/>
							<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
						</div>
					</form>
					</div><!--poll end-->
					
					<div id="poll_value" style="display:none">
						<ul>
							<!--{loop $poll[options] $key $options}-->
							<!--{eval $rand = rand(1,9)}-->
							<li>
								<h6>$options[name]</h6>
								<div>
									<span class="polloptionbar "><strong class="pollcolor$rand" style="width:<!--{eval echo ($options[percent]+1)}-->%;">$options[percent]%</strong></span>
									<span class="pollnum">得票:$options[num] / $options[percent]%</span>
								</div>
							</li>
							<!--{/loop}-->
						</ul>
						<div class="poll_op">
							<input class="input_search" type="button" value="返回"  onclick="javascript:viewpoll('return');"/>
						</div>
					</div><!--poll_value end-->
					

				</div>
			</div><!--article end-->



		</div>		
		
	</div><!--col1 end-->

	<div class="col2">
		
		<!--{block name="poll" parameter="order/dateline DESC/limit/0,10/cachetime/80000/cachename/poll/tpl/data"}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>更多投票</h3></div>
			<ul class="global_tx_list3">
				<!--{if empty($_SBLOCK['poll'])}-->
					<li>暂时没有调查</li>
				<!--{else}-->
					<!--{loop $_SBLOCK['poll'] $value}-->
					<li><a href="$value[url]">$value[subject]</a></li>
					<!--{/loop}-->
				<!--{/if}-->
			</ul>
		</div>

	</div><!--col2 end-->
</div><!--column end-->

<!--{template footer}-->