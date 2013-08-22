<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Counters.php 334 2011-11-17 14:40:38Z pepelac $ $Date: 2011-11-17 15:40:38 +0100 (Thu, 17 Nov 2011) $ $Revision: 334 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_Model_Counters extends XenForo_Model
{
	public function getStatistics($userId = null)
	{
		$stats = array();

		if ($userId)
		{
			$stats['images'] = $this->_getDb()->fetchOne('
				SELECT COUNT(image.image_id)
				FROM xfr_useralbum_image AS image
				LEFT JOIN xfr_useralbum_image_data AS data ON data.data_id = image.data_id
				WHERE unassociated = 0
					AND data.user_id = ?
			', array('user_id' => $userId));

		} else {
			$stats['images'] = $this->_getDb()->fetchOne('
				SELECT COUNT(*)
				FROM xfr_useralbum_image
				WHERE unassociated = 0
			');
		}

		$stats['comments'] = $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM xfr_useralbum_image_comment
		');

		$members = $this->_getDb()->fetchAll('
			SELECT DISTINCT user_id
			FROM xfr_useralbum
		');

		$stats['members'] = count($members);

		return $stats;
	}

    public function rebuildAlbumsCache($fetchOptions = array())
    {
        if (empty($fetchOptions))
        {
            $fetchOptions = array(
                'join' => XfRu_UserAlbums_Model_Albums::FETCH_USER,
                'userId' => XenForo_Visitor::getUserId(),
                'showEmptyAlbums' => XenForo_Application::get('options')->XfRu_UA_emptyAlbumsAtMainList
            );
        }

        $data = $this->getAlbumsDataToCache($fetchOptions);
        $this->_getDataRegistryModel()->set('userAlbums', $data);
        return $data;
    }

    public function getAlbumsDataToCache(array $fetchOptions)
    {
        /** @var XfRu_UserAlbums_Model_Albums $albumsModel  */
        $albumsModel = $this->getModelFromCache('XfRu_UserAlbums_Model_Albums');

        $data = $this->getStatistics();
        $data['totalAlbums'] = $albumsModel->countAlbums(null, $fetchOptions);
        $data['activeMembers'] = $albumsModel->getActiveUsers();
        return $data;
    }
}