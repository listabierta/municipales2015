/**
 * Autonomous Communities Spain
 * 14-03-2015
 * Source: http://www.ine.es/daco/daco42/codmun/cod_ccaa.htm
 */
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
CREATE TABLE IF NOT EXISTS `autonomous_communities_spain` (
  `id` tinyint(4) NOT NULL,
  `name` varchar(100) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `autonomous_communities_spain` (`id`, `name`) VALUES
(1, 'Andalucía'),
(2, 'Aragón'),
(3, 'Asturias, Principado de'),
(4, 'Balears, Illes'),
(5, 'Canarias'),
(6, 'Cantabria'),
(7, 'Castilla y León'),
(8, 'Castilla - La Mancha'),
(9, 'Catalunya'),
(10, 'Comunitat Valenciana'),
(11, 'Extremadura'),
(12, 'Galicia'),
(13, 'Madrid, Comunidad de'),
(14, 'Murcia, Región de'),
(15, 'Navarra, Comunidad Foral de'),
(16, 'País Vasco'),
(17, 'Rioja, La'),
(18, 'Ceuta'),
(19, 'Melilla');
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;