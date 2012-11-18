<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_database.php 11192 2009-02-25 01:45:53Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managedatabase')) {
	showmessage('no_authority_management_operation');
}

//获取分卷编号
$volume = isset($_GET['volume']) ? (intval($_GET['volume']) + 1) : 1;
$excepttables = array($tablepre.'rss');
$datadir = S_ROOT.'./data';
$dataurl = S_URL.'/data';
$backupdir = empty($_SSCONFIG['backupdir']) ? '' :$_SSCONFIG['backupdir'];

//备份文件目录
if(empty($backupdir)) {
	$backupdir = random(6);
	$_SGLOBAL['db']->query('REPLACE INTO '.tname('settings')." (variable, value) VALUES ('backupdir', '$backupdir')");
	updatesettingcache();
}
$backupdir = 'backup_'.$backupdir;
if(!is_dir($datadir.'/'.$backupdir)) {
	@mkdir($datadir.'/'.$backupdir, 0777);
}

//删除备份文件
if(submitcheck('listsubmit')) {

	if(!empty($_POST['delexport']) && is_array($_POST['delexport'])) {
		foreach($_POST['delexport'] as $value) {
			$fileext = fileext($value);
			if($fileext != 'sql' && $fileext != 'zip') {
				continue;
			}
			$value = str_replace('..', '', $value);
			if (file_exists($datadir.'/'.$value)){
				@unlink($datadir.'/'.$value);
			}
		}
	}
	showmessage('database_export_del', $theurl.'&op=import');

} elseif(submitcheck('importsubmit')) {

	$_POST['datafile'] = str_replace('..', '', $_POST['datafile']);
	if(!file_exists($datadir.'/'.$_POST['datafile'])) {
		showmessage('database_import_file_illegal');
	} else {
		$fileext = fileext($_POST['datafile']);
		if($fileext == 'sql') {
			showmessage('database_import_start', $theurl.'&op=importstart&do=import&datafile='.$_POST['datafile']);
		} elseif($fileext == 'zip') {
			showmessage('database_import_start', $theurl.'&op=importstart&do=zip&datafile='.$_POST['datafile']);
		} else {
			showmessage('database_import_file_illegal');
		}
	}

}

$op = postget('op') ? postget('op') : 'export';
$activearr = array('export'=>'', 'import'=>'');
$activearr[$op] = ' class="active"';

