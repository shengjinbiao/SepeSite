<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: m.php 13493 2009-11-11 06:15:33Z zhaofei $
*/

include_once('./common.php');
include_once(S_ROOT.'./function/model.func.php');

//站点关闭
if(!empty($_SCONFIG['closesite']) && $_SGET['action'] != 'login') {
	if((empty($_SGLOBAL['group']['groupid']) || $_SGLOBAL['group']['groupid'] != 1) && !checkperm('closeignore')) {
		if(empty($_SCONFIG['closemessage'])) $_SCONFIG['closemessage'] = $lang['site_close'];
		$userinfo = empty($_SGLOBAL['supe_username']) ? '' : "$lang[welcome], $_SGLOBAL[supe_username]&nbsp;&nbsp;<a href=\"".S_URL."/batch.login.php?action=logout\" style=\"color:#aaa;\">[{$lang[logout]}]</a><br/>";
		showmessage("$_SCONFIG[closemessage]<br /><p style=\"font-size:12px;color:#aaa;\">$userinfo<a href=\"".geturl("action/login")."\" style=\"color:#aaa;\">$lang[admin_login]</a></p>");
	}
}

$modelsinfoarr = $columnsinfoarr = $sqlchararr = $sqlintarr = $sqllikearr = $sqlbetweenarr = $listarr = $gatherarr = $categories = $categorylistarr = $childcategories = array();
$fromdate = $todate = '';
$_GET['name'] = !empty($_GET['name']) ? trim($_GET['name']) : '';
$_GET['mo_catid'] = !empty($_GET['mo_catid']) ? intval($_GET['mo_catid']) : '';
$cacheinfo = getmodelinfoall('modelname', $_GET['name']);

$channel = $_GET['name'];
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

if(empty($cacheinfo['models']) || in_array($_GET['name'], $_SCONFIG['closechannels'])) {
	showmessage('visit_the_channel_does_not_exist', S_URL);
}
$modelsinfoarr = $cacheinfo['models'];
$columnsinfoarr = $cacheinfo['columns'];
$categories = $cacheinfo['categories'];
$clistarr = getmodelcachecategory($cacheinfo['categoryarr']);
foreach ($clistarr as $key => $value) {
	$categorylistarr[$key] = $value['pre'].$value['name'];
	if(!empty($_GET['mo_catid']) && $value['upid'] == $_GET['mo_catid']) $childcategories[] = $value;
}

$orders = array(
				'0' => $lang['check_order'],
				'1' => $lang['model_dateline_desc'],
				'2' => $lang['model_dateline_asc'],
				'3' => $lang['model_viewnum_desc'],
				'4' => $lang['model_viewnum_asc'],
				'5' => $lang['model_rates_desc'],
				'6' => $lang['model_rates_asc'],
				'7' => $lang['model_grade_desc'],
				'8' => $lang['model_grade_asc']
			);
$orderbyarr = array(
				'0' => 'i.itemid DESC',
				'1' => 'i.dateline DESC',
				'2' => 'i.dateline ASC',
				'3' => 'i.viewnum DESC',
				'4' => 'i.viewnum ASC',
				'5' => 'i.rates DESC',
				'6' => 'i.rates ASC',
				'7' => 'i.grade DESC',
				'8' => 'i.grade ASC',
			);

//搜索限制
$getarr = empty($_GET) ? array() : $_GET;
unset($getarr['mo_catid']);
if(!empty($getarr['page'])) unset($getarr['page']);

$searchfieldarr = array();
if(!empty($columnsinfoarr)) {
	foreach($columnsinfoarr as $tmpvalue) {
		if(!empty($tmpvalue['allowsearch']) && !empty($tmpvalue['allowshow'])) {
			$searchfieldarr[] = $tmpvalue;
		}
	}
}

