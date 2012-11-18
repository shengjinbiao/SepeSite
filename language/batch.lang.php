<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.lang.php 13493 2009-11-11 06:15:33Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$blang = array (
	'quote' => '引用于',
	'delete' => '删除',
	'publish' => '发布于',
	'message_no_reply' => '还没有评论信息',
	'message_no_permission' => '对不起，您没有权限进行本操作',
	'message_delete_ok' => '恭喜，删除成功了，页面信息将在下次浏览时更新',
	'mail_title' => '我推荐个信息，希望好朋友能喜欢',
	'message_mail_ok' => '恭喜，推荐给好友成功了',
	'message_mail_error' => '邮件发送失败，请联系管理员',
	'mail_c1' => '你好！我发现了一个挺好的网站，认为很有价值，特推荐给你。',
	'mail_c2' => '地址',
	'mail_c3' => '希望你能喜欢。',
	'mail_c4' => '你好！我在',
	'mail_c5' => '上面发现了一个挺好的信息，认为很有价值，特推荐给你。'."\n\n".'标题:',
	'mail_c6' => '希望你能喜欢。',
	'mail_add' => '好友邮箱',
	'mail_content' => '推荐内容',
	'mail_submit' => '推荐给好友',
	'mail_close' => '关闭窗口',
	'check_grade' => '审核等级',

	//图片上传 batch.insertimage.php
	'insert_photo_url' => '插入图片URL',
	'insert_photo_upload' => '插入上传图片',

	//管理 batch.manage.php
	'management' => '管理',
	'open' => '展开',
	'normal_release' => '正常发布',
	'delete_recovery' => '删除回收',
	'lock' => '锁定',
	'your_position' => '您的位置',
	'front_page' => '首页',
	'normal' => '正常',
	'transit' => '暂存',
	'delete' => '删除',	
	'subject' => '标题',
	'author' => '作者',
	'type' => '类型',
	'classification' => '分类',
	'dateline' => '发布时间',
	'lastpost' => '最后评论',
	'viewnum' => '查看数',
	'replynum' => '评论数',
	'num' => '数',
	'examination_grades' => '审核等级',
	'state' => '状态',	
	'edit' => '编辑',
	'space_allowreply_1' => '允许评论',
	'space_allowreply_0' => '不允许评论',
	'management_comments'=>'评论管理',

	//用户面板 batch.panel.php
	'welcome' => '您好',
	'my_space' => '我的主页',
	'my_center' => '个人中心',
	'forum_visit' => '论坛',
	'home_visit' => '家园',
	'safe_logout' => '退出',
	'user_login' => '用户登录',
	'user_panel' => '用户面板',
	'username' => '用户名',
	'password' => '密&nbsp;&nbsp;&nbsp;码',
	'login' => '登录',
	'registration' => '注册新用户',
	'find_passwords' => '找回密码',
	'i_remember' => '记住我',
	'more_serach' => '高级搜索',
	
	//搜索 batch.search.php
	'search' => '搜索',
	
	//TAG秀 batch.tagshow.php
	'parameter_error' => '参数错误',
	'close' => '关闭',
	'see_more' => '查看更多',
	'not_found_the_tag' => '没有找到该tag',
	
	//上传 batch.upload.php
	'successfully_deleted_files' => '指定的文件删除成功了',
	'unable_to_complete_this_craft' => '系统错误，无法完成此次上传，请联系管理员',
	'the_number_has_reached_maximum' => '允许上传文件数目已经达到最大值，请先删除已上传的文件',
	'failure_to_obtain_upload_file_size' => '获取上传文件大小失败，请选择其他文件上传',
	'upload_not_allow_this_type_of_resources' => '出错了，上传的文件名后缀不在允许上传的范围内',
	'file_size_exceeded_the_permissible_scope' => '上传的文件大小超过允许的范围，请返回检查',
	'uploading_files_failure' => '上传文件失败，请您稍后尝试重新上传',
	'upload_document_has_been_successful' => '指定的文件已经成功上传',
	'unable_to_access_remote_documents' => '无法获取远程文件或文件太大，请重新尝试',
	'upload_not_meet_requirements' => '个文件不符合上传要求无法上传，其余文件上传成功',
	'upload_other_space' => '上传文件失败或者您目前拥有的上传空间已满',

	//查看书签 batch.viewlink.php
	'copy' => '复制',
	'time' => '时间',
	'already' => '已有',
	'no_login' => '对不起，请您先登录系统后再进行本操作',
	'system_error' => '对不起，您的操作不正确，请检查您的操作',
	'no_reply' => '对不起，您没有权限对该主题进行评分，请返回',
	'rates_succeed' => '恭喜，您进行的评分操作成功完成了',
		
	//batch.comment.php
	'the_model_can_not_detect_comments' => '无法在该模式下查看更多评论',
	'not_found' => '出错了，您请求的页面没有找到',
	
	//batch.common.php
	'admincp_header_check' => '等级审核',
	'from_the_original_note' => '原文由',
	'at' => '于',
	'mars' => '火星',
	'visitor' => '网友',
	'released' => '发表',
	'the_score_was_not_correct_designation' => '对不起,指定的评分范围不正确',
	'information_was_not_scoring' => '对不起,指定的信息不能被评分',
	'visitors_can_participate_score' => '对不起,游客不能参与评分,请登录',
	'not_on_their_scores' => '对不起,您本人不能对自己的信息进行评分',
	'only_friends_can_score' => '对不起,只有作者指定的好友才能参与评分',
	'have_too_much_commentary' => '对不起,您已经评过分了,不能重复评分',
	'have_too_much_commentary_model' => '对不起,您已经顶过了,不能重复顶',
	'extract_the_contents_of_success' => '萃取内容成功',
	'extract_the_contents_of_failure' => '萃取内容失败',
	'remote_download_complete_picture' => '远程图片下载完成',
	'not_in_keeping_with_the_long_range_picture' => '远程没有符合的图片',
	'information_has_been_reported' => '该信息已经被举报，正在等待管理员处理!',
	'not_a_malicious_report' => '管理员认为该信息没有违规，请不要恶意举报!',
	'reported_success' => '举报成功，请等待管理员的处理!',
	'reply' => '回复',
	'error_email_empty' => '邮箱格式错误或为空',

	//batch.epitome.php
	'thumb_image_ok' => '图像裁切完毕',
	'close_windows' => '关闭窗口',
	'is_image_ok' => '就要这个图像了',
	'ps_image_msg' => '提示：<br />拉动图像边角可以改变大小，但是不能调整长宽比例。',
	
	//batch.postnews.php
	'put_to_news' => '推送到咨询',
	'post_set' => '推送配置',
	'post_to_other' => '推送',
	'post_to_uchome_or_bbs' => '推送到论坛或者UCenter Home',
	'post_ok' => '推送成功',
	'post_close_div' => '关闭窗口',
	'post_error' => '推送失败',
	'this_forums_not_thread' => '该板块不能发帖',
	'default_value' => '默认分类',
	'you_no_join_mtag' => '你没有加入任何群组',
	'post_category' => '推荐分类或板块',
	'no_select_forums' => '没有选择板块',
	'no_select_tagid' => '没有选择群组'

);

?>