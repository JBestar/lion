$(document).ready(function() {

    pageLoop();

});


function pageLoop() {
    requestBetHistory();
    // 1초뒤에 다시 실행
    setTimeout(function() {
        pageLoop();
    }, 120000);

}

function showBetData(arrBetData) {
    var tHtml = "";
    if (arrBetData != null && arrBetData.length > 0) {
        for (var idx in arrBetData) {
            if(arrBetData[idx].bet_mode > 12 || (arrBetData[idx].bet_mode >= 5 && arrBetData[idx].bet_mode <= 8))
                continue;
                
            if(arrBetData[idx].bet_state != 2 && arrBetData[idx].bet_state != 3)
                continue;

            tHtml += "<tr class=\"el-table__row\">";
            tHtml += "<td><div class=\"cell\">";
            if (parseInt(arrBetData[idx].bet_game) == 0)
                tHtml += "PBG";
            else if (parseInt(arrBetData[idx].bet_game) == 1)
                tHtml += "코인";
            else if (parseInt(arrBetData[idx].bet_game) == 2)
                tHtml += "EOS";
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            if (parseInt(arrBetData[idx].bet_game) == 0)
                tHtml += arrBetData[idx].bet_round_fid;
            else if (parseInt(arrBetData[idx].bet_game) == 1)
                tHtml += arrBetData[idx].bet_round_no;
            else if (parseInt(arrBetData[idx].bet_game) == 2)
                tHtml += arrBetData[idx].bet_round_no;
            tHtml += "</div></td>";
            tHtml += '<td><div class="cell">';
            tHtml += arrBetData[idx].bet_mb_uid;
            tHtml += "</div></td>";
            tHtml += '<td><div class="cell">';
            tHtml += arrBetData[idx].bet_mb_uid;
            tHtml += "</div></td>";
            tHtml += '<td><div class="cell">';
            tHtml += getBetDetail(arrBetData[idx].bet_mode);
            tHtml += "</div></td>";
            tHtml += '<td><div class="cell">';
            tHtml += parseInt(arrBetData[idx].bet_money).toLocaleString();
            tHtml += "</div></td>";
            tHtml += '<td><div class="cell">';
            tHtml += parseInt(arrBetData[idx].bet_win_money).toLocaleString();
            tHtml += "</div></td>";
            tHtml += '<td><div class="cell">';
            tHtml += arrBetData[idx].bet_time;
            tHtml += '<td><div class="cell">';
            if(arrBetData[idx].bet_state == 2) {
                tHtml += '<button type="button" class="el-button el-button--danger el-button--small" onclick="changeBet(this);"';
                tHtml += " data-game='" + arrBetData[idx].bet_game + "' data-id='" + arrBetData[idx].bet_fid;
                tHtml += "' data-mode='" + arrBetData[idx].bet_mode;
                tHtml += "' data-uid='" + arrBetData[idx].bet_mb_uid + "' data-name='" + arrBetData[idx].bet_mb_uid + "'";
                tHtml += "><span>변경</span></button>";
            }

            tHtml += "</div></td></tr>";

        }
    }

    $("#el-table-data-id").html(tHtml);
    if (tHtml.length < 1) {
        $("#el-table__empty-id").show();
    } else
        $("#el-table__empty-id").hide();
}

function requestBetHistory() {

    var tUid = $("#el-form-input-id").val();
    var objData = {"mb_uid":tUid};
    var jsonData = JSON.stringify(objData);

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/capi/bethistory" + location.search,
        success: function(jResult) {
            // console.log(jResult);
            if (jResult.status == "success") {
                showBetData(jResult.data);
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {
            // console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
        }
    });

}

function changeBet (objBtn){
    let game = $(objBtn).data('game');
    let id = $(objBtn).data('id');
    let mode = $(objBtn).data('mode');
    let target = -1;
    switch (parseInt(mode)){
        case 1: target = 2; break;
        case 2: target = 1; break;
        case 3: target = 4; break;
        case 4: target = 3; break;
        case 9: target = 10; break;
        case 10: target = 9;break;
        case 11: target = 12; break;
        case 12: target = 11; break;
        default:break;
    } 
    if(target < 0)
        return;

    let sBet = "<"+getBetDetail(mode)+">";
    let sTarget = "<"+getBetDetail(target)+">";

    showConfirmModal("1", sBet+"을 "+ sTarget+"로 변경하시겠습니까?", function(confirm){
        if(confirm){
            reqChgBet(game, id, mode);
        }
    });

}


function reqChgBet(game, id, mode) {

    var objData = {
        "game": game,
        "mode": mode,
        "id": id,
    };

    var jsonData = JSON.stringify(objData);

    $.ajax({
        url: "/capi/changebet" + location.search,
        data: { json_: jsonData },
        type: 'post',
        dataType: "json",
        success: function(jResult) {
            // console.log(jResult);
            if (jResult.status == "success") {
                showAlertBox(1, "조작이 성공되었습니다.");
                requestBetHistory();
            } else if (jResult.status == "fail") {
                if(jResult.msg)
                    showMessageBox(1, jResult.msg);
                else showMessageBox(1, "조작이 실패되었습니다.");
                requestBetHistory();
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {
            // console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
        }

    });

}