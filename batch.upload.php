<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.upload.php 13310 2009-08-31 05:35:30Z zhaofei $
*/

include_once('./common.php');
include_once(S_ROOT.'./language/batch.lang.php');

@header('Content-Type: text/html; charset='.$_SCONFIG['charset']);

if(!checkperm('allowpostattach')) {
	exit();
} 

$uid = $_SGLOBAL['supe_uid'];
if(empty($uid)) exit;

$showtext = $hash = $allowtype_ext = '';
$action = postget('action');
$noinsert = intval(postget('noinsert'));
$aid = 0;
if($action == 'delete' || $action == 'edit') {
	
	//debug 删除编辑
	$aid = intval(postget('aid'));
	$uc = postget('uc');
	if(empty($aid) || empty($uc)) exit;
	$ucarr = explode('|', authcode($uc, 'DECODE'));
	if(empty($ucarr[0]) || empty($ucarr[1])) exit;
	$uid = intval($ucarr[0]);
	if(empty($uid)) exit;
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('attachments')." WHERE aid='$aid' AND uid='$uid'");
	if(!$value = $_SGLOBAL['db']->fetch_array($query)) exit;
	$hash = $value['hash'];

	if($action == 'delete') {
		@unlink(A_DIR.'/'.$value['filepath']);
		@unlink(A_DIR.'/'.$value['thumbpath']);
		deletetable('attachments', array('aid'=>$aid));
		$aid = 0;
		showresult($blang['successfully_deleted_files'], 'ok');
	} else {
		showresult('');
	}

} elseif (!empty($_POST)) {
	
	//编辑标题
	if(!empty($_GET['editaid']) && $editaid = intval($_GET['editaid'])) {
		$editsubject = cutstr(trim(shtmlspecialchars($_POST['editsubject'])), 50);
		updatetable('attachments', array('subject'=>$editsubject), array('aid'=>$editaid));

		print <<<END
		<script language="javascript">
		var div = parent.document.getElementById("div_upload_" + $editaid);
		var pf = parent.document.getElementById("phpframe");
		pf.src = "about:blank";
		div.innerHTML = "$editsubject";
		</script>
END;
		exit;
	}
	
	//上传文件
	//上传模式
	$mode = intval(postget('mode'));
	if($mode>3) exit;

	$hash = trim(preg_replace("/[^a-z0-9\-\_]/i", '', trim($_POST['hash'])));
	if(strlen($hash) != 16) showresult($blang['unable_to_complete_this_craft']);
	
	//个数
	$filecount = 1;

	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('attachments').' WHERE hash=\''.$hash.'\'');
	$count = $_SGLOBAL['db']->result($query, 0);
	$allowmax = intval($_POST['uploadallowmax']);
	if($allowmax > 0 && $count + $filecount > $allowmax) showresult($blang['the_number_has_reached_maximum']);

	//类型
	$allowtypearr = getallowtype(trim($_POST['uploadallowtype']));
	
	//空间
	$attachsize = 0;
	include_once(S_ROOT.'./function/upload.func.php');
	if(empty($mode)) {

		//本地上传
		//检查
		$filearr = $_FILES['localfile'];
		if(empty($filearr['size']) || empty($filearr['tmp_name'])) showresult($blang['failure_to_obtain_upload_file_size']);
		$fileext = fileext($filearr['name']);
		if(!empty($allowtypearr)) {
			if(empty($allowtypearr[$fileext])) showresult($blang['upload_not_allow_this_type_of_resources']." ($allowtype_ext)");
			if($filearr['size'] > $allowtypearr[$fileext]['maxsize']) showresult($blang['file_size_exceeded_the_permissible_scope']);
		}
		//缩略图
		if(!empty($_POST['uploadthumb0']) && !empty($_SCONFIG['thumbarray'][$_POST['uploadthumb0']])) {
			$thumbarr = $_SCONFIG['thumbarray'][$_POST['uploadthumb0']];
		} else {
			$thumbarr = array($_POST['thumbwidth'], $_POST['thumbheight']);
		}
		
		//上传
		$newfilearr = savelocalfile($filearr, $thumbarr);
		if(empty($newfilearr['file'])) showresult($blang['uploading_files_failure']);

		//数据库
		if(empty($_POST['uploadsubject0'])) $_POST['uploadsubject0'] = cutstr(filemain($filearr['name']), 50);
		$insertsqlarr = array(
			'uid' => $uid,
			'dateline' => $_SGLOBAL['timestamp'],
			'filename' => saddslashes($filearr['name']),
			'subject' => trim(shtmlspecialchars($_POST['uploadsubject0'])),
			'attachtype' => $fileext,
			'isimage' => (in_array($fileext, array('jpg','jpeg','gif','png'))?1:0),
			'size' => $filearr['size'],
			'filepath' => $newfilearr['file'],
			'thumbpath' => $newfilearr['thumb'],
			'hash' => $hash
		);
		inserttable('attachments', $insertsqlarr);

		showresult($blang['upload_document_has_been_successful'], 'ok');
			
	} elseif ($mode == 1) {
		//远程上传
		//检查类型
		$remoteurl = trim($_POST['remotefile']);
		$fileext = fileext($remoteurl);
		if(!empty($allowtypearr)) {
			if(empty($allowtypearr[$fileext])) showresult($blang['upload_not_allow_this_type_of_resources']." ($allowtype_ext)");
		}

		//缩略图
		if(!empty($_POST['uploadthumb1']) && !empty($_SCONFIG['thumbarray'][$_POST['uploadthumb1']])) {
			$thumbarr = $_SCONFIG['thumbarray'][$_POST['uploadthumb1']];
		} else {
			$thumbarr = array($_POST['thumbwidth'], $_POST['thumbheight']);
		}
		
		//远程上传
		if(!empty($allowtypearr[$fileext]['maxsize'])) {
			$filearr = saveremotefile($remoteurl, $thumbarr, 1, $allowtypearr[$fileext]['maxsize']);
		} else {
			$filearr = saveremotefile($remoteurl, $thumbarr, 1, 0);
		}
		
		//检查大小
		if(empty($filearr['file'])) showresult($blang['unable_to_access_remote_documents']);
		if(!empty($allowtypearr)) {
			if($filearr['size'] > $allowtypearr[$fileext]['maxsize']) {
				@unlink(A_DIR.'/'.$filearr['file']);
				if(!empty($filearr['thumb'])) @unlink(A_DIR.'/'.$filearr['thumb']);
				showresult($blang['file_size_exceeded_the_permissible_scope']);
			}
		}

		//数据库
		if(empty($_POST['uploadsubject1'])) $_POST['uploadsubject1'] = cutstr(filemain($filearr['name']), 50);
		$insertsqlarr = array(
			'uid' => $uid,
			'dateline' => $_SGLOBAL['timestamp'],
			'filename' => saddslashes($filearr['name']),
			'subject' => trim(shtmlspecialchars($_POST['uploadsubject1'])),
			'attachtype' => $filearr['type'],
			'isimage' => (in_array($fileext, array('jpg','jpeg','gif','png'))?1:0),
			'size' => $filearr['size'],
			'filepath' => $filearr['file'],
			'thumbpath' => $filearr['thumb'],
			'hash' => $hash
		);
		inserttable('attachments', $insertsqlarr);

		showresult($blang['upload_document_has_been_successful'], 'ok');
		
	} elseif ($mode == 2) {
		//批量上传
		$filecount = count($_FILES['batchfile']['tmp_name']) - 1;

		$okcount = 0;
		for($i=0; $i<$filecount; $i++) {
			$filearr = array(
				'name' => $_FILES['batchfile']['name'][$i],
				'tmp_name' => $_FILES['batchfile']['tmp_name'][$i],
				'size' => $_FILES['batchfile']['size'][$i]
			);
			if(empty($filearr['size']) || empty($filearr['tmp_name'])) continue;
			$fileext = fileext($filearr['name']);
			if(!empty($allowtypearr)) {
				if(empty($allowtypearr[$fileext])) continue;
				if($filearr['size'] > $allowtypearr[$fileext]['maxsize']) continue;
			}

			$attachsize = $attachsize + $filearr['size'];
			//缩略图
			if(!empty($_POST['thumb'][$i]) && !empty($_SCONFIG['thumbarray'][$_POST['thumb'][$i]])) {
				$thumbarr = $_SCONFIG['thumbarray'][$_POST['thumb'][$i]];
			} else {
				$thumbarr = array($_POST['thumbwidth'], $_POST['thumbheight']);
			}
			
			//上传
			$newfilearr = savelocalfile($filearr, $thumbarr);
			
			if(empty($newfilearr['file'])) continue;

			if(!is_array($_POST['picname'])) {
				showresult($blang['unable_to_complete_this_craft']);
			}
			//数据库
			$insertsqlarr = array(
				'uid' => $uid,
				'dateline' => $_SGLOBAL['timestamp'],
				'filename' => saddslashes($filearr['name']),
				'subject' => trim(shtmlspecialchars($_POST['picname'][$i])),
				'attachtype' => $fileext,
				'isimage' => (in_array($fileext, array('jpg','jpeg','gif','png'))?1:0),
				'size' => $filearr['size'],
				'filepath' => $newfilearr['file'],
				'thumbpath' => $newfilearr['thumb'],
				'hash' => $hash
			);
			inserttable('attachments', $insertsqlarr);
			
			$attachsize = $attachsize + $filearr['size'];
			$okcount++;
		}
		
		if($okcount) {
		} else {
			showresult($blang['upload_other_space']);
		}
		if($okcount < $filecount) {
			showresult(($filecount-$okcount).$blang['upload_not_meet_requirements'], 'ok');
		} else {
			showresult($blang['upload_document_has_been_successful'], 'ok');
		}
	} elseif ($mode == 3) {
		//获取已经下载过的图片列表
		$getimages = '';
		if(isset($_POST['getimages'])){
			$getimages = $_POST['getimages'];
		}

		//抓取图片
		$arrayimageurl = $temp = $imagereplace = array();
		$string = stripcslashes($_POST['message']);
		if(isset($_POST['itemid']) && intval($_POST['itemid']) == 0) {
			$pagebreak = 1;
		} else {
			$pagebreak = 0;
		}

		preg_match_all("/\<img.+src=('|\"|)?(.*)(\\1)([\s].*)?\>/ismUe", $string, $temp, PREG_SET_ORDER);
		if(is_array($temp) && !empty($temp)) {
			$getimageslength = strlen($getimages);
			foreach($temp as $tempvalue) {
				$tempvalue[2] = str_replace('\"', '', $tempvalue[2]);
				if(strlen(str_replace($tempvalue[2], '', $getimages)) == $getimageslength){
					$arrayimageurl[] = $tempvalue[2];
				}
			}
		}
		
		$arrayimageurl = array_unique($arrayimageurl);
		foreach($arrayimageurl as $tempvalue) {
			$imageurl = $tempvalue;
			$fileext = fileext($imageurl);
			
			if(!empty($allowtypearr)) {
				if(empty($allowtypearr[$fileext])) {
					continue;
				}
			}
			
			//缩略图
			if(!empty($_POST['uploadthumb1']) && !empty($_SGLOBAL['thumbarray'][$_POST['uploadthumb1']])) {
				$thumbarr = $_SGLOBAL['thumbarray'][$_POST['uploadthumb1']];
			} else {
				$thumbarr = array($_POST['thumbwidth'], $_POST['thumbheight']);
			}

			//远程上传
			if(!empty($allowtypearr[$fileext]['maxsize'])) {
				$filearr = saveremotefile($imageurl, $thumbarr, 1, $allowtypearr[$fileext]['maxsize']);
			} else {
				$filearr = saveremotefile($imageurl, $thumbarr, 1, 0);
			}

			//检查大小
			if(empty($filearr['file'])) {
				continue;
			}
			if(!empty($allowtypearr)) {
				if($filearr['size'] > $allowtypearr[$fileext]['maxsize']) {
					@unlink(A_DIR.'/'.$filearr['file']);
					if(!empty($filearr['thumb'])) @unlink(A_DIR.'/'.$filearr['thumb']);
					continue;
				}
			}

			//数据库
			if(empty($_POST['uploadsubject1'])) $_POST['uploadsubject1'] = cutstr(filemain($filearr['name']), 50);
			$insertsqlarr = array(
				'uid' => $uid,
				'dateline' => $_SGLOBAL['timestamp'],
				'filename' => saddslashes($filearr['name']),
				'subject' => trim(shtmlspecialchars($_POST['uploadsubject1'])),
				'attachtype' => $filearr['type'],
				'isimage' => (in_array($fileext, array('jpg','jpeg','gif','png'))?1:0),
				'size' => $filearr['size'],
				'filepath' => $filearr['file'],
				'thumbpath' => $filearr['thumb'],
				'hash' => $hash
			);
			inserttable('attachments', $insertsqlarr);
			$imagereplace['oldimageurl'][] = $imageurl; 
			$imagereplace['newimageurl'][] = A_URL . '/' . $filearr['file'];
		}

		//检查是否有图片下载，并替换掉原有的图片url
		if(!empty($imagereplace)) {
			$string = preg_replace(array("/\<(script|style|iframe)[^\>]*?\>.*?\<\/(\\1)\>/si", "/\<!*(--|doctype|html|head|meta|link|body)[^\>]*?\>/si"), '', $string);
			$string = str_replace($imagereplace['oldimageurl'], $imagereplace['newimageurl'], $string);
			$string = str_replace(array("\r", "\n", "\r\n"), '', addcslashes($string, '/"\\'));
			$getimages = implode('|', $imagereplace['newimageurl']) . '|' . $getimages;
			print <<<EOF
				<script type="text/javascript">
				parent.document.getElementById("message").innerHTML = '';
				th_upload = parent.document.getElementById("tr_upload").getElementsByTagName("th")[0];
				th_upload.innerHTML += '<input type="hidden" name="getimages" value="{$getimages}">';
				function init() {
					parent.et = new parent.word("message", "{$string}", 0, {$pagebreak});
				}
				init();
				</script>
EOF;
			showresult($blang['remote_download_complete_picture'], 'ok');
		} else {
			showresult($blang['not_in_keeping_with_the_long_range_picture']);
		}
	}
} else {
	showresult($blang['uploading_files_failure']);
}

