<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_items.php 13470 2009-11-04 07:19:53Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageitems')) {
	showmessage('no_authority_management_operation');
}

$urlplusarr = $listarr = $itemidarr = array();
$perpage = 20;
$page = intval(postget('page'));
($page<1) ? $page=1 : '';
$start = ($page-1) * $perpage;
$wheresqlstr = '';
$newurl = $theurl;
$search = trim(postget('search'));
$search2 = trim(postget('search2'));

if(submitcheck('searchsubmit') || !empty($search)) {

	$username = trim(postget('username'));
	$subject = trim(postget('subject'));
	$starttime = trim(postget('starttime'));
	$endtime = trim(postget('endtime'));
	$_SGET['uid'] = trim(postget('uid'));
	$_SGET['type'] = trim(postget('type'));
	$_SGET['folder'] = trim(postget('folder'));
	$_SGET['detail'] = trim(postget('detail'));
	$urlplusarr[] = 'search=1';
	
	$where1arr = array();
	if(!empty($_SGET['uid'])) {
		$urlplusarr[] = 'uid='.$_SGET['uid'];
		$uarr = explode(',', $_SGET['uid']);
		$newuarr = array();
		foreach ($uarr as $value) {
			$value = intval(trim($value));
			if(!empty($value)) $newuarr[] = $value;
		}
		if(!empty($newuarr)) $where1arr[] = "(uid IN ('".implode("','", $newuarr)."'))";
	}
	if(!empty($username)) {
		$urlplusarr[] = 'username='.$username;
		$uarr = explode(',', $username);
		$newuarr = array();
		foreach ($uarr as $value) {
			$value = trim($value);
			if(!empty($value)) $newuarr[] = $value;
		}
		if(!empty($newuarr)) $where1arr[] = "(username IN ('".implode("','", $newuarr)."'))";
	}
	if(!empty($subject)) {
		$urlplusarr[] = 'subject='.$subject;
		$sarr = explode(',', $subject);
		$newsarr = array();
		foreach ($sarr as $value) {
			$value = stripsearchkey($value);
			if(!empty($value)) $newsarr[] = "subject LIKE '%".$value."%'";
		}
		if(!empty($newsarr)) $where1arr[] = "(".implode(" OR ", $newsarr).")";
	}
	if(!empty($starttime)) {
		$urlplusarr[] = 'starttime='.$starttime;
		$starttime = strtotime($starttime);
		$where1arr[] = "(dateline >= '$starttime')";
	}
	if(!empty($endtime)) {
		$urlplusarr[] = 'endtime='.$endtime;
		$endtime = strtotime($endtime);
		$where1arr[] = "(dateline <= '$endtime')";
	}
	if(!empty($_SGET['type'])) {
		$urlplusarr[] = 'type='.$_SGET['type'];
		$where1arr[] = "(type = '$_SGET[type]')";
	}
	if($_SGET['folder']=='1') {
		if(!empty($_SGET['detail'])) {
				$urlplusarr[] = 'detail='.$_SGET['detail'];
		}
		$newurl .= '&'.implode('&', $urlplusarr);
		$wheresqlstr = implode(' AND ', $where1arr);
		if(empty($where1arr)) {
			showmessage('chosen_search_terms', $theurl);
		}
		$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spaceitems').' WHERE '.$wheresqlstr);
		$count = $_SGLOBAL['db']->result($query, 0);

		$multipage = '';
		if($count) {
			$plussql = '';
			if(!empty($_SGET['detail'])) {
				$plussql = " LIMIT $start,$perpage";
			}
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE $wheresqlstr ORDER BY itemid DESC $plussql");
			if(!empty($_SGET['detail'])) {
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$listarr[] = $value;
				}
				$multipage = multi($count, $perpage, $page, $newurl);
			} else {
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$itemidarr[] = $value['itemid'];
				}
			}
		}
		$fold = '0';
	}else{
		$urlplusarr[] = 'folder='.$_SGET['folder'];
		$where1arr[] = "(folder = '$_SGET[folder]')";

		if(!empty($_SGET['detail'])) {
				$urlplusarr[] = 'detail='.$_SGET['detail'];
		}
		$newurl .= '&'.implode('&', $urlplusarr);
		$wheresqlstr = implode(' AND ', $where1arr);
		if(empty($where1arr)) {
			showmessage('chosen_search_terms', $theurl);
		}
		$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('postitems').' WHERE '.$wheresqlstr);
		$count = $_SGLOBAL['db']->result($query, 0);

		$multipage = '';
		if($count) {
			$plussql = '';
			if(!empty($_SGET['detail'])) {
				$plussql = " LIMIT $start,$perpage";
			}
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('postitems')." WHERE $wheresqlstr ORDER BY itemid DESC $plussql");
			if(!empty($_SGET['detail'])) {
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$listarr[] = $value;
				}
				$multipage = multi($count, $perpage, $page, $newurl);
			} else {
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$itemidarr[] = $value['itemid'];
				}
			}
		}
		$fold = '1';
	}
	if(empty($listarr) && empty($itemidarr)) showmessage('not_find_qualified_information', $theurl);

} elseif(submitcheck('search2submit') || !empty($search2)) {
	
	$_SGET['message'] = trim(postget('message'));
	$_SGET['postip'] = trim(postget('postip'));
	$_SGET['type'] = trim(postget('type'));
	$_SGET['detail'] = trim(postget('detail'));
	$urlplusarr[] = 'search2=1';
	$urlplusarr[] = 'type='.$_SGET['type'];
	$where1arr = array();
	if(!empty($_SGET['message'])) {
		$urlplusarr[] = 'message='.$_SGET['message'];
		$sarr = explode(',', $_SGET['message']);
		$newsarr = array();
		foreach ($sarr as $value) {
			$value = stripsearchkey($value);
			if(!empty($value)) $newsarr[] = "ii.message LIKE '%".$value."%'";
		}
		if(!empty($newsarr)) $where1arr[] = "(".implode(" OR ", $newsarr).")";
	}
	if(!empty($_SGET['postip'])) {
		$urlplusarr[] = 'postip='.$_SGET['postip'];
		$_SGET['postip'] = str_replace('*', '888', $_SGET['postip']);
		$sarr = explode('.', $_SGET['postip']);
		$newips = array();
		foreach ($sarr as $value) {
			$value = intval($value);
			if($value >= 0 && $value <= 255) {
				$newips[] = $value;
			} else {
				$newips[] = '%';
			}
		}
		$where1arr[] = "(ii.postip LIKE '".implode('.', $newips)."')";
	}
	if(!empty($_SGET['detail'])) {
			$urlplusarr[] = 'detail='.$_SGET['detail'];
	}
	if(empty($where1arr)) {
		showmessage('chosen_search_terms', $theurl);
	}
	$newurl .= '&'.implode('&', $urlplusarr);
	
	$wheresqlstr = implode(' AND ', $where1arr);
	if(empty($_SGET['folder'])){
		$query = $_SGLOBAL['db']->query("SELECT count(*) FROM ".tname('spacenews')." ii LEFT JOIN ".tname('spaceitems')." i ON i.itemid=ii.itemid WHERE $wheresqlstr ORDER BY ii.itemid DESC");
	}else{
		$query = $_SGLOBAL['db']->query("SELECT count(*) FROM ".tname('postmessages')." ii LEFT JOIN ".tname('postitems')." i ON i.itemid=ii.itemid WHERE $wheresqlstr ORDER BY ii.itemid DESC");
	}
	$count = $_SGLOBAL['db']->result($query, 0);

	$multipage = '';
	if($count) {
		$plussql = '';
		if(!empty($_SGET['detail'])) {
			$plussql = " LIMIT $start,$perpage";
		}
		if(empty($_SGET['folder'])){
			$query = $_SGLOBAL['db']->query("SELECT i.type, i.uid, i.username, i.subject, i.dateline, ii.postip, ii.itemid FROM ".tname('spacenews')." ii LEFT JOIN ".tname('spaceitems')." i ON i.itemid=ii.itemid WHERE $wheresqlstr ORDER BY ii.itemid DESC $plussql");
		}else{
			$query = $_SGLOBAL['db']->query("SELECT i.type, i.uid, i.username, i.subject, i.dateline, ii.postip, ii.itemid FROM ".tname('postmessages')." ii LEFT JOIN ".tname('postitems')." i ON i.itemid=ii.itemid WHERE $wheresqlstr ORDER BY ii.itemid DESC $plussql");
		}
		if(!empty($_SGET['detail'])) {
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$listarr[] = $value;
				$itemidarr[] = $value['itemid'];
			}
			$multipage = multi($count, $perpage, $page, $newurl);
		} else {
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$itemidarr[] = $value['itemid'];
			}
		}
		if(empty($_SGET['folder'])){
			$fold = '0';
		}else{
			$fold = '1';
		}
	}
	if(empty($listarr) && empty($itemidarr)) showmessage('not_find_qualified_information', $theurl);
	
} elseif(submitcheck('dosubmit')) {
	
	if(empty($_POST['itemids'])) {
		if(empty($_POST['item'])) showmessage('you_have_no_choice_information');
		$itemids = implode("','", $_POST['item']);
	} else {
		$itemids = $_POST['itemids'];
	}
	$itemids = str_replace('\\\'', '\'', $itemids);
	$idarr = explode(',', str_replace('\'', '', $itemids));
	
	// 评论删除的积分
	$uids = getuids($idarr, 'spacecomments', 'authorid');
	updatecredit('delcomment', $uids);
	
	if(empty($_POST['theop'])) showmessage('please_choose_type_operation');
	if(empty($itemids)) showmessage('you_have_no_choice_information');
	
	if($_POST['theop'] == 'delete') {
		if(empty($_POST['fold'])){
			deleteitems('itemid', "'$itemids'", $_POST['undelete'], 0);
		}else{
			deleteitems('itemid', "'$itemids'", $_POST['undelete'], 1);
		}
	} elseif($_POST['theop'] == 'deleteattach') {
		
		$filearr = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('attachments')." WHERE itemid IN ('$itemids')");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if(!empty($value['filepath'])) $filearr[] = A_DIR.'/'.$value['filepath'];
			if(!empty($value['thumbpath'])) $filearr[] = A_DIR.'/'.$value['thumbpath'];
		}
		//删除附件
		if(!empty($filearr)) {
			foreach ($filearr as $value) {
				if(!@unlink($value)) errorlog('attachment', 'Unlink '.$value.' Error.');
			}
		}
		if(empty($_POST['theop'])){
			$_SGLOBAL['db']->query("UPDATE ".tname('spaceitems')." SET haveattach=0 WHERE itemid IN ('$itemids')");
		}else{
			$_SGLOBAL['db']->query("UPDATE ".tname('postitems')." SET haveattach=0 WHERE itemid IN ('$itemids')");
		}
		$_SGLOBAL['db']->query("DELETE FROM ".tname('attachments')." WHERE itemid IN ('$itemids')");
		
	} elseif($_POST['theop'] == 'deletecomment') {
		
		$_SGLOBAL['db']->query("DELETE FROM ".tname('spacecomments')." WHERE itemid IN ('$itemids') AND url=''");
		if(empty($_POST['fold'])){
			$_SGLOBAL['db']->query("UPDATE ".tname('spaceitems')." SET replynum=0 WHERE itemid IN ('$itemids')");
		}else{
			$_SGLOBAL['db']->query("UPDATE ".tname('postitems')." SET replynum=0 WHERE itemid IN ('$itemids')");
		}
	} elseif($_POST['theop'] == 'grade') {
		if(empty($_POST['fold'])){
			$_SGLOBAL['db']->query("UPDATE ".tname('spaceitems')." SET grade='$_POST[grade]' WHERE itemid IN ('$itemids')");
		}else{
			$_SGLOBAL['db']->query("UPDATE ".tname('postitems')." SET grade='$_POST[grade]' WHERE itemid IN ('$itemids')");
		}
	} elseif($_POST['theop'] == 'move') {
		if(empty($_POST['fold'])){
			$_SGLOBAL['db']->query("UPDATE ".tname('spaceitems')." SET catid='$_POST[catid]' WHERE itemid IN ('$itemids')");
		}else{
			$_SGLOBAL['db']->query("UPDATE ".tname('postitems')." SET catid='$_POST[catid]' WHERE itemid IN ('$itemids')");
		}
	} elseif($_POST['theop'] == 'changefolder') {
		if(empty($_POST['fold'])){
			moveitemfolder("'$itemids'", 0, $_POST['folder']);
		}else{
			moveitemfolder("'$itemids'", $_POST['folder'], 0);
		}
	}
	showmessage('successful_management_of_the_theme', $theurl);
}

