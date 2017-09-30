<?php
namespace Ht\Controller;
use Think\Controller;
class ProCatController extends PublicController{

	/*
	*
	* 构造函数，用于导入外部文件和公共方法
	*/
	public function _initialize(){
		$this->pro_cat = M('pro_cat');
		// 获取所有一级分类，进行关系划分
		$list = $this->pro_cat->where('del=0')->order('sort desc,id desc')->select();
		$this->assign('list',$list);// 赋值数据集
	}

	/*
	*
	* 获取、查询栏目表数据
	*/
	public function index(){
		$this->display(); // 输出模板

	}


	/*
	*
	* 跳转添加或修改栏目页面
	*/
	public function add(){
		//如果是修改，则查询对应分类信息
		if (intval($_GET['cid'])) {
			$cate_id = intval($_GET['cid']);
		
			$cate_info = $this->pro_cat->where('id='.intval($cate_id).' AND del=0')->find();
			if (!$cate_info) {
				$this->error('没有找到相关信息.');
				exit();
			}
			$this->assign('cate_info',$cate_info);
		}
		$this->display();
	}


	/*
	*
	* 添加或修改栏目信息
	*/
	public function save(){
		$tid = intval($_POST['tid']);
		//判断是否已经存在该栏目
		if (!intval($_POST['cid'])) {
			$check_id = $this->pro_cat->where('name="'.trim($_POST['name']).'"')->getField('id');
			if ($check_id) {
				$this->error('该分类已存在.');
				exit();
			}
		}

		//构建数组
		$this->pro_cat->create();
		//上传产品分类缩略图
		if (!empty($_FILES["file2"]["tmp_name"])) {
			//文件上传
			$info2 = $this->upload_images($_FILES["file2"],array('jpg','png','jpeg'),"category/".date(Ymd));
		    if(!is_array($info2)) {// 上传错误提示错误信息
		        $this->error($info2);
		        exit();
		    }else{// 上传成功 获取上传文件信息
			    $this->pro_cat->img = 'UploadFiles/'.$info2['savepath'].$info2['savename'];
		    }
		}
		
		//保存数据
		if (intval($_POST['cid'])) {
			$result = $this->pro_cat->where('id='.intval($_POST['cid']))->save();
		}else{
			//保存添加时间
			$this->pro_cat->addtime = time();
			$result = $this->pro_cat->add();
		}
		//判断数据是否更新成功
		if ($result) {
			$this->success('操作成功.','index');
		}else{
			$this->error('操作失败.');
		}
	}


	/*
	*
	*  设置栏目推荐
	*/
	public function set_tj(){
		$tj_id = intval($_REQUEST['tj_id']);
		$cate_info = $this->pro_cat->where('id='.intval($tj_id))->find();
		if (!$cate_info) {
			$this->error('操作失败.'.__LINE__);
			exit();
		}
		$data=array();
		$data['type'] = $cate_info['type'] == '1' ?  0 : 1;
		$up = $this->pro_cat->where('id='.intval($tj_id))->save($data);
		if ($up) {
			$this->success('操作成功.');
		}else{
			$this->error('操作失败.');
		}
	}

	/*
	*
	* 栏目删除
	*/
	public function del(){
		//以后删除还要加权限登录判断
		$id = intval($_GET['did']);
		$check_info = $this->pro_cat->where('id='.intval($id))->find();
		if (!$check_info) {
			$this->error('操作失败.'.__LINE__);
			exit();
		}

		$res = $this->pro_cat->where('id='.intval($id))->save(array('del'=>1));
		if ($res) {
			$url = "Data/".$check_info['cat_img'];
			if (file_exists($url)) {
				@unlink($url);
			}
			$this->redirect('index');
		}else{
			$this->error('操作失败.');
		}
	}

}