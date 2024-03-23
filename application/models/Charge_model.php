
<?php
class Charge_model extends CI_Model {
	
	private $mTableName ;

    function __construct()
    {
        parent::__construct();

        $this->mTableName = "member_charge";
    }

    public function getByFid($nFId){
        $this->db->where('charge_fid', $nFId);
        $this->db->where('charge_client_delete', '0');
        return $this->db->get($this->mTableName)->row();
    }



    public function getByUid($strId){
        $this->db->where('charge_mb_uid', $strId);
        $this->db->where('charge_client_delete', '0');
        $this->db->limit(30);
        $this->db->order_by('charge_time_require', 'DESC');
        return $this->db->get($this->mTableName)->result();
    }

    public function getByEmpFid($nEmpFId, $reqData){
        $this->db->where('charge_emp_fid', $nEmpFId);
        $this->db->where('charge_client_delete', '0');
        if(strlen($reqData['start']) > 0){
            $this->db->where('charge_time_require >', $reqData['start']." 00:00:00");
        }
        if(strlen($reqData['end']) > 0){
            $this->db->where('charge_time_require <=', $reqData['end']." 23:59:59");
            $this->db->limit(1000);
        } else 
            $this->db->limit(100);

        $this->db->order_by('charge_time_require', 'DESC');
        return $this->db->get($this->mTableName)->result();
    }

    public function waitChargeByEmpFid($nEmpFId){
        $this->db->where('charge_emp_fid', $nEmpFId);
        $this->db->where('charge_client_delete', 0);
        $this->db->where('charge_action_state', 1);

        return $this->db->get($this->mTableName)->row();
    }

    public function waitCharge($objUser){
        $this->db->where('charge_mb_uid', $objUser->mb_uid);
        $this->db->where('charge_client_delete', 0);
        $this->db->where('charge_action_state', 1);

        return $this->db->get($this->mTableName)->row();
    }


    public function addCharge($objUser, $arrChargeInfo){

    	if(is_null($objUser)) return false;
    	if($arrChargeInfo['charge_amount'] < 1) return false;
    	
    	 //자료기지 등록
    	$this->db->set('charge_emp_fid', $objUser->mb_emp_fid);
        $this->db->set('charge_mb_uid', $objUser->mb_uid);
        $this->db->set('charge_mb_name', $arrChargeInfo['charge_name']);
        $this->db->set('charge_type', 0);
        $this->db->set('charge_money', $arrChargeInfo['charge_amount']);
        $this->db->set('charge_time_require', 'NOW()', false);
        //1:요청, 2:처리완료 3:거절
        $this->db->set('charge_action_state', 1);
        $this->db->set('charge_money_before', $objUser->mb_money);
		
        return $this->db->insert($this->mTableName);
        
    }

    public function giveCharge($objAdmin, $objUser, $arrChargeInfo){

    	if(is_null($objUser) || is_null($objAdmin)) return false;
        
    	if($arrChargeInfo['charge_amount'] < 1) return false;
    	
    	 //자료기지 등록
    	$this->db->set('charge_emp_fid', $objUser->mb_emp_fid);
        $this->db->set('charge_mb_uid', $objUser->mb_uid);
        $this->db->set('charge_mb_name', $objUser->mb_nickname);
        $this->db->set('charge_type', 1);
        $this->db->set('charge_money', $arrChargeInfo['charge_amount']);
        $this->db->set('charge_time_require', 'NOW()', false);
        //1:요청, 2:처리완료 3:거절
        $this->db->set('charge_action_state', 2);
        $this->db->set('charge_action_uid', $objAdmin->mb_uid);
		$this->db->set('charge_time_process', 'NOW()', false);
		$this->db->set('charge_money_before', $objUser->mb_money);
        $this->db->set('charge_money_after', $objUser->mb_money+$arrChargeInfo['charge_amount']);

        return $this->db->insert($this->mTableName);
        
    }
    
    public function search($arrReqData){
        $this->db->where('charge_mb_uid', $arrReqData['mb_uid']);
        $this->db->where('charge_client_delete', '0');
        $this->db->where('charge_type', '0');
        $this->db->limit(30);
        $this->db->order_by('charge_time_require', 'DESC');
        return $this->db->get($this->mTableName)->result();
    }

    
    function permit($objCharge){

		$this->db->set('charge_action_state', $objCharge->charge_action_state);
		$this->db->set('charge_action_uid', $objCharge->charge_action_uid);
		$this->db->set('charge_time_process', 'NOW()', false);
		$this->db->set('charge_money_after', $objCharge->charge_money_after);

		$this->db->where('charge_fid', $objCharge->charge_fid);
    	return $this->db->update($this->mTableName);
    }


}