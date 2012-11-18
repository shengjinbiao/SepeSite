<?php
/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: index.php 13470 2009-11-04 07:19:53Z zhaofei $
*/

@define('IN_SUPESITE', TRUE);

error_reporting(0);
$_SGLOBAL = $_SCONFIG = array();

//程序目录
define('S_ROOT', substr(dirname(__FILE__), 0, -7));

include_once(S_ROOT.'./function/common.func.php');

//获取时间
$_SGLOBAL['timestamp'] = time();

if(!@include_once(S_ROOT.'./config.php')) {
	@include_once(S_ROOT.'./config.new.php');
	show_msg('您需要首先将程序根目录下面的 "config.new.php" 文件重命名为 "config.php"', 999);
}

extract($_SC);

//GPC过滤
if(!(get_magic_quotes_gpc())) {
	$_GET = saddslashes($_GET);
	$_POST = saddslashes($_POST);
}

ob_start();

$theurl = 'index.php';
$sqlfile = S_ROOT.'./data/install.sql';
if(!file_exists($sqlfile)) {
	show_msg('请上传最新的 install.sql 数据库结构文件到程序的 ./data 目录下面，再重新运行本程序', 999);
}
$configfile = S_ROOT.'./config.php';

//变量
$step = empty($_GET['step'])?0:intval($_GET['step']);
$action = empty($_GET['action'])?'':trim($_GET['action']);
$nowarr = array('','','','','','','');
$formhash = formhash();

