<?php
class DiymenAction extends UserAction{
	public function index(){
		$data=M('Diymen_set')->where(array('token'=>$_SESSION['token']))->find();
		if(IS_POST){
			$_POST['token']=$_SESSION['token'];			
			if($data==false){				
				$this->all_insert('Diymen_set');
			}else{
				$_POST['id']=$data['id'];
				$this->all_save('Diymen_set');
			}
		}else{
			$this->assign('diymen',$data);
			$class=M('Diymen_class')->where(array('token'=>session('token'),'pid'=>0))->order('sort desc')->select();//dump($class);
			foreach($class as $key=>$vo){
				$c=M('Diymen_class')->where(array('token'=>session('token'),'pid'=>$vo['id']))->order('sort desc')->select();
				$class[$key]['class']=$c;
			}
			$this->assign('class',$class);
			$this->display();
		}
	}
	
	public function class_add(){
		if(IS_POST){
			$this->all_insert('Diymen_class','/class_add');
		}else{
			$class=M('Diymen_class')->where(array('token'=>session('token'),'pid'=>0))->order('sort desc')->select();
			$this->assign('class',$class);
			$this->display();
		}
	}
	
	public function class_del(){		
		$class=M('Diymen_class')->where(array('token'=>session('token'),'pid'=>$this->_get('id')))->order('sort desc')->find();
		if($class==false){
			$back=M('Diymen_class')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->delete();
			if($back==true){
				$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}
		}else{
			$this->error('请删除该分类下的子分类');
		}
	}
	
	public function class_edit(){
		if(IS_POST){
			$_POST['id']=$this->_get('id');
			$this->all_save('Diymen_class');
		}else{
			$data=M('Diymen_class')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->find();
			if($data==false){
				$this->error('您所操作的数据对象不存在！');
			}else{
				$class=M('Diymen_class')->where(array('token'=>session('token'),'pid'=>0))->order('sort desc')->select();//dump($class);
				$this->assign('class',$class);
				$this->assign('show',$data);
			}
			$this->display();
		}
	}
	
	public function class_send(){
		if(IS_GET){
			$api = M('Diymen_set')->where(array('token'=>session('token')))->find();
			$url_get = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$api['appid'].'&secret='.$api['appsecret'];
			$result = file_get_contents($url_get);
			if (empty($result)){
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt ($ch, CURLOPT_URL, $url_get);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				$result = curl_exec($ch);
				curl_close($ch);
			}
			$json = json_decode($result);
			if ($api['appid'] == false || $api['appsecret'] == false ){
				$this->error('必须先填写【AppId】和【 AppSecret】');
				exit;
			}
			$data = '{"button":[';
			$class = M('Diymen_class')->where(array('token'=>session('token'),'pid'=>0))->limit(3)->order('sort desc')->select();
			$i = 1;
			foreach($class as $key=>$vo){
				//主菜单
				$data .= '{"name":"'.$vo['title'].'",';
				$c = M('Diymen_class')->where(array('token'=>session('token'),'pid'=>$vo['id']))->limit(5)->order('sort desc')->select();
				$count = M('Diymen_class')->where(array('token'=>session('token'),'pid'=>$vo['id']))->limit(5)->order('sort desc')->count();
				//子菜单
				if($c != false){
					$data .= '"sub_button":[';
				}else{
					$data .= '"type":"click","key":"'.$vo['title'].'","sub_button":[]';
				}
				$i = 1;
				foreach($c as $voo){
					if($i == $count){
						$data .= '{"type":"click","name":"'.$voo['title'].'","key":"'.$voo['keyword'].'"}';					
					}else{
						$data .= '{"type":"click","name":"'.$voo['title'].'","key":"'.$voo['keyword'].'"},';
					}
					$i++;
				}
				if($c!=false){
					$data .= ']';
				}
				if($key < 2){
					$data .= '},';
				}elseif($key == 2){
					$data .= '}';
				}
			}	
			$data .= ']}';
			file_get_contents('https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$json->access_token);
			$url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$json->access_token;
			if($this->api_notice_increment($url,$data)==false){
				$this->error('操作失败');
			}else{
				$this->success('操作成功');
			}
			exit;
		}else{
			$this->error('非法操作');
		}
	}
	
	function api_notice_increment($url, $data){
		$ch = curl_init(); 
		$header = "Accept-Charset: utf-8"; 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		$tmpInfo = curl_exec($ch);
		if (curl_errno($ch)) {  
			return false;
		}else{
			return true;
		}
	}
}

?>