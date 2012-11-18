<?exit?>

<!--{eval $cgid = 2;}-->
<!--{eval $gv = $clickgroups[$cgid]; unset($clickgroups[$cgid]);}-->
<!--{eval $counts = $clickcounts[$cgid]}-->
<!--{if $gv[status]}-->
<!--{if $gv[allowtop]}--><div id="article_state"><div class="box_r"><a href="#action/top/idtype/items/groupid/$cgid#" target="_blank">[{$gv[grouptitle]}排行榜]</a></div></div><!--{/if}-->
<div id="article_op" class="clearfix">
	<!--{eval $value = $clicks[$cgid];}-->
	<a class="aop_up" href="{S_URL}/do.php?action=click&op=add&clickid=9&id=$itemid&hash=$hash" id="click_{$itemid}_9" onclick="ajaxmenu(event, this.id, 2000, 'show_click')"><em>顶:</em>$value[9][clicknum]</a>
	<a class="aop_down" href="{S_URL}/do.php?action=click&op=add&clickid=10&id=$itemid&hash=$hash" id="click_{$itemid}_10" onclick="ajaxmenu(event, this.id, 2000, 'show_click')"><em>踩:</em>$value[10][clicknum]</a>
</div>
<!--{if $gv[allowspread]}-->
<div id="article_state">
<ul class="state_newstop clearfix">
	<!--{loop $clicks[$cgid] $v}-->
		<!--{block name="spacenews" parameter="dateline/$gv[spreadtime]/order/i.click_$v[clickid] DESC/limit/0,1/cachetime/0/subjectlen/40/subjectdot/0/cachename/click_$v[clickid]"}-->
			<!--{loop $_SBLOCK['click_'.$v[clickid]] $value}-->
				<li>[{$v[name]}最多的] <a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
			<!--{/loop}-->
	<!--{/loop}-->
</ul>
</div>
<!--{/if}-->
<!--{/if}-->

