<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: model.func.php 13493 2009-11-11 06:15:33Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}
/**
 * 取得模型信息
 * return array
 */
function getmodelinfoall($type, $value) {
	if($type == 'mid') {
		$value = intval($value);
		if(empty($value)) {
			return false;
		}
	} else {
		if(empty($value) || !preg_match("/^[a-z0-9]{2,20}$/i", $value)) {
			return false;
		}
	}
	$cachefile = S_ROOT.'./cache/model/model_'.$value.'.cache.php';
	$cacheinfo = '';
	if(file_exists($cachefile)) {
		include_once($cachefile);
	}
	if(!empty($cacheinfo) && is_array($cacheinfo)) {
		return $cacheinfo;
	} else {
		include_once(S_ROOT.'./function/cache.func.php');
		return updatemodel($type, $value);
	}
}

/**
 * 获得模型信息
 */
function getmodelinfo($value, $where='mid') {
	global $_SGLOBAL;
	$resultmodels = array();
	$cacheinfo = getmodelinfoall($where, $value);
	if(empty($cacheinfo['models'])) {
		showmessage('visit_the_channel_does_not_exist');
	}

	return $cacheinfo['models'];
}

function checkvalues($valuearr, $isedit=0, $admincp=1) {
	if(!empty($valuearr)) {
		foreach ($valuearr as $value) {
			if($value['formtype'] == 'img') {
				if(!empty($_FILES[$value['fieldname']]['name'])) {
					$fileext = fileext($_FILES[$value['fieldname']]['name']);
					if(!in_array($fileext, array('jpg', 'jpeg', 'gif', 'png'))) {
						if(empty($admincp)) {
							showmessage($value['fieldcomment'].modelmsg('upload_pic_error'));
						} else {
							showmessage($value['fieldcomment'].modelmsg('upload_pic_error'));
						}
					}
				}
			}
			//判断是否是必填
			if(!empty($value['isrequired'])) {
				if(preg_match("/^(img|flash|file)$/i", $value['formtype'])) {
					if(empty($_FILES[$value['fieldname']]['name']) && $isedit == 0) {
						if(empty($admincp)) {
							showmessage($value['fieldcomment'].modelmsg('required_error'));
						} else {
							showmessage($value['fieldcomment'].modelmsg('required_error'));
						}
					}
				} else {
					if(strlen(trim($_POST[$value['fieldname']])) <= 0) {
						if(empty($admincp)) {
							showmessage($value['fieldcomment'].modelmsg('required_error'));
						} else {
							showmessage($value['fieldcomment'].modelmsg('required_error'));
						}
					}
				}
			}
			if(!preg_match("/^(img|flash|file)$/i", $value['formtype'])) {	//判断长度是否符合要求
				if(!preg_match("/^(TEXT|MEDIUMTEXT|LONGTEXT|FLOAT|DOUBLE)$/i", $value['fieldtype'])) {
					if(isset($_POST[$value['fieldname']]) && (!is_array($_POST[$value['fieldname']]) && strlen(trim($_POST[$value['fieldname']])) > 0)) {
						if($value['formtype'] != 'checkbox' && strlen($_POST[$value['fieldname']]) > $value['fieldlength']) {
							if(empty($admincp)) {
								showmessage($value['fieldcomment'].modelmsg('length_should_not_exceed').$value['fieldlength']);
							} else {
								showmessage($value['fieldcomment'].modelmsg('length_should_not_exceed').$value['fieldlength']);
							}
						}
					}
				}
			}
		}
	}
}

function getsetsqlarr($valuearr) {
	$setsqlarr = array();
	if(!empty($valuearr)) {
		foreach ($valuearr as $value) {
			if(isset($_POST[$value['fieldname']])) {
				if(!preg_match("/^(img|flash|file)$/i", $value['formtype'])) {
					//提交来后的数据过滤
					if(preg_match("/^(VARCHAR|CHAR|TEXT|MEDIUMTEXT|LONGTEXT)$/i", $value['fieldtype'])) {
						if($value['formtype'] == 'checkbox') {
							$_POST[$value['fieldname']] = implode("\n", shtmlspecialchars($_POST[$value['fieldname']]));
						}
						if(empty($value['ishtml'])) {
							$_POST[$value['fieldname']] = shtmlspecialchars(trim($_POST[$value['fieldname']]));
						} else {
							$_POST[$value['fieldname']] = trim($_POST[$value['fieldname']]);
						}
						if(!empty($value['isbbcode'])) {
							$_POST[$value['fieldname']] = modeldiscuzcode($_POST[$value['fieldname']]);
						}
					} elseif(preg_match("/^(TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT)$/i", $value['fieldtype'])) {
						$_POST[$value['fieldname']] = intval($_POST[$value['fieldname']]);
					}

					$setsqlarr[$value['fieldname']] = $_POST[$value['fieldname']];
				}
			}
		}
	}
	return $setsqlarr;
}

function getmodelhash($mid=0, $itemid=0, $pre='i') {
	$mid = str_pad($mid, 6, 0, STR_PAD_LEFT);
	$itemid = str_pad($itemid, 8, 0, STR_PAD_LEFT);
	return 'm'.$mid.$pre.$itemid;
}

function uploadfile($valuearr, $mid=0, $itemid=0, $havethumb=1, $width=100, $height=100) {
	global $_SGLOBAL;

	$setsqlarr = array();
	$hash = getmodelhash($mid, $itemid);
	if(!empty($valuearr)) {
		foreach($valuearr as $value) {
			if(preg_match("/^(img|flash|file)$/i", $value['formtype'])) {
				$filearr = $_FILES[$value['fieldname']];
				if(!empty($filearr['name'])) {
					$setsqlarr[$value['fieldname']] = array('fieldcomment' => $value['fieldcomment'], 'filepath' => '', 'error' => '', 'aid' => '');
					if(empty($filearr['size']) || empty($filearr['tmp_name'])) {
						//获取上传文件大小失败，请选择其他文件上传
						$setsqlarr[$value['fieldname']]['error'] = modelmsg('get_upload_size_error');
						break;
					}
					$fileext = fileext($filearr['name']);
					$newfilearr = savelocalfile($filearr, array($width, $height), '', $havethumb);	//上传
					if($value['formtype'] == 'img') {
						$attachinfo	= @getimagesize(A_DIR.'/'.$newfilearr['file']);
						if(empty($attachinfo) || ($attachinfo[2] < 1 && $attachinfo[2] > 3)) {
							$setsqlarr[$value['fieldname']]['error'] = modelmsg('get_upload_size_error');
							@unlink(A_DIR.'/'.$newfilearr['file']);
							if($newfilearr['thumb'] != $newfilearr['file']) {
								@unlink(A_DIR.'/'.$newfilearr['thumb']);
							}
							break;
						}
					}

					if(empty($newfilearr['file'])) {
						//上传文件失败，请您稍后尝试重新上传
						$setsqlarr[$value['fieldname']]['error'] = modelmsg('upload_error');
						break;
					}

					//数据库
					$insertsqlarr = array(
						'uid' => $_SGLOBAL['supe_uid'],
						'dateline' => $_SGLOBAL['timestamp'],
						'filename' => saddslashes($filearr['name']),
						'subject' => $value['fieldname'],
						'attachtype' => $fileext,
						'isimage' => (in_array($fileext, array('jpg','jpeg','gif','png'))?1:0),
						'size' => $filearr['size'],
						'filepath' => $newfilearr['file'],
						'thumbpath' => $newfilearr['thumb'],
						'hash' => $hash
					);
					$aid = inserttable('attachments', $insertsqlarr, 1);
					$setsqlarr[$value['fieldname']]['filepath'] = $value['formtype'] != 'file' ? $newfilearr['file'] : $aid;
					$setsqlarr[$value['fieldname']]['aid'] = $aid;
				}
			}
		}
	}
	return $setsqlarr;
}

