/**
 * Provinces Spain
 * 14-03-2015
 * Source: http://www.ine.es/daco/daco42/codmun/cod_provincia.htm
 */
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
CREATE TABLE IF NOT EXISTS `provinces_spain` (
  `id` int(11) NOT NULL,
  `community_id` tinyint(4) default NULL,
  `name` varchar(45) default NULL,
  PRIMARY KEY  (`id`),
  KEY `community_id` (`community_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `provinces_spain` (`id`, `community_id`, `name`) VALUES
(1, 16, 'Araba/Álava'),
(2, 8, 'Albacete'),
(3, 10, 'Alicante/Alacant'),
(4, 1, 'Almería'),
(5, 7, 'Ávila'),
(6, 11, 'Badajoz'),
(7, 4, 'Balears, Illes'),
(8, 9, 'Barcelona'),
(9, 7, 'Burgos'),
(10, 11, 'Cáceres'),
(11, 1, 'Cádiz'),
(12, 10, 'Castellón/Castelló'),
(13, 8, 'Ciudad Real'),
(14, 1, 'Córdoba'),
(15, 12, 'Coruña, A'),
(16, 8, 'Cuenca'),
(17, 9, 'Girona'),
(18, 1, 'Granada'),
(19, 1, 'Guadalajara'),
(20, 16, 'Gipuzkoa'),
(21, 1, 'Huelva'),
(22, 2, 'Huesca'),
(23, 1, 'Jaén'),
(24, 7, 'León'),
(25, 9, 'Lleida'),
(26, 17, 'Rioja, La'),
(27, 12, 'Lugo'),
(28, 13, 'Madrid'),
(29, 1, 'Málaga'),
(30, 14, 'Murcia'),
(31, 15, 'Navarra'),
(32, 12, 'Ourense'),
(33, 3, 'Asturias'),
(34, 7, 'Palencia'),
(35, 5, 'Palmas, Las'),
(36, 12, 'Pontevedra'),
(37, 7, 'Salamanca'),
(38, 5, 'Santa Cruz de Tenerife'),
(39, 6, 'Cantabria'),
(40, 7, 'Segovia'),
(41, 1, 'Sevilla'),
(42, 7, 'Soria'),
(43, 9, 'Tarragona'),
(44, 2, 'Teruel'),
(45, 8, 'Toledo'),
(46, 10, 'Valencia/València'),
(47, 7, 'Valladolid'),
(48, 16, 'Bizkaia'),
(49, 7, 'Zamora'),
(50, 2, 'Zaragoza'),
(51, 18, 'Ceuta'),
(52, 19, 'Melilla');

ALTER TABLE `provinces_spain` ADD CONSTRAINT `provinces_spain_ibfk_1` FOREIGN KEY (`community_id`)
REFERENCES `autonomous_communities_spain` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;