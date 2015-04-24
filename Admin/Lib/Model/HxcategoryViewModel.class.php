<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of HxcategoryViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class HxcategoryViewModel extends ViewModel {
    public $viewFields=array(
        "Hxcategory"=>array("id","name","addtime","_type" => "LEFT"),
        "Admin"=>array("a_id","a_name"=>"adduname","_on"=>"Hxcategory.adduid=Admin.a_id"),
    );
}
