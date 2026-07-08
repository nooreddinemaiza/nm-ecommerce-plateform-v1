<?php

use Src\Helpers\Config;
?>
    <div class="page-heading header-text" style="background: #0071f8;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h3>À propos de nous</h3>
                    <span class="breadcrumb"><a href="/">Acceuil</a> > <a href="/about">À propos</a> </span>
                </div>
            </div>
        </div>
    </div>

    <div class="section cta">
        <div class="container">
            <div class="row">
                <div class="col-lg-5">
                    <div class="shop">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="section-heading">
                                    <h6>À propos de nous</h6>
                                    <h5>Bienvenue chez <span class="text-primary"><?= WEB_NAME ?></span> votre partenaire technologique de confiance.</h5>
                                </div>
                                <p><?= Config::get("WEB_SLOGAN") ?></p>
                                <div class="main-button">
                                    <a href="/shop">Naviguer</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 offset-lg-2 align-self-end">
                    <div class="subscribe">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="section-heading">
                                    <h6>Notre Mission</h6>
                                    <h2>Fournir du matériel <em>informatique performant</em> et accessible à tous.</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>