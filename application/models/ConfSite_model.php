<?php
class ConfSite_model extends CI_Model {
	
	private $mTableName ;

    function __construct()
    {
        parent::__construct();

        $this->mTableName = "conf_site";
    }

    public function getSiteName(){

        $strSiteName = SITE_NAME;
        $nConfig_SiteName = CONF_SITENAME;
        $objConfig = $this->db->get_where($this->mTableName, array('conf_id'=>$nConfig_SiteName))->row();
        if(!is_null($objConfig))
            $strSiteName = $objConfig->conf_content;
        return $strSiteName;
    }

    public function getMaintain(){

        $nConfigId = CONF_MAINTAIN;
        $objConfig = $this->db->get_where($this->mTableName, array('conf_id'=>$nConfigId))->row();
        return $objConfig;
    }

    
    public function saveMaintain($arrData){

        if($arrData == null) return false;
        if (!array_key_exists("maintain", $arrData)) return false;
        if (!array_key_exists("content", $arrData)) return false;
        
        $arrBatch = array();
        
        $updateData['conf_id'] = CONF_MAINTAIN;
        $updateData['conf_content'] = $arrData['content'];
        $updateData['conf_active'] = $arrData['maintain'];
        $arrBatch[0] = $updateData;

        return  $this->db->update_batch($this->mTableName, $arrBatch, 'conf_id');

    }

    public function getRoundStatPassword(){
        $nId = CONF_ROUNDSTAT_PWD;
        $objConfig = $this->db->get_where($this->mTableName, array('conf_id'=>$nId))->row();
        if(!is_null($objConfig) && strlen(trim($objConfig->conf_content)) > 0)
            return trim($objConfig->conf_content);
        return '3750';
    }

    public function saveRoundStatPassword($strPwd){
        if(strlen(trim($strPwd)) < 1)
            return false;
        $nId = CONF_ROUNDSTAT_PWD;
        $row = $this->db->get_where($this->mTableName, array('conf_id'=>$nId))->row();
        $this->db->set('conf_content', trim($strPwd));
        if(is_null($row)){
            $this->db->set('conf_id', $nId);
            $this->db->set('conf_memo', '회차별통계암호');
            $this->db->set('conf_active', 0);
            return $this->db->insert($this->mTableName);
        }
        $this->db->where('conf_id', $nId);
        return $this->db->update($this->mTableName);
    }
    
    
    public function getPbgInfo(){
        $info = ['site'=>'', 'uid'=>'', 'pwd'=>'', 'draw_sync_key'=>''];
        $objConfig = $this->db->get_where($this->mTableName, array('conf_id'=>CONF_PBG_ACC))->row();
        if(!is_null($objConfig)){
            $data = explode("|", $objConfig->conf_content);
            if(count($data) > 2){
                $info['site'] = trim($data[0]);
                $info['uid'] = trim($data[1]);
                $info['pwd'] = trim($data[2]);
            }
            if(count($data) > 3){
                $info['draw_sync_key'] = trim($data[3]);
            }

        }

        return $info;
    }

    public function savePbgInfo($arrData){

        $arrBatch = array();
        $dk = '';
        if(isset($arrData['draw_sync_key'])){
            $dk = trim((string) $arrData['draw_sync_key']);
        }

        $updateData['conf_id'] = CONF_PBG_ACC;
        $updateData['conf_content'] = $arrData['site']."|".$arrData['uid']."|".$arrData['pwd']."|".$dk;
        $arrBatch[0] = $updateData;

        return  $this->db->update_batch($this->mTableName, $arrBatch, 'conf_id');

    }
}