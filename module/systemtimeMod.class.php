<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		zhandong<chenzhandong@100msh.com>
 * @date		2014-10-30
 * @desc		定时任务执行
 */
class systemtimeMod extends commonMod{
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * @author	zhandong
	 * @date	2014-10-30
	 * @desc	下载网易新闻列表
	 * */
	function nav_news_list(){
		$erp_model=APP::db()->ERP;
		$url='http://c.3g.163.com/baimish/nc/article/';
		$limit=1;
		
		/*** 初始化 curl start ***/
		$ch=curl_init();
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,300);
		curl_setopt($ch,CURLOPT_TIMEOUT,300);
		/*** 初始化 curl end ***/
		set_time_limit(200);
		ini_set("memory_limit","200M");
		
		$articles=array();
		/*** 栏目新闻 start ***/
		//获取所有状态为1的栏目
		if($types=$erp_model->table(T_ARTICLE_TYPES)->field('at_id,atCode')->where("atStatus=1 AND atCode<>'' AND atCode<>'local'")->select()){
			/*** 获取所有栏目的文章列表 start，每个栏目支取固定篇数的文章，不抓取内容 ***/
			foreach($types as $type){			    
				if($content=$this->curl_get($ch,$url.'list/'.$type['atCode']."/0-{$limit}.html")){
					$content=json_decode($content,true);
					if(is_array($content)){
						$data=$content[$type['atCode']];
						foreach($data as $dt){
							$skType=isset($dt['skipType'])?$dt['skipType']:'';
							if($skType<>'photoset'){
								$articles[$dt['docid']]=array(
									'at_id'=>$type['at_id'],
									'sc_id'=>0,
									'aTitle'=>addslashes($dt['title']),
									'aSubTitle'=>addslashes((isset($dt['subtitle'])&&!empty($dt['subtitle']))?$dt['subtitle']:''),
									'aDigest'=>addslashes($dt['digest']),
									'aTags'=>isset($dt['TAG'])?$dt['TAG']:'',
									'aID'=>$dt['docid'],
									'aType'=>$skType,
									'aSort'=>isset($dt['order'])?$dt['order']:0,
									'aPublishTime'=>strtotime($dt['ptime']),
									'aReplyCount'=>isset($dt['replyCount'])&&!empty($dt['replyCount'])?$dt['replyCount']:0,
								);
								$imgsrcs=array();
								if(isset($dt['imgsrc'])&&$dt['imgsrc']){
									$imgsrcs[]=$dt['imgsrc'];
								}
								if(isset($dt['imgextra'])&&is_array($dt['imgextra'])){
									foreach($dt['imgextra'] as $img){
										$imgsrcs[]=$img['imgsrc'];
									}
								}
								$articles[$dt['docid']]['aImgs']='';
								if(count($imgsrcs)){
									$articles[$dt['docid']]['aImgs']=addslashes(serialize($imgsrcs));
								}
							}
						}
					}
				}
			}
			unset($types);
			if(isset($content))unset($content);
			if(isset($dt))unset($dt);
			if(isset($data))unset($data);
			if(isset($type))unset($type);
			/*** 获取所有栏目的文章列表 end ***/
		}
		/*** 栏目新闻 end ***/
		
