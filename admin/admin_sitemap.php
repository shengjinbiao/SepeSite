<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_sitemap.php 13489 2009-11-10 02:34:44Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managesitemap')) {
	showmessage('no_authority_management_operation');
}

//组建html url
$htmlurl = H_URL;
if(!strexists($htmlurl, '://')) $htmlurl = S_URL_ALL.substr(H_DIR, 1);

$addclass = $viewclass = $slogidarr = '';
$listarr = array();
$sitemapdata = array('index'=>'', 'file' => array());
$baidu_style = 'none';
$google_style = '';
$options = array('daily'=>'daily', 'hourly'=>'hourly',  'weekly'=>'weekly', 'monthly'=>'monthly', 'yearly'=>'yearly', 'never'=>'never', 'always'=>'always');
$start = empty($_GET['start']) ? 0 : intval($_GET['start']);
$countnum = 0;
$lastfileid = 0;
$sitemap_path = S_ROOT.'./data/sitemap/';
if(!file_exists($sitemap_path)) {
	@mkdir($sitemap_path, '0666');
}

if(submitcheck('thevalue')) {
	if(!preg_match("/^[0-9a-z_]+$/i", $_POST['mapname']) || strlen($_POST['mapname']) > 50) {
		showmessage('sitemap_name_error');
	}
	$mapdata = addslashes(serialize($sitemapdata));
	$_POST['maptype'] = saddslashes(shtmlspecialchars($_POST['maptype']));
	$_POST['mapnum'] = $_POST['maptype'] == 'google' ? intval($_POST['mapnum_google']) : intval($_POST['mapnum_baidu']);
	$_POST['createtype'] = intval($_POST['createtype']);
	$_POST['changefreq'] = $_POST['maptype'] == 'google' ? saddslashes(shtmlspecialchars($_POST['changefreq_google'])) : saddslashes(shtmlspecialchars($_POST['changefreq_baidu']));
	if(!empty($_POST['slogid'])) {
		$_SGLOBAL['db']->query("UPDATE ".tname('sitemaplogs')." SET mapname='$_POST[mapname]', maptype='$_POST[maptype]', mapnum='$_POST[mapnum]', createtype='$_POST[createtype]', changefreq='$_POST[changefreq]' WHERE slogid='$_POST[slogid]'");
		showmessage('sitemap_config_update', $theurl);
	} else {
		$query = $_SGLOBAL['db']->query("SELECT count(*) FROM ".tname('sitemaplogs')." WHERE mapname='$_POST[mapname]'");
		if($value = $_SGLOBAL['db']->result($query,0)) {
			showmessage('sitemap_name_exists');
		}
		$_SGLOBAL['db']->query("INSERT INTO ".tname('sitemaplogs')."(mapname, maptype, mapnum, mapdata, createtype, changefreq) VALUES ('$_POST[mapname]', '$_POST[maptype]', '$_POST[mapnum]', '$mapdata', '$_POST[createtype]', '$_POST[changefreq]')");
		showmessage('sitemap_config_add', $theurl);
	}

} elseif(submitcheck('listsubmit')) {
	if(!empty($_POST['slogidarr'])) {
		$slogidarr = implode('\',\'', $_POST['slogidarr']);
		$_SGLOBAL['db']->query('DELETE FROM '.tname('sitemaplogs').' WHERE slogid IN (\''.$slogidarr.'\')');
	}
	showmessage('robotmessage_op_success', $theurl);
}

