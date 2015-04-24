<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of AdweizhiViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class AdweizhiViewModel extends ViewModel {
    public $viewFields=array(
        "Adweizhi"=>array("id","name","addtime","price","width","height","wzimg","_type" => "LEFT"),
        "Admin"=>array("a_id","a_name"=>"adduname","_on"=>"Adweizhi.adduid=Admin.a_id"),
    );
}
