<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CityModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class CityModel extends Model {
    /**
     * 城市列表
     */
    public function citylist($id) {
        //import("ORG.Util.Category");
        //$cat = new Category('Area', array('region_id', 'parent_id', 'region_name', 'fullname'));
        //$temp = $cat->getList("", 0,"region_id asc");               //获取分类结构
        $M=M("Area");
        if(empty($id))
            $where="parent_id=1";
        else
            $where="parent_id=".$id;
        
        $temp=$M->where($where)->order("region_id asc")->select();
        $cof = include WEB_ROOT . 'Common/config2.php';
       
        $level = $cof['cslevel'];
        $diqufarr=$cof['diqu'];
    
        $list=array();
        foreach ($temp as $k => $v) {
            $temp[$k]['statusTxt'] = $v['agency_id'] == 1 ? "启用" : "禁用";
            $temp[$k]['chStatusTxt'] = $v['agency_id'] == 0 ? "启用" : "禁用";
            
            $temp[$k]['tjTxt'] = $v['is_tj'] == 1 ? "推荐" : "普通";
            $temp[$k]['chtjTxt']=$v['is_tj']==0?"推荐":"普通";
            
            $temp[$k]['level'] = $level[$v['region_type']];
            $temp[$k]['diquf'] = $diqufarr[$v['diqu']];
            
            $list[$v['region_id']] = $temp[$k];
        }
        unset($temp);
        return $list;
        
    }
    /**
     * 获取省份
     */
    public function getprovince($fid){
        $mod=M("Area");
        $list=$mod->where("parent_id=".$fid)->select();
        return $list;
    }
    /**
     * 获取 地区
     */
    public function getqu($cid){
        $mod=M("Area");
        $list=$mod->where("parent_id=".$cid." and region_type=3")->select();
        return $list;
    }
    
    /**
     * 根据城市ID获取省份id
     */
    public function getprovinceid($cid){
        $mod=M("Area");
        $list=$mod->where("region_id=".$cid)->find();
        return $list['parent_id'];
    }
    /**
     * 获取province 下的所有city
     */
    public function getcity($id){
        $mod=M("Area");
        $list=$mod->where("parent_id=".$id." and agency_id=1")->select();
        return $list;
    }
    
    /**
     * 根据id获取 省份或者城市名称
     */
    public function getname($id){
        $mod=M("Area");
        $list=$mod->where("region_id=".$id." and agency_id=1")->field("region_name")->find();
        return $list['region_name'];
    }
    /**
     * 根据城市ID
     * 获取省份名称
     */
    public function getproname($cid){
        $mod=M("Area");
        $find=$mod->where("region_id=".$cid." and agency_id=1")->field("parent_id")->find();
        $inf=$mod->where("region_id=".$find['parent_id']." and agency_id=1")->field("region_name")->find();
        return $inf['region_name'];
    }
    /**
     * @param   $pid 省id
     * @return  $idstr 省下面所有的市和区的id
     */
    public function getsqid($pid){
        $idstr="";
        $m=M("Area");
        $list=$m->where("parent_id=".$pid)->field("region_id")->select();
        foreach ($list as $k=>$v){
            $idstr.=$v['region_id'].",";
        }
        $idstr.=$pid;
        $list1=$m->where("parent_id in(".$idstr.")")->field("region_id")->select();
        $idstr2="";
        foreach ($list1 as $k=>$v){
            $idstr2.=$v['region_id'].",";
        }
        $idstr2.=$idstr2.$pid;
        #$idstr2=  substr($idstr2,0, -1);
        return $idstr2;
    }
    /**
     * 修改地区
     * @param string $pid 用逗号组成的id字符串
     * @return bool 
     */
    public function updatedq($pid,$dqid){
        $m=M("Area");
        return $m->where("region_id in(".$pid.")")->save(array("diqu"=>$dqid));
    }
}
