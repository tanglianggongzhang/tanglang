<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForemanAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class GongzhangAction extends CommonAction {
    /**
     * 施工动态列表
     */
    public function index(){
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        
        $this->display();
    }
    /**
     * 添加施工动态
     */
    public function addsgdt(){
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        
        
        
        $this->display();
    }
    /**
     * 编辑施工动态
     */
    public function editsgdt(){
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        $this->display("addsgdt");
    }
    /**
     * 删除施工动态
     */
    public function delsgtd(){
        
    }
}