function searchlabel($showarr) {
	global $_SGLOBAL, $_SCONFIG;

	$thetext = '';
	if(!isset($showarr['name'])) $showarr['name'] = '';
	if(!isset($showarr['size'])) $showarr['size'] = 10;
	if(!isset($showarr['maxlength'])) $showarr['maxlength'] = '';
	if(!isset($showarr['value'])) $showarr['value'] = '';
	if(!isset($showarr['options'])) $showarr['options'] = array();
	if(!isset($showarr['other'])) $showarr['other'] = '';
	if(!isset($showarr['alang'])) $showarr['alang'] = '';

	switch ($showarr['type']) {
		case 'input':
			$thetext = '<input name="'.$showarr['name'].'" type="text" id="'.$showarr['name'].'" size="'.$showarr['size'].'" value="'.$showarr['value'].'"'.$showarr['other'].' />';
			break;
		case 'select':
			$thetext = '<select name="'.$showarr['name'].'" id="'.$showarr['name'].'"'.$showarr['other'].'>'."\n";
			$thetext .= '<option value=""></option>'."\n";
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
			foreach ($showarr['options'] as $tmpkey => $tmpvalue) {
				$thetext .= '<input name="'.$showarr['name'].'[]" type="checkbox" value="'.$tmpkey.'"'.$showarr['other'].' />'.$tmpvalue.'&nbsp;';
			}
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
		case 'timestamp':
			$thetext = '<input type="text" name="'.$showarr['name'].'_from" id="'.$showarr['name'].'_from" size="18" readonly="readonly" value="'.$showarr['value'][0].'" onClick="getDatePicker(\''.$showarr['name'].'_from\', event, 21)" /><img src="'.S_URL.'/admin/images/time.gif" onClick="getDatePicker(\''.$showarr['name'].'_from\', event, 21)" />';
			$thetext .= '-- <input type="text" name="'.$showarr['name'].'_to" id="'.$showarr['name'].'_to" size="18" readonly="readonly" value="'.$showarr['value'][1].'" onClick="getDatePicker(\''.$showarr['name'].'_to\', event, 21)" /><img src="'.S_URL.'/admin/images/time.gif" onClick="getDatePicker(\''.$showarr['name'].'_to\', event, 21)" />';
			break;
		default:
			$thetext = '';
			break;
	}

	return empty($showarr['alang']) ? $thetext : $showarr['alang'].':'.$thetext;
}

function getmodelsearchsql($chararr, $intarr, $likearr, $betweenarr) {
	$resultstr = '';
	$resultarr = array();
	if(!empty($intarr)) {
		$resultarr[] = getwheresql($intarr);
	}
	if(!empty($betweenarr)) {
		foreach($betweenarr as $tmpkey => $tmpvalue) {
			if(is_array($tmpvalue)) {
				$value = array();
				$tmpvalue[0] = trim($tmpvalue[0]);
				$tmpvalue[1] = trim($tmpvalue[1]);
				if(strlen($tmpvalue[0]) > 0) {
					$value[] = $tmpkey.' >= \''.$tmpvalue[0].'\'';
				}
				if(strlen($tmpvalue[1]) > 0) {
					$value[] = $tmpkey.' <= \''.$tmpvalue[1].'\'';
				}
				if(!empty($value)) {
					$resultarr[] = implode(' AND ', $value);
				}
			}
		}
	}
	if(!empty($chararr)) {
		$resultarr[] = getwheresql($chararr);
	}
	if(!empty($likearr)) {
		foreach($likearr as $tmpkey => $tmpvalue) {
			if(is_array($tmpvalue)) {
				foreach ($tmpvalue as $value) {
					$value = trim($value);
					if(strlen($value) > 0) {
						$resultarr[] = $tmpkey.' like \'%'.$value.'%\'';
					}
				}
			} else {
				$resultarr[] = $tmpkey.' like \'%'.$tmpvalue.'%\'';
			}
		}
	}
	$resultstr = implode(' AND ', $resultarr);
	if(empty($resultstr)) $resultstr = '1';
	return $resultstr;
}

//删除附件
function delattachments($hash, $mode='LIKE') {
	global $_SGLOBAL;

	$filearr = array();

	if($mode=='LIKE') {
		$query = $_SGLOBAL['db']->query('SELECT filepath, thumbpath FROM '.tname('attachments').' WHERE hash LIKE \''.$hash.'%\'');
	} elseif($mode=='IN') {
		$query = $_SGLOBAL['db']->query('SELECT filepath, thumbpath FROM '.tname('attachments').' WHERE hash IN ('.$hash.')');
	}
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(!empty($value['filepath'])) {
			$filearr[] = $value['filepath'];
		}
		if(!empty($value['thumbpath'])) {
			$filearr[] = $value['thumbpath'];
		}
	}
	if(!empty($filearr)) {
		$filearr = array_unique($filearr);
		foreach($filearr as $tmpvalue) {
			if(file_exists(A_DIR.'/'.$tmpvalue)) {
				@unlink(A_DIR.'/'.$tmpvalue);
			}
		}
	}
	if($mode=='LIKE') {
		$_SGLOBAL['db']->query('DELETE FROM '.tname('attachments').' WHERE hash LIKE \''.$hash.'%\'');	//删除附件表
	} elseif($mode=='IN') {
		$_SGLOBAL['db']->query('DELETE FROM '.tname('attachments').' WHERE hash IN ('.$hash.')');
	}

}

function sstrip_tags($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = sstrip_tags($val);
		}
	} else {
		$string = strip_tags($string);
	}
	return $string;
}

