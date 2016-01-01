<?php
namespace Wechat\Model;

use Wechat\Entity\WechatMessageEntity;
use Zend\Http\Client as HttpClient;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream as WriterStream;
use Zend\Json\Json;
use Wechat\Redis\WechatRedis;
use Common\Exception\BusinessException;
use Wechat\Util\WechatUtil;

class Wechat
{
    const MSGTYPE_TEXT  = 'text';
    const MSGTYPE_IMAGE = 'image';
    const MSGTYPE_VOICE = 'voice';
    const MSGTYPE_EVENT = 'event';
    const MSGTYPE_LOCATION = 'location';
    const MSGTYPE_LINK = 'link';
    const MSGTYPE_VIDEO = 'video';
    const EVENT_SUBSCRIBE = 'subscribe';       //订阅
    const EVENT_UNSUBSCRIBE = 'unsubscribe';   //取消订阅
    const EVENT_SCAN = 'SCAN';                 //扫描带参数二维码
    const EVENT_LOCATION = 'LOCATION';         //上报地理位置
    const EVENT_MENU_VIEW = 'VIEW';                     //菜单 - 点击菜单跳转链接
    const EVENT_MENU_CLICK = 'CLICK';                   //菜单 - 点击菜单拉取消息
    const EVENT_MENU_SCAN_PUSH = 'scancode_push';       //菜单 - 扫码推事件(客户端跳URL)
    const EVENT_MENU_SCAN_WAITMSG = 'scancode_waitmsg'; //菜单 - 扫码推事件(客户端不跳URL)
    const EVENT_MENU_PIC_SYS = 'pic_sysphoto';          //菜单 - 弹出系统拍照发图
    const EVENT_MENU_PIC_PHOTO = 'pic_photo_or_album';  //菜单 - 弹出拍照或者相册发图
    const EVENT_MENU_PIC_WEIXIN = 'pic_weixin';         //菜单 - 弹出微信相册发图器
    const EVENT_MENU_LOCATION = 'location_select';      //菜单 - 弹出地理位置选择器
    const EVENT_SEND_MASS = 'MASSSENDJOBFINISH';        //发送结果 - 高级群发完成
    const EVENT_SEND_TEMPLATE = 'TEMPLATESENDJOBFINISH';//发送结果 - 模板消息发送结果
    const EVENT_KF_SEESION_CREATE = 'kfcreatesession';  //多客服 - 接入会话
    const EVENT_KF_SEESION_CLOSE = 'kfclosesession';    //多客服 - 关闭会话
    const EVENT_KF_SEESION_SWITCH = 'kfswitchsession';  //多客服 - 转接会话
    const EVENT_CARD_PASS = 'card_pass_check';          //卡券 - 审核通过
    const EVENT_CARD_NOTPASS = 'card_not_pass_check';   //卡券 - 审核未通过
    const EVENT_CARD_USER_GET = 'user_get_card';        //卡券 - 用户领取卡券
    const EVENT_CARD_USER_DEL = 'user_del_card';        //卡券 - 用户删除卡券
    
    private $config;
    /**
     * @var HttpClient
     */
    private $http;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var WechatUtil
     */
    private $wechatUtil;
    private $data;
    
    public function __construct(array $config, HttpClient $client)
    {
        $this->config = $config;
        $this->http = $client;
        // create file logger
        $this->logger = new Logger();
        $writer = new WriterStream(ROOT.'/data/log/wechat.log');
        $this->logger->addWriter($writer);
        
        $this->wechatUtil = new WechatUtil($this->logger, array(
            'token' => $this->config['token'],
            'encodingaeskey' => $this->config['EncodingAESKey'],
            'appid' => $this->config['appid'],
            'appsecret' => $this->config['secret'],
            'logcallback'=>$this->logger
        ));
    }


    /**
     * 做数据验证 以及 存储数据
     * @param bool $return
     */
    public function valid($return=false){
        $this->wechatUtil->valid($return);
    }


    /**
     * 获取消息类型
     */
    public function getRevType(){
        return $this->wechatUtil->getRev()->getRevType();
    }

    /**
     * 获取消息内容
     */
    public function getRevContent(){
        if($this->getRevType() == Wechat::MSGTYPE_TEXT){
            return $this->wechatUtil->getRev()->getRevContent();
        }else{
            return $this->getRevType();
        }

    }

    /**
     * 获取图片消息的内容
     */
    public function getRevPic(){
        return $this->wechatUtil->getRev()->getRevPic();
    }

    /**
     * 获取语音消息的内容
     */
    public function getRevVoice(){
        return $this->wechatUtil->getRev()->getRevVoice();
    }

    /**
     * 获取用户的openId
     */
    public function getOpenId(){
        return $this->wechatUtil->getRev()->getRevFrom();
    }

    /**
     * 获取消息类型
     */
    public function getRevOpenId(){
        return $this->wechatUtil->getRev()->getRevFrom();
    }

    /**
     * 文本回复
     */
    public function textReply($content = '""')
    {
        return $this->wechatUtil->text($content)->reply();
    }

    /**
     * 图片回复
     */
    public function imageReply($mediaId)
    {
        return $this->wechatUtil->news($mediaId)->reply();
    }

    /**
     * 多客服接口
     * @param string $customer_account
     * @return $this
     */
    public function transfer_customer_service($customer_account = ''){
        return $this->wechatUtil->transfer_customer_service($customer_account)->reply();
    }


