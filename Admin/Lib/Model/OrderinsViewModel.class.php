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
class OrderinsViewModel extends ViewModel {
    public $viewFields=array(
        "Orderxiangxi"=>array("*","_type" => "LEFT","_as"=>'o'),
        "Goods"=>array("name"=>"gname","_on"=>"o.goodsid=p.id","_as"=>'p'),
        "Order"=>array("ordersn","_on"=>"o.orderid=.o1.id","_as"=>"o1")
    );
}