function modeldiscuzcode($message, $operate='en') {
	global $discuzcodes;

	if($operate == 'en') {
		if(empty($discuzcodes['searcharray'])) {
			$discuzcodes['searcharray']['bbcode_regexp'] = array(
				"/\[url\]\s*(www.|https?:\/\/|ftp:\/\/|gopher:\/\/|news:\/\/|telnet:\/\/|rtsp:\/\/|mms:\/\/|callto:\/\/|bctp:\/\/|ed2k:\/\/|thunder:\/\/|synacast:\/\/){1}([^\[\"']+?)\s*\[\/url\]/ie",
				"/\[url=www.([^\[\"']+?)\](.+?)\[\/url\]/is",
				"/\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/is",
				"/\[email\]\s*([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\s*\[\/email\]/i",
				"/\[email=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\](.+?)\[\/email\]/is",
				"/\[color=([#\w]+?)\]/i",
				"/\[size=(\d+?)\]/i",
				"/\[size=(\d+(\.\d+)?(px|pt|in|cm|mm|pc|em|ex|%)+?)\]/i",
				"/\[font=([^\[\<]+?)\]/i",
				"/\[align=(left|center|right)\]/i",
				"/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is",
				"/\[img=(\d{1,3})[x|\,](\d{1,3})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is"
			);
			$discuzcodes['replacearray']['bbcode_regexp'] = array(
				"cuturl('\\1\\2')",
				"<a href=\"http://www.\\1\" target=\"_blank\">\\2</a>",
				"<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>",
				"<a href=\"mailto:\\1@\\2\">\\1@\\2</a>",
				"<a href=\"mailto:\\1@\\2\">\\3</a>",
				"<font color=\"\\1\">",
				"<font size=\"\\1\">",
				"<font style=\"font-size: \\1\">",
				"<font face=\"\\1 \">",
				"<p align=\"\\1\">",
				"<img src=\"\\1\" border=\"0\">",
				"<img width=\"\\1\" height=\"\\2\" src=\"\\3\" border=\"0\">"
			);

			$discuzcodes['searcharray']['bbcode_str'] = array(
				'[/color]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]',
				'[i]', '[/i]', '[u]', '[/u]'
			);

			$discuzcodes['replacearray']['bbcode_str'] = array(
				'</font>', '</font>', '</font>', '</p>', '<strong>', '</strong>', '<i>',
				'</i>', '<u>', '</u>'
			);
		}

		@$message = str_replace($discuzcodes['searcharray']['bbcode_str'], $discuzcodes['replacearray']['bbcode_str'],
				preg_replace($discuzcodes['searcharray']['bbcode_regexp'], $discuzcodes['replacearray']['bbcode_regexp'], $message));
		return nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
	} else {
			$discuzcodes['searcharray']['bbcode_regexp'] = array(
				"/\<a href=\"([^\"]*)\" target=\"_blank\">([^\<]*)\<\/a\>/is",
				"/\<a href=\"mailto:([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\">([^\<]*)\<\/a\>/is",
				"/\<font color=\"([#\w]+?)\"\>([^\<]*)\<\/font\>/i",
				"/\<font size=\"(\d+?)\"\>([^\<]*)\<\/font\>/i",
				"/\<font style=\"font-size: (\d+(\.\d+)?(px|pt|in|cm|mm|pc|em|ex|%)+?)\"\>([^\<]*)\<\/font\>/i",
				"/\<font face=\"([^\"\[\<]+?)\"\>([^\<]*)\<\/font\>/i",
				"/\<p align=\"(left|center|right)\"\>/i",
				"/\<img src=\"([^\"\[\<\r\n]+?)\" border=\"0\">/is",
				"/\<img width=\"(\d{1,3})\" height=\"(\d{1,3})\" src=\"([^\"\[\<\r\n]+?)\" border=\"0\">/is"

			);
			$discuzcodes['replacearray']['bbcode_regexp'] = array(
				"[url=\\1]\\2[/url]",
				"[email=\\1@\\2]\\3[/email]",
				"[color=\\1]\\2[/color]",
				"[size=\\1]\\2[/size]",
				"[size=\\1]\\2[/size]",
				"[font=\\1]\\2[/font]",
				"[align=\\1]",
				"[img]\\1[/img]",
				"[img=\\1,\\2]\\3[/img]"
			);

			$discuzcodes['searcharray']['bbcode_str'] = array(
				'</p>', '<strong>', '</strong>', '<i>', '</i>', '<u>', '</u>'
			);

			$discuzcodes['replacearray']['bbcode_str'] = array(
				'[/align]', '[b]', '[/b]', '[i]', '[/i]', '[u]', '[/u]'
			);

		@$message = str_replace($discuzcodes['searcharray']['bbcode_str'], $discuzcodes['replacearray']['bbcode_str'],
				preg_replace($discuzcodes['searcharray']['bbcode_regexp'], $discuzcodes['replacearray']['bbcode_regexp'], $message));
		return preg_replace("/\<br[^\>]*\>/i", "", $message);
	}
}

function writemodelvalidate($type, $value) {
	global $alang;
	$validatearr = array();
	$validatestr = '';
	$cacheinfo = getmodelinfoall($type, $value);
	if(!empty($cacheinfo['fielddefault']['subject'])) $alang['model_validate_subject'] = $cacheinfo['fielddefault']['subject'];
	if(!empty($cacheinfo['fielddefault']['subjectimage'])) $alang['model_validate_subjectimage'] = $cacheinfo['fielddefault']['subjectimage'];
	if(!empty($cacheinfo['fielddefault']['catid'])) $alang['model_validate_categories'] = $cacheinfo['fielddefault']['catid'];
	$validatearr['fieldinfo'] = array(
		'new Array(\'subject\', \''.$alang['model_validate_subject'].'\', \'text\', \'80\', \'1\', \'CHAR\')',
		'new Array(\'catid\', \''.$alang['model_validate_categories'].'\', \'select\', \'6\', \'1\', \'SMALLINT\')',
		'new Array(\'subjectimage\', \''.$alang['model_validate_subjectimage'].'\', \'img\', \'80\', \'0\', \'CHAR\')'
	);
	if(!empty($cacheinfo['columns'])) {
		foreach($cacheinfo['columns'] as $tmpvalue) {
			$issign = false;
			if($tmpvalue['formtype'] == 'checkbox' && $tmpvalue['isrequired'] == 1) {
				$issign = true;
			} else {
				if(preg_match("/^(select|linkage|radio|timestamp|file)$/i", $tmpvalue['formtype'])) {
					if($tmpvalue['isrequired'] == 1) {
						$issign = true;
					}
				} else {
					$issign = true;
				}
			}
			if($issign) {
				$validatearr['fieldinfo'][] = "new Array('$tmpvalue[fieldname]', '$tmpvalue[fieldcomment]', '$tmpvalue[formtype]', '$tmpvalue[fieldlength]', '$tmpvalue[isrequired]', '$tmpvalue[fieldtype]')";
			}

			if($tmpvalue['formtype'] == 'linkage') {
				$tmpfielddata = strim(explode("\r\n", $tmpvalue['fielddata']));
				if(!empty($tmpfielddata)) {
					foreach($tmpfielddata as $skey => $svalue) {
						if(!empty($svalue)) {
							$svalue = trim(substr($svalue, strpos($svalue, '=')+1));
							$validatearr[$tmpvalue['fieldname'].'arr'][] = 'new Array(\''.trim(substr($tmpfielddata[$skey], 0, strpos($tmpfielddata[$skey], '='))).'\', \''.$svalue.'\')';
						}
					}
				}
			}
		}
	}
	$validatestr = <<<EOF
var imageext = new Array('jpg', 'jpeg', 'gif', 'png');
var flashext = new Array('swf');

function strLen(str) {
	var charset = is_ie ? document.charset : document.characterSet;
	var len = 0;
	for(var i = 0; i < str.length; i++) {
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset.toLowerCase() == "utf-8" ? 3 : 2) : 1;
	}
	return len;
}

