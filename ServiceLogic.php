<?php

include_once('models/PballRound_Model.php');
include_once('models/Pcoin5Round_Model.php');
include_once('models/PballBet_Model.php');

include_once('models/ConfGame_Model.php');
include_once('models/ConfSite_Model.php');

include_once('models/Member_Model.php');
include_once('models/MoneyHistory_Model.php');

include_once('models/Sess_Model.php');

class ServiceLogic
{
	private $mSnoopy ;

	private $modelPballRound;
	private $modelPcoin5Round;
	
	private $modelPballBet;

	private $modelMember;
	private $modelMoneyHist;

	private $modelConfGame;
	private $modelConfSite;

	private $modelSession;

	function __construct($dbConn){
		$this->mSnoopy = new Snoopy();

		$this->modelPballRound = new PballRound_Model($dbConn);
		$this->modelPcoin5Round = new Pcoin5Round_Model($dbConn);
	
		$this->modelPballBet = new PballBet_Model($dbConn);
		
		$this->modelConfGame = new ConfGame_Model($dbConn);
		$this->modelConfSite = new ConfSite_Model($dbConn);

		$this->modelMember = new Member_Model($dbConn);
		$this->modelMoneyHist = new MoneyHistory_Model($dbConn);

		$this->modelSession = new Sess_Model($dbConn);		
	}


	//동행파워볼 정산
	public function pbaccount($bCurrent, $gameId)
	{		
		if($bCurrent)	
			$arrRound = getPbBeforeRoundInfo($gameId);	//이전회차
		else 
			$arrRound = getPbLastRoundInfo($gameId);	//전전회차

		$arrRoundInfo = null;
		if($gameId == GAME_POWERBALL)
			$arrRoundInfo = $this->modelPballRound->getByDate($arrRound['round_no'], $arrRound['round_date']);
		else if($gameId == GAME_COIN_5)
			$arrRoundInfo = $this->modelPcoin5Round->getByDate($arrRound['round_no'], $arrRound['round_date']);
	
		if(!is_null($arrRoundInfo) && $arrRoundInfo['round_state']==1){
			$objRoundInfo = (object)$arrRoundInfo;

			$arrRoundTm = getPbRoundTimes($objRoundInfo, $gameId);

			
			$arrBetData = $this->modelPballBet->getWaits($arrRoundTm, $gameId);
			
			$tBetUid = "";
			$nBeforeMoney = 0;
			foreach($arrBetData as $arrBetInfo){
				
				$objBetInfo = (object)$arrBetInfo;
				if(strcmp($tBetUid, $objBetInfo->bet_mb_uid) !== 0){
					$tBetUid  = $objBetInfo->bet_mb_uid;
					$nBeforeMoney = $objBetInfo->bet_before_money;
				}

				$bResult = $this->modelPballBet->updateBetRound($objRoundInfo, $objBetInfo, $nBeforeMoney);
				
				if($bResult){
					//회원테이블과 자동베팅 테이블을 갱신한다.
					if($objBetInfo->bet_win_money > 0){
						$objMember = $this->modelMember->updateWinMoney($objBetInfo);
						if(!is_null($objMember)) {
        					// $bRes = $this->modelMoneyHist->registerAccountBet($objMember, $objBetInfo, MONEYCHANGE_WIN);	//파워볼 정산
        					
        				}
					}					
				}
			}
			
			$arrResult['status'] = "success";
			

		} else {
				
			$arrResult['status'] = "fail";
		}
		return $arrResult;
	}


	/*
	//파워볼 정산
	public function pbaccountByRound()
	{		
		if($nRoundFid < 1)
			return false;

		echo "account-before-".$nRoundFid."\r\n";

		$arrRoundInfo = $this->modelPballRound->getByFid($nRoundFid);

		if(is_null($arrRoundInfo))
			return false;

		if($arrRoundInfo['round_state'] != 1){
			return false;
		}


		$objRoundInfo = (object)$arrRoundInfo;

		$arrRoundTm = getPbRoundTimes($objRoundInfo);
		$arrBetData = $this->modelPballBet->getWaits($arrRoundTm);
		
		$tBetUid = "";
		$nBeforeMoney = 0;
		foreach($arrBetData as $arrBetInfo){	
			
			$objBetInfo = (object)$arrBetInfo;
			if(strcmp($tBetUid, $objBetInfo->bet_mb_uid) !== 0){
				$tBetUid  = $objBetInfo->bet_mb_uid;
				$nBeforeMoney = $objBetInfo->bet_before_money;
			}

			$bResult = $this->modelPballBet->updateBetRound($objRoundInfo, $objBetInfo, $nBeforeMoney);
			
			if($bResult){
				//회원테이블과 자동베팅 테이블을 갱신한다.
				if($objBetInfo->bet_win_money > 0){
					$objMember = $this->modelMember->updateWinMoney($objBetInfo);
					if(!is_null($objMember)) {
    					$bRes = $this->modelMoneyHist->registerAccountBet($objMember, $objBetInfo, 6);	//파워볼 정산
    					
    				}
				}					
			}
		}

		return true;

		
	}
	*/

	//세션리력삭제
	function clearSession(){
		$this->modelSession->clearSession();
	}

	/*
	function get_server_cpu_usage(){

		$load = sys_getloadavg();
		return $load[0];

	}

	function get_server_memory_usage(){
	
		$free = shell_exec('free');
		$free = (string)trim($free);
		$free_arr = explode("\n", $free);
		$mem = explode(" ", $free_arr[1]);
		$mem = array_filter($mem);
		$mem = array_merge($mem);
		$memory_usage = $mem[2]/$mem[1]*100;

		return $memory_usage;
	}

	function get_server_load() {
		 if (stristr(PHP_OS, 'win')) { //윈도우 플랫폼일때
		   $wmi = new COM("Winmgmts://");
		   $server = $wmi->execquery("SELECT LoadPercentage FROM Win32_Processor");
		            
		   $cpu_num = 0;
		   $load_total = 0;
		             
		   foreach($server as $cpu){
		    $cpu_num++;
		    $load_total += $cpu->loadpercentage;
		   }
		             
		   $load = round($load_total/$cpu_num);
		             
		 } else {
		        
		  $sys_load = sys_getloadavg();
		  $load = $sys_load[0];
		        
		 }
		         
		 return (int) $load;
  
	}
	*/






}



?>