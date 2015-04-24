<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <title>添加/编辑-材料商-后台管理-<?php echo ($systemConfig["SITE_INFO"]["name_cms"]); ?></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php echo ($systemConfig["SITE_INFO"]["description_cms"]); ?>" />
	<meta name="keywords" content="<?php echo ($systemConfig["SITE_INFO"]["keyword_cms"]); ?>" />
        

<link href="__ROOT__/Public/css/common.css" rel="stylesheet" type="text/css" />



<script src="__ROOT__/Public/js/jquery.js"></script>

<script src="__ROOT__/Public/js/jquery.validate.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo ($systemConfig["WEB_ROOT"]); ?>/Public/easyui/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="<?php echo ($systemConfig["WEB_ROOT"]); ?>/Public/easyui/themes/icon.css">
<script type="text/javascript" src="<?php echo ($systemConfig["WEB_ROOT"]); ?>/Public/easyui/jquery.easyui.min.js"></script>
        
<link href="__ROOT__/Public/css/main.css" rel="stylesheet" />
<link href="__ROOT__/Public/css/doubleDate.css" rel="stylesheet" />
<link href="__ROOT__/Public/css/theme.css" rel="stylesheet" />



<script src="__ROOT__/Public/tuxcj/js/jquery-1.7.2.min.js"></script>
<script src="__ROOT__/Public/js/jquery.validate.js"></script>

<!--上传组件start-->
<link rel="stylesheet" type="text/css" href="__ROOT__/Public/tuxcj/css/jquery.Jcrop.min.css" media="all">
<link rel="stylesheet" type="text/css" href="__ROOT__/Public/tuxcj/js/uploadify-v3.1/uploadify.css" media="all">

<script type="text/javascript" src="__ROOT__/Public/tuxcj/js/uploadify-v3.1/jquery.uploadify-3.1.min.js"></script>

<script type="text/javascript" src="__ROOT__/Public/tuxcj/js/jquery.Jcrop.min.js"></script>
<script type="text/javascript" src="__ROOT__/Public/tuxcj/js/ThinkBox/jquery.ThinkBox.js"></script>

