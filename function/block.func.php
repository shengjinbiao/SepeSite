<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: block.func.php 13493 2009-11-11 06:15:33Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

//范围查询条件SQL处理
function getscopequery($pre, $var, $paramarr, $isdate=0) {
	global $_SGLOBAL;

	$wheresql = '';
	if(!empty($pre)) $pre = $pre.'.';
	if(!empty($paramarr[$var])) {
		if($isdate) {
			$paramarr[$var] = intval($paramarr[$var]);
			if($paramarr[$var]) $wheresql = $pre.$var.'>='.($_SGLOBAL['timestamp']-$paramarr[$var]);
		} else {
			$tarr = explode(',', $paramarr[$var]);
			if(count($tarr)==2) {
				$tarr[0] = intval(trim($tarr[0]));
				$tarr[1] = intval(trim($tarr[1]));
				if($tarr[1] > $tarr[0]) {
					$wheresql = '('.$pre.$var.'>='.$tarr[0].' AND '.$pre.$var.'<='.$tarr[1].')';
				}
			}
		}
	}
	return $wheresql;
}

//处理上传图片连接
function mkpicurl($pic, $thumb=1) {
	global  $_SC;
	$url = '';
	if(isset($pic['picflag'])) {
		if($pic['pic'] && !preg_match('#:\/\/#is',$pic['pic'])) {
			if($pic['picflag'] == 1) {
				$url = empty($_SC['attachurl']) ? $_SC['uchurl'].'/attachment/'.$pic['pic'] : $_SC['attachurl'].'/'.$pic['pic'];
			} elseif ($pic['picflag'] == 2) {
				$url = $_SC['uchftpurl'].'/'.$pic['pic'];
			} else {
				$url = $_SC['uchurl'].'/'.$pic['pic'];
			}
		}elseif(preg_match('#:\/\/#is', $pic['pic'])) {
			$url = $pic['pic'];
		}
	} else {
		$url = $_SC['uchurl'].'/'.$pic['pic'];
	}
	return $url;
}

