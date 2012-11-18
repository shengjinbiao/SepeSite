<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_ad.php 11531 2009-03-09 07:17:36Z zhanglijun $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Acess Denied');
}

//权限
if(!checkperm('managead')) {
	showmessage('no_authority_management_operation');
}

$perpage = 20;
$page = intval(postget('page'));
($page > 1) ? '' : $page = 1;
$start = ($page-1) * $perpage;

$thevalue = $listvalue = array();
$_GET['op'] = empty($_GET['op']) ? '' : $_GET['op'];
$system = intval(postget('system'));
$_POST['adid'] = intval(postget('adid'));
$_POST['type'] = empty($_POST['type']) ? '' : $_POST['type'];
$_POST['pagestyle'] = empty($_POST['pagestyle']) ? 'all' : $_POST['pagestyle'];
$_POST['pagetype'] = empty($_POST['pagetype']) ? '' : $_POST['pagetype'];
$_POST['starttime'] = empty($_POST['starttime']) ? sgmdate($_SGLOBAL['timestamp'], 'Y-m-d H:i:s') : $_POST['starttime'];
$_POST['endtime'] = empty($_POST['endtime']) ? '' : $_POST['endtime'];
$viewclass = $addsystemclass = $adduserclass = $isupdate = $pageout_style = '';
$adtypearr = array(
	'1'=>array(
		'echo' => $alang['ad_adtype_echo'],
		'js' => $alang['ad_adtype_js'],
		'iframe' => $alang['ad_adtype_iframe']
	),
	'0'=>array(
		'text' => $alang['ad_adtype_text'],
		'code' => $alang['ad_adtype_code'],
		'image' => $alang['ad_adtype_image'],
		'flash' => $alang['ad_adtype_flash']
	)
);
$pagetype = array(
	'onepage' => $alang['ad_adtype_page_one'],
	'twopage' => $alang['ad_adtype_page_two'],
	'viewpage' => $alang['ad_adtype_page_view']
);
$parameters = array(
	'starttime'=> $_POST['starttime'],
	'endtime'=> $_POST['endtime'],
	'adechocontent'=>'',
	'adjscontent'=>'',
	'adiframecontent'=>'',
	'adcodecontent'=>'',
	'textcontent'=>'',
	'texturl'=>'',
	'fontsize'=>'',
	'imagesrc'=>'',
	'imageurl'=>'',
	'imagewidth'=>'',
	'imageheight'=>'',
	'imagetext'=>'',
	'flashsrc'=>'',
	'flashheight'=>'',
	'flashwidth'=>'',
	'iframewidth'=>'0',
	'iframeheight'=>'0',
	'outwidth' => '250',
	'outheight' => '200'
);
$searcharr = array (
	'all' => $alang['ad_adtype_all_page'],
	'pageheadad' => $alang['ad_adtype_pageheadad_page'],
	'pagecenterad' => $alang['ad_adtype_pagecenterad_page'],
	'pagefootad' => $alang['ad_adtype_pagefootad_page'],
	'pagemovead' => $alang['ad_adtype_pagemovead_page'],
	'pageoutad' => $alang['ad_adtype_pageoutad_page'],
	'pageoutindex' => $alang['ad_adtype_pageoutindex_page'],
	'siderad' => $alang['ad_adtype_siderad'],
	'viewinad' => $alang['ad_adtype_viewinad']
);