$lockfile = S_ROOT.'./data/install.lock';
if(file_exists($lockfile)) {
	show_msg('警告!您已经安装过SupeSite<br>
		为了保证数据安全，请立即手动删除 install/index.php 文件<br>
		如果您想重新安装SupeSite，请删除 data/install.lock 文件，再次运行安装文件');
}

//检查config是否可写
if(!@$fp = fopen($configfile, 'a')) {
	show_msg("文件 $configfile 读写权限设置错误，请设置为可写，再执行安装程序");
} else {
	@fclose($fp);
}

//提交处理
if (submitcheck('ucsubmit')) {

	//安装UC配置
	$step = 1;

	//判断域名是否解析
	$ucapi = preg_replace("/\/$/", '', trim($_POST['ucapi']));
	$ucip = trim($_POST['ucip']);

	if(empty($ucapi) || !preg_match("/^(http:\/\/)/i", $ucapi)) {
		show_msg('UCenter的URL地址不正确');
	} else {
		//检查服务器 dns 解析是否正常, dns 解析不正常则要求用户输入ucenter的ip地址
		if(!$ucip) {
			$temp = @parse_url($ucapi);
			$ucip = gethostbyname($temp['host']);
			if(ip2long($ucip) == -1 || ip2long($ucip) === FALSE) {
				$ucip = '';
			}
		}
	}

	//验证supesite是否安装
	if(!@include_once S_ROOT.'./uc_client/client.php') {
		show_msg('uc_client目录不存在');
	}
	$ucinfo = sfopen($ucapi.'/index.php?m=app&a=ucinfo&release='.UC_CLIENT_RELEASE, 500, '', '', 1, $ucip);
	list($status, $ucversion, $ucrelease, $uccharset, $ucdbcharset, $apptypes) = explode('|', $ucinfo);
	$dbcharset = strtolower(trim($_SC['dbcharset'] ? str_replace('-', '', $_SC['dbcharset']) : $_SC['dbcharset']));
	$ucdbcharset = strtolower(trim($ucdbcharset ? str_replace('-', '', $ucdbcharset) : $ucdbcharset));
	$apptypes = strtolower(trim($apptypes));
	if($status != 'UC_STATUS_OK') {
		show_header();
		print<<<END
		<form id="theform" method="post" action="$theurl">
		<table class="datatable">
		<tr><td><strong>UCenter无法正常连接，返回错误 ( $status )，请确认UCenter的IP地址是否正确</strong><br><br></td></tr>
		<tr><td>UCenter服务器的IP地址: <input type="text" name="ucip" value="$ucip"> 例如：192.168.0.1</td></tr>
		</table>
		<table class=button>
		<tr><td>
		<input type="hidden" name="ucapi" value="$ucapi">
		<input type="hidden" name="ucfounderpw" value="$_POST[ucfounderpw]">
		<input type="submit" id="ucsubmit" name="ucsubmit" value="确认IP地址"></td></tr>
		</table>
		<input type="hidden" name="formhash" value="$formhash">
		</form>
END;
		show_footer();
		exit();
	} elseif($dbcharset && $ucdbcharset && $ucdbcharset != $dbcharset) {
		show_msg('UCenter 服务端字符集与当前应用的字符集不同，请下载 '.$ucdbcharset.' 编码的 SupeSite 进行安装，下载地址：http://download.comsenz.com/');
	}
	$tagtemplates = 'apptagtemplates[template]='.urlencode('<a href="{url}" target="_blank">{subject}</a>').'&'.
		'apptagtemplates[fields][subject]='.urlencode('资讯标题').'&'.
		'apptagtemplates[fields][url]='.urlencode('资讯地址');
	$uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
	$app_url = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))).'://'.$_SERVER['HTTP_HOST'].substr($uri, 0, strrpos($uri, '/install/'));
	$app_name = trim($_POST['sitename']);
	$postdata = "m=app&a=add&ucfounder=&ucfounderpw=".urlencode($_POST['ucfounderpw'])."&apptype=".urlencode('SUPESITE')."&appname=".urlencode($app_name)."&appurl=".urlencode($app_url)."&appip=&appcharset=".$_SC['charset'].'&appdbcharset='.$_SC['dbcharset'].'&release='.UC_CLIENT_RELEASE.'&'.$tagtemplates;
	$s = sfopen($ucapi.'/index.php', 500, $postdata, '', 1, $ucip);
	if(empty($s)) {
		show_msg('UCenter用户中心无法连接');
	} elseif($s == '-1') {
		show_msg('UCenter管理员帐号密码不正确');
	} else {
		$ucs = explode('|', $s);
		if(empty($ucs[0]) || empty($ucs[1])) {
			show_msg('UCenter返回的数据出现问题，请参考:<br />'.shtmlspecialchars($s));
		} else {

			//处理成功
			$apphidden = '';
			//验证是否可以直接联接MySQL
			$link = mysql_connect($ucs[2], $ucs[4], $ucs[5], 1);
			$connect = $link && mysql_select_db($ucs[3], $link) ? 'mysql' : '';
			//返回
			foreach (array('key', 'appid', 'dbhost', 'dbname', 'dbuser', 'dbpw', 'dbcharset', 'dbtablepre', 'charset') as $key => $value) {
				if($value == 'dbtablepre') {
					$ucs[$key] = '`'.$ucs[3].'`.'.$ucs[$key];
				}
				$apphidden .= "<input type=\"hidden\" name=\"uc[$value]\" value=\"".$ucs[$key]."\" />";
			}
			//内置
			$apphidden .= "<input type=\"hidden\" name=\"uc[connect]\" value=\"$connect\" />";
			$apphidden .= "<input type=\"hidden\" name=\"uc[api]\" value=\"$_POST[ucapi]\" />";
			$apphidden .= "<input type=\"hidden\" name=\"uc[ip]\" value=\"$ucip\" />";

			show_header();
			print<<<END
			<form id="theform" method="post" action="$theurl">
			<table>
			<tr><td>UCenter注册成功！当前程序ID标识为: $ucs[1]</td></tr>
			</table>

			<table class=button>
			<tr><td>$apphidden
			<input type="submit" id="uc2submit" name="uc2submit" value="进入下一步"></td></tr>
			</table>
			<input type="hidden" name="formhash" value="$formhash">
			</form>
END;
			show_footer();
			exit();
		}
	}

} elseif (submitcheck('uc2submit')) {

	//增加congfig配置
	$step = 2;

	//写入config文件
	$configcontent = sreadfile($configfile);
	$keys = array_keys($_POST['uc']);
	foreach ($keys as $value) {
		$upkey = strtoupper($value);
		$configcontent = preg_replace("/define\('UC_".$upkey."'\s*,\s*'.*?'\)/i", "define('UC_".$upkey."', '".$_POST['uc'][$value]."')", $configcontent);
	}
	if(!$fp = fopen($configfile, 'w')) {
		show_msg("文件 $configfile 读写权限设置错误，请设置为可写后，再执行安装程序");
	}
	fwrite($fp, trim($configcontent));
	fclose($fp);

} elseif(!empty($_POST['sqlsubmit'])) {

	$step = 2;

	//先写入config文件
	$configcontent = sreadfile($configfile);
	$keys = array_keys($_POST['db']);
	foreach ($keys as $value) {
		$configcontent = preg_replace("/[$]\_SC\[\'".$value."\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$_SC['".$value."']\\1= '".$_POST['db'][$value]."'", $configcontent);
	}
	if(trim($_POST['siteurl']) != $siteurl){
		$configcontent = preg_replace("/[$]\_SC\[\'siteurl\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$_SC['siteurl']\\1= '".$_POST['siteurl']."'", $configcontent);
	}
	if(!$fp = fopen($configfile, 'w')) {
		show_msg("文件 $configfile 读写权限设置错误，请设置为可写后，再执行安装程序");
	}
	fwrite($fp, trim($configcontent));
	fclose($fp);

	//判断SupeSite数据库
	$havedata = false;
	if(!@mysql_connect($_POST['db']['dbhost'], $_POST['db']['dbuser'], $_POST['db']['dbpw'])) {
		show_msg('您输入的SupeSite数据库帐号不正确');
	}
	if(mysql_select_db($_POST['db']['dbname'])) {
		if(mysql_query("SELECT COUNT(*) FROM {$_POST['db']['tablepre']}members")) {
			$havedata = true;
		}
	} else {
		if(!mysql_query("CREATE DATABASE `".$_POST['db']['dbname']."`")) {
			show_msg('设定的SupeSite数据库无权限操作，请先手工操作后，再执行安装程序');
		}
	}

	if($havedata) {
		show_msg('危险!指定的SupeSite数据库已有数据，如果继续将会清空原有数据!', ($step+1));
	} else {
		show_msg('数据库配置成功，进入下一步操作', ($step+1), 1);
	}

} elseif (submitcheck('opensubmit')) {

	//检查用户身份
	$step = 5;

	include_once(S_ROOT.'./common.php');

	//UC注册用户
	if(!@include_once S_ROOT.'./uc_client/client.php') {
		showmessage('system_error');
	}
	$uid = uc_user_register($_POST['username'], $_POST['password'], 'webmastor@yourdomain.com');
	if($uid == -3) {
		//已存在，登录
		if(!$passport = getpassport($_POST['username'], $_POST['password'])) {
			show_msg('输入的用户名密码不正确，请确认');
		}
		$setarr = array(
			'uid' => $passport['uid'],
			'username' => addslashes($passport['username'])
		);
	} elseif($uid > 0) {
		$setarr = array(
			'uid' => $uid,
			'username' => $_POST['username']
		);
	} else {
		show_msg('输入的用户名无法注册，请重新确认');
	}
	$setarr['dateline'] = $_SGLOBAL['timestamp'];
	$setarr['updatetime'] = $_SGLOBAL['timestamp'];
	$setarr['lastlogin'] = $_SGLOBAL['timestamp'];
	$setarr['ip'] = $_SGLOBAL['onlineip'];
	$setarr['password'] = md5("$setarr[uid]|$_SGLOBAL[timestamp]");//本地密码随机生成
	$setarr['groupid'] = 1;//管理员

	//更新本地用户库
	inserttable('members', $setarr, 0, true);

	//反馈受保护
	$result = uc_user_addprotected($_POST['username'], $_POST['username']);

	//设置cookie
	ssetcookie('auth', authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'), 2592000);

	//写log
	if(@$fp = fopen($lockfile, 'w')) {
		fwrite($fp, 'SupeSite');
		fclose($fp);
	}

	show_msg('<font color="red">恭喜! SupeSite安装全部完成!</font>
		<br>为了您的数据安全，请登录ftp，删除本安装文件<br><br>
		您的管理员身份已经成功确认。接下来，您可以：<br>
		<br><a href="../admincp.php" target="_blank">进入站点管理平台</a>
		<br>以管理员身份对站点参数进行设置
		<br><a href="../index.php" target="_blank">访问站点首页</a>
		<br>立即访问自己的站点首页', 999);

}

if(empty($step)) {

	show_header();

	//检查权限设置
	$checkok = true;
	$perms = array();
	if(!checkfdperm(S_ROOT.'./config.php', 1)) {
		$perms['config'] = '失败';
		$checkok = false;
	} else {
		$perms['config'] = 'OK';
	}
	if(!checkfdperm(S_ROOT.'./attachments/')) {
		$perms['attachments'] = '失败';
		$checkok = false;
	} else {
		$perms['attachments'] = 'OK';
	}
	if(!checkfdperm(S_ROOT.'./cache/')) {
		$perms['cache'] = '失败';
		$checkok = false;
	} else {
		$perms['cache'] = 'OK';
	}
	if(!checkfdperm(S_ROOT.'./channel/')) {
		$perms['channel'] = '失败';
		$checkok = false;
	} else {
		$perms['channel'] = 'OK';
	}
	if(!checkfdperm(S_ROOT.'./html/')) {
		$perms['html'] = '失败';
		$checkok = false;
	} else {
		$perms['html'] = 'OK';
	}
	if(!checkfdperm(S_ROOT.'./log/')) {
		$perms['log'] = '失败';
		$checkok = false;
	} else {
		$perms['log'] = 'OK';
	}
	if(!checkfdperm(S_ROOT.'./styles/')) {
		$perms['styles'] = '失败';
		$checkok = false;
	} else {
		$perms['styles'] = 'OK';
	}
	if(!checkfdperm(S_ROOT.'./model/')) {
		$perms['model'] = '失败';
		$checkok = false;
	} else {
		$perms['model'] = 'OK';
	}
	if(!checkfdperm(S_ROOT.'./data/')) {
		$perms['data'] = '失败';
		$checkok = false;
	} else {
		$perms['data'] = 'OK';
	}
	if(!checkfdperm(S_ROOT.'./uc_client/data/')) {
		$perms['uc_data'] = '失败';
		$checkok = false;
	} else {
		$perms['uc_data'] = 'OK';
	}

	//安装阅读
	print<<<END
	<script type="text/javascript">
	function readme() {
		var tbl_readme = document.getElementById('tbl_readme');
		if(tbl_readme.style.display == '') {
			tbl_readme.style.display = 'none';
		} else {
			tbl_readme.style.display = '';
		}
	}
	</script>
	<table class="showtable">
	<tr><td>
	<strong>欢迎您使用SupeSite 社区门户产品</strong><br>
	希望我们的努力能为您提供一个高效快速的社区门户解决方案。
	<br><a href="javascript:;" onclick="readme()"><strong>请先认真阅读我们的软件使用授权协议</strong></a>
	</td></tr>
	</table>

	<table>
	</td></tr>
	<tr><td>
	<strong>文件/目录权限设置</strong><br>
	在您执行安装文件进行安装之前，先要设置相关的目录属性，以便数据文件可以被程序正确读/写/删/创建子目录。<br>
	推荐您这样做：<br>使用 FTP 软件登录您的服务器，将服务器上以下目录、以及该目录下面的所有文件的属性设置为777，win主机请设置internet来宾帐户可读写属性<br>
	<table class="datatable">
	<tr style="font-weight:bold;"><td>名称</td><td>所需权限属性</td><td>说明</td><td>检测结果</td></tr>
	<tr><td><strong>./config.php</strong></td><td>读/写</td><td>系统配置文件</td><td>$perms[config]</td></tr>
	<tr><td><strong>./attachments/</strong> (包括本目录、子目录和文件)</td><td>读/写/删</td><td>附件目录</td><td>$perms[attachments]</td></tr>
	<tr><td><strong>./cache/</strong> (包括本目录、子目录和文件)</td><td>读/写/删</td><td>缓存目录</td><td>$perms[cache]</td></tr>
	<tr><td><strong>./channel/</strong> (包括本目录、子目录和文件)</td><td>读/写/删</td><td>频道目录</td><td>$perms[channel]</td></tr>
	<tr><td><strong>./html/</strong> (包括本目录、子目录和文件)</td><td>读/写/删</td><td>html目录</td><td>$perms[html]</td></tr>
	<tr><td><strong>./log/</strong> (包括本目录、子目录和文件)</td><td>读/写/删</td><td>log目录</td><td>$perms[log]</td></tr>
	<tr><td><strong>./styles/</strong> (包括本目录、子目录和文件)</td><td>读/写/删</td><td>模块风格目录</td><td>$perms[styles]</td></tr>
	<tr><td><strong>./model/</strong> (包括本目录、子目录和文件)</td><td>读/写/删</td><td>模型目录</td><td>$perms[model]</td></tr>
	<tr><td><strong>./data/</strong> (包括本目录、子目录和文件)</td><td>读/写/删</td><td>站点数据目录</td><td>$perms[data]</td></tr>
	<tr><td><strong>./uc_client/data/</strong> (包括本目录、子目录和文件)</td><td>读/写/删</td><td>uc_client数据目录</td><td>$perms[uc_data]</td></tr>
	</table>
	</td></tr>
	</table>
END;

	if(!$checkok) {
		echo "<table><tr><td><b>出现问题</b>:<br>系统检测到以上目录或文件权限没有正确设置<br>强烈建议正常设置权限后再刷新本页面以便继续安装<br>否则系统可能会出现无法预料的问题 [<a href=\"$theurl?step=1\">强制继续</a>]</td></tr></table>";
	} else {
		$ucapi = empty($_POST['ucapi'])?'/':$_POST['ucapi'];
		$ucfounderpw = empty($_POST['ucfounderpw'])?'':$_POST['ucfounderpw'];
		print <<<END
		<form id="theform" method="post" action="$theurl?step=1">
			<table class=button>
				<tr>
					<td><input type="submit" id="startsubmit" name="startsubmit" value="接受授权协议，开始安装SupeSite"></td>
				</tr>
			</table>
			<input type="hidden" name="ucapi" value="$ucapi" />
			<input type="hidden" name="ucfounderpw" value="$ucfounderpw" />
			<input type="hidden" name="formhash" value="$formhash">
		</form>
END;
	}

	print<<<END
	<table id="tbl_readme" style="display:none;" class="showtable">
	<tr>
	<td><strong>请您务必仔细阅读下面的许可协议:</strong> </td></tr>
	<tr>
	<td>
	<div>中文版授权协议 适用于中文用户
	<p>版权所有 (C) 2001-2009，康盛创想（北京）科技有限公司<br>保留所有权利。
	</p><p>感谢您选择 SupeSite 社区门户产品。希望我们的努力能为您提供一个高效快速的社区门户解决方案。
	</p><p>康盛创想（北京）科技有限公司为 SupeSite 产品的开发商，依法独立拥有 SupeSite 产品著作权（中国国家版权局 著作权登记号 2006SR12090）。康盛创想（北京）科技有限公司网址为
	http://www.comsenz.com，SupeSite 官方网站网址为 http://www.supesite.com。
	</p><p>SupeSite 著作权已在中华人民共和国国家版权局注册，著作权受到法律和国际公约保护。使用者：无论个人或组织、盈利与否、用途如何
	（包括以学习和研究为目的），均需仔细阅读本协议，在理解、同意、并遵守本协议的全部条款后，方可开始使用 SupeSite 软件。
	</p><p>康盛创想（北京）科技有限公司拥有对本授权协议的最终解释权。
	<ul type=i>
	<p>
	<li><b>协议许可的权利</b>
	<ul type=1>
	<li>您可以在完全遵守本最终用户授权协议的基础上，将本软件应用于非商业用途，而不必支付软件版权授权费用。
	<li>您可以在协议规定的约束和限制范围内修改 SupeSite 源代码(如果被提供的话)或界面风格以适应您的网站要求。
	<li>您拥有使用本软件构建的站点中全部会员资料、文章及相关信息的所有权，并独立承担与文章内容的相关法律义务。
	<li>获得商业授权之后，您可以将本软件应用于商业用途，同时依据所购买的授权类型中确定的技术支持期限、技术支持方式和技术支持内容，
	自购买时刻起，在技术支持期限内拥有通过指定的方式获得指定范围内的技术支持服务。商业授权用户享有反映和提出意见的权力，相关意见
	将被作为首要考虑，但没有一定被采纳的承诺或保证。 </li></ul>
	<p></p>
	<li><b>协议规定的约束和限制</b>
	<ul type=1>
	<li>未获商业授权之前，不得将本软件用于商业用途（包括但不限于企业网站、经营性网站、以营利为目或实现盈利的网站）。购买商业授权请登录http://www.discuz.com参考相关说明，也可以致电8610-51657885了解详情。
	<li>不得对本软件或与之关联的商业授权进行出租、出售、抵押或发放子许可证。
	<li>无论如何，即无论用途如何、是否经过修改或美化、修改程度如何，只要使用 SupeSite 的整体或任何部分，未经书面许可，程序页面页脚处
	的 SupeSite 名称和康盛创想（北京）科技有限公司下属网站（http://www.comsenz.com、http://www.supesite.com） 的 链接都必须保留，而不能清除或修改。
	<li>禁止在 SupeSite 的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于重新分发。
	<li>如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回，并承担相应法律责任。 </li></ul>
	<p></p>
	<li><b>有限担保和免责声明</b>
	<ul type=1>
	<li>本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。
	<li>用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未购买产品技术服务之前，我们不承诺提供任何形式的技术支持、使用担保，
	也不承担任何因使用本软件而产生问题的相关责任。
	<li>康盛创想（北京）科技有限公司不对使用本软件构建的站点中的文章或信息承担责任。 </li></ul></li></ul>
	<p>有关 SupeSite 最终用户授权协议、商业授权与技术服务的详细内容，均由 SupeSite 官方网站独家提供。康盛创想（北京）科技有限公司拥有在不 事先通知的情况下，修改授权协议和服务价目表的权力，修改后的协议或价目表对自改变之日起的新授权用户生效。
	<p>电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和等同的法律效力。您一旦开始安装 SupeSite，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。 </p></div>
	</td></tr>
	</table>
END;

	show_footer();

} elseif($step == 1) {

	show_header();
	$ucapi = "http://";
	$ucfounderpw = '';
	$showdiv = 0;
	if($_POST['ucfounderpw']) {
		$showdiv = 1;
		$ucapi = trim($_POST['ucapi']);
		$ucfounderpw = trim($_POST['ucfounderpw']);
	}

	if($showdiv) {
		print<<<END
		<form id="theform" method="post" action="$theurl">
		<div>
			<table class="showtable">
				<tr><td><strong># UCenter 参数自动获取</strong></td></tr>
				<tr><td id="msg2">UCenter的相关信息已成功获取，请直接下面的按钮提交配置</td></tr>
			</table>
			<br/>
		</div>
		<div>
END;
	} else {
		$plus = '';
		if(!$ucfounderpw) {
			$plus = '<tr><td id="msg2">
					使用SupeSite，首先需要您的站点安装有统一存储用户帐号信息的UCenter用户中心系统。<br>
					如果您的站点还没有安装过UCenter，请这样操作：<br>
					1. <a href="http://download.comsenz.com/UCenter/" target="_blank"><b>请点击这里下载最新版本的UCenter</b></a>，并阅读程序包中的说明进行UCenter的安装。<br>
					2. 安装完毕 UCenter 后，在下面填入UCenter的相关信息即可继续进行SupeSite 的安装。<br>
				</td></tr>';
		}
		print<<<END
		<form id="theform" method="post" action="$theurl">
		<div>
			<table class="showtable">
				<tr><td><strong># 请填写 UCenter 的相关参数</strong></td></tr>
				$plus
			</table>
			<br>
			<p style="font-weight:bold;">请输入已安装UCenter的信息:</p>
END;
	}
	print<<<END
		<table class=datatable>
			<tbody>
				<tr>
					<td>UCenter 的URL:</td>
					<td><input type="text" id="ucapi" name="ucapi" size="60" value="$ucapi"><br>例如：http://www.discuz.net/ucenter</td>
				</tr>
				<tr>
					<td>UCenter 的创始人密码:</td>
					<td><input type="password" id="ucfounderpw" name="ucfounderpw" size="20" value="$ucfounderpw"></td>
				</tr>
			</tbody>
		</table>
		<p style="font-weight:bold;">请填写站点信息:</p>
		<table class="datatable">
			<tbody>
				<tr>
					<td width="26%"> 站点名称:</td>
					<td><input type="text" class="txt" size="20" value="SupeSite" name="sitename"/></td>
				</tr>
			</tbody>
		</table>
	</div>

	<table class=button>
	<tr><td><input type="submit" id="ucsubmit" name="ucsubmit" value="提交UCenter配置信息"></td></tr>
	</table>
	<input type="hidden" id="ucfounder" name="ucfounder" size="20" value="">
	<input type="hidden" name="formhash" value="$formhash">
	</form>
END;
	show_footer();

} elseif ($step == 2) {

	//检测目录属性
	show_header();
	//设置数据库配置
	print<<<END
	<form id="theform" method="post" action="$theurl">

	<table class="showtable">
	<tr><td><strong># 设置SupeSite数据库信息</strong></td></tr>
	<tr><td id="msg1">这里设置SupeSite的数据库信息</td></tr>
	</table>
	<table class=datatable>
	<tr>
	<td width="25%">数据库服务器本地地址:</td>
	<td><input type="text" name="db[dbhost]" size="20" value="localhost"></td>
	<td width="30%">一般为localhost</td>
	</tr>
	<tr>
	<td>数据库用户名:</td>
	<td><input type="text" name="db[dbuser]" size="20" value=""></td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<td>数据库密码:</td>
	<td><input type="password" name="db[dbpw]" size="20" value=""></td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<td>数据库字符集:</td>
	<td>
	<select name="db[dbcharset]" onchange="addoption(this)">
	<option value="$dbcharset">$dbcharset</option>
	<option value="gbk">gbk</option>
	<option value="utf8">utf8</option>
	<option value="big5">big5</option>
	<option value="latin1">latin1</option>
	<option value="addoption" class="addoption">+自定义</option>
	</select>
	</td>
	<td>MySQL版本>4.1才有效</td>
	</tr>
	<tr>
	<td>数据库名:</td>
	<td><input type="text" name="db[dbname]" size="20" value=""></td>
	<td>如果不存在，则会尝试自动创建</td>
	</tr>
	<tr>
	<td>表名前缀:</td>
	<td><input type="text" name="db[tablepre]" size="20" value="supe_"></td>
	<td>默认为supe_</td>
	</tr>
	<tr><td><strong style="color:red;">SupeSite站点URL地址</strong><br>务必填写准确</td>
		<td><input type="text" name="siteurl" value="$siteurl" size="25"></td><td style="color:red;">可以填写以http://开头的完整URL，也可以填写以"/"开头的相对URL(如果是网站根目录，可以填写为空)。末尾不要加 /</td>
		</tr>
	</table>

	<table class=button>
	<tr><td>
	<input type="hidden" name="formhash" value="$formhash">
	<input type="submit" id="sqlsubmit" name="sqlsubmit" value="设置完毕,检测我的数据库配置"></td></tr>
	</table>
	</form>
END;
	show_footer();

} elseif ($step == 3) {

	//链接数据库
	dbconnect();

	//安装数据库
	//获取最新的sql文
	$newsql = sreadfile($sqlfile);

	if($_SC['tablepre'] != 'supe_') $newsql = str_replace('supe_', $_SC['tablepre'], $newsql);//替换表名前缀

	//获取要创建的表
	$tables = $sqls = array();
	if($newsql) {
		preg_match_all("/(CREATE TABLE ([a-z0-9\_\-`]+).+?\s*)(TYPE|ENGINE)+\=/is", $newsql, $mathes);
		$sqls = $mathes[1];
		$tables = $mathes[2];
	}
	if(empty($tables)) {
		show_msg("安装SQL语句获取失败，请确认SQL文件 $sqlfile 是否存在");
	}

	$heaptype = $_SGLOBAL['db']->version()>'4.1'?" ENGINE=MEMORY".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ):" TYPE=HEAP";
	$myisamtype = $_SGLOBAL['db']->version()>'4.1'?" ENGINE=MYISAM".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ):" TYPE=MYISAM";
	$installok = true;
	foreach ($tables as $key => $tablename) {
		if(strpos($tablename, 'session')) {
			$sqltype = $heaptype;
		} else {
			$sqltype = $myisamtype;
		}
		$_SGLOBAL['db']->query("DROP TABLE IF EXISTS $tablename");
		if(!$query = $_SGLOBAL['db']->query($sqls[$key].$sqltype, 'SILENT')) {
			$installok = false;
			break;
		}
	}
	if(!$installok) {
		show_msg("<font color=\"blue\">数据表 ($tablename) 自动安装失败</font><br />反馈: ".mysql_error()."<br /><br />请参照 $sqlfile 文件中的SQL文，自己手工安装数据库后，再继续进行安装操作<br /><br /><a href=\"?step=$step\">重试</a>");
	} else {
		show_msg('数据表已经全部安装完成，进入下一步操作', ($step+1), 1);
	}

} elseif ($step == 4) {

	dbconnect();

	//config
	$sitekey = mksitekey();
	$datas = array(
		"'allowcache', '1'",
		"'allowguest', '0'",
		"'attachmentdir', './attachments'",
		"'attachmentdirtype', 'month'",
		"'allowregister', '1'",
		"'attachmenturl', ''",
		"'bbsurltype', 'site'",
		"'checkgrade', ''",
		"'debug', '0'",
		"'gzipcompress', '0'",
		"'htmldir', './html'",
		"'htmlurl', ''",
		"'htmlcategory', '0'",
		"'htmlcategorytime', '300'",
		"'htmlindex', '0'",
		"'htmlindextime', '300'",
		"'htmlviewnews', '0'",
		"'htmlviewnewstime', '300'",
		"'language', 'chinese.php'",
		"'needcheck', '0'",
		"'pagepostfix', ''",
		"'searchinterval', '30'",
		"'sitename', 'SupeSite社区门户'",
		"'template', 'default'",
		"'thumbbgcolor', '#C0C0C0'",
		"'thumbcutmode', '2'",
		"'thumbcutstartx', '0'",
		"'thumbcutstarty', '0'",
		"'thumboption', '4'",
		"'timeoffset', '8'",
		"'urltype', '4'",
		"'watermark', '0'",
		"'watermarkfile', 'images/base/watermark.gif'",
		"'watermarkjpgquality', '85'",
		"'watermarkstatus', '9'",
		"'watermarktrans', '30'",
		"'seotitle', ''",
		"'seokeywords', ''",
		"'seodescription', ''",
		"'seohead', ''",
		"'noseccode', '1'",
		"'showindex', '0'",
		"'newsjammer', '0'",
		"'updateview', '1'",
		"'allowguestsearch', '0'",
		"'commenttime', '30'",
		"'posttime', '30'",
		"'customaddfeed', '1'",
		"'allowfeed', '1'",
		"'sitekey', '$sitekey'",
		"'commstatus', '1'",
		"'commicon', 'logo.gif'",
		"'commdefault', '我也来评论！'",
		"'commorderby', '0'",
		"'commfloornum', '2'",
		"'commshowip', '1'",
		"'commshowlocation', '1'",
		"'commdebate', '0'",
		"'commdivide', '10'",
		"'commviewnum', '50'",
		"'commhidefloor', '0'",
		"'makehtml', '0'",
		"'itempost', 'flower'",
		"'post_flower', '0'",
		"'post_egg', '0'",
		"'post_flower_egg', '0'",
		"'perpage', '20'",
		"'prehtml', 'info'"
	);
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('settings'));
	$_SGLOBAL['db']->query("INSERT INTO ".tname('settings')." (`variable`, `value`) VALUES (".implode('),(', $datas).")");

	//attachmenttypes
	$datas = array(
		"'jpg', 2097152",
		"'jpeg', 2097152",
		"'png', 2097152",
		"'gif', 2097152",
		"'rar', 2097152",
		"'txt', 2097152",
		"'zip', 2097152"
	);
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('attachmenttypes'));
	$_SGLOBAL['db']->query("INSERT INTO ".tname('attachmenttypes')." (fileext, maxsize) VALUES (".implode('),(', $datas).")");

	//categories
	$datas = array(
		//资讯
		"'科技世界', 'news'",
		"'互联网络', 'news'",
		"'财经报道', 'news'",
		"'体育资讯', 'news'",
		"'明星娱乐', 'news'",
		"'生活资讯', 'news'",
		"'网站建设', 'news'",
		"'动态报道', 'news'",
		"'特别关注', 'news'"
	);
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('categories'));
	$_SGLOBAL['db']->query("INSERT INTO ".tname('categories')." (`name`, `type`) VALUES (".implode('),(', $datas).")");
	$_SGLOBAL['db']->query("UPDATE ".tname('categories')." SET subcatid=catid");

	$datas = array(
		"'news', '资讯', 'type', '0', '1'",
		"'top', '排行榜', 'system', '10', '1'"
	);
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('channels'));
	$_SGLOBAL['db']->query("INSERT INTO ".tname('channels')." (`nameid`, `name`, `type`, `displayorder`, `status`) VALUES (".implode('),(', $datas).")");

	$datas = array(
		"1, 'system', '更新热门TAG', 'tagcontent.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, -1, '0'",
		"1, 'system', '清理无用附件', 'cleanattachment.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, 4, '0'",
		"1, 'system', '清理临时文件', 'cleanimporttemp.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, 4, '15'",
		"1, 'system', '更新论坛缓存', 'updatebbscache.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, 2, '0'",
		"1, 'system', '更新信息查看数', 'updateviewnum.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, 3, '5	15	25	35	45	55'"
	);
	$datas[] = "1, 'system', '更新论坛帖子收录','updatebbsforums.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, -1,'0	5	10	15	20	25	30	35	40	45	50	55' ";
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('crons'));
	$_SGLOBAL['db']->query("INSERT INTO ".tname('crons')." (available, type, name, filename, lastrun, nextrun, weekday, day, hour, minute) VALUES (".implode('),(', $datas).")");

	//styles
	$datas = array(
		"'系统分类名称列表', '显示数据: 系统分类名\r\n显示方式: 以 &lt;li&gt;名称&lt;/li&gt; 的方式循环显示', 'category', 'name_li'",
		"'资讯标题列表', '显示数据: 资讯标题\r\n显示方式: 以 &lt;li&gt;标题&lt;/li&gt; 的方式循环显示', 'spacenews', 'subject_li'",
		"'模型文章标题列表', '显示数据: 模型标题\r\n显示方式: 以 &lt;li&gt;标题&lt;/li&gt; 的方式循环显示', 'model', 'subject_li'",
		"'投票表单', '显示数据: 投票表单\r\n显示方式: 以 &lt;form&gt;投票具体选项&lt;/form&gt; 的方式循环显示', 'poll', 'poll_form'",
		"'TAG名列表', '显示数据: TAG名\r\n显示方式: 以 &lt;li&gt;TAG名&lt;/li&gt; 的方式循环显示', 'tag', 'tagname_li'",
		"'文章标题列表', '显示数据: 信息标题、作者\r\n显示方式: 以 &lt;li&gt;标题(作者)&lt;/li&gt; 的方式循环显示', 'spacetag', 'subject_username_li'",
		"'回复内容列表', '显示数据: 标题、内容\r\n显示方式: 以 &lt;li&gt;&lt;p&gt;标题&lt;/p&gt;&lt;p&gt;内容&lt;/p&gt;&lt;/li&gt; 的方式循环显示', 'spacecomment', 'subject_message_li'",
		"'公告标题列表', '显示数据: 标题\r\n显示方式: 以 &lt;li&gt;标题&lt;/li&gt; 的方式循环显示', 'announcement', 'subject_li'",
		"'友情链接名列表', '显示数据: 标题\r\n显示方式: 以 &lt;li&gt;标题&lt;/li&gt; 的方式循环显示', 'friendlink', 'name_li'",
		"'主题列表', '显示数据: 标题\r\n显示方式: 以 &lt;li&gt;标题&lt;/li&gt; 的方式循环显示', 'bbsthread', 'subject_li'",
		"'论坛公告列表', '显示数据: 标题\r\n显示方式: 以 &lt;li&gt;标题&lt;/li&gt; 的方式循环显示', 'bbsannouncement', 'subject_li'",
		"'论坛版块名列表', '显示数据: 版块名\r\n显示方式: 以 &lt;li&gt;版块名&lt;/li&gt; 的方式循环显示', 'bbsforum', 'name_li'",
		"'友情链接名列表', '显示数据: 链接名\r\n显示方式: 以 &lt;li&gt;链接名&lt;/li&gt; 的方式循环显示', 'bbslink', 'name_li'",
		"'会员名列表', '显示数据: 会员名\r\n显示方式: 以 &lt;li&gt;会员名&lt;/li&gt; 的方式循环显示', 'bbsmember', 'username_li'",
		"'附件名列表', '显示数据: 附件名\r\n显示方式: 以 &lt;li&gt;附件名&lt;/li&gt; 的方式循环显示', 'bbsattachment', 'filename_li'",
		"'帖子内容列表', '显示数据: 标题、内容(包含附件)\r\n显示方式: 以 &lt;li&gt;&lt;p&gt;标题&lt;/p&gt;&lt;p&gt;内容&lt;/p&gt;&lt;/li&gt; 的方式循环显示', 'bbspost', 'post_subject_message_li'"
	);
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('styles'));
	$_SGLOBAL['db']->query("INSERT INTO ".tname('styles')." (tplname, tplnote, tpltype, tplfilepath) VALUES (".implode('),(', $datas).")");

	//usergroups
	$datas = array(
		"'1', '管理员', '-1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '0', '1', '1', '1', '1', '1', '0', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1'",
		"'2', '游客组', '-1', '1', '0', '1', '0', '0', '1', '1', '0', '0', '0', '1', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'",
		"'3', '禁止访问', '-1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'",
		"'4', '禁止发言', '-1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'",
		"'10', '贵宾VIP', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '0', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'",
		"'11', '受限制会员', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '-999999999', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'",
		"'12', '初级会员', '0', '1', '1', '1', '0', '0', '1', '1', '0', '0', '0', '1', '0', '0', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'",
		"'13', '中级会员', '0', '1', '1', '1', '1', '0', '1', '1', '1', '0', '0', '1', '0', '1', '1', '1', '0', '300', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'",
		"'14', '高级会员', '0', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '0', '1', '1', '1', '1', '0', '800', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'"
	);
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('usergroups'));
	$_SGLOBAL['db']->query("INSERT INTO ".tname('usergroups').
							" (groupid, grouptitle, system, allowview, allowpost, allowcomment, allowgetattach, allowpostattach, allowvote, allowsearch, allowtransfer, allowpushin, allowpushout, allowdirectpost, allowanonymous, allowhideip, allowhidelocation, allowclick, closeignore, explower, managemodpost, manageeditpost, managedelpost, managefolder, managemodcat, manageeditcat, managedelcat, managemodrobot, manageuserobot, manageeditrobot, managedelrobot, managemodrobotmsg, manageundelete, manageadmincp, manageviewlog, managesettings, manageusergroups, manageannouncements, managead, manageblocks, managebbs, managebbsforums, managethreads, manageuchome, managemodels, managechannel, managemember, managehtml, managecache, managewords, manageattachmenttypes, managedatabase, managetpl, managecrons, managecheck, managecss, managefriendlinks, manageprefields, managesitemap, manageitems, managecomments, manageattachments, managetags, managereports, managepolls, managecustomfields, managestyles, managestyletpl, managedelmembers, manageclick, managecredit, managepostnews) ".
							" VALUES (".implode('),(', $datas).")");

	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('clickgroup'));
	$datas = array(
		"'1', '心情', 'topmood.jpg', '1', '0', '1', '1', '0', '1', 'spaceitems', '0', '1'",
		"'2', 'Digg', '', '0', '0', '0', '1', '0', '1', 'spaceitems', '0', '1'",
		"'3', '回复打分', '', '0', '0', '0', '1', '0', '1', 'spacecomments', '0', '1'",
		"'4', '事件或人物打分', '', '0', '0', '0', '1', '0', '1', 'spaceitems', '0', '1'",
		"'5', '内容质量打分', '', '0', '0', '0', '1', '0', '1', 'spaceitems', '0', '1'"
	);	
	$_SGLOBAL['db']->query("INSERT INTO ".tname('clickgroup')." (groupid, grouptitle, icon, allowspread, spreadtime, allowtop, status, allowrepeat, allowguest, idtype, mid, system) VALUES (".implode('),(', $datas).")");

	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('click'));
	$datas = array(
		"'1', '感动', '19.gif', '0', '1', '0', '1', '', '0'",
		"'2', '同情', '20.gif', '0', '1', '0', '1', '', '0'",
		"'3', '无聊', '09.gif', '0', '1', '0', '1', '', '0'",
		"'4', '愤怒', '02.gif', '0', '1', '0', '1', '', '0'",
		"'5', '搞笑', '08.gif', '0', '1', '0', '1', '', '0'",
		"'6', '难过', '15.gif', '0', '1', '0', '1', '', '0'",
		"'7', '高兴', '12.gif', '0', '1', '0', '1', '', '0'",
		"'8', '路过', '14.gif', '0', '1', '0', '1', '', '0'",
		"'9', '支持', '', '0', '2', '0', '1', '', '1'",
		"'10', '反对', '', '1', '2', '0', '1', '', '1'",
		"'11', '-5', '', '0', '4', '-5', '1', '', '0'",
		"'12', '-4', '', '1', '4', '-4', '1', '', '0'",
		"'13', '-3', '', '2', '4', '-3', '1', '', '0'",
		"'14', '-2', '', '3', '4', '-2', '1', '', '0'",
		"'15', '-1', '', '4', '4', '-1', '1', '', '0'",
		"'16', '0', '', '5', '4', '0', '1', '', '0'",
		"'17', '1', '', '6', '4', '1', '1', '', '0'",
		"'18', '2', '', '7', '4', '2', '1', '', '0'",
		"'19', '3', '', '8', '4', '3', '1', '', '0'",
		"'20', '4', '', '9', '4', '4', '1', '', '0'",
		"'21', '5', '', '10', '4', '5', '1', '', '0'",
		"'22', '-5', '', '0', '5', '-5', '1', '', '0'",
		"'23', '-4', '', '1', '5', '-4', '1', '', '0'",
		"'24', '-3', '', '2', '5', '-3', '1', '', '0'",
		"'25', '-2', '', '3', '5', '-2', '1', '', '0'",
		"'26', '-1', '', '4', '5', '-1', '1', '', '0'",
		"'27', '0', '', '5', '5', '0', '1', '', '0'",
		"'28', '1', '', '6', '5', '1', '1', '', '0'",
		"'29', '2', '', '7', '5', '2', '1', '', '0'",
		"'30', '3', '', '8', '5', '3', '1', '', '0'",
		"'31', '4', '', '9', '5', '4', '1', '', '0'",
		"'32', '5', '', '10', '5', '5', '1', '', '0'",
		"'33', '支持', 'icon8.gif', '0', '3', '0', '1', '', '1'",
		"'34', '反对', 'icon9.gif', '1', '3', '0', '1', '', '1'"
	);	
	$_SGLOBAL['db']->query("INSERT INTO ".tname('click')." (`clickid`, `name`, `icon`, `displayorder`, `groupid`, `score`, `status`, `filename`, `system`) VALUES (".implode('),(', $datas).")");
	
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('creditrule'));
	$datas = array(
		"'1', '发表信息', 'postinfo', '1', '0', '3', '1', '0', '10', '10'",
		"'2', '评论', 'postcomment', '1', '0', '20', '1', '0', '1', '1'",
		"'3', '上传', 'postattach', '1', '0', '3', '1', '0', '10', '10'",
		"'4', '投票', 'postvote', '1', '0', '10', '1', '0', '1', '1'",
		"'5', '点击', 'postclick', '1', '0', '20', '1', '0', '1', '1'",
		"'6', '设置头像', 'setavatar', '0', '0', '1', '1', '0', '10', '10'",
		"'7', '每天登陆', 'daylogin', '1', '0', '1', '1', '0', '10', '10'",
		"'9', '举报', 'report', '1', '0', '10', '1', '0', '1', '1'",
		"'10', '删除信息', 'delinfo', '4', '0', '0', '2', '0', '10', '10'",
		"'11', '删除评论', 'delcomment', '4', '0', '0', '2', '0', '10', '10'",
		"'12', '搜索', 'seach', '4', '0', '0', '0', '0', '1', '1'",
		"'13', '匿名评论', 'anonymous', '4', '0', '0', '0', '0', '5', '1'",
		"'14', '隐藏ip', 'hideip', '4', '0', '0', '0', '0', '5', '1'",
		"'15', '隐藏位置', 'hidelocation', '4', '0', '0', '0', '0', '5', '1'",
		"'16', '浏览', 'view', '4', '0', '0', '0', '0', '0', '1'",
		"'17', '下载', 'download', '4', '0', '0', '0', '0', '5', '1'"
	);	
	$_SGLOBAL['db']->query("INSERT INTO ".tname('creditrule')." (`rid`, `rulename`, `action`, `cycletype`, `cycletime`, `rewardnum`, `rewardtype`, `norepeat`, `credit`, `experience`) VALUES (".implode('),(', $datas).")");

	
	show_msg('系统默认数据添加完毕，进入下一步操作', ($step+1), 1);

} elseif ($step == 5) {

	//更新缓存
	dbconnect();
	include_once(S_ROOT.'./function/cache.func.php');

	updatesettingcache();	//系统设置缓存
	updategroupcache();		//用户组缓存
	updateadcache();		//广告缓存
	updatecronscache();		//crons列表
	updatecroncache();		//计划任务
	updatecategorycache();	//分类
	updatecensorcache();	//缓存语言屏蔽
	click_cache();			//缓存表态
	creditrule_cache();		//缓存积分
	postnews_cache;			//缓存信息推送

	$msg = <<<EOF
	<form method="post" action="$theurl">
	<table>
	<tr><td colspan="2">程序数据安装完成!<br><br>
	最后，请输入您在用户中心UCenter的用户名和密码<br>系统将自动把将您设为站点管理员!
	</td></tr>
	<tr><td>您的用户名</td><td><input type="text" name="username" value="" size="30"></td></tr>
	<tr><td>您的密码</td><td><input type="password" name="password" value="" size="30"></td></tr>
	<tr><td></td><td><input type="submit" name="opensubmit" value="设置管理员"></td></tr>
	</table>
	<input type="hidden" name="formhash" value="$formhash">
	</form>
    <iframe id="phpframe" name="phpframe" width="0" height="0" marginwidth="0" frameborder="0" src="..\"></iframe>
EOF;

	show_msg($msg, 999);
}

