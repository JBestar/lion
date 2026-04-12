var worker;
var m_objUser = null;

/** 로그인 계정 기준 표시명: 아이디(mb_uid) 고정 */
function getBetCustomerName() {
    if (!m_objUser) return "";
    if (m_objUser.mb_uid != null && String(m_objUser.mb_uid).length > 0) return String(m_objUser.mb_uid);
    return "";
}
var m_objRound = new Object;
var m_iRoundErrorCnt = 0;
var m_tmClientTime = 0;
var m_elemCountTm = null;
var m_btnBet = null;
var m_bShowResult = false;
/** 직전 마감 회차 결과(pbroundresult)를 한 번이라도 불러왔는지 — 초기 로드에서 setRoundResult 누락 방지 */
var m_bRoundResultSyncedOnce = false;
/** 추첨결과(시니어 패널) 지연 원인 분석: 브라우저 콘솔에서 `[senior-result]` 로 필터 */
var m_tmLastPbcurrentgameReq = 0;
var m_tmLastPbroundresultReq = 0;
/** PBG: DB에 round_state=1 반영 전 재시도 */
var m_pbRoundResultRetryCount = 0;
var m_pbRoundResultRetryTimer = null;
var m_pbRoundResultRetryMax = 30;

function logSeniorResultDiag(phase, detail) {
    try {
        var d = detail && typeof detail === "object" ? detail : {};
        var row = {
            phase: phase,
            iso: new Date().toISOString(),
            perfMs: typeof performance !== "undefined" && performance.now ? Math.round(performance.now()) : null
        };
        for (var k in d) {
            if (Object.prototype.hasOwnProperty.call(d, k)) row[k] = d[k];
        }
        console.log("[senior-result]", JSON.stringify(row));
    } catch (eLog) {}
}
/** 당첨내역 메뉴: 좌측 배팅리스트에 적중(결과 확정) 행만 표시 */
var m_betListWinsOnly = false;

$(document).ready(function() {

    m_objRound.game = 0;
    m_objRound.round_no = 0;
    m_objRound.round_id = 0;
    m_objRound.round_current = 0;
    m_objRound.round_betend = 0;
    m_objRound.round_end = 0;

    m_tmClientTime = 0;

    m_elemCountTm = document.getElementById("betcloserest");
    m_btnBet = document.getElementById("bet-btn-id");
    selectGame(0);

    requestMemberInfo();
    requestConfig();
    requestCurrentRound();
    requestRecvMessage();
    addEventListner();

    $(document).on("input", "#bet_money", function() {
        var raw = String($(this).val()).replace(/[^0-9]/g, "");
        var v = parseInt(raw, 10) || 0;
        $(this).val(v);
        $("#bet-money-id").attr("value", v);
        $("#bet-money-id").text(v.toLocaleString() + " 원");
    });

    startWorker();

});


function addEventListner() {
    /*=============Betting=============== */
    $(".header .el-menu-item").click(function() {
        selectMenu(this);
    });

    $(".content .v-card").click(function() {
        selectCard(this);
    });

    /*=============Mileage Show/Hidden=============== */
    $("#balance-m-id").on({
        mouseover: function() {
            $("#emp-mileage-id").show();
        },
        mouseleave: function() {
            $("#emp-mileage-id").hide();
        }
    });

    /*=============Round Result=============== */
    $("#buy-info-prev-id").click(function() {
        var nRoundId = $("#buy-info-round-id").text();
        setRoundResult(parseInt(nRoundId) - 1, "nav_prev");
    });

    $("#buy-info-next-id").click(function() {
        var nRoundId = $("#buy-info-round-id").text();
        setRoundResult(parseInt(nRoundId) + 1, "nav_next");
    });



    $("body").click(function(event) {
        if ($(event.target).is("#el-message-box-id")) {
            $("#el-message-box-id").hide();
        } else if ($(event.target).is("#el-alert-box-id")) {
            $("#el-alert-box-id").hide();
        } else if ($(event.target).is("#el-dialog-bethistory-id")) {
            $("#el-dialog-bethistory-id").hide();
        } else if ($(event.target).is("#el-dialog-charge-id")) {
            $("#el-dialog-charge-id").hide();
        } else if ($(event.target).is("#el-dialog-discharge-id")) {
            $("#el-dialog-discharge-id").hide();
        } else if ($(event.target).is("#el-dialog-mileage-id")) {
            $("#el-dialog-mileage-id").hide();
        } else if ($(event.target).is("#el-dialog-nickname-id")) {
            $("#el-dialog-nickname-id").hide();
        } else if ($(event.target).is("#el-dialog-message-id")) {
            $("#el-dialog-message-id").hide();
        } else if ($(event.target).is("#el-dialog-empinfo-id")) {
            $("#el-dialog-empinfo-id").hide();
        }

    });

    /*=============MessageBox=============== */
    $("#el-message-close-id").click(function() {
        $("#el-message-box-id").hide();
    });

    $("#el-message-ok-id").click(function() {
        $("#el-message-box-id").hide();
    });

    $("#el-alert-type-id").click(function() {
        $("#el-alert-box-id").hide();
    });

    /*=============BetHistoryDlg=============== */
    $("#el-dialog-bethistory-close-id").click(function() {
        $("#el-dialog-bethistory-id").hide();
    });

    dateRangeListener();

    $("#el-dialog-bethistory-search-id").click(function() {
        requestBetHistory();
    });

    /*=============ChargeDlg=============== */
    $("#el-dialog-charge-close-id").click(function() {
        $("#el-dialog-charge-id").hide();
    });

    $("#el-dialog-charge-perform-id").click(function() {
        requestCharge();
    });

    $("#el-dialog-charge-cancel-id").click(function() {
        //initChargeDlg();
        $("#el-dialog-charge-id").hide();
    });

    /*=============DischargeDlg=============== */
    $("#el-dialog-discharge-close-id").click(function() {
        $("#el-dialog-discharge-id").hide();
    });
    $("#el-dialog-discharge-perform-id").click(function() {
        requestDischarge();
    });

    $("#el-dialog-discharge-cancel-id").click(function() {
        //initDischargeDlg();
        $("#el-dialog-discharge-id").hide();
    });

    /*=============MileageDlg=============== */
    $("#el-dialog-mileage-close-id").click(function() {
        $("#el-dialog-mileage-id").hide();
    });
    $("#el-dialog-mileage-perform-id").click(function() {
        requestMileage();
    });

    $("#el-dialog-mileage-cancel-id").click(function() {

        $("#el-dialog-mileage-id").hide();
    });
    /*=============NicknameDlg=============== */
    $("#el-dialog-nickname-close-id").click(function() {
        $("#el-dialog-nickname-id").hide();
    });
    $("#el-dialog-nickname-perform-id").click(function() {
        requestChangeNickname();
    });

    $("#el-dialog-nickname-cancel-id").click(function() {
        //initNicknameDlg();
        $("#el-dialog-nickname-id").hide();
    });

    /*=============MessageDlg=============== */
    $("#el-dialog-message-close-id").click(function() {
        $("#el-dialog-message-id").hide();
    });

    $(".el-tabs__item").click(function() {
        selectMessageTab(this);
    });


    /*=============EmpInfoDlg=============== */
    $("#el-dialog-empinfo-close-id").click(function() {
        $("#el-dialog-empinfo-id").hide();
    });

    $("#el-dialog-empinfo-ok-id").click(function() {
        requestChangeInfo();
    });

    $("#el-dialog-empinfo-cancel-id").click(function() {
        //initEmpInfoDlg();
        $("#el-dialog-empinfo-id").hide();
    });
}

function initBet() {

    $("#bet-card-id").text("");
    $("#bet-card-id").attr("value", "0");
    $("#bet-money-id").text("");
    $("#bet-money-id").attr("value", "0");
    $("#betTitle").text("배팅을 선택해주세요");
    $("#bet_money").val("0");
    initCard();


}

function selectMenu(eleLi) {
    $(".header .el-menu-item").removeClass("is-active");
    $(eleLi).addClass("is-active");
}

function selectGame(nGameId) {
    m_betListWinsOnly = false;
    $("#game-img-id").attr("name", "");
    $(".game-button").removeClass("select");
    if (nGameId == 0) {
        $("#bet-pbg-id").addClass("select");

        $("#game-img-id").attr("name", "pbg");
        $("#game-img-id").attr("src", "/assets/image/game_pbg.png");
        $("#senior-game-label").text("PBG파워볼");
    } else if (nGameId == 1) {
        $("#bet-coin5-id").addClass("select");

        $("#game-img-id").attr("name", "coin_5");
        $("#game-img-id").attr("src", "/assets/image/game_coin5.png");
        $("#senior-game-label").text("코인파워볼");
    } else if (nGameId == 2) {
        $("#bet-eos5-id").addClass("select");

        $("#game-img-id").attr("name", "eos_5");
        $("#game-img-id").attr("src", "/assets/image/game_eos5.png");
        $("#senior-game-label").text("EOS파워볼");
    }

    requestCurrentRound();
}

function getGameId() {
    let strGame = $("#game-img-id").attr("name");
    if (strGame == "pbg") {
        return 0;
    } else if (strGame == "coin_5") {
        return 1;
    } else if (strGame == "eos_5") {
        return 2;
    } else return -1;
}

function initCard() {
    $(".senior-right .card.select_card").removeClass("select_card");
    var objSelDiv = $(".content .orange-darken-3");

    if (objSelDiv.length > 0) {

        objSelDiv.removeClass("orange-darken-3");
        if (objSelDiv.hasClass("v-card-1") === true) {
            objSelDiv.addClass("light-blue-darken-3");
        } else if (objSelDiv.hasClass("v-card-2") === true) {
            objSelDiv.addClass("red-darken-1");
        } else if (objSelDiv.hasClass("v-card-3") === true) {
            objSelDiv.addClass("teal-darken-2");
        } else if (objSelDiv.hasClass("v-card-4") === true) {
            objSelDiv.addClass("red-darken-4");
        } else if (objSelDiv.hasClass("v-card-5") === true) {
            objSelDiv.addClass("cyan-darken-1");
        } else if (objSelDiv.hasClass("v-card-6") === true) {
            objSelDiv.addClass("green-darken-2");
        } else if (objSelDiv.hasClass("v-card-7") === true) {
            objSelDiv.addClass("purple-darken-3");
        }

    }
}