if(submitcheck('listsubmit')) {

	if(!empty($_POST['displayorderarr']) && is_array($_POST['displayorderarr'])) {
		foreach($_POST['displayorderarr'] as $key => $value) {
			if(empty($_POST['adidarr']) || !in_array($key, $_POST['adidarr'])) {
				$_POST['available'] = empty($_POST['availablearr'][$key]) ? 0 : 1;
				$_SGLOBAL['db']->query("UPDATE ".tname('ads')." SET available='$_POST[available]', displayorder='$value' WHERE adid='$key'");
			}
		}
	}
	$adidstr = '';
	if(!empty($_POST['adidarr'])) {
		$adidstr = implode('\',\'', $_POST['adidarr']);
		$_SGLOBAL['db']->query('DELETE FROM '.tname('ads').' WHERE adid IN (\''.$adidstr.'\')');
	}
	updateadcache();
	showmessage('ad_op_success', $theurl);

} elseif(submitcheck('addadsubmit')) {

	$pagetypestr = $typestr = '';
	$typearr = array();

	if(empty($_POST['title']) || strlen($_POST['title']) > 50) {
		showmessage('ad_check_subject', $theurl);
	} else {
		$_POST['title'] = addslashes(shtmlspecialchars($_POST['title']));
	}
	
	if($_POST['pagestyle'] == 'all' || $_POST['pagestyle'] == 'pageoutindex') {
		$parameters['outwidth'] = intval($_POST['outwidth']);
		$parameters['outheight'] = intval($_POST['outheight']);	
		if($parameters['outwidth'] <= 0 || $parameters['outheight'] <= 0) {
			showmessage('ad_out_error', $theurl);
		}
	}

	if($system == 0) {
		if(is_array($_POST['pagetype']) && !empty($_POST['pagetype'])) {
			$pagetypestr = implode("\t", $_POST['pagetype']);
		} else {
			showmessage('ad_check_page', $theurl);
		}

		if(is_array($_POST['type']) && !empty($_POST['type'])) {
			if(in_array('all', $_POST['type'])) {
				$typestr = 'all';
			} else {
				$typestr = implode("\t", $_POST['type']);
			}
		} else {
			showmessage('ad_check_type', $theurl);
		}
	}
	switch($_POST['adtype']) {
		case 'echo':
			if(empty($_POST['adechocontent'])) {
				showmessage('ad_check_adcontent', $theurl);
			}
			$parameters['adechocontent'] = $_POST['adechocontent'];
			break;
		case 'js' :
			if(empty($_POST['adjscontent'])) {
				showmessage('ad_check_adcontent', $theurl);
			}
			$parameters['adjscontent'] = $_POST['adjscontent'];
			break;
		case 'iframe' :
			if(empty($_POST['adiframecontent'])) {
				showmessage('ad_check_adcontent', $theurl);
			}
			$parameters['adiframecontent'] = $_POST['adiframecontent'];
			$parameters['iframewidth'] = intval($_POST['iframewidth']);
			$parameters['iframeheight'] = intval($_POST['iframeheight']);
			break;
		case 'text':
			if(empty($_POST['textcontent']) || empty($_POST['textsize']) || empty($_POST['texturl'])) {
				showmessage('ad_add_check_must', $theurl);
			}
			if(intval($_POST['textsize']) == 0) {
				showmessage('ad_add_check_textsize', $theurl);
			}
			$parameters['textcontent'] = $_POST['textcontent'];
			$parameters['fontsize'] = intval($_POST['textsize']);
			$parameters['texturl'] = $_POST['texturl'];
			break;
		case 'code':
			if(empty($_POST['adcodecontent'])) {
				showmessage('ad_check_adcontent', $theurl);
			}
			$parameters['adcodecontent'] = $_POST['adcodecontent'];
			break;
		case 'image':
			if(empty($_POST['imagesrc']) || empty($_POST['imageurl']) || empty($_POST['imagewidth']) || empty($_POST['imageheight'])) {
				showmessage('ad_add_check_must',$theurl);
			}elseif(strrpos($_POST['imageurl'], '://') === false) {
				showmessage('ad_imageurl_error', $theurl);
			}
			$parameters['imagesrc'] = $_POST['imagesrc'];
			$parameters['imageurl'] = $_POST['imageurl'];
			$parameters['imagewidth'] = intval($_POST['imagewidth']);
			$parameters['imageheight'] = intval($_POST['imageheight']);
			$parameters['imagetext'] = shtmlspecialchars($_POST['imagetext']);
			break;
		case 'flash':
			if(empty($_POST['flashsrc']) || empty($_POST['flashwidth']) || empty($_POST['flashheight'])) {
				showmessage('ad_add_check_must', $theurl);
			}
			$parameters['flashsrc'] = $_POST['flashsrc'];
			$parameters['flashwidth'] = intval($_POST['flashwidth']);
			$parameters['flashheight'] = intval($_POST['flashheight']);
			break;
		default :
			showmessage('ad_no_ads');
	}
	$parameters = addslashes(serialize(sstripslashes($parameters)));

	//结束时间小于当前时间则不可用
	if(!empty($_POST['endtime']) && strtotime($_POST['endtime']) < $_SGLOBAL['timestamp'] + $_SCONFIG['timeoffset']*3600) {
		$available = 0;
	} else {
		$available = 1;
	}
	if(!empty($_POST['update'])) {
		$_SGLOBAL['db']->query("UPDATE ".tname('ads')." SET available='$available', title='$_POST[title]', adtype='$_POST[adtype]', pagetype='$pagetypestr', type='$typestr', parameters='$parameters', system='$system', style='$_POST[pagestyle]' WHERE adid='$_POST[adid]'");
		updateadcache();
		showmessage('ad_update_success',$theurl);
	} else {
		$_SGLOBAL['db']->query("INSERT INTO ".tname('ads')." (`available`, `title`, `adtype`, `pagetype`, `type`, `parameters`, `system`, `style`) VALUES ('$available', '$_POST[title]', '$_POST[adtype]', '$pagetypestr', '$typestr', '$parameters', '$system', '$_POST[pagestyle]')");
		updateadcache();
		showmessage('ad_add_success',$theurl);
	}
	
}

