<div class="rubrique_info">
    <?php if ($desc) : ?>
        <div class="row">
            <div class="cat_image col-sm-4 col-xs-12">
                <img src="<?php echo STYLESHEET_DIR_URI . '/pleinevie/assets/images/prix.png';?>" class="header-image img-responsive" alt="" />
            </div>
            <div class="cat_excerpt col-sm-8 col-xs-12">
                <h1 class="titre_prix">Prix pleine vie</h1>
            <?php echo $desc;?>
            </div>
        </div>
    <?php endif; ?>
</div>
