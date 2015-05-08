<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of CaseViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class GongdiViewModel extends ViewModel {
    public $viewFields=array(
        "Gongdi"=>array("id","name","fmimg","gdimg","status","_type" => "LEFT"),
        "ForemanInfo"=>array("truename","_on"=>"Gongdi.uid=ForemanInfo.a_id","_type" => "LEFT"),
        "Admin"=>array("a_name"=>"adduname","_on"=>"Gongdi.adduid=Admin.a_id","_type" => "LEFT"),
    );
}
