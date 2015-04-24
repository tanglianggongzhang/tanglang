<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of FirendlinkViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class FirendlinkViewModel extends ViewModel {
    public $viewFields=array(
        "Firendlink"=>array("id","name","logo","link","orders","is_tj","p_id","c_id","addtime","_type" => "LEFT"),
        "Admin"=>array("a_id","a_name"=>"adduname","_on"=>"Firendlink.adduid=Admin.a_id"),
        "Provice"=>array("region_name"=>"p_name","_table"=>"t_area","_on"=>"Provice.region_id=Firendlink.p_id"),
        "City"=>array("region_name"=>"c_name","_table"=>"t_area","_on"=>"City.region_id=Firendlink.c_id"),
    );
}