<div id="article_mark">
	<div class="dashed_botline">
	<table width="100%"><tbody>
		<!--{eval $cgid = 4;}-->
		<!--{eval $gv = $clickgroups[$cgid]; unset($clickgroups[$cgid]);}-->
		<!--{eval $counts = $clickcounts[$cgid]}-->
		<!--{if $gv[status]}-->
		<tr><td colspan="2">
				<!--{if $gv[allowtop]}--><div class="box_r"><a href="#action/top/idtype/items/groupid/$cgid#" target="_blank">[{$gv[grouptitle]}排行榜]</a></div><!--{/if}-->
				对本文中的事件或人物打分:</td></tr>
		<tr>
			<td style="width:370px">
				<div class="rating">
					<ul class="rating_bad">
					<!--{loop $clicks[$cgid] $value}-->
					<!--{if $value['score'] == 0}-->
					</ul>
					<ul class="rating_normal">
					<!--{/if}-->
						<li class="rating{$value[name]}"><a href="<!--{if $value[score]}-->{S_URL}/do.php?action=click&op=add&clickid=$value[clickid]&id=$itemid&hash=$hash" id="click_{$itemid}_{$value[clickid]}<!--{else}-->javascript:;<!--{/if}-->" onclick="ajaxmenu(event, this.id, 2000, 'show_click')">$value[name]</a><em>$value[score]</em></li>
					<!--{if $value['score'] == 0}-->
					</ul>
					<ul class="rating_good">
					<!--{/if}-->
					<!--{/loop}-->
				</div>
			</td>
			<td style="width:190px">当前平均分：<span<!--{if $counts[average]}--> class="color_red"<!--{/if}-->>{$counts[average]}</span> （{$counts[clicknum]}次打分）</td>
		</tr>
		<!--{if $gv[allowspread]}-->
		<tr>
			<td colspan="2">
			<div id="article_state" style="margin: 0;">
			<ul class="state_newstop clearfix">
				<!--{loop $clicks[$cgid] $v}-->
					<!--{block name="spacenews" parameter="dateline/$gv[spreadtime]/order/i.click_$v[clickid] DESC/limit/0,1/cachetime/0/subjectlen/40/subjectdot/0/cachename/click_$v[clickid]"}-->
						<!--{loop $_SBLOCK['click_'.$v[clickid]] $value}-->
							<li>[给{$v[name]}最多的] <a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
						<!--{/loop}-->
				<!--{/loop}-->
			</ul>
			</div>
			</td>
		</tr>
		<!--{/if}-->
		<!--{/if}-->
		
		<!--{eval $cgid = 5;}-->
		<!--{eval $gv = $clickgroups[$cgid]; unset($clickgroups[$cgid]);}-->
		<!--{eval $counts = $clickcounts[$cgid]}-->
		<!--{if $gv[status]}-->
		<tr><td style="padding-top:20px;" colspan="2">
				<!--{if $gv[allowtop]}--><div class="box_r"><a href="#action/top/idtype/items/groupid/$cgid#" target="_blank">[{$gv[grouptitle]}排行榜]</a></div><!--{/if}-->
				对本篇资讯内容的质量打分:</td></tr>
		<tr>
			<td style="width:370px">
				<div class="rating">
					<ul class="rating_bad">
					<!--{loop $clicks[$cgid] $value}-->
					<!--{if $value['score'] == 0}-->
					</ul>
					<ul class="rating_normal">
					<!--{/if}-->
						<li class="rating{$value[name]}"><a href="<!--{if $value[score]}-->{S_URL}/do.php?action=click&op=add&clickid=$value[clickid]&id=$itemid&hash=$hash" id="click_{$itemid}_{$value[clickid]}<!--{else}-->javascript:;<!--{/if}-->" onclick="ajaxmenu(event, this.id, 2000, 'show_click')">$value[name]</a><em>$value[score]</em></li>
					<!--{if $value['score'] == 0}-->
					</ul>
					<ul class="rating_good">
					<!--{/if}-->
					<!--{/loop}-->
				</div></td>
			<td style="width:190px">当前平均分：<span<!--{if $counts[average]}--> class="color_red"<!--{/if}-->>{$counts[average]}</span> （{$counts[clicknum]}次打分）</td>
		</tr>
		<!--{if $gv[allowspread]}-->
		<tr>
			<td colspan="2">
			<div id="article_state" style="margin: 0;">
			<ul class="state_newstop clearfix">
				<!--{loop $clicks[$cgid] $v}-->
					<!--{block name="spacenews" parameter="dateline/$gv[spreadtime]/order/i.click_$v[clickid] DESC/limit/0,1/cachetime/0/subjectlen/40/subjectdot/0/cachename/click_$v[clickid]"}-->
						<!--{loop $_SBLOCK['click_'.$v[clickid]] $value}-->
							<li>[给{$v[name]}最多的] <a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
						<!--{/loop}-->
				<!--{/loop}-->
			</ul>
			</div>
			</td>
		</tr>
		<!--{/if}-->
		<!--{/if}-->
	</tbody></table>
	</div>
</div><!--article_mark end-->