function selectCard(eleDiv) {

    initBet();

    var $el = $(eleDiv);
    if ($el.hasClass("senior-bet-card")) {
        $el.addClass("select_card");
    } else {
        if ($el.hasClass("v-card-1") === true) {
            $el.removeClass("light-blue-darken-3");
        } else if ($el.hasClass("v-card-2") === true) {
            $el.removeClass("red-darken-1");
        } else if ($el.hasClass("v-card-3") === true) {
            $el.removeClass("teal-darken-2");
        } else if ($el.hasClass("v-card-4") === true) {
            $el.removeClass("red-darken-4");
        } else if ($el.hasClass("v-card-5") === true) {
            $el.removeClass("cyan-darken-1");
        } else if ($el.hasClass("v-card-6") === true) {
            $el.removeClass("green-darken-2");
        } else if ($el.hasClass("v-card-7") === true) {
            $el.removeClass("purple-darken-3");
        }
        $el.addClass("orange-darken-3");
    }

    var tIndex = $el.attr("index");
    tIndex = parseInt(tIndex);
    var objCardSpan = $("#bet-card-id");
    objCardSpan.attr("value", tIndex);
    var tBetSpeak = getBetSpeak(tIndex);
    speak(tBetSpeak, { rate: 1, pitch: 1.2 });
    var tDetail = getBetDetail(tIndex);
    objCardSpan.text(tDetail);
    $("#betTitle").text(tDetail);

}


function getBetSpeak(iMode) {
    var tBetDetail = "";
    iMode = parseInt(iMode);
    switch (iMode) {
        case 1:
            tBetDetail = "홀";
            break;
        case 2:
            tBetDetail = "짝";
            break;
        case 3:
            tBetDetail = "언더";
            break;
        case 4:
            tBetDetail = "오버";
            break;
        case 5:
            tBetDetail = "홀 언더";
            break;
        case 6:
            tBetDetail = "짝 언더";
            break;
        case 7:
            tBetDetail = "홀 오버";
            break;
        case 8:
            tBetDetail = "짝 오버";
            break;
        case 9:
            tBetDetail = "홀";
            break;
        case 10:
            tBetDetail = "짝";
            break;
        case 11:
            tBetDetail = "언더";
            break;
        case 12:
            tBetDetail = "오버";
            break;
        case 13:
            tBetDetail = "홀 언더";
            break;
        case 14:
            tBetDetail = "짝 언더";
            break;
        case 15:
            tBetDetail = "홀 오버";
            break;
        case 16:
            tBetDetail = "짝 오버";
            break;
        case 17:
            tBetDetail = "대";
            break;
        case 18:
            tBetDetail = "중";
            break;
        case 19:
            tBetDetail = "소";
            break;
        case 20:
            tBetDetail = "홀 대";
            break;
        case 21:
            tBetDetail = "홀 중";
            break;
        case 22:
            tBetDetail = "홀 소";
            break;
        case 23:
            tBetDetail = "짝 대";
            break;
        case 24:
            tBetDetail = "짝 중";
            break;
        case 25:
            tBetDetail = "짝 소";
            break;
        case 30:
            tBetDetail = "0";
            break;
        case 31:
            tBetDetail = "1";
            break;
        case 32:
            tBetDetail = "2";
            break;
        case 33:
            tBetDetail = "3";
            break;
        case 34:
            tBetDetail = "4";
            break;
        case 35:
            tBetDetail = "5";
            break;
        case 36:
            tBetDetail = "6";
            break;
        case 37:
            tBetDetail = "7";
            break;
        case 38:
            tBetDetail = "8";
            break;
        case 39:
            tBetDetail = "9";
            break;
        case 41:
            tBetDetail = "홀 언더 홀";
            break;
        case 42:
            tBetDetail = "홀 언더 짝";
            break;
        case 43:
            tBetDetail = "홀 오버 홀";
            break;
        case 44:
            tBetDetail = "홀 오버 짝";
            break;
        case 45:
            tBetDetail = "짝 언더 홀";
            break;
        case 46:
            tBetDetail = "짝 언더 짝";
            break;
        case 47:
            tBetDetail = "짝 오버 홀";
            break;
        case 48:
            tBetDetail = "짝 오버 짝";
            break;
        default:
            break;
    }
    return tBetDetail;
}



function selectMoney(nMoney) {
    var tCard = $("#bet-card-id").attr("value");
    if (tCard.length < 1) {
        showMessageBox(0, "게임버튼을 먼저 눌러주세요");
        return;
    } else if (tCard < 1) {
        showMessageBox(0, "게임버튼을 먼저 눌러주세요");
        return;
    }

    var nCurrency = $("#bet-money-id").attr("value");
    if (nCurrency.length < 1)
        nCurrency = 0;
    nCurrency = parseInt(nCurrency);
    nCurrency += nMoney;

    $("#bet-money-id").attr("value", nCurrency);
    $("#bet-money-id").text(nCurrency.toLocaleString() + " 원");
    $("#bet_money").val(nCurrency);

}

function showMemberInfo(objUser) {
    if (objUser == undefined || objUser == null)
        return;
    m_objUser = objUser;

    $("#bet-name-id").text(getBetCustomerName());
    var uid = objUser.mb_uid != null ? String(objUser.mb_uid) : "";
    var money = parseInt(objUser.mb_money, 10) || 0;
    var pt = parseInt(objUser.mb_point, 10) || 0;
    $("#header-user-summary").html(
        "<span class=\"text-yellow-300 font-bold\">" + uid + "님</span>" +
        " 보유머니: <span class=\"text-yellow-300 font-bold\">" + money.toLocaleString() + "원</span>" +
        " P <span class=\"text-yellow-300 font-bold\">" + pt + "</span>"
    );

}

function showAmountInfo(objUser) {
    if (objUser == null)
        return;
    m_objUser.mb_money = objUser.mb_money;
    m_objUser.mb_point = objUser.mb_point;

    $("#bet-name-id").text(getBetCustomerName());
    var uid = m_objUser && m_objUser.mb_uid != null ? String(m_objUser.mb_uid) : "";
    var money = parseInt(objUser.mb_money, 10) || 0;
    var pt = parseInt(objUser.mb_point, 10) || 0;
    $("#header-user-summary").html(
        "<span class=\"text-yellow-300 font-bold\">" + uid + "님</span>" +
        " 보유머니: <span class=\"text-yellow-300 font-bold\">" + money.toLocaleString() + "원</span>" +
        " P <span class=\"text-yellow-300 font-bold\">" + pt + "</span>"
    );

}

function showConfigGame(objConfig) {
    if (objConfig == undefined || objConfig == null)
        return;
    $("#bet-ratio-1-id").text(objConfig.game_ratio_1);
    $("#bet-ratio-2-id").text(objConfig.game_ratio_1);
    $("#bet-ratio-3-id").text(objConfig.game_ratio_2);
    $("#bet-ratio-4-id").text(objConfig.game_ratio_2);

    $("#bet-ratio-5-id").text(objConfig.game_ratio_5);
    $("#bet-ratio-6-id").text(objConfig.game_ratio_6);
    $("#bet-ratio-7-id").text(objConfig.game_ratio_7);
    $("#bet-ratio-8-id").text(objConfig.game_ratio_8);

    $("#bet-ratio-9-id").text(objConfig.game_ratio_3);
    $("#bet-ratio-10-id").text(objConfig.game_ratio_3);
    $("#bet-ratio-11-id").text(objConfig.game_ratio_4);
    $("#bet-ratio-12-id").text(objConfig.game_ratio_4);

    $("#bet-ratio-13-id").text(objConfig.game_ratio_9);
    $("#bet-ratio-14-id").text(objConfig.game_ratio_10);
    $("#bet-ratio-15-id").text(objConfig.game_ratio_11);
    $("#bet-ratio-16-id").text(objConfig.game_ratio_12);

    $("#bet-ratio-17-id").text(objConfig.game_ratio_13);
    $("#bet-ratio-18-id").text(objConfig.game_ratio_14);
    $("#bet-ratio-19-id").text(objConfig.game_ratio_15);
    $("#bet-ratio-20-id").text(objConfig.game_ratio_16);
    $("#bet-ratio-21-id").text(objConfig.game_ratio_17);
    $("#bet-ratio-22-id").text(objConfig.game_ratio_18);
    $("#bet-ratio-23-id").text(objConfig.game_ratio_19);
    $("#bet-ratio-24-id").text(objConfig.game_ratio_20);
    $("#bet-ratio-25-id").text(objConfig.game_ratio_21);

    $("#bet-ratio-41-id").text(objConfig.game_ratio_22);
    $("#bet-ratio-42-id").text(objConfig.game_ratio_22);
    $("#bet-ratio-43-id").text(objConfig.game_ratio_22);
    $("#bet-ratio-44-id").text(objConfig.game_ratio_22);
    $("#bet-ratio-45-id").text(objConfig.game_ratio_22);
    $("#bet-ratio-46-id").text(objConfig.game_ratio_22);
    $("#bet-ratio-47-id").text(objConfig.game_ratio_22);
    $("#bet-ratio-48-id").text(objConfig.game_ratio_22);

    $("#bet-ratio-30-id").text(objConfig.game_ratio_23);
    $("#bet-ratio-31-id").text(objConfig.game_ratio_23);
    $("#bet-ratio-32-id").text(objConfig.game_ratio_23);
    $("#bet-ratio-33-id").text(objConfig.game_ratio_23);
    $("#bet-ratio-34-id").text(objConfig.game_ratio_23);
    $("#bet-ratio-35-id").text(objConfig.game_ratio_23);
    $("#bet-ratio-36-id").text(objConfig.game_ratio_23);
    $("#bet-ratio-37-id").text(objConfig.game_ratio_23);
    $("#bet-ratio-38-id").text(objConfig.game_ratio_23);
    $("#bet-ratio-39-id").text(objConfig.game_ratio_23);

}

function showNewMessage(arrMsgData) {
    var tMessage = "";
    if (arrMsgData != null && arrMsgData.length > 0) {
        tMessage = arrMsgData[0].notice_title;
        speak("새 메시지가 도착하엿습니다.", { rate: 1, pitch: 1.2 });
    }

    $("#message-marquee-id").text(tMessage);
}

/** @returns {boolean} 회차/게임 전환 또는 최초 로드 시 true (최근 베팅 목록·카드 하단 금액 갱신용) */
function setCurrentRound(objRound) {

    if (!objRound) {
        m_iRoundErrorCnt++;
        return false;
    }

    var gid = getGameId();
    var prevGame = m_objRound.game;
    var prevRoundNo = parseInt(m_objRound.round_no, 10);
    var nextRoundNo = parseInt(objRound.round_no, 10);
    if (isNaN(prevRoundNo)) prevRoundNo = -1;
    if (isNaN(nextRoundNo)) nextRoundNo = -1;

    var gameChanged = (prevGame !== gid);
    var roundNoChanged = (prevRoundNo !== nextRoundNo);

    if (roundNoChanged || gameChanged) {
        m_pbRoundResultRetryCount = 0;
        if (m_pbRoundResultRetryTimer) {
            clearTimeout(m_pbRoundResultRetryTimer);
            m_pbRoundResultRetryTimer = null;
        }
    }

    m_objRound.round_no = objRound.round_no;
    m_objRound.round_id = objRound.round_id;
    m_objRound.round_current = new Date(objRound.round_current).getTime();
    m_objRound.round_start = new Date(objRound.round_start).getTime();
    m_objRound.round_end = new Date(objRound.round_end).getTime();
    m_objRound.round_betend = new Date(objRound.round_bet_end).getTime();
    m_objRound.game = gid;

    if (objRound.last_round_fid != null && objRound.last_round_fid !== "") {
        var olf = objRound.last_round_num != null && objRound.last_round_num !== "" ? objRound.last_round_num : "";
        var tOldLabel = formatRoundDisplay(objRound.last_round_fid, olf, "회차");
        if (tOldLabel && tOldLabel !== "-") $("#old_round").text(tOldLabel);
    }

    var needInitialBuyInfo = !m_bRoundResultSyncedOnce;
    if (roundNoChanged || gameChanged || needInitialBuyInfo) {
        m_bRoundResultSyncedOnce = true;
        m_bShowResult = false;
        m_iRoundErrorCnt = 0;

        $("#buy-info-round-id").attr("name", "0");
        if (parseInt($("#buy-info-round-id").text()) < 1 || prevGame !== gid) {
            m_bShowResult = true;
        }
        setRoundResult(getLastCompletedRoundKey(), "round_sync");
        return true;
    }

    m_iRoundErrorCnt++;
    return false;

}

