<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

$ext  = version_compare(WC()->version, '2.6', '>=') ? '.svg' : '.png';
$visa = '<img src="' . WC_HTTPS::force_https_url(WC()->plugin_url() . '/assets/images/icons/credit-cards/visa' . $ext) . '" alt="Visa" width="60" />';
$masterCard = '<img src="' . WC_HTTPS::force_https_url(WC()->plugin_url() . '/assets/images/icons/credit-cards/mastercard' . $ext) . '" alt="Mastercard" width="60" />';
$americanExpress = '<img src="' . WC_HTTPS::force_https_url(WC()->plugin_url() . '/assets/images/icons/credit-cards/amex' . $ext) . '" alt="Amex" width="60" />';
?>
<p class="cc-type form-row form-row-wide">
    <input type="radio" name="cc-type" value="0"/>
    <?php echo $visa; ?>
    <input type="radio" name="cc-type" value="1"/>
    <?php echo $masterCard; ?>
    <input type="radio" name="cc-type" value="2"/>
    <?php echo $americanExpress; ?>
</p>
