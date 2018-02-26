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
                        'post_category',
                        'post_author',
                        'post_status',
                        'comment_status',
                        'comment_count',
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
            $post_category  = I('post.post_category');
            $id             = I('post.id');
            $data           = [
                    'post_title'     => $post_title,
                    'post_excerpt'   => $post_excerpt,
                    'post_password'  => $post_password,
                    'post_status'    => $post_status,
                    'post_content'   => $post_content,
                    'post_category'  => $post_category,
                    'comment_status' => $comment_status,
            ];
            $data           = $this->ArticleService->buildArticleData($data, $id);
            if (empty($id)) {
                $id = $this->ArticleService->addArticle($data);
            } else {
                $id = $this->ArticleService->updateArticle($data, ['id' => $id]);
            }
            if (empty($id)) {
                $error = $this->ArticleService->getDbError();
                $this->displayError($error);
            } else {
                $this->redirect(make_url('index'));
            }
        } else {
            $id = I('get.id');
            if ($id) {
                $articleInfo = $this->ArticleService->getArticleInfo($id);
                if ($articleInfo) {
                    $parentCategorySelector = Api::get(
                            'category.generateCategorySelector',
                            [$id, $articleInfo['post_category'], 'post_category']
                    );
                    $this->assign('articleInfo', $articleInfo);
                    $this->assign('id', $id);
                }
            }

            $jscontent    = <<<JS_CONTENT
        <script type="text/javascript">            
          $(function () {
            CKEDITOR.replace('post_content');
          });
        </script>  
JS_CONTENT;
            $categoryList = Api::get('category.getList');

            $this->assign('categoryList', $categoryList);
            $this->assign('jscontent', $jscontent);
            $this->assign('parentCategorySelector', $parentCategorySelector);
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
