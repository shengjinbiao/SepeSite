<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_spacenews.php 13513 2009-11-26 07:34:15Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./function/news.func.php');
include_once(S_ROOT.'./function/tag.func.php');
//权限
$type = postget('type');
$type = $channel = empty($type) ? 'news' : trim($type);
$_SGET['folder'] = intval(postget('folder'));
$_SGET['folder'] = empty($_SGET['folder']) ? 0 : intval($_SGET['folder']);
if($_SGET['folder'] == 1 && !(checkperm('managefolder') || checkperm('managemodpost'))) {
	showmessage('spacenews_no_popedom_check');
}

$allowmax = 100;		//最大上传数量
$catarr = array();
$perpage = empty($_GET['perpage']) ? 0 : intval($_GET['perpage']);			//默认每页显示列表数目
if(!$perpage) $perpage = 20;
$hashstr = smd5($_SGLOBAL['supe_uid'].'/'.$_SGLOBAL['timestamp'].random(6));	//附件识别码

//获取的变量初始化
$_SGET['page'] = intval(postget('page'));
$_SGET['catid'] = intval(postget('catid'));
$_SGET['itemtypeid'] = intval(postget('itemtypeid'));
$_SGET['digest'] = intval(postget('digest'));
$_SGET['fromtype'] = postget('fromtype');
$_SGET['order'] = postget('order');
$_SGET['sc'] = postget('sc');
$_SGET['searchid'] = intval(postget('searchid'))==0 ? '' : intval(postget('searchid'));
$_SGET['searchkey'] = stripsearchkey(postget('searchkey'));

if(empty($_SGET['subtype'])) $_SGET['subtype'] = '';
($_SGET['page'] < 1) ? $_SGET['page'] = 1 : '';
if(!in_array($_SGET['order'], array('dateline', 'lastpost', 'uid', 'viewnum', 'replynum'))) {
	$_SGET['order'] = '';
}
if(!in_array($_SGET['sc'], array('ASC', 'DESC'))) {
	$_SGET['sc'] = 'DESC';
}
$theurl = CPURL.'?action=spacenews';
$urlplus = '&type='.$type.'&catid='.$_SGET['catid'].'&itemtypeid='.$_SGET['itemtypeid'].'&folder='.$_SGET['folder'].'&digest='.$_SGET['digest'].'&order='.$_SGET['order'].'&sc='.$_SGET['sc'].'&subtype='.$_SGET['subtype'].'&perpage='.$perpage.'&searchkey='.rawurlencode($_SGET['searchkey']).'&fromtype='.$_SGET['fromtype'];
$newurl = $theurl.$urlplus.'&page='.$_SGET['page'];

$gradearr = array(
	'0' => $alang['general_state'],
	'1' => $alang['check_grade_1'],
	'2' => $alang['check_grade_2'],
	'3' => $alang['check_grade_3'],
	'4' => $alang['check_grade_4'],
	'5' => $alang['check_grade_5']
);
if(!empty($_SCONFIG['checkgrade'])) {
	$newgradearr = explode("\t", $_SCONFIG['checkgrade']);
	for($i=0; $i<5; $i++) {
		if(!empty($newgradearr[$i])) $gradearr[$i+1] = $newgradearr[$i];
	}
}

//INIT RESULT VAR
$showurlarr = $thevalue = $dellistarr = $listarr = array();

