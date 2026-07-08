<?php

use Src\Helpers\Config;
?>
<style>
    :root {
        --primary-color: #6a11cb;
        --secondary-color: #2575fc;
        --accent-color: #ff4b4b;
        --bg-color: #f4f5f7;
        --text-color: #333;
        --card-bg: #ffffff;
        --border-radius: 12px;
        --box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        --transition: all 0.4s ease-in-out;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        scroll-behavior: smooth;
    }

    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.7;
        color: var(--text-color);
        background-color: var(--bg-color);
        padding-bottom: 3rem;
    }

    .container {
        width: 90%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Header Section */
    .page-heading {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2.5rem 0;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .page-heading h3 {
        font-size: 2.7rem;
        font-weight: 700;
        text-transform: uppercase;
        text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.3);
        letter-spacing: 1px;
    }

    /* Headings */
    h2.h2 {
        font-size: 2.4rem;
        color: var(--primary-color);
        margin: 2.5rem 0 1.5rem;
        padding-bottom: 0.7rem;
        border-bottom: 4px solid var(--secondary-color);
        display: inline-block;
    }

    h3 {
        font-size: 2rem;
        color: var(--secondary-color);
        margin: 2.5rem 0 1.5rem;
        display: flex;
        align-items: center;
        font-weight: 600;
    }

    h4 {
        font-size: 1.6rem;
        color: var(--primary-color);
        margin: 2rem 0 1.5rem;
        display: flex;
        align-items: center;
        font-weight: 500;
    }

    h5 {
        font-size: 1.3rem;
        color: var(--secondary-color);
        margin: 1.5rem 0;
        display: flex;
        align-items: center;
        font-weight: 500;
    }

    /* Document Sections */
    .doc-section {
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 2rem;
        margin-bottom: 2.5rem;
        transition: var(--transition);
        border-left: 5px solid var(--primary-color);
    }

    .doc-section:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 25px rgba(0, 0, 0, 0.15);
    }

    /* Lists */
    ul {
        list-style-type: none;
        margin-left: 1.5rem;
        margin-bottom: 1.2rem;
    }

    ul li {
        position: relative;
        padding: 0.4rem 0;
    }

    ul li::before {
        content: "▶";
        color: var(--primary-color);
        font-weight: bold;
        position: absolute;
        left: -1.2rem;
        font-size: 0.8rem;
    }

    ul ul {
        margin-left: 1.5rem;
        margin-top: 0.7rem;
    }

    ul ul li::before {
        content: "▸";
    }

    /* Warning and Info Boxes */
    .warning,
    .info {
        border-radius: var(--border-radius);
        padding: 1.2rem;
        margin: 1.5rem 0;
        display: flex;
        align-items: center;
        font-style: italic;
    }

    .warning {
        background-color: rgba(255, 75, 75, 0.1);
        border-left: 5px solid var(--accent-color);
    }

    .info {
        background-color: rgba(37, 117, 252, 0.1);
        border-left: 5px solid var(--secondary-color);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-heading h3 {
            font-size: 2.2rem;
        }

        h2.h2 {
            font-size: 2rem;
        }

        h3 {
            font-size: 1.8rem;
        }

        h4 {
            font-size: 1.5rem;
        }
    }

    /* Theme Toggle and Navigation Dots */
    .theme-toggle {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        z-index: 100;
        transition: var(--transition);
    }

    .nav-dots {
        position: fixed;
        top: 50%;
        right: 30px;
        transform: translateY(-50%);
        display: flex;
        flex-direction: column;
        gap: 15px;
        z-index: 100;
    }

    .nav-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background-color: rgba(37, 117, 252, 0.3);
        transition: var(--transition);
        cursor: pointer;
    }

    .nav-dot.active {
        background-color: var(--primary-color);
        transform: scale(1.4);
    }
