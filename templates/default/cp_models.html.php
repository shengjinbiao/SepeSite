<?exit?>
<!--{template cp_header}-->

	<ul class="ext_nav clearfix">
		<li><a href="{S_URL}/cp.php?ac=models&op=add&amp;nameid=$_GET[nameid]&{eval echo rand(1, 999999)}">我要投稿</a></li>
		<li><a href="{S_URL}/cp.php?ac=models&op=list&do=pass&amp;nameid=$_GET[nameid]&{eval echo rand(1, 999999)}">我的发表区</a></li>
		<li><a href="{S_URL}/cp.php?ac=models&op=list&amp;nameid=$_GET[nameid]&{eval echo rand(1, 999999)}">我的待审区</a></li>
	</ul>
</div>

<div class="column">
	<div class="col1" >

<!--{if $op == 'list'}-->

	<form action="{S_URL}/cp.php?ac=models" method="post">
	<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
	<input type="hidden" name="nameid" value="$nameid" />

	<input type="hidden" name="do" value="$do" />
	
	<div class="global_module margin_bot10 bg_fff userpanel">
		<div class="global_module3_caption">
			<h3>你的位置：$channels['menus'][$channel][name] &gt;&gt;
				<!--{if $do == 'pass'}-->我的发表区<!--{else}-->我的待审区<!--{/if}--></h3>
		</div>
		<!--{if $list}-->
			<table class="article_list" cellspacing="0" cellpadding="0">
				<tbody>
					<!--{loop $list $value}-->
					<tr>
						<td class="td_input"><!--{if $do == 'me'}--><input type="checkbox" value="$value[itemid]"  name="item[]"/><!--{/if}--></td>
						<td><span class="color_gray"><!--{if $do == 'pass'}-->[<a href="{S_URL}/cp.php?ac=models&op=list&do=$do&catid=$value[catid]&nameid=$nameid" class="color_gray">{$categorylistarr[$value[catid]][name]}</a>]<!--{else}-->[{$categorylistarr[$value[catid]][name]}]<!--{/if}-->
							<!--{if $do == 'pass'}-->
							<!--{eval $nameid = $cacheinfo['models']['modelname'];}-->
							<a href="#action/model/name/$nameid/itemid/$value[itemid]#" target="_blank">
							<!--{else}-->
							<a href="{S_URL}/cp.php?ac=models&op=view&itemid=$value[itemid]&do=$do&nameid={$cacheinfo['models']['modelname']}">
							<!--{/if}-->
							$value[subject]</a> 
							#date("m-d H:i", $value[dateline], 1)#</span></td>
						<td width="130">

							</td>
						<!--{if $do == 'me'}-->
						<td width="40">
							<a href="{S_URL}/cp.php?ac=models&amp;op=edit&amp;itemid=$value[itemid]&amp;nameid=$_GET[nameid]">编辑</a>
						</td>
						<!--{/if}-->
					</tr>
					<!--{/loop}-->
					<tr>
						<td <!--{if $do == 'pass'}-->colspan="4"<!--{else}-->colspan="3"<!--{/if}-->>
						<!--{if $multipage}-->
						$multipage
						<!--{/if}-->
						</td>
					</tr>
					<!--{if $do == 'me'}-->
					<tr class="checkall_box">
						<td class="td_input"><input type="checkbox" onclick="checkall(this.form, 'item')" name="chkall"/> </td>
						<td colspan="4"><label for="check_all">全选</label> <input class="input_del" type="submit" value="删除" name="delitemsubmit" onclick="return confirm('删除不可恢复\n确认删除？');"/></td>
					</tr>
					<!--{/if}-->
				</tbody>
			</table>
		<!--{else}-->
			<div class="user_no_body"><!--{if empty($_SGLOBAL['supe_uid'])}-->游客无法浏览投稿信息<!--{else}-->没有符合条件的信息<!--{/if}--></div>
		<!--{/if}-->
	</div>
	</form>
	
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
					<li onclick="window.location.href='{S_URL}/cp.php?ac=news&op=list&do=$do&type=$value[nameid]&{eval echo rand(1, 999999)}';">
						<span>浏览</span>
						<a>$value[name]</a></li>
					<!--{elseif $value[type]=='model'}-->
					<li<!--{if $value[nameid]==$_GET['nameid']}--> class="current"<!--{else}--> onclick="window.location.href='{S_URL}/cp.php?ac=models&op=list&do=$do&nameid=$value[nameid]&{eval echo rand(1, 999999)}';"<!--{/if}-->>
						<span><!--{if $value[nameid]==$nameid}-->共($listcount)条 当前频道<!--{else}-->浏览<!--{/if}--></span>
						<a>$value[name]</a></li>
					<!--{/if}-->
				<!--{/loop}-->
			</ul>
		</div>
		
		<div class="global_module bg_fff margin_bot10">
			<div class="global_module2_caption"><h3>一周浏览最多的投稿</h3></div>
			<ul class="global_tx_list2">
			<!--{loop $mynews $value}-->
			<li><span class="box_r">$value[viewnum]</span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
			<!--{/loop}-->
			</ul>
		</div>

	</div><!--col2-->

