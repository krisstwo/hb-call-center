<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>


<p>
    <p><?php _e( 'Bonjour ', 'happybreak' ); ?> <?php echo $order->get_billing_first_name() . ' ' .$order->get_billing_last_name()  ?></p>


</p>
<p><?php _e( "Nous sommes ravis de vous compter parmi nos membres Happybreak.", 'happybreak' ); ?></p>
<p><?php _e( "Vous avez 1 an pour activer votre e-carte 3 mois.", 'happybreak' ); ?></p>
<p><?php _e( "Dès lors, et pendant 3 mois, vous pourrez réserver autant de fois que vous le voulez dans nos hôtels partenaires et bénéficier de 50% de réduction. ", 'happybreak' ); ?></p>
<h3><?php _e( "Comment ça marche ?", 'happybreak' ); ?></h3>
<ul>
    <li><?php _e( "Pour activer votre e-carte 3 mois, rendez-vous sur", 'happybreak' ); ?> : <a href="https://www.happybreak.com/operation2pour1">www.happybreak.com/operation2pour1</a></li>
    <li><?php _e( "Remplissez le formulaire d’inscription", 'happybreak' ); ?></li>
    <li><?php _e( "Entrez votre code unique dans la case prévu à cet effet et validez", 'happybreak' ); ?></li>
</ul>
<p></p>
<p style="text-align: center;"><?php _e( "VOTRE CODE UNIQUE", 'happybreak' ); ?> : <?php echo $activation_code ?></p>
<p></p>
<p><?php _e( "Vous venez d’activer votre e-carte 3 mois : Regardez vos emails vous allez recevoir votre e-carte 3 mois.", 'happybreak' ); ?></p>
<p><strong><?php _e( "Rappel : la durée de validité de la carte s’enclenche dès que vous avez activé votre carte sur le site www.happybreak.com/opération2pour1", 'happybreak' ); ?></strong></p>
<?php

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
 ?>