if(empty($_GET['op'])) {
	$viewclass = ' class="active"';
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('sitemaplogs'));
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['dateline'] = sgmdate($value['dateline']);
		$listarr[] = $value;
	}
} elseif($_GET['op'] == 'add') {
	$addclass = ' class="active"';
	$thevalue = array('slogid'=>'', 'maptype'=>'google', 'dateline'=>'', 'createtype'=>'', 'lastitemid'=>'', 'mapnum' => '');
} elseif($_GET['op'] == 'edit') {
	$_GET['slogid'] = intval($_GET['slogid']);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('sitemaplogs')." WHERE slogid='$_GET[slogid]'");
	$thevalue = $_SGLOBAL['db']->fetch_array($query);
	$thevalue['dateline'] = sgmdate($thevalue['dateline']);
	if($thevalue['maptype'] == 'baidu') {
		$baidu_style = '';
		$google_style = 'none';
		$disabled = 'disabled';
	} else {
		$baidu_style = 'none';
		$google_style = '';
		$disabled = '';
	}
} elseif($_GET['op'] == 'update') {
	if(is_dir($sitemap_path) && is_writable($sitemap_path)){
		$_GET['slogid'] = intval($_GET['slogid']);
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('sitemaplogs')." WHERE slogid='$_GET[slogid]'");
		if($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['lastitemid'] = intval($value['lastitemid']) > 0 ? intval($value['lastitemid']) : 1;
			$sitemapdata = unserialize($value['mapdata']);
			$sitemapdata['index'] = $value['mapname'].'.xml';
			$limit = $idvalue = $itemurl = '';

			$fp = @fopen(S_ROOT.$sitemapdata['index'], 'w+');
			if($value['maptype'] == 'baidu') {
				$limit = 'ORDER BY itemid DESC limit '.$value['mapnum'];
				$submiturl = "http://news.baidu.com/newsop.html#ks5";
				write('<?xml version="1.0" encoding="'.($_SCONFIG['charset'] == 'gbk' ? 'GB2312' : $_SCONFIG['charset']).'"?>');
				write('<document>');
				write('<webSite>'.S_URL_ALL.'</webSite>');
				write('<webMaster>'.$adminemail.'</webMaster>');
				write('<updatePeri>'.$value['changefreq'].'</updatePeri>');
			} else {
				$limit = 'ORDER BY itemid limit '.$value['mapnum'];
				$submiturl = "https://www.google.com/webmasters/sitemaps/";
			}
			//重新生成
			if($value['createtype'] == '0' && empty($start)) {
				$value['lastitemid'] = 0;
				$value['lastfileid'] = 0;
				$sitemapdata['file'] = array();
			}

			$i = $n = 0;
			$query = $_SGLOBAL['db']->query("SELECT max(itemid) FROM ".tname('spaceitems')." WHERE type IN (".simplode(array_keys($channels['types'])).")");
			$countnum =$_SGLOBAL['db']->result($query, 0);
			$query = $_SGLOBAL['db']->query("SELECT si.itemid, si.type, si.uid, si.dateline, si.subject, si.username, sn.message FROM ".tname('spaceitems')." si LEFT JOIN ".tname('spacenews')." sn ON sn.itemid = si.itemid WHERE si.type IN (".simplode(array_keys($channels['types'])).") AND si.itemid > $value[lastitemid] ".$limit);
			$oldid = 0;
			while($itemvalue = $_SGLOBAL['db']->fetch_array($query)) {
				if($oldid == $itemvalue['itemid']) continue;
				$oldid = $itemvalue['itemid'];
				$itemvalue['dateline'] = sgmdate($itemvalue['dateline'], "Y-m-d H:i:s");
				$itemvalue['subject'] = htmlspecialchars(strip_tags(trim($itemvalue['subject'])));
                $itemvalue['message'] = htmlspecialchars(strip_tags(trim($itemvalue['message'])));
				$itemvalue['username'] = htmlspecialchars(strip_tags(trim($itemvalue['username'])));
				
				if($_SCONFIG['urltype'] != 4 && $_SCONFIG['urltype'] != 5) {
					$itemurl = geturl("action/viewnews/itemid/$itemvalue[itemid]", 2);
				} else {
					$itemurl = geturl("action/viewnews/itemid/$itemvalue[itemid]", 0);
				}

				$idvalue = ($itemvalue['itemid']>9)?substr($itemvalue['itemid'], -2, 2):$itemvalue['itemid'];
				$htmlpath = $idvalue.'/n-'.$itemvalue['itemid'].'.html';
				if(file_exists(H_DIR.'/'.$htmlpath)) {
					$itemurl = $htmlurl.'/'.$htmlpath;
				}

				//防止$siteurl没有填时
				if(!strexists($itemurl, '://')) {
					$itemurl = S_URL_ALL.$itemurl;
				}

				if($value['maptype'] == 'google') {
					$n = floor($i / $value['mapnum']) + $value['lastfileid'];
					$sitemapfile = S_URL_ALL.'/data/sitemap/'.$value['mapname'].'_'.$n.'.xml';
					$urlarr[$n][] = $itemurl;
					if(!in_array($sitemapfile, $sitemapdata['file'])) {
						$sitemapdata['file'][] = $sitemapfile;
					}
				} elseif($value['maptype'] == 'baidu') {
					write('<item>');
					write('<title>'.$itemvalue['subject'].'</title>');
					write('<link>'.$itemurl.'</link>');
					write('<text>'.$itemvalue['message'].'</text>');
					write('<image />');
					write('<source>'.S_URL_ALL.'</source>');
					write('<author>'.$itemvalue['username'].'</author>');
					write('<pubDate>'.$itemvalue['dateline'].'</pubDate>');
					write('</item>');
				}
				$value['lastitemid'] = $itemvalue['itemid'] > $value['lastitemid'] ? $itemvalue['itemid'] : $value['lastitemid'];	//更新最后的itemid为当前的itemid值
				$i++;
			}

			if($value['maptype'] == 'baidu') {
				write('</document>', 1);
				fclose($fp);
			} else {
				write('<?xml version="1.0" encoding="UTF-8"?>');
				write('<sitemapindex xmlns="http://www.google.com/schemas/sitemap/0.84">');
				if(!empty($sitemapdata['file'])) {
					rsort($sitemapdata['file']);
					foreach($sitemapdata['file'] as $filevalue) {
						write('<sitemap>');
						write('<loc>'.$filevalue.'</loc>');
						write('<lastmod>'.sgmdate($_SGLOBAL['timestamp'], 'Y-m-d').'</lastmod>');
						write('</sitemap>');
					}
				}
				fclose($fp);
				if(!empty($urlarr)) {
					foreach($urlarr as $n=>$itemurlarr) {
						$sitemapfile = $sitemap_path.$value['mapname'].'_'.$n.'.xml';
						if($fp = @fopen($sitemapfile, 'w+')) {
							write('<?xml version="1.0" encoding="utf-8"?>');
							write('<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">');
							foreach($itemurlarr as $url) {
								write('<url>');
								write('<loc>'.$url.'</loc>');
								write('<lastmod>'.sgmdate($_SGLOBAL['timestamp'], 'Y-m-d').'</lastmod>');
								write('<changefreq>'.$value['changefreq'].'</changefreq>');
								write('</url>');
							}
						}
						write('</urlset>');
						fclose($fp);
					}
				}
			}
			$lastfileid = count($sitemapdata['file']);
			$mapdata = addslashes(serialize(($sitemapdata)));
			$_SGLOBAL['db']->query("UPDATE ".tname('sitemaplogs')." SET lastitemid='$value[lastitemid]', dateline='$_SGLOBAL[timestamp]', mapdata='$mapdata', lastfileid='$lastfileid' WHERE slogid='$value[slogid]'");
			if($value['lastitemid'] < $countnum) {
				showmessage($alang['sitemap_start_create'].$value['lastitemid'].'->'.($value['lastitemid']+$value['mapnum']).$alang['sitemap_start_create_1'], $theurl.'&op=update&start=1&slogid='.$value['slogid']);
			}  else {
				if($value['maptype'] == 'google') {
					$fp = @fopen(S_ROOT.$sitemapdata['index'], 'a+');
					write('</sitemapindex>');
					fclose($fp);
				}
				showmessage(S_URL_ALL.'/'.$sitemapdata['index'].'</a><br />'.$alang['sitemap_info'].'<a href="'.S_URL_ALL.'/'.$sitemapdata['index'].'">Sitemap</a>, '.$alang['sitemap_info_0'].'<a href="'.$submiturl.'">Sitemap</a>', $theurl, 20);
			}
		}
	} else {
		showmessage('sitemap_perm_error');
	}
}

