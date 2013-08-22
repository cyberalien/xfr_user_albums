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

class XfRu_UserAlbums_ControllerPublic_Images extends XfRu_UserAlbums_ControllerPublic_Abstract
{	
	public function actionManageImages()
	{
		$albumId = $this->_input->filterSingle('album_id', XenForo_Input::UINT);
		$fetchOptions = array(
			'join' => XfRu_UserAlbums_Model_Albums::FETCH_USER
		);
		$album = $this->getAlbumsModel()->getAlbumById($albumId, $fetchOptions);

		$this->assertCanManageImages($album);

		$visitor = XenForo_Visitor::getInstance();

		$imagesModel = $this->getImagesModel();

		$imageParams = $imagesModel->getImageParams($album, array(
			'album_id' => $album['album_id']
		), $visitor->toArray());

		$images = $imagesModel->prepareImages($imagesModel->getImagesByAlbumId($albumId, $album['album_type']), $album);

		$viewParams = array(
		    'breadCrumbs' => $this->getAlbumsModel()->getBreadCrumbs($album, $album),
			'album' => $album,
			'images' => $images,
		    'imageConstraints' => $imagesModel->getImageConstraints(),
		    'imageParams' => $imageParams,
	    );

	    return $this->responseView('XfRu_UserAlbums_ViewPublic_AddImage', 'xfr_useralbums_add_images', $viewParams);
	}

	public function actionUpload()
	{
		$input = $this->_input->filter(array(
			'hash' => XenForo_Input::STRING,
			'content_data' => array(XenForo_Input::UINT, 'array' => true)
		));

		if (!$input['hash'])
		{
			$input['hash'] = $this->_input->filterSingle('temp_hash', XenForo_Input::STRING);
		}

		$this->assertCanUploadImage($input['hash'], $input['content_data']);

		$imagesModel = $this->getImagesModel();
		$albumId = $imagesModel->getAlbumIdFromContentData($input['content_data']);

		$existingImages = ($albumId ? $imagesModel->getImagesByAlbumId($albumId) : array());
		$newImages = $imagesModel->getImagesByTempHash($input['hash']);

		$maxImages = $imagesModel->getImageCountLimit();
		if ($maxImages === true)
		{
			$canUpload = true;
			$remainingUploads = true;
		} else {
			$remainingUploads = $maxImages - (count($existingImages) + count($newImages));
			$canUpload = ($remainingUploads > 0);
		}

		$viewParams = array(
			'imageConstraints' => $imagesModel->getImageConstraints(),
			'existingImages' => $existingImages,
			'newImages' => $newImages,

			'canUpload' => $canUpload,
			'remainingUploads' => $remainingUploads,

			'hash' => $input['hash'],
			'contentData' => $input['content_data'],
			'imageParams' => array(
				'hash' => $input['hash'],
				'content_data' => $input['content_data']
			)
		);

		return $this->responseView('XfRu_UserAlbums_ViewPublic_Upload', 'xfr_useralbums_image_upload', $viewParams);
	}

