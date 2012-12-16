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
			div.message.prompt {
				background-color: #fff;
			}
			div.message h1 {
				font-size: 22px;
				font-weight: bold;
			}
			div.message h2.error {
				font-size: 48px;
				font-weight: bold;
				color: #600;
				margin: 0px 0px 10px 0px;
				padding: 0px;
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


			button {
				font-size: 20px;
				text-shadow: 0px -1px 1px white;
				padding: 0px;
				width: 200px;
				border: 1px 
				background: #eee;
				background: -moz-linear-gradient(top, #eee 0%, #ccc 50%); /* FF3.6+ */
				background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#eee), color-stop(50%,#ccc)); /* Chrome,Safari4+ */
				background: -webkit-linear-gradient(top, #eee 0%,#ccc 50%); /* Chrome10+,Safari5.1+ */
				background: -o-linear-gradient(top, #eee 0%,#ccc 50%); /* Opera 11.10+ */
				background: -ms-linear-gradient(top, #eee 0%,#ccc 50%); /* IE10+ */
				background: linear-gradient(top, #eee 0%,#ccc 50%); /* W3C */
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#eee', endColorstr='#ccc',GradientType=0 ); /* IE6-9 */
				border: 1px solid #ccc;
				border-radius: 5px;
				-moz-border-radius: 5px;
				-webkit-border-radius: 5px;
				cursor: pointer;
			}
			button:hover {
				background: #ccc;
				
			}

			button div {
				border-top: 1px solid #fff;
				padding: 7px 0px;
				width: 200px;
				border-radius: 5px;
				-moz-border-radius: 5px;
				-webkit-border-radius: 5px;
			}
			button:hover div {
				border-top: 1px solid #bbb;
			}

			label {
				display: block;
				color: gray;
				margin-bottom: 10px;
			}
			input { 
				display: block;
				padding: 5px;
				font-size: 24px;
				margin-bottom: 10px;
				border: 1px solid #bbb;
				border-radius: 5px;
				-moz-border-radius: 5px;
				-webkit-border-radius: 5px;
			}

		</style>
	</head>
	<body>
		
		<div class="message <?=isset($message_type) ? $message_type : ''?>">
			<?=$main_content?>
		</div>
		

	</body>
</html>
