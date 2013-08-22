<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Images.php 336 2011-11-17 17:59:01Z pepelac $ $Date: 2011-11-17 18:59:01 +0100 (Thu, 17 Nov 2011) $ $Revision: 336 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_Model_Images extends XenForo_Model
{
	const IMAGES_PATH = '/xfru/useralbums/images/';
	const THUMBS_PATH = '/data/xfru/useralbums/thumbnails/';

	public static $dataColumns =
		'`data`.filename, `data`.description, `data`.file_size, `data`.file_hash, data.width, data.height, data.thumbnail_width, data.thumbnail_height';
	
	public function getImageParams(array $album, array $contentData, array $viewingUser = null)
	{
		if ($this->getAlbumsModel()->canUploadImage($album, $viewingUser))
		{
			return array(
				'hash' => md5(uniqid('', true)),
				'content_data' => $contentData
			);
		} else {
			return false;
		}
	}

	public function getImageConstraints()
	{
		$options = XenForo_Application::get('options');
		return array(
			'extensions' => preg_split('/\s+/', trim($options->XfRu_UA_imageExtentions)),
			'size' => $options->XfRu_UA_imageMaxFileSize * 1024,
			'count' => $options->XfRu_UA_imagesPerAblum,
			'width' => $options->XfRu_UA_imageDimensions['width'],
			'height' => $options->XfRu_UA_imageDimensions['height']
		);
	}

	public function getAlbumIdFromContentData(array $contentData)
	{
		return (isset($contentData['album_id']) ? $contentData['album_id'] : 0);
	}

	public function getImagesByAlbumId($contentIds, $albumType = null)
	{
		if (!is_array($contentIds))
		{
			$contentIds = array($contentIds);
		}

        $userCriteria = '';
        if ($albumType == 'global' && !XfRu_UserAlbums_Permissions::canEditAlbumsByAnyone())
        {
            $userCriteria = ' AND data.user_id = ' . XenForo_Visitor::getUserId();
        }

		return $this->fetchAllKeyed('
			SELECT `image`.*,
				' . self::$dataColumns . '
			FROM xfr_useralbum_image AS `image`
			INNER JOIN xfr_useralbum_image_data AS `data` ON
				(`data`.data_id = `image`.data_id)
			WHERE `image`.album_id IN (' . $this->_getDb()->quote($contentIds) . ')'. $userCriteria .'
			ORDER BY `image`.album_id, `image`.image_date
		', 'image_id');
	}

    public function getImageCountByAlbumId($albumId)
    {
        return $this->_getDb()->fetchOne("
			SELECT COUNT(*) AS image_count
			FROM xfr_useralbum_image
			WHERE album_id = ? AND unassociated = 0
		", array('album_id' => $albumId));
    }

	public function getLatestImageInAlbum($albumId)
	{
		return $this->_getDb()->fetchRow('
			SELECT `image`.*,
				' . self::$dataColumns . '
			FROM xfr_useralbum_image AS `image`
			INNER JOIN xfr_useralbum_image_data AS `data` ON
				(`data`.data_id = `image`.data_id)
			WHERE `image`.album_id = ?
			ORDER BY `image`.image_date DESC
			LIMIT 1
		', $albumId);
	}

	public function getLatestImagesInAlbum($albumId, $limit = 5)
	{
		return $this->_getDb()->fetchAll('
			SELECT `image`.*,
				' . self::$dataColumns . '
			FROM xfr_useralbum_image AS `image`
			INNER JOIN xfr_useralbum_image_data AS `data` ON
				(`data`.data_id = `image`.data_id)
			WHERE `image`.album_id = ?
			ORDER BY `image`.image_date DESC
			LIMIT '.(int)$limit.'
		', $albumId);
	}

	public function getImagesByTempHash($tempHash)
	{
		if (strval($tempHash) === '')
		{
			return array();
		}

		return $this->fetchAllKeyed('
			SELECT `image`.*,
				' . self::$dataColumns . '
			FROM xfr_useralbum_image AS `image`
			INNER JOIN xfr_useralbum_image_data AS `data` ON
				(`data`.data_id = `image`.data_id)
			WHERE `image`.temp_hash = ?
			ORDER BY `image`.image_date
		', 'image_id', $tempHash);
	}

	public function getImageDataById($dataId)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xfr_useralbum_image_data
			WHERE data_id = ?
		', $dataId);
	}

	public function getImageById($imageId)
	{
		return $this->_getDb()->fetchRow('
			SELECT `image`.*,
				' . self::$dataColumns . ',
				user.user_id, user.username, user.avatar_date, user.gravatar
			FROM xfr_useralbum_image AS `image`
			INNER JOIN xfr_useralbum_image_data AS `data` ON (`data`.data_id = `image`.data_id)
			INNER JOIN xf_user AS `user` ON (`user`.user_id = `data`.user_id)
			WHERE `image`.image_id = ?
		', $imageId);
	}

	public function getImagesByIds(array $imageIds)
	{
		return $this->fetchAllKeyed('
			SELECT `image`.*, `user`.user_id, `user`.username, `album`.title,
				' . self::$dataColumns . '
			FROM xfr_useralbum_image AS `image`
			INNER JOIN xfr_useralbum_image_data AS `data` ON
				(`data`.data_id = `image`.data_id)
			INNER JOIN xfr_useralbum AS `album` ON
				(`album`.album_id = `image`.album_id)
			INNER JOIN xf_user AS `user` ON
				(`user`.user_id = `album`.user_id)
			WHERE `image`.image_id IN ('.$this->_getDb()->quote($imageIds).')
		', 'image_id');
	}

	public function getImageInfoById($imageId)
	{
		return $this->_getDb()->fetchRow('
			SELECT `image`.*, `user`.user_id, `user`.username, `album`.title,
				' . self::$dataColumns . '
			FROM xfr_useralbum_image AS `image`
			INNER JOIN xfr_useralbum_image_data AS `data` ON
				(`data`.data_id = `image`.data_id)
			INNER JOIN xfr_useralbum AS `album` ON
				(`album`.album_id = `image`.album_id)
			INNER JOIN xf_user AS `user` ON
				(`user`.user_id = `album`.user_id)
			WHERE `image`.image_id = ?
		', $imageId);
	}

	public function getLatestImages()
	{
		$sql = '
			SELECT `image`.*, `user`.user_id, `user`.username, `album`.title AS albumTitle,
				' . self::$dataColumns . '
			FROM xfr_useralbum_image AS `image`
			INNER JOIN xfr_useralbum_image_data AS `data` ON
				(`data`.data_id = `image`.data_id)
			INNER JOIN xfr_useralbum AS `album` ON
				(`album`.album_id = `image`.album_id)
			INNER JOIN xf_user AS `user` ON
				(`user`.user_id = `album`.user_id)
			WHERE `album`.album_type = "public" AND moderation = 0
			ORDER BY `image`.image_date DESC
		';

		$limit = XenForo_Application::get('options')->XfRu_UA_displayImages;
		return $this->prepareImages($this->fetchAllKeyed($this->limitQueryResults($sql, $limit), 'image_id'));
	}

	public function getRandomImages()
	{
		$sql = '
			SELECT `image`.*, `user`.user_id, `user`.username, `album`.title AS albumTitle,
				' . self::$dataColumns . '
			FROM xfr_useralbum_image AS `image`
			INNER JOIN xfr_useralbum_image_data AS `data` ON
				(`data`.data_id = `image`.data_id)
			INNER JOIN xfr_useralbum AS `album` ON
				(`album`.album_id = `image`.album_id)
			INNER JOIN xf_user AS `user` ON
				(`user`.user_id = `album`.user_id)
			WHERE `album`.album_type = "public" AND moderation = 0
			ORDER BY RAND()
		';
		$limit = XenForo_Application::get('options')->XfRu_UA_displayImages;
		return $this->prepareImages($this->fetchAllKeyed($this->limitQueryResults($sql, $limit), 'image_id'));
	}

	public function getImageIdsInRange($start, $limit)
	{
		$db = $this->_getDb();

		return $db->fetchCol($db->limit('
			SELECT image_id
			FROM xfr_useralbum_image
			WHERE image_id > ?
			ORDER BY image_id
		', $limit), $start);
	}

	public function getImageDatasInRange($start, $limit)
	{
		$db = $this->_getDb();

		return $db->fetchAll($db->limit('
			SELECT *
			FROM xfr_useralbum_image_data
			WHERE data_id > ?
			ORDER BY data_id
		', $limit), $start);
	}

	public function getImageCountLimit()
	{
		$max = XenForo_Application::get('options')->XfRu_UA_imagesPerAblum;
		return ($max <= 0 ? true : $max);
	}

	public function insertUploadedImageData(XenForo_Upload $uploadedImage, $userId, array $extra = array())
	{
		$tempThumbFile = '';
		$dimensions = array();

		if ($uploadedImage->isImage())
		{
			$dimensions = array(
				'width' => $uploadedImage->getImageInfoField('width'),
				'height' => $uploadedImage->getImageInfoField('height'),
			);

			$tempThumbFile = tempnam(XenForo_Helper_File::getTempDir(), 'xf');
			if ($tempThumbFile)
			{
				$image = XenForo_Image_Abstract::createFromFile($uploadedImage->getTempFile(), $uploadedImage->getImageInfoField('type'));
				if ($dimensions['width'] > 900 || $dimensions['height'] > 600)
				{
					$image->thumbnail(900, 600);
				}
				if ($image)
				{
					$cropPoint = array();
					switch ($image->getOrientation())
					{
						case XenForo_Image_Abstract::ORIENTATION_LANDSCAPE :
							$shortSide = $image->getHeight();
							$centerX = round($image->getWidth() / 2);
							$cropX = $centerX - round($shortSide / 2);
							$cropPoint['x'] = ($cropX > 0) ? $cropX : 0;
							$cropPoint['y'] = 0;
							unset($centerX, $cropX);
							break;

						case XenForo_Image_Abstract::ORIENTATION_PORTRAIT :
							$shortSide = $image->getWidth();
							$centerY = round($image->getHeight() / 2);
							$cropY = $centerY - round($shortSide / 2);
							$cropPoint['x'] = 0;
							$cropPoint['y'] = ($cropY > 0) ? $cropY : 0;
							unset($centerY, $cropY);
							break;

						default :
							$shortSide = $image->getWidth();
							$cropPoint['x'] = $cropPoint['y'] = 0;
							break;
					}

					$image->crop($cropPoint['x'], $cropPoint['y'], $shortSide, $shortSide);

//					if ($image->thumbnail(XenForo_Application::get('options')->XfRu_UA_thumbDimensions))
//					{
//						$image->output(IMAGETYPE_JPEG, $tempThumbFile);
//					} else {
//						copy($uploadedImage->getTempFile(), $tempThumbFile); // no resize necessary, use the original
//					}

					// Always save thumbnail
					$image->thumbnail(XenForo_Application::get('options')->XfRu_UA_thumbDimensions);
					$image->output(IMAGETYPE_JPEG, $tempThumbFile);

					$dimensions['thumbnail_width'] = $image->getWidth();
					$dimensions['thumbnail_height'] = $image->getHeight();

					unset($image);
				}
			}
		}

		try
		{
			$dataDw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_ImageData');
			$dataDw->bulkSet($extra);
			$dataDw->set('user_id', $userId);
			$dataDw->set('filename', $uploadedImage->getFileName());
			$dataDw->bulkSet($dimensions);
			$dataDw->setExtraData(XfRu_UserAlbums_DataWriter_ImageData::DATA_TEMP_FILE, $uploadedImage->getTempFile());
			if ($tempThumbFile)
			{
				$dataDw->setExtraData(XfRu_UserAlbums_DataWriter_ImageData::DATA_TEMP_THUMB_FILE, $tempThumbFile);
			}
			$dataDw->save();
		}  catch (Exception $e) {
			throw $e;
		}
		return $dataDw->get('data_id');
	}

	public function insertTemporaryImage($dataId, $tempHash)
	{
		$fileDw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Image');
		$fileDw->set('data_id', $dataId);
		$fileDw->set('temp_hash', $tempHash);
		$fileDw->save();

		return $fileDw->get('image_id');
	}

	public function imagePostDelete(array $image)
	{
		$data = array(
			'last_image_id' => 0,
			'last_image_date' => 0,
			'last_image_filename' => '',
		);

		$latestImage = $this->getLatestImageInAlbum($image['album_id']);

		if ($latestImage)
		{
			$data = array(
				'last_image_id' => $latestImage['image_id'],
				'last_image_date' => $latestImage['image_date'],
				'last_image_filename' => $latestImage['filename'],
			);
		}

		$this->_getDb()->query('
			UPDATE xfr_useralbum
			SET
				image_count = IF(image_count > 0, image_count - 1, 0),
				last_image_id = '.$this->_db->quote($data['last_image_id']).',
				last_image_date = '.$this->_db->quote($data['last_image_date']).',
				last_image_filename = '.$this->_db->quote($data['last_image_filename']).'
			WHERE album_id = ?
		', $image['album_id']);

		$this->_getDb()->query('
			UPDATE xfr_useralbum_image_comment
			SET image_id = 0
			WHERE image_id = ?
		', $image['image_id']);
	}

	public function prepareImage($image)
	{
		if ($image['thumbnail_width'])
		{
			$image['thumbnailUrl'] = $this->getImageThumbnailUrl($image);
		} else {
			$image['thumbnailUrl'] = '';
		}

		$image['title'] = $image['filename'];

	    return $image;
	}

	public function prepareImages($images, $album = array())
	{
		$i = 0;
		foreach ($images AS &$image)
		{
			$firstImage = ($i % 3 == 0);
			$image = $this->prepareImage($image);
			if (!empty($album) && $album['album_type'] == 'private')
			{
				$image['access_hash'] = $album['access_hash'];
				$image['album_type'] = 'private';
			}

			$image['firstImage'] = $firstImage;
			$i++;
		}

		return $images;
	}

	public function associateImages($albumId, $imagesHash)
	{
		$rows = $this->_db->update('xfr_useralbum_image', array(
			'album_id' => $albumId,
			'temp_hash' => '',
			'unassociated' => 0
		), 'temp_hash = ' . $this->_db->quote($imagesHash));
		return $rows;
	}

	public function logImageView($albumId)
	{
		$this->_getDb()->query('
			INSERT DELAYED INTO xfr_useralbum_image_view
				(image_id)
			VALUES
				(?)
		', $albumId);
	}

	public function updateImageViews()
	{
		$db = $this->_getDb();

		$updates = $db->fetchPairs('
			SELECT image_id, COUNT(*)
			FROM xfr_useralbum_image_view
			GROUP BY image_id
		');

		XenForo_Db::beginTransaction($db);

		$db->query('TRUNCATE TABLE xfr_useralbum_image_view');

		foreach ($updates AS $imageId => $views)
		{
			$db->query('
				UPDATE xfr_useralbum_image SET
					view_count = view_count + ?
				WHERE image_id = ?
			', array($views, $imageId));
		}

		XenForo_Db::commit($db);
	}

	public function getImageNeighbours($album, $imageId)
	{
		$images = $this->prepareImages($this->getImagesByAlbumId($album['album_id']), $album);
		$ids = array_keys($images);


		$prev = $first = current($ids);
		$next = $last = end($ids);
		reset($ids);

		$i = 1;
		$currentPos = null;

		foreach ($ids as $key)
		{
			if ($prev < $key && $key < $imageId)
			{
				$prev = $key;
			}
		    if ($key > $imageId && $next > $key)
		    {
			    $next = $key;
		    }
			if ($key == $imageId)
			{
				$currentPos = $i;
			}
			$i++;
		}

		return array(
			'prev' => ($prev != $imageId) ? $images[$prev] : $images[$last],
			'next' => ($next != $imageId) ? $images[$next] : $images[$first],
			'total' => count($ids),
			'current' => $currentPos,
			'imageIds' => $ids
		);
	}

	public function deleteUnassociatedImages($maxDate)
	{
		$files = $this->_getDb()->fetchPairs('
			SELECT image_id, data_id
			FROM xfr_useralbum_image
			WHERE unassociated = 1
				AND image_date <= ?
		', $maxDate);

		$this->deleteImagesFromPairs($files);
	}

	private function deleteImagesFromPairs(array $images)
	{
		if (!$images)
		{
			return;
		}

		$dataCount = array();
		foreach ($images AS $dataId)
		{
			if (isset($dataCount[$dataId]))
			{
				$dataCount[$dataId]++;
			} else {
				$dataCount[$dataId] = 1;
			}
		}

		$db = $this->_getDb();
		$db->delete('xfr_useralbum_image',
			'image_id IN (' . $db->quote(array_keys($images)) . ')'
		);
		foreach ($dataCount AS $dataId => $delta)
		{
			$db->query('
				UPDATE xfr_useralbum_image_data
				SET attach_count = IF(attach_count > ?, attach_count - ?, 0)
				WHERE data_id = ?
			', array($delta, $delta, $dataId));
		}
	}

	public function deleteUnusedImageData()
	{
		$images = $this->_getDb()->fetchAll('
			SELECT *
			FROM xfr_useralbum_image_data
			WHERE attach_count = 0
		');
		foreach ($images AS $image)
		{
			$dw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_ImageData');
			$dw->setExistingData($image, true);
			$dw->delete();
		}
	}


	public function getImageDataFilePath(array $data)
	{
		return XenForo_Helper_File::getInternalDataPath()
			. self::IMAGES_PATH . floor($data['data_id'] / 1000)
			. "/$data[data_id]-$data[file_hash].data";
	}

	public function getImageThumbnailDataFilePath(array $data)
	{
		return XenForo_Application::getInstance()->getRootDir()
			. self::THUMBS_PATH . floor($data['data_id'] / 1000)
			. "/$data[data_id]-$data[file_hash].jpg";
	}

	public function getImageThumbnailUrl(array $data)
	{
		return ltrim(self::THUMBS_PATH, '/') . floor($data['data_id'] / 1000)
			. "/$data[data_id]-$data[file_hash].jpg";
	}

	public function canViewImage(array $image, $tempHash = null)
	{
		// todo implement this
		return true;
	}

	public function canEditImage($album)
	{
		// todo: add canEditImageBy... permission
		if (XfRu_UserAlbums_Permissions::canEditAlbumsByAnyone())
		{
			return true;
		}

		return XfRu_UserAlbums_Permissions::canEditAlbumsBySelf() && $this->getAlbumsModel()->isAlbumOwner($album);
	}

	public function canDeleteImage(array $image, $tempHash = '', array $viewingUser = null)
	{
		if (!empty($image['temp_hash']) && empty($image['album_id']))
		{
			return ($tempHash === $image['temp_hash']);
		} else {
			// todo: add canDeleteImageBy... permission
			if (XfRu_UserAlbums_Permissions::canDeleteAlbumsByAnyone())
			{
				return true;
			}

			$albumsModel = $this->getAlbumsModel();

			$album = $albumsModel->getAlbumById($image['album_id']);

			if (!$albumsModel->isAlbumViewable($album))
			{
				return false;
			}

            if ($album['album_type'] == 'global')
            {
                return XfRu_UserAlbums_Permissions::canDeleteAlbumsBySelf() && $image['user_id'] == XenForo_Visitor::getUserId();
            }

			return XfRu_UserAlbums_Permissions::canDeleteAlbumsBySelf() && $albumsModel->isAlbumOwner($album);
		}
	}

    /**
     * @return XfRu_UserAlbums_Model_Albums
     */
	private function getAlbumsModel()
	{
		return $this->getModelFromCache('XfRu_UserAlbums_Model_Albums');
	}

}