CREATE TABLE IF NOT EXISTS `#__wcp` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `sid` varchar(20) NOT NULL DEFAULT '',
    `name` varchar(250) NOT NULL DEFAULT '',
    `parent_sid` varchar(250) NOT NULL DEFAULT '',
    `path` varchar(250) NOT NULL DEFAULT '',
    `params` text,
    PRIMARY KEY (`id`),
    UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;