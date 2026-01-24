CREATE TABLE `typecho_moments` (
  `id` INTEGER NOT NULL PRIMARY KEY,
  `rowStatus` varchar(16) NOT NULL DEFAULT 'NORMAL',
  `creatorId` int(10) NOT NULL DEFAULT '0',
  `createdTs` int(10) NOT NULL DEFAULT '0',
  `updatedTs` int(10) NOT NULL DEFAULT '0',
  `displayTs` int(10) NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `visibility` varchar(16) NOT NULL DEFAULT 'PUBLIC',
  `pinned` int(10) NOT NULL DEFAULT '0',
  `parent` int(10) DEFAULT NULL
);
