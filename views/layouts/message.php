<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title><?=PROJECT_NAME?> : <?=isset($message_type) ? ucfirst($message_type) : 'ERROR'?></title>
		<style type="text/css">
			body {
				margin: 0px;
				padding: 25px;
				background-color: #eee;
			}
			div.message {
				width: 600px;
				background-color: #fcc;
				padding: 20px 30px;
				font-family: arial;
				font-size: 17px;
				border-radius: 10px;
				-moz-border-radius: 10px;
				-webkit-border-radius: 10px;
				-o-border-radius: 10px;
			}
			div.message.success {
				background-color: #cec;
			}
			div.message h1 {
				font-size: 22px;
				font-weight: bold;
			}
			div.message p {
				line-height: 24px;
			}
			div.message p.error {
				color: red;
			}
			div.message input[type=submit] {
				border: 1px solid gray;
				background-color: #eee;
				padding: 10px;
				font-size: 26px;
				border-radius: 10px;
				-moz-border-radius: 10px;
				-webkit-border-radius: 10px;
				-o-border-radius: 10px;
				cursor: pointer;
			}
			div.message input[type=submit]:hover {
				background-color: #ddd;
			}
		</style>
	</head>
	<body>
		
		<div class="message <?=isset($message_type) ? $message_type : ''?>">
			<?=$main_content?>
		</div>
		

	</body>
</html>
