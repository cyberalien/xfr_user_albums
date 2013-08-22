<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: ImageComment.php 185 2011-04-03 13:34:30Z pepelac $ $Date: 2011-04-03 15:34:30 +0200 (Sun, 03 Apr 2011) $ $Revision: 185 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ViewPublic_ImageComment extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		$output = $this->_renderer->getDefaultOutputArray(get_class($this), $this->_params, $this->_templateName);

		$output['imageCommentDate'] = $this->_params['imageCommentDate'];
		$output['commentsOrder'] = XenForo_Application::get('options')->XfRu_UA_commentsOrder;

		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
	}
}