/** 진행 예정 회차가 아니라, 직전에 마감된 추첨(가장 최근 결과) 조회용 키 — round_id(round_hash) 또는 일·코인 회차번호 */
function getLastCompletedRoundKey() {
    if (!m_objRound || m_objRound.round_no == null || m_objRound.round_no === "") return 1;
    var n = parseInt(m_objRound.round_no, 10);
    if (m_objRound.game >= 1) {
        if (n <= 1) return 288;
        return n - 1;
    }
    var id = parseInt(m_objRound.round_id, 10);
    if (isNaN(id) || id < 2) return 1;
    return id - 1;
}

function setRoundResult(nRoundId, reason) {
    reason = reason || "unknown";
    logSeniorResultDiag("setRoundResult", { reason: reason, roundKey: nRoundId, game: getGameId() });

    if (m_objRound.game >= 1) {
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

function formatRoundDisplay(roundFid, roundNo, suffix) {
    suffix = suffix || "";
    var fid = roundFid != null && roundFid !== "" ? parseInt(roundFid, 10) : 0;
    var rn = roundNo != null && roundNo !== "" ? parseInt(roundNo, 10) : 0;
    if (isNaN(fid)) fid = 0;
    if (isNaN(rn)) rn = 0;

    if (fid > 0 && rn > 0) return String(fid) + "(" + String(rn) + ")" + suffix;
    if (fid > 0) return String(fid) + suffix;
    if (rn > 0) return String(rn) + suffix;
    return "-";
}

/**
 * 직전(마감) 회차 라벨 — cur_round(m_objRound)보다 누적 id는 -1, 일회차는 1이면 288로 감싸기.
 * fillSeniorRoundBetSummary의 targets 계산과 동일 규칙.
 */
function formatPreviousRoundLabelFromCurrent() {
    if (!m_objRound) return "-";
    var startFid = m_objRound.round_id != null && m_objRound.round_id !== "" ? parseInt(m_objRound.round_id, 10) : 0;
    var startNo = m_objRound.round_no != null && m_objRound.round_no !== "" ? parseInt(m_objRound.round_no, 10) : 0;
    if (isNaN(startFid)) startFid = 0;
    if (isNaN(startNo)) startNo = 0;
    var step = 1;
    var fidN = startFid > 0 ? (startFid - step) : 0;
    var rnN = startNo > 0 ? (startNo - step) : 0;
    while (rnN <= 0 && startNo > 0) rnN += 288;
    if (fidN < 1 && rnN < 1) return "-";
    return formatRoundDisplay(fidN, rnN, "회차");
}

function seniorCycleHtml(label, cls, dataV) {
    var extra = cls ? " " + cls : "";
    return '<div data-v-' + dataV + '="" class="cycle' + extra + '">' + label + "</div>";
}

/** 직전 회차 추첨 칩 표시: round_state==1 또는 결과 컬럼이 이미 채워진 경우 (상태 플래그 불일치 대비) */
function seniorRoundHasDrawResults(objRound) {
    if (!objRound) return false;
    if (parseInt(objRound.round_state, 10) === 1) return true;
    var r1 = objRound.round_result_1;
    if (r1 != null && String(r1).trim() !== "") return true;
    return false;
}

function fillSeniorRoundLabel(objRound) {
    var t = "";
    if (objRound) {
        // PBG/코인/EOS 공통: round_fid(누계) + round_num(일회차)
        var fid = objRound.round_fid != null ? objRound.round_fid : (objRound.round_id != null ? objRound.round_id : "");
        var rn = objRound.round_num != null && objRound.round_num !== "" ? objRound.round_num : objRound.round_no;
        t = formatRoundDisplay(fid, rn, "회차");
        var pf = parseInt(fid, 10);
        var curId = m_objRound && m_objRound.round_id != null && m_objRound.round_id !== "" ? parseInt(m_objRound.round_id, 10) : NaN;
        if (!isNaN(pf) && !isNaN(curId) && pf === curId) {
            t = "";
        }
    }
    if (!t || t === "-") {
        t = formatPreviousRoundLabelFromCurrent();
    }
    if (t && t !== "-") $("#old_round").text(t);
}

function fillSeniorResultCells(objRound) {
    if (!seniorRoundHasDrawResults(objRound)) {
        logSeniorResultDiag("fillSeniorResultCells", {
            action: "clear",
            round_state: objRound ? objRound.round_state : null,
            round_fid: objRound && objRound.round_fid != null ? objRound.round_fid : null,
            has_r1: !!(objRound && objRound.round_result_1 != null && String(objRound.round_result_1).trim() !== "")
        });
        $("#old_result_p").empty();
        $("#old_result_n").empty();
        return;
    }
    var r1 = objRound.round_result_1 == "P" ? "홀" : "짝";
    var c1 = objRound.round_result_1 == "P" ? "blue" : "red";
    var r2 = objRound.round_result_2 == "P" ? "언더" : "오버";
    var c2 = objRound.round_result_2 == "P" ? "blue" : "red";
    $("#old_result_p").html(seniorCycleHtml(r1, c1, "d64cd776") + seniorCycleHtml(r2, c2, "d64cd776"));

    var n1 = objRound.round_result_3 == "P" ? "홀" : "짝";
    var nc1 = objRound.round_result_3 == "P" ? "blue" : "red";
    var n2 = objRound.round_result_4 == "P" ? "언더" : "오버";
    var nc2 = objRound.round_result_4 == "P" ? "blue" : "red";
    var sz = "";
    var sc = "";
    if (objRound.round_result_5 == "L") {
        sz = "대";
        sc = "";
    } else if (objRound.round_result_5 == "M") {
        sz = "중";
        sc = "blue";
    } else if (objRound.round_result_5 == "S") {
        sz = "소";
        sc = "yellow";
    }
    var tailN = sz ? seniorCycleHtml(sz, sc, "7f6779bf") : "";
    $("#old_result_n").html(seniorCycleHtml(n1, nc1, "7f6779bf") + seniorCycleHtml(n2, nc2, "7f6779bf") + tailN);
    logSeniorResultDiag("fillSeniorResultCells", {
        action: "render",
        round_state: objRound.round_state,
        round_fid: objRound.round_fid != null ? objRound.round_fid : null
    });
}

function openMemWin(url, w, h) {
    w = w || 1200;
    h = h || 850;
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
    var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    var top = ((height / 2) - (h / 2)) + dualScreenTop;
    var full = url;
    if (location.search) {
        full += (url.indexOf("?") >= 0 ? "&" : "?") + location.search.slice(1);
    }
    window.open(full, "_blank", "scrollbars=yes,width=" + w + ",height=" + h + ",top=" + top + ",left=" + left);
}

function vieworderUrlForBetRow(el) {
    var g = parseInt(el.bet_game, 10);
    if (isNaN(g)) g = 0;
    var r = g === 0 ? el.bet_round_fid : el.bet_round_no;
    if (r === undefined || r === null) r = "";
    return "/home/vieworder?g=" + g + "&r=" + encodeURIComponent(r);
}

function vieworderUrlForRound(g, roundFid, roundNo) {
    if (g === undefined || g === null) g = getGameId();
    g = parseInt(g, 10);
    if (isNaN(g)) g = 0;
    var r = g === 0 ? roundFid : roundNo;
    if (r === undefined || r === null) r = "";
    return "/home/vieworder?g=" + g + "&r=" + encodeURIComponent(r);
}

function getBetListLabelSenior(el) {
    var mode = parseInt(el.bet_mode, 10);
    var cat = "파워볼";
    if (mode >= 5 && mode <= 8) cat = "파워볼조합";
    else if (mode >= 9 && mode <= 25) cat = "일반볼";
    else if (mode >= 30 && mode <= 39) cat = "파워볼";
    else if (mode >= 41 && mode <= 48) cat = "3묶음";
    var full = getBetDetail(mode);
    var shortL = full.replace(/^파워볼\s+/, "").replace(/^일반볼\s+/, "").replace(/\s+/g, "");
    var ratio = el.bet_ratio != null && el.bet_ratio !== "" ? String(el.bet_ratio) : "";
    var s = cat + " [" + shortL + "]";
    if (ratio) s += " [" + ratio + "]";
    return s;
}

/** 배팅리스트 행이 결과 적중인지 (당첨내역 필터용) */
function isSeniorBetWinHit(element) {
    if (!element) return false;
    var win = parseInt(element.bet_win_money, 10) || 0;
    var st = parseInt(element.bet_state, 10);
    if (st === 3) return true;
    if (win > 0) return true;
    return false;
}

/** 배팅리스트 빨간 구분선: 회차일(bet_round_date) 우선, 없으면 bet_time의 날짜 */
function getBetDateKeyForListSep(element) {
    if (!element) return "";
    var rd = element.bet_round_date;
    if (rd != null && String(rd).length > 0) {
        var sm = String(rd).trim().match(/^(\d{4}-\d{2}-\d{2})/);
        if (sm) return sm[1];
    }
    var betTime = element.bet_time;
    if (betTime == null || betTime === "") return "";
    var s = String(betTime).trim();
    var m = s.match(/^(\d{4}-\d{2}-\d{2})/);
    if (m) return m[1];
    var d = new Date(s);
    if (isNaN(d.getTime())) return "";
    var pad2 = function(n) { return (n < 10 ? "0" : "") + n; };
    return d.getFullYear() + "-" + pad2(d.getMonth() + 1) + "-" + pad2(d.getDate());
}

/** 시니어 패널 각 카드 하단 노란 숫자: 현재 회차(bet_round_fid)에 해당 bet_mode로 배팅한 누적 금액 */
function updateSeniorCurBetDisplays(arrBetData) {
    if (!$(".senior-right").length) return;
    var gid = getGameId();
    if (gid < 0) return;
    var curFid = m_objRound && m_objRound.round_id != null && m_objRound.round_id !== "" ? String(m_objRound.round_id) : "";
    var sums = {};
    if (curFid && curFid !== "0" && arrBetData != null && arrBetData.length > 0) {
        for (var j = 0; j < arrBetData.length; j++) {
            var el = arrBetData[j];
            if (parseInt(el.bet_game, 10) !== gid) continue;
            if (String(el.bet_round_fid) !== curFid) continue;
            var mode = parseInt(el.bet_mode, 10);
            if (isNaN(mode)) continue;
            sums[mode] = (sums[mode] || 0) + (parseInt(el.bet_money, 10) || 0);
        }
    }
    $(".senior-right .card.senior-bet-card").each(function() {
        var idx = parseInt($(this).attr("index"), 10);
        if (isNaN(idx)) return;
        var v = sums[idx] || 0;
        $(this).parent().find(".curBet").first().text(v.toLocaleString());
    });
}

function requestRecentBetList() {
    var gid = getGameId();
    if (gid < 0) return;
    var objData = { game: gid, limit: 80 };
    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: JSON.stringify(objData) },
        url: "/api/pbrecentbets" + location.search,
        success: function(jResult) {
            if (jResult.status === "success") {
                fillSeniorBetListTable(jResult.bets || []);
            } else if (jResult.status === "logout") {
                location.reload();
            }
        }
    });
}

