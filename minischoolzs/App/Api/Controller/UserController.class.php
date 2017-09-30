<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;
use Think\Controller;
class UserController extends PublicController {
	
	Public function verify(){
	    $image = new \Org\Util\Image;
	    $image->buildImageVerify();
    }

	//***************************
	//  获取用户订单数量
	//***************************
	public function getorder(){
		$uid = intval($_REQUEST['userId']);
		if (!$uid) {
			echo json_encode(array('status'=>0,'err'=>'非法操作.'));
			exit();
		}

		$order = array();
		$order['pay_num'] = intval(M('order')->where('uid='.intval($uid).' AND status=10 AND del=0')->getField('COUNT(id)'));
		$order['rec_num'] = intval(M('order')->where('uid='.intval($uid).' AND status=30 AND del=0 AND back="0"')->getField('COUNT(id)'));
		$order['finish_num'] = intval(M('order')->where('uid='.intval($uid).' AND status>30 AND del=0 AND back="0"')->getField('COUNT(id)'));
		$order['refund_num'] = intval(M('order')->where('uid='.intval($uid).' AND back>"0"')->getField('COUNT(id)'));
		echo json_encode(array('status'=>1,'orderInfo'=>$order));
		exit();
	}


	//***************************
	//  获取用户信息
	//***************************
	public function userinfo(){
		$uid = intval($_REQUEST['uid']);
		$user = M("user")->where('id='.intval($uid).' AND del=0')->find();
		if (!$user) {
			echo json_encode(array('status'=>0,'err'=>'用户信息异常.'));
			exit();
		}

		echo json_encode(array('status'=>1,'userinfo'=>$user));
		exit();
		
	}

	//***************************
	//  修改用户信息
	//***************************
	public function user_edit(){
			$user_id=intval($_REQUEST['uid']);
			$user_info = M('user')->where('id='.intval($user_id).' AND del=0')->find();
			if (!$user_info) {
				echo json_encode(array('status'=>0,'err'=>'会员状态异常.'));
				exit();
			}

			$tel = trim($_POST['tel']);
			$usertype = intval($_POST['usertype']);
			$truename = trim($_POST['truename']);
			$bl_number = trim($_POST['bl_number']);
			$data = array();
			if ($tel) {
				$data['tel'] = $tel;
			}
			if ($truename) {
				$data['truename'] = $truename;
			}
			//企业认证申请
			if ($usertype==1) {
				if (!$bl_number) {
					echo json_encode(array('status'=>0,'err'=>'请输入营业执照编号！'));
					exit();
				}
				if (intval($user_info['audit'])==1) {
					echo json_encode(array('status'=>0,'err'=>'您的认证资料正在审核中，请勿重复提交！'));
					exit();
				}
				if (!$truename || !$tel) {
					echo json_encode(array('status'=>0,'err'=>'参数错误！'));
					exit();
				}
				// if (!$user_info['bl_photo']) {
				// 	echo json_encode(array('status'=>0,'err'=>'您的营业执照还未上传！'));
				// 	exit();
				// }
				$data['bl_number'] = $bl_number;
				$data['audit'] = 1;
			}

			if (!$data) {
				echo json_encode(array('status'=>0,'err'=>'没有找到要修改的信息.'.__LINE__));
				exit();
			}
			//dump($data);exit;
			$result=M("user")->where('id='.intval($user_id))->save($data);
			//echo M("aaa_pts_user")->_sql();exit;
		    if($result){
				echo json_encode(array('status'=>1));
				exit();
			}else{
				echo json_encode(array('status'=>0,'err'=>'提交失败.'));
				exit();
			}
	}