function fileext(filename) {
	if(filename == null || filename == '') {
		return '';
	}
	var ext = null;
	var num = filename.lastIndexOf(".");
	if(num != -1) {
		ext = filename.substring(num + 1);
	} else {
		ext = '';
	}
	return ext;
}

function isfileext(filename, extarr) {
	var ext = fileext(filename).toLowerCase();
	for(var i = 0; i < extarr.length; i++) {
		if(extarr[i] == ext){
			return true;
		}
	}
	return false;
}

function fill(setid, parentid, arr, value) {
	setid = document.getElementById(setid);
	if(setid != null) {
		setid.options[0]=new Option('$alang[model_validate_choose]','');
		opt = 0;
		if(parentid == '') {
			for(i=0;i<arr.length;i++) {
				setid.options[i+1]=new Option(arr[i][1],arr[i][0]);
				if(arr[i][1] == value) {
					opt = i+1;
				}
			}
			setid.options[opt].selected=true;
			setid.length=i+1;
		} else {
			parentcode = document.getElementById(parentid).value;
			count=1;
			if(parentcode != '') {
				for(i=0;i<arr.length;i++) {
					if(arr[i][0].toString().substring(0,parentcode.length)==parentcode.substring(0, parentcode.length)) {
						setid.options[count]=new Option(arr[i][1],arr[i][0]);
						if(value != null && arr[i][1] == value) {
							opt = count;
						}
						count=count+1;
					}
				}
			}
			setid.options[opt].selected=true;
			setid.length=count;
		}
	}
}

function validate(theform) {
	if(fieldinfo.length > 0) {
		for(i = 0; i < fieldinfo.length; i++) {
			obj = null;
			if(fieldinfo[i][2] == 'checkbox' && fieldinfo[i][4] == '1') {
				ischoose = false;
				var nodes = document.getElementsByTagName('input');
				if(nodes) {
					for(j = 0; j < nodes.length; j++) {
						var node = nodes[j];
						if (node.name == fieldinfo[i][0]+'[]') {
							if(obj == null) obj = node;
							if(node.checked == true) {
								ischoose = true;
								break;
							}

						}
					}
					if(!ischoose) {
						alert('$alang[model_validate_choose_2]'+fieldinfo[i][1]);
						obj.focus();
						return false;
					}
				}
			} else {
				ischoose = true;
				obj = document.getElementById(fieldinfo[i][0]);
				if(fieldinfo[i][4] == '1' && obj && strLen(obj.value) < 1) {
					ischoose = false;
					if(fieldinfo[i][2] == 'text' || fieldinfo[i][2] == 'textarea') {
						alert('$alang[model_validate_input_1]'+fieldinfo[i][1]);
					} else if(fieldinfo[i][2] == 'img' || fieldinfo[i][2] == 'flash' || fieldinfo[i][2] == 'file' || fieldinfo[i][2] == 'timestamp') {
						objvalue = document.getElementById(fieldinfo[i][0]+'_value');
						if(obj && strLen(objvalue.value) < 1) {
							alert('$alang[model_validate_noset]'+fieldinfo[i][1]+',$alang[model_validate_affirm]');
						} else {
							ischoose = true;
						}
					} else {
						alert("$alang[model_validate_choose_2]"+fieldinfo[i][1]);
					}
				}
				if(obj && obj.value != '') {
					if(fieldinfo[i][2] == 'text' || (fieldinfo[i][2] == 'textarea' && fieldinfo[i][3] != 0)) {
						if(fieldinfo[i][5] != 'TEXT' && fieldinfo[i][5] != 'MEDIUMTEXT' && fieldinfo[i][5] != 'LONGTEXT' && fieldinfo[i][5] != 'FLOAT' && fieldinfo[i][5] != 'DOUBLE') {
							if (strLen(obj.value) > fieldinfo[i][3]) {
								ischoose = false;
								alert('$alang[model_validate_input_2]'+fieldinfo[i][1]+'$alang[model_validate_input_3]'+strLen(obj.value)+'$alang[model_validate_input_4]'+fieldinfo[i][3]+'$alang[model_validate_input_5]');
							}
						}
					} else if(fieldinfo[i][2] == 'img' || fieldinfo[i][2] == 'flash') {
						if (!isfileext(obj.value, (fieldinfo[i][2] == 'img' ? imageext : flashext))) {
							ischoose = false;
							alert('$alang[model_validate_input_2]'+fieldinfo[i][1]+'$alang[model_validate_input_6]');
						}
					}
				}
				if(!ischoose) {
					obj.focus();
					return false;
				}
			}
		}
	}

	return true;
}

EOF;

	foreach($validatearr as $tmpkey => $tmpvalue) {
		$validatestr .= "\nvar ".$tmpkey." = new Array(\n";
		$validatestr .= implode(",\n", $tmpvalue);
		$validatestr .= "\n);\n";
	}
	$cachefile = S_ROOT.'./model/data/'.$cacheinfo['models']['modelname'].'/images/validate.js';
	writefile($cachefile, $validatestr);
}

//删除模型目录及文件
function deltree($str, $undelarr=array()) {
	$tplarr = array();
	$tplarr = sreaddir($str.'images/');
	if(!empty($tplarr)) {
		foreach($tplarr as $value) {
			if(!in_array($value, $undelarr)) {
				@unlink($str.'images/'.$value);
			}
		}
	}
	$tplarr = array();
	$tplarr = sreaddir($str);
	if(!empty($tplarr)) {
		foreach($tplarr as $value) {
			if(!in_array($value, $undelarr)) {
				@unlink($str.$value);
			}
		}
	}
	if(empty($undelarr)) {
		@rmdir($str.'images/');
		@rmdir($str);
	}
}