function fillSeniorBetListTable(arrBetData) {
    var ROW_SEP = '<tr class="senior-bet-date-sep"><td colspan="5" style="height:4px;padding:0;line-height:0;background:#d62929;border:0;"></td></tr>';
    var tHtml = "";
    var raw = arrBetData != null ? arrBetData : [];
    var rows = m_betListWinsOnly ? raw.filter(function(el) { return isSeniorBetWinHit(el); }) : raw;
    if (rows.length > 0) {
        var prevDateKey = null;
        var gid = getGameId();
        for (var i = 0; i < rows.length; i++) {
            var element = rows[i];
            var dateKey = getBetDateKeyForListSep(element);
            if (i > 0 && dateKey !== prevDateKey) tHtml += ROW_SEP;
            prevDateKey = dateKey;

            var betLabel = getBetListLabelSenior(element);
            var winMoney = parseInt(element.bet_win_money, 10) || 0;
            var betMoney = parseInt(element.bet_money, 10) || 0;
            var roundNo = element.bet_round_no != null ? element.bet_round_no : "";
            var roundFid = element.bet_round_fid != null ? element.bet_round_fid : "";
            var lastCol = "";
            var sameGame = parseInt(element.bet_game, 10) === gid;
            if (m_btnBet && !m_btnBet.disabled && sameGame && element.bet_round_no == m_objRound.round_no)
                lastCol = "<span class='calcel-btn' onclick='cancelBet(" + element.bet_fid + ", this);'>취소</span>";
            else if (winMoney > 0)
                lastCol = "<b><font color=\"green\">적중</font></b>";
            else
                lastCol = "<b><font color=\"red\">미적중</font></b>";

            var voUrl = vieworderUrlForBetRow(element);
            tHtml += "<tr style=\"height:45px;\">";
            tHtml += "<td align=\"center\" style=\"width:18%;border-bottom:1px solid #515668;\"><a href=\"#\" onclick=\"openMemWin(" + JSON.stringify(voUrl) + ",1200,850);return false;\">" + formatRoundDisplay(roundFid, roundNo, '회') + "</a></td>";
            tHtml += "<td style=\"width:34%;border-bottom:1px solid #515668;\"><span class=\"left\" style=\"padding-left:10px;\">" + betLabel + "</span></td>";
            tHtml += "<td align=\"center\" style=\"width:18%;border-bottom:1px solid #515668;\">" + betMoney.toLocaleString() + "</td>";
            tHtml += "<td align=\"center\" style=\"width:18%;border-bottom:1px solid #515668;\">" + winMoney.toLocaleString() + "</td>";
            tHtml += "<td align=\"center\" style=\"width:12%;border-bottom:1px solid #515668;\">" + lastCol + "</td>";
            tHtml += "</tr>";
        }
    }
    $("#client-bet-list-tbody").html(tHtml);
    updateSeniorCurBetDisplays(raw);
    fillSeniorRoundBetSummary(raw);
}

function fillSeniorRoundBetSummary(arrBetData) {
    var $tb = $("#client-round-summary-tbody");
    if (!$tb.length) return;
    var gid = getGameId();
    if (gid < 0) {
        $tb.empty();
        return;
    }

    // 1) 최근 5개 회차 슬롯(배팅 없으면 0원/0건)
    var startFid = m_objRound && m_objRound.round_id != null ? parseInt(m_objRound.round_id, 10) : 0;
    var startNo = m_objRound && m_objRound.round_no != null ? parseInt(m_objRound.round_no, 10) : 0;
    if (isNaN(startFid)) startFid = 0;
    if (isNaN(startNo)) startNo = 0;
    var targets = [];
    for (var t = 0; t < 5; t++) {
        var fidN = startFid > 0 ? (startFid - t) : 0;
        var rnN = startNo > 0 ? (startNo - t) : 0;
        while (rnN <= 0 && startNo > 0) rnN += 288;
        targets.push({ fid: fidN > 0 ? String(fidN) : "", rn: rnN > 0 ? String(rnN) : "" });
    }

    // 2) 최근 베팅 목록을 회차별 합계/건수로 집계
    var map = {};
    if (arrBetData != null && arrBetData.length > 0) {
        for (var i = 0; i < arrBetData.length; i++) {
            var el = arrBetData[i];
            if (parseInt(el.bet_game, 10) !== gid) continue;
            var fid = el.bet_round_fid != null ? String(el.bet_round_fid) : "";
            var rn = el.bet_round_no != null ? String(el.bet_round_no) : "";
            var key = gid === 0 ? fid : rn;
            if (!key) continue;
            if (!map[key]) {
                map[key] = { fid: fid, rn: rn, sum: 0, cnt: 0 };
            }
            map[key].sum += (parseInt(el.bet_money, 10) || 0);
            map[key].cnt += 1;
        }
    }

    var html = "";
    for (var j = 0; j < targets.length; j++) {
        var tg = targets[j];
        var k = gid === 0 ? tg.fid : tg.rn;
        var it = (k && map[k]) ? map[k] : { fid: tg.fid, rn: tg.rn, sum: 0, cnt: 0 };
        var label = formatRoundDisplay(it.fid, it.rn, "회");
        var url = vieworderUrlForRound(gid, it.fid, it.rn);
        html += "<tr>";
        html += "<td><span style=\"color:#aace37\">" + label + "</span></td>";
        html += "<td>" + (it.sum || 0).toLocaleString() + " 원</td>";
        html += "<td>" + (it.cnt || 0).toLocaleString() + " 건</td>";
        html += "<td><div class=\"btn_result_s pointlink\" onclick=\"openMemWin(" + JSON.stringify(url) + ", '1200', '850');\">상세정보</div></td>";
        html += "</tr>";
    }
    $tb.html(html);
}

function showRoundResult(objRound, arrBetData) {
    logSeniorResultDiag("showRoundResult", {
        round_state: objRound != null ? objRound.round_state : null,
        round_fid: objRound && objRound.round_fid != null ? objRound.round_fid : null,
        round_is_null: objRound == null ? 1 : 0
    });
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

        arrBetData.forEach((element) => {

            tHtml += " <div class=\"el-row\"> ";
            if (element.bet_mode <= 8)
                tHtml += "<div class=\"bet-item-left text-red\">";
            else tHtml += "<div class=\"bet-item-left\">";
            tHtml += " <div class=\"bet-item-name\">";
            tHtml += getBetDetail(element.bet_mode);
            tHtml += "</div> <div class=\"bet-item-money\">";
            tHtml += parseInt(element.bet_money).toLocaleString();
            tHtml += "</div></div>";
            tHtml += " <div class=\"text-red bet-item-win\">";
            tHtml += parseInt(element.bet_win_money).toLocaleString();
            tHtml += "</div> <div class=\"bet-item-cancel\">";
            if(!m_btnBet.disabled && element.bet_round_no == m_objRound.round_no)
                tHtml += "<span class='calcel-btn' onclick='cancelBet("+element.bet_fid+", this);'>취소</span>";
            tHtml += "</div></div>";

            nBetSum += parseInt(element.bet_money);
            nWinSum += parseInt(element.bet_win_money);
        });
        tHtml += getBuyDetailEnd(nBetSum, nWinSum);
        nAllBet += nBetSum;
        nAllWin += nWinSum;

    }

    $("#buy-detail-id").html(tHtml);
    $("#buy-summary-bet-id").text(nAllBet.toLocaleString());
    $("#buy-summary-win-id").text(nAllWin.toLocaleString());

    fillSeniorRoundLabel(objRound);
    fillSeniorResultCells(objRound);
    requestRecentBetList();
}

function getBuyDetailEnd(nBetSum, nWinSum) {
    var tHtml = "";

    tHtml += "<div class=\"sub-summary el-row\">";
    tHtml += "<div class=\"bet-item-left\">";
    tHtml += "<div class=\"bet-item-name\">소 계</div>";
    tHtml += "<div class=\"bet-item-money\">";
    tHtml += nBetSum.toLocaleString();
    tHtml += "</div></div>";
    tHtml += "<div class=\"text-red bet-item-win\">";
    tHtml += nWinSum.toLocaleString();
    tHtml += "</div><div class=\"bet-item-cancel\"></div></div>";

    return tHtml;
}

