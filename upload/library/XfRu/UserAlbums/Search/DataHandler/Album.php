<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Album.php 298 2011-08-23 12:50:58Z pepelac $ $Date: 2011-08-23 14:50:58 +0200 (Tue, 23 Aug 2011) $ $Revision: 298 $
 * @author Pepelac
 *
 */
class XfRu_UserAlbums_Search_DataHandler_Album extends XenForo_Search_DataHandler_Abstract
{
	private $albumsModel;
	private $imagesModel;

	/**
	 * Deletes one or more records from the index. Wrapper around {@link _deleteFromIndex()}.
	 *
	 * @param XenForo_Search_Indexer $indexer Object that will will manipulate the index
	 * @param array $dataList A list of data to remove. Each element is an array of the data from one record.
	 */
	protected function _deleteFromIndex(XenForo_Search_Indexer $indexer, array $dataList) {
		$albumIds = array();

		foreach ($dataList AS $data)
		{
			$albumIds[] = $data['album_id'];
		}

		$indexer->deleteFromIndex('xfr_useralbum', $albumIds);
	}

	/**
	 * Inserts a new record or replaces an existing record in the index.
	 *
	 * @param XenForo_Search_Indexer $indexer Object that will will manipulate the index
	 * @param array $data Data that needs to be updated
	 * @param array|null $parentData Data about the parent info (eg, for a post, the parent thread)
	 */
	protected function _insertIntoIndex(XenForo_Search_Indexer $indexer, array $data, array $parentData = null) {
		$indexer->insertIntoIndex('xfr_useralbum',
			$data['album_id'], $data['title'], $data['description'], $data['createdate'], $data['user_id']
		);
	}

	/**
	 * Updates a record in the index.
	 *
	 * @param XenForo_Search_Indexer $indexer Object that will will manipulate the index
	 * @param array $data Data that needs to be updated
	 * @param array $fieldUpdates Key-value fields to update
	 */
	protected function _updateIndex(XenForo_Search_Indexer $indexer, array $data, array $fieldUpdates) {
		$indexer->updateIndex('xfr_useralbum', $data['album_id'], $fieldUpdates);
	}

	/**
	 * Determines if the specific search result (data from getDataForResults()) can be viewed
	 * by the given user. The user and combination ID will be the same as given to getDataForResults().
	 *
	 * @param array $result Data for a result
	 * @param array $viewingUser Information about the viewing user (keys: user_id, permission_combination_id, permissions)
	 *
	 * @return boolean
	 */
	public function canViewResult(array $result, array $viewingUser) {
		return $this->getAlbumsModel()->isAlbumViewable($result);
	}

	/**
	 * Gets the additional, type-specific data for a list of results. If any of
	 * the given IDs are not returned from this, they will be removed from the results.
	 *
	 * @param array $ids List of IDs of this content type.
	 * @param array $viewingUser Information about the viewing user (keys: user_id, permission_combination_id, permissions)
	 * @param array $resultsGrouped List of all results grouped by content type
	 *
	 * @return array Format: [id] => data, IDs not returned will be removed from results
	 */
	public function getDataForResults(array $ids, array $viewingUser, array $resultsGrouped) {
		$albums = $this->getAlbumsModel()->getAlbumsByIds($ids);

		foreach ($albums as &$album)
		{
			if ($album['thumbnail_width'])
			{
				$data = array(
					'data_id' => $album['data_id'],
					'file_hash' => $album['file_hash'],
				);
				$album['thumbnailUrl'] = $this->getImagesModel()->getImageThumbnailUrl($data);
			} else {
				$album['thumbnailUrl'] = null;
			}
		}

		return $albums;
	}

	/**
	 * Gets the date of the result (from the result's content).
	 *
	 * @param array $result
	 *
	 * @return integer
	 */
	public function getResultDate(array $result) {
		return $result['createdate'];
	}

	/**
	 * Get the content types that will be searched, when doing a type-specific search for this type.
	 * This may be multiple types (for example, thread and post for post searches).
	 *
	 * @return array
	 */
	public function getSearchContentTypes() {
		return array('xfr_useralbum');
	}

	/**
	 * Indexes the specified content IDs.
	 *
	 * @param XenForo_Search_Indexer $indexer
	 * @param array $contentIds
	 *
	 * @return array List of content IDs indexed
	 */
	public function quickIndex(XenForo_Search_Indexer $indexer, array $contentIds) {
		$albums = $this->getAlbumsModel()->getAlbumsByIds($contentIds);
		$albumIds = array();

		foreach ($albums AS $albumId => $album)
		{
			$albumIds[] = $albumId;
			$this->insertIntoIndex($indexer, $album);
		}

		return $albumIds;
	}

	/**
	 * Rebuilds the index in bulk.
	 *
	 * @param XenForo_Search_Indexer $indexer Object that will will manipulate the index
	 * @param integer $lastId The last ID that was processed. Should continue with the IDs above this.
	 * @param integer $batchSize Number of records to process at once
	 *
	 * @return integer|false The last ID that was processed or false if none were processed
	 */
	public function rebuildIndex(XenForo_Search_Indexer $indexer, $lastId, $batchSize) {
		$albumIds = $this->getAlbumsModel()->getAlbumIdsInRange($lastId, $batchSize);

		if (!$albumIds)
		{
			return false;
		}

		$this->quickIndex($indexer, $albumIds);

		return max($albumIds);
	}

	/**
	 * Render a result (as HTML).
	 *
	 * @param XenForo_View $view
	 * @param array $result Data from result
	 * @param array $search The search that was performed
	 *
	 * @return XenForo_Template_Abstract|string
	 */
	public function renderResult(XenForo_View $view, array $result, array $search) {
		return $view->createTemplateObject('xfr_useralbums_search_result', array(
			'album'		=> $result,
			'search'	=> $search
		));
	}


	/**
	 * @return XfRu_UserAlbums_Model_Albums
	 */
	private function getAlbumsModel()
	{
		if (!$this->albumsModel)
		{
			$this->albumsModel = XenForo_Model::create('XfRu_UserAlbums_Model_Albums');
		}
		return $this->albumsModel;
	}

	/**
	 * @return XfRu_UserAlbums_Model_Images
	 */
	private function getImagesModel()
	{
		if (!$this->imagesModel)
		{
			$this->imagesModel = XenForo_Model::create('XfRu_UserAlbums_Model_Images');
		}
		return $this->imagesModel;
	}
}