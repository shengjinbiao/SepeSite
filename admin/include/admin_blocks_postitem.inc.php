<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_spaceitem.inc.php 11951 2009-04-16 01:34:12Z zhaolei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

@include_once S_ROOT.'/data/system/click.cache.php';

$clickgroupids = array_keys($_SGLOBAL['clickgroup']['postitems']);

if(empty($_GET['name'])) $_GET['name'] = 'news';
$catlistarr = getcategory($_GET['name']);

if(!isset($theblcokvalue['setitemid'])) $theblcokvalue['setitemid'] = '';
if($theblcokvalue['setitemid'] == '1') {
	$divsetitemid1display = '';
	$divsetitemid2display = 'none';
} else {
	$divsetitemid1display = 'none';
	$divsetitemid2display = '';
}

$blockarr = array();
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

$temparr = array(
	'catid' => array(
		'type' => 'select-div',
		'alang' => 'block_spaceitem_title_catid',
		'check' => 1,
		'options' => $catlistarr
	),
	'uid' => array(
		'type' => 'input',
		'alang' => 'block_spaceitem_title_uid',
		'size' => '60'
	),
	'hot' => array(
		'type' => 'input2',
		'alang' => 'block_spaceitem_title_click',
		'size' => '10'	
	)
	
);

foreach ($_SGLOBAL['click'] as $key => $kvalue) {
	if(in_array($key, $clickgroupids)) {
		foreach ($kvalue as $value) {
			$temparr = array_merge($temparr, array('click_'.$value['clickid']=>array('type'=>'input2', 'alang'=>$_SGLOBAL['clickgroup']['postitems'][$key]['grouptitle'].':'.$value['name'], 'size'=>'10')));
		}	
	}
}

$blockarr['where'][] = $temparr;

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
			'i.lastpost' => $alang['block_thread_order_lastpost']
		)
	)
);

//multi
$showmultipage = array(
	'0' => $alang['space_showmultipage_0'],
	'1' => $alang['space_showmultipage_1']
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