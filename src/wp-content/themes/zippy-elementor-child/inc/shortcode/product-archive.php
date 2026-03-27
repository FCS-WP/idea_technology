<?php
function custom_woo_product_cards_shortcode($atts)
{
    $atts = shortcode_atts([
        'limit' => 6,
        'columns' => 3,
        'category' => '',
        'orderby' => 'date',
        'order' => 'DESC',
    ], $atts, 'custom_woo_product_cards');

    $args = [
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => intval($atts['limit']),
        'orderby' => sanitize_text_field($atts['orderby']),
        'order' => sanitize_text_field($atts['order']),
    ];

    if (!empty($atts['category'])) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => array_map('trim', explode(',', $atts['category'])),
            ]
        ];
    }

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>No products found.</p>';
    }

    ob_start();
?>
    <div class="custom-service-grid columns-<?php echo intval($atts['columns']); ?>">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <?php
            $product_id = get_the_ID();
            $product_link = get_permalink($product_id);
            $product_title = get_the_title($product_id);
            $image_url = get_the_post_thumbnail_url($product_id, 'large');

            if (!$image_url) {
                $image_url = wc_placeholder_img_src();
            }

            $terms = get_the_terms($product_id, 'product_cat');
            $category_text = '';

            if (!empty($terms) && !is_wp_error($terms)) {
                $cat_names = wp_list_pluck($terms, 'name');
                $category_text = implode(' / ', array_slice($cat_names, 0, 2));
            }
            ?>
            <a href="<?php echo esc_url($product_link); ?>" class="custom-service-card">
                <div class="custom-service-card__image">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product_title); ?>">
                </div>

                <div class="custom-service-card__overlay"></div>

                <div class="custom-service-card__content">
                    <div class="custom-service-card__meta">
                        <h3 class="custom-service-card__title"><?php echo esc_html($product_title); ?></h3>
                        <?php if ($category_text) : ?>
                            <div class="custom-service-card__category"><?php echo esc_html($category_text); ?></div>
                        <?php endif; ?>
                    </div>

                    <span class="custom-service-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M8 12H16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M13 9L16 12L13 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </div>
            </a>
        <?php endwhile; ?>
    </div>
<?php
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('custom_woo_product_cards', 'custom_woo_product_cards_shortcode');
