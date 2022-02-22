<?php
	
	  //자료기지 접속
	function connectDb($arrConfig){

	    $strDbHost="";
	    if(array_key_exists('db_host', $arrConfig)){
	        $strDbHost=$arrConfig['db_host'];
	    }
	      
	    $strDbUser="";
	    if(array_key_exists('db_user', $arrConfig)){
	        $strDbUser=$arrConfig['db_user'];
	    }
	      
	    $strDbPwd="";
	    if(array_key_exists('db_pwd', $arrConfig)){
	        $strDbPwd=$arrConfig['db_pwd'];
	    }

	    $strDbName="";
	    if(array_key_exists('db_name', $arrConfig)){
	        $strDbName=$arrConfig['db_name'];
	    }

	    $dbConn= new mysqli($strDbHost, $strDbUser, $strDbPwd, $strDbName);
	    
	    return $dbConn;
	}
	/*
	//파워볼 회차결과 얻어오기
	function fetchPballRound($snoopy)
	{
		$strNtryPballUrl = "http://ntry.com/data/json/games/powerball/result.json";
		$snoopy->fetch($strNtryPballUrl);
		$strResult = $snoopy->results;
		
		$arrResult = json_decode($strResult, true);
		return $arrResult;
		
	}

	//파워사다리 회차결과 얻어오기
	function fetchPladderRound($snoopy)
	{
		$strNtryPladderUrl = "http://ntry.com/data/json/games/power_ladder/result.json";
		$snoopy->fetch($strNtryPladderUrl);
		$strResult = $snoopy->results;
		
		$arrResult = json_decode($strResult, true);
		return $arrResult;
		
	}
	//키노사다리 회차결과 얻어오기
	function fetchKladderRound($snoopy)
	{
		$strNtryPladderUrl = "http://ntry.com/data/json/games/keno_ladder/result.json";
		$snoopy->fetch($strNtryPladderUrl);
		$strResult = $snoopy->results;
		
		$arrResult = json_decode($strResult, true);
		return $arrResult;
		
	}


    //카지노 게임리력 얻어오기
	function fetchCasinoHistory($snoopy, $arrLiveInfo, $nLastIdx)
	{
		$strCasinoUrl = "http://evolution.live99n.com/gh_history.html?";
		if(strlen($arrLiveInfo[0])>0){
			$strCasinoUrl .= "agentid=".$arrLiveInfo[0];	
		} else return null;
		$strCasinoUrl .= "&historyidx=".$nLastIdx;	

		$snoopy->fetch($strCasinoUrl);
		$strResult = $snoopy->results;
		
		$xmlData = false;
		$arrResult = array();

		if(isset($strResult))
			$xmlData=simplexml_load_string($strResult);
		
		if($xmlData !== false)
		{
			$jsonString = json_encode($xmlData);    
			$arrData = json_decode($jsonString, TRUE);
			$arrData = array_change_key_case($arrData,  CASE_LOWER);

			if(array_key_exists("playerbetdetail", $arrData)){
				$arrResult = $arrData['playerbetdetail'];		
			}
		}
		return $arrResult;
	}
	
	//보험 배팅
    function apibetting($snoopy, $arrBetSite, $arrBetSum){
    	

    	if(strlen($arrBetSite[0]) > 0 && strlen($arrBetSite[1]) > 0 && strlen($arrBetSite[2]) > 0){
    		$nRate = 0;
    		$iGameType = $arrBetSite[6];
            if($iGameType == 1) {
            	$nRate = $arrBetSite[3];
            } else if($iGameType == 2) {
            	$nRate = $arrBetSite[4];
            } else if($iGameType == 3) {
            	$nRate = $arrBetSite[5];
            } else return null;

    		$strBetSum = implode( ',', $arrBetSum );

    		$strUrl = "http://".$arrBetSite[0]."/apisite/apibet?mode=input&game=".$iGameType;
            $strUrl .= "&id=".$arrBetSite[1]."&pwd=".$arrBetSite[2]."&rate=".$nRate."&balance=".$strBetSum;


    		$snoopy->fetch($strUrl);
			$jsonResult = $snoopy->results;
			
			return json_decode($jsonResult, true);
    	} 
    	return null;


    }
	*/




?>