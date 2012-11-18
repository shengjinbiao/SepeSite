<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admincp_message.lang.php 13342 2009-09-16 05:43:20Z zhaofei $
*/

if(!defined('IN_SUPESITE')) exit('Access Denied');

$amlang = array(
	'do_success' => '进行的操作完成了',
	
	//admincp.php
	'admincp_no_popedom' => '您没有权限进行本操作，请返回',
	'admincp_no_founder_popedom' => '对不起，您没有创始人权限，请在config.php里添加创始人',
	'admincp_login' => '您没有登录站内系统，请先登录',
	'incorrect_code' => '输入的验证码不符，请重新确认',
	'submit_invalid' => '您的请求来路不正确或表单验证串不符，无法提交。请尝试使用标准的web浏览器进行操作。',
	'enter_the_password_is_incorrect' => '输入的密码不正确，请重新尝试',
	'excessive_number_of_attempts_to_sign' => '您30分钟内尝试登录管理平台的次数超过了3次，为了数据安全，请稍候再试',
	'start_listcount_error' => '出错了，您要查看页数不存在',
	
	//admin/admin_ad.php,
	'no_authority_management_operation' => '对不起,您没有权限进行本管理操作',
	'ad_op_success' => '您已经成功操作了广告设置',
	'ad_check_subject' => '您输入的广告名的长度不符合要求，请返回检查',
	'ad_check_page' => '投放页面不能为空，请选择',
	'ad_check_type' => '投放频道不能为空，请选择',
	'ad_add_check_textsize' => '文本大小为0',
	'ad_check_adcontent' => '广告内容不能为空，请填写',
	'ad_add_success' => '您已经成功添加了广告',
	'ad_add_check_must' => '请检查必填是否填写完整',
	'ad_imageurl_error' => '图片链接地址不正确',
	'ad_update_success' => '您已经成功更新了广告设置',
	'ad_no_ads' => '您指定的广告不存在',
	'ad_out_error' => '弹出广告的大小不能小于等于0',

	//admin_attachments.php
	'please_choose_search_terms' => '对不起，请正确选择查找附件的条件',
	'not_found_with_the_annex' => '对不起，没有找到符合条件的附件',
	'you_have_no_choice_operation_annex' => '对不起，您没有选择要操作的附件',
	'annex_success_of_the_operation' => '恭喜您，对附件的管理操作成功了',

    //admin/admin_bbs.php
	'bbs_db_setting' => '请配置论坛数据库',
	'bbs_db_error' => '论坛数据库配置错误',
	'bbs_dbname_error' => '论坛数据库不存在或数据表不存在',
	'bbs_url_error' => '论坛路径设置错误',
	'bbs_setting_success' => '论坛收录配置成功',
	'bbsver_error' => '论坛版本号不能为空',
	'bbs_deldata_success' => '删除论坛聚合信息成功',

	//admin_comments.php
	'identify_conditions_choice_comments' => '对不起，请正确选择查找评论的条件',
	'not_found_qualified_to_comment' => '对不起，没有找到符合条件的评论',
	'you_have_no_choice_information' => '对不起，您没有选择要操作的信息',
	'successful_management_of_the_theme' => '恭喜您，对信息主题的管理操作成功了',

	//admin/admin_usergroups.php
	'user_group_does_not_exist' => '指定操作的用户组不存在',
	'user_group_were_not_empty' => '指定的用户组名不能为空',
	'system_user_group_could_not_be_deleted' => '系统用户组不能删除',
	'integral_limit_duplication_with_other_user_group' => '指定的积分下限跟其他用户组重复',
	'integral_limit_error' => '指定的积分下限上能超过999999999，下限不能低于-999999998',

	//admin_database.php
	'database_export_dest_invalid' => '目录不存在或无法访问，请检查 ./data/ 目录。',
	'database_export_del' => '备份文件删除成功',
	'database_export_filename_error' => '对不起,您输入的文件名的长度不符合要求,请返回检查',
	'database_export_succeed' => '数据成功备份并压缩至服务器 ',
	'database_export_tables_error' => '对不起,您至少需要选择一个数据表进行备份，请返回修改',
	'database_export_write_error' => '写入文件失败,请检查文件权限',
	'database_export_zip_error' => '对不起，备份数据文件压缩失败，请检查目录权限',
	'database_export_zip_succeed' => '数据成功备份并压缩至服务器 ',
	'database_import_file_illegal' => '对不起, 数据文件不存在,请检查',
	'database_import_format_illegal' => '数据文件非 SupeSite 格式，无法导入。',
	'database_import_multivol_succeed' => '分卷数据成功导入SupeSite数据库。',
	'database_import_successd' => '数据成功导入SupeSite数据库。',
	'database_import_start' => '数据恢复开始',
	'database_shell_fail' => 'Shell 权限被禁止或服务器不支持，无法使用 MySQL Dump 方式备份或恢复数据，请返回。',

	//admin_items.php
	'chosen_search_terms' => '对不起，请正确选择查找信息的条件',
	'not_find_qualified_information' => '对不起，没有找到符合条件的信息',
	'please_choose_type_operation' => '对不起，请选择操作类型',

	//admin/admin_spacenews.php,
	'spacenews_no_popedom_check' => '对不起，您所在的用户组目前没有审阅资讯的权限，请返回',
	'space_suject_length_error' => '您输入的标题长度不符合要求，请返回修正',
	'admin_func_catid_error' => '您没有正确指定分类，请返回确认',
	'spacenews_no_popedom_add' => '对不起，您所在的用户组目前没有添加资讯的权限，请返回',
	'bbsimport_imported' => '您指定的主题内容已经导入过，不能重复导入',
	'spacenews_page_need_submit' => '您需要先将当前分页信息提交保存后，方能继续进行添加分页操作',
	'you_no_authority_to_operate' => '对不起，您没有权限对指定的信息进行操作',
	'you_had_no_competence_to_examine' => '对不起，您现在没有权限进行等级审核',
	'no_action_item' => '没有定义此类文章的操作',

	//admin_userprofile.php
	'prefield_delete_success' => '成功删除',
	'add_success' => '添加成功',
	'edit_success' => '编辑成功',

	//admin/admin_announcements.php,
	'announcements_list_update' => '公告列表批量操作成功！',
	'announcements_time_error' => '开始时间为空或者结束时间不能小于开始时间!',
	'announcements_no_message' => '内容不能为空!',
	'announcements_add_succeed' => '成功新增公告信息！',
	'announcements_update_succeed' => '公告信息修改成功！',
	'announcements_no_id' => '选择的公告不存在!',

	//admin/admin_attachmenttypes.php,
	'attachmenttype_check_fileext' => '您指定的文件后缀名长度不符合要求，请返回修正',
	'attachmenttype_add_success' => '您已经成功添加文件后缀类型',
	'attachmenttype_update_success' => '您已经成功更新文件后缀类型',
	'attachmenttype_delete_success' => '您已经成功删除指定的文件后缀类型',

	//admin/admin_bbsforums.php,
	'bbsforums_update_success' => '论坛版块设置成功更新',

	//admin/admin_blocks.php,
	'block_op_success' => '您已经成功删除指定的模块设置',
	'block_add_success' => '您已经成功添加模块设置',
	'block_update_success' => '您已经成功更新模块设置',
	'not_exist_channel' => '请选择文章频道',

	//admin/admin_categories.php,
	'common_error_type' => '参数错误，请指定正确的系统类型',
	'category_update_success' => '更新分类操作成功完成',
	'category_size_error' => '您输入的分类名称长度不符合要求(2~50字)，请返回检查',
	'category_add_success' => '添加分类操作成功完成',
	'category_bbs_update_success' => '您已经成功设定分类的论坛读取设置',
	'category_del_catid_error' => '您选择的新分类不能是原有分类的子分类，如果您执意要这样做，请把目标分类移至新的分类下',
	'category_delete_success' => '您已经成功删除合并指定的分类',
	'category_copy_succeed' => '分类设置成功复制。',
	'category_catid_no_exists' => '您指定的分类不存在，请返回修正',
	'category_sub_cat_exists' => '您要删除合并的分类存在子分类，不能继续操作',
	'category_copy_target_invalid' => '您没有选择要复制的目标分类，请返回修改。',
	'category_copy_source_invalid' => '您没有选择要复制的源版块，请返回修改。',
	'category_copy_options_invalid' => '您没有选择要复制的项目，请返回修改。',
	'category_domain_error' => '您填写二级域名格式不正确，请检查是否是以"http://"开头',
	'pre_html_error' => '静态页前缀填写有误，请返回修改。',	
	'category_have_sub_cate' => '被合并或者删除的分类不能含有子分类',
	
	//admin/admin_click.php
	'click_error_groupid' => '参数错误，请指定正确的分组。',
	'click_error_name' => '参数错误，请指定正确的动作名称。',
	'click_group_delete' => '分组下尚有动作，请先删除动作，再进行此项操作。',
	'click_error_delete_system' => '此动作为系统功能动作，不允许删除。您可通过关闭功能来屏蔽此动作。',
	'click_group_error_delete_system' => '此分类为系统功能分类，不允许删除。您可通过关闭功能来屏蔽此分类。',

	//admin/admin_channel.php,
	'channel_update_ok' => '更新频道设置操作完成',
	'channel_action_exist' => '指定的频道英文ID已经存在，请返回检查',
	'channel_action_error' => '指定的频道英文ID包含非英文字母，请返回检查',
	'channel_php_src_error' => '标志频道程序文件(channel_sample.php)不存在，请检查文件是否上传完整',
	'channel_tpl_src_error' => '标准频道模板文件(channel_sample.html.php)不存在，请检查程序是否上传完整',
	'channel_add_ok' => '添加新频道操作完成',
	'channel_no_exists' => '对不起,指定的频道不存在',
	'channel_is_model' => '模型类型频道请通过FTP方式修改指定的模板文件',
    'channel_action_protect' => '频道已经存在或者指定的频道英文ID属于系统保留ID，请返回重新填写',

	//admin/admin_check.php,
	'space_no_item' => '您没有选择要操作的信息，请返回修改',
	'check_op_ok' => '您的操作已经完成，现在将跳转到操作前的页面',

	//admin/admin_crons.php,
	'cron_update_success' => '您已经成功更新计划任务',
	'cron_error_no_filename' => '您指定的任务脚本文件不存在或包含语法错误，请返回修改',
	'cron_error_no_time' => '您没有指定计划任务执行的时间或条件，请返回修改',
	'cron_add_success' => '您已经成功添加计划任务',
	'cron_delete_success' => '您已经成功删除计划任务',
	'cron_run_success' => '您指定的计划任务已经成功执行',

	//admin/admin_css.php,
	'tpl_edit_invalid' => '您指定的文件不能被改写，请检查文件的属性设置或是否存在',
	'tpl_filename_error' => '您指定的文件名不正确，请返回检查',
	'tpl_edit_success' => '您编辑的文件内容成功保存',
	'tpl_delete_ok' => '您指定的文件已经成功删除',
	'tpl_delete_error' => '您指定的文件不能被删除，请检查文件的属性设置',

	//admin/admin_customfields.php,
	'spaceblog_no_popedom' => '对不起，您目前所在的用户组没有权限进行此操作，请返回',
	'usetype_no_open' => '对不起，系统暂时没有启用该功能，请返回确认',
	'customfield_list_update_success' => '已经成功更新个人配置信息列表',
	'customfield_add_success' => '已经成功增加个人配置信息',
	'customfield_edit_success' => '个人自定义配置信息成功修改',
	'customfield_customfieldid_no_exists' => '对不起，您要编辑的自定义信息不存在',

	//admin/admin_html.php,
	'html_update_error' => '您指定的日期期限不正确，请重新确认',
	'html_update_success' => '您已经成功设置了html的手工更新',
	'html_deletefile_filename_error' => '您没有正确指定要删除的html文件名中要包含的字符，请返回确认',
	'html_allocation_preservation_success' => '恭喜您,指定的html配置保存成功了',
	'html_page_not_found_with_generation' => '对不起，没有找到符合要求的页面来生成html',

	//admin/admin_polls.php,
	'poll_op_success' => '您的操作已经成功执行',
	'poll_check_subject' => '您输入的标题字数太少，请返回检查',
	'poll_check_summary' => '您输入的介绍内容太少，请返回检查',
	'poll_check_option' => '您没有输入有效的投票选项，请返回检查',
	'poll_add_success' => '您已经成功添加新的投票',
	'poll_update_success' => '您已经成功更新投票设置',

	//admin/admin_prefields.php,
	'prefield_none_exists' => '指定信息不存在',

	//admin/admin_robotmessages.php,
	'robotmessage_op_success' => '操作成功完成',
	'robotmessage_none_exists' => '指定的采集文章信息不存在',

	//admin/admin_robots.php,
	'robot_import_data_invalid' => '机器人配置信息不正确',
	'robot_import_version_invalid' => '机器人配置信息的版本与系统版本不符',
	'robot_import_success' => '机器人成功导入',
	'robot_add_success' => '采集机器人成功添加',
	'robot_edit_success' => '指定的采集机器人成功更新',
	'robot_robotid_no_exists' => '指定的采集机器人不存在',
	'robot_delete_success' => '指定的采集机器人成功删除',
	'robot_clearcache_success' => '指定的索引缓存成功删除',
	'robot_clear_success' => '采集到文章全部清空',
	'robot_exportmessage_fopen_error' => '写入文件出错，请检查robot导出目录(data/robot)是否存在并可写',

	//admin/admin_settings.php,
	'setting_update_success' => '您已经成功更新了您的系统配置信息',

	//admin/admin_sitemap.php
	'sitemap_name_error' => 'sitemap名称只能由数字,字母,下划线组成，长度不大于50',
	'sitemap_config_update' => 'sitemap操作成功',
	'sitemap_name_exists' => 'sitemap名称已经存在',
	'sitemap_config_add' => '配置添加成功',
	'sitemap_perm_error' => '检查目录是否存在或者是否可写',
	
	//admin/admin_spacecache.php,
	'spacecache_delete_success' => '您已经成功清空指定的缓存',

	//admin/admin_styles.php,
	'style_tpl_file_not_exists' => '您没有正确选择一个风格文件，请返回修正',
	'style_add_success' => '您已经成功添加风格',
	'style_edit_success' => '指定的风格成功编辑',
	'style_none_exists' => '指定的风格不存在',
	'style_delete_success' => '指定的风格成功删除',

	//admin/admin_tags.php,
	'tag_no_item' => '您应该至少选择一个要操作的TAG，请返回修正',
	'tag_tagname_error' => '您输入的TAG名称不符合要求，请返回确认',
	'tag_batch_op_success' => '您已经成功更新选择的TAG',
	'tag_update_success' => '您已经成功更新TAG信息',
	'tag_no_tagid' => '您指定的TAG信息不存在，请返回修正',

	//admin/admin_words.php
	'censor_keywords_tooshort' => '对不起，您添加的关键词长度过短，请返回修改.',
	'censor_update_succeed' => '词语过滤更新成功',
	'censor_add_words' => '批量导入词语完毕。总共新增词语    ',
	'censor_update_words' => '更新词语',
	'censor_ignore_words' => '忽略词语',
	'censor_keywords_exists' => '关键词语已经存在',

	//admin/admin_model.php
	'select_model_tpl' => '请选择模型模板.',
	'model_export_suc' => '模型导出完成,进入备份管理点击链接下载.',
	'create_model_tpl_error' => '模型建立失败，请稍候重试.',
	'fieldname_error' => '字段名不合法，字段名称以字母开头，后面可以跟字母数字和下划线，长度2-30个字符',
	'field_is_system_key' => '字段名为系统字段',
	'field_is_exists' => '字段名已经存在',
	'fieldcomment_error' => '字段说明不合法，字段说明长度2-60个字符',
	'fieldtype_error' => '数据表字段类型不合法',
	'formtype_error' => '表单字段类型不合法',
	'edit_field_not_exists' => '您所编辑的字段不存在',
	'upload_fileext_error' => '修改前或修改后的表单字段类型为img、flash、file类型时，不允许修改表单字段类型',
	'edit_fieldtype_error' => '修改前的字段存在定长表中，因此不允许出现变长字段(VARCHAR|TEXT|MEDIUMTEXT|LONGTEXT)',
	'edit_isfixed_error' => '字段建立后不允许修改定长与不定长',
	'edit_isfixed_char_error' => '修改前的字段存在不定长表中，请将数据表字段类型CHAR修改成VARCHAR',
	'edit_char_to_isfixed_error' => '当为不定长表时，请将数据表字段类型CHAR修改为VARCHAR',
	'tinyint_length_error' => '当数据表字段类型为TINYINT时，字段长度不能大于3',
	'tinyint_default_length' => '当数据表字段类型为TINYINT时，初始化值的范围是-128到127',
	'smallint_length_error' => '当数据表字段类型为SMALLINT时，字段长度不能大于5',
	'smallint_default_length' => '当数据表字段类型为SMALLINT时，初始化值的范围是-32768到32767',
	'mediumint_length_error' => '当数据表字段类型为MEDIUMINT时，字段长度不能大于7',
	'mediumint_default_length' => '当数据表字段类型为MEDIUMINT时，字段长度不能大于7',
	'int_length_error' => '当数据表字段类型为INT时，字段长度不能大于10',
	'int_default_length' => '当数据表字段类型为INT时，初始化值的范围是-2147483648到2147483647',
	'bigint_length_error' => '当数据表字段类型为BIGINT时，字段长度不能大于19',
	'bigint_default_length' => '当数据表字段类型为BIGINT时，初始化值的范围是-9223372036854775808到9223372036854775807',
	'default_length_error' => '初始化值为负数时长度不应长于字段长度值+1',
	'fieldlength_ng_fielddefault' => '初始化值长度不应长于字段长度值',
	'table_type_int' => '当数据表字段类型为整型数值类型时，表单显示元素也必须为整型数值型',
	'table_type_float' => '当数据表字段类型为浮点类型时，表单显示元素也必须为浮点型',
	'formtype_linkage_no_key' => '当为联动下拉框时,请为每个元素设置索引.',
	'formtype_linkage_error' => '当为联动下拉框时,元素索引不正确.',
	'required_allowsearch' => '当允许搜索时,必须选中允许显示.',
	'required_field' => '当字段必填时，必须选中允许投稿.',
	'required_allowshow' => '当字段允许列表显示时,必须选中允许显示.',
	'fielddata_repeat' => '表单显示元素存在重复数据.',
	'field_sql_error' => '字段失败，数据库错误.</p><div align="left"><p>SQL 查询: <br />',
	'create_field_error' => '字段建立失败，请稍候重试',
	'field_not_exists' => '字段不存在',
	'field_del_suc' => '字段删除成功',
	'delete_success' => '删除成功',
	'not_designated_backup_del' => '没有指定删除备份',
	'not_designated_op' => '没有指定操作',
	'model_name_existed' => '模型标识已存在',
	'vars_error_return_try' => '参数错误请返回重试',
	'import_type_error' => '导入失败，模型文件格式不正确',
	'import_sql_error' => '导入失败，数据库返回异常值',
	'file_write_error' => '文件写入失败，请检查权限',
	'import_model_error' => '导入失败，模型文件不存在',
	'import_model_charset_error' => '导入失败,导入的模型编码与网站编码不一致.',
	'fieldlistsubmit_success' => '更新字段操作成功完成',
	'writing_success_online_please_wait_for_audit' => '提交成功,请等待审核通过.',
	'online_contributions_success' => '在线投稿成功.',
	'document_types_can_only_upload_pictures' => '标题图片只能上传图片类型文件(.jpg .jpeg .gif .png).',
	'admin_func_catid_error' => '您没有正确指定分类，请返回确认',
	'space_suject_length_error' => '您输入的标题长度不符合要求(2~80个字符)',
	'parameter_error' => '出错了，参数错误,请返回',
	'no_item_or_no_prem' => '没有内容或没有权限',
	'visit_the_channel_does_not_exist' => '您访问的频道不存在,请返回首页.',

	//admin/admin_modelcategories.php
	'not_exist_module' => '模型不存在',
	'exists_module_error' => '您访问的模型不存在，请返回首页',
	'through_the_completion_of_audit' => '审核通过完成',
	'information_reduction_success' => '信息还原成功',
	'info_remove_waste_suc' => '信息移至废件箱成功',
	'info_del_suc' => '信息删除成功',
	'no_select_op' => '未选择任何操作',

	//admin/admin_model.php
	'model_name_error' => '模型标识不合法，名称只可以输入字母、数字，长度2-20个字符.',
	'model_exists_error' => '模型名已经存在，请修改模型标识.',
	'model_system_exisis_error' => '模型名为系统保留名，请修改模型标识.',
	'model_other_name_error' => '模型名称不合法，名称只可以输入中文、字母、数字、下划线，长度2-60个字符.',
	'model_key_name_error' => '模型关键词长度不合法，长度不得大于200个字符.',
	'model_subject_pic_width_error' => '标题图片缩略图宽度不合法，长度不得大于9999.',
	'model_subject_pic_height_error' => '标题图片缩略图高度不合法，长度不得大于9999.',

	//admin/admin_freshhtml.php
	'make_index_success' => '首页HTML更新成功。',
	'make_channle_index_success' => '频道HTML更新成功。',
	'freshhtml_cache_error' => '设置不存在，请重新设置......',
	
	//admin/admin_makehtml.php
	'update_html_success' => '内容HTML更新成功,下面更新列表, 请不要中断......',
	'delete_html' => '现在删除HTML, 请稍等......',
	'make_html' => '现在生成HTML, 请稍等......',
	'update_html' => '现在更新HTML, 请稍等......',
	'make_html_success' => '生成HTML完成',
	'iswrite_error' => '目录不可写, 检查目录属性或者更换路径',
	

	//admin/include/admin_blocks_announcement_code.inc.php,
	'block_thread_code_sql' => '您没有指定查询的SQL文，请返回修正',
	'block_announcement_code_aid' => '您没有指定公告ID(s)，请返回修正',
	'block_thread_code_limit' => '您没有指定数据的显示数目，请返回修正',
	'block_thread_code_cachename' => '您选择了只获取数据展示风格，变量名不能为空，请返回修正',
	'block_thread_code_tpl' => '您指定的模板风格文件不存在，请返回修正',

	//admin/include/admin_blocks_bbsattachment_code.inc.php,
	'block_attachment_code_aid' => '您没有指定附件的aid(s)，请返回修正',

	//admin/include/admin_blocks_bbsforum_code.inc.php,
	'block_forum_code_fid' => '您没有指定版块的fid(s)，请返回修改',

	//admin/include/admin_blocks_bbslink_code.inc.php,
	'block_link_code_tid' => '您没有指定链接的ID(s)，请返回修正',

	//admin/include/admin_blocks_bbsmember_code.inc.php,
	'block_member_code_uid' => '您没有指定用户的uid(s)，请返回修正',

	//admin/include/admin_blocks_bbspost_code.inc.php,
	'block_post_code_pid' => '您没有指定帖子的pid(s)，请返回修正',

	//admin/include/admin_blocks_bbsthread_code.inc.php,
	'block_thread_code_tid' => '您没有指定主题的tid(s)，请返回修正',

	//admin/include/admin_blocks_category_code.inc.php,
	'block_cat_code_catid' => '您没有指定分类的id(s)，请返回修正',

	//admin/include/admin_blocks_group_code.inc.php,
	'block_userspace_code_uid' => '您没有指定用户的UID，请返回修正',

	//admin/include/admin_blocks_poll_code.inc.php,
	'block_poll_code_pollid' => '您没有指定投票的id(s)，请返回修正',

	//admin/include/admin_blocks_spacecomment_code.inc.php,
	'block_spacecomment_code_cid' => '您没有指定评论的id(s)，请返回修正',

	//admin/include/admin_blocks_spaceitem.inc.php,
	'block_type_error' => '您没有正确指定要操作的系统类型，请检查操作过程',

	//admin/include/admin_blocks_spaceitem_code.inc.php,
	'block_spaceitem_code_itemid' => '您没有正确指定信息的itemid(s)，请返回修正',

	//admin/include/admin_blocks_spacetag_code.inc.php,
	'block_spacetag_code_tagid' => '您没有指定TAG id(s)，请返回修正',

	//admin/include/admin_blocks_uchblog_code.inc.php,
	'block_uchblog_code_blogid' => '您没有正确指定信息的blogid(s)，请返回修正',

	//admin/include/admin_blocks_uchspace_code.inc.php,
	'block_uchblog_code_uid' => '您没有正确指定信息的uid(s)，请返回修正',

	//admin/include/admin_blocks_uchphoto_code.inc.php,
	'block_uchphoto_code_albumid' => '您没有正确指定信息的albumid(s)，请返回修正',

	//admin/include/admin_blocks_tag_code.inc.php,
	'block_tag_code_tagid' => '未指定TAG ID',

	//admin/admincp_reports.php	
	'report_op_ok' => '举报信息处理成功',
	'space_does_not_exist' => '指定的用户空间不存在',
	'error_lock_self' => '不能锁定自己',

	//admin/admin_friendlinks.php
	'links_op_success' =>'您已经成功设置了友情链接',
	'link_check_name' => '您输入的站点昵称长度不符合,请返回检查',
	'link_check_url' => '站点URL地址不能为空或者填写格式不对,请返回填写',
	'link_add_success' => '您已经成功添加了友情链接',
	'link_update_success' => '您已经成功更新了友情链接',

	//function/common.func.php
	'message_no_permission' => '对不起，您没有权限进行本操作',

    //admin/admin_threads.php
	'threads_set' => '帖子批量设置成功',

    //admin/admin_uchome.php
	'uch_db_error' => 'UCenter Home数据库连接失败',
	'uch_dbname_error' => 'UCenter Home数据库不存在或数据表不存在',
	'uch_url_error' => 'UCenter Home路径设置错误',
	'uch_setting_success' => 'UCenter Home聚合配置成功',
	'uch_deldata_success' => '删除UCenter Home聚合信息成功',
		
	//admin/admin_member.php
	'choose_to_delete_the_space' => '请选择要删除的用户',
	'designated_users_do_not_exist' => '指定的用户不存在',
	
	//admin/admin_postnews.php
	'dbname_error' => '数据库名字错误',
	'db_error' => '数据库链接错误',
	'add_set_success' => '添加配置成功',
	'set_info_is_empty' => '配置信息不完整',
	'no_setid_select' => '没有配置被选择',
	'delete_set_sucess' => '删除配置成功',
	'this_set_no_code' => '这种配置没有代码',
	
	//admin/admincp_credit.php
	'rules_do_not_exist_points' => '该积分规则不存在',
	
	//admin/admin_delusers.php
	'no_uid_select' => '请选择需要恢复的uid'
	
);
?>
