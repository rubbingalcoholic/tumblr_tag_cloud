<?
	if(strstr($data['template'], 'file:'))
	{
		$template	=	$data['file'];
	}
	else
	{
		$template	=	$data['template'];
	}
?>
<h1>Error loading view</h1>
<p>
	The view <strong><?=$data['template']?></strong> could not be loaded (called in
	<strong><?=$controller?></strong> controller). Please make sure that 
	<strong><?=$data['file']?></strong> exists.
</p>