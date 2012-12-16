<form name="wendy" method="post" action="/admin/login?r=<?=$redirect_to?>">
	<input type="hidden" name="posted" value="yes"/>
	<? if ($access_denied) { ?>
		<h2 class="error">ACCESS DENIED LOL</h2>
	<? } else { ?>
		<label for="username"><strong>Please log in:</strong></label>
	<? } ?>
	<input type="text" placeholder="username" name="username" tabindex="1"/>
	<input type="text" placeholder="password" name="password" tabindex="2"/>
	<button tabindex="3"><div>Submit</div></button>
</form>