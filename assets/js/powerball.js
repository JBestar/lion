$(document).ready(function() {

    requestRoundHistory();


    function requestRoundHistory() {

        $.ajax({
            url: '/api/pbroundhistory' + location.search,
            type: 'post',
            dataType: "json",
            success: function(jResult) {
                // console.log(jResult.data);
                if (jResult.status == "success") {
                    setRoundData(jResult.data);

                } else if (jResult.status == "logout") {
                    //window.location.reload();
                }
            },
            error: function(request, status, error) {

            }

        });
    }



    function setRoundData(arrRoundData) {
        var tHtml = "";
        if (arrRoundData != null && arrRoundData.length > 0) {

            for (var idx in arrRoundData) {
                tHtml += " <tr class=\"el-table__row\">";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrRoundData[idx].round_hash;
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrRoundData[idx].round_time.substr(0, 17) + "00";
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                if (arrRoundData[idx].round_state == 1)
                    tHtml += getNormalSum(arrRoundData[idx].round_normal);
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                if (arrRoundData[idx].round_result_5 == "L")
                    tHtml += "대(81-130)";
                else if (arrRoundData[idx].round_result_5 == "M")
                    tHtml += "중(65-80)";
                else if (arrRoundData[idx].round_result_5 == "S")
                    tHtml += "소(15-64)";
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                if (arrRoundData[idx].round_state == 1)
                    tHtml += arrRoundData[idx].round_result_3 == "P" ? "홀" : "짝";
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                if (arrRoundData[idx].round_state == 1)
                    tHtml += arrRoundData[idx].round_result_4 == "P" ? "언더" : "오버";
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                if (arrRoundData[idx].round_state == 1)
                    tHtml += arrRoundData[idx].round_result_1 == "P" ? "홀" : "짝";
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                if (arrRoundData[idx].round_state == 1)
                    tHtml += arrRoundData[idx].round_result_2 == "P" ? "언더" : "오버";
                    
                tHtml += "</div></td></tr>";
            }
            $("#el-table-data-id").html(tHtml);
        }


    }

    function getNormalSum(tNormal) {
        var arrNormal = tNormal.split(",");
        var nNorSum = 0;
        for (idx in arrNormal) {
            nNorSum += parseInt(arrNormal[idx]);
        }
        return nNorSum;

    }







});