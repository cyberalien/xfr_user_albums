<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: ThumbnailSprites.php 296 2011-08-23 09:33:21Z pepelac $ $Date: 2011-08-23 11:33:21 +0200 (Tue, 23 Aug 2011) $ $Revision: 296 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_CacheRebuilder_ThumbnailSprites extends XfRu_UserAlbums_CacheRebuilder_Abstract
{

	/**
	 * Gets a message about the type of content being rebuilt.
	 * Likely depends on phrases existing.
	 *
	 * @return string|XenForo_Phrase
	 */
	public function getRebuildMessage()
	{
		return new XenForo_Phrase('xfr_useralbums_thumbnail_sprites');
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

		/* @var $albumsModel XfRu_UserAlbums_Model_Albums */
		$albumsModel = XenForo_Model::create('XfRu_UserAlbums_Model_Albums');

		/* @var $imagesModel XfRu_UserAlbums_Model_Images */
		$imagesModel = XenForo_Model::create('XfRu_UserAlbums_Model_Images');

		$albums = $albumsModel->getAlbumsInRange($position, $options['batch']);

		if (sizeof($albums) == 0)
		{
			return true;
		}

		XenForo_Db::beginTransaction();

		foreach ($albums AS $album)
		{
			$position = $album['album_id'];

			if ($album['image_count'] < 2)
			{
				continue;
			}

			if (!$album['sprite_hash'])
			{
				$album['sprite_hash'] = md5($album['album_id'].$album['user_id'].$album['createdate']);
				/* @var $albumDw XfRu_UserAlbums_DataWriter_Album */
				$albumDw = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Album', XenForo_DataWriter::ERROR_SILENT);
				if ($albumDw->setExistingData($album['album_id']))
				{
					$albumDw->set('sprite_hash', $album['sprite_hash']);
					$albumDw->save();
				}

			}

			$latestImages = $imagesModel->getLatestImagesInAlbum($album['album_id'], 10);
			/* @var $spritesModel XfRu_UserAlbums_Model_Sprites */
			$spritesModel = XenForo_Model::create('XfRu_UserAlbums_Model_Sprites');

			$spritesModel->assembleSprites($latestImages, $album);
		}

		XenForo_Db::commit();

		$detailedMessage = XenForo_Locale::numberFormat($position);

		return $position;
	}
}