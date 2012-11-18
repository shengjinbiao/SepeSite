<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_blocks_friendlink.inc.php 12219 2009-05-22 01:13:24Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

if(!isset($theblcokvalue['setid'])) $theblcokvalue['setid'] = '';
if($theblcokvalue['setid'] == '1') {
	$divsetid1display = '';
	$divsetid2display = 'none';
} else {
	$divsetid1display = 'none';
	$divsetid2display = '';
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

$blockarr = array(

	//WHERE
	'where' => array(
		'setid' => array(
			'type' =>'radio',
			'alang' => 'block_xs_link_title_setid',
			'options' => array('0'=>$alang['block_thread_settid_0'], '1'=>$alang['block_thread_settid_1']),
			'other' => ' onclick="jssettid(this.value)"',
			'width' => '30%'
		),
		array(
			'type' => 'eval',
			'text' => '<tbody id="divsettid1" style="display:'.$divsetid1display.'">'
		),
		'id' => array(
			'type' => 'input',
			'alang' => 'block_xs_link_title_id',
			'size' => '60'
		),
		array(
			'type' => 'eval',
			'text' => '</tbody>'
		),
		array(
			'type' => 'eval',
			'text' => '<tbody id="divsettid2" style="display:'.$divsetid2display.'">'
		),
		'description' => array(
			'type' => 'radio',
			'alang' => 'block_link_title_note',
			'options' => array('0'=>$alang['block_link_note_0'], '1'=>$alang['block_link_note_1'])
		),
		'logo' => array(
			'type' => 'radio',
			'alang' => 'block_link_title_logo',
			'options' => array('0'=>$alang['block_link_logo_0'], '1'=>$alang['block_link_logo_1'])
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
				'displayorder' => $alang['block_link_order_displayorder'],
				'id' => $alang['block_link_order_id']
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
		'namelen' => array(
			'type' => 'input',
			'alang' => 'block_link_title_namelen',
			'size' => '10'
		),		
		'namedot' => array(
			'type' => 'radio',
			'alang' => 'block_link_title_namedot',
			'options' => array('0'=>$alang['block_dot_0'], '1'=>$alang['block_dot_1'])
		),
		'notelen' => array(
			'type' => 'input',
			'alang' => 'block_link_title_notelen',
			'size' => '10'
		),
		'notedot' => array(
			'type' => 'radio',
			'alang' => 'block_link_title_notedot',
			'options' => array('0'=>$alang['block_dot_0'], '1'=>$alang['block_dot_1'])
		),
	)

);

?>
