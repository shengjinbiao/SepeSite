<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_spaceitem.inc.php 13493 2009-11-11 06:15:33Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

if(!in_array($type, $_SGLOBAL['type']) && in_array($type, $_SCONFIG['closechannels'])) {
	showmessage('block_type_error');
}

$catlistarr = getcategory($type);

if(!isset($theblcokvalue['setitemid'])) $theblcokvalue['setitemid'] = '';
if($theblcokvalue['setitemid'] == '1') {
	$divsetitemid1display = '';
	$divsetitemid2display = 'none';
} else {
	$divsetitemid1display = 'none';
	$divsetitemid2display = '';
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

$toparr = array(
	'1' => $alang['space_top_1'],
	'2' => $alang['space_top_2'],
	'3' => $alang['space_top_3']
);

$digestarr = array(
	'1' => $alang['space_digest_1'],
	'2' => $alang['space_digest_2'],
	'3' => $alang['space_digest_3']
);

//ÉóºË¼¶±ð
$gradearr = array(
	'1' => $alang['check_grade_1'],
	'2' => $alang['check_grade_2'],
	'3' => $alang['check_grade_3'],
	'4' => $alang['check_grade_4'],
	'5' => $alang['check_grade_5']
);
if(!empty($_SCONFIG['checkgrade'])) {
	$newgradearr = explode("\t", $_SCONFIG['checkgrade']);
	for($i=0; $i<5; $i++) {
		if(!empty($newgradearr[$i])) $gradearr[$i+1] = $newgradearr[$i];
	}
}

//multi
$showmultipage = array(
	'0' => $alang['space_showmultipage_0'],
	'1' => $alang['space_showmultipage_1']
);

$blockarr = array();

//WHERE
$blockarr['where'][] = array(
	'setitemid' => array(
		'type' =>'radio',
		'alang' => 'block_'.$blocktype.'_title_setitemid',
		'options' => array('0'=>$alang['block_thread_settid_0'], '1'=>$alang['block_thread_settid_1']),
		'other' => ' onclick="jssettid(this.value)"',
		'width' => '30%'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid1" style="display:'.$divsetitemid1display.'">'
	),
	'itemid' => array(
		'type' => 'input',
		'alang' => 'block_'.$blocktype.'_title_itemid',
		'size' => '60'
	),
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid2" style="display:'.$divsetitemid2display.'">'
	),
	'type' => array(
		'type' => 'hidden'
	)
);

$blockarr['where'][] = array(
	'grade' => array(
		'type' => 'checkbox',
		'alang' => 'block_spaceitem_title_grade',
		'options' => $gradearr
	),
	'catid' => array(
		'type' => 'select-div',
		'alang' => 'block_spaceitem_title_catid',
		'check' => 1,
		'options' => $catlistarr
	)
);
	
if($blocktype == 'spacenews') {
	$blockarr['where'][] = array(
	'haveattach' => array(
		'type' => 'radio',
		'alang' => 'block_spaceitem_title_haveattach',
		'options' => array('0'=>$alang['block_thread_attachment_0'], '1'=>$alang['block_thread_attachment_1'], '2'=>$alang['block_thread_attachment_4'])
		)
	);
}
	
$temparr = array(
	'digest' => array(
		'type' => 'checkbox',
		'alang' => 'block_spaceitem_title_digest',
		'options' => $digestarr
	),
	'top' => array(
		'type' => 'checkbox',
		'alang' => 'block_spaceitem_title_top',
		'options' => $toparr
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
	),
	'hot' => array(
		'type' => 'input2',
		'alang' => 'block_spaceitem_title_click',
		'size' => '10'
	)
);

@include_once S_ROOT.'/data/system/click.cache.php';
$clickgroupids = array_keys($_SGLOBAL['clickgroup']['spaceitems']);
foreach ($_SGLOBAL['click'] as $key => $kvalue) {
	if(in_array($key, $clickgroupids)) {
		foreach ($kvalue as $value) {
			if(!is_int($value['name'])){
				$temparr = array_merge($temparr, array('click_'.$value['clickid']=>array('type'=>'input2', 'alang'=>$_SGLOBAL['clickgroup']['spaceitems'][$key]['grouptitle'].':'.$value['name'], 'size'=>'10')));
			}
		}	
	}
}

$blockarr['where'][] = $temparr;

$blockarr['where'][] = array(
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	)
);

$blockarrwhere = array();
for($i=0; $i<count($blockarr['where']); $i++) {
	$blockarrwhere = array_merge($blockarrwhere, $blockarr['where'][$i]);
}
$blockarr['where'] = $blockarrwhere;

//ORDER
$blockarr['order'] = array(
	'order' => array(
		'type' => 'select-order',
		'alang' => 'block_thread_title_order',
		'options' => array(
			'' => '------',
			'i.dateline' => $alang['block_thread_order_dateline'],
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
	),
	'showcategory' => array(
		'type' => 'radio',
		'alang' => 'block_thread_title_showcategory',
		'options' => array('0'=>$alang['block_showattach_0'], '1'=>$alang['block_showattach_1'])
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
);


?>