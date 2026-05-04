
var m_rsSelected = {};
var m_rsBettingOpen = true;
var m_rsTimerCtx = null;
var m_rsTimerRows = null;
/** 서버 동기 후 보간 — main.js betcloserest: 배팅 중엔 round_end까지 남은 시간, 마감 후엔 표시 교체 */
var m_rsSrvUnix = null;
var m_rsDrawEndUnix = null;
var m_rsBetEndUnix = null;
var m_rsPollPerfMs = null;
var m_rsContextFailBanner = false;
var RS_EXCLUSIVE = {
	pb_holu: ["pb_jjak"], pb_jjak: ["pb_holu"],
	pb_under: ["pb_over"], pb_over: ["pb_under"],
	nb_holu: ["nb_jjak"], nb_jjak: ["nb_holu"],
	nb_under: ["nb_over"], nb_over: ["nb_under"]
};

/** 첫 행(진행 회차): 합계 금액 비교용 열 키 — RS_EXCLUSIVE 쌍과 동일 순서 */
var RS_LIVE_COMPARE_PAIRS = [
	["pb_holu", "pb_jjak"],
	["pb_under", "pb_over"],
	["nb_holu", "nb_jjak"],
	["nb_under", "nb_over"]
];

var RS_KEY_LABELS = {
	pb_holu: "파워볼홀",
	pb_jjak: "파워볼짝",
	pb_under: "파워볼언더",
	pb_over: "파워볼오버",
	nb_holu: "일반볼홀",
	nb_jjak: "일반볼짝",
	nb_under: "일반볼언더",
	nb_over: "일반볼오버"
};

function rsSelectedLabelsForKeys(keys){
	var out = [];
	for(var i = 0; i < keys.length; i++){
		out.push(RS_KEY_LABELS[keys[i]] || keys[i]);
	}
	return out.join(", ");
}

function rsFmtWon(n){
	var x = parseInt(n, 10) || 0;
	return x.toLocaleString() + "원";
}

function rsStartLiveClock(){

	if(window._rsClkIv) clearInterval(window._rsClkIv);
	window._rsClkIv = setInterval(function(){
		var dt = new Date();
		var t = ("0" + dt.getHours()).slice(-2) + ":" + ("0" + dt.getMinutes()).slice(-2) + ":" + ("0" + dt.getSeconds()).slice(-2);
		$("#roundstat-live-clock").text(t);
	}, 500);
}

function rsStartCountdownInterpolated(){

	if(window._rsCdIv) clearInterval(window._rsCdIv);
	window._rsCdIv = setInterval(rsTickCountdownDisplay, 200);
}

function rsTickCountdownDisplay(){

	if(m_rsSrvUnix == null || m_rsPollPerfMs == null){
		return;
	}
	var perfMs = (typeof performance !== "undefined" && typeof performance.now === "function") ? performance.now() : Date.now();
	var elapsedSec = (perfMs - m_rsPollPerfMs) / 1000;
	var approxSrv = m_rsSrvUnix + elapsedSec;
	if(m_rsBetEndUnix != null && approxSrv >= m_rsBetEndUnix){
		$("#roundstat-countdown").text("배팅종료");
		return;
	}
	if(m_rsDrawEndUnix == null){
		return;
	}
	var rem = Math.max(0, Math.floor(m_rsDrawEndUnix - approxSrv));
	$("#roundstat-countdown").text(rsFmtCountdown(rem));
}

function rsApplyContextCountdownSync(data){

	if(!data || data.server_unix == null){
		return;
	}
	m_rsSrvUnix = parseInt(data.server_unix, 10);
	var drawUx = data.round_draw_end_unix != null ? parseInt(data.round_draw_end_unix, 10)
		: (data.countdown_until_unix != null ? parseInt(data.countdown_until_unix, 10) : NaN);
	m_rsDrawEndUnix = !isNaN(drawUx) ? drawUx : null;
	var betUx = data.round_bet_end_unix != null ? parseInt(data.round_bet_end_unix, 10) : NaN;
	m_rsBetEndUnix = !isNaN(betUx) ? betUx : null;
	m_rsPollPerfMs = (typeof performance !== "undefined" && typeof performance.now === "function") ? performance.now() : Date.now();
	rsTickCountdownDisplay();
}

function rsFmtCountdown(sec){
	sec = parseInt(sec, 10);
	if(isNaN(sec) || sec < 0) sec = 0;
	var m = Math.floor(sec / 60);
	var s = sec % 60;
	return "[" + ("0" + m).slice(-2) + ":" + ("0" + s).slice(-2) + "]";
}

function rsAnySelected(){
	for(var k in m_rsSelected){
		if(m_rsSelected[k]) return true;
	}
	return false;
}