//POST METHOD
if (submitcheck('listvaluesubmit')) {
	
	if(!empty($_POST['operation'])) {

		if($_POST['operation'] == 'delete') {
			if(!(checkperm('managecheck') || checkperm('managemodpost') || checkperm('managedelpost'))) showmessage('spacenews_no_popedom_check');
		} else {
			if(!(checkperm('managecheck') || checkperm('managemodpost'))) showmessage('spacenews_no_popedom_check');
		}

		//LIST UPDATE
		$itemidarr = $tagidarr = array();	//初始化itemidarr、tagidarr数组
		if(empty($_POST['item'])) {		//判断提交过来的是否存在待操作的记录，如果没有，则显示提示信息并退出
			showmessage('space_no_item');
		}
		$itemidstr = simplode($_POST['item']);	//用逗号链接所有的操作ID
		//对提交的数据进行检查
	
		$newidarr = array();
		$tablename = $_SGET['folder'] ? 'postitems' : 'spaceitems';
		$query = $_SGLOBAL['db']->query("SELECT itemid, catid FROM ".tname($tablename)." WHERE itemid IN ($itemidstr) AND type='$type'");	
		
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$newidarr[] = $value['itemid'];
			$catidarr[$value['catid']][] = $value['itemid'];
		}
		if(empty($newidarr)) {
			showmessage('space_no_item');
		}
		$itemidstr = simplode($newidarr);

		switch($_POST['operation']) {	//跟据操作类型做相应的操作处理
	
			case 'movecat':		//更改分类
				$catarr = explode('_', $_POST['opcatid']);
				$_SGLOBAL['db']->query('UPDATE '.tname($tablename).' SET type=\''.$catarr[0].'\', catid=\''.$catarr[1].'\' WHERE itemid IN ('.$itemidstr.')');
				break;
	
			case 'movefolder':	//移动文件夹
				if(!$_SGET['folder']) {	//发件箱->待审箱
					if($_POST['opfolder'] == 2) {
						deleteitemhtml($itemidarr);		//删除已生成的HTML文件
						moveitemfolder($itemidstr);
					}
				} else {	//待审箱->发件箱
					if($_POST['opfolder'] == 1) {
						moveitemfolder($itemidstr, 1, 0);
					}
				}
				break;

			case 'check':	//等级审核
				//更新等级
				if($_SGET['folder']) showmessage('no_action_item');
				$_SGLOBAL['db']->query('UPDATE '.tname($tablename).' SET grade=\''.intval($_POST['opcheck']).'\' WHERE itemid IN ('.$itemidstr.')');
				break;

			case 'digest':	//设置精华
				if($_SGET['folder']) showmessage('no_action_item');
				$_SGLOBAL['db']->query('UPDATE '.tname($tablename).' SET digest=\''.$_POST['opdigest'].'\' WHERE itemid IN ('.$itemidstr.')');
				break;

			case 'top':		//设置置顶
				if($_SGET['folder']) showmessage('no_action_item');
				$_SGLOBAL['db']->query('UPDATE '.tname($tablename).' SET top=\''.$_POST['optop'].'\' WHERE itemid IN ('.$itemidstr.')');
				break;

			case 'allowreply':	//是否允许评论
				$_SGLOBAL['db']->query('UPDATE '.tname($tablename).' SET allowreply=\''.$_POST['opallowreply'].'\' WHERE itemid IN ('.$itemidstr.')');
				break;

			case 'delete':		//删除操作
				if(!$_SGET['folder']) {	//发布箱->删除
					deleteitems('itemid', $itemidstr, $_POST['opdelete']);
				} else {	//待审箱->删除
					deleteitems('itemid', $itemidstr, $_POST['opdelete'], 1);
				}
				
				break;
		}

	}
	showmessage('do_success', $theurl.'&type='.$type.'&folder='.$_SGET['folder']);

} elseif (submitcheck('valuesubmit')) {
	
	if(empty($_POST['page'])) $_POST['page'] = 1;
	$page = intval($_POST['page']); 
	if($page < 1) $page = 1;

	$_POST['pitemid'] = intval($_POST['pitemid']);
	$itemid = intval($_POST['itemid']);
	
	//权限
	if($itemid && !(checkperm('manageditpost') || checkperm('managemodpost'))) {
		showmessage('no_authority_management_operation');
	}

	//初始化用户的分页
	if(submitcheck('makepageorder')) { 
		$query = $_SGLOBAL['db']->query('SELECT pageorder,nid FROM '.tname('spacenews').' WHERE itemid=\''.$itemid.'\' ORDER BY pageorder ASC, nid ASC '); 
		$newpageorder = 1;
		while ($row = $_SGLOBAL['db']->fetch_array($query)) {
			updatetable('spacenews', array('pageorder'=>$newpageorder), array('nid'=>$row['nid']));
			$newpageorder++;
		}
	}
	//更新用户的分页
	$_POST['nid'] = empty($_POST['nid'])?0:intval($_POST['nid']);
	$pageorder = intval($_POST['pageorder']);
	if($pageorder < $page) {  //判断用户修改了页面顺序
		$_SGLOBAL['db']->query('UPDATE '.tname('spacenews').' SET pageorder = pageorder+1 WHERE itemid = '.$itemid.' AND pageorder >='.$pageorder.' AND pageorder < '.$page);
	}elseif($pageorder > $page) {
		$_SGLOBAL['db']->query('UPDATE '.tname('spacenews').' SET pageorder = pageorder-1 WHERE itemid = '.$itemid.' AND pageorder <='.$pageorder.' AND pageorder > '.$page);
	}
	
	//更新用户最新更新时间
	if(empty($itemid)) {
		updatetable('members', array('updatetime'=>$_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));
	}
	
	//第一个页面处理
	if(empty($itemid) || $page == 1) {

		//输入检查
		$_POST['catid'] = intval($_POST['catid']);
		$_POST['customfieldid'] = intval($_POST['customfieldid']);
		$_POST['picid'] = empty($_POST['picid'])?0:intval($_POST['picid']);	//图文资讯标志
				
		//检查输入
		$_POST['subject'] = shtmlspecialchars(trim($_POST['subject']));//标题支持html
		if(strlen($_POST['subject']) < 2 || strlen($_POST['subject']) > 80) {
			showmessage('space_suject_length_error');
		}
		if(empty($_POST['catid'])) {
			showmessage('admin_func_catid_error');
		}

		//自定义信息
		$setcustomfieldtext = empty($_POST['customfieldtext'][$_POST['customfieldid']])?serialize(array()):addslashes(serialize(shtmlspecialchars(sstripslashes($_POST['customfieldtext'][$_POST['customfieldid']]))));

		//TAG处理
		if(empty($_POST['tagname'])) $_POST['tagname'] = '';
		$tagarr = posttag($_POST['tagname']);
		
		//构建数据
		$setsqlarr = array(
			'catid' => $_POST['catid'],
			'subject' => scensor($_POST['subject'], 1),
			'hash' => $_POST['hash'],
			'picid' => $_POST['picid']
		);
		
		$_SGET['folder'] = checkperm('allowdirectpost') ? 1 : (isset($_SGET['folder']) && intval($_SGET['folder']) == 0 ? 0 : 1);

		//标题样式
		empty($_POST['strong'])?$_POST['strong']='':$_POST['strong']=1;
		empty($_POST['underline'])?$_POST['underline']='':$_POST['underline']=1;
		empty($_POST['em'])?$_POST['em']='':$_POST['em']=1;
		empty($_POST['fontcolor'])?$_POST['fontcolor']='':$_POST['fontcolor']=$_POST['fontcolor'];
		empty($_POST['fontsize'])?$_POST['fontsize']='':$_POST['fontsize']=$_POST['fontsize'];
		$setsqlarr['styletitle'] = sprintf("%6s%2s%1s%1s%1s",substr($_POST['fontcolor'], -6),substr($_POST['fontsize'],-4,2),$_POST['em'],$_POST['strong'],$_POST['underline']);

		if($setsqlarr['styletitle'] === '           ') {
			$setsqlarr['styletitle']  = '';
		}
	
		$setsqlarr['digest'] = intval($_POST['digest']);
		$setsqlarr['top'] = intval($_POST['top']);
		$setsqlarr['allowreply'] = intval($_POST['allowreply']);
		$setsqlarr['grade'] = intval($_POST['grade']);
		
		//附件
		if(!empty($_POST['divupload']) && is_array($_POST['divupload'])) {
			$setsqlarr['haveattach'] = 1;
			$picflag = 1;
		} else {
			$setsqlarr['haveattach'] = 0;
		}		
		
		//发布时间
		if(empty($_POST['dateline'])) {
			$setsqlarr['dateline'] = $_SGLOBAL['timestamp'];
		} else {
			$setsqlarr['dateline'] = sstrtotime($_POST['dateline']);
			if($setsqlarr['dateline'] > $_SGLOBAL['timestamp']) {
				$setsqlarr['dateline'] = $_SGLOBAL['timestamp'];
			}
		}

		if(empty($itemid)) {
			//添加数据
			$op = 'add';
			$setsqlarr['tid'] = empty($_POST['tid'])?0:intval($_POST['tid']);
			$setsqlarr['type'] = $type;
			$setsqlarr['uid'] = $_SGLOBAL['supe_uid'];
			$setsqlarr['username'] = $_SGLOBAL['supe_username'];	
			$setsqlarr['lastpost'] = $setsqlarr['dateline'];
			$setsqlarr['fromtype'] = 'adminpost';
			
			if(!$_SGET['folder']) {
				
				//插入数据
				$itemid = inserttable('spaceitems', $setsqlarr, 1);	
				getreward('postinfo');
				//feed
				if(allowfeed() && $_POST['addfeed']) {
					$feed['icon'] = 'comment';
					$feed['title_template'] = 'feed_news_title';
					$feed['body_template'] = 'feed_news_message';
					$subjecturl = geturl('action/viewnews/itemid/'.$itemid);
					
					if(empty($_SCONFIG['siteurl'])) {
						$siteurl = getsiteurl();
						$subjecturl = $siteurl.$subjecturl; 
					}
					$feed['body_data'] = array(
						'subject' => '<a href="'.$subjecturl.'">'.$_POST['subject'].'</a>',
						'message' => cutstr(strip_tags(preg_replace("/\[.+?\]/is", '', $_POST['message'])), 150)
					);
					$picurl = getmessagepic(stripslashes($_POST['message']));
	
					if($picurl && (strpos($picurl, '://') === false)) {
						$picurl = $siteurl.'/'.$picurl;
					}
					if(!empty($picurl)) {
						
						$feed['images'][] = array('url'=>$picurl, 'link'=>$subjecturl);
					}
					
					postfeed($feed);
				}	

				//信息与tag关联处理
				postspacetag('add', $type, $itemid, $tagarr,1);
				
			} else {

				unset($setsqlarr ['styletitle']);
				unset($setsqlarr ['digest']);
				unset($setsqlarr ['top']);
				unset($setsqlarr ['grade']);
				unset($setsqlarr ['tid']);
				
				$setsqlarr ['folder'] = 1;	//待审箱
				$itemid = inserttable('postitems', $setsqlarr, 1);
				postspacetag('add', $type, $itemid, $tagarr, 0);
			}

		} else {
			//更新
			$op = 'update';
			
			if(!$_SGET['folder']) {
				updatetable('spaceitems', $setsqlarr, array('itemid'=>$itemid));
			} else {
				unset($setsqlarr ['styletitle']);
				unset($setsqlarr ['digest']);
				unset($setsqlarr ['top']);
				unset($setsqlarr ['grade']);
				unset($setsqlarr ['tid']);

				$setsqlarr ['folder'] = 1;	//待审箱

				updatetable('postitems', $setsqlarr, array('itemid'=>$itemid));
				
			}
			
			//信息与tag关联处理
			$itemid = empty($_POST['oitemid']) ? $itemid : $_POST['oitemid'];
			postspacetag('update', $type, $itemid, $tagarr, $status);
		}
		
		//附件
		if($setsqlarr['haveattach']) {
			$_SGLOBAL['db']->query('UPDATE '.tname('attachments').' SET isavailable=1, type=\''.$type.'\', itemid='.$itemid.', catid=\''.$_POST['catid'].'\' WHERE hash=\''.$_POST['hash'].'\'');
		}
		
		//内容 图片路径和附件路径处理
		$_POST['message'] = preg_replace_callback("/src\=(.{2})([^\>\s]{10,105})\.(jpg|gif|png)/i", 'addurlhttp', $_POST['message']);
		$_POST['message'] = str_replace('href=\"batch.download.php', 'href=\"'.S_URL.'/batch.download.php', $_POST['message']);
		
		$setsqlarr = array(
			'message' => scensor($_POST['message'], 1),
			'postip' => $_SGLOBAL['onlineip'],
			'customfieldid' => $_POST['customfieldid'],
			'customfieldtext' => $setcustomfieldtext
		);
				
		//相关TAG
		$tagnamearr = array_merge($tagarr['existsname'], $tagarr['nonename']);
		$setsqlarr['relativetags'] = addslashes(serialize($tagnamearr));
	
		//包含tag
		$setsqlarr['includetags'] = postgetincludetags($_POST['message'], $tagnamearr);
	
		//相关阅读
		$setsqlarr['relativeitemids'] = getrelativeitemids($itemid, array('news'));
	
		//额外信息
		$setsqlarr['newsauthor'] = shtmlspecialchars(trim($_POST['newsauthor']));
		$setsqlarr['newsfrom'] = shtmlspecialchars(trim($_POST['newsfrom']));
		$setsqlarr['newsurl'] = shtmlspecialchars(trim($_POST['newsurl']));
		$setsqlarr['newsfromurl'] = shtmlspecialchars(trim($_POST['newsfromurl']));
		$setsqlarr['pageorder'] = $pageorder;
		$setsqlarr['itemid'] = $itemid;
		
		if($op == 'add') {
			
			//添加内容
			$arraymessage = array();
			if(!$_SGET['folder']) {
				$arraymessage = explode('###NextPage###', $setsqlarr['message']);
			} else {
				$arraymessage[] = $setsqlarr['message'];
			}
			
			$firstmessage = 0;
			$insertarr = array();

			foreach($arraymessage as $message) {
				$message = trim($message);
				if($firstmessage == 1){
					unset($setsqlarr['customfieldid']);
					unset($setsqlarr['relativetags']);
					unset($setsqlarr['relativeitemids']);
					unset($setsqlarr['includetags']);
					unset($setsqlarr['customfieldtext']);
				}
				$firstmessage++;
				$setsqlarr['message'] = $message;
				$setsqlarr['pageorder'] = $firstmessage;
				if($firstmessage == 1){
					
					if(!$_SGET['folder']) {
						inserttable('spacenews', $setsqlarr);
					} else {
						inserttable('postmessages', $setsqlarr);
					}
					
				} else {
					$insertarr[] = $setsqlarr;
				}
			}
			if(!empty($insertarr)) {
				$insertkeys = array_keys($insertarr[0]);
				$insertarr_str = array();
				foreach($insertarr as $_index=>$_value) {
					$insertarr_str[] = "('".implode("','",$_value)."')";
				}
				$_SGLOBAL['db']->query("INSERT INTO ".tname('spacenews')." (`".implode('`,`',$insertkeys)."`) VALUES ".implode(',',$insertarr_str));
			}
			ssetcookie('newsauthor', $setsqlarr['newsauthor'], 86400);
			ssetcookie('newsfrom', $setsqlarr['newsfrom'], 86400);
		} else {

			//更新内容
			$insertarr = array();
			$firstmessage = $page;
			if(!$_SGET['folder']) {
				$arraymessage = explode('###NextPage###', $setsqlarr['message']);
			} else{
				$arraymessage[] = $setsqlarr['message'];
			}
			
			foreach($arraymessage as $message) {
				$message = trim($message);
				$setsqlarr['message'] = $message;
				$setsqlarr['pageorder'] = $pageorder;
				if($firstmessage == $_SGET['page']) {
					if(!$_SGET['folder']) {
						updatetable('spacenews', $setsqlarr, array('nid'=>$_POST['nid'], 'itemid'=>$itemid));
					} else {
						updatetable('postmessages', $setsqlarr, array('nid'=>$_POST['nid'], 'itemid'=>$itemid));
					}					
				} else {
					$insertarr[] = $setsqlarr;
				}
				$firstmessage++;
				$pageorder++;
			}
			if(!empty($insertarr)) {

				$insertkeys = array_keys($insertarr[0]);
				$insertarrnow = count($insertarr);
				$_SGLOBAL['db']->query("UPDATE ".tname('spacenews')." SET pageorder = pageorder+".$insertarrnow." WHERE itemid = ".$itemid." AND pageorder >".$_SGET['page']);
				$insertarr_str = array();
				foreach($insertarr as $_index=>$_value) {
					$insertarr_str[] = "('".implode("','",$_value)."')";
				}
				$_SGLOBAL['db']->query("INSERT INTO ".tname('spacenews')." (`".implode('`,`',$insertkeys)."`) VALUES ".implode(',',$insertarr_str));

			}			
		}
		
		if(!$_SGET['folder']) {
			$showurlarr[] = array(geturl('action/viewnews/itemid/'.$itemid.'/php/1'), $alang['spacenews_newspage']);
			$showurlarr[] = array(geturl('action/category/catid/'.$_POST['catid'].'/php/1'), $alang['spacenews_typepage']);
		} else {
			$showurlarr[] = array(CPURL.'?action=spacenews&op=view&itemid='.$itemid, $alang['spacenews_newspage']);
			$showurlarr[] = array(geturl('action/category/catid/'.$_POST['catid'].'/php/1'), $alang['spacenews_typepage']);
		}
		
	} else {
		//额外信息
		$_POST['newsauthor'] = shtmlspecialchars(trim($_POST['newsauthor']));
		$_POST['newsfrom'] = shtmlspecialchars(trim($_POST['newsfrom']));
		$_POST['newsurl'] = isset($_POST['newurl']) ? shtmlspecialchars(trim($_POST['newsurl'])) : '';
		$_POST['newsfromurl'] = shtmlspecialchars(trim($_POST['newsfromurl']));

		//其余分页处理
		$setsqlarr = array(
			'postip' => $_SGLOBAL['onlineip'],
			'itemid' => $itemid,
			'newsauthor' => $_POST['newsauthor'],
			'newsfrom' => $_POST['newsfrom'],
			'newsfromurl' => $_POST['newsfromurl'],
			'newsurl' => $_POST['newsurl'],
		);
		$insertarr = array();
		$arraymessage = explode('###NextPage###', scensor($_POST['message'], 1));
		$firstmessage = $page;
		foreach($arraymessage as $message) {
			$message = trim($message);
			$setsqlarr['message'] = $message;
			$setsqlarr['pageorder'] = $pageorder;
			if($firstmessage == $_SGET['page']) {
				updatetable('spacenews', $setsqlarr, array('nid'=>$_POST['nid'], 'itemid'=>$itemid));
			} else {
				$insertarr[] = $setsqlarr;
			}
			$firstmessage++;
			$pageorder++;
		}
		if(!empty($insertarr)) {
			$insertkeys = array_keys($insertarr[0]);
			$insertarrnow = count($insertarr);
			$_SGLOBAL['db']->query("UPDATE ".tname('spacenews')." SET pageorder = pageorder+".$insertarrnow." WHERE itemid = ".$itemid." AND pageorder >".$_SGET['page']);
			$insertarr_str = array();
			foreach($insertarr as $_index=>$_value) {
				$insertarr_str[] = "('".implode("','",$_value)."')";
			}
			$_SGLOBAL['db']->query("INSERT INTO ".tname('spacenews')." (`".implode('`,`',$insertkeys)."`) VALUES ".implode(',',$insertarr_str));

		}

		$showurlarr[] = array(geturl('action/viewnews/itemid/'.$itemid.'/php/1'), $alang['spacenews_newspage']);
		$showurlarr[] = array(geturl('action/category/catid/'.$_POST['catid'].'/php/1'), $alang['spacenews_typepage']);
	}
	
}


