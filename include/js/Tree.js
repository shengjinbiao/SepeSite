var clickItemobj,checkedstr = gid = "";
var checkedarr = new Array();
function clickNode(){
	this.obj = null;
	this.caption = null;
	this.level = null;
	this.value = null;
}
function STree(pParent,xmlFile){
	this.PICPATH = siteUrl+"/admin/images/tree/"	//图片文件所在的文件夹，可见public，可改变。
	
	var self = this;	//相当于一个引用，指向自己。STree.
	//-----------------------------------------------------------------------------
	//不可见private。
	//常量
	var JOIN = this.PICPATH +	"join.gif";
	var JOINBOTTOM = this.PICPATH +	"joinbottom.gif";
	var MINUS = this.PICPATH +	"minus.gif";
	var MINUSBOTTOM = this.PICPATH +	"minusbottom.gif";
	var PLUS = this.PICPATH +	"plus.gif";
	var PLUSBOTTOM = this.PICPATH +	"plusbottom.gif";
	var EMPTY = this.PICPATH +	"empty.gif";
	var LINE = this.PICPATH +	"line.gif";
	
	var LEAFICON = this.PICPATH +	"page.gif";
	var NODEICON = this.PICPATH + 	"folder.gif";
	var NODEOPEN = self.PICPATH + 	"folderopen.gif";
	
	var OPEN = new Array();
		OPEN[true] = MINUS;
		OPEN[false] = PLUS;
		
	var folder = new Array();
		folder[true] = NODEOPEN;
		folder[false] = NODEICON;
		
	var OPENBOTTOM = new Array();
		OPENBOTTOM[true] = MINUSBOTTOM;
		OPENBOTTOM[false] = PLUSBOTTOM;
	
	this.CAPTIONATT = "caption";//标题属性是哪一个属性
	this.ICONATT = "icon";//图标属性
	this.EXPANDALL = true;//是否全部扩展
	this.spread = "spread";//节点是否展开:true展开、false不展开
	
	this.clickItem = new clickNode;//用于点击时，返回值。
	this.selectNode = null;//同上
	
	this.onclick = null;
	this.onmouseover = null;
	this.onmouseout = null;

	this.body = getbyid(pParent) || document.body;
	//-----------------------------------------------------------------------------
	//生XML对象。
	var createXMLDom = function(){
		if (window.ActiveXObject) 
			var xmldoc = new ActiveXObject("Microsoft.XMLDOM");
		else 
			if (document.implementation && document.implementation.createDocument)
				var xmldoc = document.implementation.createDocument("","doc",null);
		xmldoc.async = false;
		//为了和FireFox一至，这里不能改为False;
		xmldoc.preserveWhiteSpace = true;
		return xmldoc;
	}
		
	//-----------------------------------------------------------------------------
	//加载XML文件。
	var xmlDom = createXMLDom();
	if (window.ActiveXObject) {
		xmlDom.loadXML(xmlFile);
	} else {
		var vParser = new DOMParser();
		xmlDom = vParser.parseFromString(xmlFile, "text/xml");
		
	}
	//注：FF不支持xml
	var DOMRoot = xmlDom.documentElement;
	//取出指定节点的属性。
	var getDOMAtt = function(pNode,pAttribute){
		try{
			return pNode.attributes.getNamedItem(pAttribute).nodeValue;
		}catch(e){
			return false;
		}
		
	}
	//-----------------------------------------------------------------------------
	//新建HTML标签。
	var createTag = function(pTagName){
		return document.createElement(pTagName)
	}
	var createImg = function(pSrc){
		var tmp=createTag("IMG");
		tmp.align = "absmiddle";
		tmp.src = pSrc;
		tmp.onerror = function(){
			try{this.parentNode.removeChild(this);}catch(e){}
		}
		if(pParent=="containerLeft" && pSrc != false && (pSrc.indexOf('folder.gif') != -1 || pSrc.indexOf('folderopen.gif') != -1)) {
			tmp.onclick = function() {
				unsel(this.parentNode);
			}
		}
		return tmp;
	}
	var createCheckbox = function(pNode) {
		var tmp;
		try{
			tmp=createTag("<input name=\""+(pParent=="containerLeft" ? "Lblogid":"Rblogid")+"\"/>");
		} catch(e) {
			tmp=createTag("input");
			tmp.name = pParent=="containerLeft" ? "Lblogid":"Rblogid";
		}
		
		tmp.type = pParent=="containerLeft" ? "checkbox":"radio";
		tmp.setAttribute("name", pParent=="containerLeft" ? "Lblogid":"Rblogid",1);
		tmp.height = "15";
		tmp.value = getDOMAtt(pNode, 'itemid');
		tmp.id = pParent + getDOMAtt(pNode, 'itemid');
		tmp.parentNodeName = getDOMAtt(pNode, 'parent');
		tmp.caption = getDOMAtt(pNode, 'caption');
		tmp.style.height = "15px";
		tmp.style.backgroundImage = 'none';
		tmp.style.backgroundColor = 'transparent';
		tmp.style.borderWidth = '0';
		tmp.onclick = function() {
			if(this.checked)
				this.nextSibling.click();
		}
		tmp.onerror = function(){
			try{this.parentNode.removeChild(this);}catch(e){}
		}
		return tmp;
	}

	var createCaption = function(pNode,pLevel){
		var tmp = createTag("SPAN");
		tmp.innerHTML = getDOMAtt(pNode,self.CAPTIONATT);
		tmp.className = "caption";
		tmp.onmouseover = function(){
			if(this.className != "captionHighLight")
				this.className = "captionActive";
			try{self.onmouseover()}catch(e){}//必须加上
		}
		tmp.onmouseout = function(){
			if(this.className != "captionHighLight")
				this.className = "caption";
			try{self.onmouseout()}catch(e){}//必须加上
		}
		tmp.onclick = function() {
			try{
				self.clickItem.obj.className = "caption";
			}catch(e){
				//
			}
			this.className = "captionHighLight";
			preobj = this.previousSibling;
			if(preobj!=null && preobj.tagName.toLowerCase() == "input") {
				preobj.checked = true;
			}
			var clickItem = new clickNode;
			
			clickItem.obj = tmp;
			
			if(pParent == 'containerRight') {
				clickItemobj = tmp;
			}
			
			clickItem.caption = getDOMAtt(pNode,self.CAPTIONATT);
			clickItem.level = pLevel
			
			self.clickItem = clickItem;
			self.selectNode = pNode;
			try{self.onclick();}catch(e){}//必须加上，如果self没有对onclick赋值的话，会引发错误。
		}
		return tmp;
	}

	var createTreeLine = function(pNode,pParentArea){
		var hasChildren = pNode.hasChildNodes();//是否有孩子。		
		for(var i=0; i<pParentArea.level; i++){
			var tmpArea = pParentArea;		
			for(var j=pParentArea.level; j>i; j--){
				tmpArea = tmpArea.parentNode.parentNode;
			}
			
			if(tmpArea.isLastChild)
				appendTo(createImg(EMPTY),pParentArea);
			else
				appendTo(createImg(LINE),pParentArea);
		}
				
		if(hasChildren){//有孩子
			var childShowBtn;
			var tmpSpread = getDOMAtt(pNode,self.spread);
			if(!pParentArea.isLastChild){	
				childShowBtn = createImg(OPEN[tmpSpread]);	//创建一个非最后结点的拆叠图标
				appendTo(childShowBtn,pParentArea);
			}else{
				childShowBtn = createImg(OPENBOTTOM[tmpSpread]);	//创建一个最后结点的拆叠图标
				appendTo(childShowBtn,pParentArea);
			}
			childShowBtn.onclick = function(){
				var isExpand=this.parentNode.expand();
				this.nextSibling.src = folder[isExpand];	//获取下一个相邻的节点
				if(!pParentArea.isLastChild){
					this.src = OPEN[isExpand];
				}else{
					this.src = OPENBOTTOM[isExpand];
				}
			}
		}else{//无孩子。
			if(!pParentArea.isLastChild)	
				appendTo(createImg(JOIN),pParentArea);
			else
				appendTo(createImg(JOINBOTTOM),pParentArea);			
		}
	}
	
	var createIcon = function(pNode,pParentArea){
		var hasChildren = pNode.hasChildNodes();//是否有孩子
		var tmpIcon = getDOMAtt(pNode,self.ICONATT);	//获取节点属性，判断是否有预定义的图标
		var tmpSpread = getDOMAtt(pNode,self.spread);	//获取是否展开属性值
		if(tmpIcon == false){
			if(hasChildren)
				appendTo(createImg(folder[tmpSpread]),pParentArea);
			else
				appendTo(createCheckbox(pNode),pParentArea);
		}else{
			appendTo(createImg(tmpIcon),pParentArea);
		}		
	}
	//-----------------------------------------------------------------------------
	//将指定OBJ追加到某个OBJ的最后面。
	var appendTo = function(pObj,pTargetObj){
		try{
			pTargetObj.appendChild(pObj);
		}catch(e){
			alert(e.message);
		}
	}
	//-----------------------------------------------------------------------------
	var isFirstChild = function(pNode){
		//除了空白节点之外，是否是第一个节点
		var tmpNode = pNode.previousSibling;
		while(tmpNode != null && tmpNode.previousSibling != null && tmpNode.nodeType != 1)
			tmpNode=tmpNode.previousSibling;
		if(tmpNode == null || tmpNode.nodeType == 3)//是空节点
			return true;
		else
			return false;
	}
	var isLastChild = function(pNode){
		tmpNode=pNode.nextSibling;
		while(tmpNode != null && tmpNode.nextSibling != null && tmpNode.nodeType != 1)
			tmpNode=tmpNode.nextSibling;
		if(tmpNode == null || tmpNode.nodeType == 3)//是空节点
			return true;
		else
			return false;		
	}
	//-----------------------------------------------------------------------------
	//循环绘制各节点。从下面这些起，这些节点具有收缩功能，所以，下面的这些不应该被oRoot所包含，而应该是oOutLine的孩子。
	var createSubTree = function(pNode,pLevel,pNodeArea){
		var subNode;
		for(var i=0; subNode=pNode.childNodes[i]; i++){
			if(subNode.nodeType != 1) continue;//由于默认了把空白也当着一个节点来处理，所以，这里要判断一下。
			
			var subNodeItem = createTag("DIV")
			
			if(subNode.hasChildNodes()){
				var subNodeSubArea = createTag("DIV");
				var tmpSpread = getDOMAtt(subNode,self.spread);
				//由于取节点的返回值为字符串，所以下面对字符串做一个转换操作
				if(typeof tmpSpread == 'string') {
					var re = new RegExp("false","ig");
					tmpSpread = re.test(tmpSpread)?false:true;
				}
				if(!tmpSpread)
					subNodeSubArea.style.display = 'none';
			}
			subNodeItem.level = pLevel+1;
			subNodeItem.isFirstChild = isFirstChild(subNode);
			subNodeItem.isLastChild	= isLastChild(subNode);
			subNodeItem.itemid	= getDOMAtt(subNode,'itemid');
			subNodeItem.id = getDOMAtt(subNode,'id');
			subNodeItem.style.whiteSpace = "nowrap";
			//下面的这个位置不能变动，因为createTreeLine里用到了它的parentNode
			appendTo(subNodeItem,pNodeArea);
			
			createTreeLine(subNode,subNodeItem);
			createIcon(subNode,subNodeItem);
			var subNodeCaption = createCaption(subNode,pLevel+1);
			appendTo(subNodeCaption,subNodeItem);

			if(subNode.hasChildNodes()) {
				//createSubTree(subNode,pLevel+1,subNodeItem);
				appendTo(subNodeSubArea,subNodeItem);
				createSubTree(subNode,pLevel+1,subNodeSubArea);
				subNodeItem.subNodeSubArea = subNodeSubArea;
				
				subNodeItem.expand = function(){
					//如果状态是展开，返回真，否则返回假。
					if(this.subNodeSubArea.style.display == ""){
						this.subNodeSubArea.style.display = "none";
						return false;
					}else{
						this.subNodeSubArea.style.display = "";
						return true;	
					}
				};
			}
		}
	}
	
	
	this.expandByLevel = function(pLevel){
			
	}
	
	this.create = function(){
		//-----------------------------------------------------------------------------
		//绘制轮廓
		var oOutLine = createTag("DIV");
		oOutLine.className = "outLine";
		appendTo(oOutLine,this.body);
		//-----------------------------------------------------------------------------
		//绘制根。这个根不具备收缩的功能。
		var oRoot = createTag("DIV");
		oRoot.id = 0;
		oRoot.level	=-1;//级别。根的级别为-1;
	
		var oRootIcon =createImg(getDOMAtt(DOMRoot,self.ICONATT));	
		var oRootCaption=createCaption(DOMRoot,-1);
		appendTo(oRootIcon,oRoot);
		appendTo(oRootCaption,oRoot);
		appendTo(oRoot,oOutLine);
		//------------------------------------------------------------------------------
		
		createSubTree(DOMRoot,-1,oOutLine);
	}
}
function getNodeMaxIdaa(statnodes) {
	var nid,aid = 1;
	for(k=0; k< statnodes.childNodes.length; k++) {
		nid = statnodes.childNodes[k].getAttribute("id");
		if(nid.lastIndexOf(".") != -1) {
			nid = nid.substring(nid.lastIndexOf(".")+1);
		}
		if(aid < parseInt(nid)) {
			aid = parseInt(nid);
		}
	}
	return aid;
}
/**
 * 创建目录树及文章列表
 * @param array blogarr:　传递数组过来
 */
