<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of TzjlViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class TzjlViewModel extends ViewModel {
    public $viewFields=array(
        "Tzjl"=>array("id","tzid","name","movphone","is_lx","addtime","_type" => "LEFT"),
        "Sqtz"=>array("name"=>"tzname","_on"=>"Tzjl.tzid=Sqtz.id","_type" => "LEFT"),
    );
}
