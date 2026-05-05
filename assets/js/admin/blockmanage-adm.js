function blockLabel(type){
	if(type === "uid") return "아이디";
	if(type === "ip") return "아이피";
	return type || "-";
}

function escapeHtml(s){
	return String(s == null ? "" : s)
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#039;");
}

function renderBlockRows(arr){
	var html = "";
	if(arr && arr.length){
		for(var i = 0; i < arr.length; i++){
			var r = arr[i] || {};
			var t = r.block_type ? String(r.block_type) : "";
			var k = r.block_key ? String(r.block_key) : "";
			html += "<tr>";
			html += "<td><div class=\"cell\">" + blockLabel(t) + "</div></td>";
			html += "<td><div class=\"cell\">" + escapeHtml(k) + "</div></td>";
			html += "<td><div class=\"cell\">" + (parseInt(r.fail_count, 10) || 0) + "</div></td>";
			html += "<td><div class=\"cell\">" + escapeHtml(r.blocked_at || "-") + "</div></td>";
			html += "<td><div class=\"cell\">" + escapeHtml(r.updated_at || "-") + "</div></td>";
			html += "<td><div class=\"cell\"><button type=\"button\" class=\"el-button el-button--mini el-button--danger blockmanage-clear-btn\" data-block-type=\"" + escapeHtml(t) + "\" data-block-key=\"" + escapeHtml(k) + "\"><span>해제</span></button></div></td>";
			html += "</tr>";
		}
	} else {
		html += "<tr><td colspan=\"6\"><div class=\"cell\">현재 차단된 아이디/아이피가 없습니다.</div></td></tr>";
	}
	$("#blockmanage-tbody-id").html(html);
}

function requestBlockList(){
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "/capi/roundstatblocklist" + location.search,
		success: function(j){
			if(j.status === "logout"){
				location.reload();
				return;
			}
			if(j.status === "success"){
				renderBlockRows(j.data || []);
				return;
			}
			var m = j.msg ? j.msg : "블록 목록을 불러오지 못했습니다.";
			showMessageBox(1, m);
		},
		error: function(){
			showMessageBox(1, "네트워크 오류로 블록 목록 요청에 실패했습니다.");
		}
	});
}

function requestBlockClear(type, key){
	$.ajax({
		type: "POST",
		dataType: "json",
		data: { json_: JSON.stringify({ block_type: type, block_key: key }) },
		url: "/capi/roundstatblockclear" + location.search,
		success: function(j){
			if(j.status === "logout"){
				location.reload();
				return;
			}
			if(j.status === "success"){
				showAlertBox(0, "블록 해제 완료");
				requestBlockList();
				return;
			}
			var m = j.msg ? j.msg : "블록 해제 실패";
			showMessageBox(1, m);
		},
		error: function(){
			showMessageBox(1, "네트워크 오류로 블록 해제에 실패했습니다.");
		}
	});
}

$(document).ready(function(){
	$("#blockmanage-refresh-btn").on("click", function(){
		requestBlockList();
	});
	$("#blockmanage-tbody-id").on("click", ".blockmanage-clear-btn", function(){
		var t = $(this).attr("data-block-type");
		var k = $(this).attr("data-block-key");
		showConfirmModal("1", "해당 블록을 해제하시겠습니까?\n[" + blockLabel(t) + "] " + k, function(ok){
			if(ok){
				requestBlockClear(t, k);
			}
		});
	});
	requestBlockList();
});
