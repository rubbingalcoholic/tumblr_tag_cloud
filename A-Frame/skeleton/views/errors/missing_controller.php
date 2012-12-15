<?
	$controller	=	$this->event->get('_controller');
?>
<h1>Missing controller</h1>
<p>
	The controller <strong>'<?=$controller?>'</strong> could not be loaded. Check that
	<strong><?=realpath(CONTROLLERS) .DIRECTORY_SEPARATOR. $controller?>.php</strong> 
	exists, and contains at	least the following:
</p>
<pre>
&lt;?
	class <?=$controller?> extends app_controller
	{
	}
?&gt;
</pre>