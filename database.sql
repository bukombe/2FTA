DROP TABLE IF EXISTS `authProviderUsers`;
CREATE TABLE `authProviderUsers` (`id` int(11) NOT NULL AUTO_INCREMENT,`apuid` int(20) NOT NULL COMMENT 'Users Foreign id',`secret` varchar(255) NOT NULL COMMENT 'User secret for the request package',`enabled` int(1) NOT NULL DEFAULT '1', PRIMARY KEY (`id`) ) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
DROP TABLE IF EXISTS `authRequests`;
CREATE TABLE `authRequests` (`id` int(11) NOT NULL AUTO_INCREMENT,`authProviderId` int(11) NOT NULL COMMENT 'Users Foreign id',`username` varchar(128) NOT NULL,`password` varchar(128) NOT NULL,`unixtime` int(11) NOT NULL,`valid` int(11) NOT NULL,`used` int(11) NOT NULL,
PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

INSERT INTO `authProviderUsers` VALUES (1,1327804,'parsnip123',1);