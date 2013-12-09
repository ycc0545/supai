<?php
class RecordsAction extends BackAction{
	public function index(){
		$records=M('indent');
		$count=$records->count();
		$page=new Page($count,25);
		$show= $page->show();
		$info=$records->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();
		$this->assign('page',$show);
		$this->assign('info',$info);
		$this->display();
	}
	
	public function send(){
		$money=$this->_get('price','intval');
		$data['id']=$this->_get('uid','intval');
		if($money!=false&&$data['id']!=false){
			$back=M('Users')->where($data)->setInc('money',$money);
			$status=M('Indent')->where(array('id'=>$this->_get('iid','intval')))->setField('status',2);
			if($back!=false&&$status!=false){
				$this->success('充值成功',U('Admin/Records/index'));
			}else{
				$this->error('充值失败',U('Admin/Records/index'));
			}
		}else{
			$this->error('非法操作',U('Admin/Records/index'));
		}
	}
	
	public function del(){
        $id = $this->_get('iid','intval',0);
        if(!$id)$this->error('参数错误!');
		$IndentDB = D('Indent');
		if($IndentDB->delete($id)){
			$this->success("操作成功!",U('Admin/Records/index'));
		}else{
			$this->error("操作失败!",U('Admin/Records/index'));
		}
	}
}