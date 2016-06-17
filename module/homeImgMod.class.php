<?php
/**
 * @copyright   ©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link        http://www.100msh.net
 * ---------------------------------------------------------------------
 * @date        2015-08-17
 * @desc        首页图片
 */
class homeImgMod extends commonMod{
    private $homeImg_obj;
    
    public function __construct(  ){
        parent::__construct();
        $this->homeImg_obj = new HomeImg();
    }
    public function index(){
        //$this->authMod();
        $numPerPage = 10;        
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $start = ($page-1) * $numPerPage;
        $where = 1;
        $s_n_name = isset($_GET['s_n_name']) ? trim($_GET['s_n_name']) : '';
        if(!empty($s_n_name)){           
            $where .=" AND img_desc like '%".$s_n_name."%'";                           
        }
        $web_type = isset($_GET['web_type']) ? intval($_GET['web_type']) : 1;
        if($web_type){
            $where .=" AND web_type={$web_type} ";
        }
        $status = isset($_GET['status']) ? intval($_GET['status']) : 2;
        if($status!=2){
            $where .=" AND status={$status} ";
        }
        $list = $this->homeImg_obj->getHomeImgList($where, $start, $numPerPage);
        $page_string = '';
        if($list){
            $count = $this->homeImg_obj->getHomeImgCount($where);
            $page_string = $this->page('homeImg/index', $count, $numPerPage, 5, 4);           
        }

        $this->assign('official_site_url_pc', App::$config['official_site_url_pc']);
        $this->assign('official_site_url_mobile', App::$config['official_site_url_mobile']);
        $this->assign('list', $list);
        $this->assign('page_string', $page_string);
        $this->assign('s_n_name', $s_n_name);
        $this->assign('web_type', $web_type);
        $this->assign('status', $status);
        $this->display();
    }
    public function add(){
        if($_POST){
            $web_type = isset($_POST['paas']) ? intval($_POST['paas']) : 1;
            $img_desc = isset($_POST['img_desc']) ? trim($_POST['img_desc']) : '';
            if(!empty($img_desc)){
                $rs = $this->homeImg_obj->getHomeImgCount("img_desc='".$img_desc."'");
                if($rs>0){
                    echo json_encode(array('status' =>'error' ,'msg'=>"图片描述重复，请重新输入！" ));exit;
                }
            }
            
            if(empty($_FILES['bg_img']['name'])){
                echo json_encode(array('status' =>'error' ,'msg'=>"请上传背景图！" ));exit;
            }
            $file_path = '';
            if($_FILES['bg_img']['name']){
                //背景图
                $UpFileDir=App::$config['ACCESSORY_FOLDER']."official_attach/homeImg/";
                $this->upload_obj=new Upload();
                $fileNamge="bg_img";            
                $ext = strtolower(strrchr($_FILES["bg_img"]["name"], "."));
                $size = getimagesize($_FILES["bg_img"]['tmp_name']);
                $this->upload_obj->UpFileAttribute($fileNamge);
                $UpFileName='stage-bg_'.time();//无文件后缀
                $MaxSize=5000;
                $FileType=array('.png','.jpg','.jpeg','.bmp');//后缀注意带上.
                $info=$this->upload_obj->Uploads($fileNamge,$UpFileDir,$UpFileName,$MaxSize,$FileType,null);
                switch ($info){
                    case -1:
                        echo json_encode(array('status'=>"error",'msg'=>"图片上传失败:上传的图片类型不正确！请上传类型为png,jpg,jpeg,bmp的文件！"));exit;
                        break;
                    case -2:
                        echo json_encode(array('status'=>"error",'msg'=>"图片上传失败:上传的图片大小不符合要求！请上传小于".$MaxSize."(KB)的文件！"));exit;
                        break;
                    default:
                        break;
                }
                
                $file_path=App::$config['ACCESSORY_URL']."official_attach/homeImg/". $UpFileName.$ext;      //背景图
            }
            $file_path2 = '';
            if(isset($_FILES['outlook_img']['name'])){            
                //前景图
                $fileNamge2="outlook_img";
                $ext2 = strtolower(strrchr($_FILES["outlook_img"]["name"], "."));
                $size2 = getimagesize($_FILES["outlook_img"]['tmp_name']);
                $this->upload_obj->UpFileAttribute($fileNamge2);
                $UpFileName2='stage-outlook_'.time();//无文件后缀
                $FileType2=array('.png');//后缀注意带上.
                $info2=$this->upload_obj->Uploads($fileNamge2,$UpFileDir,$UpFileName2,$MaxSize,$FileType2,null);
                switch ($info2){
                    case -1:
                        echo json_encode(array('status'=>"error",'msg'=>"图片上传失败:上传的图片类型不正确！请上传类型为png的文件！"));exit;
                        break;
                    case -2:
                        echo json_encode(array('status'=>"error",'msg'=>"图片上传失败:上传的图片大小不符合要求！请上传小于".$MaxSize."(KB)的文件！"));exit;
                        break;
                    default:
                        break;
                }
                $file_path2=App::$config['ACCESSORY_URL']."official_attach/homeImg/". $UpFileName2.$ext2;   //前景图
            }
            
                        
            $background_color = isset($_POST['background_color']) ? trim($_POST['background_color']) : '';
            $pc_prepic_width = isset($_POST['pc_prepic_width'])?trim($_POST['pc_prepic_width']) : 0;
            $mb_prepic_width = isset($_POST['mb_prepic_width']) ? trim($_POST['mb_prepic_width']):0;
            if($web_type==1){
                $outlookImgWidth = $pc_prepic_width;
            }else{
                $outlookImgWidth = $mb_prepic_width;
            }
            
            $url_arr = isset($_POST['link_url'])?$_POST['link_url']:'';
            $url_str1 = isset($url_arr[0])||!empty($url_arr[0])?$url_arr[0]:'';
            $url1_arr = explode(",",$url_str1);
            $url1 = isset($url1_arr[0])||!empty($url1_arr[0])?$url1_arr[0]:'';
            $url1Style = isset($url1_arr[1])||!empty($url1_arr[1])?$url1_arr[1]:'';
            
            $url_str2 = isset($url_arr[1])||!empty($url_arr[1])?$url_arr[1]:'';
            $url2_arr = explode(",",$url_str2);
            $url2 = isset($url2_arr[0])||!empty($url2_arr[0])?$url2_arr[0]:'';
            $url2Style = isset($url2_arr[1])||!empty($url2_arr[1])?$url2_arr[1]:'';            

            $order_num = isset($_POST['img_order']) ? intval($_POST['img_order']) : 1;
            $is_show = isset($_POST['is_show']) ? intval($_POST['is_show']) : 0;
            
            $type_count = $this->homeImg_obj->getHomeImgCount("web_type={$web_type} AND status=1 ");       
            $msg = "";     
            if($type_count>=6){
                if($is_show){
                    $msg="但轮播图最多6张有效,所以该添加为无效！";
                    $is_show = 0;
                }
            }
            
            $data = array(
                'web_type'=>$web_type,
                'img_desc'=>$img_desc,
                'background'=>$file_path,
                'outlook_img'=>$file_path2,
                'outlookImgWidth'=>$outlookImgWidth,
                'background_color'=>$background_color,
                'url1'=>$url1,
                'url1Style'=>$url1Style,
                'url2'=>$url2,
                'url2Style'=>$url2Style,
                'status'=>$is_show,
                'img_order'=>$order_num,
                'add_time'=>time(),
                'add_user'=>$this->user_id,
            );
            
            $home_img_id = $this->homeImg_obj->add($data);
            if($home_img_id){
                $out_data=array('status'=>"success",'msg'=>'添加成功！'.$msg,"isRefresh"=>1,"closePopup"=>1);             
            }else{
                $out_data=array('status'=>"error",'msg'=>'添加失败！');
            }
            echo json_encode($out_data);exit;
        }
    	$this->display();
    }
    public function edit(){        
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if(!$id){
            echo json_encode(array('status'=>"error",'msg'=>'参数有误！'));exit;
        }
        $list = $this->homeImg_obj->getHomeImgById("id={$id}");
        $list['background_color'] = isset($list['background_color'])&&!empty($list['background_color'])?$list['background_color']:"#000000";
        $this->assign('ls', $list);
        $this->display();
    }
    
