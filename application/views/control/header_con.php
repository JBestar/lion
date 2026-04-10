<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="ko">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<title><?=$site_name?></title>
   
    <link rel="stylesheet" href="<?php echo base_url('assets/css/all.css');?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/control.css');?>">
    
    <script src="<?php echo base_url('assets/jslib/jquery-1.12.4.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/jslib/jquery-ui-1.12.1.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/jslib/fontawesome.js'); ?>"></script>
  	
    <script src="<?php echo base_url('assets/jslib/moment.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/jslib/daterangepicker.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?php echo base_url('assets/jslib/daterangepicker.css');?>">
    
    <script src="<?php echo base_url('assets/js/worker.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/common.js?v=1'); ?>"></script>
    <script src="<?php echo base_url('assets/js/control/header-control.js?v=4'); ?>"></script>

</head>

<body style="min-width: 1220px;">

<div class="v-application app v-application--is-ltr theme--light">
    <div class="v-application--wrap">
        <div class="el-row" style="height: 100%;">


            <div class="el-col el-col-4" style="height: 100vh;">
                <ul role="menubar" class="pa-0 el-menu" style="background-color: rgb(0, 0, 0); height: 100vh;">
                    <div class="text-center pa-2 blank" style="border-bottom: 2px solid rgb(255, 255, 255);">
                        <div class="el-image logo ">
                            <img src="/assets/image/logo.png" class="el-image__inner">
                        </div>
                    </div> 
                    <li class="el-menu-item <?=$menuitem_1?>"  onclick="clickMenu(1);">매장관리</li> 
                    <li class="el-menu-item <?=$menuitem_2?>"  onclick="clickMenu(2);">매일통계</li> 
                    <li class="el-menu-item <?=$menuitem_3?>"  onclick="clickMenu(3);">매장충전신청</li> 
                    <li class="el-menu-item <?=$menuitem_4?>"  onclick="clickMenu(4);">매장환전신청</li> 
                    <li class="el-menu-item <?=$menuitem_5?>"  onclick="clickMenu(5);">충환전내역</li> 
                    <li class="el-menu-item <?=$menuitem_6?>"  onclick="clickMenu(6);">충환전내역2</li> 
                    <li class="el-menu-item <?=$menuitem_7?>"  onclick="clickMenu(7);">공지</li> 
                    <li class="el-menu-item <?=$menuitem_8?>"  onclick="clickMenu(8);">구매취소내역</li> 
                    <li class="el-menu-item"  onclick="clickMenu(9);">로그아웃</li>
                </ul>
            </div>



            <div class="el-col el-col-20">
                <header class="el-header" style="height: 60px; background: rgb(0, 0, 0); color: yellow; line-height: 60px;">
                    <div class="el-row">
                        <div class="el-col el-col-8">
                            <span  id="emp-name-id"></span><span >， Welcome！</span>
                        </div> 
                        <div class="el-col el-col-16" style="text-align: right;">
                            <span >보유금액</span><span class="amount" id="emp-money-id"></span> 
                            <span >포인트</span><span class="amount" id="emp-mileage-id"></span> 
                            <span >수수료율</span><span class="amount" id="emp-ratio-id"></span> 
                            <button type="button" class="el-button el-button--primary" onclick="showChargeDlg();">
                                <span>충전신청</span>
                            </button> 
                            <button type="button" class="el-button el-button--primary" onclick="showDischargeDlg();">
                                <span>환전신청</span>
                            </button> 
                            <button type="button" class="el-button el-button--primary" onclick="showMileageDlg();">
                                <span>포인트전환</span>
                            </button>
                        </div>
                    </div>
                </header>
                <marquee class="marquee"  id="message-marquee-id"></marquee>



                
            
            
            
            
            





