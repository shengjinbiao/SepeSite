<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.panel.php 13359 2009-09-22 09:06:19Z zhaofei $
*/

include_once('./common.php');
include_once(S_ROOT.'./language/batch.lang.php');

$uid = $_SGLOBAL['supe_uid'];
$ucurl = avatar($uid);
$siteurl = S_URL_ALL;
if(!empty($uid)) {
	if($channels['menus']['bbs']) {
		$bbshtml = ' | <a href="'.$_SC['bbsurl'].'" target="_blank">'.$blang['forum_visit'].'</a>';
	}
	if($channels['menus']['uchblog'] || $channels['menus']['uchimage']) {
		$uchhtml = ' | <a href="'.$_SC['uchurl'].'" target="_blank">'.$blang['home_visit'].'</a>';
	}
	$showpost = 0;
	$showposturl = '';
	$divhtml = '<div id="contribute_op" style="display:none;"><ul>';

	if(!empty($channels['menus'])) {
		foreach($channels['menus'] as $value) {
			$channel = $value['nameid'];
			if(!checkperm('allowpost')) continue;
			if($value['type']=='type' || $value['type']=='user' && $value['upnameid']=='news') {
				$divhtml .= '<li><a href="'.$siteurl.'/cp.php?ac=news&op=add&type='.$value['nameid'].'" onclick="hidendivop();" target="_blank">'.$value['name'].'</a></li>';
				$showpost++;
				$showposturl = $siteurl.'/cp.php?ac=news&op=add&type='.$value['nameid'];
			} elseif($value['type']=='model') {
				$divhtml .= '<li><a href="'.$siteurl.'/cp.php?ac=models&op=add&nameid='.$value['nameid'].'" onclick="hidendivop();" target="_blank">'.$value['name'].'</a></li>';
				$showpost++;
				$showposturl = $siteurl.'/cp.php?ac=models&op=add&nameid='.$value['nameid'];
			}
		}
	}
	
	if($showpost == 1) {
		$showposturl = "document.write('<a href=\"$showposturl\" class=\"contribute_txt\" target=\"_blank\">$lang[pannel_contribution]</a> ');";
	} elseif($showpost > 1) {
		$showposturl = "document.write('<a href=\"javascript:contributeop();\" class=\"contribute_txt\">$lang[pannel_contribution]</a> ');";
	}
	
	$divhtml .= '</ul></div>';
	
	$openstate = empty($_GET['open']) ? array('', 'none') : array('none', '');
	print <<<END
	function contributeop() {
		if($('contribute_op').style.display != 'block') {
			$('contribute_op').style.display = 'block';
		} else {
			$('contribute_op').style.display = 'none';
		}	
	}
	function hidendivop(){
		$('contribute_op').style.display = 'none';
	}
	function hidenposition(){
		$('loginin_info').style.display = $('loginin_info').style.display=='none' ? '' : 'none';
		$('loginin_info_all').style.display = $('loginin_info_all').style.display=='none' ? '' : 'none';
	}
	document.write('<div id="loginin_info" style="display:$openstate[0];">');
	document.write('<a href="$siteurl/space.php?uid=$uid"><img src="$ucurl" alt=""></a>');
	document.write('<div class="user"><a href="$siteurl/space.php?uid=$uid">$_SGLOBAL[supe_username]</a> <a class="credit" href="$siteurl/cp.php?ac=credit" title="$lang[credit]">{$_SGLOBAL['member']['credit']}</a>&nbsp;<span class="out">[<a href="$siteurl/batch.login.php?action=logout">$blang[safe_logout]</a>]</span></div>');
	document.write('<span class="admin">');
	document.write(' <a href="$siteurl/cp.php">$blang[my_center]</a>');
	document.write('</span>');
	document.write('<a class="open" href="javascript:;" onclick="hidenposition();" title="$blang[open]">$blang[open]</a>');
	document.write('</div>');
	
	document.write('<div id="loginin_info_all" class="fixedheight" style="display:$openstate[1];">');
	document.write('<div id="user_login_position">');
	document.write('<h3>$blang[user_panel]</h3>');
	document.write('<div class="user_info">');
	document.write('<dl>');
	document.write('<dt><a href="$siteurl/space.php?uid=$uid"><img src="$ucurl" alt=""></a></dt>');
	document.write('<dd>');
	document.write('$blang[welcome], <a href="$siteurl/space.php?uid=$uid">$_SGLOBAL[supe_username]</a>[<a href="$siteurl/batch.login.php?action=logout">$blang[safe_logout]</a>]<br />');
	document.write('<a class="credit" href="$siteurl/cp.php?ac=credit" title="$lang[credit]">{$_SGLOBAL['member']['credit']}</a>&nbsp');
	document.write('<a class="tx_blue" href="$siteurl/space.php?uid=$uid">$blang[my_space]</a>');
	document.write(' <a class="tx_blue" href="$siteurl/cp.php">$blang[my_center]</a>');
	document.write('</dd>');
	document.write('</dl>');
    document.write('<div class="user_op">');
	$showposturl
	document.write(' <span><a href="$siteurl/batch.search.php">$blang[search]</a>');
	document.write('$bbshtml');
	document.write('$uchhtml');
END;
	if(checkperm('manageadmincp')) print("document.write(' | <a href=\"$siteurl/admincp.php\" target=\"_blank\">$blang[management]</a>');");
	print <<<END
	document.write(' </span></div></div>$divhtml');
	document.write('<a class="close" href="javascript:;" onclick="hidenposition();" title="$blang[close]">$blang[close]</a>');
	document.write('</div>');
	document.write('</div>');
END;
} else {

	$formhash = formhash();
	print <<<END
	var noseccode = $_SCONFIG[noseccode];
	document.write('<div class="fixedheight">');
	document.write('<div id="user_login_position">');
	document.write('<div id="login_authcode_img" style="display:none"><img src="$siteurl/do.php?action=seccode" alt="$lang[verification_code]" id="img_seccode" /></div>');
	document.write('<h3>$blang[user_login]</h3>');
	document.write('<form id="login_box" action="$siteurl/batch.login.php?action=login" method="post">');
	document.write('<input type="hidden" name="formhash" value="$formhash" />');
	document.write('<fieldset><legend>$blang[user_login]</legend>');
	document.write('<p><label>$blang[username]:</label> <input type="text"  name="username" class="input_tx" size="23" onfocus="addseccode();" tabindex="1" /></p>');
	document.write('<p><label>$blang[password]:</label> <input type="password" name="password" class="input_tx" size="23" onfocus="addseccode();" tabindex="2" /></p>');
	document.write('<p id="login_authcode_input" style="display:none"><label>$lang[verification_code]:</label> <input type="text" class="input_tx" name="seccode" size="10" onfocus="showseccode()"; tabindex="3" /> <a href="javascript:updateseccode();">$lang[changge_verification_code]</a></p>');
	document.write('<div id="login_showclose" style="display:none"><a class="close" href="javascript:hidesec();" title="$blang[close]">$blang[close]</a></div>');
	document.write('<div class="clearfix">');
	document.write('<input id="cookietime" type="checkbox" value="315360000" name="cookietime" class="input_remember" tabindex="4" />');
	document.write('<label class="label_remember" for="cookietime">$blang[i_remember]</label>');
	document.write('<input type="submit" name="loginsubmit" class="input_sub" value="$blang[login]" tabindex="5" />');
	document.write('</div>');
	document.write('<p class="login_ext"><a href="$siteurl/do.php?action=register">$blang[registration]</a> | <a href="$siteurl/do.php?action=lostpasswd">$blang[find_passwords]</a></p>');
	document.write('</fieldset></form></div>');
	document.write('</div>');
END;
}

?>
