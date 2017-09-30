<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;
use Think\Controller;
class ProductController extends PublicController {
	//***************************
	//  获取商品详情信息接口
	//***************************
    public function index(){
		$product=M("product");

		$pro_id = intval($_REQUEST['pro_id']);
		if (!$pro_id) {
			echo json_encode(array('status'=>0,'err'=>'商品不存在或已下架！'));
			exit();
		}
		
		$pro = $product->where('id='.intval($pro_id).' AND del=0 AND is_down=0')->find();
		if(!$pro){
			echo json_encode(array('status'=>0,'err'=>'商品不存在或已下架！'.__LINE__));
			exit();
		}

		$pro['photo_x'] =__DATAURL__.$pro['photo_x'];
		$pro['photo_d'] = __DATAURL__.$pro['photo_d'];
		$pro['brand'] = M('brand')->where('id='.intval($pro['brand_id']))->getField('name');
		$pro['cat_name'] = M('category')->where('id='.intval($pro['cid']))->getField('name');

		//图片轮播数组
		$img=explode(',',trim($pro['photo_string'],','));
		$b=array();
		if ($pro['photo_string']) {
			foreach ($img as $k => $v) {
				$b[] = __DATAURL__.$v;
			}
		}else{
			$b[] = $pro['photo_d'];
		}
		$pro['img_arr']=$b;//图片轮播数组
		
		//处理产品属性
		$catlist=array();
		if($pro['pro_buff']){//如果产品属性有值才进行数据组装
			$pro_buff = explode(',',$pro['pro_buff']);
			$commodityAttr=array();//产品库还剩下的产品规格
			$attrValueList=array();//产品所有的产品规格
			foreach($pro_buff as $key=>$val){
				$attr_name = M('attribute')->where('id='.intval($val))->getField('attr_name');
				$guigelist=M('guige')->where("attr_id=".intval($val).' AND pid='.intval($pro['id']))->field("id,name")->select();
				$ggss = array();
				$gg=array();
				foreach ($guigelist as $k => $v) {
					$gg[$k]['attrKey']=$attr_name;
					$gg[$k]['attrValue']=$v['name'];
					$ggss[] = $v['name'];
				}
				$commodityAttr[$key]['attrValueList'] = $gg;
				$attrValueList[$key]['attrKey']=$attr_name;
				$attrValueList[$key]['attrValueList']=$ggss;
			}
		}

		$content = str_replace('/minigzbdrent/Data/', __DATAURL__, $pro['content']);
		$pro['content']=html_entity_decode($content, ENT_QUOTES , 'utf-8');

		//检测产品是否收藏
		$col = M('product_sc')->where('uid='.intval($_REQUEST['uid']).' AND pid='.intval($pro_id))->getField('id');
		if ($col) {
			$pro['collect']= 1;
		}else{
			$pro['collect']= 0;
		}
		echo json_encode(array('status'=>1,'pro'=>$pro,'commodityAttr'=>$commodityAttr,'attrValueList'=>$attrValueList));
		exit();

	}

	//***************************
	//  获取商品详情接口
	//***************************
	public function details(){
		header('Content-type:text/html; Charset=utf8');
		$pro_id = intval($_REQUEST['pro_id']);
		$pro = M('product')->where('id='.intval($pro_id).' AND del=0')->find();
		if(!$pro){
			echo json_encode(array('status'=>0,'err'=>'商品不存在或已下架！'));
			exit();
		}
		$content = str_replace('/minigzbdrent/Data/', __DATAURL__, $pro['content']);
		$content = html_entity_decode($content, ENT_QUOTES , 'utf-8');

		$advimg = trim($pro['adv_img'],',');
		$advimg = explode(',',$advimg);

		$imgs = array();
		if ($pro['adv_img']) {
			foreach ($advimg as $k => $v) {
				$imgs[] = __DATAURL__.$v;
			}
		}

		$pro['addtime'] = date("Y-m-d",$pro['addtime']);

		$up = array();
		$up['renqi'] = intval($pro['renqi'])+1;
		M('product')->where('id='.intval($pro_id))->save($up);

		echo json_encode(array('status'=>1,'content'=>$content,'advimg'=>$imgs,'info'=>$pro));
		exit();
	}

