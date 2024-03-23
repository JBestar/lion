<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="ko">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="0">
	<title><?=$site_name?></title>
	<link rel="stylesheet" href="<?php echo base_url('assets/css/maintain.css');?>">
	
	<script type="text/javascript" src="<?php echo base_url('assets/jslib/jquery-1.12.4.min.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo base_url('assets/jslib/jquery-ui-1.12.1.min.js'); ?>"></script>

</head>

<script>
	
</script>

<body>
	<div class="ibg">
		<div class="login_warp" >
			<?php if(!is_null($objMaintain) && $objMaintain->conf_active == 1) {  ?>
			<div class="lg_box on" id="login_box">
				<h2><img src="<?php echo base_url('assets/image/notice-1.png'); ?>"></h2>
				<div class="content">
				<?php if(strlen($objMaintain->conf_content)>1) {  ?>
				
					<textarea class="notice-content" id="notice-content-id" readonly><?=$objMaintain->conf_content?>
					</textarea>
					
					<?php } ?>
					<p onclick="location.reload()" class="content-footer">Homepage</p>
				</div>
			</div>
			<?php } ?>

		</div>
	</div>
	<!--
	<div class="vimeo_cover">
		
		<video muted autoplay loop id="vimeo">
		    <source src="<?php echo base_url('assets/image/login-video.mp4'); ?>" type="video/mp4">
		    <strong>Your browser does not support the video tag.</strong>
		</video>

	</div>
	-->
	
</body>

<script>
	$(document).ready(function(){

		fitContent();
		maintainLoop();
	});

	function fitContent(){
		var elemNoticeContent = document.getElementById ( "notice-content-id");

		elemNoticeContent.style.height = '1px';
		elemNoticeContent.style.height = (elemNoticeContent.scrollHeight + 1) + 'px';

	}


	function checkMaintain(){
		var objData = { "maintain":0};
		var jsonData = JSON.stringify(objData);

		$.ajax({
		type: "POST",
		dataType: "json",
		url:"/api/checkMaintain",
		data: {json_: jsonData},
		success: function(jResult) {
			if(jResult.status == "success")
			{	
					if(jResult.data.conf_active != 1){
						location.reload();
					} else {
						if(jResult.data.conf_content.length > 1){
							$("#notice-content-id").val(jResult.data.conf_content);
							fitContent();
						}
						
					}
				}}, 
			error:function(request,status,error){
				//console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
			}
		});
	}

	function maintainLoop() {
		checkMaintain();
		// 30초뒤에 다시 실행
	    setTimeout( function() {
			maintainLoop();        
	    }, 30000 );

	}

</script>

</html>