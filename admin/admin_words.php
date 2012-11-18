<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_words.php 11192 2009-02-25 01:45:53Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Acess Deined');
}

//х╗оч
if(!checkperm('managewords')) {
	showmessage('no_authority_management_operation');
}

$perpage = 20;
$page = intval(postget('page'));
($page < 1 ) ? $page = 1 : '';
$start = ($page - 1) * $perpage;

$delwords = '';
$updateword = array();
$_POST['delwords'] = isset($_POST['delwords']) ? $_POST['delwords'] : array();
if(submitcheck('delcensorsubmit')) {
	if(!empty($_POST['find']) && is_array($_POST['find'])) {
		foreach($_POST['find'] as $id=>$value) {
			if(is_array($_POST['delwords']) && in_array($id, $_POST['delwords'])) {
				continue;
			} else {
				if(strlen($value) < 3) {
					showmessage('censor_keywords_tooshort');
				} elseif(in_array($value, $updateword)) {
					showmessage('('.$value.')'.$alang['censor_keywords_exists'], $theurl);
				}
				$updateword[] = $value;
				$_POST['replacement'][$id] = addslashes(str_replace("\\\'", "\'", $_POST['replacement'][$id]));
				$_POST['find'][$id] = $value = trim(str_replace('=', '', $value));	
				$_SGLOBAL['db']->query('UPDATE '.tname('words')." SET find='$value', replacement='{$_POST['replacement'][$id]}' WHERE id='$id'");
			}
		}
	}
	if(!empty($_POST['delwords']) && is_array($_POST['delwords'])) {
		$delwords = implode('\',\'', $_POST['delwords']);
		$_SGLOBAL['db']->query('DELETE FROM '.tname('words').' WHERE id IN (\''.$delwords.'\')');
	}
	
	$_POST['newfind'] = trim(str_replace('=', '', $_POST['newfind']));
	$_POST['newreplacemnet'] = trim($_POST['newreplacement']);
	if(!empty($_POST['newfind'])) {
		if(strlen($_POST['newfind']) < 3){
			showmessage('censor_keywords_tooshort');
		}
		$_POST['newreplacement'] = addslashes(str_replace("\\\'", "\'", $_POST['newreplacement']));;
		$query = $_SGLOBAL['db']->query('SELECT admin FROM '.tname('words')." WHERE find='$_POST[newfind]'");
		if($word = $_SGLOBAL['db']->fetch_array($query)) {
			showmessage('censor_keywords_exists');
		} else {
			$_SGLOBAL['db']->query('INSERT INTO '.tname('words')." (`find`, `replacement`, `admin`) VALUES ('$_POST[newfind]', '$_POST[newreplacement]', '$_SGLOBAL[supe_username]')");
		}
	}
	updatecensorcache();
	showmessage('censor_update_succeed', $theurl);
} elseif (submitcheck('addcensorsubmit')) {
	include_once S_ROOT . './language/admincp_message.lang.php';
	if(!empty($_POST['addcensors'])) {
		$oldwords = array();
		if($_POST['overwrite'] == 2) {
			$_SGLOBAL['db']->query('TRUNCATE '.tname('words'));
		} else {
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('words'));
			while($censor = $_SGLOBAL['db']->fetch_array($query)) {
				$oldwords[md5($censor['find'])] = $censor['replacement'];
			}
		}

		$censorarr = explode("\n", $_POST['addcensors']);
		$updatecount = $newcount = $ignorecount = 0;
		$addcensorstr = $addcensorcomma = '';
		foreach($censorarr as $value) {
			list($newfind, $newreplacement) = array_map('trim', explode('=', $value));
			$newreplacement = empty($newreplacement) ? '**' : addslashes(str_replace("\\\'", "\'", $newreplacement));
			if(strlen($newfind) < 3) {
				$ignorecount++;
				continue;
			} elseif (isset($oldwords[md5($newfind)])) {
				if($_POST['overwrite'] == 1) {
					$updatecount++;
					$_SGLOBAL['db']->query("UPDATE ".tname('words')." SET replacement='$newreplacement' WHERE find='$newfind'");
					
				} else {
					$ignorecount++;
				}
			} else {
				$newcount++;
				$addcensorstr .= "$addcensorcomma ('$newfind', '$newreplacement', '$_SGLOBAL[supe_username]')";
				$addcensorcomma = ',';
				$oldwords[md5($newfind)] = $newreplacement;
			}
		}
		if(!empty($addcensorstr)) {
			$_SGLOBAL['db']->query("INSERT INTO ".tname('words')." (`find`,`replacement`,`admin`) VALUES $addcensorstr");
		}
		updatecensorcache();
		showmessage($amlang['censor_add_words']."<b>($newcount)</b>,".$amlang['censor_update_words']."<b>($updatecount)</b>,".$amlang['censor_ignore_words']."<b>($ignorecount)</b>.", $theurl);
	}
}

$viewclass = $addclass = $exportclass = $addinput = '';
$_GET['op'] = empty($_GET['op'])?'':$_GET['op'];