function addNode(blogarr) {
	//获取目录树区域
	var itemid,caption;
	itemid = typeof blogarr != 'undefined' && blogarr['itemid'] != null ? blogarr['itemid']:0;
	caption = typeof blogarr != 'undefined' && blogarr['caption'] != null ? blogarr['caption']:null;
	var xmlDom = createxmlDom(rtreexml);
	var firstNode = false, newNode, selectnode = 0;
	var lastid = 0;
	var levelvalue,nodevalue,dialog;
	if(!xmlDom.childNodes[0].hasChildNodes() || clickItemobj == null || clickItemobj.parentNode.level == -1) {
		firstNode = true;
		gid = "";
		newNode = xmlDom.createNode(1, "level1", "");
		if(caption == null) {
			dialog = getdialog();
			if(dialog == false) {
				return false;
			}
		} else {
			dialog = caption;
		}
		if(xmlDom.childNodes[0].lastChild != null) {
			lastid = xmlDom.childNodes[0].lastChild.getAttribute("id");
		}
		newNode.setAttribute('caption', dialog);
		newNode.setAttribute('itemid', itemid);
		newNode.setAttribute('spread', 'true');
		newNode.setAttribute('id', parseInt(lastid)+1);
		xmlDom.childNodes[0].appendChild(newNode);

	} else {
		
		//判断选择的是否为根目录
		levelvalue = parseInt(clickItemobj.parentNode.level);
		gid = nodevalue = clickItemobj.parentNode.id;
		if(clickItemobj.parentNode.itemid != 0) {
			alert("不能在文章节点上创建子节点或添加文章");
			return false;
		}
		var nowNode = xmlDom.selectNodes("//level" + (levelvalue+1) +"[@id='"+ nodevalue +"']")[0];
		
		newNode = xmlDom.createNode(1, "level"+(levelvalue+2), "");
		if(caption == null) {
			dialog = getdialog();
			if(dialog == false) {
				return false;
			}
		} else {
			dialog = caption;
		}
		//求出最后一个节点的ID
		if(nowNode.lastChild != null) {
			lastid = nowNode.lastChild.getAttribute('id');
			if(lastid.lastIndexOf('.') != -1) {
				lastid = parseInt(lastid.substring(lastid.lastIndexOf('.')+1));
			}
		}
		newNode.setAttribute('caption',dialog);
		newNode.setAttribute('itemid',itemid);
		newNode.setAttribute('spread','true');
		newNode.setAttribute('id',nodevalue+"."+(parseInt(lastid)+1));
		nowNode.appendChild(newNode);
	}

	getbyid("rxml").value = rtreexml = xmlDom.xml;
	getbyid('containerRight').innerHTML = "";
	leftTree = new STree("containerRight", rtreexml);
	leftTree.CAPTIONATT="caption";
	leftTree.create();
	clickItemobj = null;
	//重新激活原来的操作节点
	if(gid != "") {
		getbyid(gid).getElementsByTagName("span")[0].click();
		gid = "";
	}
	return true;

}

