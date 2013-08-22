<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Comments.php 294 2011-08-22 07:04:59Z pepelac $ $Date: 2011-08-22 09:04:59 +0200 (Mon, 22 Aug 2011) $ $Revision: 294 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_Model_Comments extends XenForo_Model
{
	public function getCommentById($commentId)
	{
		return $this->_getDb()->fetchRow('
			SELECT
				comment.*, user.avatar_date, user.avatar_width, user.avatar_height, user.gravatar
			FROM xfr_useralbum_image_comment AS comment
			LEFT JOIN xf_user AS user
				ON (user.user_id = comment.user_id)
			WHERE comment_id = ?
		', $commentId);
	}

	public function getCommentsByIds($commentIds)
	{
		return $this->fetchAllKeyed('
			SELECT
				comment.*, user.avatar_date, user.avatar_width, user.avatar_height, user.gravatar,
				album.title, album.album_id, album.moderation, album.image_count, album.album_type, album.access_hash
			FROM xfr_useralbum_image_comment AS comment
			LEFT JOIN xf_user AS user
				ON (user.user_id = comment.user_id)
			LEFT JOIN xfr_useralbum_image AS image
				ON (image.image_id = comment.image_id)
			LEFT JOIN xfr_useralbum AS album
				ON (album.album_id = image.album_id)
			WHERE comment.comment_id IN (' . $this->_getDb()->quote($commentIds) . ')
		', 'comment_id');
	}

	public function getCommentsByImageId($imageId)
	{
		return $this->_getDb()->fetchAll('
			SELECT
				comment.*, user.avatar_date, user.avatar_width, user.avatar_height, user.gravatar
			FROM xfr_useralbum_image_comment AS comment
			LEFT JOIN xf_user AS user
				ON (user.user_id = comment.user_id)
			WHERE comment.image_id = '. $this->_getDb()->quote($imageId) . '
			ORDER BY comment_date DESC
		');
	}

	public function getCommentCountByImageId($imageId)
	{
		return $this->_getDb()->fetchOne("
			SELECT COUNT(*) AS comment_count
			FROM xfr_useralbum_image_comment
			WHERE image_id = ?
		", array('image_id' => $imageId));
	}

	public function getCommentAuthorsByImageId($imageId)
	{
		return $this->_getDb()->fetchAll('
			SELECT DISTINCT
				comment.user_id, user.username
			FROM xfr_useralbum_image_comment AS comment
			LEFT JOIN xf_user AS user
				ON (user.user_id = comment.user_id)
			WHERE comment.image_id = '. $this->_getDb()->quote($imageId) . '
		');
	}

	public function getNewestCommentsByDate($imageId, $date)
	{
		return $this->_getDb()->fetchAll('
			SELECT
				comment.*, user.avatar_date, user.avatar_width, user.avatar_height, user.gravatar
			FROM xfr_useralbum_image_comment AS comment
			LEFT JOIN xf_user AS user
				ON (user.user_id = comment.user_id)
			WHERE comment.comment_date > ?
				AND comment.image_id = ' . $this->_getDb()->quote($imageId) . '
			ORDER BY comment_date DESC
		', $date);
	}

	public function getRecentComments()
	{
		$recentCommentLimit = XenForo_Application::get('options')->XfRu_UA_recentCmntsLimit;

		return $this->fetchAllKeyed($this->limitQueryResults('
			SELECT
				comment.*, user.username, user.avatar_date, user.avatar_width, user.avatar_height, user.gravatar,
				album.title, album.album_id
			FROM xfr_useralbum_image_comment AS comment
			LEFT JOIN xf_user AS user
				ON (user.user_id = comment.user_id)
			LEFT JOIN xfr_useralbum_image AS image
				ON (image.image_id = comment.image_id)
			LEFT JOIN xfr_useralbum AS album
				ON (album.album_id = image.album_id)
			WHERE album.album_type = \'public\' AND moderation = 0
			ORDER BY comment.comment_date DESC
			', $recentCommentLimit
		), 'comment_id');
	}

	public function sendAlerts($imageId, $mergedData)
	{
		$image = $this->getImagesModel()->getImageInfoById($imageId);
		$user = $this->getModelFromCache('XenForo_Model_User')->getUserById($mergedData['user_id']);

		// alert image owner
		if ($image['user_id'] != $user['user_id'] && XenForo_Model_Alert::userReceivesAlert($image, 'xfr_useralbum_image_cmnt', 'insert'))
		{
			XenForo_Model_Alert::alert(
				$image['user_id'],
				$user['user_id'],
				$user['username'],
				'xfr_useralbum_image_cmnt',
				$mergedData['comment_id'],
				'insert'
			);
		}

		// alert other users, who have commented on the same image
		$visitorId = XenForo_Visitor::getUserId();
		$users = $this->getCommentAuthorsByImageId($imageId);
		foreach ($users as $u)
		{
			if ($u['user_id'] == $image['user_id'] || $u['user_id'] == $visitorId)
			{
				continue;
			}

			if (XenForo_Model_Alert::userReceivesAlert($u, 'xfr_useralbum_image_cmnt', 'reply'))
			{
				XenForo_Model_Alert::alert(
					$u['user_id'],
					$user['user_id'],
					$user['username'],
					'xfr_useralbum_image_cmnt',
					$mergedData['comment_id'],
					'reply'
				);
			}
		}
	}

	public function deleteUnassociatedComments($maxDate)
	{
		$this->_getDb()->query('
			DELETE FROM xfr_useralbum_image_comment
			WHERE image_id = 0
				AND comment_date <= ?
		', $maxDate);
	}

	public function isCommentOwner($comment)
	{
		return $comment['user_id'] == XenForo_Visitor::getUserId();
	}

	public function canEditComment($comment)
	{
		if (XfRu_UserAlbums_Permissions::canEditCommentsByAnyone())
		{
			return true;
		}

		return XfRu_UserAlbums_Permissions::canEditCommentsBySelf() && $this->isCommentOwner($comment);
	}

	public function canDeleteComment($comment)
	{
		if (XfRu_UserAlbums_Permissions::canDeleteCommentsByAnyone())
		{
			return true;
		}

		return XfRu_UserAlbums_Permissions::canDeleteCommentsBySelf() && $this->isCommentOwner($comment);
	}

	public function importImageComments($imageId, $userId, $date, $message)
	{
		$user = $this->getModelFromCache('XenForo_Model_User')->getUserById($userId);

		$dw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Comment');
		$dw->bulkSet(array(
			'image_id' => $imageId,
			'user_id' => $userId,
			'username' => ($user) ? $user['username'] : 'Unknown',
			'comment_date' => $date,
			'message' => $message
		));
		$dw->save();

		return $dw->get('comment_id');
	}

	/**
	 * Used to find new user id... There can be better way to do this...
	 * @param $oldUserId
	 * @return int
	 */
	public function lookUpNewUserId($oldUserId)
	{
		$lookUpTable = (defined('IMPORT_LOG_TABLE') ? IMPORT_LOG_TABLE : 'xf_import_log');
		return $this->_getDb()->fetchOne('
			SELECT new_id
			FROM ' . $lookUpTable . '
			WHERE content_type = '.$this->_getDb()->quote('user').' AND old_id = '.$this->_getDb()->quote($oldUserId)
		);

	}

	public function rebuildCommentsCount()
	{
		$sql = "
			SELECT image_id, COUNT(*) AS comment_count
			FROM xfr_useralbum_image_comment
			GROUP BY image_id
		";

		$comments = $this->_getDb()->fetchAll($sql);

		foreach ($comments as $comment)
		{
			$bind = array(
				'comment_count' => $comment['comment_count']
			);

			$where = array(
				'image_id = '.(int)$comment['image_id']
			);
			$this->_getDb()->update('xfr_useralbum_image', $bind, $where);
		}

	}

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

}