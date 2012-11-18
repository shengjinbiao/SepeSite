<?exit?>

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
				<!--{block name="model" parameter="name/$channel/dateline/$gv[spreadtime]/order/i.click_$v[clickid] DESC/limit/0,1/cachetime/3600/subjectlen/40/subjectdot/0/cachename/click_$v[clickid]"}-->
					<!--{loop $_SBLOCK['click_'.$v[clickid]] $value}-->
						<li>[{$v[name]}最多的] <a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
					<!--{/loop}-->
			<!--{/loop}-->
		</ul>
		<!--{/if}-->
	</div>
	<!--{/if}-->
<!--{/loop}-->
