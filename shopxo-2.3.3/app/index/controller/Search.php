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
namespace app\index\controller;

use app\service\SeoService;
use app\service\SearchService;

/**
 * 搜索
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Search extends Common
{
    /**
     * 构造方法
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-30
     * @desc    description
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-02-22T16:50:32+0800
     */
    public function Index()
    {
        // post搜索
        if(!empty($this->data_post['wd']))
        {
            return MyRedirect(MyUrl('index/search/index', ['wd'=>StrToAscii($this->data_post['wd'])]));
        }

        // 搜素条件
        $map = SearchService::SearchWhereHandle($this->data_request);

        // 获取商品列表
        $ret = SearchService::GoodsList($map, $this->data_request);

        // 分页
        $page_params = [
            'number'    => $ret['data']['page_size'],
            'total'     => $ret['data']['total'],
            'where'     => $this->data_request,
            'page'      => $ret['data']['page'],
            'url'       => MyUrl('index/search/index'),
            'bt_number' => IsMobile() ? 2 : 4,
        ];
        $page = new \base\Page($page_params);
        $page_html = $page->GetPageHtml();

        // 关键字处理
        $params = $this->data_request;
        if(!empty($params['wd']))
        {
            $params['wd'] = AsciiToStr($params['wd']);
        }

        // 模板数据
        $assign = [
            // 基础参数
            'is_map'            => $map['is_map'],
            'params'            => $params,
            'page_html'         => $page_html,
            'data_total'        => $ret['data']['total'],
            'data_list'         => $ret['data']['data'],
            // 排序方式
            'map_order_by_list' => SearchService::SearchMapOrderByList($this->data_request),
            // 面包屑导航
            'breadcrumb_data'   => SearchService::SearchBreadcrumbData($params),
        ];

        // 品牌列表
        $assign['brand_list'] = SearchService::SearchMapHandle(SearchService::CategoryBrandList($map, $this->data_request), 'bid', 'id', $this->data_request);

        // 指定数据
        $assign['search_map_info'] = SearchService::SearchMapInfo($this->data_request);

        // 商品分类
        $assign['category_list'] = SearchService::SearchMapHandle(SearchService::GoodsCategoryList($this->data_request), 'cid', 'id', $this->data_request);

        // 筛选价格区间
        $assign['screening_price_list'] = SearchService::SearchMapHandle(SearchService::ScreeningPriceList($this->data_request), 'peid', 'id', $this->data_request);

        // 商品参数
        $assign['goods_params_list'] = SearchService::SearchMapHandle(SearchService::SearchGoodsParamsValueList($map, $this->data_request), 'psid', 'id', $this->data_request, ['is_ascii'=>true, 'field'=>'value']);

        // 商品规格
        $assign['goods_spec_list'] = SearchService::SearchMapHandle(SearchService::SearchGoodsSpecValueList($map, $this->data_request), 'scid', 'id', $this->data_request, ['is_ascii'=>true, 'field'=>'value']);

        // 增加搜索记录
        $params['user_id'] = empty($this->user) ? 0 : $this->user['id'];
        $params['search_result_data'] = $ret['data'];
        SearchService::SearchAdd($params);

        // seo信息
        // 默认关键字
        $seo_title = empty($params['wd']) ? '' : $params['wd'];
        if(!empty($assign['search_map_info']))
        {
            // 分类、品牌
            $seo_info = empty($assign['search_map_info']['category']) ? (empty($assign['search_map_info']['brand']) ? [] : $assign['search_map_info']['brand']) : $assign['search_map_info']['category'];
            if(!empty($seo_info))
            {
                $seo_title = empty($seo_info['seo_title']) ? $seo_info['name'] : $seo_info['seo_title'];
                // 关键字和描述
                if(!empty($seo_info['seo_keywords']))
                {
                    $assign['home_seo_site_keywords'] = $seo_info['seo_keywords'];
                }
                if(!empty($seo_info['seo_desc']))
                {
                    $assign['home_seo_site_description'] = $seo_info['seo_desc'];
                }
            }
        }
        $assign['home_seo_site_title'] = SeoService::BrowserSeoTitle(empty($seo_title) ? MyLang('search.browser_seo_title') : $seo_title, 1);

        // 模板赋值
        MyViewAssign($assign);
        // 钩子
        $this->PluginsHook();
        return MyView();
    }

    /**
     * 钩子处理
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     */
    private function PluginsHook()
    {
        $hook_arr = [
            // 搜索页面顶部钩子
            'plugins_view_search_top',

            // 搜索页面底部钩子
            'plugins_view_search_bottom',

            // 搜索页面顶部内部结构里面钩子
            'plugins_view_search_inside_top',

            // 搜索页面底部内部结构里面钩子
            'plugins_view_search_inside_bottom',

            // 搜索页面数据容器顶部钩子
            'plugins_view_search_data_top',

            // 搜索页面数据容器底部钩子
            'plugins_view_search_data_bottom',

            // 搜索条件顶部钩子
            'plugins_view_search_map_top',

            // 搜索页面搜索导航条顶部钩子
            'plugins_view_search_nav_top',

            // 搜索页面搜索导航条内前面钩子
            'plugins_view_search_nav_inside_begin',

            // 搜索页面搜索导航条内尾部钩子
            'plugins_view_search_nav_inside_end',

            // 搜索页面筛选条件内前面钩子
            'plugins_view_search_map_inside_begin',

            // 搜索页面筛选条件内基础底部钩子
            'plugins_view_search_map_inside_base_bottom',

            // 搜索页面筛选条件内尾部钩子
            'plugins_view_search_map_inside_end',
        ];
        $assign = [];
        foreach($hook_arr as $hook_name)
        {
            $assign[$hook_name.'_data'] = MyEventTrigger($hook_name,
                [
                    'hook_name'    => $hook_name,
                    'is_backend'   => false,
                ]);
        }
        MyViewAssign($assign);
    }
}
?>