<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_spacetag.inc.php 13493 2009-11-11 06:15:33Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
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

$typearr = array(
	'news' => $alang['common_type_news']
);

$digestarr = array(
	'1' => $alang['space_digest_1'],
	'2' => $alang['space_digest_2'],
	'3' => $alang['space_digest_3']
);

//WHERE
$blockarr['where'] = array(
	'tagid' => array(
		'type' => 'input',
		'alang' => 'block_spacetag_title_tagid',
		'size' => '60',
		'width' => '30%'
	),
	'type' => array(
		'type' => 'checkbox',
		'alang' => 'block_spacetag_title_type',
		'options' => $typearr
	),
	'dateline' => array(
		'type' => 'date',
		'alang' => 'block_spacetag_title_dateline'
	),
	'haveattach' => array(
		'type' => 'radio',
		'alang' => 'block_spaceitem_title_haveattach',
		'options' => array('0'=>$alang['block_thread_attachment_0'], '1'=>$alang['block_thread_attachment_1'])
	),
	'digest' => array(
		'type' => 'checkbox',
		'alang' => 'block_spaceitem_title_digest',
		'options' => $digestarr
	),
	'dateline' => array(
		'type' => 'date',
		'alang' => 'block_spaceitem_title_dateline',
		'size' => '10'
	),
	'lastpost' => array(
		'type' => 'date',
		'alang' => 'block_spaceitem_title_lastpost',
		'size' => '10'
	),
	'uid' => array(
		'type' => 'input',
		'alang' => 'block_spaceitem_title_uid',
		'size' => '60'
	),
	'viewnum' => array(
		'type' => 'input2',
		'alang' => 'block_spaceitem_title_viewnum',
		'size' => '10'
	),
	'replynum' => array(
		'type' => 'input2',
		'alang' => 'block_spaceitem_title_replynum',
		'size' => '10'
	)
);

//ORDER
$blockarr['order'] = array(
	'order' => array(
		'type' => 'select-order',
		'alang' => 'block_thread_title_order',
		'options' => array(
			'' => '------',
			'st.dateline' => $alang['block_spacetag_order_dateline'],
			'i.lastpost' => $alang['block_thread_order_lastpost'],
			'i.viewnum' => $alang['block_thread_order_views'],
			'i.replynum' => $alang['block_thread_order_replies'],
			'i.digest' => $alang['block_thread_order_digest']
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

//BATCH
$blockarr['batch'] = array(
	'subjectlen' => array(
		'type' => 'input',
		'alang' => 'block_thread_title_subjectlen',
		'size' => '10'
	),		
	'subjectdot' => array(
		'type' => 'radio',
		'alang' => 'block_thread_title_subjectdot',
		'options' => array('0'=>$alang['block_dot_0'], '1'=>$alang['block_dot_1'])
	)
);

?>