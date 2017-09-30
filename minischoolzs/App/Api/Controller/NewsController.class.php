<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;
use Think\Controller;
class NewsController extends PublicController {

    //*****************************
    //  端新闻详情
    //*****************************
    public function detail(){
        $newid=intval($_REQUEST['news_id']);
        $detail=M('news')->where('id='.intval($newid))->find();
        if (!$detail) {
            echo json_encode(array('status'=>0,'err'=>'没有找到相关信息.'));
            exit();
        }

        $detail['addtime'] = date("Y-m-d",$detail['addtime']);
        $detail['content']=html_entity_decode($detail['content'], ENT_QUOTES, "utf-8");//数据库打成了concent字段
        //json加密输出
        echo json_encode(array('status'=>1,'info'=>$detail));
        exit();
    }
    //**************************
    //  app端新闻评论列表
    //***************************
    public function newsdp(){
        //print_r('expression');die();
        $id=intval($_REQUEST['news_id']);
        $dp=M('news_dp')->where('news_id='.intval($id))->select();
        $json=array();$json_arr=array();
        foreach ($dp as $k => $v) {
            $username= M('user')->where('id='.intval($v['uid']))->getField('name') ? M('user')->where('id='.intval($v['uid']))->getField('name') : M('user')->where('id='.intval($v['uid']))->getField('uname');
            $json['username']=urlencode($username);
            $json['addtime']=date('Y-m-d',$v['addtime']);
            $json['content']=$v['concent'];//数据库打成了concent字段
            $json_arr[] = $json;
        }
        //json加密输出
        //dump($json);
        echo urldecode(json_encode(array('status'=>1,'dp'=>$json_arr)));
        exit();
    }
    //*****************************
    //  app端新闻评论提交 session值
    //*****************************
    public function postdp(){
        $user_id = intval($_POST['user_id']);
        $news_id = intval($_POST['news_id']);
        $content = $_POST['content'];
        if (!$user_id) {
            echo json_encode(array('status'=>0,'err'=>'非法操作.'));
            exit();
        }
        $check_news = M('news')->where('id='.intval($news_id))->getField('id');
        if (!$check_news) {
            echo json_encode(array('status'=>0,'err'=>'该新闻已过了评论时间.'));
            exit();
        }

        if (!$content) {
            echo json_encode(array('status'=>0,'err'=>'请输入评论内容.'));
            exit();
        }

        //暂时还不知道session值，迟点再改
        $data = array();
        $data['uid'] = $user_id;
        $data['news_id'] = $news_id;
        $data['concent'] = $content;
        $data['addtime'] = time();
        $dp = M('news_dp')->add($data);
        if($dp){
            $username= M('user')->where('id='.intval($user_id))->getField('name') ? M('user')->where('id='.intval($user_id))->getField('name') : M('user')->where('id='.intval($user_id))->getField('uname');
            echo json_encode(array('status'=>1,'addtime'=>date('Y-m-d'),'username'=>$username));
            exit();
        }else{
            echo json_encode(array('status'=>0,'err'=>'操作失败，请稍后再试.'));
            exit();
        }
    }
    
}