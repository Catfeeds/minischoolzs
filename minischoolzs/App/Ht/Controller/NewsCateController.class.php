<?php
namespace Ht\Controller;
use Think\Controller;
class NewsCateController extends PublicController{

	/*
	*
	* 构造函数，用于导入外部文件和公共方法
	*/
	public function _initialize(){
		$this->category = M('news_cat');
	}

	/*
	*
	* 获取、查询栏目表新闻分类的数据
	*/
	public function index(){
		
		//获取分类表里所有新闻分类
		$cate_list = $this->category->where('1=1')->order('id desc')->select();
		$this->assign('cate_list',$cate_list);
		$this->display(); // 输出模板

	}


	/*
	*
	* 跳转添加或修改新闻分类页面
	*/
	public function add(){
		//如果是修改，则查询对应分类信息
		if (intval($_GET['cid'])) {
			$cate_id = intval($_GET['cid']);
		
			$cate_info = $this->category->where('id='.intval($cate_id))->find();
			if (!$cate_info) {
				$this->error('没有找到相关信息.');
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

		//判断是否已经存在该栏目
		if (!intval($_POST['cid'])) {
			$check_id = $this->category->where('name="'.trim($_POST['name']).'"')->getField('id');
			if (is_int($check_id)) {
				$this->error('分类名称已存在.');
				exit();
			}
		}

		//构建数组
		$name = trim($_POST['name']);
		if (!$name) {
			$this->error('请输入分类名称.');
			exit();
		}

		$data = array();
		$data['name'] = $name;
		//保存数据
		if (intval($_POST['cid'])) {
			$result = $this->category->where('id='.intval($_POST['cid']))->save($data);
		}else{
			//保存添加时间
			$data['addtime'] = time();
			$result = $this->category->add($data);
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
	* 栏目删除
	*/
	public function del(){
		//以后删除还要加权限登录判断
		$id = intval($_GET['did']);
		$check_info = $this->category->where('id='.intval($id))->find();
		if (!$check_info) {
			$this->error('非法操作.');
			exit();
		}

		$res = $this->category->where('id='.intval($id))->delete();
		if ($res) {
			//把对应图片也一起删除
			$this->redirect('index');
		}else{
			$this->error('操作失败.');
		}
	}

}