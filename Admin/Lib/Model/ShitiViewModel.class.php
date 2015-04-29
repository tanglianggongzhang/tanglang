<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of ShitiViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class ShitiViewModel extends ViewModel {
    public $viewFields=array(
        "Shiti"=>array("id","name","telphone","address","yingye","fwzz","tcw","mianji","mapimg","zwimg","is_display","addtime","_type" => "LEFT"),
        "Provice"=>array("region_name"=>"p_name","region_id"=>"p_id","_table"=>"t_area","_on"=>"Provice.region_id=Shiti.p_id","_type" => "LEFT"),
        "City"=>array("region_name"=>"c_name","region_id"=>"c_id","_table"=>"t_area","_on"=>"City.region_id=Shiti.c_id","_type" => "LEFT"),
        "qu"=>array("region_name"=>"q_name","region_id"=>"q_id","_table"=>"t_area","_on"=>"qu.region_id=Shiti.q_id","_type" => "LEFT")
    );
}
