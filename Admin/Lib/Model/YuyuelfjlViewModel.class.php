<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of YuyuelfjlView
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class YuyuelfjlViewModel extends ViewModel {
    public $viewFields=array(
        "Yuyuelfjl"=>array("id","name","movphone","sgid","addtime","status","_type" => "LEFT",),
        "Shigongdt"=>array("title","_on"=>"Shigongdt.id=Yuyuelfjl.sgid","_type" => "LEFT")
    );
}