		/*** 本地新闻 start ***/
		if($result=$erp_model->table(T_ARTICLE_CITIES)->field('sc_id,acName')->where("acStatus=1")->select()){
			$cities=array();
			foreach($result as $rs){
				$cities[$rs['sc_id']]=$rs['acName'];
			}
			unset($result);
			foreach($cities as $scid=>$city){
				$cname=urlencode(base64_encode($city));
				if($content=$this->curl_get($ch,$url."local/{$cname}/0-{$limit}.html")){
					$content=json_decode($content,true);
					if(is_array($content)){
						$data=isset($content[$city])&&!empty($content[$city])?$content[$city]:'';
						if($data){
							foreach($data as $dt){
								$skType=isset($dt['skipType'])?$dt['skipType']:'';
								if($skType<>'photoset'){
									$articles[$dt['docid']]=array(
										'at_id'=>0,
										'sc_id'=>$scid,
										'aTitle'=>addslashes($dt['title']),
										'aSubTitle'=>addslashes((isset($dt['subtitle'])&&!empty($dt['subtitle']))?$dt['subtitle']:''),
										'aDigest'=>isset($dt['digest'])&&!empty($dt['digest'])?addslashes($dt['digest']):'',
										'aTags'=>isset($dt['TAG'])?$dt['TAG']:'',
										'aID'=>$dt['docid'],
										'aType'=>$skType,
										'aSort'=>isset($dt['order'])?$dt['order']:0,
										'aPublishTime'=>strtotime($dt['ptime']),
										'aReplyCount'=>isset($dt['replyCount'])&!empty($dt['replyCount'])?$dt['replyCount']:0,
									);
									$imgsrcs=array();
									if(isset($dt['imgsrc'])&&$dt['imgsrc']){
										$imgsrcs[]=$dt['imgsrc'];
									}
									if(isset($dt['imgextra'])&&is_array($dt['imgextra'])){
										foreach($dt['imgextra'] as $img){
											$imgsrcs[]=$img['imgsrc'];
										}
									}
									$articles[$dt['docid']]['aImgs']='';
									if(count($imgsrcs)){
										$articles[$dt['docid']]['aImgs']=addslashes(serialize($imgsrcs));
									}
								}
							}
						}
					}
				}
			}
			unset($cities);
			if(isset($content))unset($content);
			if(isset($dt))unset($dt);
			if(isset($data))unset($data);
		}
		/*** 本地新闻 end ***/
		if(count($articles)){
			/*** 获取列表中已经下载过的文章 start ***/	
		    $articles_arr = $articles;
		    $aTitle_str = '';
		    foreach ($articles_arr as $key=>$value){
		        $aTitle_str = !empty($aTitle_str) ? $aTitle_str.",'".$value['aTitle']."'" : "'".$value['aTitle']."'";
		    }	    		    
			$aIDs=array_keys($articles);			
			$arts=array();
			if($result=$erp_model->table(T_ARTICLES)->field('a_id,aID,at_id,sc_id')->where("aID IN('".implode("','",$aIDs)."') OR aTitle IN ($aTitle_str)")->select()){
				foreach($result as $rs){
					$arts[$rs['aID']]=array(
						'a_id'=>$rs['a_id'],
						'at_id'=>$rs['at_id'],
						'sc_id'=>$rs['sc_id']
					);
				}
			}
			unset($result);
			
			/*** 获取列表中已经下载过的文章 end ***/
			
			/*** 下载新文章 start ***/
			foreach($aIDs as $aID){			  
				//如果未下载，则下载
				if(isset($arts[$aID])){
					$newdata=array();
					if($articles[$aID]['at_id']&&!$arts[$aID]['at_id']){
						$newdata['at_id']=$articles[$aID]['at_id'];
					}elseif($articles[$aID]['sc_id']&&!$arts[$aID]['sc_id']){
						$newdata['sc_id']=$articles[$aID]['sc_id'];
					}
					if(count($newdata)){
						$erp_model->table(T_ARTICLES)->data($newdata)->where("aID='{$aID}'")->update();
					}
					unset($articles[$aID]);
				}	
			}

			unset($aids);
			/*** 下载新文章 end ***/
			//对新下载的文章进行处理，并批量保存
			if(count($articles)){
				/*** 批量保存 start ***/
				$fields=implode(',',array_keys(current($articles)));
				$fields.=',aIsDown,aMSHReadCount,aMSHSort,aStatus,aAddTime';
				$data=array();
				$now=time();
				$i=0;
				$rows=0;
				foreach($articles as $key=>$article){
					$i++;
					$article['aIsDown']=3;//没有新闻内容，没有下载附件（图片）
					$article['aMSHReadCount']=$article['aMSHSort']=$article['aStatus']=0;
					$article['aAddTime']=$now;
					$data[]=$article;
					unset($articles[$key]);
					if($i>499){
						if($fetchRow=$erp_model->table(T_ARTICLES)->insertall($fields,$data)){
							$rows+=$fetchRow;
						}else{
							echo mysql_error().'=1<br>';
						}
						$i=0;
						$data=array();
					}
				}
				if(count($data)){
					if($fetchRow=$erp_model->table(T_ARTICLES)->insertall($fields,$data)){
						$rows+=$fetchRow;
					}else{
						echo mysql_error().'=2<br>';
					}
				}
				/*** 批量保存 end ***/
				echo "已下载 {$rows} 篇文章！";
			}else{
				echo '没有最新文章！';
			}
		}else{
			echo '更新失败！';
		}
		