function rsUpdateDrawChangeBtn(){

	var ok = !m_rsBettingOpen && rsAnySelected();
	var $b = $("#roundstat-drawchange-btn");
	$b.prop("disabled", !ok);
	$b.toggleClass("roundstat-drawchange-btn--ready", ok);
	$b.toggleClass("roundstat-drawchange-btn--locked", !ok);
	$b.attr("title", ok
		? "배팅마감이며 열을 선택한 뒤 한 번 클릭하면 파워볼 서버로 추첨변경 요청이 곧바로 전달됩니다."
		: "배팅마감 이면서 파워볼·일반볼 열 중 하나 이상 선택 시 사용할 수 있습니다.");
}

function rsReflectHeaderSelectionClasses(){
	$(".roundstat-selectable-th").each(function(){
		var k = $(this).attr("data-rsk");
		var on = !!(k && m_rsSelected[k]);
		$(this).toggleClass("is-selected", on);
		$(this).toggleClass("is-blocked-peer", false);
	});
	var peerBlocked = {};
	for(var sel in m_rsSelected){
		if(!m_rsSelected[sel]) continue;
		var ex = RS_EXCLUSIVE[sel];
		if(ex){
			for(var i = 0; i < ex.length; i++) peerBlocked[ex[i]] = 1;
		}
	}
	$(".roundstat-selectable-th").each(function(){
		var k = $(this).attr("data-rsk");
		if(!k || m_rsSelected[k]) return;
		$(this).toggleClass("is-blocked-peer", !!peerBlocked[k]);
	});
}

function rsToggleHeaderKey(key){

	if(!m_rsSelected[key] && peerBlockedWithoutSelecting(key)){
		return;
	}
	if(m_rsSelected[key]){
		delete m_rsSelected[key];
	} else {
		if(RS_EXCLUSIVE[key]){
			for(var i = 0; i < RS_EXCLUSIVE[key].length; i++){
				delete m_rsSelected[RS_EXCLUSIVE[key][i]];
			}
		}
		m_rsSelected[key] = 1;
	}
	rsReflectHeaderSelectionClasses();
	rsUpdateDrawChangeBtn();
}

function peerBlockedWithoutSelecting(key){
	for(var sel in m_rsSelected){
		if(!m_rsSelected[sel]) continue;
		var ex = RS_EXCLUSIVE[sel];
		if(ex && ex.indexOf(key) >= 0) return true;
	}
	var ex2 = RS_EXCLUSIVE[key];
	if(ex2){
		for(var j = 0; j < ex2.length; j++){
			var o = ex2[j];
			if(m_rsSelected[o]) return true;
		}
	}
	return false;
}

function rsCellSum(r, key){
	switch(key){
		case "pb_holu": return parseInt(r.sum_pb_holu, 10) || 0;
		case "pb_jjak": return parseInt(r.sum_pb_jjak, 10) || 0;
		case "pb_under": return parseInt(r.sum_pb_under, 10) || 0;
		case "pb_over": return parseInt(r.sum_pb_over, 10) || 0;
		case "nb_holu": return parseInt(r.sum_nb_holu, 10) || 0;
		case "nb_jjak": return parseInt(r.sum_nb_jjak, 10) || 0;
		case "nb_under": return parseInt(r.sum_nb_under, 10) || 0;
		case "nb_over": return parseInt(r.sum_nb_over, 10) || 0;
		default: return 0;
	}
}

/** 맨 위 행(진행 회차): 대응 열 쌍에서 합계가 더 작은 쪽만 하이라이트 (동률이면 둘 다 해제) */
function rsApplyLiveRowMinHighlight(){

	var $tr = $("#roundstat-tbody-id tr:first");
	if(!$tr.length){
		return;
	}
	$tr.find("td[data-rscell]").removeClass("roundstat-live-min");

	for(var p = 0; p < RS_LIVE_COMPARE_PAIRS.length; p++){
		var a = RS_LIVE_COMPARE_PAIRS[p][0];
		var b = RS_LIVE_COMPARE_PAIRS[p][1];
		var $ta = $tr.find('td[data-rscell="' + a + '"]');
		var $tb = $tr.find('td[data-rscell="' + b + '"]');
		if(!$ta.length || !$tb.length){
			continue;
		}
		var va = parseInt($ta.attr("data-rs-sum"), 10) || 0;
		var vb = parseInt($tb.attr("data-rs-sum"), 10) || 0;
		if(va < vb){
			$ta.addClass("roundstat-live-min");
		} else if(vb < va){
			$tb.addClass("roundstat-live-min");
		}
	}
}

