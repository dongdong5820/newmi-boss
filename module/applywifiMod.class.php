<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		lidc <lidaichen@100msh.com>
 * @date		2015-1-22
 * @desc		申请wifi
 */
class applywifiMod extends commonMod{
    private $applywifi_obj;
    private $stateCity_obj;
    public function __construct(){
        parent::__construct();
        $this->applywifi_obj = new ApplyWifi();
        $this->stateCity_obj = new StateCity();
    }
    
    public function index(){
        $this->authMod();        
        $where = 1;
        $s_n_name = isset($_GET['s_n_name']) ? in($_GET['s_n_name']) : "";
        if(!empty($s_n_name)){
            $where .=" AND (apply_username like '%".$s_n_name."%' or apply_store_name like '%".$s_n_name."%')";            
        }
        $apply_username = isset($_GET['apply_username']) ? in($_GET['apply_username']) : "";
        if(!empty($apply_username)){
            $where .=" AND apply_username like '%".$apply_username."%' ";
        }
        $apply_phone = isset($_GET['apply_phone']) ? in($_GET['apply_phone']) : "";
        if(!empty($apply_phone)){
            $where .=" AND apply_phone like '%".$apply_phone."%' ";
        }
        $apply_store_name = isset($_GET['apply_store_name']) ? in($_GET['apply_store_name']) : "";
        if(!empty($apply_store_name)){
            $where .=" AND apply_store_name like '%".$apply_store_name."%' ";
        }
        $province = isset($_GET['province']) ? intval($_GET['province']) : 0;
        $s_city = isset($_GET['s_city']) ? intval($_GET['s_city']) : 0;
        $city_list = 0;
        if($province){
            if($s_city){
                $where .=" AND sc_pid=".$province." AND sc_id=".$s_city."";
                $city_list = $this->applywifi_obj->getApplyWifiCityByList("sc_pid=".$province."");;    //获取城市信息
            }else{
                $where .=" AND sc_pid=".$province."";
            }
        }
        $s_time_start = isset($_GET['s_time_start']) ? in($_GET['s_time_start']) : "";
        $s_time_end = isset($_GET['s_time_end']) ? in($_GET['s_time_end']) : "";
        $time_start = strtotime($s_time_start);
        $time_end = strtotime($s_time_end);
        if(!empty($s_time_start)){
            if(!empty($s_time_end)){
                $time_end = strtotime('+1 day',strtotime($s_time_end));
                $where .=" AND apply_time>=".$time_start." AND apply_time<=".$time_end." ";                               
            }else{
                $where .=" AND apply_time>=".$time_start."";
            }
        }elseif (!empty($s_time_end)){
            $time_end = strtotime('+1 day',strtotime($s_time_end));
            $where .=" AND apply_time<=".$time_end."";
        }

        $page = isset($_GET['page'])&&!empty($_GET['page']) ? intval($_GET['page']) : 1;
        $numPerPage = 15;
        $start = ($page-1)*$numPerPage;
        $list = $this->applywifi_obj->getApplyWifiList($where, $start, $numPerPage);               
        $count = $this->applywifi_obj->getApplyWifiCount($where);
        $page_string = $this->page('applywifi', $count, $numPerPage, 5, 4);
        if($list){
            foreach ($list as $key=>$value){
                if($value['apply_operators']==0){
                    $list[$key]['apply_operators'] = '中国电信';
                }elseif ($value['apply_operators']==1){
                    $list[$key]['apply_operators'] = '中国联通';
                }elseif ($value['apply_operators']==2){
                    $list[$key]['apply_operators'] = '中国移动';
                }elseif ($value['apply_operators']==3){
                    $list[$key]['apply_operators'] = '长城宽带';
                }
                $partner = $this->applywifi_obj->getPartnerById($value['apply_industry']);
                $list[$key]['apply_industry'] = $partner['cat_name'];
                $cl = $this->applywifi_obj->getStatecityList($value['sc_id']);
                               
                $sc_pname = isset($cl['sc_pname']) ? $cl['sc_pname'] : "";
                $sc_name = isset($cl['sc_name']) ? $cl['sc_name'] : "";
                if(!empty($sc_pname) && !empty($sc_name)){
                    $list[$key]['sc_pname'] = $sc_pname;
                    $list[$key]['sc_name'] = $sc_name;
                }else{
                    $cl = $this->applywifi_obj->getApplyWifiCityList($value['sc_id']);
                    $list[$key]['sc_pname'] = $cl['sc_pname'];
                    $list[$key]['sc_name'] = $cl['sc_name'];
                }
                
                
                
                $list[$key]['apply_address'] = out($value['apply_address']);
            }
        }
                  
        
//         $province_list = $this->stateCity_obj->getScListByScpid(0);    //获取省份信息
        $province_list = $this->applywifi_obj->getApplyWifiCityByList("sc_pid=0"); 
        
        $sArr = array(
          's_n_name' => $s_n_name,
          'apply_username' => $apply_username,
          'apply_phone' => $apply_phone,
          'apply_store_name' => $apply_store_name,
          'province' => $province,
          's_city' => $s_city,
          's_time_start' => $s_time_start,
          's_time_end' => $s_time_end  
        );
        $this->assign('city_list', $city_list);
        $this->assign('lss', $sArr);
        $this->assign("list", $list);
        $this->assign("page_string", $page_string);    
        $this->assign('plist', $province_list);
        $this->display();
    }
    
//     public function getCityAjax(){
//         $sc_pid = isset($_GET['sc_pid']) ? intval($_GET['sc_pid']) : 0;
//         $province_list = $this->stateCity_obj->getScListByScpid($sc_pid);
//         echo json_encode($province_list);
//     }
    public function getCityAjax(){
        $sc_pid = isset($_GET['sc_pid']) ? intval($_GET['sc_pid']) : 0;
        $province_list = $this->applywifi_obj->getApplyWifiCityByList("sc_pid=".$sc_pid."");
        echo json_encode($province_list);
    }
    