if(empty($_GET['op'])) {
	
	if($_POST['pagestyle'] != 'all') {
		$wheresql = " where style='$_POST[pagestyle]'";
	} else {
		$wheresql = '';
	}
	
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('ads').$wheresql);
	$listcount = $_SGLOBAL['db']->result($query,0);
	$multipage = '';
	if($listcount) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('ads').$wheresql.' ORDER BY displayorder LIMIT '.$start.','.$perpage);
		while($ad = $_SGLOBAL['db']->fetch_array($query)) {
			$listvalue[] = $ad;
		}
		$multipage = multi($listcount, $perpage, $page, $theurl);
	}
	$viewclass = ' class="active"';
	
} elseif($_GET['op'] == 'edit') {
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('ads')." WHERE adid='$_POST[adid]'");
	$thevalue = $_SGLOBAL['db']->fetch_array($query);
	if($thevalue['style'] != 'pageoutindex' && $thevalue['style'] != 'all') {
		$pageout_style = 'none';
	}
	$parameters = empty($thevalue['parameters']) ? array() : unserialize($thevalue['parameters']);
	$parameters = sstripslashes($parameters);
	$isupdate = '<input type="hidden" name="update" value="1"><input type="hidden" name="adid" value="'.$thevalue['adid'].'">';

} elseif($_GET['op'] == 'add') {
	
	if($system == '0') {
		$addsystemclass = ' class="active"';
	} else {
		$adduserclass = ' class="active"';
	}
	$thevalue = array('adid'=>'' , 'title'=>'', 'adtype'=>'', 'parameters'=>'', 'pagetype'=>'', 'type'=>'', 'style'=>'');

} elseif($_GET['op'] == 'code') {

	if(empty($_POST['adid'])){
		showmessage('ad_no_ads');
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('ads')." WHERE adid='$_POST[adid]' AND system = 1");
	if($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
		$parameters = unserialize($thevalue['parameters']);
		switch($thevalue['adtype']) {
			case 'echo':
				$code = '<div>#getad("user","'.$thevalue['adid'].'")#</div><!--'.$thevalue['title'].'-->';
				$showcode = '<div>'.stripslashes($parameters['adechocontent']).'</div>';
				break;
			case 'iframe':
				$code = '<iframe src="'.S_URL_ALL.'/batch.ad.php?id='.$thevalue['adid'].'" name="_ad_'.$thevalue['adid'].'" width="'.$parameters['iframewidth'].'px" height="'.$parameters['iframeheight'].'px" marginwidth="0" frameborder="0" scrolling="no" allowTransparency="true"></iframe><!--'.$thevalue['title'].'-->';
				$showcode = $code;
				break;
			case 'js':
				$code = '<script type="text/javascript" src="'.S_URL_ALL.'/batch.ad.php?id='.$thevalue['adid'].'"></script><!--'.$thevalue['title'].'-->';
				$showcode = $code;
				break;
		}
	} else {
		showmessage('ad_no_ads');
	}
	$listvalue = $thevalue = array();

}
print<<<END
	<script type="text/javascript">
	function changetype(objtype) {
		var styles, keys;
		styles = new Array('echo', 'js', 'iframe', 'text', 'code', 'image', 'flash');
		for(keys in styles) {
			var obj=$('style_'+styles[keys]);
			obj.style.display = (styles[keys] == objtype.value) ? '':'none';
		}
	}
	function changepagetype(obj) {
		$("style_type").style.display = '';
		$("style_pagetype").style.display = '';
		var intobj = $('style_pagetype').getElementsByTagName("input");
		for(var i=0;i<intobj.length;i++) {
			if(obj.value == 'viewinad') {
				if(intobj[i].value == 3) {
					intobj[i].checked = true;
				} else {
					intobj[i].disabled = true;
				}
			} else if(obj.value == 'siderad') {
				if(intobj[i].value == 1) {
					intobj[i].disabled = true;
				} else {
					intobj[i].checked = true;
				}
			} else {
				intobj[i].checked = false;
				intobj[i].disabled = false;
			}
		}

		if(obj.value == "pageoutindex" || obj.value == "all") {
			$("pageout_style").style.display = '';
		} else {
			$("pageout_style").style.display = 'none';
		}
	}
	</script>
	<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
	<td><h1>$alang[ad_adtype_code]</h1></td>
	<td class="actions">
		<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
		<tr>
		<td$viewclass><a href="$theurl">$alang[ad_title_view]</a></td>
		<td$addsystemclass><a href="$theurl&op=add&system=0">$alang[ad_title_add_system]</a></td>
		<td$adduserclass><a href="$theurl&op=add&system=1">$alang[ad_title_add_user]</a></td>
		</tr>
		</table>
	</td>
	</tr>
	</table>
		<table cellspacing="2" cellpadding="2" class="helptable"><tr><td>$alang[help_ad_view]</td></tr></table>
	