if(empty($_GET['op'])) {

	if(empty($showurlarr)) {
		$catarr = getcategory($type);	//CATEGORY
		$allcatarr = getcategory('', '|----', $_SGET['catid']);
		$rtarr = array();	//LIST
		
		//LIST VIEW
		if(!empty($_SGET['searchid'])) {

			$wheresqlstr = ' itemid = \''.$_SGET['searchid'].'\'';
			
		} else {
			
			$wheresqlarr = array();
			$wheresqlarr['type'] = $type;
			if($_SGET['folder']) $wheresqlarr['folder'] = $_SGET['folder'];
			if(!empty($_SGET['catid'])) $wheresqlarr['catid'] = $_SGET['catid'];
			if(!empty($_SGET['itemtypeid'])) $wheresqlarr['itemtypeid'] = $_SGET['itemtypeid'];
			if(!empty($_SGET['digest'])) $wheresqlarr['digest'] = $_SGET['digest'];
			if(!empty($_SGET['subtype'])) $wheresqlarr['subtype'] = $_SGET['subtype'];
			if(!empty($_SGET['fromtype'])) $wheresqlarr['fromtype'] = $_SGET['fromtype'];
			$wheresqlstr = getwheresql($wheresqlarr);
			if(!empty($_SGET['searchkey'])) $wheresqlstr .= ' AND subject LIKE \'%'.$_SGET['searchkey'].'%\'';
			
		}

		$tablename = $_SGET['folder'] ? 'postitems' : 'spaceitems';
		$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname($tablename).' WHERE '.$wheresqlstr);
		$listcount = $_SGLOBAL['db']->result($query, 0);
		$multipage = '';
		$listarr = $hasharr = array();
		if($listcount) {			
			$order = empty($_SGET['order']) ? ($_SGET['folder'] ? 'dateline DESC' : 'top DESC, dateline DESC') : $_SGET['order'].' '.$_SGET['sc'];
			$start = ($_SGET['page']-1)*$perpage;
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($tablename).' WHERE '.$wheresqlstr.' ORDER BY '.$order.' LIMIT '.$start.','.$perpage);
			while ($item = $_SGLOBAL['db']->fetch_array($query)) {
				$hasharr[] = $item['hash'];
				$listarr[] = $item;
			}
			$multipage = multi($listcount, $perpage, $_SGET['page'], $theurl.$urlplus);
		}

		$rtarr = array(
			'listcount'	=>	$listcount,
			'multipage'	=>	$multipage,
			'listarr'	=>	$listarr,
			'hasharr'	=>	$hasharr
		);

	}
	
} elseif ($_GET['op'] == 'edit') {
	
	//权限
	if(!(checkperm('manageditpost') || checkperm('managemodpost'))) {
		showmessage('no_authority_management_operation');
	}

	$multipage = $alang['spacenews_title_message_page_none'];	//GET ONE VALUE
	$itemid = intval($_GET['itemid']);

	if(!$_SGET['folder']) {
		
		if(empty($_GET['page'])) $_GET['page'] = 1;
		$page = intval($_GET['page']);
		if($page < 1 || empty($_GET['nextpage'])) $page = 1;
		$pageorderarr = array();
		$makepageorder = 0;

		$query = $_SGLOBAL['db']->query('SELECT pageorder FROM '.tname('spacenews').' WHERE itemid=\''.$itemid.'\' ORDER BY pageorder ASC, nid ASC '); 
		while ($pageorder = $_SGLOBAL['db']->fetch_array($query)) {
			$pageorderarr[] = intval($pageorder['pageorder']);
		}
		
		$listcount = count($pageorderarr);
		if(md5(serialize($pageorderarr)) != md5(serialize(range(1,$listcount)))) {
			$pageorderarr = range(1,$listcount);
			$makepageorder = 1;
		}
		if($page > $listcount) $page = 1;
		if(!empty($_GET['last'])) $page = $listcount;
		if($page < 1) $page = 1;
		if($listcount > 1) $multipage = multi($listcount, 1, $page, $theurl.'&op=edit&nextpage=1&itemid='.$itemid);
	
		$query = $_SGLOBAL['db']->query('SELECT ii.*, i.* FROM '.tname('spacenews').' ii LEFT JOIN '.tname('spaceitems').' i ON i.itemid=ii.itemid WHERE ii.itemid=\''.$itemid.'\' ORDER BY ii.pageorder ASC, ii.nid ASC LIMIT '.($page-1).', 1');
		$thevalue = $_SGLOBAL['db']->fetch_array($query);
		
		if($page == 1) {
			$thevalue['allowmax'] = $allowmax;
			if(empty($thevalue['hash'])) $thevalue['hash'] = $hashstr;
			//TAG
			$thevalue['tagname'] = gettagname($itemid, '1');	//TAG
			//UPLOAD
			$thevalue['uploadarr'] = array();
			if(!empty($thevalue['haveattach'])) {
				$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('attachments').' WHERE itemid=\''.$itemid.'\' ORDER BY dateline');
				while ($attach = $_SGLOBAL['db']->fetch_array($query)) {
					$thevalue['uploadarr'][] = $attach;
				}
				if(empty($thevalue['uploadarr'])) {
					$setsqlarr = array('haveattach' => 0);
					$wheresqlarr = array('itemid' => $itemid);
					updatetable('spaceitems', $setsqlarr, $wheresqlarr);
				}
			}
		}

	} else {
		
		$query = $_SGLOBAL['db']->query('SELECT ii.*, i.* FROM '.tname('postitems').' ii LEFT JOIN '.tname('postmessages').' i ON i.itemid=ii.itemid WHERE ii.itemid=\''.$itemid.'\'');
		$thevalue = $_SGLOBAL['db']->fetch_array($query);
		$page = 1;
		
		$spacetag_itemid	=	empty($thevalue['oitemid']) ? $itemid : $thevalue['oitemid'];
		$thevalue['tagname'] = gettagname($spacetag_itemid, '0');	//TAG
	}
	$type = $thevalue['type'];
	
} elseif($_GET['op'] == 'add') {
	
	if(!(checkperm('managemodpost') || checkperm('manageeditpost'))) {
		showmessage('spacenews_no_popedom_add');
	}

	//ONE ADD
	$thevalue = array(
		'itemid' => 0,
		'itemtypeid' => 0,
		'catid' => $_SGET['catid'],
		'type' => $type,
		'subject' => '',
		'dateline' => $_SGLOBAL['timestamp'],
		'digest' => '0',
		'top' => '0',
		'allowreply' => '1',
		'hash' => $hashstr,
		'message' => '',
		'tagname' => '',
		'uploadarr' => array(),
		'allowmax' => $allowmax,
		'customfieldid' => 0,
		'customfieldtext' => '',
		'haveattach' => 0,
		'replynum' => 0,
		'tid' => 0,
		'grade' => 0,
		'picid' => 0,
		'hottagarr' => array(),
		'lasttagarr' => array()
	);
	
	$thevalue['newsurl'] = '';
	$thevalue['nid'] = 0;
	$thevalue['newsfromurl'] = '';
	
	$page =1;
	$listcount = 1;
	$multipage = '';

	//论坛导入
	$tid = 0;
	if(!empty($_GET['tid'])) $tid = intval($_GET['tid']);
	if(!empty($tid) && discuz_exists()) {
		if($_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query('SELECT itemid FROM '.tname('spaceitems').' WHERE tid=\''.$tid.'\' AND type=\''.$type.'\' LIMIT 1'))) {
			showmessage('bbsimport_imported');
		}
		include_once(S_ROOT.'./include/bbsimport.inc.php');
	}

} elseif ($_GET['op'] == 'view') {
	
	if(empty($_GET['page'])) $_GET['page'] = 1;
	$itemid = intval($_GET['itemid']);
	
	$query = $_SGLOBAL['db']->query('SELECT ii.*, i.* FROM '.tname('postitems').' ii LEFT JOIN '.tname('postmessages').' i ON i.itemid=ii.itemid WHERE ii.itemid=\''.$itemid.'\'');
	$news = $_SGLOBAL['db']->fetch_array($query);
	$news['message'] = shtmlspecialchars($news['message']);
	$news['message'] = preg_replace("/&lt;(\/)*(p|br)(?:\s*)(\/)*&gt;/", '<\1\2>', $news['message']);
	if(empty($news)) {
		showmessage('prefield_none_exists');
	}
	
	$news['custom'] = array('name'=>'', 'key'=>array(), 'value'=>array());
	if($page == 1 && !empty($news['customfieldid'])) {
		$news['custom']['value'] = unserialize($news['customfieldtext']);
		if(!empty($news['custom']['value'])) {
			foreach ($news['custom']['value'] as $key => $value) {
				if(is_array($value)) {
					$news['custom']['value'][$key] = implode(', ', $value);
				}
			}
		}
		$query = $_SGLOBAL['db']->query('SELECT name, customfieldtext FROM '.tname('customfields').' WHERE customfieldid=\''.$news['customfieldid'].'\'');
		$fields= $_SGLOBAL['db']->fetch_array($query);
		$news['custom']['name'] = $fields['name'];
		$news['custom']['key'] = unserialize($fields['customfieldtext']);
	}

	$categories = getcategory($type);
	$viewhtml = '';
	$viewhtml .= '<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">';
	$viewhtml .= '<tr>'."\n";
	$viewhtml .= '<th>'.$alang['spacenews_title_subject'].'</th>';
	$viewhtml .= '<td>'.shtmlspecialchars($news['subject']).'</td>';
	$viewhtml .= '</tr>'."\n";
	$viewhtml .= '<tr>'."\n";
	$viewhtml .= '<th>'.$alang['check_catname'].'</th>';
	$viewhtml .= '<td>'.$categories[$news['catid']]['name'].'</td>';
	$viewhtml .= '</tr>'."\n";
	$viewhtml .= '<tr>'."\n";
	$viewhtml .= '<th>'.$alang['check_dateline'].'</th>';
	$viewhtml .= '<td>'.sgmdate($news['dateline']).'</td>';
	$viewhtml .= '</tr>'."\n";
	if($multipage) {
		$viewhtml .= '<tr>'."\n";
		$viewhtml .= '<th>'.$alang['spacenews_title_message_page'].'</th>';
		$viewhtml .= '<td>'.$multipage.'</td>';
		$viewhtml .= '</tr>'."\n";
	}
	if(empty($news['custom']['key'])) {
		foreach ($news['custom']['key'] as $key=>$value) {
			$viewhtml .= '<tr>'."\n";
			$viewhtml .= '<th>'.$value['name'].'('.$news['custom']['name'].')</th>';
			$viewhtml .= '<td>'.shtmlspecialchars($news['custom']['value'][$key]).'</td>';
			$viewhtml .= '</tr>'."\n";
		}
	}
	
	$viewhtml .= '<tr>'."\n";
	$viewhtml .= '<th>'.$alang['spacenews_title_message'].'</th>';
	$viewhtml .= '<td>'.$news['message'].'</td>';
	$viewhtml .= '</tr>'."\n";
	$viewhtml .= '</table>';
	$viewhtml .= label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$theurl, 'other'=>' onSubmit="return listsubmitconfirm(this)"'));
	$viewhtml .= '<input value="delete" name="operation" type="hidden" /><input value="'.$news['folder'].'" name="folder" type="hidden" /><input value="0" name="opdelete" type="hidden" /><input name="listvaluesubmit" type="hidden" value="yes" /><input type="hidden" value="'.$itemid.'" name="item[]"/>';
	$viewhtml .= '<div class="buttons">';
	$viewhtml .= label(array('type'=>'button-submit', 'name'=>'listsubmit', 'value'=>$alang['completely_erased']));
	$viewhtml .= '</div>'."\n";
	$viewhtml .= label(array('type'=>'form-end'));

} elseif ($_GET['op'] == 'addpage') {

	if(!(checkperm('managemodpost') || checkperm('manageeditpost'))) {
		showmessage('spacenews_no_popedom_add');
	}

	//ONE DELETE
	$itemid = intval($_GET['itemid']);
	$setsqlarr = array('itemid' => $itemid);
	if(!empty($itemid)) {
		$query = $_SGLOBAL['db']->query("SELECT max(pageorder) as bid FROM ".tname('spacenews')." WHERE itemid =".$itemid);
		$setsqlarr['pageorder'] = $_SGLOBAL['db']->result($query, 0) + 1;
		inserttable('spacenews', $setsqlarr);
		header('Location: '.$theurl.'&op=edit&itemid='.$itemid.'&last=1');
	} else {
		showmessage('spacenews_page_need_submit');
	}

} elseif ($_GET['op'] == 'deletepage') {
	
	if(!(checkperm('managemodpost') || checkperm('manageeditpost'))) {
		showmessage('spacenews_no_popedom_add');
	}
	
	$itemid = intval($_GET['itemid']);
	$nid = intval($_GET['nid']);
	$pageorder = intval($_GET['pageorder']);
	if(!empty($itemid) && !empty($nid)) {
		$_SGLOBAL['db']->query('UPDATE '.tname('spacenews').' SET pageorder = pageorder-1 WHERE itemid=\''.$itemid.'\' AND pageorder >\''.$pageorder.'\'');
		$_SGLOBAL['db']->query('DELETE FROM '.tname('spacenews').' WHERE itemid=\''.$itemid.'\' AND nid=\''.$nid.'\'');
	}
	header('Location: '.$theurl.'&op=edit&itemid='.$itemid.'&last=1');
	
} elseif ($_GET['op'] == 'deleteallwaste') {
	
	if(!(checkperm('managemodpost') || checkperm('managedelpost'))) {
		showmessage('spacenews_no_popedom_add');
	}

	$catarr = getcategory($type);
	$_GET['delnum']= empty($_GET['delnum'])?0:intval($_GET['delnum']);
	$dellistarr = $wheresqlarr = array();
	$wheresqlarr['type'] = $type;
	$wheresqlarr['folder'] = 2;
	$wheresqlstr = getwheresql($wheresqlarr);

	if(empty($_GET['all'])) {
		$all = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('postitems').' WHERE '.$wheresqlstr), 0);
	} else {
		$all = intval($_GET['all']);
	}
	$allitemids = empty($_GET['all'])?$rtarr['listcount']:intval($_GET['all']);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('postitems').' WHERE '.$wheresqlstr.' ORDER BY dateline DESC LIMIT 0,'.$perpage);
	while ($item = $_SGLOBAL['db']->fetch_array($query)) {
		$dellistarr[] = $item['itemid'];
	}
	$dels = count($dellistarr);

	$delnum = $_GET['delnum']+$dels;

}

