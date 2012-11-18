<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_category.inc.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

if(!isset($theblcokvalue['setcatid'])) $theblcokvalue['setcatid'] = '';
if($theblcokvalue['setcatid'] == '1') {
	$divsetcatid1display = '';
	$divsetcatid2display = 'none';
} else {
	$divsetcatid1display = 'none';
	$divsetcatid2display = '';
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

$typearr = array(
	'news' => $alang['common_type_news']
);

$isrootarr = array(
	'0' => $alang['block_cat_isroot_0'],
	'1' => $alang['block_cat_isroot_1'],
	'2' => $alang['block_cat_isroot_2']
);

//multi
$showmultipage = array(
	'0' => $alang['space_showmultipage_0'],
	'1' => $alang['space_showmultipage_1']
);

$blockarr = array();

//WHERE
$blockarr['where'] = array(
	'setcatid' => array(
		'type' =>'radio',
		'alang' => 'block_cat_title_setcatid',
		'options' => array('0'=>$alang['block_thread_settid_0'], '1'=>$alang['block_thread_settid_1']),
		'other' => ' onclick="jssettid(this.value)"',
		'width' => '30%'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid1" style="display:'.$divsetcatid1display.'">'
	),
	'catid' => array(
		'type' => 'input',
		'alang' => 'block_cat_title_catid',
		'size' => '60'
	),
	array(
		'type' => 'eval',
		'text' => '</tbody>'
	),
	array(
		'type' => 'eval',
		'text' => '<tbody id="divsettid2" style="display:'.$divsetcatid2display.'">'
	),
	'type' => array(
		'type' => 'checkbox',
		'alang' => 'block_cat_title_type',
		'options' => $typearr
	),	
	'upid' => array(
		'type' => 'input',
		'alang' => 'block_cat_title_upid',
	),
	'isroot' => array(
		'type' => 'radio',
		'alang' => 'block_cat_title_isroot',
		'options' => $isrootarr
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
			'c.displayorder' => $alang['block_cat_order_displayorder']
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