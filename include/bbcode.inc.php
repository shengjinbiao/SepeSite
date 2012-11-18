<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: bbcode.inc.php 13497 2009-11-11 07:16:39Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

include_once S_ROOT.'/data/system/bbs_bbcodes.cache.php';
include_once S_ROOT.'/data/system/bbs_settings.cache.php';
include_once S_ROOT.'/data/system/bbs_style.cache.php';

define('S_IMGDIR', B_URL.'/'.$_DCACHE['style']['imgdir']);
if(empty($_DCACHE['style']['smdir'])) $_DCACHE['style']['smdir'] = 'images/smilies/default';
define('S_SMDIR', B_URL.'/'.$_DCACHE['style']['smdir']);

$discuzcodes = array
	(
	'pcodecount' => -1,
	'codecount' => 0,
	'codehtml' => '',
	'searcharray' => array(),
	'replacearray' => array(),
	'seoarray' => array
		(
		0 => '',
		1 => $_SERVER['HTTP_HOST'],
		2 => $_SCONFIG['sitename'],
		3 => $_DCACHE['settings']['seotitle'],
		4 => $_DCACHE['settings']['seokeywords'],
		5 => $_DCACHE['settings']['seodescription']
		)
	);


//处理表情
foreach($_DCACHE['smilies']['replacearray'] as $key => $smiley) {
	$_DCACHE['smilies']['replacearray'][$key] = '<img src="'.S_SMDIR.'/'.$smiley.'" align="absmiddle" border="0">';
}

mt_srand((double)microtime() * 1000000);