/**
 * 从左边移除指定的节点
 */
function delNode() {
	if(clickItemobj == null) return false;
	var levelvalue = parseInt(clickItemobj.parentNode.level);
	if(levelvalue == -1) levelvalue = 0;
	nodevalue = clickItemobj.parentNode.id;
	var xmlDom = createxmlDom(rtreexml);
	var term = "//level" + (levelvalue+1);
	if(nodevalue != "" && nodevalue != "0") {
		term += "[@id='"+ nodevalue +"']";
	}
	var currNode = xmlDom.selectNodes(term);
	for(i=0; i<currNode.length; i++) {
		currNode[i].parentNode.removeChild(currNode[i]);
		//重新激活左边的blog选持框
		
		var checkboxobj, itemid = currNode[i].attributes.getNamedItem("itemid").nodeValue;
		if(itemid>0) {
			checkboxobj = getbyid("containerLeft"+itemid);
			checkboxobj.disabled = false;
			checkboxobj.checked = false;
			//移除选择项
			getbyid("theform").removeChild(getbyid('bid'+itemid));
		} else {
			var j = 0,nodenum = currNode[i].selectNodes("//");
			for(j=0; j<nodenum.length; j++) {
				itemid = nodenum[j].attributes.getNamedItem("itemid").nodeValue;
				if(itemid>0) {
					checkboxobj = getbyid("containerLeft"+itemid);
					checkboxobj.disabled = false;
					checkboxobj.checked = false;
					//移除选择项
					getbyid("theform").removeChild(getbyid('bid'+itemid));
				}
			}
		}
	}
	//重构树
	getbyid("rxml").value = rtreexml = xmlDom.xml;
	getbyid('containerRight').innerHTML = "";
	leftTree = new STree("containerRight", rtreexml);
	leftTree.CAPTIONATT="caption";
	leftTree.create();
	clickItemobj = null;
	gid="";
}
/**
 * 交换节点
 */
