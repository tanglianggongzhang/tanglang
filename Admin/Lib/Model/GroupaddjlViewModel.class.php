<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of GroupaddjlViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class GroupaddjlViewModel extends ViewModel {
    public $viewFields=array(
        "Groupaddjl"=>array("id","name","movphone","status","addtime","_type" => "LEFT",),
        "Provice"=>array("region_name"=>"p_name","region_id"=>"p_id","_table"=>"t_area","_on"=>"Provice.region_id=Groupaddjl.p_id","_type" => "LEFT"),
        "City"=>array("region_name"=>"c_name","region_id"=>"c_id","_table"=>"t_area","_on"=>"City.region_id=Groupaddjl.c_id","_type" => "LEFT"),
        "qu"=>array("region_name"=>"q_name","region_id"=>"q_id","_table"=>"t_area","_on"=>"qu.region_id=Groupaddjl.q_id","_type" => "LEFT"),
        "xq"=>array("name"=>"xq_name","id"=>"xq_id","_on"=>"xq.id=Groupaddjl.xiaoqu","_type"=>"LEFT")
    );
}
