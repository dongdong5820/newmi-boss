<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
* @link		http://www.100msh.net
* ---------------------------------------------------------------------
* @author		lidc <lidaichen@100msh.com>
* @date		2015-3-8
* @desc		申请wifi
*/
class applywificityMod extends commonMod{ 
    private $applywifiCity_obj;
    public function __construct(){
        parent::__construct();
        $this->applywifiCity_obj = new ApplyWifiCity();
    }

    public function index(){
        $this->authMod();
        $where = 1;
        $s_n_name = isset($_GET['s_n_name']) ? in($_GET['s_n_name']) : "";
        if(!empty($s_n_name)){
            $where .=" AND city_name like '%".$s_n_name."%'  ";
        }        
        $sc_pid = isset($_GET['sc_pid']) ? intval($_GET['sc_pid']) : 0;
        if($sc_pid){
            $where .=" AND sc_pid=".$sc_pid." ";
        }

        $page = isset($_GET['page'])&&!empty($_GET['page']) ? intval($_GET['page']) : 1;
        $numPerPage = 15;
        $start = ($page-1)*$numPerPage;
        $list = $this->applywifiCity_obj->getApplyWifiCityList($where, $start, $numPerPage);
        $count = $this->applywifiCity_obj->getApplyWifiCityCount($where);
        $page_string = $this->page('applywificity', $count, $numPerPage, 5, 4);
        if($list){
            foreach ($list as $key=>$value){
                if($value['city_type']=='A'){
                    $list[$key]['city_type'] = '签约地市';
                }elseif ($value['city_type']=='B'){
                    $list[$key]['city_type'] = '周边城市B';
                }elseif ($value['city_type']=='C'){
                    $list[$key]['city_type'] = '周边城市C';
                }else{
                    $list[$key]['city_type'] = '';
                }
                
            }
        }        
        $sp_list = $this->applywifiCity_obj->getApplyWifiCityP('sc_pid=0');
        $this->assign('spls', $sp_list);
        $this->assign('s_n_name', $s_n_name);
        $this->assign("list", $list);
        $this->assign("page_string", $page_string);
        $this->display();
    }

    public function add(){
        if($_POST){
            $sc_pid = isset($_POST['sc_pid']) ? intval($_POST['sc_pid']) : 0;
            $city_name = isset($_POST['city_name']) ? in($_POST['city_name']) : '';
            if(!empty($city_name)){
                $cn_count = $this->applywifiCity_obj->getApplyWifiCityCount("city_name='".$city_name."'");
                if($cn_count>0){
                    echo json_encode(array("status"=>"error","msg"=>"该城市已经添加成功！"));	
                    exit;
                }
            }
            $city_type = isset($_POST['city_type']) ? in($_POST['city_type']) : '';
            $city_status = isset($_POST['city_status']) ? intval($_POST['city_status']) : 1;
            
            $data=array(
                "sc_pid"=>$sc_pid,
                "city_name"=>$city_name,
                "city_type"=>$city_type,
                "city_status"=>$city_status
            );
            $res=$this->applywifiCity_obj->add($data);
            if($res){

                echo json_encode(array("status"=>"success","msg"=>"添加覆盖城市成功","isRefresh"=>1,"closePopup"=>1));	exit;
            }else{
                echo json_encode(array("status"=>"error","msg"=>"添加覆盖城市失败！"));	exit;
            }
        }
        $sp_list = $this->applywifiCity_obj->getApplyWifiCityP('sc_pid=0');
        $this->assign('spls', $sp_list);
        $this->display();
    }
    
    public function edit(){
        if($_POST){
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if(!$id){
                echo json_encode(array("status"=>"error","msg"=>"参数不对！"));	exit;
            }
            $sc_pid = isset($_POST['sc_pid']) ? intval($_POST['sc_pid']) : 0;
            $city_name = isset($_POST['city_name']) ? in($_POST['city_name']) : '';
            $city_type = isset($_POST['city_type']) ? in($_POST['city_type']) : '';
            $city_status = isset($_POST['city_status']) ? intval($_POST['city_status']) : 1;
    
            $data=array(
                "sc_pid"=>$sc_pid,
                "city_name"=>$city_name,
                "city_type"=>$city_type,
                "city_status"=>$city_status
            );
            $res=$this->applywifiCity_obj->edit('id='.$id, $data);
            if($res){
                echo json_encode(array("status"=>"success","msg"=>"修改覆盖城市成功","isRefresh"=>1,"closePopup"=>1));	exit;
            }else{
                echo json_encode(array("status"=>"error","msg"=>"修改覆盖城市失败！"));	exit;
            }
        }
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if(!$id){
            echo json_encode(array("status"=>"error","msg"=>"参数不对！"));	exit;
        }
        $list = $this->applywifiCity_obj->getApplyWifiCityById($id);
        $sp_list = $this->applywifiCity_obj->getApplyWifiCityP('sc_pid=0');
        $this->assign('spls', $sp_list);
        $this->assign("ls", $list);
        $this->display();
    }
    
    public function del(){
        $ids_str=isset($_GET['kid'])?in($_GET['kid']):'';
        if(empty($ids_str)){
            echo json_encode(array('status'=>"error",'msg'=>'空参数错误！',));exit;
        }
        $ids = $ids_str;
        $ids_str=explode(',',$ids_str);
        $suc=$err=0;
        foreach($ids_str as $id){
            $count = $this->applywifiCity_obj->getApplyWifiCityCount("sc_pid=".$id."");         
            if($count){      
                $suc = 0;          
                echo json_encode(array('status'=>"error",'msg'=>'删除项中有关联关系，无法删除！'));exit;
            }else{
                $suc = 1;
            }            
        }
        $result = $this->applywifiCity_obj->del($ids);
        if($result){
            echo json_encode(array('status'=>"success",'msg'=>'删除成功！',"isRefresh"=>1,"closePopup"=>1));exit;
        }else{
            echo json_encode(array('status'=>"error",'msg'=>'删除失败！'));exit;
        }
    }
}