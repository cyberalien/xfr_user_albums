<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Album.php 334 2011-11-17 14:40:38Z pepelac $ $Date: 2011-11-17 15:40:38 +0100 (Thu, 17 Nov 2011) $ $Revision: 334 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_DataWriter_Album extends XenForo_DataWriter
{
	const DATA_TABLE = 'xfr_useralbum';

	/**
	 * @return XfRu_UserAlbums_Model_Albums
	 */
	private function getAlbumsModel()
	{
		return $this->getModelFromCache('XfRu_UserAlbums_Model_Albums');
	}

	/**
	 * @return XfRu_UserAlbums_Model_Images
	 */
	private function getImagesModel()
	{
		return $this->getModelFromCache('XfRu_UserAlbums_Model_Images');
	}

	/**
	 * @return XenForo_Model_ModerationQueue
	 */
	private function getModerationQueueModel()
	{
		return $this->getModelFromCache('XenForo_Model_ModerationQueue');
	}

	protected function _getExistingData($data)
	{
		if (!$albumId = $this->_getExistingPrimaryKey($data))
		{
			return false;
		}

		return array(self::DATA_TABLE => $this->getAlbumsModel()->getAlbumById($albumId));
	}

	protected function _getFields()
	{
		return array(
			self::DATA_TABLE => array(
				'album_id'        => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'user_id'         => array('type' => self::TYPE_UINT, 'default' => 0),
				'createdate'      => array('type' => self::TYPE_UINT, 'default' => XenForo_Application::$time),
				'title'           => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 100, 'requiredError' => 'please_enter_valid_title'),
				'description'     => array('type' => self::TYPE_STRING, 'default' => "", 'maxLength' => '10000'),
				'cover_image_id'  => array('type' => self::TYPE_UINT, 'default' => 0),
				'album_type'      => array('type' => self::TYPE_STRING, 'default' => "public"),
				'access_hash'     => array('type' => self::TYPE_STRING, 'default' => NULL),
				'sprite_hash'     => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 32),
				'moderation'      => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
				'image_count'     => array('type' => self::TYPE_UINT, 'default' => 0),
				'last_image_id'   => array('type' => self::TYPE_UINT, 'default' => 0),
				'last_image_date' => array('type' => self::TYPE_UINT, 'default' => 0),
				'last_image_filename' => array('type' => self::TYPE_STRING, 'default' => ""),
				'likes'			  => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
				'like_users'	  => array('type' => self::TYPE_SERIALIZED, 'default' => 'a:0:{}')
			)
		);
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'album_id = ' . $this->_db->quote($this->getExisting('album_id'));
	}

	protected function _preDelete()
	{
		$data = $this->getMergedData();
		$images = $this->getImagesModel()->getImagesByAlbumId($data['album_id']);
		foreach ($images as $image)
		{
			$dw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Image');
			$dw->setExistingData($image, true);
			$dw->delete(); 
		}

		unset($images);
	}

	protected final function _postSave()
	{
		$this->_updateModerationQueue();
        $this->getModelFromCache('XfRu_UserAlbums_Model_Counters')->rebuildAlbumsCache();
	}

    protected function _postDelete()
    {
        $this->getModelFromCache('XfRu_UserAlbums_Model_Counters')->rebuildAlbumsCache();
    }

	protected function _updateModerationQueue()
	{
		// todo: new field album_state instead of field "moderation"

		if (!$this->isChanged('moderation'))
		{
			return;
		}

		if ($this->get('moderation'))
		{
			$this->getModerationQueueModel()->insertIntoModerationQueue(
				XfRu_UserAlbums_Helper::CT_ALBUM, $this->get('album_id'), $this->get('createdate')
			);
		} else if ($this->getExisting('moderation')) {
			$this->getModerationQueueModel()->deleteFromModerationQueue(
				XfRu_UserAlbums_Helper::CT_ALBUM, $this->get('album_id')
			);
		}
	}
}