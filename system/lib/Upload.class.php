<?php 
/** Copyright ? 2006 - 2009 ActiveNet Logics IT Co.,Ltd. 动网创新科技（深圳）有限公司
 * @version:  v2.0
 * @website   http://www.anlogics.com
 * ---------------------------------------------
 * $Date: 2007.03.01
 * $Program Name: Upload_class.php 
*/
interface FileUpload{
	/*上传
	$FormName: 提交过来的表单name;
	$UpFileDir:上传路经;
	$UpFileName:  上传的以后的名称，空('')为当前文件的名称;
	$MaxSize:  最大上传数，单位为(K);
	$FileType: 上传类型限制。空('')为没有限制，1为默认限制(jpg,jif,png)，其它为自定义类型，如$a[]='image/jpeg';
	*/
	function Uploads($FormName,$UpFileDir,$UpFileName='',$MaxSize=0,$FileType='');
	/*错误提示*/
	function Errors($error='');
	/*上传的以后的名称，空('')为当前文件的名称;*/
	function FileName($name='');
	/*获取上传文件的属性*/
	function UpFileAttribute($FormName);
	/*限制文件大小,单位为(K),默认为200K*/
	function FileMaxSize($MaxKb);
	/*上传类型限制。空('')表示没有限制，1表示默认限制(jpg,jif,png)，其它表示自定义类型，如$a[]='image/jpeg'*/
	function UpFileType($FileType);
}

class Upload implements FileUpload{

	public $_FornName;
	public $_UpFileDir;
	public $_MaxSize=200;                  //最大上传为200k
	public $_FileType;
	public $_InitFileName;
	public $_UpFileName='';                    //上传名称
	public $_UpAddress;                     //上传文件地址+名称;
	public $_fileAtt;                       //上传文件属性
	public $_errors=true;
	public $file_info=array();

	//function __construct($FormName='',$UpFileDir='',$UpFileName='',$MaxSize=0,$FileType=1){
	//$this->Uploads($FormName,$UpFileDir,$UpFileName,$MaxSize,$FileType);
	//}//end fun
	//$UpFileName: 文件名为不带后缀名,例：example.jpg 为example
	function Uploads($FormName,$UpFileDir,$UpFileName='',$MaxSize=0,$FileType=array(),$key=null){
		if(!file_exists($UpFileDir)) $this->createDirectory($UpFileDir,$mode=0777);
		if($key!==null){
			if(empty($_FILES[$FormName]['name'][$key])){return false;}
			$this->UpFileAttribute_array($FormName,$key);
		}else{
			if(empty($_FILES[$FormName]['name'])){return false;}
			$this->UpFileAttribute($FormName);
		}
		if(!$this->FileMaxSize($MaxSize)){return SIZE_UNMATCH;}
		if(!$this->get_file_type($FileType)){return EXT_UNMATCH;}
		$this->_UpFileName=$this->FileName($UpFileName);
		$this->_UpAddress=$UpFileDir.'/'.$this->_UpFileName;
		if(is_uploaded_file($this->_fileAtt['tmp_name']) && move_uploaded_file($this->_fileAtt['tmp_name'],$this->_UpAddress))
		{
			chmod($this->_UpAddress,0777);
			@unlink($this->_fileAtt['tmp_name']);
			return UPLOAD_SUCCESS;
		}
	}

//	//获得文件的id
//	function max_file_id($table=TBL_FILES){
//		$sql="SELECT max(file_id) AS id FROM ".$table;
//		$result=DB()->ADM->execute($sql);
//		$out=DB()->ADM->arrays($result);
//		return $out['id']+1;
//	}

//	//添加文件进数据库
//	function add_file($user_id,$desc='',$table=TBL_FILES){
//		//$id=$this->max_file_id();
//		$filename=$this->_UpFileName;
//		$realname=$this->_fileAtt['name'];
//		$size=$this->_fileAtt['size'];
//		$type=$this->_FileType;
//		$query="insert into ".$table." (filename,realname,size,user_id,description,upload_date,file_ext) values ('$filename','$realname',$size,$user_id,'$desc',".time().",'$type')";
//		DB()->ADM->execute($query);
//		$file_id=DB()->ADM->getInsertId();
//		return $file_id;
//	}

