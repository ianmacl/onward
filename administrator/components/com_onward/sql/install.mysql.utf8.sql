CREATE TABLE IF NOT EXISTS `#__onward_sites` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL DEFAULT '',
  `location` varchar(255) NOT NULL DEFAULT '',
  `version` varchar(250) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE  `#__onward_site_state` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`site_id` INT NOT NULL ,
`asset` VARCHAR( 255 ) NOT NULL ,
`import_date` DATETIME NOT NULL ,
`total` INT NOT NULL ,
`limit` INT NOT NULL ,
`offset` INT NOT NULL
) ENGINE = MYISAM ;


CREATE TABLE IF NOT EXISTS `#__onward_data_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL,
  `asset` varchar(255) NOT NULL,
  `original_id` int(11) NOT NULL,
  `new_id` int(11) NOT NULL,
  `status` int(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

