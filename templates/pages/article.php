<?php

use Src\Helpers\FileAndPathManager;
use Src\Helpers\StringUtils;

$title = $article['title'];
$published = date('d/m/Y', strtotime($article['published_at']));
$published = str_replace('/', ' ', $published);
$creator = $article['creator'] ?? "Anonyme";
$content = $article['content'];
$link = '/actualites/' . $article['slug'];
$image = $article['image'];
?>

<div class="page-heading header-text" style="background: #0071f8;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h3><?= $title ?></h3>
                <span class="breadcrumb"><a href="/">Acceuil</a> > Actualités/ <?= $title ?></span>
            </div>
        </div>
    </div>
</div>
<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="row mb-4 mt-4 page-title">
                <h2><?= $title ?></h2>
            </div>
            <div class="row mb-4 mt-4 d-block d-lg-none">
                <div class="search-input-container">
                    <input type="text" class="searchArticleIn" placeholder="Recherchez..." id="searchArticleIn" name="searchArticleIn">
                    <button type="button" id="searchArticleBtn" class="searchArticleBtn">
                        <i class="fa fa-search" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="col-12 mt-5">
                <div class="card h-100 shadow-sm hover-shadow transition">
                    <div class="position-relative">
                        <!-- Date en haut à gauche de l'image -->
                        <div class="position-absolute bg-primary text-white px-3 py-1 rounded-end" style="top: 15px; left: 0; z-index: 1;">
                            <?= $published ?>
                        </div>
                        <a href="<?= $link ?>">
                            <img class="card-img-top" style="height: 310px;" src="<?= $image ?>" alt="<?= $title ?>">
                        </a>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p class="card-text text-muted small mb-2">Par <?= $creator ?></p>
                        <div class="overflow-auto text-break">
                            <?= $content ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 pt-3" style="background: #f5f5f5;border-radius: 25px;">
            <div class="col col-12 mb-3 d-none d-lg-block">
                <div class="search-input-container">
                    <input type="text" class="searchArticleIn" placeholder="Recherchez..." id="searchArticleIn" name="searchArticleIn">
                    <button type="button" id="searchArticleBtn" class="searchArticleBtn"><i class="fa fa-search" aria-hidden="true"></i></button>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col col-12">
                    <h5>Articles récents</h5>
                    <div class="list-group list-group-flush">
                        <?php
                        if (!empty($recents)) {
                            foreach ($recents as $article) {
                                $title = $article['title'];
                                $published = date('d/m/Y', strtotime($article['published_at']));
                                $published = str_replace('/', ' ', $published);
                                $link = '/actualites/' . $article['slug'];
                                $image = $article['image'];
                                $image = FileAndPathManager::fileExists('article-image', $image)
                                    ? ('/assets/images/article-image/' . $article['image'])
                                    : '/assets/images/product-image/unfound.jpg';
                        ?>
                                <a href="<?= $link ?>" class="list-group-item list-group-item-action d-flex gap-3 py-3 mt-2" aria-current="true">
                                    <img src="<?= $image ?>" alt="" class="flex-shrink-0" style="width: 60px !important;height: 60px !important;">
                                    <div class="d-flex gap-2 w-100 justify-content-between">
                                        <div>
                                            <h6 class="mb-0"><?= $title ?></h6>
                                        </div>
                                    </div>
                                </a>
                        <?php
                            }
                        } else {
                            echo '<div class="alert mt-4">Aucun article trouvé.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col col-12 cta subscribe" id="newsletter" style="margin-top: 35px;">
                    <div class="section-heading">
                        <h6>INFOLETTRE</h6>
                        <h5>Soyez les <em>Premiers</em> à Découvrir Nos Nouveautés Exclusives!</h5>
                    </div>
                    <div class="search-input-container">
                        <input type="email" class="form-control" id="subscriberEmail" aria-describedby="emailHelp" placeholder="Votre email...">
                        <button type="button" id="subscribeButton" data-bs-toggle="tooltip" title="S’abonner">
                            <i class="fa fa-bell" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>