<?php
class ConfGame_model extends CI_Model {
	
	private $mTableName ;

    function __construct()
    {
        parent::__construct();

        $this->mTableName = "conf_game";
    }

    public function getByIndex($strIndex){
        return $this->db->get_where($this->mTableName, array('game_index'=>$strIndex))->row();
    }

}