//删除主题
function deletemodelitems($modelname, $ids, $mid=0, $undel=0, $folder=2) {
	global $_SGLOBAL, $_SCONFIG;

	if(is_array($ids)) $ids = simplode($ids);
	if($undel) {	//移动到废件箱
		$data = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname($modelname.'items')." i, ".tname($modelname.'message')." m WHERE i.itemid IN ($ids) AND i.itemid = m.itemid");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$setsqlarr = array(
					'uid'		=>	$value['uid'],
					'mid'		=>	$mid,
					'subject'	=>	addslashes($value['subject']),
					'message'	=> addslashes(serialize($value)),
					'dateline'	=>	$_SGLOBAL['timestamp'],
					'folder'	=>	$folder
				);
			inserttable('modelfolders', $setsqlarr);
		}

		$_SGLOBAL['db']->query("DELETE FROM ".tname($modelname.'items')." WHERE itemid IN ($ids)");
		$_SGLOBAL['db']->query("DELETE FROM ".tname($modelname.'message')." WHERE itemid IN ($ids)");
		return true;
	} else {
		$modelcolumns = array();
		if(!empty($mid)) {
			$idarr = array();
			foreach(explode(',', $ids) as $tmpvalue) {
				$idarr[] = getmodelhash($mid, trim(str_replace('\'', '', $tmpvalue)));
			}
			$idarr = simplode($idarr);
			delattachments($idarr, 'IN');

			$_SGLOBAL['db']->query("DELETE FROM ".tname($modelname.'items')." WHERE itemid IN ($ids)");
			$_SGLOBAL['db']->query("DELETE FROM ".tname($modelname.'message')." WHERE itemid IN ($ids)");
			$_SGLOBAL['db']->query("DELETE FROM ".tname('spacecomments')." WHERE type='$modelname' AND itemid IN ($ids)");
			return true;

		}
		return false;
	}
}

//删除主题
function changemodelfolder($modelname, $ids, $undel=0) {
	global $_SGLOBAL, $_SCONFIG, $systemfieldarr;
	include_once(S_ROOT.'./include/model_field.inc.php');

	$_GET['mid'] = empty($_GET['mid']) ? 0 : intval($_GET['mid']);
	if(is_array($ids)) $ids = simplode($ids);
	if($undel == 1) {	//还原
		$columnsarr = $feedimg = $defaultarr = $setmessagesqlarr = $setmessagesqlnoitemidarr = array();
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelcolumns').' WHERE mid = \''.$_GET['mid'].'\'');
		while ($result = $_SGLOBAL['db']->fetch_array($query)) {
			$columnsarr[$result['fieldname']] = $result['isfixed'];
			if(empty($result['isfixed'])) $defaultarr[$result['fieldname']] = $result['fielddefault'];
			if($result['formtype'] == 'img') {
				$feedimg[] = $result['fieldname'];
			}
		}
		foreach($systemfieldarr as $tmpvalue) {
			$columnsarr[$tmpvalue['fieldname']] = $tmpvalue['isfixed'];
			if(empty($tmpvalue['isfixed'])) $defaultarr[$tmpvalue['fieldname']] = $tmpvalue['fielddefault'];
		}
		
		
		$idarr = explode(',', str_replace('\'', '', $ids));
		$uids = getuids($idarr, 'modelfolders');
		updatecredit('delnews', $uids);

		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelfolders').' i WHERE i.mid=\''.$_GET['mid'].'\' AND i.itemid IN ('.$ids.')');
		$i = $j = 0;
		$addfeed = array();
		while ($result = $_SGLOBAL['db']->fetch_array($query)) {
			$data = $setitemssqlarr = array();
			$data = unserialize($result['message']);
			$tmpdefaultarr = $defaultarr;
			if(empty($data['itemid'])) {
				$addfeed[$i] = $data;
			}
			unset($data['addfeed']);

			foreach($data as $tmpkey => $tmpvalue) {
				if($columnsarr[$tmpkey] == 1 || preg_match("/^click_/i", $tmpkey)) {
					$setitemssqlarr[$tmpkey] = $tmpvalue;
				} else {
					$tmpdefaultarr[$tmpkey] = $tmpvalue;
				}
			}

			if(empty($data['itemid'])) {
				$setmessagesqlnoitemidarr[$j] = $tmpdefaultarr;
			} else {
				$setmessagesqlarr[$i] = $tmpdefaultarr;
			}

			if(empty($data['itemid'])) {
				$setmessagesqlnoitemidarr[$j]['itemid']= $addfeed[$i]['itemid'] = inserttable($modelname.'items', $setitemssqlarr, 1);
				$oldhash = getmodelhash($_GET['mid'], $result['itemid'], 'f');
				$hash = getmodelhash($_GET['mid'], $setmessagesqlnoitemidarr[$j]['itemid']);
				$_SGLOBAL['db']->query('UPDATE '.tname('attachments').' SET hash=\''.$hash.'\' WHERE hash = \''.$oldhash.'\'');
				$j++;
			} else {
				$setmessagesqlarr[$i]['itemid'] = $data['itemid'];
				inserttable($modelname.'items', $setitemssqlarr);
				$i++;
			}
		}
		if(allowfeed()) {
			$cacheinfo = getmodelinfoall('modelname', $modelname);
			foreach($addfeed as $feedvalue) {
				if(!empty($feedvalue['addfeed']) && !empty($feedvalue['uid']) ) {
					$feed['uid'] =$feedvalue['uid'];
					$feed['username'] = $feedvalue['username'];
					$feed['icon'] = 'comment';
					$feed['title_template'] = 'feed_model_title';
					$feed['title_data'] = array('modelname'=> '<a href="'.S_URL_ALL.'/m.php?name='.$modelname.'">'.$cacheinfo['models']['modelalias'].'</a>');
					$feed['body_template'] = 'feed_model_message';
					$feed['body_data'] = array(
						'subject' => '<a href="'.geturl('action/model/name/'.$modelname.'/itemid/'.$feedvalue['itemid'], 1).'">'.$feedvalue['subject'].'</a>',
						'message' => cutstr(strip_tags(preg_replace("/\[.+?\]/is", '', $feedvalue['message'])), 150)
					);
					if(!empty($feedvalue['subjectimage'])) {
						$feed['images'][] = array('url'=>A_URL.'/'.$feedvalue['subjectimage'], 'link'=>geturl('action/model/name/'.$modelname.'/itemid/'.$feedvalue['itemid'], 1));
					} else {
						$feedbool =false;
						foreach($feedimg as $feedimgvalue) {
							if(!empty($feedvalue[$feedimgvalue])) {
								$feed['images'][] = array('url'=>A_URL.'/'.$feedvalue[$feedimgvalue], 'link'=>geturl('action/model/name/'.$modelname.'/itemid/'.$feedvalue['itemid'], 1));
								break;
							}
						}
						if(empty($feed['images'])) {
							$picurl = getmessagepic(stripslashes($feedvalue['message']));
							if(!empty($picurl)) {
								$feed['images'][] = array('url'=>$picurl, 'link'=>geturl('action/model/name/'.$modelname.'/itemid/'.$feedvalue['itemid'], 1));
							}
						}
					}

					postfeed($feed);
				}
			}
		}
		$insertvalue = $insertkey = $comma = $pre = '';
		if(!empty($setmessagesqlarr)) {
			foreach($setmessagesqlarr[0] as $tmpkey => $tmpvalue) {
				$insertkey .= $pre.$tmpkey;
				$pre = ',';
			}
			foreach($setmessagesqlarr as $tmpvalue) {
				$insertvalue .= $comma.'('.simplode($tmpvalue).')';
				$comma = ',';
			}
			$_SGLOBAL['db']->query('INSERT INTO '.tname($modelname.'message').'
						('.$insertkey.') VALUES '.$insertvalue);
		}
		$insertvalue = $insertkey = $comma = $pre = '';
		if(!empty($setmessagesqlnoitemidarr)) {
			foreach($setmessagesqlnoitemidarr[0] as $tmpkey => $tmpvalue) {
				$insertkey .= $pre.$tmpkey;
				$pre = ',';
			}
			foreach($setmessagesqlnoitemidarr as $tmpvalue) {
				$insertvalue .= $comma.'('.simplode($tmpvalue).')';
				$comma = ',';
			}
			$_SGLOBAL['db']->query('INSERT INTO '.tname($modelname.'message').'
						('.$insertkey.') VALUES '.$insertvalue);
		}
		$_SGLOBAL['db']->query("DELETE FROM ".tname('modelfolders')." WHERE itemid IN ($ids)");

	} elseif($undel == 2) {
		$_SGLOBAL['db']->query('UPDATE '.tname('modelfolders').' SET folder=2 WHERE mid=\''.$_GET['mid'].'\' AND itemid IN ('.$ids.')');
	} elseif($undel == 3) {
		$idarr = $itemid = array();
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('modelfolders').' i WHERE i.mid=\''.$_GET['mid'].'\' AND i.itemid IN ('.$ids.')');
		while ($result = $_SGLOBAL['db']->fetch_array($query)) {
			$data = array();
			$data = saddslashes(unserialize($result['message']));
			if(empty($data['itemid'])) {	//待审箱的
				$idarr[] = getmodelhash($_GET['mid'], $result['itemid'], 'f');
			} else {	//废件箱的
				$idarr[] = getmodelhash($_GET['mid'], $data['itemid']);
				$itemid[] = $data['itemid'];
			}
		}
		if(!empty($data['itemid'])) {
			$itemid = simplode($itemid);
			$_SGLOBAL['db']->query("DELETE FROM ".tname($modelname.'comments')." WHERE itemid IN ($itemid)");
			$_SGLOBAL['db']->query("DELETE FROM ".tname($modelname.'rates ')." WHERE itemid IN ($itemid)");
		}
		$idarr = simplode($idarr);
		delattachments($idarr, 'IN');
		$_SGLOBAL['db']->query("DELETE FROM ".tname('modelfolders')." WHERE itemid IN ($ids)");
	}

	return true;
}

