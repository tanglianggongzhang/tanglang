<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WebpageAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class WebpagesAction extends CommonAction {
    /**
     * 单页面列表
     */
    public function index(){
        parent::_initalize();
        $this->assign("systemConfig",  $this->systemConfig);
        import("ORG.Util.Page");
        
         #-------------------------------------------------------------省start
        $is_qx=  $this->getqx($_SESSION['my_info']['role']);
        if($is_qx==0){
            #省
            $citymod=new CityModel();
            $plist=$citymod->getprovince(1);
            $this->assign("plist",$plist);
            $p_id=$_GET['p_id'];
            $c_id=$_GET['c_id'];
            
        }else{
            $p_id=$_SESSION['my_info']['proid'];
            $c_id=$_SESSION['my_info']['cityid'];
        }
        $c_name=  $citymod->getname($c_id);
        
        $this->assign("p_id",$p_id);
        $this->assign("c_id",$c_id);
        $this->assign("c_name",$c_name);
        #-------------------------------------------------------------省end
        $keys=$_GET['keys'];
        $keys=$keys=="请输入关键字"?"":$keys;
        
        $where="1";
        if(!empty($p_id))
            $where.=" and Webpages.p_id=".$p_id;
        if(!empty($c_id))
            $where.=" and Webpages.c_id=".$c_id;
        if(!empty($keys))
            $where.=" and Webpages.name like '%".$keys."%'";
        
        $M=D("WebpagesView");
        $totalRows=$M->where($where)->count();
        #echo $M->getLastSql();
        $p=new Page($totalRows,10);
        $list=$M->where($where)->limit($p->firstRow.",".$p->listRows)->select();
        $this->assign("list",$list);
        $this->assign("page",$p->show());
        
        $this->display();
    }
    /**
     * 添加单页面
     */
    public function add_webpage(){
        if(IS_POST){
            $name=trim($_POST['name']);
            $ywname=  trim($_POST['ywname']);
            $p_id=$_POST['p_id'];
            $c_id=$_POST['c_id'];
            $contents=  trim($_POST['contents']);
            if(empty($name)){
                $this->error("单页面名称不能为空！");exit;
            }
            if(empty($ywname)){
                $this->error("单页面英文代码不能为空！");exit;
            }
            $M=M("Webpages");
            $ncou=$M->where("name ='".$name."'")->count();
            if($ncou>0){
                $this->error("单页面名称已经存在！");exit;
            }
            $ycou=$M->where("ywname ='".$ywname."'")->count();
            if($ycou>0){
                $this->error("单页面英文代码已经存在！");exit;
            }
            $data=array(
                "name"=>$name,
                "contents"=>$contents,
                "ywname"=>$ywname,
                "p_id"=>$p_id,
                "c_id"=>$c_id
            );
            $rs=$M->add($data);
            if($rs)
                $this->success ("操作成功！");
            else
                $this->error ("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig",  $this->systemConfig);
        #-------------------------------------------------------------省start
        $is_qx=  $this->getqx($_SESSION['my_info']['role']);
        if($is_qx==0){
            #省
            $citymod=new CityModel();
            $plist=$citymod->getprovince(1);
            $this->assign("plist",$plist);
            
        }else{
            $p_id=$_SESSION['my_info']['proid'];
            $c_id=$_SESSION['my_info']['cityid'];
        }
        $this->assign("p_id",$p_id);
        $this->assign("c_id",$c_id);
        #-------------------------------------------------------------省end
        $this->assign("is_qx",$is_qx);
        
        $this->display();
    }
    /**
     * 编辑单页面
     */
    public function edit_webpage(){
        if(IS_POST){
            $name=  trim($_POST['name']);
            $ywname=  trim($_POST['ywname']);
            $p_id=  trim($_POST['p_id']);
            $c_id=  trim($_POST['c_id']);
            $contents=  trim($_POST['contents']);
            $id=$_POST['id'];
            $M=M("Webpages");
            $data=array(
                "name"=>$name,
                "contents"=>$contents,
                "ywname"=>$ywname,
                "p_id"=>$p_id,
                "c_id"=>$c_id
            );
            $rs=$M->where("id=".$id)->save($data);
            if($rs)
                $this->success ("操作成功！",U("Webpages/index"));
            else
                $this->error ("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig",  $this->systemConfig);
        $id=$_GET['id'];
        $wmod=M("Webpages");
        $info=$wmod->where("id=".$id)->find();
        $this->assign("info",$info);
         #-------------------------------------------------------------省start
        $is_qx=  $this->getqx($_SESSION['my_info']['role']);
        if($is_qx==0){
            #省
            $citymod=new CityModel();
            $plist=$citymod->getprovince(1);
            $this->assign("plist",$plist);
            
        }else{
            $p_id=$_SESSION['my_info']['proid'];
            $c_id=$_SESSION['my_info']['cityid'];
        }
        $p_id=$info['p_id'];
        $c_id=$info['c_id'];
        $c_name=  $citymod->getname($c_id);
        
        $this->assign("p_id",$p_id);
        $this->assign("c_id",$c_id);
        $this->assign("c_name",$c_name);
        #-------------------------------------------------------------省end
        
        $this->display("add_webpage");
    }
    /**
     * 删除单页面
     */
    public function del_webpage(){
        $id=$_GET['id'];
        $M=M("Webpages");
        $rs=$M->where("id=".$id)->delete();
        if($rs)
            $this->success ("操作成功!");
        else
            $this->error ("操作失败！");
    }
}
