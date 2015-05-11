<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ShipinAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class ShipinAction extends CommonAction {
    /**
     * 视频列表
     */
    public function index(){
        parent::_initalize();
        $this->assign("systemConfig",  $this->systemConfig);
        $this->display();
    }
    /**
     * 添加视频
     */
    public function add_shipin(){
        parent::_initalize();
        $this->assign("systemConfig",  $this->systemConfig);
        $this->display();
    }
    /**
     * 编辑视频
     */
    public function edit_shipin(){
        parent::_initalize();
        $this->assign("systemConfig",  $this->systemConfig);
        $this->display("add_shipin");
    }
    /**
     * 删除视频
     */
    public function del_shipin(){
        parent::_initalize();
        $this->assign("systemConfig",  $this->systemConfig);
        $this->display();
    }
    //------------------private
    
    //------------------ajax
    
}
