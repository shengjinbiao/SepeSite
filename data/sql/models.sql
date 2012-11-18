DROP TABLE IF EXISTS supe_[models]items;
CREATE TABLE supe_[models]items (
	itemid mediumint(8) unsigned NOT NULL auto_increment,
	catid smallint(6) unsigned NOT NULL default '0',
	uid mediumint(8) unsigned NOT NULL default '0',
	tid mediumint(8) unsigned NOT NULL default '0',
	username char(15) NOT NULL default '',
	subject char(80) NOT NULL default '',
	subjectimage char(80) NOT NULL default '',
	rates smallint(6) unsigned NOT NULL default '0',
	dateline int(10) unsigned NOT NULL default '0',
	lastpost int(10) unsigned NOT NULL default '0',
	viewnum mediumint(8) unsigned NOT NULL default '0',
	replynum mediumint(8) unsigned NOT NULL default '0',
	allowreply tinyint(1) NOT NULL default '0',
	grade tinyint(1) NOT NULL default '0',
	hot mediumint(8) unsigned NOT NULL default '0',
	PRIMARY KEY (itemid),
	KEY catid (catid, itemid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS supe_[models]message;
CREATE TABLE supe_[models]message (
	nid mediumint(8) unsigned NOT NULL auto_increment,
	itemid mediumint(8) unsigned NOT NULL default '0',
	message text NOT NULL,
	postip varchar(15) NOT NULL default '',
	relativeitemids varchar(255) NOT NULL default '',
	PRIMARY KEY (nid),
	KEY itemid (itemid)
) TYPE=MyISAM;
