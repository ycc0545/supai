<?php
class IndexAction extends BaseAction{
	//关注回复
	public function index(){
		
		$this->display();
	}
	public function resetpwd(){
		$uid=$this->_get('uid','intval');
		$code=$this->_get('code','trim');
		$rtime=$this->_get('resettime','intval');
		$info=M('Users')->find($uid);
		if( (md5($info['uid'].$info['password'].$info['email'])!==$code) || ($rtime<time()) ){
			$this->error('非法操作',U('Index/index'));
		}
		$this->assign('uid',$uid);
		$this->display();
	}
	public function add_order(){
		dump($_POST);
	}
    // 用户登出
    public function logout() {
		session(null);
		session_destroy();
		unset($_SESSION);
        if(session('?'.C('USER_AUTH_KEY'))) {
            session(C('USER_AUTH_KEY'),null);
            redirect(U('Index/index'));
        }else {
            $this->error('已经登出！',U('Index/index'));
        }
    }
}