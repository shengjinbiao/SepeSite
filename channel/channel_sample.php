<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: channel_sample.php 13303 2009-08-31 05:32:16Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

/*
	������δ��룬���ø�ҳ���Ƿ�����html�ļ�
	�������Ҫ��ҳ���Զ����� html���Ϳ������ñ��� $makehtml = 1;
	���� $makehtml = 0; ������html�ļ�
	$htmlupdatetime ������Ϊ���� ����html�󣬳���ÿ���೤ʱ���Զ�
	�������ɵ�html�ļ�����λΪ����
	���磬��������Ϊ $htmlupdatetime = 3600; ��1Сʱ���Զ�����
	���ɵ�html��̬�ļ���
	Ҳ����ʹ��ϵͳ���õı�����������Բο� news.php �ļ���д����
*/

$makehtml = 0;
$htmlupdatetime = 3600;

/*
	������δ��룬�Ƕ�����html������
	��Щ���룬�뱣��Ĭ�ϼ��ɡ�һ������£�����Ҫ�޸ġ�
*/

if(!empty($makehtml)) {
	$_SHTML['action'] = $_SGET['action'];
	$_SHTML['name'] = $_SGET['name'];
	if(!empty($_SGET['page'])) $_SHTML['page'] = intval($_SGET['page']);
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $htmlupdatetime);
	$_SCONFIG['debug'] = 0;
}

/*
	�����Ҫͨ�����ݿ�����ض����ݲ�ѯ
	����Ԥ������һЩ���������������λ�ý�������php��̡�
	һ������£��������ұ�̡�
	��ֻ��Ҫ��ģ���ļ��У�д��SupeSite��ģ����ô��빦�ܾͿ���ʵ�ִ󲿷ֹ��ܡ�
*/

// ���ұ�̿�ʼ 
// .........
// .........
// .........
// .........
// .........
// ���ұ�̽���

/*
	������Щ���룬�����˵���ģ�塢���ɻ��桢����html�ȹ���
	��Щ���룬�뱣��Ĭ�ϼ��ɡ����ر�������벻Ҫ�޸ġ�
	
	�����Ҫ���Ĺ���������ȥ�޸Ķ�Ӧ��ģ���ļ��ˡ�
	��ģ���ļ��У���Ϳ���ʹ�� SupeSite ǿ���ģ�鹦�ܣ���Discuz!��̳
	�����������Ϣ���������ۺ�չʾ��
	�������Լ���Ƶ��ҳ���ˡ�
*/

if(!isset($_SGET['name'])) $_SGET['name'] = $_SCONFIG['defaultchannel'];
$keywords = $description = $title = $_SCONFIG['channel'][$_SGET['name']]['name'];

//Ȩ���ж�
$channel = $_SGET['name'];
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

if(empty($_SCONFIG['channel'][$_SGET['name']]['tpl'])) {
	exit('Channel Template File Not found or have no access!');
} else {
	$tplname = $_SCONFIG['channel'][$_SGET['name']]['tpl'];
}

include template($tplname);

ob_out();

if(!empty($makehtml)) {
	ehtml('make');
} else {
	maketplblockvalue('cache');
}

exit();

?>