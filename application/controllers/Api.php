<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

	public function index()
	{

	}

	//사용자 로그인
	public function login(){ 
		$logHead = "Api.login ";
		$jsonData = isset($_REQUEST['json_']) ? $_REQUEST['json_'] : '';
		$jsonLen = is_string($jsonData) ? strlen($jsonData) : 0;
		writeLog($logHead . "start ip=" . $this->input->ip_address() . " json_len=" . $jsonLen);

		$arrLoginData = is_string($jsonData) ? json_decode($jsonData, true) : null;
		if (!is_array($arrLoginData) || !array_key_exists('username', $arrLoginData) || !array_key_exists('password', $arrLoginData)) {
			logLoginInvalidPayload($logHead, $jsonLen, json_last_error_msg());
			$arrResult['code'] = 2;
			$arrResult['status'] = "fail";
			echo json_encode($arrResult);
			return;
		}

		$strUid = $arrLoginData['username'];
		$strPwd = $arrLoginData['password'];
		writeLog($logHead . "try uid=" . $strUid . " pwd_len=" . strlen((string) $strPwd));

		$this->load->model('member_model');
		$this->load->model('loghist_model');

		$objUser = $this->member_model->login($strUid, $strPwd);

		if(is_null($objUser)){
			logLoginCredentialFailure($logHead, $this->member_model, $strUid, strlen((string) $strPwd));
			$arrResult['code'] = 2;		
			$arrResult['status'] = "fail";

		} else {
			writeLog($logHead . "db_match mb_uid=" . $objUser->mb_uid . " mb_level=" . $objUser->mb_level . " mb_state_delete=" . $objUser->mb_state_delete);
			if(!$this->member_model->permittedEmployee($objUser) || $objUser->mb_level != MEMBER_EMPLOYEE_LEVEL){
				$bPerm = $this->member_model->permittedEmployee($objUser);
				writeLog($logHead . "FAIL policy permittedEmployee=" . ($bPerm ? '1' : '0') . " mb_level=" . $objUser->mb_level . " need_level=" . MEMBER_EMPLOYEE_LEVEL . " mb_state_delete=" . $objUser->mb_state_delete . " mb_emp_fid=" . $objUser->mb_emp_fid);
				$arrResult['code'] = 2;
				$arrResult['status'] = "fail";
			} else {
				$objSess = $this->sess_model->getByUid($objUser->mb_uid);
				$bNeedLog = false;
				$nLogId = 0;

				if(is_null($objSess))
					$bNeedLog = true;
				else if(!strcmp($objSess->sess_ip, $this->input->ip_address())) {
					$bNeedLog = true;
					$nLogId = $objSess->sess_fid;
				}
				
				if($bNeedLog && $nLogId == 0){
					//결과값 
					$nLogId = $this->sess_model->login($objUser);
				}
				
				if($nLogId > 0) {
					//세션 생성
					$sessData = array('username' => $objUser->mb_uid, 'logged_in'=>TRUE, 'user_level'=>MEMBER_EMPLOYEE_LEVEL);
					$this->session->set_userdata($sessData);
					$this->member_model->updateLogin($objUser);
					$this->loghist_model->addLog($objUser, 1);

					$arrResult['status'] = "success";
					$arrResult['code'] = 1;		//1-성공 2-계정틀림 3-삭제
					$arrResult['data'] = $nLogId;	
				} /*else if($bNeedLog){
					$arrResult['status'] = "fail";
					$arrResult['code'] = 4;		//1-성공 2-계정틀림 4-재가입
				
				}*/ else {
					writeLog($logHead . "FAIL session ip=" . $this->input->ip_address() . " uid=" . $objUser->mb_uid . " bNeedLog=" . ($bNeedLog ? '1' : '0') . " nLogId=" . $nLogId . " (code5=다른IP세션등)");
					$arrResult['status'] = "fail";
					$arrResult['code'] = 5;		//1-성공 2-계정틀림 4-재가입 5-중복
				
				}
				
			} 			
		}
		

		echo json_encode($arrResult);

	}
	
	//사용자정보
	public function assets(){ 
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL))
		{
			//model
			$this->load->model('member_model');
			$this->load->model('confsite_model');

			$strUid = $this->sess_model->getUserId($nLogId);
			$objUser = $this->member_model->getInfoByUid($strUid);
			
			$objMaintain = $this->confsite_model->getMaintain();

			$bIsAlive = false;
			if(!is_null($objUser) && !is_null($objMaintain)){	
				if($this->member_model->permittedEmployee($objUser) && $objMaintain->conf_active != 1 && $objUser->mb_level == MEMBER_EMPLOYEE_LEVEL)
					$bIsAlive = true;
			}

			$objResult = new StdClass; 
			if($bIsAlive){	
				
				$objResult->data = $objUser;
				$objResult->status = "success";
			}
			else {
				//세션 아웃
				//$this->session->sess_destroy();
				$this->sess_model->logout($nLogId);
				$objResult->status = "logout";
			}

			echo json_encode($objResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}
	}

	//사용자정보
	public function session(){ 
	
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL))
		{
			//model
			$this->load->model('member_model');
			$this->load->model('confsite_model');

			$strUid = $this->sess_model->getUserId($nLogId);
			$objUser = $this->member_model->getInfoByUid($strUid);
			
			$objMaintain = $this->confsite_model->getMaintain();

			$bIsAlive = false;
			if(!is_null($objUser) && !is_null($objMaintain)){	
				if($this->member_model->permittedEmployee($objUser) && $objMaintain->conf_active != 1 && $objUser->mb_level == MEMBER_EMPLOYEE_LEVEL)
					$bIsAlive = true;
			}

			$objResult = new StdClass; 
			if($bIsAlive){	
				
				$objResult->data = $objUser;
				$objResult->status = "success";
			}
			else {
				//세션 아웃
				//$this->session->sess_destroy();
				$this->sess_model->logout($nLogId);
				$objResult->status = "logout";
			}

			echo json_encode($objResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}
	}
	
	//게임 설정 정보를 얻기
	public function getconfgame(){ 
		$jsonData = $_REQUEST['json_'];
		$arrGetData = json_decode($jsonData, true);
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) 
		{	
			//model
			$this->load->model('confgame_model');
			$gameId = intval($arrGetData['index']);
			$objConfPb = $this->confgame_model->getByIndex($gameId);//파워볼 설정값:1

			$objResult = new StdClass;
			$objResult->data = $objConfPb;			
			$objResult->status = "success";
		
			echo json_encode($objResult);

		} else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}

	}
	//현재 게임회차, 시간정보를 보내주는 함수
	public function pbcurrentgame(){ 
		$t0 = microtime(true);
		$logHead = 'Api.pbcurrentgame ';
		writeLog($logHead . 'start');

		$jsonData = $_REQUEST['json_'];
		$arrRaData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) 
		{
			$gameId = intval($arrRaData['game']);

			//model
			$this->load->model('pbround_model');
			$this->load->model('confgame_model');
			
			$objConfigPb = $this->confgame_model->getByIndex($gameId);

			if(is_null($objConfigPb)){
				$arrResult['status'] = "fail";
			} else {				

				$arrRoundData = getPballRoundTimes($objConfigPb);
				if($gameId == GAME_POWERBALL){
					
					$arrRound = $this->pbround_model->gets($gameId, 1);
					if(count($arrRound) > 0){	
						calcRoundId($arrRound[0], $arrRoundData);
					} else $arrRoundData['round_id'] = 10001;		
				} else if($gameId == GAME_COIN_5){
					
					$arrRound = $this->pbround_model->gets($gameId, 1);
					if(count($arrRound) > 0){	
						$arrRoundData['round_id'] = $arrRound[0]->round_fid + 1;
					} else $arrRoundData['round_id'] = 10001;	
				} else if($gameId == GAME_EOS_5){
					
					$arrRound = $this->pbround_model->gets($gameId, 1);
					if(count($arrRound) > 0){	
						$arrRoundData['round_id'] = $arrRound[0]->round_fid + 1;
					} else $arrRoundData['round_id'] = 10001;	
				}
				
				$arrResult['data'] = $arrRoundData;
				$arrResult['status'] = "success";
			}
			
			writeLog($logHead . 'done_ms=' . (int) round((microtime(true) - $t0) * 1000) . ' status=' . (isset($arrResult['status']) ? $arrResult['status'] : '') . ' game=' . $gameId);
			echo json_encode($arrResult);
		}
		else{
			writeLog($logHead . 'done_ms=' . (int) round((microtime(true) - $t0) * 1000) . ' status=logout');
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}
	}

	public function betting(){

		$jsonData = $_REQUEST['json_'];
		$arrBetData = json_decode($jsonData, true);
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) 
		{
			$this->load->model('member_model');
			$this->load->model('confgame_model');
			$this->load->model('confsite_model');
			$this->load->model('pbround_model');				

			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);
			$objUser->emp_state_active = 0;
			if($objUser->mb_level == MEMBER_EMPLOYEE_LEVEL && $this->member_model->permittedEmployee($objUser))
				$objUser->emp_state_active = 1;
			
			//서버 점검상태 확인
			$objMaintain = $this->confsite_model->getMaintain();
			if($objMaintain->conf_active == 1) {
				$objUser->emp_state_active = 0;
			}

			$gameId = intval($arrBetData['game']);

			//회차점검시간
			// if($gameId == GAME_POWERBALL && !isNotValidSystemTm()) {
			// 	$objUser->emp_state_active = 0;
			// }

			$iBetFid = 0;
			$iResult = 0;
			$bBetRessult = false;
			 

			if($gameId == GAME_POWERBALL){							//파워볼 일회차 배팅
				$this->load->model('pbbet_model');
				
				$arrRoundData = getPballRoundInfo($gameId);					//계산된 회차결과

				$arrRoundInfo = $this->pbround_model->gets($gameId, 1);		//등록된 회차
				$iRoundState = 0;
				
				if(count($arrRoundInfo)>0 && $arrRoundData['round_no'] == $arrBetData['roundno']){
					$iRoundState = calcRoundId($arrRoundInfo[0], $arrRoundData);
				}
				
				if($iRoundState > 0){
					$arrBetData['roundid'] = $arrRoundData['round_id'];
					$arrBetData['roundno'] = $arrRoundData['round_no'] ;
					$arrBetData['round_date'] = $arrRoundData['round_date'] ;
					
					$arrBetStatist = $this->pbbet_model->getBetStatist($objUser, $arrRoundData, $gameId);
					$objConfig = $this->confgame_model->getByIndex($gameId);
					$iResult = isEnablePbBet($arrBetData, $objUser, $objConfig, $arrRoundData, $arrBetStatist);
					writeLog("Betting iResult=".$iResult);

					if($iResult == 1){
						$arrEmpRatio = $this->member_model->getEmployeePbRatio($objUser, $arrBetData['amount']);
						
						//배팅에 성공하면 거래내역에 반영,유저머니 변경
						$bResult = $this->member_model->moneyHistoryProc($objUser->mb_fid, $arrBetData, $arrEmpRatio[1][2], MONEYCHANGE_BET);
						if($bResult)
							$iBetFid = $this->pbbet_model->addBetRound($arrBetData, $objUser, $arrEmpRatio);
						
					}	
				} else {
					$iResult = 11;
				}
			} else if($gameId == GAME_COIN_5) {							//코인파워볼 일회차 배팅
				$this->load->model('pbbet_model');
				
				$arrRoundData = getPballRoundInfo($gameId);					//계산된 회차결과

				$arrRoundInfo = $this->pbround_model->gets($gameId, 1);		//등록된 회차
				$iRoundState = 0;
				
				if(count($arrRoundInfo)>0 && $arrRoundData['round_no'] == $arrBetData['roundno']){
					
					$arrRoundData['round_id'] = $arrRoundInfo[0]->round_fid + 1;
					$iRoundState = 1;
					
				} else $arrRoundData['round_id'] = 1;
				
				if($iRoundState > 0){
					$arrBetData['roundid'] = $arrRoundData['round_id'];
					$arrBetData['roundno'] = $arrRoundData['round_no'] ;
					$arrBetData['round_date'] = $arrRoundData['round_date'] ;
					
					$arrBetStatist = $this->pbbet_model->getBetStatist($objUser, $arrRoundData, $gameId);
					$objConfig = $this->confgame_model->getByIndex($gameId);
					$iResult = isEnablePbBet($arrBetData, $objUser, $objConfig, $arrRoundData, $arrBetStatist);

					if($iResult == 1){
						$arrEmpRatio = $this->member_model->getEmployeePbRatio($objUser, $arrBetData['amount']);
						
						//배팅에 성공하면 거래내역에 반영,유저머니 변경
						$bResult = $this->member_model->moneyHistoryProc($objUser->mb_fid, $arrBetData, $arrEmpRatio[1][2], MONEYCHANGE_BET);
						if($bResult)
							$iBetFid = $this->pbbet_model->addBetRound($arrBetData, $objUser, $arrEmpRatio);
						
					}	
				} else {
					$iResult = 11;
				}
			} else if($gameId == GAME_EOS_5) {							//EOS파워볼 일회차 배팅
				$this->load->model('pbbet_model');
				
				$arrRoundData = getPballRoundInfo($gameId);					//계산된 회차결과

				$arrRoundInfo = $this->pbround_model->gets($gameId, 1);		//등록된 회차
				$iRoundState = 0;
				
				if(count($arrRoundInfo)>0 && $arrRoundData['round_no'] == $arrBetData['roundno']){
					
					$arrRoundData['round_id'] = $arrRoundInfo[0]->round_fid + 1;
					$iRoundState = 1;
					
				} else $arrRoundData['round_id'] = 1;
				
				if($iRoundState > 0){
					$arrBetData['roundid'] = $arrRoundData['round_id'];
					$arrBetData['roundno'] = $arrRoundData['round_no'] ;
					$arrBetData['round_date'] = $arrRoundData['round_date'] ;
					
					$arrBetStatist = $this->pbbet_model->getBetStatist($objUser, $arrRoundData, $gameId);
					$objConfig = $this->confgame_model->getByIndex($gameId);
					$iResult = isEnablePbBet($arrBetData, $objUser, $objConfig, $arrRoundData, $arrBetStatist);

					if($iResult == 1){
						$arrEmpRatio = $this->member_model->getEmployeePbRatio($objUser, $arrBetData['amount']);
						
						//배팅에 성공하면 거래내역에 반영,유저머니 변경
						$bResult = $this->member_model->moneyHistoryProc($objUser->mb_fid, $arrBetData, $arrEmpRatio[1][2], MONEYCHANGE_BET);
						if($bResult)
							$iBetFid = $this->pbbet_model->addBetRound($arrBetData, $objUser, $arrEmpRatio);
						
					}	
				} else {
					$iResult = 11;
				}
			}
			
			if($iResult == 1 && $iBetFid>0){				

				for($i=0; $i<1; $i++){
					if($arrEmpRatio[$i][0] > 0 && $arrEmpRatio[$i][2] > 0 ){
						$this->member_model->updatePoint($arrEmpRatio[$i][0], $arrEmpRatio[$i][2]);
						
					}
				}
			}

			if($iResult == 1 && $iBetFid>0){
				$arrResult['status'] = "success";
				$arrResult['data'] = $iBetFid;
			} else {	
				$arrResult['status'] = "fail";
				$arrResult['data'] = $iResult;
			}
			echo json_encode($arrResult);
		}
		else{//logout		
			
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}


	}

	
	public function bet_cancel(){

		$logHead = "Api.bet_cancel ";
		$jsonData = isset($_REQUEST['json_']) ? $_REQUEST['json_'] : '';
		$arrReqData = is_string($jsonData) ? json_decode($jsonData, true) : null;
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) {
			$this->load->model('member_model');
			$this->load->model('confgame_model');
			$this->load->model('moneyhistory_model');
			$this->load->model('pbbet_model');
			$this->load->model('pbround_model');

			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);
			$reqGame = is_array($arrReqData) && array_key_exists('game', $arrReqData) ? $arrReqData['game'] : null;
			$reqFid = is_array($arrReqData) && array_key_exists('fid', $arrReqData) ? $arrReqData['fid'] : null;
			writeLog($logHead . "start uid=" . $strUid . " l=" . $nLogId . " game=" . var_export($reqGame, true) . " fid=" . var_export($reqFid, true));

			$objConfig = is_array($arrReqData) ? $this->confgame_model->getByIndex($arrReqData['game']) : null;
			$iResult = 0;
			$sFailReason = "";
			if(!is_array($arrReqData) || !array_key_exists('fid', $arrReqData) || !array_key_exists('game', $arrReqData)){
				$iResult = 0;
				$sFailReason = "bad_request_json_or_missing_keys";
				writeLog($logHead . "FAIL " . $sFailReason . " json_decode_ok=" . (is_array($arrReqData) ? '1' : '0'));
			} else if(is_null($objConfig)){
				$iResult = 0;
				$sFailReason = "confgame_null_for_game";
				writeLog($logHead . "FAIL " . $sFailReason . " game=" . var_export($arrReqData['game'], true));
			} else{
				$arrRoundData = getPballRoundTimes($objConfig);
				$nGameId = intval($arrReqData['game']);
				$nCurrentRoundId = 0;
				$arrRound = $this->pbround_model->gets($nGameId, 1);
				if ($nGameId == GAME_POWERBALL && count($arrRound) > 0) {
					calcRoundId($arrRound[0], $arrRoundData);
					$nCurrentRoundId = isset($arrRoundData['round_id']) ? intval($arrRoundData['round_id']) : 0;
				} else if ($nGameId == GAME_POWERBALL) {
					$nCurrentRoundId = 10001;
				} else if (count($arrRound) > 0) {
					$nCurrentRoundId = intval($arrRound[0]->round_fid) + 1;
				} else {
					$nCurrentRoundId = 10001;
				}
				writeLog($logHead . "round_ctx round_no=" . (isset($arrRoundData['round_no']) ? $arrRoundData['round_no'] : '') . " round_id_computed=" . $nCurrentRoundId . " round_start=" . (isset($arrRoundData['round_start']) ? $arrRoundData['round_start'] : '') . " round_bet_end=" . (isset($arrRoundData['round_bet_end']) ? $arrRoundData['round_bet_end'] : '') . " enableBetTime=" . (isEnableBetTime($arrRoundData) ? '1' : '0'));

				$objBet = $this->pbbet_model->getOrderById($strUid, $arrReqData['fid']);
				if(is_null($objBet) || $objBet->bet_mb_uid !== $strUid ){
					$iResult = 2;		//베팅아이디 오류
					$sFailReason = is_null($objBet) ? "bet_row_null" : "bet_mb_uid_mismatch";
					writeLog($logHead . "FAIL iResult=2 " . $sFailReason . " bet_mb_uid=" . (is_object($objBet) && isset($objBet->bet_mb_uid) ? $objBet->bet_mb_uid : 'null'));
				} else if($objBet->bet_state != BET_WAIT){				// 이미 정산/취소 등 — 취소 불가
					$iResult = 6;
					$sFailReason = "bet_state_not_wait";
					writeLog($logHead . "FAIL iResult=6 bet_state=" . (isset($objBet->bet_state) ? $objBet->bet_state : '') . " need=" . BET_WAIT);
				} else if($nCurrentRoundId <= 0){
					$iResult = 0;
					$sFailReason = "current_round_id_unresolved";
					writeLog($logHead . "FAIL iResult=0 cannot_resolve_current_round_id game=" . $nGameId);
				} else if(intval($objBet->bet_round_fid) !== $nCurrentRoundId){
					$iResult = 3;
					$sFailReason = "bet_round_fid_mismatch";
					writeLog($logHead . "FAIL iResult=3 bet_round_fid=" . (isset($objBet->bet_round_fid) ? $objBet->bet_round_fid : '') . " current_round_id=" . $nCurrentRoundId);
				} else if(!isEnableBetTime($arrRoundData)){
					$iResult = 5;		//베팅 취소 불가능
					$sFailReason = "isEnableBetTime_false";
					$tmCurrent = date("Y-m-d H:i:s", time());
					writeLog($logHead . "FAIL iResult=5 now=" . $tmCurrent . " round_start=" . (isset($arrRoundData['round_start']) ? $arrRoundData['round_start'] : '') . " round_bet_end=" . (isset($arrRoundData['round_bet_end']) ? $arrRoundData['round_bet_end'] : ''));
				} else {
					if($this->pbbet_model->deleteByFid($objBet->bet_fid)){
						if( $objBet->bet_money > 0 && $this->member_model->moneyProc($objUser, $objBet->bet_money, 0-$objBet->bet_empl_amount)){
							$objEmp = $this->member_model->getInfoByFid($objUser->mb_emp_fid);
							if(!is_null($objEmp))
								$this->member_model->moneyProc($objEmp, 0, 0-$objBet->bet_agen_amount);

							$this->moneyhistory_model->registerCancelBet($objUser, $objBet);
						}
						$iResult = 1;		
						writeLog($logHead . "OK bet_fid=" . $objBet->bet_fid . " money=" . $objBet->bet_money);
					} else {
						$iResult = 7;
						$sFailReason = "deleteByFid_failed";
						writeLog($logHead . "FAIL iResult=7 deleteByFid returned false bet_fid=" . (isset($objBet->bet_fid) ? $objBet->bet_fid : ''));
					}
				}
			}
			
			$arrResult['data'] = $iResult;	

			if($iResult == 1){
				$arrResult['status'] = "success";	
				writeLog($logHead . "response success");
			} else if($iResult == 5){
				$arrResult['status'] = "fail";	
				$arrResult['msg'] = "베팅을 취소할수 없습니다.";	
				writeLog($logHead . "response fail msg=cannot_cancel_now reason=" . $sFailReason);
			} else {
				$arrResult['status'] = "fail";	
				$arrResult['msg'] = "거절되었습니다.";	
				writeLog($logHead . "response fail msg=generic_denied iResult=" . $iResult . " reason=" . $sFailReason);
			}
			echo json_encode($arrResult);	

		}
		else{//logout		
			writeLog($logHead . "FAIL not_logged_in_or_bad_session l=" . var_export($nLogId, true));
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}
	}

	public function pbbetinfo(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) 
		{
			$this->load->model('member_model');	
			$this->load->model('pbbet_model');	

			$strUid = $this->sess_model->getUserId($nLogId);	
			$objBetInfo = $this->pbbet_model->getOrderById($strUid, $arrReqData['bet_id']);

			$objResult = new StdClass;
			$objResult->data = $objBetInfo;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		} else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}

	public function pbrecentbets(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL))
		{
			$this->load->model('member_model');
			$this->load->model('pbbet_model');

			$strUid = $this->sess_model->getUserId($nLogId);
			$nGameId = isset($arrReqData['game']) ? intval($arrReqData['game']) : -1;
			$nLimit = isset($arrReqData['limit']) ? intval($arrReqData['limit']) : 50;
			if ($nLimit < 1 || $nLimit > 200) {
				$nLimit = 50;
			}

			$arrBets = $this->pbbet_model->getByUserId($strUid, $nLimit, $nGameId);

			$objResult = new StdClass;
			$objResult->bets = $arrBets;
			$objResult->status = "success";

			echo json_encode($objResult);
		} else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);
		}
	}

	public function pbroundresult(){
		$tPbRoundResult0 = microtime(true);
		$logHead = 'Api.pbroundresult ';
		$jsonData = isset($_REQUEST['json_']) ? $_REQUEST['json_'] : '';
		$jsonLen = is_string($jsonData) ? strlen($jsonData) : 0;
		writeLog($logHead . 'start json_len=' . $jsonLen);

		$arrReqData = is_string($jsonData) ? json_decode($jsonData, true) : null;
		if (!is_array($arrReqData)) {
			writeLog($logHead . 'FAIL json_decode err=' . json_last_error_msg());
			$arrReqData = array();
		}

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) 
		{
			
			//model
			$this->load->model('member_model');	
			$this->load->model('pbround_model');
			$this->load->model('pbbet_model');	

			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);


			$nRoundId = isset($arrReqData['round_id']) ? $arrReqData['round_id'] : null;
			$nGameId = isset($arrReqData['game_id']) ? $arrReqData['game_id'] : null;
			$nDateNo = isset($arrReqData['date_no']) ? $arrReqData['date_no'] : null;

			writeLog($logHead . 'req round_id=' . var_export($nRoundId, true) . ' game_id=' . var_export($nGameId, true) . ' date_no=' . var_export($nDateNo, true) . ' uid=' . ($strUid !== '' ? $strUid : '(empty)'));

			$objRound = null;
			$arrBetData = null;

			if($nGameId == GAME_POWERBALL){
				if($nRoundId > 0 && !is_null($objUser)){
					$objRound = $this->pbround_model->getById($nGameId, $nRoundId);
					$arrBetData = $this->pbbet_model->getByRoundId($objUser->mb_uid, $nRoundId, $nGameId);
					if (is_null($objRound)) {
						writeLog($logHead . 'PBG getById returned NULL (lookup round_game=' . $nGameId . ' round_fid=' . var_export($nRoundId, true) . ')');
						$hintRows = $this->pbround_model->gets($nGameId, 1);
						if (!empty($hintRows) && isset($hintRows[0])) {
							$h = $hintRows[0];
							$rf = isset($h->round_fid) ? $h->round_fid : '';
							$rh = isset($h->round_hash) ? $h->round_hash : '';
							writeLog($logHead . 'PBG hint latest DB row round_fid=' . var_export($rf, true) . ' round_hash=' . var_export($rh, true) . ' round_num=' . (isset($h->round_num) ? $h->round_num : '') . ' round_date=' . (isset($h->round_date) ? $h->round_date : ''));
						} else {
							writeLog($logHead . 'PBG hint no rows in round_pball for round_game=' . $nGameId);
						}
					} else {
						writeLog($logHead . 'PBG OK round_fid=' . (isset($objRound->round_fid) ? $objRound->round_fid : '') . ' round_hash=' . (isset($objRound->round_hash) ? $objRound->round_hash : ''));
					}
					$nBets = is_array($arrBetData) ? count($arrBetData) : 0;
					writeLog($logHead . 'PBG bets count=' . $nBets);
				} else {
					if (!($nRoundId > 0)) {
						writeLog($logHead . 'PBG SKIP round_id not > 0 (got ' . var_export($nRoundId, true) . ')');
					}
					if (is_null($objUser)) {
						writeLog($logHead . 'PBG SKIP objUser null');
					}
				}
			} else if($nGameId == GAME_COIN_5){
				if(!is_null($objUser)){
					$tmNow = time();
					$strDate = date( 'Y-m-d', $tmNow );
					if($nDateNo < 0){
						$strDate = date('Y-m-d', strtotime("-1 day", $tmNow));
					} else if($nDateNo > 0){
						$strDate = date('Y-m-d', strtotime("+1 day", $tmNow));
					}
						
					$objRound = $this->pbround_model->getByNum($nGameId, $strDate, $nRoundId);
					$arrBetData = $this->pbbet_model->getByRoundNo($objUser->mb_uid, $strDate, $nRoundId, $nGameId);
					if (is_null($objRound)) {
						writeLog($logHead . 'COIN getByNum NULL date=' . $strDate . ' round_num=' . var_export($nRoundId, true));
					} else {
						writeLog($logHead . 'COIN OK round_fid=' . (isset($objRound->round_fid) ? $objRound->round_fid : ''));
					}
				} else {
					writeLog($logHead . 'COIN SKIP objUser null');
				}
			} else if($nGameId == GAME_EOS_5){
				if(!is_null($objUser)){
					$tmNow = time();
					$strDate = date( 'Y-m-d', $tmNow );
					if($nDateNo < 0){
						$strDate = date('Y-m-d', strtotime("-1 day", $tmNow));
					} else if($nDateNo > 0){
						$strDate = date('Y-m-d', strtotime("+1 day", $tmNow));
					}

					$objRound = $this->pbround_model->getByNum($nGameId, $strDate, $nRoundId);
					$arrBetData = $this->pbbet_model->getByRoundNo($objUser->mb_uid, $strDate, $nRoundId, $nGameId);
					if (is_null($objRound)) {
						writeLog($logHead . 'EOS getByNum NULL date=' . $strDate . ' round_num=' . var_export($nRoundId, true));
					} else {
						writeLog($logHead . 'EOS OK round_fid=' . (isset($objRound->round_fid) ? $objRound->round_fid : ''));
					}
				} else {
					writeLog($logHead . 'EOS SKIP objUser null');
				}
			} else {
				writeLog($logHead . 'SKIP unknown game_id=' . var_export($nGameId, true));
			}

			$objResult = new StdClass;
			$objResult->round = $objRound;		
			$objResult->bets = $arrBetData;		
			$objResult->status = "success";

			$rs = 'n/a';
			$rfid = 'n/a';
			if (!is_null($objRound)) {
				$rs = isset($objRound->round_state) ? (string) $objRound->round_state : 'n/a';
				$rfid = isset($objRound->round_fid) ? (string) $objRound->round_fid : 'n/a';
			}
			writeLog($logHead . 'response round_is_null=' . (is_null($objRound) ? '1' : '0') . ' round_state=' . $rs . ' round_fid=' . $rfid . ' bets_count=' . (is_array($arrBetData) ? count($arrBetData) : 0) . ' done_ms=' . (int) round((microtime(true) - $tPbRoundResult0) * 1000));
		
			echo json_encode($objResult);

		} else{
			writeLog($logHead . 'FAIL not logged in or bad session l=' . var_export($nLogId, true) . ' done_ms=' . (int) round((microtime(true) - $tPbRoundResult0) * 1000));
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}

	
	function pbroundhistory(){
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) 
		{
			
			//model
			$this->load->model('member_model');	
			$this->load->model('pbround_model');	

			$gameId = GAME_POWERBALL;
			$arrRoundData = $this->pbround_model->gets($gameId, 10);

			$objResult = new StdClass;
			$objResult->data = $arrRoundData;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		} else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}

	
	function pc5roundhistory(){
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) 
		{
			
			//model
			$this->load->model('member_model');	
			$this->load->model('pbround_model');	

			$gameId = GAME_COIN_5;
			$arrRoundData = $this->pbround_model->gets($gameId, 10);

			$objResult = new StdClass;
			$objResult->data = $arrRoundData;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		} else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}

	function pe5roundhistory(){
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) 
		{
			
			//model
			$this->load->model('member_model');	
			$this->load->model('pbround_model');	

			$gameId = GAME_EOS_5;
			$arrRoundData = $this->pbround_model->gets($gameId, 10);

			$objResult = new StdClass;
			$objResult->data = $arrRoundData;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		} else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}

	public function bethistory(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) 
		{
			
			//model
			$this->load->model('member_model');	
			$this->load->model('pbbet_model');	

			$strUid = $this->sess_model->getUserId($nLogId);			
			$arrReqData['mb_uid'] = $strUid;
			$arrReqData['max_count'] = 300;
			$arrBetData = $this->pbbet_model->search($arrReqData);

			$objResult = new StdClass;
			$objResult->data = $arrBetData;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		} else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}


	public function charge(){ 
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL))
		{
			
			//model
			$this->load->model('charge_model');
			$this->load->model('member_model');

			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);
			$objWaitCharge = $this->charge_model->waitCharge($objUser);
			$bResult = false;
			if(is_null($objWaitCharge))
				$bResult = $this->charge_model->addCharge($objUser, $arrReqData);
		
	 		if($bResult)	{
				$arrResult['status'] = "success";				
			}
			else{
					if(!is_null($objWaitCharge))
						$arrResult['status'] = "wait";
					else $arrResult['status'] = "fail";
				}
				echo json_encode($arrResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}
	}

		
	public function chargehistory(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) 
		{
			
			//model
			$this->load->model('member_model');	
			$this->load->model('charge_model');	

			$strUid = $this->sess_model->getUserId($nLogId);			
			$arrReqData['mb_uid'] = $strUid;
			$arrChargeData = $this->charge_model->search($arrReqData);

			$objResult = new StdClass;
			$objResult->data = $arrChargeData;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		} else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}



	
	public function discharge(){ 
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL))
		{
			
			//model
			$this->load->model('discharge_model');
			$this->load->model('member_model');
			$this->load->model('moneyhistory_model');

			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);
			$objAdmin = $this->member_model->getInfoByFid($objUser->mb_emp_fid);
			$iResult = 0;
			
			/*
			$objWaitCharge = $this->discharge_model->waitDicharge($objUser);
			if(!is_null($objWaitCharge))
				$iResult = 4;
			else 	
				$iResult = $this->discharge_model->addDischarge($objUser, $arrReqData);
			*/
			if(!is_null($objUser) && !is_null($objAdmin)){
				if($objUser->mb_money < $arrReqData['discharge_amount']) 
					$iResult = 5;
				else if($arrReqData['discharge_amount'] < 10000)
					$iResult = 6;
				else {
					$nDtMoney = 0 - $arrReqData['discharge_amount'];
					$bResult = $this->member_model->moneyProc($objAdmin, $arrReqData['discharge_amount']);
					$bResult = $this->member_model->moneyProc($objUser, $nDtMoney);
					if($bResult){
						//moneyhistory Table
						$objUser->mb_emp_uid = $objAdmin->mb_uid;
						$this->moneyhistory_model->registerDischarge($objUser, $arrReqData['discharge_amount']);
						$objAdmin->mb_emp_uid = $objUser->mb_uid;
						$this->moneyhistory_model->registerDischargeFrom($objAdmin, $arrReqData['discharge_amount']);
					
						$arrReqData['discharge_after'] = $objUser->mb_money -  $arrReqData['discharge_amount'];
						$iResult = $this->discharge_model->addDischarge($objUser, $arrReqData);
					}
				}
			} 
			

	 		if($iResult == 1)	{
				$arrResult['status'] = "success";				
			} else{
					$arrResult['data'] = $iResult;		
					$arrResult['status'] = "fail";
			}
			echo json_encode($arrResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}
	}

		
	public function dischargehistory(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) 
		{
			
			//model
			$this->load->model('member_model');	
			$this->load->model('discharge_model');	

			$strUid = $this->sess_model->getUserId($nLogId);			
			$arrReqData['mb_uid'] = $strUid;
			$arrChargeData = $this->discharge_model->search($arrReqData);

			$objResult = new StdClass;
			$objResult->data = $arrChargeData;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		} else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}



	
	public function mileage(){ 
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL))
		{
			
			//model
			$this->load->model('member_model');

			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);
			$bResult = false;
			if($objUser->mb_point >= $arrReqData['mileage'])
				$bResult = $this->member_model->changePoint($objUser, $arrReqData['mileage']);
			
	 		if($bResult)	{
				$arrResult['status'] = "success";				
			} else{
					$arrResult['status'] = "fail";
			}
			echo json_encode($arrResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}
	}

		
	public function mileagehistory(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) 
		{
			
			//model
			$this->load->model('member_model');	
			
			$strUid = $this->sess_model->getUserId($nLogId);			
			$arrReqData['mb_uid'] = $strUid;
			$arrMileageData = $this->member_model->searchPointHistory($arrReqData);

			$objResult = new StdClass;
			$objResult->data = $arrMileageData;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		} else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}


	
	public function changeuser(){ 
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL))
		{	
			//model
			$this->load->model('member_model');

			$strUid = $this->sess_model->getUserId($nLogId);	
			$objUser = $this->member_model->getInfoByUid($strUid);	
			$bResult = false;	
			if(!is_null($objUser))
				$bResult = $this->member_model->modifyUser($objUser->mb_fid, $arrReqData['mb_user']);
			
	 		if($bResult)	{
				$arrResult['status'] = "success";				
			} else{
					$arrResult['status'] = "fail";
			}
			echo json_encode($arrResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}
	}
	
	public function changeinfo(){ 
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL))
		{	
			//model
			$this->load->model('member_model');

			$strUid = $this->sess_model->getUserId($nLogId);	
			$objUser = $this->member_model->login($strUid, $arrReqData['mb_pwd']);	
			$iResult = 0;	
			if(is_null($objUser)){
				$iResult = 2;
			} else {	
				$iResult = $this->member_model->modifyInfo($objUser->mb_fid, $arrReqData);
			}

	 		if($iResult==1)	{
				$arrResult['status'] = "success";				
			} else{
				$arrResult['data'] = $iResult;
				$arrResult['status'] = "fail";
			}
			echo json_encode($arrResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}
	}

	public function sendmessage(){ 
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL))
		{	
			//model
			$this->load->model('member_model');
			$this->load->model('message_model');

			$strUid = $this->sess_model->getUserId($nLogId);	
			$objUser = $this->member_model->getInfoByUid($strUid);
			if(!is_null($objUser))	{
				$objEmp = $this->member_model->getInfoByFid($objUser->mb_emp_fid);	
				if(!is_null($objEmp)){
					$arrReqData['recv_id'] = $objEmp->mb_uid;
				}
			}
			$bResult = $this->message_model->addMessage($objUser, $arrReqData);
			
	 		if($bResult) {
				$arrResult['status'] = "success";				
			} else{
				$arrResult['status'] = "fail";
			}
			echo json_encode($arrResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}
	}

		
	public function getSendMessage(){
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) 
		{
			
			//model
			$this->load->model('member_model');	
			$this->load->model('message_model');
			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);	

			$arrMessage = $this->message_model->getSendMessage($objUser);

			$objResult = new StdClass;
			$objResult->data = $arrMessage;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		} else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}

	public function getRecvNewMessage(){
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) 
		{
			
			//model
			$this->load->model('member_model');	
			$this->load->model('message_model');
			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);	

			$arrMessage = $this->message_model->getRecvMessage($objUser, 1);

			$objResult = new StdClass;
			$objResult->data = $arrMessage;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		} else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}
		
	public function getRecvMessage(){
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL)) 
		{
			
			//model
			$this->load->model('member_model');	
			$this->load->model('message_model');
			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);	

			$arrMessage = $this->message_model->getRecvMessage($objUser, 20);

			$objResult = new StdClass;
			$objResult->data = $arrMessage;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		} else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}

	public function deletemessage(){ 
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_EMPLOYEE_LEVEL))
		{	
			//model
			$this->load->model('member_model');
			$this->load->model('message_model');

			$strUid = $this->sess_model->getUserId($nLogId);	
			$objUser = $this->member_model->getInfoByUid($strUid);	
			$iResult = 0;	
			if(!is_null($objUser)){
				$bResult = $this->message_model->deleteMessage($objUser, $arrReqData);
			}

	 		if($bResult) {
				$arrResult['status'] = "success";				
			} else{
				$arrResult['status'] = "fail";
			}
			echo json_encode($arrResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}
	}

	//점검중검사 
	public function checkMaintain(){ 
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);
		
		//model
		$this->load->model('confsite_model');
		$objMaintain = $this->confsite_model->getMaintain();

		$arrResult['data'] = $objMaintain;
		$arrResult['status'] = "success";
		
		echo json_encode($arrResult);

	}

}



