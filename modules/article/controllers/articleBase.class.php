<?php

/**
 * Class CategoryBaseController
 */
class ArticleBaseController extends BaseController
{

    /**
     * @var AdminService
     */
    public $AdminService;

    protected $headerTpl;
    protected $topbarTpl;
    protected $leftMenuTpl;
    protected $mainTpl;
    protected $footerTpl;
    protected $layout;
    protected $breadCrumbTpl;
    protected $defaultTplComponent = [
            'headerTpl'     => 'header.tpl.html',
            'topbarTpl'     => 'topbar.tpl.html',
            'leftMenuTpl'   => 'left_menu.tpl.html',
            'mainTpl'       => 'main.tpl.html',
            'breadCrumbTpl' => 'bread_crumb.tpl.html',
            'layout'        => 'layout.tpl.html',
    ];

    /**
     * @return mixed
     */
    public function getBreadCrumbTpl()
    {
        return $this->breadCrumbTpl;
    }

    /**
     * @param mixed $breadCrumbTpl
     *
     * @return $this
     */
    public function setBreadCrumbTpl($breadCrumbTpl)
    {
        $this->breadCrumbTpl = $breadCrumbTpl;
        $this->assign('breadCrumbTpl', $breadCrumbTpl);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLayout()
    {
        if (empty($this->layout) && isset($this->defaultTplComponent['layout'])) {
            $this->layout = $this->defaultTplComponent['layout'];
        }

        return $this->layout;
    }

    /**
     * @param mixed $layout
     *
     * @return $this
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
        $this->assign('layout', $layout);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeaderTpl()
    {
        return $this->headerTpl;
    }

    /**
     * @param mixed $headerTpl
     *
     * @return $this
     */
    public function setHeaderTpl($headerTpl)
    {
        $this->headerTpl = $headerTpl;
        $this->assign('headerTpl', $headerTpl);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTopbarTpl()
    {
        return $this->topbarTpl;
    }

    /**
     * @param mixed $topbarTpl
     *
     * @return $this
     */
    public function setTopbarTpl($topbarTpl)
    {
        $this->topbarTpl = $topbarTpl;
        $this->assign('topbarTpl', $topbarTpl);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLeftMenuTpl()
    {
        return $this->leftMenuTpl;
    }

    /**
     * @param mixed $leftMenuTpl
     *
     * @return $this
     */
    public function setLeftMenuTpl($leftMenuTpl)
    {
        $this->leftMenuTpl = $leftMenuTpl;
        $this->assign('leftMenuTpl', $leftMenuTpl);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMainTpl()
    {
        return $this->mainTpl;
    }

    /**
     * @param mixed $mainTpl
     *
     * @return $this
     */
    public function setMainTpl($mainTpl)
    {
        $this->mainTpl = $mainTpl;
        $this->assign('mainTpl', $mainTpl);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFooterTpl()
    {
        return $this->footerTpl;
    }

    /**
     * @param mixed $footerTpl
     *
     * @return $this
     */
    public function setFooterTpl($footerTpl)
    {
        $this->footerTpl = $footerTpl;
        $this->assign('footerTpl', $footerTpl);

        return $this;
    }

    /**
     * 初始化模板组件
     *
     * @param array $tplComponent
     */
    protected function initTplComponent($tplComponent = array())
    {
        if (!is_array($tplComponent) || empty($tplComponent)) {
            $tplComponent = $this->defaultTplComponent;
        } else {
            $tplComponent = array_merge($this->defaultTplComponent, $tplComponent);
        }

        foreach ($tplComponent as $componentName => $componentValue) {
            if (is_string($componentValue)) {
                $this->assign($componentName, $componentValue);
            }
        }
    }

    /**
     * @param string $tpl
     *
     * @return mixed
     */
    public function display($tpl = '')
    {
        if (empty($tpl)) {
            $tpl = $this->getLayout();
            if (empty($tpl)) {
                die('please set the template or the layout');
            }
        }

        return parent::display($tpl);
    }

    /**
     * 执行初始化操作
     * @author Jeff Liu
     */
    protected function _initialize()
    {

        $this->assign('title', 'Simply MVC backend');
        $this->initTplComponent();

        $sessionInfo = session('userInfo');
        if (empty($sessionInfo) && (!isset($_GET['a']) || $_GET['a'] != 'login')) {
            $url = make_url('modules/login/login/login');
            session('backurl', Request::getCurrentUrl());
            Request::redirect($url);
        }
    }


    /**
     * 根据编号获得对一个tipsInfo.
     *
     * @author Jeff Liu
     *
     * @param $errno
     *
     * @return mixed
     */
    protected function getMessageInfoByErrno($errno)
    {
        return TipsInfoService::getMessageInfoByErrno($errno);
    }


    /**
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param $errno
     *
     * @return array
     */
    protected function buildAjaxErrorReturnData($errno)
    {
        $messageInfo = $this->getMessageInfoByErrno($errno);
        $msg         = isset($messageInfo['errorSoftTxt']) ? $messageInfo['errorSoftTxt'] : $errno;
        $data        = ['data' => 'error', 'info' => $msg, 'status' => $errno,];

        return $data;
    }

    /**
     * Ajax方式返回数据到客户端.
     *
     * @param mixed  $data 要返回的数据
     * @param string $type AJAX返回数据格式
     */
    protected function ajaxReturn($data, $type = 'JSON')
    {
        if (method_exists($this, 'beforeAjaxReturn')) {
            $this->beforeAjaxReturn($data, $type);
        }

        if (func_num_args() == 1 && is_numeric($data) && $data < 0) {
            $data = $this->buildAjaxErrorReturnData($data);
            parent::ajaxReturn($data, $type);
        } else {
            parent::ajaxReturn($data, $type);
        }
    }

}