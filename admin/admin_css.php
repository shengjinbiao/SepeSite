<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_css.php 13380 2009-09-29 01:31:46Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managecss')) {
	showmessage('admincp_no_founder_popedom');
}

//INIT RESULT VAR
$listarr = array();
$thevalue = array();
$addvalue = array();

//POST METHOD
if(submitcheck('valuesubmit')) {

	$filename = '';
	if(!empty($_POST['filename'])) $filename = str_replace(array('..', '/', '\\'), array('', '', ''), $_POST['filename']);
	if(!empty($filename)) {
		$filefullname = S_ROOT.'./css/'.$filename.'.css';
		if(!is_writeable($filefullname)) {
			showmessage('tpl_edit_invalid');
		}
		$fp = fopen($filefullname, 'wb');
		flock($fp, 2);
		fwrite($fp, stripslashes($_POST['content']));
		fclose($fp);
	} else {
		showmessage('tpl_filename_error');
	}
	showmessage('tpl_edit_success', $theurl);

} elseif(submitcheck('addsubmit')) {

	$filename = '';
	if(!empty($_POST['filename'])) $filename = str_replace(array('..', '/', '\\'), array('', '', ''), $_POST['filename']);
	if(!empty($filename)) {
		$filefullname = S_ROOT.'./css/'.$filename.'.css';
		if(!file_exists($filefullname)) {
			$fp = fopen($filefullname, 'wb');
			flock($fp, 2);
			fwrite($fp, '/**	站点CSS样式表('.$filename.')	**/');
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
	$cssdir = dir(S_ROOT.'./css');
	$cssarray = array();
	while(false !== ($entry = $cssdir->read())) {
		$extension = substr($entry, -4, 4);
		if($extension == '.css') {
			$cssname = substr($entry, 0, -4);
			$pos = strpos($cssname, '_');
			if(!$pos) {
				$cssarray['default'][] = $cssname;
			} else {
				$cssarray[substr($cssname, 0, $pos)][] = $cssname;
			}
		}
	}
	$cssdir->close();
	ksort($cssarray);
	$listarr['css'] = '';
	foreach($cssarray as $css => $subcsss) {
		$listarr['css'] .= "<ul style=\"line-height:150%;\"><li><b>$css</b><ul>\n";
		foreach($subcsss as $filename) {
			$listarr['css'] .= "<li>$filename &nbsp;&nbsp; <a href=\"$theurl&op=edit&filename=$filename\">[$alang[common_edit]]</a> ".
				"<a href=\"$theurl&op=delete&filename=$filename\" onclick=\"return confirm('$alang[tpl_delete_confirm]');\">[$alang[common_delete]]</a>";
		}
		$listarr['css'] .= "</ul></ul>\n";
	}
	$viewclass = ' class="active"';

} elseif($_GET['op'] == 'edit') {

	$filename = '';
	if(!empty($_GET['filename'])) $filename = str_replace(array('..', '/', '\\'), array('', '', ''), $_GET['filename']);
	if(!empty($filename)) {
		$filefullname = S_ROOT.'./css/'.$filename.'.css';
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
	
} elseif($_GET['op'] == 'add') {

	//ONE ADD
	$addvalue = array(
		'filename' => ''
	);
	$addclass = ' class="active"';
		
} elseif($_GET['op'] == 'delete') {

	//ONE DELETE
	$filename = '';
	if(!empty($_GET['filename'])) $filename = str_replace(array('..', '/', '\\'), array('', '', ''), $_GET['filename']);
	if(!empty($filename)) {
		$filefullname = S_ROOT.'./css/'.$filename.'.css';
		if(@unlink($filefullname)) {
			showmessage('tpl_delete_ok', $theurl);
		} else {
			showmessage('tpl_delete_error');
		}
	} else {
		showmessage('tpl_filename_error');
	}

}

//SHOW HTML
//MENU
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['admincp_header_css'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass.'><a href="'.$theurl.'" class="view">'.$alang['css_view_list'].'</a></td>
					<td'.$addclass.'><a href="'.$theurl.'&op=add" class="add">'.$alang['css_add'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';

//LIST SHOW
if($listarr) {
	
	echo label(array('type'=>'table-start', 'class'=>'listtable'));
	echo '<tr><th style="text-align: left;">'.$alang['admincp_header_css'].' ( '.$alang['style_dir'].' ./css/ )</th></tr>';
	echo '<tr><td style="padding-top: 2em;">'.$listarr['css'].'</td></tr>';
	echo label(array('type'=>'table-end'));

}

//THE VALUE SHOW
if($thevalue) {

	echo '
	<script language="JavaScript">
	var n = 0;
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

	echo '<tr><th style="text-align: left;">css/<strong><i>'.$thevalue['filename'].'</i></strong>.css</th></tr>';
	echo '<tr><td><textarea name="content" rows="25" style="width:98%;font-family: Courier New, Arial, Helvetica, sans-serif;word-break: break-all;font-size: 11px;">'.$thevalue['content'].'</textarea></td></tr>';
	echo '<tr><th style="text-align: left;"><input type="text" name="keyword" value="" onChange="n=0;" /> <input type="button" name="btnkeyword" value="'.$alang['tpl_title_keyword'].'" onClick="findInPage(this.form.content, this.form.keyword.value)"> <input type="button" name="btnopenwindow" value="'.$alang['css_help_tool'].'" onClick="OpenWindow(\''.S_URL.'/images/css/tool.html\', \'OnLine_CSS_Designer\', 750, 450);"></th></tr>';
	
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
	echo label(array('type'=>'text', 'alang'=>'tpl_title_filename', 'text'=>'css/<input type="text" name="filename" value="'.$addvalue['filename'].'" />'.'.css'));
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