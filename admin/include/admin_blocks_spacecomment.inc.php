<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_spacecomment.inc.php 11157 2009-02-20 08:31:58Z zhaolei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

if(!isset($theblcokvalue['setcid'])) $theblcokvalue['setcid'] = '';
if($theblcokvalue['setcid'] == '1') {
	$divsetcid1display = '';
	$divsetcid2display = 'none';
} else {
	$divsetcid1display = 'none';
	$divsetcid2display = '';
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

//系统类型
$types = array();
foreach ($_SGLOBAL['type'] as $value) {
	$types[$value] = $lang[$value];
}

$blockarr = array();

//WHERE
$blockarr['where'] = array(
	'setcid' => array(
		'type' =>'radio',
		'alang' => 'block_spacecomment_title_setcid',
		'options' => array('0'=>$alang['block_thread_settid_0'], '1'=>$alang['block_thread_settid_1']),
		'other' => ' onclick="jssettid(this.value)"',
		'width' => '30%'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid1" style="display:'.$divsetcid1display.'">'
	),
	'cid' => array(
		'type' => 'input',
		'alang' => 'block_spacecomment_title_cid',
		'size' => '60'
	),
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid2" style="display:'.$divsetcid2display.'">'
	),
	'type' => array(
		'type' => 'select',
		'alang' => 'block_spacecomment_title_type',
		'options' => $types
	),
	'itemid' => array(
		'type' => 'input',
		'alang' => 'block_spacecomment_title_itemid'
	),
	'uid' => array(
		'type' => 'input',
		'alang' => 'block_spacecomment_title_uid'
	),
	'authorid' => array(
		'type' => 'input',
		'alang' => 'block_spacecomment_title_authorid'
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
			'dateline' => $alang['block_spacecomment_order_dateline'],
			'rates' => $alang['block_spacecomment_order_rates']
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
);

?>