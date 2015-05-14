<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of GroupViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class GroupViewModel extends ViewModel {
    public $viewFields=array(
        "Group"=>array("id","title","uid","is_tj","starthdtime","endhdtime","address","shuoming","chengchelx","mapimg","hdimg","bmnum","addtime","status","_type" => "LEFT","_as"=>"Group1"),
        "Provice"=>array("region_name"=>"p_name","region_id"=>"p_id","_table"=>"t_area","_on"=>"Provice.region_id=Group1.p_id","_type" => "LEFT"),
        "City"=>array("region_name"=>"c_name","region_id"=>"c_id","_table"=>"t_area","_on"=>"City.region_id=Group1.c_id","_type" => "LEFT"),
        "qu"=>array("region_name"=>"q_name","region_id"=>"q_id","_table"=>"t_area","_on"=>"qu.region_id=Group1.q_id","_type" => "LEFT")
    );
}
