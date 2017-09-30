<?php
namespace Api\Controller;
use Think\Controller;
class IndexController extends PublicController {
	//***************************
	//  首页数据接口
	//***************************
    public function index(){
    	//如果缓存首页没有数据，那么就读取数据库
    	/***********获取首页顶部轮播图************/
    	$ggtop=M('guanggao')->order('sort desc,id asc')->field('id,name,photo')->limit(10)->select();
		foreach ($ggtop as $k => $v) {
			$ggtop[$k]['photo']=__DATAURL__.$v['photo'];
			$ggtop[$k]['name']=urlencode($v['name']);
		}
    	/***********获取首页顶部轮播图 end************/

        //======================
        //首页分类6个
        //======================
        $cat = M('pro_cat')->where('del=0 AND tid=0')->order('type desc,sort desc')->field('id,name,img')->limit(6)->select();
        foreach ($cat as $k => $v) {
            $cat[$k]['img'] = __DATAURL__.$v['img'];
        }

    	//======================
    	//首页推荐产品
    	//======================
    	$pro_list = M('product')->where('del=0 AND type=1')->order('sort desc,id desc')->field('id,name,photo_x')->limit(4)->select();
    	foreach ($pro_list as $k => $v) {
    		$pro_list[$k]['photo_x'] = __DATAURL__.$v['photo_x'];
    	}

        //======================
        //首页推荐新闻资讯
        //======================
        $news = M('news')->where('1=1')->order('sort desc,id desc')->field('id,name,addtime,photo,source')->limit(6)->select();
        foreach ($news as $k => $v) {
            $news[$k]['photo'] = __DATAURL__.$v['photo'];
            $news[$k]['addtime'] = date('Y-m-d',$v['addtime']);
        }

        //======================
        //首页 供应内容
        //======================
        $supply = M('supply');
        $gong = $supply->where('state=0 AND type=1')->order('addtime desc')->limit(3)->select();
        foreach ($gong as $k => $v) {
            $gong[$k]['addtime'] = date("Y-m-d",$v['addtime']);
            $gong[$k]['photo'] = M('user')->where('id='.intval($v['uid']))->getField('photo');
        }

        //======================
        //首页 求购内容
        //======================
        $qiu = $supply->where('state=0 AND type=2')->order('addtime desc')->limit(3)->select();
        foreach ($qiu as $k => $v) {
            $qiu[$k]['addtime'] = date("Y-m-d",$v['addtime']);
            $qiu[$k]['photo'] = M('user')->where('id='.intval($v['uid']))->getField('photo');
        }

    	echo json_encode(array('ggtop'=>$ggtop,'prolist'=>$pro_list,'cat'=>$cat,'news'=>$news,'gong'=>$gong,'qiu'=>$qiu));
    	exit();
    }

    //***************************
    //  首页产品 分页
    //***************************
    public function getlist(){
        $page = intval($_REQUEST['page']);
        if (!$page) {
           $page=2;
        }
        $limit = intval($page*6)-6;

        $news = M('news')->where('1=1')->order('sort desc,id desc')->field('id,name,addtime,photo,source')->limit($limit.',6')->select();
        foreach ($news as $k => $v) {
            $news[$k]['photo'] = __DATAURL__.$v['photo'];
            $news[$k]['addtime'] = date('Y-m-d',$v['addtime']);
        }

        echo json_encode(array('news'=>$news));
        exit();
    }

    //***************************
    //  首页供求 上一页
    //***************************
    public function getpage(){
        $page = intval($_REQUEST['page']);
        if (!$page) {
           $page=2;
        }
        $limit = intval($page*3)-3;

        $condition = array();
        $condition['state'] = 0;
        $ptype = intval($_REQUEST['ptype']);
        if ($ptype==1) {
            $condition['type'] = 1;
        }else{
            $condition['type'] = 2;
        }

        //======================
        //首页 供应内容
        //======================
        $supply = M('supply');
        $list = $supply->where($condition)->order('addtime desc')->limit($limit.',3')->select();
        foreach ($list as $k => $v) {
            $list[$k]['addtime'] = date("Y-m-d",$v['addtime']);
            $list[$k]['photo'] = M('user')->where('id='.intval($v['uid']))->getField('photo');
        }

        echo json_encode(array('list'=>$list));
        exit();
    }

    public function ceshi(){
        $strPol = "SPA精油保湿手套 硅胶防裂凝胶美容嫩白手膜护肤睡眠水疗保养滋润";
        $str = $this->cut_str($strPol,36);
        echo $str;
    }

}