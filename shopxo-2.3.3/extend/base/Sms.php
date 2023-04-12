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
namespace base;

/**
 * 短信驱动
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Sms
{
    // 保存错误信息
    public $error;

    // Access Key ID
    private $access_key_id = '';
    // Access Access Key Secret
    private $access_key_secret = '';
    // 签名
    private $sign_ame = '';

    private $expire_time;
	private $key_code;
    private $is_frq;

    /**
	 * 构造方法
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2017-03-07T14:03:02+0800
	 * @param    [int]        $params['interval_time'] 	[间隔时间（默认30）单位（秒）]
	 * @param    [int]        $params['expire_time'] 	[到期时间（默认30）单位（秒）]
	 * @param    [string]     $params['key_prefix']     [验证码种存储前缀key（默认 空）]
     * @param    [string]     $params['is_frq']         [是否验证频率（默认 是）]
	 */
	public function __construct($params = [])
	{
		$this->interval_time = isset($params['interval_time']) ? intval($params['interval_time']) : 30;
		$this->expire_time = isset($params['expire_time']) ? intval($params['expire_time']) : 30;
		$this->key_code = isset($params['key_prefix']) ? trim($params['key_prefix']).'_sms_code' : '_sms_code';
        $this->is_frq = isset($params['is_frq']) ? intval($params['is_frq']) : 1;

		$this->sign_ame = MyC('common_sms_sign');
		$this->access_key_id = MyC('common_sms_apikey');
		$this->access_key_secret = MyC('common_sms_apisecret');
	}

    /**
     * 验证码发送
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-04-02
     * @desc    description
     * @param   [string]            $mobile        [手机号码，多个以 英文逗号 , 分割]
     * @param   [string|array]      $code          [变量code（单个直接传入 code 即可，多个传入数组）]
     * @param   [string]            $template_code [模板 id]
     * @param   [boolean]           $sign_name     [自定义签名，默认使用基础配置的签名]
     */
    public function SendCode($mobile, $code, $template_code, $sign_name = '')
    {
    	// 单个验证码需要校验是否频繁
        if(is_string($code))
        {
            // 是否频繁操作
            if(!$this->IntervalTimeCheck())
            {
                $this->error = MyLang('operate_frequent_tips');
                return false;
            }
            $codes = ['code'=>$code];
        } else {
            $codes = $code;
        }

        // 请求发送
        $status = $this->SmsRequest($mobile, $template_code, $sign_name, $codes);
        if($status)
        {
            // 种session
            if(is_string($code))
            {
                $this->KindofSession($code);
            }
        }
        return $status;
    }

    /**
     * 短信发送
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-04-02
     * @desc    description
     * @param   [string]            $mobile          [手机号码，多个以 英文逗号 , 分割]
     * @param   [string]            $template_code   [模板 id]
     * @param   [boolean]           $sign_name       [自定义签名，默认使用基础配置的签名]
     * @param   [string|array]      $template_params [变量code（单个直接传入 code 即可，多个传入数组）]
     */
    public function SendTemplate($mobile, $template_code, $sign_name = '', $template_params = [])
    {
        // 是否频繁操作
        if($this->is_frq == 1)
        {
            if(!$this->IntervalTimeCheck())
            {
                $this->error = MyLang('operate_frequent_tips');
                return false;
            }
        }

        // 请求发送
        $status = $this->SmsRequest($mobile, $template_code, $sign_name, $template_params);
        if($status)
        {
            // 种session
            if($this->is_frq == 1)
            {
                $this->KindofSession();
            }
        }
        return $status;
    }

    /**
     * 请求发送
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-03-26
     * @desc    description
     * @param   [string]            $mobile          [手机号码，多个以 英文逗号 , 分割]
     * @param   [string]            $template_code   [模板 id]
     * @param   [boolean]           $sign_name       [自定义签名，默认使用基础配置的签名]
     * @param   [string|array]      $template_params [默认变量code（单个直接传入 code 即可，多个传入数组）]
     */
    public function SmsRequest($mobile, $template_code, $sign_name = '', $template_params = [])
    {
        // 签名
        $sign_name = empty($sign_name) ? $this->sign_ame : $sign_name;

        // 请求参数
        $params = [
            'SignName'          => $sign_name,
            'Format'            => 'JSON',
            'Version'           => '2017-05-25',
            'AccessKeyId'       => $this->access_key_id,
            'SignatureVersion'  => '1.0',
            'SignatureMethod'   => 'HMAC-SHA1',
            'SignatureNonce'    => uniqid(),
            'Timestamp'         => gmdate('Y-m-d\TH:i:s\Z'),
            'Action'            => 'SendSms',
            'TemplateCode'      => $template_code,
            'PhoneNumbers'      => $mobile,
        ];
        // 携带参数
        if(!empty($template_params))
        {
            if(!is_array($template_params))
            {
                $template_params = ['code'=>$template_params];
            }
            $params['TemplateParam'] = json_encode($template_params, JSON_UNESCAPED_UNICODE);
        }
        // 签名
        $params ['Signature'] = $this->ComputeSignature($params, $this->access_key_secret);
        // 远程请求
        $url = 'http://dysmsapi.aliyuncs.com/?' . http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        if(isset($result['Code']) && $result['Code'] != 'OK')
        {
            $this->error = $this->GetErrorMessage($result['Code']);
            return false;
        }
        return true;
    }

    /**
     * 签名生成
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-03-26
     * @desc    description
     * @param   [array]          $parameters      [参数]
     * @param   [string]         $accessKeySecret [秘钥]
     */
    private function ComputeSignature($parameters, $accessKeySecret)
    {
        ksort($parameters);
        $canonicalizedQueryString = '';
        foreach($parameters as $key=>$value)
        {
            $canonicalizedQueryString .= '&' . $this->PercentEncode($key) . '=' . $this->PercentEncode($value );
        }
        $stringToSign = 'GET&%2F&' . $this->Percentencode(substr($canonicalizedQueryString, 1));
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));
        return $signature;
    }

    /**
     * 签名字符处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-03-26
     * @desc    description
     * @param   [string]          $string [需要处理的字符]
     */
    private function PercentEncode($string)
    {
        $string = urlencode($string);
        $string = preg_replace('/\+/', '%20', $string);
        $string = preg_replace('/\*/', '%2A', $string);
        $string = preg_replace('/%7E/', '~', $string);
        return $string;
    }

    /**
     * 获取详细错误信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-03-26
     * @desc    description
     * @param   [string]          $status [错误码]
     */
    public function GetErrorMessage($status)
    {
        // 阿里云的短信 乱八七糟的(其实是用的阿里大于)
        // https://api.alidayu.com/doc2/apiDetail?spm=a3142.7629140.1.19.SmdYoA&apiId=25450
        $message = [
            'InvalidDayuStatus.Malformed'           => '账户短信开通状态不正确',
            'InvalidSignName.Malformed'             => '短信签名不正确或签名状态不正确',
            'InvalidTemplateCode.MalFormed'         => '短信模板Code不正确或者模板状态不正确',
            'InvalidRecNum.Malformed'               => '目标手机号不正确，单次发送数量不能超过100',
            'InvalidParamString.MalFormed'          => '短信模板中变量不是json格式',
            'InvalidParamStringTemplate.Malformed'  => '短信模板中变量与模板内容不匹配',
            'InvalidSendSms'                        => '触发业务流控',
            'InvalidDayu.Malformed'                 => '变量不能是url，可以将变量固化在模板中',
            'isv.RAM_PERMISSION_DENY'               => 'RAM权限DENY',
            'isv.OUT_OF_SERVICE'                    => '业务停机',
            'isv.PRODUCT_UN_SUBSCRIPT'              => '未开通云通信产品的阿里云客户',
            'isv.PRODUCT_UNSUBSCRIBE'               => '产品未开通',
            'isv.ACCOUNT_NOT_EXISTS'                => '账户不存在',
            'isv.ACCOUNT_ABNORMAL'                  => '账户异常',
            'isv.SMS_TEMPLATE_ILLEGAL'              => '短信模板不合法',
            'isv.SMS_SIGNATURE_ILLEGAL'             => '短信签名不合法',
            'isv.INVALID_PARAMETERS'                => '参数异常',
            'isv.SYSTEM_ERROR'                      => '系统错误',
            'isv.MOBILE_NUMBER_ILLEGAL'             => '非法手机号',
            'isv.MOBILE_COUNT_OVER_LIMIT'           => '手机号码数量超过限制',
            'isv.TEMPLATE_MISSING_PARAMETERS'       => '模板缺少变量',
            'isv.BUSINESS_LIMIT_CONTROL'            => '业务限流',
            'isv.INVALID_JSON_PARAM'                => 'JSON参数不合法，只接受字符串值',
            'isv.BLACK_KEY_CONTROL_LIMIT'           => '黑名单管控',
            'isv.PARAM_LENGTH_LIMIT'                => '参数超出长度限制',
            'isv.PARAM_NOT_SUPPORT_URL'             => '不支持URL',
            'isv.AMOUNT_NOT_ENOUGH'                 => '账户余额不足',
        ];
        if(isset($message[$status]))
        {
            return $message[$status];
        }
        return $status;
    }

    /**
     * 种验证码session
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-03-07T14:59:13+0800
     * @param    [string]      $value [存储值或验证码]
     */
    private function KindofSession($value = '')
    {
        $data = [
            'value' => $value,
            'time'  => time(),
        ];
        MyCache($this->key_code, $data, $this->expire_time);
    }

    /**
     * 验证码是否过期
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-03-05T19:02:26+0800
     * @return   [boolean] [有效true, 无效false]
     */
    public function CheckExpire()
    {
        $data = MyCache($this->key_code);
        if(!empty($data))
        {
            return (time() <= $data['time']+$this->expire_time);
        }
        return false;
    }

	/**
	 * 验证码是否正确
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2017-03-05T16:55:00+0800
	 * @param    [string] $code    [验证码（默认从post读取）]
	 * @return   [booolean]        [正确true, 错误false]
	 */
	public function CheckCorrect($code = '')
	{
        // 安全验证
        if(SecurityPreventViolence($this->key_code, 1, $this->expire_time))
        {
            // 验证是否正确
            $data = MyCache($this->key_code);
            if(!empty($data))
            {
                if(empty($code) && isset($_POST['code']))
                {
                    $code = trim($_POST['code']);
                }
                return (isset($data['value']) && $data['value'] == $code);
            }
        }
		return false;
	}

	/**
	 * 验证码清除
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2017-03-08T10:18:20+0800
	 * @return   [other] [无返回值]
	 */
	public function Remove()
	{
		MyCache($this->key_code, null);
        SecurityPreventViolence($this->key_code, 0);
	}

	/**
	 * 是否已经超过控制的间隔时间
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2017-03-10T11:26:52+0800
	 * @return   [booolean]        [已超过间隔时间true, 未超过间隔时间false]
	 */
	private function IntervalTimeCheck()
	{
		$data = MyCache($this->key_code);
		if(!empty($data))
		{
			return (time() > $data['time']+$this->interval_time);
		}
		return true;
	}
}
?>