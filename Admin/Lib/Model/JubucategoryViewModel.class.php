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
class JubucategoryViewModel extends ViewModel {
    public $viewFields=array(
        "Jubucategory"=>array("id","name","addtime","_type" => "LEFT"),
        "Admin"=>array("a_id","a_name"=>"adduname","_on"=>"Jubucategory.adduid=Admin.a_id"),
    );
}
