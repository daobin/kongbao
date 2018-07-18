<?php

namespace app\admin\controller;

use app\common\constant\Route;
use app\common\constant\Session;
use app\common\controller\Controller;
use app\common\service\model\article\Category;
use app\common\service\model\article\Article as ArticleMoel;

class Article extends Controller
{
    public function category()
    {
        if (request()->isPost()) {
            $this->categorySave();
        }

        $this->assign('categories', Category::scope('order')->select());
        return $this->fetch();
    }

    private function categorySave()
    {
        $names = input('post.names/a', []);
        $sorts = input('post.sorts/a', []);
        $status = input('post.status/a', []);

        if (empty($names)) {
            session(Session::ERROR_MSG, '请新增分类');
            $this->redirect(url(Route::ADMIN_CATEGORY));
        }

        $inDatas = [];
        $upDatas = [];
        foreach ($names as $id => $name) {
            $name = trim($name);
            if ($id == '__I__' || $name == '') {
                continue;
            }

            if (strpos($id, 'i_') !== false) {
                $inDatas[] = [
                    'category_name' => $name,
                    'sort' => (int)$sorts[$id],
                    'status' => (int)$status[$id],
                    'operator' => session('admin.account')
                ];
            } else {
                $upDatas[] = [
                    'category_id' => $id,
                    'category_name' => $name,
                    'sort' => (int)$sorts[$id],
                    'status' => (int)$status[$id],
                    'operator' => session('admin.account')
                ];
            }
        }

        if (empty($inDatas) && empty($upDatas)) {
            session(Session::ERROR_MSG, '请新增分类');
            $this->redirect(url(Route::ADMIN_CATEGORY));
        }

        $cateModel = new Category();
        if (!empty($inDatas)) {
            $cateModel->saveAll($inDatas);
        }
        if (!empty($upDatas)) {
            $cateModel->saveAll($upDatas);
        }

        session(Session::SUCCESS_MSG, '分类保存成功');
        $this->redirect(url(Route::ADMIN_CATEGORY));
    }

    public function article()
    {
        $this->assign('articles', ArticleMoel::scope('order')->paginate());
        return $this->fetch();
    }

    public function articleEdit()
    {
        if (request()->isPost()) {
            $this->articleSave();
        }

        $articleId = input('article_id/d', 0);
        $this->assign('article', ArticleMoel::get($articleId));
        $this->assign('categories', Category::scope('order')->select());
        return $this->fetch();
    }

    private function articleSave()
    {
        $articleId = input('post.article_id/d', 0);
        $title = input('post.title/s', '');
        $categoryId = input('post.category_id/d', 0);
        if($title == ''){
            session(Session::ERROR_MSG, '文章标题不能为空');
            $this->redirect(url(Route::ADMIN_ARTICLE_EDIT, ['article_id'=>$articleId]));
        }
        if($categoryId <= 0){
            session(Session::ERROR_MSG, '请选择文章分类');
            $this->redirect(url(Route::ADMIN_ARTICLE_EDIT, ['article_id'=>$articleId]));
        }

        $saveData = [
            'title' => $title,
            'category_id' => $categoryId,
            'description' => input('post.description/s', '', 'trim'),
            'review_count' => input('post.review_count/d', ''),
            'is_recommend' => input('post.is_recommend/d', ''),
            'status' => input('post.status/d', ''),
            'sort' => input('post.sort/d', ''),
            'operator' => session('admin.account')
        ];

        $articleModel = new ArticleMoel();
        if($articleId > 0){
            $articleModel->save($saveData, ['article_id'=>$articleId]);
        }else{
            $articleModel->save($saveData);
            $articleId = $articleModel->article_id;
        }

        session(Session::SUCCESS_MSG, '文章保存成功');
        $this->redirect(url(Route::ADMIN_ARTICLE_EDIT, ['article_id'=>$articleId]));
    }
}