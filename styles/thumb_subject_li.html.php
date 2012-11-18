<?exit?>
<!--{loop $iarr $ikey $value}-->
<li>
	<a href="$value[url]" target="_blank"><img src="$value[image]" alt="$value[subjectall]" width="110" height="120" border="0" /></a>
	<p><a href="$value[url]" title="$value[subjectall]" target="_blank">$value[subject]</a></p>
</li>
<!--{/loop}-->