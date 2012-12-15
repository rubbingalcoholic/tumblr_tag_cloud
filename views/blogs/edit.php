<form name="percival" action="/blogs/edit<?=$id ? '/'.$id : ''?>" method="post">
	<input type="hidden" name="posted" value="yes"/>
	<div class="form_entry clear">
		<label for="domain">
			Blog domain:
			<span class="hint">(don't use HTTP://)</span>
		</label>
		<input type="text" name="domain" id="domain" value="<?=$domain?>" tabindex="1"/>
		<? if (!empty($settings)) { ?>
			<hr/>
			<label for="max_tag_size_percent">
				Max scale percent:
				<span class="hint">(the size difference between the biggest and smallest tags)</span>
			</label>
			<input type="text" name="settings[max_tag_size_percent]" id="max_tag_size_percent" value="<?=$settings['max_tag_size_percent']?>" tabindex="2"/>
			<hr/>
			<label for="number_of_results">
				Count tags from <em>N</em> posts:
				<span class="hint">(the more posts, the slower it is to initially generate)</span>
			</label>
			<input type="text" name="settings[number_of_results]" id="number_of_results" value="<?=$settings['number_of_results']?>" tabindex="3"/>
			<hr/>
			<label for="expire_timeout">
				Cache expire minutes:
				<span class="hint">(number of minutes to cache results on the server)</span>
			</label>
			<input type="text" name="settings[expire_timeout]" id="expire_timeout" value="<?=$settings['expire_timeout']?>" tabindex="4"/>
			<hr/>
			<label for="apply_css_styles">
				Apply CSS styles:
				<span class="hint">(choose between our handy default styles or none if you want to skin it yourself)</span>
			</label>
			<select name="settings[apply_css_styles]" id="apply_css_styles">
				<option value="default" <? if ($settings['apply_css_styles'] == 'default') { ?>selected="selected"<? } ?>>default</option>
				<option value="none" <? if ($settings['apply_css_styles'] == 'none') { ?>selected="selected"<? } ?>>none</option>
			</select>
			<hr/>
			<label for="text_scaling_math">
				Scaling algorithm:
				<span class="hint">(choose what you think looks better)</span>
			</label>
			<select name="settings[text_scaling_math]" id="text_scaling_math">
				<option value="logarithmic" <? if ($settings['text_scaling_math'] == 'logarithmic') { ?>selected="selected"<? } ?>>logarithmic</option>
				<option value="linear" <? if ($settings['text_scaling_math'] == 'linear') { ?>selected="selected"<? } ?>>linear</option>
			</select>
		<? } ?>
		<hr/>
		<div class="buttons">
			<? if ($id) { ?><a href="/blogs/delete/<?=$id?>" class="delete" onclick="return confirm('Are you sure you wish to delete this blog?')">Delete this Blog</a><? } ?>
			<button type="submit" tabindex="5"><div>Save<? if ($id) { ?> &amp; Refresh<? } ?></div></button>
			<button type="button" onclick="window.location.href='/blogs/manage';"><div>Cancel</div></button>
		</div>
	</div>
	
</form>
<? if ($id) { ?>
	<h2>&raquo; Embed Code:</h2>
	<textarea name="billy" class="embed_code">
&lt;!-- Tumblr Tag Cloud (Rubbing Alcoholic) --&gt;
&lt;div id="tumblr_tag_cloud"&gt;Loading tags...&lt;/div&gt;
&lt;script type="text/javascript" src="http://<?=SITE?>/blogs/js/<?=$domain?>"&gt;&lt;/script&gt;
&lt;!-- End of Tumblr Tag Cloud!! LOL --&gt;
	</textarea>
	<h2>&raquo; Preview:</h2>
	<!-- Tumblr Tag Cloud (Rubbing Alcoholic) -->
	<div id="tumblr_tag_cloud">Loading tags...</div>
	<script type="text/javascript" src="http://<?=SITE?>/blogs/js/<?=$domain?>"></script>
	<!-- End of Tumblr Tag Cloud!! LOL -->
<? } ?>
<script type="text/javascript">
	window.addEvent('domready', function(e) {
		if (!$('domain').value)
		{
			$('domain').focus();
		}
	});
</script>