function exchangeNode(op) {
	var opitemid,opvalue;
	if(clickItemobj == null) return false;
	var levelvalue = parseInt(clickItemobj.parentNode.level);
	if(levelvalue == -1) levelvalue = 0;
	nodevalue = clickItemobj.parentNode.id;
	var xmlDom = createxmlDom(rtreexml);
	var term = "//level" + (levelvalue+1);
	if(nodevalue != "") {
		term += "[@id='"+ nodevalue +"']";
	}
	var currNode = xmlDom.selectNodes(term)[0];
	opvalue = currNode.getAttribute("caption");
	opitemid = currNode.getAttribute("itemid");
	var pNode = currNode.parentNode;
	var index,exNode;
	for(i=0; i<pNode.childNodes.length; i++) {
		if(nodevalue == pNode.childNodes[i].getAttribute("id")) {
			index = i;
			break;
		}
	}
	if(op == "up") {
		exNode = pNode.childNodes[index].previousSibling;
		
	} else if(op == "down") {
		exNode = pNode.childNodes[index].nextSibling;
	}
	if(exNode != null) {
		gid = currNode.getAttribute("id");
		pNode.replaceChild(exNode.cloneNode(true), currNode);
		pNode.replaceChild(currNode,exNode);
	}
	//重构树
	getbyid("rxml").value = rtreexml = xmlDom.xml;
	getbyid('containerRight').innerHTML = "";
	leftTree = new STree("containerRight", rtreexml);
	leftTree.CAPTIONATT="caption";
	leftTree.create();
	clickItemobj = null;
	if(gid != "")
		getbyid(""+gid+"").getElementsByTagName("span")[0].click();
}
/**
 * 创建人机会话，并返回会话结果
 */
