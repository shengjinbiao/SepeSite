<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: db_mysql_error.inc.php 11073 2009-02-10 05:01:48Z zhaolei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($_SGLOBAL['supe_username'])) {
	$_SGLOBAL['supe_username'] = 'guest';
}
if(empty($_SCONFIG['timeoffset'])) {
	$_SCONFIG['timeoffset'] = 8;
}

$timestamp = time();
$errmsg = '';
$dberror = $this->error();
$dberrno = $this->errno();

if($dberrno == 1114) {
?>
<html>
<head>
<title>Max Onlines Reached</title>
</head>
<body bgcolor="#FFFFFF">
<table cellpadding="0" cellspacing="0" border="0" width="600" align="center" height="85%">
  <tr align="center" valign="middle">
    <td>
    <table cellpadding="10" cellspacing="0" border="0" width="80%" align="center" style="font-family: Verdana, Tahoma; color: #666666; font-size: 9px">
    <tr>
      <td valign="middle" align="center" bgcolor="#EBEBEB">
        <br><b style="font-size: 10px">Site onlines reached the upper limit</b>
        <br><br><br>Sorry, the number of online visitors has reached the upper limit.
        <br>Please wait for someone else going offline or visit us in idle hours.
        <br><br>
      </td>
    </tr>
    </table>
    </td>
  </tr>
</table>
</body>
</html>
<?
	exit();
} else {

	if($message) {
		$errmsg = "<b>SupeSite info</b>: $message\n\n";
	}
	if(!empty($_SGLOBAL['supe_username'])) {
		$errmsg .= "<b>User</b>: ".shtmlspecialchars(stripslashes($_SGLOBAL['supe_username']))."\n";
	}
	$errmsg .= "<b>Time</b>: ".gmdate("Y-n-j g:ia", $_SGLOBAL['timestamp'] + ($_SCONFIG['timeoffset'] * 3600))."\n";
	$errmsg .= "<b>Script</b>: ".(empty($_SERVER['PHP_SELF'])?$_SERVER['SCRIPT_NAME']:$_SERVER['PHP_SELF'])."\n\n";
	if($sql) {
		$errmsg .= "<b>SQL</b>: ".htmlspecialchars($sql)."\n";
	}
	$errmsg .= "<b>Error</b>:  $dberror\n";
	$errmsg .= "<b>Errno.</b>:  $dberrno";

	echo "</table></table></table></table></table>\n";
	echo "<p style=\"font-family: Verdana, Tahoma; font-size: 11px; background: #FFFFFF;\">";
	echo nl2br(str_replace(array($GLOBALS['tablepre'], $GLOBALS['tablepre_bbs']), '[Table]', $errmsg));
	
	if($_SCONFIG['dbreport'] && $_SCONFIG['adminemail']) {
		$errlog = array();
		if(@$fp = fopen(S_ROOT.'./log/dberror.log', 'r')) {
			while((!feof($fp)) && count($errlog) < 20) {
				$log = explode("\t", fgets($fp, 50));
				if($_SGLOBAL['timestamp'] - $log[0] < 86400) {
					$errlog[$log[0]] = $log[1];
				}
			}
			fclose($fp);
		}

		if(!in_array($dberrno, $errlog)) {
			$errlog[$_SGLOBAL['timestamp']] = $dberrno;
			@$fp = fopen(S_ROOT.'./log/dberror.log', 'w');
			@flock($fp, 2);
			foreach(array_unique($errlog) as $dateline => $errno) {
				@fwrite($fp, "$dateline\t$errno");
			}
			@fclose($fp);
			if(function_exists('errorlog')) {
				errorlog('MySQL', basename($_SERVER['SCRIPT_NAME'])." : $dberror - ".cutstr($sql, 120), 0);
			}

			echo "<br><br>An error report has been dispatched to our administrator.";

			$email_to = $_SCONFIG['adminemail'];
			$email_subject = '[SupeSite] MySQL Error Report';
			$email_message = "There seems to have been a problem with the database of your SupeSite.\n\n".
					strip_tags($errmsg)."\n\n".
					"Please check-up your MySQL server and forum scripts, similar errors will not be reported again in recent 24 hours\n".
					"If you have troubles in solving this problem, please visit SupeSite Community http://www.SupeSite.com.";
			include(S_ROOT.'./function/sendmail.fun.php');
			sendmail(array($email_to), $email_subject, $email_message);
		} else {
			echo '<br><br>Similar error report has beed dispatched to administrator before.';
		}

	}
	echo '</p>';
	exit();
}

?>