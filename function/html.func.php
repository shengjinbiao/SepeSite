<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: html.func.php 12799 2009-07-21 02:31:04Z zhaolei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

/**
 * 用于后台HTML的显示调用函数。
 *
 * @param array $showarr:
 * return
*/
function label($showarr, $isall = 1) {
	global $_SGLOBAL, $_SCONFIG, $alang, $lang;
	
	$thetext = $htmltext = $thelang = '';

	if(!empty($showarr['alang'])) {
		if(isset($alang[$showarr['alang']])) {
			$thelang = $alang[$showarr['alang']];			
		} else {
			$thelang = $showarr['alang'];
		}
	} elseif (!empty($showarr['lang'])) {
		if(isset($lang[$showarr['lang']])) {
			$thelang = $lang[$showarr['lang']];
		} else {
			$thelang = $showarr['lang'];
		}
	}
	
	if(!isset($showarr['name'])) $showarr['name'] = '';
	if(!isset($showarr['size'])) $showarr['size'] = 30;
	if(!isset($showarr['maxlength'])) $showarr['maxlength'] = '';
	if(!isset($showarr['value'])) $showarr['value'] = '';
	if(!isset($showarr['values'])) $showarr['values'] = array();
	if(!isset($showarr['options'])) $showarr['options'] = array();
	if(!isset($showarr['other'])) $showarr['other'] = '';
	if(!isset($showarr['display'])) $showarr['display'] = '';
	if(!isset($showarr['hots'])) $showarr['hots'] = array();
	if(!isset($showarr['lasts'])) $showarr['lasts'] = array();
	if(!isset($showarr['btnname'])) $showarr['btnname'] = '';
	if(!isset($showarr['title'])) $showarr['title'] = '';
	if(!isset($showarr['mode'])) $showarr['mode'] = '0';
	if(!isset($showarr['cols'])) $showarr['cols'] = '';
	if(!isset($showarr['fileurl'])) $showarr['fileurl'] = '';

	switch ($showarr['type']) {
		case 'input':
			$thetext = '<input name="'.$showarr['name'].'" type="text" id="'.$showarr['name'].'" size="'.$showarr['size'].'" value="'.$showarr['value'].'"'.$showarr['other'].' />';
			break;
		case 'file':
			$thetext = '<input name="'.$showarr['name'].'" type="file" id="'.$showarr['name'].'" size="'.$showarr['size'].'" '.$showarr['other'].' />'
						."\n".'<input name="'.$showarr['name'].'_value" type="hidden" id="'.$showarr['name'].'_value" value="'.$showarr['value'].'" />';
			if(!empty($showarr['value'])) {
				$thetext .= "\n".'<div id="'.$showarr['name'].'_dv"><a href="'.$showarr['fileurl'].'" target="_blank">'.$showarr['value'].'</a>&nbsp;<a href="javascript:;" title="Delete" onclick="document.getElementById(\''.$showarr['name'].'_value\').value=\'\'; this.parentNode.parentNode.removeChild(this.parentNode);">'.$lang['delete'].'</a></div>';
			}
			break;
		case 'password':
			$thetext = '<input name="'.$showarr['name'].'" type="password" id="'.$showarr['name'].'" size="'.$showarr['size'].'" value="'.$showarr['value'].'"'.$showarr['other'].' />';
			break;
		case 'input2':
			if(!isset($showarr['value'][0])) $showarr['value'][0] = '';
			if(!isset($showarr['value'][1])) $showarr['value'][1] = '';
			$thetext = '<input name="'.$showarr['name'].'[]" type="text" id="'.$showarr['name'].'0" size="'.$showarr['size'].'" value="'.$showarr['value'][0].'"'.$showarr['other'].' />';
			$thetext .= ' ~ ';
			$thetext .= '<input name="'.$showarr['name'].'[]" type="text" id="'.$showarr['name'].'1" size="'.$showarr['size'].'" value="'.$showarr['value'][1].'"'.$showarr['other'].' />';
			break;
		case 'edit':
			$showarr['value'] = addcslashes($showarr['value'], '/"\\');
			$showarr['value'] = str_replace("\r", '\r', $showarr['value']);
			$showarr['value'] = str_replace("\n", '\n', $showarr['value']);
			$text = '
			<script type="text/javascript">
			function init() {
				et = new word("'.$showarr['name'].'", "'.$showarr['value'].'", '.$showarr['mode'].', '.$showarr['op'].');
			}
			if(window.Event) {
				window.onload = init;
			} else {
				init();
			}
			</script>
			';
			$htmltext .= '<tr><td><div id="fulledit" class="editerTextBox"><div id="'.$showarr['name'].'" class="editerTextBox"></div></div>'.$text.'</td></tr>';
			break;
		case 'textarea':
			$thetext = '<textarea name="'.$showarr['name'].'" style="width:98%;" rows="'.$showarr['rows'].'"'.$showarr['other'].'>'.$showarr['value'].'</textarea>';
			break;
		case 'select':
			$thetext = '<select name="'.$showarr['name'].'" id="'.$showarr['name'].'"'.$showarr['other'].'>'."\n";
			foreach ($showarr['options'] as $tmpkey => $tmpvalue) {
				if(strlen($showarr['value']) > 0 && $tmpkey == $showarr['value']) {
					$tmpselected = ' selected';
				} else {
					$tmpselected = '';
				}
				$thetext .= '<option value="'.$tmpkey.'"'.$tmpselected.'>'.shtmlspecialchars($tmpvalue).'</option>'."\n";
			}
			$thetext .= '</select>'."\n";
			break;
		case 'select-div':
			if(!empty($showarr['radio'])) {
				$thetext = '<select name="'.$showarr['name'].'" id="'.$showarr['name'].'"'.$showarr['other'].'>'."\n";
				foreach ($showarr['options'] as $okey => $ovalue) {
					$name = $ovalue['name'];
					if(strlen($showarr['value']) > 0 && $okey == $showarr['value']) {
						$oselected = ' selected';//当前选中
					} else {
						$oselected = '';
					}
					$thetext .= '<option value="'.$okey.'"'.$oselected.'>'.$ovalue['pre'].shtmlspecialchars($name).'</option>'."\n";
				}
				$thetext .= '</select>'."\n";
				break;
			} else {
				$thetext = '<div id="div'.$showarr['name'].'" style="background-color: #FFFFFF; border: 1px solid #7F9DB9; height: 120px;overflow:auto">'."\n";
				foreach ($showarr['options'] as $tmpkey => $tmpvalue) {
					$pre = $tmpvalue['pre'];
					$pre .= '<input name="'.$showarr['name'].'[]" type="checkbox" value="'.$tmpkey.'"'.$showarr['other'].' />';
					$thetext .= '<div style="height:20px;">'.$pre.' '.$tmpvalue['name'].'</div>'."\n";
				}
				$thetext .= '</div>'."\n";
				if(!empty($showarr['value'])) {
					if(is_array($showarr['value'])) {
						$showvaluearr = $showarr['value'];
					} else {
						$showvaluearr = explode(',', $showarr['value']);
					}
					foreach ($showvaluearr as $showvalue) {
						$showvalue = trim($showvalue);
						$thetext = str_replace('value="'.$showvalue.'"', 'value="'.$showvalue.'" checked', $thetext);
					}
				}
			}
			break;
		case 'select-order':
			$scselarr = array(''=>'----', 'ASC'=>$alang['space_order_asc'], 'DESC'=>$alang['space_order_desc']);
			$thetext = '<table class="freetable">';
			for($i=0; $i<3; $i++) {
				if(!isset($showarr['order'][$i])) $showarr['order'][$i] = '';
				if(!isset($showarr['sc'][$i])) $showarr['sc'][$i] = '';
				$orderselstr = getselectstr('order[]', $showarr['options'], $showarr['order'][$i]);
				$scselstr = getselectstr('sc[]', $scselarr, $showarr['sc'][$i]);
				$thetext .= '<tr><td>'.$alang['space_order_'.$i].'</td><td>'.$orderselstr.'</td><td>'.$scselstr.'</td></tr>';
			}
			$thetext .= '</table>';
			break;
		case 'select-div-preview':
			$thetext = '<div id="div'.$showarr['name'].'" style="background-color: #F7F7F7; border: 1px solid #7F9DB9; height: 120px;overflow:auto">'."\n";
			$thetext .= '<table><tr><td valign="top" style="background: #F7F7F7;border: 0;"><div id="div1'.$showarr['name'].'" style="overflow:auto">';
			$divnote = '';
			$thejscode = '<script language="javascript">
			<!--
			function show'.$showarr['name'].'note(notekey) {
				var note=new Array();
			';			
			foreach ($showarr['options'] as $tmpkey => $tmpvalue) {
				$thejscode .= 'note['.$tmpkey.']="'.str_replace('\n', '<br>', jsstrip($tmpvalue['tplnote'])).'";'."\n";
				if(isset($showarr['id']) && $showarr['id']) {
					$inputvalue = $tmpvalue['tplid'];
				} else {
					$inputvalue = $tmpvalue['tplfilepath'];
				}
				if(strlen($showarr['value']) > 0 && $inputvalue == $showarr['value']) {
					$tmpchecked = ' checked';
					$divnote = nl2br($tmpvalue['tplnote']);
				} else {
					$tmpchecked = '';
				}
				$thetext .= '<input name="'.$showarr['name'].'" type="radio" value="'.$inputvalue.'"'.$tmpchecked.' onclick="show'.$showarr['name'].'note('.$tmpkey.')" />'.$tmpvalue['tplname']."<br>\n";
			}
			$thetext .= '</div></td>';
			$thetext .= '<td valign="top" style="background: #F7F7F7;border: 0;"><div style="background-color: #FFFCCC; overflow:auto; padding: 0.3em;" id="div2'.$showarr['name'].'">'.$divnote.'</div></td></tr></table>';
			$thetext .= '</div>';
			
			$thejscode .= '
			document.getElementById("div2'.$showarr['name'].'").innerHTML=note[notekey];
			}
			//-->
			</script>';
			$thetext = $thejscode.$thetext;
			break;
		case 'select-input':
			$optionstr = '<select name="sl'.$showarr['name'].'" onchange="changevalue(\''.$showarr['name'].'\', this.value)">';
			foreach ($showarr['options'] as $opkey => $opvalue) {
				$optionstr .= '<option value="'.$opkey.'">'.$opvalue.'</option>';
			}
			$optionstr .= '</select>';
			$optionstr = str_replace('value="'.$showarr['value'].'"', 'value="'.$showarr['value'].'" selected', $optionstr);
			$thetext = '<input name="'.$showarr['name'].'" type="text" id="'.$showarr['name'].'" size="'.$showarr['size'].'" value="'.$showarr['value'].'" /> ';
			$thetext .= $optionstr;
			break;
		case 'upload':
			$count = count($showarr['values']);
			if(empty($showarr['noinsert'])) {
				$showarr['noinsert'] = 0;
				$inserthtml = getuploadinserthtml($showarr['values']);
			} else {
				$inserthtml = getuploadinserthtml($showarr['values'], 1);
			}
			
			if(empty($showarr['allowtype'])) $showarr['allowtype'] = '';

			$thetext = '
			<div id="uploadbox">
			<div class="tabs">
			<a id="localuploadtab" href="javascript:;" onclick="hideshowtags(\'uploadbox\', \'localupload\');" class="current">'.$alang['html_func_thumb_local'].'</a><a id="remoteuploadtab" href="javascript:;" onclick="hideshowtags(\'uploadbox\', \'remoteupload\');">'.$alang['html_func_thumb_remote'].'</a>';
			if($showarr['allowmax'] > 1) {
				$thetext .= '<a id="batchuploadtab" href="javascript:;" onclick="hideshowtags(\'uploadbox\', \'batchupload\');">'.$alang['html_func_thumb_batch'].'</a>';
			}
			$thetext .= '</div>
				<div id="localupload">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<th>'.$alang['html_func_thumb_select_file'].':</th>
							<td><input name="localfile" type="file" id="localfile" size="28" /></td>
							<td valign="bottom" rowspan="3" class="upbtntd"><button onclick="return uploadFile(0)">'.$alang['html_func_thumb_upload'].'</button></td>
						</tr>
						<tr>
							<th>'.$alang['html_func_thumb_explain'].':</th>
							<td><input name="uploadsubject0" type="text" size="40" /></td>
						</tr>
					</table>
				</div>
				<div id="remoteupload" style="display: none;">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<th>'.$alang['html_func_thumb_url'].':</th>
							<td><input type="text" size="40" name="remotefile" value="http://" /></td>
							<td valign="bottom" rowspan="2" class="upbtntd"><button onclick="return uploadFile(1)" />'.$alang['html_func_thumb_upload'].'</button></td>
						</tr>
						<tr>
							<th>'.$alang['html_func_thumb_explain'].':</th>
							<td><input name="uploadsubject1" type="text" size="40" /></td>
						</tr>
					</table>
				</div>
				<div id="batchupload" style="display: none;">
					<table summary="" cellpadding="0" cellspacing="6" border="0" width="100%">
						<tr>
							<td><span id="batchdisplay"><input size="28" class="fileinput" id="batch_1" name="batchfile[]" onchange="insertimg(this)" type="file" /></span></td>
							<td class="upbtntd" align="right">
							<button id="doupfile" onclick="return uploadFile(2)">'.$alang['html_func_thumb_upload'].'</button>
							</td>
						<tr>
							<td colspan="2">
							<div id="batchpreview"></div>
							</td>
						</tr>
						<tr>
							<td colspan="2">
							<button id="delall" name="delall" onclick="return delpic()" style="background: transparent; border: none; cursor: pointer; color: red; " >'.$alang['spaceimage_delete_images'].'</button>
							</td>
						</tr>
					</table>
				</div>

				<p class="textmsg" id="divshowuploadmsg" style="display:none"></p>
				<p class="textmsg succ" id="divshowuploadmsgok" style="display:none"></p>
				<input type="hidden" id="uploadallowmax" name="uploadallowmax" value="'.$showarr['allowmax'].'">
				<input type="hidden" name="uploadallowtype" value="'.$showarr['allowtype'].'">
				<input type="hidden" name="thumbwidth" value="'.$showarr['thumb'][0].'">
				<input type="hidden" name="thumbheight" value="'.$showarr['thumb'][1].'">
				<input type="hidden" name="noinsert" value="'.$showarr['noinsert'].'">
			</div>
			';
			
			$thetext .= '<div id="divshowupload">'.$inserthtml.'</div>';
			break;
		case 'uploadpic':
			$thetext = '
			<script type="text/javascript">
			<!--
			var maxWidth=110;
			var maxHeight=120;
			var fileTypes=["jpg","gif"];
			var outImage="previewField";
			var defaultPic="'.$showarr['thumb'].'";
			var globalPic;
		
			function preview(what){
				var source=what.value;
				var ext=source.substring(source.lastIndexOf(".")+1,source.length).toLowerCase();
				for (var i=0; i<fileTypes.length; i++) if (fileTypes[i]==ext) break;
				globalPic=new Image();
				if (i<fileTypes.length) globalPic.src=source;
				else {
					globalPic.src=defaultPic;
					what.outerHTML = what.outerHTML.replace(/value=\w/g,"");
					alert("'.$lang['document_type_error'].': "+fileTypes.join(", "));
				}
				setTimeout("applyChanges()",200);
			}
			
			function applyChanges(){
				var field=document.getElementById(outImage);
				var x=parseInt(globalPic.width);
				var y=parseInt(globalPic.height);
				if (x>maxWidth) {
					y*=maxWidth/x;
					x=maxWidth;
				}
				if (y>maxHeight) {
					x*=maxHeight/y;
					y=maxHeight;
				}
				field.style.display=(x<1 || y<1)?"none":"";
				field.src=globalPic.src;
				field.width=x;
				field.height=y;
			}
			//-->
			</script>
			<img alt="preview" id="previewField" src="'.$showarr['thumb'].'" width="110" height="120" ><br />
			<input type="file" name="'.$showarr['name'].'" id="'.$showarr['name'].'" onchange="preview(this)" size="15" >';
			break;
		case 'tag':
			$nowstr = '';
			$showvaluearr = array();
			if(!empty($showarr['values'])) {
				if(is_array($showarr['values'])) {
					$showvaluearr = $showarr['values'];
				} else {
					$showvaluearr = explode(',', $showarr['values']);
				}
			}			
			if(!empty($showvaluearr)) {
				foreach ($showvaluearr as $showvalue) {
					$nowstr .= '<input type="button" name="tagnamebtn[]" value="'.$showvalue.'" onclick="deletetag(this)"><input type="hidden" name="tagname[]" id="tagnameid'.$showvalue.'" value="'.$showvalue.'">';
				}
			}
			$hotsrt = $comma = '';
			if(!empty($showarr['hots']) && is_array($showarr['hots'])) {
				foreach ($showarr['hots'] as $showvalue) {
					$hotsrt .= $comma.'<a href="javascript:;" onclick="addtagname(\''.$showvalue['tagname'].'\', \'tagtext\')">'.$showvalue['tagname'].'</a>';
					$comma = '&nbsp;&nbsp;';
				}
			}
			
			$lastsrt = $comma = '';
			if(!empty($showarr['lasts']) && is_array($showarr['lasts'])) {
				foreach ($showarr['lasts'] as $showvalue) {
					$lastsrt .= $comma.'<a href="javascript:;" onclick="addtagname(\''.$showvalue['tagname'].'\', \'tagtext\')">'.$showvalue['tagname'].'</a>';
					$comma = '&nbsp;&nbsp;';
				}
			}
			
			$thetext = '<table class="freetable">';
			$thetext .= '<tr><td><table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" style="table-layout: fixed; word-wrap: break-word"><tr><td>
			<div id="tagtext">'.$nowstr.'</div></td></tr></table></td></tr>';
			$thetext .= '<tr><td><input type="text" name="newtagname" id="newtagname" size="15" value="" onKeyDown="if(event.keyCode==13) {addtag(\'newtagname\', \'tagtext\');return false;}">&nbsp;<input type="button" name="btaddtag" value="'.$alang['space_add_tag'].'" onclick="addtag(\'newtagname\', \'tagtext\')"></td></tr>';
			if(!empty($hotsrt)) $thetext .= '<tr><td>'.$alang['space_hot_tag'].': '.$hotsrt.'</td></tr>';
			if(!empty($lastsrt)) $thetext .= '<tr><td>'.$alang['space_last_tag'].': '.$lastsrt.'</td></tr>';
			$thetext .= '</table>';
			break;
		case 'radio':
			$thetext = '';
			foreach ($showarr['options'] as $tmpkey => $tmpvalue) {
				if(strlen($showarr['value']) > 0 && $tmpkey == $showarr['value']) {
					$tmpchecked = ' checked';
				} else {
					$tmpchecked = '';
				}
				$thetext .= '<input name="'.$showarr['name'].'" type="radio" value="'.$tmpkey.'"'.$tmpchecked.$showarr['other'].' />'.$tmpvalue.'&nbsp;&nbsp;';
			}
			break;
		case 'checkbox':
			$thetext = '';
			$i=0;
			$thetext = '<table class="freetable"><tr>';
			foreach ($showarr['options'] as $tmpkey => $tmpvalue) {
				$thetext .= '<td><input name="'.$showarr['name'].'[]" type="checkbox" value="'.$tmpkey.'"'.$showarr['other'].' />'.$tmpvalue.'</td>';
				if($i%5==4) $thetext .= '</tr><tr>';
				$i++;
			}
			$thetext .= '</tr></table>';
			if(!empty($showarr['value'])) {
				if(is_array($showarr['value'])) {
					$showvaluearr = $showarr['value'];
				} else {
					$showvaluearr = explode(',', $showarr['value']);
				}
				foreach ($showvaluearr as $showvalue) {
					$showvalue = trim($showvalue);
					$thetext = str_replace('value="'.$showvalue.'"', 'value="'.$showvalue.'" checked', $thetext);
				}
			}
			break;
		case 'date':
			$datearr = array(
				'0' => $alang['space_date_null'],
				'86400' => $alang['space_date_day_1'],
				'172800' => $alang['space_date_day_2'],
				'604800' => $alang['space_date_week_1'],
				'1209600' => $alang['space_date_week_2'],
				'2592000' => $alang['space_date_month_1'],
				'7948800' => $alang['space_date_month_3'],
				'15897600' => $alang['space_date_month_6'],
				'31536000' => $alang['space_date_year_1']
			);
			$thetext = getselectstr($showarr['name'], $datearr, $showarr['value']);
			break;
		case 'time':
			$thetext = '<input name="'.$showarr['name'].'" readonly type="text" id="'.$showarr['name'].'" value="'.$showarr['value'].'"/><img src="'.S_URL.'/admin/images/time.gif" onClick="getDatePicker(\''.$showarr['name'].'\',event,21)"/>';
			break;
		case 'hidden':
			$htmltext = '<tr><td colspan="2" style="display:none"><input name="'.$showarr['name'].'" type="hidden" value="'.$showarr['value'].'"'.$showarr['other'].' /></td></tr>';
			break;
		case 'title':
			$htmltext = '<h2>'.$thelang.'</h2>';
			break;
		case 'help':
			$htmltext = '<table cellspacing="2" cellpadding="2" class="helptable"><tr><td>'.$showarr['text'].'</td></tr></table>';
			break;
		case 'table-start':
			if($showarr['name']) {
				$tblid = ' id="'.$showarr['name'].'"';
			} else {
				$tblid = '';
			}
			if(isset($showarr['class'])) {
				$class = ' class="'.$showarr['class'].'"';
			} else {
				$class = ' class="maintable"';
			}
			$htmltext = '<table cellspacing="0" cellpadding="0" width="100%" '.$tblid.$class.'>'."\n";
			break;
		case 'table-end':
			$htmltext = '</table>'."\n";
			break;
		case 'div-start':
			if(empty($_SGLOBAL['_tmp_div_num'])) {
				$_SGLOBAL['_tmp_div_num'] = 1;
			} elseif($_SGLOBAL['_tmp_div_num'] == 1) {
				$_SGLOBAL['_tmp_div_num'] = 2;
			} elseif($_SGLOBAL['_tmp_div_num'] == 2) {
				$_SGLOBAL['_tmp_div_num'] = 3;
			} else {
				$_SGLOBAL['_tmp_div_num'] = 1;
			}
			$htmltext = '<div class="colorarea0'.$_SGLOBAL['_tmp_div_num'].'">'."\n";
			break;
		case 'div-end':
			$htmltext = '</div>'."\n";
			break;
		case 'form-start':
			$htmltext = '<form method="post" name="'.$showarr['name'].'" id="theform" action="'.$showarr['action'].'" enctype="multipart/form-data"'.$showarr['other'].'>'."\n".
						'<input type="hidden" name="formhash" value="'.formhash().'">'."\n";
			break;
		case 'form-end':
			$htmltext = '</form>'."\n";
			break;
		case 'button-submit':
			$htmltext = '<input type="submit" name="'.$showarr['name'].'" value="'.$showarr['value'].'"'.$showarr['other'].' class="submit">'."\n";
			break;
		case 'button-reset':
			$htmltext = '<input type="reset" name="'.$showarr['name'].'" value="'.$showarr['value'].'">'."\n";
			break;
		case 'text':
			$thetext = $showarr['text']."\n";
		case 'eval':
			$htmltext = $showarr['text']."\n";
			break;
		default:
			$thetext = '';
			break;
	}
	
	if(!$isall) {
		return $thetext;	
	}
	
	if(!empty($thetext)) {
		if(empty($showarr['display'])) {
			$tmpdisplay = '';
		} else {
			$tmpdisplay = ' style="display:none"';
		}
		if(empty($showarr['id'])) {
			$idname = 'tr_'.$showarr['name'];
		} else {
			$idname = $showarr['id'];
		}
		$htmltext = "".'<tr id="'.$idname.'"'.$tmpdisplay.'>'."\n".'<th>'.$thelang.'</th>'."\n".'<td>'.$thetext.'</td>'."\n".'</tr>'."\n";
	}

	return $htmltext."\n";	
}

?>