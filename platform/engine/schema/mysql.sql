--
-- Database schema.
-- Mysql database schema
--
-- @package core
-- @license $licence
-- @author Marcus Povey <marcus@marcus-povey.co.uk>
-- @copyright Marcus Povey 2009-2010
-- @link http://platform.barcamptransparency.org/
-- 


-- BCT Objects
--
-- This table contains the root definition for all objects in the system
-- including objects, news items, calendar items etc etc etc
--
-- guid : Unique integer representing the entity in the system
-- type : Type hierarchy of this object in the form "foo:bar", where bar is a subtype of foo. This
--         lets you perform a search for the 'foo:%' supertype and return all, possibly unknown, subtypes.
--         Types are delimited by ":"
-- handling_class : The system class physically handling this type.
-- created_ts : Unix timestamp denoting when this entity was created.
CREATE table prefix_bctobjects (
	`guid` bigint(20) unsigned  NOT NULL auto_increment,
	`type` varchar(256) NOT NULL,
	`handling_class` varchar(50) NOT NULL,
	`created_ts` int(11) NOT NULL,

	PRIMARY KEY (`guid`),
	KEY (`type`),
	KEY (`handling_class`),
	KEY (`created_ts`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- BCT Object metadata
--
-- Arbitrary metadata attached to a system object, allowing for easy extension
-- of data items in the system.
-- 
-- id : Unique integer uniquely identifying this item of metadata.
-- guid : Object this item of metadata is attached to.
-- name : The name of the metadata.
-- value : Its value
CREATE table prefix_bctobjects_metadata (
	`id` bigint(20) unsigned NOT NULL auto_increment,
	`guid` bigint(20) unsigned  NOT NULL,

	`name` varchar(128) NOT NULL,
	`value` longblob NOT NULL,

	PRIMARY KEY (`id`),
	KEY (`guid`),
	KEY (`name`)

) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- Notes:
-- Annotations are implemented as a special entity class so that it can also have 
-- metadata and be passed through the same object handling methods.
--
-- All data can be saved using the highly abstract datastructure, but for speed you may
-- want to provide a less normalised structure for your datastructure 