function getdialog(initialization) {
	if(initialization == null) initialization="";
	var dialog = window.prompt("请输入章节名称", initialization);
	if(dialog == null) return false;
	return dialog;
}
/**
 * 给章节添加文章
 */
function addarticle() {
	//获取左边所有选择框
	var inputobj = '';
	var newinputobj, cls = true;
	checkedarr = replacecomma(checkedstr).split(',');
	if(checkedarr.length > 0) {
		if(checkedarr.length > 20) {
			if(confirm('您一次性移动超20个节点，客户端会有点慢，确定移动吗？')==false) {
				return false;
			}
		}
		for (key in checkedarr) {
			if(checkedarr[key] != 0) {
				inputobj = getbyid(checkedarr[key]);
				if(inputobj.checked && !inputobj.disabled) {
					//组合数组
					var blogarr = {caption:inputobj.caption,itemid:inputobj.value};
					if(addNode(blogarr)) {
						try{
							newinputobj = document.createElement("<input name='blogsid[]'/>"); 
						}catch(e) {
							newinputobj = document.createElement("input");
							newinputobj.setAttribute('name', 'blogsid[]');
						}
						newinputobj.setAttribute('type', 'hidden');
						newinputobj.setAttribute('id', "bid"+inputobj.value);
						newinputobj.setAttribute('value', inputobj.value);
						getbyid("theform").appendChild(newinputobj);
						inputobj.disabled = true;
					} else {
						cls = false;
						break;
					}
				}
			} else {
				continue;
			}
		}
		if(cls) {
			checkedarr = new Array();
			checkedstr = "";
		}
	}
}

