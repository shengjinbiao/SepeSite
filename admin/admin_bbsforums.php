<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	论坛版块
	$Id: admin_bbsforums.php 11150 2009-02-20 01:35:59Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managebbsforums')) {
	showmessage('no_authority_management_operation');
}

//判断论坛数据配置
if(empty($_SCONFIG['bbsurl'])) {
	showmessage('bbs_db_setting', CPURL.'?action=bbs');
}

$perpage = 20;

dbconnect(1);
$listarr = array();
$thevalue = array();

//POST METHOD
if (submitcheck('valuesubmit')) {

	$farr = $forumarr = $sqlarr = $bbsforumarr = array();
	$formsql = '';
	
	$_SGLOBAL['db']->query('TRUNCATE TABLE '.tname('forums'));
	if(is_array($_POST['newname'])) {
		foreach($_POST['newname'] as $fid=>$fourm){
			$bbsforumarr[$fid]['fid'] = $fid;
			$bbsforumarr[$fid]['fup'] = intval($_POST['fup'][$fid]);
			$bbsforumarr[$fid]['type'] = $_POST['type'][$fid];
			$bbsforumarr[$fid]['allowshare'] = intval($_POST['allowshare'][$fid]);
			$bbsforumarr[$fid]['bbsname'] = stripslashes($fourm);
			$bbsforumarr[$fid]['pushsetting'] = $_POST['pushsetting'][$fid];
			$setting = saddslashes(serialize($_POST['pushsetting'][$fid]));
			$bbsforumarr[$fid]['displayorder'] = intval($_POST['displayorder'][$fid]);
			$sqlarr[$fid] = "('$fid', '".$bbsforumarr[$fid]['fup']."', '$fourm', '".$bbsforumarr[$fid]['type']."', '".$bbsforumarr[$fid]['allowshare']."', '$setting', '".$bbsforumarr[$fid]['displayorder']."')";			
		}
 	}
 	$formsql = implode(',', $sqlarr);
	$formsql = 'INSERT INTO '.tname('forums').'(fid, fup, name, type, allowshare, pushsetting, displayorder) VALUES'.$formsql;
	$_SGLOBAL['db']->query($formsql);
	
	//更新配置缓存
	updatebbsforumset();
	
	//执行聚合帖子计划任务
	if(!@include S_ROOT."./include/cron/updatebbsforums.php") {
		errorlog('CRON', "Cronid 7 : Cron script(./include/cron/updatebbsforums.php) not found or syntax error", 0);
	}
	
	showmessage('bbsforums_update_success', CPURL.'?action=bbsforums');
}

$_SGLOBAL['grouparr'] = $forums = $showedforums = array();
$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('forums').' ORDER BY displayorder');
while ($value=$_SGLOBAL['db']->fetch_array($query)) {
	$value['pushsetting'] = unserialize($value['pushsetting']);
	$_SGLOBAL['bbsforumarr'][$value['fid']] = $value;
}

$query = $_SGLOBAL['db_bbs']->query('SELECT type, fup, fid, name FROM '.tname('forums', 1));
while($forum = $_SGLOBAL['db_bbs']->fetch_array($query)) {
	$forums[] = $forum;
}
$thevalue['forumsstr'] = $class = '';

for($i = 0; $i < count($forums); $i++) {
	if($forums[$i]['type'] == 'group') {
		$thevalue['forumsstr'] .= showforum($i, 'group');
		for($j = 0; $j < count($forums); $j++) {
			if($forums[$j]['fup'] == $forums[$i]['fid'] && $forums[$j]['type'] == 'forum') {
				$thevalue['forumsstr'] .= showforum($j);
				for($k = 0; $k < count($forums); $k++) {
					if($forums[$k]['fup'] == $forums[$j]['fid'] && $forums[$k]['type'] == 'sub') {
						$thevalue['forumsstr'] .= showforum($k, 'sub');
					}
				}
			}
		}
	} elseif(!$forums[$i]['fup'] && $forums[$i]['type'] == 'forum') {
		$thevalue['forumsstr'] .= showforum($i);
		for($j = 0; $j < count($forums); $j++) {
			if($forums[$j]['fup'] == $forums[$i]['fid'] && $forums[$j]['type'] == 'sub') {
				$thevalue['forumsstr'] .= showforum($j, 'sub');
			}
		}
	}
}

$cpurl =  CPURL;

