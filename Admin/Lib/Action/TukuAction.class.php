<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TukuAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class TukuAction extends CommonAction {
    /**
     * 图库列表
     */
    public function index(){
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        
        $this->display();
    }
    /**
     * 添加图库
     */
    public function add_tuku(){
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        
        $this->display();
    }
    /**
     * 编辑图库
     */
    public function edit_tuku(){
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        
        $this->display();
    }
    /**
     * 删除图库
     */
    public function del_tuku(){
        
    }
}
