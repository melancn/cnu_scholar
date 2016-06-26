CREATE TABLE `scholar` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `scholarid` int(11) unsigned NOT NULL,
 `type` varchar(255) NOT NULL,
 `tittle` varchar(1024) NOT NULL,
 `date` varchar(20) NOT NULL,
 `indate` date NOT NULL,
 `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 KEY `scholarid_2` (`scholarid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `mail_account` (
 `address` varchar(255) NOT NULL,
 `type` int(11) NOT NULL DEFAULT '1',
 KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;