<link rel="stylesheet" type="text/css" href="__ROOT__/Public/tuxcj/js/ThinkBox/css/ThinkBox.css" media="all">
<!--上传组件 end-->
<style type="text/css">
.main{
	margin: 0 auto;
	padding: 15px;
	width: 750px;
	font-family: "microsoft yahei";
	background-color: #F5F5F5;
}
.cf:before,.cf:after {
	display: table;
	content: "";
	line-height: 0;
}
.cf:after {
	clear: both;
}
.cf {
	*zoom: 1;
}
.upload-area {
	position: relative;
	float: left;
	margin-right: 30px;
	width: 200px;
	height: 200px;
	background-color: #F5F5F5;
    border: 2px solid #E1E1E1;
}
.upload-area .file-tips {
	position: absolute;
	top: 90px;
	left: 0;
    padding: 0 15px;
    width: 170px;
    line-height: 1.4;
    font-size: 12px;
	color: #A8A8A3;
    text-align: center;
}
.userup-icon {
    display: inline-block;
    margin-right: 3px;
    width: 16px;
    height: 16px;
    vertical-align: -2px;
	background: url("__ROOT__/Public/tuxcj/img/userup_icon.png") no-repeat;
}
.uploadify-button {
	line-height: 120px!important;
	text-align: center;
}
.preview-area {
	float: left;
}
.tcrop {
    clear: right;
    font-size: 14px;
    font-weight: bold;
}
.update-pic .crop {
    background: url("__ROOT__/Public/tuxcj/img/mystery.png") no-repeat scroll center center #EEEEEE;
    float: left;
    margin-bottom: 20px;
    margin-top: 10px;
    overflow: hidden;
}
.crop100 {
    height: 100px;
    width: 100px;
}
.crop60 {
    height: 60px;
    margin-left: 20px;
    width: 60px;
}
.update-pic .save-pic {
    clear: left;
    margin-right: 20px;
}
.update-pic .uppic-btn {
    background-color: #348FD4;
    color: #FFFFFF;
    display: block;
    float: left;
    font-size: 16px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    vertical-align: middle;
    width: 89px;
}
.preview {
	position: absolute;
	top: 0;
	left: 0;
	z-index: 11;
	width: 200px;
	height: 200px;
	overflow: hidden;
	background:#fff;
	display: none;
}
</style>
    </head>
    <body>
        <div class="wrap"> <div class="header">
  <div class="logo"><img src="__ROOT__/Public/images/logo.jpg" /></div>
  <div class="headet_r">
    <div class="header_r_t">
      <div class="header_r_t_l">></div>
      <a href="<?php echo U('MyInfo/index');?>"><img src="__ROOT__/Public/images/ico1.png" />管理员</a> <a href="<?php echo U('Index/logout');?>"><img src="__ROOT__/Public/images/ico3.png" />退出</a> </div>
    <div class="conter_scroll_w">
      <div class="conter_scroll_bot">
        <div class="rollBox"> <img onmousedown="ISL_GoDown()" onmouseup="ISL_StopDown()" onmouseout="ISL_StopDown()"  class="img3" src="__ROOT__/Public/images/s_left.png" />
          <div class="Cont" id="ISL_Cont">
            <div class="ScrCont">
              <div id="List1"> 
                <!-- 图片列表 begin -->
                <?php if(is_array($nodelist)): $k = 0; $__LIST__ = $nodelist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k; if($actname==$vo[name]): ?><div class="pic a1">
                        <?php else: ?>
                        <div class="pic"><?php endif; ?>
                      <a href="<?php echo ($vo["url"]); ?>">
                    <p><img src="__ROOT__/Public/images/menuico1.png" /></p>
                    <?php echo ($vo["title"]); ?>
                      </a> </div><?php endforeach; endif; else: echo "" ;endif; ?>
                <!--
                <div class="pic a1"><a href="">
                  <p><img src="/Public/images/menuico1.png" /></p>
                  用户中心</a></div>
                <div class="pic"><a href="">
                  <p><img src="/Public/images/menuico2.png" /></p>
                  装修攻略</a></div>
                <div class="pic"><a href="">
                  <p><img src="/Public/images/menuico3.png" /></p>
                  明星工长</a></div>
                <div class="pic"><a href="">
                  <p><img src="/Public/images/menuico4.png" /></p>
                  设计团队</a></div>
                <div class="pic"><a href="">
                  <p><img src="/Public/images/menuico5.png" /></p>
                  团购商家</a></div>
                <div class="pic"><a href="">
                  <p><img src="/Public/images/menuico6.png" /></p>
                  团购活动</a></div>
                <div class="pic"><a href="">
                  <p><img src="/Public/images/menuico7.png" /></p>
                  社区团装</a></div>
                <div class="pic"><a href="">
                  <p><img src="/Public/images/menuico8.png" /></p>
                  装修美图</a></div>
                <div class="pic"><a href="">
                  <p><img src="/Public/images/menuico7.png" /></p>
                  用户中心</a></div>
                <div class="pic"><a href="">
                  <p><img src="/Public/images/menuico8.png" /></p>
                  装修攻略</a></div>
                -->
                
                <!-- 图片列表 end --> 
              </div>
              <div id="List2"></div>
            </div>
          </div>
          <img  onmousedown="ISL_GoUp()" onmouseup="ISL_StopUp()" onmouseout="ISL_StopUp()"  class="img4" src="__ROOT__/Public/images/s_right.png" /> </div>
      </div>
      <script src="__ROOT__/Public/js/rollBox.js" type="text/javascript"></script> 
    </div>
  </div>
</div>
 <div class="tabBox" id="Threetab">
	      <ul class="tab center_side">
                    <li></li>
                    <?php if(is_array($nodelist2)): $i = 0; $__LIST__ = $nodelist2;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($funname==$vo[name]): ?><li class="li1"> 
                               <?php else: ?>
                               <li><?php endif; ?>
		    <img src="__ROOT__/Public/images/sideico_1.png" class="img1" /><img src="__ROOT__/Public/images/sideico.png" class="img1 img2" />
                        <a href="<?php echo ($vo["url"]); ?>"><?php echo ($vo["title"]); ?></a>
                    </li><?php endforeach; endif; else: echo "" ;endif; ?>

	      </ul>