//备份跳转
if($op == 'backupstart') {
	//POST OR GET var
	$filename = postget('filename');
	$type = postget('type');
	$sqlcharset = postget('sqlcharset');
	$sqlcompat = postget('sqlcompat');
	$sizelimit = postget('sizelimit');
	$usezip = postget('usezip');
	$method = postget('method');
	$extendins = postget('extendins');
	$usehex = postget('usehex');

	//无报错执行关闭我的创建表和列时不加引号
	$_SGLOBAL['db']->query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');

	$filename = preg_replace("/[^a-z0-9_]/i", '',(str_replace('.', '_', $filename)));
	//文件名长度判断
	if(empty($filename) || strlen($filename) > 40){
		showmessage('database_export_filename_error');
	}

	$tables = array();
	//备份方式
	if($type == 'supesite') {
		$tables = arraykeys2(fetchtablelist($tablepre), 'Name');
	} elseif($type == 'custom') {
		if(isset($_POST['setup'])) {
			//POST提交备份
			$tables = empty($_POST['customtables']) ? array() : $_POST['customtables'];
			$tablesnew = addslashes(serialize($tables));
			$_SGLOBAL['db']->query('REPLACE INTO '.tname('settings')." (variable, value) VALUES ('custombackup', '$tablesnew')");
		} else {
			//自动跳转备份
			$query = $_SGLOBAL['db']->query("SELECT value FROM ".tname('settings')." WHERE variable='custombackup'");
			if($tables = $_SGLOBAL['db']->fetch_array($query)) {
				$tables = unserialize($tables['value']);
			}
		}
	}

	if(empty($tables) && is_array($tables)) {
		showmessage('database_export_tables_error');
	} 

	$time = sgmdate($_SGLOBAL['timestamp']);
	$idstring = '# Identify: '.base64_encode("$_SGLOBAL[timestamp],".S_VER.",$type,$method,$volume")."\n";
	$dumpcharset = $sqlcharset ? $sqlcharset : str_replace('-', '', $_SCONFIG['charset']);
	$setnames = ($sqlcharset && $_SGLOBAL['db']->version() > '4.1' && (!$sqlcompat || $sqlcompat == 'MYSQL41')) ? "SET NAMES '$dumpcharset';\n\n" : '';

	if($_SGLOBAL['db']->version() > '4.1') {
		if($sqlcharset) {
			$_SGLOBAL['db']->query("SET NAMES '".$sqlcharset."'");
		}
		if($sqlcompat == 'MYSQL40') {
			$_SGLOBAL['db']->query("SET SQL_MODE='MYSQL40'");
		} elseif ($sqlcompat == 'MYSQL41') {
			$_SGLOBAL['db']->query("SET SQL_MODE=' '");
		}
	}

	$backupfile = $datadir.'/'.$backupdir.'/'.$filename;
	if($usezip) {
		include_once S_ROOT .'./include/zip.lib.php';
	}

	//分卷备份
	if($method == 'multivol') {
		$sqldump = '';
		//表ID
		$tableid = isset($_GET['tableid']) ? intval($_GET['tableid']) : 0 ;
		//起始位置
		$startfrom = isset($_GET['startfrom']) ? intval($_GET['startfrom']) : 0 ;
		$tablenum = count($tables);
		$filesize = $sizelimit * 1000;
		$complate = true;
		for( ; $complate && $tableid < $tablenum && strlen($sqldump) + 500 < $filesize; ++$tableid) {
			$sqldump .= sqldumptable($tables[$tableid], $startfrom, strlen($sqldump));
			if($complate) {
				$startfrom = 0;
			}
		}

		$dumpfile = sprintf($backupfile.'-%s'.'.sql', $volume);
		!$complate && $tableid --;
		if(trim($sqldump)) {
			$sqldump = "$idstring".
			"# <?exit();?>\n".
			"# SupeSite Multi-Volume Data Dump Vol.$volume\n".
			"# Version: SupeSite ".S_VER."\n".
			"# Time: $time\n".
			"# Type: $type\n".
			"# Table Prefix: $tablepre\n".
			"#\n".
			"# SupeSite Home: http://www.supesite.com\n".
			"# Please visit our website for newest infomation about SupeSite\n".
			"# ---------------------------------------------------------\n\n\n".
			"$setnames".
			$sqldump;
			$fp = fopen($dumpfile, 'wb');
			@flock($fp, 2);
			if(!fwrite($fp, $sqldump)) {
				fclose($fp);
				showmessage('database_export_write_error', $theurl);
			} else {
				fclose($fp);
				if($usezip == 2) {
					$zipfile = sprintf($backupfile.'-%s'.'.zip', $volume);
					$zipfile = new Zip($zipfile);
					if(!$zipfile->create($dumpfile, PCLZIP_OPT_REMOVE_PATH, $datadir.'/'.$backupdir)) {
						showmessage('database_export_write_error', $theurl);
					} else {
						@unlink($dumpfile);
					}
					fclose($fp);
				}
				showmessage($alang['database_export_multivol_redirect'].$volume.$alang['database_export_multivol_redirect1'], $theurl.'&op=backupstart&type='.rawurldecode($type).'&filename='.rawurlencode($filename).'&method=multivol&sizelimit='.intval($sizelimit).'&tableid='.intval($tableid).'&startfrom='.intval($startrows).'&extendins='.intval($extendins).'&sqlcharset='.rawurlencode($sqlcharset).'&sqlcompat='.rawurlencode($sqlcompat).'&usehex='.intval($usehex).'&usezip='.intval($usezip).'&volume='.intval($volume));
			}
		} else {
			
			if($usezip == 1){
				$zipfile = $backupfile.'.zip';
				$zipfile = new Zip($zipfile);
				$unlinks = '';
				$arrayzipfile = array();
				for($i = 1; $i < $volume; ++$i){
					$dumpfile = sprintf($backupfile.'-%s'.'.sql', $i);
					$arrayzipfile[] = $dumpfile;
					$unlinks .= "@unlink('$dumpfile');";
				}
				if($zipfile->create($arrayzipfile, PCLZIP_OPT_REMOVE_PATH, $datadir.'/'.$backupdir)) {
					@eval($unlinks);
				} else {
					showmessage($alang['database_export_multivol_succeed'].($volume-1).$alang['database_export_multivol_succeed1'], $theurl);
				}
				fclose(fopen($datadir.'/'.$backupdir.'/index.htm', 'a'));
				$_SGLOBAL['db']->query('DELETE FROM '.tname('settings')." WHERE variable='custombackup'");
				showmessage('database_export_zip_succeed', $theurl);
			} else {
				fclose(fopen($datadir.'/'.$backupdir.'/index.htm', 'a'));
				$_SGLOBAL['db']->query('DELETE FROM '.tname('settings')." WHERE variable='custombackup'");
				showmessage($alang['database_export_multivol_succeed'].($volume-1).$alang['database_export_multivol_succeed1'], $theurl.'&op=import');
			}
		}
	} else {
		$tablesstr = '';
		foreach($tables as $value) {
			$tablesstr .= $value.' ';
		}
		list($dbhost, $dbport) = explode(':', $dbhost);
		$query = $_SGLOBAL['db']->query("SHOW VARIABLES LIKE 'basedir'");
		list(, $mysql_base) = $_SGLOBAL['db']->fetch_array($query, MYSQL_NUM);
		$dumpfile = $backupfile.'.sql';
		@unlink($dumpfile);

		$mysqlbin = $mysql_base == '/' ? '' : addslashes($mysql_base).'bin/';
		$dbcharset = empty($dbcharset) ? $charset : $dbcharset;
		@shell_exec('"'.$mysqlbin.'mysqldump" --force --quick --default-character-set='.$dbcharset.' '.($_SGLOBAL['db']->version() > 4.1 ? '--skip-opt --create-options' : '-all').' --add-drop-table'.($extendins == 1 ? '--extended-insert' : '').''.($_SGLOBAL['db']->version() > '4.1' && $sqlcompat == 'MYSQL40' ? '--compatible=mysql40' : '').' --host='.$dbhost.($dbport ? (is_numeric($dbport) ? ' --port='.$dbport : ' --sock='.$dbport) : '').' --user='.$dbuser.' --password='.$dbpw.' '.$dbname.' '.$tablesstr.' > '.$dumpfile);

		if(file_exists($dumpfile)) {
			if(is_writable($dumpfile)) {
				$fp = fopen($dumpfile, 'rb+');
				fwrite($fp,  $idstring."# <?exit();?>\n ".$setnames."\n #");
				fclose($fp);
			}

			if($usezip) {
				require_once S_ROOT .'./include/zip.lib.php';
				$zipfilename = $backupfile.'.zip';
				$zipfile = new Zip($zipfilename);
				if($zipfile->create($dumpfile, PCLZIP_OPT_REMOVE_PATH, $datadir.'/'.$backupdir)) {
					@unlink($dumpfile);
					fclose(fopen($datadir.'/'.$backupdir.'/index.htm', 'a'));
					showmessage('database_export_zip_succeed');
				} else {
					showmessage('database_export_zip_error', $theurl);
				}
			} else {
				fclose(fopen($datadir.'/'.$backupdir.'/index.htm', 'a'));
				showmessage('database_export_succeed');
			}
		} else {
			showmessage('database_shell_faile', $theurl);
		}
	}
}
//HEAD
print<<<END
	<script type="text/javascript">
	</script>
	<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
	<td><h1>$alang[database_op_export]</h1></td>
	<td class="actions">
		<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
		<tr>
		<td$activearr[export]><a href="$theurl&op=export">$alang[database_op_export]</a></td>
		<td$activearr[import]><a href="$theurl&op=import">$alang[database_op_import]</a></td>
		</tr>
		</table>
	</td>
	</tr>
	</table>
