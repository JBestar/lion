<?php

  //회차번호로부터 회차시작시간과 마감시간, 계산하는 함수-파워볼, 파워사다리
    function getPbRoundTimes($objRoundInfo, $gameId){
      // date_default_timezone_set('Asia/Seoul');
      
      $nRoundNo = $objRoundInfo->round_num;
      $nSumMinutes = $nRoundNo * 5 ;
      $nHour = $nSumMinutes / 60;
      $nHour = (int)$nHour;
      $nMinute = $nSumMinutes % 60;
      
      $arrRoundInfo['round_no'] =  $objRoundInfo->round_num;
      //날자는 이전회차 날자
      $strNowDate = $objRoundInfo->round_date;
      //회차 시작시간설정      
      $strRoundEnd = $strNowDate." ".$nHour.":".$nMinute.":"."0";
      $tmRoundEnd = strtotime($strRoundEnd);
      if($gameId == GAME_POWERBALL)
        $tmRoundEnd = strtotime("-".TM_OFFSET." seconds", $tmRoundEnd);
      
      $arrRoundInfo['round_end'] = date("Y-m-d H:i:s", $tmRoundEnd);
      //회차 시작시간설정
      $tmRoundStart = strtotime("-5 minutes", $tmRoundEnd);
      $arrRoundInfo['round_start'] = date("Y-m-d H:i:s", $tmRoundStart);
      
      return $arrRoundInfo; 
    }
    


     //이전회차번호, 날자 계산하는 함수-파워볼, 파워사다리
    function getPbBeforeRoundInfo($gameId){

      //$tmNow = mktime('23','59','40','6','8','2021') + TM_OFFSET;
      $tmNow = time() ;
      if($gameId == GAME_POWERBALL)
        $tmNow += TM_OFFSET;
      
      $nYear = date("Y",$tmNow);
      $nMonth = date("m",$tmNow);
      $nDay = date("d",$tmNow);

      $nHour = date("G",$tmNow);
      $nMin = date("i",$tmNow);
      
      $nSumMinutes = $nHour * 60 + $nMin ;
      $nRoundNo = floor($nSumMinutes / 5) ;
      $nRoundNo = $nRoundNo % 288 ;
      if($nRoundNo == 0) {
        $nRoundNo = 288;
        $strDate = date('Y-m-d', strtotime("-1 day", $tmNow));
      } else 
        $strDate = date( 'Y-m-d', $tmNow );
      
      $arrRoundInfo['round_no'] = $nRoundNo;
      $arrRoundInfo['round_date'] = $strDate;

      return $arrRoundInfo;
    }

    
    //전전회차번호, 날자 계산하는 함수-파워볼, 파워사다리
    function getPbLastRoundInfo($gameId){

      //$tmNow = mktime('23','59','40','6','8','2021') + TM_OFFSET;
      $tmNow = time() ;
      if($gameId == GAME_POWERBALL)
        $tmNow += TM_OFFSET;
      
      $nYear = date("Y",$tmNow);
      $nMonth = date("m",$tmNow);
      $nDay = date("d",$tmNow);

      $nHour = date("G",$tmNow);
      $nMin = date("i",$tmNow);
      
      $nSumMinutes = $nHour * 60 + $nMin -5;
      $nRoundNo = floor($nSumMinutes / 5) ;
      $nRoundNo = $nRoundNo % 288 ;
      if($nRoundNo < 1) {
        $nRoundNo = 288 + $nRoundNo;
        $strDate = date('Y-m-d', strtotime("-1 day", $tmNow));        
      } 
      else 
        $strDate = date( 'Y-m-d', $tmNow );
      
      
      $arrRoundInfo['round_no'] = $nRoundNo;
      $arrRoundInfo['round_date'] = $strDate;

      return $arrRoundInfo;
    }


    /*

    //회차번호로부터 회차시작시간과 마감시간, 계산하는 함수-키노사다리
    function getKsRoundTimes($objRoundInfo){
      date_default_timezone_set('Asia/Seoul');
      
      $nRoundNo = $objRoundInfo->round_num;
      $nSumMinutes = $nRoundNo * 5 ;
      $nHour = $nSumMinutes / 60;
      $nHour = (int)$nHour;
      $nMinute = $nSumMinutes % 60;
      
      $arrRoundInfo['round_no'] =  $objRoundInfo->round_num;
      //날자는 이전회차 날자
      $strNowDate = $objRoundInfo->round_date;
      //회차 시작시간설정      
      $strRoundEnd = $strNowDate." ".$nHour.":".$nMinute.":"."0";
      $tmRoundEnd = strtotime($strRoundEnd);
      $arrRoundInfo['round_end'] = date("Y-m-d H:i:s", $tmRoundEnd);
      //회차 시작시간설정
      $tmRoundStart = strtotime("-5 minutes", $tmRoundEnd);
      $arrRoundInfo['round_start'] = date("Y-m-d H:i:s", $tmRoundStart);
      
      return $arrRoundInfo; 
    }

    //회차번호, 회차시작시간과 마감시간, 배팅마감시간 계산하는 함수-파워볼, 파워사다리
    function getPbRoundInfo(){

      $tmNow = time();
      $nYear = date("Y",$tmNow);
      $nMonth = date("m",$tmNow);
      $nDay = date("d",$tmNow);

      $nHour = date("G",$tmNow);
      $nMin = date("i",$tmNow);
      
      $nSumMinutes = $nHour * 60 + $nMin + 2;
      $nRoundNo = floor($nSumMinutes / 5) ;
      $nRoundNo = $nRoundNo % 288 + 1;
      $arrRoundInfo['round_no'] = $nRoundNo;

      $strDate = "";
      if($nSumMinutes < 1440){
        $strDate = date( 'Y-m-d', $tmNow );
      }
      else {
        $strDate = date('Y-m-d', strtotime("+1 day", $tmNow));
      }

      $arrRoundInfo['round_date'] = $strDate;

      $nSumMinutes = $nRoundNo * 5 - 2;
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



    //회차번호, 회차시작시간과 마감시간, 배팅마감시간 계산하는 함수-키노사다리
    function getKsRoundInfo(){

      $tmNow = time();
      $nYear = date("Y",$tmNow);
      $nMonth = date("m",$tmNow);
      $nDay = date("d",$tmNow);

      $nHour = date("G",$tmNow);
      $nMin = date("i",$tmNow);
      
      $nSumMinutes = $nHour * 60 + $nMin ;
      $nRoundNo = floor($nSumMinutes / 5) ;
      $nRoundNo = $nRoundNo % 288 + 1;
      $arrRoundInfo['round_no'] = $nRoundNo;

      $strDate = "";
      if($nSumMinutes < 1440){
        $strDate = date( 'Y-m-d', $tmNow );
      }
      else {
        $strDate = date('Y-m-d', strtotime("+1 day", $tmNow));
      }

      $arrRoundInfo['round_date'] = $strDate;

      $nSumMinutes = $nRoundNo * 5 ;
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

     //이전회차번호, 날자 계산하는 함수-키노사다리
    function getKsLastRoundInfo(){

      $tmNow = time();
      $nYear = date("Y",$tmNow);
      $nMonth = date("m",$tmNow);
      $nDay = date("d",$tmNow);

      $nHour = date("G",$tmNow);
      $nMin = date("i",$tmNow);
      
      $nSumMinutes = $nHour * 60 + $nMin + 1;
      $nRoundNo = floor($nSumMinutes / 5) ;
      $nRoundNo = $nRoundNo % 288 ;
      if($nRoundNo == 0) $nRoundNo = 288;

      $arrRoundInfo['round_no'] = $nRoundNo;

      $strDate = "";
      if($nSumMinutes >= 5 ){
        $strDate = date( 'Y-m-d', $tmNow );
      }
      else {
        $strDate = date('Y-m-d', strtotime("-1 day", $tmNow));
      }

      $arrRoundInfo['round_date'] = $strDate;

      return $arrRoundInfo;
    }
    
    function getDiffMoney($nMoney1, $nMoney2, $nMin, &$nBetSum){

      $strRes = "";

      if( $nMoney1 - $nMoney2 >= $nMin ){
        $nMoney = $nMoney1 - $nMoney2;
        $nBetSum += $nMoney;
        $strRes .= $nMoney;
        $strRes .= "|0";

      } else if($nMoney2 - $nMoney1 >= $nMin){
        $nMoney = $nMoney2 - $nMoney1;
        $nBetSum += $nMoney;
        $strRes .= "0|";
        $strRes .= $nMoney;     
      } else {
        $strRes .= "0|0";

      }

      return $strRes;
    }

    function getDiffMoney2($nMoney, $nMin, &$nBetSum){
      $strRes = "";
      
      if($nMoney >= $nMin){
        $nBetSum += $nMoney;
        $strRes .= $nMoney;
      } else {
        $strRes .= "0";
      }

      return $strRes;

    } 
    */

?>