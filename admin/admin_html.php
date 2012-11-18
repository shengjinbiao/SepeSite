<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_html.php 13422 2009-10-22 07:41:14Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managehtml')) {
	showmessage('no_authority_management_operation');
}

$delfilearr = array();
$thecachefile = S_ROOT.'./data/temp/make_html.cache.php';

//提交处理
if(submitcheck('htmlsubmit')) {
	//启用设置
	$setsqlarr = array();
	if(!empty($_POST['usesystem'])) {
		//推荐配置
		$setsqlarr[] = "('htmlmode', '2')";
		$setsqlarr[] = "('htmlindex', '1')";
		$setsqlarr[] = "('htmlindextime', '1800')";
		$setsqlarr[] = "('htmlcategory', '1')";
		$setsqlarr[] = "('htmlcategorytime', '21600')";
		$setsqlarr[] = "('htmlviewnews', '1')";
		$setsqlarr[] = "('htmlviewnewstime', '86400')";
		$_POST['htmlupdatemode'] = 1;
	} else {
		$setsqlarr[] = "('htmlmode', '$_POST[htmlmode]')";
		foreach ($_POST['htmltime'] as $key => $value) {
			$htmltype = empty($_POST['html'][$key])?0:1;
			$value = intval($value)*60;
			if($value<300) $value = 300;
			$setsqlarr[] = "('html{$key}', '$htmltype')";
			$setsqlarr[] = "('html{$key}time', '$value')";
		}
	}
	
	$_SGLOBAL['db']->query('REPLACE INTO '.tname('settings').' (variable, value) VALUES '.implode(',', $setsqlarr));
	//CACHE
	include_once(S_ROOT.'./function/cache.func.php');
	updatesettingcache();

	//更新模式
	sethtmlupdatemode($_POST['htmlupdatemode']);

	showmessage('html_allocation_preservation_success', $theurl);

} elseif (submitcheck('htmltimesubmit')) {
	//更新时间设置
	$text = '';
	$br = "\n";
	
	$newhtmltime = sstrtotime($_POST['htmltime']);
	if($newhtmltime > $_SGLOBAL['timestamp']) {
		showmessage('html_update_error', $theurl.'&op=update');
	} else {
		$cachefile = S_ROOT.'./data/system/html.cache.php';
		@include_once($cachefile);
		$text = '';
		$text .= '$htmltime=\''.$newhtmltime.'\';'."\n";
		$text .= '$htmlupdatemode=\''.$htmlupdatemode.'\';'."\n";
		writefile($cachefile, $text, 'php');
		showmessage('html_update_success', $theurl.'&op=update');
	}	
} elseif (submitcheck('deletesubmit')) {
	//删除文件
	$filename = trim($_POST['filename']);
	if(empty($filename)) {
		showmessage('html_deletefile_filename_error');
	}
	
	$filename = preg_quote($filename, "/");
	$filename = str_replace('\*', '.*?', $filename);
	$filename = str_replace('\|', '|', $filename);
	
	$delfilearr = array();
	
	$filearr = sreaddir(H_DIR);
	foreach ($filearr as $file) {
		if(is_dir(H_DIR.'/'.$file)) {
			$subfilearr = sreaddir(H_DIR.'/'.$file);
			foreach ($subfilearr as $subfile) {
				if(trim(substr(strrchr($subfile, '.'), 1)) == 'html') {
					if(preg_match("/^$filename/i", $subfile)) {
						if(@unlink(H_DIR.'/'.$file.'/'.$subfile)) {
							$delfilearr[] = array(H_DIR.'/'.$file.'/'.$subfile, 1);
						} else {
							$delfilearr[] = array(H_DIR.'/'.$file.'/'.$subfile, 1);
						}
					}
				}
			}
		} else {
			if(preg_match("/^$filename/i", $file)) {
				if(trim(substr(strrchr($file, '.'), 1)) == 'html') {
					if(@unlink(H_DIR.'/'.$file)) {
						$delfilearr[] = array(H_DIR.'/'.$file, 1);
					} else {
						$delfilearr[] = array(H_DIR.'/'.$file, 0);
					}
				}
			}
		}
	}

	if(empty($delfilearr)) {
		$delfilearr[] = array(H_DIR, 2);
	}
	
	$_GET['op'] = 'deleteresult';
	
} elseif (submitcheck('makesubmit')) {

	$pagearr = array(array(), array(), array());
	$itemid1 = intval($_POST['itemid1']);
	$itemid2 = intval($_POST['itemid2']);
	$dateline1 = empty($_POST['dateline1'])?0:sstrtotime($_POST['dateline1']);
	$dateline2 = empty($_POST['dateline2'])?0:sstrtotime($_POST['dateline2']);
	$catid = $_POST['catid'];
	$types = empty($_POST['type'])?'':simplode($_POST['type']);
	
	//页面类型
	//action/itemid/uid/
	$wheresql = array();
	if(!empty($_POST['pagetype'])) {
		foreach ($_POST['pagetype'] as $value) {
			if($value == 'viewnews' && $_SCONFIG['htmlviewnews']) {
				//查看资讯页面
				$wheresql = array();
				$wheresql[] = "type='news'";
				if($itemid2 > $itemid1) {
					$wheresql[] = "itemid>'$itemid1' AND itemid<'$itemid2'";
				}
				if(!empty($catid)) {
					$wheresql[] = "catid IN ($catid)";
				}
				if($dateline2 > $dateline1) {
					$wheresql[] = "dateline>'$dateline1' AND dateline<'$dateline2'";
				}

				$query = $_SGLOBAL['db']->query("SELECT uid, type, itemid FROM ".tname('spaceitems')." WHERE ".implode(' AND ', $wheresql)." ORDER BY itemid");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$pagearr[0][] = "viewnews|$value[itemid]";
				}
			} elseif($value == 'category' && $_SCONFIG['htmlcategory']) {
				//分类
				$wheresql = array();
				$sql = "SELECT catid FROM ".tname('categories');
				if(!empty($catid)) {
					$wheresql[] = "catid IN ($catid)";
				}
				if(!empty($types)) {
					$wheresql[] = "type IN ($types)";
				}
				$wheresql[] = "url=''";
				if(!empty($wheresql)) {
					$sql .= " WHERE type='news' AND ".implode(' AND ', $wheresql);
				}
				$query = $_SGLOBAL['db']->query($sql);
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$pagearr[1][] = "category|$value[catid]";
				}
			} elseif($value == 'index' && $_SCONFIG['htmlindex']) {
				//频道首页
				foreach ($channels['menus'] as $value) {
					if($value['type'] != 'user' || $value['type'] != 'model') {
						$pagearr[2][] = $value['nameid'];
					}
				}
			}
		}
	}
	if(!empty($_POST['batchthread']) && discuz_exists()) {
		dbconnect(1);
		//帖子查看页面
		if($_SCONFIG['htmlviewnews']) {
			$wheresql = array();
			$wheresql[] = "supe_pushstatus>0";
			if($dateline2 > $dateline1) {
				$wheresql[] = "dateline>'$dateline1' AND dateline<'$dateline2'";
			}
			$query = $_SGLOBAL['db_bbs']->query("SELECT tid FROM ".tname('threads', 1)." WHERE ".implode(' AND ', $wheresql)." ORDER BY tid");
			while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
				$pagearr[0][] = "viewthread|$value[tid]";
			}
		}
		//帖子列表
		if($_SCONFIG['htmlcategory']) {
			$query = $_SGLOBAL['db_bbs']->query("SELECT fid FROM ".tname('forums', 1)." WHERE type<>'group' AND allowshare=1");//url为空
			while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
				$pagearr[1][] = "forumdisply|$value[fid]";
			}
		}
	}
	
	//写入缓存
	$cachetext = (empty($pagearr[0])?'':implode("\n", $pagearr[0])."\n").(empty($pagearr[1])?'':implode("\n", $pagearr[1])."\n").implode("\n", $pagearr[2]);
	writefile($thecachefile, $cachetext);

	$_GET['op'] = 'making';
	$_GET['pernum'] = $_POST['pernum'];
}