</div>

  <div class="center_r">
    <div class="center">
      <div class="center_r_t">您的位置：<a href="<?php echo U('Member/dianpu');?>" class="a1">材料商</a>>>>编辑/添加 材料商信息</div>
      <div class="center_t">
          <form action="" id="form1" name="form1" method="post">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tab center_t">
                <tr>
                    <td width="120">登录名称：</td>
                    <td><input <?php if(ACTION_NAME == 'editAdmin'): ?>disabled="disabled"<?php endif; ?> name="a_name" id="a_name" type="text" class="required" size="40" value="<?php echo ($info["a_name"]); ?>" /></td>
                </tr>
                <tr>
                    <td>密码：</td>
                    <td><input class="input" name="pwd" class="required" id="pwd" type="password" size="40" value="" /></td>
                </tr>
                <tr>
                    <td>确认密码：</td>
                    <td><input class="input" name="confirm_pwd" class="required" id="confirm_pwd" type="password" size="40" value="" /></td>
                </tr>
                <tr>
                    <td>是否启动</td>
                    <td>
                        <label><input type="radio"  name="status" id="status" <?php if($info[status]==1): ?>checked<?php endif; ?> value="1" />启动</label>
                        <label><input type="radio"  name="status" id="status" <?php if($info[status]==0): ?>checked<?php endif; ?> value="0" />禁用</label>
                    </td>
                </tr>
                <tr>
                    <td>头像上传：</td>
                    <td>
                       <div class="upload-area">
		<input type="file" id="user-pic">
		<div class="file-tips">支持JPG,PNG,GIF，图片小于1MB，尺寸不小于100*100,真实高清头像更受欢迎！</div>
		<div class="preview hidden" id="preview-hidden"></div>
	</div>
	<div class="preview-area">
		<input type="hidden" id="x" name="x" />
		<input type="hidden" id="y" name="y" />
		<input type="hidden" id="w" name="w" />
		<input type="hidden" id="h" name="h" />
		<input type="hidden" id='img_src' name='src'/>
                <input type="hidden" id='picName' value="<?php echo ($info["logo"]); ?>" name='picName'/>
		<div class="tcrop">头像预览</div>
		<div class="crop crop100"><img id="crop-preview-100" src="" alt=""></div>
		<div class="crop crop60"><img id="crop-preview-60" src="" alt=""></div>
		
	</div>
                       
    
                    </td>
                </tr> 
                <tr>
                    <td></td>
                    <td>
                        <a class="uppic-btn save-pic" href="javascript:;">保存</a>
		<a class="uppic-btn reupload-img" href="javascript:$('#user-pic').uploadify('cancel','*');">重新上传</a>
                        
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <div style="margin: 20px 0 10px;">
	<h2>头像展示：</h2>
	
	<div>
		<span style="vertical-align: top">裁切图片：</span>
                
                <img src="<?php if(empty($info[logo])): ?>__ROOT__/avatar/avatar_100.png<?php else: ?>__ROOT__/avatar/<?php echo ($info[logo]); ?>_100.jpg<?php endif; ?>" id="pic1" title="100*100"/>
                <img src="<?php if(empty($info[logo])): ?>__ROOT__/avatar/avatar_60.png<?php else: ?>__ROOT__/avatar/<?php echo ($info[logo]); ?>_60.jpg<?php endif; ?>" id="pic2" title="60*60"/>
                <img src="<?php if(empty($info[logo])): ?>__ROOT__/avatar/avatar_30.png<?php else: ?>__ROOT__/avatar/<?php echo ($info[logo]); ?>_30.jpg<?php endif; ?>" id="pic3" title="30*30"/>
	</div>