echo '
<style>
.tipdiv{ background-color:#FFFFFF; border:1px solid #DDDDDD; padding:1em; position:absolute; text-align:left; width:150px; z-index:1; }
</style>
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['bbs_block_set'].'</h1></td>
	    <td class="actions">
		<table cellspacing="0" cellpadding="0" border="0" align="right" summary="">
			<tbody><tr>
					<td><a href="'.$cpurl.'?action=bbs">'.$alang['admincp_header_type_bbsset'].'</a></td>
					<td class="active"><a href="'.$cpurl.'?action=bbsforums">'.$alang['admincp_header_type_bbsforums'].'</a></td>
					<td><a href="'.$cpurl.'?action=threads">'.$alang['admincp_header_type_bbsthreads'].'</a></td>
				</tr>
			</tbody></table>
		</td>
	</tr>
</table>
';

//THE VALUE SHOW
if($thevalue) {
	echo '
	<script language="javascript">
	function showdiv(fid) {
		hiddenalldiv();
		var fdiv = document.getElementById("divfilter"+fid);
		fdiv.style.display = "";
	}
	function hiddendiv(fid) {
		var fdiv = document.getElementById("divfilter"+fid);
		fdiv.style.display = "none";
	}
	function changestatus(fid, value) {
		var sdiv = document.getElementById("divstatus"+fid);
		var name = "";
		if(value == 0) {
			name = "'.$alang['bbsforums_status_0'].'"; document.getElementById("select"+fid).style.display="none";
		} else if(value == 1) {
			name = "'.$alang['bbsforums_status_1'].'"; document.getElementById("select"+fid).style.display="none";
		} else if(value == 2) {
			name = "'.$alang['bbsforums_status_2'].'"; document.getElementById("select"+fid).style.display="none";
		} else if(value == 3) {
			name = "'.$alang['bbsforums_status_3'].'"; document.getElementById("select"+fid).style.display="block";
		}
		sdiv.innerHTML = name;
	}
	function hiddenalldiv() {
		var list = document.getElementsByTagName(\'div\');
		for ( i=0; i<list.length; i++ ) {
			if(list[i].className == "tipdiv") {
				list[i].style.display = "none";
			}
		}
	}
	</script>';

	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$theurl, 'other'=>' onSubmit="return validate(this)"'));
	echo label(array('type'=>'div-start'));

	echo label(array('type'=>'help', 'text'=>$alang['help_bbsforums']));

	echo label(array('type'=>'table-start', 'class'=>'listtable'));
	echo '<tr><th width="29%">'.$alang['bbsforums_view'].'</th>
	<th align="center" width="31%">'.$alang['bbsforums_new_name'].'</th>
	<th align="center" width="10%">'.$alang['bbsforums_title_displayorder'].'</th>
	<th align="center" width="15%">'.$alang['bbsforums_allowshare'].'<input type="checkbox" onclick="checkall(this.form, \'allowshare\')" name="chkall"/></th>
	<th align="center" width="15%">'.$alang['bbsforums_supe_pushsetting'].'</th>
	</tr>';

	echo $thevalue['forumsstr'];
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo '<input name="valuesubmit" type="hidden" value="yes" />';
	echo label(array('type'=>'form-end'));
}