	public function actionDoUpload()
	{
		$this->_assertPostOnly();

		$deleteArray = array_keys($this->_input->filterSingle('delete', XenForo_Input::ARRAY_SIMPLE));
		$delete = reset($deleteArray);
		if ($delete)
		{
			$this->_request->setParam('image_id', $delete);
			return $this->responseReroute(__CLASS__, 'delete');
		}

		$input = $this->_input->filter(array(
			'hash' => XenForo_Input::STRING,
			'content_data' => array(XenForo_Input::UINT, 'array' => true)
		));

		if (!$input['hash'])
		{
			$input['hash'] = $this->_input->filterSingle('temp_hash', XenForo_Input::STRING);
		}

		$this->assertCanUploadImage($input['hash'], $input['content_data']);

		$imagesModel = $this->getImagesModel();
		$albumId = $imagesModel->getAlbumIdFromContentData($input['content_data']);

		$existingImages = ($albumId ? $imagesModel->getImagesByAlbumId($albumId) : array());
		$newImages = $imagesModel->getImagesByTempHash($input['hash']);



		$maxImages = $imagesModel->getImageCountLimit();
		if ($maxImages !== true)
		{
			$remainingUploads = $maxImages - (count($existingImages) + count($newImages));
			if ($remainingUploads <= 0)
			{
				return $this->responseError(new XenForo_Phrase('xfr_useralbums_you_may_not_upload_more_images_to_album'));
			}
		}

		$imageConstraints = $imagesModel->getImageConstraints();

		/**
		 * @var XenForo_Upload $image
		 */
		$image = XenForo_Upload::getUploadedFile('upload');
		if (!$image)
		{
			return $this->responseError(new XenForo_Phrase('xfr_useralbums_file_was_not_uploaded'));
		}

		$image->setConstraints($imageConstraints);
		if (!$image->isValid())
		{
			return $this->responseError($image->getErrors());
		}

		$dataId = $imagesModel->insertUploadedImageData($image, XenForo_Visitor::getUserId());
		$imageId = $imagesModel->insertTemporaryImage($dataId, $input['hash']);

		if ($this->_noRedirect())
		{
			$viewParams = array(
				'image' => $imagesModel->prepareImage($imagesModel->getImageById($imageId))
			);

			return $this->responseView('XfRu_UserAlbums_ViewPublic_DoUpload', '', $viewParams);
		} else {
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('useralbums/upload', false, array(
					'hash' => $input['hash'],
					'content_data' => $input['content_data']
				))
			);
		}
	}

	public function actionDeleteImage()
	{
		$this->_assertPostOnly();

		$input = $this->_input->filter(array(
			'image_id' => XenForo_Input::UINT,
			'hash' => XenForo_Input::STRING,
			'content_data' => array(XenForo_Input::UINT, 'array' => true)
		));

		if (!$input['hash'])
		{
			$input['hash'] = $this->_input->filterSingle('temp_hash', XenForo_Input::STRING);
		}


		$image = $this->getImageOrError($input['image_id']);
		if (!$this->getImagesModel()->canDeleteImage($image, $input['hash']))
		{
			return $this->responseNoPermission();
		}

		$dw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Image');
		$dw->setExistingData($image, true);
		$dw->delete();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('useralbums/upload', false, array(
				'hash' => $input['hash'],
				'content_data' => $input['content_data']
			))
		);
	}

	protected function assertCanManageImages(array $album)
	{
		if (empty($album))
		{
			throw $this->getNoPermissionResponseException();
		}

		if (!XfRu_UserAlbums_Permissions::canCreateAlbum())
		{
			throw $this->getNoPermissionResponseException();
		}

		if (!$this->getAlbumsModel()->isAlbumOwner($album) && $album['album_type'] != 'global')
		{
			throw $this->getNoPermissionResponseException();
		}
	}

	protected function assertCanUploadImage($hash, array $contentData)
	{
		if (!$hash)
		{
			throw $this->getNoPermissionResponseException();
		}

		$albumsModel = $this->getAlbumsModel();
        if (!array_key_exists('album_id', $contentData))
        {
            throw $this->getNoPermissionResponseException();
        }

		$album = $albumsModel->getAlbumById($contentData['album_id']);

		$maxImagesPerAlbum = XenForo_Application::get('options')->XfRu_UA_imagesPerAblum;
		if ($maxImagesPerAlbum && $album['image_count'] >= $maxImagesPerAlbum)
		{
			throw $this->getMaxImagesPerAlbumResponseException();
		}

		if (!$albumsModel->canUploadImage($album, XenForo_Visitor::getInstance()->toArray()))
		{
			 throw $this->getNoPermissionResponseException();
		}
	}

	private function getMaxImagesPerAlbumResponseException()
	{
		return $this->responseException(
			$this->responseError(new XenForo_Phrase('xfr_useralbums_error_max_images_per_album_count_reached'))
		);
	}
}