    /**
     * @desc	导出申请WiFi商户表
     * @author 	lidc <lidaichen@100msh.com>
     */
    public function exceloutput(){
        header("Content-Type:text/html;charset:utf-8");
        require 'system/lib/phpExcel_class/Classes/PHPExcel.php';
        require 'system/lib/phpExcel_class/Classes/PHPExcel/Style.php';
        $where = "1";
        //关键词搜索    
        $s_n_name = isset($_GET['s_n_name']) ? in($_GET['s_n_name']) : "";
        if(!empty($s_n_name)){
            $where .=" AND (apply_username like '%".$s_n_name."%' or apply_store_name like '%".$s_n_name."%')";            
        }
        //条件搜索
        $apply_username = isset($_GET['apply_username']) ? in($_GET['apply_username']) : "";
        if(!empty($apply_username)){
            $where .=" AND apply_username like '%".$apply_username."%' ";
        }
        $apply_phone = isset($_GET['apply_phone']) ? in($_GET['apply_phone']) : "";
        if(!empty($apply_phone)){
            $where .=" AND apply_phone like '%".$apply_phone."%' ";
        }
        $apply_store_name = isset($_GET['apply_store_name']) ? in($_GET['apply_store_name']) : "";
        if(!empty($apply_store_name)){
            $where .=" AND apply_store_name like '%".$apply_store_name."%' ";
        }
        $s_city = isset($_GET['s_city']) ? intval($_GET['s_city']) : 0;
        if($s_city){
            $where .=" AND sc_id=".$s_city."";
        }

        $s_time_start = isset($_GET['s_time_start']) ? in($_GET['s_time_start']) : "";
        $s_time_end = isset($_GET['s_time_end']) ? in($_GET['s_time_end']) : "";
        $time_start = strtotime($s_time_start);
        $time_end = strtotime($s_time_end);
        if(!empty($s_time_start)){
            if(!empty($s_time_end)){
                $time_end = strtotime('+1 day',strtotime($s_time_end));
                $where .=" AND apply_time>=".$time_start." AND apply_time<=".$time_end." ";                               
            }else{
                $where .=" AND apply_time>=".$time_start."";
            }
        }elseif (!empty($s_time_end)){
            $time_end = strtotime('+1 day',strtotime($s_time_end));
            $where .=" AND apply_time<=".$time_end."";
        }


        
        $result = $this->applywifi_obj->getApplyWifiList($where, 0, 5000);
        $total_num = $this->applywifi_obj->getApplyWifiCount($where);
        if($total_num>10000){
            echo "数据大于10000条无法导出,请筛选后进行导出操作！";
            exit;
        }
    
        set_time_limit(300);
        $objExcel = new PHPExcel();
            
        $objExcel->setActiveSheetIndex(0); //设置当前活动的sheet
        $objWorksheet = $objExcel->getActiveSheet();
        $objWorksheet->setTitle("申请WiFi商户表"); //设置sheet名字
        
        $objWorksheet->setCellValue("A1", "申请人名称");        
        $objWorksheet->getColumnDimension("A")->setWidth(20);
        $objWorksheet->setCellValue("B1", "门店名称");
        $objWorksheet->getColumnDimension("B")->setWidth(25);
        $objWorksheet->setCellValue("C1", "联系电话");
        $objWorksheet->getColumnDimension("C")->setWidth(25);        
        $objWorksheet->setCellValue("D1", "所在省份");
        $objWorksheet->getColumnDimension("D")->setWidth(20);
        $objWorksheet->setCellValue("E1", "所在城市");
        $objWorksheet->getColumnDimension("E")->setWidth(20);        
        $objWorksheet->setCellValue("F1", "所在详细地址");
        $objWorksheet->getColumnDimension("F")->setWidth(35);
        $objWorksheet->setCellValue("G1", "行业");
        $objWorksheet->getColumnDimension("G")->setWidth(25);
        $objWorksheet->setCellValue("H1", "运营商");
        $objWorksheet->getColumnDimension("H")->setWidth(20);
        $objWorksheet->setCellValue("I1", "宽带");
        $objWorksheet->getColumnDimension("I")->setWidth(20);
        $objWorksheet->setCellValue("J1", "来源");
        $objWorksheet->getColumnDimension("J")->setWidth(25);
        $objWorksheet->setCellValue("K1", "申请时间");
        $objWorksheet->getColumnDimension("K")->setWidth(25); 

        $y=2;
        if(!empty($result)){
            foreach ($result as $k=>$v){
                if($v['apply_operators']==0){
                    $apply_operators = "中国电信";
                }elseif ($v['apply_operators']==1){
                    $apply_operators = "中国联通";
                }elseif ($v['apply_operators']==2){
                    $apply_operators = "中国移动";
                }elseif ($v['apply_operators']==3){
                    $apply_operators = "长城宽带";
                }                
                $apply_broadband = $v['apply_broadband']."M";
                $apply_time = date("Y-m-d H:i:s", $v['apply_time']);
                $partner = $this->applywifi_obj->getPartnerById($v['apply_industry']);
                $apply_industry = $partner['cat_name'];
                
                $province = '';
                $city = '';
                $cl = $this->applywifi_obj->getApplyWifiCityById($v['sc_id']);
                if($cl && $cl['sc_pid']==$v['sc_pid']){
                    $cls = $this->applywifi_obj->getApplyWifiCityById($v['sc_pid']);
                    $province = $cls['city_name'];
                    $city = $cl['city_name'];
                }else {
                    $scl = $this->applywifi_obj->getStatecityList($v['sc_id']);
                    $province = $scl['sc_pname'];
                    $city = $scl['sc_name'];
                }                
                
                 
                
                $objWorksheet->getCell("A".$y)->setValueExplicit(html_out($v['apply_username']), PHPExcel_Cell_DataType::TYPE_STRING);    //申请商户
                $objWorksheet->getCell("B".$y)->setValueExplicit(html_out($v['apply_store_name']), PHPExcel_Cell_DataType::TYPE_STRING);  //门店名称
                $objWorksheet->getCell("C".$y)->setValueExplicit(html_out($v['apply_phone']), PHPExcel_Cell_DataType::TYPE_STRING);       //联系电话
                $objWorksheet->getCell("D".$y)->setValueExplicit($province, PHPExcel_Cell_DataType::TYPE_STRING);       //所在省份
                $objWorksheet->getCell("E".$y)->setValueExplicit($city, PHPExcel_Cell_DataType::TYPE_STRING);       //所在城市
                $objWorksheet->getCell("F".$y)->setValueExplicit(html_out($v['apply_address']), PHPExcel_Cell_DataType::TYPE_STRING);     //详细地址
                $objWorksheet->getCell("G".$y)->setValueExplicit($apply_industry, PHPExcel_Cell_DataType::TYPE_STRING);    //所在行业
                $objWorksheet->getCell("H".$y)->setValueExplicit($apply_operators, PHPExcel_Cell_DataType::TYPE_STRING);   //运营商
                $objWorksheet->getCell("I".$y)->setValueExplicit($apply_broadband, PHPExcel_Cell_DataType::TYPE_STRING);   //宽带
                $objWorksheet->getCell("J".$y)->setValueExplicit($v['apply_source'], PHPExcel_Cell_DataType::TYPE_STRING);      //来源
                $objWorksheet->getCell("K".$y)->setValueExplicit($apply_time, PHPExcel_Cell_DataType::TYPE_STRING);        //运营商 */
                $y++;
            }
        }
        
        ob_end_clean();//清除缓冲区,避免乱码
        /** 导出其他低版本excel格式 */
        $filename= "申请WiFi商户表-".date("Ymd-His").".xls";
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Cache-Control: max-age=0');
		$objWriter=PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
		$objWriter->save('php://output');
        exit;
    }
    
