<div class="section cta">
    <div class="container">
        <div class="row">
            <div class="shop">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-heading">
                            <h2><?= $data['title'] ?></h2>
                        </div>
                        <p><?= $data['details'] ?></p>
                        <?php if (!empty(trim($data['link']))) : ?>
                            <div class="main-button">
                                <a href="<?= $data['link'] ?>"></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>