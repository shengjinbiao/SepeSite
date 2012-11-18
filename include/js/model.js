function trdisplay(str, display) {
	if(str){
		var objarr = str.split(',');
		for(i = 0; i < objarr.length; i++) {
			obj = document.getElementById(objarr[i]);
			obj.style.display = display;
		}
	}
}

function removenodebyclass(nodename, tag) {
	var nodes = document.getElementsByTagName(tag);
	if(nodes) {
		for(i = nodes.length-1; i>=0; i--) {
			var node = nodes[i];
			if (node.parentNode.name == nodename) {
				node.parentNode.removeChild(node);
			}
		}
	}
}

function addselectnodebyclass(nodename, valuearr, textarr, selected) {
	obj = document.getElementById(nodename);
	ii = 0;
	if(valuearr.length > 0) {
		for(i = 0; i < valuearr.length; i++) {
			objopt = document.createElement('option');
			obj.options.add(objopt);
			objopt.value = valuearr[i];
			objopt.text = textarr[i];
			if(valuearr[i] == selected) {
				ii = i;
			}
		}
		obj.options[ii].selected = true;
	}
}