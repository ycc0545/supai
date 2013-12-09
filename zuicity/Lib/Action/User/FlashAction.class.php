<?php
/**
 *首页幻灯片回复
**/
class FlashAction extends UserAction{
	public function index(){
		$db=D('Flash');
		$where['uid']=session('uid');
		$where['token']=session('token');
		$count=$db->where($where)->count();
		$page=new Page($count,25);
		$info=$db->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('info',$info);
		$this->display();
	}
	public function add(){
		$this->display();
	}
	public function edit(){
		$where['id']=$this->_get('id','intval');
		$where['uid']=session('uid');
		$res=D('Flash')->where($where)->find();
		$this->assign('info',$res);
		$this->display();
	}
	public function del(){
		$where['id']=$this->_get('id','intval');
		$where['uid']=session('uid');
		if(D(MODULE_NAME)->where($where)->delete()){
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
	}
	public function insert(){
    	$data=array();
        $data['token']=$_SESSION['token'];
    	$data['img']=trim($_POST['img']);
    	if($_FILES['file']['name']){
			$img=$this->_upload();
			$data['img']=$img[0]['savepath'].$img[0]['savename'];
    	}	
    	$data['info']=trim($_POST['info']);
    	$data['url']=trim($_POST['url']);
    	$result=M('flash')->add($data);
		if($result!==false){
				$this->success('操作成功');
			}
	}
	public function upsave(){
		$this->all_save();
	}

	protected function _upload() {
		import("@.ORG.UploadFile");
		$upload = new UploadFile();
		//设置上传文件大小
		$upload->maxSize = 3292200;
		//设置上传文件类型
		$upload->allowExts = explode(',', 'jpg,gif,png,jpeg');
		//设置附件上传目录
		$upload->savePath = './zuicitydata/attachments/';
		//设置需要生成缩略图，仅对图像文件有效
		$upload->thumb = true;
		// 设置引用图片类库包路径
		$upload->imageClassPath = '@.ORG.Image';
		//设置需要生成缩略图的文件后缀
		$upload->thumbPrefix = 'm_';
		//生产2张缩略图
		//设置缩略图最大宽度
		$upload->thumbMaxWidth = '720';
		//设置缩略图最大高度
		$upload->thumbMaxHeight = '400';
		//设置上传文件规则
		$upload->saveRule = uniqid;
		//删除原图
		$upload->thumbRemoveOrigin = false;
		if (!$upload->upload()) {
			//捕获上传异常
			return $upload->getErrorMsg();
		} else {
			//取得成功上传的文件信息
			$uploadList = $upload->getUploadFileInfo();
			return $uploadList;
		}
	}


}
?>