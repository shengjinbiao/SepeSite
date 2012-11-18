<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: feed.lang.php 11565 2009-03-10 07:21:08Z zhaofei $
*/

if(!defined('IN_SUPESITE')) exit('Access Denied');

$flang = array
(
	'feed_model_title' =>			'{actor} 在 {modelname} 发布了新信息</b>',
	'feed_model_message' =>			'<b>{subject}</b><br />{message}',
	'feed_model_comment_title' =>	'{actor} 在 {modelname} 评论了 {author} 的信息 {modelpost}',
	
	'feed_news_title' =>			'{actor} 发布了新'.$channels['menus']['news']['name'],
	'feed_news_message' =>			'<b>{subject}</b><br />{message}',
	'feed_news_comment_title' =>	'{actor} 评论了 {author} 的'.$channels['menus']['news']['name'].' {mommentpost}'
);


?>