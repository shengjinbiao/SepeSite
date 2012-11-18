<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.tagshow.php 13470 2009-11-04 07:19:53Z zhaofei $
*/

include_once('./common.php');
include_once(S_ROOT.'./language/batch.lang.php');

$tagname = empty($_GET['tagname'])?'':trim($_GET['tagname']);
if(empty($tagname)) showxml($blang['parameter_error']);

$html = '<h5><a href="javascript:taghide();" target="_self">'.$blang['close'].'</a>TAG: '.$tagname.'</h5>
<div class="xspace-ajaxcontent">
<ul style="margin: 0.5em; padding-left: 2.7em; text-indent: -2.7em; list-style: none; line-height: 1.8em;">';

$query = $_SGLOBAL['db']->query('SELECT tagid FROM '.tname('tags').' WHERE tagname=\''.$tagname.'\'');
if($tag = $_SGLOBAL['db']->fetch_array($query)) {
	$query = $_SGLOBAL['db']->query('SELECT st.tagid, i.uid, i.type, i.itemid, i.subject FROM '.tname('spacetags').' st INNER JOIN '.tname('spaceitems').' i ON i.itemid=st.itemid WHERE st.tagid=\''.$tag['tagid'].'\' ORDER BY st.dateline DESC LIMIT 0,10');
	while($item = $_SGLOBAL['db']->fetch_array($query)) {
		$item['url'] = geturl('action/viewnews/itemid/'.$item['itemid'], 2);
		$html .= '<li>['.$channels[menus][$item['type']][name].'] <a href="'.$item['url'].'" target="_blank">'.$item['subject'].'</a></li>';
	}
	$html .= '
	</ul>
	<p style="margin: 0; padding: 0.5em; border-top: 1px dotted #EEE; text-align: right;"><a href="'.geturl('action/tag/tagid/'.$tag['tagid']).'" target="_blank">'.$blang['see_more'].'</a></p>';
} else {
	$html .= '<li>'.$blang['not_found_the_tag'].'</li></ul>';
}

$html .= '</div>';

showxml($html);

?>