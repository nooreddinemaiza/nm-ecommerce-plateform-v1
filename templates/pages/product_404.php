<?php

use Src\Helpers\Helper;
use Src\Helpers\UrlHelper;

$data = Helper::safeJsonDecode($data, "productList");
$productList = Helper::combineDataByKey($productList, 'title');
?>
<style>
    .error-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 60vh;
        text-align: center;
        padding: 2rem;
        background: #f8f9fa;
    }

    .error-code {
        font-size: 120px;
        font-weight: bold;
        color: #dc3545;
        margin: 0;
        line-height: 1;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }

    .error-message {
        font-size: 24px;
        color: #343a40;
        margin: 1rem 0;
    }

    .error-details {
        font-size: 16px;
        color: #6c757d;
        margin-bottom: 2rem;
        max-width: 600px;
    }

    .error-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    .error-button {
        padding: 0.8rem 1.5rem;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .primary-button {
        background: #007bff;
        color: white;
        border: none;
    }

    .primary-button:hover {
        background: #0056b3;
        transform: translateY(-2px);
    }

    .secondary-button {
        background: white;
        color: #007bff;
        border: 2px solid #007bff;
    }

    .secondary-button:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
    }

    .search-form {
        margin: 2rem 0;
        max-width: 500px;
        width: 100%;
    }

    .search-input {
        width: 100%;
        padding: 0.8rem;
        border: 2px solid #ced4da;
        border-radius: 5px;
        font-size: 16px;
        transition: border-color 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #007bff;
    }

    .suggested-products {
        margin-top: 3rem;
        width: 100%;
        max-width: 800px;
    }

    .suggested-title {
        font-size: 20px;
        color: #343a40;
        margin-bottom: 1rem;
        text-align: left;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        padding: 1rem;
    }

    .product-card {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .product-card:hover {
        transform: translateY(-5px);
    }

    .product-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 4px;
        margin-bottom: 1rem;
    }

    .product-title {
        font-size: 16px;
        color: #343a40;
        margin-bottom: 0.5rem;
    }

    .product-price {
        font-weight: bold;
        color: #0071f8;
    }

    @media (max-width: 768px) {
        .error-code {
            font-size: 80px;
        }

        .error-message {
            font-size: 20px;
        }

        .products-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
    }
</style>
<div class="error-container">
    <h1 class="error-code">404</h1>
    <h2 class="error-message">Produit non trouvé</h2>
    <p class="error-details">
        Désolé, le produit que vous recherchez n'existe plus ou a été déplacé.
        Vous pouvez découvrir notre gamme de produits qui peuvent vous interressez.
    </p>
    <div class="error-actions">
        <a href="/" class="error-button primary-button">
            Retour à l'accueil
        </a>
        <a href="/shop" class="error-button secondary-button">
            Retour au Shop
        </a>
    </div>

    <?php if (!empty($productList)): ?>
        <div class="suggested-products">
            <h3 class="suggested-title">Produits qui pourraient vous intéresser</h3>
            <div class="products-grid">
                <?php foreach ($productList as $product):
                    $link = UrlHelper::generateProductLink(htmlspecialchars($product['slug']), $product['id']);
                    $images = explode(',', $product['images']);
                    foreach ($images as $k => $image){
                        if($image == ""){
                            unset($images[$k]);
                        }
                    }
                    ?>
                    <div class="product-card">
                        <a href="<?= $link ?>">
                            <img
                                src="/assets/images/product-image/<?= htmlspecialchars(!empty($images) ? $images[rand(0,count($images)-1)]:"No_Image_Available.jpg") ?>" alt="<?= htmlspecialchars($product['title']) ?>"
                                class="product-image">
                        </a>
                        <h4 class="product-title"><?= htmlspecialchars($product['title']) ?></h4>
                        <p class="product-price"><?= number_format($product['price'], 2, ',', ' ') ?> DH</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>