//树状菜单


function treeView() {
	
	var list = document.getElementById('menu').getElementsByTagName('div');

	for ( i=0; i<list.length; i++ ) {
		if ( list[i].getElementsByTagName('ul').length > 0 ) {
			if (list[i].className=='') {list[i].className = 'folder';}
			list[i].getElementsByTagName('h3')[0].onclick = function() {
				if (this.parentNode.getElementsByTagName('ul')[0].style.display == 'none' || this.parentNode.getElementsByTagName('ul')[0].style.display == '') {
					this.parentNode.getElementsByTagName('ul')[0].style.display = 'block';
					this.parentNode.className = 'folderopen';
				} else {
					this.parentNode.getElementsByTagName('ul')[0].style.display = 'none';
					this.parentNode.className = 'folder';
				}
			}
		}
	}
	
	var linkitem = document.getElementById('menu').getElementsByTagName('li');;
	for ( j=0; j<linkitem.length; j++ ) {
		linkitem[j].getElementsByTagName('a')[0].onclick = function() {
			for ( k=0; k<linkitem.length; k++ ) {
				linkitem[k].className = '';
			}
			this.parentNode.className = 'active';
			this.blur();
		}
	}
}

function showAllNode() {
	var treeDoc = window.parent.leftframe.document;
	var treeNodes = treeDoc.getElementsByTagName('ul');
	var treeState = document.getElementById('treestate');
	
	if (treeState.innerHTML == '展开') {
		for ( i=0; i<treeNodes.length; i++ ) {
			treeNodes[i].style.display = 'block';
			treeNodes[i].parentNode.className = 'folderopen';
		}
		treeState.innerHTML = '关闭';
	} else {
		for ( i=0; i<treeNodes.length; i++ ) {
			treeNodes[i].style.display = 'none';
			treeNodes[i].parentNode.className = 'folder';
		}
		treeState.innerHTML = '展开';
	}
}