function rsRenderRows(arr){
	var html = "";
	if(arr && arr.length > 0){
		for(var i = 0; i < arr.length; i++){
			var r = arr[i];
			var fid = r.bet_round_fid != null ? String(r.bet_round_fid) : "";
			var rn = r.bet_round_no != null ? String(r.bet_round_no) : "";
			var label = fid && rn ? (fid + "(" + rn + ")회") : (fid || rn || "—");
			var isLive = i === 0;
			html += "<tr>";
			html += "<td><div class=\"cell\">PBG</div></td>";
			html += "<td><div class=\"cell roundstat-cell-round\">" + label + "</div></td>";

			if(isLive){
				var k = ["pb_holu", "pb_jjak", "pb_under", "pb_over", "nb_holu", "nb_jjak", "nb_under", "nb_over"];
				for(var c = 0; c < k.length; c++){
					var kk = k[c];
					var sum = rsCellSum(r, kk);
					html += "<td data-rscell=\"" + kk + "\" data-rs-sum=\"" + sum + "\"><div class=\"cell\">" + rsFmtWon(r["sum_" + kk]) + "</div></td>";
				}
			} else {
				html += "<td><div class=\"cell\">" + rsFmtWon(r.sum_pb_holu) + "</div></td>";
				html += "<td><div class=\"cell\">" + rsFmtWon(r.sum_pb_jjak) + "</div></td>";
				html += "<td><div class=\"cell\">" + rsFmtWon(r.sum_pb_under) + "</div></td>";
				html += "<td><div class=\"cell\">" + rsFmtWon(r.sum_pb_over) + "</div></td>";
				html += "<td><div class=\"cell\">" + rsFmtWon(r.sum_nb_holu) + "</div></td>";
				html += "<td><div class=\"cell\">" + rsFmtWon(r.sum_nb_jjak) + "</div></td>";
				html += "<td><div class=\"cell\">" + rsFmtWon(r.sum_nb_under) + "</div></td>";
				html += "<td><div class=\"cell\">" + rsFmtWon(r.sum_nb_over) + "</div></td>";
			}
			html += "</tr>";
		}
	}
	$("#roundstat-tbody-id").html(html);
	rsApplyLiveRowMinHighlight();
}

function requestRoundstatContext(){

	$.ajax({
		type: "POST",
		dataType: "json",
		url: "/capi/roundstatcontext" + location.search,
		success: function(j){

			if(j.status === "logout"){
				location.reload();
				return;
			}
			if(j.status !== "success" || !j.data){
				if(!m_rsContextFailBanner){
					m_rsContextFailBanner = true;
					if(typeof showMessageBox === "function"){
						showMessageBox(1, "진행 회차 정보를 불러오지 못했습니다. 주소에 로그인 파라미터(?l=...)가 있는지, conf_game 설정을 확인해 주세요.");
					}
				}
				return;
			}
			m_rsContextFailBanner = false;

			m_rsBettingOpen = !!j.data.betting_open;

			var rid = (j.data.round_id !== undefined && j.data.round_id !== null && j.data.round_id !== "") ? j.data.round_id : "—";
			var rno = (j.data.round_no !== undefined && j.data.round_no !== null && j.data.round_no !== "") ? j.data.round_no : "—";
			$("#roundstat-round-line").text("진행회차 " + rid + "(" + rno + ")회");

			rsApplyContextCountdownSync(j.data);

			rsUpdateDrawChangeBtn();
		},
		error: function(xhr){
			if(!m_rsContextFailBanner){
				m_rsContextFailBanner = true;
				var t = (xhr && xhr.status) ? (" HTTP " + xhr.status) : "";
				if(typeof showMessageBox === "function"){
					showMessageBox(1, "진행 회차 정보 요청 실패입니다." + t + " 새로고침 후 재시도해 주세요.");
				}
			}
		}
	});
}

function requestRoundstatRows(){

	$.ajax({
		type: "POST",
		dataType: "json",
		url: "/capi/roundstatrows" + (location.search ? location.search + "&" : "?") + "limit=9",
		success: function(j){
			if(j.status === "logout"){
				location.reload();
				return;
			}
			if(j.status !== "success" || !j.data){
				rsRenderRows([]);
				return;
			}
			rsRenderRows(j.data);
		},
		error: function(){
			rsRenderRows([]);
		}
	});
}

function unlockRoundstat(pwd){

	var jsonData = JSON.stringify({ pwd: pwd });
	$("#roundstat-pwd-err").text("");
	$.ajax({
		type: "POST",
		dataType: "json",
		data: { json_: jsonData },
		url: "/capi/roundstatunlock" + location.search,
		success: function(j){

			if(j.status === "success"){
				m_rsContextFailBanner = false;
				$("#roundstat-lock-overlay").hide();
				$("#roundstat-main-panel").show();
				rsStartLiveClock();
				rsStartCountdownInterpolated();
				requestRoundstatContext();
				requestRoundstatRows();
				if(m_rsTimerCtx) clearInterval(m_rsTimerCtx);
				m_rsTimerCtx = setInterval(requestRoundstatContext, 6000);
				if(m_rsTimerRows) clearInterval(m_rsTimerRows);
				m_rsTimerRows = setInterval(requestRoundstatRows, 2500);
			} else if(j.status === "fail"){
				$("#roundstat-pwd-err").text("암호가 올바르지 않습니다.");
			}
		}
	});
}