</div>
                        
                    </td>
                </tr>
                <tr>
                    <td>公司名称：</td>
                    <td><input type="text" name="dinfo[company]" size="40" id="company" value="<?php echo ($info["company"]); ?>" /></td>
                </tr>
                <tr>
                    <td>法人：</td>
                    <td>
                        <input type="text" name="dinfo[faren]" size="40" id="faren" value="<?php echo ($info["faren"]); ?>" />
                    </td>
                </tr>                
                
                <tr>
                    <td>上传营业执照：</td>
                    <td>
                        <input id="file_upload" name="file_upload" type="file" multiple="true">
            <div id="some_file_queue">

            </div>
            <div class="imgList" id="image"></div>
            <input type="hidden" name="dinfo[yingyezhizhao]" id="yingyezhizhao" value="<?php echo ($info["yingyezhizhao"]); ?>" />
            <?php if(!empty($info[yingyezhizhao])): ?><img src="__ROOT__/Uploads/product/<?php echo ($info["yingyezhizhao"]); ?>" height="100" /><?php endif; ?>
                    </td>
                </tr>
                
                <tr>
                    <td>联系人姓名：</td>
                    <td><input type="text" name="dinfo[lxrname]" size="40" id="lxrname" value="<?php echo ($info[lxrname]); ?>" /></td>
                </tr>                 
                
                <tr>
                    <td>联系人电话：</td>
                    <td><input type="text" name="dinfo[phone]" size="40" id="phone" value="<?php echo ($info[phone]); ?>" /></td>
                </tr>
                
                <tr>
                    <td>客服电话：</td>
                    <td><input type="text" name="dinfo[kefu_phone]" size="40" id="kefu_phone" value="<?php echo ($info[kefu_phone]); ?>" /></td>
                </tr>  
                
                <tr>
                    <td>收藏数值：</td>
                    <td><input type="text" name="dinfo[collect]"size="10" id="collect" value="<?php echo ($info[collect]); ?>" /></td>
                </tr>  
                <tr>
                    <td>口碑值：</td>
                    <td><input type="text" name="dinfo[koubei]" size="10" id="koubei" value="<?php echo ($info[koubei]); ?>" /></td>
                </tr>  
                <tr>
                    <td>级别：</td>
                    <td><input type="text" name="dinfo[jibie]" size="10" id="jibie" value="<?php echo ($info[jibie]); ?>" /></td>
                </tr>  
<?php if($_SESSION['my_info']['role']!=2): ?><tr>
                    <td>省份/城市：</td>
                    <td>
                        
                        <select id="pro_id" name="pro_id" onchange="getcity('pro_id','city_id')" >
                            <option value="">请选择省份</option>
                            <?php if(is_array($pro_list)): $i = 0; $__LIST__ = $pro_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["region_id"]); ?>" <?php if($info[pro_id]==$vo[region_id]): ?>selected<?php endif; ?>><?php echo ($vo["region_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                        <select id="city_id" name="city_id">
                            <option value="">请选择市</option>
                            <?php if(!empty($info[city_id])): ?><option value="<?php echo ($info["city_id"]); ?>" selected="selected"><?php echo ($info["cityname"]); ?></option><?php endif; ?>
                        </select>
                       
                    </td>
                </tr><?php endif; ?>
                <tr>
                    <td>地址：</td>
                    <td><input type="text" name="dinfo[dizhi]" size="40" id="dizhi" value="<?php echo ($info[address]); ?>" /></td>
                </tr>
                
                <tr>
                    <td colspan="2"><input type="submit" class="btn submit" id="sub" name="sub" value="提交"/></td>
                    
                </tr>
                
            </table>
            <input type="hidden" name="aid" value="<?php echo ($info["a_id"]); ?>"/>
        </form>
      </div>
    </div>
    <div class="foot"> <ul>
    <li>Copyright © 2003-2010 tlgzjlb.com All Right Reserved</li>
    <li>版权所有 中国北京·唐亮工长俱乐部</li>
</ul>
 </div>
  </div>
</div>
        
    
    </body>
</html>
<script>
    $(document).ready(function() {
        $("#form1").validate({
            rules:{
                a_name:{required:true,},
               
            },
            messages:{
                a_name:{required:"请输入用户名/邮件地址"},
                
            }
        });
		
        
		
    })
	
	 function getcity(proid,cityid){
	//	 alert(proid+","+cityid);
//		var pid=$("#"+proid).val();
		var pid=document.getElementById(proid).value;
		
		$.ajax({
			data:"fid="+pid,
			cache:false,
			url:"<?php echo U('Member/getcity');?>",
			type:"POST",
			dataType:"Json",
			success: function(msg){
				
				if(msg.status==1){
					$("#"+cityid).empty();
					var citystr="";
					var len=msg.data.length;
					var l;
					citystr+="<option value=''>请选择</option>";
					
					if(len>0){
						for(l=0;l<len;l++){
							citystr+="<option value='"+msg.data[l].region_id+"'>"+msg.data[l].region_name+"</option>";
						}
					}
					$("#"+cityid).append(citystr);
					
				}
				
			}
					
		});	
	}
	
