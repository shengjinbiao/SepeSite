<?exit?>
<!--{if empty($_SGLOBAL['inajax'])}-->
<div id="footer">
	<div id="footer_top">
		<p class="good_link">
			<a href="{S_URL}/index.php">$_SCONFIG[sitename]</a> | 
			<a href="#action/site/type/map#">站点地图</a> | 
			<a href="#action/site/type/link#">友情链接</a> | 
			<a href="mailto:$_SCONFIG[adminemail]">联系我们</a>
		</p>
		<form action="{S_URL}/batch.search.php" method="post">
		<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
		<input type="hidden" name="searchname" id="searchname" value="subject" />
		<p class="footer_search">
			<select name="searchtxt" id="searchtxt" onchange="changetype();">
				<option value="标题">标题</option>
				<option value="内容">内容</option>
				<option value="作者">作者</option>
			</select>
			<input class="input_tx" type="text" value="" name="searchkey" size="30"/>
			<input class="input_search" type="submit" value="搜索" name="searchbtn"/>
		</p>
		</form>
	</div>
	<div class="copyright">
		<p id="copyright">
		Powered by <a href="http://www.supesite.com" target="_blank">SupeSite</a> <em title="<?=S_RELEASE?>"><?=S_VER?></em> &copy; 2001-2009 <a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>
		</p>
		<p>
		<!--{eval debuginfo();}-->
		</p>
	</div>
</div><!--footer end-->
<script language="javascript">
function changetype() {
	if($('searchtxt').value == '标题') {
		$('searchname').value = 'subject';
	}else if($('searchtxt').value == '内容') {
		$('searchname').value = 'message';
	}else if($('searchtxt').value == '作者') {
		$('searchname').value = 'author';
	}
}
</script>
</body>
</html>
<!--{/if}-->