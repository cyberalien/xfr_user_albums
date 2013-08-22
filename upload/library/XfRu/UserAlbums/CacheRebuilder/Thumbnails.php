<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Thumbnails.php 334 2011-11-17 14:40:38Z pepelac $ $Date: 2011-11-17 15:40:38 +0100 (Thu, 17 Nov 2011) $ $Revision: 334 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_CacheRebuilder_Thumbnails extends XfRu_UserAlbums_CacheRebuilder_Abstract
{

	/**
	 * Gets a message about the type of content being rebuilt.
	 * Likely depends on phrases existing.
	 *
	 * @return string|XenForo_Phrase
	 */
	public function getRebuildMessage()
	{
		return new XenForo_Phrase('xfr_useralbums_thumbnails');
	}

	public function showExitLink()
	{
		return true;
	}

	/**
	 * Rebuilds the data as requested. If there is a large amount of data, it should
	 * only be partially rebuilt in each invocation.
	 *
	 * If true is returned, then the rebuild is done. Otherwise, an integer should be returned.
	 * This will be passed to the next call as the position.
	 *
	 * @param integer $position Position to start building from.
	 * @param array $options List of options. Can be modified and updated value will be passed to next call.
	 * @param string $detailedMessage A detailed message about the progress to return.
	 *
	 * @return integer|true
	 */
	public function rebuild($position = 0, array &$options = array(), &$detailedMessage = '')
	{
		$options['batch'] = isset($options['batch']) ? $options['batch'] : 75;
		$options['batch'] = max(1, $options['batch']);

		if ($options['delay'] >= 0.01)
		{
			usleep($options['delay'] * 1000000);
		}

		/* @var $imagesModel XfRu_UserAlbums_Model_Images */
		$imagesModel = XenForo_Model::create('XfRu_UserAlbums_Model_Images');

		$imageDatas = $imagesModel->getImageDatasInRange($position, $options['batch']);

		if (sizeof($imageDatas) == 0)
		{
			return true;
		}

        $logHandler = fopen('/tmp/xfr_ua_thumb_rebuild.log', 'a');

		XenForo_Db::beginTransaction();

		foreach ($imageDatas AS $imageData)
		{
			$position = $imageData['data_id'];

			$dimensions  = array('thumbnail_width' => 0, 'thumbnail_height' => 0);

			$dataFile = $imagesModel->getImageDataFilePath($imageData);

            $logMessage = "Processing image data ".$imageData['data_id'];

			if ($dataFile)
			{
				$imageInfo = getimagesize($dataFile);
				
				$image = XenForo_Image_Abstract::createFromFile($dataFile, $imageInfo[2]);

                if ($image)
                {
                    if ($image->getWidth() > 900 || $image->getHeight() > 600)
                    {
                        $image->thumbnail(900, 600);
                    }

                    if ($image)
                    {
                        $thumbFile = $imagesModel->getImageThumbnailDataFilePath($imageData);

                        $cropPoint = array();
                        switch ($image->getOrientation())
                        {
                            case XenForo_Image_Abstract::ORIENTATION_LANDSCAPE :
                                $shortSide = $image->getHeight();
                                $centerX = round($image->getWidth() / 2);
                                $cropX = $centerX - round($shortSide / 2);
                                $cropPoint['x'] = ($cropX > 0) ? $cropX : 0;
                                $cropPoint['y'] = 0;
                                unset($centerX, $cropX);
                                break;

                            case XenForo_Image_Abstract::ORIENTATION_PORTRAIT :
                                $shortSide = $image->getWidth();
                                $centerY = round($image->getHeight() / 2);
                                $cropY = $centerY - round($shortSide / 2);
                                $cropPoint['x'] = 0;
                                $cropPoint['y'] = ($cropY > 0) ? $cropY : 0;
                                unset($centerY, $cropY);
                                break;

                            default :
                                $shortSide = $image->getWidth();
                                $cropPoint['x'] = $cropPoint['y'] = 0;
                                break;
                        }

                        $image->crop($cropPoint['x'], $cropPoint['y'], $shortSide, $shortSide);

                        //					if ($image->thumbnail(XenForo_Application::get('options')->XfRu_UA_thumbDimensions))
                        //					{
                        //						$image->output(IMAGETYPE_JPEG, $thumbFile);
                        //					} else {
                        ////						copy($uploadedImage->getTempFile(), $thumbFile); // no resize necessary, use the original
                        //					}

                        // Always save thumbnail
                        $image->thumbnail(XenForo_Application::get('options')->XfRu_UA_thumbDimensions);
                        $image->output(IMAGETYPE_JPEG, $thumbFile);

                        $dimensions['thumbnail_width'] = $image->getWidth();
                        $dimensions['thumbnail_height'] = $image->getHeight();

                        unset($image);
                    }
                } else {
                    $logMessage .= " - Error: not an image\n";
                    fwrite($logHandler, $logMessage);
                }
			} else {
                $logMessage .= " - Error: data file not exists\n";
                fwrite($logHandler, $logMessage);
            }

			/* @var $imageDataDw XfRu_UserAlbums_DataWriter_ImageData */
			$imageDataDw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_ImageData', XenForo_DataWriter::ERROR_SILENT);
			if ($imageDataDw->setExistingData($imageData['data_id']))
			{
				$imageDataDw->bulkSet($dimensions);
				$imageDataDw->save();
			}
		}

		XenForo_Db::commit();

        fclose($logHandler);

		$detailedMessage = XenForo_Locale::numberFormat($position);

		return $position;
	}
}