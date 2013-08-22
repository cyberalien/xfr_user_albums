<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Comments.php 334 2011-11-17 14:40:38Z pepelac $ $Date: 2011-11-17 15:40:38 +0100 (Thu, 17 Nov 2011) $ $Revision: 334 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ControllerPublic_Comments extends XfRu_UserAlbums_ControllerPublic_Abstract
{
	public function actionImageAddComment()
	{
		if (!XfRu_UserAlbums_Permissions::canPostComments())
		{
			throw $this->getNoPermissionResponseException();
		}

		$this->_assertPostOnly();
		$this->_assertRegistrationRequired();

		$imageId = $this->_input->filterSingle('image_id', XenForo_Input::UINT);
		$message = $this->_input->filterSingle('message', XenForo_Input::STRING);

		$visitor = XenForo_Visitor::getInstance()->toArray();

		$data = array(
			'image_id' => $imageId,
			'user_id' => $visitor['user_id'],
			'username' => $visitor['username'],
			'message' => $message
		);

		$writer = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Comment');
		$writer->bulkSet($data);
		$writer->save();

		$this->getModelFromCache('XfRu_UserAlbums_Model_NewsFeed')->publishFeedItem($writer->getMergedData(), 'comment');

		$commentsModel = $this->getCommentsModel();

		$commentsModel->sendAlerts($imageId, $writer->getMergedData());

		if ($this->_noRedirect())
		{
			$lastCommentDate = $this->_input->filterSingle('imageCommentDate', XenForo_Input::UINT);
			$newComments = $commentsModel->getNewestCommentsByDate($imageId, $lastCommentDate);

			$lastCommentDate = $newComments[0]['comment_date'];

			foreach ($newComments AS &$cmnt)
			{
				$cmnt['canEditComment'] = $commentsModel->canEditComment($cmnt);
				$cmnt['canDeleteComment'] = $commentsModel->canDeleteComment($cmnt);
				$cmnt['message'] = XenForo_Helper_String::censorString($cmnt['message']);
			}

			$viewParams = array(
				'imageComments' => $newComments,
				'imageCommentDate' => $lastCommentDate
			);

			return $this->responseView('XfRu_UserAlbums_ViewPublic_ImageComment', 'xfr_useralbums_image_comment', $viewParams);
		}

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('useralbums/image-comments', $writer->getMergedData()),
			new XenForo_Phrase('xfr_useralbums_your_comment_has_been_posted')
		);
	}

	public function actionImageComments()
	{
		$commentId = $this->_input->filterSingle('comment_id', XenForo_Input::UINT);

		$commentsModel = $this->getCommentsModel();
		$imagesModel = $this->getImagesModel();

		$comment = $commentsModel->getCommentById($commentId);
		$image = $imagesModel->getImageById($comment['image_id']);

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('useralbums/view-image', $image) . '#imagecomment-' . $comment['comment_id']
		);
	}

	public function actionCommentEdit()
	{
		$commentsModel = $this->getCommentsModel();
		$imagesModel = $this->getImagesModel();

		$comment = $commentsModel->getCommentById($this->_input->filterSingle('comment_id', XenForo_Input::UINT));
		$image = $imagesModel->getImageById($comment['image_id']);

		if (!$commentsModel->canEditComment($comment))
		{
			throw $this->getNoPermissionResponseException();
		}

		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);
		$albumsModel = $this->getAlbumsModel();

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($image['album_id'], $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

		$owner = array(
			'username' => $album['username'],
			'user_id' => $album['user_id']
		);

		$image['title'] = $album['title'];

		$viewParams = array(
			'image' => $image,
			'comment' => $comment,
			'breadCrumbs' => $albumsModel->getBreadCrumbs($owner, $album)
		);

		return $this->responseView('XfRu_UserAlbums_ViewPublic_Comment_Edit', 'xfr_useralbums_comment_edit', $viewParams);
	}

	public function actionCommentSave()
	{
		$this->_assertPostOnly();

		$commentId = $this->_input->filterSingle('comment_id', XenForo_Input::UINT);
		$comment = $this->getCommentOrError($commentId);

		if (!$this->getCommentsModel()->canEditComment($comment))
		{
			throw $this->getNoPermissionResponseException();
		}

		$image = $this->getImageOrError($comment['image_id']);

		$writer = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Comment');
		$writer->setExistingData($comment['comment_id']);
		$writer->set('message', XenForo_Helper_String::autoLinkBbCode($this->getHelper('Editor')->getMessageText('message', $this->_input)));
		$writer->save();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('useralbums/view-image', $image) . '#imagecomment-' . $comment['comment_id']
		);
	}

	public function actionCommentDelete()
	{
		$albumsModel = $this->getAlbumsModel();
		$imagesModel = $this->getImagesModel();
		$commentsModel = $this->getCommentsModel();

		$commentId = $this->_input->filterSingle('comment_id', XenForo_Input::UINT);
		$comment = $this->getCommentOrError($commentId);

		if (!$commentsModel->canDeleteComment($comment))
		{
			throw $this->getNoPermissionResponseException();
		}

		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);
		$image = $imagesModel->getImageById($comment['image_id']);

		try {
			$album = $albumsModel->assertAlbumValidAndViewable($image['album_id'], $albumHash);
		} catch (Exception $e) {
			throw $this->getNoPermissionResponseException();
		}

		$hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::UINT);
		$deleteType = ($hardDelete ? 'hard' : 'soft');

		if ($this->isConfirmedPost())
		{
			if ($deleteType == 'hard')
			{
				$dw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Comment');
				$dw->setExistingData($comment, true);
				$dw->delete();

				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					XenForo_Link::buildPublicLink('useralbums/view-image', $image)
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
//				'image' => $image,
				'album' => $album,
				'comment' => $comment,
				'breadCrumbs' => $albumsModel->getBreadCrumbs($owner, $album)
			);

			return $this->responseView('XfRu_UserAlbums_ViewPublic_CommentDelete', 'xfr_useralbums_delete_comment', $viewParams);
		}
	}

	public function actionCommentReport()
	{
		$albumsModel = $this->getAlbumsModel();
//		$imagesModel = $this->getImagesModel();
//		$commentsModel = $this->getCommentsModel();

		$commentId = $this->_input->filterSingle('comment_id', XenForo_Input::UINT);
		$comment = $this->getCommentOrError($commentId);

		$albumHash = $this->_input->filterSingle('access_hash', XenForo_Input::STRING);

		$image = $this->getImageOrError($comment['image_id']);

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
				return $this->responseError(new XenForo_Phrase('xfr_useralbums_please_enter_reason_for_reporting_this_comment'));
			}

			$reportModel = XenForo_Model::create('XenForo_Model_Report');
			$reportModel->reportContent('xfr_useralbum_image_cmnt', $comment, $message);

			$controllerResponse = $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('useralbums/view-image', $image) . '#imagecomment-' . $comment['comment_id']
			);

			$controllerResponse->redirectMessage = new XenForo_Phrase('xfr_useralbums_thank_you_for_reporting_this_comment');

			return $controllerResponse;
		} else {
			$comment['title'] = $album['title'];

			$owner = array(
				'username' => $album['username'],
				'user_id' => $album['user_id']
			);
			$viewParams = array(
				'comment' => $comment,
				'breadCrumbs' => $albumsModel->getBreadCrumbs($owner, $album),
			);

			return $this->responseView('XfRu_UserAlbums_ViewPublic_CommentReport', 'xfr_useralbums_comment_report', $viewParams);
		}
	}
}