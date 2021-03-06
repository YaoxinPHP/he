<?php

namespace addons\cms\model;

use think\Model;

/**
 * 标签模型
 */
class Block Extends Model
{

    protected $name = "block";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];

    //自定义初始化
    protected static function init()
    {
        
    }

    public static function getBlockList($params)
    {
        $name = empty($params['name']) ? '' : $params['name'];
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $row = empty($params['row']) ? 10 : (int) $params['row'];
        $orderby = empty($params['orderby']) ? 'nums' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $limit = empty($params['limit']) ? $row : $params['limit'];
        $cache = !isset($params['cache']) ? true : (int) $params['cache'];
        $imgwidth = empty($params['imgwidth']) ? '' : $params['imgwidth'];
        $imgheight = empty($params['imgheight']) ? '' : $params['imgheight'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';

        $where = [];
        if ($name !== '')
        {
            $where['name'] = $name;
        }
        $order = $orderby == 'rand' ? 'rand()' : (in_array($orderby, ['name', 'id', 'createtime', 'updatetime']) ? "{$orderby} {$orderway}" : "id {$orderway}");

        $list = self::where($where)
                ->where($condition)
                ->order($order)
                ->limit($limit)
                ->cache($cache)
                ->select();
        self::render($list, $imgwidth, $imgheight);
        return $list;
    }

    public static function render(&$list, $imgwidth, $imgheight)
    {
        $width = $imgwidth ? 'width="' . $imgwidth . '"' : '';
        $height = $imgheight ? 'height="' . $imgheight . '"' : '';
        foreach ($list as $k => &$v)
        {
            $v['hasimage'] = $v->getData('image') ? true : false;
            $v['textlink'] = '<a href="' . $v['url'] . '">' . $v['title'] . '</a>';
            $v['imglink'] = '<a href="' . $v['url'] . '"><img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' /></a>';
            $v['img'] = '<img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' />';
        }
        return $list;
    }

    public static function getBlockContent($params)
    {
        $field = isset($params['id']) ? 'id' : 'name';
        $value = isset($params[$field]) ? $params[$field] : '';
        $cache = !isset($params['cache']) ? true : (int) $params['cache'];
        $row = self::where($field, $value)
                ->cache($cache)
                ->find();
        $result = '';
        if ($row)
        {
            if ($row['content'])
            {
                $result = $row['content'];
            }
            else if ($row['image'])
            {
                $result = '<img src="' . cdnurl($row['image']) . '" class="img-responsive"/>';
            }
            else
            {
                $result = $row['title'];
            }
            if ($row['url'] && !$row['content'])
            {
                $result = $row['url'] ? '<a href="' . (preg_match("/^https?:\/\/(.*)/i", $row['url']) ? $row['url'] : url($row['url'])) . '">' . $result . '</a>' : $result;
            }
        }
        return $result;
    }

}