//函数
function showresult($message, $type='error') {
	
	global $hash, $noinsert, $aid;

	$attacharr = empty($hash)?array():(selecttable('attachments', array(), array('hash'=>$hash), 'ORDER BY dateline'));
	$listtext = getuploadinserthtml($attacharr, $noinsert, $aid);

	$listtext = addcslashes($listtext, '"');
	$message = addcslashes($message, '"');
	
	if(empty($message)) {
		$typestr = 'if(msg != null)msg.style.display = "none";
					if(msgok != null)msgok.style.display = "none";';
	} else {
		if($type == 'ok') {
			$typestr = 'if(msg != null)msg.style.display = "none";
						if(msgok != null)msgok.style.display = "";
						if(msgok != null)msgok.innerHTML = "'.$message.'";
						if(subject0 != null)subject0.value = "";
						if(subject1 != null)subject1.value = "";
						if(localfile != null)localfile.outerHTML = localfile.outerHTML;
						if(remotefile != null)remotefile.value = "http://";
						';
		} else {
			$typestr = 'if(msg != null)msg.style.display = "";
						if(msgok != null)msgok.style.display = "none";
						if(msg != null)msg.innerHTML =  "'.$message.'";
						';
		}
	}
	$execjs = '';
	if(!$noinsert) {
		$execjs = 'parent.setdefaultpic()';
	}
	print <<<END
	<script language="javascript">
	<!--

	var list = parent.document.getElementById("divshowupload");
	var msg = parent.document.getElementById("divshowuploadmsg");
	var msgok = parent.document.getElementById("divshowuploadmsgok");
	var subject0 = parent.document.getElementById("uploadsubject0");
	var subject1 = parent.document.getElementById("uploadsubject1");
	var localfile = parent.document.getElementById("localfile");
	var remotefile = parent.document.getElementById("remotefile");
	var pf = parent.document.getElementById("phpframe");
	localfile.value = "";
	pf.src = "about:blank";
	list.innerHTML = "$listtext";
	$typestr
	$execjs
	//-->
	</script>
END;
	exit;
}

function getallowtype($sqltype='') {
	global $_SGLOBAL, $allowtype_ext;

	$sqltype = strtolower($sqltype);
	$types = $exts = array();
	if(empty($sqltype)) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('attachmenttypes'));
	} else {
		$sqltypearr = explode(',', $sqltype);
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('attachmenttypes').' WHERE fileext IN ('.simplode($sqltypearr).')');
	}
	while ($type = $_SGLOBAL['db']->fetch_array($query)) {
		$exts[] = $type['fileext'];
		$types[$type['fileext']] = $type;
	}

	$allowtype_ext = implode(',', $exts);
	return $types;
}

?>