if(count($getarr) > 1) {
	if(!empty($modelsinfoarr['allowguestsearch'])) {
		if(empty($_COOKIE['ss_modeldateline_1'])) {
			setcookie('ss_modeldateline_1', $_SGLOBAL['timestamp'], $_SGLOBAL['timestamp']+86400);
			showmessage('inquiries_about_the_short_time_interval');
		} else {
			if($_SGLOBAL['timestamp'] - $_COOKIE['ss_modeldateline_1'] < $modelsinfoarr['searchinterval']) {
				showmessage('inquiries_about_the_short_time_interval');
			} else {
				setcookie('ss_modeldateline_1', $_SGLOBAL['timestamp'], $_SGLOBAL['timestamp']+86400);
			}
		}
	} else {
		if(empty($_SGLOBAL['supe_uid'])) {
			setcookie('_refer', rawurlencode(S_URL_ALL.'/m.php?'.$_SERVER['QUERY_STRING']));
			showmessage('no_login', geturl('action/login'));
		}
		if(!empty($modelsinfoarr['searchinterval']) && $_SGLOBAL['group']['groupid'] != 1) {
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelinterval').' WHERE uid = \''.$_SGLOBAL['supe_uid'].'\' AND type=1');
			$result = $_SGLOBAL['db']->fetch_array($query);
			if(!empty($result)) {
				if($_SGLOBAL['timestamp'] - $result['dateline'] < $modelsinfoarr['searchinterval']) {
					showmessage('inquiries_about_the_short_time_interval');
				} else {
					updatetable('modelinterval', array('dateline' => $_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid'], 'type'=>1));
				}
			} else {
				inserttable('modelinterval', array('uid'=>$_SGLOBAL['supe_uid'], 'dateline'=>$_SGLOBAL['timestamp'], 'type'=>1));
			}
		}
	}
} else {
	if(!empty($modelsinfoarr['allowguestsearch'])) {
		if(empty($_COOKIE['ss_modeldateline_1'])) {
			setcookie('ss_modeldateline_1', $_SGLOBAL['timestamp']-86400, $_SGLOBAL['timestamp']+86400);
		}
	}
}

$perpage = !empty($modelsinfoarr['listperpage']) ? $modelsinfoarr['listperpage'] : 20;
$_GET['mo_order'] = !empty($_GET['mo_order']) ? intval($_GET['mo_order']) : 0;
$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
if($page < 1) $page = 1;
$start = ($page-1) * $perpage;

//header
$title = $modelsinfoarr['modelalias'].' - '.$_SCONFIG['sitename'];
$keywords = !empty($modelsinfoarr['seokeywords']) ? $modelsinfoarr['seokeywords'] : $modelsinfoarr['modelalias'];
$description = !empty($modelsinfoarr['seodescription']) ? $modelsinfoarr['seodescription'] : $modelsinfoarr['modelalias'];
$title = strip_tags($title);
$keywords = strip_tags($keywords);
$description = strip_tags($description);

//导航
$guidearr = array();
$guidearr[] = array('url' => empty($channels['menus'][$modelsinfoarr['modelname']]) ? S_URL.'/m.php?name='.$modelsinfoarr['modelname'] : $channels['menus'][$modelsinfoarr['modelname']]['url'],'name' => $modelsinfoarr['modelalias']);

//自定义类别
if(!empty($columnsinfoarr)) {
	foreach($columnsinfoarr as $tmpvalue) {
		if(!empty($tmpvalue['fielddata'])) {
			$temparr = explode("\r\n", $tmpvalue['fielddata']);
			if($tmpvalue['formtype'] != 'linkage') {
				$gatherarr[$tmpvalue['fieldname']] = $temparr;
			}
		}
	}
}

//search
$_GET['fromdate'] = !empty($_GET['fromdate']) ? trim($_GET['fromdate']) : '';
$_GET['todate'] = !empty($_GET['todate']) ? trim($_GET['todate']) : '';
if(!empty($_GET['fromdate'])) {
	$fromdate = sstrtotime($_GET['fromdate']);
	if($fromdate > $_SGLOBAL['timestamp'] || $fromdate < ($_SGLOBAL['timestamp']-3600*24*365*2)) {//不能早于2年
		$fromdate = $_SGLOBAL['timestamp']-3600*24*30;
		$_GET['fromdate'] = sgmdate($fromdate);
	}
}

if(!empty($_GET['todate'])) {
	$todate = sstrtotime($_GET['todate']);
	if($todate > $_SGLOBAL['timestamp'] || $todate < $fromdate) {//不能早于2年
		$todate = $_SGLOBAL['timestamp'];
		$_GET['todate'] = sgmdate($todate);
	}
}

$sqlbetweenarr['i.`dateline`'] = array($fromdate, $todate);
$isfixedsearch = 1;
if(!empty($columnsinfoarr)) {
	foreach($columnsinfoarr as $tmpvalue) {
		if(!empty($tmpvalue['allowlist']) && empty($tmpvalue['isfixed'])) {
			$isfixedsearch = 0;
			break;
		}
	}
}
$wherecatid = '';
foreach($_GET as $tmpkey => $tmpvalue) {
	if(!is_array($tmpvalue)) {
		$tmpvalue = trim($tmpvalue);
	}
	if(preg_match("/^mo_/", $tmpkey) && ((!is_array($tmpvalue) && strlen($tmpvalue) > 0) || (is_array($tmpvalue) && !empty($tmpvalue)))) {
		$key = preg_replace("/(^mo_|_from$|_to$)/", '', $tmpkey);

		if($key == 'subject') {
			$sqllikearr['i.`subject`'] = stripsearchkey(shtmlspecialchars($tmpvalue));
		} elseif($key == 'catid') {
			if(!empty($cacheinfo['categoryarr'][$tmpvalue]['url'])) {
				sheader($cacheinfo['categoryarr'][$tmpvalue]['url']);
			}
			$wherecatid = ' i.catid IN ('.$cacheinfo['categoryarr'][$tmpvalue]['subcatid'].') AND ';
		} elseif($key == 'username') {
			$sqlchararr['i.`username`'] = stripsearchkey(shtmlspecialchars($tmpvalue));
		} elseif(!empty($columnsinfoarr[$key])) {
			if(!empty($columnsinfoarr[$key]['isfixed'])) {
				$pre = 'i.';
			} else {
				$pre = 'm.';
				$isfixedsearch = 0;
			}
			if($columnsinfoarr[$key]['formtype'] == 'linkage') {
				if(!empty($cacheinfo['linkage']['info'][$key][$tmpvalue])) {
					$_GET[$tmpkey] = $tmpvalue = $cacheinfo['linkage']['info'][$key][$tmpvalue];
				}
			}
			if($columnsinfoarr[$key]['formtype'] == 'timestamp') {
				if(preg_match("/_from$/i", $tmpkey)) {
					if(empty($sqlbetweenarr[$pre.'`'.$key.'`'][1])) {
						$sqlbetweenarr[$pre.'`'.$key.'`'] = array(sstrtotime($tmpvalue), '');
					} else {
						$sqlbetweenarr[$pre.'`'.$key.'`'][0] = sstrtotime($tmpvalue);
					}
				} elseif(preg_match("/_to$/i", $tmpkey)) {
					if(empty($sqlbetweenarr[$pre.'`'.$key.'`'][0])) {
						$sqlbetweenarr[$pre.'`'.$key.'`'] = array('', sstrtotime($tmpvalue));
					} else {
						$sqlbetweenarr[$pre.'`'.$key.'`'][1] = sstrtotime($tmpvalue);
					}
				}
			} elseif(preg_match("/^(select|radio|linkage)$/i", $columnsinfoarr[$key]['formtype']) || !preg_match("/^(VARCHAR|CHAR|TEXT|MEDIUMTEXT|LONGTEXT|)$/i", $columnsinfoarr[$key]['fieldtype'])) {	//=
				if(preg_match("/^(VARCHAR|CHAR|TEXT|MEDIUMTEXT|LONGTEXT|)$/i", $columnsinfoarr[$key]['fieldtype'])) {	//char
					$sqlchararr[$pre.'`'.$key.'`'] = stripsearchkey(shtmlspecialchars($tmpvalue));
				} else {
					$sqlintarr[$pre.'`'.$key.'`'] = intval($tmpvalue);
				}
			} elseif(preg_match("/^(text|textarea|checkbox)$/i", $columnsinfoarr[$key]['formtype'])) {	//like
				$sqllikearr[$pre.'`'.$key.'`'] = stripsearchkey(shtmlspecialchars($tmpvalue));
			}
		}
	}
}

$where = getmodelsearchsql($sqlchararr, $sqlintarr, $sqllikearr, $sqlbetweenarr);
if(empty($isfixedsearch)) {
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname($modelsinfoarr['modelname'].'items').' i, '
														.tname($modelsinfoarr['modelname'].'message').' m '
														.' WHERE i.itemid=m.itemid AND '.$wherecatid.$where);
} else {
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname($modelsinfoarr['modelname'].'items').' i '
														.' WHERE '.$wherecatid.$where);
}

