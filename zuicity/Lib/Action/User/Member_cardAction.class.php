<?php
class Member_cardAction extends UserAction{
	public function _initialize() {
		parent::_initialize();
		$this->token=session('token');
		$this->assign('token',$this->token);
		//权限
		if ($this->token!=$_GET['token']){
			//exit();
		}
		$this->wxuser_db=M("Wxuser");
		//获取所在组的开卡数量
		$thisWxUser=$this->wxuser_db->where(array('token'=>$this->token))->find();
		$thisUser=M("Users")->where(array('uid'=>$thisWxUser['uid']))->find();
		$thisGroup=M("User_group")->where(array('id'=>$thisUser['gid']))->find();
		$this->wxuser_db->where(array('token'=>$this->token))->save(array('allcardnum'=>$thisGroup['create_card_num']));
		$can_cr_num = $thisWxUser['allcardnum'] - $thisWxUser['yetcardnum'];
		if($can_cr_num > 0){
			$data['cardisok'] = 1;
			$this->wxuser_db->where(array('uid'=>session('uid'),'token'=>session('token')))->save($data);
		}
	}
	//会员卡配置
	public function index(){
		$token_open=M('token_open')->field('queryname')->where(array('token'=>session('token')))->find();
		if(!strpos($token_open['queryname'],'huiyuanka')){
            $this->error('您还未开启该模块的使用权,请到功能模块中添加',U('Function/index',array('token'=>session('token'),'id'=>session('wxid'))));
		}
		$data=M('Member_card_set')->where(array('token'=>$_SESSION['token']))->find();
		if(IS_POST){
			$_POST['token']=$_SESSION['token'];			
			if($data==false){				
				$this->all_insert('Member_card_set');
			}else{
				$_POST['id']=$data['id'];
				$this->all_save('Member_card_set');
			}
		}else{
			if($data==false){
				$data=M('Member_card_set')->where(array('token'=>'zaichangzhouweixinapi'))->find();
			}
			$this->assign('card',$data);
			$this->display();
		}
	}
	public function privilege(){
		$data=M('Member_card_vip')->where(array('token'=>$_SESSION['token']))->order('id desc')->select();
		$this->assign('data_vip',$data);
		$this->display();	
	}	
	public function privilege_add(){
		if(IS_POST){
			$this->all_insert('Member_card_vip','/privilege');
		}else{			
			$this->display();	
		}
		
	}
	public function privilege_edit(){
		if(IS_POST){
			$_POST['id']=$this->_get('id');
			$this->all_save('Member_card_vip','/privilege');
		}else{
			$data=M('Member_card_vip')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->find();
			if($data!=false){
				$this->assign('vip',$data);
				$this->display();
			}else{
				$this->error('非法操作');
			}
			
		}
		
	}
	public function privilege_del(){
			$data=M('Member_card_vip')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->delete();
			if($data==false){
				$this->error('服务器繁忙请稍后再试');
			}else{
				$this->success('操作成功',U('Member_card/privilege',array('id'=>$data_vip['id'])));
			
			}
	}
	//生成会员卡列表
	public function create(){
		$data=M('Member_card_create');
		if (IS_POST){//删除操作
			for ($i=0;$i<50;$i++){
				$data->where(array('id'=>$_POST['id_'.$i]))->delete();
			}
			$this->success('删除成功',U('Member_card/create',array('token'=>session('token'))));
		}else {
			$count      = $data->where(array('token'=>$_SESSION['token']))->count();
			$Page       = new Page($count,15);
			$show       = $Page->show();
			$list = $data->where(array('token'=>$_SESSION['token']))->limit($Page->firstRow.','.$Page->listRows)->select();
			//计算领取张数
			$count    =M('member_card_create')->where(array('token'=>$_SESSION['token']))->count();
			$usecount =M('member_card_create')->where('token="'.$_SESSION['token'] .'" and wecha_id !=""')->count();

			$this->assign("usecount",$usecount);
			$this->assign("count",$count);
			$this->assign("ucount",$count - $usecount);
			$this->assign('page',$show);
			$this->assign('data_vip',$list);
			$this->display();
		}
	}

	public function getuserinfo(){
		$wecha_id = $this->_get("id");

		$uinfo = M('Userinfo')->where(array('wecha_id'=>$wecha_id ,'token'=>$_SESSION['token']))->find();
		$this->assign('list',$uinfo);
	
		$this->display();	
	}

