
<?php
class Discharge_model extends CI_Model {
	
	private $mTableName ;

    function __construct()
    {
        parent::__construct();

        $this->mTableName = "member_exchange";
    }

    public function getByFid($nFId){
        $this->db->where('exchange_fid', $nFId);
        $this->db->where('exchange_client_delete', '0');
        return $this->db->get($this->mTableName)->row();
    }

    public function getByUid($strId){
         $this->db->where('exchange_mb_uid', $strId);
        $this->db->where('exchange_client_delete', '0');
        $this->db->limit(30);
        $this->db->order_by('exchange_time_require', 'DESC');
        return $this->db->get($this->mTableName)->result();
        
    }


    public function getByEmpFid($nEmpFId, $reqData){

        $this->db->where('exchange_emp_fid', $nEmpFId);
        $this->db->where('exchange_client_delete', '0');
        if(strlen($reqData['start']) > 0){
            $this->db->where('exchange_time_require >', $reqData['start']." 00:00:00");
        }
        if(strlen($reqData['end']) > 0){
            $this->db->where('exchange_time_require <=', $reqData['end']." 23:59:59");
            $this->db->limit(1000);
        } else 
            $this->db->limit(100);

        $this->db->order_by('exchange_time_require', 'DESC');
        return $this->db->get($this->mTableName)->result();
    }

    
    public function waitDichargeByEmpFid($nEmpFId){
        $this->db->where('exchange_emp_fid', $nEmpFId);
        $this->db->where('exchange_client_delete', 0);
        $this->db->where('exchange_action_state', 1);

        return $this->db->get($this->mTableName)->row();
    }

    public function waitDicharge($objUser){
        $this->db->where('exchange_mb_uid', $objUser->mb_uid);
        $this->db->where('exchange_client_delete', 0);
        $this->db->where('exchange_action_state', 1);

        return $this->db->get($this->mTableName)->row();
    }


    public function addDischarge($objUser, $arrExchangeInfo){

        if(is_null($objUser)) return 0;
    	if (!array_key_exists("discharge_amount", $arrExchangeInfo)) return 0;
    	
    	if($arrExchangeInfo['discharge_amount'] < 10000) return 6;
        if($objUser->mb_money < $arrExchangeInfo['discharge_amount']) return 5;
    	
    	 //자료기지 등록
    	$this->db->set('exchange_emp_fid', $objUser->mb_emp_fid);
        $this->db->set('exchange_mb_uid', $objUser->mb_uid);
        $this->db->set('exchange_type', 0);
        $this->db->set('exchange_money', $arrExchangeInfo['discharge_amount']);
        $this->db->set('exchange_time_require', 'NOW()', false);
        //1:요청, 2:처리완료 3:거절
        $this->db->set('exchange_action_state', 1);

        $this->db->set('exchange_bank_name', $arrExchangeInfo['discharge_bank']);
        $this->db->set('exchange_bank_owner', $arrExchangeInfo['discharge_owner']);
        $this->db->set('exchange_bank_number', $arrExchangeInfo['discharge_number']);

        $this->db->set('exchange_money_before', $objUser->mb_money);
        if(array_key_exists('discharge_after', $arrExchangeInfo))
            $this->db->set('exchange_money_after', $arrExchangeInfo['discharge_after']);

        $bReault = $this->db->insert($this->mTableName);
        return $bReault ? 1:0;
    }

    public function giveDischarge($objAdmin, $objUser, $arrDischargeInfo){

    	if(is_null($objUser) || is_null($objAdmin)) return false;
        
        if($arrDischargeInfo['discharge_amount'] < 1) return false;
    	
    	 //자료기지 등록
    	$this->db->set('exchange_emp_fid', $objUser->mb_emp_fid);
        $this->db->set('exchange_mb_uid', $objUser->mb_uid);
        $this->db->set('exchange_type', 1);
        $this->db->set('exchange_money', $arrDischargeInfo['discharge_amount']);
        $this->db->set('exchange_time_require', 'NOW()', false);
        //1:요청, 2:처리완료 3:거절
        $this->db->set('exchange_action_state', 2);
        $this->db->set('exchange_action_uid', $objAdmin->mb_uid);
		$this->db->set('exchange_time_process', 'NOW()', false);
		$this->db->set('exchange_money_before', $objUser->mb_money);
        $this->db->set('exchange_money_after', $objUser->mb_money-$arrDischargeInfo['discharge_amount']);

        return $this->db->insert($this->mTableName);
        
    }
        
    public function search($arrReqData){
        $this->db->where('exchange_mb_uid', $arrReqData['mb_uid']);
        $this->db->where('exchange_client_delete', '0');
        $this->db->limit(30);
        $this->db->order_by('exchange_time_require', 'DESC');
        return $this->db->get($this->mTableName)->result();
    }

    function permit($objDischarge){

		$this->db->set('exchange_action_state', $objDischarge->exchange_action_state);
		$this->db->set('exchange_action_uid', $objDischarge->exchange_action_uid);
		$this->db->set('exchange_time_process', 'NOW()', false);
		$this->db->set('exchange_money_after', $objDischarge->exchange_money_after);

		$this->db->where('exchange_fid', $objDischarge->exchange_fid);
    	return $this->db->update($this->mTableName);
    }

}