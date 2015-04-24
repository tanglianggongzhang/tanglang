<?php

class NodeModel extends Model {
    /**
     * 获取  所有的节点
     */
    public function get_node($pid=0){
        $where="pid=".$pid." and  status=1 and is_menu=1";
        $list=$this->where($where)->order("sort desc")->select();
        
        #echo $this->getLastSql();
        
        return $list;
    }
    /**
     * 获取非管理员的节点
     */
    public function get_user_node($pid=0,$userid=0){
        $where="pid=".$pid." and  status=1 and is_menu=1 ";
        
        if($userid)
            $where.="and user_id=".$userid;
        
        $mod=M("Quanxianview");
        $list=$mod->where($where)->order("sort desc")->select();
       # echo $mod->getLastSql();
        return $list;
    }
}

?>
