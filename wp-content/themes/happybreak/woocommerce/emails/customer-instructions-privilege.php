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
<p><?php _e( "D’ici quelques jours vous recevrez par la poste vos cartes privilèges Happybreak.", 'happybreak' ); ?></p>
<p><?php _e( "Activez votre carte quand vous le souhaitez, et pendant 1 an, vous pourrez réserver autant de fois que vous le voulez dans nos hôtels partenaires et bénéficier de 50% de réduction.", 'happybreak' ); ?></p>
<h3><?php _e( "Comment ça marche ?", 'happybreak' ); ?></h3>
<ul>
    <li><?php _e( "Une fois votre carte reçue, rendez-vous sur www.happybreak.com", 'happybreak' ); ?></li>
    <li><?php _e( "Cliquez sur « J’active ma carte » puis, « Je crée un compte »", 'happybreak' ); ?></li>
    <li><?php _e( "Remplissez les informations et saisissez le numéro de votre carte", 'happybreak' ); ?></li>
</ul>
<p><?php _e( "Après avoir « validé », vous pourrez accéder à l’ensemble des informations permettant de contacter nos hôteliers partenaires.", 'happybreak' ); ?></p>
<p><strong><?php _e( "Rappel : la durée de validité de la carte ne s'enclenche que le jour de l’activation.", 'happybreak' ); ?></strong></p>
<?php

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
 ?>