function bbcode($message, $bbcodeoff=0, $smileyoff=0, $htmlon=0, $jammer=0, $allowmediacode=0) {
	global $_DCACHE, $discuzcodes;
	$message = preg_replace("/\s*\[code\](.+?)\[\/code\]\s*/ies", "codedisp('\\1')", $message);

	if(!$htmlon) {
		$message = $jammer ? preg_replace("/\r\n|\n|\r/e", "jammer()", shtmlspecialchars($message)) : shtmlspecialchars($message);
	}
	
	if(!$smileyoff && !empty($_DCACHE['smilies']) && is_array($_DCACHE['smilies'])) {
		$message = preg_replace($_DCACHE['smilies']['searcharray'], $_DCACHE['smilies']['replacearray'], $message, 50);
	}
	
	if(!$bbcodeoff) {
		if(empty($discuzcodes['searcharray'])) {
			$discuzcodes['searcharray']['bbcode_regexp'] = array(
				"/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is",
				"/\s*\[free\][\n\r]*(.+?)[\n\r]*\[\/free\]\s*/is",
				"/\[url\]\s*(www.|https?:\/\/|ftp:\/\/|gopher:\/\/|news:\/\/|telnet:\/\/|rtsp:\/\/|mms:\/\/|callto:\/\/|ed2k:\/\/){1}([^\[\"']+?)\s*\[\/url\]/ie",
				"/\[url=www.([^\[\"']+?)\](.+?)\[\/url\]/is",
				"/\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|ed2k){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/is",
				"/\[email\]\s*([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\s*\[\/email\]/i",
				"/\[email=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\](.+?)\[\/email\]/is",
				"/\[color=([^\[\<]+?)\]/i",
				"/\[size=(\d+(\.\d+)?(px|pt|in|cm|mm|pc|em|ex|%)+?)\]/i",
				"/\[size=(\d+?)\]/i",
				"/\[font=([^\[\<]+?)\]/i",
				"/\[align=([^\[\<]+?)\]/i",	
				"/\[hide=?\d*\].+?\[\/hide\]/is",
				"/\[p=(\d{1,2}), (\d{1,2}), (left|center|right)\]/i",
				"/\[float=(left|right)\]/i"
			);
			$discuzcodes['replacearray']['bbcode_regexp'] = array(
				"<p style=\"font-weight: bold; margin: 1em 1em 0 1em;\">QUOTE:</p><blockquote style=\"border: 1px dotted #DDD; margin: 0 1em 1em; padding: 0.5em; line-height: 1.8em;\">\\1</blockquote>",
				"<p style=\"font-weight: bold; margin: 1em 1em 0 1em;\">FREE:</p><blockquote style=\"border: 1px dotted #DDD; margin: 0 1em 1em; padding: 0.5em; line-height: 1.8em;\">\\1</blockquote>",
				"cuturl('\\1\\2')",
				"<a href=\"http://www.\\1\" target=\"_blank\">\\2</a>",
				"<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>",
				"<a href=\"mailto:\\1@\\2\">\\1@\\2</a>",
				"<a href=\"mailto:\\1@\\2\">\\3</a>",
				"<font color=\"\\1\">",
				"<font style=\"font-size: \\1\">",
				"<font size=\"\\1\">",
				"<font face=\"\\1\">",
				"<p align=\"\\1\">",
				"<strong>*** Hidden to visitors ***</strong>",
				"<p style=\"line-height: \\1px; text-indent: \\2em; text-align: \\3;\">",
				"<span style=\"float: \\1;\">"
			);

			$discuzcodes['searcharray']['bbcode_regexp'][] = "/\[table(?:=(\d{1,4}%?)(?:,([\(\)%,#\w ]+))?)?\]\s*(.+?)\s*\[\/table\]/ies";
			$discuzcodes['replacearray']['bbcode_regexp'][] = "parsetable('\\1', '\\2', '\\3')";
			$discuzcodes['searcharray']['bbcode_regexp'][] = "/\[table(?:=(\d{1,4}%?)(?:,([\(\)%,#\w ]+))?)?\]\s*(.+?)\s*\[\/table\]/ies";
			$discuzcodes['replacearray']['bbcode_regexp'][] = "parsetable('\\1', '\\2', '\\3')";

			$discuzcodes['searcharray']['bbcode_regexp'][] = "/\[payto\]\s*\(seller\)(.*)\(\/seller\)\s*(\(subject\)(.*)\(\/subject\))?\s*(\(body\)(.*)\(\/body\))?\s*(\(gross\)(.*)\(\/gross\))?\s*(\(price\)(.*)\(\/price\))?\s*(\(url\)(.*)\(\/url\))?\s*(\(type\)(.*)\(\/type\))?\s*(\(transport\)(.*)\(\/transport\))?\s*(\(ordinary_fee\)(.*)\(\/ordinary_fee\))?\s*(\(express_fee\)(.*)\(\/express_fee\))?\s*\[\/payto\]/iesU";
			$discuzcodes['replacearray']['bbcode_regexp'][] = "payto('\\1',array('subject'=>'\\3','body'=>'\\5','price'=>'\\7','price'=>'\\9','url'=>'\\11','type'=>'\\13','transport'=>'\\15','ordinary_fee'=>'\\17','express_fee'=>'\\19'))";

			$discuzcodes['searcharray']['bbcode_str'] = array(
				'[/color]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]',
				'[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
				'[list=A]', '[*]', '[/list]', '[indent]', '[/indent]', '[/p]', '[/float]'
			);

			$discuzcodes['replacearray']['bbcode_str'] = array(
				'</font>', '</font>', '</font>', '</p>', '<b>', '</b>', '<i>',
				'</i>', '<u>', '</u>', '<ul>', '<ol type=1>', '<ol type=a>',
				'<ol type=A>', '<li>', '</ul></ol>', '<blockquote>', '</blockquote>', '</p>', '</span>'
			);
		}

		$discuzcodes['searcharray']['bbcode_regexp'][] = "/\[media=(\w{1,4}),(\d{1,4}),(\d{1,4}),(\d)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/ies";
		if($allowmediacode) {
			$discuzcodes['replacearray']['bbcode_regexp'][] = "parsemedia('\\1', \\2, \\3, \\4, '\\5')";
		} else {
			$discuzcodes['replacearray']['bbcode_regexp'][] = "bbcodeurl('\\5', '<a href=\"%s\" target=\"_blank\">%s</a>')";
		}

		@$message = str_replace($discuzcodes['searcharray']['bbcode_str'], $discuzcodes['replacearray']['bbcode_str'],
				preg_replace(
					($_DCACHE['bbcodes'] ? array_merge($discuzcodes['searcharray']['bbcode_regexp'], $_DCACHE['bbcodes']['searcharray']) : $discuzcodes['searcharray']['bbcode_regexp']),
					($_DCACHE['bbcodes'] ? array_merge($discuzcodes['replacearray']['bbcode_regexp'], $_DCACHE['bbcodes']['replacearray']) : $discuzcodes['replacearray']['bbcode_regexp']),
					$message));
		if(preg_match("/\[hide=?\d*\].+?\[\/hide\]/is", $message)) {
			$message = preg_replace("/\[hide\]\s*(.+?)\s*\[\/hide\]/is", '<b>\\1</b>', $message);
			$message = preg_replace("/\[hide=(\d+)\]\s*(.+?)\s*\[\/hide\]/ies", "creditshide(\\1,'\\2')", $message);
		}

 		if(empty($discuzcodes['searcharray']['imgcode'])) {
			$discuzcodes['searcharray']['imgcode'] = array(
				"/\[swf\]\s*([^\[\<\r\n]+?)\s*\[\/swf\]/ies",
				"/\[wma\]\s*([^\[\<\r\n]+?)\s*\[\/wma\]/ies",
				"/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies",
				"/\[img=(\d{1,3})[x|\,](\d{1,3})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies"
			);
			$discuzcodes['replacearray']['imgcode'] = array(
				"bbcodeurl('\\1', '<br>Flash: <a href=\"%s\" class=\"showflash\" onclick=\"return showmedia(this);\">%s</a><br>')",
				"bbcodeurl('\\1', '<br>Media: <a href=\"%s\" class=\"showvideo\" onclick=\"return showmedia(this);\">%s</a><br>')",
				"bbcodeurl('\\1', '<img src=\"%s\" border=\"0\">')",
				"bbcodeurl('\\3', '<img width=\"\\1\" height=\"\\2\" src=\"%s\" border=\"0\">')"
			);
		}
		$message = preg_replace($discuzcodes['searcharray']['imgcode'], $discuzcodes['replacearray']['imgcode'], $message);
	}

	for($i = 0; $i <= $discuzcodes['pcodecount']; $i++) {
		$message = str_replace("[\tDISCUZ_CODE_$i\t]", $discuzcodes['codehtml'][$i], $message);
	}

	return $htmlon ? $message : nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
}

