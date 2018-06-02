<?php

namespace addons\cms\controller;

use addons\cms\model\Archives;
use addons\cms\model\Channel as ChannelModel;
use addons\cms\model\Modelx;
use think\Config;
use think\Db;

class Channel extends Base
{

    public function index()
    {
        $diyname = $this->request->param('diyname');

        if ($diyname && !is_numeric($diyname)) {
            $channel = ChannelModel::getByDiyname($diyname);
        } else {
            $id = $diyname ? $diyname : $this->request->get('id', '');
            $channel = ChannelModel::get($id);
        }        
        if (!$channel) {
            $this->error(__('No specified channel found'));
        }        
        if(!$channel['parent_id']){
            $this->redirect(self::isRect($channel['id']));
        }
        $filterlist = [];
        $orderlist = [];

        $filter = $this->request->get('filter/a', []);
        $orderby = $this->request->get('orderby', '');
        $orderway = $this->request->get('orderway', '', 'strtolower');
        $params = ['filter' => '', 'id' => $channel->id, 'diyname' => $channel->diyname];
        if ($filter)
            $params['filter'] = $filter;
        if ($orderby)
            $params['orderby'] = $orderby;
        if ($orderway)
            $params['orderway'] = $orderway;
        if ($channel['type'] === 'link') {
            $this->redirect($channel['outlink']);
        }

        $model = Modelx::get($channel['model_id']);
        if (!$model) {
            $this->error(__('No specified model found'));
        }
        $fields = [];
        foreach ($model->fields_list as $k => $v) {
            if (!$v['isfilter'] || !in_array($v['type'], ['select', 'selects', 'checkbox', 'radio', 'array']) || !$v['content_list'])
                continue;
            $fields[] = [
                'name' => $v['name'], 'title' => $v['title'], 'content' => $v['content_list']
            ];
        }
        $filter = array_intersect_key($filter, array_flip(array_column($fields, 'name')));
        foreach ($fields as $k => $v) {
            $content = [];
            $all = ['' => __('All')] + $v['content'];
            foreach ($all as $m => $n) {
                $active = ($m === '' && !isset($filter[$v['name']])) || (isset($filter[$v['name']]) && $filter[$v['name']] == $m) ? TRUE : FALSE;
                $prepare = $m === '' ? array_diff_key($filter, [$v['name'] => $m]) : array_merge($filter, [$v['name'] => $m]);
                $url = '?' . http_build_query(array_merge(['filter' => $prepare], array_diff_key($params, ['filter' => ''])));
                $content[] = ['value' => $m, 'title' => $n, 'active' => $active, 'url' => $url];
            }
            $filterlist[] = [
                'name'    => $v['name'],
                'title'   => $v['title'],
                'content' => $content,
            ];
        }

        $sortrank = [
            ['name' => 'default', 'field' => 'weigh', 'title' => __('Default')],
            ['name' => 'views', 'field' => 'views', 'title' => __('Views')],
            ['name' => 'id', 'field' => 'id', 'title' => __('Post date')],
        ];

        $orderby = $orderby && in_array($orderby, ['default', 'id', 'views']) ? $orderby : 'default';
        $orderway = $orderway ? $orderway : 'desc';
        foreach ($sortrank as $k => $v) {
            $url = '?' . http_build_query(array_merge($params, ['orderby' => $v['name'], 'orderway' => ($orderway == 'desc' ? 'asc' : 'desc')]));
            $v['active'] = $orderby == $v['name'] ? true : false;
            $v['orderby'] = $orderway;
            $v['url'] = $url;
            $orderlist[] = $v;
        }
        $orderby = $orderby == 'default' ? 'weigh' : $orderby;
        if($channel["listtpl"]=='list_develop.html'){
            $res = self::getDevelop($channel['id']);
            // print_r(count($res['content'][1]));exit;
            // print_r($res);exit;
        }elseif($channel["listtpl"]=='list_getnews.html'||$channel["listtpl"]=='list_honor.html'){
            $this->view->assign("data", $res);
            $res = Archives::alias('a')
                ->where('status', 'normal')
                ->where($filter)
                ->join($model['table'] . ' n', 'a.id=n.id', 'LEFT')
                ->field('a.*,n.*')
                ->field('id,content', true, config('database.prefix') . $model['table'], 'n')
                ->where('channel_id', $channel['id'])
                ->order($orderby, $orderway)
                ->paginate($channel['pagesize'], false, ['type' => '\\addons\\cms\\library\\Bootstrap']);
            $res->appends($params);
        }else{
            $res = self::getData($channel['id'],$model['table']);
        }  
        // echo preg_replace('/\.html$/', '', $channel["{$channel['type']}tpl"]);exit;
        $this->view->assign("data", $res);
        $this->view->assign("flagId", $channel['parent_id']?$channel['parent_id']:$channel['id']);
        $this->view->assign("__FILTERLIST__", $filterlist);
        $this->view->assign("__ORDERLIST__", $orderlist);
        // $this->view->assign("__PAGELIST__", $pagelist);
        $this->view->assign("__CHANNEL__", $channel);
        Config::set('cms.title', $channel['name']);
        Config::set('cms.keywords', $channel['keywords']);
        Config::set('cms.description', $channel['description']);
        $this->view->assign("__FLAG__", $channel['parent_id']?$channel['diyname']:self::getData($channel['parent_id'],$channel['id'],$model['table'],1));
        $url = is_mobile()?'/wap/':'/';
        return $this->view->fetch($url . preg_replace('/\.html$/', '', $channel["{$channel['type']}tpl"]));
    }