function block_spacenews($paramarr) {
	global $_SGLOBAL, $_SGET;
		
	$_SGLOBAL['attachsql'] = 'a.aid AS a_aid, a.type AS a_type, a.itemid AS a_itemid, a.uid AS a_uid, a.dateline AS a_dateline, a.filename AS a_filename, a.subject AS a_subject, a.attachtype AS a_attachtype, a.isimage AS a_isimage, a.size AS a_size, a.filepath AS a_filepath, a.thumbpath AS a_thumbpath, a.downloads AS a_downloads';
	
	if(empty($paramarr['sql'])) {
		$sql = array();
		$sql['select'] = 'SELECT i.*';
		$sql['from'] = 'FROM '.tname('spaceitems').' i';
		$sql['join'] = '';
	
		$wherearr = array();
		$showpic = 0;
		
		//where
		if(!empty($paramarr['itemid'])) {
			$paramarr['itemid'] = getdotstring($paramarr['itemid'], 'int');
			if($paramarr['itemid']) $wherearr[] = 'i.itemid IN ('.$paramarr['itemid'].')';
		} else {
			//作者
			if(!empty($paramarr['uid'])) {
				$paramarr['uid'] = getdotstring($paramarr['uid'], 'int');
				if($paramarr['uid']) $wherearr[] = 'i.uid IN ('.$paramarr['uid'].')';
			}
	
			//分类
			if(!empty($paramarr['catid'])) {
				$paramarr['catid'] = getdotstring($paramarr['catid'], 'int');
				if($paramarr['catid']) $wherearr[] = 'i.catid IN ('.$paramarr['catid'].')';
			}
	
			//限制
			if(empty($paramarr['catid'])) {
				if(!empty($paramarr['type'])) {
					$wherearr[] = 'i.type=\''.$paramarr['type'].'\'';
				}
			}

			//站点审核
			if(!empty($paramarr['grade'])) {
				$paramarr['grade'] = getdotstring($paramarr['grade'], 'int');
				if(!empty($paramarr['grade'])) $wherearr[] = 'i.grade IN ('.$paramarr['grade'].')';
			} else {
				if(empty($paramarr['uid'])) {
					if(!empty($_SCONFIG['needcheck'])) {
						$wherearr[] = 'i.grade>0';
					}
				}
			}
		
			if(!empty($paramarr['digest'])) {
				$paramarr['digest'] = getdotstring($paramarr['digest'], 'int');
				if($paramarr['digest']) $wherearr[] = 'i.digest IN ('.$paramarr['digest'].')';
			}
	
			if(!empty($paramarr['top'])) {
				$paramarr['top'] = getdotstring($paramarr['top'], 'int');
				if($paramarr['top']) $wherearr[] = 'i.top IN ('.$paramarr['top'].')';
			}
			
			if(!empty($paramarr['dateline'])) {
				$paramarr['dateline'] = intval($paramarr['dateline']);
				if($paramarr['dateline']) $wherearr[] = 'i.dateline >= '.($_SGLOBAL['timestamp']-$paramarr['dateline']);
			}
	
			if(!empty($paramarr['lastpost'])) {
				$paramarr['lastpost'] = intval($paramarr['lastpost']);
				if($paramarr['lastpost']) $wherearr[] = 'i.lastpost >= '.($_SGLOBAL['timestamp']-$paramarr['lastpost']);
			}
	
			$scopequery = getscopequery('i', 'viewnum', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
	
			$scopequery = getscopequery('i', 'replynum', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
		
			@include_once S_ROOT.'/data/system/click.cache.php';
			$clickgroupids = array_keys($_SGLOBAL['clickgroup']['spaceitems']);
	
			foreach ($_SGLOBAL['click'] as $key => $kvalue) {
				if(in_array($key, $clickgroupids)) {
					foreach ($kvalue as $value) {
						if(!is_int($value['name'])){
							$scopequery = getscopequery('i', 'click_'.$value['clickid'], $paramarr);
							if(!empty($scopequery)) $wherearr[] = $scopequery;
						}
					}	
				}
			}
			
			$scopequery = getscopequery('i', 'hot', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
			
			$paramarr['haveattach'] = intval($paramarr['haveattach']);
			if(!empty($paramarr['haveattach']) && $paramarr['haveattach'] == 1) {
				$wherearr[] = 'i.haveattach = 1';
			}
			//兼容早期的图文$paramarr['showattach']
			if(!empty($paramarr['showattach']) || (!empty($paramarr['haveattach']) && $paramarr['haveattach'] == 2)) {
				$showpic = 1;
				$wherearr[] = 'i.picid != 0';
			}
		}
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
		
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;
	
			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;
	
			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ', $sql);
		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spaceitems').' i '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	
	} else {
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}
	
	if($listcount) {
		//预处理
		if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;
		if(empty($paramarr['messagedot'])) $paramarr['messagedot'] = 0;
		
		if(!empty($paramarr['showcategory'])) {
			include_once(S_ROOT.'./data/system/category.cache.php');
		}
		$query = $_SGLOBAL['db']->query($sqlstring);
		$allitemids = $aids = array();
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			//生成HTML时
			if(defined('CREATEHTML')) {
				$_SGLOBAL['item_cache']['viewnews_'.$value['itemid']] = array('catid' => $value['catid'], 'dateline' => $value['dateline']);
			}
			
			//处理
			$value['subjectall'] = $value['subject'];
			if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
				$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
			}
			//处理标题样式
			if(!empty($value['styletitle'])) {
				$value['subject'] = '<span style=\''.mktitlestyle($value['styletitle']).'\'>'.$value['subject'].'</span>';
			}

			//链接
			$value['url'] = geturl('action/viewnews/itemid/'.$value['itemid']);
			
			//附件
			if($value['picid'] && $value['hash']) {
				$aids[] = $value['picid'];
			}
			$allitemids[] = $value['itemid'];
	
			//相关tag
			if(!empty($value['relativetags'])) $value['relativetags'] = $value['tags'] = unserialize($value['relativetags']);
			
			//分类名
			if(!empty($_SGLOBAL['category'][$value['catid']])) $value['catname'] = $_SGLOBAL['category'][$value['catid']];
			
			//附件
			if(!empty($value['picid']) && ($value['type'] == 'blog' || $value['type'] == 'news')) $value['subject'] = $value['subject'].$lang['block_image'];
			
			$theblockarr[$value['itemid']] = $value;
		}
		
		//分页内容处理/取第一页
		if(!empty($paramarr['showdetail'])) {
			if(!empty($allitemids)) {
				$theitemarr = array();
				$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spacenews').' WHERE itemid IN (\''.implode('\',\'', $allitemids).'\') ORDER BY nid');
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					if(empty($theitemarr[$value['itemid']])) {
						if(!empty($value['message']) && !empty($paramarr['messagelen'])) {
							$value['message'] = strip_tags(trim($value['message']));
							$value['message'] = trim(cutstr($value['message'], $paramarr['messagelen'], $paramarr['messagedot']));
						}
						$theitemarr[$value['itemid']] = 1;
						$theblockarr[$value['itemid']] = array_merge($theblockarr[$value['itemid']], $value);
					}
				}
			}
		}
	
		if(!empty($showpic)) {
			$attacharr = array();
			if(!empty($aids)) {
				$query = $_SGLOBAL['db']->query('SELECT '.$_SGLOBAL['attachsql'].' FROM '.tname('attachments').' a WHERE a.aid IN (\''.implode('\',\'', $aids).'\') ORDER BY a.dateline');
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					//处理
					if(!empty($attacharr[$value['a_itemid']])) continue;
					
					$value['a_subjectall'] = $value['a_subject'];
					if(!empty($value['a_subject']) && !empty($paramarr['subjectlen'])) {
						$value['a_subject'] = cutstr($value['a_subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
					}
					//附件处理
					if(!empty($value['a_thumbpath'])) $value['a_thumbpath'] = A_URL.'/'.$value['a_thumbpath'];
					if(!empty($value['a_filepath'])) $value['a_filepath'] = A_URL.'/'.$value['a_filepath'];
					if(empty($value['a_thumbpath'])) {
						if(empty($value['a_filepath'])) {
							$value['a_thumbpath'] = S_URL.'/images/base/nopic.gif';
						} else {
							$value['a_thumbpath'] = $value['a_filepath'];
						}
					}
					if(empty($value['a_filepath'])) $value['a_filepath'] = $value['a_thumbpath'];
					$attacharr[$value['a_itemid']] = $value;
					$theblockarr[$value['a_itemid']] = array_merge($theblockarr[$value['a_itemid']], $value);
				}
			}
		}
	}
	return $theblockarr;
}

function block_spacetag($paramarr) {
	global $_SGLOBAL, $_SGET;
	if(empty($paramarr['sql'])) {
		
		$wherearr = array();
		if(!empty($paramarr['type'])) {
			$paramarr['type'] = getdotstring($paramarr['type'], 'char', false, $_SGLOBAL['type']);
			if($paramarr['type']) $wherearr[] = 'i.type IN ('.$paramarr['type'].')';
		}

		if(!empty($paramarr['haveattach'])) {
			$wherearr[] = 'i.haveattach = 1';
		}

		if(!empty($paramarr['uid'])) {
			$paramarr['uid'] = getdotstring($paramarr['uid'], 'int');
			if($paramarr['uid']) $wherearr[] = 'i.uid IN ('.$paramarr['uid'].')';
		}

		if(!empty($paramarr['digest'])) {
			$paramarr['digest'] = getdotstring($paramarr['digest'], 'int');
			if($paramarr['digest']) $wherearr[] = 'i.digest IN ('.$paramarr['digest'].')';
		}

		if(!empty($paramarr['lastpost'])) {
			$paramarr['lastpost'] = intval($paramarr['lastpost']);
			if($paramarr['lastpost']) $wherearr[] = 'i.lastpost >= '.($_SGLOBAL['timestamp']-$paramarr['lastpost']);
		}

		$scopequery = getscopequery('i', 'viewnum', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$scopequery = getscopequery('i', 'replynum', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$innersql = empty($wherearr) ? '1' : implode(' AND ', $wherearr);
		
		$sql = array();
		$sql['select'] = 'SELECT st.tagid, i.*';
		$sql['from'] = 'FROM '.tname('spacetags').' st INNER JOIN '.tname('spaceitems').' i ON i.itemid=st.itemid AND '.$innersql;
		$sql['join'] = '';

		
		if(!empty($paramarr['showdetail'])) {
			if(str_replace("'", '', $paramarr['type'])) {
				$sql['select'] .= ', ii.*';
				$sql['join'] .= ' LEFT JOIN '.tname('spacenews').' ii ON ii.itemid=st.itemid';
			}
		}
		
		//where
		$wherearr = array();
		$paramarr['tagid'] = getdotstring($paramarr['tagid'], 'int');
		if($paramarr['tagid']) {
			$wherearr[] = 'st.tagid IN ('.$paramarr['tagid'].')';
		}
		
		if(!empty($paramarr['dateline'])) {
			$paramarr['dateline'] = intval($paramarr['dateline']);
			if($paramarr['dateline']) $wherearr[] = 'st.dateline >= '.($_SGLOBAL['timestamp']-$paramarr['dateline']);
		}
		
		$sql['where'] = '';
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
		
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ', $sql);

		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) '.$sql['from'].' '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}

	} else {
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//查询数据
	if($listcount) {
		//预处理
		if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;
		if(empty($paramarr['messagedot'])) $paramarr['messagedot'] = 0;
		
		if(!empty($paramarr['showcategory'])) {
			include_once(S_ROOT.'./data/system/category.cache.php');
		}
		
		//查询
		$query = $_SGLOBAL['db']->query($sqlstring);
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			
			//处理
			$value['subjectall'] = $value['subject'];
			if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
				$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
			}
		
			if(!empty($value['message']) && !empty($paramarr['messagelen'])) {
				$value['message'] = trim(strip_tags($value['message']));
				$value['message'] = trim(cutstr($value['message'], $paramarr['messagelen'], $paramarr['messagedot']));
			}
		
			//类型
			if(!empty($value['type'])) $value['typename'] = $lang[$value['type']];
			
			//链接
			$value['url'] = geturl('action/viewnews/itemid/'.$value['itemid']);

			//相关tag
			if(!empty($value['relativetags'])) $value['relativetags'] = $value['tags'] = unserialize($value['relativetags']);
			
			//分类名
			if(!empty($_SGLOBAL['category'][$value['catid']])) $value['catname'] = $_SGLOBAL['category'][$value['catid']];
			
			//图片处理
			if(empty($value['thumb'])) {
				$value['thumb'] = S_URL.'/images/base/nopic.gif';
			} else {
				$value['thumb'] = A_URL.'/'.$value['thumb'];
			}
			if(empty($value['image'])) {
				$value['image'] = S_URL.'/images/base/nopic.gif';
			} else {
				$value['image'] = A_URL.'/'.$value['image'];
			}
			
			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function block_announcement($paramarr) {
	global $_SGLOBAL, $_SGET;
	
	if(empty($paramarr['sql'])) {
		//set sql var
		$sql = array();
	
		//select
		$sql['select'] = 'SELECT id, author, subject, starttime, endtime';
		if(!empty($paramarr['showdetail'])) {
			$sql['select'] .= ', message';
		}
	
		//from
		$sql['from'] = 'FROM '.tname('announcements');
	
		//where
		$wherearr = array();
		if(!empty($paramarr['id'])) {
			$paramarr['id'] = getdotstring($paramarr['id'], 'int');
			if($paramarr['id']) $wherearr[] = 'id IN ('.$paramarr['id'].')';
		} else {
			$wherearr[] = 'starttime <= '.$_SGLOBAL['timestamp'].' AND (endtime =0 OR endtime >= '.$_SGLOBAL['timestamp'].')';
			if(!empty($paramarr['author'])) {
				$paramarr['author'] = getdotstring($paramarr['author'], 'char');
				if($paramarr['author']) $wherearr[] = 'author IN ('.$paramarr['author'].')';
			}
		}
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
	
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
	
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;
	
			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;
	
			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
	
		//query
		$sqlstring = implode(' ',$sql);
		
		//multi
		$listcount = 1;//默认读取
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('announcements').' '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	
	} else {
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}
	
	
	//查询数据
	if($listcount) {
		//预处理
		if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;
		if(empty($paramarr['messagedot'])) $paramarr['messagedot'] = 0;
		
		//查询
		$query = $_SGLOBAL['db']->query($sqlstring);
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			
			//标题处理
			$value['subjectall'] = $value['subject'];
			if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
				$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
			}
			
			//内容处理
			if(!empty($value['message']) && !empty($paramarr['messagelen'])) {
				$value['message'] = trim(strip_tags($value['message']));
				$value['message'] = trim(cutstr($value['message'], $paramarr['messagelen'], $paramarr['messagedot']));
			}
			
			//链接
			$value['url'] = geturl('action/announcement/id/'.$value['id']);
			
			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function block_bbsannouncement($paramarr) {
	global $_SGLOBAL, $_SGET;
	dbconnect(1);
	if(empty($paramarr['sql'])) {
		//set sql var
		$sql = array();
		
		//select
		$sql['select'] = 'SELECT id, author, subject, starttime, endtime';
		if(!empty($paramarr['showdetail'])) {
			$sql['select'] .= ', message';
		}
		
		//from
		$sql['from'] = 'FROM '.tname('announcements', 1);
		
		//where
		$wherearr = array();
		if(!empty($paramarr['aid'])) {
			$paramarr['aid'] = getdotstring($paramarr['aid'], 'int');
			if($paramarr['aid']) $wherearr[] = 'id IN ('.$paramarr['aid'].')';
		} else {
			$wherearr[] = 'starttime <= '.$_SGLOBAL['timestamp'].' AND (endtime =0 OR endtime >= '.$_SGLOBAL['timestamp'].')';
			if(!empty($paramarr['author'])) {
				$paramarr['author'] = getdotstring($paramarr['author'], 'char');
				if($paramarr['author']) $wherearr[] = 'author IN ('.$paramarr['author'].')';
			}
		}
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);

		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}

		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}

		//query
		$sqlstring = implode(' ',$sql);
		
		
		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query('SELECT COUNT(*) FROM '.tname('announcements', 1).' '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}

	} else {
		
		$bbsdb = $_SGLOBAL['db_bbs'];
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr, $bbsdb);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//查询数据
	if($listcount) {
		//变量处理
		if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;
		if(empty($paramarr['messagedot'])) $paramarr['messagedot'] = 0;
		
		//查询
		$query = $_SGLOBAL['db_bbs']->query($sqlstring);
		while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
			
			//标题处理
			$value['subjectall'] = $value['subject'];
			if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
				$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
			}
			
			//内容处理
			if(!empty($value['message']) && !empty($paramarr['messagelen'])) {
				$value['message'] = trim(strip_tags($value['message']));
				$value['message'] = trim(cutstr($value['message'], $paramarr['messagelen'], $paramarr['messagedot']));
			}
			

			//链接
			$value['url'] = B_URL.'/announcement.php?id='.$value['id'];
			
			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function block_bbsattachment($paramarr) {

	global $_SGLOBAL, $_SGET, $_SCONFIG;
	dbconnect(1);
	
	@include_once(S_ROOT.'./data/system/bbsforums.cache.php');
	$fidarr = array();
	foreach($_SGLOBAL['bbsforumarr'] as $value) {
		if(!empty($value['allowshare'])) $fidarr[] = $value['fid'];
	}
	$fids = simplode($fidarr);
	
	if(empty($paramarr['sql'])) {
		
		$wherearr = array();
		$wherearr[] = 't.tid = a.tid';
		if(empty($paramarr['aid'])) {
			if(B_VER == '5') {
				$wherearr[] = 't.supe_pushstatus > 0';
			}
			if(!empty($paramarr['t_fid'])) {
				$paramarr['t_fid'] = getdotstring($paramarr['t_fid'], 'int');
				if($paramarr['t_fid']) $wherearr[] = 't.fid IN ('.$paramarr['t_fid'].')';
			}
			
			$wherearr[] = 't.fid IN ('.$fids.')';
			
			if(!empty($paramarr['t_typeid'])) {
				$paramarr['t_typeid'] = getdotstring($paramarr['t_typeid'], 'int');
				if($paramarr['t_typeid']) $wherearr[] = 't.typeid IN ('.$paramarr['t_typeid'].')';
			}
		
			if(!empty($paramarr['t_authorid'])) {
				$paramarr['t_authorid'] = getdotstring($paramarr['t_authorid'], 'int');
				if($paramarr['t_authorid']) $wherearr[] = 't.authorid IN ('.$paramarr['t_authorid'].')';
			}
		
			if(!empty($paramarr['t_digest'])) {
				$paramarr['t_digest'] = getdotstring($paramarr['t_digest'], 'int');
				if($paramarr['t_digest']) $wherearr[] = 't.digest IN ('.$paramarr['t_digest'].')';
			}
			
			if(!empty($paramarr['t_dateline'])) {
				$paramarr['t_dateline'] = intval($paramarr['t_dateline']);
				if($paramarr['t_dateline']) $wherearr[] = 't.dateline >= '.($_SGLOBAL['timestamp']-$paramarr['t_dateline']);
			}
			if(!empty($paramarr['t_lastpost'])) {
				$paramarr['t_lastpost'] = intval($paramarr['t_lastpost']);
				if($paramarr['t_lastpost']) $wherearr[] = 't.lastpost >= '.($_SGLOBAL['timestamp']-$paramarr['t_lastpost']);
			}
		
			if(!empty($paramarr['t_readperm'])) $paramarr['readperm'] = $paramarr['t_readperm'];
			if(!empty($paramarr['t_price'])) $paramarr['price'] = $paramarr['t_price'];
			if(!empty($paramarr['t_views'])) $paramarr['views'] = $paramarr['t_views'];
			if(!empty($paramarr['t_replies'])) $paramarr['replies'] = $paramarr['t_replies'];
			if(!empty($paramarr['t_rate'])) $paramarr['rate'] = $paramarr['t_rate'];
			
			$scopequery = getscopequery('t', 'dateline', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

			$scopequery = getscopequery('t', 'lastpost', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

			$scopequery = getscopequery('t', 'readperm', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
		
			$scopequery = getscopequery('t', 'price', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
		
			$scopequery = getscopequery('t', 'views', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
		
			$scopequery = getscopequery('t', 'replies', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
		
			$scopequery = getscopequery('t', 'rate', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
		
			if(!empty($paramarr['t_blog'])) {
				$wherearr[] = 't.blog = 1';
			}
			
			if(!empty($paramarr['t_closed'])) {
				$wherearr[] = 't.closed = 1';
			}
			$wherearr[] = 't.displayorder >= 0';
		}
		$innersql = implode(' AND ', $wherearr);

		//set sql var
		$sql = array();
		
		//select
		$sql['select'] = 'SELECT t.*, a.*, a.dateline AS a_dateline, a.readperm AS a_readperm, a.attachment AS a_attachment';
		
		//from
		$sql['from'] = 'FROM '.tname('attachments', 1).' a';

		//join
		$sql['join'] = 'INNER JOIN '.tname('threads', 1).' t ON '.$innersql;

		//where
		$wherearr = array();
		if(!empty($paramarr['aid'])) {
			$paramarr['aid'] = getdotstring($paramarr['aid'], 'int');
			if($paramarr['aid']) $wherearr[] = 'a.aid IN ('.$paramarr['aid'].')';
		} else {
			if(!empty($paramarr['filetype'])) {
				$paramarr['filetype'] = getdotstring($paramarr['filetype'], 'char', false, array('file', 'image'));
				
				if(B_VER == '5') {
					if($paramarr['filetype'] == '\'image\'') {
						$wherearr[] = 'a.isimage = 1';
					} elseif($paramarr['filetype'] == '\'file\'') {
						$wherearr[] = 'a.isimage = 0';
					}
				} elseif(B_VER == '4') {
					$imagestr = '\'image/bmp\', \'image/gif\', \'image/jpeg\', \'image/pjpeg\', \'image/png\'';
					if($paramarr['filetype'] == '\'image\'') {
						$wherearr[] = 'a.filetype IN ('.$imagestr.')';
					} elseif($paramarr['filetype'] == '\'file\'') {
						$wherearr[] = 'NOT(a.filetype IN ('.$imagestr.'))';
					}
				}
			}
			$scopequery = getscopequery('a', 'dateline', $paramarr, 1);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

			$scopequery = getscopequery('a', 'readperm', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

			$scopequery = getscopequery('a', 'downloads', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

		}
		$sql['where'] = '';
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
		
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ',$sql);
		
		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query('SELECT COUNT(*) FROM '.tname('attachments', 1).' a INNER JOIN '.tname('threads', 1).' t ON '.$innersql.' '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	} else {
		
		$bbsdb = $_SGLOBAL['db_bbs'];
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr, $bbsdb);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//查询数据
	if($listcount) {
		
		//变量处理
		if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;
		
		//查询
		$query = $_SGLOBAL['db_bbs']->query($sqlstring);
		while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {

			//帖子标题处理
			$value['subjectall'] = $value['subject'];
			if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
				$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
			}
			
			//附件路径
			$value['a_attachment'] = $value['attachment'] = $value['a_thumbfile'] = getbbsattachment($value);

			//链接
			if($_SCONFIG['bbsurltype'] == 'bbs' || (!empty($paramarr['bbsurltype']) && $paramarr['bbsurltype'] == 'bbs')) {
				$value['url'] = B_URL.'/viewthread.php?tid='.$value['tid'];
			} else {
				$value['url'] = geturl('action/viewthread/tid/'.$value['tid']);
			}
			

			$theblockarr[] = $value;
		}
	}

	return $theblockarr;
}

function block_bbsforum($paramarr) {

	global $_SGLOBAL, $_SGET, $_SC, $_SCONFIG;
	dbconnect(1);
	
	@include_once(S_ROOT.'./data/system/bbsforums.cache.php');
	$fidarr = array();
	foreach($_SGLOBAL['bbsforumarr'] as $value) {
		if(!empty($value['allowshare'])) $fidarr[] = $value['fid'];
	}
	$fids = simplode($fidarr);
	
	if(empty($paramarr['sql'])) {
		
		//set sql var
		$sql = array();
		
		//select
		$sql['select'] = 'SELECT f.*';
		
		//from
		$sql['from'] = 'FROM '.tname('forums', 1).' f';
		
		//join
		if(!empty($paramarr['showdetail'])) {
			$sql['select'] = 'SELECT ff.*, f.*';
			$sql['join'] = 'LEFT JOIN '.tname('forumfields', 1).' ff ON ff.fid = f.fid';
		}

		//where
		$wherearr = array();

		$wherearr[] = (empty($paramarr['status'])?'f.status>0':'f.status='.$paramarr['status']);

		if(!empty($paramarr['fid'])) {
			$paramarr['fid'] = getdotstring($paramarr['fid'], 'int');
			if($paramarr['fid']) $wherearr[] = 'f.fid IN ('.$paramarr['fid'].')';
		}
		
		$wherearr[] = 'f.fid IN ('.$fids.')';
		
		if(!empty($paramarr['fup'])) {
			$paramarr['fup'] = getdotstring($paramarr['fup'], 'int', true);
			if($paramarr['fup']) $wherearr[] = 'f.fup IN ('.$paramarr['fup'].')';
		}
		
		if(!empty($paramarr['type'])) {
			$paramarr['type'] = getdotstring($paramarr['type'], 'char', false, array('group', 'forum', 'sub'));
			if($paramarr['type']) $wherearr[] = 'f.type IN ('.$paramarr['type'].')';
		}

		$scopequery = getscopequery('f', 'threads', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		
		$scopequery = getscopequery('f', 'posts', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		
		$scopequery = getscopequery('f', 'todayposts', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		if(!empty($paramarr['allowblog'])) {
			if($_SC['bbsver'] == '5' || $_SC['bbsver'] == '6') {
				$wherearr[] = 'f.allowshare = 1';
			} elseif($_SC['bbsver'] == '4') {
				$wherearr[] = 'f.allowblog = 1';
			}
		}
		
		if(!empty($paramarr['allowtrade'])) {
			if(B_VER == '5') {
				$wherearr[] = 'f.allowpostspecial = 1';
			} elseif(B_VER == '4') {
				$wherearr[] = 'f.allowtrade = 1';
			}
		}
		
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
		
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ',$sql);

		
		
		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query('SELECT COUNT(*) FROM '.tname('forums', 1).' f '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	} else {
		
		$bbsdb = $_SGLOBAL['db_bbs'];
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr, $bbsdb);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//查询数据
	if($listcount) {

		//查询
		$query = $_SGLOBAL['db_bbs']->query($sqlstring);
		while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
			
			//版块名
			if(!empty($_SGLOBAL['bbsforumarr']) && !empty($_SGLOBAL['bbsforumarr'][$value['fid']]['name'])) {
				$value['name'] = $_SGLOBAL['bbsforumarr'][$value['fid']]['name'];
			}

			//链接
			if($_SCONFIG['bbsurltype'] == 'bbs' || (!empty($paramarr['bbsurltype']) && $paramarr['bbsurltype'] == 'bbs')) {
				$value['url'] = B_URL.'/forumdisplay.php?fid='.$value['fid'];
			} else {
				$value['url'] = geturl('action/forumdisplay/fid/'.$value['fid']);
			}

			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function block_bbslink($paramarr) {
	global $_SGLOBAL, $_SGET;
	dbconnect(1);
	if(empty($paramarr['sql'])) {
		
		//set sql var
		$sql = array();
		
		//select
		$sql['select'] = 'SELECT *';
		
		//from
		$sql['from'] = 'FROM '.tname('forumlinks', 1);
		
		//where
		$wherearr = array();
		if(!empty($paramarr['id'])) {
			$paramarr['id'] = getdotstring($paramarr['id'], 'int');
			if($paramarr['id']) $wherearr[] = 'id IN ('.$paramarr['id'].')';
		} else {
			if(!empty($paramarr['note'])) {
				$wherearr[] = 'description != \'\'';
			}
			if(!empty($paramarr['logo'])) {
				$wherearr[] = 'logo != \'\'';
			}
		}
		$sql['where'] = '';
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);

		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}

		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ',$sql);

		
		
		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query('SELECT COUNT(*) FROM '.tname('forumlinks', 1).' '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}

	} else {
		
		$bbsdb = $_SGLOBAL['db_bbs'];
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr, $bbsdb);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//查询数据
	if($listcount) {
		//预处理
		if(empty($paramarr['namedot'])) $paramarr['namedot'] = 0;
		if(empty($paramarr['notedot'])) $paramarr['notedot'] = 0;
		
		//查询
		$query = $_SGLOBAL['db_bbs']->query($sqlstring);
		while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
			
			//链接名
			$value['nameall'] = $value['name'];
			if(!empty($value['name']) && !empty($paramarr['namelen'])) {
				$value['name'] = cutstr($value['name'], $paramarr['namelen'], $paramarr['namedot']);
			}
			
			//注释
			$value['noteall'] = $value['description'];
			if(!empty($value['description']) && !empty($paramarr['notelen'])) {
				$value['note'] = $value['description'] = cutstr($value['description'], $paramarr['notelen'], $paramarr['notedot']);
			}		

			//logo
			if(!empty($value['logo'])) {
				$check_url = parse_url($value['logo']);
				if(empty($check_url['scheme'])) {
					$value['logo'] = B_URL.'/'.$value['logo'];
				}
			}

			$theblockarr[] = $value;
		}
	}

	return $theblockarr;
}

function block_bbsmember($paramarr) {

	global $_SGLOBAL, $_SGET;
	dbconnect(1);
	if(empty($paramarr['sql'])) {
		
		//set sql var
		$sql = array();
		
		//select
		$sql['select'] = 'SELECT m.*';
		
		//from
		$sql['from'] = 'FROM '.tname('members', 1).' m';
		
		//join
		if(!empty($paramarr['showdetail'])) {
			$sql['select'] = 'SELECT mm.*, m.*';
			$sql['join'] = 'LEFT JOIN '.tname('memberfields', 1).' mm ON mm.uid = m.uid';
		}

		//where
		$wherearr = array();
		if(!empty($paramarr['uid'])) {
			$paramarr['uid'] = getdotstring($paramarr['uid'], 'int');
			if($paramarr['uid']) $wherearr[] = 'm.uid IN ('.$paramarr['uid'].')';
		} else {
			if(!empty($paramarr['adminid'])) {
				$paramarr['adminid'] = getdotstring($paramarr['adminid'], 'int');
				if($paramarr['adminid']) $wherearr[] = 'm.adminid IN ('.$paramarr['adminid'].')';
			}
			if(!empty($paramarr['groupid'])) {
				$paramarr['groupid'] = getdotstring($paramarr['groupid'], 'int');
				if($paramarr['groupid']) $wherearr[] = 'm.groupid IN ('.$paramarr['groupid'].')';
			}
			if(!empty($paramarr['regdate'])) {
				$paramarr['regdate'] = intval($paramarr['regdate']);
				if($paramarr['regdate']) $wherearr[] = 'm.regdate >= '.($_SGLOBAL['timestamp']-$paramarr['regdate']);
			}
			if(!empty($paramarr['lastvisit'])) {
				$paramarr['lastvisit'] = intval($paramarr['lastvisit']);
				if($paramarr['lastvisit']) $wherearr[] = 'm.lastvisit >= '.($_SGLOBAL['timestamp']-$paramarr['lastvisit']);
			}
			if(!empty($paramarr['lastpost'])) {
				$paramarr['lastpost'] = intval($paramarr['lastpost']);
				if($paramarr['lastpost']) $wherearr[] = 'm.lastpost >= '.($_SGLOBAL['timestamp']-$paramarr['lastpost']);
			}
			$scopequery = getscopequery('m', 'posts', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
			$scopequery = getscopequery('m', 'digestposts', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
			$scopequery = getscopequery('m', 'oltime', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
			$scopequery = getscopequery('m', 'pageviews', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
			$scopequery = getscopequery('m', 'credits', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
			$scopequery = getscopequery('m', 'credits1', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
			$scopequery = getscopequery('m', 'credits2', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
			$scopequery = getscopequery('m', 'credits3', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
			$scopequery = getscopequery('m', 'credits4', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
			$scopequery = getscopequery('m', 'credits5', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
			$scopequery = getscopequery('m', 'credits6', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
			$scopequery = getscopequery('m', 'credits7', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
			$scopequery = getscopequery('m', 'credits8', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
		}
		$sql['where'] = '';
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}

		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}

		//query
		$sqlstring = implode(' ',$sql);

		dbconnect(1);
		
		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query('SELECT COUNT(*) FROM '.tname('members', 1).' m '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}

	} else {
		dbconnect(1);
		$bbsdb = $_SGLOBAL['db_bbs'];
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr, $bbsdb);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//查询数据
	if($listcount) {
		//预处理
		if(empty($paramarr['signaturedot'])) $paramarr['signaturedot'] = 0;
		
		//查询
		$query = $_SGLOBAL['db_bbs']->query($sqlstring);
		while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
			
			//签名档
			if(!empty($member['signature']) && !empty($paramarr['signaturelen'])) {
				$value['signature'] = cutstr($value['signature'], $paramarr['signaturelen'], $paramarr['signaturedot']);
			}
			
			//头像
			if(!empty($value['avatar'])) {
				$check_url = parse_url($value['avatar']);
				if(empty($check_url['scheme'])) {
					$value['avatar'] = B_URL.'/'.$value['avatar'];
				}
			} else {
				$value['avatar'] = S_URL.'/images/base/space_noface.gif';
			}
			
			//链接
			$value['url'] = S_URL.'/space.php?uid='.$value['uid'];
			
			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function block_bbspost($paramarr) {
	global $_SGLOBAL, $_SGET;

	dbconnect(1);

	@include_once(S_ROOT.'./data/system/bbsforums.cache.php');
	$fidarr = array();
	foreach($_SGLOBAL['bbsforumarr'] as $value) {
		if(!empty($value['allowshare'])) $fidarr[] = $value['fid'];
	}
	$fids = simplode($fidarr);
	
	if(empty($paramarr['sql'])) {
		
		//set sql var
		$sql = array();
		
		//select
		$sql['select'] = 'SELECT *';
		
		//from
		$sql['from'] = 'FROM '.tname('posts', 1);
		
		//where
		$wherearr = array();
		if(!empty($paramarr['pid'])) {
			$paramarr['pid'] = getdotstring($paramarr['pid'], 'int');
			$wherearr[] = 'pid IN ('.$paramarr['pid'].')';
		} else {
			$wherearr[] = 'invisible = 0';
			if(!empty($paramarr['fid'])) {
				$paramarr['fid'] = getdotstring($paramarr['fid'], 'int');
				if($paramarr['fid']) $wherearr[] = 'fid IN ('.$paramarr['fid'].')';
			}
			$wherearr[] = 'fid IN ('.$fids.')';
			if(!empty($paramarr['tid'])) {
				$paramarr['tid'] = getdotstring($paramarr['tid'], 'int');
				if($paramarr['tid']) $wherearr[] = 'tid IN ('.$paramarr['tid'].')';
			}
			if(!empty($paramarr['first'])) {
				$wherearr[] = 'first = 1';
			}
			if(!empty($paramarr['attachment'])) {
				$wherearr[] = 'attachment = 1';
			}
		}
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
		
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ',$sql);

		dbconnect(1);
		
		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query('SELECT COUNT(*) FROM '.tname('posts', 1).' '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}

	} else {
		dbconnect(1);
		$bbsdb = $_SGLOBAL['db_bbs'];
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr, $bbsdb);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//query
	if($listcount) {
			
		if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;
		if(empty($paramarr['messagedot'])) $paramarr['messagedot'] = 0;
		
		$query = $_SGLOBAL['db_bbs']->query($sqlstring);
		$aids = $dot = '';
		$theblockarr['text'] = array();
		while($post = $_SGLOBAL['db_bbs']->fetch_array($query)) {
			
			//附件
			if($post['attachment']) {
				$aids .= $dot.$post['pid'];
				$dot = ', ';
			}
			
			//链接
			$post['url'] = getbbsurl('viewthread.php', array('tid'=>$post['tid']));
			
			//标题处理
			if(!empty($post['subject'])) $post['subjectall'] = $post['subject'];
			if(!empty($post['subject']) && !empty($paramarr['subjectlen'])) {
				$post['subject'] = cutstr($post['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
			}
			
			//内容
			if(!empty($post['message']) && !empty($paramarr['messagelen'])) {
				$post['message'] = trim(stripbbcode(strip_tags($post['message'])));
				$post['message'] = trim(cutstr($post['message'], $paramarr['messagelen'], $paramarr['messagedot']));
			}
			if(empty($post['subject']) && !empty($post['message'])) {
				$post['subject'] = $post['subjectall'] = cutstr(trim(stripbbcode(strip_tags($post['message']))), 40, 1);
			}

			$theblockarr['text'][] = $post;
		}
		
		//附件处理
		if($aids) {
			$sqlstring = 'SELECT * FROM '.tname('attachments', 1).' WHERE pid IN ('.$aids.')';
			$query = $_SGLOBAL['db_bbs']->query($sqlstring);
			while($attach = $_SGLOBAL['db_bbs']->fetch_array($query)) {
				$value['attachment'] = getbbsattachment($value);
				$theblockarr[$attach['pid']][] = $attach;
			}
		}
		unset($aids, $dot);
	}
	return $theblockarr;
}

function block_bbsthread($paramarr) {
	global $_SGLOBAL, $_SGET, $_SCONFIG;
	
	dbconnect(1);
	
	@include_once(S_ROOT.'./data/system/bbsforums.cache.php');
	$fidarr = array();
	foreach($_SGLOBAL['bbsforumarr'] as $value) {
		if(!empty($value['allowshare'])) $fidarr[] = $value['fid'];
	}
	$fids = simplode($fidarr);

	if(empty($paramarr['sql'])) {
		//set sql var
		$sql = array();
		
		//select
		$sql['select'] = 'SELECT t.*';
		
		//from
		$sql['from'] = 'FROM '.tname('threads', 1).' t';
		
		if(!empty($paramarr['showdetail'])) {
			$sql['select'] .= ', p.message, p.htmlon, p.bbcodeoff, p.smileyoff, p.parseurloff';
			$sql['join'] = 'LEFT JOIN '.tname('posts', 1).' p ON p.tid=t.tid AND p.first=1';
		}
		
		//where
		$wherearr = array();
		if(!empty($paramarr['tid'])) {
			$paramarr['tid'] = getdotstring($paramarr['tid'], 'int');
			if($paramarr['tid']) $wherearr[] = 't.tid IN ('.$paramarr['tid'].')';
		} else {

			if(!empty($paramarr['blog'])) {
				$wherearr[] = 't.blog = 1';
			}
			
			if(!empty($paramarr['fid'])) {
				$paramarr['fid'] = getdotstring($paramarr['fid'], 'int');
				if($paramarr['fid']) $wherearr[] = 't.fid IN ('.$paramarr['fid'].')';
			}
			$wherearr[] = 't.fid IN ('.$fids.')';
			if(!empty($paramarr['typeid'])) {
				$paramarr['typeid'] = getdotstring($paramarr['typeid'], 'int');
				if($paramarr['typeid']) $wherearr[] = 't.typeid IN ('.$paramarr['typeid'].')';
			}

			if(!empty($paramarr['authorid'])) {
				$paramarr['authorid'] = getdotstring($paramarr['authorid'], 'int');
				if($paramarr['authorid']) $wherearr[] = 't.authorid IN ('.$paramarr['authorid'].')';
			}

			if(!empty($paramarr['digest'])) {
				$paramarr['digest'] = getdotstring($paramarr['digest'], 'int');
				if($paramarr['digest']) $wherearr[] = 't.digest IN ('.$paramarr['digest'].')';
			}
			
			$scopequery = getscopequery('t', 'readperm', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

			$scopequery = getscopequery('t', 'price', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

			$scopequery = getscopequery('t', 'views', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

			$scopequery = getscopequery('t', 'dateline', $paramarr, 1);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

			$scopequery = getscopequery('t', 'lastpost', $paramarr, 1);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

			$scopequery = getscopequery('t', 'replies', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

			$scopequery = getscopequery('t', 'rate', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

			if(!empty($paramarr['poll'])) {
				if(B_VER == '5') {
					$wherearr[] = 't.special = 1';
				} elseif(B_VER == '4') {
					$wherearr[] = 't.poll = 1';
				}
			}
			if(!empty($paramarr['attachment'])) {
				$wherearr[] = 't.attachment > 0';
			}
			if(!empty($paramarr['closed'])) {
				$wherearr[] = 't.closed = 1';
			}
			$wherearr[] = 't.displayorder >= 0';
			if(empty($paramarr['sgid'])) {
				if(B_VER == '5') {
					$wherearr[] = 't.supe_pushstatus > 0';//版本5推送
				}
			}
		}

		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
		
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ', $sql);
		
		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query('SELECT COUNT(*) FROM '.tname('threads', 1).' t '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	} else {
		$bbsdb = $_SGLOBAL['db_bbs'];
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr, $bbsdb);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//查询数据
	if($listcount) {
			
		//预处理
		if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;
		if(empty($paramarr['messagedot'])) $paramarr['messagedot'] = 0;
		
		//查询
		$query = $_SGLOBAL['db_bbs']->query($sqlstring);
		while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
			//生成HTML时
			if(defined('CREATEHTML')) {
				$_SGLOBAL['item_cache']['viewthread_'.$value['tid']] = array('fid' => $value['fid'], 'dateline' => $value['dateline']);
			}
			
			//标题处理
			$value['subjectall'] = $value['subject'];
			if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
				$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
			}
			
			//内容处理
			if(!empty($value['message']) && !empty($paramarr['messagelen'])) {
				$value['message'] = trim(strip_tags(stripbbcode($value['message'])));
				$value['message'] = trim(cutstr($value['message'], $paramarr['messagelen'], $paramarr['messagedot']));
			}

			//链接
			if($_SCONFIG['bbsurltype'] == 'bbs' || (!empty($paramarr['bbsurltype']) && $paramarr['bbsurltype'] == 'bbs')) {
				$value['url'] = B_URL.'/viewthread.php?tid='.$value['tid'];
			} else {
				$value['url'] = geturl('action/viewthread/tid/'.$value['tid']);
			}
			
	
			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function block_category($paramarr) {
	global $_SGLOBAL, $_SGET;

	if(empty($paramarr['sql'])) {

		$sql = array();
		
		$sql['select'] = 'SELECT c.*';
		$sql['from'] = 'FROM '.tname('categories').' c';

		$wherearr = array();
		//where
		if(!empty($paramarr['catid'])) {
			$paramarr['catid'] = getdotstring($paramarr['catid'], 'int');
			if($paramarr['catid']) $wherearr[] = 'c.catid IN ('.$paramarr['catid'].')';
		} else {
			if(!empty($paramarr['type'])) {
				if($paramarr['type']) $wherearr[] = 'c.type = \''.$paramarr['type'].'\'';
			}
			
			if(!empty($paramarr['isroot'])) {
				$paramarr['isroot'] = intval($paramarr['isroot']);
				if($paramarr['isroot'] == 1) {
					$wherearr[] = 'c.upid < 1';
				} elseif($paramarr['isroot'] == 2) {
					if(!empty($paramarr['upid'])) {
						$paramarr['upid'] = getdotstring($paramarr['upid'], 'int');
						if($paramarr['upid']) $wherearr[] = 'c.upid IN ('.$paramarr['upid'].')';					
					} else {
						$wherearr[] = 'c.upid > 0';
					}
				}
			} else {
				if(!empty($paramarr['upid'])) {
					$paramarr['upid'] = getdotstring($paramarr['upid'], 'int');
					if($paramarr['upid']) $wherearr[] = 'c.upid IN ('.$paramarr['upid'].')';
				}
			}
		}
		$sql['where'] = '';
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
		
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ', $sql);
		
		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('categories').' c '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}

	} else {
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//查询数据
	if($listcount) {
		//预处理
		//查询
		$query = $_SGLOBAL['db']->query($sqlstring);
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {

			//链接
			$value['url'] = geturl('action/category/catid/'.$value['catid']);
			
			//图片封面
			if(!empty($value['image'])) {
				$value['image'] = A_URL.'/'.$value['image'];
			} else {
				$value['image'] = S_URL.'/images/base/nopic.gif';
			}
			
			//图片封面
			if(!empty($value['thumb'])) {
				$value['thumb'] = A_URL.'/'.$value['thumb'];
			} else {
				$value['thumb'] = S_URL.'/images/base/nopic.gif';
			}

			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function block_friendlink($paramarr) {
	global $_SGLOBAL, $_SGET, $_SCONFIG;
	
	if(empty($paramarr['sql'])) {
		
		//set sql var
		$sql = array();
		
		//select
		$sql['select'] = 'SELECT *';
		
		//from
		$sql['from'] = 'FROM '.tname('friendlinks');
		
		//where
		$wherearr = array();
		if(!empty($paramarr['id'])) {
			$paramarr['id'] = getdotstring($paramarr['id'], 'int');
			if($paramarr['id']) $wherearr[] = 'id IN ('.$paramarr['id'].')';
		} else {
			if(!empty($paramarr['note'])) {
				$wherearr[] = 'description != \'\'';
			}
			if(!empty($paramarr['logo'])) {
				$wherearr[] = 'logo != \'\'';
			}
		}
		$sql['where'] = '';
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);

		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}

		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ',$sql);

		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('friendlinks').' '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	} else {
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//查询数据
	if($listcount) {
		//预处理
		if(empty($paramarr['namedot'])) $paramarr['namedot'] = 0;
		if(empty($paramarr['notedot'])) $paramarr['notedot'] = 0;
		
		//查询
		$query = $_SGLOBAL['db']->query($sqlstring);
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			
			//链接名
			$value['nameall'] = $value['name'];
			if(!empty($value['name']) && !empty($paramarr['namelen'])) {
				$value['name'] = cutstr($value['name'], $paramarr['namelen'], $paramarr['namedot']);
			}
			
			//注释
			$value['noteall'] = $value['description'];
			if(!empty($value['description']) && !empty($paramarr['notelen'])) {
				$value['note'] = $value['description'] = cutstr($value['description'], $paramarr['notelen'], $paramarr['notedot']);
			}		

			//logo
			if(!empty($value['logo'])) {
				$check_url = parse_url($value['logo']);
				if(empty($check_url['scheme'])) {
					$value['logo'] = S_URL.'/'.$value['logo'];
				}
			}

			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function block_model_category($paramarr) {

	global $_SGLOBAL, $_SGET;
	if(empty($paramarr['sql'])) {

		$sql = array();
		
		$sql['select'] = 'SELECT c.*';
		$sql['from'] = 'FROM '.tname($paramarr['name'].'categories').' c';

		$wherearr = array();
		//where
		if(!empty($paramarr['catid'])) {
			$paramarr['catid'] = getdotstring($paramarr['catid'], 'int');
			if($paramarr['catid']) $wherearr[] = 'c.catid IN ('.$paramarr['catid'].')';
		} else {
					
			if(!empty($paramarr['isroot'])) {
				$paramarr['isroot'] = intval($paramarr['isroot']);
				if($paramarr['isroot'] == 1) {
					$wherearr[] = 'c.upid < 1';
				} elseif($paramarr['isroot'] == 2) {
					if(!empty($paramarr['upid'])) {
						$paramarr['upid'] = getdotstring($paramarr['upid'], 'int');
						if($paramarr['upid']) $wherearr[] = 'c.upid IN ('.$paramarr['upid'].')';					
					} else {
						$wherearr[] = 'c.upid > 0';
					}
				}
			} else {
				if(!empty($paramarr['upid'])) {
					$paramarr['upid'] = getdotstring($paramarr['upid'], 'int');
					if($paramarr['upid']) $wherearr[] = 'c.upid IN ('.$paramarr['upid'].')';
				}
			}
					
		}
		$sql['where'] = '';
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
		
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ', $sql);
		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname($paramarr['name'].'categories').' c '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	} else {
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//查询数据
	if($listcount) {
		//预处理
		//查询
		$query = $_SGLOBAL['db']->query($sqlstring);
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			//链接
			$value['url'] = geturl('action/model/name/'.$paramarr['name'].'/catid/'.$value['catid']);
			
			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function block_model($paramarr) {

	global $_SGLOBAL, $_SGET;
	if(empty($paramarr['sql'])) {
		$sql = array();
		$sql['select'] = 'SELECT i.*';
		$sql['from'] = 'FROM '.tname($paramarr['name'].'items').' i';
		$sql['join'] = '';
		
		//内容
		if(!empty($paramarr['showdetail']) && empty($paramarr['notype'])) {
			$sql['select'] = 'SELECT ii.*, i.*';
			$sql['join'] .= ' LEFT JOIN '.tname($paramarr['name'].'message').' ii ON ii.itemid=i.itemid';
		}

		$wherearr = array();

		//where
		if(!empty($paramarr['itemid'])) {
			$paramarr['itemid'] = getdotstring($paramarr['itemid'], 'int');
			if($paramarr['itemid']) $wherearr[] = 'i.itemid IN ('.$paramarr['itemid'].')';
		} else {

			//作者
			if(!empty($paramarr['uid'])) {
				$paramarr['uid'] = getdotstring($paramarr['uid'], 'int');
				if($paramarr['uid']) $wherearr[] = 'i.uid IN ('.$paramarr['uid'].')';
			}

			//分类
			if(!empty($paramarr['catid'])) {
				$paramarr['catid'] = getdotstring($paramarr['catid'], 'int');
				if($paramarr['catid']) $wherearr[] = 'i.catid IN ('.$paramarr['catid'].')';
			}

			//站点审核
			if(!empty($paramarr['grade'])) {
				$paramarr['grade'] = getdotstring($paramarr['grade'], 'int');
				if(!empty($paramarr['grade'])) $wherearr[] = 'i.grade IN ('.$paramarr['grade'].')';
			}

			if(!empty($paramarr['haveattach'])) {
				 $wherearr[] = 'i.subjectimage !=\'\'';
			}

			if(!empty($paramarr['dateline'])) {
				$paramarr['dateline'] = intval($paramarr['dateline']);
				if($paramarr['dateline']) $wherearr[] = 'i.dateline >= '.($_SGLOBAL['timestamp']-$paramarr['dateline']);
			}

			if(!empty($paramarr['lastpost'])) {
				$paramarr['lastpost'] = intval($paramarr['lastpost']);
				if($paramarr['lastpost']) $wherearr[] = 'i.lastpost >= '.($_SGLOBAL['timestamp']-$paramarr['lastpost']);
			}

			$scopequery = getscopequery('i', 'viewnum', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

			$scopequery = getscopequery('i', 'replynum', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

		}
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}

		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}

		//query
		$sqlstring = implode(' ', $sql);

		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname($paramarr['name'].'items').' i '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	} else {
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	if($listcount) {
		//预处理
		if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;

		if(!empty($paramarr['showcategory'])) {
			include_once(S_ROOT.'./function/model.func.php');
			$cacheinfo = getmodelinfoall('modelname', $paramarr['name']);
		}

		$query = $_SGLOBAL['db']->query($sqlstring);
		$itemids = array();
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			//生成HTML时
			if(defined('CREATEHTML')) {
				$_SGLOBAL['item_cache']['model_'.$paramarr['name'].'_'.$value['itemid']] = array('dateline' => $value['dateline']);
			}
			
			//处理
			$value['subjectall'] = $value['subject'];
			if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
				$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
			}
		
			//链接
			$value['url'] = geturl('action/model/name/'.$paramarr['name'].'/itemid/'.$value['itemid']);

			//内容处理
			if(!empty($value['message']) && !empty($paramarr['messagelen'])) {
				$value['message'] = trim(strip_tags($value['message']));
				$value['message'] = trim(cutstr($value['message'], $paramarr['messagelen'], $paramarr['messagedot']));
			}

			//图片地址
			if(!empty($value['subjectimage'])) {
				$value['subjectimage'] = A_URL.'/'.$value['subjectimage'];
			} else{
				$value['subjectimage'] = S_URL.'/images/base/nopic.gif';
			}
			
			//分类名
			if(!empty($cacheinfo['categoryarr'][$value['catid']])) $value['catname'] = $cacheinfo['categoryarr'][$value['catid']];

			$theblockarr[$value['itemid']] = $value;
		}
	}
	return $theblockarr;
}

function block_poll($paramarr) {

	global $_SGLOBAL, $_SGET;
	if(empty($paramarr['sql'])) {
		$sql = array();
		$sql['select'] = 'SELECT pollid, pollnum, dateline, updatetime, ismulti, subject, summary as message, options, voters ';
		$sql['from'] = 'FROM '.tname('polls');

		$wherearr = array();
		//where
		if(!empty($paramarr['pollid'])) {
			$paramarr['pollid'] = getdotstring($paramarr['pollid'], 'int');
			if($paramarr['pollid']) $wherearr[] = 'pollid IN ('.$paramarr['pollid'].')';
		} else {
			if(!empty($paramarr['dateline'])) {
				$paramarr['dateline'] = intval($paramarr['dateline']);
				if($paramarr['dateline']) $wherearr[] = 'dateline >= '.($_SGLOBAL['timestamp']-$paramarr['dateline']);
			}
		}
		$sql['where'] = '';
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
		
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ', $sql);

		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('polls').' '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}

	} else {
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//查询数据
	if($listcount) {
		//预处理
		//查询
		$query = $_SGLOBAL['db']->query($sqlstring);
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {

			//选项
			$value['options'] = unserialize($value['options']);
			
			//链接
			$value['url'] = geturl('action/poll/pollid/'.$value['pollid']);
			
			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function block_postitem($paramarr) {
	global $_SGLOBAL, $_SGET;
	
	if(empty($paramarr['sql'])) {
		$sql = array();
		$sql['select'] = 'SELECT i.*';
		$sql['from'] = 'FROM '.tname('postitems').' i';
		$sql['join'] = '';
	
		$wherearr = array();
		$showpic = 0;
		
		//where
		if(!empty($paramarr['itemid'])) {
			$paramarr['itemid'] = getdotstring($paramarr['itemid'], 'int');
			if($paramarr['itemid']) $wherearr[] = 'i.itemid IN ('.$paramarr['itemid'].')';
		} else {
			//作者
			if(!empty($paramarr['uid'])) {
				$paramarr['uid'] = getdotstring($paramarr['uid'], 'int');
				if($paramarr['uid']) $wherearr[] = 'i.uid IN ('.$paramarr['uid'].')';
			}
	
			//分类
			if(!empty($paramarr['catid'])) {
				$paramarr['catid'] = getdotstring($paramarr['catid'], 'int');
				if($paramarr['catid']) $wherearr[] = 'i.catid IN ('.$paramarr['catid'].')';
			}
			
			//限制
			if(empty($paramarr['catid'])) {
				if(!empty($paramarr['type'])) {
					$wherearr[] = 'i.type=\''.$paramarr['type'].'\'';
				}
			}
			
			if(!empty($paramarr['dateline'])) {
				$paramarr['dateline'] = intval($paramarr['dateline']);
				if($paramarr['dateline']) $wherearr[] = 'i.dateline >= '.($_SGLOBAL['timestamp']-$paramarr['dateline']);
			}
	
			if(!empty($paramarr['lastpost'])) {
				$paramarr['lastpost'] = intval($paramarr['lastpost']);
				if($paramarr['lastpost']) $wherearr[] = 'i.lastpost >= '.($_SGLOBAL['timestamp']-$paramarr['lastpost']);
			}
			
			@include_once S_ROOT.'/data/system/click.cache.php';
			$clickgroupids = array_keys($_SGLOBAL['clickgroup']['spaceitems']);
	
			foreach ($_SGLOBAL['click'] as $key => $kvalue) {
				if(in_array($key, $clickgroupids)) {
					foreach ($kvalue as $value) {
						if(!is_int($value['name'])){
							$scopequery = getscopequery('i', 'click_'.$value['clickid'], $paramarr);
							if(!empty($scopequery)) $wherearr[] = $scopequery;
						}
					}	
				}
			}
			
			$scopequery = getscopequery('i', 'hot', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
			
		}
		
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
		
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;
	
			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;
	
			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ', $sql);
		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('postitems').' i '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}

	} else {
		
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	if($listcount) {
		//预处理
		if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;
		if(empty($paramarr['messagedot'])) $paramarr['messagedot'] = 0;
		
		if(!empty($paramarr['showcategory'])) {
			include_once(S_ROOT.'./data/system/category.cache.php');
		}
		$query = $_SGLOBAL['db']->query($sqlstring);
		$allitemids = $aids = array();
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			//处理
			$value['subjectall'] = $value['subject'];
			if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
				$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
			}
			
			//链接
			$value['url'] = S_URL_ALL."/cp.php?ac=$value[type]&op=view&itemid=$value[itemid]";
			
			$allitemids[] = $value['itemid'];
	
			//相关tag
			if(!empty($value['relativetags'])) $value['relativetags'] = $value['tags'] = unserialize($value['relativetags']);
			
			//分类名
			if(!empty($_SGLOBAL['category'][$value['catid']])) $value['catname'] = $_SGLOBAL['category'][$value['catid']];
						
			$theblockarr[$value['itemid']] = $value;
		}

		//分页内容处理/取第一页
		if(!empty($paramarr['showdetail'])) {
			if(!empty($allitemids)) {
				$theitemarr = array();
				$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('postitems').' WHERE itemid IN (\''.implode('\',\'', $allitemids).'\')');
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					if(empty($theitemarr[$value['itemid']])) {
						if(!empty($value['message']) && !empty($paramarr['messagelen'])) {
							$value['message'] = strip_tags(trim($value['message']));
							$value['message'] = trim(cutstr($value['message'], $paramarr['messagelen'], $paramarr['messagedot']));
						}
						$theitemarr[$value['itemid']] = 1;
						$theblockarr[$value['itemid']] = array_merge($theblockarr[$value['itemid']], $value);
					}
				}
			}
		}
	}
	
	return $theblockarr;

}

function block_spacecomment($paramarr) {

	global $_SGLOBAL, $_SGET;
	if(empty($paramarr['sql'])) {
		$sql = array();
		$sql['select'] = 'SELECT *';
		$sql['from'] = 'FROM '.tname('spacecomments');

		$wherearr = array();
		//where
		if(!empty($paramarr['cid'])) {
			$paramarr['cid'] = getdotstring($paramarr['cid'], 'int');
			if($paramarr['cid']) $wherearr[] = 'cid IN ('.$paramarr['cid'].')';
		} else {
			if(!empty($paramarr['itemid'])) {
				$paramarr['itemid'] = getdotstring($paramarr['itemid'], 'int');
				if($paramarr['itemid']) $wherearr[] = 'itemid IN ('.$paramarr['itemid'].')';
			}
			if(!empty($paramarr['uid'])) {
				$paramarr['uid'] = getdotstring($paramarr['uid'], 'int');
				if($paramarr['uid']) $wherearr[] = 'uid IN ('.$paramarr['uid'].')';
			}
			if(!empty($paramarr['authorid'])) {
				$paramarr['authorid'] = getdotstring($paramarr['authorid'], 'int');
				if($paramarr['authorid']) $wherearr[] = 'authorid IN ('.$paramarr['authorid'].')';
			}
			if(!empty($paramarr['type'])) {
				$paramarr['type'] = getdotstring($paramarr['type'], 'char', false, $_SGLOBAL['type'], 1);
				if($paramarr['type']) $wherearr[] = 'type IN ('.$paramarr['type'].')';
			}
			if(!empty($paramarr['click_33'])) {
				$paramarr['click_33'] = intval($paramarr['click_33']);
				if($paramarr['click_33']) $wherearr[] = 'click_33 >= '.$paramarr['click_33'];
			}
			if(!empty($paramarr['click_34'])) {
				$paramarr['click_34'] = intval($paramarr['click_34']);
				if($paramarr['click_34']) $wherearr[] = 'click_34 >= '.$paramarr['click_34'];
			}
		}
		$sql['where'] = '';
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
		
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ', $sql);

		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spacecomments').' '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}

	} else {
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//查询数据
	if($listcount) {
		//预处理
		if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;
		if(empty($paramarr['messagedot'])) $paramarr['messagedot'] = 0;
		
		//查询
		$query = $_SGLOBAL['db']->query($sqlstring);
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			
			//标题处理
			$value['subjectall'] = $value['subject'];
			if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
				$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
			}
			
			//内容处理
			if(!empty($value['message']) && !empty($paramarr['messagelen'])) {
				$value['message'] = trim(strip_tags($value['message']));
				$value['message'] = trim(cutstr($value['message'], $paramarr['messagelen'], $paramarr['messagedot']));
			}
			
			//链接
			$value['url'] = geturl('action/viewnews/itemid/'.$value['itemid']);
			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function block_tag($paramarr) {

	global $_SGLOBAL, $_SGET;
	if(empty($paramarr['sql'])) {
		$sql = array();
		$sql['select'] = 'SELECT *';
		$sql['from'] = 'FROM '.tname('tags');

		$wherearr = array();
		//where
		if(!empty($paramarr['tagid'])) {
			$paramarr['tagid'] = getdotstring($paramarr['tagid'], 'int');
			if($paramarr['tagid']) $wherearr[] = 'tagid IN ('.$paramarr['tagid'].')';
		} else {
			if(!empty($paramarr['dateline'])) {
				$paramarr['dateline'] = intval($paramarr['dateline']);
				if($paramarr['dateline']) $wherearr[] = 'dateline >= '.($_SGLOBAL['timestamp']-$paramarr['dateline']);
			}
			if(!empty($paramarr['uid'])) {
				$paramarr['uid'] = getdotstring($paramarr['uid'], 'int');
				if($paramarr['uid']) $wherearr[] = 'uid IN ('.$paramarr['uid'].')';
			}
			$wherearr[] = "close='0'";
		}
		$sql['where'] = '';
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
		
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ', $sql);

		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('tags').' '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	} else {
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//查询数据
	if($listcount) {
		//预处理
		//查询
		$query = $_SGLOBAL['db']->query($sqlstring);
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['url'] = geturl('action/tag/tagid/'.$value['tagid']);
			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function block_uchblog($paramarr) {

	global $_SGLOBAL, $_SC, $_SGET, $_SCONFIG;
	dbconnect(2);
	if(empty($paramarr['sql'])) {
		$sql = array();
		$sql['select'] = 'SELECT b.*';
		$sql['from'] = 'FROM '.tname('blog', '2').' b';

		if(!empty($paramarr['showdetail'])) {
			$sql['select'] = 'SELECT bf.*, b.*';
			$sql['from'] .= ' LEFT JOIN '.tname('blogfield', '2').' bf ON bf.blogid=b.blogid';
		}
		$wherearr = array();
		//where
		if(!empty($paramarr['blogid'])) {
			$paramarr['blogid'] = getdotstring($paramarr['blogid'], 'int');
			if($paramarr['blogid']) $wherearr[] = 'b.blogid IN ('.$paramarr['blogid'].')';
		} else {
			if(!empty($paramarr['dateline'])) {
				$paramarr['dateline'] = intval($paramarr['dateline']);
				if($paramarr['dateline']) $wherearr[] = 'dateline >= '.($_SGLOBAL['timestamp']-$paramarr['dateline']);
			}

			if(!empty($paramarr['uid'])) {
				$paramarr['uid'] = getdotstring($paramarr['uid'], 'int');
				if($paramarr['uid']) $wherearr[] = 'b.uid IN ('.$paramarr['uid'].')';
			}

			if(!empty($paramarr['picflag'])) {
				if($paramarr['picflag'] == 1) {
					$wherearr[] = "b.pic = ''";
				} else {
					$wherearr[] = "b.pic != ''";
				}
			}
			
			$wherearr[] = 'b.friend = 0';

			$scopequery = getscopequery('b', 'viewnum', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

			$scopequery = getscopequery('b', 'replynum', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

			$scopequery = getscopequery('b', 'tracenum', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
		}
		$sql['where'] = '';
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
		
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ', $sql);
		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db_uch']->result($_SGLOBAL['db_uch']->query('SELECT COUNT(*) FROM '.tname('blog', '2').' b '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}

	} else {
		$uchdb = $_SGLOBAL['db_uch'];
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr, $uchdb);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//查询数据
	if($listcount) {
		//预处理
		if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;
		if(empty($paramarr['messagedot'])) $paramarr['messagedot'] = 0;
		
		//查询
		$query = $_SGLOBAL['db_uch']->query($sqlstring);
		while ($value = $_SGLOBAL['db_uch']->fetch_array($query)) {
			//生成HTML时
			if(defined('CREATEHTML')) {
				$_SGLOBAL['item_cache']['blogdetail_'.$value['blogid']] = array('dateline' => $value['dateline']);
			}
			
			//图片处理
			$value['pic'] = mkpicurl($value);
			//标题处理
			$value['subjectall'] = $value['subject'];
			if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
				$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
			}
			
			//连接处理
			if($_SCONFIG['bbsurltype'] == 'bbs' || (!empty($paramarr['bbsurltype']) && $paramarr['bbsurltype'] == 'bbs')) {
				$value['url'] = $_SC['uchurl'].'/space.php?uid='.$value['uid'].'&do=blog&id='.$value['blogid'];
			} else {
				$value['url'] = geturl('action/blogdetail/uid/'.$value['uid'].'/id/'.$value['blogid']);
			}
			
			//内容处理
			if(!empty($value['message']) && !empty($paramarr['messagelen'])) {
				$value['message'] = trim(strip_tags($value['message']));
				$value['message'] = trim(cutstr($value['message'], $paramarr['messagelen'], $paramarr['messagedot']));
			}
			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function block_uchphoto($paramarr) {
	global $_SGLOBAL, $_SC, $_SGET,$_SCONFIG;
	
	dbconnect(2);
	if(empty($paramarr['sql'])) {
		$sql = array();
		$sql['select'] = 'SELECT *';
		$sql['from'] = 'FROM '.tname('album', '2');

		$wherearr = array();
		//where
		if(!empty($paramarr['albumid'])) {
			$paramarr['albumid'] = getdotstring($paramarr['albumid'], 'int');
			if($paramarr['albumid']) $wherearr[] = 'albumid IN ('.$paramarr['albumid'].')';
		} else {
			if(!empty($paramarr['dateline'])) {
				$paramarr['dateline'] = intval($paramarr['dateline']);
				if($paramarr['dateline']) $wherearr[] = 'dateline >= '.($_SGLOBAL['timestamp']-$paramarr['dateline']);
			}

			if(!empty($paramarr['updatetime'])) {
				$paramarr['updatetime'] = intval($paramarr['updatetime']);
				if($paramarr['updatetime']) $wherearr[] = 'updatetime >= '.($_SGLOBAL['timestamp']-$paramarr['updatetime']);
			}

			if(!empty($paramarr['uid'])) {
				$paramarr['uid'] = getdotstring($paramarr['uid'], 'int');
				if($paramarr['uid']) $wherearr[] = 'uid IN ('.$paramarr['uid'].')';
			}
			
			$wherearr[] = 'friend = 0';
			$wherearr[] = 'picnum != 0';
			if(!empty($paramarr['dateline'])) {
				$paramarr['dateline'] = intval($paramarr['dateline']);
				if($paramarr['dateline']) $wherearr[] = 'dateline >= '.($_SGLOBAL['timestamp']-$paramarr['dateline']);
			}

			if(!empty($paramarr['updatetime'])) {
				$paramarr['updatetime'] = intval($paramarr['updatetime']);
				if($paramarr['updatetime']) $wherearr[] = 'updatetime >= '.($_SGLOBAL['timestamp']-$paramarr['updatetime']);
			}

		}
		$sql['where'] = '';
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
		
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ', $sql);

		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db_uch']->result($_SGLOBAL['db_uch']->query('SELECT COUNT(*) FROM '.tname('album', '2').' '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}

	} else {
		$uchdb = $_SGLOBAL['db_uch'];
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr, $uchdb);
	}

	//查询数据
	if($listcount) {
		//预处理
		if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;
		
		//查询
		$query = $_SGLOBAL['db_uch']->query($sqlstring);
		while ($value = $_SGLOBAL['db_uch']->fetch_array($query)) {
			
			if(defined('CREATEHTML')) {
				$_SGLOBAL['item_cache']['imagelist_'.$value['albumid']] = array('dateline' => $value['dateline']);
			}
			
			//图片处理
			$value['pic'] = mkpicurl($value);
			//标题处理
			$value['subjectall'] = $value['albumname'];
		
			//连接处理
			if($_SCONFIG['bbsurltype'] == 'bbs' || (!empty($paramarr['bbsurltype']) && $paramarr['bbsurltype'] == 'bbs')) {
				$value['url'] = $_SC['uchurl'].'/space.php?uid='.$value['uid'].'&do=album&id='.$value['albumid'];
			} else {
				$value['url'] = geturl('action/imagelist/uid/'.$value['uid'].'/id/'.$value['albumid']);
			}

			if(!empty($value['albumname']) && !empty($paramarr['subjectlen'])) {
				$value['albumname'] = cutstr($value['albumname'], $paramarr['subjectlen'], $paramarr['subjectdot']);
			}
			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function block_uchspace($paramarr) {
	global $_SGLOBAL, $_SC, $_SGET;
	dbconnect(2);
	if(empty($paramarr['sql'])) {
		$sql = array();
		$sql['select'] = 'SELECT s.*';
		$sql['from'] = 'FROM '.tname('space', '2').' s';

		if(!empty($paramarr['showdetail'])) {
			$sql['select'] = 'SELECT sf.*, s.*';
			$sql['from'] .= ' LEFT JOIN '.tname('spacefield', '2').' sf ON sf.uid=s.uid';
		}
		$wherearr = array();
		//where
		if(!empty($paramarr['uid'])) {
			$paramarr['uid'] = getdotstring($paramarr['uid'], 'int');
			if($paramarr['uid']) $wherearr[] = 's.uid IN ('.$paramarr['uid'].')';
		} else {
			if(!empty($paramarr['avatar'])) {
				if($paramarr['avatar'] == 1) {
					$wherearr[] = 's.avatar = 0';
				} else {
					$wherearr[] = 's.avatar = 1';
				}
			}

			$scopequery = getscopequery('s', 'viewnum', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;

			$scopequery = getscopequery('s', 'friendnum', $paramarr);
			if(!empty($scopequery)) $wherearr[] = $scopequery;
		}
		$sql['where'] = '';
		if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
		
		//order
		if(!empty($paramarr['order'])) {
			$sql['order'] = 'ORDER BY '.$paramarr['order'];
		}
		
		//limit
		if(!empty($paramarr['perpage'])) {
			$paramarr['perpage'] = intval($paramarr['perpage']);
			if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

			if(empty($_SGET['page'])) $_SGET['page'] = 1;
			$_SGET['page'] = intval($_SGET['page']);
			if($_SGET['page'] < 1) $_SGET['page'] = 1;

			$start = ($_SGET['page']-1)*$paramarr['perpage'];
			$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
		} else {
			if(empty($paramarr['limit'])) {
				$sql['limit'] = 'LIMIT 0,1';
			} else {
				$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
				if($paramarr['limit']) {
					$sql['limit'] = 'LIMIT '.$paramarr['limit'];
				} else {
					$sql['limit'] = 'LIMIT 0,1';
				}
			}
		}
		
		//query
		$sqlstring = implode(' ', $sql);
		//multi
		$listcount = 1;
		if(!empty($paramarr['perpage'])) {
			$listcount = $_SGLOBAL['db_uch']->result($_SGLOBAL['db_uch']->query('SELECT COUNT(*) FROM '.tname('space', '2').' '.$sql['where']), 0);
			if($listcount) {
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}

	} else {
		$uchdb = $_SGLOBAL['db_uch'];
		include_once(S_ROOT.'./function/block_sql.func.php');
		list($sqlstring, $listcount) = runsql($paramarr, $uchdb);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {			
				$urlarr = $_SGET;
				unset($urlarr['page']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
			}
		}
	}

	//查询数据
	if($listcount) {
		//预处理	
		include_once S_ROOT.'./uc_client/client.php';
		//查询
		$query = $_SGLOBAL['db_uch']->query($sqlstring);
		while ($value = $_SGLOBAL['db_uch']->fetch_array($query)) {
			//头像处理
			$value['avatarflash'] = uc_avatar($_SGLOBAL['supe_uid']);
			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function stripbbcode($string) {
	return preg_replace("/\[.+?\]/i", '', $string);
}
?>