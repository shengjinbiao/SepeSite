<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_bbsforum.inc.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

if(!isset($theblcokvalue['setfid'])) $theblcokvalue['setfid'] = '';
if($theblcokvalue['setfid'] == '1') {
	$divsetfid1display = '';
	$divsetfid2display = 'none';
} else {
	$divsetfid1display = 'none';
	$divsetfid2display = '';
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

$typearr = array(
	'group' => $alang['block_forum_type_group'],
	'forum' => $alang['block_forum_type_forum'],
	'sub' => $alang['block_forum_type_sub'],
);

$allowspacearr = array(
	'0' => $alang['block_forum_allow_0'],
	'1' => $alang['block_forum_allow_1']
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
	'setfid' => array(
		'type' =>'radio',
		'alang' => 'block_forum_title_setfid',
		'options' => array('0'=>$alang['block_thread_settid_0'], '1'=>$alang['block_thread_settid_1']),
		'other' => ' onclick="jssettid(this.value)"',
		'width' => '30%'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid1" style="display:'.$divsetfid1display.'">'
	),
	'fid' => array(
		'type' => 'input',
		'alang' => 'block_forum_title_fid',
		'size' => '60'
	),
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid2" style="display:'.$divsetfid2display.'">'
	),
	'fup' => array(
		'type' => 'input',
		'alang' => 'block_forum_title_fup',
		'size' => '60'
	),
	'type' => array(
		'type' => 'checkbox',
		'alang' => 'block_forum_title_type',
		'options' => $typearr
	),
	'threads' => array(
		'type' => 'input2',
		'alang' => 'block_forum_title_threads',
		'size' => '10'
	),
	'posts' => array(
		'type' => 'input2',
		'alang' => 'block_forum_title_posts',
		'size' => '10'
	),
	'todayposts' => array(
		'type' => 'input2',
		'alang' => 'block_forum_title_todayposts',
		'size' => '10'
	),
	'allowblog' => array(
		'type' => 'radio',
		'alang' => 'block_forum_title_allowblog',
		'options' => $allowspacearr
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
			'displayorder' => $alang['block_forum_order_displayorder'],
			'threads' => $alang['block_forum_order_threads'],
			'posts' => $alang['block_forum_order_posts'],
			'todayposts' => $alang['block_forum_order_todayposts']
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
	'descriptionlen' => array(
		'type' => 'input',
		'alang' => 'block_forum_title_descriptionlen',
		'size' => '10'
	),
	'descriptiondot' => array(
		'type' => 'radio',
		'alang' => 'block_forum_title_descriptiondot',
		'options' => array('0'=>$alang['block_dot_0'], '1'=>$alang['block_dot_1'])
	),
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	)
)

);

?>