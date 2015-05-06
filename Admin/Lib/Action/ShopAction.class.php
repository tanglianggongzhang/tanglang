<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ShopAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class ShopAction extends CommonAction {

    /**
     * 商品列表
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->display();
    }

    /**
     * 商城分类
     */
    public function shopcategory() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $cmod=new ShopcategoryModel();
        $list=$cmod->getcategory();
        $this->assign("list",$list);
        
        $this->display();
    }

    /**
     * 添加分类
     */
    public function addshopcategory() {
        if (IS_POST) {
            $cname = trim($_POST['cname']);
            if (empty($cname)) {
                $this->success("分类名称不能为空！");
                exit;
            }
            if (!empty($_FILES['clogo']['name'])) {
                $path = "/Uploads/shangjia/";
                $imgsrc = $this->upload("." . $path);
                if (!empty($imgsrc[0]['savename']))
                    $clogo = $path . $imgsrc[0]['savename'];
            }
            $cmod = new ShopcategoryModel();
            $data = array();
            $pid=$_POST['pid'];
            $data['cname'] = $cname;
            $data['clogo'] = $clogo;
            $data['pid'] = $pid;
            if($cmod->checkname($cname)){
                $this->error("商品分类已经存在！");
                exit;
            }
            
            if ($pid == 0) {
                $data['level'] = 0;
            } else {
                $data['level']=$cmod->getlevel($pid);
                $data['level']+=1;
            }
            $res=$cmod->add($data);
            if($res){
                $this->success("操作成功!",U("Shop/shopcategory"));
            }else{
                $this->error("操作失败!");
            }
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $scmod = new ShopcategoryModel();
        $sclist = $scmod->getcategory();
        $this->assign("sclist", $sclist);
        $this->display();
    }
    /**
     * 编辑分类
     */
    public function editshopcategory(){
        if(IS_POST){
            $cid=$_POST['cid'];
            $scmod=new ShopcategoryModel();
            $info=$scmod->where("cid=".$cid)->find();
            
            $cname=trim($_POST['cname']);
            if(!empty($_FILES['clogo']['name'])){
                
                $path = "/Uploads/shangjia/";
                
                
                $imgsrc = $this->upload("." . $path);
                if (!empty($imgsrc[0]['savename'])){
                    $clogo = $path . $imgsrc[0]['savename'];
                    unlink(".".$path.$info['clogo']);
                }
            }
            $pid=$_POST['pid'];
            if ($pid == 0) {
                $level = 0;
            } else {
                $level=$scmod->getlevel($pid);
                $level+=1;
            }
            
            $data=array(
                "cname"=>$cname,
                "clogo"=>$clogo,
                "pid"=>$pid,
                "level"=>$level
            );
            $where="cid=".$cid;
            $res=$scmod->where($where)->save($data);
            if($res){
                $this->success("操作成功!",U("Shop/shopcategory"));
            }else{
                $this->error("操作失败!");
            }
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        $cid=$_GET['cid'];
        $scmod=new ShopcategoryModel();
        $info=$scmod->getinfo($cid);
        $this->assign("info",$info);
        #分类
        $sclist=$scmod->getcategory();
        $this->assign("sclist",$sclist);
        
        $this->display("addshopcategory");
    }
    /**
     * 删除分类
     */
    public function  delshopcategory(){
        $cid=$_GET['cid'];
        $scmod=new ShopcategoryModel();
        $info=$scmod->getinfo($cid);
        if(!empty($info['clogo'])){
            unlink(".".$info['clogo']);
        }
        $rs=$scmod->where("cid=".$cid)->delete();
        if($rs)
            $this->success ("操作成功!");
        else
            $this->error ("操作失败!");
    }
}