    private static function getData($id,$table)
    {
        $reslut = Archives::alias('a')->join($table.' b','a.id = b.id','LEFT')->field('a.*,b.*')->where("a.channel_id",$id)->order('a.weigh Desc')->select();
        $reslut = collection($reslut)->toArray();
        if(empty($reslut)){
            return [];
        }
        $keys = ['image_1','title_1','content_1','image_2','title_2','content_2','image_3','title_3','content_3','image_4','title_4','content_4','image_5','title_5','content_5','image_6','title_6','content_6','image_7','title_7','content_7'];
        $arr = [];
        foreach ($reslut[0] as $k => $v) {
           if(in_array($k, $keys)&&$v){
                $kk = substr($k, 0,strlen($k)-2);
                $ks = substr($k, -1);
                $arr['extends'][$ks][$kk] = $v;
           }else{
                $arr[$k] = $v;
           }
        }
        return $arr;
    }
    private static function getDevelop($id)
    {
        $res = Archives::where('channel_id',$id)->field("FROM_UNIXTIME(publishtime,'%Y') years")->group("years")->order('publishtime Desc')->select();

        $res = collection($res)->toArray();
        $arr['years'] = $res;
        foreach ($res as $k => $v) {
           $arr['content'][] = Archives::alias('a')->join('news b','a.id = b.id')->field('a.id,a.title,a.description,a.publishtime,a.image')->where("FROM_UNIXTIME(a.publishtime,'%Y') = $v[years]")->where('channel_id',$id)->order('a.publishtime Desc')->select();
        }
        return $arr;
    }
    private static function getHonor($id)
    {
        $res = Archives::where('channel_id',$id)->select();
    }
    private static function isRect($id)
    {
        if(!$parent){
            $res = ChannelModel::alias('a')->join('archives b','a.id = b.channel_id','INNER')->where("a.parent_id",$id)->field('a.diyname')->order('a.weigh Desc')->select();
            return $res[0]['diyname']?'/cms/c/'.$res[0]['diyname']:'/cms';
            /*foreach ($res as $k => $v) {
               $flag = Archives::where('channel_id',$v['id'])->find();
               if($flag){}
            }
            $id = $res['id'];
            if($choose){
                return $res['diyname'];
            }*/
        }
    }
}
