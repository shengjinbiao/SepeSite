<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_attachments.php 11552 2009-03-10 05:21:35Z zhaolei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//х╗оч
if(!checkperm('manageattachments')) {
	showmessage('no_authority_management_operation');
}

$urlplusarr = $listarr = $itemidarr = array();
$perpage = 20;

//CHECK GET VAR
$page = intval(postget('page'));
($page < 1) ? $page= 1 : '';
$start = ($page - 1) * $perpage;
$wheresqlstr = '';
$newurl = $theurl;
$search = trim(postget('search'));

if(submitcheck('searchsubmit') || !empty($search)) {
	
	$_SGET['uid'] = trim(postget('uid'));
	$_SGET['filename'] = trim(postget('filename'));
	$_SGET['subject'] = trim(postget('subject'));
	$_SGET['starttime'] = trim(postget('starttime'));
	$_SGET['endtime'] = trim(postget('endtime'));
	$_SGET['unavailable'] = trim(postget('unavailable'));
	$_SGET['isimage'] = trim(postget('isimage'));
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
	if(!empty($_SGET['filename'])) {
		$urlplusarr[] = 'filename='.$_SGET['filename'];
		$sarr = explode(',', $_SGET['filename']);
		$newsarr = array();
		foreach ($sarr as $value) {
			$value = stripsearchkey($value);
			if(!empty($value)) $newsarr[] = "filename LIKE '%".$value."%'";
		}
		if(!empty($newsarr)) $where1arr[] = "(".implode(" OR ", $newsarr).")";
	}
	if(!empty($_SGET['subject'])) {
		$urlplusarr[] = 'subject='.$_SGET['subject'];
		$sarr = explode(',', $_SGET['subject']);
		$newsarr = array();
		foreach ($sarr as $value) {
			$value = stripsearchkey($value);
			if(!empty($value)) $newsarr[] = "subject LIKE '%".$value."%'";
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
	if(!empty($_SGET['unavailable'])) {
		$urlplusarr[] = 'unavailable=0';
		$where1arr[] = "(isavailable='0')";
	}
	if(!empty($_SGET['isimage'])) {
		$urlplusarr[] = 'isimage=1';
		$where1arr[] = "(isimage='1')";
	}
	if(!empty($_SGET['detail'])) {
			$urlplusarr[] = 'detail='.$_SGET['detail'];
	}
	$newurl .= '&'.implode('&', $urlplusarr);
	$wheresqlstr = implode(' AND ', $where1arr);
	if(empty($where1arr)) {
		showmessage('please_choose_search_terms', $theurl);
	}
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('attachments').' WHERE '.$wheresqlstr);
	$count = $_SGLOBAL['db']->result($query, 0);
	
	$multipage = '';
	if($count) {
		$plussql = '';
		if(!empty($_SGET['detail'])) {
			$plussql = " LIMIT $start,$perpage";
		}
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('attachments')." WHERE $wheresqlstr ORDER BY aid DESC $plussql");
		if(!empty($_SGET['detail'])) {
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$listarr[] = $value;
			}
			$multipage = multi($count, $perpage, $page, $newurl);
		} else {
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$itemidarr[] = $value['aid'];
			}
		}
	}
	if(empty($listarr) && empty($itemidarr)) showmessage('not_found_with_the_annex', $theurl);

} elseif(submitcheck('dosubmit')) {
	
	if(empty($_POST['itemids'])) {
		if(empty($_POST['item'])) showmessage('you_have_no_choice_operation_annex');
		$itemids = implode('\',\'', $_POST['item']);
	} else {
		$itemids = $_POST['itemids'];
	}
	$itemids = str_replace('\\\'', '\'', $itemids);
	if(empty($itemids)) showmessage('you_have_no_choice_operation_annex');
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('attachments')." WHERE aid IN ('$itemids')");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(!@unlink(A_DIR.'/'.$value['thumbpath'])) errorlog('Attachment', 'Unlink '.A_DIR.'/'.$value['thumbpath'].' Error.');
		if(!@unlink(A_DIR.'/'.$value['filepath'])) errorlog('Attachment', 'Unlink '.A_DIR.'/'.$value['filepath'].' Error.');
	}
	$_SGLOBAL['db']->query("DELETE FROM ".tname('attachments')." WHERE aid IN ('$itemids')");

	showmessage('annex_success_of_the_operation', $theurl);

}

print<<<END
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>$alang[batch_management_annex]</h1></td>
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
	<th>$alang[no_use_of_annex]$alang[notes_unused_annex]</th>
	<td><input class="checkbox" type="checkbox" name="unavailable" value="1">$alang[no_use_of_annex]</td>
	</tr>
	<tr>
	<th>$alang[photo_annex]</th>
	<td><input class="checkbox" type="checkbox" name="isimage" value="1">$alang[annex_photo]</td>
	</tr>
	<tr>
	<th>$alang[upload_who_channels]</th>
	<td><input name="uid" type="text" size="50" value="" /></td>
	</tr>
	<tr>
	<th>$alang[upload_time_frame]</th>
	<td><input name="starttime" type="text" size="20" value="$starttime" /> ~ <input name="endtime" type="text" size="20" value="$endtime" /></td>
	</tr>
	<tr>
	<th>$alang[keyword_document]</th>
	<td><input name="filename" type="text" size="50" value="" /></td>
	</tr>
	<tr>
	<th>$alang[annex_on_keywords]</th>
	<td><input name="subject" type="text" size="50" value="" /></td>
	</tr>
	<tr>
	<th>$alang[detailed_listings_show]</th>
	<td><input class="checkbox" type="checkbox" name="detail" value="1">$alang[annex_table_shows_details]</td>
	</tr>
	</table>
	<div class="buttons">
	<input type="submit" name="searchsubmit" value="$alang[annex_search]" class="submit">
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
	<th>$alang[annex_to_meet_several_conditions]</th>
	<td><strong>$count</strong></td>
	</tr>
	</table>
END;

if(!empty($listarr)) {
	echo '<table cellspacing="0" cellpadding="0" width="100%"  class="listtable">';
	echo '<tr>
		<th width="5%">'.$alang['block_select'].'</th>
		<th>'.$alang['annex_name'].'</th>
		<th width="15%">'.$alang['annex_size'].'</th>
		<th width="15%">'.$alang['space_order_dateline'].'</th>
	</tr>';
	foreach ($listarr as $value) {
		echo '<tr>
		<td><input class="checkbox" type="checkbox" name="item[]" value="'.$value['aid'].'" checked></td>
		<td><a href="'.A_URL.'/'.$value['filepath'].'" target="_blank">'.$value['filename'].'</a><br>'.$value['subject'].'</td>
		<td>'.$value['size'].'</td>
		<td>'.sgmdate($value['dateline'], 'Y-n-d\<\b\r\>H:i:s').'</td>
		</tr>';
	}
	echo '</table>';
	if(!empty($multipage)) echo $multipage;
}

	print<<<END
	<input type="hidden" name="itemids" value="$itemids">
	<div class="buttons">
	<input type="submit" name="dosubmit" value="$alang[batch_deletion]" class="submit">
	<input type="reset" name="doreset" value="$alang[common_reset]">
	</div>
	</form>
END;
}

?>