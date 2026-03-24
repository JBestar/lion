<?php

	include_once('libraries/Snoopy.php');
	include_once('helpers/Constant.php');
	include_once('helpers/Logic_Helper.php');
	include_once('helpers/MY_Helper.php');
	include_once('ServiceLogic.php');
	
	//서버가 기동할 동안 대기
	sleep(3);

	date_default_timezone_set('Asia/Seoul');

	$arrConfig = parse_ini_file("config/config.ini");

	//자료기지 접속
	$dbConn = connectDb($arrConfig);

	if ($dbConn->connect_error) {
	    echo "Connection failed.". $dbConn->connect_error;
	    sleep(50);
	    die("Connection failed: ");
		
	} 

	$tRootDir = "";
	if(array_key_exists('dir_root', $arrConfig)){
        $tRootDir = $arrConfig['dir_root'];
    }
    
    $fName = date( 'Y-m-d', time());
	$fLog = fopen($tRootDir."/log/acc_".$fName, "a") ;

	sleep(1);

	//로직 생성
	$objServLogic = new ServiceLogic($dbConn);
	//회차정산상태 
	$bPbAccTime = true; 
	$bPbAcc = false; 
	$bPbBeforeAcc = false; 

	$bPc5Acc = false; 
	$bPc5BeforeAcc = false; 
	$bPe5Acc = false; 
	$bPe5BeforeAcc = false; 
	$bAccStart = false;

	while(true){

		$tmNow = time(); 
		$nHour = date("G",$tmNow);
		$nMin = date("i",$tmNow);
		$nSec = date("s",$tmNow);
		
		//로그파일 
		if($nHour == 0 && $nMin == 0 && $nSec < 4) {
			$strDate = date( 'Y-m-d', $tmNow );
			if($fName !== $strDate){
				if($fLog)
					fclose($fLog);
				$fName = $strDate;
					$fLog = fopen($tRootDir."/log/acc_".$fName, "a") ;
				echo "Log File----".$fName."\r\n";
			}
		}
		
		$nSecSum = ($nMin%10)*60 + $nSec;
		if($nSec < 3) {
			$objServLogic->clearSession();
		}
		//파워볼 정산
		if($bPbAccTime && !$bPbAcc && ( (  $nSecSum>= 3 && $nSecSum<= 75) || ( $nSecSum>= 303 && $nSecSum<= 375) ) ){
			
			$bAccStart = true;
			
			$tContent = "acc-pb-".$nHour.":".$nMin.":".$nSec."-start\r\n";
			echo $tContent;
			if($fLog) 
				fputs($fLog, $tContent);
			
			//파워볼 정산
			$arrPbAccResult = $objServLogic->pbaccount(true, GAME_POWERBALL);
				
			$tmNow = time() ;
			$nHour = date("G",$tmNow);
			$nMin = date("i",$tmNow);
			$nSec = date("s",$tmNow);	

			if($arrPbAccResult['status'] == "success"){
				$bPbAcc = true;
				$tContent = "acc-pb-".$nHour.":".$nMin.":".$nSec."-success\r\n";
			} else {
				$tContent = "acc-pb-".$nHour.":".$nMin.":".$nSec."-fail\r\n";
			}
			
			echo $tContent;
			if($fLog) 
				fputs($fLog, $tContent);

			//이전회차 정산
			if(!$bPbBeforeAcc){
				$tContent = "acc-pb-".$nHour.":".$nMin.":".$nSec."-last\r\n";
				$arrPbAccBefore = $objServLogic->pbaccount(false, GAME_POWERBALL);
				if($arrPbAccBefore['status']=="success")
					$bPbBeforeAcc = true;

				echo $tContent;
				if($fLog) 
					fputs($fLog, $tContent);
			}
			//echo $nHour.":".$nMin.":".$nSec."\n";
		}
		
		//EOS파워볼 정산
		if(!$bPe5Acc && (($nSecSum>= 3 && $nSecSum<= 75) || ($nSecSum>= 303 && $nSecSum<= 375) ) ){
			$tContent = "acc-pe5-".$nHour.":".$nMin.":".$nSec."-start\r\n";
			echo $tContent;
			if($fLog) 
				fputs($fLog, $tContent);
			
			$bAccStart = true;

			//파워볼 정산
			$arrPbAccResult = $objServLogic->pbaccount(true, GAME_EOS_5);
				
			$tmNow = time();
			$nHour = date("G",$tmNow);
			$nMin = date("i",$tmNow);
			$nSec = date("s",$tmNow);	

			if($arrPbAccResult['status'] == "success"){
				$bPe5Acc = true;
				$tContent = "acc-pe5-".$nHour.":".$nMin.":".$nSec."-success\r\n";
			} else {
				$tContent = "acc-pe5-".$nHour.":".$nMin.":".$nSec."-fail\r\n";
			}
			
			echo $tContent;
			if($fLog) 
				fputs($fLog, $tContent);

			//이전회차 정산
			if(!$bPe5BeforeAcc){
				$tContent = "acc-pe5-".$nHour.":".$nMin.":".$nSec."-last\r\n";
				$arrPbAccBefore = $objServLogic->pbaccount(false, GAME_EOS_5);
				if($arrPbAccBefore['status']=="success")
					$bPe5BeforeAcc = true;

				echo $tContent;
				if($fLog) 
					fputs($fLog, $tContent);
			}
			//echo $nHour.":".$nMin.":".$nSec."\n";
		} 
		//코인파워볼 정산
		if(!$bPc5Acc && (($nSecSum>= 3 && $nSecSum<= 75) || ($nSecSum>= 303 && $nSecSum<= 375) ) ){
			$tContent = "acc-pc5-".$nHour.":".$nMin.":".$nSec."-start\r\n";
			echo $tContent;
			if($fLog) 
				fputs($fLog, $tContent);
			
			$bAccStart = true;

			//파워볼 정산
			$arrPbAccResult = $objServLogic->pbaccount(true, GAME_COIN_5);
				
			$tmNow = time();
			$nHour = date("G",$tmNow);
			$nMin = date("i",$tmNow);
			$nSec = date("s",$tmNow);	

			if($arrPbAccResult['status'] == "success"){
				$bPc5Acc = true;
				$tContent = "acc-pc5-".$nHour.":".$nMin.":".$nSec."-success\r\n";
			} else {
				$tContent = "acc-pc5-".$nHour.":".$nMin.":".$nSec."-fail\r\n";
			}
			
			echo $tContent;
			if($fLog) 
				fputs($fLog, $tContent);

			//이전회차 정산
			if(!$bPc5BeforeAcc){
				$tContent = "acc-pc5-".$nHour.":".$nMin.":".$nSec."-last\r\n";
				$arrPbAccBefore = $objServLogic->pbaccount(false, GAME_COIN_5);
				if($arrPbAccBefore['status']=="success")
					$bPc5BeforeAcc = true;

				echo $tContent;
				if($fLog) 
					fputs($fLog, $tContent);
			}
			//echo $nHour.":".$nMin.":".$nSec."\n";
		} else if( $bAccStart && ( ($nSecSum>= 570 && $nSecSum< 600) || ($nSecSum>= 270 && $nSecSum< 300) ) ) {
			$bAccStart = false;
			$bPbAcc = false;
			$bPbBeforeAcc = false;
			$bPe5Acc = false;
			$bPe5BeforeAcc = false;
			$bPc5Acc = false;
			$bPc5BeforeAcc = false;
			$objServLogic->clearSession();

			// $bPbAccTime = trim($objServLogic->getSiteConf(CONF_BENZ_ACC)) != "";

			$tContent = "============Round Start===========\r\n";
			echo $tContent;
				if($fLog) 
					fputs($fLog, $tContent);
		}

		sleep(3);
		
	}
	
	if($fLog) 
		fclose($fLog);
	
	sleep(100);
?>