function showforum($key, $type = '') {
	global $forums, $class, $alang, $_SGLOBAL, $_SCONFIG;
	
	$forum = $forums[$key];

	$forum['allowshare'] = $_SGLOBAL['bbsforumarr'][$forum['fid']]['allowshare'];
	$forum['pushsetting'] = $_SGLOBAL['bbsforumarr'][$forum['fid']]['pushsetting'];
	$forum['displayorder'] = $_SGLOBAL['bbsforumarr'][$forum['fid']]['displayorder'];
	
	if(!empty($_SGLOBAL['bbsforumarr'][$forum['fid']]) && !empty($_SGLOBAL['bbsforumarr'][$forum['fid']]['name'])) {
		$forum['newname'] = $_SGLOBAL['bbsforumarr'][$forum['fid']]['name'];
	} else {
		$forum['newname'] = $forum['name'];
	}
	$forum['newname'] = shtmlspecialchars($forum['newname']);

	if(empty($type)) {
		$pre = '<img src="images/base/category_space.gif" align="absmiddle" />
		<img src="images/base/category_folder.gif" align="absmiddle" />';
	} elseif ($type == 'group') {
		$pre = '';
	} elseif ($type == 'sub') {
		$pre = '<img src="images/base/category_space.gif" align="absmiddle" />
		<img src="images/base/category_space.gif" align="absmiddle" />
		<img src="images/base/category_folder.gif" align="absmiddle" />';

	}
	empty($class) ? $class=' class="darkrow"': $class='';

	$blodclass = '';
	if($forum['type'] == 'group') {
		$forum['allowshare'] = $forum['allowgroup'] = '';
	} else {
		if(empty($forum['allowshare'])) {
			$forum['allowshare'] = '';
		} else {
			$forum['allowshare'] = ' checked';
			$blodclass = ' style="font-weight:bold;"';
		}
		$forum['allowshare'] = '<input type="checkbox" name="allowshare['.$forum['fid'].']" value="1"'.$forum['allowshare'].'>';
	
	}

	if($type != 'group') {
		$forum['pushsetting']['status'] = intval($forum['pushsetting']['status']);
		if(empty($forum['pushsetting']['filter'])) {
			$forum['pushsetting']['filter'] = array('views'=>'0', 'replies'=>'0', 'digest'=>'0', 'displayorder'=>'0');
		}
		$forum['pushsetting']['filter']['views'] = intval($forum['pushsetting']['filter']['views']);
		$forum['pushsetting']['filter']['replies'] = intval($forum['pushsetting']['filter']['replies']);
		$forum['pushsetting']['filter']['digest'] = intval($forum['pushsetting']['filter']['digest']);
		$forum['pushsetting']['filter']['displayorder'] = intval($forum['pushsetting']['filter']['displayorder']);

		$statusstr = '<input type="radio" name="pushsetting['.$forum['fid'].'][status]" value="1" onclick="changestatus('.$forum['fid'].', this.value)">'.$alang['bbsforums_status_1'].'<br>'.
					'<input type="radio" name="pushsetting['.$forum['fid'].'][status]" value="0" onclick="changestatus('.$forum['fid'].', this.value)">'.$alang['bbsforums_status_0'].'<br>'.
					 '<input type="radio" name="pushsetting['.$forum['fid'].'][status]" value="3" onclick="changestatus('.$forum['fid'].', this.value)">'.$alang['bbsforums_status_3'].'';
		
		if($forum['pushsetting']['status'] != 3) {
			$divstatus = 'none';
		}
		$pushstr = '<div id="divfilter'.$forum['fid'].'" class="tipdiv" style="display:none;">
		<a href="javascript:;" onclick="hiddendiv('.$forum['fid'].')"><img src="admin/images/icon_del2.gif" border="0" align="right"></a>
		'.$statusstr.'<div id="select'.$forum['fid'].'" style="display:'.$divstatus.';">
		<br>'.$alang['bbsforums_views'].' >= <input type="input" name="pushsetting['.$forum['fid'].'][filter][views]" value="'.$forum['pushsetting']['filter']['views'].'" size="5">
		<br>'.$alang['bbsforums_replies'].' >= <input type="input" name="pushsetting['.$forum['fid'].'][filter][replies]" value="'.$forum['pushsetting']['filter']['replies'].'" size="5">
		<br>'.$alang['bbsforums_digest'].' >= <select name="pushsetting['.$forum['fid'].'][filter][digest]">
		<option value="0"></option>
		<option value="1">'.$alang['bbsforums_digest1'].'</option>
		<option value="2">'.$alang['bbsforums_digest2'].'</option>
		<option value="3">'.$alang['bbsforums_digest3'].'</option>
		</select>
		<br>'.$alang['bbsforums_displayorder'].' >= <select name="pushsetting['.$forum['fid'].'][filter][displayorder]">
		<option  value="0"></option>
		<option  value="1">'.$alang['bbsforums_displayorder1'].'</option>
		<option  value="2">'.$alang['bbsforums_displayorder2'].'</option>
		<option  value="3">'.$alang['bbsforums_displayorder3'].'</option>
		</select></div>
		<br/>
		<div align="center">
		<button onclick="hiddendiv('.$forum['fid'].');return false;">'.$alang['button_ok'].'</button>
		</div>
		</div>
		';

		$pushstr = str_replace('[status]" value="'.$forum['pushsetting']['status'].'"', '[status]" value="'.$forum['pushsetting']['status'].'" checked', $pushstr);
		$pushstr = str_replace('option value="'.$forum['pushsetting']['filter']['digest'].'"', 'option value="'.$forum['pushsetting']['filter']['digest'].'" selected', $pushstr);
		$pushstr = str_replace('option  value="'.$forum['pushsetting']['filter']['displayorder'].'"', 'option  value="'.$forum['pushsetting']['filter']['displayorder'].'" selected', $pushstr);
	}

	$result = '<tr'.$class.'><td>'.
	$pre.'<span'.$blodclass.'>'.$forum['name'].'</span> </td>
	<td align="center"><input type="text" name="newname['.$forum['fid'].']" value="'.$forum['newname'].'" size="28"></td>
	<td align="center"><input type="text" name="displayorder['.$forum['fid'].']" value="'.$forum['displayorder'].'" size="5"></td>
	<td align="center">'.$forum['allowshare'].'</td>
	<td align="center">';

	if($type == 'group') {
		$result .= '-';
	} else {
		$result .= '<a href="javascript:;" onclick="showdiv('.$forum['fid'].')"><div id="divstatus'.$forum['fid'].'" style="text-decoration: underline;font-weight: bold;">'.$alang['bbsforums_status_'.$forum['pushsetting']['status']].'</div></a>'.$pushstr;
	}
	$result .= '<input type="hidden" name="type['.$forum['fid'].']" value="'.$forum['type'].'" /><input type="hidden" value="'.$forum['fup'].'" name="fup['.$forum['fid'].']" /></td></tr>';
	
	return $result;
}

//更新论坛聚合配置缓存
function updatebbsforumset() {
	global $_SGLOBAL;
	
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('forums').' ORDER BY displayorder');
	while ($value=$_SGLOBAL['db']->fetch_array($query)) {
		$value['pushsetting'] = unserialize($value['pushsetting']);
		$_SGLOBAL['bbsforumarr'][$value['fid']] = $value;
	}
	
	$cachefile = S_ROOT.'./data/system/bbsforums.cache.php';
	$cachetext = '$_SGLOBAL[\'bbsforumarr\']='.arrayeval($_SGLOBAL['bbsforumarr']);
	writefile($cachefile, $cachetext, 'php');
}

?>