</script>

<script>
            $(function() {
			$('#file_upload').uploadify({
				'swf'      : '__ROOT__/Public/uploadimg/uploadify.swf',
				'uploader' : '<?php echo U("Member/scimg");?>',
				'auto':true, //自动上传
				'buttonClass':'submit',//给上传按钮一个样式名称
				'buttonImage':'__ROOT__/avatar/shangchuan.png',//上传按钮加上图片
				//'buttonText':'增加图片',
				'height':28,//按钮的高度,
				'width':83,
				//'checkExisting':'<?php echo U("paicar/check-exists");?>',
				//'debug':true,
				//'fileObjName':'the_files',
				'fileSizeLimit':'2048KB',
				'fileTypeDesc':'Image Files',
				'fileTypeExts':'*.gif;*.jpg;*.png',
				//'formData':{'someKey':'11','someOne':'bb'},
				//'itemTemplate':'<div id="${fileID}" class="uploadify-queue-item">\
//					<div class="cancel">\
//						<a href="javascript:$(\'#${instanceID}\').uploadify(\'cancel\', \'${fileID}\')">X</a>\
//					</div>\
//					<span class="fileName">${fileName} (${fileSize})</span><span class="data"></span>\
//					</div>',
				'method':'post',
				'multi':true,//是否允许多个文件上传,
				'queueID'  : 'some_file_queue',
				'queueSizeLimit':22,	
				//'removeCompleted':false,	
				//'removeTimeout':10,//从队列中删除已完成的上传文件，延迟几秒
				'requeueErrors':true,
				'onUploadStart':function(file){
				//	console.log('Attempting to upload: ' + file.name);
				}
				,
				'successTimeout':5,//等待服务器响应几秒提示成功,
				'uploadLimit':1,//上传文件最大值
				'onCancel':function(file){
					//alert('the file '+file.name+' was cancelled');
				},
				'onClearQueue' : function(queueItemCount) {
            		//alert(queueItemCount + ' file(s) were removed from the queue');
        		},
				'onUploadComplete':function(file){
					//console.log(file);
				},
				'onUploadSuccess':function (file, data, response) {
					if(response){
						//console.log(data);
						$("#image").html("<image src=__ROOT__/Uploads/product/"+data+" height=100 />");	
						$("#yingyezhizhao").val(data);
						
					}else{
						alert('图片'+file.name+"上传失败");
					}
					//console.log(data);
					//console.log(response);
					//console.log(file);
					//alert('The file ' + data + ' was successfully uploaded with a response of ' + response + ':' + data);
				},
				'onQueueComplete' : function(queueData) {
					//console.log(queueData);
					//alert(queueData.uploadsSuccessful + ' files were successfully uploaded.');
				}
				
				
			});
		});
        </script>
