<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: ImageData.php 183 2011-03-27 20:59:22Z pepelac $ $Date: 2011-03-27 22:59:22 +0200 (Sun, 27 Mar 2011) $ $Revision: 183 $
 * @author Pepelac
 *
 */


class XfRu_UserAlbums_DataWriter_ImageData extends XenForo_DataWriter
{
	const DATA_TEMP_FILE = 'tempFile';
	const DATA_TEMP_THUMB_FILE = 'tempThumbFile';
	const DATA_TABLE = 'xfr_useralbum_image_data';

	protected function _getExistingData($data)
	{
		if (!$id = $this->_getExistingPrimaryKey($data, 'data_id', self::DATA_TABLE))
		{
			return false;
		}

		return array(self::DATA_TABLE => $this->getImagesModel()->getImageDataById($id));
	}

	protected function _getFields()
	{
		return array(
			self::DATA_TABLE => array(
				'data_id'          => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'user_id'          => array('type' => self::TYPE_UINT, 'required' => true),
				'upload_date'      => array('type' => self::TYPE_UINT, 'default' => XenForo_Application::$time),
				'filename'         => array('type' => self::TYPE_STRING, 'maxLength' => 100, 'required' => true),
				'description'      => array('type' => self::TYPE_STRING, 'maxLength' => 10000),
				'file_size'        => array('type' => self::TYPE_UINT, 'required' => true),
				'file_hash'        => array('type' => self::TYPE_STRING, 'maxLength' => 32, 'required' => true),
				'width'            => array('type' => self::TYPE_UINT, 'default' => 0),
				'height'           => array('type' => self::TYPE_UINT, 'default' => 0),
				'thumbnail_width'  => array('type' => self::TYPE_UINT, 'default' => 0),
				'thumbnail_height' => array('type' => self::TYPE_UINT, 'default' => 0),
				'attach_count'     => array('type' => self::TYPE_UINT, 'default' => 0),
			)
		);
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'data_id = ' . $this->_db->quote($this->getExisting('data_id', self::DATA_TABLE));
	}

	protected function _preSave()
	{
		$tempFile = $this->getExtraData(self::DATA_TEMP_FILE);
		if ($this->isInsert() && !$tempFile)
		{
			throw new XenForo_Exception('Tried to insert file data without the data.');
		}
		if ($tempFile)
		{
			if (!is_readable($tempFile))
			{
				$this->error(new XenForo_Phrase('xfr_useralbums_image_could_not_be_read_by_server'));
				return;
			}

			clearstatcache();
			$this->set('file_size', filesize($tempFile));
			$this->set('file_hash', md5_file($tempFile));
		}

		// todo: check this if it works with thumbnails regeneration process (will use datawriter update)
		$tempThumbFile = $this->getExtraData(self::DATA_TEMP_THUMB_FILE);
	    if ($this->isInsert() || ($this->isUpdate() && $tempThumbFile))
	    {
			if (!$tempThumbFile || !file_exists($tempThumbFile) || !is_readable($tempThumbFile))
			{
				$this->set('thumbnail_width', 0);
				$this->set('thumbnail_height', 0);

				$this->setExtraData(self::DATA_TEMP_THUMB_FILE, '');
			}
	    }
	}

	protected function _postSave()
	{
		$data = $this->getMergedData();

		$tempFile = $this->getExtraData(self::DATA_TEMP_FILE);
		if ($tempFile)
		{
			if (!$this->writeImage($tempFile, $data))
			{
				throw new XenForo_Exception('Failed to write the image.');
			}
		}
	    
		$tempThumbFile = $this->getExtraData(self::DATA_TEMP_THUMB_FILE);
		if ($tempThumbFile)
		{
			if (!$this->writeImage($tempThumbFile, $data, true))
			{
				throw new XenForo_Exception('Failed to write the image thumbnail file.');
			}
		}
	}

	protected function _postDelete()
	{
		$data = $this->getMergedData();
		$imagesModel = $this->getImagesModel();

		$image = $imagesModel->getImageDataFilePath($data);
		if (file_exists($image) && is_writable($image))
		{
			unlink($image);
		}

	    $thumbnail = $imagesModel->getImageThumbnailDataFilePath($data);
		if (file_exists($thumbnail) && is_writable($thumbnail))
		{
			unlink($thumbnail);
		}
	}

	protected function writeImage($tempFile, array $data, $thumbnail = false)
	{
		if ($tempFile && is_readable($tempFile))
		{
			if ($thumbnail)
			{
				$filePath = $this->getImagesModel()->getImageThumbnailDataFilePath($data);
			} else {
				$filePath = $this->getImagesModel()->getImageDataFilePath($data);
			}


			$directory = dirname($filePath);

			if (XenForo_Helper_File::createDirectory($directory, true))
			{
				return $this->moveFile($tempFile, $filePath);
			}
		}

		return false;
	}

	protected function moveFile($source, $destination)
	{
		if (is_uploaded_file($source))
		{
			$success = move_uploaded_file($source, $destination);
		} else {
			$success = rename($source, $destination);
		}

		if ($success)
		{
			XenForo_Helper_File::makeWritableByFtpUser($destination);
		}

		return $success;
	}

	private function getImagesModel()
	{
		return $this->getModelFromCache('XfRu_UserAlbums_Model_Images');
	}
}