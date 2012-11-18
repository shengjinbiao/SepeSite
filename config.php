<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: config.new.php 10885 2008-12-30 07:47:03Z zhaofei $
*/

$_SC = array();

//--------------- SupeSite设置 ---------------
$_SC['dbhost'] = 'localhost';					//SupeSite数据库服务器(一般为本地localhost)
$_SC['dbuser'] = 'root';					//SupeSite数据库用户名
$_SC['dbpw'] = '';						//SupeSite数据库密码
$_SC['dbname'] = '';						//SupeSite数据库名
$_SC['tablepre'] = 'supe_';					//SupeSite表名前缀(不能与论坛的表名前缀相同)
$_SC['pconnect'] = 0;						//SupeSite数据库持久连接 0=关闭, 1=打开
$_SC['dbcharset'] = 'utf8';					//SupeSite数据库字符集

$_SC['siteurl'] = '';						//SupeSite程序文件所在目录的URL访问地址。可以填写以 http:// 开头的完整URL，也可以填写相对URL。末尾不要加 /。如果程序无法自动获取，请务必手工修改为 http://www.yourwebsite.com/supesite 形式

//--------------- Discuz!设置 ---------------
$_SC['dbhost_bbs'] = 'localhost';				//Discuz!论坛数据库服务器。推荐情况下,你的Discuz!论坛与SupeSite应该是使用同一台MySQL服务器,所以请保留为空。如果你确认使用不同的MySQL服务器,请填写Discuz!论坛使用的远程MySQL服务器IP
$_SC['dbuser_bbs'] = 'root';					//Discuz!数据库用户名
$_SC['dbpw_bbs'] = '';						//Discuz!数据库密码
$_SC['dbname_bbs'] = '';					//Discuz!数据库名(如果与SupeSite安装在同一个数据库，留空即可)
$_SC['tablepre_bbs'] = 'cdb_';					//Discuz!表名前缀
$_SC['pconnect_bbs'] = '0';					//Discuz!数据库持久连接 0=关闭, 1=打开
$_SC['dbcharset_bbs'] = 'utf8';					//Discuz!数据库字符集
$_SC['bbsver'] = '';						//论坛版本(选择Discuz!论坛的版本，例如：7)

$_SC['bbsurl'] = '';						//论坛URL地址。可以填写以http://开头的完整URL，也可以填写相对URL。末尾不要加 /
$_SC['bbsattachurl'] = '';					//论坛附件目录URL地址(为空则系统将用论坛默认附件路径，如果您修改了论坛默认附件保存目录，请设置该选项)

//--------------- UCenter HOME设置 ---------------
$_SC['dbhost_uch'] = 'localhost';				//UCenter HOME数据库服务器
$_SC['dbuser_uch'] = 'root';					//UCenter HOME数据库用户名
$_SC['dbpw_uch'] = '';						//UCenter HOME数据库密码
$_SC['dbname_uch'] = '';					//UCenter HOME数据库名
$_SC['tablepre_uch'] = 'uchome_';				//UCenter HOME表名前缀
$_SC['pconnect_uch'] = '0';					//UCenter HOME数据库持久连接 0=关闭, 1=打开
$_SC['dbcharset_uch'] = 'utf8';					//UCenter HOME数据库字符集

$_SC['uchurl'] = '';						//UCenter HOME URL地址。可以填写以http://开头的完整URL，也可以填写相对URL。末尾不要加 /
$_SC['uchattachurl'] = '';					//UCenter HOME 附件目录URL地址(为空则系统将用默认附件路径，如果您修改了默认附件保存目录，请设置该选项)
$_SC['uchftpurl'] = '';						//远程附件访问地址,支持 HTTP 和 FTP 协议，结尾不要加斜杠“/”

//安全相关
$_SC['founder'] = '1';						//创始人 UID, 可以支持多个创始人，之间使用 “,” 分隔。部分管理功能只有创始人才可操作。
$_SC['dbreport'] = 0;						//是否发送数据库错误报告? 0=否, 1=是

