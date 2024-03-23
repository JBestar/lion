
    
    function showExchange(arrExchange){
        
        var tHtml = "";

        if(arrExchange != null && arrExchange.length > 0){
            for(var idx in arrExchange){
                tHtml += " <tr class=\"el-table__row\" >";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrExchange[idx].money_mb_uid;
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrExchange[idx].money_mb_ech_uid;
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += parseInt(arrExchange[idx].money_before).toLocaleString();
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += parseInt(arrExchange[idx].money_amount).toLocaleString();
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += "<span class=\"el-tag el-tag--success el-tag--light\">거래완료</span>";
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                if(arrExchange[idx].money_change_type==0)
                    tHtml += "<span class=\"el-tag el-tag--light\">직충전</span>"
                else if(arrExchange[idx].money_change_type==1)
                    tHtml += "<span class=\"el-tag el-tag--light\">충전확인</span>"
                else if(arrExchange[idx].money_change_type==2)
                    tHtml += "<span class=\"el-tag el-tag--light\">환전확인</span>"
                else if(arrExchange[idx].money_change_type==3)
                    tHtml += "<span class=\"el-tag el-tag--light\">마일리지전환</span>"
                else if(arrExchange[idx].money_change_type==10)
                    tHtml += "<span class=\"el-tag el-tag--light\">직충전송금</span>"
                else if(arrExchange[idx].money_change_type==11)
                    tHtml += "<span class=\"el-tag el-tag--light\">충전송금</span>"
                else if(arrExchange[idx].money_change_type==12)
                    tHtml += "<span class=\"el-tag el-tag--light\">환전입금</span>"
                
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrExchange[idx].money_update_time;
                tHtml += "<td><div class=\"cell\">";
                tHtml += parseInt(arrExchange[idx].mb_money).toLocaleString();
                tHtml += "</div></td></tr>";
            }
        }

        $("#el-main-data-id").html(tHtml);
        if(tHtml.length < 1){
            $("#el-main__empty-id").show();
        } else {
            $("#el-main__empty-id").hide();
        }

    }


    function requestExchange(){
        var tUid = $("#el-form-input-id").val();
        if(tUid.length < 1)
            return;
        var objData = {"uid":tUid};
        var jsonData = JSON.stringify(objData);

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {json_: jsonData},
            url:"/capi/exchange"+location.search,
            success: function(jResult) {
                //console.log(jResult);
                if(jResult.status == "success")
                {
                    showExchange(jResult.data);
                } else if(jResult.status == "logout"){
                    location.reload();
                }
            },
            error:function(request,status,error){
                //console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
            }
        });
    }