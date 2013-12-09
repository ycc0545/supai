<?php
/**
 *模板设置
**/
class TmplsAction extends UserAction{
	public function index(){
		$db=D('Wxuser');
		$where['token']=session('token');
		$where['uid']=session('uid');
		$info=$db->where($where)->find();
		$this->assign('info',$info);
		$this->display();
	}
	public function add(){
		$gets=$this->_get('style');
		$db=M('Wxuser');
		switch($gets){
			case 1:
				$data['tpltypeid']=1;
				$data['tpltypename']='tpl_101_index';
				break;
			case 2:
				$data['tpltypeid']=2;
				$data['tpltypename']='tpl_102_index';
				break;
			case 3:
				$data['tpltypeid']=3;
				$data['tpltypename']='tpl_103_index';
				break;
			case 4:
				$data['tpltypeid']=4;
				$data['tpltypename']='tpl_104_index';
				break;
			case 5:
				$data['tpltypeid']=5;
				$data['tpltypename']='tpl_105_index';
				break;
			case 6:
				$data['tpltypeid']=6;
				$data['tpltypename']='tpl_106_index';
				break;
			case 7:
				$data['tpltypeid']=7;
				$data['tpltypename']='tpl_107_index';
				break;
			case 8:
				$data['tpltypeid']=8;
				$data['tpltypename']='tpl_108_index';
				break;
			case 9:
				$data['tpltypeid']=9;
				$data['tpltypename']='tpl_109_index';
				break;
			case 10:
				$data['tpltypeid']=10;
				$data['tpltypename']='tpl_110_index';
				break;
			case 11:
				$data['tpltypeid']=11;
				$data['tpltypename']='tpl_111_index';
				break;
			case 12:
				$data['tpltypeid']=12;
				$data['tpltypename']='tpl_112_index';
				break;
			case 13:
				$data['tpltypeid']=13;
				$data['tpltypename']='tpl_113_index';
				break;
			case 14:
				$data['tpltypeid']=14;
				$data['tpltypename']='tpl_114_index';
				break;
		}
		$where['token']=session('token');
		$db->where($where)->save($data);
	}
	public function lists(){
		$gets=$this->_get('style');
		$db=M('Wxuser');
		switch($gets){
			case 4:
				$data['tpllistid']=4;
				$data['tpllistname']='ktv_list';
				break;
			case 1:
				$data['tpllistid']=1;
				$data['tpllistname']='yl_list';
				break;
		}
		$where['token']=session('token');
		$db->where($where)->save($data);
	}
	public function content(){
		$gets=$this->_get('style');
		$db=M('Wxuser');
		switch($gets){
			case 1:
				$data['tplcontentid']=1;
				$data['tplcontentname']='yl_content';
				break;
			case 3:
				$data['tplcontentid']=3;
				$data['tplcontentname']='ktv_content';
				break;
		}
		$where['token']=session('token');
		$db->where($where)->save($data);
	}
	public function insert(){
	
	}
	public function upsave(){
	
	}
}
?>