//MENU
$active[] = array(0=>'', 1=>'', 2=>'', 'add'=>'', 'edit'=>'');
if($_GET['op'] == 'add') {
	$active['add'] = ' class="active"';
} elseif($_GET['op'] == 'edit') {
	$active['edit'] = ' class="active"';
} elseif(!$_SGET['folder']) {
	$active[0] = ' class="active"';
} elseif($_SGET['folder'] == 1 && empty($thevalue)) {
	$active[1] = ' class="active"';
} elseif($_SGET['folder'] == 2 && empty($thevalue)) {
	$active[2] = ' class="active"';
}

//清空垃圾箱信息
if(is_array($dellistarr) && !empty($dellistarr)) {

	$itemidstr = simplode($dellistarr);
	deleteitems('itemid', $itemidstr);
	$residual = $all-$delnum;
	echo label(array('type'=>'table-start', 'class'=>'listtable'));
	echo '<tr>';
	echo '<th>'.$alang['delete_all_message_0'].$all.$alang['delete_all_message_1'].$residual.$alang['delete_all_message_3'].'</th>';
	echo '</tr>';
	echo label(array('type'=>'table-end'));
	if($residual) {
		jumpurl($newurl.'&op=deleteallwaste&all='.$all.'&delnum='.$delnum, 1000, 'meta');
	} else {
		jumpurl($newurl, 1000, 'meta');
	}
}