print<<<END
	<script>
	function changemaptype(obj) {
		if(obj.value == 'baidu') {
			document.getElementById('typehtml_baidu').style.display = '';
			document.getElementById('typehtml_google').style.display = 'none';
			document.getElementById('createtype_baidu').disabled = true;
			document.getElementById('createtype_google').disabled = true;
		} else {
			document.getElementById('typehtml_baidu').style.display = 'none';
			document.getElementById('typehtml_google').style.display = '';
			document.getElementById('createtype_baidu').disabled = false;
			document.getElementById('createtype_google').disabled = false;
		}
	}
	</script>
	<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
	<td><h1>$alang[sitemap_paper_maps]</h1></td>
	<td class="actions">
		<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
		<tr>
		<td$viewclass><a href="$theurl" class="view">$alang[sitemap_config_view]</a></td>
		<td$addclass><a href="$theurl&op=add" class="add">$alang[sitemap_config_add]</a></td>
		</tr>
		</table>
	</td>
	</tr>
	</table>
	<table cellspacing="2" cellpadding="2" class="helptable"><tr><td>$alang[sitemap_help]</td></tr></table>
END;

if(!empty($listarr)) {
	$formhash = formhash();
	print<<<END
		<form method="post" action="$theurl" name="thevalueform" enctype="multipart/form-data" onSubmit="return listsubmitconfirm(this)">
		<input type="hidden" name="formhash" value="$formhash">
		<table cellspacing="0" cellpadding="0" width="100%"  class="listtable">
		<tr>
		<th>$alang[words_del]</th>
		<th>$alang[sitemap_name]</th>
		<th>$alang[sitemap_type]</th>
		<th>$alang[sitemap_num]</th>
		<th>$alang[sitemap_createtime]</th>
		<th>$alang[sitemap_createtype]</th>
		<th>$alang[sitemap_lastitemid]</th>
		<th>$alang[spacecache_op]</th>
		</tr>
END;
	foreach($listarr as $value) {
		echo '<tr>';
		echo '<td><input type="checkbox"  class="checkbox" name="slogidarr[]" value="'.$value['slogid'].'"></td>';
		echo '<td>'.$value['mapname'].'</td>';
		echo '<td>'.$value['maptype'].'</td>';
		echo '<td>'.$value['mapnum'].'</td>';
		echo '<td>'.$value['dateline'].'</td>';
		echo '<td>'.$alang['sitemap_createtype_'.$value['createtype']].'</td>';
		echo '<td>'.$value['lastitemid'].'</td>';
		echo '<td align="center"><a href="'.$theurl.'&op=edit&slogid='.$value['slogid'].'">'.$alang['robot_robot_op_edit'].'</a>|<a href="'.$theurl.'&op=update&slogid='.$value['slogid'].'">'.$alang['generation_sitemap_clicking_here'].'</a></td></tr>';
	}
	echo '</table>';
	echo '<table cellspacing="0" cellpadding="0" width="100%"  class="btmtable">';
	echo '<tr><th><input type="checkbox" name="chkall" onclick="checkall(this.form, \'slogid\')">'.$alang['space_select_all'].'</th></tr>';
	echo '</table>';
	echo '<div class="buttons">';
	echo '<input type="submit" name="listsubmit" value="'.$alang['common_submit'].'" class="submit"> ';
	echo '<input type="reset"  value="'.$alang['common_reset'].'">';
	echo '</div>';
	echo '</form>';
}

