<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

$wpUploadDir = wp_upload_dir();
$errorFiles  = glob($wpUploadDir['basedir'] . '/happybreak-prospects-import/*.csv');
?>

<div class="wrap">

    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <form id="happybreak-prospect-import-form" name="happybreak-prospect-import" method="post"
          action="<?php echo esc_html(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
        <p>
            <label><?= __('Fichier de prospects', 'happybreak-prospect-import'); ?></label>
            <input type="file" name="happybreak-prospect-import-file" accept=".csv" required/>
        </p>
        <p>
            <label><?= __('Lignes à sauter', 'happybreak-prospect-import'); ?></label>
            <input type="number" name="happybreak-prospect-import-skip-lines" value="1"/>
        </p>

        <input type="hidden" name="action" value="happybreak-prospect-import-process"/>


        <?php
        wp_nonce_field('happybreak-prospect-import-process');
        submit_button(__('Importer', 'happybreak-prospect-import'));
        ?>

    </form>

    <?php if (is_array($errorFiles) and count($errorFiles)) : ?>
        <h2><?= __('Dernières lignes échouées', 'happybreak-prospect-import'); ?></h2>
        <p>
            <strong><?= __('ATTENTION : il faut purger ces fichiers dès que possible pour garder les données le plus confidentiel possible</strong>',
                    'happybreak-prospect-import'); ?>
        </p>
        <p>
            <?= __('En cas de lignes échouées, vous pouvez télécharger le fichier du dernier import et corriger directement les valeurs puis resoumettre, <strong>attention à mettre 0 dans "Lignes à sauter"</strong> si le fichier ne comprends pas de ligne d\'entête.',
                'happybreak-prospect-import'); ?>
        </p>
        <ol>
            <?php
            rsort($errorFiles);
            foreach ($errorFiles as $file) :
                ?>
                <li>
                <span><?= __('Exécution du', 'happybreak-prospect-import'); ?> : <?= date_create_from_format('YmdHis',
                        str_replace('.csv', '', basename($file)))->format('Y/m/d H:i:s'); ?></span>
                    - <a href="<?= $wpUploadDir['baseurl'] . '/happybreak-prospects-import/' . basename($file); ?>"
                            target="_blank"><?= basename($file); ?></a>
                    - <a href="<?= $wpUploadDir['baseurl'] . '/happybreak-prospects-import/' . str_replace('.csv', '.log', basename($file)); ?>"
                         target="_blank"><?= str_replace('.csv', '.log', basename($file)); ?></a>
                    - <a href="<?= wp_nonce_url(admin_url('admin-post.php') . '?action=happybreak-prospect-import-delete-csv&name=' . basename($file),
                                'happybreak-prospect-import-delete-csv'); ?>" title="<?= __('Supprimer', 'happybreak-prospect-import'); ?>"><span
                                class="dashicons dashicons-dismiss"></span></a>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <p>
        <a href="<?= admin_url('admin-post.php') . '?action=happybreak-prospect-import-download-template'; ?>"
           target="_blank"><?= __('Télécharger le fichier template', 'happybreak-prospect-import'); ?></a>
    </p>

</div><!-- .wrap -->