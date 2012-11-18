<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.formhash.php 11762 2009-03-24 05:34:09Z zhaolei $
*/

include_once('./common.php');

$formhash = formhash();
echo <<<END
document.write('<input type="hidden" name="formhash" value="$formhash" />');
END;
?>