END;

//备份
if($op == 'export') {

	$shelldisabled = function_exists('shell_exec') ? '' : 'disabled';
	$zipdisplay = function_exists('gzcompress') ? '' : 'style="display:none"';
	$filename = date('ymd').'_'.random(8);
	$dbversion = intval($_SGLOBAL['db']->version());
	$sqlcharsets = "<input class=\"radio\" type=\"radio\" name=\"sqlcharset\" value=\"\" checked> $alang[customfield_title_isdefault]".
			($dbcharset ? " &nbsp; <input class=\"radio\" type=\"radio\" name=\"sqlcharset\" value=\"$dbcharset\"> ".strtoupper($dbcharset) : '').
			($dbversion > '4.1' && $dbcharset != 'utf8' ? " &nbsp; <input class=\"radio\" type=\"radio\" name=\"sqlcharset\" value='utf8'> UTF-8</option>" : '');
	$tablelist = '';
	//取得SupeSite表
	$supe_tablelist = fetchtablelist($tablepre);

	$rowcount = 0;
	foreach($supe_tablelist as $value) {
		$tablelist .= ($rowcount % 4 ? '' : '</tr><tr>')."<td><input type='checkbox' name='customtables[]' value='$value[Name]' checked>$value[Name]</td>\n";
		$rowcount ++;
	}
	$tablelist .= '</tr>';

	$formhash = formhash();
print<<<END
	<form method="post" action="$theurl&op=backupstart" enctype="multipart/form-data">
	<input type="hidden" name="formhash" value="$formhash">
		<table cellspacing="2" cellpadding="2" class="helptable"><tr><td>$alang[database_export_help]</td></tr></table>
		<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
			<tr>
				<th><input type="radio" name="type" value="supesite" checked onclick="$('showtables').style.display='none'">$alang[supesite_all_database]</th>
			</tr>
			<tr>
				<th><input type="radio" name="type" value="custom" onclick="$('showtables').style.display=''">$alang[supesite_custom_database]</th>
				<td>$alang[custom_database_about]</td>
			</tr>
			<tr>
				<th></th>
				<td style="text-align:right" align="right"><input type="checkbox"  onclick="$('advanceoption').style.display=$('advanceoption').style.display == 'none' ? '' : 'none'; this.value = this.value == 1 ? 0 : 1; this.checked = this.value == 1 ? false : true" value="1">$alang[supesite_more_select]</td>
			</tr>
		</table>
		<div id="showtables" style="display:none">
			<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
			<tr><th><strong>$alang[supesite_database]</strong><input type="checkbox" name="chkall" onclick="checkall(this.form, 'customtables')" checked>$alang[space_select_all]</th>
			$tablelist
			</table>
		</div>
		<div id="advanceoption" style="display:none">
		<h2>$alang[database_method]</h2>
		<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
			<tr>
				<th><input type="radio" name="method" value="shell" $shelldisabled onclick="if($dbversion < '4.1'){if(this.form.sqlcompat[2].checked==true) this.form.sqlcompat[0].checked=true; this.form.sqlcompat[2].disabled=true; this.form.sizelimit.disabled=true;} else {this.form.sqlcharset[0].checked=true; for(var i=1; i<=5; i++) {if(this.form.sqlcharset[i]) this.form.sqlcharset[i].disabled=true;}}">$alang[database_export_shell]</th>
			</tr>
			<tr>
				<th><input type="radio" name="method" value="multivol" checked onclick="this.form.sqlcompat[2].disabled=false; this.form.sizelimit.disabled=false; for(var i=1; i<=5; i++) {if(this.form.sqlcharset[i]) this.form.sqlcharset[i].disabled=false;}">$alang[database_export_multivol]</th>
				<td><input type="text" size="40" value="2048" name="sizelimit"></td>
			</tr>
		</table>
		<h2>$alang[database_export_option]</h2>
		<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
			<tr>
				<th>$alang[database_export_extendins]</th>
				<td><input type="radio" value="1" name="extendins" checked>$alang[setting_pconnect_1] <input type="radio" value="0" name="extendins" checked>$alang[setting_pconnect_0]</td>
			</tr>
			<tr>
				<th>$alang[database_export_options_sql_compatible]</th>
				<td><input type="radio" value="" name="sqlcompat" checked>$alang[customfield_title_isdefault] <input type="radio" value="MYSQL40" name="sqlcompat">MySQL 3.23/4.0.x  <input type="radio" value="MYSQL41" name="sqlcompat">MySQL 4.1.x/5.x </td>
			</tr>
			<tr>
				<th>$alang[database_export_options_charset]</th>
				<td>$sqlcharsets</td>
			</tr>
			<tr>
				<th>$alang[database_export_usehex]</th>
				<td><input type="radio" value="1" name="usehex" checked>$alang[setting_pconnect_1] <input type="radio" value="0" name="usehex" checked>$alang[setting_pconnect_0]</td>
			</tr>
			<tr $zipdisplay>
				<th>$alang[database_export_usezip]</th>
				<td><input type="radio" value="1" name="usezip">$alang[database_export_zip_1] <input type="radio" value="2" name="usezip">$alang[database_export_zip_2] <input type="radio" value="0" name="usezip" checked>$alang[database_export_zip_0]</td>
			</tr>
			<tr>
				<th>$alang[database_export_filename]</th>
				<td><input type="text" size="40" value="$filename" name="filename">.sql</td>
			</tr>
		</table>
		</div>
		<input type="hidden" name="setup" value="1">
		<div class="buttons">
		<input type="submit" name="exportsubmit" value="$alang[common_submit]" class="submit">
		</div>
	</form>
END;

} elseif($op == 'import') {

	//导入
	$exportlog = array();
	if(is_dir($datadir.'/'.$backupdir)) {
		$dir = dir($datadir.'/'.$backupdir);
		while(FALSE !== ($entry = $dir->read())){
			$filename = $datadir.'/'.$backupdir.'/'.$entry;
			$basefile = $backupdir.'/'.$entry;
			if(is_file($filename)){
				$filesize = filesize($filename);
				if(preg_match('/\.sql$/i', $filename)) {
					$fp = fopen($filename, 'rb');
					$identify = explode(',', base64_decode(preg_replace('/^# Identify:\s*(\w+).*/s', '\\1', fgets($fp, 256))));
					fclose($fp);
					$exportlog[] = array(
						'version' => $identify[1],
						'type' => $identify[2],
						'method' => $identify[3],
						'volume' => $identify[4],
						'filename' => $basefile,
						'dateline' => filemtime($filename),
						'size' => $filesize
						);
				} elseif(preg_match('/\.zip$/i', $filename)) {
					$exportlog[] = array(
						'type' => 'zip',
						'filename' => $basefile,
						'size' => $filesize,
						'dateline' => filemtime($filename),
						'method' => '',
					);
				}
			}
		}
		$dir->close();
	} else {
		showmessage('database_export_dest_invalid');
	}
	
	$formhash = formhash();
	print<<<END
		<table cellspacing="2" cellpadding="2" class="helptable"><tr><td>$alang[database_import_help]</td></tr></table>
		<h2>$alang[database_import]</h2>
			<form method="post" id="theform" action="$theurl" name="thevalueform" enctype="multipart/form-data" onSubmit="return listsubmitconfirm(this)">
			<input type="hidden" name="formhash" value="$formhash">
			<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
				<tr><th>$alang[database_import_from]</th><td><input type="text" name="datafile" value="./$backupdir/" size="50"></td></tr>
			</table>
			<div class="buttons">
				<input type="submit" name="importsubmit" value="$alang[common_submit]" class="submit">
			</div>
			<h2>$alang[database_import_list]</h2>
			<table cellspacing="0" cellpadding="0" width="100%"  class="listtable">
				<tr>
				<th width="5%">$alang[database_export_del]</th>
				<th width="20%">$alang[database_export_filename]</th>
				<th width="5%">$alang[database_export_version]</th>
				<th width="20%">$alang[database_export_dateline]</th>
				<th width="10%">$alang[database_export_type]</th>
				<th width="8%">$alang[database_export_filesize]</th>
				<th width="5%">$alang[database_export_method]</th>
				<th width="5%">$alang[database_expot_volume]</th>
				<th width="5%">$alang[database_export_op]</th>
				</tr>
END;
	$exportinfo = '';
	foreach($exportlog as $info) {
		$info['dateline'] = is_int($info['dateline']) ? sgmdate($info['dateline']) : $alang['database_date_unknown'];
		$info['size'] = formatsize($info['size']);
		$info['volume'] = $info['method'] == 'multivol' ? $info['volume'] : '';
		$info['method'] = $info['type'] != 'zip' ? ($info['method'] == 'multivol' ? $alang['database_multivol'] : $alang['database_shell']) : '';
		$import = ($info['type'] == 'zip' ? "<td align=center><a href='$theurl&op=importstart&do=zip&datafile=$info[filename]'>[$alang[database_import_unzip]]</td>" : "<td align='center'><a href='$theurl&op=importstart&do=import&datafile=$info[filename]'".($info['version'] != S_VER ? " onclick=\"return confirm('$alang[database_import_confirm]');\"" : '').">[$alang[database_import_import]]</a></td>"); 
		$exportinfo .= '<tr><td align="center"><input type="checkbox" name="delexport[]" value="'.$info['filename'].'"></td><td><a href='.$dataurl.'/'.$info['filename'].'>'.basename($info['filename']).'</a><td align="center">'.S_VER.'</td><td>'.$info['dateline'].'</td><td align="center">'.$alang['database_export_'.$info['type']].'</td><td align="center">'.$info['size'].'</td><td align="center">'.$info['method'].'</td><td align="center">'.$info['volume'].'</td>'.$import;
	}
	print<<<END
	$exportinfo
	<table cellspacing="0" cellpadding="0" width="100%"  class="btmtable">
	<tr><th><input type="checkbox" name="chkall" onclick="checkall(this.form, 'delexport')">$alang[space_select_all]</th></tr>
	</table>
	</table>
	<div class="buttons">
		<input type="submit" name="listsubmit" value="$alang[common_submit]" class="submit">
	</div>
	</form>
END;

} elseif($op == 'importstart') {

	$do = postget('do') ;
	$delunzip = postget('delunzip');
	$datafile = postget('datafile');
	$confirm = postget('confirm');
	$multivol = postget('multivol');
	$datafile_vol1 = postget('datafile_vol1');
	$autoimport = postget('autoimport');

	if($do == 'zip') {
		require_once S_ROOT .'./include/zip.lib.php';
		$unzip = new SimpleUnzip();
		$unzip->ReadFile($datadir.'/'.$datafile);

		if($unzip->Count() == 0 || $unzip->GetError(0) != 0 || !preg_match('/\.sql$/i', $importfile = $unzip->GetName(0))) {
			showmessage('database_import_file_illegal');
		}

		$identify = explode(',', base64_decode(preg_replace('/^# identify:\s*(\w+).*/s', '\\1', substr($unzip->GetData(0), 0, 256))));

		$info = basename($datafile).'<br />'.$alang['database_export_version'].':'.$identify[1].'<br />'.$alang['database_export_type'].':'.$alang['database_export_'.$identify[2]].'<br />'.$alang['database_method'].':'.($identify[3] == 'multivol' ? $alang['database_multivol'] : $alang['database_shell']).'<br />';
		//检查版本号
		$confirm = isset($confirm) ? 1 : 0;
		if(!$confirm && $identify[1] != S_VER) {
			echo "<table cellspacing='0' cellpadding='0' width='100%' class='helptable'>" .
					"<form method='post' action='$theurl&op=importstart&do=zip&datafile=$datafile&confirm=yes' name='thevalueform' enctype='multipart/form-data'>" .
					'<input type="hidden" name="formhash" value="'.formhash().'">'.
					"<tr><td align='center'>$info<br /><br /><br />$alang[database_import_confirm]<br /><br /></td></tr><br />\n" .
					"<tr><td align='center'><div class='buttons'>" .
					"<input type='submit' name='confirmed' value='$alang[common_continue]' class='submit'>" .
					" <input type='button' value='$alang[common_back]' onClick=\"location.href='$theurl&op=import'\"; class='submit'>" .
					"</div></td></tr></form></table>";
			include_once template('admin/tpl/footer.htm', 1);
			exit();
		}

		$sqlfilecount = 0;
		foreach($unzip->Entries as $entry) {
			if(preg_match('/\.sql$/i', $entry->Name)) {
				$fp = fopen($datadir.'/'.$backupdir.'/'.$entry->Name, 'w');
				fwrite($fp, $entry->Data);
				fclose($fp);
				$sqlfilecount++;
			}
		}

		if(!$sqlfilecount) {
			showmessage('database_import_file_illegal');
		}

		$multivol = isset($multivol) ? $multivol : 0;
		$datafile_vol1 = isset($datafile_vol1) ? $datafile_vol1 : '';

		if(!empty($multivol)) {
			$multivol++;
			$datafile = preg_replace('/-(\d+)(\..+)$/', "-$multivol\\2", $datafile);
			if(file_exists($datadir.'/'.$datafile)) {
				showmessage($alang['database_import_multivol_unzip_redirect'].$multivol.$alang['database_import_multivol_unzip_redirect1'], $theurl.'&op=importstart&do=zip&multivol='.$multivol.'&datafile_vol1='.$datafile_vol1.'&datafile='.$datafile.'&confirm=yes');
			} else {
				echo "<table cellspacing='0' cellpadding='0' width='100%' class='helptable'>" .
					"<form method='post' action='$theurl&op=importstart&do=import&datafile=$datafile_vol1&delunzip=yes' name='thevalueform' enctype='multipart/form-data'>" .
					'<input type="hidden" name="formhash" value="'.formhash().'">'.
					"<tr><td align='center'>$alang[database_import_multivol_confirm]</td></tr><br />\n" .
					"<tr><td align='center'><div class='buttons'>" .
					"<input type='submit' name='confirmed' value='$alang[common_continue]' class='submit'>" .
					" <input type='button' value='$alang[common_back]' onClick=\"location.href='$theurl&op=import'\"; class='submit'>" .
					"</div></td></tr></form></table>";
				include_once template('admin/tpl/footer.htm', 1);
				exit();
			}
		}

		if($identify[3] == 'multivol' && $identify[4] == 1 && preg_match("/-1(\..+)$/", $datafile)) {
			$datafile_vol1 = $datafile;
			$datafile = preg_replace('/-1(\..+)$/', '-2\\1', $datafile);
			if(file_exists($datadir.'/'.$datafile)) {
				echo "<table cellspacing='0' cellpadding='0' width='100%' class='helptable'>" .
					"<form method='post' action='$theurl&op=importstart&do=zip&multivol=1&datafile_vol1=$backupdir/$importfile&datafile=$datafile&confirm=yes' name='thevalueform' enctype='multipart/form-data'>" .
					'<input type="hidden" name="formhash" value="'.formhash().'">'.
					"<tr><td align='center'>$info<br />$alang[database_import_multivol_unzip]<br /></td></tr><br />\n" .
					"<tr><td align='center'><div class='buttons'>" .
					"<input type='submit' name='confirmed' value='$alang[common_continue]' class='submit'>" .
					" <input type='button' value='$alang[common_back]' onClick=\"location.href='$theurl&op=import'\"; class='submit'>" .
					"</div></td></tr></form></table>";
				include_once template('admin/tpl/footer.htm', 1);
				exit();
			}
		}

		echo "<table cellspacing='0' cellpadding='0' width='100%' class='helptable'>" .
			"<form method='post' action='$theurl&op=importstart&do=import&datafile=$backupdir/$importfile&delunzip=yes' name='thevalueform' enctype='multipart/form-data'>" .
			'<input type="hidden" name="formhash" value="'.formhash().'">'.
			"<tr><td align='center'>$info<br />$alang[database_import_unzip_success]<br /></td></tr><br />\n" .
			"<tr><td align='center'><div class='buttons'>" .
			"<input type='submit' name='confirmed' value='$alang[common_continue]' class='submit'>" .
			" <input type='button' value='$alang[common_back]' onClick=\"location.href='$theurl&op=import'\"; class='submit'>" .
			"</div></td></tr></form></table>";
		include_once template('admin/tpl/footer.htm', 1);
		exit();
	} elseif($do == 'import') {
		$sqldump = '';
		$datafile_root = $datadir.'/'.$datafile;

		if($fp = @fopen($datafile_root, 'rb')) {
			$sqldump = fgets($fp, 256);
			$identify = explode(',', base64_decode(preg_replace('/^# Identify:\s*(\w+).*/s', '\\1', $sqldump)));
			if($identify[3] == 'multivol') {
				$sqldump .= fread($fp,filesize($datafile_root));
			}
			fclose($fp);
		} else {
			if(isset($autoimport)) {
				showmessage('database_import_multivol_succeed', $theurl.'&op=import');
			} else {
				showmessage('database_import_file_illegal');
			}
		}

		if($identify[3] == 'multivol') {
			$sqlquery = splitsql($sqldump);
			unset($sqldump);
			foreach($sqlquery as $sql) {
				$sql = syntablestruct(trim($sql), $_SGLOBAL['db']->version() > '4.1', $dbcharset);
				if(!empty($sql)) {
					//屏蔽错误
					$_SGLOBAL['db']->query($sql, 'SILENT');
					if($_SGLOBAL['db']->error() && $_SGLOBAL['db']->errno() != 1062) {
						$_SGLOBAL['db']->halt('MySQL Query Error', $sql);
					}
				}
			}

			if($delunzip == 'yes') {
				@unlink($datadir.'/'.$datafile);
			}

			$identify[4] = intval($identify[4]);
			$datafile_next = preg_replace("/-($identify[4])(\..+)$/", '-'.($identify[4] + 1).'\\2', $datafile);

			if($identify[4] == 1) {
				echo "<table cellspacing='0' cellpadding='0' width='100%' class='helptable'>" .
					"<form method='post' action='$theurl&op=importstart&do=import&datafile=$datafile_next&autoimport=yes".(isset($unzip) ? '&delunzip=yes' : '')." name='thevalueform' enctype='multipart/form-data'>" .
					'<input type="hidden" name="formhash" value="'.formhash().'">'.
					"<tr><td align='center'>$alang[database_import_multivol_prompt]</td></tr><br />\n" .
					"<tr><td align='center'><div class='buttons'>" .
					"<input type='submit' name='confirmed' value='$alang[common_continue]' class='submit'>" .
					" <input type='button' value='$alang[common_back]' onClick=\"location.href='$theurl&op=import'\"; class='submit'>" .
					"</div></td></tr></form></table>";
				include_once template('admin/tpl/footer.htm', 1);
				exit();	
			} elseif (isset($autoimport)) {
				showmessage($alang['database_import_multivol_redirect'].$identify[4].$alang['database_import_multivol_redirect1'], "$theurl&op=importstart&do=import&datafile=$datafile_next&autoimport=yes".(isset($unzip) ? '&delunzip=yes' : ''));
			} else {
				showmessage('database_import_success', $theurl.'&op=import');
			}
		} elseif($identify[3] == 'shell') {

			list($dbhost, $dbport) = explode(':', $dbhost);

			$query = $_SGLOBAL['db']->query("SHOW VARIABLES LIKE 'basedir'");
			list(, $mysql_base) = $_SGLOBAL['db']->fetch_array($query, MYSQL_NUM);

			$mysqlbin = $mysql_base == '/' ? '' : addslashes($mysql_base).'bin/';
			$dbcharset = empty($dbcharset) ? $charset : $dbcharset;
			$datafile_root = addslashes($datafile_root);
			@shell_exec('"'.$mysqlbin.'mysql" --default-character-set='.$dbcharset.' -h '.$dbhost.($dbport ? (is_numeric($dbport) ? ' -P'.$dbport : ' -S'.$dbport.'') : '').' -u'.$dbuser.' -p'.$dbpw.' '.$dbname.' < '.$datafile_root);

			showmessage('database_import_successd');
		} else {
			showmessage('database_import_format_illegal');
		}
	}
}

