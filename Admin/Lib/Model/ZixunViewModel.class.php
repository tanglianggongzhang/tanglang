<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of ZixunViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class ZixunViewModel extends ViewModel {
    public $viewFields=array(
        "Zixun"=>array("id","name","lxfs","addtime","p_id","c_id","status","_type" => "LEFT"),
        "Provice"=>array("region_name"=>"p_name","_table"=>"t_area","_on"=>"Provice.region_id=Zixun.p_id","_type" => "LEFT"),
        "City"=>array("region_name"=>"c_name","_table"=>"t_area","_on"=>"City.region_id=Zixun.c_id","_type" => "LEFT")
    );
}
