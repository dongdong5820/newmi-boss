<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		lidc <lidaichen@100msh.com>
 * @date		2015-1-22
 * @desc		申请wifi
 */
class navListMod extends commonMod{
    private $nav_obj;
    public function __construct(){
        parent::__construct();
        $this->nav_obj = new NavList();
    }   
    
    /**
     *  @author [lidc] <[email]>
     *  获取导航跳转列表信息
     */
    public function index(){
        $this->authMod();
        $where = "1 AND nav_default=0";
        $s_n_name = isset($_GET['s_n_name']) ? in($_GET['s_n_name']) : "";
        if(!empty($s_n_name)){
            $where .=" AND (nav_name like '%".$s_n_name."%' or nav_url like '%".$s_n_name."%')";
        }

        $nav_status = isset($_GET['nav_status']) ? intval($_GET['nav_status']) : -1;
        if($nav_status!=-1){
            $where .= " AND nav_status=".$nav_status." ";
        }
        $page = isset($_GET['page']) && !empty($_GET['page']) ? intval($_GET['page']) : 1;
        $numPerPage = 10;
        $start = ($page - 1)* $numPerPage;
        $list = $this->nav_obj->getNavList($where, $start, $numPerPage);
        $count = $this->nav_obj->getNavListCount($where);
        $page_string = $this->page('navList', $count, $numPerPage, 5, 4);

        if($list){
            foreach ($list as $key => $value) {
                if($list[$key]['nav_status']==1){
                    $list[$key]['nav_status'] = "有效";
                }else{
                    $list[$key]['nav_status'] = "无效";
                }
            }
        }else{
            $list = "";
        }

        $this->assign("list",$list);
        $this->assign("page_string", $page_string);
        $this->assign("s_n_name", $s_n_name);
        $this->assign("nav_status", $nav_status);
        $this->display();
    }

    /**
     * @author [lidc] 
     * @desc   添加新的导航跳转.
     */
    public function add(){
        if($_POST){
            $nav_name = isset($_POST['nav_name']) ? in($_POST['nav_name']) : "";
            if(empty($nav_name)){
                echo json_encode(array("status"=>"error","msg"=>"导航名称不能为空！")); 
                exit;
            }else{
                $checkCount = $this->nav_obj->getCheckNavList("nav_name='".$nav_name."'");
                if($checkCount>0){
                    echo json_encode(array("status"=>"error","msg"=>"该导航名称已经存在！")); 
                    exit;
                }
            }
            $nav_url = isset($_POST['nav_url']) ? in($_POST['nav_url']) : "";
            $nav_url=html_entity_decode($nav_url);
            if(empty($nav_url)){
                echo json_encode(array("status"=>"error","msg"=>"导航链接地址不能为空！"));
                exit;
            }else{
                $checkCount = $this->nav_obj->getCheckNavList("nav_url='".$nav_url."'");
                if($checkCount>0){
                    echo json_encode(array("status"=>"error","msg"=>"该导航地址已经存在！")); 
                    exit;
                }
            }
            $nav_status = isset($_POST['nav_status']) ? intval($_POST['nav_status']) : 0;

            $data = array(
                'nav_name' => $nav_name,
                'nav_url'  => $nav_url,
                'nav_status' => $nav_status
            );
            $result = $this->nav_obj->add($data);
            if($result){
                //日志的管理
                $log_content="";
                $log_content[] = "nav_name:" . $nav_name;
                $log_content[] = "nav_url:" . $nav_url;
                $log_content[] = "nav_status:" . $nav_status==1 ? "有效" : "无效";
                $log_content = implode(" , ", $log_content);
                $operationLogDAO_obj=operationLogDAO::getInstance();
                $operationLogDAO_obj->create_log('导航跳转导航列表--添加导航',$log_content,'LC006',"LOT001");
                echo json_encode(array("status"=>"success","msg"=>"添加成功!","isRefresh"=>1,"closePopup"=>1));  exit;
            }else{
                 echo json_encode(array("status"=>"error","msg"=>"添加失败！")); exit;
            }
        }
        $this->display();
    }