	//创建目录
	function createDirectory($dir,$mode=0777){
		if( ! $dir ){return 0;}global $_LANG;
		$dir = str_replace( "\\", "/", $dir);
		$mdir = "";
		foreach(explode("/", $dir) as $val){
			$mdir .= $val."/";
			if( $val == ".." || $val == "." ){continue;}
			if(!file_exists($mdir)) {
				if(!@mkdir($mdir)){
					//echo sprintf($_LANG["l_createdir_failing"],$mdir);
					echo sprintf('目录创建不成功',$mdir);
					exit;
				}else{chmod($mdir,$mode);}
			}
		}
		return true;
	}

//	//删除目录
//	function deleteDir($dir){ if(!file_exists($dir)){return false;}
//	if (@rmdir($dir)==false && is_dir($dir)) { global $_LANG;
//	if ($dp=opendir($dir)) {
//		while (($file=readdir($dp)) != false) {
//			if (is_dir($file) && $file!='.' && $file!='..') {
//				deleteDir($file);
//			}else{
//				@unlink($dir.$file);
//			}
//		}
//		closedir($dp);
//	}else{
//		exit($_LANG["l_not_permission"]."！");
//	}
//	}
//	}
//
//	//删除文件
//	function del_file($path,$fileid,$table=TBL_FILES){
//		$sql="SELECT filename FROM ".$table." WHERE file_id=$fileid";
//		$result=DB()->ADM->execute($sql);
//		if(!$result){return false;}
//		$row=DB()->ADM->arrays($result);
//		if($row){
//			$filename=$row["filename"];
//			$fileurl=$path.$filename;
//			if(file_exists($fileurl)){
//				@unlink($fileurl);
//			}
//			$sql="DELETE FROM ".$table." WHERE file_id=$fileid";
//			DB()->ADM->execute($sql);return true;
//		}return false;
//	}

	//获取文件的类型
	//$type  为array类型
	function get_file_type($type){
		global $_LANG;
		$file_type=strtolower(strrchr($this->_fileAtt['name'], "."));
		if(empty($type)){//格式不限制
			$this->_FileType=$file_type;
			return true;
		}elseif(in_array(strtolower($file_type),$type)||(!in_array(strtolower($file_type),$type)&&in_array('ban',$type))){//规定制定格式 1 在指定格式内，2 不在指定格式内
			$this->_FileType=$file_type;
			return true;
		}else{//格式不合法
			return false;
		}
	}
	
	//文件属性
	function UpFileAttribute_array($FormName,$key){
		$this->_fileAtt['tmp_name']=$_FILES[$FormName]['tmp_name'][$key];
		$this->_fileAtt['name']=$fileName=$_FILES[$FormName]['name'][$key];
		$this->_fileAtt['type']=$_FILES[$FormName]['type'][$key];
		$this->_fileAtt['size']=$_FILES[$FormName]['size'][$key];
	}	

	//文件属性
	function UpFileAttribute($FormName){
		$this->_fileAtt=array();
		$this->_fileAtt['tmp_name']=$_FILES[$FormName]['tmp_name'];
		$this->_fileAtt['name']=$fileName=$_FILES[$FormName]['name'];
		$this->_fileAtt['type']=$_FILES[$FormName]['type'];
		$this->_fileAtt['size']=$_FILES[$FormName]['size'];
	}

//	//上传文件的文件名加工函数
//	function name_method($arr_arug){
//		if(!is_array($arr_arug)){return false;}
//		foreach($arr_arug as $key =>$val){
//			$name_str.='_'.$val;
//		}
//		if(substr($name_str,0,1)=='_')
//		$name_str=substr($name_str,1);
//		return $name_str;
//	}

	//得到文件名
	function FileName($name=''){
		if(empty($name)) return $this->_fileAtt["name"];
		return $name.$this->_FileType;
	}


	//检查文件的大小
	function FileMaxSize($MaxKb){
		global $_LANG;
		if($MaxKb==0){$MaxKb=$this->_MaxSize;}
		if($this->_fileAtt['size']<=$MaxKb*1024){return true;}
		return false;
	}

	//检查图片文件的类型
	function UpFileType($FileType){
		global $_LANG;
		if(empty($FileType))return true;
		if($FileType!=1 AND is_array($FileType)){
			if(in_array($this->_fileAtt['type'],$FileType))return true;
		}
		$allType=array('image/jpeg','image/gif','image/png','image/pjpeg','image/x-png');
		if(in_array($this->_fileAtt['type'],$allType))return true;
		$this->Errors($_LANG["l_fileFormatError"].'！');
		return false;
	}
//
//	//用于上传图片的功能
//	function switchType($type){
//		global $_LANG;
//		switch($type){
//			case 'image/jpeg':  return '.jpg';break;
//			case 'image/gif':   return '.gif';break;
//			case 'image/png':   return '.png';break;
//			case 'image/pjpeg': return '.jpg';break;
//		}
//		$this->Errors($_LANG["l_typeconvert_failing"].'！');
//		exit;
//	}

	function Errors($error=''){
		if($this->_errors==false)return false;
		die($error);
		return false;
	}
}
?>