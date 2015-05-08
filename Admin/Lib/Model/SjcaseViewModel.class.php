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
class SjcaseViewModel extends ViewModel {
    public $viewFields=array(
        "Case"=>array("id","title","fmimg","is_jd","hid","fid","status","_type" => "LEFT","_as"=>"cases"),
        "Sheji"=>array("truename","_on"=>"cases.uid=Sheji.a_id","_type" => "LEFT"),
        "Admin"=>array("a_name"=>"adduname","_on"=>"cases.adduid=Admin.a_id","_type" => "LEFT"),
    );
}