	//***************************
	//  获取商品列表接口
	//***************************
   	public function lists(){
 		$json="";
 		$id=intval($_POST['cat_id']);//获得分类id 这里的id是pro表里的cid

 		$keyword=I('post.keyword');
 		//排序
 		$order="addtime desc";//默认按添加时间排序

 		//条件
 		$where="1=1 AND del=0";
 		if(intval($id)){
 			$where.=" AND cid=".intval($id);
 		}

 		if($keyword && $keyword!='undefined') {
            $where.=' AND name LIKE "%'.$keyword.'%"';
        }

 		$product = M('product')->where($where)->order($order)->limit(8)->select();

 		$json = array();$json_arr = array();
 		foreach ($product as $k => $v) {
 			$json['id'] = $v['id'];
 			$json['name'] = $v['name'];
 			$json['photo_x'] = __DATAURL__.$v['photo_x'];
 			$json['sname'] = M('shangchang')->where('id='.intval($v['shop_id']))->getField('name');
 			$json['tel'] = $v['tel'];
 			$json['area'] = $v['area'];//面积
 			$json['xq'] = $v['xq'];
 			$json['address'] = M('area')->where('id='.intval($v['address']))->getField('name');
 			$json['htype'] = M('pro_cat')->where('id='.intval($v['htype']))->getField('name');
 			$json['addtime'] = date("m-d",$v['addtime']);
 			$json['price'] = $v['price'];
 			$json_arr[] = $json;
 		}

 		//获取所有一级地区
 		$addr = M('area')->where('tid=0')->field('id,name')->select();
 		$addrlist = array();$addr_list = array();
 		$addr_list[0]['id'] = 0;
 		$addr_list[0]['name'] = '不限';
 		foreach ($addr as $k => $v) {
 			$addrlist['id'] = intval($v['id']);
 			$addrlist['name'] = $v['name'];
 			$addr_list[] = $addrlist;
 		}

 		//获取所有对应产品分类的户型
 		$htype = M('pro_cat')->where('tid='.intval($id))->field('id,name')->select();
 		$hlist = array();$h_list = array();
 		$h_list[0]['id'] = 0;
 		$h_list[0]['name'] = '不限';
 		foreach ($htype as $k => $v) {
 			$hlist['id'] = intval($v['id']);
 			$hlist['name'] = $v['name'];
 			$h_list[] = $hlist;
 		}

 		//自定义排序
 		$plist = array();
 		$plist[0]['name'] = '不限';
 		$plist[0]['id'] = 0;
 		$plist[1]['name'] = '1000以下';
 		$plist[1]['id'] = 1;
 		$plist[2]['name'] = '1000-2000';
 		$plist[2]['id'] = 2;
 		$plist[3]['name'] = '2000-5000';
 		$plist[3]['id'] = 3;
 		$plist[4]['name'] = '5000-10000';
 		$plist[4]['id'] = 4;
 		$plist[5]['name'] = '10000以上';
 		$plist[5]['id'] = 5;
 		$plist[6]['name'] = '面议';
 		$plist[6]['id'] = 6;

 		//自定义排序
 		$sortlists = array();
 		$sortlists[0]['name'] = '不限';
 		$sortlists[0]['sorttype'] = '';
 		$sortlists[1]['name'] = '最近发布';
 		$sortlists[1]['sorttype'] = 'addtime';
 		$sortlists[2]['name'] = '面积';
 		$sortlists[2]['sorttype'] = 'area';
 		$sortlists[3]['name'] = '浏览人气';
 		$sortlists[3]['sorttype'] = 'renqi';

 		echo json_encode(array('status'=>1,'pro'=>$json_arr,'addr'=>$addr_list,'htype'=>$h_list,'sortlist'=>$sortlists,'pricelist'=>$plist));
 		exit();
    }

    //*******************************
	//  商品列表页面 获取更多接口
	//*******************************
    public function get_more(){
 		$json="";
 		$id=intval($_POST['cat_id']);//获得分类id 这里的id是pro表里的cid

 		$page= intval($_POST['page']);
 		if (!$page) {
 			$page=2;
 		}
 		$limit = intval($page*8)-8;

 		$keyword=I('post.keyword');
 		//排序
 		$order="addtime desc";//默认按添加时间排序
 		//条件
 		$where="1=1 AND del=0";
 		if(intval($id)) {
 			$where.=" AND cid=".intval($id);
 		}

 		//地区排序
 		$addrid = intval($_POST['addrid']);//地区ID
 		if ($addrid) {
	 		//判断是不是一级分类，是则查询该分类下的所有二级分类id
	 		$ids = M('area')->where('tid='.intval($addrid))->field('id')->select();
	 		if ($ids) {
	 			$arr = array();
		 		foreach ($ids as $k => $v) {
		 			$arr[] = $v['id'];
		 		}
		 		$arr[] = $addrid;
		 		$arrstr = implode($arr, ',');
		 		$where.=" AND address IN (".$arrstr.")";
		 	}else{
		 		$where.=" AND address=".intval($addrid);
		 	}	
	 	}

	 	//价格排序
	 	$priceId = intval($_POST['priceId']);
	 	if ($priceId) {
	 		switch ($priceId) {
	 			case '1':
	 				$where.=" AND price<=1000";
	 				break;
	 			case '2':
	 				$where.=" AND price<=2000 AND price>1000";
	 				break;
	 			case '3':
	 				$where.=" AND price<=5000 AND price>2000";
	 				break;
	 			case '4':
	 				$where.=" AND price<=10000 AND price>5000";
	 				break;
	 			case '5':
	 				$where.=" AND price>10000";
	 				break;
	 			case '6':
	 				$where.=" AND price=0";
	 				break;			
	 			default:
	 				# code...
	 				break;
	 		}
	 	}

	 	//户型排序
	 	$htype = intval($_POST['htype']);
	 	if ($htype) {
	 		$where.=" AND htype=".intval($htype);
	 	}

	 	//排序
 		$sorttype = trim($_REQUEST['sorttype']);
 		if ($sorttype) {
 			if ($sorttype=='area') {
 				//面积排序
 				$order="area desc";
 			}elseif ($sorttype=='renqi') {
 				//人气排序
 				$order="renqi desc";
 			}else{
 				//时间排序
 				$order="addtime desc";
 			}
 		}

 		if($keyword && $keyword!='undefined') {
            $where.=' AND name LIKE "%'.$keyword.'%"';
        }

 		$product=M('product')->where($where)->order($order)->limit($limit.',8')->select();

 		$json = array();$json_arr = array();
 		foreach ($product as $k => $v) {
 			$json['id'] = $v['id'];
 			$json['name'] = $v['name'];
 			$json['photo_x'] = __DATAURL__.$v['photo_x'];
 			$json['sname'] = M('shangchang')->where('id='.intval($v['shop_id']))->getField('name');
 			$json['tel'] = $v['tel'];
 			$json['area'] = $v['area'];//面积
 			$json['xq'] = $v['xq'];
 			$json['address'] = M('area')->where('id='.intval($v['address']))->getField('name');
 			$json['htype'] = M('pro_cat')->where('id='.intval($v['htype']))->getField('name');
 			$json['addtime'] = date("m-d",$v['addtime']);
 			$json['price'] = $v['price'];
 			$json_arr[] = $json;
 		}

 		echo json_encode(array('pro'=>$json_arr));
 		exit();
    }

