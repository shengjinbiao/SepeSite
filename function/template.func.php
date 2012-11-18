<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: template.func.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

/**
 * ��ģ��ҳ�����滻��д�뵽cacheҳ��
 *
 * @param string $tplfile ��ģ���ļ���
 * @param string $objfile ��cache�ļ���
 * @return 
 */
function parse_template($tplfile, $objfile, $template='') {
	global $_SCONFIG;

	//read
	if(empty($template)) {
		if(!@$fp = fopen($tplfile, 'r')) {
			exit('Template file :<br>'.srealpath($tplfile).'<br>Not found or have no access!');
		}
		$template = fread($fp, filesize($tplfile));
		fclose($fp);
		$template = str_replace('<?exit?>', '', $template);
	}
	
	//parse
	$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
	$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
	
	$template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
	$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
	$template = preg_replace("/\{lang\s+(.+?)\}/ies", "languagevar('\\1')", $template);
	$template = str_replace("{LF}", "<?=\"\\n\"?>", $template);

	$template = preg_replace("/(\\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\.([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/s", "\\1['\\2']", $template);
	$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
	$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
	$template = preg_replace("/$var_regexp/es", "addquote('<?=\\1?>')", $template);
	$template = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>\?\>/es", "addquote('<?=\\1?>')", $template);

	$template = preg_replace("/[\n\r\t]*\{block\s+name=\"(.+?)\"\s+parameter=\"(.+?)\"\}[\n\r\t]*/ies", "blocktags('\\1', '\\2')", $template);
	$template = preg_replace("/[\n\r\t]*\#date\((.+?)\)\#[\n\r\t]*/ies", "striptagquotes('<?php sdate(\\1); ?>')", $template);
	$template = preg_replace("/[\n\r\t]*\#getad\((.+?)\)\#[\n\r\t]*/ies", "striptagquotes('<?php echo getad(\\1); ?>')", $template);
	$template = preg_replace("/[\n\r\t]*\#(uid|action)(.+?)\#[\n\r\t]*/ies", "striptagquotes('<?php echo geturl(\"\\1\\2\"); ?>')", $template);

	$template = ltrim($template);
	$template = "<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?>$template";
	$template = preg_replace("/[\n\r\t]*\{template\s+([a-z0-9_]+)\}[\n\r\t]*/is", "\n<?php include template('\\1'); ?>\n", $template);
	$template = preg_replace("/[\n\r\t]*\{template\s+(.+?)\}[\n\r\t]*/is", "\n<?php include template(\\1); ?>\n", $template);
	$template = preg_replace("/[\n\r\t]*\{eval\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('<?php \\1; ?>','')", $template);
	$template = preg_replace("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('\n<?php echo \\1; ?>\n','')", $template);
	$template = preg_replace("/[\n\r\t]*\{elseif\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('\n<?php } elseif(\\1) { ?>\n','')", $template);
	$template = preg_replace("/[\n\r\t]*\{else\}[\n\r\t]*/is", "\n<?php } else { ?>\n", $template);

	for($i = 0; $i < 5; $i++) {
		$template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r]*(.+?)[\n\r]*\{\/loop\}[\n\r\t]*/ies", "stripvtags('\n<?php if(is_array(\\1)) { foreach(\\1 as \\2) { ?>','\n\\3\n<?php } } ?>\n')", $template);
		$template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/loop\}[\n\r\t]*/ies", "stripvtags('\n<?php if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { ?>','\n\\4\n<?php } } ?>\n')", $template);
		$template = preg_replace("/[\n\r\t]*\{if\s+(.+?)\}[\n\r]*(.+?)[\n\r]*\{\/if\}[\n\r\t]*/ies", "stripvtags('\n<?php if(\\1) { ?>','\n\\2\n<?php } ?>\n')", $template);
	}
	$template = preg_replace("/\{$const_regexp\}/s", "<?=\\1?>", $template);
	$template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);
	
	//write
	$template = trim($template);
	if(!empty($template)) {
		$needwrite = false;
		if(@unlink($objfile)) {
			writefile($objfile.'.tmp', $template, 'text', 'w', 0);
			if(@rename($objfile.'.tmp', $objfile)) {
				$needwrite = false;
			} else {
				$needwrite = true;
			}
		} else {
			$needwrite = true;
		}
		//�ٴ�д��
		if($needwrite) writefile($objfile, $template, 'text', 'w', 0);
	}
}

/**
 * ������ʽƥ���滻
 *
 * @param string $var ��
 * @return 
 */
function addquote($var) {
	return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
}

/**
 * ������ʽƥ���滻
 *
 * @param string $expr ��
 * @return 
 */
function striptagquotes($expr) {
	$expr = preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr);
	$expr = str_replace("\\\"", "\"", preg_replace("/\[\'([a-zA-Z0-9_\-\.\x7f-\xff]+)\'\]/s", "[\\1]", $expr));
	return $expr;
}

/**
 * ?
 *
 * @param string $var ��
 * @return 
 */
function languagevar($var) {
	global $lang;
	if(isset($lang[$var])) {
		return $lang[$var];
	} else {
		return "!$var!";
	}
}

/**
 * ��ģ���еĿ��滻��BLOCK����
 *
 * @param string $cachekey ��
 * @param string $parameter ��
 * @return 
 */
function blocktags($cachekey, $parameter) {
	return striptagquotes("<?php block(\"$cachekey\", \"$parameter\"); ?>");
}

/**
 * ������ʽƥ���滻
 *
 * @param string $expr ��
 * @param string $statement ��
 * @return 
 */
function stripvtags($expr, $statement='') {
	$expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
	$statement = str_replace("\\\"", "\"", $statement);
	return $expr.$statement;
}

?>