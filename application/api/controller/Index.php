<?php

namespace app\api\controller;

use app\common\controller\Api;
use addons\cms\model\Archives;

/**
 * 首页接口
 */
class Index extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     * 
     */
    public function index()
    {
        $this->success('请求成功');
    }
    public function getgetDevelopInfo()
    {
        $id = $this->request->request('id');
        if(is_numeric($id)){
           $info = Archives::alias('a')->join('news b','a.id = b.id')->field('a.*,FROM_UNIXTIME(publishtime,"%Y年%m月%d日")  datetime,b.*')->where("a.id",$id)->order('a.publishtime Desc')->find();
           // return json_encode(['status'=>200,'info'=>collection()->toArray()]);
           $this->success('',$info);
        }
    }
    public function getInfo()
    {
        $id = $this->request->request('id');
        if(is_numeric($id)){
           $info = Archives::alias('a')->join('news b','a.id = b.id')->field('a.*,FROM_UNIXTIME(publishtime,"%Y年%m月%d日")  datetime,b.*')->where("a.id",$id)->find();
           // return json_encode(['status'=>200,'info'=>collection()->toArray()]);
           $this->success('',$info);
        }
    }

    public function addJoin()
    {
        if($this->request->isPost()){
            $post = $this->request->request();
            if(!$post['name']||!$post['mobile']){
              $this->error('a');
            }
            $res = model('joinus')->save($post);
            if($res){
              $this->success();
            }
            $this->error('b');
        }
        $this->error('aq');
    }

}