$listcount = $_SGLOBAL['db']->result($query,0);
$multipage = '';
$theurl = S_URL.'/m.php?'.str_replace('&page='.$page, '', $_SERVER["QUERY_STRING"]);
if($listcount) {
	if(empty($isfixedsearch)) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($modelsinfoarr['modelname'].'items').' i, '
															.tname($modelsinfoarr['modelname'].'message').' m '
															.' WHERE i.itemid=m.itemid AND '.$wherecatid.$where
															.' ORDER BY '.(empty($orderbyarr[$_GET['mo_order']]) ? $orderbyarr[0] : $orderbyarr[$_GET['mo_order']]).' LIMIT '.$start.','.$perpage);
	} else {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($modelsinfoarr['modelname'].'items').' i '
															.' WHERE '.$wherecatid.$where
															.' ORDER BY '.(empty($orderbyarr[$_GET['mo_order']]) ? $orderbyarr[0] : $orderbyarr[$_GET['mo_order']]).' LIMIT '.$start.','.$perpage);
	}
	
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['ss_url'] = geturl('action/model/name/'.$modelsinfoarr['modelname'].'/itemid/'.$value['itemid']);
		if(!empty($value['subjectimage'])) {
			$fileext = fileext($value['subjectimage']);
			if($fileext == 'gif') {
				$value['ss_imgurl'] = A_URL.'/'.$value['subjectimage'];
			} else {
				$value['ss_imgurl'] = A_URL.'/'.substr($value['subjectimage'], 0, strrpos($value['subjectimage'], '.')).'.thumb.jpg';
			}
		} else {
			$value['ss_imgurl'] = S_URL.'/images/base/nopic.gif';
		}
		
		if(!empty($columnsinfoarr)) {
			foreach($columnsinfoarr as $temp) {
				if(empty($temp['allowshow']) || empty($temp['allowlist'])) {
					unset($columnsinfoarr[$temp['fieldname']]);
				} else {
					$tmpvalue = trim($value[$temp['fieldname']]);
					if($temp['formtype'] == 'checkbox' || $temp['formtype'] == 'textarea') {
						$value[$temp['fieldname']] = explode("\n", $tmpvalue);
					}
				}
			}
		}
		$value = strim(sstrip_tags($value));
		$listarr[] = $value;
	}
	$multipage = multi($listcount, $perpage, $page, $theurl);
}

