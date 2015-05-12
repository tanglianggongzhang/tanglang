<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of WebpagesViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class WebpagesViewModel extends ViewModel {
    public $viewFields=array(
        "Webpages"=>array("id","name","contents","ywname","p_id","c_id","_type" => "LEFT"),
        
        "Provice"=>array("region_name"=>"p_name","_table"=>"t_area","_on"=>"Provice.region_id=Webpages.p_id"),
        "City"=>array("region_name"=>"c_name","_table"=>"t_area","_on"=>"City.region_id=Webpages.c_id"),
    );
}