	//*****************************
	// h5头像上传
	//******************************
	public function uploadify(){
		$imgtype = array(
		  'gif'=>'gif',
		  'png'=>'png',
		  'jpg'=>'jpg',
		  'jpeg'=>'jpeg'
		); //图片类型在传输过程中对应的头信息
		$message = $_POST['message']; //接收以base64编码的图片数据
		$filename = $_POST['filename']; //自定义文件名称
		$ftype = $_POST['filetype']; //接收文件类型
		//首先将头信息去掉，然后解码剩余的base64编码的数据
		$message = base64_decode(substr($message,strlen('data:image/'.$imgtype[strtolower($ftype)].';base64,')));
		$filename2 = $filename.".".$ftype;
		$furl = "./Data/UploadFiles/user_img/".date("Ymd");
		if (!is_dir($furl)) {
			@mkdir($furl, 0777);
		}
		$furl = $furl.'/';

		//开始写文件
		$file = fopen($furl.$filename2,"w");
		if(fwrite($file,$message) === false){
		  echo json_encode(array('status'=>0,'err'=>'failed'));
		  exit;
		}

		////图片URL地址
		$pic_url = $furl.$filename2;
		//$pic_url = "./Data/UploadFiles/user_img/20170115/0.jpeg";
		$image = new \Think\Image();
	    $image->open($pic_url);
	    // 生成一个居中裁剪为150*150的缩略图并保存为thumb.jpg
	    $image->thumb(100, 100,\Think\Image::IMAGE_THUMB_SCALE)->save($pic_url);
	    /*echo $pic_url;
	    exit();*/

	    $uid = intval($_REQUEST['uid']);
	    if (!$uid) {
	    	echo json_encode(array('status'=>0,'err'=>'登录状态异常！error'));
	    	exit();
	    }
	    //获取原来的头像链接
	    $oldpic = M('user')->where('id='.intval($uid))->getField('photo');
	    $oldpic2 = './Data/'.$oldpic;

	    $data =array();
	    $data['photo'] = "UploadFiles/user_img/".date("Ymd").'/'.$filename2;
	    $up = M('user')->where('id='.intval($uid))->save($data);
	    if ($up) {
	    	//如果原头像存在就删除
	    	if ($oldpic && file_exists($oldpic2)) {
	    		@unlink($oldpic2);
	    	}
	    	echo json_encode(array('status'=>1,'urls'=>'Data/'.$data['photo']));
	    	exit();
	    }else{
	    	echo json_encode(array('status'=>0,'err'=>'头像保存失败.'));
			exit();
	    }
		
    }

    //***************************
	//  用户 接单
	//***************************
	public function orders(){
		$uid = intval($_REQUEST['uid']);
		$sid = intval($_REQUEST['sid']);
		if (!$uid || !$sid) {
			echo json_encode(array('status'=>0,'err'=>'参数错误.'));
			exit();
		}

		$userinfo = M('user')->where('del=0 AND id='.intval($uid))->find();
		if (!$userinfo) {
			echo json_encode(array('status'=>0,'err'=>'用户信息错误.'));
			exit();
		}

		$check = M('supply')->where('id='.intval($sid))->find();
		if (intval($check['state'])!=0) {
			echo json_encode(array('status'=>0,'err'=>'供求信息异常！'));
			exit();
		}

		if (intval($check['uid'])==intval($uid)) {
			echo json_encode(array('status'=>0,'err'=>'状态异常！'));
			exit();
		}

		if (intval($userinfo['audit'])==0) {
			echo json_encode(array('status'=>0,'err'=>'只有认证企业才可以接单哦！'));
			exit();
		}

		if (intval($userinfo['audit'])!=2) {
			echo json_encode(array('status'=>0,'err'=>'企业认证审核中...'));
			exit();
		}

		$up = array();
		$up['rec_id'] = $uid;
		$up['rec_time'] = time();
		$up['state'] = 1;
		$res = M('supply')->where('id='.intval($sid))->save($up);
		if ($res) {
			echo json_encode(array('status'=>1));
			exit();
		}else{
			echo json_encode(array('status'=>0,'err'=>'接单失败，请稍后再试！'));
			exit();
		}
	}