function doBet() {

    var tName = getBetCustomerName();
    var iMode = $("#bet-card-id").attr("value");
    var nMoney = $("#bet-money-id").attr("value");
    var nRoundNo = m_objRound.round_no;


    if (!m_objUser || tName.length < 1) {
        showMessageBox(0, "회원정보를 불러오지 못했습니다. 새로고침 후 다시 시도해주세요.");
        return;
    }
    if (iMode.length < 1 || iMode < 1) {
        showMessageBox(0, "게임을 선택해주세요");
        return;
    }
    if (nMoney < 1) {
        showMessageBox(0, "금액을 선택해주세요");
        return;
    }

    var objData = {
        "game": getGameId(),
        "roundno": nRoundNo,
        "name": tName,
        "mode": iMode,
        "amount": nMoney
    };
    $("#bet-btn-id").addClass("is-loading");

    var jsonData = JSON.stringify(objData);

    $.ajax({
        url: '/api/betting' + location.search,
        data: { json_: jsonData },
        type: 'post',
        dataType: "json",
        success: function(jResult) {
            // console.log(jResult);
            $("#bet-btn-id").removeClass("is-loading");
            if (jResult.status == "success") {
                initBet();
                showAlertBox(1, "구매하셧습니다!", 500);
                setRoundResult(getLastCompletedRoundKey(), "after_bet_success");
                requestRecentBetList();
                setTimeout(function() { requestAmountInfo(); }, 500);

                //if(parseInt(m_objUser.mb_state_print) == 1){
                setTimeout(function() { requestSavePDF(jResult.data); }, 800);
                //}


            } else if (jResult.status == "fail") {
                if (jResult.data == 2 || jResult.data == 3)
                    showMessageBox(1, "배팅이 차단되었습니다.");
                else if (jResult.data == 6)
                    showMessageBox(1, "보유머니를 초과하셧습니다.");
                else if (jResult.data == 8)
                    showMessageBox(1, "단품한도를 초과하셧습니다.");
                else if (jResult.data == 9)
                    showMessageBox(1, "조합한도를 초과하셧습니다.");
                else if (jResult.data == 10)
                    showMessageBox(1, "회차한도를 초과하셧습니다.");
                else if (jResult.data == 11)
                    showMessageBox(1, "구매기간이 아닙니다.");
                else
                    showMessageBox(1, "배팅이 실패되었습니다.");
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {
            $("#bet-btn-id").removeClass("is-loading");
            // console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
        }

    });

}


function cancelBet(fid, objBtn) {
    $(objBtn).attr("disabled", true);
    var objData = {
        "game": getGameId(),
        "fid": fid
    };
    var jsonData = JSON.stringify(objData);
    try {
        console.log("[bet_cancel] request", objData);
    } catch (eLog) {}
    $.ajax({
        url: '/api/bet_cancel' + location.search,
        type: 'post',
        data: { json_: jsonData },
        dataType: "json",
        success: function(jResult) {
            $(objBtn).attr("disabled", false);
            try {
                console.log("[bet_cancel] response", jResult);
            } catch (eLog2) {}
            if (jResult.status == "success") {
                showAlertBox(0, "취소되었습니다.", 2000);
                setRoundResult(getLastCompletedRoundKey(), "after_cancel");
                requestRecentBetList();
                setTimeout(function() { requestAmountInfo();}, 500);
                
            } else if (jResult.status == "fail") {
                if(jResult.msg){
                    showMessageBox(1, jResult.msg);
                }
            } else if (jResult.status == "logout") {
                
            }
        },
        error: function(request, status, error) {
            $(objBtn).attr("disabled", false);
            try {
                console.log("[bet_cancel] xhr_error", {
                    status: request.status,
                    statusText: status,
                    error: error,
                    responseText: request.responseText ? String(request.responseText).substring(0, 800) : ""
                });
            } catch (eLog3) {}
        }

    });
    
}

function requestSavePDF(iBetId) {

    var objData = { "bet_id": iBetId };
    var jsonData = JSON.stringify(objData);

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/api/pbbetinfo" + location.search,
        success: function(jResult) {
            //console.log(jResult);
            if (jResult.status == "success") {
                saveToPDF(jResult.data);
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {
            //console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
        }
    });
}

function requestMemberInfo() {


    $.ajax({
        type: "POST",
        dataType: "json",
        url: "/api/assets" + location.search,
        success: function(jResult) {
            //console.log(jResult);
            if (jResult.status == "success") {
                showMemberInfo(jResult.data);
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {

        }
    });

}

function requestAmountInfo() {

    $.ajax({
        type: "POST",
        dataType: "json",
        url: "/api/assets" + location.search,
        success: function(jResult) {
            //console.log(jResult);
            if (jResult.status == "success") {
                showAmountInfo(jResult.data);
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {

        }
    });

}

function requestConfig() {
    var objData = { "index": getGameId() };
    var jsonData = JSON.stringify(objData);

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/api/getconfgame" + location.search,
        success: function(jResult) {
            if (jResult.status == "success") {
                showConfigGame(jResult.data);
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {

        }
    });

}

function requestCurrentRound() {

    var objData = { "game": getGameId() };
    var jsonData = JSON.stringify(objData);
    var tAjax0 = typeof performance !== "undefined" && performance.now ? performance.now() : 0;
    var nowWall = Date.now();
    var sinceLast = m_tmLastPbcurrentgameReq > 0 ? (nowWall - m_tmLastPbcurrentgameReq) : null;
    m_tmLastPbcurrentgameReq = nowWall;
    logSeniorResultDiag("pbcurrentgame_request", { ms_since_last: sinceLast, game: objData.game });
    $.ajax({
        url: '/api/pbcurrentgame' + location.search,
        type: 'post',
        data: { json_: jsonData },
        dataType: "json",
        success: function(jResult) {
            var ajaxMs = typeof performance !== "undefined" && performance.now ? Math.round(performance.now() - tAjax0) : null;
            logSeniorResultDiag("pbcurrentgame_response", { status: jResult.status, ajax_ms: ajaxMs });
            // console.log(jResult);
            if (jResult.status == "success") {
                if (setCurrentRound(jResult.data)) {
                    requestRecentBetList();
                }

            } else if (jResult.status == "logout") {
                location.reload();
            } else {
                setTimeout(function() { requestCurrentRound(); }, 5000);
            }
        },
        error: function(request, status, error) {
            var ajaxMs = typeof performance !== "undefined" && performance.now ? Math.round(performance.now() - tAjax0) : null;
            logSeniorResultDiag("pbcurrentgame_error", { ajax_ms: ajaxMs, status: status, error: String(error || "") });
            // console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
        }

    });
}

function pbRoundResultIsComplete(r) {
    return r != null && parseInt(r.round_state, 10) === 1;
}

function requestRoundResult() {
    var nRoundId = $("#buy-info-round-id").text();
    if (nRoundId.length < 1) return;

    var nDate = $("#buy-info-round-id").attr("name");

    var objData = { "round_id": nRoundId, "game_id": getGameId(), "date_no": nDate };
    var jsonData = JSON.stringify(objData);

    // console.log(jsonData);
    var tAjax0 = typeof performance !== "undefined" && performance.now ? performance.now() : 0;
    var wall0 = Date.now();
    var sinceLast = m_tmLastPbroundresultReq > 0 ? (wall0 - m_tmLastPbroundresultReq) : null;
    m_tmLastPbroundresultReq = wall0;
    logSeniorResultDiag("pbroundresult_request", {
        round_id: nRoundId,
        game_id: objData.game_id,
        date_no: nDate,
        ms_since_last_pbroundresult: sinceLast
    });

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/api/pbroundresult" + location.search,
        success: function(jResult) {
            var ajaxMs = typeof performance !== "undefined" && performance.now ? Math.round(performance.now() - tAjax0) : null;
            var r = jResult.round;
            logSeniorResultDiag("pbroundresult_response", {
                status: jResult.status,
                ajax_ms: ajaxMs,
                round_is_null: r == null ? 1 : 0,
                round_state: r != null ? r.round_state : null,
                round_fid: r && r.round_fid != null ? r.round_fid : null,
                has_draw_bits: r ? seniorRoundHasDrawResults(r) : false
            });
            if (jResult.status == "success") {
                var gid = getGameId();
                if (gid === 0 && !pbRoundResultIsComplete(r) && m_pbRoundResultRetryCount < m_pbRoundResultRetryMax) {
                    m_pbRoundResultRetryCount++;
                    if (m_pbRoundResultRetryTimer) {
                        clearTimeout(m_pbRoundResultRetryTimer);
                    }
                    m_pbRoundResultRetryTimer = setTimeout(function() {
                        m_pbRoundResultRetryTimer = null;
                        requestRoundResult();
                    }, 1500);
                } else {
                    m_pbRoundResultRetryCount = 0;
                    if (m_pbRoundResultRetryTimer) {
                        clearTimeout(m_pbRoundResultRetryTimer);
                        m_pbRoundResultRetryTimer = null;
                    }
                }
                showRoundResult(jResult.round, jResult.bets);
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {
            var ajaxMs = typeof performance !== "undefined" && performance.now ? Math.round(performance.now() - tAjax0) : null;
            logSeniorResultDiag("pbroundresult_error", { ajax_ms: ajaxMs, status: status, error: String(error || "") });
        }
    });
}

function requestBetHistory() {
    var tGame = $("#el-dialog-bethistory-game-id").val();
    var iRoundId = $("#el-dialog-bethistory-round-id").val();
    var tRange = $("#el-dialog-bethistory-range-id").val();

    var tStart = "",
        tEnd = "";
    var arrRange = tRange.split('~');
    if (arrRange.length == 2) {
        tStart = arrRange[0].trim();
        tEnd = arrRange[1].trim();
    }

    var objData = { "game": tGame, "mb_name": "", "round_fid": iRoundId, "start": tStart, "end": tEnd };
    var jsonData = JSON.stringify(objData);

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/api/bethistory" + location.search,
        success: function(jResult) {
            //console.log(jResult);
            if (jResult.status == "success") {
                showBetHistoryDlgData(jResult.data);
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {
            // console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
        }
    });


}

function requestCharge() {
    var tName = $("#el-dialog-charge-name-id").val();
    var nAmount = $("#el-dialog-charge-amount-id").val();

    if (nAmount.lenth < 1 || nAmount < 1) {
        showMessageBox(1, "금액을 입력해주세요");
        return;
    }
    if (tName.length < 1) {
        showMessageBox(1, "입금자를 입력해주세요");
        return;
    }

    var objData = { "charge_name": tName, "charge_amount": nAmount };
    var jsonData = JSON.stringify(objData);

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/api/charge" + location.search,
        success: function(jResult) {

            if (jResult.status == "success") {
                $("#el-dialog-charge-id").hide();
                initChargeDlg();
                showAlertBox(0, "신청완료!", 2000);
            } else if (jResult.status == "wait") {
                showMessageBox(1, "충전신청 대기중입니다.");
            } else if (jResult.status == "fail") {
                showMessageBox(1, "신청이 거절되었습니다.");
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {

        }
    });
}


function requestChargeHistory() {
    var objData = { "charge_type": 0 };
    var jsonData = JSON.stringify(objData);

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/api/chargehistory" + location.search,
        success: function(jResult) {

            if (jResult.status == "success") {
                showChargeDlgData(jResult.data);
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {

        }
    });

}


function requestDischarge() {
    var nAmount = $("#el-dialog-discharge-amount-id").val();
    var tBank = $("#el-dialog-discharge-bank-id").val();
    var tOwner = $("#el-dialog-discharge-owner-id").val();
    var tNumber = $("#el-dialog-discharge-number-id").val();

    if (nAmount.lenth < 1 || nAmount < 1) {
        showMessageBox(1, "금액을 입력해주세요");
        return;
    }
    if (parseInt(nAmount) > parseInt(m_objUser.mb_money)) {
        showMessageBox(1, "보유금액을 초과하셧습니다.");
        return;
    }
    if (tBank.length < 1 || tOwner.length < 1 || tNumber.length < 1) {
        showMessageBox(1, "은행정보를 입력해주세요");
        return;
    }

    var objData = {
        "discharge_amount": nAmount,
        "discharge_bank": tBank,
        "discharge_owner": tOwner,
        "discharge_number": tNumber,
    };
    var jsonData = JSON.stringify(objData);

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/api/discharge" + location.search,
        success: function(jResult) {

            if (jResult.status == "success") {
                $("#el-dialog-discharge-id").hide();
                initDischargeDlg();
                requestAmountInfo();
                showAlertBox(0, "신청완료!", 2000);
            } else if (jResult.status == "fail") {
                if (jResult.data == 4)
                    showMessageBox(1, "환전신청 대기중입니다.");
                else if (jResult.data == 5)
                    showMessageBox(1, "보유머니를 초과하셧습니다.");
                else if (jResult.data == 6)
                    showMessageBox(1, "환전신청 최소금액은 10,000원입니다.");
                else showMessageBox(1, "신청이 거절되엇습니다.");

            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {
            //console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
        }
    });
}


function requestDischargeHistory() {
    var objData = { "discharge_type": 0 };
    var jsonData = JSON.stringify(objData);

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/api/dischargehistory" + location.search,
        success: function(jResult) {

            if (jResult.status == "success") {
                showDischargeDlgData(jResult.data);
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {

        }
    });

}

function requestMileage() {
    var nAmount = $("#el-dialog-mileage-input-id").val();

    if (nAmount.lenth < 1 || nAmount < 1) {
        showMessageBox(1, "금액을 입력해주세요");
        return;
    }
    if (nAmount > parseInt(m_objUser.mb_point)) {
        showMessageBox(1, "적립액을 초과하셧습니다.");
        return;
    }
    var objData = { "mileage": nAmount };
    var jsonData = JSON.stringify(objData);

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/api/mileage" + location.search,
        success: function(jResult) {

            if (jResult.status == "success") {
                $("#el-dialog-mileage-id").hide();

                showAlertBox(0, "신청완료!", 2000);
                requestAmountInfo();
            } else if (jResult.status == "fail") {
                showMessageBox(1, "신청이 거절되었습니다.");
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {

        }
    });
}


function requestMileageHistory() {
    var objData = { "mileage_type": 0 };
    var jsonData = JSON.stringify(objData);

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/api/mileagehistory" + location.search,
        success: function(jResult) {

            if (jResult.status == "success") {
                showMileageDlgData(jResult.data);
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {

        }
    });

}

function requestChangeNickname() {
    var tNames = "";
    for (var i = 1; i < 10; i++) {
        tNames += $("#el-dialog-nickname-input-id" + i.toString()).val().trim() + "#";

    }
    tNames += $("#el-dialog-nickname-input-id10").val().trim();

    var objData = { "mb_user": tNames };
    var jsonData = JSON.stringify(objData);

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/api/changeuser" + location.search,
        success: function(jResult) {
            if (jResult.status == "success") {
                $("#el-dialog-nickname-id").hide();
                showAlertBox(1, "수정완료", 2000);
                requestMemberInfo();
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {

        }
    });
}


function requestChangeInfo() {

    var tPwd = $("#el-dialog-empinfo-pwd-id").val();
    var tNewPwd1 = $("#el-dialog-empinfo-newpwd-id1").val();
    var tNewPwd2 = $("#el-dialog-empinfo-newpwd-id2").val();
    var tNickname = $("#el-dialog-empinfo-nickname-id").val();
    var iPrint = $("#el-dialog-empinfo-print-id").is(":checked") ? 1 : 0;


    if (tPwd.length < 1) {
        showMessageBox(1, "기존 비밀번호를 입력해주세요");
        return;
    }
    if (tNewPwd1 !== tNewPwd2) {
        showMessageBox(1, "새비밀번호를 정확히 입력해주세요");
        return;
    }
    if (tNickname.length < 1) {
        showMessageBox(1, "별명을 입력해주세요");
        return;
    }
    var objData = { "mb_pwd": tPwd, "mb_newpwd": tNewPwd1, "mb_nickname": tNickname, "mb_print": iPrint };
    var jsonData = JSON.stringify(objData);

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/api/changeinfo" + location.search,
        success: function(jResult) {

            if (jResult.status == "success") {
                $("#el-dialog-empinfo-id").hide();
                showAlertBox(1, "수정완료", 2000);
                requestMemberInfo();
            } else if (jResult.status == "fail") {
                if (jResult.data == 2)
                    showMessageBox(0, "기존 비밀번호를 정확히 입력해주세요");
                else showMessageBox(1, "정보변경이 실패되었습니다.");
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {

        }
    });
}


function sendMessage() {
    var tTitle = $("#el-dialog-message-title-id").val();
    var tContent = $("#el-dialog-message-content-id").val();
    if (tTitle.length < 1) {
        showMessageBox(1, "제목을 입력해주세요");
        return;
    }
    if (tContent.length < 1) {
        showMessageBox(1, "내용을 입력해주세요");
        return;
    }
    var objData = { "title": tTitle, "content": tContent };
    var jsonData = JSON.stringify(objData);

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/api/sendmessage" + location.search,
        success: function(jResult) {
            //console.log(jResult);
            if (jResult.status == "success") {
                showAlertBox(0, "발송완료", 2000);
                initMessageContent();
            } else if (jResult.status == "fail") {
                showMessageBox(1, "발송이 실패되었습니다.");
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {

        }
    });

}



function requestSendMessages() {

    $.ajax({
        type: "POST",
        dataType: "json",
        url: "/api/getSendMessage" + location.search,
        success: function(jResult) {
            if (jResult.status == "success") {
                showMessageDlgData(1, jResult.data);
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {

        }
    });

}

function requestRecvMessage() {

    $.ajax({
        type: "POST",
        dataType: "json",
        url: "/api/getRecvNewMessage" + location.search,
        success: function(jResult) {
            if (jResult.status == "success") {
                showNewMessage(jResult.data);
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {

        }
    });

}

function requestRecvMessages() {

    $.ajax({
        type: "POST",
        dataType: "json",
        url: "/api/getRecvMessage" + location.search,
        success: function(jResult) {
            if (jResult.status == "success") {
                showMessageDlgData(0, jResult.data);

            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {

        }
    });

}


function requestDeleteMessage(iType, iMsgFid) {
    var objData = { "msg_type": iType, "msg_fid": iMsgFid };
    var jsonData = JSON.stringify(objData);

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { json_: jsonData },
        url: "/api/deletemessage" + location.search,
        success: function(jResult) {
            //console.log(jResult);
            if (jResult.status == "success") {
                showAlertBox(1, "Delete OK!", 2000);
                if (iType == 0)
                    requestRecvMessages();
                else if (iType == 1)
                    requestSendMessages();
            } else if (jResult.status == "fail") {
                showMessageBox(1, "삭제가 실패되었습니다.");
            } else if (jResult.status == "logout") {
                location.reload();
            }
        },
        error: function(request, status, error) {

        }
    });
}


/*=============MainLoop=============== */




// worker 실행
function startWorker() {

    // Worker 지원 유무 확인
    if (!!window.Worker) {

        // 실행하고 있는 워커 있으면 중지시키기
        if (worker) {
            stopWorker();
        }

        worker = new Worker('/assets/js/worker.js');
        worker.postMessage('워커 실행'); // 워커에 메시지를 보낸다.

        // 메시지는 JSON구조로 직렬화 할 수 있는 값이면 사용할 수 있다. Object등
        // worker.postMessage( { name : '302chanwoo' } );

        // 워커로 부터 메시지를 수신한다.
        worker.onmessage = function(e) {
            showTime();

        };
    }

}


// worker 중지
function stopWorker() {

    if (worker) {
        worker.terminate();
        worker = null;
    }

}
/*
function mainLoop() {

    showTime(); 
    
    // 1초뒤에 다시 실행
    setTimeout( function() {
        mainLoop();
    }, 1000 );

}
*/


function showTime() {

    let tmCurrent = new Date();

    var elH = document.getElementById("hours");
    var elM = document.getElementById("min");
    var elS = document.getElementById("sec");
    if (elH && elM && elS) {
        elH.textContent = fullNumber(tmCurrent.getHours());
        elM.textContent = fullNumber(tmCurrent.getMinutes());
        elS.textContent = fullNumber(tmCurrent.getSeconds());
    }
    var elRound = document.getElementById("cur_round");
    if (elRound) {
        var rid = m_objRound && m_objRound.round_id != null && m_objRound.round_id !== ""
            ? parseInt(m_objRound.round_id, 10) : 0;
        var rn = m_objRound && m_objRound.round_no != null ? parseInt(m_objRound.round_no, 10) : 0;
        if (isNaN(rid)) rid = 0;
        if (isNaN(rn)) rn = 0;
        elRound.textContent = formatRoundDisplay(rid, rn, "회");
    }

    //Request for Current Round Data to WebServer Per 1 Minute; 
    if (m_tmClientTime == 0) {
        m_tmClientTime = tmCurrent.getTime();

    }

    let nCurSec = tmCurrent.getSeconds();
    if (nCurSec % 4 == 0) {
        requestAmountInfo();
        requestRecvMessage();
    }

    m_objRound.round_current = parseInt(m_objRound.round_current) + tmCurrent.getTime() - m_tmClientTime;
    m_tmClientTime = tmCurrent.getTime();


    var nRemainMin = 0,
        nRemainSec = 0;
    if (m_objRound.round_current < m_objRound.round_betend) {
        var nRemainTm = m_objRound.round_end - m_objRound.round_current;
        nRemainMin = Math.floor((nRemainTm % (1000 * 60 * 60)) / (1000 * 60));
        nRemainSec = Math.floor((nRemainTm % (1000 * 60)) / 1000);

        if (!m_bShowResult && m_objRound.round_current >= m_objRound.round_start) {
            m_bShowResult = true;
            setRoundResult(getLastCompletedRoundKey(), "timer_after_round_start");
        }

        if (m_elemCountTm) m_elemCountTm.innerHTML = fullNumber(nRemainMin) + ":" + fullNumber(nRemainSec);

        if ($("#roundStatusText").length) $("#roundStatusText").text("배팅을 해주세요");

        if (m_btnBet.disabled) {
            m_btnBet.disabled = false;
            m_btnBet.classList.remove("is-disabled");

        }
    } else {
        if (!m_btnBet.disabled) {
            if (m_elemCountTm) m_elemCountTm.innerHTML = "배팅종료";

            if ($("#roundStatusText").length) $("#roundStatusText").text("배팅 마감");

            if (m_objRound.round_no != 0) {
                speak("배팅이 종료되었습니다.", { rate: 1, pitch: 1.2 });
                setTimeout(function() { speak("배팅이 종료되었습니다.", { rate: 1, pitch: 1.2 }); }, 2000);
            }
            m_btnBet.disabled = true;
            m_btnBet.classList.add("is-disabled");
            
            setRoundResult(getLastCompletedRoundKey(), "timer_bet_end");
        }
    }
    //회차결과현시


    //회차요청
    if (m_iRoundErrorCnt < 20 && m_objRound.round_current >= m_objRound.round_end - 3000 && m_objRound.round_current < m_objRound.round_end + 600000) {
        setTimeout(function() { requestCurrentRound(); }, 2000);
    }

    syncBetLockOverlay();
}

function syncBetLockOverlay() {
    var $m = $("#betLockMain");
    var $t = $("#betLockText");
    if (!$m.length || !m_btnBet) return;
    if (m_btnBet.disabled) {
        $m.css("display", "block");
        $t.css("display", "block");
        var msg = $("#roundStatusText").length ? $("#roundStatusText").text() : "";
        if (msg) $t.text(msg);
    } else {
        $m.css("display", "none");
        $t.css("display", "none");
    }
}

function fullNumber(number) {
    if (number < 10)
        return "0" + number;
    else return number;
}




/*=============Dialog=============== */

function dateRangeListener() {

    $('input[name="daterange"]').daterangepicker({

        "autoApply": true,
        "locale": {
            "format": "YYYY-MM-DD",
            "separator": "     ~     ",
            "firstDay": 0
        },
        "showCustomRangeLabel": true

    }, function(start, end, label) {

    });

    $(".el-date-editor--daterange").on({
        mouseover: function() {
            if ($('input[name="daterange"]').val().length > 0)
                $(".el-range__close-icon").addClass("fa fa-times-circle");

        },
        mouseleave: function() {
            $(".el-range__close-icon").removeClass("fa fa-times-circle");
        }
    });

    $(".el-range__close-icon").click(function() {
        $('input[name="daterange"]').val('');
    });

    $('input[name="daterange"]').val('');
}


function showMessageBox(iType, tMessage) {
    var tTitle = "";
    var tClass = "";
    if (iType == 1) {
        tTitle = "";
        tClass = "fas fa-exclamation-circle";
    } else {
        tClass = "fas fa-times-circle";
        tTitle = "시스템메시지";
    }

    $("#el-message-type-id").attr("class", tClass);
    $("#el-message-title-id").text(tTitle);

    $("#el-message-text-id").text(tMessage);
    $("#el-message-box-id").fadeIn(300);

}

function showAlertBox(iType, tMessage, nDelay) {
    var tClass = "";
    if (iType == 1) {
        tClass = "fas fa-check-circle";
    } else {
        tClass = "fas fa-times-circle";
    }
    $("#el-alert-type-id").attr("class", tClass);
    $("#el-alert-content-id").text(tMessage);
    $("#el-alert-box-id").slideDown(300);
    if (nDelay < 500)
        nDelay = 500;
    setTimeout(function() { $("#el-alert-box-id").fadeOut(500); }, nDelay);
}

/*=============Dialogs=============== */



function showRoundHistoryPage() {
    var w = 830;
    var h = 1000;
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
    var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    var top = ((height / 2) - (h / 2)) + dualScreenTop;

    var strUrl = "";
    if (location.search.length > 0) {
        if (m_objRound.game == 1) {
            w = 830;
            strUrl = "/home/coin_5" + location.search;
            window.open(strUrl, "_blank", 'scrollbars=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
        } else if (m_objRound.game == 2) {
            w = 830;
            strUrl = "/home/eos_5" + location.search;
            window.open(strUrl, "_blank", 'scrollbars=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
        } else if (m_objRound.game == 0) {
            strUrl = "/home/pbg" + location.search;
            window.open(strUrl, "_blank", 'scrollbars=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
        }
    }


}

function openScreen() {
    showRoundHistoryPage();
}

function showWinHistoryPage() {
    m_betListWinsOnly = true;
    requestRecentBetList();
}

function showBetHistoryPage() {
    var w = 320;
    var h = 1000;
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
    var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    var top = ((height / 2) - (h / 2)) + dualScreenTop;

    if (location.search.length > 0) {
        var strUrl = "/home/vieworder" + location.search + "&g=" + m_objRound.game.toString();
        if (m_objRound.game >= 1) {
            strUrl += "&r=" + m_objRound.round_no.toString();
        } else {
            strUrl += "&r=" + m_objRound.round_id.toString();
        }
        window.open(strUrl, "_blank", 'scrollbars=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
    }

}




/*=============BetHistoryDialog=============== */

function showBetHistoryDlg() {
    m_betListWinsOnly = false;
    requestRecentBetList();
    var fRatio = m_objUser.mb_game_pb_ratio;
    fRatio = fRatio / 100.0;
    $("#el-dialog-bethistory-ratio-id").text(fRatio.toFixed(5));

    $("#el-dialog-bethistory-id").fadeIn(300);

}

function showBetHistoryDlgData(arrBetData) {
    var tHtml = "";
    if (arrBetData != null && arrBetData.length > 0) {
        for (var idx in arrBetData) {
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
            tHtml += "<td><div class=\"cell\">";
            tHtml += arrBetData[idx].bet_mb_uid;
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            tHtml += arrBetData[idx].bet_time;
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            tHtml += getBetDetail(arrBetData[idx].bet_mode);
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            if (arrBetData[idx].bet_state == 1)
                tHtml += "대기중";
            else if (arrBetData[idx].bet_state == 2)
                tHtml += "미적중";
            else if (arrBetData[idx].bet_state == 3)
                tHtml += "적중";
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            tHtml += parseInt(arrBetData[idx].bet_money).toLocaleString();
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            tHtml += (parseInt(arrBetData[idx].bet_before_money) - parseInt(arrBetData[idx].bet_money)).toLocaleString();
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            tHtml += arrBetData[idx].bet_ratio;
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            tHtml += arrBetData[idx].bet_empl_amount;
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            tHtml += parseInt(arrBetData[idx].bet_win_money).toLocaleString();
            tHtml += "</div></td>";

            tHtml += "</tr>";
        }
    }

    $("#el-dialog-bethistory-data-id").html(tHtml);
    if (tHtml.length < 1)
        $("#el-dialog-bethistory-empty-id").show();
    else
        $("#el-dialog-bethistory-empty-id").hide();
}


/*=============CharegeDialog=============== */

function showChargeDlg() {
    initChargeDlg();
    $("#el-dialog-charge-id").fadeIn(300);
    requestChargeHistory();
}

function selectChargeAmount(nAmount) {
    if (nAmount < 1)
        return;

    var nCurAmount = $("#el-dialog-charge-amount-id").val();
    if (nCurAmount.length < 1)
        nCurAmount = 0;
    nCurAmount = parseInt(nCurAmount);
    nCurAmount += nAmount;

    $("#el-dialog-charge-amount-id").val(nCurAmount);
}

function initChargeDlg() {
    $("#el-dialog-charge-amount-id").val('');
    if (m_objUser) {
        $("#el-dialog-charge-name-id").val(getBetCustomerName());
    } else {
        $("#el-dialog-charge-name-id").val('');
    }
}

function showChargeDlgData(arrChargeData) {
    var tHtml = "";
    if (arrChargeData != null && arrChargeData.length > 0) {
        for (var idx in arrChargeData) {
            tHtml += " <tr class=\"el-table__row\">";
            tHtml += "<td><div class=\"cell\">";
            tHtml += parseInt(arrChargeData[idx].charge_money).toLocaleString();
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            if (arrChargeData[idx].charge_action_state == 1) {
                tHtml += "<span class=\"el-tag el-tag--warning el-tag--light\">미확인</span>"
            } else if (arrChargeData[idx].charge_action_state == 2) {
                tHtml += "<span class=\"el-tag el-tag--success el-tag--light\">확인</span>";
            } else if (arrChargeData[idx].charge_action_state == 3) {
                tHtml += "<span class=\"el-tag el-tag--danger el-tag--light\">취소됨</span>";
            }
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            tHtml += arrChargeData[idx].charge_time_require;
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            if (arrChargeData[idx].charge_action_state != 1) {
                tHtml += arrChargeData[idx].charge_time_process;
            }
            tHtml += "</div></td></tr>";
        }
    }

    $("#el-dialog-charge-data-id").html(tHtml);
    if (tHtml.length < 1)
        $("#el-dialog-charge-empty-id").show();
    else
        $("#el-dialog-charge-empty-id").hide();
}



/*=============DischaregeDialog=============== */


function showDischargeDlg() {
    initDischargeDlg();
    $("#el-dialog-discharge-id").fadeIn(300);
    requestDischargeHistory();
}


function selectDischargeAmount(nAmount) {
    if (nAmount < 1)
        return;

    var nCurAmount = $("#el-dialog-discharge-amount-id").val();
    if (nCurAmount.length < 1)
        nCurAmount = 0;
    nCurAmount = parseInt(nCurAmount);
    nCurAmount += nAmount;

    $("#el-dialog-discharge-amount-id").val(nCurAmount);
}

function initDischargeDlg() {
    $("#el-dialog-discharge-amount-id").val('');
    $("#el-dialog-discharge-bank-id").val('');
    $("#el-dialog-discharge-number-id").val('');
    $("#el-dialog-discharge-owner-id").val('');

}

function showDischargeDlgData(arrChargeData) {
    var tHtml = "";
    if (arrChargeData != null && arrChargeData.length > 0) {
        for (var idx in arrChargeData) {
            tHtml += " <tr class=\"el-table__row\">";
            tHtml += "<td><div class=\"cell\">";
            tHtml += parseInt(arrChargeData[idx].exchange_money).toLocaleString();
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            if (arrChargeData[idx].exchange_action_state == 1) {
                tHtml += "<span class=\"el-tag el-tag--warning el-tag--light\">미확인</span>"
            } else if (arrChargeData[idx].exchange_action_state == 2) {
                tHtml += "<span class=\"el-tag el-tag--success el-tag--light\">확인</span>";
            } else if (arrChargeData[idx].exchange_action_state == 3) {
                tHtml += "<span class=\"el-tag el-tag--danger el-tag--light\">취소됨</span>";
            }
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            tHtml += arrChargeData[idx].exchange_time_require;
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            if (arrChargeData[idx].exchange_action_state != 1) {
                tHtml += arrChargeData[idx].exchange_time_process;
            }
            tHtml += "</div></td></tr>";
        }
    }

    $("#el-dialog-discharge-data-id").html(tHtml);
    if (tHtml.length < 1)
        $("#el-dialog-dixcharge-empty-id").show();
    else
        $("#el-dialog-discharge-empty-id").hide();
}



/*=============MileageDialog=============== */


function showMileageDlg() {
    initMileageDlg();
    $("#el-dialog-mileage-id").fadeIn(300);
    requestMileageHistory();
}

function selectMileageAmount() {

    $("#el-dialog-mileage-input-id").val(m_objUser.mb_point);
}

function initMileageDlg() {
    $("#el-dialog-mileage-amount-id").text(m_objUser.mb_point);

    $("#el-dialog-mileage-input-id").val('');

}



function showMileageDlgData(arrMileageData) {
    var tHtml = "";
    if (arrMileageData != null && arrMileageData.length > 0) {
        for (var idx in arrMileageData) {
            tHtml += " <tr class=\"el-table__row\">";
            tHtml += "<td><div class=\"cell\">";
            tHtml += parseInt(arrMileageData[idx].money_amount).toLocaleString();
            tHtml += "</div></td>";
            tHtml += "<td><div class=\"cell\">";
            tHtml += arrMileageData[idx].money_update_time;
            tHtml += "</div></td></tr>";
        }
    }

    $("#el-dialog-mileage-data-id").html(tHtml);
    if (tHtml.length < 1)
        $("#el-dialog-mileage-empty-id").show();
    else
        $("#el-dialog-mileage-empty-id").hide();
}


/*=============NicknameDialog=============== */


function showNicknameDlg() {

    initNicknameDlg();

    $("#el-dialog-nickname-id").fadeIn(300);

}

function initNicknameDlg() {
    for (var i = 1; i <= 10; i++) {
        $("#el-dialog-nickname-input-id" + i.toString()).val('');
    }
    if (!m_objUser) return;
    var arrUser = [];
    if (m_objUser.mb_user != null && String(m_objUser.mb_user).length > 0) {
        arrUser = String(m_objUser.mb_user).split("#");
    }
    var hasAny = false;
    for (var i = 0; i < arrUser.length && i < 10; i++) {
        if (arrUser[i].length > 0) {
            $("#el-dialog-nickname-input-id" + (i + 1).toString()).val(arrUser[i]);
            hasAny = true;
        }
    }
    if (!hasAny && getBetCustomerName().length > 0) {
        $("#el-dialog-nickname-input-id1").val(getBetCustomerName());
    }
}

/*=============MessageDialog=============== */


function showMessageDlg() {

    $("#el-dialog-message-id").fadeIn(300);
    initMessageDlg();
}

function initMessageDlg() {
    var elTab = $(".el-tabs__nav .is-active");

    if (elTab.length > 0) {
        var iIndex = $(elTab).attr("index");
        if (iIndex == 1) {
            requestRecvMessages();
        } else if (iIndex == 2) {
            requestSendMessages();
        }
    }
}

function initMessageContent() {
    $("#el-dialog-message-title-id").val('');
    $("#el-dialog-message-content-id").val('');
}

function selectMessageTab(elTab) {
    $(".el-tabs__item").removeClass("is-active");
    $(".el-tab-pane").hide();

    var iIndex = $(elTab).attr("index");
    if (iIndex == 1) {
        $(".el-tabs__active-bar").attr("style", "width: 75px; transform: translateX(0px);");
        $("#el-dialog-message-tab1-id").show();
        requestRecvMessages();
    } else if (iIndex == 2) {
        $(".el-tabs__active-bar").attr("style", "width: 75px; transform: translateX(118px);");
        $("#el-dialog-message-tab2-id").show();
        requestSendMessages();
    } else if (iIndex == 3) {
        $(".el-tabs__active-bar").attr("style", "width: 75px; transform: translateX(232px);");
        $("#el-dialog-message-tab3-id").show();
    }
    $(elTab).addClass("is-active");
}

function showMessageDlgData(iType, arrMsgData) {
    if (iType == 0) { //Receive Messages   
        var tHtml = "";
        if (arrMsgData != null && arrMsgData.length > 0) {
            for (var idx in arrMsgData) {
                tHtml += " <tr class=\"el-table__row\">";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrMsgData[idx].notice_send_uid;
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrMsgData[idx].notice_time_create;
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrMsgData[idx].notice_title;
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrMsgData[idx].notice_content;
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += "<button type=\"button\" onclick=\"requestDeleteMessage(0, " + arrMsgData[idx].notice_fid + ");\"";
                tHtml += " class=\"el-button el-button--danger el-button--small\"> <span>삭 제</span> </button>";
                tHtml += "</div></td></tr>";
            }
        }

        $("#el-dialog-message-recvdata-id").html(tHtml);
        if (tHtml.length < 1) {
            $("#el-dialog-message-empty1-id").show();
            $("#el-dialog-message-recvtb-id").hide();
        } else {
            $("#el-dialog-message-empty1-id").hide();
            $("#el-dialog-message-recvtb-id").show();
        }
    } else if (iType == 1) { //Send Messages
        var tHtml = "";
        if (arrMsgData != null && arrMsgData.length > 0) {
            for (var idx in arrMsgData) {
                tHtml += " <tr class=\"el-table__row\">";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrMsgData[idx].notice_recv_uid;
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrMsgData[idx].notice_time_create;
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrMsgData[idx].notice_title;
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += arrMsgData[idx].notice_content;
                tHtml += "</div></td>";
                tHtml += "<td><div class=\"cell\">";
                tHtml += "<button type=\"button\" onclick=\"requestDeleteMessage(1, " + arrMsgData[idx].notice_fid + ");\"";
                tHtml += " class=\"el-button el-button--danger el-button--small\"> <span>삭 제</span> </button>";
                tHtml += "</div></td></tr>";
            }
        }

        $("#el-dialog-message-senddata-id").html(tHtml);
        if (tHtml.length < 1) {
            $("#el-dialog-message-empty2-id").show();
            $("#el-dialog-message-sendtb-id").hide();
        } else {
            $("#el-dialog-message-empty2-id").hide();
            $("#el-dialog-message-sendtb-id").show();
        }
    }
}


/*=============EmpInfoDialog=============== */

function logout() {
    location.href = "/home/logout" + location.search;
}

function showEmpInfoDlg() {
    initEmpInfoDlg();
    $("#el-dialog-empinfo-id").fadeIn(300);
}


function initEmpInfoDlg() {
    $("#el-dialog-empinfo-pwd-id").val('');
    $("#el-dialog-empinfo-newpwd-id1").val('');
    $("#el-dialog-empinfo-newpwd-id2").val('');
    $("#el-dialog-empinfo-nickname-id").val(m_objUser.mb_uid);
    $("#el-dialog-empinfo-print-id").prop('checked', m_objUser.mb_state_print == 1 ? true : false);
}

/*=============EmpInfoDialog=============== */

function saveToPDF(objBetInfo) {

    if (objBetInfo == null)
        return;
    /*
    $("#el-pdf-time-id").text(  "구매날짜 :  "+objBetInfo.bet_time);
    $("#el-pdf-round-id").text( "추첨회차 :  "+objBetInfo.bet_round_fid);
    $("#el-pdf-num-id").text(   "일회차 :    "+objBetInfo.bet_round_no);
    $("#el-pdf-name-id").text(  "구매자 :    "+objBetInfo.bet_mb_uid);
    $("#el-pdf-mode-id").text(  "게임종류 :  "+getBetDetail(objBetInfo.bet_mode));
    $("#el-pdf-ratio-id").text( "배당율 :    "+objBetInfo.bet_ratio);
    $("#el-pdf-amount-id").text("포인트 :    "+parseInt(objBetInfo.bet_money).toLocaleString());
    $("#el-pdf-win-id").text(   "예상포인트 : "+parseInt(objBetInfo.bet_ratio * objBetInfo.bet_money).toLocaleString());
    */


    /*

	    127.0.0.1:8000	/print?
            round=1065322
            &customer=%EA%B9%80%EC%82%AC%EC%9E%A5
            &betid=802
            &betname=%EC%A7%9D
            &money=10000
            &rate=1.93
            &tround=144
            &usernm=111
            &color=blue
            &total=19300
            &userid=80024				

        */
    let strRound = "",
        strBetName = "",
        strColor = "";

    if (m_objRound.game == 1) {
        strRound = objBetInfo.bet_round_no;
        strBetName = getBetDetail(objBetInfo.bet_mode);
        strColor = "red";
    }else if (m_objRound.game == 2) {
        strRound = objBetInfo.bet_round_no;
        strBetName = getBetDetail(objBetInfo.bet_mode);
        strColor = "red";
    } else {
        strRound = objBetInfo.bet_round_fid;
        strBetName = getBetDetail(objBetInfo.bet_mode);
        strColor = "red";
    }

    var strUrl = "http://127.0.0.1:8000/print?";
    strUrl += "round=" + strRound;
    strUrl += "&customer=" + objBetInfo.bet_mb_uid;
    strUrl += "&betid=" + objBetInfo.bet_fid;
    strUrl += "&betname=" + strBetName;
    strUrl += "&money=" + objBetInfo.bet_money;
    strUrl += "&rate=" + objBetInfo.bet_ratio;
    strUrl += "&tround=" + objBetInfo.bet_round_no;
    strUrl += "&usernm=" + objBetInfo.bet_mb_uid;
    strUrl += "&color=" + strColor;
    strUrl += "&total=" + parseInt(objBetInfo.bet_ratio * objBetInfo.bet_money);
    strUrl += "&userid=" + m_objUser.mb_fid;

    /*
    var data = {};
    data.round = objBetInfo.bet_round_fid;
    data.customer = objBetInfo.bet_mb_uid;
    data.betid = objBetInfo.bet_fid;
    data.betname = getBetDetail(objBetInfo.bet_mode);
    data.money = objBetInfo.bet_money;
    data.rate = objBetInfo.bet_ratio;
    data.tround = objBetInfo.bet_round_no;
    data.usernm = objBetInfo.bet_mb_uid;
    data.color = objBetInfo.red;
    data.total = parseInt(objBetInfo.bet_ratio * objBetInfo.bet_money);



    var strUrl = "http://127.0.0.1:8000/print?";
    strUrl += "round=" + objBetInfo.bet_round_fid;
    strUrl += "&customer=" + objBetInfo.bet_mb_uid;
    strUrl += "&betid=" + objBetInfo.bet_fid;
    strUrl += "&betname=" + getBetDetail(objBetInfo.bet_mode);
    strUrl += "&money=" + objBetInfo.bet_money;
    strUrl += "&rate=" + objBetInfo.bet_ratio;
    strUrl += "&tround=" + objBetInfo.bet_round_no;
    strUrl += "&usernm=" + getBetCustomerName();
    strUrl += "&color=red";
    strUrl += "&total=" + parseInt(objBetInfo.bet_ratio * objBetInfo.bet_money);
    strUrl += "&userid=" + m_objUser.mb_fid;
    */

    $.get(strUrl, function(data) {
        //console.log(data);          

    }).fail(function(jqXHR, textStatus, errorThrown) {
        //console.log('getRequest failed! ' + textStatus); 
    });

}

function speak(text, opt_prop) {
    if (typeof SpeechSynthesisUtterance === "undefined" || typeof window.speechSynthesis === "undefined") {
        //alert("이 브라우저는 음성 합성을 지원하지 않습니다.");
        return;
    }

    window.speechSynthesis.cancel(); // 현재 읽고있다면 초기화

    const prop = opt_prop; // {}

    const speechMsg = new SpeechSynthesisUtterance();
    speechMsg.rate = prop.rate; // 1 // 속도: 0.1 ~ 10      
    speechMsg.pitch = prop.pitch; // 1 // 음높이: 0 ~ 2
    speechMsg.lang = "ko-KR"; //prop.lang ;// "ko-KR"
    speechMsg.text = text;

    // SpeechSynthesisUtterance에 저장된 내용을 바탕으로 음성합성 실행
    window.speechSynthesis.speak(speechMsg);
}