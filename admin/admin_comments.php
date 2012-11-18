<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_comments.php 13470 2009-11-04 07:19:53Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managecomments')) {
	showmessage('no_authority_management_operation');
}

$urlplusarr = $listarr = $itemidarr = array();

$perpage = 20;
$page = intval(postget('page'));
($page<1)?$page=1:'';
$start = ($page-1)*$perpage;
$wheresqlstr = '';
$newurl = $theurl;
$search = trim(postget('search'));

if(submitcheck('searchsubmit') || !empty($search)) {

	$_SGET['authorid'] = trim(postget('authorid'));
	$_SGET['author'] = trim(postget('author'));
	$_SGET['message'] = trim(postget('message'));
	$_SGET['url'] = trim(postget('url'));
	$_SGET['starttime'] = trim(postget('starttime'));
	$_SGET['endtime'] = trim(postget('endtime'));
	$_SGET['type'] = trim(postget('type'));
	$_SGET['ip'] = trim(postget('ip'));
	$_SGET['detail'] = trim(postget('detail'));
	$urlplusarr[] = 'search=1';
	$where1arr = array();
	if(!empty($_SGET['authorid'])) {
		$urlplusarr[] = 'authorid='.$_SGET['authorid'];
		$uarr = explode(',', $_SGET['authorid']);
		$newuarr = array();
		foreach ($uarr as $value) {
			$value = intval(trim($value));
			if(!empty($value)) $newuarr[] = $value;
		}
		if(!empty($newuarr)) $where1arr[] = "(authorid IN ('".implode("','", $newuarr)."'))";
	}
	if(!empty($_SGET['author'])) {
		$urlplusarr[] = 'author='.$_SGET['author'];
		$uarr = explode(',', $_SGET['author']);
		$newuarr = array();
		foreach ($uarr as $value) {
			$value = trim($value);
			if(!empty($value)) $newuarr[] = $value;
		}
		if(!empty($newuarr)) $where1arr[] = "(author IN ('".implode("','", $newuarr)."'))";
	}
	if(!empty($_SGET['message'])) {
		$urlplusarr[] = 'message='.$_SGET['message'];
		$sarr = explode(',', $_SGET['message']);
		$newsarr = array();
		foreach ($sarr as $value) {
			$value = stripsearchkey($value);
			if(!empty($value)) $newsarr[] = "message LIKE '%".$value."%'";
		}
		if(!empty($newsarr)) $where1arr[] = "(".implode(" OR ", $newsarr).")";
	}
	if(!empty($_SGET['url'])) {
		$urlplusarr[] = 'url='.$_SGET['url'];
		$sarr = explode(',', $_SGET['url']);
		$newsarr = array();
		foreach ($sarr as $value) {
			$value = stripsearchkey($value);
			if(!empty($value)) $newsarr[] = "url LIKE '%".$value."%'";
		}
		if(!empty($newsarr)) $where1arr[] = "(".implode(" OR ", $newsarr).")";
	}
	if(!empty($_SGET['starttime'])) {
		$urlplusarr[] = 'starttime='.$_SGET['starttime'];
		$starttime = strtotime($_SGET['starttime']);
		$where1arr[] = "(dateline >= '$starttime')";
	}
	if(!empty($_SGET['endtime'])) {
		$urlplusarr[] = 'endtime='.$_SGET['endtime'];
		$endtime = strtotime($_SGET['endtime']);
		$where1arr[] = "(dateline <= '$endtime')";
	}
	if(!empty($_SGET['type'])) {
		$urlplusarr[] = 'type='.$_SGET['type'];
		$where1arr[] = "(type = '$_SGET[type]')";
	}
	if(!empty($_SGET['ip'])) {
		$urlplusarr[] = 'ip='.$_SGET['ip'];
		$_SGET['ip'] = str_replace('*', '888', $_SGET['ip']);
		$sarr = explode('.', $_SGET['ip']);
		$newips = array();
		foreach ($sarr as $value) {
			$value = intval($value);
			if($value >= 0 && $value <= 255) {
				$newips[] = $value;
			} else {
				$newips[] = '%';
			}
		}
		$where1arr[] = "(ip LIKE '".implode('.', $newips)."')";
	}
	if(!empty($_SGET['detail'])) {
			$urlplusarr[] = 'detail='.$_SGET['detail'];
	}
	$newurl .= '&'.implode('&', $urlplusarr);
	$wheresqlstr = implode(' AND ', $where1arr);
	if(empty($where1arr)) {
		showmessage('identify_conditions_choice_comments', $theurl);
	}
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spacecomments').' WHERE '.$wheresqlstr);
	$count = $_SGLOBAL['db']->result($query, 0);
	
	$multipage = '';
	if($count) {
		$plussql = '';
		if(!empty($_SGET['detail'])) {
			$plussql = " LIMIT $start,$perpage";
		}
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spacecomments')." WHERE $wheresqlstr ORDER BY cid DESC $plussql");
		if(!empty($_SGET['detail'])) {
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$listarr[] = $value;
			}
			$multipage = multi($count, $perpage, $page, $newurl);
		} else {
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$itemidarr[] = $value['cid'];
			}
		}
	}
	if(empty($listarr) && empty($itemidarr)) showmessage('not_found_qualified_to_comment', $theurl);

} elseif (submitcheck('dosubmit')) {
	
	if(empty($_POST['itemids'])) {
		if(empty($_POST['item'])) showmessage('you_have_no_choice_information');
		$itemids = implode('\',\'', $_POST['item']);
	} else {
		$itemids = $_POST['itemids'];
	}
	$itemids = str_replace('\\\'', '\'', $itemids);
	if(empty($itemids)) showmessage('you_have_no_choice_information');
	
	$itemarr = array();
	$itemarr2 = array();
	$uids = array();	//积分需要的uid
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spacecomments')." WHERE cid IN ('$itemids')");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($itemarr[$value['itemid']])) $itemarr[$value['itemid']] = 0;
		$itemarr[$value['itemid']]++;
		$uids[] = $value['authorid'];
		$uids[] =$value['uid'];
	}
	
	updatecredit('delcomment', $uids);
	
	$_SGLOBAL['db']->query("DELETE FROM ".tname('spacecomments')." WHERE cid IN ('$itemids')");
	
	foreach ($itemarr as $itemid => $num) {
		$_SGLOBAL['db']->query("UPDATE ".tname('spaceitems')." SET replynum=replynum-$num WHERE itemid='$itemid'");
	}

	showmessage('successful_management_of_the_theme', $theurl);
}

