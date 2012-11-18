<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_customfields.php 13411 2009-10-22 03:13:01Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//х╗оч
if(!checkperm('managecustomfields')) {
	showmessage('no_authority_management_operation');
}

$listarr = array();
$thevalue = array();

//[POST]
if(submitcheck('listsubmit')) {

	//LIST UPDATE
	if(!empty($_POST['displayorder'])) {
		if(is_array($_POST['displayorder'])) {
			foreach ($_POST['displayorder'] as $customfieldkey => $customfielddisplayordervalue) {
				$wheresqlarr = array('customfieldid' => $customfieldkey, 'uid' => $_SGLOBAL['supe_uid']);
				if(!empty($_POST['delete'][$customfieldkey])) {
					deletetable('customfields', $wheresqlarr);
				} else {
					$setsqlarr = array('displayorder' => intval($_POST['displayorder'][$customfieldkey]));
					updatetable('customfields', $setsqlarr, $wheresqlarr);
				}
			}
		}
	}
	showmessage('customfield_list_update_success', $theurl);
	
} elseif(submitcheck('valuesubmit')) {
	
	$_POST['customfieldid'] = intval($_POST['customfieldid']);
	$_POST['name'] = shtmlspecialchars($_POST['name']);
	$_POST['customfieldname'] = sstripslashes($_POST['customfieldname']);
	$_POST['isdefault'] = intval($_POST['isdefault']);
	
	if(strlen($_POST['name']) < 1 || strlen($_POST['name']) > 50) {
		$_POST['name'] = $_SGLOBAL['timestamp'];
	}
	
	//ONE SUBMIT
	$cfarr = array();
	if(is_array($_POST['customfieldname'])) {
		foreach ($_POST['customfieldname'] as $ckey => $cname) {
			$cname = trim($cname);
			if($cname) {
				$cname = shtmlspecialchars($cname);
				$ctype = $_POST['customfieldtype'][$ckey];
				$coption = shtmlspecialchars($_POST['customfieldoption'][$ckey]);
				$cfarr[] = array('type'=>$ctype, 'name'=>$cname, 'option'=>$coption);
			}
		}
	}
	$cfstr = addslashes(serialize(sstripslashes($cfarr)));

	if(empty($_POST['customfieldid'])) {
		//ADD
		$insertsqlarr = array(
			'uid' => $_SGLOBAL['supe_uid'],
			'type' => $_POST['type'],
			'name' => $_POST['name'],
			'displayorder' => intval($_POST['displayorder']),
			'customfieldtext' => $cfstr,
			'isdefault' => $_POST['isdefault']
		);
		inserttable('customfields', $insertsqlarr);
		showmessage('customfield_add_success', $theurl);
	} else {
		//UPDATE
		$setsqlarr = array(
			'name' => $_POST['name'],
			'displayorder' => intval($_POST['displayorder']),
			'customfieldtext' => $cfstr,
			'isdefault' => $_POST['isdefault']
		);
		$wheresqlarr = array(
			'customfieldid' => $_POST['customfieldid'],
			'uid' => $_SGLOBAL['supe_uid']
		);
		updatetable('customfields', $setsqlarr, $wheresqlarr);
		showmessage('customfield_edit_success', $theurl);
	}
} else {
	//[GET]
	$viewclass = $addclass = '';
	//LIST VIEW
	if(empty($_GET['op'])) {
		//LIST VIEW
		$wheresqlarr = array();
		$plussql = 'ORDER BY displayorder';
		$listarr = selecttable('customfields', array(), $wheresqlarr, $plussql);
		$viewclass = ' class="active"';
		
	} elseif ($_GET['op'] == 'edit') {
		//EDIT VIEW
		$wheresqlarr = array(
			'customfieldid' => $_GET['customfieldid']
		);
		$thevalues = selecttable('customfields', array(), $wheresqlarr);
		if(empty($thevalues[0])) {
			showmessage('customfield_customfieldid_no_exists');
		} else {
			$thevalue = $thevalues[0];
		}
	} elseif ($_GET['op'] == 'add') {
		//ADD VIEW
		$thevalue = array(
			'customfieldid' => '0',
			'type' => '',
			'name' => '',
			'displayorder' => '0',
			'customfieldtext' => serialize(array(array('type'=>'input','name'=>'','option'=>''))),
			'isdefault' => 0
		);
		$addclass = ' class="active"';
	}
}

//display
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['customfield_title'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass.'><a href="'.$theurl.'" class="view">'.$alang['customfield_view'].'</a></td>
					<td'.$addclass.'><a href="'.$theurl.'&op=add" class="add">'.$alang['customfield_add'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';


if(empty($_GET['op'])) {
	echo label(array('type'=>'help', 'text'=>$alang['help_customfields_list']));
}

if(is_array($listarr) && $listarr) {
	echo label(array('type'=>'form-start', 'name'=>'editcustomfield', 'action'=>$theurl));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));
	echo '<tr>';
	echo '<th>'.$alang['customfield_title_delete'].'</th>';
	echo '<th>'.$alang['customfield_title_name'].'</th>';
	echo '<th>'.$alang['channel_name'].'</th>';
	echo '<th>'.$alang['customfield_customfield'].'</th>';
	echo '<th>'.$alang['customfield_title_isdefault'].'</th>';
	echo '<th>'.$alang['customfield_title_displayorder'].'</th>';
	echo '<th>'.$alang['customfield_title_edit'].'</th>';
	echo '</tr>';	
	foreach ($listarr as $value) {
		$cfarr = unserialize($value['customfieldtext']);
		$cfnamearr = array();
		foreach ($cfarr as $cfvalue) {
			$cfnamearr[] = $cfvalue['name'];
		}
		$cftext = implode(', ', $cfnamearr);
		
		if($value['isdefault']) {
			$value['name'] = '<b>'.$value['name'].'</b>';
			$value['isdefault'] = $alang['space_yes'];
		} else {
			$value['isdefault'] = $alang['space_no'];
		}
		empty($class) ? $class=' class="darkrow"': $class='';
		echo '<tr'.$class.'>';
		echo '<td><input type="checkbox" name="delete['.$value['customfieldid'].']" value="1" /></td>';
		echo '<td>'.$value['name'].'</td>';
		echo '<td>'.$channels['types'][$value['type']]['name'].'</td>';
		echo '<td>'.$cftext.'</td>';
		echo '<td>'.$value['isdefault'].'</td>';
		echo '<td><input name="displayorder['.$value['customfieldid'].']" type="text" id="displayorder['.$value['customfieldid'].']" size="7" maxlength="" value="'.$value['displayorder'].'" /></td>';
		echo '<td><a href="'.$theurl.'&op=edit&customfieldid='.$value['customfieldid'].'">'.$alang['customfield_title_edit'].'</a></td>';
		echo '</tr>';
	}
	echo label(array('type'=>'table-end'));
	
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'listsubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'customfieldlistreset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo label(array('type'=>'form-end'));
}

