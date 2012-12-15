<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title><?=!empty($site_title) ? $site_title . ' | ' : ''?><?=PROJECT_NAME?></title>
		<script src="/js/mootools-1.4.1.js"></script>
		<link rel="stylesheet" type="text/css" href="/css/template.css" />
	</head>
	<body>
		<div id="main">
			<h1 class="logo"><a href="/"><?=PROJECT_NAME?></a></h1>
			<?
			if($msg_object->num_messages > 0)
			{
				$has_errors = '';
				if ($msg_object->has_errors)
				{
					$has_errors = 'has_errors';
				}
				echo '<div class="clear '.$has_errors.'" id="message_list">'. $msg_object->dump() .'</div>';
			}
			?>
			<? if (isset($page_title)) { ?>
				<h2>&raquo;	<?=$page_title?></h2>
			<? } ?>
<?=$main_content?>
			<div class="footer">
				Tumblr Tag Cloud Copyright &COPY; <a href="http://blog.rubbingalcoholic.com">Rubbing Alcoholic</a>. Distributed under the MIT License.
			</div>
		</div>
	</body>
</html>