	//创建会员卡
	public function create_add(){ 
		 
		$card=M("Wxuser")->where(array('uid'=>session('uid'),'token'=>$_SESSION['token']))->find();
		if($card['cardisok'] == 2){ //不可以创建
		 	$this->error('您的开卡数量已经用完.请充值或者续费升级.');
		 	return;
		} 


		if(IS_POST){
			$end=(int)$_POST['end'];			
			$stat=(int)$_POST['stat'];
		 
			if($end==false||$stat==false){$this->error('卡号起始值或结束值都不能为空');}

			if($end > 65535 || $stat <= 0){
				$this->error('卡号起始值部能为0或结束值不能超过65535');
				return;
			}
			
			$num=$end - $stat;
			if($num <=0 ){
				$this->error('开始卡号不能大于结束卡号');
				return;
			}
			
			$group_cread_num=M('User_group')->field('create_card_num,create_card_num')->where(array('id'=>session('gid')))->find();
			//echo $num.'-'.$group_cread_num['create_card_num'];exit;
			if(($num>$group_cread_num['create_card_num'])){
			 $this->error('你当前的vip等级只允许创建'.$group_cread_num['create_card_num'].'张卡');exit;
			}	

			//------------------------------------
			// tp_wxuser 
			//------------------------------------

			$markcard = M("Wxuser")->where(array('uid'=>session('uid'),'token'=>session('token')))->find();
			$can_cr_num = $group_cread_num['create_card_num'] - $markcard['yetcardnum']; //还剩下几张

			if( $num > $can_cr_num ){
				$this->error("您本月开卡数量只剩下 " . $can_cr_num ." 张！");exit;
			}

			for($i=1;$i<=$num;$i++){
				 $data['number']=$_POST['title'].($stat+=1);
				 $data['token']=session('token');
				 M('member_card_create')->data($data)->add();
			}		
			 
  			 
  			$back = M('Wxuser')->where(array('uid'=>session('uid'),'token'=>session('token')))->setInc('yetcardnum',$num);
  			 M('Wxuser')->where(array('uid'=>session('uid'),'token'=>session('token')))->setInc('totalcardnum',$num);
  			$markcard = M("Wxuser")->where(array('uid'=>session('uid'),'token'=>session('token')))->find();
  			$can_cr_num = $markcard['allcardnum'] - $markcard['yetcardnum'];
  			if($can_cr_num <= 0){
  				$data['cardisok'] = 2;
  				M('Wxuser')->where(array('uid'=>session('uid'),'token'=>session('token')))->save($data);	
  			}
			
			if($back!=false){
				$this->success('恭喜您共开了'.$num.'张会员卡',U('Member_card/create',array('token'=>session('token'))));
			}else{
				$this->error('服务器繁忙请稍后再试');
			}
		}else{
			$markcard = M("Wxuser")->where(array('uid'=>session('uid'),'token'=>session('token')))->find();
			$can_cr_num = $markcard['allcardnum'] - $markcard['yetcardnum'];
			$this->assign('count',$markcard['allcardnum']);
			$this->assign('cancrnum',$can_cr_num);
			$this->display();
		}
	}
	
	//会员优惠卷
	public function coupon(){
		$data=M('Member_card_coupon')->where(array('token'=>$_SESSION['token']))->order('id desc')->select();

		$this->assign('data_vip',$data);
		$this->display();	
	}
	public function coupon_edit(){
		if(IS_POST){			
			$this->all_save('Member_card_coupon','/coupon');
		}else{
			$data=M('Member_card_coupon')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->find();
			if($data!=false){
				$this->assign('vip',$data);
				$this->display();
			}else{
				$this->error('非法操作');
			}
			
		}
		
	}	
	public function coupon_add(){
		if(IS_POST){			
			$this->all_insert('Member_card_coupon','/coupon');
		}else{
			
			$this->display();	
		}
		
	}
	public function coupon_del(){
			$data=M('Member_card_coupon')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->delete();
			if($data==false){
				$this->error('服务器繁忙请稍后再试');
			}else{
				$this->success('操作成功',U('Member_card/coupon',array('id'=>$data_vip['id'])));
			
			}
	}
	//会员礼卷
	public function integral(){
		$data=M('Member_card_integral')->where(array('token'=>$_SESSION['token']))->order('id desc')->select();
		$this->assign('data_vip',$data);
		$this->display();	
	}
	public function integral_edit(){
		if(IS_POST){			
			$this->all_save('Member_card_integral','/integral');
		}else{
			$data=M('Member_card_integral')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->find();
			if($data!=false){
				$this->assign('vip',$data);
				$this->display();
			}else{
				$this->error('非法操作');
			}
			
		}
		
	}	
	public function integral_add(){
		if(IS_POST){			
			$this->all_insert('Member_card_integral','/integral');
		}else{
			$this->display();	
		}
		
	}
	public function integral_del(){
			$data=M('Member_card_integral')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->delete();
			if($data==false){
				$this->error('服务器繁忙请稍后再试');
			}else{
				$this->success('操作成功',U('Member_card/integral',array('id'=>$data_vip['id'])));
			
			}
	}
	//会员详情
	public function info(){
		$data=M('Member_card_info')->where(array('token'=>$_SESSION['token']))->find();
		if(IS_POST){
			//dump($_POST);EXIT;
			$_POST['token']=$_SESSION['token'];			
			if($data==false){				
				$this->all_insert('Member_card_info','/info');
			}else{
				$_POST['id']=$data['id'];
				$this->all_save('Member_card_info','/info');
			}
		}else{
			$this->assign('info',$data);
			$contact=M('Member_card_contact')->where(array('token'=>$_SESSION['token']))->order('sort desc')->select();
			$this->assign('contact',$contact);
			$this->display();
		}	
	}
	public function contact(){
		if(IS_POST){			
			$this->all_insert('Member_card_contact','/info');
		}else{
				$this->error('非法操作');	
		}
	
	}
	public function contact_edit(){
		if(IS_POST){			
			$this->all_save('Member_card_contact','/info');
		}else{
			$this->error('非法操作');			
		}		
	}

	/**
	*积分设置 设置会员卡积分策略及会员卡级别
	*
	*/
	public function exchange(){
		$data=M('Member_card_exchange')->where(array('token'=>$_SESSION['token']))->find();
		if(IS_POST){
			$_POST['token']=$_SESSION['token'];	
			$_POST['create_time'] = time();		
			if($data==false){				
				$this->all_insert('Member_card_exchange','/exchange');
			}else{
				$_POST['id']=$data['id'];
				$this->all_save('Member_card_exchange','/exchange');
			}
		}else{
			$this->assign('exchange',$data);
			$this->display();
		}	
	}
}
?>