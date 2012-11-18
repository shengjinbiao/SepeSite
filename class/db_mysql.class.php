<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: db_mysql.class.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

class dbstuff {
	var $querynum = 0;
	var $charset = '';
	var $link_identifier = null;

	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $newlink=0) {
		if($pconnect) {
			if(!$this->link_identifier = @mysql_pconnect($dbhost, $dbuser, $dbpw, $newlink)) {
				$this->halt('Can not connect to MySQL server');
			}
		} else {
			if(!$this->link_identifier = @mysql_connect($dbhost, $dbuser, $dbpw, $newlink)) {
				$this->halt('Can not connect to MySQL server');
			}
		}

		$version = $this->version();
		if($version > '4.1') {
			if(!empty($this->charset)) {
				mysql_query('SET character_set_connection='.$this->charset.', character_set_results='.$this->charset.', character_set_client=binary', $this->link_identifier);
			}
			if($version > '5.0.1') {
				mysql_query("SET sql_mode=''", $this->link_identifier);
			}
		}

		if($dbname) {
			mysql_select_db($dbname, $this->link_identifier);
		}
	}

	function select_db($dbname) {
		return mysql_select_db($dbname, $this->link_identifier);
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function query($sql, $type = '') {
		if(D_BUG) {
			$mtime = explode(' ', microtime());
			$sqlstarttime = $mtime[1] + $mtime[0];
		}
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->link_identifier)) && $type != 'SILENT') {
			$this->halt('MySQL Query Error', $sql);
		}
		if(D_BUG) {
			global $_SGLOBAL;
			if(empty($_SGLOBAL['debug_query'])) $_SGLOBAL['debug_query'] = array();
			$mtime = explode(' ', microtime());
			$sqltime = number_format(($mtime[1] + $mtime[0] - $sqlstarttime), 6)*1000;
			$explain = array();
			$info = mysql_info();
			if(strtolower(substr($sql,0,7)) == 'select ') {
				$explain = mysql_fetch_assoc(mysql_query('EXPLAIN '.$sql, $this->link_identifier));
			}
			$_SGLOBAL['debug_query'][] = array('sql'=>$sql, 'time'=>$sqltime, 'info'=>$info, 'explain'=>$explain);
		}
		$this->querynum++;
		return $query;
	}

	function affected_rows() {
		return mysql_affected_rows($this->link_identifier);
	}

	function error() {
		return (($this->link_identifier) ? mysql_error($this->link_identifier) : mysql_error());
	}

	function errno() {
		return intval(($this->link_identifier) ? mysql_errno($this->link_identifier) : mysql_errno());
	}

	function result($query, $row) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function num_fields($query) {
		return mysql_num_fields($query);
	}

	function free_result($query) {
		return mysql_free_result($query);
	}

	function insert_id() {
		return ($id = mysql_insert_id($this->link_identifier)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}

	function version() {
		return mysql_get_server_info($this->link_identifier);
	}

	function close() {
		return mysql_close($this->link_identifier);
	}

	function halt($message = '', $sql = '') {
		global $_SGLOBAL, $_SCONFIG, $mailsend, $adminemail, $sendmail_silent, $mailcfg;
		$adminemail = $_SCONFIG['adminemail'];
		include_once S_ROOT.'./include/db_mysql_error.inc.php';
	}
}

?>