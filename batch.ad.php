<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.ad.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

define('IN_SUPESITE', TRUE);

$adid = empty($_GET['id'])?0:intval($_GET['id']);
if(empty($adid)) exit();
$_SGLOBAL = array();

@include_once('./data/system/aduser.cache.php');
@include_once('./function/common.func.php');

if(empty($_SGLOBAL['ad'][$adid])) exit();
$thead = $_SGLOBAL['ad'][$adid];

$parameters = $thead['parameters'];
if($thead['adtype'] == 'iframe') {
	$thead['adiframecontent'] = $parameters['adiframecontent'];
	print<<<END
	<div>$thead[adiframecontent]</div>
END;

} elseif($thead['adtype'] == 'js') {
	$code = $parameters['adjscontent'];
	$code = str_replace(array('<!--', '//-->'), '', $code);
	$code = preg_replace("/(\r|\n)/s", '', $code);
	$code = addcslashes($code, '\'"\\');
	echo "document.write('$code')";
}
?>