</style>
<div class="page-heading">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h3><i class="fas fa-book icon-animate"></i> Documentation</h3>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="nav-dots">
        <div class="nav-dot active" data-section="intro"></div>
        <div class="nav-dot" data-section="dashboard"></div>
        <div class="nav-dot" data-section="managers"></div>
        <div class="nav-dot" data-section="products"></div>
        <div class="nav-dot" data-section="categories"></div>
        <div class="nav-dot" data-section="articles"></div>
    </div>

    <h2 class="h2" id="intro"><i class="fas fa-wrench icon-animate"></i> Fonctionnement du site</h2>

    <div class="doc-section">
        <div>
            Bonsoir, <span><?= Config::get('WEB_ADMIN_USERNAME') ?></span> <br>
            Merci d'avoir choisi notre service. <br>
            Cette documentation vous guidera à travers le fonctionnement de votre site.
        </div>
    </div>

    <div class="doc-section">
        <h3><i class="fas fa-rocket icon-animate"></i> Initialisation du site</h3>
        <div>
            Une fois que vous avez correctement configuré le fichier <span>.env</span>, un processus automatisé se déclenche afin de créer toutes les tables nécessaires au bon fonctionnement du site. Ce processus inclut également l'initialisation d'exemples de produits, de catégories et d'informations pour les pages principales (Accueil, Contact et Boutique). De plus, un compte administrateur est automatiquement généré.
        </div>
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">Fichier <b>.env</b></h4>
            <p>
                Le fichier contient les variables globales necessaires pour le fonctionnement du site, parmi les :
            </p>
            <div>
                <b>DB_HOST</b>: nom d'hôte pour la base de donnée
                <br><b>DB_NAME</b>: nom de la base de données
                <br><b>DB_USER</b>: l'utilisateur pour la base de données
                <br><b>DB_PASS</b>: mot de passe pour l'utilisateur pour la base de données <span class="text-muted">(le systeme chiffre automatiquement la valeur et la remplace dans le fichier)</span>
                <hr>
                <b>WEB_ADMIN_USERNAME</b>: nom d'utilisateur initial de l'administrateur
                <br><b>WEB_ADMIN_EMAIL</b>: Email initial de l'administrateur
                <br><b>WEB_EMAIL</b>: Email de l'application (utilisé pour les envoies des emails)
                <br><b>WEB_EMAIL_PASSWORD</b>: Email initial de l'application <span class="text-muted">(le systeme chiffre automatiquement la valeur et la remplace dans le fichier)</span>
                <hr>Et autres variables neccessaires à configurer pour les commandes, le systeme de mail etc..
                <br>
                <b>Note</b>: Vous pouvez toujours utiliser le fichier <b>.env.copy</b> pour créer une autre configuration pour votre site en éleminant la partie <b>.copy</b>
            </div>
        </div>
        <h4><i class="fas fa-user icon-animate"></i> Le compte administrateur</h4>
        <div>
            Un email d'initialisation de mot de passe est envoyé à l'adresse email configurée comme celle de l'administrateur. Cet email contient un lien permettant d'accéder à la page de réinitialisation du mot de passe.
            <div class="warning">
                Ce lien est valable pendant une heure. Si ce délai expire, vous devrez demander un nouvel email depuis la page <a href="/forgot-password">Mot de passe oublié</a> en saisissant votre adresse email.
            </div>
        </div>

        <h4><i class="fas fa-key icon-animate"></i> La page de connexion -<a href="/login"> Se connecter</a></h4>
        <div>
            Pour simplifier le processus de connexion, vous pouvez utiliser l'un des identifiants suivants :
            <ul>
                <li>Nom d'utilisateur</li>
                <li>Adresse email</li>
                <li>Numéro de téléphone <span>(Doit être configuré dans votre tableau de bord)</span></li>
            </ul>
            accompagné de votre mot de passe.
            <div class="info">
                Pour des raisons de sécurité, les tentatives de connexion sont limitées.
            </div>
        </div>
    </div>

    <div class="doc-section" id="dashboard">
        <h3><i class="fas fa-chart-pie icon-animate"></i> Tableau de bord</h3>
        <div>
            Votre tableau de bord vous offre un contrôle total sur votre site avec les fonctionnalités suivantes :
            <ul>
                <li>Gestion des managers</li>
                <li>Gestion des produits</li>
                <li>Gestion des catégories</li>
                <li>Gestion des aerticles</li>
                <li>Gestion des commandes</li>
                <li>Gestion des avis et feedbacks</li>
                <li>Gestion des pages principales</li>
                <li>Gestion de votre profil</li>
            </ul>
        </div>
    </div>

    <div class="doc-section" id="managers">
        <h4><i class="fas fa-users icon-animate"></i> Gestion des managers</h4>
        <div>
            Il existe trois types de comptes managers :
            <ul>
                <li>
                    <b>Admin :</b>
                    <ul>
                        <li>Créé automatiquement à l'initialisation du site (ne peut pas être ajouté manuellement).</li>
                        <li>Détient un contrôle total sur le site.</li>
                        <li>Seul l'admin peut gérer les managers et les pages principales.</li>
                    </ul>
                </li>
                <li>
                    <b>Super Manager :</b>
                    <ul>
                        <li>Peut publier et gérer tous les produits et catégories.</li>
                        <li>A le droit de modifier et supprimer n'importe quel produit, catégorie ou article.</li>
                        <li>Gérer la quantité en stock des produtis.</li>
                    </ul>
                </li>
                <li>
                    <b>Manager :</b>
                    <ul>
                        <li>Peut créer de nouveaux produits et modifier uniquement ses propres produits.</li>
                        <li>Ses produits doivent être validés par un Super Manager ou l'Admin avant d'être visibles.</li>
                    </ul>
                </li>
            </ul>

            <h5><i class="fas fa-plus-circle"></i> Création d'un compte manager</h5>
            <div>
                Un compte est défini par :
                <ul>
                    <li>Un nom d'utilisateur</li>
                    <li>Un nom complet</li>
                    <li>Une adresse email</li>
                    <li>Un numéro de téléphone</li>
                    <li>Un mot de passe</li>
                    <li>Un status (<b>actif</b> ou <b>inactif</b>)</li>
                    <li>Un type (<b>manager</b> ou <b>super manager</b>)</li>
                </ul>
            </div>

            <h5><i class="fas fa-edit"></i> Modification d'un compte</h5>
            <div>
                <ul>
                    <li>Définir le statut du compte (Actif/Inactif).</li>
                    <li>Changer le rôle du compte (Manager ou Super Manager).</li>
                    <li>réinitialisation du mot de passe</li>
                </ul>
                <div class="warning">
                    Un compte inactif est inaccessible.
                </div>
            </div>
        </div>
    </div>
    <div class="doc-section" id="products">
        <h4><i class="fas fa-shopping-cart icon-animate"></i> Gestion des produits</h4>
        <div>
            <h5><i class="fas fa-thumbtack"></i> Création d'un produit</h5>
            <p>Un produit est composé de plusieurs champs répartis comme suit :</p>
            <ul>
                <li><b>Champs obligatoires :</b>
                    <ul>
                        <li><b>Titre</b> : Peut être identique à d'autres produits (non recommandé).</li>
                        <li><b>Slug (URL)</b> : Généré automatiquement depuis le titre via JavaScript. Doit être unique.</li>
                        <li><b>Prix</b> : Doit être renseigné avec le caractère <code>.</code> pour les décimales (ex. 12.99).</li>
                    </ul>
                </li>
                <li><b>Champs optionnels ou réservés :</b>
                    <ul>
                        <li><b>Stock</b> : Quantité en stock. Doit être saisi par un Super Manager ou l'Admin.</li>
                        <li><b>Réduction (%)</b> : Réduction appliquée. Par défaut 0.</li>
                        <li><b>Réduction à partir de</b> : Nombre de produits nécessaires pour appliquer la réduction.</li>
                        <li><b>Description</b> : Texte libre sur le produit.</li>
                        <li><b>Meta description</b>, <b>Tags</b>, <b>Meta tags</b> : Pour le SEO.</li>
                        <li><b>Catégories</b> : Un produit peut appartenir à plusieurs catégories ou aucune.</li>
                        <li><b>Images</b> : Jusqu’à 4 images au format JPEG/PNG. Taille max. 5 Mo/image.</li>
                    </ul>
                </li>
            </ul>
            <div class="alert alert-warning">
                <b>Important :</b> Tout produit créé est <strong>invisible par défaut</strong>. Seuls les Super Managers et l’Admin peuvent modifier sa visibilité.
            </div>

            <h5><i class="fas fa-edit"></i> Modification d'un produit</h5>
            <p>Lors de la modification :</p>
            <ul>
                <li>Les champs obligatoires doivent rester remplis.</li>
                <li>La visibilité peut être modifiée (Visible/Invisible).</li>
                <li>Les métadonnées peuvent être mises à jour pour améliorer le référencement.</li>
            </ul>

            <h5><i class="fas fa-trash"></i> Suppression d'un produit</h5>
            <p>
                Supprime définitivement le produit ainsi que toutes ses images associées.
            </p>
        </div>
    </div>
    <div class="doc-section" id="categories">
        <h4><i class="fas fa-folder icon-animate"></i> Gestion des catégories</h4>
        <div>
            <h5><i class="fas fa-plus-circle"></i> Création d'une catégorie</h5>
            <p>Chaque catégorie comprend les champs suivants :</p>
            <ul>
                <li><b>Titre (obligatoire)</b> :
                    <ul>
                        <li>Doit être unique et ne contenir que des lettres et chiffres (sans caractères spéciaux).</li>
                        <li>Utilisé comme identifiant (slug) de la catégorie.</li>
                    </ul>
                </li>
                <li><b>Champs optionnels</b> :
                    <ul>
                        <li><b>Description</b> : Courte présentation de la catégorie.</li>
                        <li><b>Tags</b> : Mots-clés liés à la catégorie.</li>
                        <li><b>Réduction</b> : <i>Pas d'effet dans cette version (prévu pour une future mise à jour).</i></li>
                        <li><b>Image</b> : Une seule image autorisée (formats JPEG/PNG, max 5 Mo).</li>
                    </ul>
                </li>
            </ul>

            <h5><i class="fas fa-edit"></i> Modification</h5>
            <p>Les champs de la catégorie peuvent être mis à jour à tout moment, en respectant les contraintes sur le titre et l'image.</p>

            <h5><i class="fas fa-trash"></i> Suppression</h5>
            <p>Supprime définitivement la catégorie ainsi que son image associée. Cette action est irréversible.</p>
        </div>
    </div>
    <div class="doc-section" id="articles">
        <h4><i class="fas fa-newspaper icon-animate"></i> Gestion des articles</h4>
        <div>
            <h5><i class="fas fa-plus-circle"></i> Création d’un article</h5>
            <ul>
                <li><b>Titre</b> : Obligatoire.</li>
                <li><b>Slug (URL)</b> : Généré automatiquement à partir du titre.</li>
                <li><b>Image à la une</b> : Format JPEG ou PNG recommandé.</li>
                <li><b>Contenu</b> : Peut contenir du HTML enrichi.</li>
                <li><b>Extrait</b> : Brève description (200 mots maximum).</li>
                <li><b>Méta description</b> : Pour le SEO.</li>
                <li><b>Méta tags</b> : Mots-clés pour améliorer le référencement.</li>
            </ul>

            <h5><i class="fas fa-edit"></i> Modification</h5>
            <p>Vous pouvez mettre à jour tous les champs d’un article, y compris son contenu HTML et ses métadonnées.</p>

            <h5><i class="fas fa-trash"></i> Suppression</h5>
            <p>Supprime l’article ainsi que son image associée.</p>
        </div>
    </div>

    <div class="doc-section" id="pages">
        <h4>
            <i class="fas fa-file icon-animate"></i> Gestion des pages
        </h4>
        <div>
            <p>
                Cette section vous permet de :
            <ul>
                <li>Modifier les meta données des pages principales</li>
                <li>
                    Modification de certaines informations affichées dans le flux de ce pages
                </li>
                <li>
                    Manipulation de certaines sctions personnalisées dans les pages (Acceuil et Boutique)
                </li>
            </ul>
            </p>
        </div>
        <div>
            <h5>
                Les meta données
            </h5>
            <p>
                cette onglet vous pememt de modifier les meta données des pages (description, tags et auteur)
            </p>
        </div>
        <div>
            <h5>
                La page d'Acceuil
            </h5>
            <div>
                <h5>Puisque c'est l'entrée premiere de votre site, vous pouvez modifier:</h5>
                <div>
                    <h5>
                        La banniere :
                    </h5>
                    <div>
                        Toute information affichée dans cette section est manipulée depuis ici
                        <ul>
                            <li>Le petit titre</li>
                            <li>Le gros titre</li>
                            <li>La description</li>
                            <li>Le produit affiché</li>
                        </ul>
                    </div>
                </div>
                <div>
                    <h5>
                        La liste des produits en tendance :
                    </h5>
                    <div>
                        Vous pouvez choisir plusieurs produits à afficher
                    </div>
                </div>
                <div>
                    <h5>
                        La liste des catégories en tendance :
                    </h5>
                    <div>
                        Vous pouvez choisir 4 catégories à afficher (4 au moins pour les afficher)
                    </div>
                </div>
            </div>
            <h5>La boutique</h5>
            <div>
                Pour cette version de l'application, vous pouvez seulement modifier les meta données de cette page
            </div>
        </div>
    </div>

    <div class="doc-section" id="orders">
        <h4><i class="fas fa-box icon-animate"></i> Gestion des commandes</h4>
        <div>
            <p><b>Super Manager</b> et <b>Admin</b> peuvent consulter, modifier et supprimer toutes les commandes.</p>
            <ul>
                <li>Visualisation des détails d’une commande (client, produits, quantités, montant total).</li>
                <li>Changement de statut (en attente, confirmé, expédié, annulé...).</li>
                <li>Suppression définitive si nécessaire.</li>
            </ul>
        </div>
        <div class="alert alert-info">
            Une commande est enregistrée sous le statut <b>"En traitement"</b>. Le client peut l'annuler dans un délai défini dans le fichier de configuration.
        </div>
    </div>

    <div class="doc-section">
        <h4><i class="fas fa-bullhorn icon-animate"></i> Gestion des avis & feedbacks</h4>
        <div>
            <ul>
                <li>Répondre aux messages des clients.</li>
                <li>Envoyer un email groupé aux abonnés de la newsletter.</li>
            </ul>
        </div>
    </div>

    <div class="doc-section">
        <h4><i class="fas fa-file-alt icon-animate"></i> Gestion des pages principales</h4>
        <div>
            Les pages principales du site sont :
            <ul>
                <li>Accueil</li>
                <li>Boutique</li>
                <li>Contact</li>
            </ul>
            Chaque page possède ses propres paramètres de configuration.
        </div>
    </div>

    <div class="doc-section">
        <h4><i class="fas fa-lock icon-animate"></i> Gestion du profil</h4>
        <div>
            Vous pouvez modifier :
            <ul>
                <li>Votre nom d'utilisateur.</li>
                <li>Votre nom complet.</li>
                <li>Votre email.</li>
                <li>Votre numéro de téléphone.</li>
            </ul>
        </div>
        <div class="info">
            <b>Mot de passe: </b> La modification du mot de passe se fait seulement par envoie d'un lien de reinitialisation vers l'email associé au compte
        </div>
    </div>

    <div class="doc-section">
        <h4><i class="fas fa-lock icon-animate"></i>Processus de commande</h4>
        <div>
            <h5>Le panier</h5>
            Chaque produit commandé est ajouté automatiquement au panier,
            Si un produit existe déja dans le panier, un message s'affiche pour notifier l'utilisateur que la quantité demandé à nouveau va être ajouter à la quantité existante
            Le panier permet de :
            <ul>
                <li>Modifier la quantité commandée d'un produit</li>
                <li>Supprimer un produit du panier</li>
                <li>Confirmer la commande</li>
                <li>Consulter une commande précédente</li>
            </ul>
            <div class="info">
                Pour cette version du site, la quantité en stock n'a aucun effet, et elle n'est pas affichée dans la page des produit donc, le client peut commander une quantité infinée du même produit
            </div>
        </div>
        <div>
            <h4>
                Confirmation d'une commande
            </h4>
            <div>
                Pour confirmer sa commande, le client est demandé d'entrez :
                <ul>
                    <li>Son nom et prenom</li>
                    <li>Email</li>
                    <li>Télephone</li>
                    <li>Adresse de livraison</li>
                    <li>Code postal (Optionnel)</li>
                    <li>Ville (Optionnel)</li>
                </ul>
            </div>
        </div>
        <div>
            <h5>Lorsque un client valide sa commande, ça implique:</h5>
            <ul>
                <li>L'envoi du recapulitaitif de la commnde au <span>client</span> </li>
                <li>L'envoi du recapulitaitif de la commnde à <span>la boite d'email du site</span> </li>
                <li>
                    Enregistrement de la commande dans la base de données
                </li>
            </ul>
            <div>
                <h5>Consultation d'une commande</h5>
                <div>
                    Afin de consulter sa commande, le client doit entrez :
                    <ul>
                        <li>Son email/ numero de telephone</li>
                        <li>La reference de sa commande</li>
                    </ul>
                    La page de Consultation permet aussi au client <span>d'annuler</span> sa commande dans la durée configurée dans le ficheir de config
                </div>
            </div>
            <div class="info">
                Un client peut consulter ses commande dans la page de - <a href="/consulter-commande">Consultation</a>- de commande
            </div>
        </div>
    </div>