//投稿链接
$posturl = '';
if(checkperm('allowpost')) {
	$posturl = S_URL.'/cp.php?ac=models&op=add&nameid='.$channel;
}

//搜索生成
$_GET['mo_subject'] = !empty($_GET['mo_subject']) ? trim($_GET['mo_subject']) : '';
$_GET['mo_username'] = !empty($_GET['mo_username']) ? trim($_GET['mo_username']) : '';

$searchtable = array();
$linkagestr = '';
if(!empty($cacheinfo['fielddefault']['subject'])) $lang['subject'] = $cacheinfo['fielddefault']['subject'];
if(!empty($cacheinfo['fielddefault']['subjectimage'])) $lang['photo_title'] = $cacheinfo['fielddefault']['subjectimage'];
if(!empty($cacheinfo['fielddefault']['catid'])) $lang['system_catid'] = $cacheinfo['fielddefault']['catid'];
if(!empty($cacheinfo['fielddefault']['message'])) $lang['content'] = $cacheinfo['fielddefault']['message'];

$searchtable['subject'] = searchlabel(array('type'=>'input', 'alang'=>$lang['subject'], 'name'=>'mo_subject', 'size'=>'10', 'value'=>$_GET['mo_subject']));
$searchtable['catid'] = searchlabel(array('type'=>'select', 'alang'=>$lang['system_catid'], 'name'=>'mo_catid', 'options'=>$categorylistarr, 'value'=>$_GET['mo_catid']));
$searchtable['username'] = searchlabel(array('type'=>'input', 'alang'=>$lang['check_username'], 'name'=>'mo_username', 'size'=>'10', 'value'=>$_GET['mo_username']));

