<?php

namespace Src\Middlewares;

use Src\Services\Route;
use Src\Controllers\PageController;
use Src\Helpers\FileAndPathManager;

class ArticleMiddleware
{
    public static function checkArticleExists($slug)
    {
        $articleController = new \Src\Controllers\ArticleController();
        $article = $articleController->getArticleBySlug($slug);
        if (empty($article)) {
            Route::redirect('/404');
            exit;
        }
        $article['meta'] = json_decode($article['meta'], true) ?? [
            'description' => '',
            'tag' => ''
        ];
        $article['image'] = FileAndPathManager::fileExists('article-image', $article['image'])
            ? ('/assets/images/article-image/' . $article['image'])
            : '/assets/images/product-image/unfound.jpg';
        $recents = $articleController->getRecent(5);
        (new PageController())->article([
            'article' => $article,
            'recents' => $recents
        ]);
        exit;
    }
}
