
-- It looks like the sql structure got slightly mangled when we ported it between servers.
-- For monopolyPlayers, primary key is probably supposed to be (GameID, PlayerNum)
-- This predates utf8mb4

-- > describe monopoly;
-- +-------------+---------------------+------+-----+---------+-------+
-- | Field       | Type                | Null | Key | Default | Extra |
-- +-------------+---------------------+------+-----+---------+-------+
-- | GameID      | varchar(25)         | NO   | PRI | NULL    |       |
-- | Theme       | tinyint(3) unsigned | NO   |     | 0       |       |
-- | Options     | tinyint(3) unsigned | NO   |     | 0       |       |
-- | NumPlayers  | tinyint(3) unsigned | NO   |     | NULL    |       |
-- | Started     | tinyint(1)          | NO   |     | 0       |       |
-- | StartTime   | int(11)             | NO   |     | NULL    |       |
-- | Turn        | tinyint(3) unsigned | NO   |     | 0       |       |
-- | TurnOrder   | int(10) unsigned    | NO   |     | 0       |       |
-- | Messages    | text                | NO   |     | NULL    |       |
-- | GameLog     | text                | NO   |     | NULL    |       |
-- | Owned       | int(10) unsigned    | NO   |     | 0       |       |
-- | House1      | int(10) unsigned    | NO   |     | 0       |       |
-- | House2      | int(10) unsigned    | NO   |     | 0       |       |
-- | House3      | int(10) unsigned    | NO   |     | 0       |       |
-- | HousesInUse | tinyint(3) unsigned | NO   |     | 0       |       |
-- | HotelsInUse | tinyint(3) unsigned | NO   |     | 0       |       |
-- +-------------+---------------------+------+-----+---------+-------+

-- > describe monopolyPlayers;
-- +------------+---------------------+------+-----+---------+-------+
-- | Field      | Type                | Null | Key | Default | Extra |
-- +------------+---------------------+------+-----+---------+-------+
-- | GameID     | varchar(25)         | NO   |     | NULL    |       |
-- | PlayerNum  | tinyint(3) unsigned | NO   |     | NULL    |       |
-- | Name       | varchar(255)        | NO   |     | NULL    |       |
-- | Token      | tinyint(3) unsigned | NO   |     | NULL    |       |
-- | Roll       | tinyint(3) unsigned | NO   |     | 0       |       |
-- | PollTime   | int(11)             | NO   |     | NULL    |       |
-- | Password   | char(32)            | NO   |     | NULL    |       |
-- | Updates    | text                | NO   |     | NULL    |       |
-- | Ready      | tinyint(1)          | NO   |     | 0       |       |
-- | Money      | int(11)             | NO   |     | 1500    |       |
-- | Location   | tinyint(3) unsigned | NO   |     | 0       |       |
-- | Own        | int(10) unsigned    | NO   |     | 0       |       |
-- | noMortFees | int(10) unsigned    | NO   |     | 0       |       |
-- +------------+---------------------+------+-----+---------+-------+

-- > describe monopolyTransactions;
-- +-----------+-----------------------+------+-----+---------+----------------+
-- | Field     | Type                  | Null | Key | Default | Extra          |
-- +-----------+-----------------------+------+-----+---------+----------------+
-- | id        | mediumint(8) unsigned | NO   | PRI | NULL    | auto_increment |
-- | GameID    | varchar(25)           | NO   |     | NULL    |                |
-- | pFrom     | tinyint(3) unsigned   | NO   |     | 0       |                |
-- | pTo       | tinyint(3) unsigned   | NO   |     | 0       |                |
-- | FromP     | int(10) unsigned      | NO   |     | 0       |                |
-- | FromM     | mediumint(8) unsigned | NO   |     | 0       |                |
-- | ToP       | int(10) unsigned      | NO   |     | 0       |                |
-- | ToM       | mediumint(8) unsigned | NO   |     | 0       |                |
-- | Status    | tinyint(3) unsigned   | NO   |     | 0       |                |
-- | Mortgaged | int(10) unsigned      | NO   |     | 0       |                |
-- +-----------+-----------------------+------+-----+---------+----------------+


CREATE TABLE IF NOT EXISTS `monopoly` (
  `GameID` varchar(25) NOT NULL,
  `Theme` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `Options` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `NumPlayers` tinyint(3) UNSIGNED NOT NULL,
  `Started` tinyint(1) NOT NULL DEFAULT '0',
  `StartTime` int(11) NOT NULL,
  `Turn` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `TurnOrder` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `Messages` text NOT NULL,
  `GameLog` text NOT NULL,
  `Owned` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `House1` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `House2` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `House3` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `HousesInUse` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `HotelsInUse` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`GameID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `monopolyPlayers` (
  `GameID` varchar(25) NOT NULL,
  `PlayerNum` tinyint(3) UNSIGNED NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Token` tinyint(3) UNSIGNED NOT NULL,
  `Roll` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `PollTime` int(11) NOT NULL,
  `Password` char(32) NOT NULL,
  `Updates` text NOT NULL,
  `Ready` tinyint(1) NOT NULL DEFAULT '0',
  `Money` int(11) NOT NULL DEFAULT '1500',
  `Location` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `Own` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `noMortFees` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `monopolyTransactions` (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `GameID` varchar(25) NOT NULL,
  `pFrom` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `pTo` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `FromP` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `FromM` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `ToP` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ToM` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `Status` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `Mortgaged` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

