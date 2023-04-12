<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2099 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://opensource.org/licenses/mit-license.php )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\service;

use think\facade\Db;
use app\service\SystemService;
use app\service\GoodsService;
use app\service\BrandService;
use app\service\ResourcesService;
use app\service\GoodsCategoryService;

/**
 * 搜索服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class SearchService
{
    /**
     * 排序列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-01
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function SearchMapOrderByList($params = [])
    {
        $ov = empty($params['ov']) ? ['default'] : explode('-', $params['ov']);
        $data = MyLang('common_search_order_by_list');
        foreach($data as &$v)
        {
            // 是否选中
            $v['is_active'] = ($ov[0] == $v['type']) ? 1 : 0;

            // url
            $temp_ov = '';
            if($v['type'] == 'default')
            {
                $temp_params = $params;
                unset($temp_params['ov']);
            } else {
                // 类型
                if($ov[0] == $v['type'])
                {
                    $v['value'] = ($ov[1] == 'desc') ? 'asc' : 'desc';
                }

                // 参数值
                $temp_ov = $v['type'].'-'.$v['value'];
                $temp_params = array_merge($params, ['ov'=>$temp_ov]);
            }
            $v['url'] = MyUrl('index/search/index', $temp_params);
        }
        return $data;
    }

    /**
     * 搜素条件处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-30
     * @desc    description
     * @param   [array]          $data   [数据列表]
     * @param   [string]         $pid    [参数字段]
     * @param   [string]         $did    [数据字段]
     * @param   [array]          $params [输入参数]
     * @param   [array]          $ext    [扩展数据]
     */
    public static function SearchMapHandle($data, $pid, $did, $params, $ext = [])
    {
        // 移除分页
        unset($params['page']);

        // ascii字段处理
        $is_ascii = isset($ext['is_ascii']) && $ext['is_ascii'] == true;
        $field = empty($ext['field']) ? 'value' : $ext['field'];
        foreach($data as &$v)
        {
            // 是否转ascii处理主键字段
            if($is_ascii && !empty($field) && isset($v[$field]))
            {
                $v[$did] = StrToAscii($v[$field]);
            }
            $temp_params = $params;
            if(isset($v[$did]))
            {
                if(isset($params[$pid]) && $params[$pid] == $v[$did])
                {
                    unset($temp_params[$pid]);
                } else {
                    $temp_params = array_merge($params, [$pid=>$v[$did]]);
                }
            }
            $v['url'] = MyUrl('index/search/index', $temp_params);
            $v['is_active'] = (isset($params[$pid]) && isset($v[$did]) && $params[$pid] == $v[$did]) ? 1 : 0;
        }
        return $data;
    }

    /**
     * 获取商品列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-07
     * @desc    description
     * @param   [array]          $map    [搜素条件]
     * @param   [array]          $params [输入参数]
     */
    public static function GoodsList($map, $params = [])
    {
        // 返回格式
        $result = [
            'page_start'    => 0,
            'page_size'     => 0,
            'page'          => 1,
            'page_total'    => 0,
            'total'         => 0,
            'data'          => [],
        ];

        // 搜索条件
        $order_by = $map['order_by'];
        $where_base = $map['base'];
        $where_keywords = $map['keywords'];
        $where_screening_price = $map['screening_price'];

        // 分页计算
        $field = 'g.*';
        $result['page'] = max(1, isset($params['page']) ? intval($params['page']) : 1);
        $result['page_size'] = empty($params['page_size']) ? MyC('home_search_limit_number', 20, true) : intval($params['page_size']);
        // 数量不能超过500
        if($result['page_size'] > 500)
        {
            $result['page_size'] = 500;
        }
        $result['page_start'] = intval(($result['page']-1)*$result['page_size']);

        // 搜索商品列表读取前钩子
        $hook_name = 'plugins_service_search_goods_list_begin';
        MyEventTrigger($hook_name, [
            'hook_name'                 => $hook_name,
            'is_backend'                => true,
            'params'                    => &$params,
            'where_base'                => &$where_base,
            'where_keywords'            => &$where_keywords,
            'where_screening_price'     => &$where_screening_price,
            'field'                     => &$field,
            'order_by'                  => &$order_by,
            'page'                      => &$result['page'],
            'page_start'                => &$result['page_start'],
            'page_size'                 => &$result['page_size'],
        ]);

        // 获取商品总数
        $result['total'] = (int) Db::name('Goods')->alias('g')->join('goods_category_join gci', 'g.id=gci.goods_id')->where($where_base)->where(function($query) use($where_keywords) {
            self::SearchKeywordsWhereJoinType($query, $where_keywords);
        })->where(function($query) use($where_screening_price) {
            $query->whereOr($where_screening_price);
        })->count('DISTINCT g.id');

        // 获取商品列表
        if($result['total'] > 0)
        {
            // 查询数据
            $data = Db::name('Goods')->alias('g')->join('goods_category_join gci', 'g.id=gci.goods_id')->field($field)->where($where_base)->where(function($query) use($where_keywords) {
                self::SearchKeywordsWhereJoinType($query, $where_keywords);
            })->where(function($query) use($where_screening_price) {
                $query->whereOr($where_screening_price);
            })->group('g.id')->order($order_by)->limit($result['page_start'], $result['page_size'])->select()->toArray();

            // 数据处理
            $params['is_spec'] = true;
            $params['is_cart'] = true;
            $goods = GoodsService::GoodsDataHandle($data, $params);

            // 返回数据
            $result['data'] = $goods['data'];
            $result['page_total'] = ceil($result['total']/$result['page_size']);
        }
        return DataReturn(MyLang('handle_success'), 0, $result);
    }

    /**
     * 关键字搜索关系类型
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-04-02
     * @desc    description
     * @param   [object]          $query          [查询对象]
     * @param   [array]           $where_keywords [搜索关键字]
     */
    public static function SearchKeywordsWhereJoinType($query, $where_keywords)
    {
        // 搜索关键字默认或的关系
        $join = 'whereOr';

        // 是否开启并且关系
        if(MyC('home_search_is_keywords_where_and') == 1)
        {
            $join = 'where';
        }

        // 条件设置
        $query->$join($where_keywords);
    }

    /**
     * 搜索条件处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-01-08
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function SearchWhereHandle($params = [])
    {
        // 搜索商品条件处理钩子
        $hook_name = 'plugins_service_search_goods_list_where';
        MyEventTrigger($hook_name, [
            'hook_name'     => $hook_name,
            'is_backend'    => true,
            'params'        => &$params,
        ]);

        // 基础条件
        $where_base = [
            ['g.is_delete_time', '=', 0],
            ['g.is_shelves', '=', 1]
        ];

        // 关键字
        $where_keywords = [];
        if(!empty($params['wd']))
        {
            // WEB端则处理关键字
            if(APPLICATION_CLIENT_TYPE == 'pc')
            {
                $params['wd'] = AsciiToStr($params['wd']);
            }
            $keywords_fields = 'g.title|g.simple_desc|g.model';
            if(MyC('home_search_is_keywords_seo_fields') == 1)
            {
                $keywords_fields .= '|g.seo_title|g.seo_keywords|g.seo_desc';
            }
            $keywords = explode(' ', $params['wd']);
            foreach($keywords as $kv)
            {
                $where_keywords[] = [$keywords_fields, 'like', '%'.$kv.'%'];
            }
        }

        // 品牌
        // 不存在搜索品牌的时候则看是否指定品牌
        if(!empty($params['brand_ids']))
        {
            if(!is_array($params['brand_ids']))
            {
                $params['brand_ids'] = (substr($params['brand_ids'], 0, 1) == '{') ? json_decode(htmlspecialchars_decode($params['brand_ids']), true) : explode(',', $params['brand_ids']);
            }
            if(!empty($params['brand_ids']))
            {
                $where_base[] = ['g.brand_id', 'in', array_unique($params['brand_ids'])];
            }
        }
        // 指定品牌
        if(!empty($params['brand']))
        {
            $where_base[] = ['g.brand_id', 'in', [intval($params['brand'])]];
        }
        // web端
        if(!empty($params['bid']))
        {
            $where_base[] = ['g.brand_id', 'in', [intval($params['bid'])]];
        }

        // 分类id
        // 不存在搜索分类的时候则看是否指定分类
        if(!empty($params['category_ids']))
        {
            if(!is_array($params['category_ids']))
            {
                $params['category_ids'] = (substr($params['category_ids'], 0, 1) == '{') ? json_decode(htmlspecialchars_decode($params['category_ids']), true) : explode(',', $params['category_ids']);
            }
            if(!empty($params['category_ids']))
            {
                $ids = GoodsCategoryService::GoodsCategoryItemsIds($params['category_ids'], 1);
                $where_base[] = ['gci.category_id', 'in', $ids];
            }
        } else {
            if(!empty($params['category_id']))
            {
                $ids = GoodsCategoryService::GoodsCategoryItemsIds([intval($params['category_id'])], 1);
                $where_base[] = ['gci.category_id', 'in', $ids];
            }
        }
        // web端
        if(!empty($params['cid']))
        {
            $ids = GoodsCategoryService::GoodsCategoryItemsIds([intval($params['cid'])], 1);
            $where_base[] = ['gci.category_id', 'in', $ids];
        }

        // 筛选价格
        $map_price = [];
        $where_screening_price = [];
        if(!empty($params['screening_price_values']))
        {
            if(!is_array($params['screening_price_values']))
            {
                $map_price = (substr($params['screening_price_values'], 0, 1) == '{') ? json_decode(htmlspecialchars_decode($params['screening_price_values']), true) : explode(',', $params['screening_price_values']);
            }
        }
        // web端
        if(!empty($params['peid']))
        {
            $temp_price = Db::name('ScreeningPrice')->where(['is_enable'=>1, 'id'=>intval($params['peid'])])->field('min_price,max_price')->find();
            if(!empty($temp_price))
            {
                $map_price[] = implode('-', $temp_price);
            }
        }
        if(!empty($map_price))
        {
            foreach($map_price as $v)
            {
                $temp = explode('-', $v);
                if(count($temp) == 2)
                {
                    // 最小金额等于0、最大金额大于0
                    if(empty($temp[0]) && !empty($temp[1]))
                    {
                        $where_screening_price[] = [
                            ['min_price', '<=', $temp[1]],
                        ];

                    // 最小金额大于0、最大金额大于0
                    // 最小金额等于0、最大金额等于0
                    } elseif((!empty($temp[0]) && !empty($temp[1])) || (empty($temp[0]) && empty($temp[1])))
                    {
                        $where_screening_price[] = [
                            ['min_price', '>=', $temp[0]],
                            ['min_price', '<=', $temp[1]],
                        ];

                    // 最小金额大于0、最大金额等于0
                    } elseif(!empty($temp[0]) && empty($temp[1]))
                    {
                        $where_screening_price[] = [
                            ['min_price', '>=', $temp[0]],
                        ];
                    }
                }
            }
        }

        // 商品参数、属性
        $map_params = [];
        if(!empty($params['goods_params_values']))
        {
            if(!is_array($params['goods_params_values']))
            {
                $map_params = (substr($params['goods_params_values'], 0, 1) == '{') ? json_decode(htmlspecialchars_decode($params['goods_params_values']), true) : explode(',', $params['goods_params_values']);
            }
        }
        if(!empty($params['psid']))
        {
            $map_params[] = AsciiToStr($params['psid']);
        }
        if(!empty($map_params))
        {
            $ids = Db::name('GoodsParams')->where(['value'=>$map_params, 'type'=>self::SearchParamsWhereTypeValue()])->column('goods_id');
            if(!empty($ids))
            {
                $where_base[] = ['g.id', 'in', $ids];
            }
        }

        // 商品规格
        $map_spec = [];
        if(!empty($params['goods_spec_values']))
        {
            if(!is_array($params['goods_spec_values']))
            {
                $map_spec = (substr($params['goods_spec_values'], 0, 1) == '{') ? json_decode(htmlspecialchars_decode($params['goods_spec_values']), true) : explode(',', $params['goods_spec_values']);
            }
        }
        if(!empty($params['scid']))
        {
            $map_spec[] = AsciiToStr($params['scid']);
        }
        if(!empty($map_spec))
        {
            $ids = Db::name('GoodsSpecValue')->where(['value'=>$map_spec])->column('goods_id');
            if(!empty($ids))
            {
                $where_base[] = ['g.id', 'in', $ids];
            }
        }

        // 排序
        $order_by = 'g.access_count desc, g.sales_count desc, g.id desc';
        if(!empty($params['ov']))
        {
            // 数据库字段映射关系
            $fields = [
                'sales'     => 'g.sales_count',
                'access'    => 'g.access_count',
                'price'     => 'g.min_price',
                'new'       => 'g.id',
            ];

            // 参数判断
            $temp = explode('-', $params['ov']);
            if(count($temp) == 2 && $temp[0] != 'default' && array_key_exists($temp[0], $fields) && in_array($temp[1], ['desc', 'asc']))
            {
                $order_by = $fields[$temp[0]].' '.$temp[1];
            }
        } else {
            if(!empty($params['order_by_type']) && !empty($params['order_by_field']) && $params['order_by_field'] != 'default')
            {
                $order_by = 'g.'.$params['order_by_field'].' '.$params['order_by_type'];
            }
        }

        // 是否存在搜索条件
        $is_map = (count($where_base) > 2 || !empty($where_keywords) || !empty($where_screening_price) || !empty($params['ov'])) ? 1 : 0;
        return [
            'base'              => $where_base,
            'keywords'          => $where_keywords,
            'screening_price'   => $where_screening_price,
            'order_by'          => $order_by,
            'is_map'            => $is_map,
        ];
    }

    /**
     * 参数搜索条件类型
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-04-11
     * @desc    description
     */
    public static function SearchParamsWhereTypeValue()
    {
        // 获取配置
        $value = MyC('home_search_params_type');
        if(empty($value))
        {
            $value = [2];
        }

        // 是否为数组
        if(!is_array($value))
        {
            $value = explode(',', $value);
        }

        return $value;
    }

    /**
     * 搜索记录添加
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-10-21T00:37:44+0800
     * @param   [array]          $params [输入参数]
     */
    public static function SearchAdd($params = [])
    {
        // 是否增加搜索记录
        if(MyC('home_search_history_record', 0) == 1)
        {
            // 排序
            $ov_arr = empty($params['ov']) ? '' : explode('-', $params['ov']);
            if(empty($ov_arr) && !empty($params['order_by_type']) && !empty($params['order_by_field']))
            {
                $ov_arr = [$params['order_by_field'], $params['order_by_type']];
            }

            // 结果仅保留商品id
            if(!empty($params['search_result_data']) && is_array($params['search_result_data']) && !empty($params['search_result_data']['data']))
            {
                $params['search_result_data']['data'] = array_column($params['search_result_data']['data'], 'id');
            }

            // 日志数据
            $data = [
                'user_id'           => isset($params['user_id']) ? intval($params['user_id']) : 0,
                'keywords'          => empty($params['wd']) ? '' : $params['wd'],
                'order_by_field'    => empty($ov_arr) ? '' : $ov_arr[0],
                'order_by_type'     => empty($ov_arr) ? '' : $ov_arr[1],
                'search_result'     => empty($params['search_result_data']) ? '' : json_encode($params['search_result_data'], JSON_UNESCAPED_UNICODE),
                'ymd'               => date('Ymd'),
                'add_time'          => time(),
            ];

            // 参数处理
            $field_arr = [
                'brand_ids'             => ['brand_ids', 'brand', 'bid'],
                'category_ids'          => ['category_ids', 'category_id', 'cid'],
                'screening_price_values'=> ['screening_price_values', 'peid'],
                'goods_params_values'   => ['goods_params_values', 'psid'],
                'goods_spec_values'     => ['goods_spec_values', 'scid'],
            ];
            foreach($field_arr as $k=>$v)
            {
                $item = [];
                foreach($v as $vs)
                {
                    if(!empty($params[$vs]))
                    {
                        // 价格区间
                        if($vs == 'peid')
                        {
                            $temp_price = Db::name('ScreeningPrice')->where(['is_enable'=>1, 'id'=>intval($params[$vs])])->field('min_price,max_price')->find();
                            $params[$vs] = empty($temp_price) ? '' : implode('-', $temp_price);
                        }

                        // Ascii处理
                        if(in_array($vs, ['psid', 'scid']))
                        {
                            $params[$vs] = AsciiToStr($params[$vs]);
                        }

                        // 合并参数
                        $tv = (substr($params[$vs], 0, 1) == '{') ? json_decode(htmlspecialchars_decode($params[$vs]), true) : $params[$vs];
                        if($tv !== '' && $tv !== null)
                        {
                            $item = array_merge($item, is_array($tv) ? $tv : [$tv]);
                        }
                    }
                }
                $data[$k] = empty($item) ? '' : (is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : $item);
            }
            Db::name('SearchHistory')->insert($data);
        }
    }

    /**
     * 搜索关键字列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-01-03
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function SearchKeywordsList($params = [])
    {
        $key = SystemService::CacheKey('shopxo.cache_search_keywords_key');
        $data = MyCache($key);
        if($data === null || MyEnv('app_debug'))
        {
            switch(intval(MyC('home_search_keywords_type', 0)))
            {
                case 1 :
                    $data = Db::name('SearchHistory')->where([['keywords', '<>', '']])->group('keywords')->limit(10)->column('keywords');
                    break;
                case 2 :
                    $keywords = MyC('home_search_keywords', '', true);
                    if(!empty($keywords))
                    {
                        $data = explode(',', $keywords);
                    }
                    break;
            }

            // 没数据则赋空数组值
            if(empty($data))
            {
                $data = [];
            }

            // 存储缓存
            MyCache($key, $data, 180);
        }
        return $data;
    }

    /**
     * 分类下品牌列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-08-29
     * @desc    description
     * @param   [array]          $map    [搜索条件]
     * @param   [array]          $params [输入参数]
     */
    public static function CategoryBrandList($map, $params = [])
    {
        $data = [];
        if(MyC('home_search_is_brand', 0) == 1)
        {
            // 基础条件
            $brand_where = [
                ['is_enable', '=', 1],
            ];

            // 仅搜索关键字相关的品牌
            if(!empty($map['keywords']))
            {
                $where_keywords = $map['keywords'];
                $ids = Db::name('Goods')->alias('g')->join('goods_category_join gci', 'g.id=gci.goods_id')->where(function($query) use($where_keywords) {
                    self::SearchKeywordsWhereJoinType($query, $where_keywords);
                })->group('g.brand_id')->column('g.brand_id');
                if(!empty($ids))
                {
                    $brand_where[] = ['id', 'in', array_unique($ids)];
                }
            }

            // 仅获取已关联商品的品牌
            $where = [
                ['is_shelves', '=', 1],
                ['is_delete_time', '=', 0],
                ['brand_id', '>', 0],
            ];
            $ids = Db::name('Goods')->where($where)->column('distinct brand_id');
            if(!empty($ids))
            {
                $brand_where[] = ['id', 'in', $ids];
            }

            // 获取品牌列表
            $data_params = [
                'field'     => 'id,name,logo,website_url',
                'where'     => $brand_where,
                'm'         => 0,
                'n'         => 0,
            ];
            $ret = BrandService::BrandList($data_params);
            $data = empty($ret['data']) ? [] : $ret['data'];
        }
        return $data;
    }

    /**
     * 根据分类id获取下级列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-08-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function GoodsCategoryList($params = [])
    {
        $data = [];
        if(MyC('home_search_is_category', 0) == 1)
        {
            $cid = empty($params['category_id']) ? (empty($params['cid']) ? 0 : intval($params['cid'])) : intval($params['category_id']);
            $pid = empty($cid) ? 0 : Db::name('GoodsCategory')->where(['id'=>$cid])->value('pid');
            $where = [
                ['pid', '=', intval($pid)],
            ];
            $data = GoodsCategoryService::GoodsCategoryList(['where'=>$where, 'field'=>'id,name']);
        }
        return $data;
    }

    /**
     * 获取商品价格筛选列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-07
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function ScreeningPriceList($params = [])
    {
        $data = [];
        if(MyC('home_search_is_price', 0) == 1)
        {
            $data = Db::name('ScreeningPrice')->field('id,name,min_price,max_price')->where(['is_enable'=>1])->order('sort asc')->select()->toArray();
        }
        return $data;
    }

    /**
     * 搜索商品参数列表、去重
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-01-08
     * @desc    description
     * @param   [array]          $map    [搜素条件]
     * @param   [array]          $params [输入参数]
     */
    public static function SearchGoodsParamsValueList($map, $params = [])
    {
        $data = [];
        if(MyC('home_search_is_params', 0) == 1)
        {
            // 搜索条件
            $where_base = $map['base'];
            $where_keywords = $map['keywords'];
            $where_screening_price = $map['screening_price'];

            // 仅搜索基础参数
            $where_base[] = ['gp.type', 'in', self::SearchParamsWhereTypeValue()];

            // 一维数组、参数值去重
            $data = Db::name('Goods')->alias('g')->join('goods_category_join gci', 'g.id=gci.goods_id')->join('goods_params gp', 'g.id=gp.goods_id')->where($where_base)->where(function($query) use($where_keywords) {
                self::SearchKeywordsWhereJoinType($query, $where_keywords);
            })->where(function($query) use($where_screening_price) {
                $query->whereOr($where_screening_price);
            })->group('gp.value')->field('gp.value')->select()->toArray();
        }
        return $data;
    }

    /**
     * 搜索商品规格列表、去重
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-01-08
     * @desc    description
     * @param   [array]           $map    [搜素条件]
     * @param   [array]           $params [输入参数]
     */
    public static function SearchGoodsSpecValueList($map, $params = [])
    {
        $data = [];
        if(MyC('home_search_is_spec', 0) == 1)
        {
            // 搜索条件
            $where_base = $map['base'];
            $where_keywords = $map['keywords'];
            $where_screening_price = $map['screening_price'];

            // 一维数组、参数值去重
            $data = Db::name('Goods')->alias('g')->join('goods_category_join gci', 'g.id=gci.goods_id')->join('goods_spec_value gsv', 'g.id=gsv.goods_id')->where($where_base)->where(function($query) use($where_keywords) {
                self::SearchKeywordsWhereJoinType($query, $where_keywords);
            })->where(function($query) use($where_screening_price) {
                $query->whereOr($where_screening_price);
            })->group('gsv.value')->field('gsv.value')->select()->toArray();
        }
        return $data;
    }

    /**
     * 搜索条件基础数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-01-11
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function SearchMapInfo($params = [])
    {
        // 分类
        $category = null;
        $cid = empty($params['category_id']) ? (empty($params['cid']) ? 0 : intval($params['cid'])) : intval($params['category_id']);
        if(!empty($cid))
        {
            $category = GoodsCategoryService::GoodsCategoryRow(['id'=>$cid, 'field'=>'name,vice_name,describe,seo_title,seo_keywords,seo_desc']);
        }

        // 品牌
        $brand = null;
        $bid = empty($params['brand']) ? (empty($params['bid']) ? 0 : intval($params['bid'])) : intval($params['brand']);
        if(!empty($bid))
        {
            $data_params = [
                'field'     => 'id,name,describe,logo,website_url,seo_title,seo_keywords,seo_desc',
                'where'     => [
                    ['id', '=', $bid]
                ],
                'm'         => 0,
                'n'         => 1,
            ];
            $ret = BrandService::BrandList($data_params);
            if(!empty($ret['data']) && !empty($ret['data'][0]))
            {
                $brand = $ret['data'][0];
            }
        }

        return [
            'category'  => empty($category) ? null : $category,
            'brand'     => empty($brand) ? null : $brand,
        ];
    }

    /**
     * 搜索面包屑导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-07-06
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function SearchBreadcrumbData($params = [])
    {
        // 默认首页
        $result = [
            [
                'type'  => 0,
                'name'  => MyLang('home_title'),
                'url'   => SystemService::HomeUrl(),
                'icon'  => 'am-icon-home',
            ],
        ];
        // 商品分类
        $temp_data = [];
        if(!empty($params['cid']))
        {
            // 一级
            $where = [
                ['id', '=', intval($params['cid'])],
                ['is_enable', '=', 1],
            ];
            $category = Db::name('GoodsCategory')->where($where)->field('id,pid,name')->find();
            if(!empty($category))
            {
                $where = [
                    ['pid', '=', $category['pid']],
                    ['is_enable', '=', 1],
                ];
                $category_list = Db::name('GoodsCategory')->where($where)->field('id,pid,name')->select()->toArray();
                if(!empty($category_list))
                {
                    array_unshift($temp_data, [
                        'id'    => $category['id'],
                        'name'  => $category['name'],
                        'type'  => 1,
                        'data'  => array_map(function($v)
                                    {
                                        $v['url'] = MyUrl('index/search/index', ['cid'=>$v['id']]);
                                        return $v;
                                    }, $category_list),
                    ]);

                    // 二级
                    $where = [
                        ['id', '=', $category['pid']],
                        ['is_enable', '=', 1],
                    ];
                    $category = Db::name('GoodsCategory')->where($where)->field('id,pid,name')->find();
                    if(!empty($category))
                    {
                        $where = [
                            ['pid', '=', $category['pid']],
                            ['is_enable', '=', 1],
                        ];
                        $category_list = Db::name('GoodsCategory')->where($where)->field('id,pid,name')->select()->toArray();
                        if(!empty($category_list))
                        {
                            array_unshift($temp_data, [
                                'id'    => $category['id'],
                                'name'  => $category['name'],
                                'type'  => 1,
                                'data'  => array_map(function($v)
                                            {
                                                $v['url'] = MyUrl('index/search/index', ['cid'=>$v['id']]);
                                                return $v;
                                            }, $category_list),
                            ]);

                            // 三级
                            $where = [
                                ['id', '=', $category['pid']],
                                ['is_enable', '=', 1],
                            ];
                            $category = Db::name('GoodsCategory')->where($where)->field('id,pid,name')->find();
                            if(!empty($category))
                            {
                                $where = [
                                    ['pid', '=', $category['pid']],
                                    ['is_enable', '=', 1],
                                ];
                                $category_list = Db::name('GoodsCategory')->where($where)->field('id,pid,name')->select()->toArray();
                                if(!empty($category_list))
                                {
                                    array_unshift($temp_data, [
                                        'id'    => $category['id'],
                                        'name'  => $category['name'],
                                        'type'  => 1,
                                        'data'  => array_map(function($v)
                                                    {
                                                        $v['url'] = MyUrl('index/search/index', ['cid'=>$v['id']]);
                                                        return $v;
                                                    }, $category_list),
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }

        // 品牌、价格、关键字、属性、规格
        // 集合名称
        $temp_name = [];

        // 品牌
        $bid = empty($params['bid']) ? (empty($params['brand']) ? 0 : intval($params['brand'])) : intval($params['bid']);
        if(!empty($bid))
        {
            $brand = Db::name('Brand')->where(['id'=>$bid])->field('id,name')->find();
            if(!empty($brand))
            {
                $temp_name[] = $brand['name'];
            }
        }

        // 价格区间
        if(!empty($params['peid']))
        {
            $price = Db::name('ScreeningPrice')->where(['id'=>intval($params['peid'])])->field('id,name')->find();
            if(!empty($price))
            {
                $temp_name[] = $price['name'];
            }
        }

        // 搜索关键字
        if(!empty($params['wd']))
        {
            $temp_name[] = $params['wd'];
        }

        // 属性
        if(!empty($params['psid']))
        {
            $temp_name[] = AsciiToStr($params['psid']);
        }

        // 规格
        if(!empty($params['scid']))
        {
            $temp_name[] = AsciiToStr($params['scid']);
        }
        if(!empty($temp_name))
        {
            $temp_data[] = [
                'type'  => 0,
                'name'  => implode(' / ', $temp_name).MyLang('common_service.search.search_breadcrumb_result_last_text'),
            ];
        }

        // 数据合并
        if(!empty($temp_data))
        {
            $result = array_merge($result, $temp_data);
        }
        return $result;
    }
}
?>