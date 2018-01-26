<?php

namespace cms;

use think\facade\Url;
use think\facade\View;
use think\facade\Config;
use think\facade\Request;
use cms\facade\Response;
use cms\core\objects\ReturnObject;
use cms\core\constant\CodeConstant;

class Controller
{

    /**
     * @var \think\View
     */
    protected $view;

    /**
     * @var \think\Request
     */
    protected $request;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->view = View::init(Config::pull('template'));
        $this->request = Request::instance();

        // 初始化
        $this->_initialize();
    }

    /**
     * 初始化
     */
    protected function _initialize()
    {
    }

    /**
     * 视图渲染前
     */
    protected function beforeViewRender()
    {
    }

    /**
     * 成功返回
     *
     * @param string $msg
     * @param string $url
     * @param string $data
     * @param int $wait
     * @param array $header
     */
    protected function success($msg = '', $url = '', $data = '', $wait = 3, $header = [])
    {
        $this->jump(CodeConstant::CODE_SUCCESS, $msg, $url, $data, $wait, $header);
    }

    /**
     * 失败返回
     *
     * @param string $msg
     * @param string $url
     * @param string $data
     * @param int $wait
     * @param array $header
     */
    protected function error($msg = '', $url = '', $data = '', $wait = 3, $header = [])
    {
        $this->jump(CodeConstant::CODE_ERROR, $msg, $url, $data, $wait, $header);
    }

    /**
     * 结果返回
     *
     * @param int $code
     * @param string $msg
     * @param string $url
     * @param string $data
     * @param int $wait
     * @param array $header
     */
    protected function jump($code = CodeConstant::CODE_SUCCESS, $msg = '', $url = '', $data = '', $wait = 3, $header = [])
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'url' => $this->buildUrl($url),
            'data' => $data,
            'wait' => $wait
        ];
        if ($this->request->isAjax()) {
            Response::json($result, $header);
        } else {
            $template = $this->getJumpTemplate($code);
            $content = $this->fetch($template, $result);
            Response::data($content, 'html', 200, $header);
        }
    }

    /**
     * 返回结果
     *
     * @param ReturnObject $return
     * @param string $url
     * @param int $wait
     * @param array $header
     */
    protected function response($return, $url = '', $wait = 3, $header = [])
    {
        if ($return->isSuccess()) {
            $this->success($return->getMsg(), $url, $return->getData(), $wait, $header);
        } else {
            $this->error($return->getMsg(), $url, $return->getData(), $wait, $header);
        }
    }

    /**
     * 模板变量赋值
     *
     * @param string $name
     * @param string $value
     * @return \think\View
     */
    protected function assign($name, $value = '')
    {
        return $this->view->assign($name, $value);
    }

    /**
     * 解析和获取模板内容
     *
     * @param  string $template
     * @param  array $vars
     * @param  array $config
     * @param  bool $renderContent
     * @return string
     */
    protected function fetch($template = '', $vars = [], $config = [], $renderContent = false)
    {
        $this->beforeViewRender();

        return $this->view->fetch($template, $vars, $config, $renderContent);
    }

    /**
     * 渲染内容输出
     *
     * @param  string $content
     * @param  array $vars
     * @param  array $config
     * @return string
     */
    protected function display($content, $vars = [], $config = [])
    {
        $this->beforeViewRender();

        return $this->view->fetch($content, $vars, $config, true);
    }

    /**
     * 构造Url
     *
     * @param string $url
     * @return string
     */
    protected function buildUrl($url)
    {
        if (strpos($url, '://') || 0 === strpos($url, '/')) {
            return $url;
        } elseif ($url === '') {
            return $url;
        } else {
            return Url::build($url);
        }
    }

    /**
     * 获取跳转模板
     *
     * @param int $code
     * @return mixed
     */
    protected function getJumpTemplate($code)
    {
        if ($code == CodeConstant::CODE_SUCCESS) {
            return Config::get('dispatch_success_tmpl');
        } else {
            return Config::get('dispatch_error_tmpl');
        }
    }

}