<!--{elseif $op=='view'}-->

	<div class="global_module margin_bot10 bg_fff userpanel">
		<div class="global_module3_caption"><h3>你的位置：$channels['menus'][$channel][name] &gt;&gt; 详细内容</h3></div>
		<div class="view_article">
			<h2>内容查看</h2>
			<table class="list">
				<tr>
					<td class="left_title"><!--{if $cacheinfo['fielddefault']['subject']}-->{$cacheinfo[fielddefault][subject]}<!--{else}-->标题<!--{/if}-->：</td>
					<td>$item[subject]</td>
				</tr>
				<tr>
					<td class="left_title"><!--{if $cacheinfo['fielddefault']['subjectimage']}-->{$cacheinfo[fielddefault][subjectimage]}<!--{else}-->标题图片<!--{/if}-->：</td>
					<td><!--{if $item[subjectimage]}--><img src="$item[subjectthumb]" /><!--{else}-->&nbsp;<!--{/if}--></td>
				</tr>
				<tr>
					<td class="left_title"><!--{if $cacheinfo['fielddefault']['catid']}-->{$cacheinfo[fielddefault][catid]}<!--{else}-->模型分类<!--{/if}-->：</td>
					<td>{$categorylistarr[$item[catid]][name]}&nbsp;</td>
				</tr>
				<tr>
					<td class="left_title">发布时间：</td>
					<td>$item[dateline]</td>
				</tr>
				
			</table>
			<div class="content">
				<h3><!--{if $cacheinfo['fielddefault']['message']}-->{$cacheinfo[fielddefault][message]}<!--{else}-->内容<!--{/if}-->：</h3>
				$item[message]
			</div>
			<!--{if $htmlarr}-->
			<table class="list">
			<!--{loop $htmlarr $value}-->
				<tr>
					<td class="left_title">$value[subject]：</td>
					<td>$value[content]</td>
				</tr>
			<!--{/loop}-->
			</table>
			<!--{/if}-->
		</div>
		</div>
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
					<li onclick="window.location.href='{S_URL}/cp.php?ac=news&op=list&do=$do&type=$value[nameid]&{eval echo rand(1, 999999)}';">
						<span>浏览</span>
						<a>$value[name]</a></li>
					<!--{elseif $value[type]=='model'}-->
					<li<!--{if $value[nameid]==$nameid}--> class="current"<!--{/if}--> onclick="window.location.href='{S_URL}/cp.php?ac=models&op=list&do=$do&nameid=$value[nameid]&{eval echo rand(1, 999999)}';">
						<span><!--{if $value[nameid]==$nameid}-->当前频道<!--{else}-->浏览<!--{/if}--></span>
						<a>$value[name]</a></li>
					<!--{/if}-->
				<!--{/loop}-->
			</ul>
		</div>
		
		<!--{block name="spacenews" parameter="uid/$_SGLOBAL[supe_uid]/order/i.dateline DESC/limit/0,10/cachetime/900/subjectlen/40/subjectdot/0/cachename/mynews"}-->
		<div class="global_module bg_fff margin_bot10">
			<div class="global_module2_caption"><h3>支持最多的投稿</h3></div>
			<ul class="global_tx_list2">
			<!--{loop $_SBLOCK['mynews'] $value}-->
			<li><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
			<!--{/loop}-->
			</ul>
		</div>
	
		<!--{block name="spacenews" parameter="uid/$_SGLOBAL[supe_uid]/order/i.dateline DESC/limit/0,10/cachetime/900/subjectlen/40/subjectdot/0/cachename/mynews2"}-->
		<div class="global_module bg_fff">
			<div class="global_module2_caption"><h3>反对最多的投稿</h3></div>
			<ul class="global_tx_list2">
			<!--{loop $_SBLOCK['mynews2'] $value}-->
			<li><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
			<!--{/loop}-->
			</ul>
		</div>
	</div><!--col2-->
	
