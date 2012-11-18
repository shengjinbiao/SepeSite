<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: news.func.php 12536 2009-07-06 06:22:57Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

function freshcookie($itemid) {
	global $_SC, $_SGLOBAL;

	$isupdate = 1;
	$old = empty($_COOKIE[$_SC['cookiepre'].'supe_refresh_items'])?0:trim($_COOKIE[$_SC['cookiepre'].'supe_refresh_items']);
	$itemidarr = explode('_', $old);
	if(in_array($itemid, $itemidarr)) {
		$isupdate = 0;
	} else {
		$itemidarr[] = trim($itemid);
		ssetcookie('supe_refresh_items', implode('_', $itemidarr));
	}
	if(empty($_COOKIE)) $isupdate = 0;

	return $isupdate;
}

function updateviewnum($itemid) {
	global $_SGLOBAL;

	$logfile = S_ROOT.'./log/viewcount.log';
	if(@$fp = fopen($logfile, 'a+')) {
		fwrite($fp, $itemid."\n");
		fclose($fp);
		@chmod($logfile, 0777);
	} else {
		$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET viewnum=viewnum+1 WHERE itemid=\''.$itemid.'\'');
	}
}

function sjammer($str) {
	global $_SGLOBAL, $_SCONFIG;

	$randomstr = '';
	for($i = 0; $i < mt_rand(5, 15); $i++) {
		$randomstr .= chr(mt_rand(0, 59)).chr(mt_rand(63, 126));
	}
	return mt_rand(0, 1) ? '<span style="display:none">'.$_SCONFIG['sitename'].$randomstr.'</span>'.$str :
		$str.'<span style="display:none">'.$randomstr.$_SGLOBAL['supe_uid'].'</span>';
}

function getcheckboxstr($var, $optionarray, $value='', $other='') {
	$html = '<table><tr>';
	$i=0;
	foreach ($optionarray as $okey => $ovalue) {
		$html .= '<td style="border:0"><input name="'.$var.'[]" type="checkbox" value="'.$okey.'"'.$other.' />'.$ovalue.'</td>';
		if($i%5==4) $html .= '</tr><tr>';
		$i++;
	}
	$html .= '</tr></table>';

	$valuearr = array();
	if(!empty($value)) {
		if(is_array($value)) {
			$valuearr = $value;
		} else {
			$valuearr = explode(',', $value);
		}
	}

	if(!empty($valuearr)) {
		foreach ($valuearr as $ovalue) {
			$html = str_replace('value="'.$ovalue.'"', 'value="'.$ovalue.'" checked', $html);
		}
	}

	return $html;
}