END;

if(!empty($code)) {
	echo '<table cellspacing="0" cellpadding="0" width="100%" class="listtable"><tr>
		<th>'.$alang['ad_title_adcode'].'</th></tr><tr><td>'.$alang['ad_title_explain'].'</td></tr>
		<tr><td><textarea name="adcode" id="adcode" cols="140" rows="4">'.shtmlspecialchars($code).'</textarea></td></tr>
		<tr><th>'.$alang['ad_title_adshow'].'</th></tr>
		<tr><td>'.$showcode.'&nbsp;</td></tr>
		</table>';
}

if(!empty($listvalue)) {
	$formhash = formhash();
	echo '<table cellspacing="0" cellpadding="0" width="100%"  class="toptable">
		<form method="post" action="'.$theurl.'" enctype="multipart/form-data">'.getselectstr('pagestyle', $searcharr, $_POST['pagestyle']).'
		<input type="hidden" name="formhash" value="'.$formhash.'"><input type="submit" value="'.$alang['space_submit_filter'].'" name="submit"></form></table>';
	print<<<END
	<form method="post" id="theform" action="$theurl" enctype="multipart/form-data"  onSubmit="return listsubmitconfirm(this)">
	<input type="hidden" name="formhash" value="$formhash">
	<table cellspacing="0" cellpadding="0" width="100%"  class="listtable">
	<tr>
	<th>$alang[ad_delete]</th>
	<th>$alang[ad_available]</th>
	<th>$alang[ad_displayorder]</th>
	<th>$alang[ad_title_subject]</th>
	<th>$alang[ad_style]</th>
	<th>$alang[ad_title_dateline]</th>
	<th>$alang[ad_title_endtime]</th>
	<th>$alang[ad_title_type]</th>
	<th>$alang[ad_type]</th>
	<th>$alang[ad_page]</th>
	<th>$alang[ad_adtype_code]</th>
	<th>$alang[ad_title_operate]</th>
	</tr>
END;

	foreach($listvalue as $value) {
		$parameters = unserialize($value['parameters']);
		$checked = $value['available'] ? ' checked' : '';
		$style = $value['available'] ? '' : ' background-color:#f5f5f5;';

		if($value['system']) {
			$button = '<button type="button" onclick="javascript:window.location.href=\''.$theurl.'&op=code&adid='.$value['adid'].'\'">'.$alang['ad_adtype_code'].'</button>';
			$value['style'] = '';
		} else {
			$button = $alang['ad_system'];
			$value['style'] = $searcharr[$value['style']];
		}
		print<<<END
			<tr style="$style">
			<td align="center"><input type="checkbox" name="adidarr[]" value="$value[adid]"></td>
			<td align="center"><input type="checkbox" name="availablearr[$value[adid]]" size="5" $checked></td>
			<td align="center"><input type="text" name="displayorderarr[$value[adid]]" value="$value[displayorder]" size="5"></td>
			<td align="center">$value[title]</td>
			<td align="center">{$adtypearr[$value['system']][$value['adtype']]}</td>
			<td align="center">$parameters[starttime]</td>
			<td align="center">$parameters[endtime]</td>
			<td align="center">$value[style]</td>
			<td align="center">$value[type]</td>
			<td align="center">$value[pagetype]</td>
			<td align="center">$button</td>
			<td align="center"><a href=$theurl&op=edit&adid=$value[adid]&system=$value[system]>$alang[ad_adtype_detail]</a></td>
			</tr>
END;
	}
	echo '</table>
		<table cellspacing="0" cellpadding="0" width="100%"  class="btmtable">
		<tr><th><input type="checkbox" name="chkall" onclick="checkall(this.form, \'adid\')">'.$alang['space_select_all'].'<input type="checkbox" name="chkall2" onclick="checkall(this.form, \'available\', \'chkall2\')">'.$alang['ad_available_all'].'</th></tr>
		</table>';
	if(!empty($multipage)) {
		echo '<table cellspacing="0" cellpadding="0" width="100%"  class="listpage">';
		echo '<tr><td>'.$multipage.'</td></tr></table>';
	}
	echo '<div class="buttons">
		<input type="submit" name="listsubmit" value="'.$alang['common_submit'].'" class="submit"> 
		<input type="reset"  value="'.$alang['common_reset'].'">
		</div>
		</form>';
}

