<fieldset class="fieldset create account" data-hasrequired="<?php /* @escapeNotVerified */
echo __('* Required Fields') ?>">
    <legend class="legend">
        <span>
        <?php 
            /* @escapeNotVerified */ 
            echo __('Logo') 
        ?>
        </span>
    </legend>
 
    <p>
        <?php if ($logo = $block->getLogoUrl()): ?>
            <img src="<?php echo $logo ?>" alt="logo" />
        <?php endif; ?>
    </p>
 
     <p>
        <div class="field my_customer_image ">
            <label for="my_customer_image" class="label"><span><?php /* @escapeNotVerified */
                    echo __('Logo Image') ?></span></label>
            <div class="control">
                <input type="file" name="my_customer_image" id="my_customer_image" title="<?php /* @escapeNotVerified */
                echo __('Logo Image') ?>" class="input-text" data-validate="{required:false}">
            </div>
        </div>
    </p>
</fieldset>