<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Album.php 301 2011-08-23 21:09:28Z pepelac $ $Date: 2011-08-23 23:09:28 +0200 (Tue, 23 Aug 2011) $ $Revision: 301 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_Helper_Album
{
	public function makeDirs()
	{
		$dataPath = XenForo_Helper_File::getInternalDataPath();
		$imagesPath = $dataPath . XfRu_UserAlbums_Model_Images::IMAGES_PATH;

		$rootDir = XenForo_Application::getInstance()->getRootDir();
		$thumbsPath = $rootDir . XfRu_UserAlbums_Model_Images::THUMBS_PATH;

		if (!is_dir($imagesPath) && is_writable(XenForo_Helper_File::getInternalDataPath()))
		{
			$folders = explode('/', trim(XfRu_UserAlbums_Model_Images::IMAGES_PATH, '/'));
			$this->checkAndCreateFolders($folders, $dataPath);
		}

		if (!is_dir($thumbsPath) && is_writable(XenForo_Application::getInstance()->getRootDir().'/data'))
		{
			$folders = explode('/', trim(XfRu_UserAlbums_Model_Images::THUMBS_PATH, '/'));
			$this->checkAndCreateFolders($folders, $rootDir);
		}
	}

	public function makeSpriteDir()
	{
		$rootDir = XenForo_Application::getInstance()->getRootDir();
		$spritesPath = $rootDir . XfRu_UserAlbums_Model_Albums::SPRITES_PATH;

		if (!is_dir($spritesPath) && is_writable(XenForo_Application::getInstance()->getRootDir().'/data'))
		{
			$folders = explode('/', trim(XfRu_UserAlbums_Model_Albums::SPRITES_PATH, '/'));
			$this->checkAndCreateFolders($folders, $rootDir);
		}
	}

	private function checkAndCreateFolders(array $folders, $dataPath)
	{
		foreach ($folders as $folder)
		{
			$dataPath .= DIRECTORY_SEPARATOR . $folder;

			if (!is_dir($dataPath))
			{
				@mkdir($dataPath);
			}

			if (!is_file($dataPath . DIRECTORY_SEPARATOR . 'index.html'))
			{
				$fh = @fopen($dataPath.'/index.html', 'w');
				@fwrite($fh, ' ');
				@fclose($fh);
			}
		}
	}

	public function getTemplateContent($type, &$template, $params)
	{
		return call_user_func_array(array($this, $type), array($template, $params));
	}



	private function getUserAlbumsMemberProfileTab(&$template, $params)
	{
		$viewParams['requestUri'] = $params['requestUri'];
		$tpl = new XenForo_Template_Public('xfr_useralbums_member_profile_tab', $viewParams);
		$html = $tpl->render();
		return $html;
	}

	private function getUserAlbumsMemberProfileTabContent(&$template, $params)
	{
		$viewParams['user'] = $params['user'];
		$tpl = new XenForo_Template_Public('xfr_useralbums_member_profile_tab_content', $viewParams);
		$html = $tpl->render();
		return $html;
	}


}