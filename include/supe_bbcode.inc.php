<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: supe_bbcode.inc.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}


$_SGLOBAL['smilies'] = Array (
	'searcharray' => Array
	(
	0 => '/\\:loveliness\\:/',
	1 => '/\\:handshake/',
	2 => '/\\:victory\\:/',
	3 => '/\\:funk\\:/',
	4 => '/\\:time\\:/',
	5 => '/\\:kiss\\:/',
	6 => '/\\:call\\:/',
	7 => '/\\:hug\\:/',
	8 => '/\\:lol/',
	9 => '/\\:\'\\(/',
	10 => '/\\:Q/',
	11 => '/\\:L/',
	12 => '/;P/',
	13 => '/\\:\\$/',
	14 => '/\\:P/',
	15 => '/\\:o/',
	16 => '/\\:@/',
	17 => '/\\:D/',
	18 => '/\\:\\(/',
	19 => '/\\:\\)/'
	),
	'replacearray' => Array
	(
	0 => 'loveliness.gif',
	1 => 'handshake.gif',
	2 => 'victory.gif',
	3 => 'funk.gif',
	4 => 'time.gif',
	5 => 'kiss.gif',
	6 => 'call.gif',
	7 => 'hug.gif',
	8 => 'lol.gif',
	9 => 'cry.gif',
	10 => 'mad.gif',
	11 => 'sweat.gif',
	12 => 'titter.gif',
	13 => 'shy.gif',
	14 => 'tongue.gif',
	15 => 'shocked.gif',
	16 => 'huffy.gif',
	17 => 'biggrin.gif',
	18 => 'sad.gif',
	19 => 'smile.gif'
	),
	'display' => Array
	(
	0 => Array
		(
		'code' => ':loveliness:',
		'url' => 'loveliness.gif'
		),
	1 => Array
		(
		'code' => ':handshake',
		'url' => 'handshake.gif'
		),
	2 => Array
		(
		'code' => ':victory:',
		'url' => 'victory.gif'
		),
	3 => Array
		(
		'code' => ':funk:',
		'url' => 'funk.gif'
		),
	4 => Array
		(
		'code' => ':time:',
		'url' => 'time.gif'
		),
	5 => Array
		(
		'code' => ':kiss:',
		'url' => 'kiss.gif'
		),
	6 => Array
		(
		'code' => ':call:',
		'url' => 'call.gif'
		),
	7 => Array
		(
		'code' => ':hug:',
		'url' => 'hug.gif'
		),
	8 => Array
		(
		'code' => ':lol',
		'url' => 'lol.gif'
		),
	9 => Array
		(
		'code' => ':\'(',
		'url' => 'cry.gif'
		),
	10 => Array
		(
		'code' => ':Q',
		'url' => 'mad.gif'
		),
	11 => Array
		(
		'code' => ':L',
		'url' => 'sweat.gif'
		),
	12 => Array
		(
		'code' => ';P',
		'url' => 'titter.gif'
		),
	13 => Array
		(
		'code' => ':$',
		'url' => 'shy.gif'
		),
	14 => Array
		(
		'code' => ':P',
		'url' => 'tongue.gif'
		),
	15 => Array
		(
		'code' => ':o',
		'url' => 'shocked.gif'
		),
	16 => Array
		(
		'code' => ':@',
		'url' => 'huffy.gif'
		),
	17 => Array
		(
		'code' => ':D',
		'url' => 'biggrin.gif'
		),
	18 => Array
		(
		'code' => ':(',
		'url' => 'sad.gif'
		),
	19 => Array
		(
		'code' => ':)',
		'url' => 'smile.gif'
		)
	)
);

//处理表情
foreach($_SGLOBAL['smilies']['replacearray'] as $key => $smiley) {
	$_SGLOBAL['smilies']['replacearray'][$key] = '<img src="'.S_URL.'/images/smilies/'.$smiley.'" align="absmiddle" border="0">';
}

function bbcode($message) {
	global $_SGLOBAL;

	$message = preg_replace($_SGLOBAL['smilies']['searcharray'], $_SGLOBAL['smilies']['replacearray'], $message, 50);
	return snl2br($message);
}


?>