<script type="text/javascript">
	$(function(){
		//上传头像(uploadify插件)
		$("#user-pic").uploadify({
			'queueSizeLimit' : 1,
			'removeTimeout' : 0.5,
			'preventCaching' : true,
			'multi'    : false,
			'swf' 			: '__ROOT__/Public/tuxcj/js/uploadify-v3.1/uploadify.swf',
			'uploader' 		: '<?php echo U("Member/uploadImg");?>',
			'buttonText' 	: '<i class="userup-icon"></i>上传头像',
			'width' 		: '200',
			'height' 		: '200',
			'fileTypeExts'	: '*.jpg; *.png; *.gif;',
			'onUploadSuccess' : function(file, data, response){
				var data = $.parseJSON(data);
                                //alert(data);
				if(data['status'] == 0){
					//$.ThinkBox.error(,{'delayClose':3000});
                                        alert(data['info']);
					return;
				}

				var preview = $('.upload-area').children('#preview-hidden');
				preview.show().removeClass('hidden');
				//两个预览窗口赋值
				$('.crop').children('img').attr('src',data['url']+'?random='+Math.random());
				//隐藏表单赋值
				$('#img_src').val(data['url']);
				$('#picName').val(data['picName']);			
				//绑定需要裁剪的图片
				var img = $('<img />');
				preview.append(img);
				preview.children('img').attr('src',data['url']+'?random='+Math.random());
				var crop_img = preview.children('img');
				crop_img.attr('id',"cropbox").show();
				var img = new Image();
				img.src = data['url']+'?random='+Math.random();
				//根据图片大小在画布里居中
				img.onload = function(){
					var img_height = 0;
					var img_width = 0;
					var real_height = img.height;
					var real_width = img.width;
					if(real_height > real_width && real_height > 200){
						var persent = real_height / 200;
						real_height = 200;
						real_width = real_width / persent;
					}else if(real_width > real_height && real_width > 200){
						var persent = real_width / 200;
						real_width = 200;
						real_height = real_height / persent;
					}
					if(real_height < 200){
						img_height = (200 - real_height)/2;	
					}
					if(real_width < 200){
						img_width = (200 - real_width)/2;
					}
					preview.css({width:(200-img_width)+'px',height:(200-img_height)+'px'});
					preview.css({paddingTop:img_height+'px',paddingLeft:img_width+'px'});			
				}
				//裁剪插件
				$('#cropbox').Jcrop({
		            bgColor:'#333',   //选区背景色
		            bgFade:true,      //选区背景渐显
		            fadeTime:1000,    //背景渐显时间
		            allowSelect:false, //是否可以选区，
		            allowResize:true, //是否可以调整选区大小
		            aspectRatio: 1,     //约束比例
		            minSize : [100,100],//可选最小大小
		            boxWidth : 200,		//画布宽度
		            boxHeight : 200,	//画布高度
		            onChange: showPreview,//改变时重置预览图
		            onSelect: showPreview,//选择时重置预览图
		            setSelect:[ 0, 0, 100, 100],//初始化时位置
		            onSelect: function (c){	//选择时动态赋值，该值是最终传给程序的参数！
			            $('#x').val(c.x);//需裁剪的左上角X轴坐标
			            $('#y').val(c.y);//需裁剪的左上角Y轴坐标
			            $('#w').val(c.w);//需裁剪的宽度
			            $('#h').val(c.h);//需裁剪的高度
		          }
		        });
				//提交裁剪好的图片
				$('.save-pic').click(function(){
					if($('#preview-hidden').html() == ''){
						//$.ThinkBox.error('请先上传图片！');
                                                alert('请先上传图片!');
					}else{
						//由于GD库裁剪gif图片很慢，所以长时间显示弹出框
						$.ThinkBox.success('图片处理中，请稍候……',{'delayClose':30000});
                                                var xv=$("#x").val();
                                                var yv=$("#y").val();
                                                var wv=$("#w").val();
                                                var hv=$("#h").val();
                                                var img_srcv=$("#img_src").val();
                                                var picNamev=$("#picName").val();
                                                $.post("<?php echo U('Member/cropImg');?>",{x:xv,y:yv,w:wv,h:hv,img_src:img_srcv,picName:picNamev},function(msg){
                         // var msg = $.parseJSON(msg);  
                          
                     $("#pic1").attr("src",msg.pic1);
                     $("#pic2").attr("src",msg.pic2);
                     $("#pic3").attr("src",msg.pic3);
                                                });
						//$('#pic').submit();
					}
				});
				//重新上传,清空裁剪参数
				var i = 0;
				$('.reupload-img').click(function(){
					$('#preview-hidden').find('*').remove();
					$('#preview-hidden').hide().addClass('hidden').css({'padding-top':0,'padding-left':0});
				});
		     }
		});
		//预览图
		function showPreview(coords){
			var img_width = $('#cropbox').width();
			var img_height = $('#cropbox').height();
			  //根据包裹的容器宽高,设置被除数
			  var rx = 100 / coords.w;
			  var ry = 100 / coords.h; 
			  $('#crop-preview-100').css({
			    width: Math.round(rx * img_width) + 'px',
			    height: Math.round(ry * img_height) + 'px',
			    marginLeft: '-' + Math.round(rx * coords.x) + 'px',
			    marginTop: '-' + Math.round(ry * coords.y) + 'px'
			  });
			  rx = 60 / coords.w;
			  ry = 60 / coords.h;
			  $('#crop-preview-60').css({
			    width: Math.round(rx * img_width) + 'px',
			    height: Math.round(ry * img_height) + 'px',
			    marginLeft: '-' + Math.round(rx * coords.x) + 'px',
			    marginTop: '-' + Math.round(ry * coords.y) + 'px'
			  });
		}
	})
	
</script>