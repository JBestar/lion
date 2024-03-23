<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    public function index()
	{
        $this->load->model('confsite_model');
		$strSiteName = $this->confsite_model->getSiteName();
        $objMaintain = $this->confsite_model->getMaintain();

        if(!is_null($objMaintain) && $objMaintain->conf_active == 1) {
			redirect( base_url().'home/maintain');
		} else{
		    $this->load->view('client/login', array("site_name"=>$strSiteName));
        }
    }

}