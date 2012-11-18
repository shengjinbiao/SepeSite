<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: site.php 10928 2009-01-06 05:13:03Z zhanglijun $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(!empty($_SGET['type'])) {
	$tplname = 'site_'.trim(str_replace(array('..', '/', '\\'), '', $_SGET['type']));
	if($_SGET['type'] == 'map') {
		include_once(S_ROOT.'./function/model.func.php');
		$modelarr = array();
		foreach($channels['menus'] as $key => $val) {
			if($val['type'] == 'model' && $val['status'] == 1) {
				$catlistarr = getmodelinfoall('modelname', $key);
				$modelarr[$key] = array(
					'modelalias' => $catlistarr['models']['modelalias'],
					'modelname' => $catlistarr['models']['modelname'],
					'categories' => $catlistarr['categories'],
				);
			}
		}
		$title = $lang['site_map'];
	} elseif($_SGET['type'] == 'link') {
		$title = $lang['site_link'];
	}
} else {
	header('Location: '.S_URL);
	exit;
}



if(file_exists(S_ROOT.'./templates/'.$_SCONFIG['template'].'/'.$tplname.'.html.php')) {
	include template($tplname);
} else {
	header('Location: '.S_URL);
}
ob_out();
?>