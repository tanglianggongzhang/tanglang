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
class HdpshopViewModel extends ViewModel {
    public $viewFields=array(
        "Hdpmember"=>array("id","name","img","link","uid","status","_type" => "LEFT","_as"=>"Hdp"),
        "Dianpu"=>array("lxrname","company","_on"=>"Hdp.uid=Dianpu.a_id","_type" => "LEFT"),
        
    );
}