//变量
$op = empty($_GET['op'])?'setting':$_GET['op'];
$activearr = array('setting'=>'', 'update'=>'', 'delete'=>'', 'make'=>'');
$activearr[$op] = ' class="active"';

//显示导航菜单
print<<<END
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>$alang[admincp_header_html]</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td$activearr[setting]><a href="$theurl&op=setting">$alang[admincp_html_opening]</a></td>
					<td$activearr[update]><a href="$theurl&op=update">$alang[html_title_update]</a></td>
					<td$activearr[delete]><a href="$theurl&op=delete">$alang[userhtml_op_delete]</a></td>
					<td$activearr[make]><a href="$theurl&op=make">$alang[html_manual_generation]</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
END;

if($op == 'setting') {

	$htmlupdatemodearr = array(1=>'', 2=>'');
	include_once(S_ROOT.'./data/system/html.cache.php');
	if(empty($htmlupdatemode)) $htmlupdatemode = 1;
	$htmlupdatemodearr[$htmlupdatemode] = ' checked';

	//启用
	$html = $htmltime = array();
	$makehtml = array('0' => '', '1' => '');
	$isopen = 0;
	foreach (array('index', 'category', 'viewnews') as $value) {
		$html[$value] = '';
		if(!empty($_SCONFIG["html{$value}"])) {
			$html[$value] = 'checked ';
			$isopen = 1;	
		}
		$htmltime[$value] = intval((empty($_SCONFIG["html{$value}time"])?'15':$_SCONFIG["html{$value}time"]/60));
	}
	$makehtml[$isopen] = ' checked ';
	//链接模式
	$htmlmodearr = array(1=>'', 2=>'');
	if(empty($_SCONFIG['htmlmode'])) $_SCONFIG['htmlmode']= 1;
	$htmlmodearr[$_SCONFIG['htmlmode']] = ' checked';
	
	//启用设置
	$formhash = formhash();
	print<<<END
	<script>
		function make(val) {
			var checkstr = val=="1"?true:false;
			var checkboxobj = getbyid("listid").getElementsByTagName("input");
			for(i in checkboxobj) {
				if(checkboxobj[i].type == "checkbox") {
					checkboxobj[i].checked = checkstr;
				}
			}
			if(!checkstr) {
				getbyid("htmlmode").checked = true;
				getbyid("submit").click();
			}

		}
	</script>
	<table cellspacing="2" cellpadding="2" class="helptable">
	<tr><td><ul>
	$alang[admincp_html_opening_note]
	</ul></td></tr>
	</table>
			
	<form method="post" name="theform" id="theform" action="$theurl">
	<input type="hidden" name="formhash" value="$formhash">
	<table cellspacing="0" cellpadding="0" width="100%" class="maintable">
	<tr>
		<th>$alang[admincp_html_opening]</th>
		<td>
			<input type="radio" name="makehtml" value="0"{$makehtml[0]} onclick="make(this.value)">$alang[do_not_open]
			<input type="radio" name="makehtml" value="1"{$makehtml[1]} onclick="make(this.value)">$alang[block_forum_allow_1]
		</td>
	</tr>
	<tr>
		<th>$alang[html_link_mode]</th>
		<td><input type="radio" id="htmlmode" name="htmlmode" value="1"{$htmlmodearr[1]}>$alang[html_link_mode_1]
		<br><input type="radio" name="htmlmode" value="2"{$htmlmodearr[2]}>$alang[html_link_mode_2]</td>
	</tr>
	</table>
	<br>
	
	<table cellspacing="0" cellpadding="0" width="100%" class="listtable">
	<tr>
		<th colspan="3"><strong>$alang[html_page_polymerization_installed_supesite]</strong></th>
	</tr>
	<tbody id="listid">
	<tr>
		<td><strong>$alang[front_page_level]</strong></td>
		<td><input type="checkbox" name="html[index]" value="1" $html[index]/>$alang[static_document_generated_html]</td>
		<td>$alang[html_interval] <input type="text" name="htmltime[index]" value="$htmltime[index]" size="5" /> $alang[page_updated_minutes]</td>
	</tr>
	<tr>
		<td><strong>$alang[index_page_level2]</strong></td>
		<td><input type="checkbox" name="html[category]" value="1" $html[category]/>$alang[static_document_generated_html]</td>
		<td>$alang[html_interval] <input type="text" name="htmltime[category]" value="$htmltime[category]" size="5" /> $alang[page_updated_minutes]</td>
	</tr>
	<tr>
		<td><strong>$alang[see_page_level3]</strong></td>
		<td><input type="checkbox" name="html[viewnews]" value="1" $html[viewnews]/>$alang[static_document_generated_html]</td>
		<td>$alang[html_interval] <input type="text" name="htmltime[viewnews]" value="$htmltime[viewnews]" size="5" /> $alang[page_updated_minutes]</td>
	</tr>
	</tbody>
	<tr>
		<td colspan="3"><input type="checkbox" name="usesystem" value="1" />$alang[html_installed_using_the_system_recommended]</td>
	</tr>
	</table>
	
	<div class="buttons">
	<input type="hidden" name="htmlsubmit" value="true" />
	<input type="submit" name="submit" id="submit" value="$alang[save_setup_submit]" class="submit">
	<input type="reset" name="htmlreset" value="$alang[common_reset]">
	</div>
	</form>
END;

} elseif($op == 'update') {
	//更新设置
	$now = sgmdate($_SGLOBAL['timestamp'], 'Y-m-d H:i:s');
	
	$formhash = formhash();
	print<<<END
	<script src="include/js/selectdate.js" language="JavaScript" type="text/javascript"></script>
	<table cellspacing="2" cellpadding="2" class="helptable">
	<tr><td><ul>
	$alang[update_html_note]
	</ul></td></tr>
	</table>
			
	<form method="post" name="theform" id="theform" action="$theurl">
	<input type="hidden" name="formhash" value="$formhash">
	<table cellspacing="0" cellpadding="0" width="100%" class="maintable">
	<tr>
		<th>$alang[choose_a_time_update]</th>
		<td><input name="htmltime" readonly type="text" id="htmltime" value="$now"/> <img src="admin/images/time.gif" onClick="getDatePicker('htmltime',event,21)" align="absmiddle" /></td>
	</tr>
	</table>
	<br>

	<div class="buttons">
	<input type="submit" name="htmltimesubmit" value="$alang[time_set]" class="submit">
	<input type="reset" name="htmltimereset" value="$alang[common_reset]">
	</div>
	</form>
END;

} elseif($op == 'delete') {
	//批量删除
	$formhash = formhash();
	print<<<END
	<table cellspacing="2" cellpadding="2" class="helptable">
	<tr><td><ul>
	$alang[delete_html_note] 
	<ul></td></tr>
	</table>
			
	<form method="post" name="theform" id="theform" action="$theurl">
	<input type="hidden" name="formhash" value="$formhash">
	<table cellspacing="0" cellpadding="0" width="100%" class="maintable">
	<tr>
		<th>$alang[html_file_name_to_be_deleted_input]</th>
		<td><input name="filename" type="text" value=""/></td>
	</tr>
	</table>
	<br>

	<div class="buttons">
	<input type="submit" name="deletesubmit" value="$alang[delete_file]" class="submit">
	<input type="reset" name="deletereset" value="$alang[common_reset]">
	</div>
	</form>
END;

} elseif($op == 'deleteresult') {
	//删除结果
	$delstr = '';
	foreach ($delfilearr as $file) {
		if($file[1] == 1) {
			$delstr .= '<tr><th>'.$file[0].'</th><td><b>'.$alang['userhtml_delete_success'].'</b></td></tr>';
		} elseif($file[1] == 2) {
			$delstr .= '<tr><th>'.$file[0].'</th><td>'.$alang['userhtml_no_file'].'</td></tr>';
		} else {
			$delstr .= '<tr><th>'.$file[0].'</th><td>'.$alang['userhtml_delete_fail'].'</td></tr>';
		}
	}
	print<<<END
	<table cellspacing="2" cellpadding="2" class="helptable">
	<tr><td>$alang[delete_results]</td></tr>
	</table>

	<table cellspacing="0" cellpadding="0" width="100%" class="maintable">
	$delstr
	</table>	
END;
	
} elseif($op == 'make') {
	//批量生成
	
	$dateline1 = sgmdate($_SGLOBAL['timestamp']-3600*24*7, 'Y-n-d');//7天前
	$dateline2 = sgmdate($_SGLOBAL['timestamp'], 'Y-n-d');
	
	$typestr = '';
	foreach ($channels['types'] as $value) {
		$typestr .= "<input type=\"checkbox\" name=\"type[]\" value=\"$value[nameid]\"> $value[name]";
	}
	
	$pagestr = '';
	if(file_exists($thecachefile)) {
		$lasttime = filemtime($thecachefile);
		$pagearr = file($thecachefile);
		$pagestr = '<tr><td colspan="2">'.$alang['batch_last_several_file_generated_html_0'].':'.count($pagearr).$alang['batch_last_several_file_generated_html_1'].':'.sgmdate($lasttime).' (<a href="'.$theurl.'&op=making&pernum=1">'.$alang['batch_last_several_file_generated_html_2'].'</a>)</td></tr>';
	}
	
	$formhash = formhash();
	print<<<END
	<table cellspacing="2" cellpadding="2" class="helptable">
	<tr><td><ul>$alang[make_html_note]</ul></td></tr>
	</table>

	<form method="post" name="theform" id="theform" action="$theurl">
	<input type="hidden" name="formhash" value="$formhash">
	<table cellspacing="0" cellpadding="0" width="100%" class="maintable">
	<tr><th>$alang[generation_page_types]</th><td>
	$alang[view_page]: 
	<input type="checkbox" name="pagetype[]" value="viewnews" checked> $alang[see_page_level3]
	<br>$alang[page_list]:
	<input type="checkbox" name="pagetype[]" value="category" checked> $alang[index_page_level2]
	<br>$alang[html_home_page]:
	<input type="checkbox" name="pagetype[]" value="index" checked> $alang[channel_home_page]
	</td></tr>
	
	<tr><th>$alang[channel_type_choice]</th><td>$typestr</td></tr>
	<tr><th>$alang[id_information_designated_areas]</th><td><input type="text" name="itemid1" value="0" size="10"> ~  <input type="text" name="itemid2" value="0" size="10"></td></tr>
	<tr><th>$alang[id_classification_system_set]</th><td><input type="text" name="catid" value=""> $alang[id_classification_system_set_note]</td></tr>
	<tr><th>$alang[block_thread_title_dateline]</th><td><input type="text" name="dateline1" value="$dateline1" size="10"> ~  <input type="text" name="dateline2" value="$dateline2" size="10"> ($alang[date_format]: 2007-01-22)</td></tr>
	<tr><th>$alang[whenever_the_number_of_pages]</th><td><input type="text" name="pernum" value="1" size="5"> $alang[whenever_the_number_of_pages_note]</td></tr>
	<tr><td colspan="2"><input type="checkbox" name="batchthread" value="1"> $alang[ss_from_the_forum_to_html]</td></tr>
	$pagestr
	</table>

	<div class="buttons">
	<input type="submit" name="makesubmit" value="$alang[batch_create_html]" class="submit">
	<input type="reset" name="makereset" value="$alang[common_reset]">
	</div>
	</form>
END;
} elseif($op == 'making') {
	
	@ini_set('max_execution_time', 2000);	//设置超时时间
	
	$batch_makehtml_pagearr = array();
	//action/itemid/uid/
	if(file_exists($thecachefile)) {
		$batch_makehtml_pagearr = file($thecachefile);
	}
	if(empty($batch_makehtml_pagearr)) {
		showmessage('html_page_not_found_with_generation');
	}
	
	$batch_makehtml_count = empty($_GET['count'])?count($batch_makehtml_pagearr):intval($_GET['count']);
	$batch_makehtml_start = empty($_GET['start'])?0:intval($_GET['start']);
	$batch_makehtml_pernum = empty($_GET['pernum'])?5:intval($_GET['pernum']);
	
	$batch_makehtml_makes = array();
	if($batch_makehtml_start<$batch_makehtml_count) {
		for($batch_makehtml_i=$batch_makehtml_start; $batch_makehtml_i<$batch_makehtml_start+$batch_makehtml_pernum; $batch_makehtml_i++) {
			if(!isset($batch_makehtml_pagearr[$batch_makehtml_i])) break;//没有就退出
			obclean();
			$_SGET = $_SHTML = array();
			$thepage = $batch_makehtml_pagearr[$batch_makehtml_i];
			$arr = explode('|', trim($thepage));
			if(empty($arr[0])) continue;
			
			$_SGET['action'] = $arr[0];
			$_SGET['php'] = 1;
			if($arr[0] == 'viewnews') {
				$_SGET['itemid'] = $arr[1];
				$query = $_SGLOBAL['db']->query('SELECT newsurl FROM '.tname('spacenews').' WHERE itemid=\''.$_SGET['itemid'].'\' ORDER BY nid LIMIT 0,1');
				if($_SGLOBAL['db']->result($query, 0)) {
					continue;//有跳转
				}
			} elseif($arr[0] == 'category') {
				$_SGET['catid'] = $arr[1];
			} elseif($arr[0] == 'viewthread') {
				$_SGET['tid'] = $arr[1];
				//避免跳转
				$thread = $item = array();
				if($_SGET['tid'] && discuz_exists()) {
					dbconnect(1);
					$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('threads', 1).' WHERE tid=\''.$_SGET['tid'].'\'');
					$thread = $_SGLOBAL['db_bbs']->fetch_array($query);
				}
				$jumptobbs = false;
				if(!empty($thread['readperm'])) {
					$jumptobbs = true;
				} elseif (!empty($thread['price'])) {
					$jumptobbs = true;
				}
				if(B_VER == '5') {
					if($thread['supe_pushstatus'] <= 0) {
						$jumptobbs = true;
					}
				}
				if($jumptobbs) continue;
				
				if(discuz_exists()) {
					$fid = $thread['fid'];
					$query = $_SGLOBAL['db_bbs']->query('SELECT f.*, ff.* FROM '.tname('forums', 1).' f LEFT JOIN '.tname('forumfields', 1).' ff ON ff.fid=f.fid WHERE f.fid=\''.$fid.'\'');
					if(!$forum = $_SGLOBAL['db_bbs']->fetch_array($query)) {
						continue;
					}
					if($forum['status'] < 1) {//隐藏板块
						$jumptobbs = true;
					} elseif(!empty($forum['password'])) {
						$jumptobbs = true;
					} elseif(!empty($forum['viewperm'])) {
						$viewpermarr = explode("\t", $forum['viewperm']);
						if(!in_array('7', $viewpermarr)) {
							$jumptobbs = true;
						}
					} elseif(!empty($forum['redirect'])) {
						$forumurl = $forum['redirect'];
						$jumptobbs = true;
					}
					if($jumptobbs) continue;
				}

			} elseif($arr[0] == 'forumdisply' && discuz_exists()) {
				$_SGET['fid'] = $arr[1];
				//避免跳转
				$fid = intval($_SGET['fid']);
				$forum = array();
				if($fid) {
					dbconnect(1);
					$query = $_SGLOBAL['db_bbs']->query("SELECT f.*, ff.* FROM ".tname('forums', 1)." f LEFT JOIN ".tname('forumfields', 1)." ff ON ff.fid=f.fid WHERE f.fid='$fid'");
					$forum = $_SGLOBAL['db_bbs']->fetch_array($query);
				}
				if(empty($forum)) continue;
				
				$jumptobbs = false;
				if($forum['status'] < 1) {
					$jumptobbs = true;
				} elseif(!empty($forum['password'])) {
					$jumptobbs = true;
				} elseif(!empty($forum['viewperm'])) {
					$viewpermarr = explode("\t", $forum['viewperm']);
					if(!in_array('7', $viewpermarr)) {
						$jumptobbs = true;
					}
				} elseif(!empty($forum['redirect'])) {
					$forumurl = $forum['redirect'];
					$jumptobbs = true;
				}
				if(empty($forum['allowshare'])) $jumptobbs = true;
				if($jumptobbs) continue;
			}
			@include(S_ROOT.'./'.$arr[0].'.php');
			obclean();
			if(!empty($_SGLOBAL['htmlfile'])) $batch_makehtml_makes[] = $_SGLOBAL['htmlfile'];
		}
		
		echo '<table style="width:98%;padding:0.2em;border: 1px solid #698CC3;font-size:12px;font-family: Trebuchet MS, Lucida Console, Lucida Sans, sans-serif;" align="center">';
		foreach ($batch_makehtml_makes as $value) {
			echo '<tr><td><a href="'.$value['url'].'" target="_blank">'.$value['path'].'</a></td><td width="35%">'.$alang['generation_html_end'].'</td></tr>';
		}
		echo '</table>';
		
		//下一页
		$nexturl = "$theurl&op=making&start=".($batch_makehtml_start+$batch_makehtml_pernum)."&pernum=$batch_makehtml_pernum";
		echo '<table style="width:98%;padding:0.2em;border: 1px solid #698CC3;font-size:14px;font-weight:bold;font-family: Trebuchet MS, Lucida Console, Lucida Sans, sans-serif;" align="center"><tr><td><a href='.$nexturl.'>'.$alang['making_page_info_0'].$batch_makehtml_count.$alang['making_page_info_1'].$batch_makehtml_start.' / '.($batch_makehtml_start+$batch_makehtml_pernum).$alang['making_page_info_2'].'</a></td><td width="35%"><a href="'.$theurl.'&op=make">'.$alang['making_page_info_3'].'</a></td></tr></table>';
		jumpurl($nexturl, 1);
		exit();

	} else {
		echo '<table class="maintable" align="center"><tr><td>'.$alang['generation_html_operation_completed'].$batch_makehtml_count.'</td></tr></table><br>';
	}
}

function sethtmlupdatemode($newmode) {

	$cachefile = S_ROOT.'./data/system/html.cache.php';
	@include_once($cachefile);
	$text = '';
	if(!empty($htmltime)) $text .= '$htmltime=\''.$htmltime.'\';'."\n";
	$text .= '$htmlupdatemode=\''.$newmode.'\';'."\n";
	
	writefile($cachefile, $text, 'php', 'w', 0);

}

?>