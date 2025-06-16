<div class="wrap">
    <h1><?php _e('Customer Appreciation Perks Settings', CMITCAP_TEXT_DOMAIN); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('cmitcap_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="cmitcap_enabled"><?php _e('Enable Feature', CMITCAP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="cmitcap_enabled" name="cmitcap_enabled" value="1" <?php checked($settings['enabled'], 'yes'); ?>>
                    <p class="description"><?php _e('Enable or disable the freebie popup feature.', CMITCAP_TEXT_DOMAIN); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="cmitcap_expiration_date"><?php _e('Expiration Date', CMITCAP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <input type="date" id="cmitcap_expiration_date" name="cmitcap_expiration_date" value="<?php echo esc_attr($settings['expiration_date']); ?>">
                    <p class="description"><?php _e('Set when the freebie offer should expire. Leave empty for no expiration.', CMITCAP_TEXT_DOMAIN); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label><?php _e('Product Categories', CMITCAP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <?php
                    $product_categories = get_terms(array(
                        'taxonomy' => 'product_cat',
                        'hide_empty' => false,
                    ));
                    
                    if (!empty($product_categories) && !is_wp_error($product_categories)) {
                        foreach ($product_categories as $category) {
                            ?>
                            <label style="display: block; margin-bottom: 5px;">
                                <input type="checkbox" name="cmitcap_categories[]" value="<?php echo esc_attr($category->term_id); ?>"
                                    <?php checked(in_array($category->term_id, $settings['selected_categories'])); ?>>
                                <?php echo esc_html($category->name); ?>
                            </label>
                            <?php
                        }
                    }
                    ?>
                    <p class="description"><?php _e('Select product categories to include in the freebie selection.', CMITCAP_TEXT_DOMAIN); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label><?php _e('Perk Products', CMITCAP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <?php
                    $args = array(
                        'post_type' => 'product',
                        'posts_per_page' => -1,
                        'post_status' => 'publish',
                    );
                    
                    $products = get_posts($args);
                    
                    if (!empty($products)) {
                        foreach ($products as $product) {
                            $product_obj = wc_get_product($product->ID);
                            ?>
                            <label style="display: block; margin-bottom: 5px;">
                                <input type="checkbox" name="cmitcap_freebie_products[]" value="<?php echo esc_attr($product->ID); ?>"
                                    <?php checked(in_array($product->ID, $settings['freebie_products'])); ?>>
                                <?php echo esc_html($product->post_title); ?> 
                                (<?php echo wc_price($product_obj->get_price()); ?>)
                            </label>
                            <?php
                        }
                    }
                    ?>
                    <p class="description"><?php _e('Select products to offer as freebies. These products will be added to the cart at zero cost.', CMITCAP_TEXT_DOMAIN); ?></p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="cmitcap_save_settings" class="button-primary" value="<?php _e('Save Settings', CMITCAP_TEXT_DOMAIN); ?>">
        </p>
    </form>
</div> 