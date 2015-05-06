<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ShopcategoryModel
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class ShopcategoryModel extends Model {

    /**
     * 分类列表
     */
    public function getcategory() {
        import("ORG.Util.Category");
        $cat = new Category('Shopcategory', array('cid', 'pid', 'cname', 'fullname'));
        $temp = $cat->getList("", 0, "cid asc");               //获取分类结构
        return $temp;
    }

    /**
     * 根据 分类id
     * 获取 分类级别
     */
    public function getlevel($cid) {
        $info = $this->where("cid=" . $cid)->field("level")->find();
        return $info['level'];
    }

    /**
     * 根据分类id
     * 获取 分类信息
     */
    public function getinfo($cid) {
        $info = $this->where("cid=" . $cid)->find();
        return $info;
    }

    /**
     * 检查分类是否存在
     * 存在返回true
     * 不存在返回false
     */
    public function checkname($cname) {
        $res = $this->where("cname =" . $cname)->count();
        if ($res > 0)
            return true;
        else
            return false;
    }

}
