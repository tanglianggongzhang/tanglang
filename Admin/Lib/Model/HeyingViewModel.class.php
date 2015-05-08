<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of HeyingViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class HeyingViewModel extends ViewModel {
    public $viewFields=array(
        "Heying"=>array("id","name","img","uid","is_tj","status","addtime","_type" => "LEFT"),
        "Admin"=>array("a_name"=>"adduname","_on"=>"Heying.adduid=Admin.a_id"),
    );
}