//页面头部
function show_header() {
	global $_SGLOBAL, $nowarr, $step, $theurl, $_SC;

	$nowarr[$step] = ' class="current"';
	print<<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title> SupeSite 程序安装 </title>
	<style type="text/css">
	* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em; word-break: break-all; }
	body { text-align:center; margin: 0; padding: 0; background: #F5FBFF; }
	.bodydiv { margin: 40px auto 0; width:720px; text-align:left; border: solid #86B9D6; border-width: 5px 1px 1px; background: #FFF; }
	h1 { font-size: 18px; margin: 1px 0 0; line-height: 50px; height: 50px; background: #E8F7FC; color: #5086A5; padding-left: 10px; }
	#menu {width: 100%; margin: 10px auto; text-align: center; }
	#menu td { height: 30px; line-height: 30px; color: #999; border-bottom: 3px solid #EEE; }
	.current { font-weight: bold; color: #090 !important; border-bottom-color: #F90 !important; }
	.showtable { width:100%; border: solid; border-color:#86B9D6 #B2C9D3 #B2C9D3; border-width: 3px 1px 1px; margin: 10px auto; background: #F5FCFF; }
	.showtable td { padding: 3px; }
	.showtable strong { color: #5086A5; }
	.datatable { width: 100%; margin: 10px auto 25px; }
	.datatable td { padding: 5px 0; border-bottom: 1px solid #EEE; }
	input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
	.button { margin: 10px auto 20px; width: 100%; }
	.button td { text-align: center; }
	.button input, .button button { border: solid; border-color:#F90; border-width: 1px 1px 3px; padding: 5px 10px; color: #090; background: #FFFAF0; cursor: pointer; }
	#footer { font-size: 10px; line-height: 40px; background: #E8F7FC; text-align: center; height: 38px; overflow: hidden; color: #5086A5; margin-top: 20px; }
	</style>
	<script type="text/javascript">
	function $(id) {
		return document.getElementById(id);
	}
	//添加Select选项
	function addoption(obj) {
		if (obj.value=='addoption') {
			var newOption=prompt('请输入:','');
			if (newOption!=null && newOption!='') {
				var newOptionTag=document.createElement('option');
				newOptionTag.text=newOption;
				newOptionTag.value=newOption;
				try {
					obj.add(newOptionTag, obj.options[0]); // doesn't work in IE
				}
				catch(ex) {
					obj.add(newOptionTag, obj.selecedIndex); // IE only
				}
				obj.value=newOption;
			} else {
				obj.value=obj.options[0].value;
			}
		}
	}
	</script>
	</head>
	<body id="append_parent">
	<div class="bodydiv">
	<h1>SupeSite程序安装</h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>
	<td{$nowarr[0]}>1.安装开始</td>
	<td{$nowarr[1]}>2.设置UCenter信息</td>
	<td{$nowarr[2]}>3.设置数据库连接信息</td>
	<td{$nowarr[3]}>4.创建数据库结构</td>
	<td{$nowarr[4]}>5.添加默认数据</td>
	<td{$nowarr[5]}>6.安装完成</td>
	</tr>
	</table>
END;
}

//页面顶部
function show_footer() {
	print<<<END
	</div>
	<iframe id="phpframe" name="phpframe" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank"></iframe>
	<div id="footer">&copy; Comsenz Inc. 2001-2009 www.supesite.com</div>
	</div>
	<br>
	</body>
	</html>
END;
}


//显示
function show_msg($message, $next=0, $jump=0) {
	global $theurl;

	$nextstr = '';
	$backstr = '';

	obclean();
	if(empty($next)) {
		$backstr = "<a href=\"javascript:history.go(-1);\">返回上一步</a>";
	} elseif ($next == 999) {
	} else {
		$url_forward = "$theurl?step=$next";
		$nextstr = "<a href=\"$url_forward\">继续下一步</a>";
		if($jump) {
			$nextstr .= "<script>setTimeout(\"window.location.href ='$url_forward';\", 1000);</script>";
		}
		$backstr = "<a href=\"javascript:history.go(-1);\">返回上一步</a>";
	}

	show_header();
	print<<<END
	<table>
	<tr><td>$message</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>$backstr $nextstr</td></tr>
	</table>
END;
	show_footer();
	exit();
}

//检查权限
function checkfdperm($path, $isfile=0) {
	if($isfile) {
		$file = $path;
		$mod = 'a';
	} else {
		$file = $path.'./install_tmptest.data';
		$mod = 'w';
	}
	if(!@$fp = fopen($file, $mod)) {
		return false;
	}
	if(!$isfile) {
		//是否可以删除
		fwrite($fp, ' ');
		fclose($fp);
		if(!@unlink($file)) {
			return false;
		}
		//检测是否可以创建子目录
		if(is_dir($path.'./install_tmpdir')) {
			if(!@rmdir($path.'./install_tmpdir')) {
				return false;
			}
		}
		if(!@mkdir($path.'./install_tmpdir')) {
			return false;
		}
		//是否可以删除
		if(!@rmdir($path.'./install_tmpdir')) {
			return false;
		}
	} else {
		fclose($fp);
	}
	return true;
}

//打开远程地址
function sfopen($url, $limit = 500000, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
	$return = '';
	$matches = parse_url($url);
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].'?'.$matches['query'].(empty($matches['fragment'])?'':'#'.$matches['fragment']) : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;

	if($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: '.strlen($post)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
	if(!$fp) {
		return '';//note $errstr : $errno \r\n
	} else {
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $out);
		$status = stream_get_meta_data($fp);
		if($status['timed_out']) {
			return '';
		}
		$return = fread($fp, 524);
		$limit -= strlen($return);
		while(!feof($fp) && $limit > -1) {
			$limit -= 100524;
			$return .= @fread($fp, 100524);
		}
		@fclose($fp);
		$return = preg_replace("/\r\n\r\n/", "\n\n", $return, 1);
		$strpos = strpos($return, "\n\n");
		$strpos = $strpos !== FALSE ? $strpos + 2 : 0;
		$return = substr($return, $strpos);
		return $return;
	}
}

?>