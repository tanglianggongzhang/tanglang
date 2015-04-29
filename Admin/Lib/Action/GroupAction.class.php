<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GroupAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class GroupAction extends CommonAction {
    /**
     * 列表
     * 团购活动
     */
    public function index(){
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        $this->display();
    }
    /**
     * 添加
     * 团购活动
     */
    public function add_group(){
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        $this->display();
    }
    /**
     * 编辑
     * 团购活动
     */
    public function edit_group(){
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        $this->display("add_group");
    }
    /**
     * 删除
     * 团购活动
     */
    public function del_group(){
        
    }
    /**
     * ajax
     * 快速修改
     * 推荐
     * 团购活动
     */
    public function edit_tj(){
        header('Content-Type:application/json; charset=utf-8');
        $data=array();
        echo json_encode($data);
    }
    /**
     * 修改
     * 状态
     * 团购活动
     */
    public function edit_status(){
        
    }
}
