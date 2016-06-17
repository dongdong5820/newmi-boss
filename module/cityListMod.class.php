<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		lidc <lidaichen@100msh.com>
 * @date		2015-1-22
 * @desc		申请wifi
 */
class cityListMod extends commonMod{
    private $city_obj;
    public function __construct(){
        parent::__construct();
        $this->city_obj = new CityList();
    }   
    
    /**
     *  @author [lidc] <[email]>
     *  获取导航跳转列表信息
     */
    public function index(){
        $this->authMod();
       
        $where = "";
        $s_n_name = isset($_GET['s_n_name']) ? in($_GET['s_n_name']) : "";
        if(!empty($s_n_name)){
            $sc_name =" sc_name='".$s_n_name."' ";
        }else{
            $sc_name = "";
        }
        $nav_url = isset($_GET['nav_url']) ? in($_GET['nav_url']) : "";
        if(!empty($nav_url)){
            $where .= " nav_url like '% ".urldecode($nav_url)." %' ";
        }
        $page = isset($_GET['page']) && !empty($_GET['page']) ? intval($_GET['page']) : 1;
        $numPerPage = 10;
        $start = ($page - 1)* $numPerPage;
        
        $list = $this->city_obj->getCityList($sc_name, $where, $start, $numPerPage);

        $count = $this->city_obj->getCityListCount($where,$sc_name);
        $page_string = $this->page('cityList', $count, $numPerPage, 5, 4);

        
        $navList = $this->city_obj->getNavList();

        $this->assign("list",$list);
        $this->assign("page_string", $page_string);
        $this->assign("s_n_name", $s_n_name);
        $this->assign("nav_url", $nav_url);
        $this->assign("navList",$navList);
        $this->display();
    }


    public function edit(){
        if($_POST){
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            
            $city_id = isset($_POST['city_id']) ? in($_POST['city_id']) : "";
            if(empty($city_id)){
                echo json_encode(array("status"=>"error","msg"=>"城市参数不能为空！")); 
                exit;
            }  
            $nav_id = isset($_POST['nav_id']) ? intval($_POST['nav_id']) : 0;
            if(!$nav_id){
                echo json_encode(array('status' => 'error', 'msg' => '导航跳转选择参数异常!'));
            }
            $area_code = isset($_POST['area_code']) ? in($_POST['area_code']) : "";
            $data = array(
                'city_id' => $city_id,
                'nav_id'  => $nav_id,
                'area_code' => $area_code,
                'date' => time()
            );
            if($id==0){
                $result = $this->city_obj->add($data);
            }else{
                $result = $this->city_obj->edit("id=".$id."",$data);
            }            
            if($result){
                $city_name = isset($_POST['city_name']) ? in($_POST['city_name']) : "";
                $nav_ls = $this->city_obj->getNavListById($nav_id);
                //日志的管理
                $log_content="";
                $log_content[] = "nav_name:" . $nav_ls['nav_name'];
                $log_content[] = "nav_url:" . $nav_ls['nav_url'];
                $log_content[] = "city_name:" . $city_name;                
                $log_content[] = "area_code:" . $area_code;
                $log_content = implode(" , ", $log_content);
                $operationLogDAO_obj=operationLogDAO::getInstance();
                $operationLogDAO_obj->create_log('导航跳转城市修改',$log_content,'LC006',"LOT006");
                echo json_encode(array("status"=>"success","msg"=>"修改成功!","isRefresh"=>1,"closePopup"=>1));  exit;
            }else{
                 echo json_encode(array("status"=>"error","msg"=>"修改失败！")); exit;
            }
        }

        //获取get传过来的参数
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if($id){        
            $ls = $this->city_obj->getcityListById("id=".$id."");            
            $this->assign("ls", $ls);            
        }
        $city_name = isset($_GET['city_name']) ? in($_GET['city_name']) : "";
        $city_id = isset($_GET['city_id']) ? intval($_GET['city_id']) : 0;
        $area_code = isset($_GET['area_code']) ? in($_GET['area_code']) : '';
        //根据条件获取信息
        $navList = $this->city_obj->getNavList();
        $this->assign("navList",$navList);
        $this->assign("city_name", $city_name);
        $this->assign('city_id', $city_id);
        $this->assign('area_code', $area_code);
        $this->assign('id', $id);
        $this->display();
    }

