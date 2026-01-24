CREATE TABLE `typecho_moments` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'moments表主键',
  `rowStatus` varchar(16) NOT NULL DEFAULT 'NORMAL' COMMENT '状态',
  `creatorId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建者uid',
  `createdTs` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间戳',
  `updatedTs` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间戳',
  `displayTs` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '展示时间戳',
  `content` text NOT NULL COMMENT '内容',
  `visibility` varchar(16) NOT NULL DEFAULT 'PUBLIC' COMMENT '可见性',
  `pinned` tinyint(1) NOT NULL DEFAULT '0' COMMENT '置顶',
  `parent` int(10) UNSIGNED DEFAULT NULL COMMENT '父级',
  PRIMARY KEY (`id`),
  KEY `idx_rowStatus` (`rowStatus`),
  KEY `idx_visibility` (`visibility`),
  KEY `idx_displayTs` (`displayTs`),
  KEY `idx_pinned` (`pinned`)
) ENGINE=MYISAM DEFAULT CHARSET=%charset%;
