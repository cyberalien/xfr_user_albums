<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Tools.php 296 2011-08-23 09:33:21Z pepelac $ $Date: 2011-08-23 11:33:21 +0200 (Tue, 23 Aug 2011) $ $Revision: 296 $
 * @author Pepelac
 *
 */
class XfRu_UserAlbums_ControllerAdmin_Tools extends XenForo_ControllerAdmin_Abstract
{
	public function actionRebuild()
	{
		return $this->responseView(
			'XfRu_UserAlbums_ViewAdmin_Tools_Rebuild',
			'xfr_acp_useralbums_tools_rebuild'
		);
	}

	public function actionDataRebuild()
	{
		$input = $this->_input->filter(array(
			'caches' => XenForo_Input::JSON_ARRAY,
			'position' => XenForo_Input::UINT,

			'cache' => XenForo_Input::STRING,
			'options' => XenForo_Input::ARRAY_SIMPLE,

			'process' => XenForo_Input::UINT
		));

		if ($input['cache'])
		{
			$input['caches'][] = array($input['cache'], $input['options']);
		}


		$doRebuild = ($this->_request->isPost() && $input['process']);

		if ($doRebuild)
		{
			$redirect = $this->getDynamicRedirect(false, false);

		} else {
			$redirect = $this->getDynamicRedirect(false);
		}

		$output = $this->getHelper('XfRu_UserAlbums_ControllerHelper_DataRebuild')->rebuildData(
			$input, $redirect, XenForo_Link::buildAdminLink('useralbums-tools/data-rebuild'), $doRebuild
		);

		if ($output instanceof XenForo_ControllerResponse_Abstract)
		{

			return $output;
		} else {
			$viewParams = $output;

			$containerParams = array(
				'containerTemplate' => 'PAGE_CONTAINER_SIMPLE'
			);

			return $this->responseView('XfRu_UserAlbums_ViewAdmin_Tools_DataRebuild', 'xfr_acp_useralbums_tools_data_rebuild', $viewParams, $containerParams);
		}
	}
}