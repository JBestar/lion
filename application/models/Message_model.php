<?php
class Message_model extends CI_Model {
	
	private $mTableName ;

    function __construct()
    {
        parent::__construct();

        $this->mTableName = "board_notice";
    }

    
    public function getByFid($nFid){
        
        $this->db->where('notice_type', 3);
        $this->db->where('notice_fid', $nFid);
        return $this->db->get($this->mTableName)->row();
    }


    function addMessage($objUser, $arrReqData)
    {
        if(is_null($objUser)) return false;
        if(!array_key_exists('recv_id', $arrReqData)) return false;
        //3-문의 
        $this->db->set('notice_type', 3);
        $this->db->set('notice_title', $arrReqData['title']);
        $this->db->set('notice_content', $arrReqData['content']);
        $this->db->set('notice_send_uid', $objUser->mb_uid);
        $this->db->set('notice_recv_uid', $arrReqData['recv_id']);
        $this->db->set('notice_read_count', 0);
        $this->db->set('notice_time_create', 'NOW()', false);
        

        return $this->db->insert($this->mTableName);
    }
    
     

    public function getMessages($objUser){
        if(is_null($objUser))
            return null;

        $nCount = 20;
        $strSql = " SELECT * FROM ".$this->mTableName;
        $strSql.= " WHERE notice_type = 3 AND (  (notice_send_uid = '".$objUser->mb_uid."' AND notice_send_delete = 0 )";
        $strSql.= " OR (notice_recv_uid = '".$objUser->mb_uid."' AND notice_recv_delete = 0) ) ";
        $strSql.="  ORDER BY notice_fid DESC LIMIT 0, ".$nCount;
        $query = $this -> db -> query($strSql);
        $result = $query -> result();
        
        return $result;
    }

    public function getSendMessage($objUser){
        if(is_null($objUser))
            return null;

        $this->db->where('notice_type', 3);
        $this->db->where('notice_send_delete', '0');
        $this->db->where('notice_send_uid', $objUser->mb_uid);
        $this->db->limit(30);
        $this->db->order_by('notice_time_create', 'DESC');

        return $this->db->get($this->mTableName)->result();
    }

    public function getRecvMessage($objUser, $nCount){
        if(is_null($objUser))
            return null;

        $this->db->where('notice_type', 3);
        $this->db->where('notice_recv_delete', '0');
        $this->db->where('notice_recv_uid', $objUser->mb_uid);
        $this->db->limit($nCount);
        $this->db->order_by('notice_time_create', 'DESC');

        return $this->db->get($this->mTableName)->result();
    }

    function deleteMessage($objUser, $arrReqData){
        if(is_null($objUser))
            return false;

        $bResult = false;

        if($arrReqData['msg_type']==0){
            $this->db->set('notice_recv_delete', 1);

            $this->db->where('notice_type', 3);
            $this->db->where('notice_recv_uid', $objUser->mb_uid);
            $this->db->where('notice_fid', $arrReqData['msg_fid']);
            $bResult = $this->db->update($this->mTableName);
        } else if($arrReqData['msg_type']==1){
            $this->db->set('notice_send_delete', 1);

            $this->db->where('notice_type', 3);
            $this->db->where('notice_send_uid', $objUser->mb_uid);
            $this->db->where('notice_fid', $arrReqData['msg_fid']);
            $bResult = $this->db->update($this->mTableName);
        } 



        return $bResult;
    }

}