function fetchtablelist($tablepre = '') {
	global $_SGLOBAL, $excepttables;
	!$tablepre && $tablepre = '*';
	$tables = $table = array();
	$query = $_SGLOBAL['db']->query("SHOW TABLE STATUS LIKE '$tablepre%'");
	while($table = $_SGLOBAL['db']->fetch_array($query)) {
		if(!in_array($table['Name'], $excepttables) && !strexists($table['Name'], 'cache')) {
			$tables[] = $table;
		}
	}
	return $tables;
}

function arraykeys2($array, $key2) {
	$return = array();
	foreach($array as $value) {
		$return[] = $value[$key2];
	}
	return $return;
}

function sqldumptable($table, $startfrom = 0, $currsize = 0) {
	global $_SGLOBAL, $sizelimit, $filesize, $startrows, $extendins, $sqlcompat, $sqlcharset, $dumpcharset, $usehex, $complate, $excepttables;

	$offset = 300;
	$tabledump = '';
	$tablefields = array();

	$query = $_SGLOBAL['db']->query('SHOW FULL COLUMNS FROM '.$table, 'SILENT');
	if(strexists($table, 'cache')) {
		return;
	} elseif (!$query && $_SGLOBAL['db']->errno() == '1146') {
		return;
	} elseif (!$query) {
		$usehex = FALSE;
	} else {
		while($result = $_SGLOBAL['db']->fetch_array($query)) {
			$tablefields[] = $result;
		}
	}
	if(!$startfrom) {
		$createtable = $_SGLOBAL['db']->query('SHOW CREATE TABLE '.$table, 'SILENT');

		if(!$_SGLOBAL['db']->errno()) {
			$tabledump = "DROP TABLE IF EXISTS $table;\n";
		} else {
			return;
		}
		
		$create = $_SGLOBAL['db']->fetch_row($createtable);
		$tabledump .= $create[1];

		if($sqlcompat == 'MYSQL41' && $_SGLOBAL['db']->version() < '4.1') {
			$tabledump = preg_replace('/TYPE=(.+)/', "ENGINE=\\1 DEFAULT CHARSET=".$dumpcharset, $tabledump);
		}
		if($_SGLOBAL['db']->version() > '4.1' && $sqlcharset) {
			$tabledump = preg_replace('/(DEFAULT)*\s*CHARSET=.+/', 'DEFAULT CHARSET='.$dumpcharset, $tabledump);
		}

		$query = $_SGLOBAL['db']->query("SHOW TABLE STATUS LIKE '$table'");
		$tablestatus = $_SGLOBAL['db']->fetch_array($query);
		$tabledump .= ($tablestatus['Auto_increment'] ? " AUTO_INCREMENT=$tablestatus[Auto_increment]" : '').";\n\n";
		if($sqlcompat == 'MYSQL40' && $_SGLOBAL['db']->version() >= '4.1' && $_SGLOBAL['db']->version() < '5.1') {
			if(!empty($tablestatus['Auto_increment'])) {
				$temppos = strpos($tabledump, ',');
				$tabledump = substr($tabledump, 0, $temppos).' auto_increment'.substr($tabledump, $temppos);
			}

			if($tablestatus['Engine'] == 'MEMORY') {
				$tabledump = str_replace('TYPE=MEMORY', 'TYPE=HEAP', $tabledump);
			}
		}
	}

	if(!in_array($table, $excepttables)) {
		$tabledumped = 0;
		$numrows = $offset;
		$firstfield = $tablefields[0];

		if($extendins == 0){
			while($currsize + strlen($tabledump) + 500 < $filesize && $numrows == $offset){
				if($firstfield['Extra'] == 'auto_increment'){
					$selectsql = 'SELECT * FROM '.$table." WHERE $firstfield[Field] > $startfrom LIMIT $offset";
				} else {
					$selectsql = 'SELECT * FROM '.$table." LIMIT $startfrom, $offset";
				}
				$tabledumped = 1;
				$query = $_SGLOBAL['db']->query($selectsql);
				$numfields = $_SGLOBAL['db']->num_fields($query);	//取得列数

				if($numrows = $_SGLOBAL['db']->num_rows($query)) {
					while($row = $_SGLOBAL['db']->fetch_row($query)) {	//以枚举形式取得行值
						$dumpsql = $comma = '';
						for($i = 0; $i < $numfields; ++$i) {
							$dumpsql .= $comma.($usehex && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text')) ? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
							$comma = ',';
						}
						if(strlen($dumpsql) + $currsize + strlen($tabledump) + 500 < $filesize ) {
							if($firstfield['Extra'] == 'auto_increment') {
								$startfrom = $row[0];
							} else {
								$startfrom ++;
							}
							$tabledump .= "INSERT INTO $table VALUES ($dumpsql);\n";
						} else {
							$complate = FALSE;
							break 2;
						}
					}
				}
			}
		} else {
			while($currsize + strlen($tabledump) + 500 < $filesize && $numrows == $offset) {
				if($firstfield['Extra'] == 'auto_increment'){
					$selectsql = 'SELECT * FROM '.$table." WHERE $firstfield[Field] > $startfrom LIMIT $offset";
				} else {
					$selectsql = 'SELECT * FROM '.$table." LIMIT $startfrom, $offset";
				}
				$tabledumped = 1;
				$query = $_SGLOBAL['db']->query($selectsql);
				$numfields = $_SGLOBAL['db']->num_fields($query);
				
				if($numrows = $_SGLOBAL['db']->num_rows($query)) {
					$extdumpsql = $extcomma = '';
					while($row = $_SGLOBAL['db']->fetch_row($query)) {
						$dumpsql = $comma = '';
						for($i = 0; $i < $numfields; ++$i) {
							$dumpsql .= $comma.($usehex && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text')) ? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
							$comma = ',';
						}
						if(strlen($extdumpsql) + $currsize + strlen($tabledump) + 500 < $filesize ) {
							if($firstfield['Extra'] == 'auto_increment') {
								$startfrom = $row[0];
							} else {
								$startfrom ++;
							}
							$extdumpsql .= "$extcomma ($dumpsql)";
							$extcomma = ',';
						} else {
							$tabledump .= "INSERT INTO $table VALUES $extdumpsql;\n";
							$complate = FALSE;
							break 2;
						}
					}
					$tabledump .= "INSERT INTO $table VALUES $extdumpsql;\n";
				}
			}
		}
		$startrows = $startfrom;
		$tabledump .= "\n";
	}
	return $tabledump;
}

function splitsql($sqldump) {
	$sql = str_replace("\r", "\n", $sqldump);
	$num = 0;
	$ret = array('0'=>'');
	
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $subquery) {
			if(!empty($subquery[0])){
				$ret[$num] .= $subquery[0] == '#' ? NULL : $subquery;
			}
		}
		$num++;
		$ret[$num] = '';
	}
	return $ret;
}

function syntablestruct($sql, $version, $dbcharset) {
	if(strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) {
		return $sql;
	}

	$sqlversion = strpos($sql, 'ENGINE=') === FALSE ? FALSE : TRUE;

	if($sqlversion === $version ) {
		return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', '/DEFAULT CHARSET=\w+/is'), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	}

	if($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);
	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}
?>