//THE VALUE SHOW
if($thevalue) {

	//缩略图
	//CUSTOM FIELD
	if($page == 1) {
	
		$cfhtmlselect = array('0'=>$alang['space_customfield_none']);
		
		$wheresqlarr = array();
		$wheresqlarr['type'] = $type;
		$plussql = 'ORDER BY displayorder';
		$allcfarr = selecttable('customfields', array(), $wheresqlarr, $plussql);

		$cfhtml = '';
		$tbodynum = 0;
		foreach ($allcfarr as $cfkey => $cfvalue) {
			if(empty($thevalue['customfieldid'])) {
				if($cfvalue['isdefault']) {
					$thevalue['customfieldid'] = $cfvalue['customfieldid'];
				}
			}
			$cfhtmlselect[$cfvalue['customfieldid']] = $cfvalue['name'];
			$cfarr = unserialize($cfvalue['customfieldtext']);
			if(is_array($cfarr) && $cfarr) {
				if(!empty($thevalue['customfieldid']) && $thevalue['customfieldid'] == $cfvalue['customfieldid']) {
					$tbodydisplay = '';
					if(empty($thevalue['customfieldtext'])) {
						$thecfarr = array();
					} else {
						$thecfarr = unserialize($thevalue['customfieldtext']);
					}
				} else {
					$tbodydisplay = 'none';
					$thecfarr = array();
				}
				$tbodynum++;
				$cfhtml .= '<tbody id="cf_'.$tbodynum.'" style="display:'.$tbodydisplay.'">';
				
				foreach ($cfarr as $ckey => $cvalue) {
					$inputstr = '';
					if(empty($thecfarr[$ckey])) $thecfarr[$ckey] = '';
					$cfoptionarr = array();
					if($cvalue['type'] == 'select' || $cvalue['type'] == 'checkbox') {
						$cfoptionstr = $cvalue['option'];
						$coarr = explode("\n", $cfoptionstr);
						$coarr = sarray_unique($coarr);
						foreach ($coarr as $covalue) {
							$covalue = trim($covalue);
							$cfoptionarr[$covalue] = $covalue;
						}
					}
					switch ($cvalue['type']) {
						case 'input':
							$inputstr = '<input name="customfieldtext['.$cfvalue['customfieldid'].']['.$ckey.']" type="text" size="30" value="'.$thecfarr[$ckey].'" />';
							break;
						case 'textarea':
							$inputstr = '<textarea name="customfieldtext['.$cfvalue['customfieldid'].']['.$ckey.']" rows="5" cols="60">'.$thecfarr[$ckey].'</textarea>';
							break;
						case 'select':
							$inputstr = getselectstr('customfieldtext['.$cfvalue['customfieldid'].']['.$ckey.']', $cfoptionarr, $thecfarr[$ckey]);
							break;
						case 'checkbox':
							$inputstr = getcheckboxstr('customfieldtext['.$cfvalue['customfieldid'].']['.$ckey.']', $cfoptionarr, $thecfarr[$ckey]);
							break;
					}
					$cfhtml .= '<tr><th>'.$cvalue['name'].'</th><td>'.$inputstr.'</td></tr>';
				}
				$cfhtml .= '</tbody>';
			}
		}
	
		//CATEGORIES
		$clistarr = getcategory($type);
		$categorylistarr = array('0'=>array('pre'=>'', 'name'=>'------'));
		foreach ($clistarr as $key => $value) {
			$categorylistarr[$key] = $value;
		}
		
	}

	//PRE FIELD
	$rarr = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('prefields').' WHERE type=\''.$type.'\'');
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$rarr[$value['field']][] = $value;
	}
	$prefieldarr = $rarr;

	//读取最后一次提交值
	if(!empty($_COOKIE[$_SC['cookiepre'].'newsauthor'])) {
		array_unshift($prefieldarr['newsauthor'], array('id' => 0, 'type' => 'news', 'field' => 'newsauthor', 'value' => $_COOKIE[$_SC['cookiepre'].'newsauthor'], 'isdefault' => 1));
	}
	if(!empty($_COOKIE[$_SC['cookiepre'].'newsfrom'])) {
		array_unshift($prefieldarr['newsfrom'], array('id' => 0, 'type' => 'news', 'field' => 'newsfrom', 'value' => $_COOKIE[$_SC['cookiepre'].'newsfrom'], 'isdefault' => 1));
	}

	//NEWS AUTHOR
	$newsauthorstr = prefieldhtml($thevalue, $prefieldarr, 'newsauthor', 1, '20');
	
	//NEWS FROM
	$newsfromstr = prefieldhtml($thevalue, $prefieldarr, 'newsfrom', 1, '20');

	if(!empty($thevalue['itemid'])) {
		$optext = '<a href="'.$theurl.'&op=addpage&itemid='.$thevalue['itemid'].'"><img src="admin/images/icon_folder.gif" align="absmiddle" border="0" /> '.$alang['spacenews_title_message_op_add'].'</a>';
	} else {
		$optext = $alang['spacenews_page_need_submit'];
	}
	if($listcount > 1) {
		$optext .= ' &nbsp;&nbsp; <a href="'.$theurl.'&op=deletepage&itemid='.$thevalue['itemid'].'&nid='.$thevalue['nid'].'&pageorder='.$thevalue['pageorder'].'"><img src="admin/images/icon_folder3.gif" align="absmiddle" border="0" /> '.$alang['spacenews_title_message_op_delete'].'</a>';
	}
	
	$thevalue['subject'] = shtmlspecialchars($thevalue['subject']);
	$mktitlestyle = empty($thevalue['styletitle']) ? '' : mktitlestyle($thevalue['styletitle']);

	//NEWS MESSAGE
	$thevalue['message'] = addcslashes($thevalue['message'], '/"\\');
	$thevalue['message'] = str_replace("\r", '\r', $thevalue['message']);
	$thevalue['message'] = str_replace("\n", '\n', $thevalue['message']);

	$count = count($thevalue['uploadarr']);
	if(empty($thevalue['noinsert'])) {
		$thevalue['noinsert'] = 0;
		$inserthtml = getuploadinserthtml($thevalue['uploadarr']);
	} else {
		$inserthtml = getuploadinserthtml($thevalue['uploadarr'], 1);
	}
	if(empty($thevalue['allowtype'])) $thevalue['allowtype'] = '';
	
	if($listcount > 1) {
		$pageorders =  array();

		foreach($pageorderarr as $key => $value) {
			if($key == 0)  {
				$pageorders[$value] = $alang['spacenews_title_message_start'];
			} elseif($key == count($pageorderarr)-1) {
				$pageorders[$value] = $alang['spacenews_title_message_end'];
			} else {
				$pageorders[$value] = $alang['spacenews_title_message_no1'].$value.$alang['spacenews_title_message_no2'];
			}
		}
	}

}