    public function editInfo(){
            $id = isset($_POST['id'])?intval($_POST['id']):0;
            if(!$id){
                echo json_encode(array('status' =>'error' ,'msg'=>"参数错误！" ));exit;
            }
            $bg_type=isset($_POST['bg_type'])?intval($_POST['bg_type']):0;
            
            $web_type = isset($_POST['paas']) ? intval($_POST['paas']) : 1;
            $img_desc = isset($_POST['img_desc']) ? trim($_POST['img_desc']) : '';
            if(!empty($img_desc)){
                $rs = $this->homeImg_obj->getHomeImgCount("img_desc='".$img_desc."' AND id<>{$id}");
                if($rs>0){
                    echo json_encode(array('status' =>'error' ,'msg'=>"图片描述重复，请重新输入！" ));exit;
                }
            }
            $background_color = isset($_POST['background_color']) ? trim($_POST['background_color']) : '';
            $backgroundColor = isset($_POST['backgroundColor']) ? trim($_POST['backgroundColor']):'';
            if($background_color=='#000000'){
                $background_color = $backgroundColor;
            }
            $pc_prepic_width = isset($_POST['pc_prepic_width'])?trim($_POST['pc_prepic_width']) : 0;
            $mb_prepic_width = isset($_POST['mb_prepic_width']) ? trim($_POST['mb_prepic_width']):0;
            if($web_type==1){
                $outlookImgWidth = $pc_prepic_width;
            }else{
                $outlookImgWidth = $mb_prepic_width;
            }

            $url_arr = isset($_POST['link_url'])?$_POST['link_url']:'';
            $url_str1 = isset($url_arr[0])||!empty($url_arr[0])?$url_arr[0]:'';
            $url1_arr = explode(",",$url_str1);
            $url1 = isset($url1_arr[0])||!empty($url1_arr[0])?$url1_arr[0]:'';
            $url1Style = isset($url1_arr[1])||!empty($url1_arr[1])?$url1_arr[1]:'';
        
            $url_str2 = isset($url_arr[1])||!empty($url_arr[1])?$url_arr[1]:'';
            $url2_arr = explode(",",$url_str2);
            $url2 = isset($url2_arr[0])||!empty($url2_arr[0])?$url2_arr[0]:'';
            $url2Style = isset($url2_arr[1])||!empty($url2_arr[1])?$url2_arr[1]:'';
            $order_num = isset($_POST['img_order']) ? intval($_POST['img_order']) : 1;
            $is_show = isset($_POST['is_show']) ? intval($_POST['is_show']) : 0;
            
            $type_count = $this->homeImg_obj->getHomeImgCount("web_type={$web_type} AND status=1 AND id<>{$id} ");
            $msg = "";            
            if($type_count>=6){
                if($is_show){
                    $msg="但轮播图最多6张有效,所以该添加为无效！";
                    $is_show = 0;
                }
            }
            
            if($bg_type){
                $data = array(
                    'web_type'=>$web_type,
                    'img_desc'=>$img_desc,
                    'status'=>$is_show,
                    'img_order'=>$order_num,
                    'edit_time'=>time(),
                    'edit_user'=>$this->user_id,
                );
            }else{            
                $data = array(
                    'web_type'=>$web_type,
                    'img_desc'=>$img_desc,
                    'outlookImgWidth'=>$outlookImgWidth,
                    'background_color'=>$background_color,
                    'url1'=>$url1,
                    'url1Style'=>$url1Style,
                    'url2'=>$url2,
                    'url2Style'=>$url2Style,
                    'status'=>$is_show,
                    'img_order'=>$order_num,
                    'edit_time'=>time(),
                    'edit_user'=>$this->user_id,
                );
            }
            //背景图
            $UpFileDir=App::$config['ACCESSORY_FOLDER']."official_attach/homeImg/";
            $this->upload_obj=new Upload();
            $MaxSize=5000;
            if(!empty($_FILES['bg_img']['name'])){            
                $fileNamge="bg_img";
                $ext = strtolower(strrchr($_FILES["bg_img"]["name"], "."));
                $size = getimagesize($_FILES["bg_img"]['tmp_name']);
                $this->upload_obj->UpFileAttribute($fileNamge);
                $UpFileName='stage-bg_'.time();//无文件后缀
                $FileType=array('.png','.jpg','.jpeg','.bmp');//后缀注意带上.
                $info=$this->upload_obj->Uploads($fileNamge,$UpFileDir,$UpFileName,$MaxSize,$FileType,null);
                switch ($info){
                    case -1:
                        echo json_encode(array('status'=>"error",'msg'=>"图片上传失败:上传的图片类型不正确！请上传类型为png,jpg,jpeg,bmp的文件！"));exit;
                        break;
                    case -2:
                        echo json_encode(array('status'=>"error",'msg'=>"图片上传失败:上传的图片大小不符合要求！请上传小于".$MaxSize."(KB)的文件！"));exit;
                        break;
                    default:
                        break;
                }
                $file_path=App::$config['ACCESSORY_URL']."official_attach/homeImg/". $UpFileName.$ext;      //背景图
                if(isset($file_path)&&!empty($file_path)){
                    $data['background']=$file_path;
                }                
            }
            if(!empty($_FILES['outlook_img']['name'])){
                //前景图
                $fileNamge2="outlook_img";
                $ext2 = strtolower(strrchr($_FILES["outlook_img"]["name"], "."));
                $size2 = getimagesize($_FILES["outlook_img"]['tmp_name']);
                $this->upload_obj->UpFileAttribute($fileNamge2);
                $UpFileName2='stage-outlook_'.time();//无文件后缀
                $FileType2=array('.png');//后缀注意带上.
                $info2=$this->upload_obj->Uploads($fileNamge2,$UpFileDir,$UpFileName2,$MaxSize,$FileType2,null);
                switch ($info2){
                    case -1:
                        echo json_encode(array('status'=>"error",'msg'=>"图片上传失败:上传的图片类型不正确！请上传类型为png的文件！"));exit;
                        break;
                    case -2:
                        echo json_encode(array('status'=>"error",'msg'=>"图片上传失败:上传的图片大小不符合要求！请上传小于".$MaxSize."(KB)的文件！"));exit;
                        break;
                    default:
                        break;
                }
                $file_path2=App::$config['ACCESSORY_URL']."official_attach/homeImg/". $UpFileName2.$ext2;   //前景图
                if(isset($file_path2)&&!empty($file_path2)){
                    $data['outlook_img']=$file_path2;
                }
            }

            $result = $this->homeImg_obj->edit("id={$id}", $data);
            if($result){                
                echo json_encode(array("status"=>"success","msg"=>"修改成功!".$msg,"isRefresh"=>1,"closePopup"=>1));	exit;
            }else{
                echo json_encode(array("status"=>"error","msg"=>"修改失败！"));	exit;
            }
    }
    