/**
 * 模型在线投稿提交处理函数
 */
function modelpost($cacheinfo, $cp=1) {
	global $_SGLOBAL, $theurl, $_SCONFIG;

	include_once(S_ROOT.'./function/upload.func.php');

	$_POST['mid'] = !empty($_POST['mid']) ? intval($_POST['mid']) : 0;
	$itemid = !empty($_POST['itemid']) ? intval($_POST['itemid']) : 0;

	$hash = '';
	$op = 'add';
	$resultitems = $resultmessage = array();
	$modelsinfoarr = $cacheinfo['models'];
	$columnsinfoarr = $cacheinfo['columns'];

	if(empty($_POST['mid']) || $_POST['mid'] != $modelsinfoarr['mid']) {
		showmessage('parameter_error');
	}
	$feedcolum = array();
	foreach($columnsinfoarr as $result) {
		if($result['isfixed'] == 1) {
			$resultitems[] = $result;
		} else {
			$resultmessage[] = $result;
		}
		if($result['formtype'] == 'linkage') {
			if(!empty($_POST[$result['fieldname']])) {
				$_POST[$result['fieldname']] = $cacheinfo['linkage']['info'][$result['fieldname']][$_POST[$result['fieldname']]];
			}
		} elseif($result['formtype'] == 'timestamp') {
			if(empty($_POST[$result['fieldname']])) {
				$_POST[$result['fieldname']] = $_SGLOBAL['timestamp'];
			} else {
				$_POST[$result['fieldname']] = sstrtotime($_POST[$result['fieldname']]);
			}
		}
	}
	
	//更新用户最新更新时间
	if(empty($itemid) && $_SGLOBAL['supe_uid']) {
		updatetable('members', array('updatetime'=>$_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));
	}
	//输入检查
	$_POST['catid'] = intval($_POST['catid']);
	$_POST['allowreply'] = isset($_POST['allowreply']) ? intval($_POST['allowreply']) : checkperm('allowcomment') ? 1 : 0;
	$_POST['subject'] = shtmlspecialchars(trim($_POST['subject']));

	//检查输入
	if(strlen($_POST['subject']) < 2 || strlen($_POST['subject']) > 80) {
		showmessage('space_suject_length_error');
	}
	if(empty($_POST['catid'])) {
		showmessage('admin_func_catid_error');
	}

	if(!empty($_FILES['subjectimage']['name'])) {
		$fileext = fileext($_FILES['subjectimage']['name']);
		if(!in_array($fileext, array('jpg', 'jpeg', 'gif', 'png'))) {
			showmessage('document_types_can_only_upload_pictures');
		}
	}

	//数据检查
	checkvalues(array_merge($resultitems, $resultmessage), 0, 1);

	//修改时检验标题图片是否修改
	$defaultmessage = array();
	if(!empty($itemid)) {
		if(empty($_POST['subjectimage_value']) || !empty($_FILES['subjectimage']['name'])) {	//当file删除时，或修改时执行删除操作
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($modelsinfoarr['modelname'].'items').' WHERE itemid = \''.$itemid.'\'');
			$defaultmessage = $_SGLOBAL['db']->fetch_array($query);

			$hash = getmodelhash($_GET['mid'], $itemid);
			deletetable('attachments', array('hash' => $hash, 'subject' => 'subjectimage'));	//删除附件表
			updatetable($modelsinfoarr['modelname'].'items', array('subjectimage' => ''), array('itemid'=>$itemid));
			$ext = fileext($defaultmessage['subjectimage']);
			if(in_array($ext, array('jpg', 'jpeg', 'png'))) {
				@unlink(A_DIR.'/'.substr($defaultmessage['subjectimage'] , 0, strrpos($defaultmessage['subjectimage'], '.')).'.thumb.jpg');
			}
			@unlink(A_DIR.'/'.$defaultmessage['subjectimage']);
		}
	}

	//构建数据
	$setsqlarr = $setitemsqlarr = array();
	$setsqlarr = getsetsqlarr($resultitems);
	$setsqlarr['catid'] = $_POST['catid'];
	$setsqlarr['subject'] = $_POST['subject'];
	$setsqlarr['allowreply'] = $_POST['allowreply'];
	if(checkperm('managefolder') || checkperm('managemodpost')) {
		$setsqlarr['grade'] = intval($_POST['grade']);
	} else {
		$setsqlarr['grade'] = 0;
	}
	
	$setsqlarr['dateline'] = $_SGLOBAL['timestamp'];
	$setsqlarr['uid'] = $_SGLOBAL['supe_uid'];
	$setsqlarr['username'] = $_SGLOBAL['supe_username'];
	$setsqlarr['lastpost'] = $setsqlarr['dateline'];

	$modelsinfoarr['subjectimagewidth'] = 400;
	$modelsinfoarr['subjectimageheight'] = 300;
	if(!empty($modelsinfoarr['thumbsize'])) {
		$modelsinfoarr['thumbsize'] = explode(',', trim($modelsinfoarr['thumbsize']));
		$modelsinfoarr['subjectimagewidth'] = $modelsinfoarr['thumbsize'][0];
		$modelsinfoarr['subjectimageheight'] = $modelsinfoarr['thumbsize'][1];
	}

	$uploadfilearr = $ids = array();
	$subjectimageid = '';
	$uploadfilearr = uploadfile(array(array('fieldname'=>'subjectimage', 'fieldcomment'=>modelmsg('photo_title'), 'formtype'=>'img')), $_POST['mid'], 0, 1, $modelsinfoarr['subjectimagewidth'], $modelsinfoarr['subjectimageheight']);

	if(!empty($uploadfilearr)) {
		$feedsubjectimg = $uploadfilearr;
		foreach($uploadfilearr as $tmpkey => $tmpvalue) {
			if(empty($tmpvalue['error'])) {
				$setsqlarr[$tmpkey] = $tmpvalue['filepath'];
			}
			if(!empty($tmpvalue['aid'])) {
				$ids[] = $tmpvalue['aid'];
			}
		}
	}

	//词语过滤
	if(!empty($modelsinfoarr['allowfilter'])) $setsqlarr = scensor($setsqlarr, 1);
	//发布时间
	if(empty($_POST['dateline'])) {
		$setsqlarr['dateline'] = $_SGLOBAL['timestamp'];
	} else {
		$setsqlarr['dateline'] = sstrtotime($_POST['dateline']);
		if($setsqlarr['dateline'] > $_SGLOBAL['timestamp'] || $setsqlarr['dateline'] < ($_SGLOBAL['timestamp']-3600*24*365*2)) {//不能早于2年
			$setsqlarr['dateline'] = $_SGLOBAL['timestamp'];
		}
	}

	if(!checkperm('allowdirectpost') || checkperm('managemodpost')) {
		//不需要审核时入item表
		if(empty($itemid)) {
			//插入数据
			$itemid = inserttable($modelsinfoarr['modelname'].'items', $setsqlarr, 1);
		} else {
			//更新
			$op = 'update';
			unset($setsqlarr['uid']);
			unset($setsqlarr['username']);
			unset($setsqlarr['lastpost']);
			updatetable($modelsinfoarr['modelname'].'items', $setsqlarr, array('itemid'=>$itemid));
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($modelsinfoarr['modelname'].'message').' WHERE nid = \''.$_POST['nid'].'\'');
			$defaultmessage = $_SGLOBAL['db']->fetch_array($query);
		}
		$hash = getmodelhash($_POST['mid'], $itemid);
		if(!empty($ids)) {
			$ids = simplode($ids);
			$_SGLOBAL['db']->query('UPDATE '.tname('attachments').' SET hash=\''.$hash.'\' WHERE aid IN ('.$ids.')');
		}
		$do = 'pass';
	} else {
		if(!empty($uploadfilearr['subjectimage']['aid'])) {
			$subjectimageid = $uploadfilearr['subjectimage']['aid'];
		}
		$setitemsqlarr = $setsqlarr;
		$do = 'me';
	}

	if($op == 'update') {
		if(!empty($resultmessage)) {
			foreach($resultmessage as $value) {
				if(preg_match("/^(img|flash|file)$/i", $value['formtype']) && !empty($defaultmessage[$value['fieldname']])) {
					if(empty($_POST[$value['fieldname'].'_value']) || !empty($_FILES[$value['fieldname']]['name'])) {	//当file删除时，或修改时执行删除操作
						deletetable('attachments', array('hash' => $hash, 'subject' => $value['fieldname']));	//删除附件表
						updatetable($modelsinfoarr['modelname'].'message', array($value['fieldname'] => ''), array('nid'=>$_POST['nid']));
						$ext = fileext($defaultmessage[$value['fieldname']]);
						if(in_array($ext, array('jpg', 'jpeg', 'png'))) {
							@unlink(A_DIR.'/'.substr($defaultmessage[$value['fieldname']] , 0, strrpos($defaultmessage[$value['fieldname']], '.')).'.thumb.jpg');
						}
						@unlink(A_DIR.'/'.$defaultmessage[$value['fieldname']]);
					}
				}
			}
		}
	}

	//内容
	$setsqlarr = $uploadfilearr = $ids = array();
	$setsqlarr = getsetsqlarr($resultmessage);
	$uploadfilearr = $feedcolum = uploadfile($resultmessage, $_POST['mid'], $itemid, 0);
	$setsqlarr['message'] = trim($_POST['message']);
	$setsqlarr['postip'] = $_SGLOBAL['onlineip'];

	if(!empty($uploadfilearr)) {
		foreach($uploadfilearr as $tmpkey => $tmpvalue) {
			if(empty($tmpvalue['error'])) {
				$setsqlarr[$tmpkey] = $tmpvalue['filepath'];
			}
			if(!empty($tmpvalue['aid'])) {
				$ids[] = $tmpvalue['aid'];
			}
		}
	}

	//添加内容
	if(!empty($modelsinfoarr['allowfilter'])) $setsqlarr = scensor($setsqlarr, 1);
	if(!checkperm('allowdirectpost') || checkperm('managemodpost') || (checkperm('allowdirectpost') && $op == 'update')) {	//不需要审核时入message表
		if($op == 'add') {
			$setsqlarr['itemid'] = $itemid;
			//添加内容
			inserttable($modelsinfoarr['modelname'].'message', $setsqlarr);
			getreward('postinfo');

			if(allowfeed() && !empty($_POST['addfeed']) && !empty($_SGLOBAL['supe_uid'])) {
				$feed['icon'] = 'comment';
				$feed['title_template'] = 'feed_model_title';
				$murl = geturl('action/model/name/'.$modelsinfoarr['modelname'].'/itemid/'.$itemid);
				$aurl = A_URL;
				if(empty($_SCONFIG['siteurl'])) {
					$siteurl = getsiteurl();
					$murl = $siteurl.$murl;
					$aurl = $siteurl.$aurl;
				} else {
					$siteurl = S_URL_ALL;
				}
				$feed['title_data'] = array( 'modelname'=> '<a href="'.$siteurl.'/m.php?name='.$modelsinfoarr['modelname'].'">'.$modelsinfoarr['modelalias'].'</a>');
				$feed['body_template'] = 'feed_model_message';
				$feed['body_data'] = array(
					'subject' => '<a href="'.$murl.'">'.$_POST['subject'].'</a>',
					'message' => cutstr(strip_tags(preg_replace("/\[.+?\]/is", '', $_POST['message'])), 150)
				);
				if(!empty($feedsubjectimg)) {
					$feed['images'][] = array('url'=>$aurl.'/'.$feedsubjectimg['subjectimage']['filepath'], 'link'=>$murl);
				} else{
					foreach($feedcolum as $feedimgvalue) {
						if($feedimgvalue['filepath']) {
							$feed['images'][] = array('url'=>$aurl.'/'.$feedimgvalue['filepath'], 'link'=>$murl);
							break;
						}
					}
					if(empty($feed['images'])) {
						$picurl = getmessagepic(stripslashes($_POST['message']));
						if($picurl &&(strpos($picurl, '://') === false)) {
							$picurl = $siteurl.'/'.$picurl;
						}
						if(!empty($picurl)) {
							$feed['images'][] = array('url'=>$picurl, 'link'=>$murl);
						}
					}
				}

				postfeed($feed);
			}
		} else {
			//更新内容
			updatetable($modelsinfoarr['modelname'].'message', $setsqlarr, array('nid'=>$_POST['nid'], 'itemid'=>$itemid));
		}

		updatetable('attachments', array('isavailable' => '1', 'type' => 'model'), array('hash'=>$hash));
		if(checkperm('allowdirectpost') && $op == 'update') {
			deletemodelitems($modelsinfoarr['modelname'], array($itemid), $_POST['mid'], 1, 1);
		}

		if(checkperm('allowdirectpost') && $op == 'update') {
			$jpurl = ($cp) ? (empty($setsqlarr['uid']) ? S_URL."/admincp.php?action=modelmanages&op=add&mid=$modelsinfoarr[mid]" : S_URL.'/'.$theurl.'&mid='.$modelsinfoarr['mid']) : S_URL."/cp.php?ac=models&op=list&do=$do&nameid=$modelsinfoarr[modelname]";
			showmessage('writing_success_online_please_wait_for_audit', $jpurl);
		} else {
			$jpurl = ($cp) ? S_URL.'/'.$theurl.'&mid='.$modelsinfoarr['mid'] : S_URL."/cp.php?ac=models&op=list&do=$do&nameid=$modelsinfoarr[modelname]";
			showmessage('online_contributions_success', $jpurl);
		}
	} else {
		$setsqlarr = array_merge($setitemsqlarr, $setsqlarr);
		$setsqlarr['addfeed'] = $_POST['addfeed'];
		$setsqlarr = array(
			'subject'	=>	$setitemsqlarr['subject'],
			'mid'	=>	$modelsinfoarr['mid'],
			'uid'	=>	$setsqlarr['uid'],
			'message'	=>	saddslashes(serialize($setsqlarr)),
			'dateline'	=>	$_SGLOBAL['timestamp'],
			'folder'	=>	1
		);
		if(!empty($_POST['itemid'])) {
			$itemid = intval($_POST['itemid']);
			updatetable('modelfolders', $setsqlarr, array('itemid' => $itemid));
		} else {
			$itemid = inserttable('modelfolders', $setsqlarr, 1);
		}
		
		if(!empty($subjectimageid)) {
			$ids[] = $subjectimageid;
		}
		if(!empty($ids)) {
			$ids = simplode($ids);
			$hash = 'm'.str_pad($_POST['mid'], 6, 0, STR_PAD_LEFT).'f'.str_pad($itemid, 8, 0, STR_PAD_LEFT);
			$_SGLOBAL['db']->query('UPDATE '.tname('attachments').' SET isavailable=\'1\', type=\'model\', hash=\''.$hash.'\' WHERE aid IN ('.$ids.')');
		}
		$jpurl = ($cp) ? (empty($setsqlarr['uid']) ? S_URL."/admincp.php?action=modelmanages&op=add&mid=$modelsinfoarr[mid]" : S_URL."/admincp.php?action=modelfolders&mid=$modelsinfoarr[mid]") : S_URL."/cp.php?ac=models&op=list&do=$do&nameid=$modelsinfoarr[modelname]";
		showmessage('writing_success_online_please_wait_for_audit', $jpurl);
	}
}

