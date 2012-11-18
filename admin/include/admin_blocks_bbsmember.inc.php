<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_bbsmember.inc.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

dbconnect(1);

$groupidarr = array();
$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('usergroups', 1));
while ($group = $_SGLOBAL['db_bbs']->fetch_array($query)) {	
	$groupidarr[$group['groupid']] = $group['grouptitle'];
}

if(!isset($theblcokvalue['setuid'])) $theblcokvalue['setuid'] = '';
if($theblcokvalue['setuid'] == '1') {
	$divsetuid1display = '';
	$divsetuid2display = 'none';
} else {
	$divsetuid1display = 'none';
	$divsetuid2display = '';
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
	'setuid' => array(
		'type' =>'radio',
		'alang' => 'block_member_title_setuid',
		'options' => array('0'=>$alang['block_thread_settid_0'], '1'=>$alang['block_thread_settid_1']),
		'other' => ' onclick="jssettid(this.value)"',
		'width' => '30%'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid1" style="display:'.$divsetuid1display.'">'
	),
	'uid' => array(
		'type' => 'input',
		'alang' => 'block_member_title_uid',
		'size' => '60'
	),
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid2" style="display:'.$divsetuid2display.'">'
	),
	'groupid' => array(
		'type' => 'checkbox',
		'alang' => 'block_member_title_groupid',
		'options' => $groupidarr
	),
	'regdate' => array(
		'type' => 'date',
		'alang' => 'block_member_title_regdate',
		'size' => '10'
	),
	'lastvisit' => array(
		'type' => 'date',
		'alang' => 'block_member_title_lastvisit',
		'size' => '10'
	),
	'lastpost' => array(
		'type' => 'date',
		'alang' => 'block_member_title_lastpost',
		'size' => '10'
	),
	'posts' => array(
		'type' => 'input2',
		'alang' => 'block_member_title_posts',
		'size' => '10'
	),
	'digestposts' => array(
		'type' => 'input2',
		'alang' => 'block_member_title_digestposts',
		'size' => '10'
	),
	'oltime' => array(
		'type' => 'input2',
		'alang' => 'block_member_title_oltime',
		'size' => '10'
	),
	'pageviews' => array(
		'type' => 'input2',
		'alang' => 'block_member_title_pageviews',
		'size' => '10'
	),
	'credits' => array(
		'type' => 'input2',
		'alang' => 'block_member_title_credits',
		'size' => '10'
	),
	'credits1' => array(
		'type' => 'input2',
		'alang' => 'block_member_title_credits1',
		'size' => '10'
	),
	'credits2' => array(
		'type' => 'input2',
		'alang' => 'block_member_title_credits2',
		'size' => '10'
	),
	'credits3' => array(
		'type' => 'input2',
		'alang' => 'block_member_title_credits3',
		'size' => '10'
	),
	'credits4' => array(
		'type' => 'input2',
		'alang' => 'block_member_title_credits4',
		'size' => '10'
	),
	'credits5' => array(
		'type' => 'input2',
		'alang' => 'block_member_title_credits5',
		'size' => '10'
	),
	'credits6' => array(
		'type' => 'input2',
		'alang' => 'block_member_title_credits6',
		'size' => '10'
	),
	'credits7' => array(
		'type' => 'input2',
		'alang' => 'block_member_title_credits7',
		'size' => '10'
	),
	'credits8' => array(
		'type' => 'input2',
		'alang' => 'block_member_title_credits8',
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
			'm.posts' => $alang['block_member_order_posts'],
			'm.digestposts' => $alang['block_member_order_digestposts'],
			'm.oltime' => $alang['block_member_order_oltime'],
			'm.pageviews' => $alang['block_member_order_pageviews'],
			'm.credits' => $alang['block_member_order_credits'],
			'm.extcredits1' => $alang['block_member_order_credits1'],
			'm.extcredits2' => $alang['block_member_order_credits2'],
			'm.extcredits3' => $alang['block_member_order_credits3'],
			'm.extcredits4' => $alang['block_member_order_credits4'],
			'm.extcredits5' => $alang['block_member_order_credits5'],
			'm.extcredits6' => $alang['block_member_order_credits6'],
			'm.extcredits7' => $alang['block_member_order_credits7'],
			'm.extcredits8' => $alang['block_member_order_credits8'],
			'm.regdate' => $alang['block_member_order_regdate'],
			'm.lastvisit' => $alang['block_member_order_lastvisit'],
			'm.lastpost' => $alang['block_member_order_lastpost'],
			'm.lastactivity' => $alang['block_member_order_lastactivity']
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
	'signaturelen' => array(
		'type' => 'input',
		'alang' => 'block_member_title_signaturelen',
		'size' => '10'
	),
	'signaturedot' => array(
		'type' => 'radio',
		'alang' => 'block_member_title_signaturedot',
		'options' => array('0'=>$alang['block_dot_0'], '1'=>$alang['block_dot_1'])
	),	
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	)
)

);

?>