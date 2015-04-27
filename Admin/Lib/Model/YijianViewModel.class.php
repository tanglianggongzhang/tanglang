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
class YijianViewModel extends ViewModel {
    public $viewFields=array(
        "Yijian"=>array("id","movphone","content","is_lx","addtime","_type" => "LEFT"),
        "Provice"=>array("region_name"=>"p_name","_table"=>"t_area","_on"=>"Provice.region_id=Yijian.p_id","_type" => "LEFT"),
        "City"=>array("region_name"=>"c_name","_table"=>"t_area","_on"=>"City.region_id=Yijian.c_id","_type" => "LEFT"),
        "qu"=>array("region_name"=>"q_name","_table"=>"t_area","_on"=>"qu.region_id=Yijian.q_id","_type" => "LEFT"),
        
    );
}
