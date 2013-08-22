<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Image.php 337 2011-11-17 18:18:37Z pepelac $ $Date: 2011-11-17 19:18:37 +0100 (Thu, 17 Nov 2011) $ $Revision: 337 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ControllerPublic_Image extends XfRu_UserAlbums_ControllerPublic_Abstract
{
	public function actionImagebox()
	{
		$imageId = $this->_input->filterSingle('image_id', XenForo_Input::UINT);
		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);
		$image = $this->getImageOrError($imageId);
		$albumsModel = $this->getAlbumsModel();
		$imagesModel = $this->getImagesModel();

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($image['album_id'], $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

		$imageNeighbours = $this->getImagesModel()->getImageNeighbours($album, $image['image_id']);

	    $imagesModel->logImageView($image['image_id']);

		$viewParams = array(
			'album' => $album,
			'image' => $image,
			'imageNeighbours' => $imageNeighbours,
		);

		return $this->responseView('XfRu_UserAlbums_ViewPublic_Imagebox', 'xfr_useralbums_imagebox', $viewParams);
	}

	public function actionViewImage()
	{

		$imageId = $this->_input->filterSingle('image_id', XenForo_Input::UINT);
		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);
		$image = $this->getImageOrError($imageId);

		$albumsModel = $this->getAlbumsModel();
		$imagesModel = $this->getImagesModel();

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($image['album_id'], $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

	    $imageNeighbours = $this->getImagesModel()->getImageNeighbours($album, $image['image_id']);

	    $imagesModel->logImageView($image['image_id']);

	    $owner = array(
		    'username' => $album['username'],
		    'user_id' => $album['user_id']
	    );

		$images = $imagesModel->prepareImages(array($image), $album);

		$visitorId = XenForo_Visitor::getUserId();

		$images[0]['like_users'] = unserialize($images[0]['like_users']);
		$images[0]['like_date'] = $this->getLikeModel()->getContentLikeByLikeUser('xfr_useralbum_image', $images[0]['image_id'], $visitorId);

		$comments = $this->getCommentsModel()->getCommentsByImageId($images[0]['image_id']);
		$commentsDate = null;
		if (!empty($comments))
		{
			$commentsDate = $comments[0]['comment_date'];
			$commentsModel = $this->getCommentsModel();
			foreach ($comments AS &$cmnt)
			{
				$cmnt['canEditComment'] = $commentsModel->canEditComment($cmnt);
				$cmnt['canDeleteComment'] = $commentsModel->canDeleteComment($cmnt);
				$cmnt['message'] = XenForo_Helper_String::censorString($cmnt['message']);
			}
		}

		$commentsOrder = XenForo_Application::get('options')->XfRu_UA_commentsOrder;
		if ($commentsOrder == 'oldest')
		{
			$comments = array_reverse($comments);
		}

		$images[0]['comments_count'] = count($comments);
		
	    $viewParams = array(
		    'album' => $album,
		    'image' => $images[0],
		    'imageNeighbours' => $imageNeighbours,
		    'breadCrumbs' => $albumsModel->getBreadCrumbs($owner, $album),
		    'imageCommentDate' => $commentsDate,
		    'imageComments' => $comments,
		    'commentsOrder' => $commentsOrder,
		    'canEditImage' => $imagesModel->canEditImage($album),
		    'canDeleteImage' => $imagesModel->canDeleteImage($images[0]),
		    'canLikeImage' => XfRu_UserAlbums_Permissions::canLikeImages() && $image['user_id'] != $visitorId,
		    'canPostComments' => XfRu_UserAlbums_Permissions::canPostComments()
	    );

		if ($this->_noRedirect())
		{
			$viewParams['isOverlay'] = true;
			return $this->responseView(
				'XfRu_UserAlbums_ViewPublic_ViewImagebox',
				'',
				$viewParams
			);
		} else {
			return $this->responseView(
				'XfRu_UserAlbums_ViewPublic_ViewImage',
				'xfr_useralbums_image_view',
				$viewParams
			);
		}
	}

	public function actionStandalone()
	{

		$imageId = $this->_input->filterSingle('image_id', XenForo_Input::UINT);
		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);
		$image = $this->getImageOrError($imageId);
		$image['attach_date'] = $image['image_date'];

		$tempHash = $this->_input->filterSingle('temp_hash', XenForo_Input::STRING);

		$albumsModel = $this->getAlbumsModel();
		$imagesModel = $this->getImagesModel();

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($image['album_id'], $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

		if (!$imagesModel->canViewImage($image, $tempHash))
		{
			return $this->responseNoPermission();
		}

		$filePath = $imagesModel->getImageDataFilePath($image);
		if (!file_exists($filePath) || !is_readable($filePath))
		{
			return $this->responseError(new XenForo_Phrase('xfr_useralbums_image_cannot_be_shown_at_this_time'));
		}

		$this->canonicalizeRequestUrl(
			XenForo_Link::buildPublicLink('useralbums/standalone', $image)
		);

		$eTag = $this->_request->getServer('HTTP_IF_NONE_MATCH');
		if ($eTag && $eTag == $image['image_date'])
		{
			$this->_routeMatch->setResponseType('raw');
			return $this->responseView('XenForo_ViewPublic_Attachment_View304');
		}

		if (!$this->_input->filterSingle('embedded', XenForo_Input::UINT))
		{
			$imagesModel->logImageView($imageId);
		}

		$this->_routeMatch->setResponseType('raw');

		$viewParams = array(
			'attachment' => $image,
			'attachmentFile' => $filePath
		);

		return $this->responseView('XenForo_ViewPublic_Attachment_View', '', $viewParams);
	}

	public function actionReportImage()
	{
		$imageId = $this->_input->filterSingle('image_id', XenForo_Input::UINT);
		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);

		$albumsModel = $this->getAlbumsModel();

		$image = $this->getImageOrError($imageId);

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($image['album_id'], $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

		if ($this->_request->isPost())
		{
			$message = $this->_input->filterSingle('message', XenForo_Input::STRING);

			if (!$message)
			{
				return $this->responseError(new XenForo_Phrase('xfr_useralbums_please_enter_reason_for_reporting_this_image'));
			}

			$reportModel = XenForo_Model::create('XenForo_Model_Report');
			$reportModel->reportContent('xfr_useralbum_image', $image, $message);

			$controllerResponse = $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('useralbums/view-image', $image)
			);

			$controllerResponse->redirectMessage = new XenForo_Phrase('xfr_useralbums_thank_you_for_reporting_this_image');

			return $controllerResponse;
		} else {
			$image['title'] = $album['title'];

			$owner = array(
				'username' => $album['username'],
				'user_id' => $album['user_id']
			);
			$viewParams = array(
				'image' => $image,
				'breadCrumbs' => $albumsModel->getBreadCrumbs($owner, $album),
			);

			return $this->responseView('XfRu_UserAlbums_ViewPublic_ReportImage', 'xfr_useralbums_image_report', $viewParams);
		}
	}

	public function actionLikeImage()
	{
		if (!XfRu_UserAlbums_Permissions::canLikeImages())
		{
			throw $this->getNoPermissionResponseException();
		}

		$likeModel = $this->getLikeModel();

		$imageId = $this->_input->filterSingle('image_id', XenForo_Input::UINT);
		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);

		$albumsModel = $this->getAlbumsModel();

		$image = $this->getImageOrError($imageId);

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($image['album_id'], $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

		$image['title'] = $album['title'];

		$existingLike = $likeModel->getContentLikeByLikeUser('xfr_useralbum_image', $image['image_id'], XenForo_Visitor::getUserId());

		if ($this->_request->isPost())
		{
			if ($existingLike)
			{
				$latestUsers = $likeModel->unlikeContent($existingLike);
			} else {
				$latestUsers = $likeModel->likeContent('xfr_useralbum_image', $image['image_id'], $image['user_id']);
			}

			$liked = ($existingLike ? false : true);

			if ($this->_noRedirect() && $latestUsers !== false)
			{
				$image['like_users'] = $latestUsers;
				$image['likes'] += ($liked ? 1 : -1);
				$image['like_date'] = ($liked ? XenForo_Application::$time : 0);

				$viewParams = array(
					'image' => $image,
					'liked' => $liked
				);
				return $this->responseView('XfRu_UserAlbums_ViewPublic_ImageLikeConfirmed', '', $viewParams);
			} else {
				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					XenForo_Link::buildPublicLink('useralbums/view-image', $image)
				);
			}
		} else {
			$owner = array(
				'username' => $image['username'],
				'user_id' => $image['user_id']
			);

			$viewParams = array(
				'image' => $image,
				'like' => $existingLike,
				'breadCrumbs' => $albumsModel->getBreadCrumbs($owner, $album)
			);
			return $this->responseView('XfRu_UserAlbums_ViewPublic_ImageLike', 'xfr_useralbums_image_like', $viewParams);
		}
	}

	public function actionLikesImage()
	{
		$likeModel = $this->getLikeModel();
//		$imagesModel = $this->getImagesModel();

		$imageId = $this->_input->filterSingle('image_id', XenForo_Input::UINT);
		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);

		$albumsModel = $this->getAlbumsModel();

		$image = $this->getImageOrError($imageId);

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($image['album_id'], $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}
		
		$likes = $likeModel->getContentLikes('xfr_useralbum_image', $image['image_id']);

		if (!$likes)
		{
			return $this->responseError(new XenForo_Phrase('xfr_useralbums_no_one_has_liked_this_image_yet'));
		}

		$owner = array(
			'username' => $album['username'],
			'user_id' => $album['user_id']
		);

		$image['title'] = $album['title'];

		$viewParams = array(
//			'image' => $image,
			'album' => $album,
			'likes' => $likes,
			'breadCrumbs' => $albumsModel->getBreadCrumbs($owner, $album)
		);

		return $this->responseView('XfRu_UserAlbums_ViewPublic_ImageLikes', 'xfr_useralbums_image_likes', $viewParams);
	}

	public function actionEditImageDescr()
	{
		$albumsModel = $this->getAlbumsModel();
		$imagesModel = $this->getImagesModel();

		$imageId = $this->_input->filterSingle('image_id', XenForo_Input::UINT);
		$image = $this->getImageOrError($imageId);

		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($image['album_id'], $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

		if (!$imagesModel->canEditImage($album))
		{
			return $this->responseNoPermission();
		}

		$owner = array(
			'username' => $album['username'],
			'user_id' => $album['user_id']
		);

		$image['title'] = $album['title'];

		$viewParams = array(
			'image' => $image,
			'album' => $album,
			'breadCrumbs' => $albumsModel->getBreadCrumbs($owner, $album)
		);

		return $this->responseView('XfRu_UserAlbums_ViewPublic_ImageEdit', 'xfr_useralbums_image_edit', $viewParams);
	}

	public function actionEditImageDescrInline()
	{
		$imagesModel = $this->getImagesModel();

		$imageId = $this->_input->filterSingle('image_id', XenForo_Input::UINT);
		$image = $this->getImageOrError($imageId);

		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);
		$albumsModel = $this->getAlbumsModel();

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($image['album_id'], $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

		if (!$imagesModel->canEditImage($album))
		{
			return $this->responseNoPermission();
		}


		$owner = array(
			'username' => $album['username'],
			'user_id' => $album['user_id']
		);

		$image['title'] = $album['title'];

		$viewParams = array(
			'image' => $image,
			'album' => $album,
			'breadCrumbs' => $albumsModel->getBreadCrumbs($owner, $album)
		);

		return $this->responseView('XfRu_UserAlbums_ViewPublic_ImageEditInline', 'xfr_useralbums_image_edit_inline', $viewParams);
	}

	public function actionSaveImageDescr()
	{
		$this->_assertPostOnly();

		$imageId = $this->_input->filterSingle('image_id', XenForo_Input::UINT);
		$image = $this->getImageOrError($imageId);

		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);
		$albumsModel = $this->getAlbumsModel();

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($image['album_id'], $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

		$imagesModel = $this->getImagesModel();

		if (!$imagesModel->canEditImage($album))
		{
			return $this->responseNoPermission();
		}

		$writer = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_ImageData');
		$writer->setExistingData($image['data_id']);
		$writer->set('description', XenForo_Helper_String::autoLinkBbCode($this->getHelper('Editor')->getMessageText('message', $this->_input)));
		$writer->save();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('useralbums/view-image', $image)
		);
	}

	public function actionSaveImageDescrInline()
	{
		$this->_assertPostOnly();

		// todo: enable more button in the tpl

		if ($this->_input->inRequest('more_options'))
		{
			return $this->responseReroute(__CLASS__, 'edit-image-descr');
		}

		$imageId = $this->_input->filterSingle('image_id', XenForo_Input::UINT);
		$image = $this->getImageOrError($imageId);

		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);
		$albumsModel = $this->getAlbumsModel();

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($image['album_id'], $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

		$imagesModel = $this->getImagesModel();

		if (!$imagesModel->canEditImage($album))
		{
			return $this->responseNoPermission();
		}

		$writer = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_ImageData');
		$writer->setExistingData($image['data_id']);
		$writer->set('description', XenForo_Helper_String::autoLinkBbCode($this->getHelper('Editor')->getMessageText('message', $this->_input)));
		$writer->save();

		if ($this->_noRedirect())
		{
			$this->_request->setParam('image_id', $imageId);
			return $this->responseReroute('XfRu_UserAlbums_ControllerPublic_Image', 'show-image');
		}  else {
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('useralbums/view-image', $image)
			);
		}
	}

	public function actionShowImage()
	{
		$likeModel = $this->getLikeModel();
		$imagesModel = $this->getImagesModel();

		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);
		$albumsModel = $this->getAlbumsModel();

		$image = $imagesModel->getImageById($this->_input->filterSingle('image_id', XenForo_Input::UINT));

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($image['album_id'], $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

		$visitorId = XenForo_Visitor::getUserId();

		$image['like_users'] = unserialize($image['like_users']);
		$image['like_date'] = $likeModel->getContentLikeByLikeUser('xfr_useralbum_image', $image['image_id'], $visitorId);

		$viewParams = array(
			'album' => $album,
			'image' => $image,
			'canEditImage' => $imagesModel->canEditImage($album),
		    'canDeleteImage' => $imagesModel->canDeleteImage($image),
		    'canLikeImage' => XfRu_UserAlbums_Permissions::canLikeImages() && $album['user_id'] != $visitorId
		);
		
		return $this->responseView('XfRu_UserAlbums_ViewPublic_ViewImage', '', $viewParams);
	}

	public function actionDeleteImageConfirm()
	{
		$imagesModel = $this->getImagesModel();

		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);
		$albumsModel = $this->getAlbumsModel();

		$image = $imagesModel->getImageById($this->_input->filterSingle('image_id', XenForo_Input::UINT));

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($image['album_id'], $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

		if (!$imagesModel->canDeleteImage($image))
		{
			throw $this->getNoPermissionResponseException();
		}

		$hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::UINT);
		$deleteType = ($hardDelete ? 'hard' : 'soft');

		if ($this->isConfirmedPost())
		{
			if ($deleteType == 'hard')
			{
				$dw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Image');
				$dw->setExistingData($image, true);
				$dw->delete();

				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					XenForo_Link::buildPublicLink('useralbums/view', $album)
				);
			} else {
				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					XenForo_Link::buildPublicLink('useralbums/view-image', $image)
				);
			}
		} else  {
			$owner = array(
				'username' => $album['username'],
				'user_id' => $album['user_id']
			);

			$image['title'] = $album['title'];

			$viewParams = array(
				'image' => $image,
				'album' => $album,
				'breadCrumbs' => $albumsModel->getBreadCrumbs($owner, $album)
			);

			return $this->responseView('XfRu_UserAlbums_ViewPublic_DeleteImageConfirm', 'xfr_useralbums_delete_image_confirm', $viewParams);
		}
	}

	public static function getSessionActivityDetailsForList(array $activities)
	{
		$userActivity = array();

		foreach ($activities AS $key => $activity)
		{
			$phrase = '';
			switch ($activity['controller_action'])
			{
				case 'ViewImage' :
				case 'Standalone' :
					$phrase = 'xfr_useralbums_viewing_album_image'; break;
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