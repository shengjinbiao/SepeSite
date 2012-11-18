<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_styletpl.php 13382 2009-10-09 07:06:41Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//х╗оч
if(!checkperm('managestyletpl')) {
	showmessage('admincp_no_founder_popedom');
}

$listarr = $thevalue = $addvalue = array();

if (submitcheck('valuesubmit')) {

	$filename = '';
	if(!empty($_POST['filename'])) $filename = str_replace(array('..', '/', '\\'), array('', '', ''), $_POST['filename']);
	if(!empty($filename)) {
		$filefullname = S_ROOT.'./styles/'.$filename.'.html.php';
		if(!is_writeable($filefullname)) {
			showmessage('tpl_edit_invalid');
		}
		$_POST['content'] = str_replace('<?exit?>', '', $_POST['content']);
		$fp = fopen($filefullname, 'wb');
		flock($fp, 2);
		fwrite($fp, stripslashes('<?exit?>'.$_POST['content']));
		fclose($fp);
	} else {
		showmessage('tpl_filename_error');
	}
	showmessage('tpl_edit_success', $theurl);

} elseif (submitcheck('addsubmit')) {

	$filename = '';
	if(!empty($_POST['filename'])) $filename = str_replace(array('..', '/', '\\'), array('', '', ''), $_POST['filename']);
	if(!empty($filename)) {
		$filefullname = S_ROOT.'./styles/'.$filename.'.html.php';
		if(!file_exists($filefullname)) {
			$fp = fopen($filefullname, 'wb');
			flock($fp, 2);
			fwrite($fp, '<?exit?>');
			fclose($fp);
		}
		header('Location: '.$theurl.'&op=edit&filename='.$filename);
		exit();
	} else {
		showmessage('tpl_filename_error');
	}

}

//GET METHOD
$addclass = $viewclass = '';
if (empty($_GET['op'])) {

	//LIST VIEW
	$tpldir = dir(S_ROOT.'./styles');
	$tplarray = array();
	while(false !== ($entry = $tpldir->read())) {
		$extension = substr($entry, -9, 9);
		if($extension == '.html.php') {
			$tplname = substr($entry, 0, -9);
			$pos = strpos($tplname, '_');
			if(!$pos) {
				$tplarray[$tplname][] = $tplname;
			} else {
				$tplarray[substr($tplname, 0, $pos)][] = $tplname;
			}
		}
	}
	$tpldir->close();
	ksort($tplarray);
	$templates = '';
	foreach($tplarray as $tpl => $subtpls) {
		$templates .= "<ul style=\"line-height:150%;\"><li><b>$tpl</b><ul>\n";
		foreach($subtpls as $filename) {
			$templates .= "<li>$filename &nbsp; <a href=\"$theurl&op=edit&filename=$filename\">[$alang[common_edit]]</a> ".
				"<a href=\"$theurl&op=delete&filename=$filename\" onclick=\"return confirm('$alang[tpl_delete_confirm]');\">[$alang[common_delete]]</a>";
		}
		$templates .= "</ul></ul>\n";
	}

	$listarr['templates'] = $templates;
	$viewclass = ' class="active"';

} elseif ($_GET['op'] == 'edit') {

	$filename = '';
	if(!empty($_GET['filename'])) $filename = str_replace(array('..', '/', '\\'), array('', '', ''), $_GET['filename']);
	if(!empty($filename)) {
		$filefullname = S_ROOT.'./styles/'.$filename.'.html.php';
		if(!is_writeable($filefullname)) {
			showmessage('tpl_edit_invalid');
		}
		$fp = fopen($filefullname, 'rb');
		$content = trim(shtmlspecialchars(fread($fp, filesize($filefullname))));
		fclose($fp);
		
		$thevalue['filename'] = $filename;
		$thevalue['content'] = $content;
	} else {
		showmessage('tpl_filename_error');
	}
	
} elseif ($_GET['op'] == 'add') {
	//ONE ADD
	$addvalue = array(
		'filename' => ''
	);
	$addclass = ' class="active"';
		
} elseif ($_GET['op'] == 'delete') {

	//ONE DELETE
	$filename = '';
	if(!empty($_GET['filename'])) $filename = str_replace(array('..', '/', '\\'), array('', '', ''), $_GET['filename']);
	if(!empty($filename)) {
		$filefullname = S_ROOT.'./styles/'.$filename.'.html.php';
		if(@unlink($filefullname)) {
			showmessage('tpl_delete_ok', $theurl);
		} else {
			showmessage('tpl_delete_error');
		}
	} else {
		showmessage('tpl_filename_error');
	}

}

