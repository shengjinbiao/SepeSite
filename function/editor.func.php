<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: editor.func.php 11147 2009-02-20 01:29:23Z zhaolei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

function absoluteurl($url) {
	if($url{0} == '/') {
		return 'http://'.$_SERVER['HTTP_HOST'].$url;
	} else {
		return S_URL.'/'.$url;
	}
}

function atag($aoptions, $text) {
	$href = getoptionvalue('href', $aoptions);

	if(substr($href, 0, 7) == 'mailto:') {
		$tag = 'email';
		$href = substr($href, 7);
	} else {
		$tag = 'url';
		if(!preg_match("/^[a-z0-9]+:/i", $href)) {
			$href = absoluteurl($href);
		}
	}

	return "[$tag=$href]".trim(recursion('a', $text, 'atag'))."[/$tag]";
}

function divtag($divoptions, $text) {
	$prepend = $append = '';

	parsestyle($divoptions, $prepend, $append);
	$align = getoptionvalue('align', $divoptions);

	switch($align) {
		case 'left':
		case 'center':
		case 'right':
			break;
		default:
			$align = '';
	}

	if($align) {
		$prepend .= "[align=$align]";
		$append .= "[/align]";
	}
	$append .= "\n";

	return $prepend.recursion('div', $text, 'divtag').$append;
}

function fetchoptionvalue($option, $text) {
	if(($position = strpos($text, $option)) !== false) {
		$delimiter = $position + strlen($option);
		if($text{$delimiter} == '"') {
			$delimchar = '"';
		} elseif($text{$delimiter} == '\'') {
			$delimchar = '\'';
		} else {
			$delimchar = ' ';
		}
		$delimloc = strpos($text, $delimchar, $delimiter + 1);
		if($delimloc === false) {
			$delimloc = strlen($text);
		} elseif($delimchar == '"' OR $delimchar == '\'') {
			$delimiter++;
		}
		return trim(substr($text, $delimiter, $delimloc - $delimiter));
	} else {
		return '';
	}
}

function fonttag($fontoptions, $text) {
	$tags = array('font' => 'face=', 'size' => 'size=', 'color' => 'color=');
	$prependtags = $appendtags = '';

	foreach($tags AS $bbcode => $locate) {
		$optionvalue = fetchoptionvalue($locate, $fontoptions);
		if($optionvalue) {
			$prependtags .= "[$bbcode=$optionvalue]";
			$appendtags = "[/$bbcode]$appendtags";
		}
	}

	parsestyle($fontoptions, $prependtags, $appendtags);

	return $prependtags.recursion('font', $text, 'fonttag').$appendtags;
}

function getoptionvalue($option, $text) {
	preg_match("/$option(\s+?)?\=(\s+?)?[\"']?(.+?)([\"']|$|>)/is", $text, $matches);
	return trim($matches[3]);
}

function litag($listoptions, $text) {
	return '[*]'.rtrim($text);
}

function listtag($listoptions, $text, $tagname) {
	$text = preg_replace('/<li>((.(?!<\/li))*)(?=<\/?ol|<\/?ul|<li|\[list|\[\/list)/siU', '<li>\\1</li>', $text).(isopera() ? '</li>' : NULL);
	$text = recursion('li', $text, 'litag');

	if($tagname == 'ol') {
		$listtype = fetchoptionvalue('type=', $listoptions) ? fetchoptionvalue('type=', $listoptions) : 1;
		if(in_array($listtype, array('1', 'a', 'A'))) {
			$opentag = '[list='.$listtype.']';
		}
	} else {
		$opentag = '[list]';
	}
	return $text ? $opentag.recursion($tagname, $text, 'listtag').'[/list]' : FALSE;
}

function parsestyle($tagoptions, &$prependtags, &$appendtags) {
	$searchlist = array(
		array('tag' => 'align', 'option' => TRUE, 'regex' => 'text-align:\s*(left);?', 'match' => 1),
		array('tag' => 'align', 'option' => TRUE, 'regex' => 'text-align:\s*(center);?', 'match' => 1),
		array('tag' => 'align', 'option' => TRUE, 'regex' => 'text-align:\s*(right);?', 'match' => 1),
		array('tag' => 'color', 'option' => TRUE, 'regex' => '(?<![a-z0-9-])color:\s*([^;]+);?', 'match' => 1),
		array('tag' => 'font', 'option' => TRUE, 'regex' => 'font-family:\s*([^;]+);?', 'match' => 1),
		array('tag' => 'size', 'option' => TRUE, 'regex' => 'font-size:\s*(\d+(\.\d+)?(px|pt|in|cm|mm|pc|em|ex|%|));?', 'match' => 1),
		array('tag' => 'b', 'option' => FALSE, 'regex' => 'font-weight:\s*(bold);?'),
		array('tag' => 'i', 'option' => FALSE, 'regex' => 'font-style:\s*(italic);?'),
		array('tag' => 'u', 'option' => FALSE, 'regex' => 'text-decoration:\s*(underline);?')
	);

	$style = getoptionvalue('style', $tagoptions);
	$style = preg_replace(
		"/(?<![a-z0-9-])color:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)(;?)/ie",
		'sprintf("color: #%02X%02X%02X$4", $1, $2, $3)',
		$style
	);
	foreach($searchlist AS $searchtag) {
		if(preg_match('/'.$searchtag['regex'].'/i', $style, $match)) {
			$opnvalue = $match["$searchtag[match]"];
			$prependtags .= '['.$searchtag['tag'].($searchtag['option'] == TRUE ? '='.$opnvalue.']' : ']');
			$appendtags = '[/'.$searchtag['tag']."]$appendtags";
		}
	}
}