</div>

<div class="theme-toggle">
    <i class="fas fa-moon"></i>
</div>

<script>
    // Theme Toggle Functionality
    const themeToggle = document.querySelector('.theme-toggle');
    const root = document.documentElement;
    let darkMode = false;

    themeToggle.addEventListener('click', function() {
        darkMode = !darkMode;
        if (darkMode) {
            root.style.setProperty('--bg-color', '#2c3e50');
            root.style.setProperty('--text-color', '#f8f9fa');
            root.style.setProperty('--card-bg', '#34495e');
            root.style.setProperty('--secondary-color', '#3498db');
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            root.style.setProperty('--bg-color', '#f8f9fa');
            root.style.setProperty('--text-color', '#333');
            root.style.setProperty('--card-bg', 'white');
            root.style.setProperty('--secondary-color', '#2c3e50');
            themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        }
    });

    // Navigation Dots Functionality
    const navDots = document.querySelectorAll('.nav-dot');
    const sections = {
        'intro': document.querySelector('#intro'),
        'dashboard': document.querySelector('#dashboard'),
        'managers': document.querySelector('#managers'),
        'products': document.querySelector('#products'),
        'categories': document.querySelector('#categories')
    };

    navDots.forEach(dot => {
        dot.addEventListener('click', function() {
            const section = this.getAttribute('data-section');
            if (sections[section]) {
                sections[section].scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Update active dot on scroll
    window.addEventListener('scroll', function() {
        let current = '';
        for (const key in sections) {
            const section = sections[key];
            if (section) {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= (sectionTop - 200)) {
                    current = key;
                }
            }
        }

        navDots.forEach(dot => {
            dot.classList.remove('active');
            if (dot.getAttribute('data-section') === current) {
                dot.classList.add('active');
            }
        });
    });

    // Add animation to icons on section hover
    const docSections = document.querySelectorAll('.doc-section');
    docSections.forEach(section => {
        section.addEventListener('mouseenter', function() {
            const icon = this.querySelector('.icon-animate');
            if (icon) {
                icon.style.animation = 'none';
                void icon.offsetWidth; // Trigger reflow
                icon.style.animation = 'pulse 2s infinite';
            }
        });
    });
</script>