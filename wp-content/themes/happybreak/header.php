<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package storefront
 */

?>
    <!doctype html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
        <link rel="profile" href="http://gmpg.org/xfn/11">

        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
        <?php wp_head(); ?>
    </head>


<body <?php body_class(); ?>>

<?php do_action( 'storefront_before_site' ); ?>
<?php do_action( 'storefront_before_header' ); ?>
<div id="wrapper" class="compte-non-valide">

    <div id="header">
        <div class="header container">
            <div class="row">
                <div class="region region-header">
                    <div id="block-block-40" class="block block-block col-lg-4 col-md-4 col-sm-4 ">
                        <div class="content"><a id="logo" href="https://www.happybreak.com/" target="_blank"><img src="<?php echo get_stylesheet_directory_uri() ?>/assets/images/logo_header.png"></a></div>
                    </div>
                    <div id="block-block-14" class="block block-block col-lg-4 col-md-4 col-sm-4 middle telephone ">
                        <div class="content">
                            <div class="tooltip tooltip-effect-1"><p class="phone_text tooltip-item bottom orange">09 80
                                    01 01 01</p><span
                                    class="tooltip-content clearfix">Appel non surtaxé. 7j/7j de 8h à 22h</span></div>
                        </div>
                    </div>
                    <div id="block-block-78" class="block block-block">
                        <div class="content">

                        </div>
                    </div>
                </div>
                <div class="header_left col-lg-4 col-md-4 col-sm-4 ">
                    <div class="region region-header-left">
                        <div id="block-menu-menu-menu-se-connecter-haut" class="block block-menu menu_login">
                            <div class="content">
                                <ul class="menu">
                                    <li class="first leaf"><a href="https://www.happybreak.com/user/register" target="_blank">Je crée mon compte membre</a></li>
                                    <li class="last leaf"><a href="https://www.happybreak.com/panier" target="_blank">Panier</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <div id="header_menu">
        <div class="header_menu container">
            <div class="row"><a href="#" id="open-menu">Menu</a>

                <div class="region region-header-menu">
                    <div id="block-menu-menu-menu-principal" class="block block-menu col-md-12 menuheader">
                        <div class="content">
                            <ul class="menu">
                                <li class="first leaf active-trail">
                                    <a href="https://www.happybreak.com" class="active-trail active" target="_blank">Accueil</a></li>
                                <li class="leaf"><a href="https://www.happybreak.com/showroom" target="_blank">Je commande</a></li>
                                <li class="leaf"><a href="https://www.happybreak.com/recherche" target="_blank">Je cherche un hôtel</a></li>
                                <li class="leaf"><a href="https://www.happybreak.com/comment-ca-marche" target="_blank">Comment ça marche ?</a></li>
                                <li class="leaf"><a href="https://www.happybreak.com/qui-somme-nous" target="_blank">Qui sommes-nous ?</a></li>
                                <li class="leaf"><a href="https://www.happybreak.com/vos-questions" target="_blank">J&#039;ai une question</a></li>
                                <li class="last leaf"><a href="https://www.happybreak.com/contactez-nous" target="_blank">Contactez-nous</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>




    <?php
    /**
     * Functions hooked in to storefront_before_content
     *
     * @hooked storefront_header_widget_region - 10
     */
    do_action( 'storefront_before_content' ); ?>

    <div id="page" class="container">

<?php