function codedisp($code) {
	global $discuzcodes, $_DCACHE;
	$discuzcodes['pcodecount']++;
	$code = shtmlspecialchars(str_replace('\\"', '"', preg_replace("/^[\n\r]*(.+?)[\n\r]*$/is", "\\1", $code)));
	$discuzcodes['codehtml'][$discuzcodes['pcodecount']] = "<p style=\"font-weight: bold; margin: 1em 1em 0 1em;\">CODE:</p><code style=\"display: block; margin: 0 1em 1em; padding: 0.5em; border: 1px solid #CCC; font: 12px Courier, monospace; line-height: 1.8em;\">$code</code>";
	$discuzcodes['codecount']++;
	return "[\tDISCUZ_CODE_$discuzcodes[pcodecount]\t]";
}

function bbcodeurl($url, $tags) {
	if(!preg_match("/<.+?>/s", $url)) {
		if(!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'ftp://', 'rtsp:/', 'mms://'))) {
			$url = 'http://'.$url;
		}
		return str_replace(array('submit', 'logging.php'), array('', ''), sprintf($tags, $url, addslashes($url)));
	} else {
		return '&nbsp;'.$url;
	}
}

function parsemedia($type, $width, $height, $autostart, $url) {
	if(in_array($type, array('ra', 'rm', 'wma', 'wmv', 'mp3', 'mov'))) {
		$url = str_replace(array('<', '>'), '', str_replace('\\"', '\"', $url));
		$mediaid = 'media_'.random(3);
		switch($type) {
			case 'ra'	: return '<object classid="clsid:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="'.$width.'" height="32"><param name="autostart" value="'.$autostart.'" /><param name="src" value="'.$url.'" /><param name="controls" value="controlpanel" /><param name="console" value="'.$mediaid.'_" /><embed src="'.$url.'" type="audio/x-pn-realaudio-plugin" controls="ControlPanel" '.($autostart ? 'autostart="true"' : '').' console="'.$mediaid.'_" width="'.$width.'" height="32"></embed></object>';break;
			case 'rm'	: return '<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="'.$width.'" height="'.$height.'"><param name="autostart" value="'.$autostart.'" /><param name="src" value="'.$url.'" /><param name="controls" value="imagewindow" /><param name="console" value="'.$mediaid.'_" /><embed src="'.$url.'" type="audio/x-pn-realaudio-plugin" controls="IMAGEWINDOW" console="'.$mediaid.'_" width="'.$width.'" height="'.$height.'"></embed></object><br /><object classid="clsid:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="'.$width.'" height="32"><param name="src" value="'.$url.'" /><param name="controls" value="controlpanel" /><param name="console" value="'.$mediaid.'_" /><embed src="'.$url.'" type="audio/x-pn-realaudio-plugin" controls="ControlPanel" '.($autostart ? 'autostart="true"' : '').' console="'.$mediaid.'_" width="'.$width.'" height="32"></embed></object>';break;
			case 'wma'	: return '<object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="'.$width.'" height="64"><param name="autostart" value="'.$autostart.'" /><param name="url" value="'.$url.'" /><embed src="'.$url.'" autostart="'.$autostart.'" type="audio/x-ms-wma" width="'.$width.'" height="64"></embed></object>';break;
			case 'wmv'	: return '<object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="'.$width.'" height="'.$height.'"><param name="autostart" value="'.$autostart.'" /><param name="url" value="'.$url.'" /><embed src="'.$url.'" autostart="'.$autostart.'" type="video/x-ms-wmv" width="'.$width.'" height="'.$height.'"></embed></object>';break;
			case 'mp3'	: return '<object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="'.$width.'" height="64"><param name="autostart" value="'.$autostart.'" /><param name="url" value="'.$url.'" /><embed src="'.$url.'" autostart="'.$autostart.'" type="application/x-mplayer2" width="'.$width.'" height="64"></embed></object>';break;
			case 'mov'	: return '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="'.$width.'" height="'.$height.'"><param name="autostart" value="'.($autostart ? 'true' : 'false').'" /><param name="src" value="'.$url.'" /><embed controller="true" width="'.$width.'" height="'.$height.'" src="'.$url.'" autostart="'.($autostart ? 'true' : 'false').'"></embed></object>';break;
		}
	}
	return;
}