/** 추첨변경: 확인·다이얼로그 없이 즉시 파워볼(PBG) 큐 API 호출 */
function admRoundstatQueueConstraintNow(){

	if($("#roundstat-drawchange-btn").prop("disabled")){
		return;
	}
	var keys = [];
	for(var k in m_rsSelected){
		if(m_rsSelected[k]) keys.push(k);
	}
	if(keys.length < 1){
		if(typeof showMessageBox === "function"){
			showMessageBox(1, "적용할 열을 표에서 먼저 선택하세요.");
		}
		return;
	}
	var $btn = $("#roundstat-drawchange-btn");
	if($btn.data("rs-sending")){
		return;
	}
	var $span = $btn.find("span");
	var prevText = $span.text();
	$btn.data("rs-sending", true).prop("disabled", true);
	$span.text("전송 중…");

	$.ajax({
		type: "POST",
		dataType: "json",
		data: { json_: JSON.stringify({ keys: keys }) },
		url: "/capi/roundstatpbgqueueconstraint" + location.search,
		complete: function(){

			$btn.data("rs-sending", false);
			$span.text(prevText);
			rsUpdateDrawChangeBtn();
		},
		success: function(j){

			if(j.status === "logout"){
				location.reload();
				return;
			}
			if(j.status === "success"){
				var at = (j.drawn_at != null) ? j.drawn_at : (j.data && j.data.drawn_at ? j.data.drawn_at : "");
				var cond = rsSelectedLabelsForKeys(keys);
				var msg = "파워볼 추첨 서버가 추첨변경 요청을 정상적으로 접수했습니다. 선택하신 조건(" + cond + ")이 이번 회차 추첨 전달 내용에 반영됩니다.";
				if(at){
					msg += " (적용 슬롯 " + at + ")";
				}
				if(typeof showAlertBox === "function"){
					showAlertBox(0, msg);
				} else {
					alert(msg);
				}
				return;
			}
			var m = j.msg ? j.msg : "요청 실패";
			if(j.remote && j.remote.msg){
				m += " — " + j.remote.msg;
			} else if(j.raw){
				m += " — " + String(j.raw).slice(0, 120);
			}
			if(typeof showMessageBox === "function"){
				showMessageBox(1, m);
			}
		},
		error: function(){

			if(typeof showMessageBox === "function"){
				showMessageBox(1, "네트워크 오류로 요청을 보내지 못했습니다.");
			}
		}
	});
}

function admRoundstatChgPwdDlg(){
	$("#roundstat-chgpwd-old").val("");
	$("#roundstat-chgpwd-new").val("");
	$("#roundstat-chgpwd-dialog").show();
}

function admRoundstatSavePwd(){

	var oldP = $("#roundstat-chgpwd-old").val();
	var newP = $("#roundstat-chgpwd-new").val();
	if(oldP.length < 1 || newP.length < 1){
		showMessageBox(1, "현재 암호와 새 암호를 입력하세요.");
		return;
	}
	var jsonData = JSON.stringify({ old_pwd: oldP, new_pwd: newP });
	$.ajax({
		type: "POST",
		dataType: "json",
		data: { json_: jsonData },
		url: "/capi/roundstatchgpwd" + location.search,
		success: function(j){
			if(j.status === "logout"){
				location.reload();
				return;
			}
			if(j.status === "success"){
				$("#roundstat-chgpwd-dialog").hide();
				showAlertBox(0, "암호가 변경되었습니다.");
			} else if(j.status === "fail"){
				showMessageBox(1, "현재 암호가 일치하지 않거나 저장에 실패했습니다.");
			}
		}
	});
}

$(document).ready(function(){

	$("#roundstat-main-panel").hide();
	$("#roundstat-lock-overlay").show();

	$("#roundstat-pwd-submit").on("click", function(){
		unlockRoundstat($("#roundstat-pwd-input").val());
	});

	$("#roundstat-pwd-input").on("keydown", function(e){
		if(e.keyCode === 13){
			unlockRoundstat($(this).val());
		}
	});

	$(".roundstat-selectable-th").on("click", function(){
		var key = $(this).attr("data-rsk");
		if(!key) return;
		rsToggleHeaderKey(key);
	});

	$("#roundstat-drawchange-btn").on("click", function(){
		admRoundstatQueueConstraintNow();
	});
});
