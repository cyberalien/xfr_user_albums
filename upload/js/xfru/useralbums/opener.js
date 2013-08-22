!function($, window, document, _undefined)
{
	XenForo.XfrImageboxOpener = function($link)
	{
		$link.click(function(e){
			e.preventDefault();
			window.location = XenForo.canonicalizeUrl($link.data('href'));
		});
	};

	XenForo.register('a.ImageboxOpener', 'XenForo.XfrImageboxOpener');
}
(jQuery, this, document);