if($_GET['op'] == 'export') {
	$exportclass = ' class="active"';
	ob_end_clean();
	header('Cache-control: max-age=0');
	header('Expires: '.gmdate('D, d M Y H:i:s', $_SGLOBAL['timestamp'] - 31536000).' GMT');
	header('Content-Encoding: none');
	header('Content-Disposition: attachment; filename=CensorWords.txt');
	header('Content-Type: text/plain');
	
	$query = $_SGLOBAL['db']->query('SELECT find, replacement FROM '.tname('words'));
	while($censor = $_SGLOBAL['db']->fetch_array($query)) {
		$censor['replacement'] = str_replace('*', '', $censor['replacement']) == '' ? '' : $censor['replacement'];
		echo $censor['find'].(empty($censor['replacement']) ? '' : '='.stripslashes($censor['replacement']))."\n";
	}
	exit;
} elseif($_GET['op'] == 'addcensor') {
	$addclass = ' class="active"';
} elseif(empty($_GET['op'])) {
	$viewclass = ' class="active"';
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('words'));
	$listcount = $_SGLOBAL['db']->result($query, 0);
	$multipage = '';
	$word = $wordsarr = array();
	if(!empty($listcount)) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('words').' LIMIT '.$start.','.$perpage);
		while($word = $_SGLOBAL['db']->fetch_array($query)) {
			$wordsarr[] = $word;
		}
		$multipage = multi($listcount, $perpage, $page, $theurl);
	}
}

print<<<END
	<script type="text/javascript">
	</script>
	<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
	<td><h1>$alang[supe_words_censor]</h1></td>
		<td class="actions">
		<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
		<tr>
		<td$viewclass><a href="$theurl" class="view">$alang[supe_words_censor]</a></td>
		<td$addclass><a href="$theurl&op=addcensor" class="add">$alang[words_add_lot]</a></td>
		<td$exportclass><a href="$theurl&op=export" class="view">$alang[words_export]</a></td>
		</tr>
		</table>
	</td>
	</tr>
	</table>
	<form method="post" action="$theurl" enctype="multipart/form-data">
END;
echo '<input type="hidden" name="formhash" value="'.formhash().'">';

if(empty($_GET['op'])) {
	echo "<table cellspacing='2' cellpadding='2' class='helptable'><tr><td>$alang[words_censor_help]</td></tr></table>
		<table cellspacing='0' cellpadding='0' width='100%'  class='listtable'>
		<tr>
			<th>$alang[words_del]</th>
			<th>$alang[bad_words]</th>
			<th>$alang[replace_words]</th>
			<th>$alang[words_op_user]</th>
		</tr>";
	if(!empty($wordsarr)) {
		foreach($wordsarr as $value) {
			$value['replacement'] = htmlspecialchars(stripslashes($value['replacement']));
			echo "<tr>
				<td><input type='checkbox' name='delwords[]' value='$value[id]'></td>
				<td><input type='text' name='find[$value[id]]' value=\"".$value['find']."\"></td>
				<td><input type='text' name='replacement[$value[id]]' value=\"".$value['replacement']."\"></td>
				<td>$value[admin]</td>
			</tr>";
		}
	}
	echo  "<tr>
		<td>$alang[words_add]</td>
		<td><input type='text' name='newfind' value=''></td>
		<td><input type='text' name='newreplacement' value=''></td>
		</tr>";
	echo '<table cellspacing="0" cellpadding="0" width="100%"  class="btmtable">';
	echo '<tr><th><input type="checkbox" name="chkall" onclick="checkall(this.form, \'delwords\')">'.$alang['space_select_all'].'<input name="worddelete" type="radio" value="1" checked /> '.$alang['common_delete'].'</th></tr>';
	echo '</table>';
	echo '</table>';
	if(!empty($multipage)) {
		echo '<table cellspacing="0" cellpadding="0" width="100%"  class="listpage">';
		echo '<tr><td>'.$multipage.'</td></tr>';
	}
	echo '<div class="buttons">';
	echo '<input type="submit" name="delcensorsubmit" value="'.$alang['common_submit'].'" class="submit"> ';
	echo '</div>';
	echo '</form>';
} else {
	echo "<table cellspacing='2' cellpadding='2' class='helptable'><tr><td>$alang[words_addcensor_help]</td></tr></table>";
	echo "<table cellspacing='0' cellpadding='0' width='100%'  class='maintable'>";
	echo "<tr><td width='20%'><br /><br /><strong>$alang[words_addcensor_example]</strong><br />toobad<br />nobad<br />badword=good<br />sexword={BANNED}<br /></td><td><TEXTAREA style='WIDTH: 90%' name=addcensors rows=10 cols=80></TEXTAREA><br /><input type='radio' name='overwrite' value=2>$alang[words_addcensor_all]<br /><input type='radio' name='overwrite' value=1 >$alang[words_addcensor_over]<br /><input type='radio' name='overwrite' value=0 checked>$alang[words_addcensor_add]</td></tr>";
	echo "</table>";
	echo '<div class="buttons">';
	echo '<input type="submit" name="addcensorsubmit" value="'.$alang['common_submit'].'" class="submit"> ';
	echo '</div>';
	echo '</form>';
}
?>