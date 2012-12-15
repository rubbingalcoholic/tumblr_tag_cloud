<ul class="blog_list">
	<? for ($i=0, $c=count($blogs); $i<$c; $i++) { ?>
		<li><a href="/blogs/edit/<?=$blogs[$i]['id']?>"><?=$blogs[$i]['domain']?></a></li>
	<? } ?>
</ul>
<button onclick="window.location.href='/blogs/edit';"><div>Define a New Blog</div></button>