<!--{eval $cgid = 1;}-->
<!--{eval $gv = $clickgroups[$cgid]; unset($clickgroups[$cgid]);}-->
<!--{eval $counts = $clickcounts[$cgid];}-->
<!--{if $gv[status]}-->
<div id="article_state">
	<div class="dashed_botline">
		<div class="clearfix">
			<!--{if $gv[allowtop]}--><div class="box_r"><a href="#action/top/idtype/items/groupid/$cgid#" target="_blank">[{$gv[grouptitle]}排行榜]</a></div><!--{/if}-->
			<em>【已经有<span class="color_red">$counts[clicknum]</span>人表态】</em>
		</div>
		<div class="state_value clearfix">
			<table><tbody><tr>
				<!--{loop $clicks[$cgid] $value}-->
				<!--{eval $value['height'] = $counts['maxclicknum']?intval($value['clicknum']*80/$counts['maxclicknum']):0;}-->
				<td valign="bottom"><!--{if $value[clicknum]}--><div class="<!--{if $value[clicknum] == $counts[maxclicknum]}-->max_value<!--{/if}-->" style="height:{$value[height]}px;"><em>$value[clicknum]票</em></div><!--{/if}-->
					<a href="{S_URL}/do.php?action=click&op=add&clickid=$value[clickid]&id=$itemid&hash=$hash" id="click_{$itemid}_{$value[clickid]}" onclick="ajaxmenu(event, this.id, 2000, 'show_click')"><!--{if $value[icon]}--><img src="{S_URL}/images/click/$value['icon']" alt="" /><!--{/if}--><span>$value[name]</span></a></td>
				<!--{/loop}-->
			</tr></tbody></table>
		</div>
	</div>
	<!--{if $gv[allowspread]}-->
	<ul class="state_newstop clearfix">
		<!--{loop $clicks[$cgid] $v}-->
			<!--{block name="spacenews" parameter="dateline/$gv[spreadtime]/order/i.click_$v[clickid] DESC/limit/0,1/cachetime/3600/subjectlen/40/subjectdot/0/cachename/click_$v[clickid]"}-->
				<!--{loop $_SBLOCK['click_'.$v[clickid]] $value}-->
					<li>[{$v[name]}最多的] <a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
		<!--{/loop}-->
	</ul>
	<!--{/if}-->
</div>
<!--{/if}-->

<!--自定义表态部分-->
<!--{loop $clickgroups $cgid $gv}-->
	<!--{eval $counts = $clickcounts[$cgid];}-->
	<!--{if $gv[status]}-->
	<div id="article_state">
		<div class="dashed_botline">
			<div class="clearfix">
				<!--{if $gv[allowtop]}--><div class="box_r"><a href="#action/top/idtype/items/groupid/$cgid#" target="_blank">[{$gv[grouptitle]}排行榜]</a></div><!--{/if}-->
				<em>【已经有<span class="color_red">$counts[clicknum]</span>人表态】</em>
			</div>
			<div class="state_value clearfix">
				<table><tbody><tr>
					<!--{loop $clicks[$cgid] $value}-->
					<!--{eval $value['height'] = $counts['maxclicknum']?intval($value['clicknum']*80/$counts['maxclicknum']):0;}-->
					<td valign="bottom"><!--{if $value[clicknum]}--><div class="<!--{if $value[clicknum] == $counts[maxclicknum]}-->max_value<!--{/if}-->" style="height:{$value[height]}px;"><em>$value[clicknum]票</em></div><!--{/if}-->
						<a href="{S_URL}/do.php?action=click&op=add&clickid=$value[clickid]&id=$itemid&hash=$hash" id="click_{$itemid}_{$value[clickid]}" onclick="ajaxmenu(event, this.id, 2000, 'show_click')"><!--{if $value[icon]}--><img src="{S_URL}/images/click/$value['icon']" alt="" /><!--{/if}--><span>$value[name]</span></a></td>
					<!--{/loop}-->
				</tr></tbody></table>
			</div>
		</div>
		<!--{if $gv[allowspread]}-->
		<ul class="state_newstop clearfix">
			<!--{loop $clicks[$cgid] $v}-->
				<!--{block name="spacenews" parameter="dateline/$gv[spreadtime]/order/i.click_$v[clickid] DESC/limit/0,1/cachetime/3600/subjectlen/40/subjectdot/0/cachename/click_$v[clickid]"}-->
					<!--{loop $_SBLOCK['click_'.$v[clickid]] $value}-->
						<li>[{$v[name]}最多的] <a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
					<!--{/loop}-->
			<!--{/loop}-->
		</ul>
		<!--{/if}-->
	</div>
	<!--{/if}-->
<!--{/loop}-->
