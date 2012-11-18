<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: main.lang.php 13513 2009-11-26 07:34:15Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$lang = array (
	'index' => '首页',
	'news' => '资讯',
	'uchblog' => 'uch日志',
	'bloglist' => '日志列表',
	'blogdetail' => '日志查看页',
	'bbs' => '论坛',
	'top' => '排行榜',
	'hottop' => '热度排行榜',
	'uchimage' => 'uch相册',
	'imagelist' => '相册列表',
	'imagedetail' => '图片查看',
	'tobbs' => '进入论坛',
	'favorite' => '收藏',
	'site_map' => '站点地图',
	'site_link' => '友情链接',
	'pushed_to_the_feed' => '加入事件',
	'user_info' => '用户信息',
	'yes'=>'是',
	'no'=>'否',
	'list' => '列表',
	'category' => '信息列表',
	'year' => '年',
	'month' => '月',
	'edit' => '编辑',
	'delete' => '删除',
	'insert' => '插入大图',
	'insertsmall' => '插入缩略图',
	'slice' => '裁切缩略图',
	'insert_attachments' => '插入附件',
	'login' => '登录站点',
	'logout' => '退出',
	'welcome' => '您好',
	'announcement' => '站点公告',
	'site_reg' => '注册',
	'poll'=>'投票',
	'new'=>'全新',
	'subject'=>'标题',
	'dateline'=>'发布时间',
	'location'=>'所在地',
	'file_size' => '文件大小',
	'link_url'=>'网址',
	'tag' => '标签',
	'email' => '常用E-Mail',
	'document_type_error'=>'文件类型错误，允许的图片文件后缀是',
	'next_page' => '下一页',
	'pre_page' => '上一页',
	'pannel_contribution' =>'投稿',
	'hour' => '小时',
	'minute' => '分钟',
	'second' => '秒',
	'now' => '现在',
	'before' => '前',
	'mars' => '火星',
	'visitor' => '网友',
	'from_the_original_note' => '的原贴：',

	//function/item.func.php
	'not_allowed_to_belong_to_the_following_tag' => '对不起，本站不允许使用以下TAG',
	//function/common.func.php
	'the_page_can_be_updated_immediately_hits' => '点击可以立即更新本页面',
	'close' => '关闭',

	//include/space_nospace.inc.php
	'user_group' => '用户组',
	'see_more' => '查看更多',
	'view' => '查看',
	'reply' => '评论',

	//include/space_template.inc.php
	'score' => '评分',
	'search' => '搜索',
	'more' => '更多',
	'manage' => '管理',
	'photo_upload' => '上传图片',
	'last_reply' => '最后评论',
	'thread' => '帖子',
	
	//include/bbsimport.inc.php
	'bbsimport_self' => '您只能指定自己的论坛主题进行导入',
	'block_image'=>'(图)',

	//spacelist.php
	'visitors' => '访客',

	//common.func.php
	'quote' => '引用',
	'rate_pre' => '评',
	'fen' => '分',
	'admin_login' => '管理员点击此处登录',
	'site_close' => '站点临时关闭，请稍后再访问',
	'tag_match' => '§|№|☆|★|○|●|◎|◇|◆|□|■|△|▲|※|→|←|↑|↓|〓|＃|＆|＠|＼|︿|＿|￣|〖|〗|【|】|（|）|〔|〕|｛|｝|．|’|‘|”|“|》|《|〉|〈|〕|〔|‘|’|“|”|々|～|‖|∶|”|’|‘|｜|¨|ˇ|ˉ|·|－|…|！|？|：|；|，|、|。| | |\~|\.|\!|\@|\#|\\\$|\%|\^|\&|\*|\(|\)|\+|\=|\{|\}|\||\[|\]|\\|\:|\;|\"|\'|\<|\,|\>|\?|\/|\s',

	//modelview.php
	'details' => '详细信息',
	
	//modelpost.php
	'photo_title' => '标题图片',
	'check_username' => '发布者',
	'online_contribution' => '在线投稿',
	'system_catid' => '系统分类',
	'content' => '内容',
	'common_reset' => '重置',	
	'common_submit' => '提交保存',
	'verification_code' => '验证码',
	'changge_verification_code' => '换一张',

	//m.php
	'check_order' => '选择排序方式',
	'model_dateline_desc' => '按发布时间 从晚到早',
	'model_dateline_asc' => '按发布时间 从早到晚',
	'model_viewnum_desc' => '按查看数 从多到少',
	'model_viewnum_asc' => '按查看数 从少到多',
	'model_rates_desc' => '按评分 从高到低',
	'model_rates_asc' => '按评分 从低到高',
	'model_grade_desc' => '按审核等级 从高到低',
	'model_grade_asc' => '按审核等级 从低到高',
	'classification' => '分类',
	'fromdate_0' => '发布时间: 从',
	'fromdate_1' => '到',
	'login_succeed' => '操作完成，您已经成功登录站点系统了',
	'logout_succeed' => '操作完成，您已经成功退出站点系统了',

	//source/do_lostpasswd.php
	'get_passwd_subject' => '取回密码邮件',
	'get_passwd_message' => '您只需在提交请求后的三天之内，通过点击下面的链接重置您的密码：<br />\\1<br />(如果上面不是链接形式，请将地址手工粘贴到浏览器地址栏再访问)<br />上面的页面打开后，输入新的密码后提交，之后您即可使用新的密码登录了。',
	
	//viewthread.php
	'view_thread' => '查看帖子',
	
	//function/news.func.php
	'comment_elevator' => '高层电梯：',
	'comment_floor_hide' => '部分楼层隐藏中...',
	'comment_floor_up_title' => '上翻39层',
	'comment_floor_up' => '上',
	'comment_floor_down_title' => '下翻39层',
	'comment_floor_down' => '下',
	'comment_floor_total' => '本楼共有',
	'comment_floor_total_2' => '层！请乘坐电梯浏览，每次显示39层，顶层和后9层保持不变。',
	'comment_floor_repeat' => '已经隐藏重复盖楼',
	'comment_floor_view_repeat' => '点击展开',
	
	//api/uc.php
	'credit' => '积分',
	'credit_unit' => '个'

);

?>