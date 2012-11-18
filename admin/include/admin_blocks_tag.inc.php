<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_tag.inc.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

if(!isset($theblcokvalue['settagid'])) $theblcokvalue['settagid'] = '';
if($theblcokvalue['settagid'] == '1') {
	$divsettagid1display = '';
	$divsettagid2display = 'none';
} else {
	$divsettagid1display = 'none';
	$divsettagid2display = '';
}

//multi
if(!isset($theblcokvalue['showmultipage'])) $theblcokvalue['showmultipage'] = 0;
if($theblcokvalue['showmultipage'] == '1') {
	$divshowmulti1display = 'none';
	$divshowmulti2display = '';
} else {
	$divshowmulti1display = '';
	$divshowmulti2display = 'none';
}

//multi
$showmultipage = array(
	'0' => $alang['space_showmultipage_0'],
	'1' => $alang['space_showmultipage_1']
);

$blockarr = array();

//WHERE
$blockarr['where'] = array(
	'settagid' => array(
		'type' =>'radio',
		'alang' => 'block_tag_title_settagid',
		'options' => array('0'=>$alang['block_thread_settid_0'], '1'=>$alang['block_thread_settid_1']),
		'other' => ' onclick="jssettid(this.value)"',
		'width' => '30%'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid1" style="display:'.$divsettagid1display.'">'
	),
	'tagid' => array(
		'type' => 'input',
		'alang' => 'block_tag_title_tagid',
		'size' => '60'
	),
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid2" style="display:'.$divsettagid2display.'">'
	),
	'dateline' => array(
		'type' => 'date',
		'alang' => 'block_tag_title_dateline'
	),
	'uid' => array(
		'type' => 'input',
		'alang' => 'block_tag_title_uid',
		'size' => 60
	),
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	)
);

//ORDER
$blockarr['order'] = array(
	'order' => array(
		'type' => 'select-order',
		'alang' => 'block_thread_title_order',
		'options' => array(
			'' => '------',
			'spacenewsnum' => $alang['block_tag_order_spacenewsnum'],
		)
	)
);

//LIMIT
$blockarr['limit'] = array(
	'showmultipage' => array(
		'type' => 'radio',
		'alang' => 'block_thread_title_showmultipage',
		'options' => $showmultipage,
		'other' => ' onclick="jsshowmulti(this.value)"'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divshowmulti1" style="display:'.$divshowmulti1display.'">'
	),
	'start' => array(
		'type' => 'input',
		'alang' => 'block_thread_title_start',
		'size' => '10'
	),
	'limit' => array(
		'type' => 'input',
		'alang' => 'block_thread_title_limit',
		'size' => '10'
	),
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divshowmulti2" style="display:'.$divshowmulti2display.'">'
	),
	'multipageexplain' => array(
		'type' => 'text',
		'alang' => 'block_thread_title_multipageexplain',
		'text' => $alang['block_thread_multipageexplain']
	),
	'perpage' => array(
		'type' => 'input',
		'alang' => 'block_thread_title_perpage',
		'size' => '10'
	),
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	)
);

?>