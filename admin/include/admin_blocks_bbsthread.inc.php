<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_bbsthread.inc.php 10898 2008-12-31 02:58:50Z zhaofei $
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

$digestarr = array(
	'1' => $alang['block_thread_digest_1'],
	'2' => $alang['block_thread_digest_2'],
	'3' => $alang['block_thread_digest_3']
);

$bbsurltypearr = array(
	'site' => $alang['block_thread_bbsurltype_site'],
	'bbs' => $alang['block_thread_bbsurltype_bbs']
);

//multi
$showmultipage = array(
	'0' => $alang['space_showmultipage_0'],
	'1' => $alang['space_showmultipage_1']
);

$blockarr = array(

//WHERE
'where' => array(
	'settid' => array(
		'type' =>'radio',
		'alang' => 'block_thread_title_settid',
		'options' => array('0'=>$alang['block_thread_settid_0'], '1'=>$alang['block_thread_settid_1']),
		'other' => ' onclick="jssettid(this.value)"',
		'width' => '30%'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid1" style="display:'.$divsettid1display.'">'
	),
	'tid' => array(
		'type' => 'input',
		'alang' => 'block_thread_title_tid',
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
	'fid' => array(
		'type' => 'select-div',
		'alang' => 'block_thread_title_fid',
		'check' => 1,
		'options' => $bbsforumlistarr
	),
	'typeid' => array(
		'type' => 'checkbox',
		'alang' => 'block_thread_title_typeid',
		'check' => 1,
		'options' => $bbstypeidarr
	),
	'digest' => array(
		'type' => 'checkbox',
		'alang' => 'block_thread_title_digest',
		'options' => $digestarr
	),
	'blog' => array(
		'type' => 'radio',
		'alang' => 'block_thread_title_blog',
		'options' => array('0'=>$alang['block_thread_blog_0'], '1'=>$alang['block_thread_blog_1'])
	),
	'poll' => array(
		'type' => 'radio',
		'alang' => 'block_thread_title_poll',
		'options' => array('0'=>$alang['block_thread_poll_0'], '1'=>$alang['block_thread_poll_1'])
	),
	'attachment' => array(
		'type' => 'radio',
		'alang' => 'block_thread_title_attachment',
		'options' => array('0'=>$alang['block_thread_attachment_0'],
				'1'=>$alang['block_thread_attachment_1'],
				//'2'=>$alang['block_thread_attachment_2'],
				//'3'=>$alang['block_thread_attachment_3']
		)
	),
	'closed' => array(
		'type' => 'radio',
		'alang' => 'block_thread_title_closed',
		'options' => array('0'=>$alang['block_thread_closed_0'], '1'=>$alang['block_thread_closed_1'])
	),
	'dateline' => array(
		'type' => 'date',
		'alang' => 'block_thread_title_dateline',
		'size' => '10'
	),
	'lastpost' => array(
		'type' => 'date',
		'alang' => 'block_thread_title_lastpost',
		'size' => '10'
	),
	'authorid' => array(
		'type' => 'input',
		'alang' => 'block_thread_title_authorid',
		'size' => '60'
	),
	'readperm' => array(
		'type' => 'input2',
		'alang' => 'block_thread_title_readperm',
		'size' => '10'
	),
	'price' => array(
		'type' => 'input2',
		'alang' => 'block_thread_title_price',
		'size' => '10'
	),
	'views' => array(
		'type' => 'input2',
		'alang' => 'block_thread_title_views',
		'size' => '10'
	),
	'replies' => array(
		'type' => 'input2',
		'alang' => 'block_thread_title_replies',
		'size' => '10'
	),
	'rate' => array(
		'type' => 'input2',
		'alang' => 'block_thread_title_rate',
		'size' => '10'
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
			'displayorder' => $alang['block_thread_order_displayorder'],
			'dateline' => $alang['block_thread_order_dateline'],
			'lastpost' => $alang['block_thread_order_lastpost'],
			'views' => $alang['block_thread_order_views'],
			'replies' => $alang['block_thread_order_replies'],
			'digest' => $alang['block_thread_order_digest'],
			'rate' => $alang['block_thread_order_rate'],
			'readperm' => $alang['block_thread_order_readperm'],
			'price' => $alang['block_thread_order_price']
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
	'bbsurltype' => array(
		'type' => 'radio',
		'alang' => 'block_thread_title_bbsurltype',
		'options' => $bbsurltypearr
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
	'messagelen' => array(
		'type' => 'input',
		'alang' => 'block_thread_title_messagelen',
		'size' => '10'
	),
	'messagedot' => array(
		'type' => 'radio',
		'alang' => 'block_thread_title_messagedot',
		'options' => array('0'=>$alang['block_dot_0'], '1'=>$alang['block_dot_1'])
	),
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	)
)

);

?>