<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ArticleAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class ArticleAction extends CommonAction {
    /**
     * 列表
     * 文章
     */
    public function index(){
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        
        
        
        $this->display();
    }
    /**
     * 添加
     * 文章
     */
    public function add_art(){
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        $this->display();
    }
    /**
     * 编辑
     * 文章
     */
    public function edit_art(){
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        $this->display("add_art");
    }
    /**
     * 删除文章
     */
    public function del_art(){
        
    }
    /**
     * 文章分类
     */
    public function artcategory(){
        if(IS_POST){
            $cname=  trim($_POST['cname']);
            $artmod=new ArtcategoryModel();
            if(empty($cname)){
                $this->error("分类名称不能为空！");exit;
            }
            if($artmod->checkname($cname)){
                $this->error("分类名称已经存在！");exit;
            }
            $pid=$_POST['pid'];
            if($pid==0)
            {
                $level=0;
            }else{
                $level=$artmod->getlevel($pid);
                $level=$level+1;
            }
            $data=array(
                "cname"=>$cname,
                "pid"=>$pid,
                "level"=>$level
            );
            $rs=$artmod->add($data);
            if($rs)
                $this->success ("操作成功！");
            else
                $this->error ("操作失败!");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        $artmod=new ArtcategoryModel();
        $artlist=$artmod->getcategory();
        
        $this->assign("list",$artlist);
        
        $this->display();
    }
    /**
     * 删除分类
     */
    public function del_cat(){
        $cid=$_GET['cid'];
        $M=M("Artcategory");
        $rs=$M->where("cid=".$cid)->delete();
        if($rs)
            $this->success ("操作成功！");
        else
            $this->error ("操作失败！");
    }
    /**
     * 修改分类名称
     */
    public function xgcname(){
        header('Content-Type:application/json; charset=utf-8');
        $cname=  trim($_POST['cname']);
        $id=$_POST['id'];
        $M=M("Artcategory");
        $data=array("cname"=>$cname);
        $rs=$M->where("cid=".$id)->save($data);
        echo $rs;
    }
    
}
