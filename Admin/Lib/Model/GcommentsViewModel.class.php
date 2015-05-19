<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of GcommentsViewModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class GcommentsViewModel extends ViewModel {
    public $viewFields=array(
        "Gcomments"=>array("*","_type" => "LEFT"),
        "Member"=>array("a_name"=>"uname","_on"=>"Gcomments.uid=Member.a_id","_type" => "LEFT"),
        "Dianpu"=>array("company","_on"=>"Dianpu.a_id=Gcomments.sjid","_type" => "LEFT"),
        "Goods"=>array("name"=>"gname","_on"=>"Goods.id=Gcomments.gid","_type" => "LEFT")
    );
}
