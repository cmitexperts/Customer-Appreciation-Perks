<?php
// Get selected freebie products
$freebie_products = array();
if (!empty($settings['freebie_products'])) {
    $args = array(
        'post_type' => 'product',
        'post__in' => $settings['freebie_products'],
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );
    
    $freebie_products = get_posts($args);
}
?>

<div class="cmitcap-popup-overlay"></div>
<div class="cmitcap-popup">
    <button class="cmitcap-close-button">&times;</button>
    
    <div class="cmitcap-popup-header">
        <h2 class="cmitcap-popup-title"><?php _e('Choose Your Perk!', CMITCAP_TEXT_DOMAIN); ?></h2>
        <p class="cmitcap-popup-subtitle"><?php _e('Select one item from our special collection as a token of our appreciation.', CMITCAP_TEXT_DOMAIN); ?></p>
    </div>
    
    <?php if (!empty($freebie_products)) : ?>
        <div class="swiper cmitcap-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($freebie_products as $product) : 
                    $product_obj = wc_get_product($product->ID);
                    $image_id = $product_obj->get_image_id();
                    $image_url = wp_get_attachment_image_url($image_id, 'medium');
                ?>
                    <div class="swiper-slide">
                        <div class="cmitcap-product-item">
                            <?php if ($image_url) : ?>
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product->post_title); ?>" class="cmitcap-product-image">
                            <?php endif; ?>
                            
                            <h3 class="cmitcap-product-title"><?php echo esc_html($product->post_title); ?></h3>
                            
                            <button class="cmitcap-select-button" data-product-id="<?php echo esc_attr($product->ID); ?>">
                                <?php _e('Select Perk', CMITCAP_TEXT_DOMAIN); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>
            <!-- Add Navigation -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    <?php else : ?>
        <p><?php _e('No freebie products available at the moment.', CMITCAP_TEXT_DOMAIN); ?></p>
    <?php endif; ?>
</div> 