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

class XfRu_UserAlbums_ControllerPublic_Albums extends XfRu_UserAlbums_ControllerPublic_Abstract
{	
	public function actionIndex()
	{
		$this->assertViewAlbums();

		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$albumsPerPage = XenForo_Application::get('options')->XfRu_UA_albumsPerPage;

		$this->canonicalizeRequestUrl(
			XenForo_Link::buildPublicLink('useralbums', null, array('page' => $page))
		);

		/* @var $albumsModel XfRu_UserAlbums_Model_Albums */
		$albumsModel = $this->getAlbumsModel();
		$imagesModel = $this->getImagesModel();
		$commentsModel = $this->getCommentsModel();

		$albumsModel->doSmth();

		$visitor = XenForo_Visitor::getInstance();

		$canEditAlbums = XfRu_UserAlbums_Permissions::canEditAlbumsByAnyone();

		$fetchOptions = array(
//			'conditions' => array(
//			),
			'join' => XfRu_UserAlbums_Model_Albums::FETCH_USER,
			'order' => array('album.last_image_date' => 'DESC', 'album.createdate' => 'DESC'),
			'perPage' => $albumsPerPage,
			'page' => $page,
			'userId' => $visitor->getUserId(),
			'showEmptyAlbums' => XenForo_Application::get('options')->XfRu_UA_emptyAlbumsAtMainList
		);

        /** @var XenForo_Model_DataRegistry $cacheModel  */
        $cacheModel = XenForo_Model::create('XenForo_Model_DataRegistry');
        $userAlbumsCache = $cacheModel->get('userAlbums');

        if (!$userAlbumsCache)
        {
            $userAlbumsCache = $this->getCountersModel()->rebuildAlbumsCache($fetchOptions);
        }

//		$totalAlbums = $albumsModel->countAlbums(null, $fetchOptions);
		$totalAlbums = $userAlbumsCache['totalAlbums'];

		$this->canonicalizePageNumber($page, $albumsPerPage, $totalAlbums, 'useralbums');

		$albums = $albumsModel->getAlbums($fetchOptions);

		foreach ($albums as &$a)
		{
			$a['canEditAlbum'] = $canEditAlbums;
		    $a = $albumsModel->prepareAlbum($a);
			if ($a['last_image_id'])
			{
				$a['image'] = array(
					'thumbnailUrl' => $imagesModel->getImageThumbnailUrl(array('data_id' => $a['image_data_id'], 'file_hash' => $a['image_file_hash'])),
					'image_id' => $a['last_image_id'],
					'title' => $a['last_image_filename']
				);
			}
		}

//		$counters = $this->getCountersModel()->getStatistics();

		$stats = array(
			'albumsCount' => $totalAlbums,
			'imagesCount' => $userAlbumsCache['images'],
			'commentsCount' => $userAlbumsCache['comments'],
			'albumOwners' => $userAlbumsCache['members'],
            'activeMembers' => $userAlbumsCache['activeMembers']
		);

		$viewParams = array(
			'albums' => $albums,
			'canCreateAlbum' => XfRu_UserAlbums_Permissions::canCreateAlbum(),
			'page' => $page,
			'albumStartOffset' => ($page - 1) * $albumsPerPage + 1,
			'albumEndOffset' => ($page - 1) * $albumsPerPage + count($albums) ,
			'albumsPerPage' => $albumsPerPage,
			'totalAlbums' => $totalAlbums,
			'recentComments' => $commentsModel->getRecentComments(),
			'stats' => $stats,
            'popularAlbums' => $albumsModel->getPopularAlbums(),
		);

		if (XenForo_Application::get('options')->XfRu_UA_listType == 'grid')
		{
			$tpl = 'xfr_useralbums_albums_list_grid';

		} else {
			$tpl = 'xfr_useralbums_albums_list';
		}

		return $this->responseView(
			'XfRu_UserAlbums_ViewPublic_ListUserAlbums',
			$tpl,
			$viewParams
		);
	}