//--------------- COOKIE设置 ---------------
$_SC['cookiepre'] = 'supe_';					//Cookie前缀
$_SC['cookiedomain'] = '';					//cookie 作用域。请设置为 .yourdomain.com 形式
$_SC['cookiepath'] = '/';					//cookie 作用路径

//--------------- 字符集设置 ---------------
$_SC['headercharset'] = 1;					//强制设置字符集,只乱码时使用
$_SC['charset'] = 'utf-8';					//页面字符集(可选 'gbk', 'big5', 'utf-8')

//--------------- 邮件发送配置 ---------------
$_SC['adminemail'] = 'admin@yourdomain.com';			//系统Email
$_SC['sendmail_silent'] = 1;					//屏蔽邮件发送中的全部错误提示, 1=是, 0=否
$_SC['mailsend'] = '1';						//邮件发送方式。0=不发送任何邮件

$mailcfg = array();
$mailcfg['maildelimiter'] = '1';
$mailcfg['mailusername'] = '1';
$mailcfg['server'] = 'smtp.126.com';				//SMTP 服务器
$mailcfg['port'] = '25';					//SMTP 端口, 默认不需修改

if($_SC['mailsend'] == 1) {
	//1=通过 PHP 函数及 UNIX sendmail 发送(推荐此方式)

} elseif($_SC['mailsend'] == 2) {

	//2=通过 SOCKET 连接 SMTP 服务器发送(支持 ESMTP 验证)
	$mailcfg['auth'] = '1';					//是否需要 AUTH LOGIN 验证, 1=是, 0=否
	$mailcfg['from'] = 'supesitedemo@126.com';		//发信人地址 (如果需要验证,必须为本服务器地址)
	$mailcfg['auth_username'] = 'supesitedemo';		//验证用户名
	$mailcfg['auth_password'] = 'supesitedemo';		//验证密码

} elseif($_SC['mailsend'] == 3) {
	//3=通过 PHP 函数 SMTP 发送 Email(仅 win32 下有效, 不支持 ESMTP)

}

//--------------- 其他系统参数 ---------------
$_SC['tplrefresh'] = 1;						//风格模板自动刷新开关。关闭后，你修改模板页面后，需要手工进入管理员后台=>缓存更新 进行一下模板文件缓存清空，才能看到修改的效果。
$_SC['cachegrade'] = 0;						//系统缓存分表等级(默认为1，级别每增加1，分表数目增加255个，级别越大，单个表的尺寸越小)

//--------------- UCenter设置 ---------------
define('UC_CONNECT', 'mysql');					// 连接 UCenter 的方式: mysql/NULL, 默认为空时为 fscoketopen(), mysql 是直接连接的数据库, 为了效率, 建议采用 mysql

// 数据库相关 (mysql 连接时)
define('UC_DBHOST', 'localhost');				// UCenter 数据库主机
define('UC_DBUSER', 'root');					// UCenter 数据库用户名
define('UC_DBPW', '');						// UCenter 数据库密码
define('UC_DBNAME', '');					// UCenter 数据库名称
define('UC_DBCHARSET', 'utf8');					// UCenter 数据库字符集
define('UC_DBTABLEPRE', '');					// UCenter 数据库表前缀
define('UC_DBCONNECT', '0');					// UCenter 数据库持久连接 0=关闭, 1=打开

// 通信相关
define('UC_KEY', '');						// 与 UCenter 的通信密钥, 要与 UCenter 保持一致
define('UC_API', '');						// UCenter 的 URL 地址, 在调用头像时依赖此常量
define('UC_CHARSET', 'utf-8');					// UCenter 的字符集
define('UC_IP', '');						// UCenter 的 IP, 当 UC_CONNECT 为非 mysql 方式时, 并且当前应用服务器解析域名有问题时, 请设置此值
define('UC_APPID', '');						// 当前应用的 ID
define('UC_PPP', '20');

//-------------------------------------------

?>