if(!empty($thevalue)) {
	echo '<form method="post" action="'.$theurl.'" name="thevalueform" enctype="multipart/form-data">';
	echo '<input type="hidden" name="formhash" value="'.formhash().'">';
	echo '<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">';
	echo '<tr id="tr_subject"><th>'.$alang['sitemap_name'].'</th><td><input type="text" name="mapname" size="30" value="'.$thevalue['mapname'].'"></td></tr>';
	echo '<tr id="tr_subject"><th>'.$alang['sitemap_type'].'</th><td><input type="radio" name="maptype" size="30" value="baidu" '.($thevalue['maptype'] == 'baidu' ? 'checked' : '').' onclick="changemaptype(this)">Baidu <input type="radio" name="maptype" size="30" value="google" '.($thevalue['maptype'] == 'google' ? 'checked' : '').' onclick="changemaptype(this)">Google</td></tr>';
	echo '<tbody id="typehtml_baidu" style="display:'.$baidu_style.'">';
	echo '<tr id="tr_subject"><th>'.$alang['sitemap_changefreq'].'</th><td><input type="text" name="changefreq_baidu" value="'.(is_numeric($thevalue['changefreq']) ? $thevalue['changefreq'] : 15).'" id="changefreq_baidu"></td></tr>';
	echo '<tr id="tr_subject"><th>'.$alang['sitemap_num'].'</th><td><input type="text" name="mapnum_baidu" size="30" value="'.(empty($thevalue['mapnum']) ? 100 : $thevalue['mapnum']).'"></td>';
	echo '</tbody>';
	echo '<tbody id="typehtml_google" style="display:'.$google_style.'">';
	echo label(array('type' => 'select', 'name'=>'changefreq_google', 'options'=>$options, 'value'=>$thevalue['changefreq'], 'alang'=>$alang['sitemap_changefreq']));
	echo '<tr id="tr_subject"><th>'.$alang['sitemap_num'].'</th><td><input type="text" name="mapnum_google" size="30" value="'.(empty($thevalue['mapnum']) ? 5000 : $thevalue['mapnum']).'"></td>';
	echo '</tbody>';
	echo '<tr id="tr_subject"><th>'.$alang['sitemap_createtype'].'</th><td><input id="createtype_baidu" type="radio" name="createtype" size="30" value="0" '.(($thevalue['createtype'] == 0) ? 'checked' : '').' '.$disabled.'>'.$alang['sitemap_createtype_0'].' <input id="createtype_google" type="radio" name="createtype" size="30" value="1" '.(($thevalue['createtype'] == 1) ? 'checked' : '').' '.$disabled.'>'.$alang['sitemap_createtype_1'].'</td></tr>';
	echo '</table>';
	echo '<div class="buttons">';
	if($_GET['op'] == 'edit'){
		echo '<input type="hidden" name="slogid" value="'.$thevalue['slogid'].'">';
	}
	echo '<input type="submit" name="thevalue" value="'.$alang['common_submit'].'" class="submit"> ';
	echo '<input type="reset"  value="'.$alang['common_reset'].'">';
	echo '</div>';
	echo '</form>';
}

function write($text, $n=0) {
	global $fp;
	if(empty($n)) {
		fwrite($fp, $text."\r\n");
	} else {
		fwrite($fp, $text."\r");
	}
}
?>