	//***************************
	//  用户 接单
	//***************************
	public function contact(){
		$uid = intval($_REQUEST['uid']);
		$sid = intval($_REQUEST['id']);
		// if (!$uid || !$sid) {
		// 	echo json_encode(array('status'=>0,'err'=>'参数错误.'));
		// 	exit();
		// }

		// $userinfo = M('user')->where('del=0 AND id='.intval($uid))->find();
		// if (!$userinfo) {
		// 	echo json_encode(array('status'=>0,'err'=>'用户信息错误.'));
		// 	exit();
		// }

		// $check = M('supply')->where('id='.intval($sid))->find();
		// if (intval($check['state'])!=0) {
		// 	echo json_encode(array('status'=>0,'err'=>'供求信息异常！'));
		// 	exit();
		// }

		// if (intval($check['uid'])==intval($uid)) {
		// 	echo json_encode(array('status'=>0,'err'=>'状态异常！'));
		// 	exit();
		// }

		// if (intval($userinfo['audit'])==0) {
		// 	echo json_encode(array('status'=>0,'err'=>'只有认证企业才可以接单哦！'));
		// 	exit();
		// }

		// if (intval($userinfo['audit'])!=2) {
		// 	echo json_encode(array('status'=>0,'err'=>'企业认证审核中...'));
		// 	exit();
		// }

		$up = array();
		$up['rec_id'] = $uid;
		$up['rec_time'] = time();
		$up['state'] = 1;
		$res = M('supply')->where('id='.intval($sid))->save($up);
		if ($res) {
			echo json_encode(array('status'=>1));
			exit();
		}else{
			echo json_encode(array('status'=>0,'err'=>'接单失败，请稍后再试！'));
			exit();
		}
	}

	//***************************
	//  用户 发布供求
	//***************************
	public function supply(){
		$uid = intval($_REQUEST['uid']);
		$dtype = intval($_REQUEST['dtype']);
		if (!$uid || !$dtype) {
			echo json_encode(array('status'=>0,'err'=>'参数错误.'));
			exit();
		}

		$content = trim($_POST['content']);
		if (!$content) {
			echo json_encode(array('status'=>0,'err'=>'请输入供求内容.'));
			exit();
		}

		$userinfo = M('user')->where('del=0 AND id='.intval($uid))->find();
		if (!$userinfo) {
			echo json_encode(array('status'=>0,'err'=>'用户信息异常.'));
			exit();
		}

		if (intval($userinfo['audit'])>0 || intval($userinfo['type'])==2) {
			echo json_encode(array('status'=>0,'err'=>'认证企业请从后台发布供求产品！'));
			exit();
		}

		if (!$userinfo['tel']) {
			echo json_encode(array('status'=>0,'err'=>'请先去个人中心绑定您的手机号.'));
			exit();
		}

		$add = array();
		$add['uid'] = $uid;
		$add['content'] = $content;
		$add['phone'] = M('user')->where('id='.intval($uid))->getField('tel');
		$add['type'] = $dtype;
		$add['addtime'] = time();
		$res = M('supply')->add($add);
		if ($res) {
			echo json_encode(array('status'=>1));
			exit();
		}else{
			echo json_encode(array('status'=>0,'err'=>'发布失败！'));
			exit();
		}
	}

	//***************************
	//  用户 我的供应
	//***************************
	public function my_supply(){
		$uid = intval($_REQUEST['uid']);
		if (!$uid) {
			echo json_encode(array('status'=>0,'err'=>'用户信息异常.'));
			exit();
		}

		$supply = M('supply');
		$state = array('0'=>'供求中','1'=>'有人接单','2'=>'已取消','3'=>'达成合作');
        $gong = $supply->where('type=1 AND uid='.intval($uid))->order('addtime desc')->select();
        foreach ($gong as $k => $v) {
            $gong[$k]['addtime'] = date("Y-m-d",$v['addtime']);
            if ($v['rec_id']) {
            	$gong[$k]['rec_name'] = M('user')->where('id='.intval($v['rec_id']))->getField('truename');
            	$gong[$k]['rec_phone'] = M('user')->where('id='.intval($v['rec_id']))->getField('tel');
            }else{
            	$gong[$k]['rec_name'] = '暂无';
            	$gong[$k]['rec_phone'] = '暂无';
            }
            $gong[$k]['desc'] = $state[$v['state']];
        }
        echo json_encode(array('status'=>1,'list'=>$gong));
		exit();
	}

