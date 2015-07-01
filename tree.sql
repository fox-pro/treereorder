DROP TABLE IF EXISTS `tree`;
CREATE TABLE IF NOT EXISTS `tree` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent` int(11) unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `folder` tinyint(1) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

INSERT INTO `tree` (`id`, `parent`, `path`, `name`, `folder`, `sort`) VALUES
(1, 0, '0', 'TreeReorder', 1, 1),
(2, 1, '0.1', 'backend', 1, 1),
(3, 1, '0.1', 'images', 1, 2),
(4, 1, '0.1', '.htaccess', 0, 3),
(5, 1, '0.1', 'index.html', 0, 4),
(6, 1, '0.1', 'readme.txt', 0, 5),
(7, 1, '0.1', 'script.js', 0, 6),
(8, 1, '0.1', 'style.css', 0, 7),
(9, 2, '0.1.2', 'DI', 1, 1),
(10, 2, '0.1.2', 'Router', 1, 2),
(11, 2, '0.1.2', 'Tree', 1, 3),
(12, 2, '0.1.2', 'config.php', 0, 4),
(13, 2, '0.1.2', 'index.php', 0, 5),
(14, 3, '0.1.3', 'arrows.png', 0, 1),
(15, 3, '0.1.3', 'drop.png', 0, 2),
(16, 3, '0.1.3', 'drop-no.png', 0, 3),
(17, 3, '0.1.3', 'folder.png', 0, 4),
(18, 3, '0.1.3', 'folder-open.png', 0, 5),
(19, 3, '0.1.3', 'leaf.png', 0, 6),
(20, 3, '0.1.3', 'loading.png', 0, 7),
(21, 3, '0.1.3', 'square.gif', 0, 8),
(22, 9, '0.1.2.9', 'Dependency.pnp', 0, 1),
(23, 9, '0.1.2.9', 'DependencyContainer.php', 0, 2),
(24, 9, '0.1.2.9', 'DependencyInjection.php', 0, 3),
(25, 9, '0.1.2.9', 'DI.php', 0, 4),
(26, 10, '0.1.2.10', 'Router.php', 0, 1),
(27, 11, '0.1.2.11', 'Tree.php', 0, 1);