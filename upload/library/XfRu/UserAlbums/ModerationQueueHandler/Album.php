<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Album.php 288 2011-08-18 07:01:55Z pepelac $ $Date: 2011-08-18 09:01:55 +0200 (Thu, 18 Aug 2011) $ $Revision: 288 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ModerationQueueHandler_Album extends XenForo_ModerationQueueHandler_Abstract
{
	public function getVisibleModerationQueueEntriesForUser(array $contentIds, array $viewingUser)
	{
		/** @var XfRu_UserAlbums_Model_Albums $albumsModel  */
		$albumsModel = XenForo_Model::create('XfRu_UserAlbums_Model_Albums');

		$albums = $albumsModel->getAlbumsByIds($contentIds);

		$output = array();

		foreach ($albums AS $album)
		{
			$canManage = true;

			if (!$albumsModel->isAlbumViewable($album))
			{
				$canManage = false;
			} else if (!XenForo_Permission::hasPermission($viewingUser['permissions'], 'xfrUserAlbumsPermissions', 'xfr_UA_EditAlbumsByAnyone')
					|| !XenForo_Permission::hasPermission($viewingUser['permissions'], 'xfrUserAlbumsPermissions', 'xfr_UA_DeleteAlbumsByAny'))
			{
				$canManage = false;
			}

			if ($canManage)
			{
				$output[$album['album_id']] = array(
					'message' => $album['description'],
					'user' => array(
						'user_id' => $album['user_id'],
						'username' => $album['username']
					),
					'title' => $album['title'],
					'contentTypeTitle' => new XenForo_Phrase('xfr_useralbums_album'),
					'titleEdit' => false,
					'link' => XenForo_Link::buildPublicLink('useralbums/view', $album)
				);
			}
		}

		return $output;
	}

	public function approveModerationQueueEntry($contentId, $message, $title)
	{
		$writer = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Album', XenForo_DataWriter::ERROR_SILENT);
		$writer->setExistingData($contentId);
		$writer->set('description', $message);
		// todo: use album_state instead of moderation
		$writer->set('moderation', 0);

		return $writer->save();
	}

	public function deleteModerationQueueEntry($contentId)
	{
		$writer = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Album', XenForo_DataWriter::ERROR_SILENT);
		$writer->setExistingData($contentId);
		return $writer->delete();
//		$writer->set('album_state', 'deleted');
//		return $writer->save();
	}
}