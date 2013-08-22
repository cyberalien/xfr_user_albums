<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Image.php 334 2011-11-17 14:40:38Z pepelac $ $Date: 2011-11-17 15:40:38 +0100 (Thu, 17 Nov 2011) $ $Revision: 334 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_DataWriter_Image extends XenForo_DataWriter
{
	const DATA_TABLE = 'xfr_useralbum_image';

	protected function _getExistingData($data)
	{
		if (!$id = $this->_getExistingPrimaryKey($data, 'image_id', self::DATA_TABLE))
		{
			return false;
		}

		return array(self::DATA_TABLE => $this->getImagesModel()->getImageById($id));
	}

	protected function _getFields()
	{
		return array(
			self::DATA_TABLE => array(
				'image_id' => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'data_id'       => array('type' => self::TYPE_UINT, 'required' => true),
				'album_id'      => array('type' => self::TYPE_UINT, 'default' => 0),
				'image_date'    => array('type' => self::TYPE_UINT, 'default' => XenForo_Application::$time),
				'temp_hash'     => array('type' => self::TYPE_STRING, 'maxLength' => 32, 'default' => ''),
				'unassociated'  => array('type' => self::TYPE_BOOLEAN, 'default' => 1),
				'view_count'    => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
				'comment_count' => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
				'likes'			=> array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
				'like_users'	=> array('type' => self::TYPE_SERIALIZED, 'default' => 'a:0:{}')
			)
		);
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'image_id = ' . $this->_db->quote($this->getExisting('image_id', self::DATA_TABLE));
	}


	protected function _preSave()
	{
		if (!$this->get('album_id'))
		{
			if (!$this->get('temp_hash'))
			{
				throw new XenForo_Exception('Temp hash must be specified if no content is specified.');
			}

			$this->set('unassociated', 1);
		} else {
			$this->set('temp_hash', '');
			$this->set('unassociated', 0);
		}
	}

	protected function _postSave()
	{
		$this->_db->query('
			UPDATE xfr_useralbum_image_data
			SET attach_count = attach_count + 1
			WHERE data_id = ?
		', $this->get('data_id'));
        $this->getModelFromCache('XfRu_UserAlbums_Model_Counters')->rebuildAlbumsCache();
	}

	protected function _postDelete()
	{
		$data = $this->getMergedData();

		$this->getModelFromCache('XenForo_Model_Alert')->deleteAlerts('xfr_useralbum_image', $data['image_id']);
		$this->getModelFromCache('XenForo_Model_Like')->deleteContentLikes('xfr_useralbum_image', $data['image_id']);

		$this->_db->query('
			UPDATE xfr_useralbum_image_data
			SET attach_count = IF(attach_count > 0, attach_count - 1, 0)
			WHERE data_id = ?
		', $data['data_id']);

		if ($data['album_id'])
		{
			$this->getImagesModel()->imagePostDelete($data);
		}
        $this->getModelFromCache('XfRu_UserAlbums_Model_Counters')->rebuildAlbumsCache();
	}

	/**
	 * @return XfRu_UserAlbums_Model_Images
	 */
	private function getImagesModel()
	{
		return $this->getModelFromCache('XfRu_UserAlbums_Model_Images');
	}
}



