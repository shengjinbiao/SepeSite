function $(id) {
	return document.getElementById(id);
}
bxHole_ini()
function bxHole_ini(){
    var bx=$("bxHole"),tb=$("tbHole")
    $("bxImgHoleShow").innerHTML="<"+(document.all?"v:image":"img")+" id=imgHoleShow src="+squitimage+" style='position:absolute;left:0;top:0;width:"+imagesizew+";height:"+imagesizeh+"' />"
    bx.w0=tb.rows[0].cells[1].offsetWidth
    bx.h0=tb.rows[1].offsetHeight
    bx.w_img=$("imgHoleShow").offsetWidth
    bx.h_img=$("imgHoleShow").offsetHeight
    bx.dragStart=function(e,dragType){
        bx.dragType=dragType
        bx.px=tb.rows[0].cells[0].offsetWidth
        bx.py=tb.rows[0].offsetHeight
        bx.pw=tb.rows[0].cells[1].offsetWidth
        bx.ph=tb.rows[1].offsetHeight
        bx.sx=e.screenX
        bx.sy=e.screenY
    }
    bx.onmouseup=function(){
        if(bx.dragType==null)
            return
        var w=tb.rows[0].cells[1].offsetWidth,h=tb.rows[1].offsetHeight
        bx.dragType=null
        if(w/h>bx.w0/bx.h0)
            tb.rows[0].cells[1].style.width=h*bx.w0/bx.h0
        else
            tb.rows[1].style.height=w*bx.h0/bx.w0
        bx.setTip()
    }
    bx.onmousemove=function(e){
        var x,y,w,h
        if(bx.dragType==null)
            return
        if(e==null)
            e=event
        x=Math.max(bx.px+e.screenX-bx.sx,1)
        y=Math.max(bx.py+e.screenY-bx.sy,1)
        w=Math.min(bx.pw+e.screenX-bx.sx,tb.offsetWidth-bx.px-1)
        h=Math.min(bx.ph+e.screenY-bx.sy,tb.offsetHeight-bx.py-1)
        if(bx.dragType==0){
            x=Math.min(x,tb.offsetWidth-bx.pw-1)
            y=Math.min(y,tb.offsetHeight-bx.ph-1)
            w=bx.pw
            h=bx.ph
        }
        if(bx.dragType==1||bx.dragType==4)
            w=bx.pw+bx.px-x
        if(bx.dragType==1||bx.dragType==2)
            h=bx.ph+bx.py-y
        if(bx.dragType==2||bx.dragType==3)
            x=bx.px
        if(bx.dragType==3||bx.dragType==4)
            y=bx.py
        w=Math.max(w,bx.w0/2)
        h=Math.max(h,bx.h0/2)
        if(bx.dragType==1||bx.dragType==4)
            x=bx.pw+bx.px-w
        if(bx.dragType==1||bx.dragType==2)
            y=bx.ph+bx.py-h
        tb.rows[0].cells[0].style.width=x
        tb.rows[0].cells[1].style.width=w
        tb.rows[0].style.height=y
        tb.rows[1].style.height=h
        $("bxHole").setTip()
    }
    bx.setTip=function(){
        var x=tb.rows[0].cells[0].offsetWidth,y=tb.rows[0].offsetHeight,w=tb.rows[0].cells[1].offsetWidth,h=tb.rows[1].offsetHeight
        var img=$("imgHoleShow"),per
        $("bxHoleMove1").style.left=$("bxHoleMove4").style.left=x-3
        $("bxHoleMove1").style.top=$("bxHoleMove2").style.top=y-3
        $("bxHoleMove2").style.left=$("bxHoleMove3").style.left=x+w-4
        $("bxHoleMove3").style.top=$("bxHoleMove4").style.top=y+h-4

        if(w/h>bx.w0/bx.h0)
            w=h*bx.w0/bx.h0
        else
            h=w*bx.h0/bx.w0
        per=bx.h0/h
        img.style.width=per*bx.w_img
        img.style.height=per*bx.h_img
        img.style.left=-x*per
        img.style.top=-y*per
    }
    bx.setTip()
}
function   getAbsPoint(obj,formobjx,formobjy)  
 {  
  var formobjx = document.getElementById(formobjx);
  var formobjy = document.getElementById(formobjy);
  var    x    =    obj.offsetLeft,    y    =    obj.offsetTop;   
  formobjx.value = x
  formobjy.value = y
 } 
 
function   setallpostvar()  
 {  
  getAbsPoint(lu,'lux','luy')
  getAbsPoint(ld,'ldx','ldy')
  getAbsPoint(ru,'rux','ruy')
  getAbsPoint(rd,'rdx','rdy')
 } 
