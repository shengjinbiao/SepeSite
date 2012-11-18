<?exit?>
<!--{loop $iarr $ikey $value}-->
<li>
<!--{if !empty($value['subject'])}--><p><a href="$value[url]" target="_blank" title="$value[subjectall]">$value[subject]</a></p><!--{/if}-->
<!--{if !empty($value['message'])}--><p><a href="$value[url]" target="_blank">$value[message]</a><p><!--{/if}-->
</li>
<!--{/loop}-->