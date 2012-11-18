<?exit?>
<!--{loop $iarr $value}-->
<li><a href="$value[url]" title="$value[subjectall]" target="_blank">$value[subject]</a> (<a href="{S_URL}/?$value[uid]" title="$value[spacename]" target="_blank">$value[username]</a>)</li>
<!--{/loop}-->