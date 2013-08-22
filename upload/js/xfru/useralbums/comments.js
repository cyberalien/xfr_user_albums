!function($, window, document, _undefined)
{
	XenForo.XfrUserAlbums =
	{
		addImageComment: function($form)
		{
			$form.bind('AutoValidationComplete', function(e)
			{
				if (e.ajaxData.templateHtml)
				{
					e.preventDefault();

					new XenForo.ExtLoader(e.ajaxData, function()
					{
						var insertMethod = e.ajaxData.commentsOrder == 'newest' ? 'prependTo' : 'appendTo';
						$(e.ajaxData.templateHtml).xfInsert(insertMethod, '#NewImageComments');
					});

					$form.find('textarea[name=message]').val('');
					$form.find('input[name=imageCommentDate]').val(e.ajaxData.imageCommentDate);
					$form.find('input:submit').removeAttr('disabled').removeClass('disabled');
				}
			});
		}
	};

	XenForo.register('form.ImageCommentForm', 'XenForo.XfrUserAlbums.addImageComment');
}
(jQuery, this, document);