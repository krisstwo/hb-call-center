<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package storefront
 */

?>


<?php do_action( 'storefront_before_footer' ); ?>

<?php do_action( 'storefront_after_footer' ); ?>

</div><!-- #page -->

<div id="footer">
    <div id="footer_top">
        <div class="footer_top container">
            <div class="row">
                <div class="region region-footer">
                    <div id="block-block-39" class="block block-block block_footer_top">
                        <div class="content"><img src="<?php echo get_stylesheet_directory_uri() ?>/assets/images/logo_footer.png"></div>
                    </div>
                    <div id="block-menu-menu-f1" class="block block-menu block_footer_top">
                        <div class="content">
                            <ul class="menu">
                                <li class="first leaf"><a href="https://www.happybreak.com/qui-somme-nous" target="_blank">Qui sommes-nous ?</a></li>
                                <li class="last leaf"><a href="https://www.happybreak.com/mentions-legales" target="_blank">Mentions légales</a></li>
                            </ul>
                        </div>
                    </div>
                    <div id="block-menu-menu-f2" class="block block-menu block_footer_top">
                        <div class="content">
                            <ul class="menu">
                                <li class="first leaf"><a href="https://www.happybreak.com/vos-questions" target="_blank">Questions fréquentes</a></li>
                                <li class="last leaf"><a href="https://www.happybreak.com/contactez-nous" target="_blank">Contactez-nous</a></li>
                            </ul>
                        </div>
                    </div>
                    <div id="block-menu-menu-f3" class="block block-menu block_footer_top">
                        <div class="content">
                            <ul class="menu">
                                <li class="first leaf"><a href="https://www.happybreak.com/cgv" target="_blank">Conditions Générales de Vente</a></li>
                                <li class="last leaf"><a href="https://www.happybreak.com/cgu" target="_blank">Conditions Générales d&#039;Utilisation</a></li>
                            </ul>
                        </div>
                    </div>
                    <div id="block-menu-menu-f4" class="block block-menu block_footer_top">
                        <div class="content">
                            <ul class="menu">
                                <li class="first leaf"><a href="https://www.happybreak.com/comment-ca-marche" class="payment" target="_blank">Paiement
                                        sécurisé</a></li>
                                <li class="last leaf"><a href="https://www.happybreak.com/presse" target="_blank">Presse</a></li>
                            </ul>
                        </div>
                    </div>
                    <div id="block-menu-menu-menu-suivez-nous-sur-facebo"
                         class="block block-menu block_footer_top block_footer_facebook">
                        <div class="content">
                            <ul class="menu">
                                <li class="first last leaf"><a
                                        href="https://www.facebook.com/Happybreak-103517903321720/?fref=ts"
                                        target="_blank">Suivez-nous sur Facebook !</a></li>
                            </ul>
                        </div>
                    </div>
                    <div id="block-block-65" class="block block-block mention-alcool col-md-12">
                        <div class="content"><i>L'abus d'alcool est dangereux pour la santé, à consommer avec
                                modération</i></div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <div id="footer_bottom"> Tous droits réservés - Happybreak 2017 - <a href="https://www.advency.fr/" target="_blank">Réalisation
            Advency</a></div>
</div>
<div id="bio_ep_bg"></div>



<?php wp_footer(); ?>



</body>
</html>
