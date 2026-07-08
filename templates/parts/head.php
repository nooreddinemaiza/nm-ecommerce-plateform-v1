<?php

/**
 * @var string $pageTitle
 * @var array $metaTags
 * @var string $css
 */
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($metaTags['title'], ENT_QUOTES) ?></title>
        <!-- Meta tags -->
        <?php if (!empty($metaTags)): ?>
<?php if (!empty($metaTags['name'])): ?>
<!-- Meta classiques (name) -->
    <?php foreach ($metaTags['name'] as $name => $content): ?>
    <meta name="<?= htmlspecialchars($name, ENT_QUOTES) ?>" content="<?= htmlspecialchars($content, ENT_QUOTES) ?>">
    <?php endforeach; ?>
<?php endif; ?>
<?php if (!empty($metaTags['properties'])): ?>
    <!-- Open Graph (og:...) et autres propriétés -->
    <?php foreach ($metaTags['properties'] as $property => $content): ?>
    <meta property="<?= htmlspecialchars($property, ENT_QUOTES) ?>" content="<?= htmlspecialchars($content, ENT_QUOTES) ?>">
    <?php endforeach; ?>
<?php endif; ?>
<?php if (!empty($metaTags['twitter'])): ?>
    <!-- Twitter Cards -->
    <?php foreach ($metaTags['twitter'] as $name => $content): ?>
    <meta name="<?= htmlspecialchars($name, ENT_QUOTES) ?>" content="<?= htmlspecialchars($content, ENT_QUOTES) ?>">
    <?php endforeach; ?>
<?php endif; ?>
<?php if (!empty($metaTags['rel'])): ?>
    <!-- Liens rel -->
    <?php foreach ($metaTags['rel'] as $rel => $href): ?>
    <link rel="<?= htmlspecialchars($rel, ENT_QUOTES) ?>" href="<?= htmlspecialchars($href, ENT_QUOTES) ?>">
    <?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>
    <!-- Font google -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <!-- Stylesheets -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="/assets/css/vendor/bootstrap.icons.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
        <link rel="stylesheet" href="/assets/css/owned/templatemo-lugx-gaming.css">
        <link rel="stylesheet" href="/assets/css/owned/style.css">
        <link rel="stylesheet" href="/assets/css/owned/toaster.css">
        <link rel="stylesheet" href="/assets/css/owned/ajax-libs-toastr.js-latest-toastr.min.css">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="/assets/js/owned/js.js"></script>
        <?= $css ?? '' ?>
        
    </head>

    <body>