function ptag($poptions, $text) {
	$align = getoptionvalue('align', $poptions);

	switch($align) {
		case 'left':
		case 'center':
		case 'right':
			break;
		default:
			$align = '';
	}

	$prepend = $append = '';
	parsestyle($poptions, $prepend, $append);
	if($align) {
		$prepend .= "[align=$align]";
		$append .= "[/align]";
	}
	$append .= "\n";

	return $prepend.recursion('p', $text, 'ptag').$append;
}

function recursion($tagname, $text, $function, $extraargs = '') {
	$tagname = strtolower($tagname);
	$open_tag = "<$tagname";
	$open_tag_len = strlen($open_tag);
	$close_tag = "</$tagname>";
	$close_tag_len = strlen($close_tag);

	$beginsearchpos = 0;
	do {
		$textlower = strtolower($text);
		$tagbegin = @strpos($textlower, $open_tag, $beginsearchpos);
		if($tagbegin === FALSE) {
			break;
		}

		$strlen = strlen($text);

		$inquote = '';
		$found = FALSE;
		$tagnameend = FALSE;
		for($optionend = $tagbegin; $optionend <= $strlen; $optionend++) {
			$char = $text{$optionend};
			if(($char == '"' || $char == "'") && $inquote == '') {
				$inquote = $char;
			} elseif(($char == '"' || $char == "'") && $inquote == $char) {
				$inquote = '';
			} elseif($char == '>' && !$inquote) {
				$found = TRUE;
				break;
			} elseif(($char == '=' || $char == ' ') && !$tagnameend) {
				$tagnameend = $optionend;
			}
		}
		if(!$found) {
			break;
		}
		if(!$tagnameend) {
			$tagnameend = $optionend;
		}
		$offset = $optionend - ($tagbegin + $open_tag_len);
		$tagoptions = substr($text, $tagbegin + $open_tag_len, $offset);
		$acttagname = substr($textlower, $tagbegin + 1, $tagnameend - $tagbegin - 1);
		if($acttagname != $tagname) {
			$beginsearchpos = $optionend;
			continue;
		}

		$tagend = strpos($textlower, $close_tag, $optionend);
		if($tagend === FALSE) {
			break;
		}

		$nestedopenpos = strpos($textlower, $open_tag, $optionend);
		while($nestedopenpos !== FALSE && $tagend !== FALSE) {
			if($nestedopenpos > $tagend) {
				break;
			}
			$tagend = strpos($textlower, $close_tag, $tagend + $close_tag_len);
			$nestedopenpos = strpos($textlower, $open_tag, $nestedopenpos + $open_tag_len);
		}
		if($tagend === FALSE) {
			$beginsearchpos = $optionend;
			continue;
		}

		$localbegin = $optionend + 1;
		$localtext = $function($tagoptions, substr($text, $localbegin, $tagend - $localbegin), $tagname, $extraargs);

		$text = substr_replace($text, $localtext, $tagbegin, $tagend + $close_tag_len - $tagbegin);

		$beginsearchpos = $tagbegin + strlen($localtext);
	} while($tagbegin !== FALSE);

	return $text;
}

function simpletag($options, $text, $tagname, $parseto) {
	if(trim($text) == '') {
		return '';
	}
	$text = recursion($tagname, $text, 'simpletag', $parseto);
	return "[$parseto]{$text}[/$parseto]";
}

function spantag($spanoptions, $text) {
	$prependtags = $appendtags = '';
	parsestyle($spanoptions, $prependtags, $appendtags);

	return $prependtags.recursion('span', $text, 'spantag').$appendtags;
}

function isopera() {
	$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
	if(strpos($useragent, 'opera') !== false) {
		preg_match('/opera(\/| )([0-9\.]+)/', $useragent, $regs);
		return $regs[2];
	}
	return FALSE;
}
?>