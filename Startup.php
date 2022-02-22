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
    
    $strDate = date( 'Y-m-d', time()+TM_OFFSET);
	$fLog = fopen($tRootDir."/log/acc_".$strDate, "a") ;

	sleep(1);

	//로직 생성
	$objServLogic = new ServiceLogic($dbConn);
	//회차정산상태 
	$bPbAccTime = false; 
	$bPbAcc = false; 
	$bPbBeforeAcc = false; 

	$bPc5Acc = false; 
	$bPc5BeforeAcc = false; 
	$bAccStart = false;

	while(true){

		if(!$dbConn->connect_error){
			$tmNow = time()+TM_OFFSET;
			$nHour = date("G",$tmNow);
			$nMin = date("i",$tmNow);
			$nSec = date("s",$tmNow);
			
			//로그파일 
			if($nHour == 0 && $nMin == 0 && $nSec < 3) {
				if($fLog)
					fclose($fLog);

				$strDate = date( 'Y-m-d', $tmNow );
				$fLog = fopen($tRootDir."/log/acc_".$strDate, "a") ;
			
				echo "Log File----".$strDate."\r\n";
			}
			
			$nMinSum = $nHour*60 + $nMin;
			//운영시간:06~24
			if($nMinSum > 360 || $nMinSum < 5){
				$bPbAccTime = true;
			} else {
				$bPbAccTime = false;
			}
			$nSecSum = ($nMin%10)*60 + $nSec;
			//파워볼 정산
			if($bPbAccTime && !$bPbAcc && ( (  $nSecSum>= 3 && $nSecSum<= 75) || ( $nSecSum>= 303 && $nSecSum<= 375) ) ){
				
				$bAccStart = true;
				
				$tContent = "acc-pb-".$nHour.":".$nMin.":".$nSec."-start\r\n";
				echo $tContent;
				if($fLog) 
					fputs($fLog, $tContent);
				
				//파워볼 정산
				$arrPbAccResult = $objServLogic->pbaccount(true, GAME_POWERBALL);
					
				$tmNow = time() + TM_OFFSET;
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
			if(!$bPc5Acc && (($nSecSum>= 23 && $nSecSum<= 95) || ($nSecSum>= 323 && $nSecSum<= 395) ) ){
				$tContent = "acc-pc5-".$nHour.":".$nMin.":".$nSec."-start\r\n";
				echo $tContent;
				if($fLog) 
					fputs($fLog, $tContent);
				
				$bAccStart = true;

				//파워볼 정산
				$arrPbAccResult = $objServLogic->pbaccount(true, GAME_COIN_5);
					
				$tmNow = time() + TM_OFFSET;
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
				$bPc5Acc = false;
				$bPc5BeforeAcc = false;
				$objServLogic->clearSession();	
				$tContent = "============Round Start===========\r\n";
				echo $tContent;
					if($fLog) 
						fputs($fLog, $tContent);
			}

			

		} else {
			$dbConn=connectDb($arrConfig);
			sleep(5);				
		}
		sleep(3);
		
	}
	
	if($fLog) 
		fclose($fLog);
	
	sleep(100);
?>