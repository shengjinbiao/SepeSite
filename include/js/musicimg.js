toggleKey =new Object();	//按钮状态数组
toggleKey[0] = "_off";
toggleKey[1] = "_on";
toggleKey[2] = "_ovr";
toggleKey[3] = "_out";
toggleKey[4] = "_mdn";
toggleKey[5] = "_mup";
toggleKey[6] = "_buf";
toggleKey[7] = "_onovr";
toggleKey[8] = "_offovr";
toggleKey[9] = "_etc";

if(document.images) {	//按钮图标的三种态
	img = new Object();
	
	img.pmode_off = new Image();
	img.pmode_off.src = siteUrl+"/images/base/music/btn_rndmode_off.gif";
	img.pmode_on = new Image();
	img.pmode_on.src = siteUrl+"/images/base/music/btn_rndmode_on.gif";
	img.pmode_ovr = new Image();
	img.pmode_ovr.src = siteUrl+"/images/base/music/btn_rndmode_ovr.gif";

	img.tloop_off = new Image();
	img.tloop_off.src = siteUrl+"/images/base/music/btn_trkloop_off.gif";
	img.tloop_on = new Image();
	img.tloop_on.src = siteUrl+"/images/base/music/btn_trkloop_on.gif";
	img.tloop_ovr = new Image();
	img.tloop_ovr.src = siteUrl+"/images/base/music/btn_trkloop_on.gif";

	img.rept_off = new Image();
	img.rept_off.src = siteUrl+"/images/base/music/btn_rept_off.gif";
	img.rept_on = new Image();
	img.rept_on.src = siteUrl+"/images/base/music/btn_rept_on.gif";
	img.rept_ovr = new Image();
	img.rept_ovr.src = siteUrl+"/images/base/music/btn_rept_ovr.gif";

	img.playt_off = new Image();
	img.playt_off.src = siteUrl+"/images/base/music/btn_play.gif";
	img.playt_on = new Image();
	img.playt_on.src = siteUrl+"/images/base/music/btn_play_on.gif";
	img.playt_ovr = new Image();
	img.playt_ovr.src = siteUrl+"/images/base/music/btn_play_ovr.gif";
	img.playt_buf = new Image();
	img.playt_buf.src = siteUrl+"/images/base/music/btn_play_buf.gif";

	img.pauzt_off = new Image();
	img.pauzt_off.src = siteUrl+"/images/base/music/btn_pauz_off.gif";
	img.pauzt_on = new Image();
	img.pauzt_on.src = siteUrl+"/images/base/music/btn_pauz_on.gif";
	img.pauzt_ovr = new Image();
	img.pauzt_ovr.src = siteUrl+"/images/base/music/btn_pauz_ovr.gif";

	img.stopt_off = new Image();
	img.stopt_off.src = siteUrl+"/images/base/music/btn_stop.gif";
	img.stopt_on = new Image();
	img.stopt_on.src = siteUrl+"/images/base/music/btn_stop_on.gif";
	img.stopt_ovr = new Image();
	img.stopt_ovr.src = siteUrl+"/images/base/music/btn_stop_ovr.gif";

	img.rwdt_off = new Image();
	img.rwdt_off.src = siteUrl+"/images/base/music/btn_rwd.gif";
	img.rwdt_on = new Image();
	img.rwdt_on.src = siteUrl+"/images/base/music/btn_rwd_on.gif";
	img.rwdt_ovr = new Image();
	img.rwdt_ovr.src = siteUrl+"/images/base/music/btn_rwd_ovr.gif";

	img.fwdt_off = new Image();
	img.fwdt_off.src = siteUrl+"/images/base/music/btn_fwd.gif";
	img.fwdt_on = new Image();
	img.fwdt_on.src = siteUrl+"/images/base/music/btn_fwd_on.gif";
	img.fwdt_ovr = new Image();
	img.fwdt_ovr.src = siteUrl+"/images/base/music/btn_fwd_ovr.gif";

	img.prevt_out = new Image();
	img.prevt_out.src = siteUrl+"/images/base/music/btn_prev.gif";
	img.prevt_ovr = new Image();
	img.prevt_ovr.src = siteUrl+"/images/base/music/btn_prev_ovr.gif";

	img.nextt_out = new Image();
	img.nextt_out.src = siteUrl+"/images/base/music/btn_next.gif";
	img.nextt_ovr = new Image();
	img.nextt_ovr.src = siteUrl+"/images/base/music/btn_next_ovr.gif";

	img.vmute_off = new Image();
	img.vmute_off.src = siteUrl+"/images/base/music/btn_mute_off.gif";
	img.vmute_on = new Image();
	img.vmute_on.src = siteUrl+"/images/base/music/btn_mute_on.gif";
	img.vmute_ovr = new Image();
	img.vmute_ovr.src = siteUrl+"/images/base/music/btn_mute_ovr.gif";

}

function imgChange(id, act){
	if(document.images) {
		document.images[id].src = eval("img."+ id + toggleKey[act] + ".src");
	}
}

function imgtog(tg, act) {
	switch(tg) {
		case 'vmute': act=="2"?imgChange("vmute", 2):imgmute();break;
		case 'pmode': act=="2"?imgChange("pmode", 2):imgrnd();break;
		case 'rept': act=="2"?imgChange("rept", 2):imgrept();break;
		case 'nextt': act=="2"?imgChange("nextt", 2):imgChange("nextt",3);break;
		case 'prevt': act=="2"?imgChange("prevt", 2):imgChange("prevt",3);break;
		case 'pauzt': act=="2"?imgpauz(2):imgpauz();break;
		case 'playt': act=="2"?imgplay(2):imgplay();break;
		case 'stopt': act=="2"?imgstop(2):imgstop();break;
		case 'plist': act=="2"?imgChange("plist", 2):imgChange("plist",3);break;
		case 'tloop': act=="2"?imgChange("tloop", 2):imgtloop("plist",3);break;
		default: break;			
	}
}

function imgmute() {
	var ps=musicobj.settings;
	ps.mute?imgChange("vmute",1):imgChange("vmute",0);
}

function imgrnd() {
	blnRndPlay?imgChange("pmode", 1):imgChange("pmode", 0);
}

function imgrept() {
	blnRept?imgChange("rept", 1):imgChange("rept", 0);
}

function imgpauz(f) {
	var wmps=musicobj.playState;
	if(f==2) {
		imgChange("pauzt",2);
	} else { 
		wmps==2 ? imgChange("pauzt",1) : imgChange("pauzt",0);
	}
}

function imgplay(f) {
	var wmps=musicobj.playState;
	if(f==2) { 
		imgChange("playt",2);
	} else {
		wmps==3 ? imgChange("playt",1) : imgChange("playt",0);
	}
}



function imgstop(f){
 	var wmps=musicobj.playState;
 	if(f==2) { 
 		imgChange("stopt",2);
 	} else { 
 		wmps==2 || wmps==3 ? imgChange("stopt",0) : imgChange("stopt",1);
	}
}

function imgtloop() {
	blnLoopTrk ? imgChange("tloop", 1):imgChange("tloop", 0);
}