//MENU
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['module_style_paper'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass.'><a href="'.$theurl.'" class="view">'.$alang['browse_documents_style'].'</a></td>
					<td'.$addclass.'><a href="'.$theurl.'&op=add" class="add">'.$alang['add_style_paper'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';

//LIST SHOW
if($listarr) {
	
	echo label(array('type'=>'table-start', 'class'=>'listtable'));
	echo '<tr><th style="text-align: left;">'.$alang['module_style_paper'].' ( '.$alang['style_dir'].' ./styles/ )</th></tr>';
	echo '<tr><td style="padding-top: 2em;">'.$listarr['templates'].'</td></tr>';
	echo label(array('type'=>'table-end'));

}

//THE VALUE SHOW
if($thevalue) {

	echo '
	<script language="JavaScript">
	var n = 0;
	function displayHTML(obj) {
		win = window.open(" ", \'popup\', \'toolbar = no, status = no, scrollbars=yes\');
		win.document.write("" + obj.value + "");
	}
	function findInPage(obj, str) {
		var txt, i, found;
		if (str == "") {
			return false;
		}
		if (document.layers) {
			if (!obj.find(str)) {
				while(obj.find(str, false, true)) {
					n++;
				}
			} else {
				n++;
			}
			if (n == 0) {
				alert("'.$alang['tpl_keyword_on_find'].'");
			}
		}
		if (document.all) {
			txt = obj.createTextRange();
			for (i = 0; i <= n && (found = txt.findText(str)) != false; i++) {
				txt.moveStart(\'character\', 1);
				txt.moveEnd(\'textedit\');
			}
			if (found) {
				txt.moveStart(\'character\', -1);
				txt.findText(str);
				txt.select();
				txt.scrollIntoView();
				n++;
			} else {
				if (n > 0) {
					n = 0;
					findInPage(str);
				} else {
					alert("'.$alang['tpl_keyword_on_find'].'");
				}
			}
		}
		return false;
	}
	</script>
	';

	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$theurl));
	echo label(array('type'=>'div-start'));

	echo label(array('type'=>'table-start', 'class'=>'listtable'));

	echo '<tr><th style="text-align: left;">styles/<strong><i>'.$thevalue['filename'].'</i></strong>.html.php</th></tr>';
	echo '<tr><td><textarea id="content" name="content" rows="25" style="width:98%;font-family: Courier New, Arial, Helvetica, sans-serif;word-break: break-all;font-size: 11px;">'.$thevalue['content'].'</textarea></td></tr>';
	echo '<tr><th style="text-align: left;"><input type="text" name="keyword" value="" onChange="n=0;" /> <input type="button" value="'.$alang['tpl_title_keyword'].'" onClick="findInPage(this.form.content, this.form.keyword.value)"> <input type="button" value="'.$alang['tpl_title_preview'].'" onClick="javascript:displayHTML(this.form.content);"></th></tr>';

	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo '<input name="filename" type="hidden" value="'.$thevalue['filename'].'" />';
	echo '<input name="valuesubmit" type="hidden" value="yes" />';
	echo label(array('type'=>'form-end'));
}

if($addvalue) {
	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$theurl));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'text', 'alang'=>'tpl_title_filename', 'text'=>'styles/<input type="text" name="filename" value="'.$addvalue['filename'].'" />'.'.html.php'));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'addvaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'addvaluereset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo '<input name="addsubmit" type="hidden" value="yes" />';
	echo label(array('type'=>'form-end'));	
}

?>