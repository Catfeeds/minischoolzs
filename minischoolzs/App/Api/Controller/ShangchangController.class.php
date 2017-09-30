<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;
use Think\Controller;
class ShangchangController extends PublicController {

	//***************************
	//  获取所有商场的数据
	//***************************
    public function index(){
    	//查询条件
    	//根据店铺分类id查询
    	$condition = array();
    	$condition['status']=1;
    	$cid = intval($_REQUEST['cid']);
    	if ($cid) {
    		$condition['cid']=intval($cid);
    	}

    	//根据店铺名称查询
    	$keyword = trim($_REQUEST['keyword']);
    	if ($keyword) {
    		$condition['name']=array('LIKE','%'.$keyword.'%');
    	}

    	//获取页面显示条数
    	$page = intval($_REQUEST['page']);
    	if (!$page) {
    		$page = 1;
    	}
        $limit = intval($page*8)-8;

        //获取所有企业分类
        $catlist = M('sccat')->where('1=1')->order('addtime desc')->field('id,name')->select();

    	//获取所有的商家数据
    	$store_list = M('shangchang')->where($condition)->order('sort desc,type desc')->field('id,name,logo,main_hy')->limit(8)->select();
    	foreach ($store_list as $k => $v) {
    		$store_list[$k]['logo'] = __DATAURL__.$v['logo'];
    	}

    	echo json_encode(array('status'=>1,'store_list'=>$store_list,'catlist'=>$catlist));
    	exit();
    }

    //***************************
    //  商家列表获取更多
    //***************************
    public function get_more(){
        //查询条件
        //根据店铺分类id查询
        $condition = array();
        $condition['status']=1;

        //根据店铺名称查询
        $keyword = trim($_REQUEST['keyword']);
        if ($keyword) {
            $condition['name']=array('LIKE','%'.$keyword.'%');
        }

        $cid = intval($_REQUEST['cid']);
        if ($cid) {
            $condition['cid']=intval($cid);
        }

        //获取页面显示条数
        $page = intval($_REQUEST['page']);
        if (!$page) {
            $page = 1;
        }
        $limit = intval($page*8)-8;

        //获取所有的商家数据
        $store_list = M('shangchang')->where($condition)->order('sort desc,type desc')->field('id,name,logo,main_hy')->limit($limit.',8')->select();
        foreach ($store_list as $k => $v) {
            $store_list[$k]['logo'] = __DATAURL__.$v['logo'];
        }

        echo json_encode(array('status'=>1,'store_list'=>$store_list));
        exit();
    }

    //***************************
	//  获取商铺详情信息接口
	//***************************
    public function shop_details(){

    	$shop_id = intval($_REQUEST['shop_id']);
    	$shop_info = M('shangchang')->where('id='.intval($shop_id))->field('id,name,uname,tel,utel,vip_char,address_xq')->find();
    	if (!$shop_info) {
    		echo json_encode(array('status'=>0,'err'=>'没有找到商铺信息.'));
    		exit();
    	}

    	$shop_info['vip_char']=__DATAURL__.$shop_info['vip_char'];
    	//$shop_info['content']=html_entity_decode($shop_info['content'], ENT_QUOTES ,'utf-8');

    	echo json_encode(array('status'=>1,'shop_info'=>$shop_info));
    	exit();
    }


	//***************************
	//  会员店铺收藏接口
	//***************************
	public function shop_collect(){
		$uid = intval($_REQUEST['uid']);
		$shop_id = intval($_REQUEST['shop_id']);
		if (!$uid || !$shop_id) {
			echo json_encode(array('status'=>0,'err'=>'系统错误，请稍后再试.'));
			exit();
		}

		$check = M('shangchang_sc')->where('uid='.intval($uid).' AND shop_id='.intval($shop_id))->getField('id');
		if ($check) {
			echo json_encode(array('status'=>1,'succ'=>'您已收藏该店铺.'));
			exit();
		}
		$data = array();
		$data['uid'] = intval($uid);
		$data['shop_id'] = intval($shop_id);
		$res = M('shangchang_sc')->add($data);
		if ($res) {
			echo json_encode(array('status'=>1,'succ'=>'收藏成功！'));
			exit();
		}else{
			echo json_encode(array('status'=>0,'err'=>'网络错误..'));
			exit();
		}
	}

}