    public function edit(){
        if($_POST){
            $nav_id = isset($_POST['nav_id']) ? intval($_POST['nav_id']) : 0;
            if(!$nav_id){
                echo json_encode(array("status"=>"error","msg"=>"修改失败！")); 
                exit;
            }
            
            $nav_name = isset($_POST['nav_name']) ? in($_POST['nav_name']) : "";
            if(empty($nav_name)){
                echo json_encode(array("status"=>"error","msg"=>"导航名称不能为空！")); 
                exit;
            }else{
                $checkCount = $this->nav_obj->getCheckNavList("nav_name='".$nav_name."'  and nav_id!=".$nav_id." ");
                
                if($checkCount>0){
                    echo json_encode(array("status"=>"error","msg"=>"该导航名称已经存在！")); 
                    exit;
                }
            }
            $nav_url = isset($_POST['nav_url']) ? in($_POST['nav_url']) : "";
            $nav_url=html_entity_decode($nav_url);
            if(empty($nav_url)){
                echo json_encode(array("status"=>"error","msg"=>"导航链接地址不能为空！"));
                exit;
            }else{
                $checkCount = $this->nav_obj->getCheckNavList("nav_url='".$nav_url."' and nav_id!=".$nav_id." ");
                if($checkCount>0){
                    echo json_encode(array("status"=>"error","msg"=>"该导航地址已经存在！")); 
                    exit;
                }
            }
            $nav_status = isset($_POST['nav_status']) ? intval($_POST['nav_status']) : 0;
            $rel_count = isset($_POST['rel_count']) ? intval($_POST['rel_count']) : 0;
            $data = array(
                'nav_name' => $nav_name,
                'nav_url'  => $nav_url,
                'nav_status' => $nav_status
            );
            
            $result = $this->nav_obj->edit("nav_id=".$nav_id."",$data);
            if($result){
                
                if($rel_count>0 && $nav_status==0){
                    $nl = $this->nav_obj->getNavListDefault();                    
                    if($nl){
                        $default_nav_id = $nl['nav_id'];
                        $data = array('nav_id' => $default_nav_id);
                        $this->nav_obj->editTNavCityRel("nav_id=".$nav_id."", $data);
                    }                    
                }
                //日志的管理
                $log_content="";
                $log_content[] = "nav_name:" . $nav_name;
                $log_content[] = "nav_url:" . $nav_url;
                $log_content[] = "nav_status:" . $nav_status==1 ? "有效" : "无效";
                $log_content = implode(" , ", $log_content);
                $operationLogDAO_obj=operationLogDAO::getInstance();
                $operationLogDAO_obj->create_log('导航跳转导航列表--修改导航',$log_content,'LC006',"LOT002");
                echo json_encode(array("status"=>"success","msg"=>"修改成功!","isRefresh"=>1,"closePopup"=>1));  exit;
            }else{
                 echo json_encode(array("status"=>"error","msg"=>"修改失败！")); exit;
            }
        }
        $nav_id = isset($_GET['nav_id']) ? intval($_GET['nav_id']) : 0;
        if(!$nav_id){            
            echo json_encode(array("status"=>"error","msg"=>"参数异常！")); exit;
        }
        $rs = $this->nav_obj->getNavListById($nav_id);
        $rel_count = $this->nav_obj->getCheckCityNavRelCount("nav_id=".$nav_id."");
        $this->assign("rel_count", $rel_count);
        $this->assign("ls", $rs);
        $this->display();
    }

