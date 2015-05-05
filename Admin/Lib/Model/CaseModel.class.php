<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CaseModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class CaseModel extends Model {
    /**
     * 获取案例列表
     */
    public function getcaselist($type,$uid){
        $map=array(
            "uid"=>$uid,
            "type"=>$type,
            "status"=>"1"
        );
        $list=$this->where($map)->select();
        return $list;
    }
    
    
}