<!--{elseif $op=='add' || $op=='edit'}-->

	<script language="javascript">
	<!--//
		function textCounter(obj, showid, maxlimit) {
			var len = strLen(obj.value);
			var showobj = document.getElementById(showid);
			if(len < maxlimit) {
				showobj.innerHTML = maxlimit - len;
			} else {
				obj.value = getStrbylen(obj.value, maxlimit);
				showobj.innerHTML = "0";
			}
		}
		function strLen(str) {
			var charset = is_ie ? document.charset : document.characterSet;
			var len = 0;
			for(var i = 0; i < str.length; i++) {
				len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset.toLowerCase() == "utf-8" ? 3 : 2) : 1;
			}
			return len;
		}
	//-->
	</script>
	<script src="{S_URL}/include/js/selectdate.js" type="text/javascript" language="javascript"></script>
	<script src="{S_URL}/model/data/$channel/images/validate.js"></script>
	<form method="post" name="thevalueform" id="theform" action="{S_URL}/cp.php?ac=models&amp;nameid=$_GET[nameid]" enctype="multipart/form-data">
	<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
	<input type="hidden" name="itemid" value="$item[itemid]" />
	<input type="hidden" name="nid" value="$item[nid]" />
	<div class="global_module margin_bot10 bg_fff userpanel">
		<div class="global_module3_caption">
			<h3>你的位置： $channels['menus'][$channel][name] &gt;&gt; 我的投稿</h3>
		</div>
		<div class="contribution">
			<table width="100%" cellspacing="0" cellpadding="0" class="maintable">
				<tbody>
					<tr>
						<td width="110" align="right"><span class="color_red">*</span><!--{if $cacheinfo['fielddefault']['subject']}-->{$cacheinfo[fielddefault][subject]}<!--{else}-->标&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;题<!--{/if}-->&nbsp;</td>
						<td><input type="text" value="$item[subject]" size="52" id="subject" name="subject" /></td>
					</tr>

					<tr>
						<td align="right"><!--{if $cacheinfo['fielddefault']['subjectimage']}-->{$cacheinfo[fielddefault][subjectimage]}<!--{else}-->标题图片<!--{/if}-->&nbsp;</td>
						<td><input type="file" size="52" id="subjectimage" name="subjectimage"/>
						<!--{if $item[subjectimage]}-->
						<input type="hidden" value="$item[subjectimage]" id="subjectimage_value" name="subjectimage_value"/>
						<div id="subjectimage_dv"><a target="_blank" href="{A_DIR}/$item[subjectimage]">$item[subjectimage]</a> <a onclick="document.getElementById('subjectimage_value').value=''; this.parentNode.parentNode.removeChild(this.parentNode);" title="Delete" href="javascript:;">删除</a></div>
						<!--{/if}-->
						</td>
					</tr>

					<tr>
						<td align="right"><span class="color_red">*</span><!--{if $cacheinfo['fielddefault']['catid']}-->{$cacheinfo[fielddefault][catid]}<!--{else}-->模型分类<!--{/if}-->&nbsp;</td>
						<td>
						<select name="catid" id="catid">
								<!--{loop $categorylistarr $value}-->
									<option value="$value[catid]"<!--{if $value['catid'] == $item['catid']}--> selected="selected"<!--{/if}-->>{$value[pre]}{$value[name]}</option>
								<!--{/loop}-->
						</select>
						</td>
					</tr>

					<tr>
						<td width="14%" align="right" valign="top" ><!--{if $cacheinfo['fielddefault']['message']}-->{$cacheinfo[fielddefault][message]}<!--{else}-->内容<!--{/if}-->&nbsp;</td>
						<td valign="top">
							<div id="fulledit" style="margin-top:4px;" class="editerTextBox"><div id="message" class="editerTextBox"></div></div>
							<script type="text/javascript">
							function init() {
								et = new word("message", "{$item[message]}", 0, 0);
							}
							if(window.Event) {
								window.onload = init;
							} else {
								init();
							}
							</script>
						</td>
					</tr>
			</tbody>
			</table>
			<!--{if $htmlarr}-->
			
					
			<!--{loop $htmlarr $value}-->
			<table width="100%" class="globalbox_border">
				<tbody>
				<tr>
						<td width="14%" align="right">$value[subject]&nbsp;&nbsp;</td><td>$value[input]</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><span class="color_gray">$value[help]<script>$value[js]</script></span></td>
				</tr>
				</tbody>
			</table>
			<!--{/loop}-->
				
			<!--{/if}-->
			<table width="100%" cellspacing="0" cellpadding="0" class="maintable">
				<tbody>
					<tr>
						<td width="14%" align="right">加入事件&nbsp;</td>
						<td>
						<input type="radio" checked="checked" value="1" name="addfeed"/>是
						<input type="radio" value="0" name="addfeed"/>否
						</td>
					</tr>
				</tbody>
			</table>

		</div>
		<div class="contribution">
			<div style="padding-left:10px;" align="center">
			<input class="input_search" type="submit"  name="postsubmit" onclick="publish_article();"  value="提交"/>
			<input class="input_search" type="submit" name="searchbtn" value="重置"/>
			<input type="hidden" name="mid" value="{$cacheinfo[models][mid]}"/>
			</div>
			
		</div>
	</div>
	</form>
	
	</div>
	
	<div class="col2" style="overflow:visible;">
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->
	
		<div id="contribute" class="global_module bg_fff margin_bot10">
			<div class="global_module2_caption"><h3>投送频道</h3></div>
			<ul>
				<!--{loop $channels['menus'] $value}-->
					<!--{if $value[type]=='type' || $value[upnameid]=='news'}-->
					<li onclick="window.location.href='{S_URL}/cp.php?ac=news&op=add&type=$value[nameid]&{eval echo rand(1, 999999)}';">
						<span><!--{if !in_array($value[nameid], $postmenus)}-->无权限投稿<!--{else}-->投稿<!--{/if}--></span>
						<a>$value[name]</a></li>
					<!--{elseif $value[type]=='model'}-->
					<li<!--{if $value[nameid]==$nameid}--> class="current"<!--{else}--> onclick="window.location.href='{S_URL}/cp.php?ac=models&op=add&nameid=$value[nameid]&{eval echo rand(1, 999999)}';"<!--{/if}-->>
						<span><!--{if $value[nameid]==$_GET['nameid']}-->当前频道<!--{elseif !in_array($value[nameid], $postmenus)}-->无权限投稿<!--{else}-->投稿<!--{/if}--></span>
						<a>$value[name]</a></li>
					<!--{/if}-->
				<!--{/loop}-->
			</ul>
		</div>

		<!--{block name="spacenews" parameter="uid/$_SGLOBAL[supe_uid]/order/i.dateline DESC/limit/0,10/cachetime/900/subjectlen/40/subjectdot/0/cachename/mynews"}-->
		<div class="global_module bg_fff margin_bot10">
			<div class="global_module2_caption"><h3>审核通过的投稿</h3></div>
			<ul class="global_tx_list2">
			<!--{loop $_SBLOCK['mynews'] $value}-->
			<li><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
			<!--{/loop}-->
			</ul>
		</div>
	
		<!--{block name="spacenews" parameter="uid/$_SGLOBAL[supe_uid]/order/i.dateline DESC/limit/0,10/cachetime/900/subjectlen/40/subjectdot/0/cachename/mynews2"}-->
		<div class="global_module bg_fff">
			<div class="global_module2_caption"><h3>等待审核的投稿</h3></div>
			<ul class="global_tx_list2">
			<!--{loop $_SBLOCK['mynews2'] $value}-->
			<li><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
			<!--{/loop}-->
			</ul>
		</div>
	</div><!--col2-->
	
<!--{/if}-->

</div> 

<!--{template cp_footer}-->
	