	//***************************
	//  用户 我的求购
	//***************************
	public function my_qiu(){
		$uid = intval($_REQUEST['uid']);
		if (!$uid) {
			echo json_encode(array('status'=>0,'err'=>'用户信息异常.'));
			exit();
		}

		$supply = M('supply');
		$state = array('0'=>'求购中','1'=>'已联系','2'=>'已取消','3'=>'达成合作');
        $qiu = $supply->where('type=2 AND uid='.intval($uid))->order('addtime desc')->select();
        foreach ($qiu as $k => $v) {
            $qiu[$k]['addtime'] = date("Y-m-d",$v['addtime']);
            if ($v['rec_id']) {
            	$qiu[$k]['rec_name'] = M('user')->where('id='.intval($v['rec_id']))->getField('truename');
            	$qiu[$k]['rec_phone'] = M('user')->where('id='.intval($v['rec_id']))->getField('tel');
            }else{
            	$qiu[$k]['rec_name'] = '暂无';
            	$qiu[$k]['rec_phone'] = '暂无';
            }
            $qiu[$k]['desc'] = $state[$v['state']];
        }
        echo json_encode(array('status'=>1,'list'=>$qiu));
		exit();
	}

	//***************************
	//  用户 我的供应
	//***************************
	public function shop_supply(){
		$uid = intval($_REQUEST['uid']);
		if (!$uid) {
			echo json_encode(array('status'=>0,'err'=>'用户信息异常.'));
			exit();
		}

		$supply = M('supply');
		$state = array('0'=>'供求中','1'=>'已接单','2'=>'已取消','3'=>'达成合作');
        $gong = $supply->where('type=1 AND rec_id='.intval($uid))->order('addtime desc')->select();
        foreach ($gong as $k => $v) {
            $gong[$k]['rec_time'] = date("Y-m-d",$v['rec_time']);
            $gong[$k]['rec_name'] = M('user')->where('id='.intval($v['uid']))->getField('truename');
            if (!$gong[$k]['rec_name']) {
            	$gong[$k]['rec_name'] = M('user')->where('id='.intval($v['uid']))->getField('uname');
            }
            $gong[$k]['rec_phone'] = M('user')->where('id='.intval($v['uid']))->getField('tel');
            $gong[$k]['desc'] = $state[$v['state']];
        }
        echo json_encode(array('status'=>1,'list'=>$gong));
		exit();
	}

	//***************************
	//  用户 我的求购
	//***************************
	public function shop_qiu(){
		$uid = intval($_REQUEST['uid']);
		if (!$uid) {
			echo json_encode(array('status'=>0,'err'=>'用户信息异常.'));
			exit();
		}

		$supply = M('supply');
		$state = array('0'=>'求购中','1'=>'已联系','2'=>'已取消','3'=>'达成合作');
        $qiu = $supply->where('type=2 AND rec_id='.intval($uid))->order('addtime desc')->select();
        foreach ($qiu as $k => $v) {
            $qiu[$k]['rec_time'] = date("Y-m-d",$v['rec_time']);
            $qiu[$k]['rec_name'] = M('user')->where('id='.intval($v['uid']))->getField('truename');
            if (!$qiu[$k]['rec_name']) {
            	$qiu[$k]['rec_name'] = M('user')->where('id='.intval($v['uid']))->getField('uname');
            }
            $qiu[$k]['rec_phone'] = M('user')->where('id='.intval($v['uid']))->getField('tel');
            $qiu[$k]['desc'] = $state[$v['state']];
        }
        echo json_encode(array('status'=>1,'list'=>$qiu));
		exit();
	}


