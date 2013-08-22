<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Template.php 334 2011-11-17 14:40:38Z pepelac $ $Date: 2011-11-17 15:40:38 +0100 (Thu, 17 Nov 2011) $ $Revision: 334 $
 * @author Pepelac
 *
 */
class XfRu_UserAlbums_EventListener_Template
{
	public static function templateCreate(&$templateName, &$params, $template)
	{
		switch($templateName)
		{
			case 'member_view' :
				$template->preloadTemplate('xfr_useralbums_member_profile_tab');
				$template->preloadTemplate('xfr_useralbums_member_profile_tab_content');
				break;

			case 'forum_list' :
				$template->preloadTemplate('xfr_useralbums_sidebar_latest_albums');
				$template->preloadTemplate('xfr_useralbums_sidebar_random_albums');
				$template->preloadTemplate('xfr_useralbums_sidebar_popular_albums');
				$template->preloadTemplate('xfr_useralbums_forum_list_latest_images');
				$template->preloadTemplate('xfr_useralbums_forum_list_random_images');
				break;

			case 'member_card':
				$template->preloadTemplate('xfr_useralbums_member_card_link');
				break;

			case 'account_alert_preferences':
				$template->preloadTemplate('xfr_useralbums_alert_preferences');
				break;
		}
	}

	public static function templateHook($name, &$contents, array $params, XenForo_Template_Abstract $template)
	{
		$templateParams = $template->getParams();
		$canViewAlbums = $templateParams['visitor']['permissions']['xfrUserAlbumsPermissions']['xfr_UA_ViewAlbums'];

		if (!$canViewAlbums)
		{
			return;
		}

		/* @var XenForo_Options $options */
		$options = XenForo_Application::get('options');
		/* @var XfRu_UserAlbums_Model_Albums $albumsModel */
		$albumsModel = XenForo_Model::create('XfRu_UserAlbums_Model_Albums');
		/* @var XfRu_UserAlbums_Model_Images $imagesModel */
		$imagesModel = XenForo_Model::create('XfRu_UserAlbums_Model_Images');

		$latestImages = array();
		$randomImages = array();

		switch($name)
		{
			case 'member_view_tabs_heading' :
				if ($options->XfRu_UA_displayLocations['profile_tab'])
				{
					$viewParams = array(
						'user' => $params['user']
					);
					$contents .= $template->create('xfr_useralbums_member_profile_tab', $viewParams);
				}
				break;

			case 'member_view_tabs_content' :
				if ($options->XfRu_UA_displayLocations['profile_tab'])
				{
					$fetchOptions = array(
						'join' => XfRu_UserAlbums_Model_Albums::FETCH_USER,
						'limit' => $options->XfRu_UA_albumsPerPage,
						'order' => array(
							'createdate' => 'DESC'
						)
					);

					$albums = $albumsModel->getUserAlbums($templateParams['user']['user_id'], $fetchOptions);

					foreach ($albums as &$album)
					{
						if ($album['thumbnail_width'])
						{
							$data = array(
								'data_id' => $album['data_id'],
								'file_hash' => $album['file_hash'],
							);
							$album['thumbnailUrl'] = $imagesModel->getImageThumbnailUrl($data);
						} else {
							$album['thumbnailUrl'] = null;
						}
					}

//					Zend_Debug::dump($albums); die();

					$viewParams = array(
						'albums' => $albums,
						'showMoreLink' => count($albums) == $options->XfRu_UA_albumsPerPage,
						'user' => $templateParams['user']
					);

					unset($albums);

					$contents .= $template->create('xfr_useralbums_member_profile_tab_content', $viewParams);
				}

				break;

			case 'forum_list_sidebar':
				if ($options->XfRu_UA_displayLocations['forum_sidebar_latest_albums'])
				{
					$viewParams = array(
						'latestAlbums' => $albumsModel->getLatestAlbums()
					);
					
					$tpl = $template->create('xfr_useralbums_sidebar_latest_albums', $viewParams);

					$needle = '<!-- block: forum_stats -->';
					$contents = str_replace($needle, $tpl."\n".$needle, $contents);
				}

				if ($options->XfRu_UA_displayLocations['forum_sidebar_random_albums'])
				{
					$viewParams = array(
						'randomAlbums' => $albumsModel->getRandomAlbums()
					);

					$tpl = $template->create('xfr_useralbums_sidebar_random_albums', $viewParams);

					$needle = '<!-- block: forum_stats -->';
					$contents = str_replace($needle, $tpl."\n".$needle, $contents);
				}

                if ($options->XfRu_UA_displayLocations['forum_sidebar_popular_albums'])
                {
                    $viewParams = array(
                        'popularAlbums' => $albumsModel->getPopularAlbums()
                    );

                    $tpl = $template->create('xfr_useralbums_sidebar_popular_albums', $viewParams);

                    $needle = '<!-- block: forum_stats -->';
                    $contents = str_replace($needle, $tpl."\n".$needle, $contents);
                }
				break;

			case 'forum_list_nodes' :
				if ($options->XfRu_UA_displayLocations['forum_list_latest_images_before'])
				{
					$latestImages = $imagesModel->getLatestImages();
					$viewParams = array(
						'images' => $latestImages
					);

					$tpl = $template->create('xfr_useralbums_forum_list_latest_images', $viewParams);
					$contents = $tpl . $contents;
				}

				if ($options->XfRu_UA_displayLocations['forum_list_latest_images_below'])
				{
					$viewParams = array(
						'images' => (!empty($latestImages)) ? $latestImages : $imagesModel->getLatestImages()
					);

					$tpl = $template->create('xfr_useralbums_forum_list_latest_images', $viewParams);
					$contents .= $tpl;
				}

				if ($options->XfRu_UA_displayLocations['forum_list_random_images_before'])
				{
					$randomImages = $imagesModel->getRandomImages();
					$viewParams = array(
						'images' => $randomImages
					);

					$tpl = $template->create('xfr_useralbums_forum_list_random_images', $viewParams);
					$contents = $tpl . $contents;
				}

				if ($options->XfRu_UA_displayLocations['forum_list_random_images_below'])
				{
					$viewParams = array(
						'images' => (!empty($randomImages)) ? $randomImages : $imagesModel->getRandomImages()
					);

					$tpl = $template->create('xfr_useralbums_forum_list_random_images', $viewParams);
					$contents .= $tpl;
				}
				break;

			case 'member_card_stats' :
				if ($options->XfRu_UA_displayLocations['member_card'])
				{
					$viewParams = array(
						'albumsLink' => XenForo_Link::buildPublicLink('useralbums/list', $templateParams['user']),
						'albumsCount' => $albumsModel->getUserVisibleAlbumsCount($templateParams['user']['user_id'])
					);

					$contents .= $template->create('xfr_useralbums_member_card_link', $viewParams);
				}
				break;

			case 'account_alerts_extra':
					$viewParams = array(
						'alertOptOuts' => $templateParams['alertOptOuts']
					);
					$contents .= $template->create('xfr_useralbums_alert_preferences', $viewParams);
					break;

		}

	}

}