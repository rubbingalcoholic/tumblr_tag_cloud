<?
	$controller	=	$this->event->get('_controller');
	$action		=	$this->event->get('_action');
?>
<h1>Missing action</h1>
<p>
	The action <strong>'<?=$action?>'</strong> is not present in controller 
	<strong>'<?=$controller?>'</strong>. Check that 
	<strong><?=realpath(CONTROLLERS) .DIRECTORY_SEPARATOR. $controller?>.php</strong> 
	contains the following:
</p>
<pre>
&lt;?
	class <?=$controller?> extends app_controller
	{
		function <?=$action?>()
		{
		}
	}
?&gt;
</pre>
