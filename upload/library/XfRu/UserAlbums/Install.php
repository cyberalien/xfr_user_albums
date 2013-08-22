<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Install.php 336 2011-11-17 17:59:01Z pepelac $ $Date: 2011-11-17 18:59:01 +0100 (Thu, 17 Nov 2011) $ $Revision: 336 $
 * @author Pepelac
 *
 */
class XfRu_UserAlbums_Install
{
	/* @var $instance XfRu_UserAlbums_Install */
	private static $instance;

	/* @var $db Zend_Db_Adapter_Abstract */
	protected $db;

	private $installSteps;


	/**
	 * @static
	 * @return XfRu_UserAlbums_Install
	 */
	public static function getInstance()
	{
		if (!self::$instance)
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	private function __construct()
	{
		$this->installSteps = array(
			'100' => 'Beta 1',
//			'1000032' => 'Beta 2',
//			'1000033' => 'Beta 3',
			'1000034' => 'Beta 4',
			'1000035' => 'Beta 5',
			'1000037' => 'Beta 7',
		);
	}

	/**
	 * @return Zend_Db_Adapter_Abstract
	 */
	protected function getDb()
	{
		if ($this->db === null)
		{
			$this->db = XenForo_Application::get('db');
		}

		return $this->db;
	}


	public static function install($existingAddOn, $addOnData)
	{
		$install = self::getInstance();

		$installedVersion = ($existingAddOn) ? $existingAddOn['version_id'] : false;

		foreach ($install->installSteps as $stepVersion => $versionText)
		{
			if ($installedVersion >= $stepVersion)
			{
				// skip this step, this version is already installed
				continue;
			}

			$method = 'installStep' . $stepVersion;
			if (method_exists($install, $method) === false)
			{
				continue;
			}

			$install->$method();
		}
	}

	private function installStep100()
	{
		$db = $this->getDb();

		$db->query("
			CREATE TABLE IF NOT EXISTS `xfr_useralbum` (
			  `album_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `createdate` int(10) unsigned NOT NULL DEFAULT '0',
			  `title` varchar(100) NOT NULL DEFAULT '',
			  `description` text,
			  `cover_image_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `album_type` enum('public','private') NOT NULL DEFAULT 'public',
			  `access_hash` varchar(10) DEFAULT NULL,
			  `moderation` tinyint(1) unsigned NOT NULL DEFAULT '0',
			  `image_count` int(10) unsigned NOT NULL DEFAULT '0',
			  `view_count` int(10) unsigned NOT NULL DEFAULT '0',
			  `last_image_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `last_image_date` int(10) unsigned NOT NULL DEFAULT '0',
			  `last_image_filename` varchar(100) DEFAULT NULL,
			  PRIMARY KEY (`album_id`),
			  UNIQUE KEY `access_hash` (`access_hash`),
			  KEY `last_image_id_date` (`last_image_id`,`last_image_date`),
			  KEY `user_id` (`user_id`,`last_image_date`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		");

		$db->query("
			CREATE TABLE IF NOT EXISTS `xfr_useralbum_image` (
			  `image_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `data_id` int(10) unsigned NOT NULL,
			  `album_id` int(10) unsigned NOT NULL,
			  `image_date` int(10) unsigned NOT NULL,
			  `temp_hash` varchar(32) NOT NULL DEFAULT '',
			  `unassociated` tinyint(3) unsigned NOT NULL,
			  `view_count` int(10) unsigned NOT NULL DEFAULT '0',
			  `likes` int(10) unsigned NOT NULL,
			  `like_users` blob NOT NULL,
			  PRIMARY KEY (`image_id`),
			  KEY `album_id_date` (`album_id`,`image_date`),
			  KEY `temp_hash_image_date` (`temp_hash`,`image_date`),
			  KEY `unassociated_image_date` (`unassociated`,`image_date`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		");

		$db->query("
			CREATE TABLE IF NOT EXISTS `xfr_useralbum_image_data` (
			  `data_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `user_id` int(10) unsigned NOT NULL,
			  `upload_date` int(10) unsigned NOT NULL,
			  `filename` varchar(100) NOT NULL,
			  `description` text,
			  `file_size` int(10) unsigned NOT NULL,
			  `file_hash` varchar(32) NOT NULL,
			  `width` int(10) unsigned NOT NULL DEFAULT '0',
			  `height` int(10) unsigned NOT NULL DEFAULT '0',
			  `thumbnail_width` int(10) unsigned NOT NULL DEFAULT '0',
			  `thumbnail_height` int(10) unsigned NOT NULL DEFAULT '0',
			  `attach_count` int(10) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`data_id`),
			  KEY `user_id_upload_date` (`user_id`,`upload_date`),
			  KEY `attach_count` (`attach_count`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		");

		$db->query("
			CREATE TABLE IF NOT EXISTS `xfr_useralbum_image_comment` (
			  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `image_id` int(10) unsigned NOT NULL,
			  `user_id` int(10) unsigned NOT NULL,
			  `username` varchar(50) NOT NULL,
			  `comment_date` int(10) unsigned NOT NULL,
			  `message` mediumtext NOT NULL,
			  PRIMARY KEY (`comment_id`),
			  KEY `image_id_comment_date` (`image_id`,`comment_date`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		");

		$db->query("
			CREATE TABLE IF NOT EXISTS `xfr_useralbum_album_view` (
			  `album_id` int(10) unsigned NOT NULL,
			  KEY `album_id` (`album_id`)
			) ENGINE=MEMORY DEFAULT CHARSET=utf8;
		");

		$db->query("
			CREATE TABLE IF NOT EXISTS `xfr_useralbum_image_view` (
			  `image_id` int(10) unsigned NOT NULL,
			  KEY `image_id` (`image_id`)
			) ENGINE=MEMORY DEFAULT CHARSET=utf8;
		");

		$db->query("
			INSERT INTO xf_content_type
				(content_type, addon_id, fields)
			VALUES
				('xfr_useralbum', 'XfRuUserAlbums', ''),
				('xfr_useralbum_image', 'XfRuUserAlbums', ''),
				('xfr_useralbum_image_cmnt', 'XfRuUserAlbums', '')
		");

		// insert handlers

		$db->query("
			INSERT INTO xf_content_type_field
				(content_type, field_name, field_value)
			VALUES
				('xfr_useralbum', 'search_handler_class', 'XfRu_UserAlbums_Search_DataHandler_Album'),
				('xfr_useralbum_image', 'alert_handler_class', 'XfRu_UserAlbums_AlertHandler_Image'),
				('xfr_useralbum_image', 'like_handler_class', 'XfRu_UserAlbums_LikeHandler_Image'),
				('xfr_useralbum_image', 'report_handler_class', 'XfRu_UserAlbums_ReportHandler_Image'),
				('xfr_useralbum_image_cmnt', 'alert_handler_class', 'XfRu_UserAlbums_AlertHandler_Comment'),
				('xfr_useralbum_image_cmnt', 'like_handler_class', 'XfRu_UserAlbums_LikeHandler_Comment'),
				('xfr_useralbum_image_cmnt', 'report_handler_class', 'XfRu_UserAlbums_ReportHandler_Comment')
		");

		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
		unset($db);

		// try to create folders
		$albumHelper = new XfRu_UserAlbums_Helper_Album();
	    $albumHelper->makeDirs();
	}

	private function installStep1000034()
	{
		$db = $this->getDb();

		$db->query("
			ALTER TABLE  `xfr_useralbum`
			ADD  `likes` INT( 10 ) UNSIGNED NOT NULL ,
			ADD  `like_users` BLOB NOT NULL
		");

		$db->query("
			INSERT INTO xf_content_type_field
				(content_type, field_name, field_value)
			VALUES
				('xfr_useralbum', 'moderation_queue_handler_class', 'XfRu_UserAlbums_ModerationQueueHandler_Album'),
				('xfr_useralbum', 'like_handler_class', 'XfRu_UserAlbums_LikeHandler_Album'),
				('xfr_useralbum', 'alert_handler_class', 'XfRu_UserAlbums_AlertHandler_Album'),
				('xfr_useralbum', 'news_feed_handler_class', 'XfRu_UserAlbums_NewsFeedHandler_Album'),
				('xfr_useralbum_image', 'news_feed_handler_class', 'XfRu_UserAlbums_NewsFeedHandler_Image'),
				('xfr_useralbum_image_cmnt', 'news_feed_handler_class', 'XfRu_UserAlbums_NewsFeedHandler_ImageComment')
		");

		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();

		$moderatedAlbums = XenForo_Model::create('XfRu_UserAlbums_Model_Albums')->getModeratedAlbums();

		foreach ($moderatedAlbums as $album)
		{
			$bind = array(
				'content_type' => XfRu_UserAlbums_Helper::CT_ALBUM,
				'content_id' => $album['album_id'],
				'content_date' => $album['createdate'],
			);

			$db->insert('xf_moderation_queue', $bind);
		}
	}

	private function installStep1000035()
	{
		$db = $this->getDb();

		$db->query("
			ALTER TABLE  `xfr_useralbum_image` ADD  `comment_count` INT( 10 ) UNSIGNED NOT NULL AFTER  `view_count`
		");

		$db->query("
			ALTER TABLE  `xfr_useralbum` ADD  `sprite_hash` VARCHAR( 32 ) NOT NULL AFTER  `access_hash`
		");

		// try to create folders
		$albumHelper = new XfRu_UserAlbums_Helper_Album();
	    $albumHelper->makeSpriteDir();
	}

	private function installStep1000037()
	{
		$db = $this->getDb();

		$db->query("
			ALTER TABLE `xfr_useralbum` CHANGE `album_type` `album_type` ENUM('public','private','global')  NOT NULL  DEFAULT 'public';
		");
	}

	public static function uninstall()
	{
		$db = XenForo_Application::get('db');

		$commentIds = $db->fetchAll("
			SELECT comment_id
			FROM xfr_useralbum_image_comment
		");

		XenForo_Model::create('XenForo_Model_Alert')->deleteAlerts('xfr_useralbum_image_cmnt', $commentIds);

		$imagesIds = $db->fetchAll("
			SELECT image_id
			FROM xfr_useralbum_image
		");
		XenForo_Model::create('XenForo_Model_Alert')->deleteAlerts('xfr_useralbum_image', $imagesIds);
		XenForo_Model::create('XenForo_Model_Like')->deleteContentLikes('xfr_useralbum_image', $imagesIds);

		$db->query('
			DROP TABLE IF EXISTS
			`xfr_useralbum`,
			`xfr_useralbum_image`,
			`xfr_useralbum_image_data`,
			`xfr_useralbum_album_view`,
			`xfr_useralbum_image_view`,
			`xfr_useralbum_image_comment`
			;
		');

		$db->query("
			DELETE FROM xf_content_type
			WHERE content_type IN ('xfr_useralbum', 'xfr_useralbum_image', 'xfr_useralbum_image_cmnt')
		");

		$db->query("
			DELETE FROM xf_content_type_field
			WHERE content_type IN ('xfr_useralbum', 'xfr_useralbum_image', 'xfr_useralbum_image_cmnt')
		");

		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();

		$db->query("
			DELETE FROM xf_moderation_queue
			WHERE content_type IN ('xfr_useralbum')
		");

	    unset($db);
	}
}