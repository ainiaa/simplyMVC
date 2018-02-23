<?php

class ArticleController extends ArticleBaseController
{

    /**
     * @var ArticleService
     */
    public $ArticleService;

    /**
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public function indexAction()
    {
        $articleList = $this->ArticleService->getArticleList();

        $this->assign('list', $articleList);
        $this->assign('title', 'Simply MVC backend - table list');
        $this->assign(
                'tableHeaderList',
                [
                        'id',
                        'post_title',
                        'post_excerpt',
                        'post_password',
                        'post_content',
                        'post_author',
                        'post_status',
                        'comment_status',
                        'comment_count'
                ]
        );

        $this->setMainTpl('article_list.tpl.html');
        $this->display();
    }

    /**
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public function addAction()
    {
        $url = make_url();
        $this->assign('action', $url);
        if (IS_POST) {
            $post_title     = I('post.post_title');
            $post_excerpt   = I('post.post_excerpt');
            $post_password  = I('post.post_password');
            $post_content   = I('post.post_content');
            $post_status    = I('post.post_status');
            $comment_status = I('post.comment_status');
            $data           = [
                    'post_title'     => $post_title,
                    'post_excerpt'   => $post_excerpt,
                    'post_password'  => $post_password,
                    'post_status'    => $post_status,
                    'post_content' => $post_content,
                    'comment_status' => $comment_status,
            ];
            $data           = $this->ArticleService->buildArticleData($data);
            $id             = $this->ArticleService->addArticle($data);
            if (empty($id)) {
                $error = $this->ArticleService->getDbError();
                $this->displayError($error);
            } else {
                $this->redirect(make_url('index'));
            }
        } else {
            $jscontent = <<<JS_CONTENT
        <script type="text/javascript">            
          $(function () {
            CKEDITOR.replace('post_content');
          });
        </script>  
JS_CONTENT;
            $categoryList = Api::get('category.getList'); //curl local timeout multiple port is ok or api call via rpc

            $this->assign('jscontent', $jscontent);
            $this->assign('jslist', ['assets/bower_components/ckeditor/ckeditor.js']);
            $this->setMainTpl('article_add.tpl.html');
            $this->display();
        }
    }

    /**
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public function detailAction()
    {
        $id = I('id');
        if ($id) {
            $categoryInfo = $this->ArticleService->getArticleInfo($id);
            if ($categoryInfo) {
                $parentId           = isset($categoryInfo['parent_id']) ? $categoryInfo['parent_id'] : '';
                $parentCategoryInfo = $this->ArticleService->getArticleInfo($parentId);
                $this->assign('parentCategoryInfo', $parentCategoryInfo);
                $this->assign('categoryInfo', $categoryInfo);
                $this->setMainTpl('category_detail.tpl.html');
                $this->assign('id', $id);
                $this->display();
            } else {
                //todo 分类不存在
            }
        } else {
            //todo id传递有问题
        }
    }

    /**
     * 编辑
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public function editAction()
    {
        $this->assign('action', make_url('edit'));
        $id = I('id');
        if ($id) {
            if (IS_POST) {
                $name           = I('post.name');
                $desc           = I('post.desc');
                $parentId       = I('post.pid');
                $type           = I('post.type');
                $parentCategory = $this->ArticleService->getArticleInfo($parentId);
                $data           = ['name' => $name, 'desc' => $desc, 'type' => $type];
                if ($parentCategory) {
                    $data['path']      = $parentCategory['path'] . $parentId . ',';
                    $data['depth']     = (int)$parentCategory['depth'] + 1;
                    $data['parent_id'] = $parentId;
                } else {
                    $data['path']  = ',0,';
                    $data['depth'] = 1;
                }
                $data = $this->ArticleService->buildArticleData($data);
                $id   = $this->ArticleService->updateArticle($data, ['id' => $id]);
                if (empty($id)) {
                    $error = $this->ArticleService->getDbError();
                    $this->displayError($error);
                } else {
                    $this->redirect(make_url('index'));
                }
            } else {//显示
                $categoryInfo = $this->ArticleService->getArticleInfo($id);
                if ($categoryInfo) {
                    $categoryList           = $this->ArticleService->getArticleList();
                    $parentCategorySelector = $this->ArticleService->generateCategorySelector(
                            $id,
                            $categoryInfo['parent_id']
                    );
                    $this->assign('list', $categoryList);
                    $this->assign('parentCategorySelector', $parentCategorySelector);
                    $this->assign('articleInfo', $categoryInfo);
                    $this->setMainTpl('category_add.tpl.html');
                    $this->assign('id', $id);
                    $this->display();
                } else {
                    $error = 'category id:' . $id . ' not found!';
                    $this->displayError($error);
                }
            }
        } else {
            $error = 'category id:' . $id . ' is missing!';
            $this->displayError($error);
        }
    }

    /**
     * 删除
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public function deleteAction()
    {
        $id = I('get.id');
        $this->ArticleService->deleteArticleById($id);
        $this->redirect(make_url('index'));
    }
}
