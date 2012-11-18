<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$op = empty($_GET['op']) ? 'list' : trim($_GET['op']);
$channel = $nameid = postget('nameid');

//权限
if($op == 'add' || $op == 'edit'){
	$newchannel = '';
	$postmenus = array();
	if(checkperm('allowpost')) $newchannel = $channel;
	foreach($channels['menus'] as $key => $value) {
		if(in_array($value['type'], array('type', 'model')) || $value['upnameid']=='news') {
			$channel = $key;
			if(checkperm('allowpost')) {
				if(empty($newchannel)) $newchannel = $channel;
				$postmenus[] = $key;
			}
		}
	}
	$channel = $nameid = empty($newchannel) ? $nameid : $newchannel;
	if(!checkperm('allowpost')) {
		showmessage('no_permission', S_URL.'/cp.php?ac=news');
	}
}

if(empty($channels['menus'][$nameid])){
	showmessage('visit_the_channel_does_not_exist');
} elseif($channels['menus'][$nameid]['type'] == 'model') {
	include_once(S_ROOT.'./function/model.func.php');
	$cacheinfo = getmodelinfoall('modelname', $nameid);
} else {
	showmessage('', S_URL.'/cp.php?ac=news&op=add&type='.$nameid, 0);
}

$do = empty($_GET['do']) ? 'me' : trim($_GET['do']);
$itemid = empty($_GET['itemid']) ? 0 : intval($_GET['itemid']);
$catid = empty($_GET['catid']) ? 0 : intval($_GET['catid']);
$page = empty($_GET['page']) && intval($_GET['page']) < 1 ? 1 : intval($_GET['page']);
$perpage = 20;
$start = ($page - 1) * $perpage;
$wheresql = $mpurlstr = '';
if(!empty($catid)) $wheresql .= " AND catid='$catid' ";

if(submitcheck('postsubmit')) {

	if(!empty($_POST['itemid']) && empty($_SGLOBAL['supe_uid'])) showmessage('no_permission');
	modelpost($cacheinfo, 0);

} elseif(submitcheck('delitemsubmit')) {

	$itemarr = array();
	$tablename = $do == 'pass' ? $nameid.'items' : 'modelfolders';
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($tablename).' WHERE itemid IN('.simplode($_POST['item'], ',').') AND uid=\''.$_SGLOBAL['supe_uid'].'\'');
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$itemarr[] = $value['itemid'];
	}

	$_SGLOBAL['db']->query('DELETE FROM '.tname($tablename).' WHERE itemid IN('.simplode($itemarr, ',').')');
	$_SGLOBAL['db']->query('DELETE FROM '.tname($tablename).' WHERE itemid IN('.simplode($itemarr, ',').')');
	
	showmessage('do_success', 'cp.php?ac=models&op=list&do='.$do.'&nameid='.$nameid);

} 

if($itemid) {

	if($do == 'pass') {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($cacheinfo['models']['modelname'].'items').' LEFT JOIN '.tname($cacheinfo['models']['modelname'].'message')." USING (itemid) WHERE itemid='$itemid'");
		if(!$item = $_SGLOBAL['db']->fetch_array($query)) {
			showmessage('no_item', 'cp.php?ac=models&op=list&nameid='.$nameid);
		}
		
		$item['subject'] = shtmlspecialchars($item['subject']);
		$item['message'] = jsstrip($item['message']);	
	} else {

		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelfolders')." WHERE mid='".$cacheinfo['models']['mid']."' AND itemid='$itemid'");
		if(!$item = $_SGLOBAL['db']->fetch_array($query)) {
			showmessage('no_item', 'cp.php?ac=models&op=list&nameid='.$nameid);
		}
		$temparr = unserialize($item['message']);
		unset($item['message']);
		$item = array_merge($item, $temparr);
	}
	
	$item['dateline'] = sgmdate($item['dateline']);

}

$categorylistarr = getmodelcategory($nameid);
$categorylistarr[0] = array('pre'=>'', 'name'=>'------');
$resultmodelcolumns = array();

