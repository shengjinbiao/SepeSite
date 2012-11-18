<?exit?>
<!--{loop $iarr[text] $ikey $value}-->
<li>
<!--{if !empty($value['subject'])}--><p><a href="$value[url]" target="_blank" title="$value[subjectall]">$value[subject]</a></p><!--{/if}-->
<!--{if !empty($value['message'])}--><p><a href="$value[url]" target="_blank">$value[message]</a><p><!--{/if}-->

<!--{if !empty($iarr[$value[pid]])}-->
<!--{loop $iarr[$value[pid]] $attach}-->
<p><a href="{B_URL}/attachment.php?aid=$attach[aid]" target="_blank">$attach[filename]</a></p>
<!--{/loop}-->
<!--{/if}-->

</li>
<!--{/loop}-->