    //*******************************
	//  商品列表页面 获取更多接口
	//*******************************
    public function sortlist(){
 		$json="";
 		$id=intval($_REQUEST['cat_id']);//获得分类id 这里的id是pro表里的cid

 		$keyword=I('post.keyword');
 		//排序
 		$order="addtime desc";//默认按添加时间排序
 		//条件
 		$where="1=1 AND del=0";
 		if(intval($id)) {
 			$where.=" AND cid=".intval($id);
 		}

 		$addrid = intval($_REQUEST['addrid']);//地区ID
 		if ($addrid) {
	 		//判断是不是一级分类，是则查询该分类下的所有二级分类id
	 		$ids = M('area')->where('tid='.intval($addrid))->field('id')->select();
	 		if ($ids) {
	 			$arr = array();
		 		foreach ($ids as $k => $v) {
		 			$arr[] = $v['id'];
		 		}
		 		$arr[] = $addrid;
		 		$arrstr = implode($arr, ',');
		 		$where.=" AND address IN (".$arrstr.")";
		 	}else{
		 		$where.=" AND address=".intval($addrid);
		 	}	
	 	}

	 	//价格排序
	 	$priceId = intval($_POST['priceId']);
	 	if ($priceId) {
	 		switch ($priceId) {
	 			case '1':
	 				$where.=" AND price<=1000";
	 				break;
	 			case '2':
	 				$where.=" AND price<=2000 AND price>1000";
	 				break;
	 			case '3':
	 				$where.=" AND price<=5000 AND price>2000";
	 				break;
	 			case '4':
	 				$where.=" AND price<=10000 AND price>5000";
	 				break;
	 			case '5':
	 				$where.=" AND price>10000";
	 				break;
	 			case '6':
	 				$where.=" AND price=0";
	 				break;			
	 			default:
	 				# code...
	 				break;
	 		}
	 	}

	 	$htype = intval($_REQUEST['htype']);
	 	if ($htype) {
	 		$where.=" AND htype=".intval($htype);
	 	}

 		$sorttype = trim($_REQUEST['sorttype']);
 		if ($sorttype) {
 			if ($sorttype=='area') {
 				//面积排序
 				$order="area desc";
 			}elseif ($sorttype=='renqi') {
 				//人气排序
 				$order="renqi desc";
 			}else{
 				//时间排序
 				$order="addtime desc";
 			}
 		}

 		if($keyword && $keyword!='undefined') {
            $where.=' AND name LIKE "%'.$keyword.'%"';
        }

 		$product=M('product')->where($where)->order($order)->limit(8)->select();

 		$json = array();$json_arr = array();
 		foreach ($product as $k => $v) {
 			$json['id'] = $v['id'];
 			$json['name'] = $v['name'];
 			$json['photo_x'] = __DATAURL__.$v['photo_x'];
 			$json['sname'] = M('shangchang')->where('id='.intval($v['shop_id']))->getField('name');
 			$json['tel'] = $v['tel'];
 			$json['area'] = $v['area'];//面积
 			$json['xq'] = $v['xq'];
 			$json['address'] = M('area')->where('id='.intval($v['address']))->getField('name');
 			$json['htype'] = M('pro_cat')->where('id='.intval($v['htype']))->getField('name');
 			$json['addtime'] = date("m-d",$v['addtime']);
 			$json['price'] = $v['price'];
 			$json_arr[] = $json;
 		}

 		echo json_encode(array('pro'=>$json_arr,'sql'=>$where));
 		exit();
    }

}