	public function actionOwn()
	{
		$visitor = XenForo_Visitor::getInstance();
	    if ($visitor->getUserId())
	    {
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
				XenForo_Link::buildPublicLink('useralbums/list', $visitor)
			);
	    } else {
			throw $this->getErrorOrNoPermissionResponseException('');
	    }
	}

	public function actionPreview()
	{
		$fetchOptions = array(
			'join' => XfRu_UserAlbums_Model_Albums::FETCH_USER,
		);

		$albumsModel = $this->getAlbumsModel();

		$albumId = $this->_input->filterSingle('album_id', XenForo_Input::UINT);
		$album = $albumsModel->getAlbumById($albumId, $fetchOptions);
		$accessHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);

		if (empty($album))
		{
			return $this->responseView('XfRu_UserAlbums_ViewPublic_AlbumPreview', '', array('album' => false));
		}

		if (!$albumsModel->isAlbumViewable($album, $accessHash))
		{
			return $this->responseView('XfRu_UserAlbums_ViewPublic_AlbumPreview', '', array('album' => false));
		}

		$viewParams = array(
			'album' => $album
		);

		return $this->responseView('XfRu_UserAlbums_ViewPublic_AlbumPreview', 'xfr_useralbums_album_list_item_preview', $viewParams);
	}

	public function actionList()
	{
		$this->assertViewAlbums();

		$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);

		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$albumsPerPage = XenForo_Application::get('options')->XfRu_UA_albumsPerPage;

		if (!$user = $this->getUserModel()->getUserById($userId))
		{
			return $this->responseError(new XenForo_Phrase('requested_member_not_found'), 404);
		}

		$fetchOptions = array(
			'conditions' => array(
				'canViewEmptyAlbums' => XfRu_UserAlbums_Permissions::canViewEmptyAlbums(),
				'canViewPrivateAlbums' => XfRu_UserAlbums_Permissions::canViewPrivateAlbums(),
				'canModerateAlbums' => XfRu_UserAlbums_Permissions::canEditAlbumsByAnyone(),
			),
			'join' => XfRu_UserAlbums_Model_Albums::FETCH_USER,
			'order' => array('album.last_image_date' => 'DESC', 'album.createdate' => 'DESC'),
			'userId' => ($userId == XenForo_Visitor::getUserId()) ? $userId : 0,
			'perPage' => $albumsPerPage,
			'page' => $page,
		);

		$albumsModel = $this->getAlbumsModel();
		$imagesModel = $this->getImagesModel();

		$totalAlbums = $albumsModel->countAlbums($userId, $fetchOptions);

		$this->canonicalizePageNumber($page, $albumsPerPage, $totalAlbums, 'useralbums/list', $user);

		$albums = $albumsModel->getUserAlbums($userId, $fetchOptions);

		$counters = $this->getCountersModel()->getStatistics($userId);

		foreach ($albums as &$a)
		{
			$a['canEditAlbum'] = $albumsModel->isAlbumEditable($a);
		    $a = $albumsModel->prepareAlbum($a);
			if ($a['last_image_id'])
			{
				$a['image'] = array(
					'thumbnailUrl' => $imagesModel->getImageThumbnailUrl(array('data_id' => $a['image_data_id'], 'file_hash' => $a['image_file_hash'])),
					'image_id' => $a['last_image_id'],
					'title' => $a['last_image_filename']
				);
			}
		}

		$stats = array(
			'albumsCount' => $totalAlbums,
			'imagesCount' => $counters['images'],
			'commentsCount' => $counters['comments'],
			'albumOwners' => $counters['members']
		);

		$viewParams = array(
			'albums' => $albums,
			'user' => $user,
			'canCreateAlbum' => XfRu_UserAlbums_Permissions::canCreateAlbum(),
			'stats' => $stats,
			'page' => $page,
			'albumStartOffset' => ($page - 1) * $albumsPerPage + 1,
			'albumEndOffset' => ($page - 1) * $albumsPerPage + count($albums) ,
			'albumsPerPage' => $albumsPerPage,
			'totalAlbums' => $totalAlbums,
		);

		if (XenForo_Application::get('options')->XfRu_UA_listType == 'grid')
		{
			$tpl = 'xfr_useralbums_member_albums_list_grig';

		} else {
			$tpl = 'xfr_useralbums_member_albums_list';
		}

		return $this->responseView(
			'XfRu_UserAlbums_ViewPublic_ListUserAlbums',
			$tpl,
			$viewParams
		);
	}

	public function actionCreate()
	{
		$this->assertCreateAlbum();

		$viewParams = array(
			'breadCrumbs' => $this->getAlbumsModel()->getBreadCrumbs(XenForo_Visitor::getInstance()->toArray()),
            'canManageGlobalAlbums' => XfRu_UserAlbums_Permissions::canCreateGlobalAlbum()
		);

		return $this->responseView('XfRu_UserAlbums_ViewPublic_CreateAlbum', 'xfr_useralbums_create_album', $viewParams);
	}


	public function actionInsert()
	{
		$this->_assertPostOnly();

		$this->assertCreateAlbum();
		
		$visitor = XenForo_Visitor::getInstance();
		$userId = $visitor->getUserId();

		$input = $this->_input->filter(array(
			'title' => XenForo_Input::STRING,
			'type' => XenForo_Input::STRING
		));
		$input['description'] = $this->getHelper('Editor')->getMessageText('description', $this->_input);
		$input['description'] = XenForo_Helper_String::autoLinkBbCode($input['description']);

	    $writer = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Album');
	    $writer->set('user_id', $userId);
	    $writer->set('title', $input['title']);
	    $writer->set('description', $input['description']);
	    $writer->set('album_type', $input['type']);
	    if ($input['type'] == 'private')
	    {
		    $writer->set('access_hash', $this->getAlbumsModel()->generateAccessHash());
	    }

	    $writer->save();

		$this->getModelFromCache('XfRu_UserAlbums_Model_NewsFeed')->publishFeedItem($writer->getMergedData(), 'album');

	    return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('useralbums/view', $writer->getMergedData())
		);
	}

	public function actionEdit()
	{
		$albumId = $this->_input->filterSingle('album_id', XenForo_Input::UINT);
	    $album = $this->assertEditAlbum($albumId);

	    $viewParams = array(
		    'album' => $album,
		    'canModerateAlbums' => XfRu_UserAlbums_Permissions::canApproveUnapprove(),
			'breadCrumbs' => $this->getAlbumsModel()->getBreadCrumbs(XenForo_Visitor::getInstance()->toArray()),
            'canManageGlobalAlbums' => XfRu_UserAlbums_Permissions::canCreateGlobalAlbum()
	    );

	    return $this->responseView('XfRu_UserAlbums_ViewPublic_EditAlbum', 'xfr_useralbums_edit_album', $viewParams);

	}

	public function actionSave()
	{
		$this->_assertPostOnly();

	    $albumId = $this->_input->filterSingle('album_id', XenForo_Input::UINT);
	    $album = $this->assertEditAlbum($albumId);

		$input = $this->_input->filter(array(
			'title' => XenForo_Input::STRING,
			'type' => XenForo_Input::STRING,
			'moderated'  => XenForo_Input::UINT,
		));
		$input['description'] = $this->getHelper('Editor')->getMessageText('description', $this->_input);
		$input['description'] = XenForo_Helper_String::autoLinkBbCode($input['description']);

	    $writer = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Album');
	    $writer->setExistingData(array('album_id' => $album['album_id']));
	    $writer->set('title', $input['title']);
	    $writer->set('description', $input['description']);
	    $writer->set('album_type', $input['type']);
	    $writer->set('moderation', $input['moderated']);
	    if ($input['type'] == 'private' && empty($album['access_hash']))
	    {
		    // need to generate access hash to this album
		    $writer->set('access_hash', $this->getAlbumsModel()->generateAccessHash());
	    }

	    $writer->save();

	    return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('useralbums/view', $writer->getMergedData())
		);
	}

	public function actionView()
	{
		$albumsModel = $this->getAlbumsModel();
		
		$albumId = $this->_input->filterSingle('album_id', XenForo_Input::UINT);
		$album = $this->assertAlbumValidAndViewable($albumId, XenForo_Visitor::getInstance()->getUserId());

		$imagesModel = $this->getImagesModel();

		$images = $imagesModel->prepareImages($imagesModel->getImagesByAlbumId($albumId), $album);

		$albumsModel->logAlbumView($album['album_id']);

		$album['title'] = XenForo_Helper_String::censorString($album['title']);

		$album['like_users'] = unserialize($album['like_users']);
		$album['like_date'] = $this->getLikeModel()->getContentLikeByLikeUser('xfr_useralbum', $album['album_id'], XenForo_Visitor::getUserId());

		$viewParams = array(
			'album' => $album,
			'images' => $images,
			'breadCrumbs' => $albumsModel->getBreadCrumbs($album),
			'canAddImages' => $albumsModel->canAddImages($album),
			'canEditAlbum' => $albumsModel->isAlbumEditable($album),
			'canDeleteAlbum' => $albumsModel->isAlbumDeletable($album),
			'canLikeAlbum' => XfRu_UserAlbums_Permissions::canLikeImages() && $album['user_id'] != XenForo_Visitor::getUserId(),
		);

		return $this->responseView(
			'XfRu_UserAlbums_ViewPublic_ViewUserAlbum',
			'xfr_useralbums_album_view',
			$viewParams
		);
	}

	public function actionSaveImages()
	{
		$this->_assertPostOnly();
		$visitor = XenForo_Visitor::getInstance();

	    $albumId = $this->_input->filterSingle('album_id', XenForo_Input::UINT);
	    $album = $this->assertAlbumValidAndViewable($albumId, $visitor->getUserId());

		$this->assertCanUploadToAlbum($album, $visitor->getUserId());

		$imagesHash = $this->_input->filterSingle('images_hash', XenForo_Input::STRING);
		$imagesDescription = $this->_input->filterSingle('imageDescr', XenForo_Input::ARRAY_SIMPLE);

		$tempImages = $this->getImagesModel()->getImagesByTempHash($imagesHash);
		$existingImages = $this->getImagesModel()->getImagesByAlbumId($albumId);

		if (empty($tempImages) && !$album['image_count'])
		{
			return $this->responseError(new XenForo_Phrase('xfr_useralbums_you_must_upload_at_least_one_image'));
		}

		$associatedImages = $this->getImagesModel()->associateImages($album['album_id'], $imagesHash);

		if (!$associatedImages && !empty($tempImages))
		{
			return $this->responseError(new XenForo_Phrase('xfr_useralbums_error_while_saving_images'));
		}

		// adding or updating images description
		foreach ($imagesDescription as $imageId => $description)
		{
			$dataDw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_ImageData');
			if (array_key_exists($imageId, $tempImages))
			{
				$dataDw->setExistingData($tempImages[$imageId]['data_id']);
			} else {
				$dataDw->setExistingData($existingImages[$imageId]['data_id']);
			}
			$dataDw->set('description', $description);
			$dataDw->save();
			unset($dataDw);
		}

		$albumDw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Album');
		$albumDw->setExistingData($album['album_id']);

		$imagesCount = $albumDw->get('image_count') + $associatedImages;
		$lastImage = $this->getAlbumsModel()->getAlbumLastImage($album['album_id']);

		if ($album['image_count'] != $imagesCount || $album['last_image_id'] != $lastImage['image_id'])
		{
			$albumDw->set('image_count', $imagesCount);

			$albumDw->set('last_image_id', $lastImage['image_id']);
			$albumDw->set('last_image_date', $lastImage['image_date']);
			$albumDw->set('last_image_filename', $lastImage['filename']);
			$albumDw->save();
			$album = $albumDw->getMergedData();
		}

		if($associatedImages)
		{
			$data = $album + array('newImages' => $associatedImages);
			$this->getModelFromCache('XfRu_UserAlbums_Model_NewsFeed')->publishFeedItem($data, 'image');
			unset($data);
		}

		$this->getAlbumsModel()->rebuildAlbumSprite($album);

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('useralbums/view', $album),
			new XenForo_Phrase('xfr_useralbums_your_images_were_uploaded_and_saved')
		);
	}

	public function actionDelete()
	{
		$albumsModel = $this->getAlbumsModel();

		$albumId = $this->_input->filterSingle('album_id', XenForo_Input::UINT);
		$album = $this->assertAlbumValidAndViewable($albumId, XenForo_Visitor::getInstance()->getUserId());

		if (!$album || !$albumsModel->isAlbumDeletable($album))
		{
			throw $this->getNoPermissionResponseException();
		}

		$hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::UINT);
		$deleteType = ($hardDelete ? 'hard' : 'soft');

		if ($this->isConfirmedPost())
		{
			if ($deleteType == 'hard')
			{
				$albumDw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Album');
				$albumDw->setExistingData($album['album_id']);
				$albumDw->delete();

				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					XenForo_Link::buildPublicLink('useralbums'),
					new XenForo_Phrase('xfr_useralbums_album_has_been_deleted')
				);
			} else {
				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					XenForo_Link::buildPublicLink('useralbums/view', $album)
				);
			}
		} else  {
			$viewParams = array(
				'album' => $album,
				'breadCrumbs' => $albumsModel->getBreadCrumbs($album),
			);

			return $this->responseView('XfRu_UserAlbums_ViewPublic_DeleteAlbumConfirm', 'xfr_useralbums_delete_album_confirm', $viewParams);
		}
	}

	public function actionLikeAlbum()
	{
		if (!XfRu_UserAlbums_Permissions::canLikeImages())
		{
			throw $this->getNoPermissionResponseException();
		}

		$likeModel = $this->getLikeModel();

		$imageId = $this->_input->filterSingle('album_id', XenForo_Input::UINT);
		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);

		$albumsModel = $this->getAlbumsModel();

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($imageId, $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

		$existingLike = $likeModel->getContentLikeByLikeUser('xfr_useralbum', $album['album_id'], XenForo_Visitor::getUserId());

		if ($this->_request->isPost())
		{
			if ($existingLike)
			{
				$latestUsers = $likeModel->unlikeContent($existingLike);
			} else {
				$latestUsers = $likeModel->likeContent('xfr_useralbum', $album['album_id'], $album['user_id']);
			}

			$liked = ($existingLike ? false : true);

			if ($this->_noRedirect() && $latestUsers !== false)
			{
				$album['like_users'] = $latestUsers;
				$album['likes'] += ($liked ? 1 : -1);
				$album['like_date'] = ($liked ? XenForo_Application::$time : 0);

				$viewParams = array(
					'album' => $album,
					'liked' => $liked
				);
				return $this->responseView('XfRu_UserAlbums_ViewPublic_AlbumLikeConfirmed', '', $viewParams);
			} else {
				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					XenForo_Link::buildPublicLink('useralbums/view', $album)
				);
			}
		} else {
			$owner = array(
				'username' => $album['username'],
				'user_id' => $album['user_id']
			);

			$viewParams = array(
				'album' => $album,
				'like' => $existingLike,
				'breadCrumbs' => $albumsModel->getBreadCrumbs($owner, $album)
			);
			return $this->responseView('XfRu_UserAlbums_ViewPublic_AlbumLike', 'xfr_useralbums_album_like', $viewParams);
		}
	}

	public function actionLikesAlbum()
	{
		$likeModel = $this->getLikeModel();

		$albumId = $this->_input->filterSingle('album_id', XenForo_Input::UINT);
		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);

		$albumsModel = $this->getAlbumsModel();

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($albumId, $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

		$likes = $likeModel->getContentLikes('xfr_useralbum', $albumId);

		if (!$likes)
		{
			return $this->responseError(new XenForo_Phrase('xfr_useralbums_no_one_has_liked_this_album_yet'));
		}

		$owner = array(
			'username' => $album['username'],
			'user_id' => $album['user_id']
		);

		$viewParams = array(
			'album' => $album,
			'likes' => $likes,
			'breadCrumbs' => $albumsModel->getBreadCrumbs($owner, $album)
		);

		return $this->responseView('XfRu_UserAlbums_ViewPublic_AlbumLikes', 'xfr_useralbums_album_likes', $viewParams);
	}

	private function assertCreateAlbum()
	{
		if (XfRu_UserAlbums_Permissions::canCreateAlbum())
		{
			$maxAlbums = XenForo_Application::get('options')->XfRu_UA_albumsPerUser;

			if ($maxAlbums)
			{
				$existingAlbums = $this->getAlbumsModel()->getUserAlbumsCount(XenForo_Visitor::getInstance()->getUserId());
				if ($existingAlbums >= $maxAlbums)
				{
					throw $this->getMaxAlbumsResponseException();
				}
			}
		} else {
			throw $this->getNoPermissionResponseException();
		}

		return true;
	}

	private function assertViewAlbums()
	{
	    if (XfRu_UserAlbums_Permissions::canViewAlbums())
	    {
			return true;
	    } else {
		    throw $this->getNoPermissionResponseException();
	    }
	}

	private function assertAlbumValidAndViewable($albumId, $userId)
	{
//		$fetchOptions = array(
//			'join' => XfRu_UserAlbums_Model_Albums::FETCH_USER
//		);
//
		$albumsModel = $this->getAlbumsModel();
//	    $album = $albumsModel->getAlbumById($albumId, $fetchOptions);

		$accessHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($albumId, $accessHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

		if (empty($album))
		{
			throw $this->getAlbumNotFoundException();
		}

//		if (!$albumsModel->isAlbumViewable($album, $accessHash))
//		{
//			throw $this->getNoPermissionResponseException();
//		}

	    return $album;
	}

	private function assertCanUploadToAlbum($album, $userId)
	{
		if (empty($album) || ($album['album_type'] != 'global' && $album['user_id'] != $userId))
		{
			throw $this->getNoPermissionResponseException();
		}
	}

	private function assertEditAlbum($albumId)
	{
		$fetchOptions = array(
			'conditions' => array(
				'canModerateAlbums' => XfRu_UserAlbums_Permissions::canApproveUnapprove(),
			)
		);

		$albumsModel = $this->getAlbumsModel();

		$album = $albumsModel->getAlbumById($albumId, $fetchOptions);

	    if ($albumsModel->isAlbumEditable($album))
	    {
		    return $album;
	    } else {
		    throw $this->getNoPermissionResponseException();
	    }

	}

	private function getMaxAlbumsResponseException()
	{
		return $this->responseException(
			$this->responseError(new XenForo_Phrase('xfr_useralbums_error_max_album_count_reached'))
		);
	}

	public static function getSessionActivityDetailsForList(array $activities)
	{
		$userActivity = array();

		foreach ($activities AS $key => $activity)
		{
			$phrase = '';
			switch ($activity['controller_action'])
			{
				case 'Index' : $phrase = 'xfr_useralbums_viewing_albums_list'; break;
				case 'View' : $phrase = 'xfr_useralbums_viewing_album'; break;
				case 'List' : $phrase = 'xfr_useralbums_view_own_albums_list'; break;
				case 'Edit' : $phrase = 'xfr_useralbums_editing_album'; break;
				case 'Create' : $phrase = 'xfr_useralbums_creating_album'; break;
			}

			if (!empty($phrase))
			{
				$userActivity[$key] = array(
					new XenForo_Phrase($phrase),
					null,
					null,
					null
				);
			}
		}

		return $userActivity;
	}
}