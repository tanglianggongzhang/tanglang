<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of RijicategoryView
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class RijicategoryViewModel extends ViewModel {
    public $viewFields=array(
        "Rijicategory"=>array("cid","cname","_type" => "LEFT"),
        "ForemanInfo"=>array("truename","_on"=>"Rijicategory.uid=ForemanInfo.a_id","_type" => "LEFT"),
        "Member"=>array("a_name"=>"mname","_on"=>"Rijicategory.uid=Member.a_id","_type" => "LEFT"),
        "Admin"=>array("a_name"=>"adduname","_on"=>"Rijicategory.adduid=Admin.a_id","_type" => "LEFT"),
    );
}
