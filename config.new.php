<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: config.new.php 10885 2008-12-30 07:47:03Z zhaofei $
*/

$_SC = array();

//--------------- SupeSite���� ---------------
$_SC['dbhost'] = 'localhost';					//SupeSite���ݿ������(һ��Ϊ����localhost)
$_SC['dbuser'] = 'root';					//SupeSite���ݿ��û���
$_SC['dbpw'] = '';						//SupeSite���ݿ�����
$_SC['dbname'] = '';						//SupeSite���ݿ���
$_SC['tablepre'] = 'supe_';					//SupeSite����ǰ׺(��������̳�ı���ǰ׺��ͬ)
$_SC['pconnect'] = 0;						//SupeSite���ݿ�־����� 0=�ر�, 1=��
$_SC['dbcharset'] = 'utf8';					//SupeSite���ݿ��ַ���

$_SC['siteurl'] = '';						//SupeSite�����ļ�����Ŀ¼��URL���ʵ�ַ��������д�� http:// ��ͷ������URL��Ҳ������д���URL��ĩβ��Ҫ�� /����������޷��Զ���ȡ��������ֹ��޸�Ϊ http://www.yourwebsite.com/supesite ��ʽ

//--------------- Discuz!���� ---------------
$_SC['dbhost_bbs'] = 'localhost';				//Discuz!��̳���ݿ���������Ƽ������,���Discuz!��̳��SupeSiteӦ����ʹ��ͬһ̨MySQL������,�����뱣��Ϊ�ա������ȷ��ʹ�ò�ͬ��MySQL������,����дDiscuz!��̳ʹ�õ�Զ��MySQL������IP
$_SC['dbuser_bbs'] = 'root';					//Discuz!���ݿ��û���
$_SC['dbpw_bbs'] = '';						//Discuz!���ݿ�����
$_SC['dbname_bbs'] = '';					//Discuz!���ݿ���(�����SupeSite��װ��ͬһ�����ݿ⣬���ռ���)
$_SC['tablepre_bbs'] = 'cdb_';					//Discuz!����ǰ׺
$_SC['pconnect_bbs'] = '0';					//Discuz!���ݿ�־����� 0=�ر�, 1=��
$_SC['dbcharset_bbs'] = 'utf8';					//Discuz!���ݿ��ַ���
$_SC['bbsver'] = '';						//��̳�汾(ѡ��Discuz!��̳�İ汾�����磺7)

$_SC['bbsurl'] = '';						//��̳URL��ַ��������д��http://��ͷ������URL��Ҳ������д���URL��ĩβ��Ҫ�� /
$_SC['bbsattachurl'] = '';					//��̳����Ŀ¼URL��ַ(Ϊ����ϵͳ������̳Ĭ�ϸ���·����������޸�����̳Ĭ�ϸ�������Ŀ¼�������ø�ѡ��)

//--------------- UCenter HOME���� ---------------
$_SC['dbhost_uch'] = 'localhost';				//UCenter HOME���ݿ������
$_SC['dbuser_uch'] = 'root';					//UCenter HOME���ݿ��û���
$_SC['dbpw_uch'] = '';						//UCenter HOME���ݿ�����
$_SC['dbname_uch'] = '';					//UCenter HOME���ݿ���
$_SC['tablepre_uch'] = 'uchome_';				//UCenter HOME����ǰ׺
$_SC['pconnect_uch'] = '0';					//UCenter HOME���ݿ�־����� 0=�ر�, 1=��
$_SC['dbcharset_uch'] = 'utf8';					//UCenter HOME���ݿ��ַ���

$_SC['uchurl'] = '';						//UCenter HOME URL��ַ��������д��http://��ͷ������URL��Ҳ������д���URL��ĩβ��Ҫ�� /
$_SC['uchattachurl'] = '';					//UCenter HOME ����Ŀ¼URL��ַ(Ϊ����ϵͳ����Ĭ�ϸ���·����������޸���Ĭ�ϸ�������Ŀ¼�������ø�ѡ��)
$_SC['uchftpurl'] = '';						//Զ�̸������ʵ�ַ,֧�� HTTP �� FTP Э�飬��β��Ҫ��б�ܡ�/��

//��ȫ���
$_SC['founder'] = '1';						//��ʼ�� UID, ����֧�ֶ����ʼ�ˣ�֮��ʹ�� ��,�� �ָ������ֹ�����ֻ�д�ʼ�˲ſɲ�����
$_SC['dbreport'] = 0;						//�Ƿ������ݿ���󱨸�? 0=��, 1=��

//--------------- COOKIE���� ---------------
$_SC['cookiepre'] = 'supe_';					//Cookieǰ׺
$_SC['cookiedomain'] = '';					//cookie ������������Ϊ .yourdomain.com ��ʽ
$_SC['cookiepath'] = '/';					//cookie ����·��

//--------------- �ַ������� ---------------
$_SC['headercharset'] = 1;					//ǿ�������ַ���,ֻ����ʱʹ��
$_SC['charset'] = 'utf-8';					//ҳ���ַ���(��ѡ 'gbk', 'big5', 'utf-8')

//--------------- �ʼ��������� ---------------
$_SC['adminemail'] = 'admin@yourdomain.com';			//ϵͳEmail
$_SC['sendmail_silent'] = 1;					//�����ʼ������е�ȫ��������ʾ, 1=��, 0=��
$_SC['mailsend'] = '1';						//�ʼ����ͷ�ʽ��0=�������κ��ʼ�

$mailcfg = array();
$mailcfg['maildelimiter'] = '1';
$mailcfg['mailusername'] = '1';
$mailcfg['server'] = 'smtp.126.com';				//SMTP ������
$mailcfg['port'] = '25';					//SMTP �˿�, Ĭ�ϲ����޸�

if($_SC['mailsend'] == 1) {
	//1=ͨ�� PHP ������ UNIX sendmail ����(�Ƽ��˷�ʽ)

} elseif($_SC['mailsend'] == 2) {

	//2=ͨ�� SOCKET ���� SMTP ����������(֧�� ESMTP ��֤)
	$mailcfg['auth'] = '1';					//�Ƿ���Ҫ AUTH LOGIN ��֤, 1=��, 0=��
	$mailcfg['from'] = 'supesitedemo@126.com';		//�����˵�ַ (�����Ҫ��֤,����Ϊ����������ַ)
	$mailcfg['auth_username'] = 'supesitedemo';		//��֤�û���
	$mailcfg['auth_password'] = 'supesitedemo';		//��֤����

} elseif($_SC['mailsend'] == 3) {
	//3=ͨ�� PHP ���� SMTP ���� Email(�� win32 ����Ч, ��֧�� ESMTP)

}

//--------------- ����ϵͳ���� ---------------
$_SC['tplrefresh'] = 1;						//���ģ���Զ�ˢ�¿��ء��رպ����޸�ģ��ҳ�����Ҫ�ֹ��������Ա��̨=>������� ����һ��ģ���ļ�������գ����ܿ����޸ĵ�Ч����
$_SC['cachegrade'] = 0;						//ϵͳ����ֱ�ȼ�(Ĭ��Ϊ1������ÿ����1���ֱ���Ŀ����255��������Խ�󣬵�����ĳߴ�ԽС)

//--------------- UCenter���� ---------------
define('UC_CONNECT', 'mysql');					// ���� UCenter �ķ�ʽ: mysql/NULL, Ĭ��Ϊ��ʱΪ fscoketopen(), mysql ��ֱ�����ӵ����ݿ�, Ϊ��Ч��, ������� mysql

// ���ݿ���� (mysql ����ʱ)
define('UC_DBHOST', 'localhost');				// UCenter ���ݿ�����
define('UC_DBUSER', 'root');					// UCenter ���ݿ��û���
define('UC_DBPW', '');						// UCenter ���ݿ�����
define('UC_DBNAME', '');					// UCenter ���ݿ�����
define('UC_DBCHARSET', 'utf8');					// UCenter ���ݿ��ַ���
define('UC_DBTABLEPRE', '');					// UCenter ���ݿ��ǰ׺
define('UC_DBCONNECT', '0');					// UCenter ���ݿ�־����� 0=�ر�, 1=��

// ͨ�����
define('UC_KEY', '');						// �� UCenter ��ͨ����Կ, Ҫ�� UCenter ����һ��
define('UC_API', '');						// UCenter �� URL ��ַ, �ڵ���ͷ��ʱ�����˳���
define('UC_CHARSET', 'utf-8');					// UCenter ���ַ���
define('UC_IP', '');						// UCenter �� IP, �� UC_CONNECT Ϊ�� mysql ��ʽʱ, ���ҵ�ǰӦ�÷�������������������ʱ, �����ô�ֵ
define('UC_APPID', '');						// ��ǰӦ�õ� ID
define('UC_PPP', '20');

//-------------------------------------------

?>