    public function batchedit(){
        $kid = isset($_GET['kid']) ? in($_GET['kid']) : '';
        $city_id_str = "";
        $area_code_str = "";
        $city__arr = array();
        if(!empty($kid)){
            $sc_id_arr = explode(",", $kid);    
            foreach ($sc_id_arr as $k=>$v){
                $snd_ls = $this->city_obj->getSiteNodeDeployById($v);
                $sc_ls = $this->city_obj->getCityBySc_Id($v);
                $city__arr[$k]['sc_id'] = $v;
                $city__arr[$k]['code_no'] = $snd_ls['code_no'];
                $city__arr[$k]['sc_name'] = $sc_ls['sc_name'];
                $city_id_str = isset($city_id_str) && !empty($city_id_str) ? $city_id_str.",".$v : $v;
                $area_code_str = isset($area_code_str) && !empty($area_code_str) ? $area_code_str.",".$snd_ls['code_no'] : $snd_ls['code_no'];                
            }        
        }


        //获取百万点初始化后的所有城市
        $sndc = $this->city_obj->getSiteNodeDeployCityList();

        // 获取导航跳转列数据
        $navList = $this->city_obj->getNavList();
        $this->assign("navList",$navList);
        $this->assign("kid",$kid);
        $this->assign("city_id",$city_id_str);
        $this->assign("area_code",$area_code_str);
        $this->assign("sc_name_list",$city__arr);
        $this->assign("sndc",$sndc);
        $this->display();
    }

    public function batchadd(){
        if($_POST){
            $city = isset($_POST['city']) ? in($_POST['city']) : '';
            if(empty($city)){
                echo json_encode(array('status' => 'error', 'msg' => '城市不能为空!'));
                exit;
            }
            $nav_id = isset($_POST['nav_id']) ? intval($_POST['nav_id']) : 0;
            if(!$nav_id){
                echo json_encode(array('status' => 'error', 'msg' => '导航跳转地址参数错误!'));
                exit;
            }
            
            $s = substr($city, 0, 1);;
            if($s==','){
                $city = substr($city, 1);
            }
            $city_arr = explode(",", $city);
            foreach ($city_arr as $key => $value) {
                if($value!='undefined'){                
                    $snd_ls = $this->city_obj->getSiteNodeDeployById($value);
                    $data = array(
                      'city_id' => $value,
                      'area_code' => $snd_ls['code_no'],
                      'nav_id' => $nav_id
                    );
                    $rel_ls = $this->city_obj->getcityListById("city_id=".$value."");
                    if($rel_ls){
                        $result = $this->city_obj->edit("city_id=".$value."",$data);
                    }else{
                        $result = $this->city_obj->add($data);
                    }
                }
            }
            if($result){
                $nav_ls = $this->city_obj->getNavListById($nav_id);
                //日志的管理
                $log_content="";
                $log_content[] = "nav_name:" . $nav_ls['nav_name'];
                $log_content[] = "nav_url:" . $nav_ls['nav_url'];
                $log_content[] = "city_id:" . $city;
                $log_content = implode(" , ", $log_content);
                $operationLogDAO_obj=operationLogDAO::getInstance();
                $operationLogDAO_obj->create_log('导航跳转城市批量修改',$log_content,'LC006',"LOT006");
                echo json_encode(array("status"=>"success","msg"=>"修改成功!","isRefresh"=>1,"closePopup"=>1));  exit;
            }else{
                 echo json_encode(array("status"=>"error","msg"=>"修改失败！")); exit;
            }            
        }
        
    }
}