function printjs() {
print <<<EOF
	<script type="text/javascript">
		function settitlestyle() {
			var objsubject=document.getElementById('subject');
			var objfontcolor=document.getElementById('fontcolor');
			var objfontsize=document.getElementById('fontsize');
			var objem=document.getElementById('em');
			var objstrong=document.getElementById('strong');
			var objunderline=document.getElementById('underline');
			objsubject.style.color = objfontcolor.value;
			objfontcolor.style.backgroundColor = objfontcolor.value;
			objsubject.style.fontSize = objfontsize.value;
			objsubject.style.width = 500;
			if(objem.checked == true) {
				objsubject.style.fontStyle = "italic";
			} else {
				objsubject.style.fontStyle = "";
			}
			if(objstrong.checked == true) {
				objsubject.style.fontWeight = "bold";
			} else {
				objsubject.style.fontWeight = "";
			}
			if(objunderline.checked == true) {
				objsubject.style.textDecoration = "underline";
			} else {
				objsubject.style.textDecoration = "none";
			}
		}
		function loadtitlestyle() {
			var objsubject=document.getElementById('subject');
			var objfontcolor=document.getElementById('fontcolor');
			var objfontsize=document.getElementById('fontsize');
			var objem=document.getElementById('em');
			var objstrong=document.getElementById('strong');
			var objunderline=document.getElementById('underline');
			objfontcolor.style.backgroundColor = objsubject.style.color;
			objfontcolor.value = objsubject.style.color;
			var colorstr = objsubject.style.color;
			if(isFirefox=navigator.userAgent.indexOf("Firefox")>0 && colorstr != ""){
				colorstr = rgbToHex(colorstr);
			}
			if(colorstr != "") {
				objfontcolor.options.selectedIndex = getbyid(colorstr).index;
				objfontcolor.options.selected = true;
			}
			objfontsize.value = objsubject.style.fontSize;
			if(objsubject.style.fontWeight == "bold") {
				objstrong.checked = true;
			} else {
				objstrong.checked = false;
			}
			if(objsubject.style.fontStyle == "italic") {
				objem.checked = true;
			} else {
				objem.checked = false;
			}
			if(objsubject.style.textDecoration == "underline") {
				objunderline.checked = true;
			} else {
				objunderline.checked = false;
			}		
		}
		function makeselectcolor(selectname){
			subcat = new Array('00','33','66','99','CC','FF');
			var length = subcat.length;
			var RED = subcat;
			var GREEN = subcat;
			var BLUE = subcat;
			var b,r,g;
			var objsubject=document.getElementById(selectname);
			for(r=0;r < length;r++){
				for(g=0;g < length;g++){
					for(b=0;b < length;b++){
						var oOption = document.createElement("option");
						oOption.style.backgroundColor="#"+RED[r]+GREEN[g]+BLUE[b];
						oOption.style.color="#"+RED[r]+GREEN[g]+BLUE[b];
						oOption.value="#"+RED[r]+GREEN[g]+BLUE[b];
						oOption.text="#"+RED[r]+GREEN[g]+BLUE[b];
						oOption.id="#"+RED[r]+GREEN[g]+BLUE[b];
						objsubject.appendChild(oOption);
						}
					}
				}
		}
		function rgbToHex(color) {
			color=color.replace("rgb(","")
			color=color.replace(")","")
			color=color.split(",")
			
			r=parseInt(color[0]);
			g=parseInt(color[1]);
			b=parseInt(color[2]);
			
			r = r.toString(16);
			if (r.length == 1) {
				r = '0' + r;
			}
			g = g.toString(16);
			if (g.length == 1) {
				g = '0' + g;
			}
			b = b.toString(16);
			if (b.length == 1) {
				b = '0' + b;
			}
			return ("#" + r + g + b).toUpperCase();
		}
			
	</script>
EOF;

}
function addurlhttp($m) {
	if (preg_grep("/^http\:/", array($m[2])) || preg_grep("/^\//", array($m[2]))) {
		return 'src="'.$m[2].'.'.$m[3];
	} else {
		return 'src="'.S_URL_ALL.'/'.$m[2].'.'.$m[3];
	}
		
}

//显示扩充信息选择列表
function prefieldhtml($thevalue, $prefieldarr, $var, $input=1, $size='20', $isarray=0) {
	global $alang;

	if($isarray) {
		$optionstr = '';
		foreach ($prefieldarr as $nakey => $navalue) {
			$optionstr .= '<option value="'.$nakey.'">'.$navalue.'</option>';
		}
	} else {
		if(empty($prefieldarr[$var])) {
			$vararr = array();
		} else {
			$vararr = $prefieldarr[$var];
		}
		$optionstr = '';
		foreach ($vararr as $navalue) {
			$optionstr .= '<option value="'.$navalue['value'].'">'.$navalue['value'].'</option>';
			if(empty($thevalue[$var]) && !empty($navalue['isdefault'])) {
				$thevalue[$var] = $navalue['value'];
			}
		}
	}
	$varstr = '';
	if($input) {
		if(empty($thevalue[$var])) $thevalue[$var] = '';
		$varstr .= '<input name="'.$var.'" type="text" id="'.$var.'" size="'.$size.'" value="'.$thevalue[$var].'" />';
		$varstr .= ' <select name="varop" onchange="changevalue(\''.$var.'\', this.value)">';
		$varstr .= '<option value="">'.$alang['prefield_option_'.$var].'</option>';
	} else {
		$varstr .= '<select name="'.$var.'">';
		if(!empty($optionstr)) {
			$optionstr = str_replace('value="'.$thevalue[$var].'"', 'value="'.$thevalue[$var].'" selected', $optionstr);
		}
	}

	$varstr .= $optionstr;
	$varstr .= '</select>';
	return $varstr;
}
?>