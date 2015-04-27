<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ShitijlViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class ShitihdpViewModel extends ViewModel {
    public $viewFields=array(
        "Shitihdp"=>array("id","img","link","addtime","is_display","_type"=>"LEFT"),
        "Shiti"=>array("id"=>"stid","name"=>"title","_on"=>"Shiti.id=Shitihdp.stid","_type" => "LEFT"),
        
    );
}
