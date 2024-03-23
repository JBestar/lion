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
    
    
    public function getPbgInfo(){
        $info = ['site'=>'', 'uid'=>'', 'pwd'=>''];
        $objConfig = $this->db->get_where($this->mTableName, array('conf_id'=>CONF_PBG_ACC))->row();
        if(!is_null($objConfig)){
            $data = explode("|", $objConfig->conf_content);
            if(count($data) > 2){
                $info['site'] = trim($data[0]);
                $info['uid'] = trim($data[1]);
                $info['pwd'] = trim($data[2]);
            }

        }

        return $info;
    }

    public function savePbgInfo($arrData){

        $arrBatch = array();
        
        $updateData['conf_id'] = CONF_PBG_ACC;
        $updateData['conf_content'] = $arrData['site']."|".$arrData['uid']."|".$arrData['pwd'];
        $arrBatch[0] = $updateData;

        return  $this->db->update_batch($this->mTableName, $arrBatch, 'conf_id');

    }
}