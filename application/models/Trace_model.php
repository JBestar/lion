
<?php
class Trace_model extends CI_Model {
	
	private $mTableName ;

    function __construct()
    {
        parent::__construct();

        $this->mTableName = "log_history";
    }


    public function getByUid($strUid){
        
        $strSql = " SELECT log_fid, log_mb_uid, log_type, log_ip, log_time, mb_nickname FROM ".$this->mTableName;
        $strSql.= " INNER JOIN MEMBER ON member.mb_uid = log_history.log_mb_uid AND member.mb_state_delete = 0 ";
        $strSql .= " WHERE log_mb_uid = '".$strUid."' ORDER BY log_fid DESC LIMIT 0, 30 ";

        $query = $this -> db -> query($strSql);
        $result = $query -> result();
        
        return $result; 
    }



}