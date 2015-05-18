<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of OrderViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class OrderViewModel extends ViewModel {
    public $viewFields=array(
        "Order"=>array("*","_type" => "LEFT","_as"=>'o'),
        "Peisongtype"=>array("name"=>"psname","_on"=>"o.pstype=p.id","_as"=>'p'),
        "Zhifutype"=>array("name"=>"zfname","_on"=>"o.paytype=z.id","_as"=>'z')
    );
}