    public function infos(){
        $id=isset($_GET['id'])?intval($_GET['id']):0;
        if(!$id){
            echo json_encode(array('status'=>"error",'msg'=>'参数有误！'));exit;
        }
        $result = $this->homeImg_obj->getHomeImgById("id={$id}");
        $users = array($result['add_user'],$result['edit_user']);
        $users = array_unique($users);
        $users = implode(",", $users);
        $this->admusers_dao = new Admusers();
        $sys_list = $this->admusers_dao->getUserList($users);
        $result['add_user'] = isset($sys_list[$result['add_user']]) ? $sys_list[$result['add_user']]['username'] : '';
        $result['edit_user'] = isset($sys_list[$result['edit_user']]) ? $sys_list[$result['edit_user']]['username'] : '';
        $this->assign('info', $result);
        $this->display();
    }
    
    public function del(){
        $kid = isset($_GET['kid']) ? trim($_GET['kid']) : '';
        if(empty($kid)){
            echo json_encode(array('status'=>"error",'msg'=>'传递的参数有误！'));exit;
        }
        $list = $this->homeImg_obj->getHomeImgByIdList("id IN({$kid})");
        if($list){
            foreach ($list as $key=>$val){                
                if($val['status']){
                    echo json_encode(array('status'=>"error",'msg'=>'删除项中是有效状态,请先设置无效再删除！'));exit;
                }
            }        
            $rs = $this->homeImg_obj->delImg($kid);
            if($rs){
                echo json_encode(array('status'=>"success",'msg'=>'删除成功！',"isRefresh"=>1,"closePopup"=>1));exit;
            }else{
                echo json_encode(array('status'=>"error",'msg'=>'删除失败！'));exit;
            }
        }
    }
    public function showPic(){
        $pic_url = isset($_GET['pic_url']) ? in($_GET['pic_url']) : '';
        $outlook_img = isset($_GET['outlook_img']) ? in($_GET['outlook_img']) : '';
        if(empty($pic_url)){$this->ajax_error("图片无效!");exit;}
        $this->assign("pic_url",$pic_url);
        $this->assign('outlook_img', $outlook_img);
        $this->display();
    }
    public function setlink(){
    	$this->display();
    }
}