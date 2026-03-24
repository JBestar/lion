<?php

include_once('models/PballRound_Model.php');
include_once('models/PballBet_Model.php');

include_once('models/ConfGame_Model.php');
include_once('models/ConfSite_Model.php');
include_once('models/Pcoin5Round_Model.php');
include_once('models/Peos5Round_Model.php');

include_once('models/Member_Model.php');
include_once('models/MoneyHistory_Model.php');

include_once('models/Sess_Model.php');

class ServiceLogic
{
	private $mSnoopy ;

	private $modelPballRound;
	private $modelCoin5Round;
	private $modelEos5Round;
	
	private $modelPballBet;

	private $modelMember;
	private $modelMoneyHist;

	private $modelConfGame;
	private $modelConfSite;

	private $modelSession;

	function __construct($dbConn){
		$this->mSnoopy = new Snoopy();

		$this->modelPballRound = new PballRound_Model($dbConn);
		$this->modelCoin5Round = new Pcoin5Round_Model($dbConn);
		$this->modelEos5Round = new Peos5Round_Model($dbConn);
		
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
		$arrBetData = $this->modelPballBet->getWaitsByGame($gameId);

		$tBetUid = "";
		$nBeforeMoney = 0;
		$nUpdated = 0;
		$nWon = 0;
		$nSkipRoundMissing = 0;
		$nSkipRoundNotReady = 0;

		foreach($arrBetData as $arrBetInfo){
			$objBetInfo = (object)$arrBetInfo;

			$objRoundInfo = null;
			if($gameId == GAME_COIN_5){
				$objRoundInfo = $this->modelCoin5Round->getByFid($objBetInfo->bet_round_fid);
			} else if($gameId == GAME_EOS_5){
				$objRoundInfo = $this->modelEos5Round->getByFid($objBetInfo->bet_round_fid);
			} else {
				$objRoundInfo = $this->modelPballRound->getByFid($gameId, $objBetInfo->bet_round_fid);
			}

			if(is_null($objRoundInfo)){
				$nSkipRoundMissing++;
				continue;
			}
			$objRoundInfo = (object)$objRoundInfo;
			if($objRoundInfo->round_state != 1){
				$nSkipRoundNotReady++;
				continue;
			}

			if(strcmp($tBetUid, $objBetInfo->bet_mb_uid) !== 0){
				$tBetUid  = $objBetInfo->bet_mb_uid;
				$nBeforeMoney = $objBetInfo->bet_before_money;
			}

			$bResult = $this->modelPballBet->updateBetRound($objRoundInfo, $objBetInfo, $nBeforeMoney);
			if($bResult){
				$nUpdated++;
				if($objBetInfo->bet_win_money > 0){
					$nWon++;
					$objMember = $this->modelMember->updateWinMoney($objBetInfo);
				}
			}
		}

		$arrResult['status'] = "success";
		return $arrResult;
	}

	//사이트 정보얻기
	public function getSiteActive($confId)
	{		
		//게임배팅시간
		$objConfig = $this->modelConfSite->getById($confId);
		if(!is_null($objConfig)){
			return $objConfig->conf_active == 1;
		}
		return false;
	}

	public function getSiteConf($confId)
	{		
		//게임배팅시간
		$objConfig = $this->modelConfSite->getById($confId);
		if(!is_null($objConfig)){
			return $objConfig->conf_content;
		}
		return "";
	}

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