//view
if($viewhtml) {
	echo $viewhtml;
} else {
	include template('admin/tpl/spacenews.htm', 1);
	//完成后的url

	if(is_array($showurlarr) && $showurlarr) {
		echo label(array('type'=>'div-start'));
		echo label(array('type'=>'table-start'));
		foreach ($showurlarr as $url) {
			$turl = $url[0];
			echo '<tr><td><a href="'.$turl.'" target="_blank"><strong>'.$url[1].'</strong> '.$alang['spaceblog_viewpage_success'].'</a></td></tr>';
		}
		echo label(array('type'=>'table-end'));
		echo label(array('type'=>'div-end'));
		
		echo '
		<div class="buttons">
		<input type="button" name="continuesubmit4" value="'.$alang['spacenews_op_add'].'" onclick="window.location.href=\''.$theurl.'&type='.$type.'&op=add\'"> 
		<input type="button" name="continuesubmit3" value="'.$alang['spacenews_title_message_op_add'].'" onclick="window.location.href=\''.$theurl.'&type='.$type.'&op=addpage&itemid='.$itemid.'\'"> 
		<input type="button" name="continuesubmit2" value="'.$alang['continue_the_current_editorial_page'].'" onclick="window.location.href=\''.$theurl.'&op=edit&folder='.$_SGET['folder'].'&itemid='.$itemid.'&page='.$page.'\'"> 
		<input type="button" name="continuesubmit1" value="'.$alang['common_continue_list_edit'].'" onclick="window.location.href=\''.$theurl.'&type='.$type.'\'"> 
		</div>';
	}
}

?>
