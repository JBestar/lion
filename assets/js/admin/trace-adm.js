
    function showTraces(arrTrace){
        
        var tHtml = "";

        if(arrTrace != null && arrTrace.length > 0){
            for(var idx in arrTrace){
                tHtml += " <tr class=\"el-table__row\" >";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrTrace[idx].log_time;
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrTrace[idx].log_mb_uid;
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrTrace[idx].mb_nickname;
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += "::ffff:"+arrTrace[idx].log_ip;
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                if(arrTrace[idx].log_type==1)
                    tHtml += "login";
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

    
    function requestTrace(){
        var tUid = $("#el-form-input-id").val();
        if(tUid.length < 1)
            return;
        var objData = {"uid":tUid};
        var jsonData = JSON.stringify(objData);

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {json_: jsonData},
            url:"/capi/gettrace"+location.search,
            success: function(jResult) {
                //console.log(jResult);
                if(jResult.status == "success")
                {
                    showTraces(jResult.data);
                } else if(jResult.status == "logout"){
                    location.reload();
                }
            },
            error:function(request,status,error){
                //console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
            }
        });
    }