if(!empty($thevalue)) {
			
	$stylearr = array('echo'=>'none', 'js'=>'none', 'iframe'=>'none', 'text'=>'none', 'image'=>'none', 'flash'=>'none', 'code'=>'none');
	$style_type = $style_pagetype = '';
	if($system == '0') {
		$addsystemclass = ' class="active"';
		$thevalue['adtype'] = empty($thevalue['adtype']) ? 'text' : $thevalue['adtype'];
		$adtypehtml = '<tr><th>'.$alang['ad_show'].'</th><td>'.getselectstr('adtype', $adtypearr[0], $thevalue['adtype'], ' onchange="changetype(this)"').'</td></tr><input type="hidden" name="system" value="0">';

		$pagetypearr = explode("\t", $thevalue['pagetype']);
		$typecheck1 = $typecheck2 = $typecheck3 = '';
		//投放页面
		foreach(explode("\t", $thevalue['pagetype']) as $value) {
			${'typecheck'.$value} = 'checked';
		}

		$checkall = '';
		$checkindexad = '';
        $checkspacead = '';
		if($thevalue['type'] == 'all') {
			$checkall = 'selected';
		} else {
			//投放频道
			foreach(explode("\t", $thevalue['type']) as $value){
				if($value == 'indexad') {
					$checkindexad = 'selected';
				}
   
				if($value == 'space') {
                    $checkspacead = 'selected';
					$style_type = 'none';
					$style_pagetype = 'none';
				}
				${$value} = 'true';
			}
		}
			
	} else {
		$adduserclass = ' class="active"';
		$thevalue['adtype'] = empty($thevalue['adtype']) ? 'echo' : $thevalue['adtype'];
		$adtypehtml = '<tr><th>'.$alang['ad_show'].'</th><td>'.getselectstr('adtype', $adtypearr[1], $thevalue['adtype'], ' onchange="changetype(this)"').'</td></tr><input type="hidden" name="system" value="1">';
	}

	if(!empty($thevalue['adtype'])) {
		$stylearr[$thevalue['adtype']] = '';
	}
	echo '<h2>'.$alang['ad_add_adtype'].'</h2>
		<script language="javascript" src="'.S_URL.'/include/js/selectdate.js"></script>
		<form method="post" action="'.$theurl.'" enctype="multipart/form-data">
		<input type="hidden" name="formhash" value="'.formhash().'">
		<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">'.$adtypehtml;
	if($system == 1) {
		echo '<tr><th>'.$alang['ad_title_subject'].'</th><td><input type="text" size="30" name="title" value="'.$thevalue['title'].'"></td></tr>'.
		'<input type="hidden" size="10" name="outwidth" value="'.$parameters['outwidth'].'">'.
		'<input type="hidden" size="10" name="outheight" value="'.$parameters['outheight'].'">'.'
			<tr><th>'.$alang['ad_title_dateline'].'</th><td>
			<input type="text" size="30" name="starttime" value="'.$parameters['starttime'].'" id="starttime" readonly>
			<img src='.S_URL.'/admin/images/time.gif onClick="getDatePicker(\'starttime\',event,21)"/></td></tr>
			<tr><th>'.$alang['ad_title_endtime'].'</th><td>
			<input type="text" size="30" name="endtime" value="'.$parameters['endtime'].'" id="endtime" readonly>
			<img src='.S_URL.'/admin/images/time.gif onClick="getDatePicker(\'endtime\',event,21)"/></td></tr></table>';
	} else {
		echo '<th>'.$alang['ad_title_type'].'</th><td>'.getselectstr('pagestyle', $searcharr, $thevalue['style'], ' onchange="changepagetype(this)"').'</td></tr>
		<tr><th>'.$alang['ad_title_subject'].'</th><td><input type="text" size="30" name="title" value="'.$thevalue['title'].'"></td></tr><tr id="style_type" style="display:'.$style_type.'"><th>'.$alang['ad_type'].'</th><td><select name="type[]" size="10"  multiple="multiple"><option value="all" '.$checkall.'>'.$alang['ad_type_all'].'</option><option value="indexad" '.$checkindexad.'>&nbsp;&nbsp;>>'.$alang['ad_adtype_index_page'].'</option><option value="space" '.$checkspacead.'>&nbsp;&nbsp;>>'.$alang['ad_adtype_space'].'</option>';
		foreach($_SSCONFIG['channel'] as $key=>$value) {
			$selected = empty(${$key}) ? '' : 'selected';
			if(empty($value['name'])) {
				echo "<option value='$key' $selected>&nbsp;&nbsp;>>".$lang[$value['nameid']]."</option>";
			} else {
				echo "<option value='$key' $selected>&nbsp;&nbsp;>>".$value['name']."</option>";
			}
		}
		echo '</select></td></tr>
			<tr id="style_pagetype" style="display:'.$style_pagetype.'"><th>'.$alang['ad_page'].'</th><td>
			<input id="id_view_1" type="checkbox" name="pagetype[]" value="1" '.$typecheck1.'>'.$alang['ad_adtype_page_one'].'&nbsp;&nbsp;
			<input id="id_view_2" type="checkbox" name="pagetype[]" value="2" '.$typecheck2.'>'.$alang['ad_adtype_page_two'].'&nbsp;&nbsp;
			<input id="id_view_3" type="checkbox" name="pagetype[]" value="3" '.$typecheck3.'>'.$alang['ad_adtype_page_view'].
			'</td></tr>
			<tr><th>'.$alang['ad_title_dateline'].'</th><td>
			<input type="text" size="30" name="starttime" value="'.$parameters['starttime'].'" id="starttime" readonly>
			<img src='.S_URL.'/admin/images/time.gif onClick="getDatePicker(\'starttime\',event,21)"/></td></tr>
			<tr><th>'.$alang['ad_title_endtime'].'</th><td>
			<input type="text" size="30" name="endtime" value="'.$parameters['endtime'].'" id="endtime" readonly>
			<img src='.S_URL.'/admin/images/time.gif onClick="getDatePicker(\'endtime\',event,21)"/></td></tr>
			<tbody id="pageout_style" style="display:'.$pageout_style.'">
			<tr><th>'.$alang['ad_pageout_width'].'</th><td>
			<input type="text" size="10" name="outwidth" value="'.$parameters['outwidth'].'">px</td></tr>
			<tr><th>'.$alang['ad_pageout_height'].'</th><td>
			<input type="text" size="10" name="outheight" value="'.$parameters['outheight'].'">px</td></tr>
			</tbody>
			</table><br />';
	}
	echo '<h2>'.$alang['ad_adtype_code'].'</h2>
		<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
		<tbody id="style_js" style="display: '.$stylearr['js'].'">
		<tr><th>'.$alang['ad_adtype_js_code'].'</th><td>
					<textarea name="adjscontent" style="width:98%;" rows="10">'.$parameters['adjscontent'].'</textarea></td></tr></tbody>
		<tbody id="style_iframe" style="display: '.$stylearr['iframe'].'"><tr><th>'.$alang['ad_adtype_iframeheight'].'</th><td>
		<input type="text" size="10" name="iframewidth" value="'.$parameters['iframewidth'].'"> px</td></tr>' .
			'<tr><th>'.$alang['ad_adtype_iframewidth'].'</th><td>
			<input type="text" size="10" name="iframeheight" value="'.$parameters['iframeheight'].'"> px</td></tr>' .
					'<tr><th>'.$alang['ad_adtype_code'].'</th><td>
					<textarea name="adiframecontent" style="width:98%;" rows="10">'.$parameters['adiframecontent'].'</textarea>
					</td></tr>
		</tbody>
		<tbody id="style_text" style="display: '.$stylearr['text'].'"><tr><th>'.$alang['ad_adtype_textcontent'].'</th><td>
		<input type="text" size="60" name="textcontent" value="'.$parameters['textcontent'].'"></td></tr>' .
			'<tr><th>'.$alang['ad_adtype_texturl'].'</th><td>
			<input type="text" size="60" name="texturl" value="'.$parameters['texturl'].'"></td></tr>' .
			'<tr><th>'.$alang['ad_adtype_textsize'].'</th><td>
			<input type="text" size="10" name="textsize" value="'.$parameters['fontsize'].'"> px</td></tr>
		</tbody>
		<tbody id="style_image" style="display: '.$stylearr['image'].'"><tr><th>'.$alang['ad_adtype_imagesrc'].'</th><td>
		<input type="text" size="60" name="imagesrc" value="'.$parameters['imagesrc'].'"></td></tr>' .
			'<tr><th>'.$alang['ad_adtype_imageurl'].'</th><td>
			<input type="text" size="60" name="imageurl" value="'.$parameters['imageurl'].'"></td></tr>' .
			'<tr><th>'.$alang['ad_adtype_imagewidth'].'</th><td>
			<input type="text" size="10" name="imagewidth" value="'.$parameters['imagewidth'].'"> px</td></tr>' .
			'<tr><th>'.$alang['ad_adtype_imageheight'].'</th><td>
			<input type="text" size="10" name="imageheight" value="'.$parameters['imageheight'].'"> px</td></tr>' .
			'<tr><th>'.$alang['ad_adtype_imagetext'].'</th><td>
			<input type="text" size="60" name="imagetext" value="'.$parameters['imagetext'].'"></tr></tbody>
		<tbody id="style_flash" style="display: '.$stylearr['flash'].'"><tr><th>'.$alang['ad_adtype_flashsrc'].'</th><td>
		<input type="text" size="60" name="flashsrc" value="'.$parameters['flashsrc'].'"></td></tr>' .
			'<tr><th>'.$alang['ad_adtype_flashwidth'].'</th><td>
			<input type="text" size="10" name="flashwidth" value="'.$parameters['flashwidth'].'"></td></tr>' .
			'<tr><th>'.$alang['ad_adtype_flashheight'].'</th><td>
			<input type="text" size="10" name="flashheight" value="'.$parameters['flashheight'].'"></td></tr>
		</tbody>
		<tbody id="style_code" style="display: '.$stylearr['code'].'"><tr><th>'.$alang['ad_adtype_js_code'].'</th><td>
		<textarea name="adcodecontent" style="width:98%;" rows="10">'.$parameters['adcodecontent'].'</textarea></td></tr></tbody>
		<tbody id="style_echo" style="display: '.$stylearr['echo'].'"><tr><th>'.$alang['ad_adtype_js_code'].'</th><td>
		<textarea name="adechocontent" style="width:98%;" rows="10">'.$parameters['adechocontent'].'</textarea></td></tr></tbody>
		</table>
		<div class="buttons">
		'.$isupdate.'
		<input type="submit" name="addadsubmit" value="'.$alang['common_submit'].'" class="submit">
		<input type="reset"  value="'.$alang['common_reset'].'">
		</div>
		</form>';
}
?>