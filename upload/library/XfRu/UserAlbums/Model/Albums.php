<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Albums.php 336 2011-11-17 17:59:01Z pepelac $ $Date: 2011-11-17 18:59:01 +0100 (Thu, 17 Nov 2011) $ $Revision: 336 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_Model_Albums extends XenForo_Model
{
	const FETCH_USER = 0x01;
	const FETCH_AVATAR = 0x02;

	const ERROR_ALBUM_MODERATED = 10001;
	const ERROR_WRONG_HASH = 10002;

	const ALBUM_IS_NOT_VIEWABLE = 'Album is not viewable';

	const SPRITES_PATH = '/data/xfru/useralbums/sprites/';

	protected $_contentMapCache;

	public function getAlbumById($albumId, $fetchOptions = array())
	{
		if (!$albumId)
		{
			return false;
		}

	    $options = $this->prepareFetchOptions($fetchOptions);

//		$conditions = '';
//		if (!empty($options['conditions']))
//		{
//			$conditions .= ' AND '.$options['conditions'];
//		}

		$sql = '
			SELECT album.*
				' . $options['selectFields'] . '
			FROM xfr_useralbum AS album' . $options['joinTables'] . '
			WHERE album.album_id = ? 
			'.$options['orderBy'].'
		';
	    return $this->_getDb()->fetchRow($sql, $albumId);
	}

	public function getAlbumsByIds(array $albumIds)
	{
		return $this->fetchAllKeyed('
			SELECT `album`.*, `user`.username, user.avatar_date, user.gravatar, image_data.data_id, image_data.file_hash, image_data.thumbnail_width
			FROM xfr_useralbum AS `album`
			INNER JOIN xf_user AS `user` ON
				(`user`.user_id = `album`.user_id)
			LEFT JOIN xfr_useralbum_image AS image ON image.image_id = album.last_image_id
			LEFT JOIN xfr_useralbum_image_data AS image_data ON image_data.data_id = image.data_id
			WHERE `album`.album_id IN ('.$this->_getDb()->quote($albumIds).')
		', 'album_id');
	}

	public function getAlbumsInRange($start, $limit)
	{
		$db = $this->_getDb();

		return $db->fetchAll($db->limit('
			SELECT *
			FROM xfr_useralbum
			WHERE album_id > ?
			ORDER BY album_id
		', $limit), $start);
	}

	public function getModeratedAlbums()
	{
		return $this->fetchAllKeyed('
			SELECT `album`.album_id, album.createdate
			FROM xfr_useralbum AS `album`
			WHERE `album`.moderation = 1
		', 'album_id');
	}

	public function getAlbumIdsInRange($start, $limit)
	{
		$db = $this->_getDb();

		return $db->fetchCol($db->limit('
			SELECT album_id
			FROM xfr_useralbum AS album
			WHERE album_id > ?
			ORDER BY album_id
		', $limit), $start);
	}

	public function getUserAlbums($userId, $fetchOptions = array())
	{
		if (!$userId)
		{
			return array();
		}

		$options = $this->prepareFetchOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		$conditions = '';
		if (!empty($options['conditions']))
		{
			$conditions .= ' AND '.$options['conditions'];
		}

//		$sql = '
//			SELECT album.*, image_data.data_id, image_data.file_hash, image_data.thumbnail_width
//				' . $options['selectFields'] . '
//			FROM xfr_useralbum AS album' . $options['joinTables'] . '
//			LEFT JOIN xfr_useralbum_image AS image ON image.image_id = album.last_image_id
//			LEFT JOIN xfr_useralbum_image_data AS image_data ON image_data.data_id = image.data_id
//			WHERE album.user_id = ' . $this->_getDb()->quote($userId) . $conditions . '
//			'.$options['orderBy'].'
//		';

		$sql = '
			SELECT album.*
				' . $options['selectFields'] . ',
				image_data.data_id AS image_data_id, image_data.file_hash AS image_file_hash
			FROM xfr_useralbum AS album' . $options['joinTables'] . '
			LEFT JOIN xfr_useralbum_image AS image ON image.image_id = album.last_image_id
			LEFT JOIN xfr_useralbum_image_data AS image_data ON image_data.data_id = image.data_id
			WHERE album.user_id = ' . $this->_getDb()->quote($userId) . $conditions . '
			'.$options['orderBy'].'
		';

		if (!empty($limitOptions))
		{
			return $this->fetchAllKeyed($this->limitQueryResults($sql, $limitOptions['limit'], $limitOptions['offset']), 'album_id');
		} else {
			return $this->fetchAllKeyed($sql, 'album_id');
		}

	}

	public function getAlbums($fetchOptions = array())
	{
		$options = $this->prepareFetchOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		$where = '';

		if (!empty($options['conditions']))
		{
			$where .= 'WHERE '.$options['conditions'];
		}

		$sql = '
			SELECT album.*
				' . $options['selectFields'] . ',
				image_data.data_id AS image_data_id, image_data.file_hash AS image_file_hash
			FROM xfr_useralbum AS album' . $options['joinTables'] . '
			LEFT JOIN xfr_useralbum_image AS image ON image.image_id = album.last_image_id
			LEFT JOIN xfr_useralbum_image_data AS image_data ON image_data.data_id = image.data_id
			'.$where.'
			'.$options['orderBy'].'
		';

		return $this->fetchAllKeyed($this->limitQueryResults($sql, $limitOptions['limit'], $limitOptions['offset']), 'album_id');
	}

	public function getLatestAlbums()
	{
        $options = XenForo_Application::get('options');
		$fetchOptions = array(
			'join' => XfRu_UserAlbums_Model_Albums::FETCH_USER,
			'order' => array('album.last_image_date' => 'DESC', 'album.createdate' => 'DESC'),
			'limit' => $options->XfRu_UA_albumsPerSidebarBlock,
			'showEmptyAlbums' => false
		);

		return $this->getAlbums($fetchOptions);
	}

	public function getRandomAlbums()
	{
        $options = XenForo_Application::get('options');
		$fetchOptions = array(
			'join' => XfRu_UserAlbums_Model_Albums::FETCH_USER,
			'order' => array('RAND()' => ''),
			'limit' => $options->XfRu_UA_albumsPerSidebarBlock,
			'showEmptyAlbums' => false
		);

		return $this->getAlbums($fetchOptions);
	}

    public function getPopularAlbums()
    {
        $options = XenForo_Application::get('options');
        $fetchOptions = array(
            'join' => XfRu_UserAlbums_Model_Albums::FETCH_USER,
            'order' => array('album.likes' => 'DESC'),
            'limit' => $options->XfRu_UA_albumsPerSidebarBlock,
            'showEmptyAlbums' => false
        );

        return $this->getAlbums($fetchOptions);
    }

	public function countAlbums($userId = null, $fetchOptions = array())
	{
		$options = $this->prepareFetchOptions($fetchOptions);

		$user = $glue = '';

	    if ($userId)
	    {
		    $user = 'album.user_id = ' . $this->_getDb()->quote($userId);
	    }

		if ($user && !empty($options['conditions']))
		{
			$glue = ' AND ';
		}

		$sql = '
			SELECT COUNT(album.album_id) AS total
			FROM xfr_useralbum AS album
		';

		if (!empty($user) || !empty($options['conditions']))
		{
			$sql .= 'WHERE '.$user.$glue.$options['conditions'];
		}

	    return $this->_getDb()->fetchOne($sql);
	}

	public function getUserAlbumsCount($userId)
	{
		return $this->_getDb()->fetchOne('
			SELECT COUNT(album.album_id) AS total
			FROM xfr_useralbum AS album
			WHERE album.user_id = ' . $this->_getDb()->quote($userId) .'
		');
	}

	public function getUserVisibleAlbumsCount($userId)
	{
		$options = $this->prepareFetchOptions(array('userId' => XenForo_Visitor::getUserId()));

		$sql = '
			SELECT COUNT(album.album_id) AS total
			FROM xfr_useralbum AS album
			WHERE album.user_id = ' . $this->_getDb()->quote($userId)
		;

		if (!empty($options['conditions']))
		{
			$sql .= ' AND '.$options['conditions'];
		}

		return $this->_getDb()->fetchOne($sql);
	}

	public function getAlbumLastImage($albumId)
	{
		return $this->_getDb()->fetchRow('
			SELECT `image`.*,
				' . XfRu_UserAlbums_Model_Images::$dataColumns . '
			FROM xfr_useralbum_image AS `image`
			INNER JOIN xfr_useralbum_image_data AS `data` ON
				(`data`.data_id = `image`.data_id)
			WHERE `image`.album_id = ? ORDER BY image_date DESC LIMIT 1
		', $albumId);
	}

	public function getUserAlbumIdByAccessHash($hash)
	{
		return $this->_getDb()->fetchOne("
			SELECT album_id FROM xfr_useralbum
			WHERE access_hash = ?
		", array('access_hash' => $hash));
	}

	public function logAlbumView($albumId)
	{
		$this->_getDb()->query('
			INSERT DELAYED INTO xfr_useralbum_album_view
				(album_id)
			VALUES
				(?)
		', $albumId);
	}

	public function updateAlbumViews()
	{
		$db = $this->_getDb();

		$updates = $db->fetchPairs('
			SELECT album_id, COUNT(*)
			FROM xfr_useralbum_album_view
			GROUP BY album_id
		');

		XenForo_Db::beginTransaction($db);

		$db->query('TRUNCATE TABLE xfr_useralbum_album_view');

		foreach ($updates AS $albumId => $views)
		{
			$db->query('
				UPDATE xfr_useralbum SET
					view_count = view_count + ?
				WHERE album_id = ?
			', array($views, $albumId));
		}

		XenForo_Db::commit($db);
	}

	public function generateAccessHash()
	{
		$hash = XenForo_Application::generateRandomString(10);
	    while ($this->getUserAlbumIdByAccessHash($hash))
	    {
		    $hash = XenForo_Application::generateRandomString(10);
	    }

	    return $hash;
	}

	public function isAccessHashExists($hash)
	{
		return $this->_getDb()->fetchOne("
			SELECT access_hash FROM xfr_useralbum
			WHERE access_hash = ?
		", array('access_hash' => $hash));
	}

	public function assertAlbumValidAndViewable($albumId, $albumHash = null)
	{
		$fetchOptions = array(
			'join' => self::FETCH_USER
		);
		$album = $this->getAlbumById($albumId, $fetchOptions);

		if (empty($album))
		{
			return array();
		}

		$album['title'] = XenForo_Helper_String::censorString($album['title']);

		if (XfRu_UserAlbums_Permissions::canApproveUnapprove())
		{
			return $album;
		}

		if ($album['moderation'])
		{
			throw new Exception(self::ALBUM_IS_NOT_VIEWABLE, self::ERROR_ALBUM_MODERATED);
		}

		if ($album['album_type'] == 'private')
		{
			if (!$this->isAlbumOwner($album) && $album['access_hash'] != $albumHash)
			{
				throw new Exception(self::ALBUM_IS_NOT_VIEWABLE, self::ERROR_WRONG_HASH);
			}
		}
		
	    return $album;
	}

	public function getBreadCrumbs(array $user, $album = array())
	{
		$breadCrumbs = array();

		$breadCrumbs['user_'.$user['user_id']] = array(
			'href' => XenForo_Link::buildPublicLink('full:useralbums/list', $user),
			'value' => new XenForo_Phrase('xfr_useralbums_xs_albums', array('name' => $user['username'])),
		);

		if (!empty($album))
		{
			$breadCrumbs[$album['album_id']] = array(
				'href' => XenForo_Link::buildPublicLink('full:useralbums/view', $album),
				'value' => XenForo_Helper_String::censorString($album['title'])
			);
		}

		return $breadCrumbs;
	}

	public function prepareAlbum($album)
	{
		$album['lastImageInfo'] = array(
			'image_date' => $album['last_image_date'],
			'image_id' => $album['last_image_id'],
			'title' => $album['last_image_filename'],

		);
		$album['hasDescription'] = $album['description'] != '';
		$album['title'] = XenForo_Helper_String::censorString($album['title']);
		$album['spriteUrl'] = $this->getAlbumThumbnailSpriteUrl($album);
	    return $album;
	}

	public function isAlbumOwner(array $album)
	{
		return XenForo_Visitor::getUserId() == $album['user_id'];
	}


	/*
	 * User Permission Functions
	 */

	public function canAddImages(array $album)
	{
		$viewingUser = XenForo_Visitor::getInstance()->toArray();
		return $this->canUploadImage($album, $viewingUser);
	}

	public function canUploadImage($album, $viewingUser)
	{
        if ($album['moderation'])
        {
            return false;
        }

        if ($viewingUser['user_id'])
        {
            return ($album['user_id'] == $viewingUser['user_id']) || $album['album_type'] == 'global';
        }

		return false;
	}

	public function isAlbumViewable(&$album, $accessHash = null)
	{
		if ($album['moderation'] && !XfRu_UserAlbums_Permissions::canApproveUnapprove())
		{
			return false;
		}

		$isAlbumOwner = $this->isAlbumOwner($album);

		if (!$album['image_count'])
		{
			if (!$isAlbumOwner || !XfRu_UserAlbums_Permissions::canViewEmptyAlbums())
			{
				return false;
			}
		}

		if ($album['album_type'] == 'private')
		{
			if (XfRu_UserAlbums_Permissions::canViewPrivateAlbums())
			{
				return true;
			} else {
				if (!$this->isAlbumOwner($album) && ($accessHash != $album['access_hash']))
				{
					return false;
				}
			}
		}

		return true;
	}

	public function isAlbumEditable(&$album)
	{
		if (XfRu_UserAlbums_Permissions::canEditAlbumsByAnyone())
		{
			return true;
		}

		return XfRu_UserAlbums_Permissions::canEditAlbumsBySelf() && $album['user_id'] == XenForo_Visitor::getUserId();
	}

	public function isAlbumDeletable(&$album)
	{
		if (XfRu_UserAlbums_Permissions::canDeleteAlbumsByAnyone())
		{
			return true;
		}

		return XfRu_UserAlbums_Permissions::canDeleteAlbumsBySelf() && $album['user_id'] == XenForo_Visitor::getUserId();
	}

	public function getAlbumIdsMapFromArray(array $source, $key)
	{
		$albumIds = array();
		foreach ($source AS $data)
		{
			$albumIds[] = $data[$key];
		}
		return $this->getImportContentMap('xfr_UA_Album', $albumIds);
	}

    /**
     * Gets an import content map to map old IDs to new IDs for the given content type.
     *
     * @param string $contentType
     * @param array|bool $ids
     *
     * @return array
     */
	public function getImportContentMap($contentType, $ids = false)
	{
		$logTable = 'xf_import_log';

		$db = $this->_getDb();

		if ($ids === false)
		{
			return $db->fetchPairs('
				SELECT old_id, new_id
				FROM ' . $logTable . '
				WHERE content_type = ?
			', $contentType);
		}

		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		if (!$ids)
		{
			return array();
		}

		$final = array();
		if (isset($this->_contentMapCache[$contentType]))
		{
			$lookup = $this->_contentMapCache[$contentType];
			foreach ($ids AS $key => $id)
			{
				if (isset($lookup[$id]))
				{
					$final[$id] = $lookup[$id];
					unset($ids[$key]);
				}
			}
		}

		if (!$ids)
		{
			return $final;
		}

		foreach ($ids AS &$id)
		{
			$id = strval($id);
		}

		$merge = $db->fetchPairs('
			SELECT old_id, new_id
			FROM ' . $logTable . '
			WHERE content_type = ?
				AND old_id IN (' . $db->quote($ids) . ')
		', $contentType);

		if (isset($this->_contentMapCache[$contentType]))
		{
			$this->_contentMapCache[$contentType] += $merge;
		}
		else
		{
			$this->_contentMapCache[$contentType] = $merge;
		}

		return $final + $merge;
	}

    /**
     * Imports an album image
     *
     * @param integer $oldImageId
     * @param string $fileName
     * @param $description
     * @param string $tempFile
     * @param integer $userId
     * @param integer $albumId
     * @param integer $date
     * @param array $image data to import
     * @param XenForo_Model_Import $importModel import model
     *
     * @return Imported image ID
     */
	public function importAlbumImage($oldImageId, $fileName, $description, $tempFile, $userId, $albumId, $date, array $image = array(), &$importModel)
	{
		$upload = new XenForo_Upload($fileName, $tempFile);

		try {
			$dataExtra = array(
				'upload_date' => $date,
				'attach_count' => 1,
				'description' => $description
			);
			$dataId = $this->getModelFromCache('XfRu_UserAlbums_Model_Images')->insertUploadedImageData($upload, $userId, $dataExtra);
		} catch (XenForo_Exception $e) {
			return false;
		}

		$dw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Image');
		$dw->setImportMode(true);
		$dw->bulkSet(array(
			'data_id' => $dataId,
			'album_id' => $albumId,
			'image_date' => $date,
			'unassociated' => 0
		));
		$dw->bulkSet($image);
		$dw->save();

		$newImageId = $dw->get('image_id');

		$albumDw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Album');
		$albumDw->setExistingData($albumId);

		$imagesCount = $albumDw->get('image_count') + 1;
		$albumDw->bulkSet(array(
			'image_count' => $imagesCount,
			'last_image_id' => $newImageId,
			'last_image_date' => $date,
			'last_image_filename' => $fileName,
		));
		$albumDw->save();

		$importModel->logImportData('xfr_UA_Image', $oldImageId, $newImageId);

		return $newImageId;
	}

	private function prepareFetchOptions(array $fetchOptions)
	{
		$selectFields = '';
		$joinTables = '';
		$conditions = '';
		$orderBy = '';

		if (!XfRu_UserAlbums_Permissions::canApproveUnapprove())
		{
			$conditions .= "album.moderation = 0";
		}

		if (!XfRu_UserAlbums_Permissions::canViewPrivateAlbums())
		{
			$glue = '';
			if (!empty($conditions))
			{
				$glue = ' AND ';
			}
			if (array_key_exists('userId', $fetchOptions) && $fetchOptions['userId'])
			{
				$conditions .= $glue."((album.album_type = 'public' OR album.album_type = 'global') OR (album.album_type = 'private' AND album.user_id = ".(int)$fetchOptions['userId']."))";
			} else {
				$conditions .= $glue."(album.album_type = 'public' OR album.album_type = 'global')";
			}
		}

		if (!XfRu_UserAlbums_Permissions::canViewEmptyAlbums())
		{
			$glue = '';
			if (!empty($conditions))
			{
				$glue = ' AND ';
			}
			if (array_key_exists('userId', $fetchOptions) && $fetchOptions['userId'])
			{
				$conditions .= $glue."(album.last_image_date > 0 OR (album.last_image_date = 0 AND album.user_id = ".(int)$fetchOptions['userId']."))";
			} else {
				$conditions .= $glue."album.last_image_date > 0";
			}
		} else {
			if (array_key_exists('showEmptyAlbums', $fetchOptions))
			{
				if (!$fetchOptions['showEmptyAlbums'])
				{
					$glue = '';
					if (!empty($conditions))
					{
						$glue = ' AND ';
					}
					$conditions .= $glue."album.last_image_date > 0";
				}
			}
		}

//		if (!empty($fetchOptions['conditions']))
//		{
//		}

		if (array_key_exists('join', $fetchOptions))
		{
			if ($fetchOptions['join'] & self::FETCH_USER)
			{
				$selectFields .= ',
					user.*';
				$joinTables .= '
					LEFT JOIN xf_user AS user ON
						(user.user_id = album.user_id)';
			} else if ($fetchOptions['join'] & self::FETCH_AVATAR) {
				$selectFields .= ',
					user.avatar_date, user.gravatar';
				$joinTables .= '
					LEFT JOIN xf_user AS user ON
						(user.user_id = album.user_id)';
			}
		}

		if (!empty($fetchOptions['order']))
		{
			$order = array();
		    foreach ($fetchOptions['order'] as $field => $dir)
		    {
			    $order[] = $field." ".$dir;
		    }
		    $orderBy = 'ORDER BY '.implode(', ', $order);
		    unset($order);
		}

	    return array(
		    'selectFields' => $selectFields,
		    'joinTables' => $joinTables,
		    'conditions' => $conditions,
		    'orderBy' => $orderBy
	    );

	}


	public function getAlbumThumbnailSpriteFilePath(array $album)
	{
		return XenForo_Application::getInstance()->getRootDir()
			. self::SPRITES_PATH . floor($album['album_id'] / 1000)
			. "/$album[album_id]-$album[sprite_hash].jpg";
	}

	public function getAlbumThumbnailSpriteUrl(array $album)
	{
		return ltrim(self::SPRITES_PATH, '/') . floor($album['album_id'] / 1000)
			. "/$album[album_id]-$album[sprite_hash].jpg";
	}

	public function rebuildAlbumSprite(array $album)
	{
		/* @var $imagesModel XfRu_UserAlbums_Model_Images */
		$imagesModel = $this->getModelFromCache('XfRu_UserAlbums_Model_Images');

		if ($album['image_count'] < 2)
		{
			return;
		}

		if (!$album['sprite_hash'])
		{
			$album['sprite_hash'] = md5($album['album_id'].$album['user_id'].$album['createdate']);
			/* @var $albumDw XfRu_UserAlbums_DataWriter_Album */
			$albumDw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Album', XenForo_DataWriter::ERROR_SILENT);
			if ($albumDw->setExistingData($album['album_id']))
			{
				$albumDw->set('sprite_hash', $album['sprite_hash']);
				$albumDw->save();
			}

		}

		$latestImages = $imagesModel->getLatestImagesInAlbum($album['album_id'], 10);
		/* @var $spritesModel XfRu_UserAlbums_Model_Sprites */
		$spritesModel = $this->getModelFromCache('XfRu_UserAlbums_Model_Sprites');

		$spritesModel->assembleSprites($latestImages, $album);

	}

    public function getActiveUsers()
    {
        return $this->_getDb()->fetchAll("
            SELECT user.user_id, user.username, COUNT(album.album_id) AS albums
            FROM xfr_useralbum AS album
            LEFT JOIN xf_user AS user ON user.user_id = album.user_id
            WHERE album.`album_type` = 'public' AND album.`image_count` > 0
            GROUP BY album.user_id
            ORDER BY albums DESC
            LIMIT 5
        ");
    }

	/***********************/

	public function doSmth()
	{
//		$this->_getDb()->query("
//			INSERT INTO xf_content_type_field
//				(content_type, field_name, field_value)
//			VALUES
//				('xfr_useralbum', 'news_feed_handler_class', 'XfRu_UserAlbums_NewsFeedHandler_Album'),
//				('xfr_useralbum_image', 'news_feed_handler_class', 'XfRu_UserAlbums_NewsFeedHandler_Image'),
//				('xfr_useralbum_image_cmnt', 'news_feed_handler_class', 'XfRu_UserAlbums_NewsFeedHandler_ImageComment')
//		");
//
//		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
	}
}