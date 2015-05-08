<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of HdpViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class HdpViewModel extends ViewModel {
    public $viewFields=array(
        "Hdpmember"=>array("id","name","img","link","uid","status","_type" => "LEFT","_as"=>"Hdp"),
        "Sheji"=>array("truename","_on"=>"Hdp.uid=Sheji.a_id","_type" => "LEFT"),
        
    );
}
