<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of RijiViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class RijiViewModel extends ViewModel {
    public $viewFields=array(
        "Riji"=>array("id","title","fmimg","click","status","_type" => "LEFT"),
        "ForemanInfo"=>array("truename","_on"=>"Riji.uid=ForemanInfo.a_id","_type" => "LEFT"),
        "Admin"=>array("a_name"=>"adduname","_on"=>"Riji.adduid=Admin.a_id","_type" => "LEFT"),
    );
}
