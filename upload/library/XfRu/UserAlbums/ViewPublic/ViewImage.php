<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: ViewImage.php 144 2011-03-13 21:18:48Z pepelac $ $Date: 2011-03-13 22:18:48 +0100 (Sun, 13 Mar 2011) $ $Revision: 144 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ViewPublic_ViewImage extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		if (!empty($this->_params['image']))
		{
			$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));

		    $this->_params['image']['descriptionHtml'] = new XenForo_BbCode_TextWrapper(
			    $this->_params['image']['description'],
			    $bbCodeParser
		    );
		}
	}

	public function renderJson()
	{
		$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));

		$this->_params['image']['descriptionHtml'] = $bbCodeParser->render($this->_params['image']['description']);

		$output['messagesTemplateHtml']["#image-{$this->_params['image']['image_id']}"] =
			$this->createTemplateObject('xfr_useralbums_image_content', $this->_params)->render();

		$template = $this->createTemplateObject('', array());

		$output['css'] = $template->getRequiredExternals('css');
		$output['js'] = $template->getRequiredExternals('js');

		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
	}
}