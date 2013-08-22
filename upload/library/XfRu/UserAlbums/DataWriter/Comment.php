<?php

/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Comment.php 334 2011-11-17 14:40:38Z pepelac $ $Date: 2011-11-17 15:40:38 +0100 (Thu, 17 Nov 2011) $ $Revision: 334 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_DataWriter_Comment extends XenForo_DataWriter
{
	protected function _getFields()
	{
		return array(
			'xfr_useralbum_image_comment' => array(
				'comment_id'    => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'image_id'		=> array('type' => self::TYPE_UINT, 'required' => true),
				'user_id'		=> array('type' => self::TYPE_UINT, 'required' => true),
				'username'		=> array('type' => self::TYPE_STRING, 'required' => true),
				'comment_date'	=> array('type' => self::TYPE_UINT, 'required' => true, 'default' => XenForo_Application::$time),
				'message'		=> array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 10000, 'requiredError' => 'please_enter_valid_message'),
			)
		);
	}
	
	protected function _getExistingData($data)
	{
		if (!$id = $this->_getExistingPrimaryKey($data, 'comment_id'))
		{
			return false;
		}
		
		return array('xfr_useralbum_image_comment' => $this->getModelFromCache('XfRu_UserAlbums_Model_Comments')->getCommentById($id));
	}
	
	protected function _getUpdateCondition($tableName)
	{
		return 'comment_id = ' . $this->_db->quote($this->getExisting('comment_id'));
	}

	protected function _postSave()
	{
		if ($this->isInsert())
		{
			$this->_db->query('
				UPDATE xfr_useralbum_image
				SET comment_count = comment_count + 1
				WHERE image_id = ?
			', $this->get('image_id'));
            $this->getModelFromCache('XfRu_UserAlbums_Model_Counters')->rebuildAlbumsCache();
		}
	}

	protected function _postDelete()
	{
		$commentId = $this->_db->quote($this->get('comment_id'));

		$data = $this->getMergedData();

		$this->_db->query('
			UPDATE xfr_useralbum_image
			SET comment_count = IF(comment_count > 0, comment_count - 1, 0)
			WHERE image_id = ?
		', $data['image_id']);

		$this->getModelFromCache('XenForo_Model_Alert')->deleteAlerts('xfr_useralbum_image_cmnt', $commentId);
		$this->getModelFromCache('XenForo_Model_Like')->deleteContentLikes('xfr_useralbum_image_cmnt', $commentId);
        $this->getModelFromCache('XfRu_UserAlbums_Model_Counters')->rebuildAlbumsCache();
	}
}