/**
 * 重命名节点名称
 */
function renameNode() {
	if(clickItemobj == null) { 
		alert('请选择要重命名的章节点，不能对文章标题进行重命名');
		return false;
	} else {
		var levelvalue = parseInt(clickItemobj.parentNode.level);
		if(levelvalue == -1) levelvalue = 0;
		nodevalue = clickItemobj.parentNode.id;
		var xmlDom = createxmlDom(rtreexml);
		var term = "//level" + (levelvalue+1);
		if(nodevalue != "") {
			term += "[@id='"+ nodevalue +"']";
		}
		var currNode = xmlDom.selectNodes(term)[0];
		dialog = getdialog(currNode.getAttribute("caption"));
		if(dialog == false) {
			return false;
		}
		currNode.setAttribute('caption', dialog);
		//重构树
		getbyid("rxml").value = rtreexml = xmlDom.xml;
		getbyid('containerRight').innerHTML = "";
		leftTree = new STree("containerRight", rtreexml);
		leftTree.CAPTIONATT="caption";
		leftTree.create();
		clickItemobj = null;
	}
}
function allsel(obj) {
	var selobj = obj.getElementsByTagName("input");
	for(i=0; i<selobj.length; i++) {
		if(selobj[i].type == 'checkbox' && !selobj[i].disabled) {
			selobj[i].checked = true;
			opcheckedarr(selobj[i]);
		}
	}
}

function unsel(obj) {
	var selobj = obj.getElementsByTagName("input");
	for(i=0; i<selobj.length; i++) {
		if(selobj[i].type == 'checkbox' && !selobj[i].disabled) {
			selobj[i].checked = !selobj[i].checked;
			opcheckedarr(selobj[i]);
		}
	}
}
function createxmlDom(xml) {
	if(window.ActiveXObject) {
		vXMLDoc = new ActiveXObject("Microsoft.XMLDOM");
		vXMLDoc.async = false;
		vXMLDoc.loadXML(xml);
	}else if(document.implementation.createDocument) {
		var vParser = new DOMParser();
		vXMLDoc = vParser.parseFromString(xml, "text/xml");
	}
	return vXMLDoc;
}
function opcheckedarr(obj){
	if(obj.checked) {
		checkedstr +=  ',' + obj.id;
	} else {
		var re = new RegExp(obj.id, "ig");
		checkedstr = checkedstr.replace(re,'');
		checkedstr = replacecomma(checkedstr);
	}
}
function replacecomma(str) {
	str = str.replace(/,+/ig, ',');
	str = str.replace(/^,+/ig, '');
	str = str.replace(/,+$/ig, '');
	return str;
}
function opexpand(obj) {
	this.PICPATH = siteUrl+"/admin/images/tree/"	//图片文件所在的文件夹，可见public，可改变。
	//常量
	var MINUS = this.PICPATH +	"minus.gif";
	var MINUSBOTTOM = this.PICPATH +	"minusBottom.gif";
	var PLUS = this.PICPATH +	"plus.gif";
	var PLUSBOTTOM = this.PICPATH +	"plusBottom.gif";

	var NODEICON = this.PICPATH + 	"folder.gif";
	var NODEOPEN = self.PICPATH + 	"folderopen.gif";
	
	var OPEN = new Array();
		OPEN[true] = MINUS;
		OPEN[false] = PLUS;
		
	var folder = new Array();
		folder[true] = NODEOPEN;
		folder[false] = NODEICON;
		
	var OPENBOTTOM = new Array();
		OPENBOTTOM[true] = MINUSBOTTOM;
		OPENBOTTOM[false] = PLUSBOTTOM;
		
	var eobj = obj.parentNode.lastChild;
	var isopen;
	if(eobj.style.display == ""){
		eobj.style.display = "none";
		isopen = false;
	} else {
		eobj.style.display = "";
		isopen = true;
	}
	if(obj.src.indexOf('Bottom')!=-1) {
		obj.src = OPENBOTTOM[isopen];
	} else {
		obj.src = OPEN[isopen];
	}
	obj.nextSibling.src = folder[isopen];	
}