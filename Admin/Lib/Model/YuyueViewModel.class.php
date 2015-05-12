<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of YuyueViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class YuyueViewModel extends ViewModel {
    public $viewFields=array(
        "Yuyue"=>array("id","name","typeid","quyu","mianji","yusuan","yaoqiu","p_id","c_id","status","_type" => "LEFT"),
        "Yuyuetype"=>array("name"=>"ytname","_on"=>"Yuyuetype.yid=Yuyue.typeid","_type" => "LEFT"),
        "Member"=>array("a_name","_on"=>"Yuyue.uid=Member.a_id","_type" => "LEFT")
    );
}
