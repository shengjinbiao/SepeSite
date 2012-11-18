<?exit?>
<!--{template cp_header}-->

	<ul class="ext_nav clearfix">
		<li><a href="cp.php?ac=credit">积分日志</a></li>
		<li><a href="cp.php?ac=credit&op=rule">积分规则</a></li>
		<!--{if checkperm('allowtransfer') }-->
		<li><a href="cp.php?ac=credit&op=exchange">积分兑换</a></li>
		<!--{/if}-->
	</ul>
</div>

<!--{eval $_TPL['rewardtype'] = array('0' => '消费','1' => '奖励','2' => '惩罚');
	$_TPL['cycletype'] = array('0' => '一次性','1' => '每天','2' => '整点','3' => '间隔分钟','4' => '不限周期'); }-->

<div class="column">
	<div class="col1" >
	
	<!--{if empty($op)}-->
		<div class="global_module margin_bot10 bg_fff userpanel">
			<div class="global_module3_caption"><h3>你的位置：<a href="cp.php?ac=credit">积分日志</a></h3></div>
			<div class="integral">
				<div class="integral_caption"><h2>获得积分历史</h2></div>
				<table><tbody>
					<tr>
						<td width="17%">动作名称</td>
						<td width="17%">总次数</td>
						<td width="17%">周期次数</td>
						<td width="17%">单次积分</td>
						<td width="18%">单次经验值</td>
						<td>最后奖励时间</td>
					</tr>
					<!--{if $list}-->
						<!--{loop $list $key $value}-->
						<tr>
							<td><a>$value[rulename]</a></td>
							<td>$value[total]</td>
							<td>$value[cyclenum]</td>
							<td>$value[credit]</td>
							<td>$value[experience]</td>
							<td>#date('m-d H:i',$value[dateline], 1)#</td>
						</tr>
						<!--{/loop}-->
					<!--{else}-->
						<tr>
							<td colspan="6" class="user_no_body">暂时没有获得任何积分</td>
						</tr>
					<!--{/if}-->
				</tbody></table>
			</div>
		</div>
	<!--{elseif $op=='rule'}-->
		<div class="global_module margin_bot10 bg_fff userpanel">
			<div class="global_module3_caption"><h3>你的位置：<a href="cp.php?ac=credit&op=rule">积分规则</a></h3></div>
			<div class="personaldata">
				<table><tbody>
					<!--{if $list}-->
						<tr class="font_weight">
							<td>动作名称</td>
							<td width="80">奖励周期</td>
							<td width="80">奖励次数</td>
							<td width="80">奖励方式</td>
							<td width="80">获得积分</td>
							<td width="80">获得经验值</td>
						</tr>
						<!--{loop $list $value}-->
						<tr>
							<td>$value[rulename]</td>
							<td>$_TPL[cycletype][$value[cycletype]]</td>
							<td>$value[rewardnum]</td>
							<td<!--{if $value[rewardtype]==1}--> class="num_add"<!--{else}--> class="num_reduce"<!--{/if}-->>$_TPL[rewardtype][$value[rewardtype]]</td>
							<td><!--{if $value[rewardtype]==1}-->+<!--{else}-->-<!--{/if}-->$value[credit]</td>
							<td><!--{if $value[rewardtype]==2}-->-<!--{else}-->+<!--{/if}-->$value[experience]</td>
						</tr>
						<!--{/loop}-->
					<!--{else}-->
						<tr>
							<td colspan="5" class="user_no_body">暂无相关积分规则</td>
						</tr>
					<!--{/if}-->
					<!--{if $multi}-->
						<tr>
							<td colspan="6"><div class="pages">$multi</div></td>
						</tr>
					<!--{/if}-->
				</tbody></table>
			</div>
		</div>
	<!--{elseif $op == 'exchange'}-->
		<form method="post" action="cp.php?ac=credit&op=exchange">
			<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
			<div class="global_module margin_bot10 bg_fff userpanel">
				<div class="global_module3_caption"><h3>你的位置：<a href="cp.php?ac=credit&op=rule">积分兑换</a></h3></div>
				<div class="sumup">
					<h2>您可以将自己的积分兑换到本站其他的应用（比如论坛）里面。</h2>
					<table cellspacing="0" cellpadding="0" class="formtable">
						<tr><th width="150">目前您的积分数:</th>
							<td><span class="big_red">{$_SGLOBAL[member][credit]}</span></td></tr>
						<tr><th><label for="password">密码</label>:</th>
							<td><input type="password" name="password" class="t_input" /></td></tr>
						<tr><th>支出积分:</th>
							<td><input type="text" id="amount" name="amount" value="0" class="t_input" onkeyup="calcredit();" /></td></tr>
						<tr><th>兑换成:</th>
							<td><input type="text" id="desamount" value="0" class="t_input" disabled />&nbsp;&nbsp;
								<select name="tocredits" id="tocredits" onChange="calcredit();">
								<!--{loop $_CACHE['creditsettings'] $id $ecredits}-->
									<!--{if $ecredits[ratio]}-->
										<option value="$id" unit="$ecredits[unit]" title="$ecredits[title]" ratio="$ecredits[ratio]">$ecredits[title]</option>
									<!--{/if}-->
								<!--{/loop}-->
								</select></td></tr>
						<tr><th>兑换比率:</th>
							<td><span class="bold">1</span>&nbsp;<span id="orgcreditunit">积分</span>
								<span id="orgcredittitle"></span>&nbsp;兑换&nbsp;
								<span class="bold" id="descreditamount"></span>&nbsp;
								<span id="descreditunit"></span><span id="descredittitle"></span></td></tr>
						<tr><th>&nbsp;</th><td><input type="submit" name="exchangesubmit" value="兑换积分" class="submit"></td></tr>
					</table>
				</div>
			</div>
		</form>
	
		<script type="text/javascript">
			function calcredit() {
				tocredit = $('tocredits')[$('tocredits').selectedIndex];
				$('descreditunit').innerHTML = tocredit.getAttribute('unit');
				$('descredittitle').innerHTML = tocredit.getAttribute('title');
				$('descreditamount').innerHTML = Math.round(1/tocredit.getAttribute('ratio') * 100) / 100;
				$('amount').value = $('amount').value.toInt();
				if($('amount').value != 0) {
					$('desamount').value = Math.floor(1/tocredit.getAttribute('ratio') * $('amount').value);
				} else {
					$('desamount').value = $('amount').value;
				}
			}
			String.prototype.toInt = function() {
				var s = parseInt(this);
				return isNaN(s) ? 0 : s;
			}
			calcredit();
		</script>
	<!--{/if}-->
			
	</div>

	<div class="col2" >
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->

		<div id="contribute" class="global_module bg_fff margin_bot10">
			<div class="global_module2_caption"><h3>频道</h3></div>
			<ul>
				<!--{loop $channels['menus'] $value}-->
					<!--{if $value[type]=='type' || $value[upnameid]=='news'}-->
					<li<!--{if $value[nameid]==$type}--> class="current"<!--{/if}--> onclick="window.location.href='{S_URL}/cp.php?ac=news&op=list&do=$do&type=$value[nameid]&{eval echo rand(1, 999999)}';">
						<span><!--{if $value[nameid]==$type}-->共($listcount)条 当前频道<!--{else}-->浏览<!--{/if}--></span>
						<a>$value[name]</a></li>
					<!--{elseif $value[type]=='model'}-->
					<li onclick="window.location.href='{S_URL}/cp.php?ac=models&op=list&do=$do&nameid=$value[nameid]&{eval echo rand(1, 999999)}';">
						<span>浏览</span>
						<a>$value[name]</a></li>
					<!--{/if}-->
				<!--{/loop}-->
			</ul>
		</div>
	</div><!--col2-->
</div>
<!--{template cp_footer}-->