	//***************************
	//  用户 我的求购
	//***************************
	public function up_state(){
		$uid = intval($_REQUEST['uid']);
		$sid = intval($_REQUEST['id']);
		if (!$uid || !$sid) {
			echo json_encode(array('status'=>0,'err'=>'参数错误.'.__LINE__));
			exit();
		}
		$ztype = trim($_REQUEST['ztype']);

		$supply = M('supply');
		$check = $supply->where('id='.intval($sid))->find();
		if (!$check) {
			echo json_encode(array('status'=>0,'err'=>'供求信息异常！.'.__LINE__));
			exit();
		}

		$up = array();
		if ($ztype=='hz') {
			$up['state'] = 3;
			if (intval($check['state'])==3) {
				echo json_encode(array('status'=>1));
				exit();
			}
		}elseif($ztype=='qx'){
			if (intval($check['state'])==2) {
				echo json_encode(array('status'=>1));
				exit();
			}
			$up['state'] = 0;
			$up['rec_id'] = 0;
		}else{
			echo json_encode(array('status'=>0,'err'=>'操作失败！.'.__LINE__));
			exit();
		}

		$res = $supply->where('id='.intval($sid))->save($up);
		if ($res) {
			echo json_encode(array('status'=>1));
			exit();
		}else{
			echo json_encode(array('status'=>0,'err'=>'操作失败！.'.__LINE__));
			exit();
		}
	}

	//***************************
	//  用户 上传营业执照
	//***************************
	public function uploadbl(){
		$uid = intval($_REQUEST['uid']);
		$check = M('user')->where('id='.intval($uid).' AND del=0')->find();
		if (!$check) {
			echo json_encode(array('status'=>0,'err'=>'会员信息异常！.'.__LINE__));
			exit();
		}

		$info = $this->upload_images($_FILES['img'],array('jpg','png','jpeg'),"bl_photo/".date(Ymd));
		if(is_array($info)) {// 上传错误提示错误信息
			$url = 'UploadFiles/'.$info['savepath'].$info['savename'];
			//修改会员图片
			$up = M('user')->where('id='.intval($uid))->save(array('bl_photo'=>$url));
			if ($up) {
				$xt = $check['bl_photo'];
				if ($uid && $xt) {
					$img_url = "Data/".$xt['bl_photo'];
					if(file_exists($img_url)) {
						@unlink($img_url);
					}
				}
				echo json_encode(array('status'=>1,'urls'));
				exit();
			}else{
				echo json_encode(array('status'=>0,'err'=>'图片保存失败！'));
				exit();
			}
		}else{
			echo json_encode(array('status'=>0,'err'=>$info));
			exit();
		}
	}
		
	/*
	*
	* 图片上传的公共方法
	*  $file 文件数据流 $exts 文件类型 $path 子目录名称
	*/
	public function upload_images($file,$exts,$path){
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =  2097152 ;// 设置附件上传大小2M
		$upload->exts      =  $exts;// 设置附件上传类型
		$upload->rootPath  =  './Data/UploadFiles/'; // 设置附件上传根目录
		$upload->savePath  =  ''; // 设置附件上传（子）目录
		$upload->saveName = time().mt_rand(100000,999999); //文件名称创建时间戳+随机数
		$upload->autoSub  = true; //自动使用子目录保存上传文件 默认为true
		$upload->subName  = $path; //子目录创建方式，采用数组或者字符串方式定义
		// 上传文件 
		$info = $upload->uploadOne($file);
		if(!$info) {// 上传错误提示错误信息
		    return $upload->getError();
		}else{// 上传成功 获取上传文件信息
			//return 'UploadFiles/'.$file['savepath'].$file['savename'];
			return $info;
		}
	}

}