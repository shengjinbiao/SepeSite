<?exit?>
<!--{if empty($iarr)}-->
<p>暂时没有投票</p>
<!--{else}-->
<!--{loop $iarr $value}-->
<form id="voteform" action="#action/poll/php/1#" method="post" target="_blank">
<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
<p class="title">$value[subject]</p>
<p>$value[message]</p>
<ul>

<!--{if $value[ismulti]}-->
<!--{loop $value[options] $okey $options}-->
<li><label for="votekey-$okey"><input type="checkbox" id="votekey-$okey" name="votekey[]" value="$okey" />$options[name]</label></li>
<!--{/loop}-->
<!--{else}-->
<!--{loop $value[options] $okey $options}-->
<li><label for="votekey-$okey"><input type="radio" id="votekey-$okey" name="votekey[]" value="$okey" />$options[name]</label></li>
<!--{/loop}-->
<!--{/if}-->

</ul>

<p>
<a href="#action/poll/pollid/$value[pollid]#" class="showresult" target="_blank">查看结果</a>
<button id="dovote" name="pollbtn" type="submit" value="true">投票</button>
</p>
<input type="hidden" name="pollid" value="$value[pollid]" />
<input type="hidden" name="pollsubmit" value="yes" />
</form>
<!--{/loop}-->
<!--{/if}-->