<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CApi extends CI_Controller {

	public function index()
	{
		
	}

	//사용자 로그인
	public function login(){ 
		$logHead = "CApi.login ";
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
		
		if(!is_null($objUser)){
			if($objUser->mb_state_delete == 0 && $objUser->mb_level >= MEMBER_COMPANY_LEVEL){
				
	 			//결과값 
				 $objUser->mb_level = MEMBER_COMPANY_LEVEL;
				 $nLogId = $this->sess_model->login($objUser);
				
				 if($nLogId > 0) {
					//세션 생성
					$sessData = array('username' => $objUser->mb_uid, 'logged_in'=>TRUE, 'user_level'=>MEMBER_COMPANY_LEVEL);
					$this->session->set_userdata($sessData);
					$this->member_model->updateLogin($objUser);
					$this->loghist_model->addLog($objUser, 1);	
					
					 $arrResult['status'] = "success";
					 $arrResult['code'] = 1;		//1-성공 2-계정틀림 3-삭제
					 $arrResult['data'] = $nLogId;	
				 } else {
					 writeLog($logHead . "FAIL sess_model->login returned<=0 uid=" . $objUser->mb_uid);
					 $arrResult['status'] = "fail";
					 $arrResult['code'] = 4;		//1-성공 2-계정틀림 4-재가입
				 
				 }

			} else{
				writeLog($logHead . "FAIL policy uid=" . $objUser->mb_uid . " mb_level=" . $objUser->mb_level . " need>=" . MEMBER_COMPANY_LEVEL . " mb_state_delete=" . $objUser->mb_state_delete);
				$arrResult['code'] = 2;
				$arrResult['status'] = "fail";
			} 			
		}
		else
		{
				logLoginCredentialFailure($logHead, $this->member_model, $strUid, strlen((string) $strPwd));
				$arrResult['code'] = 2;		
				$arrResult['status'] = "fail";
		}

		echo json_encode($arrResult);

	}

	public function logout() {
		//$this->session->sess_destroy();
	}

    	
	//사용자정보
	public function assets(){ 
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{
			//model
			$this->load->model('member_model');
			$this->load->model('confsite_model');

			$strUid = $this->sess_model->getUserId($nLogId);
			$objUser = $this->member_model->getInfoByUid($strUid);
			
			$objMaintain = $this->confsite_model->getMaintain();
			
			$bIsAlive = false;
			if(!is_null($objUser) && !is_null($objMaintain)){	
				$objMaintain->conf_active = 0;			
				if($objUser->mb_state_delete == 0 && $objMaintain->conf_active != 1 && $objUser->mb_level >= MEMBER_COMPANY_LEVEL)
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
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{
			//model
			$this->load->model('member_model');
			$this->load->model('confsite_model');

			$strUid = $this->sess_model->getUserId($nLogId);
			$objUser = $this->member_model->getInfoByUid($strUid);
			
			$objMaintain = $this->confsite_model->getMaintain();
			
			$bIsAlive = false;
			if(!is_null($objUser) && !is_null($objMaintain)){	
				$objMaintain->conf_active = 0;
				if($objUser->mb_state_delete == 0 && $objMaintain->conf_active != 1 && $objUser->mb_level >= MEMBER_COMPANY_LEVEL)
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
	
	public function getemployee(){ 
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{
			
			//model
			$this->load->model('member_model');

			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);
			
			$objUser->mb_fid = 0;
			$arrEmployee = $this->member_model->getEmployee($objUser, MEMBER_AGENCY_LEVEL);
			
			$arrResult['data'] = $arrEmployee;
	 		$arrResult['status'] = "success";				
			
			echo json_encode($arrResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}
	}


	public function addemployee(){ 
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{
			
			//model
			$this->load->model('member_model');

			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);
			$arrReqData['level'] = MEMBER_AGENCY_LEVEL;
			$iResult = 0;
			if(!is_null($objUser)){
				$objUser->mb_fid = 0;
				$iResult = $this->member_model->addEmployee($objUser, $arrReqData);
			}

			if($iResult == 1)
				$arrResult['status'] = "success";
			else {
				$arrResult['status'] = "fail";
				$arrResult['data'] = $iResult;
			}			
			
			echo json_encode($arrResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}
	}


	public function modifyemployee(){ 
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{
			
			//model
			$this->load->model('member_model');

			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);
			$arrReqData['level'] = MEMBER_AGENCY_LEVEL;
			$iResult = 0;
			if(!is_null($objUser)){
				$iResult = $this->member_model->modifyEmployee($objUser, $arrReqData);
			}
			
			if($iResult == 1)
				$arrResult['status'] = "success";
			else {
				$arrResult['status'] = "fail";
				$arrResult['data'] = $iResult;
			}			
			
			echo json_encode($arrResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}
	}


	

	public function deleteemployee(){ 
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{
			
			//model
			$this->load->model('member_model');
			$this->load->model('delhist_model');

			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);
			
			$iResult = 0;
			$del_uid = "";
			if(!is_null($objUser)){
				$arrReqData['level'] = MEMBER_AGENCY_LEVEL;				
				$iResult = $this->member_model->deleteEmployee($objUser, $arrReqData, $del_uid);
			}
			if($iResult == 1){
				$this->delhist_model->register($strUid, $del_uid);
				$arrResult['status'] = "success";
			}
				
			else {
				$arrResult['status'] = "fail";
				$arrResult['data'] = $iResult;
			}			
			
			echo json_encode($arrResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}
	}



	public function emplist(){

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL)) {

			//model
			$this->load->model('member_model');	
			$strUid = $this->sess_model->getUserId($nLogId);	
			$objUser = $this->member_model->getInfoByUid($strUid);
			
			$arrEmp = $this->member_model->getAgencyIds();
			
			$objResult = new StdClass;
			$objResult->data = $arrEmp;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		}  else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}

	}

	
	
	public function waitTransfer(){

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL)) {

			//model
			$this->load->model('member_model');	
			$this->load->model('charge_model');	
			$this->load->model('discharge_model');

			$strUid = $this->sess_model->getUserId($nLogId);	
			$objUser = $this->member_model->getInfoByUid($strUid);

			$arrWait = [0, 0];
			$arrCharge = null;
			if(!is_null($objUser)){
				$objUser->mb_fid  = 0;
				$objCharge = $this->charge_model->waitChargeByEmpFid($objUser->mb_fid);
				$objDischarge = $this->discharge_model->waitDichargeByEmpFid($objUser->mb_fid);
				if(!is_null($objCharge))
					$arrWait[0] = 1;
				if(!is_null($objDischarge))
					$arrWait[1] = 1;
			}

			$objResult = new StdClass;
			$objResult->data = $arrWait;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		}  else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}


	}
	
	public function chargelist(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL)) {

			//model
			$this->load->model('member_model');	
			$this->load->model('charge_model');	

			$strUid = $this->sess_model->getUserId($nLogId);	
			$objUser = $this->member_model->getInfoByUid($strUid);

			$arrCharge = null;
			if(!is_null($objUser)){
				$arrCharge = $this->charge_model->getByEmpFid(0, $arrReqData);
			}

			$objResult = new StdClass;
			$objResult->data = $arrCharge;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		}  else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}


	}


	function chargeproc(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);
		

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL)) {

			$this->load->model('charge_model');
			$this->load->model('member_model');
			$this->load->model('moneyhistory_model');
			$strUid = $this->sess_model->getUserId($nLogId);
			$objAdmin = $this->member_model->getInfoByUid($strUid);
			$iResult = 0;

			$objCharge = $this->charge_model->getByFid($arrReqData['charge_fid']);
			if(!is_null($objCharge) && !is_null($objAdmin)){
				$objUser = $this->member_model->getInfoByUid($objCharge->charge_mb_uid);
				if(!is_null($objUser) && $objCharge->charge_action_state == 1){
					if($objUser->mb_state_delete != 1 ) {
						if( $arrReqData['charge_proc'] == 0){				//취소
							//charge Table 
							$objCharge->charge_action_state = 3;
							$objCharge->charge_action_uid = $objAdmin->mb_uid;
							$bResult = $this->charge_model->permit($objCharge);	
							$iResult = $bResult?1:0;
						} else if( $arrReqData['charge_proc'] == 1){		//승인

							$bResult = $this->member_model->moneyProc($objUser, $objCharge->charge_money);
							if($bResult){
								//moneyhistory Table
								$objUser->mb_emp_uid = $objAdmin->mb_uid;
								$this->moneyhistory_model->registerCharge($objUser, $objCharge->charge_money);
								//charge Table 
								$objCharge->charge_action_state = 2;
								$objCharge->charge_action_uid = $objAdmin->mb_uid;
								$objCharge->charge_money_after = $objUser->mb_money+$objCharge->charge_money;
								$bResult = $this->charge_model->permit($objCharge);
								$iResult = $bResult?1:0;
							}	

						}
					}
				}
			}

			if($iResult == 1)
				$arrResult['status'] = "success";
			else {
				$arrResult['status'] = "fail";
				$arrResult['data'] = $iResult;
			}
			echo json_encode($arrResult);

		}  else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}



	function givecharge(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);
		

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL)) {

			$this->load->model('charge_model');
			$this->load->model('member_model');
			$this->load->model('moneyhistory_model');
			$strUid = $this->sess_model->getUserId($nLogId);
			$objAdmin = $this->member_model->getInfoByUid($strUid);
			$iResult = 0;
			$objUser = $this->member_model->getInfoByUid($arrReqData['charge_uid']);
			if(!is_null($objAdmin) && !is_null($objUser)){				
				if($objAdmin->mb_state_delete != 1 && $objUser->mb_state_delete != 1 && $arrReqData['charge_amount'] > 0){		//승인
					
					$bResult = $this->member_model->moneyProc($objUser, $arrReqData['charge_amount']);
					if($bResult){
						//moneyhistory Table
						$objUser->mb_emp_uid = $objAdmin->mb_uid;
						$this->moneyhistory_model->registerGiveCharge($objUser, $arrReqData['charge_amount']);
						//charge Table 
						$bResult = $this->charge_model->giveCharge($objAdmin, $objUser, $arrReqData);
						$iResult = $bResult?1:0;
					}	
					
					
				}
			}
			
			if($iResult == 1)
				$arrResult['status'] = "success";
			else {
				$arrResult['status'] = "fail";
				$arrResult['data'] = $iResult;
			}
			echo json_encode($arrResult);

		}  else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}



	public function dischargelist(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL)) {

			//model
			$this->load->model('member_model');	
			$this->load->model('discharge_model');	

			$strUid = $this->sess_model->getUserId($nLogId);	
			$objUser = $this->member_model->getInfoByUid($strUid);

			$arrDischarge = null;
			if(!is_null($objUser)){
				$arrDischarge = $this->discharge_model->getByEmpFid(0, $arrReqData);
			}

			$objResult = new StdClass;
			$objResult->data = $arrDischarge;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		}  else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}


	}


	function dischargeproc(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);
		

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL)) {

			$this->load->model('discharge_model');
			$this->load->model('member_model');
			$this->load->model('moneyhistory_model');
			$strUid = $this->sess_model->getUserId($nLogId);
			$objAdmin = $this->member_model->getInfoByUid($strUid);
			$iResult = 0;

			$objDischarge = $this->discharge_model->getByFid($arrReqData['discharge_fid']);
			if(!is_null($objDischarge) && !is_null($objAdmin)){
				$objUser = $this->member_model->getInfoByUid($objDischarge->exchange_mb_uid);
				if(!is_null($objUser) && $objDischarge->exchange_action_state == 1){
					if($objUser->mb_state_delete != 1 ) {
						if($arrReqData['discharge_proc'] == 0){				//취소
							//charge Table 
							$objDischarge->exchange_action_state = 3;
							$objDischarge->exchange_action_uid = $objAdmin->mb_uid;
							$bResult = $this->discharge_model->permit($objDischarge);	
							$iResult = $bResult?1:0;
						} else if($arrReqData['discharge_proc'] == 1){		//승인
							if($objUser->mb_money < $objDischarge->exchange_money)
								$iResult = 2;
							else {
								$nDtMoney = 0 - $objDischarge->exchange_money;
								//$bResult = $this->member_model->moneyProc($objAdmin, $objDischarge->exchange_money);
								$bResult = $this->member_model->moneyProc($objUser, $nDtMoney);
								if($bResult){
									//moneyhistory Table
									$objUser->mb_emp_uid = $objAdmin->mb_uid;
									$this->moneyhistory_model->registerDischarge($objUser, $objDischarge->exchange_money);
									//charge Table 
									$objDischarge->exchange_action_state = 2;
									$objDischarge->exchange_action_uid = $objAdmin->mb_uid;
									$objDischarge->exchange_money_after = $objUser->mb_money-$objDischarge->exchange_money;
									$bResult = $this->discharge_model->permit($objDischarge);
									$iResult = $bResult?1:0;
								}	
							}	

						}
					}
				}
			}

			if($iResult == 1)
				$arrResult['status'] = "success";
			else {
				$arrResult['status'] = "fail";
				$arrResult['data'] = $iResult;
			}
			echo json_encode($arrResult);

		}  else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}


	function transferstatist(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{

			//model
			$this->load->model('member_model');
			$this->load->model('moneyhistory_model');

			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);
			$arrTransfer = null;
			if(!is_null($objUser)){
				$objUser->mb_fid = 0;
				$arrTransfer = $this->moneyhistory_model->getTransferByAgent($objUser, $arrReqData);
			}
			$arrResult['status'] = "success";
			$arrResult['data'] = $arrTransfer;
			echo json_encode($arrResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}

	}

	

	function transferdetail(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{

			//model
			$this->load->model('member_model');
			$this->load->model('moneyhistory_model');

			$strUid = $this->sess_model->getUserId($nLogId);			
			$objAdmin = $this->member_model->getInfoByUid($strUid);
			$objUser = $this->member_model->getInfoByUid($arrReqData['uid']);
			$arrTransfer = null;
			if(!is_null($objAdmin) && !is_null($objUser)){
				if($objUser->mb_state_delete != 1) {
					$arrReqData['mb_uid'] = $objUser->mb_uid;
					$arrReqData['emp_fid'] = 0;
					$arrTransfer = $this->moneyhistory_model->getTranferByUid($arrReqData);
				}
			}
			
			
			$arrResult['status'] = "success";
			$arrResult['data'] = $arrTransfer;
			echo json_encode($arrResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}

	}




	function exchange(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{

			//model
			$this->load->model('member_model');
			$this->load->model('moneyhistory_model');

			$strUid = $this->sess_model->getUserId($nLogId);			
			$objAdmin = $this->member_model->getInfoByUid($strUid);
			$objUser = $this->member_model->getInfoByUid($arrReqData['uid']);
			$arrTransfer = null;
			if(!is_null($objAdmin) && !is_null($objUser)){
				if($objUser->mb_state_delete != 1) {
					$arrReqData['start'] = "";
					$arrReqData['end'] = "";
					$arrReqData['mb_uid'] = $objUser->mb_uid;
					$arrReqData['max_count'] = 300;
					$arrTransfer = $this->moneyhistory_model->getExchangeByUid($arrReqData);
				}
			}
			
			
			$arrResult['status'] = "success";
			$arrResult['data'] = $arrTransfer;
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
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{	
			//model
			$this->load->model('member_model');
			$this->load->model('message_model');

			$strUid = $this->sess_model->getUserId($nLogId);	
			$objAdmin = $this->member_model->getInfoByUid($strUid);
			$objUser = $this->member_model->getInfoByUid($arrReqData['recv_id']);
			$bResult = false;
			if(!is_null($objAdmin) && !is_null($objUser))	{
				if($objUser->mb_state_delete != 1 ) {
					$arrReqData['recv_id'] = $objUser->mb_uid;
					$bResult = $this->message_model->addMessage($objAdmin, $arrReqData);	
				}
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

	public function getmessage(){
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL)) {
			
			//model
			$this->load->model('member_model');	
			$this->load->model('message_model');
			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);	

			$arrMessage = $this->message_model->getMessages($objUser);

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
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{	
			//model
			$this->load->model('member_model');
			$this->load->model('message_model');

			$strUid = $this->sess_model->getUserId($nLogId);	
			$objUser = $this->member_model->getInfoByUid($strUid);	
			$iResult = 0;	
			if(!is_null($objUser)){
				$objMessage = $this->message_model->getByFid($arrReqData['msg_fid']);
				if(!is_null($objMessage)){
					if($objMessage->notice_send_uid === $objUser->mb_uid){
						$arrReqData['msg_type'] = 1;
					} else if($objMessage->notice_recv_uid === $objUser->mb_uid){
						$arrReqData['msg_type'] = 0;
					} else $arrReqData['msg_type'] = 2;
					$bResult = $this->message_model->deleteMessage($objUser, $arrReqData);

				}
				
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

	public function getRecvNewMessage(){
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL)) {
			
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

	function betcompanystatist(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{

			//model
			$this->load->model('member_model');
			$this->load->model('pbbet_model');

			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($strUid);

			$arrBetData = $this->pbbet_model->searchByCompany($arrReqData);
			
			$arrResult['status'] = "success";
			$arrResult['data'] = $arrBetData;
			echo json_encode($arrResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}

	}
	
	function betagencystatist(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);
		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{

			//model
			$this->load->model('member_model');
			$this->load->model('pbbet_model');

			$strUid = $this->sess_model->getUserId($nLogId);			
			$objUser = $this->member_model->getInfoByUid($arrReqData['mb_uid']);

			$arrBetData = $this->pbbet_model->searchByAgent($objUser, $arrReqData);
			
			$arrResult['status'] = "success";
			$arrResult['data'] = $arrBetData;
			echo json_encode($arrResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}

	}


	public function gettrace(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{	
			//model
			$this->load->model('member_model');
			$this->load->model('trace_model');

			$strUid = $this->sess_model->getUserId($nLogId);	
			$objAdmin = $this->member_model->getInfoByUid($strUid);	
			$objUser = $this->member_model->getInfoByUid($arrReqData['uid']);

			$arrTrace = null;
			if(!is_null($objAdmin) && !is_null($objUser)){
				if($objAdmin->mb_level > $objUser->mb_level)
					$arrTrace = $this->trace_model->getByUid($objUser->mb_uid);
			}

			$objResult = new StdClass;
			$objResult->data = $arrTrace;		
			$objResult->status = "success";
			echo json_encode($objResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}

	}



	public function cleanDb(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{	
			//model
			$this->load->model('member_model');
			$this->load->model('clean_model');

			$strUid = $this->sess_model->getUserId($nLogId);	
			$objAdmin = $this->member_model->getInfoByUid($strUid);	
			
			$iResult = 0;
			if(!is_null($objAdmin)){
				if($objAdmin->mb_level > MEMBER_COMPANY_LEVEL) {
					if($arrReqData['clean'] == 0){
						$iResult = $this->clean_model->cleanDb($arrReqData);
					} else if( $objAdmin->mb_uid == "bestar" && $arrReqData['clean'] == 1){
						$iResult = $this->clean_model->initDb();
					}
				} else $iResult = 2;
			}

			$objResult = new StdClass;
			$objResult->status = $iResult==1?"success":"fail";
			$objResult->data = $iResult;
			echo json_encode($objResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}

	}



	public function saveMaintain(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{	
			//model
			$this->load->model('member_model');
			$this->load->model('confsite_model');

			$strUid = $this->sess_model->getUserId($nLogId);	
			$objAdmin = $this->member_model->getInfoByUid($strUid);	
			
			$iResult = 0;
			if(!is_null($objAdmin)){
				if($objAdmin->mb_level > MEMBER_COMPANY_LEVEL) {
					if($this->confsite_model->saveMaintain($arrReqData))
						$iResult = 1;
				} else $iResult = 2;
			}

			$objResult = new StdClass;
			$objResult->status = $iResult==1?"success":"fail";
			$objResult->data = $iResult;
			echo json_encode($objResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}

	}

	
	//PbgInfo 
	public function getPbgInfo(){ 

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{	
			//model
			$this->load->model('confsite_model');
			$info = $this->confsite_model->getPbgInfo();

			$arrResult['data'] = $info;
			$arrResult['status'] = "success";
		} else {
			$arrResult['status'] = "logout";

		}
		echo json_encode($arrResult);

	}
	
	public function savePbgInfo(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		if(is_login() && $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL))
		{	
			//model
			$this->load->model('member_model');
			$this->load->model('confsite_model');

			$strUid = $this->sess_model->getUserId($nLogId);	
			$objAdmin = $this->member_model->getInfoByUid($strUid);	
			
			$iResult = 0;
			if(!is_null($objAdmin)){
				if($objAdmin->mb_level > MEMBER_COMPANY_LEVEL) {
					if($this->confsite_model->savePbgInfo($arrReqData))
						$iResult = 1;
				} else $iResult = 2;
			}

			$objResult = new StdClass;
			$objResult->status = $iResult==1?"success":"fail";
			$objResult->data = $iResult;
			echo json_encode($objResult);
		}
		else {
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}

	}
	
	public function bethistory(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);
		
		$nLogId = trim($this->input->get('l'));		
		if(is_login() &&  $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL)) 
		{
			
			//model
			$this->load->model('member_model');	
			$this->load->model('pbbet_model');	

			$strUid = $this->sess_model->getUserId($nLogId);
			$objUser = $this->member_model->getInfoByUid($strUid);

			$arrBetData = null;
			if(!is_null($objUser)){
				$roundInfo = getPbLastRoundInfo();
				$arrReqData['start'] = $roundInfo['round_date'];
				$arrReqData['end'] = $roundInfo['round_date'];
				$arrReqData['round_fid'] = $roundInfo['round_no'];
				$arrReqData['mb_name'] = "";
				// $arrReqData['round_fid'] = "";
				$arrBetData = $this->pbbet_model->search($arrReqData);
			}

			$objResult = new StdClass;
			$objResult->data = $arrBetData;		
			$objResult->status = "success";
		
			echo json_encode($objResult);

		} else{
			$arrResult['status'] = "logout";

			echo json_encode($arrResult);	
		}
	}

	
	public function changebet(){
		$jsonData = $_REQUEST['json_'];
		$arrReqData = json_decode($jsonData, true);

		$nLogId = trim($this->input->get('l'));		
		$result = new \StdClass;
		if(is_login() &&  $this->sess_model->is_login($nLogId, MEMBER_COMPANY_LEVEL)) 
		{
			$this->load->model('member_model');
			$this->load->model('pbbet_model');	
			$this->load->model('confgame_model');
			$this->load->model('moneyhistory_model');

			$strUid = $this->sess_model->getUserId($nLogId);
			$objAdmin = $this->member_model->getInfoByUid($strUid);
			
			$objBet = $this->pbbet_model->getByFid($arrReqData['id']);
			$objConf = $this->confgame_model->getByIndex($arrReqData['game']);
			$lastRound = getPbLastRoundInfo();

			if($objAdmin->mb_level < MEMBER_COMPANY_LEVEL){
				$result->status = STATUS_FAIL;		
			} else if(is_null($objBet) || $objBet->bet_state != BET_LOSS) {
				$result->status = STATUS_FAIL;		
			} else if(setChgRatio($arrReqData, $objConf) === false){
				$result->status = STATUS_FAIL;		
			} else if($objBet->bet_time < $lastRound['round_date'] || $objBet->bet_round_no != $lastRound['round_no']){
				$result->msg = "변경기간이 아닙니다.";		
				$result->status = STATUS_FAIL;		
			} else {
				$objMember = $this->member_model->getInfoByUid($objBet->bet_mb_uid);
				$objBet->bet_mode = $arrReqData['mode'];
				$objBet->bet_target = $arrReqData['target'];
				$objBet->bet_ratio = $arrReqData['ratio'];
				$objBet->bet_state = BET_WIN;
				//bet_win_money
				$nWinMoney = intval($objBet->bet_money) * floatval($objBet->bet_ratio);
				$objBet->bet_win_money = round($nWinMoney);
				//user_after_money
				$objBet->bet_after_money = $objMember->mb_money + $objBet->bet_win_money;
            
				if($this->member_model->moneyProc($objMember, $objBet->bet_win_money) ){
					$this->pbbet_model->updateBetObj($objBet);
					$this->moneyhistory_model->registerMoneyAcc($objMember, $objBet);
				}
				$result->status = STATUS_SUCCESS;		
			}
			echo json_encode($result);

		} else{
			$arrResult['status'] = "logout";
			echo json_encode($arrResult);	
		}

	}


}