<?php

namespace addons\cms\model;

use think\Model;
/**
 * 文章模型
 */
class Banner Extends Model
{

    protected $name = "banner";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    //判断位置
    public function getType($name,$mod,$num=5)
    {
    	$code = 1;
    	if($mod != 'index'){
    		$where = is_numeric($name)?['a.id'=>$name]:['a.diyname'=>$name];
    		$res = ($mod == 'archives')?Archives::alias('a')->join('he_channel b','a.channel_id = b.id')->field('b.channeltpl,b.listtpl')->where($where)->find():Channel::alias('a')->where($where)->find();
    		$tpl = $res['listtpl']?substr($res['listtpl'],5):substr($res['channeltpl'],5);
    		$arr = explode('.', $tpl);
    		switch ($arr[0]) {
    			case 'product':
    				$code = 2;
    				break;
    			case 'about':
    				$code = 3;
    				break;
    			case 'join':
    				$code = 4;
    				break;
    			case 'contway':
    				$code = 5;
    				break;    			
    			default:
    				$code = 1;
    				break;
    		}
    	}    
    	return $this->where('type',$code)->limit($num)->select();	
    }
}