if($cacheinfo['models']['mid'] > 0) {
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelcolumns').' WHERE mid = \''.$cacheinfo['models']['mid'].'\' ORDER BY displayorder, id');
	while ($result = $_SGLOBAL['db']->fetch_array($query)) {
		$resultmodelcolumns[] = $result;
	}
}

if($op == 'add' || $op == 'edit'){
	
	if($itemid && ($item['uid'] != $_SGLOBAL['supe_uid'] || empty($_SGLOBAL['supe_uid']))) {
		showmessage('no_permission', 'cp.php?ac=models&op=list&nameid='.$nameid);
	}
	
	$htmlarr = array();
	foreach ($resultmodelcolumns as $value) {
		if(!checkperm('managemodelfolders') && empty($value['allowpost'])) continue;
		$temparr = $temparr2 = array();
		$other = '';
		if($value['formtype'] == 'select') {
			$temparr2 = array(''=>'');
		}

		$temparr = explode("\r\n", $value['fielddata']);
		foreach($temparr as $value2) {
			$temparr2[$value2] = $value2;
		}

		//整理提示信息
		if(!empty($value['isrequired'])) {
			$value['fieldcomment'] = '<span style="color: #F00">*</span>'.$value['fieldcomment'];
		}
		$htmlarr[$value['id']]['subject'] = $value['fieldcomment'];

		if(!empty($value['ishtml'])) {
			$htmlarr[$value['id']]['help'] = $alang['fieldcomment_help'];
		}
		if(!empty($value['isbbcode'])) {
			$htmlarr[$value['id']]['help'] = $alang['fieldcomment_discuz_code'];
		}
		if(preg_match("/^(TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT)$/i", $value['fieldtype']) && !preg_match("/^(select|radio|checkbox|file|img|flash)$/i", $value['formtype'])) {
			$htmlarr[$value['id']]['help'] = $alang['field_only_int'];
		}
		if(preg_match("/^(FLOAT|DOUBLE)$/i", $value['fieldtype']) && !preg_match("/^(select|radio|checkbox|file|img|flash)$/i", $value['formtype'])) {
			$htmlarr[$value['id']]['help'] = $alang['field_only_float'];
		}
		if(preg_match("/^(text|textarea)$/i", $value['formtype']) && !preg_match("/^(TEXT|MEDIUMTEXT|LONGTEXT|FLOAT|DOUBLE)$/i", $value['fieldtype'])) {
			$htmlarr[$value['id']]['help'] = $alang['field_length_1'].$value['fieldlength'].$alang['field_length_2'];
		}

		if($value['formtype'] == 'linkage') {
			$temparr2 = array();
			if(!empty($cacheinfo['linkage']['down'][$value['id']])) {
				$downfieldname = $cacheinfo['columnids'][$cacheinfo['linkage']['down'][$value['id']]];
				$other = ' onchange="fill(\''.$downfieldname.'\', \''.$value['fieldname'].'\', '.$downfieldname.'arr);"';
			}
			if($value['upid'] == '0') {
				$linkagestr .= 'fill(\''.$value['fieldname'].'\', \'\', '.$value['fieldname'].'arr, \''.$item[$value['fieldname']].'\');';
			} else {
				$linkagestr .= 'fill(\''.$value['fieldname'].'\', \''.$cacheinfo['columnids'][$value['upid']].'\', '.$value['fieldname'].'arr, \''.$item[$value['fieldname']].'\');';
			}
		}
		$htmlarr[$value['id']]['js'] = $linkagestr;
		$value['formtype'] = $value['formtype'] == 'text' ? 'input' : $value['formtype'];
		$value['formtype'] = $value['formtype'] == 'linkage' ? 'select' : $value['formtype'];
		if($value['formtype'] == 'checkbox') {
			$item[$value['fieldname']] = explode("\n", $item[$value['fieldname']]);
		}
		if($value['formtype'] == 'file') {
			$fileurl = S_URL.'/batch.modeldownload.php?hash='.rawurlencode(authcode($nameid.','.$item[$value['fieldname']], 'ENCODE'));
		} else {
			$fileurl = A_URL.'/'.$item[$value['fieldname']];
		}
		
		if(preg_match("/^(img|flash|file)$/i", $value['formtype'])) {
			$value['formtype'] = 'file';
		}
		
		if($value['formtype'] != 'timestamp') {
			$htmlarr[$value['id']]['input'] = label(array('type'=>$value['formtype'], 'alang'=>$value['fieldcomment'], 'name'=>$value['fieldname'], 'options'=>$temparr2, 'rows'=>10, 'width'=>'30%', 'size'=>'60', 'value'=>$item[$value['fieldname']],  'other'=>$other, 'fileurl'=>$fileurl), 0);
		} else {
			$item[$value['fieldname']] = sgmdate($item[$value['fieldname']]);

			$htmlarr[$value['id']]['input'] = <<<EOF
			<input type="text" name="$value[fieldname]" id="$value[fieldname]" readonly="readonly" value="{$item[$value['fieldname']]}" /><img src="$siteurl/admin/images/time.gif" onClick="getDatePicker('$value[fieldname]', event, 21)" />
EOF;
		}
		
	}

} elseif($op == 'view') {
	if(empty($_SGLOBAL['supe_uid'])) showmessage('no_permission');

	$item['subject'] = shtmlspecialchars($item['subject']);
	
	if(!empty($item['subjectimage'])) {
		$fileext = fileext($item['subjectimage']);
		$item['subjectimage'] = $item['subjectthumb'] = A_URL.'/'.$item['subjectimage'];
		if(preg_match("/^(jpg|jpeg|png)$/i", $fileext)) {
			$item['subjectthumb'] = substr($item['subjectimage'], 0, strrpos($item['subjectimage'], '.')).'.thumb.jpg';
		}
	}
	
	if(!empty($cacheinfo['columns'])) {
		$htmlarr = array();
		foreach($cacheinfo['columns'] as $temp) {
			$tmpvalue = trim($item[$temp['fieldname']]);
			if((empty($temp['isfile']) && strlen($tmpvalue) > 0) || (!empty($temp['isfile']) && $tmpvalue != 0)) {
				if($temp['formtype'] == 'checkbox') {
					$tmpvalue = explode("\n", $item[$temp['fieldname']]);
				} elseif($temp['formtype'] == 'textarea' && empty($temp['ishtml'])) {
					$tmpvalue = str_replace("\n", '<br />', $item[$temp['fieldname']]);
				}
		
				$temp['filepath'] = '';
				if(!empty($temp['isimage']) || !empty($temp['isflash'])) {
					$temp['filepath'] = A_URL.'/'.$tmpvalue;
				} elseif(!empty($temp['isfile'])) {
					$temp['filepath'] = rawurlencode(authcode($resultmodels['modelname'].','.$tmpvalue, 'ENCODE'));
				}
				$columnsinfoarr[] = array(
						'fieldname'	=>	$temp['fieldname'],
						'fieldcomment'	=>	$temp['fieldcomment'],
						'fieldtype'	=>	$temp['fieldtype'],
						'formtype'	=>	$temp['formtype'],
						'ishtml'	=>	$temp['ishtml'],
						'isbbcode'	=>	$temp['isbbcode'],
						'isfile'	=>	$temp['isfile'],
						'isimage'	=>	$temp['isimage'],
						'isflash'	=>	$temp['isflash'],
						'filepath'	=>	$temp['filepath'],
						'value'	=>	$tmpvalue
				);
			}
		}

		$value['fieldcomment'] .= '<p>';
		if(!empty($value['ishtml'])) {
			$value['fieldcomment'] .= $alang['fieldcomment_help'];
		}
		
		if(preg_match("/^(TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT|FLOAT|DOUBLE)$/i", $value['fieldtype']) && !preg_match("/^(select|radio|checkbox|file|img|flash)$/i", $value['formtype'])) {
			$value['fieldcomment'] .= $alang['field_only_int'];
		}
		if(preg_match("/^(text|textarea)$/i", $value['formtype']) && !preg_match("/^(TEXT|MEDIUMTEXT|LONGTEXT)$/i", $value['fieldtype'])) {
			$value['fieldcomment'] .= $alang['field_length_1'].$value['fieldlength'].$alang['field_length_2'];
		}
		$value['fieldcomment'] .= '</p>';
		
		if(!empty($columnsinfoarr)) {
			foreach($columnsinfoarr as $tmpkey => $tmpvalue) {
				$htmlarr[$tmpkey]['subject'] = $tmpvalue['fieldcomment'];
				$output .= '<tr>'."\n";
				$output .= '<th>'.$tmpvalue['fieldcomment'].'</th>'."\n";
				$output .= '<td>'."\n";
				$htmlarr[$tmpkey]['content'] = '';
				if(!empty($tmpvalue['isflash'])) {
					
					$htmlarr[$tmpkey]['content'] .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="400" height="300">';
					$htmlarr[$tmpkey]['content'] .= '<param name="movie" value="'.$tmpvalue['filepath'].'" />';
					$htmlarr[$tmpkey]['content'] .= '<param name="quality" value="high" />';
					$htmlarr[$tmpkey]['content'] .= '<embed src="'.$tmpvalue['filepath'].'" type="application/x-shockwave-flash" pluginspage=" http://www.macromedia.com/go/getflashplayer" width="400" height="300"/>';
					$htmlarr[$tmpkey]['content'] .= '</object>';
				} elseif(!empty($tmpvalue['isfile'])) {
					$htmlarr[$tmpkey]['content'] .= '<a href="'.$siteurl.'/batch.modeldownload.php?hash='.$tmpvalue['filepath'].'">'.$alang['download_title'].'</a>';
				} elseif(!empty($tmpvalue['isimage'])) {
					$htmlarr[$tmpkey]['content'] .= '<a href="'.$tmpvalue['filepath'].'" target="_blank"><img src="'.$tmpvalue['filepath'].'"></a>';
				} else {
					if($tmpvalue['formtype'] == 'timestamp') {
						$tmpvalue['value'] = sgmdate($tmpvalue['value']);
					}
					$htmlarr[$tmpkey]['content'] .= !is_array($tmpvalue['value']) ? $tmpvalue['value'] : implode(', ', $tmpvalue['value']);
				}
				$output .= '</td>'."\n";
				$output .= '</tr>'."\n";
			}
		}
	}
	$output .= '</table>';
	
} elseif($op == 'list') {

	$tablename = $do == 'pass' ? $nameid.'items' : 'modelfolders';
	$uidsql = "uid='$_SGLOBAL[supe_uid]'";
	if($do != 'pass') $uidsql .= ' AND mid=\''.$cacheinfo['models']['mid'].'\' AND folder=\'1\'';
	$list = $mynews = array();
	$listcount = 0;
	if(!empty($_SGLOBAL['supe_uid'])) {
		$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname($tablename)." WHERE $uidsql $wheresql");
		$listcount = $_SGLOBAL['db']->result($query, 0);
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($tablename)." WHERE $uidsql $wheresql ORDER BY dateline DESC LIMIT $start, $perpage");
		$multipage = multi($listcount, $perpage, $page, "cp.php?ac=models&op=list&nameid={$cacheinfo[models][modelname]}&do=$do$mpurlstr");
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			if($do != 'pass') {
				$temparr  = unserialize($value['message']);
				unset($value['message']);
				$value = array_merge($value, $temparr);
			}
			
			$list[] = $value;
		}
		
		$uidsql = 'dateline > \''.($_SGLOBAL['timestamp']-604800).'\' AND '.$uidsql;
		if($do != 'pass') {
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($tablename)." WHERE $uidsql AND folder='1' ORDER BY dateline DESC LIMIT 0, 10");
		} else {
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($tablename)." WHERE $uidsql ORDER BY viewnum DESC, dateline DESC LIMIT 0, 10");
		}
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			if($do != 'pass') {
				$temparr  = unserialize($value['message']);
				unset($value['message']);
				$value = array_merge($value, $temparr);
			}
			$mynews[] = $value;
		}
	}

}

$mpurlstr = str_replace(array(' ', 'AND', '\''), array('', '&', ''), $wheresql);

include template('cp_models');

?>
