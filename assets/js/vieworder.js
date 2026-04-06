$(document).ready(function() {


    addEventListner();
    requestRoundResult();

});

function setRoundId(nRoundId) {

    let gameId = $("#buy-info-game-id").val();
    if (parseInt(gameId) == 1) {
        let dVal = parseInt($("#buy-info-round-id").attr("name"));
        if (nRoundId < 1) {
            dVal--;
            $("#buy-info-round-id").attr("name", dVal);
            nRoundId = 288;
        } else if (nRoundId > 288) {
            dVal++;
            nRoundId = 1;
            $("#buy-info-round-id").attr("name", dVal);
        }
    }

    $("#buy-info-round-id").text(nRoundId);
    requestRoundResult();
}

function addEventListner() {
    $("#buy-info-prev-id").click(function() {
        var nRoundId = $("#buy-info-round-id").text();

        nRoundId = parseInt(nRoundId) - 1;

        setRoundId(nRoundId);
    });

    $("#buy-info-next-id").click(function() {
        var nRoundId = $("#buy-info-round-id").text();
        nRoundId = parseInt(nRoundId) + 1;

        setRoundId(nRoundId);
    });

}



function requestRoundResult() {
    var nRoundId = $("#buy-info-round-id").text();
    if (nRoundId.length < 1) return;
    var nGameId = $("#buy-info-game-id").val();
    var nDate = $("#buy-info-round-id").attr("name");

    var objData = { "round_id": nRoundId, "game_id": nGameId, "date_no": nDate };
    var jsonData = JSON.stringify(objData);


    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/api/pbroundresult" + location.search,
        success: function(jResult) {
            // console.log(jResult);
            if (jResult.status == "success") {
                showRoundResult(jResult.round, jResult.bets);
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {
            // console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
        }
    });
}



function showRoundResult(objRound, arrBetData) {
    /*
    소  background: rgb(255, 193, 7); color:white; border:none;
    중  background: skyblue; 
    대  background: rgb(246, 4, 223); 
    */
    if (objRound != null && objRound.round_state == 1) {
        var arrNormal = objRound.round_normal.split(",");
        var nNorSum = 0;
        for (idx in arrNormal) {
            nNorSum += parseInt(arrNormal[idx]);
        }

        $("#result-normal-sum-id").text(nNorSum);
        var tNorParity = objRound.round_result_3 == "P" ? "홀" : "짝";
        $("#result-normal-parity-id").text(tNorParity);
        var tNorArrow = objRound.round_result_4 == "P" ? "fas fa-arrow-down" : "fas fa-arrow-up";
        $("#result-normal-arrow-id").attr("class", tNorArrow);

        var tNorSize = "";
        if (objRound.round_result_5 == "L") {
            tNorSize = "대";
            tNorSizeStyle = "background: rgb(246, 4, 223); color:white; border:none;";
        } else if (objRound.round_result_5 == "M") {
            tNorSize = "중";
            tNorSizeStyle = "background: skyblue; color:white; border:none;";
        } else if (objRound.round_result_5 == "S") {
            tNorSize = "소";
            tNorSizeStyle = "background: rgb(255, 193, 7); color:white; border:none;";
        }
        $("#result-normal-size-id").attr("style", tNorSizeStyle);
        $("#result-normal-size-id").text(tNorSize);

        tNorParity = objRound.round_result_1 == "P" ? "홀" : "짝";
        $("#result-power-parity-id").text(tNorParity);
        tNorArrow = objRound.round_result_2 == "P" ? "fas fa-arrow-down" : "fas fa-arrow-up";
        $("#result-power-arrow-id").attr("class", tNorArrow);

        $("#lottery-result-id").show();
    } else $("#lottery-result-id").hide();

    var tHtml = "";
    var nAllBet = 0;
    var nAllWin = 0;
    var nBetSum = 0;
    var nWinSum = 0;

    if (arrBetData != null && arrBetData.length > 0) {

        for (var idx in arrBetData) {

            tHtml += " <div class=\"el-row\"> ";
            if (arrBetData[idx].bet_mode <= 8)
                tHtml += "<div class=\"text-red\">";
            else tHtml += "<div>";
            tHtml += " <div class=\"el-col el-col-8\">";
            tHtml += getBetDetail(arrBetData[idx].bet_mode);
            tHtml += "</div> <div class=\"el-col el-col-8\">";
            tHtml += parseInt(arrBetData[idx].bet_money).toLocaleString();
            tHtml += "</div></div>";
            tHtml += " <div class=\"text-red el-col el-col-8\">";
            tHtml += parseInt(arrBetData[idx].bet_win_money).toLocaleString();
            tHtml += "</div></div>";

            nBetSum += parseInt(arrBetData[idx].bet_money);
            nWinSum += parseInt(arrBetData[idx].bet_win_money);
        }
        tHtml += getBuyDetailEnd(nBetSum, nWinSum);
        nAllBet += nBetSum;
        nAllWin += nWinSum;

    }

    $("#buy-detail-id").html(tHtml);
    $("#buy-summary-bet-id").text(nAllBet.toLocaleString());
    $("#buy-summary-win-id").text(nAllWin.toLocaleString());

}

function getBuyDetailEnd(nBetSum, nWinSum) {
    var tHtml = "";

    tHtml += "<div class=\"sub-summary el-row\">";
    tHtml += "<div class=\"el-col el-col-8\">소 계</div>";
    tHtml += "<div class=\"el-col el-col-8\">";
    tHtml += nBetSum.toLocaleString();
    tHtml += "</div> <div class=\"text-red el-col el-col-8\">";
    tHtml += nWinSum.toLocaleString();
    tHtml += "</div></div>";

    return tHtml;
}