    /**
     * 获取用户列表
     * @param string $next_openid
     * @return bool|mixed
     */
    public function getUserList($next_openid =''){
        return $this->wechatUtil->getUserList($next_openid);
    }

    /**
     * 创建菜单
     * @return string status
     */
    public function createMenu()
    {
        return $this->wechatUtil->createMenu(array('button' => $this->config['menu']));
    }

    /**
     * {"errcode":0,"errmsg":"ok"}
     */
    public function deleteMenu()
    {
        return $this->wechatUtil->deleteMenu();
    }

    /**
     * 获取永久素材列表
     */
    public function getForeverList($type,$offset,$count){
        return $this->wechatUtil->getForeverList($type,$offset,$count);
    }

    /**
     * 获取临时素材
     */
    public function getMedia($media_id,$is_video=false){
        return $this->wechatUtil->getMedia($media_id,$is_video=false);
    }

    /**
     * 上传临时素材
     */
    public function uploadMedia($data, $type){
        return $this->wechatUtil->uploadMedia($data, $type);
    }

    /**
     * 上传永久素材
     */
    public function uploadForeverMedia($data, $type){
        return $this->wechatUtil->uploadForeverMedia($data, $type);
    }

    /**
     * 组装callback地址
     * @param $callback
     * @param string $state
     * @param string $scope
     * @return string
     */
    public function getOauthRedirect($callback,$state='STATE',$scope='snsapi_base'){
        return $this->wechatUtil->getOauthRedirect($callback,$state,$scope);
    }

    /**
     * oauth 2.0 获取用户信息
     * {
        "openid":" OPENID",
        "nickname": NICKNAME,
        "sex":"1",
        "province":"PROVINCE"
        "city":"CITY",
        "country":"COUNTRY",
        "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46",
        "privilege":[
        "PRIVILEGE1"
        "PRIVILEGE2"
        ],
        "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
        }
     */
    public function getOauthUserinfo($access_token,$openid){
        return $this->wechatUtil->getOauthUserinfo($access_token,$openid);
    }

    /**
     * 公众号通过openId获取用户信息
     * @return array
     */
    public function getUserInfo($openId){
        return $this->wechatUtil->getUserInfo($openId);
    }

    /**
     * 获取用户的openId
     */
    public function getOauthOpenId(){
        $tokenData = $this->wechatUtil->getOauthAccessToken();
        return $tokenData['openid'];
    }

    /**
     * 获取tokenData
     * {
     *  "access_token":"ACCESS_TOKEN",
     *  "expires_in":7200,
     *  "refresh_token":"REFRESH_TOKEN",
     *  "openid":"OPENID",
     *  "scope":"SCOPE",
     *  "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
     *  }
     */
    public function getOauthData(){
        return  $this->wechatUtil->getOauthAccessToken();
    }


    /**
     * 快捷键文本
     */
    public function getFastKeyText(){
        $text = '';
        return $text;
    }

    /**
     * 获取用户上一次操作
     */
    public function getUserLastOperation($user){
        return $this->redis->getUserOperation($user->id);
    }

    /**
     * 记录用户最新一次操作
     */
    public function setUserLastOperation($user,$op){
        return $this->redis->setUserOperation($user->id,$op);
    }

    /**
     * 清除用户最新一次操作
     */
    public function clearUserOpeartion($user){
        return $this->redis->clearUserOpeartion($user->id);
    }

    public function setUserSelectData($user ,$field ,$data){
        return $this->redis->setUserSelectData($user->id,$field ,$data);
    }

    public function getUserSelectData($user ,$field){
        return  $rst = $this->redis->getUserSelectData($user->id ,$field);
    }


    /**
     * 获取AccessToken
     * @return bool|mixed
     */
    public function getAccessToken(){
        return $this->wechatUtil->checkAuth();
    }

    /**
     * 获取语音的下载链接
     */
    public function getMediaUrl($mediaId){
        return $this->wechatUtil->getMediaUrl($mediaId);
    }




    public function getRevEvent(){
        return $this->wechatUtil->getRevEvent();
    }

    /**
     * 发送模版消息接口
     * @param $toUserOpenId
     * @param $template
     * @param $data
     * @param null $url
     * @param null $topColor
     * @return bool
     * @throws BusinessException
     */
    public function sendTemplateMessage($toUserOpenId, $template, $data, $url = null, $topColor = null) {
        $arr_data = array();
        $arr_data['touser'] = $toUserOpenId;
        $arr_data['template_id'] = $template;
        $arr_data['topcolor'] = $topColor;
        if (isset($url)) {
            $arr_data['url'] = $url;
        }
        if (isset($topColor)) {
            $arr_data['topcolor'] = $topColor;
        }
        $templateData = array();
        foreach($data as $k => $v) {
            if (!is_array($v)) {
                $v = array('value' => $v);
            }
            $templateData[$k] = $v;
        }
        $arr_data['data'] = $templateData;
        $result = $this->wechatUtil->sendTemplateMessage($arr_data);
        if ($result == false) {
            $this->log('微信模版信息发送失败');
        }
        return true;
    }




    /**
     * js Sign
     */
    public function getJsSign($url, $timestamp=0, $noncestr='', $appid=''){
        return $this->wechatUtil->getJsSign($url, $timestamp=0, $noncestr='', $appid='');
    }

    public function log($text){
        $this->logger->debug($text);
    }
}