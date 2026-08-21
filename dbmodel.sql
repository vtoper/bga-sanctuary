
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- agricola implementation : © Timothée Pecatte <tim.pecatte@gmail.com>, Vincent Toper <vincent.toper@gmail.com>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql


CREATE TABLE IF NOT EXISTS `meeples` (
  `meeple_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `meeple_location` varchar(110) NOT NULL,
  `meeple_state` int(10),
  `type` varchar(32),
  `player_id` int(10) NULL,
  `x` varchar(100) NULL,
  `y` varchar(100) NULL,
  PRIMARY KEY (`meeple_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Store zoo cards
CREATE TABLE IF NOT EXISTS `tiles` (
  `tiles_id` varchar(100)  NOT NULL,
  `tiles_location` varchar(32) NOT NULL,
  `tiles_state` int(10) DEFAULT 0,
  `player_id` int(10) NULL,
  `extra_datas` JSON NULL,
  `x` int(10) NULL,
  `y` int(10) NULL,
  PRIMARY KEY (`tiles_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Store action cards
CREATE TABLE IF NOT EXISTS `actioncards` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_location` varchar(32) NOT NULL,
  `card_state` int(10) DEFAULT 0,
  `level` int(1) NOT NULL DEFAULT 1,
  `type` varchar(32) NOT NULL,
  `player_id` int(10) NULL,
  `extra_datas` JSON NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Additional player's info
--ALTER TABLE `player` ADD `map_id` varchar(10);
ALTER TABLE `player` ADD `appeal` INT(10) NOT NULL DEFAULT 0;
ALTER TABLE `player` ADD `conservation_marker` INT(10) NOT NULL DEFAULT 0;


-- CORE TABLES --
CREATE TABLE IF NOT EXISTS `global_variables` (
  `name` varchar(255) NOT NULL,
  `value` JSON,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `move_id` int(10) NOT NULL,
  `table` varchar(32) NOT NULL,
  `primary` varchar(32) NOT NULL,
  `type` varchar(32) NOT NULL,
  `affected` JSON,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `gamelog` ADD `cancel` TINYINT(1) NOT NULL DEFAULT 0;
