<?php
class IndexAction extends UserAction{
	//公众帐号列表
	public function index(){
		$where['uid']=session('uid');
		$group=D('User_group')->select();
		foreach($group as $key=>$val){
			$groups[$val['id']]['did']=$val['diynum'];
			$groups[$val['id']]['cid']=$val['connectnum'];
		}
		unset($group);
		$db=M('Wxuser');
		$count=$db->where($where)->count();
		$page=new Page($count,25);
		$info=$db->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('info',$info);
		$this->assign('group',$groups);
		$this->assign('page',$page->show());
		$this->display();
	}
	//添加公众帐号
	public function add(){
		$email = str_replace("http://","",C('site_url'));
		$email_l = strpos($email,".") + 1;
		$email = substr($email,$email_l);
		$randLength=6;
		$chars='abcdefghijklmnopqrstuvwxyz';
		$len=strlen($chars);
		$randStr='';
		for ($i=0;$i<$randLength;$i++){
			$randStr.=$chars[rand(0,$len-1)];
		}
		$tokenvalue=$randStr.time();
		$this->assign('tokenvalue',$tokenvalue);
		$this->assign('email',time().'@'.$email);
		//地理信息
		if (C('baidu_map_api')){
			//$locationInfo=json_decode(file_get_contents('http://api.map.baidu.com/location/ip?ip='.$_SERVER['REMOTE_ADDR'].'&coor=bd09ll&ak='.C('baidu_map_api')),1);
			///$this->assign('province',$locationInfo['content']['address_detail']['province']);
			//$this->assign('city',$locationInfo['content']['address_detail']['city']);
			//var_export($locationInfo);
		}
	
		
		$this->display();
	}
	public function edit(){
		$id=$this->_get('id','intval');
		$where['uid']=session('uid');
		$res=M('Wxuser')->where($where)->find($id);
		$this->assign('info',$res);
		$this->display();
	}
	
	public function del(){
		$where['id']=$this->_get('id','intval');
		$where['uid']=session('uid');
		if(D('Wxuser')->where($where)->delete()){
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
	}
	
	public function upsave(){
		$id = $this->_get('id','intval');
		$where['uid'] = session('uid');
		$res = M('Wxuser')->where($where)->find($id);
		$_POST['wxid'] = $_POST['wxid'] != $res['wxid'] ? $res['wxid'] : $_POST['wxid'];
		$this->all_save('Wxuser');
	}
	
	public function insert(){
		$data=M('User_group')->field('wechat_card_num')->where(array('id'=>session('gid')))->find();
		$users=M('Users')->field('wechat_card_num')->where(array('id'=>session('uid')))->find();
		if($users['wechat_card_num']<$data['wechat_card_num']){
			
		}else{
			$this->error('您的VIP等级所能创建的公众号数量已经到达上线，请购买后再创建',U('User/Index/index'));exit();
		}
		//$this->all_insert('Wxuser');
		//
		$db=D('Wxuser');
		if($db->create()===false){
			$this->error($db->getError());
		}else{
			$id=$db->add();
			if($id){
				M('Users')->field('wechat_card_num')->where(array('id'=>session('uid')))->setInc('wechat_card_num');
				$this->addfc();
				//
				$this->success('操作成功',U('Index/index'));
			}else{
				$this->error('操作失败',U('Index/index'));
			}
		}
		
	}
	
	//功能
	public function autos(){
		$this->display();
	}
	
	public function addfc(){
		$token_open=M('Token_open');
		$open['uid']=session('uid');
		$open['token']=$_POST['token'];
		$gid=session('gid');
		$fun=M('Function')->field('funname,gid,isserve')->where('`gid` <= '.$gid)->select();
		foreach($fun as $key=>$vo){
			$queryname.=$vo['funname'].',';
		}
		$open['queryname']=rtrim($queryname,',');
		$token_open->data($open)->add();
	}
	
	public function usersave(){
		$pwd=$this->_post('password');
		if($pwd!=false){
			$data['password']=md5($pwd);
			$data['id']=$_SESSION['uid'];
			if(M('Users')->save($data)){
				$this->success('密码修改成功！',U('Index/index'));
			}else{
				$this->error('密码修改失败！',U('Index/index'));
			}
		}else{
			$this->error('密码不能为空!',U('Index/useredit'));
		}
	}
	
	//余额续费
	public function pay(){
		$userinfo = M('Users')->where(array('id'=>session('uid')))->find();
		$group=M('User_group')->field('id,name,price')->where('price > 0')->select();
		$user=M('User_group')->field('price')->where(array('id'=>session('gid')))->find();
		$viptime = $userinfo['viptime'];
		$money = $userinfo['money'];
		$gid = $userinfo['gid'];
		$this->assign('group',$group);
		$this->assign('user',$user);
		$this->assign('viptime',$viptime);
		$this->assign('money',$money);
		$this->assign('gid',$gid);
		$this->display();
	}
	public function dopay(){
		$userinfo = M('Users')->where(array('id'=>session('uid')))->find();
		$money = $userinfo['money'];
		$viptime = $userinfo['viptime'];
		$price = intval($_POST['price']);
		$num = intval($_POST['num']);
		$vip = strtotime("+".$num." month", $viptime);
		$mback = M('Users')->where(array('id'=>session('uid')))->setDec('money',$price);
		$vback = M('Users')->where(array('id'=>session('uid')))->setField('viptime',$vip);
		if ($money < $price){
			$this->error("余额不足，请充值！", U('Alipay/index'));
		}else{
			if ($mback != false && $vback != false){
				$this->success('续费成功！', U('Index/index'));
			}else{
				$this->error('操作失败！请联系管理员', U('Index/index'));
			}
		}
	}
	
	public function pay_history(){
		$this->display();
	}
	
}
?>