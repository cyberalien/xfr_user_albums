<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: DataRebuild.php 296 2011-08-23 09:33:21Z pepelac $ $Date: 2011-08-23 11:33:21 +0200 (Tue, 23 Aug 2011) $ $Revision: 296 $
 * @author Pepelac
 *
 */
class XfRu_UserAlbums_ViewAdmin_Tools_DataRebuild extends XenForo_ViewAdmin_Base
{
	public function renderJson()
	{
		$output = $this->_renderer->getDefaultOutputArray(get_class($this), $this->_params, $this->_templateName);
		$output['elements'] = $this->_params['elements'];
		$output['rebuildMessage'] = $this->_params['rebuildMessage'];
		$output['detailedMessage'] = $this->_params['detailedMessage'];
		$output['showExitLink'] = $this->_params['showExitLink'];

		return $output;
	}
}