		curl_close($ch);
	}
	
	/**
	 * @author	zhandong
	 * @date	2014-10-30
	 * @desc	下载网易新闻内容
	 * */
	function nav_news_content(){
		$erp_model=APP::db()->ERP;
		$url='http://c.3g.163.com/baimish/nc/article/';
		$limit=1;
		
		/*** 初始化 curl start ***/
		$ch=curl_init();
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,300);
		curl_setopt($ch,CURLOPT_TIMEOUT,300);
		/*** 初始化 curl end ***/
		set_time_limit(200);
		ini_set("memory_limit","300M");
		
		if($atcs=$erp_model->table(T_ARTICLES)->field('a_id,aID')->where("aIsDown=3 AND aStatus<>2")->limit('0,'.$limit)->select()){
			/*** 下载文章 start ***/
			$details=$attachments=array();
			foreach($atcs as $atc){
				$content=$this->curl_get($ch,$url.$atc['aID'].'/full.html');
				if($content){
					$content=json_decode($content,true);
					if(is_array($content)){
						$data=$content[$atc['aID']];
						//aReplyCount
						//$articles[$atc['a_id']]=$data['replyCount'];
						$details[$atc['a_id']]=array(
							'a_id'=>$atc['a_id'],
							'aContent'=>isset($data['body'])?addslashes($data['body']):'',
						);
						$attachment=array(
							'a_id'=>$atc['a_id'],
							'aLinks'=>'',
							'aContentPhotos'=>'',
							'aContentVideos'=>''
						);
						if(isset($data['link'])&&$data['link']){$attachment['aLinks']=addslashes(serialize($data['link']));}
						if(isset($data['img'])&&$data['img']){$attachment['aContentPhotos']=addslashes(serialize($data['img']));}
						if(isset($data['video'])&&$data['video']){$attachment['aContentVideos']=addslashes(serialize($data['video']));}
						if($attachment['aContentPhotos']||$attachment['aContentVideos']){
							$attachments[]=$attachment;
						}
					}
				}
			}
			if(isset($_GET['test'])){
				var_dump($details);
				exit;
			}			
			unset($content);
			unset($atcs);
			unset($atc);
			if(isset($data))unset($data);
			/*** 下载文章 end ***/
			
			//对新下载的文章数据进行处理，并批量保存
			if(count($details)){
				/*** 批量保存文章内容 start ***/
				$fields=implode(',',array_keys(current($details)));
				$data=array();
				$aids='';
				$i=0;
				$rows=0;
				foreach($details as $aid=>$detail){
					$i++;
					$data[]=$detail;
					$aids.=$aid.',';
					if($i>$limit){
						if($fetchRow=$erp_model->table(T_ARTICLE_CONTENT)->insertall($fields,$data)){
							$aids=substr($aids,0,-1);
							$erp_model->query("UPDATE ".$erp_model->pre.T_ARTICLES." SET aIsDown=2,aStatus=1 WHERE a_id IN({$aids})");
							$rows+=$fetchRow;
						}else{
							echo mysql_error().'=1<br>';
						}
						$i=0;
						$data=array();
						$aids='';
					}
				}
				unset($details);
				if($aids){
					if($fetchRow=$erp_model->table(T_ARTICLE_CONTENT)->insertall($fields,$data)){
						$aids=substr($aids,0,-1);
						$erp_model->query("UPDATE ".$erp_model->pre.T_ARTICLES." SET aIsDown=2,aStatus=1 WHERE a_id IN({$aids})");
						$rows+=$fetchRow;
					}else{
						echo mysql_error().'=2<br>';
					}
				}
				/*** 批量保存文章内容 end ***/
				echo "已下载 {$rows} 篇文章内容！";
				
				if(count($attachments)){
					/*** 批量保存附件数据 start ***/
					$fields=implode(',',array_keys(current($attachments)));
					$fields.=",aLocalImgs,aLocalContentPhotos";
					$data=array();
					$i=0;
					foreach($attachments as $attachment){
						$i++;
						$attachment['aLocalImgs']=$attachment['aLocalContentPhotos']='';
						$data[]=$attachment;
						if($i>$limit){
							if($fetchRow=$erp_model->table(T_ARTICLE_ATTACHEMENTS)->insertall($fields,$data)){
							}else{
								echo mysql_error().'=3<br>';
							}
							$i=0;
							$data=array();
						}
					}
					if(count($data)){
						if($fetchRow=$erp_model->table(T_ARTICLE_ATTACHEMENTS)->insertall($fields,$data)){
						}else{
							echo mysql_error().'=4<br>';
						}
					}
					/*** 批量保存附件数据 end ***/
				}
			}else{
				echo '下载文章内容失败！';
			}
		}else{
			echo '没有要下载文章内容！';
		}
		
		curl_close($ch);
	}
	
	
	/**
	 * @author	lidc
	 * @date	2015-2-11
	 * @desc	下载唯彩会列表
	 * */
	function weicaihui_list(){
	    $erp_model=APP::db()->ERP;
	    $rss1 = new Rss();
	    $rss1->load('http://vipc.cn/feed');
	    $items = $rss1->getItems();
	     		var_dump($items);exit;
	    $types = $erp_model->table(T_ARTICLE_TYPES)->field('at_id,atCode')->where("atStatus=1 AND atCode='weicaihui'")->find();
	    $at_id = 0;
	    if($types){
	        $at_id = $types['at_id'];
	    }
	    $data = array();
	    $datac = array();
	    foreach ($items as $key=>$value){
	        $aDigest = substr(trim(strip_tags($value['description'])), 0, 80);	   //描述
	        $guid = substr($value['guid'], strrpos($value['guid'], '/'));     //唯一标识guid 转换 aID
	        $aID = substr($guid, 1, strpos($guid, '?')-1);
	        $first_p = substr($value['description'], 0, strpos($value['description'], '</p>')+4);     //第一张图的获取，但需由第一个<p>中获得。
	        preg_match ("<img.*src=[\"](.*?)[\"].*?>",$first_p,$match);               //获取图片正则表达式
	        $aImgs = '';
	        if(is_array($match) && $match){
	            $aImgs = addslashes(serialize($match[1]));        //获取图片路径
	        }
	        $pubDate = strtotime(substr($value['pubDate'], 0, strrpos($value['pubDate'], 'GMT')));    //时间转换
	    
	        $data[$key] = array(
	            'at_id'=>$at_id,
	            'sc_id'=>0,
	            'aTitle'=>in($value['title']),
	            'aDigest'=>$aDigest,
	            'aImgs' => $aImgs,
	            'author'=>in(addslashes($value['author'])),
	            'aID'=>$aID,
	            'link'=>addslashes($value['link']),
	            'aPublishTime'=>$pubDate,
	            'aStatus'=>1
	        );
	        $datac[$key] = array(
	            'aID'=>$aID,
	            'aContent'=>in($value['description']),
	        );
	    
	        $wcount = $erp_model->table(T_ARTICLES)->where("aID='".$aID."'")->count();   //唯一标识符不能重复，重复则只做相应的信息修改
	        if($wcount){
	            $wr = $erp_model->table(T_ARTICLES)->data($data[$key])->where("aID='".$aID."'")->update();
	            if($wr){
	                unset($data[$key]);
	            }else{
	                echo "因aID冲突，修改失败！";
	                exit;
	            }
	        }
	    }
	    unset($items);    
	    $fields=implode(',',array_keys(current($data)));
	    $fetchRow = $erp_model->table(T_ARTICLES)->insertall($fields,$data);
	    unset($data);
	    $fetchRow = 1;
	    if($fetchRow){
	        $atlist = $erp_model->table(T_ARTICLES)->field('a_id,aID')->where("at_id=".$at_id."")->select();	        
	        if ($atlist){
	            $data = array();
	            foreach ($datac as $k=>$v){
	                foreach ($atlist as $key=>$val){
	                    if($v['aID']==$val['aID']){
	                        $data[$k] = array(
	                            'a_id'=> $val['a_id'],
	                            'aContent' => $v['aContent'],
	                        );
	        
	                        $count = $erp_model->table(T_ARTICLE_CONTENT)->where("a_id=".$val['a_id']."")->count();
	                        if($count){
	                            $wrc = $erp_model->table(T_ARTICLE_CONTENT)->data($data[$k])->where("a_id=".$val['a_id']."")->update();
	                            if($wrc){
	                                unset($data[$k]);
	                            }else{
	                                echo mysql_error();
	                                exit;
	                            }
	                        }
	                         
	                    }
	                }
	            }
	            if($data && is_array($data)){
	                $fieldsc = implode(',',array_keys(current($data)));
	                $erp_model->table(T_ARTICLE_CONTENT)->insertall($fieldsc,$data);
	            }
	        }
	    }else{
	        echo mysql_error();
	    }
	}
	
	/**
	 * 抓取网页内容
	 * */
	private function curl_get($ch=null,$url=''){
		if($ch&&$url&&is_resource($ch)&&$url){
			curl_setopt($ch,CURLOPT_URL,$url);
			return curl_exec($ch);
		}
		return false;
	}
}
?>