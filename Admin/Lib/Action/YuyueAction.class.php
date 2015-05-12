<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of YuyueAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class YuyueAction extends CommonAction {
    /**
     * 列表
     */
    public function index(){
        parent::_initalize();
        $this->assign("systemConfig",  $this->systemConfig);
        
        
        
        $this->display();
    }
    /**
     * 删除预约
     */
    public function del_yuyue(){
        
        $id=$_GET['id'];
        $M=M("Yuyue");
        $rs=$M->where("id=".$id)->delete();
        if($rs)
            $this->success ("操作成功！");
        else
            $this->error ("操作失败！");
    }
    /**
     * 快速修改状态
     */
    public function edit_status(){
        $status=$_GET['status'];
        if($status==1){
            $status_u=0;
        }else{
            $status_u=1;
        }
        $id=$_GET['id'];
        $M=M("Yuyue");
        $rs=$M->where("id=".$id)->save(array("status"=>$status_u));
        if($rs)
            $this->success ("操作成功！");
        else
            $this->error ("操作失败！");
    }
    /**
     * 预约类型
     */
    public function ytype(){
        if(IS_POST){
            $name=  trim($_POST['name']);
            $ymod=M("Yuyuetype");
            if(empty($name)){
                $this->error("类型名称不能为空!");
                exit;
            }
            $cou=$ymod->where("name = '".$name."'")->count();
            if($cou>0){
                $this->error("预约类型名称已经存在！");exit;
            }
            $data['name']=$name;
            $rs=$ymod->add($data);
            if($rs)
                $this->success ("操作成功!");
            else
                $this->error ("操作失败!");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig",  $this->systemConfig);
        $ymod=M("Yuyuetype");
        $ylist=$ymod->where(1)->select();
        $this->assign("ylist",$ylist);
        
        $this->display();
    }
    /**
     * 删除预约类型
     */
    public function del_ytype(){
        $id=$_GET['id'];
        $ymod=M("Yuyuetype");
        $rs=$ymod->where("yid=".$id)->delete();
        if($rs)
            $this->success ("操作成功!");
        else
            $this->error ("操作失败！");
    }
    //---------------------ajax
    /**
     * 修改预约类型
     */
    public function ajax_updatename(){
        header('Content-Type:application/json; charset=utf-8');
        $id=$_POST['id'];
        $sort=$_POST['sort'];
        $M=M("Yuyuetype");
        $rs=$M->where("yid=".$id)->save(array("name"=>$sort));
        if($rs)
            echo 1;
        else
            echo 0;
    }
}
