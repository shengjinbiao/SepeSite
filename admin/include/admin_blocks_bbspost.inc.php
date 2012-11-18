<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_bbspost.inc.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

$bbsforumlistarr = getbbsforum();

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

//multi
$showmultipage = array(
	'0' => $alang['space_showmultipage_0'],
	'1' => $alang['space_showmultipage_1']
);

$blockarr = array(

//WHERE
'where' => array(
	'setpid' => array(
		'type' =>'radio',
		'alang' => 'block_post_title_setpid',
		'options' => array('0'=>$alang['block_thread_settid_0'], '1'=>$alang['block_thread_settid_1']),
		'other' => ' onclick="jssettid(this.value)"',
		'width' => '30%'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid1" style="display:'.$divsettid1display.'">'
	),
	'pid' => array(
		'type' => 'input',
		'alang' => 'block_post_title_pid',
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
	'tid' => array(
		'type' => 'input',
		'alang' => 'block_post_title_tid',
		'size' => '60'
	),
	'first' => array(
		'type' => 'radio',
		'alang' => 'block_post_title_first',
		'options' => array('0'=>$alang['block_post_first_0'], '1'=>$alang['block_post_first_1'])
	),
	'attachment' => array(
		'type' => 'radio',
		'alang' => 'block_thread_title_attachment',
		'options' => array('0'=>$alang['block_thread_attachment_0'], '1'=>$alang['block_thread_attachment_1'])
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
			'dateline' => $alang['block_thread_order_dateline']
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
	'messagelen' => array(
		'type' => 'input',
		'alang' => 'block_thread_title_messagelen',
		'size' => '10'
	),
	'messagedot' => array(
		'type' => 'radio',
		'alang' => 'block_thread_title_messagedot',
		'options' => array('0'=>$alang['block_dot_0'], '1'=>$alang['block_dot_1'])
	)
)

);

?>