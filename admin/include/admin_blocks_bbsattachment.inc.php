<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_bbsattachment.inc.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

$bbsforumlistarr = getbbsforum();

$bbstypeidarr = getbbstype();

if(!isset($theblcokvalue['settid'])) $theblcokvalue['settid'] = '';
if($theblcokvalue['settid'] == '1') {
	$divsettid1display = '';
	$divsettid2display = 'none';
} else {
	$divsettid1display = 'none';
	$divsettid2display = '';
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

if(!isset($theblcokvalue['showdetail'])) $theblcokvalue['showdetail'] = '';
if($theblcokvalue['showdetail'] == '1') {
	$divshowdetaildisplay = '';
} else {
	$divshowdetaildisplay = 'none';
}

$filetypearr = array(
	'image' => $alang['block_attachment_filetype_image'],
	'file' => $alang['block_attachment_filetype_file']
);

$digestarr = array(
	'1' => $alang['block_thread_digest_1'],
	'2' => $alang['block_thread_digest_2'],
	'3' => $alang['block_thread_digest_3']
);

//multi
$showmultipage = array(
	'0' => $alang['space_showmultipage_0'],
	'1' => $alang['space_showmultipage_1']
);

$blockarr = array(

//WHERE
'where' => array(
	'setaid' => array(
		'type' =>'radio',
		'alang' => 'block_attachment_title_setaid',
		'options' => array('0'=>$alang['block_thread_settid_0'], '1'=>$alang['block_thread_settid_1']),
		'other' => ' onclick="jssettid(this.value)"',
		'width' => '30%'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid1" style="display:'.$divsettid1display.'">'
	),
	'aid' => array(
		'type' => 'input',
		'alang' => 'block_attachment_title_aid',
		'size' => '60'
	),
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid2" style="display:'.$divsettid2display.'">'
	),
	'filetype' => array(
		'type' => 'checkbox',
		'alang' => 'block_attachment_title_filetype',
		'options' => $filetypearr
	),
	'dateline' => array(
		'type' => 'date',
		'alang' => 'block_attachment_title_dateline',
		'size' => '10'
	),	
	'readperm' => array(
		'type' => 'input2',
		'alang' => 'block_attachment_title_readperm',
		'size' => '10'
	),	
	'downloads' => array(
		'type' => 'input2',
		'alang' => 'block_attachment_title_downloads',
		'size' => '10'
	),

	't_fid' => array(
		'type' => 'select-div',
		'alang' => 'block_thread_title_fid',
		'check' => 1,
		'options' => $bbsforumlistarr
	),
	't_typeid' => array(
		'type' => 'checkbox',
		'alang' => 'block_thread_title_typeid',
		'check' => 1,
		'options' => $bbstypeidarr
	),
	't_dateline' => array(
		'type' => 'date',
		'alang' => 'block_thread_title_dateline',
		'size' => '10'
	),
	't_lastpost' => array(
		'type' => 'date',
		'alang' => 'block_thread_title_lastpost',
		'size' => '10'
	),	
	't_authorid' => array(
		'type' => 'input',
		'alang' => 'block_thread_title_authorid',
		'size' => '60'
	),
	't_readperm' => array(
		'type' => 'input2',
		'alang' => 'block_thread_title_readperm',
		'size' => '10'
	),
	't_price' => array(
		'type' => 'input2',
		'alang' => 'block_thread_title_price',
		'size' => '10'
	),
	't_views' => array(
		'type' => 'input2',
		'alang' => 'block_thread_title_views',
		'size' => '10'
	),
	't_replies' => array(
		'type' => 'input2',
		'alang' => 'block_thread_title_replies',
		'size' => '10'
	),
	't_rate' => array(
		'type' => 'input2',
		'alang' => 'block_thread_title_rate',
		'size' => '10'
	),
	't_digest' => array(
		'type' => 'checkbox',
		'alang' => 'block_thread_title_digest',
		'options' => $digestarr
	),
	't_blog' => array(
		'type' => 'radio',
		'alang' => 'block_thread_title_blog',
		'options' => array('0'=>$alang['block_thread_blog_0'], '1'=>$alang['block_thread_blog_1'])
	),
	't_closed' => array(
		'type' => 'radio',
		'alang' => 'block_thread_title_closed',
		'options' => array('0'=>$alang['block_thread_closed_0'], '1'=>$alang['block_thread_closed_1'])
	),
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	)
),

//ORDER
'order' => array(
	'order' => array(
		'type' => 'select-order',
		'alang' => 'block_thread_title_order',
		'options' => array(
			'' => '------',
			'a.downloads' => $alang['block_attachment_order_downloads'],
			'a.filesize' => $alang['block_attachment_order_filesize'],
			'a.dateline' => $alang['block_attachment_order_dateline'],
			't.displayorder' => $alang['block_attachment_thread_order_displayorder'],
			't.dateline' => $alang['block_attachment_thread_order_dateline'],
			't.lastpost' => $alang['block_attachment_thread_order_lastpost'],
			't.views' => $alang['block_attachment_thread_order_views'],
			't.replies' => $alang['block_attachment_thread_order_replies'],
			't.digest' => $alang['block_attachment_thread_order_digest'],
			't.rate' => $alang['block_attachment_thread_order_rate'],
			't.readperm' => $alang['block_attachment_thread_order_readperm'],
			't.price' => $alang['block_attachment_thread_order_price']
		)
	)
),

//LIMIT
'limit' => array(
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
),

//BATCH
'batch' => array(
	'subjectlen' => array(
		'type' => 'input',
		'alang' => 'block_thread_title_subjectlen',
		'size' => '10'
	),		
	'subjectdot' => array(
		'type' => 'radio',
		'alang' => 'block_thread_title_subjectdot',
		'options' => array('0'=>$alang['block_dot_0'], '1'=>$alang['block_dot_1'])
	),
	'showdetail' => array(
		'type' =>'radio',
		'alang' => 'block_title_showdetail',
		'options' => array('0'=>$alang['block_showdetail_0'], '1'=>$alang['block_showdetail_1']),
		'other' => ' onclick="jsshowdetail(this.value)"',
		'width' => '30%'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divshowdetail" style="display:'.$divshowdetaildisplay.'">'
	),
	'descriptionlen' => array(
		'type' => 'input',
		'alang' => 'block_attachment_title_descriptionlen',
		'size' => '10'
	),
	'descriptiondot' => array(
		'type' => 'radio',
		'alang' => 'block_attachment_title_descriptiondot',
		'options' => array('0'=>$alang['block_dot_0'], '1'=>$alang['block_dot_1'])
	),
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	)
)

);

?>