    public function editdefault(){
        if($_POST){
            $nav_id = isset($_POST['nav_id']) ? intval($_POST['nav_id']) : 0;
            
            $nav_name = isset($_POST['nav_name']) ? in($_POST['nav_name']) : "";
            if(empty($nav_name)){
                echo json_encode(array("status"=>"error","msg"=>"导航名称不能为空！")); 
                exit;
            }else{
                $checkCount = $this->nav_obj->getCheckNavList("nav_name='".$nav_name."'  and nav_id!=".$nav_id." ");                
                if($checkCount>0){
                    echo json_encode(array("status"=>"error","msg"=>"该导航名称已经存在！")); 
                    exit;
                }
            }
            $nav_url = isset($_POST['nav_url']) ? in($_POST['nav_url']) : "";
            if(empty($nav_url)){
                echo json_encode(array("status"=>"error","msg"=>"导航链接地址不能为空！"));
                exit;
            }else{
                $checkCount = $this->nav_obj->getCheckNavList("nav_url='".$nav_url."' and nav_id!=".$nav_id." ");
                if($checkCount>0){
                    echo json_encode(array("status"=>"error","msg"=>"该导航地址已经存在！")); 
                    exit;
                }
            }
            $nav_status = 1;

            $data = array(
                'nav_name' => $nav_name,
                'nav_url'  => $nav_url,
                'nav_status' => $nav_status,
                'nav_default' => 1
            );
            if(!$nav_id){
                $result = $this->nav_obj->add($data);
            }else{
                $result = $this->nav_obj->edit("nav_id=".$nav_id."",$data);
            }
            
            if($result){
                //日志的管理
                $log_content="";
                $log_content[] = "nav_name:" . $nav_name;
                $log_content[] = "nav_url:" . $nav_url;
                $log_content[] = "nav_status:" . $nav_status==1 ? "有效" : "无效";
                $log_content = implode(" , ", $log_content);
                $operationLogDAO_obj=operationLogDAO::getInstance();
                $operationLogDAO_obj->create_log('导航跳转导航列表--修改默认导航',$log_content,'LC006',"LOT002");
                echo json_encode(array("status"=>"success","msg"=>"修改成功!","isRefresh"=>1,"closePopup"=>1));  exit;
            }else{
                 echo json_encode(array("status"=>"error","msg"=>"修改失败！")); exit;
            }
        }
        $nav_id = isset($_GET['nav_id']) ? intval($_GET['nav_id']) : 0;    
        $rs = $this->nav_obj->getNavListDefaultById($nav_id);
        if(!empty($nav_id)){
            $rel_count = $this->nav_obj->getCheckCityNavRelCount("nav_id=".$rs['nav_id']."");
        }else {
            $rel_count = 0;
        }
        
        $this->assign("rel_count", $rel_count);
        $this->assign("ls", $rs);
        $this->display();
    }

    public function del(){
        $kid = isset($_GET['kid']) ? in($_GET['kid']) : '';
        if(empty($kid)){
            echo json_encode(array('status'=>"error",'msg'=>'空参数错误！',));exit; 
        }else{
            $nav_id_arr = explode(",", $kid);
            $count = count($nav_id_arr);
            if ($count > 1) {                
                if (is_array($nav_id_arr)) {
                    $num = 0;
                    foreach ($nav_id_arr as $val) {
                        $count_id = $this->nav_obj->getCheckCityNavRelCount("nav_id=".$val."");
                        $num = $num + $count_id;                 
                    }
                    if ($num > 0) {
                        echo json_encode(array("status" => "error", "msg" => "您选择的导航与城市关联，请先取消关联!"));
                        exit;
                    }              
                }
            } else {
                $count_ids = $this->nav_obj->getCheckCityNavRelCount("nav_id=".$nav_id_arr[0]."");
                if ($count_ids > 0) {
                    echo json_encode(array("status" => "error", "msg" => "您选择的导航与城市关联，请先取消关联!"));
                    exit;
                }
            }
        }
        $nav_id_str = $kid;
        $result = $this->nav_obj->del($kid);
        if($result){            
            $nav_name_str = "";
            $nls = $this->nav_obj->getNavListBiId($nav_id_str);
            if($nls){
                foreach ($nls as $key=>$value){
                    $nav_name_str = $nav_name_str!="" ? $nav_name_str.",".$value['nav_name'] : $value['nav_name'];
                }    
            }      
            //日志的管理
            $log_content="";
            $log_content[] = "nav_name:" . $nav_name_str;
            $log_content = implode(" , ", $log_content);
            $operationLogDAO_obj=operationLogDAO::getInstance();
            $operationLogDAO_obj->create_log('导航跳转导航列表--删除导航',$log_content,'LC006',"LOT003");
            echo json_encode(array('status'=>"success",'msg'=>'删除成功！',"isRefresh"=>1,"closePopup"=>1));exit;
        }else{
            echo json_encode(array('status'=>"error",'msg'=>'删除失败！'));exit;
        }
    }
}