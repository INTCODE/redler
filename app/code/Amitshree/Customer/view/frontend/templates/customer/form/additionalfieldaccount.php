<fieldset class="fieldset create account" data-hasrequired="<?php /* @escapeNotVerified */ echo __('* Required Fields') ?>"> 
    <p>
        <?php if ($logo = $block->getLogoUrl()): ?>
            <img src="<?php echo $logo ?>" alt="logo" />
        <?php endif; ?>
    </p>
 
     <p>
        <div class="field customer_documents ">
            <label for="customer_documents" class="label"><span><?php /* @escapeNotVerified */
                    echo __('Logo Image') ?></span></label>
            <div class="control">
                <input type="file" name="customer_documents[]" id="customer_documents" title="<?php /* @escapeNotVerified */
                echo __('Logo Image') ?>" class="input-text" data-validate="{required:false}">
            </div>
        </div>
    </p>
</fieldset>