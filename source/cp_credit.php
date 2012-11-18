<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($_SGLOBAL['supe_uid'])) {
	showmessage('no_login', geturl('action/login'));
}

$op = empty($_GET['op']) ? '' : trim($_GET['op']);

if(empty($op)) {

	$query = $_SGLOBAL['db']->query("SELECT r.rulename, c.* FROM ".tname('creditlog')." c LEFT JOIN ".tname('creditrule')." r ON r.rid=c.rid WHERE c.uid='$_SGLOBAL[supe_uid]' ORDER BY dateline DESC ");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}

} elseif ($op == 'rule') {

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditrule'));
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}

} elseif ($op == 'exchange') {
	
	//х╗оч
	if(!checkperm('allowtransfer')) {
		showmessage('no_permission');
	}
	
	@include_once(S_ROOT.'./uc_client/data/cache/creditsettings.php');
	if(submitcheck('exchangesubmit')) {
		$netamount = $tocredits = 0;
		$tocredits = $_POST['tocredits'];
		$outexange = strexists($tocredits, '|');
		if(!$outexange && !$_CACHE['creditsettings'][$tocredits]['ratio']) {
			showmessage('credits_exchange_invalid');
		}
		$amount = intval($_POST['amount']);
		if($amount <= 0) {
			showmessage('credits_transaction_amount_invalid');
		}
		@include_once(S_ROOT.'./uc_client/client.php');
		$ucresult = uc_user_login($_SGLOBAL['supe_username'], $_POST['password']);
		list($tmp['uid']) = saddslashes($ucresult);
		
		if($tmp['uid'] <= 0) {
			showmessage('credits_password_invalid');
		} elseif($_SGLOBAL['member']['credit']-$amount < 0) {
			showmessage('credits_balance_insufficient');
		}
		$netamount = floor($amount * 1/$_CACHE['creditsettings'][$tocredits]['ratio']);
		list($toappid, $tocredits) = explode('|', $tocredits);
		
		$ucresult = uc_credit_exchange_request($_SGLOBAL['supe_uid'], $_CACHE['creditsettings'][$tocredits]['creditsrc'], $tocredits, $toappid, $netamount);
		if(!$ucresult) {
			showmessage('extcredits_dataerror');
		}
		$_SGLOBAL['db']->query("UPDATE ".tname('members')." SET credit=credit-$amount WHERE uid='$_SGLOBAL[supe_uid]'");
		
		showmessage('do_success', 'cp.php?ac=credit&op=exchange');
	} elseif(empty($_CACHE['creditsettings'])) {
		showmessage('integral_convertible_unopened');
	}

}

include template('cp_credit');

?>