/**
 * 从缓存中获得模型分类
 */
function getmodelcachecategory($categoryarr, $space='|----', $delbase=0) {
	include_once(S_ROOT.'./class/tree.class.php');
	$tree = new Tree('');
	$miniupid = 0;
	$delid = array();
	if($delbase) {
		$delid[] = $delbase;
	}
	$listarr = array();
	if(!empty($categoryarr)) {
		foreach ($categoryarr as $value) {
			$tree->setNode($value['catid'], $value['upid'], $value);
		}
		//根目录
		$carr = $tree->getChilds($miniupid);
		foreach ($carr as $key => $catid) {
			$cat = $tree->getValue($catid);
			$cat['pre'] = $tree->getLayer($catid, $space);
			if(!empty($delid) && (in_array($cat['upid'], $delid) || $cat['catid'] == $delbase)) {
				$delid[] = $cat['catid'];
			} else {
				$listarr[$cat['catid']] = $cat;
			}
		}
	}
	return $listarr;
}

//模型语言包调用
function modelmsg($key) {
	global $_SGLOBAL;
	include_once(S_ROOT.'./language/model.lang.php');

	$message = $key;
	if(!empty($_SGLOBAL['modellang'][$key])) $message = $_SGLOBAL['modellang'][$key];

	return $message;
}

/**
 * 取得用户后台模型mid
 * return array
 */
function getuserspacemid() {
	$cachefile = S_ROOT.'./cache/model/model.cache.php';
	$cacheinfo = '';
	if(file_exists($cachefile)) {
		include_once($cachefile);
	}
	if(!empty($cacheinfo) && is_array($cacheinfo)) {
		return $cacheinfo;
	} else {
		include_once(S_ROOT.'./function/cache.func.php');
		return updateuserspacemid();
	}
}
?>