if(!empty($searchfieldarr)) {
	foreach($searchfieldarr as $tmpvalue) {
		$temparr = $temparr2 = array();
		$other = '';
		if($tmpvalue['formtype'] == 'timestamp') {
			$getvalue[0] = empty($_GET['mo_'.$tmpvalue['fieldname'].'_from']) ? '' : trim($_GET['mo_'.$tmpvalue['fieldname'].'_from']);
			$getvalue[1] = empty($_GET['mo_'.$tmpvalue['fieldname'].'_to']) ? '' : trim($_GET['mo_'.$tmpvalue['fieldname'].'_to']);
		} else {
			$getvalue = isset($_GET['mo_'.$tmpvalue['fieldname']]) && strlen($_GET['mo_'.$tmpvalue['fieldname']]) > 0 ? trim($_GET['mo_'.$tmpvalue['fieldname']]) : '';
		}
		if(!empty($tmpvalue['fielddata'])) {
			$temparr = explode("\r\n", $tmpvalue['fielddata']);
			foreach($temparr as $value2) {
				$temparr2[$value2] = $value2;
			}
		}
		if($tmpvalue['formtype'] == 'linkage') {
			$temparr2 = array();
			if(!empty($cacheinfo['linkage']['down'][$tmpvalue['id']])) {
				$downfieldname = $cacheinfo['columnids'][$cacheinfo['linkage']['down'][$tmpvalue['id']]];
				$other = ' onchange="fill(\'mo_'.$downfieldname.'\', \'mo_'.$tmpvalue['fieldname'].'\', '.$downfieldname.'arr);"';
			}
			if($tmpvalue['upid'] == '0') {
				$linkagestr .= 'fill(\'mo_'.$tmpvalue['fieldname'].'\', \'\', '.$tmpvalue['fieldname'].'arr, \''.$getvalue.'\');';
			} else {
				$linkagestr .= 'fill(\'mo_'.$tmpvalue['fieldname'].'\', \'mo_'.$cacheinfo['columnids'][$tmpvalue['upid']].'\', '.$tmpvalue['fieldname'].'arr, \''.$getvalue.'\');';
			}
		}

		$tmpvalue['formtype'] = preg_match("/^(text|password|textarea)$/i", $tmpvalue['formtype']) ? 'input' : $tmpvalue['formtype'];
		$tmpvalue['formtype'] = preg_match("/^(radio|checkbox|linkage)$/i", $tmpvalue['formtype']) ? 'select' : $tmpvalue['formtype'];
		$tmpvalue['size'] = preg_match("/^(TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT|FLOAT|DOUBLE)$/i", $tmpvalue['fieldtype']) ? 5 : 10;
		$searchtable[$tmpvalue['fieldname']] = searchlabel(array('type'=>$tmpvalue['formtype'], 'alang'=>$tmpvalue['fieldcomment'], 'name'=>'mo_'.$tmpvalue['fieldname'], 'size'=>$tmpvalue['size'], 'options'=>$temparr2, 'value'=>$getvalue, 'other'=>$other));
	}
}
$searchtable['order'] = searchlabel(array('type'=>'select', 'alang'=>'', 'name'=>'mo_order', 'options'=>$orders, 'value'=>$_GET['mo_order']));
$searchtable['date'] = $lang['fromdate_0'].'<input type="text" name="fromdate" id="fromdate" size="9" readonly="readonly" value="'.$_GET['fromdate'].'" onClick="getDatePicker(\'fromdate\', event, 21)" /><img src="'.S_URL.'/admin/images/time.gif" align="absmiddle" onClick="getDatePicker(\'fromdate\', event, 0)" />';
$searchtable['date'] .= $lang['fromdate_1'].'<input type="text" name="todate" id="todate" size="9" readonly="readonly" value="'.$_GET['todate'].'" onClick="getDatePicker(\'todate\', event, 21)" /><img src="'.S_URL.'/admin/images/time.gif" onClick="getDatePicker(\'todate\', event, 0)" />';


if(!empty($linkagestr)) $linkagestr = "<script>$linkagestr</script>\n";

//模板
if(empty($modelsinfoarr['tpl'])) {
	$tpldir = 'model/data/'.$modelsinfoarr['modelname'];
} else {
	$tpldir = 'mthemes/'.$modelsinfoarr['tpl'];
}

include template($tpldir.'/category.html.php', 1);

ob_out();

?>