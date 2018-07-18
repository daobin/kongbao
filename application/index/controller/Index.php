<?php
/**
 * 前台首页控制器
 */

namespace app\index\controller;

use app\common\constant\Route;
use app\common\controller\Controller;
use app\common\service\model\advertisement\Banner;
use app\common\service\model\article\Article;
use app\common\service\model\article\Category;

class Index extends Controller
{
    public function index(){
        $recommendId = input('r_id/s');
        $recommendId = (int)substr($recommendId, 6);
        if($recommendId > 0){
            cookie('recommend_user_id', $recommendId, [
                'expire' => 365 * 24 * 3600
            ]);
        }

        $this->assign('loop_banner', Banner::where([
            'banner_position'=>'index_loop',
            'banner_status'=>1
        ])->field(true)->find());

        $this->assign('main_banner', Banner::where([
            'banner_position'=>'index',
            'banner_status'=>1
        ])->field(true)->find());

        $this->assign('categories', Category::scope('list', ['status'=>1])->field(true)->select());
        return $this->fetch();
    }

    public function pageNotFound(){
        return $this->fetch();
    }

    public function category(){
        $categoryId = input('category_id/d', 0);
        if($categoryId <= 0){
            $this->redirect(url(Route::PAGE_NOT_FOUND));
        }

        $this->assign('categories', Category::scope('list', ['status'=>1])->field(true)->select());

        $cateInfo = Category::where([
            'category_id' => $categoryId,
            'status' => 1
        ])->field(true)->find();
        if(empty($cateInfo)){
            $this->redirect(url(Route::PAGE_NOT_FOUND));
        }
        $this->assign('cate_info', $cateInfo);

        $this->assign('articles', Article::scope('list')->where([
            'category_id' => $categoryId,
            'status' => 1
        ])->field(true)->paginate());

        return $this->fetch();
    }

    public function article(){
        $articleId = input('article_id/d', 0);
        if($articleId <= 0){
            $this->redirect(url(Route::PAGE_NOT_FOUND));
        }

        $articleInfo = Article::where([
            'article_id' => $articleId,
            'status' => 1
        ])->field(true)->find();
        if(empty($articleInfo)){
            $this->redirect(url(Route::PAGE_NOT_FOUND));
        }
        $this->assign('article_info', $articleInfo);

        $cateInfo = Category::where([
            'category_id' => (int)$articleInfo->category_id,
            'status' => 1
        ])->field(true)->find();
        if(empty($cateInfo) && empty($cateInfo->articles)){
            $this->redirect(url(Route::PAGE_NOT_FOUND));
        }

        $currArticleFlag = 0;
        $prevArticle = null;
        $nextArticle = null;
        foreach($cateInfo->articles as $article){
            if((int)$articleInfo->article_id == (int)$article->article_id){
                $currArticleFlag = 1;
                continue;
            }

            if($currArticleFlag == 0){
                $prevArticle = $article;
            }else if($currArticleFlag == 1){
                $nextArticle = $article;
                break;
            }
        }
        $this->assign('prev_article', $prevArticle);
        $this->assign('next_article', $nextArticle);

        $this->assign('new_articles', Article::scope('list', [
            'status' => 1,
            'orders' => ['article_id'=>'desc']
        ])->field(true)->limit(0, 10)->select());

        $this->assign('hot_articles', Article::scope('list', [
            'status' => 1,
            'is_recommend' => 1
        ])->field(true)->limit(0, 10)->select());

        return $this->fetch();
    }
}

