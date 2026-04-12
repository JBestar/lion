<?php

  	function is_login(){ 

      if(!isset($_SESSION['logged_in']))
        return false;
      else if($_SESSION['logged_in']==TRUE)
        return true;
      else return false;  	  	
  	}

    function is_Mobile(){
      $useragent=$_SERVER['HTTP_USER_AGENT'];
      if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
        return true;
      return false;
    }
    

    //사이드바의 선택상태 초기화 배렬을 반환해주는 함수
    function getSidebarArray(){

      return array(
               'menuitem_1' => '',
               'menuitem_2' => '',
               'menuitem_3' => '',
               'menuitem_4' => '',
               'menuitem_5' => '',
               'menuitem_6' => '',
               'menuitem_7' => '',
               'menuitem_8' => '',
               'menuitem_9' => ''
          );

    }

    /**
     * PBG 일회차 번호·날짜 — reground_LT getLastRoundInfo(ROUND_5MIN) 과 동일.
     * 00:00~00:04 → 전일 288회차, 00:05~ → floor(당일0시~분/5) 는 1..287.
     */
    function pballRoundSlotFloorFromTime($tmNow) {
      date_default_timezone_set('Asia/Seoul');
      $nHour = date("G", $tmNow);
      $nMin = date("i", $tmNow);
      $nSumMinutes = (int) ($nHour * 60 + $nMin);
      $roundMin = 5;
      $nRoundMax = (int) floor(1440 / $roundMin);
      $nRoundNo = (int) floor($nSumMinutes / $roundMin);
      if ($nRoundNo == 0) {
        $nRoundNo = $nRoundMax;
        $strDate = date('Y-m-d', strtotime("-1 day", $tmNow));
      } else {
        $strDate = date('Y-m-d', $tmNow);
      }
      return array(
        'round_no' => $nRoundNo,
        'round_date' => $strDate,
        'nSumMinutes' => $nSumMinutes,
        'nRoundMax' => $nRoundMax
      );
    }

    /** 해당 일회차 슬롯의 마감 시각(당일 0시 기준 분) — 구 %288+1 체계의 round_no*5 와 동일 물리 시각 */
    function pballRoundEndMinutesFromMidnight($nRoundNo) {
      $nRoundNo = (int) $nRoundNo;
      $nEnd = ($nRoundNo + 1) * 5;
      return ($nEnd > 1440) ? 1440 : $nEnd;
    }

    //회차시작시간과 마감시간, 배팅초과시간 계산하는 함수-파워볼, 파워사다리
    function getPballRoundTimes($objConfPb){

      date_default_timezone_set('Asia/Seoul');
      //$tmNow = mktime('23','59','40','5','25','2021')+TM_OFFSET;
      $tmNow = time();
      // if($objConfPb->game_index == GAME_POWERBALL)
      //   $tmNow += TM_OFFSET;

      $slot = pballRoundSlotFloorFromTime($tmNow);
      $nRoundNo = $slot['round_no'];
      $strDate = $slot['round_date'];
      $arrRoundInfo['round_no'] = $nRoundNo;
      $arrRoundInfo['round_date'] = $strDate;

      $nSumMinutes = pballRoundEndMinutesFromMidnight($nRoundNo);
      $nHour = $nSumMinutes / 60;
      $nHour = floor($nHour);
      $nMinute = $nSumMinutes % 60;

      //현재시간설정      
      $tmRoundCurrent = date("Y-m-d H:i:s", $tmNow);        
      $arrRoundInfo['round_current'] = $tmRoundCurrent;

      //회차 마감시간설정
      $strRoundEnd = $strDate." ".$nHour.":".$nMinute.":"."0";
      $tmRoundEnd = strtotime($strRoundEnd);
      $arrRoundInfo['round_end'] = date("Y-m-d H:i:s", $tmRoundEnd);
      
      //회차 시작시간설정
      $tmRoundStart = strtotime("-5 minutes", $tmRoundEnd);
      $arrRoundInfo['round_start'] = date("Y-m-d H:i:s", $tmRoundStart);
      
      $tmBetEnd = 0;
      //베팅 마감시간설정
      if($objConfPb->game_bet_permit != 1){
        $tmBetEnd = $tmRoundStart;
      } else if($objConfPb->game_time_countdown >= 20 && $objConfPb->game_time_countdown <= 250 ) {
        //$objConfPb->game_time_countdown += 5;
        $tmBetEnd = strtotime("-".$objConfPb->game_time_countdown." seconds", $tmRoundEnd);      
      } else $tmBetEnd = strtotime("-1 minutes", $tmRoundEnd); 

      $arrRoundInfo['round_bet_end'] = date("Y-m-d H:i:s", $tmBetEnd);
      
      return $arrRoundInfo;
    }

    

    //회차번호로부터 회차시작시간과 마감시간, 배팅초과시간 계산하는 함수-파워볼, 파워사다리
    function getPballRoundInfo($gameId){

      date_default_timezone_set('Asia/Seoul');
      //$tmNow = mktime('23','58','10','5','25','2021');
      $tmNow = time();
      // if($gameId == GAME_POWERBALL)
      //   $tmNow += TM_OFFSET;

      $slot = pballRoundSlotFloorFromTime($tmNow);
      $nRoundNo = $slot['round_no'];
      $strDate = $slot['round_date'];
      $arrRoundInfo['round_no'] = $nRoundNo;
      $arrRoundInfo['round_date'] = $strDate;

      $nSumMinutes = pballRoundEndMinutesFromMidnight($nRoundNo);
      $nHour = $nSumMinutes / 60;
      $nHour = floor($nHour);
      $nMinute = $nSumMinutes % 60;

      //현재시간설정      
      $tmRoundCurrent = date("Y-m-d H:i:s", $tmNow);        
      $arrRoundInfo['round_current'] = $tmRoundCurrent;

      //회차 마감시간설정
      $strRoundEnd = $strDate." ".$nHour.":".$nMinute.":"."0";
      $tmRoundEnd = strtotime($strRoundEnd);
      $arrRoundInfo['round_end'] = date("Y-m-d H:i:s", $tmRoundEnd);
      
      //회차 시작시간설정
      $tmRoundStart = strtotime("-5 minutes", $tmRoundEnd);
      $arrRoundInfo['round_start'] = date("Y-m-d H:i:s", $tmRoundStart);
      
      return $arrRoundInfo;
    }
    
    function getPbLastRoundInfo(){

      $tmNow = time()+TM_OFFSET;
      $slot = pballRoundSlotFloorFromTime($tmNow);
      $nRoundNo = $slot['round_no'] - 1;
      if ($nRoundNo == 0) {
        $nRoundNo = $slot['nRoundMax'];
        $strDate = date('Y-m-d', strtotime("-1 day", $tmNow));
      } else {
        $strDate = $slot['round_date'];
      }

      $arrRoundInfo['round_no'] = $nRoundNo;
      $arrRoundInfo['round_date'] = $strDate;


      return $arrRoundInfo;
    }
    
    //회차번호로부터 배팅가능성 계산하는 함수
    function isEnablePbBet(&$arrBetData, $objUser, $objConfPb, $arrRoundData, $arrBetStatist){

      //0:오유 1:정상 2:유저베팅차단 3:전체베팅차단 4:최소금액오류 5:최대금액오류 6:보유머니 부족 7:회차오류 8:단폴한도 9:조합한도 10:회차한도
      if(is_null( $objConfPb)) return 0;
      if(is_null( $objUser)) return 0;

      if (!array_key_exists("roundno", $arrBetData)) return 0;

      //if(is_null($arrRoundData)) return 7;
      //if($objRoundInfo->round_fid != $arrBetData['roundid']) return 7;
      //if($objRoundInfo->round_num != $arrBetData['roundno']) return 7;

      //게임 베팅가능성
      if($objConfPb->game_bet_permit == 0) return 3;

      //유저 베팅 가능성
      if($objUser->mb_state_delete == 1 || $objUser->emp_state_active != 1) 
        return 2;
      
      //배팅요청정보 검사
      if($arrBetData['roundid'] < 1 ) return 0;
      if($arrBetData['mode'] < 1 || $arrBetData['mode'] > 48) return 0;
      if(strlen($arrBetData['name']) < 1) return 0;
      if($arrBetData['amount'] < 1) return 0;

      //금액 조건 검사
      $arrBetData['amount'] = (int)($arrBetData['amount']);
      if($arrBetData['amount'] > $objUser->mb_money)  return 6;
      if($arrBetData['amount'] < $objConfPb->game_min_bet_money)  return 4;
      //if($arrBetData['amount'] > $objConfPb->game_max_bet_money)  return 5;

      
      $tmRoundCurrent = strtotime($arrRoundData['round_current']);
      $tmRoundStart = strtotime($arrRoundData['round_start']);
      $tmRoundEnd = strtotime("+5 minutes", $tmRoundStart);
      //베팅 마감시간
      if($objConfPb->game_time_countdown >= 20)
      {
        $tmRoundBetEnd = strtotime("-".$objConfPb->game_time_countdown." seconds", $tmRoundEnd);      
      } else $tmRoundBetEnd = strtotime("-30 seconds", $tmRoundEnd);      
    
      //현재 회차가 배팅가능한 시간이 아니라면   
      if($tmRoundCurrent < $tmRoundStart || $tmRoundCurrent > $tmRoundBetEnd){
          return 0;        
      }

      $strRatio = "0";
      $nMode = (int)($arrBetData['mode']);
      $bMix = false;
      switch ($nMode) {
        case 1: $strRatio = $objConfPb->game_ratio_1; $arrBetData['target']="P"; break;
        case 2: $strRatio = $objConfPb->game_ratio_1; $arrBetData['target']="B"; break;
        case 3: $strRatio = $objConfPb->game_ratio_2; $arrBetData['target']="P"; break;
        case 4: $strRatio = $objConfPb->game_ratio_2; $arrBetData['target']="B"; break;
        case 5: $strRatio = $objConfPb->game_ratio_5; $arrBetData['target']="PP"; $bMix=true; break;
        case 6: $strRatio = $objConfPb->game_ratio_6; $arrBetData['target']="BP"; $bMix=true; break;
        case 7: $strRatio = $objConfPb->game_ratio_7; $arrBetData['target']="PB"; $bMix=true; break;
        case 8: $strRatio = $objConfPb->game_ratio_8; $arrBetData['target']="BB"; $bMix=true; break;
        case 9: $strRatio = $objConfPb->game_ratio_3; $arrBetData['target']="P"; break;
        case 10: $strRatio = $objConfPb->game_ratio_3; $arrBetData['target']="B"; break;
        case 11: $strRatio = $objConfPb->game_ratio_4; $arrBetData['target']="P"; break;
        case 12: $strRatio = $objConfPb->game_ratio_4; $arrBetData['target']="B"; break;
        case 13: $strRatio = $objConfPb->game_ratio_9; $arrBetData['target']="PP"; $bMix=true; break;
        case 14: $strRatio = $objConfPb->game_ratio_10; $arrBetData['target']="BP"; $bMix=true; break;
        case 15: $strRatio = $objConfPb->game_ratio_11; $arrBetData['target']="PB"; $bMix=true; break;
        case 16: $strRatio = $objConfPb->game_ratio_12; $arrBetData['target']="BB"; $bMix=true; break;
        case 17: $strRatio = $objConfPb->game_ratio_13; $arrBetData['target']="L"; break;
        case 18: $strRatio = $objConfPb->game_ratio_14; $arrBetData['target']="M"; break;
        case 19: $strRatio = $objConfPb->game_ratio_15; $arrBetData['target']="S"; break;
        case 20: $strRatio = $objConfPb->game_ratio_16; $arrBetData['target']="PL"; $bMix=true; break;
        case 21: $strRatio = $objConfPb->game_ratio_17; $arrBetData['target']="PM"; $bMix=true; break;
        case 22: $strRatio = $objConfPb->game_ratio_18; $arrBetData['target']="PS"; $bMix=true; break;
        case 23: $strRatio = $objConfPb->game_ratio_19; $arrBetData['target']="BL"; $bMix=true; break;
        case 24: $strRatio = $objConfPb->game_ratio_20; $arrBetData['target']="BM"; $bMix=true; break;
        case 25: $strRatio = $objConfPb->game_ratio_21; $arrBetData['target']="BS"; $bMix=true; break;
        case 30: 
        case 31:
        case 32:
        case 33:
        case 33:
        case 35:
        case 36:
        case 37:
        case 38:
        case 39: $strRatio = $objConfPb->game_ratio_23; $arrBetData['target'] = 'Q'; break;
        case 41:
        case 42:
        case 43:
        case 43:
        case 44:
        case 45:
        case 46:
        case 47: 
        case 48: $strRatio = $objConfPb->game_ratio_22; $arrBetData['target'] = 'PPP'; $bMix = true; break;
        default: break;
      }
      
      if($strRatio < 1)   return 0;
      $arrBetData['ratio'] = $strRatio;

      //단폴
      if(!$bMix && $objUser->mb_limit_single > 0){
          if( ($arrBetStatist[0] + $arrBetData['amount']) > $objUser->mb_limit_single )
              return 8;
      }
      //조합
      if($bMix && $objUser->mb_limit_mix > 0){
        if( ($arrBetStatist[1] + $arrBetData['amount']) > $objUser->mb_limit_mix )
            return 9;
      }
      //회차
      if($objUser->mb_limit_round > 0){
          if( ($arrBetStatist[2] + $arrBetData['amount']) > $objUser->mb_limit_round )
              return 10;
      }

      return 1;
    }


    function calcRoundId($objLastRound, &$arrRoundData) {
      $iResult = 0;   //0:비정상 1:정상
      if($objLastRound->round_date == $arrRoundData['round_date']){
        // 화면/베팅 round_id는 외부 회차(times)와 일치하도록 round_fid 기준으로 계산한다.
        $arrRoundData['round_id'] = $objLastRound->round_fid + $arrRoundData['round_no'] - $objLastRound->round_num;
        $iResult = 1;
        
      } else if($objLastRound->round_date < $arrRoundData['round_date']){
        
        $date1 = date_create($objLastRound->round_date);
        $date2 = date_create($arrRoundData['round_date']);
        
        $dtDiff = date_diff($date1, $date2);
        $nRoundDiff = $dtDiff->days*288 + $arrRoundData['round_no'] - $objLastRound->round_num;
        $arrRoundData['round_id'] = $objLastRound->round_fid + $nRoundDiff;
        if($nRoundDiff > 0 && $nRoundDiff < 300)
          $iResult = 1;
        
      } else {
        $arrRoundData['round_id'] = $objLastRound->round_fid + 1;
      }
      return $iResult;
    }


    function isNotValidSystemTm(){

      date_default_timezone_set('Asia/Seoul');
      
      $tmNow = time();
      
      $nHour = date("G",$tmNow);
      $nMin = date("i",$tmNow);

      $nMinSum = $nHour * 60 + $nMin;
      if($nMinSum >= 365)
        return true;
      return false;

    }
    
    function setChgRatio(&$arrBetData, $objConf){
      if(is_null($objConf))
        return false;
      $strRatio = "-1";
      $nMode = (int)($arrBetData['mode']);
      switch ($nMode) {
        case 1: $strRatio = $objConf->game_ratio_1; $arrBetData['mode'] = 2; $arrBetData['target'] = 'B'; break;
        case 2: $strRatio = $objConf->game_ratio_1; $arrBetData['mode'] = 1; $arrBetData['target'] = 'P'; break;
        case 3: $strRatio = $objConf->game_ratio_2; $arrBetData['mode'] = 4; $arrBetData['target'] = 'B'; break;
        case 4: $strRatio = $objConf->game_ratio_2; $arrBetData['mode'] = 3; $arrBetData['target'] = 'P'; break;
        
        case 9: $strRatio = $objConf->game_ratio_3; $arrBetData['mode'] = 10; $arrBetData['target'] = 'B'; break;
        case 10: $strRatio = $objConf->game_ratio_3; $arrBetData['mode'] = 9; $arrBetData['target'] = 'P'; break;
        case 11: $strRatio = $objConf->game_ratio_4; $arrBetData['mode'] = 12; $arrBetData['target'] = 'B'; break;
        case 12: $strRatio = $objConf->game_ratio_4; $arrBetData['mode'] = 11; $arrBetData['target'] = 'P'; break;
          
        default: break;
      }

      if(floatval($strRatio) < 0)   
        return false;
      $arrBetData['ratio'] = floatval($strRatio);
      return true;
    }

    function isEnableBetTime($arrRoundData){

      $tmCurrent = date("Y-m-d H:i:s", time()); 

      if($tmCurrent < $arrRoundData['round_start'] || $tmCurrent > $arrRoundData['round_bet_end']){
          return false;        
      }
      return true;
    }

    function writeLog($contenet){ 
    
      if(!LOG_WRITE)
          return;
  
      $tmNow = time() ;
      $nHour = date("G",$tmNow);
      $nMin = date("i",$tmNow);
      $nSec = date("s",$tmNow);
  
      $sDate = date( 'Y-m-d', $tmNow);
      $fLog = fopen(LOG_FILE.$sDate, "a") ;
  
      $tContent = "[".$nHour.":".$nMin.":".$nSec."] ".$contenet."\r\n";
  
      fputs($fLog, $tContent);
      fclose($fLog);
  }

  /** 로그인 요청 JSON 파싱 실패 (비밀번호 미기록) */
  function logLoginInvalidPayload($endpointLabel, $jsonLen, $jsonErrorMsg) {
      if (!LOG_WRITE) {
          return;
      }
      writeLog($endpointLabel . " FAIL invalid_payload json_len=" . $jsonLen . " json_error=" . $jsonErrorMsg);
  }

  /**
   * member_model->login 이 null일 때: 아이디 없음 vs 비밀번호 불일치 구분 (비밀번호 값은 기록하지 않음)
   */
  function logLoginCredentialFailure($endpointLabel, $member_model, $uid, $pwdLen) {
      if (!LOG_WRITE) {
          return;
      }
      $row = $member_model->getInfoByUid($uid);
      if (is_null($row)) {
          writeLog($endpointLabel . " FAIL auth uid=" . $uid . " pwd_len=" . $pwdLen . " reason=no_user");
      } else {
          writeLog($endpointLabel . " FAIL auth uid=" . $uid . " pwd_len=" . $pwdLen . " reason=password_not_match mb_level=" . $row->mb_level . " mb_state_delete=" . $row->mb_state_delete);
      }
  }
?>
