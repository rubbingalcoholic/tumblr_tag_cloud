<h1>Your app is in the wrong place.</h1>
<p>
	It looks like you have the <em>index.php</em> file at the base of the application in your webroot
	(meaning that all application subdirectories, such as <em>/controllers</em> and <em>/includes</em> are inside your public HTML
	directory and potentially accessible via the web). This isn't a good idea.
</p>
<p>
	You should set the app up so that your server's public HTML directory is serving the contents of this app's <em>/webroot</em>
	directory and all of the framework and application files exist in the parent directory of your server's public HTML directory.
	This puts them "out of reach" from the web.
</p>