<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="ko">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<title><?=$site_name?></title>
   
    <link rel="stylesheet" href="<?php echo base_url('assets/css/all.css');?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css');?>">
    
    <script src="<?php echo base_url('assets/jslib/jquery-1.12.4.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/jslib/jquery-ui-1.12.1.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/jslib/fontawesome.js'); ?>"></script>
  	
    <script src="<?php echo base_url('assets/jslib/moment.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/jslib/daterangepicker.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?php echo base_url('assets/jslib/daterangepicker.css');?>">
    
    <script src="<?php echo base_url('assets/js/worker.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/common.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/admin/header-adm.js?v=4');?>"></script>
    <!-- <script src="<?php echo base_url('assets/js/admin/header-adm.js?v=').time();?>"></script> -->

</head>

<body style="min-width: 1220px;">

<div class="v-application app v-application--is-ltr theme--light">
    <div class="v-application--wrap">
        <div class="el-row" style="height: 100%;">


            <div class="el-col el-col-4" style="height: 100%; background: url('/assets/image/aside.jpg');">
                <ul class="pa-0 el-menu" style="height: 100%; background-color: rgba(27, 27, 27, 0.74);">
                    <div class="text-center pa-2" style="border-bottom: 2px solid rgb(255, 255, 255);">
                        <div class="el-image logo ">
                            <img src="/assets/image/logo.png" class="el-image__inner"><!---->
                        </div>
                    </div> 
                    <li class="el-menu-item <?=$menuitem_1?>" onclick="clickMenu(1);">총판관리</li> 
                    <li class="el-menu-item <?=$menuitem_2?>" onclick="clickMenu(2);">매일통계</li> 
                    <li class="el-menu-item <?=$menuitem_3?>" onclick="clickMenu(3);">총판충전신청</li> 
                    <li class="el-menu-item <?=$menuitem_4?>" onclick="clickMenu(4);">총판환전신청</li> 
                    <li class="el-menu-item <?=$menuitem_5?>" onclick="clickMenu(5);">충환전내역</li> 
                    <li class="el-menu-item <?=$menuitem_6?>" onclick="clickMenu(6);">공지</li> 
                    <li class="el-menu-item <?=$menuitem_7?>" onclick="clickMenu(7);">총판거래내역</li> 
                    <li class="el-menu-item <?=$menuitem_8?>" onclick="clickMenu(8);">아이피추적</li> 
                    <!-- <li class="el-menu-item <?=$menuitem_9?>" onclick="clickMenu(9);">구매변경</li>  -->
                    <li class="el-menu-item"  onclick="clickMenu(10);">로그아웃</li>
                </ul>
            </div>


            <div class="el-col el-col-20">

                <header class="el-header" style="height: 60px; background: rgb(96, 98, 102); line-height: 60px; color: rgb(255, 255, 255);">
                    <div class="el-row">
                        <div class="el-col el-col-16">
                            <span id="emp-name-id">Administrator</span>， Welcome！
                        </div> 
                        <div class="el-col el-col-8" style="text-align: right; display:block;">
                            <!-- <button type="button" class="el-button el-button--success el-button--mini" onclick="showPbgDlg();">
                                <span>PBG등록</span>
                            </button> -->
                            <button type="button" class="el-button el-button--primary el-button--mini" onclick="showCleanDlg();">
                                <span>디비정리</span>
                            </button>
                            <button type="button" class="el-button el-button--danger el-button--mini" onclick="cleanDb(1);" style="display:none;">
                                <span>디비초기화</span>
                            </button>
                            <button type="button" class="el-button el-button--danger el-button--mini" onclick="showMaintainDlg();">
                                <span>사이트점검</span>
                            </button>
                            
                        </div>
                    </div>
                </header>
                <marquee class="marquee"  id="message-marquee-id"></marquee>