//one
if(is_array($thevalue) && $thevalue) {
	
	$cftypearr = array(
		'input' => $alang['customfield_input'],
		'textarea' => $alang['customfield_textarea'],
		'select' => $alang['customfield_select'],
		'checkbox' => $alang['customfield_check']
	);
	
	$isdefaultarr = array(
		'0' => $alang['customfield_isdefault_0'],
		'1' => $alang['customfield_isdefault_1']
	);
	
	foreach($channels['types'] as $value) {
		$typearr[$value['nameid']] = $value['name'];
	}
	
	$cftypestr = getselectstr('customfieldtype[]', $cftypearr);
		
	$jscustomfieldtext = '<table><tr valign="top">';
	$jscustomfieldtext .= '<td><input type="text" name="customfieldname[]" id="aaa" size="20" value="" /></td>';
	$jscustomfieldtext .= '<td>'.$cftypestr.'</td>';
	$jscustomfieldtext .= '<td><textarea name="customfieldoption[]" rows="5" cols="22"></textarea></td>';
	$jscustomfieldtext .= '</tr></table>';

	$customfield_text = '<div id="div_customfield"><table>';
	$customfield_text .= '<tr bgcolor="#DEE1E4" style="font-weight:bold" height="22">';
	$customfield_text .= '<td width="130">'.$alang['customfield_customfield_title1'].'</td>';
	$customfield_text .= '<td width="75">'.$alang['customfield_customfield_title2'].'</td>';
	$customfield_text .= '<td width="155">'.$alang['customfield_customfield_title3'].'</td>';
	$customfield_text .= '</tr></table>';

	$cfarr = unserialize($thevalue['customfieldtext']);
	foreach ($cfarr as $cfkey => $cfvalue) {
		$thecftypestr = str_replace('value="'.$cfvalue['type'].'"', 'value="'.$cfvalue['type'].'" selected', $cftypestr);
		$customfield_text .= '<table><tr bgcolor="#FFFFFF" valign="top">';
		$customfield_text .= '<td><input type="text" name="customfieldname[]" size="20" value="'.$cfvalue['name'].'" /></td>';
		$customfield_text .= '<td>'.$thecftypestr.'</td>';
		$customfield_text .= '<td><textarea name="customfieldoption[]" rows="5" cols="22">'.$cfvalue['option'].'</textarea></td>';
		$customfield_text .= '</tr></table>';
	}
	
	$customfield_text .= '</div><table><tr><td colspan="3"><input type="button" name="Submit" value="'.$alang['customfield_addcustomfield'].'" onClick="adddivcustomfield()" /></td></tr>';
	$customfield_text .= '</table>';

	echo '
	<script language="javascript">
	<!--
	function adddivcustomfield() {
		var oDiv=document.createElement("DIV");
		document.getElementById("div_customfield").appendChild(oDiv);
		oDiv.innerHTML = "'.addcslashes($jscustomfieldtext, '"').'";
	}
	//-->
	</script>
	';
	echo label(array('type'=>'form-start', 'name'=>'addcustomfield', 'action'=>$theurl, 'other'=>' onSubmit="return validate(this)"'));
	
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'help', 'text'=>$alang['help_customfields_add']));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'input', 'alang'=>'customfield_title_name', 'name'=>'name', 'size'=>'30', 'width'=>'30%', 'value'=>$thevalue['name']));
	echo label(array('type'=>'select', 'alang'=>'channel_name', 'name'=>'type', 'width'=>'30%', 'value'=>$thevalue['type'], 'options'=>$typearr));
	echo label(array('type'=>'text', 'alang'=>'customfield_title_customfield', 'name'=>'customfieldtext', 'text'=>$customfield_text));
	echo label(array('type'=>'radio', 'alang'=>'customfield_title_isdefault', 'name'=>'isdefault', 'options'=>$isdefaultarr, 'value'=>$thevalue['isdefault']));
	echo label(array('type'=>'input', 'alang'=>'customfield_title_displayorder', 'name'=>'displayorder', 'size'=>'10', 'value'=>$thevalue['displayorder']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	
	echo '<input name="customfieldid" type="hidden" value="'.$thevalue['customfieldid'].'" />';
	echo '<input name="valuesubmit" type="hidden" value="yes" />';
	
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'editcustomfieldreset', 'value'=>$alang['common_reset']));
	echo '</div>';
	
	echo label(array('type'=>'form-end'));
}
?>