print<<<END
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>$alang[batch_management_theme]</h1></td>
		<td class="actions">
		</td>
	</tr>
</table>
END;

if(empty($listarr) && empty($itemidarr)) {
	
	$starttime = sgmdate($_SGLOBAL['timestamp']-604800, 'Y-m-d');
	$endtime = sgmdate($_SGLOBAL['timestamp'], 'Y-m-d');
	
	$formhash = formhash();
	print<<<END
	<form method="post" name="thevalueform" action="$theurl">
	<input type="hidden" name="formhash" value="$formhash">
	<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
	<tr>
	<th>$alang[author_uid]</th>
	<td><input name="uid" type="text" size="50" value="" /></td>
	</tr>
	<tr>
	<th>$alang[author_info]</th>
	<td><input name="username" type="text" size="50" value="" /></td>
	</tr>
	<tr>
	<th>$alang[heading_keyword]</th>
	<td><input name="subject" type="text" size="50" value="" /></td>
	</tr>
	<tr>
	<th>$alang[made_time_frame]</th>
	<td><input name="starttime" type="text" size="20" value="$starttime" /> ~ <input name="endtime" type="text" size="20" value="$endtime" /></td>
	</tr>
	<tr>
	<th>$alang[information_state]</th>
	<td><select name="folder">
		<option value="1">$alang[normal_release]</option>
		<option value="2">$alang[private_draft]</option>
		<option value="3">$alang[delete_recovery]</option>
		</select></td>
	</tr>
	<tr>
	<th>$alang[shows_a_detailed_list_of_themes]</th>
	<td><input class="checkbox" type="checkbox" name="detail" value="1">$alang[detailed_listings_show]</td>
	</tr>
	</table>
	<div class="buttons">
	<input type="submit" name="searchsubmit" value="$alang[find_theme]" class="submit">
	<input type="reset" name="searchreset" value="$alang[common_reset]">
	</div>
	</form>
	
	<br>
	<form method="post" name="thevalueform2" action="$theurl">
	<input type="hidden" name="formhash" value="$formhash">
	<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
	<tr>
	<th>$alang[keyword_content]</th>
	<td><input name="message" type="text" size="50" value="" /></td>
	</tr>
	<tr>
	<th>$alang[author_ip_info]</th>
	<td><input name="postip" type="text" size="30" value="" /></td>
	</tr>
	<tr>
	<th>$alang[information_state]</th>
	<td><select name="folder">
		<option value="1">$alang[normal_release]</option>
		<option value="0">$alang[private_draft]</option>
		</select></td>
	</tr>
	<tr>
	<th>$alang[table_shows_details]</th>
	<td><input class="checkbox" type="checkbox" name="detail" value="1">$alang[detailed_listings_show]</td>
	</tr>
	</table>
	<div class="buttons">
	<input type="submit" name="search2submit" value="$alang[find_content]" class="submit">
	<input type="reset" name="search2reset" value="$alang[common_reset]">
	</div>

	</form>
END;

} else {
	
	if (!empty($itemidarr)) {
		$count = count($itemidarr);
		$itemids = implode("','", $itemidarr);
	} else {
		$count = count($listarr);
		$itemids = '';
	}
	
	//审核级别
	$gradearr = array(
		'0' => $alang['check_grade_0'],
		'1' => $alang['check_grade_1'],
		'2' => $alang['check_grade_2'],
		'3' => $alang['check_grade_3'],
		'4' => $alang['check_grade_4'],
		'5' => $alang['check_grade_5']
	);
	if(!empty($_SCONFIG['checkgrade'])) {
		$newgradearr = explode("\t", $_SCONFIG['checkgrade']);
		for($i=0; $i<5; $i++) {
			if(!empty($newgradearr[$i])) $gradearr[$i+1] = $newgradearr[$i];
		}
	}
	
	//分类
	$catstr = '';
	$ptype = postget('type');
	if(!empty($ptype)) {
		$catstr .= '<tr>
		<th><input class="radio" type="radio" name="theop" value="move">'.$alang['mass_transfer_classification'].'</th>
		<td>
		<select name="catid">';
		$clistarr = getcategory($ptype);
		foreach ($clistarr as $key => $value) {
			$catstr .= '<option value="'.$value['catid'].'">'.$value['pre'].$value['name'].'</option>';
		}
		$catstr .= '</select>
		</td>
		</tr>';
	}

	$formhash = formhash();
	print<<<END
	<form method="post" name="thevalueform" action="$theurl" onsubmit="return confirm('$alang[information_operations_to_determine]');">
	<input type="hidden" name="formhash" value="$formhash">
	<input type="hidden" name="fold" value="$fold">
	<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
	<tr>
	<th>$alang[information_with_a_few_conditions]</th>
	<td><strong>$count</strong></td>
	</tr>
	<tr>
	<th><input class="radio" type="radio" name="theop" value="delete">$alang[batch_delete_all_information]</th>
	<td><input type="radio" name="undelete" value="1" checked>$alang[deleted_to_the_collection_points] <input type="radio" name="undelete" value="0">$alang[completely_erased]</td>
	</tr>
	<tr>
	<th><input class="radio" type="radio" name="theop" value="deleteattach">$alang[delete_the_information_contained_in_annex]</th>
	<td>$alang[delete_specified_in_the_annex]</td>
	</tr>
	<tr>
	<th><input class="radio" type="radio" name="theop" value="deletecomment">$alang[information_deleted_comments]</th>
	<td>$alang[information_delete_all_the_comments]</td>
	</tr>
	<tr>
	<th><input class="radio" type="radio" name="theop" value="changefolder">$alang[changing_the_state]</th>
END;
	$out="<td><select name=\"folder\">";
	if(empty($fold)){
		$out .= "'<option value=\"1\">$alang[private_draft]</option>
		<option value=\"2\">$alang[delete_recovery]</option>'";
	}else{
		$out .=  "'<option value=\"0\">$alang[normal_release]</option>'";
	}
		$out .=  '</select></td>';
	echo $out;
	print<<<END
	</tr>
	<tr>
	<tr>
	<th><input class="radio" type="radio" name="theop" value="grade">$alang[batch_audited_information]</th>
	<td>
	<select name="grade">
	<option value="0">$gradearr[0]</option>
	<option value="1">$gradearr[1]</option>
	<option value="2">$gradearr[2]</option>
	<option value="3">$gradearr[3]</option>
	<option value="4">$gradearr[4]</option>
	<option value="5">$gradearr[5]</option>
	</select>	
	</td>
	</tr>
	$catstr
	</table>
END;

	if(!empty($listarr)) {
		echo '<table cellspacing="0" cellpadding="0" width="100%"  class="listtable">';
		echo '<tr>
			<th width="5%">'.$alang['block_select'].'</th>
			<th>'.$alang['spaceblog_subject'].'</th>
			<th width="15%">'.$alang['robot_robot_author'].'</th>
			<th width="15%">'.$alang['space_order_dateline'].'</th>
		</tr>';
		foreach ($listarr as $value) {
			$url = geturl('action/viewnews/itemid/'.$value['itemid']);
			$postip = '';
			if(!empty($value['postip'])) $postip = '<br>'.$value['postip'];
			echo '<tr>
			<td><input class="checkbox" type="checkbox" name="item[]" value="'.$value['itemid'].'" checked></td>
			<td><a href="'.$url.'" target="_blank">'.$value['subject'].'</a></td>
			<td><a href="'.geturl('uid/'.$value['uid']).'" target="_blank">'.$value['username'].'</a>'.$postip.'</td>
			<td>'.sgmdate($value['dateline'], 'Y-n-d\<\b\r\>H:i:s').'</td>
			</tr>';
		}
		echo '</table>';
		if(!empty($multipage)) echo $multipage;
	}

	print<<<END
	<input type="hidden" name="itemids" value="$itemids">
	<div class="buttons">
	<input type="submit" name="dosubmit" value="$alang[submitted_operation]" class="submit">
	<input type="reset" name="doreset" value="$alang[common_reset]">
	</div>
	</form>
END;
}
?>