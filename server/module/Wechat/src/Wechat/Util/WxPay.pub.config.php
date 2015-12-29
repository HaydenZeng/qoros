<?php
/**
* 	配置账号信息
*/

class WxPayConf_pub
{
	//=======【基本信息设置】=====================================
    const APPID = 'wx4e1e6f371f969694';
    const MCHID = '1240494002';
    const KEY = '2a1e858e5cb396ad278affa616d26594';
    const APPSECRET = '2a1e858e5cb396ad278affa616d26594';
	
	//=======【JSAPI路径设置】===================================
	//获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
//	const JS_API_CALL_URL = 'http://meck.dang5.com/m/order/wxPay';
	
	//=======【证书路径设置】=====================================
	//证书路径,注意应该填写绝对路径
    const SSLCERT_PATH = '../cert/apiclient_cert.pem';
    const SSLKEY_PATH = '../cert/apiclient_key.pem';
	
	//=======【异步通知url设置】===================================
	//异步通知url，商户根据实际开发过程设定
//	const NOTIFY_URL = 'http://meck.dang5.com/m/order/wxnotify';

	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	const CURL_TIMEOUT = 30;
}
	
?>