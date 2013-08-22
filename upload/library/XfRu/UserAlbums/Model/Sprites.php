<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Sprites.php 378 2011-12-23 13:29:39Z pepelac $ $Date: 2011-12-23 14:29:39 +0100 (Fri, 23 Dec 2011) $ $Revision: 378 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_Model_Sprites extends XenForo_Model
{

	public function assembleSprites(array $images, array $album)
	{
		if (empty($images) || empty($album))
		{
			return;
		}

		$spriteWidth = $thumbDimensions = XenForo_Application::get('options')->XfRu_UA_thumbDimensions;

		$spriteHeight = $spriteWidth * count($images);
		$thumbOffset = $thumbDimensions;// + 1;

		$spriteImage = imagecreatetruecolor($spriteWidth, $spriteHeight);
		$yPos = 0;

		$imagesModel = $this->getImagessModel();
		$albumsModel = $this->getAlbumsModel();

		$processed = 0;

		foreach ($images as $image)
		{
			$file = $imagesModel->getImageThumbnailDataFilePath($image);

			$thumb = $this->createImageFormFile($file);
			if ($thumb)
			{
				if (imagecopyresampled($spriteImage, $thumb, 0, $yPos, 0, 0, $spriteWidth, $spriteWidth, $thumbDimensions, $thumbDimensions))
                {
                    $yPos += $thumbOffset;
                    $processed++;
                }
			}
			unset($thumb);
		}

		$spritePath = $albumsModel->getAlbumThumbnailSpriteFilePath($album);

		$this->ensurePathExists($spritePath);

		if ($processed == count($images))
		{
			imagejpeg($spriteImage, $spritePath, 85);
			unset($spriteImage);
		} else if ($processed) {
            $tmpImg = tempnam(XenForo_Helper_File::getTempDir(), 'xf');
            if (imagejpeg($spriteImage, $tmpImg, 100))
            {
                $fixedImage = XenForo_Image_Abstract::createFromFile($tmpImg, IMAGETYPE_JPEG);
                $fixedImage->crop(0, 0, $spriteWidth, ($processed * $spriteWidth));
                $fixedImage->output(IMAGETYPE_JPEG, $spritePath);
                unset($fixedImage);
                unlink($tmpImg);
            }

		}
	}

	protected function createImageFormFile($fileName)
	{
        if (!file_exists($fileName))
        {
            return false;
        }

		$fileInfo = @getimagesize($fileName);

        if (!$fileInfo)
        {
            return false;
        }

		switch ($fileInfo[2])
		{
			case IMAGETYPE_GIF:
				if (!function_exists('imagecreatefromgif'))
				{
					return false;
				}
				$image = imagecreatefromgif($fileName);
				break;

			case IMAGETYPE_JPEG:
				if (!function_exists('imagecreatefromjpeg'))
				{
					return false;
				}
				$image = imagecreatefromjpeg($fileName);
				break;

			case IMAGETYPE_PNG:
				if (!function_exists('imagecreatefrompng'))
				{
					return false;
				}
				$image = imagecreatefrompng($fileName);
				break;

			default:
				return false;
		}
		return $image;
	}

	protected function ensurePathExists($path)
	{
		$parts = explode('/', $path);
		array_pop($parts);
		$path = implode('/', $parts);
		unset($parts);

		if (!is_dir($path))
		{
			if (!mkdir($path))
			{
				throw new XenForo_Exception('Cannot create folder for sprite!');
			}
		}
	}

	/**
	 * @return XfRu_UserAlbums_Model_Albums
	 */
	protected function getAlbumsModel()
	{
		return $this->getModelFromCache('XfRu_UserAlbums_Model_Albums');
	}

	/**
	 * @return XfRu_UserAlbums_Model_Images
	 */
	protected function getImagessModel()
	{
		return $this->getModelFromCache('XfRu_UserAlbums_Model_Images');
	}
}