print<<<END
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>$alang[batch_management_commentary]</h1></td>
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
	<td><input name="authorid" type="text" size="50" value="" /></td>
	</tr>
	<tr>
	<th>$alang[author_info]</th>
	<td><input name="author" type="text" size="50" value="" /></td>
	</tr>
	<tr>
	<th>$alang[keyword_content]</th>
	<td><input name="message" type="text" size="50" value="" /></td>
	</tr>
	<tr>
	<th>$alang[made_time_frame]</th>
	<td><input name="starttime" type="text" size="20" value="$starttime" /> ~ <input name="endtime" type="text" size="20" value="$endtime" /></td>
	</tr>
	<tr>
	<th>$alang[author_ip_info]</th>
	<td><input name="ip" type="text" size="30" value="" /></td>
	</tr>
	<tr>
	<th>$alang[detailed_listings_show]</th>
	<td><input class="checkbox" type="checkbox" name="detail" value="1">$alang[shows_a_detailed_list_of_comments]</td>
	</tr>
	</table>
	<div class="buttons">
	<input type="submit" name="searchsubmit" value="$alang[find_comments]" class="submit">
	<input type="reset" name="searchreset" value="$alang[common_reset]">
	</div>
	</form>
END;

} else {
	
	if (!empty($itemidarr)) {
		$count = count($itemidarr);
		$itemids = implode('\',\'', $itemidarr);
	} else {
		$count = count($listarr);
		$itemids = '';
	}

	$formhash = formhash();
	print<<<END
	<form method="post" name="thevalueform" action="$theurl" onsubmit="return confirm('$alang[information_operations_to_determine]');">
	<input type="hidden" name="formhash" value="$formhash">
	<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
	<tr>
	<th>$alang[commenting_on_the_number_of_eligible]</th>
	<td><strong>$count</strong></td>
	</tr>
	</table>
END;

	if(!empty($listarr)) {
		echo '<table cellspacing="0" cellpadding="0" width="100%"  class="listtable">';
		echo '<tr>
			<th width="5%">'.$alang['block_select'].'</th>
			<th>'.$alang['comment_on_the_content'].'</th>
			<th width="15%">'.$alang['robot_robot_author'].'</th>
			<th width="15%">'.$alang['space_order_dateline'].'</th>
		</tr>';
		foreach ($listarr as $value) {
			$url = geturl('action/viewnews/itemid/'.$value['itemid']);
			$postip = $tburl = '';
			if(!empty($value['ip'])) $postip = '<br>'.$value['ip'];
			echo '<tr>
			<td><input class="checkbox" type="checkbox" name="item[]" value="'.$value['cid'].'" checked></td>
			<td><a href="'.$url.'" target="_blank">'.$value['message'].$tburl.'</a></td>
			<td><a href="'.geturl('uid/'.$value['authorid']).'" target="_blank">'.$value['author'].'</a>'.$postip.'</td>
			<td>'.sgmdate($value['dateline'], 'Y-n-d\<\b\r\>H:i:s').'</td>
			</tr>';
		}
		echo '</table>';
		if(!empty($multipage)) echo $multipage;
	}

	print<<<END
	<input type="hidden" name="itemids" value="$itemids">
	<div class="buttons">
	<input type="submit" name="dosubmit" value="$alang[batch_delete_comments]" class="submit">
	<input type="reset" name="doreset" value="$alang[common_reset]">
	</div>
	</form>
END;
}

?>