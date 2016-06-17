<?php
//分页类，提供四种显示样式，和ajax支持
class Page 
 {		
	public $pageName="page";//page标签，用来控制url页。比如说xxx.php?page=2中的page
	public $pageSeparator='-';//页面参数分隔符
	public $pageSuffix='';//页面后缀
	
	public $nextPage='下一页';//下一页
	public $prePage='上一页';//上一页
	public $firstPage='首页';//首页
	public $lastPage='尾页';//尾页
	public $preBar='<<';//上一分页条
	public $nextBar='>>';//下一分页条
	public $isAjax=false;//是否支持AJAX分页模式 
  
    public $pageBarNum=10;//控制记录条的个数。
    public $totalPage=0;//总页数
    public $ajaxActionName='';//AJAX动作名
    public $nowIndex=0;//当前页
    public $url="";//url地址头
	public $requestUri="";
	public $total=0;//总条数

/**
 *constructor构造函数
 *@param 
 *$array['pageName'],
 *$array['ajax']
 */
  public function __construct($array=array())
  {
		if(isset($array['pageName']))
		 	$this->set('pageName',$array['pageName']);//设置pagename
					
		if(isset($array['ajax'])&&!empty($array['ajax']))
	  	  $this->openAjax($array['ajax']);//打开AJAX模式
  }
  
 //处理分页参数
  public function doPage($url,$total,$perPage,$pageBarNum)
  {		
  	    $this->total=$total;
		$this->totalPage=ceil($total/$perPage);	//计算总页数	
		$this->pageBarNum=$pageBarNum;
		//获取到当前页,同时避免两次执行getCurPage()方法
		$this->nowIndex=$this->nowIndex==0?$this->getCurPage($url):$this->nowIndex;
  }
 
 /**
 * 设定类中指定变量名的值，如果改变量不属于这个类，将返回false
 * @param string $var
 * @param string $value
 */
   public function set($var,$value)
  {
  	 if(in_array($var,get_object_vars($this)))
     {
	   $this->$var=$value;
	   return true;
	 }
     else 
   	 {
	   return false;
	 }
  }
  
  /**
  * 打开AJAX模式
  *
  * @param string $action 默认ajax触发的动作。
  */
  public function openAjax($action)
 {
  	$this->isAjax=true;
  	$this->ajaxActionName=$action;
 }
 
/**
  * 获取显示"下一页"的代码
  * 
  * @param string $style
  * @return string
  */
  public function nextPage($style='nextPage')
 {
	if($this->nowIndex<$this->totalPage)
	{
		return $this->_getLink($this->_getUrl($this->nowIndex+1),$this->nextPage,$style);
	}
    return '';
}
 
/**
 * 获取显示“上一页”的代码
 *
 * @param string $style
 * @return string
  */
  public function prePage($style='prePage')
 {
 	 if($this->nowIndex>1)
 	 {
  		 return $this->_getLink($this->_getUrl($this->nowIndex-1),$this->prePage,$style);
 	 }
	 return '';

 }
 
 /**
  * 获取显示“首页”的代码
  *
  * @return string
  */
  public function firstPage($style='firstPage')
 {
	  if($this->nowIndex==1)
	  {
	  	  return '';
	  }
	  return $this->_getLink($this->_getUrl(1),$this->firstPage,$style);
 }
 
 /**
  * 获取显示“尾页”的代码
  *
  * @return string
  */
  public function lastPage($style='lastPage')
 {
	  if($this->nowIndex==$this->totalPage)
	  {
		  return '';
	  }
	  return $this->_getLink($this->_getUrl($this->totalPage),$this->lastPage,$style);
 }
 
  public function nowBar($style='pages',$nowIndex_style='current'){
	if($this->totalPage<2){return '';}
		$page_count=$this->totalPage;
		$page=$this->nowIndex;
		//计算要显示的首页/末页页数
		$startPageNum=1;
		$endPageNum;
		$show_count = 5;
		if($page_count <= $show_count + 1) {//总页数小于等于要显示的页数
			$startPageNum = 2;
			$endPageNum = $page_count - 1;
		}else{
			$j = floor($show_count/2);
			if($page - $j <= 1) {
				$startPageNum = 2;
				$endPageNum = $startPageNum + $show_count - 1;
				if($endPageNum >= $page_count){$endPageNum = $page_count - 1;}
			}else if($page - $j > 1) {
				if($page + $j < $page_count - 1) {
					$startPageNum = $page - $j;
					$endPageNum = $startPageNum + $show_count - 1;
				} else {
					$endPageNum = $page_count - 1;
					$startPageNum = $endPageNum - $show_count + 1;
				}
			}
			//判断是否有前/后省略
			$pageinfoP = false;
			$pageinfoN = false;
			if($startPageNum > 2) {
				$pageinfoP = true;
			}
			if($endPageNum < ($page_count - 1)) {
				$pageinfoN = true;
			}
		}
		$i=1;$return='';
		if($this->nowIndex==$i){
		    $return=$this->_getText('<span class="'.$nowIndex_style.'">'.$i.'</span>');
		}else{
			$return=$this->_getText($this->_getLink($this->_getUrl($i),$i,$style));
		}
		if(isset($pageinfoP) && $pageinfoP){
			$return.="<strong>...</strong>";
		}
		for($i=$startPageNum;$i<=$endPageNum;$i++){
			if($i!=$this->nowIndex)
			$return.=$this->_getText($this->_getLink($this->_getUrl($i),$i,$style));
			else
			$return.=$this->_getText('<span class="'.$nowIndex_style.'">'.$i.'</span>');
		}
		if(isset($pageinfoN) && $pageinfoN){
			$return.="<strong>...</strong>";
		}
		$i=$page_count;
		if($this->nowIndex==$i){
		    $return.=$this->_getText('<span class="'.$nowIndex_style.'">'.$i.'</span>');
		}else{
			$return.=$this->_getText($this->_getLink($this->_getUrl($i),$i,$style));
		}
		return $return;
 }
 /**
  * @desc 获取显示跳转按钮的代码
  * @return string
 */
  public function select()
 {
	if($this->totalPage>1)
	{
	$return='<select onChange="window.location=this.options[this.selectedIndex].value">';
	for($i=1;$i<=$this->totalPage;$i++)
	{
		if($i==$this->nowIndex)
		{
			$return.='<option value="'.$this->_getUrl($i).'" selected>'.$i.'</option>';
		}
		else
		{
			$return.='<option value="'.$this->_getUrl($i).'">'.$i.'</option>';
		}
	}
	$return.='</select>';
	return $return;
	}
 }
 
  /**
  * @desc 获取显示跳转页面代码
  * @return string
 */
 public function jump(){
 	if($this->totalPage<6){return '';}
 	$return= "<span class=\"total\">共{$this->totalPage}页</span><span class=\"jump\"><input id=\"to_pagenum\" class=\"text\" type=\"text\" value=\"{$this->nowIndex}\" onkeydown=\"if(event.keyCode == 13 && $(this).closest('.popup').length == 0) location.hash = $(this).next('a').eq(0).attr('href');\" onkeyup=\"value=(value >= $this->totalPage) ? $this->totalPage : value.replace(/[^\d]/g,'');var goto = $(this).next('a').attr('href').split('?');$(this).next('a').attr('href', goto[0].replace(/-page-\d*/, '') + '-page-' + value + '?' + (!goto[1] ? '' : goto[1]));\" />";
	$return .= "<a href='#".$this->_getUrl($this->nowIndex)."' tabIndex='0' onblur='if(/[^0123456789]/g.test(value))value=value.replace(/[^0123456789]/g,'');' onkeyup='if(/[^0123456789]/g.test(value))value=value.replace(/[^0123456789]/g,'');'>GO</a></span>";
	return $return;
 }
 
 /**
  * @desc 获取数据总条数
  * @return string
 **/  
 public function totalRecord(){
 	$return="<span class=\"total\">共&nbsp;".$this->total."&nbsp;条数据&nbsp;</span>";
 	return $return;
 }
 
/**
  * 控制分页显示风格（你可以增加相应的风格）
 *$url，基准网址，若为空，将会自动获取，不建议设置为空 
 *$total，信息总条数 
 *$perpage，每页显示行数 
 *$pagebarnum，分页栏每页显示的页数 
 *$mode，显示风格，参数可为整数1，2，3，4任意一个 
 */
	public function show($url="",$total=0,$perPage=10,$pageBarNum=10,$mode=1)
	{
 		$this->doPage($url,$total,$perPage,$pageBarNum);

		switch ($mode)
		{
			case '1':
				return $this->prePage().$this->nowBar().$this->nextPage().$this->select();
				break;
			case '2':
				return $this->firstPage().$this->prePage().'[第'.$this->nowIndex.'页]'.$this->nextPage().$this->lastPage().'第'.$this->select().'页';
				break;
			case '3':
				return $this->firstPage().$this->prePage().$this->nextPage().$this->lastPage();
				break;
			case '4':
				return $this->totalRecord().$this->prePage().$this->nowBar().$this->nextPage().$this->jump();
				break;
			default:break;
		}
 }
 /**
  *	获取当前页
  * @param: String $url
  * @return int
 */
 public function getCurPage($url="")
 {
 	$this->_setUrl($url);
 	$nowIndex=1;
	if(isset($_GET[$this->pageName])&&intval($_GET[$this->pageName])>0)
		return intval($_GET[$this->pageName]);
			
	$pattern =str_replace('\{page\}','(\d{1,})',preg_quote($this->url,'/'));
	if(preg_match('/'.$pattern .'/i',$this->requestUri,$matches))
	{
		if(isset($matches[1])&&$matches[1]>0)
			return $matches[1];
	}
 	return $nowIndex;
 }
 
 //文章内容分页
 public function contentPage($content,$separator="[page]",$url="",$pageBarNum=10,$mode=1)
 {
 	$content_array=explode($separator,$content);
	unset($content);
	$total=count($content_array);//计算总行数
	$this->nowIndex=$this->getCurPage($url);
	$index=$this->nowIndex-1;
	$content=isset($content_array[$index])?$content_array[$index]:"";//获取当前内容
	unset($content_array);
	if($total>1)
 		$page=$this->show($url,$total,$perPage=1,$pageBarNum,$mode);//获取分页栏
	else
		$page="";
		
	return array('content'=>$content,'page'=>$page);
 }
 
/*----------------private function (私有方法)-----------------------------------------------------------*/
//获取REQUEST_URI
private function _requestUri()
{
    if (isset($_SERVER['REQUEST_URI']))
    {
        $uri = $_SERVER['REQUEST_URI'];
    }
    else
    {
        if (isset($_SERVER['argv']))
        {
            $uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
        }
        else
        {
            $uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
        }
    }
    return 'http://'.$_SERVER['HTTP_HOST'].$uri;
}

 private function _setUrl($url="")
 {
 	$this->requestUri=$this->_requestUri();
	if(!empty($url)&&preg_match('/\{page\}/',$url))
	{	
		$this->url=$url;
	}
	else
	{
		 $page_str=$this->pageSeparator.$this->pageName.$this->pageSeparator;
		 if(($url=preg_replace('/'.preg_quote($page_str,'/').'(\d{1,})/',$page_str.'{page}',$this->requestUri))==$this->requestUri)
		 {
			if(($url=str_replace($this->pageSuffix,$page_str.'{page}'.$this->pageSuffix,$this->requestUri))==$this->requestUri)
			{
				$uri_arr=explode('?',$this->requestUri,2);
				//处理网址中不带操作方法名
				$str1=rtrim($uri_arr[0],'/');
				if(($pos=@strrpos($url,App::$action))>0)
				{
					$str1=substr($str1,0,$pos);
				}
				$str1=rtrim($str1,'/').'/'.App::$action;
				
				$str2="";
				if(isset($uri_arr[1]))
				{
					$str2='?'.$uri_arr[1];
				}				
				$url=$str1.$page_str.'{page}'.$this->pageSuffix.$str2;
				
			}
		 }
		 $root = preg_replace('/(\/)/', '\/', App::$config['__APP__']);
         $root = preg_replace('/(\.)/', '\.', $root);
		 $url=preg_replace('/'.$root.'/', '', $url);
		 $this->url=preg_replace('/^\//', '', $url);
  	}

 }
 

 
 /**
  * 为指定的页面返回地址值
  *
  * @param int $pageNum
  * @return string $url
  **/
	private function _getUrl($pageNum=1)
	{
	 	$url=$this->url;
		if($pageNum>1)
		{
			$url=str_replace('{page}',$pageNum,$this->url);
		}
		else
		{
			$url=str_replace($this->pageSeparator.'{page}','',$this->url);
			$url=str_replace($this->pageSeparator.$this->pageName,'',$url);
		}
		return $url;
	}
 
 /**
  * 获取分页显示文字，比如说默认情况下_getText('<a href="">1</a>')将返回[<a href="">1</a>]
  *
 * @param String $str
  * @return string $url
 */ 
	function _getText($str)
	{
		return $str;
	}
	
/**
* 获取链接地址
*/
	function _getLink($url,$text,$style=''){
		$style=empty($style)?'':'class="'.$style.'"';
		if($this->isAjax){
		   //如果是使用AJAX模式
		 	return '<a '.$style.' href="javascript:'.$this->ajaxActionName.'(\''.$url.'\')">'.$text.'</a>';
		 }else{
		 	return '<a '.$style.' href="#'.$url.'">'.$text.'</a>';
		 }
	}
}
?>
