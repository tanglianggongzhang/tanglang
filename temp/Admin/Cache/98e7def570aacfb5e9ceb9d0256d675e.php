<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <title>权限管理-后台管理-<?php echo ($systemConfig["SITE_INFO"]["name_cms"]); ?></title>
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
      <div class="center_r_t">您的位置：<a href="<?php echo U('Access/index');?>" class="a1">权限管理</a>>><a class="a1" href="<?php echo U('Access/index');?>">管理员列表</a>>编辑/添加 管理员信息</div>
      <div class="center_t">
          <form action="" id="form1" name="form1" method="post">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tab center_t">
                <tr>
                    <td width="120">登录名称：</td>
                    <td><input <?php if(ACTION_NAME == 'editAdmin'): ?>disabled="disabled"<?php endif; ?> name="a_name" id="a_name" type="text" class="required" size="40" value="<?php echo ($info["a_name"]); ?>" /></td>
                </tr>
                <tr>
                    <td width="120">所在省市：</td>
                    <td>
                        <select id="proid" name="proid" onchange="getcity('proid','cityid')">
                            <option value="">请选择省</option>
                            <?php if(is_array($pro_list)): $i = 0; $__LIST__ = $pro_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["region_id"]); ?>" <?php if($vo[region_id]==$ainfo[proid]): ?>selected<?php endif; ?> ><?php echo ($vo["region_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                        <select id="cityid" name="cityid" onchange="getdiqu('proid','cityid','quid')">
                            <option value="">请选择市</option>
                            <?php if($cityname): ?><option value="<?php echo ($cityid); ?>" selected><?php echo ($cityname); ?></option><?php endif; ?>
                        </select>
                        <select id="quid" name="quid" >
                            <option value="">请选择区</option>
                            <?php if($quname): ?><option value="<?php echo ($quid); ?>" selected><?php echo ($quname); ?></option><?php endif; ?>
                        </select>
                        
                    </td>
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
                    <td>状态：</td>
                    <td>
                        <select name="status" style="width: 80px;">
                            <?php if($info["status"] == 1): ?><option value="1" selected>启用</option>
                                <option value="0">禁用</option>
                                <?php else: ?>
                                <option value="1">启用</option>
                                <option value="0" selected>禁用</option><?php endif; ?>
                        </select>
                        如果禁用了将无法登陆本系统</td>
                </tr>
                <tr>
                    <td>所属用户组</td>
                    <td><select name="role_id" style="min-width: 80px;"><?php echo ($info["roleOption"]); ?></select></td>
                </tr>
                <tr>
                    <td>备注：</td>
                    <td><textarea name="remark" id="remark" rows="5" cols="57" class="max_length" ><?php echo ($info["remark"]); ?></textarea></td>
                </tr>
                
                <tr>
                    <td>真实姓名：</td>
                    <td><input type="text" name="adminfo[a_truename]" id="a_truename" value="<?php echo ($ainfo["a_truename"]); ?>" /></td>
                </tr>
                <tr>
                    <td>性别：</td>
                    <td>
                        
                        <label><input type="radio" name="adminfo[a_sex]" id="a_sex1" value="1" <?php if($ainfo[a_sex]==1): ?>checked="checked"<?php endif; ?> />男</label>
                    <label><input type="radio" name="adminfo[a_sex]" id="a_sex2" value="2" <?php if($ainfo[a_sex]==2): ?>checked="checked"<?php endif; ?>  />女</label>
                    </td>
                </tr>                
                
                <tr>
                    <td>生日：</td>
                    <td>
                        
                            <input type="text" id="but" name="adminfo[a_birthday]" value="<?php echo ($ainfo["a_birthday"]); ?>" />
            <div class="easyui-calendar" style="width:250px;height:250px; display:none" data-options="formatter:formatDay"></div>
            <script>
		var d1 = Math.floor((Math.random()*30)+1);
		var d2 = Math.floor((Math.random()*30)+1);
		function formatDay(date){
			var m = date.getMonth()+1;
			var d = date.getDate();
			var opts = $(this).calendar('options');
			//if (opts.month == m && d == d1){
				//return '<div class="icon-ok md">' + d + '</div>';
			//} 
			//else if (opts.month == m && d == d2){
				//return '<div class="icon-search md">' + d + '</div>';
			//}
			return d;
		}
		$("#but").click(function(){
			$(".easyui-calendar").show();	
		});
		$(".easyui-calendar").calendar({
				onSelect:function(date){
					var month_selected=(date.getMonth()+1)<=9?"0"+(date.getMonth()+1):(date.getMonth()+1);
					var day_selected=date.getDate()<=9?"0"+date.getDate():date.getDate();
					
					var date_selected=date.getFullYear()+"-"+month_selected+"-"+day_selected;
					var dateobj=new Date();
					
					var month_current=(dateobj.getMonth()+1)<=9?"0"+(dateobj.getMonth()+1):(dateobj.getMonth()+1);
					var day_current=dateobj.getDate()<=9?"0"+dateobj.getDate():dateobj.getDate();
					
					var date_current=dateobj.getFullYear()+"-"+month_current+"-"+day_current;
					
					if(date_selected<=date_current){
					$("#but").val(date_selected);
					$(".easyui-calendar").hide();
					}else{
						alert("所选日期不能大于当前日期");
					}
					}
			});
	</script>
            <style scoped>
		.md{
			height:16px;
			line-height:16px;
			background-position:2px center;
			text-align:right;
			font-weight:bold;
			padding:0 2px;
			color:red;
		}
	</style>
    
                    
                    
                    </td>
                </tr>
                
                <tr>
                    <td>联系电话：</td>
                    <td><input type="text" name="adminfo[a_telphone]" id="a_telphone" value="<?php echo ($ainfo[a_telphone]); ?>" /></td>
                </tr>                 
                
                <tr>
                    <td>联系地址：</td>
                    <td><input type="text" name="adminfo[a_address]" id="a_address" value="<?php echo ($ainfo[a_address]); ?>" /></td>
                </tr>
                
                <tr>
                    <td>QQ：</td>
                    <td><input type="text" name="adminfo[a_qq]" id="a_qq" value="<?php echo ($ainfo[a_qq]); ?>" /></td>
                </tr>  
                
                <tr>
                    <td>职位：</td>
                    <td><input type="text" name="adminfo[a_zhiwei]" id="a_zhiwei" value="<?php echo ($ainfo[a_zhiwei]); ?>" /></td>
                </tr>  

                <tr>
                    <td>Email：</td>
                    <td><input type="text" name="adminfo[a_email]" id="a_email" value="<?php echo ($ainfo[a_email]); ?>" /></td>
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
                pwd:{required:true,},
                confirm_pwd:{required:true,},
                remark:{max_length:500,}
            },
            messages:{
                a_name:{required:"请输入用户名/邮件地址"},
                pwd:{required:"请输入密码"},
                confirm_pwd:{required:"请输入确认密码"},
                remark:{max_length:"最多500个字符"}
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
        function getdiqu(proid,cityid,dqid){
            var pid=document.getElementById(proid).value;
		var cid=document.getElementById(cityid).value;
		$.ajax({
			data:"fid="+pid+"&cid="+cid,
			cache:false,
			url:"<?php echo U('Member/getqu');?>",
			type:"POST",
			dataType:"Json",
			success: function(msg){
				
				if(msg.status==1){
					$("#"+dqid).empty();
					var citystr="";
					var len=msg.data.length;
					var l;
					citystr+="<option value=''>请选择</option>";
					
					if(len>0){
						for(l=0;l<len;l++){
							citystr+="<option value='"+msg.data[l].region_id+"'>"+msg.data[l].region_name+"</option>";
						}
					}
					$("#"+dqid).append(citystr);
					
				}
				
			}
					
		});
        }
</script>