    public function infos(){
        $apply_id = isset($_GET['apply_id']) ? intval($_GET['apply_id']) : 0;
        if(!$apply_id){
            echo "无参数";
            exit;
        }
        $apply_list = $this->applywifi_obj->getApplyWifiById($apply_id);
        if($apply_list){
            if($apply_list['apply_operators']==0){
                $apply_list['apply_operators'] = '中国电信';
            }elseif ($apply_list['apply_operators']==1){
                $apply_list['apply_operators'] = '中国联通';
            }elseif ($apply_list['apply_operators']==2){
                $apply_list['apply_operators'] = '中国移动';
            }elseif ($apply_list['apply_operators']==3){
                $apply_list['apply_operators'] = '长城宽带';
            }
            $partner = $this->applywifi_obj->getPartnerById($apply_list['apply_industry']);
            $apply_list['apply_industry'] = $partner['cat_name'];
            $cl = $this->applywifi_obj->getStatecityList($apply_list['sc_id']);
            $sc_pname = isset($cl['sc_pname']) ? $cl['sc_pname'] : '';
            $sc_name = isset($cl['sc_name']) ? $cl['sc_name'] : '';
            if(!empty($sc_pname) && !empty($sc_name)){                
                $apply_list['sc_pname'] = $sc_pname;
                $apply_list['sc_name'] = $sc_name;
            }else{
                $cl = $this->applywifi_obj->getApplyWifiCityList($apply_list['sc_id']);
                $apply_list['sc_pname'] = $cl['sc_pname'];
                $apply_list['sc_name'] = $cl['sc_name'];
            }            
            
        }
        $this->assign('info', $apply_list);
        $this->display();
        
    }
}