function jammer() {
	global $discuzcodes;
	$randomstr = '';
	for($i = 0; $i < mt_rand(5, 15); $i++) {
		$randomstr .= chr(mt_rand(0, 59)).chr(mt_rand(63, 126));
	}
	return mt_rand(0, 1) ? '<span style="display:none">'.$discuzcodes['seoarray'][mt_rand(0, 5)].$randomstr.'</span>'."\r\n" :
		"\r\n".'<span style="display:none">'.$randomstr.$discuzcodes['seoarray'][mt_rand(0, 5)].'</span>';
}

function creditshide($creditsrequire, $message) {
	return '<b>'.str_replace('\\"', '"', $message).'</b>';
}

function attachtag($aid) {
        global $attachlist;

        if(isset($attachlist[$aid])) {
                $attach = $attachlist[$aid];
                unset($attachlist[$aid]);

                if($attach['isimage']) {
                        $replacement = "<p><a href=\"".B_URL."/attachment.php?aid=".bbs_aidencode($attach[aid])."\" target=\"_blank\"><img src=\"$attach[attachment]\" border=\"0\"><br>$attach[filename]</a></p>";
                } else {
                        $replacement = "<p><img src=\"".S_URL."/images/base/haveattach.gif\" align=\"absmiddle\" border=\"0\"><a href=\"".B_URL."/attachment.php?aid=".bbs_aidencode($attach[aid])."\" target=\"_blank\"><strong>$attach[filename]</strong></a><br />($attach[dateline], Size: $attach[attachsize], Downloads: $attach[downloads])</p>";
                }

                return $replacement;
        } else {
                return '<strike>[attach]'.$aid.'[/attach]</strike>';
        }
}

function attachtagone($pid, $aid) {
        global $attacharr;
        
        if(isset($attacharr[$pid][$aid])) {
                $attach = $attacharr[$pid][$aid];
                unset($attacharr[$pid][$aid]);

                $replacement = '<br>';
                if($attach['attachimg']) {
                        $replacement .= "<a href=\"".B_URL."/attachment.php?aid=".bbs_aidencode($attach[aid])."\" target=\"_blank\"><img src=\"$attach[attachment]\" border=\"0\"><br>$attach[filename]</a>";
                } else {
                        $replacement .= "<p><img src=\"".S_URL."/images/base/haveattach.gif\" align=\"absmiddle\" border=\"0\"><a href=\"".B_URL."/attachment.php?aid=".bbs_aidencode($attach[aid])."\" target=\"_blank\"><strong>$attach[filename]</strong></a><br />($attach[dateline], Size: $attach[attachsize], Downloads: $attach[downloads])</p>";
                }

                return $replacement;
        } else {
                return '<strike>[attach]'.$aid.'[/attach]</strike>';
        }
}

function parsetable($width, $bgcolor, $message) {
	$width = substr($width, -1) == '%' ? (substr($width, 0, -1) <= 98 ? $width : '98%') : ($width <= 560 ? $width : '98%');
	return '<table cellspacing="0" '.
		($width == '' ? NULL : 'width="'.$width.'" ').
		'align="center" class="t_table"'.($bgcolor ? ' style="background: '.$bgcolor.'">' : '>').
		str_replace('\\"', '"', preg_replace(array(
				"/\[tr(?:=([\(\)%,#\w]+))?\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/ie",
				"/\[\/td\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/ie",
				"/\[\/td\]\s*\[\/tr\]/i"
			), array(
				"parsetrtd('\\1', '\\2', '\\3', '\\4')",
				"parsetrtd('td', '\\1', '\\2', '\\3')",
				'</td></tr>'
			), $message)
		).'</table>';
}

function parsetrtd($bgcolor, $colspan, $rowspan, $width) {
	return ($bgcolor == 'td' ? '</td>' : '<tr'.($bgcolor ? ' style="background: '.$bgcolor.'"' : '').'>').'<td'.($colspan > 1 ? ' colspan="'.$colspan.'"' : '').($rowspan > 1 ? ' rowspan="'.$rowspan.'"' : '').($width ? ' width="'.$width.'"' : '').'>';
}
?>
