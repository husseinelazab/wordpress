<?php

if (! defined('ABSPATH')) {
    exit;
}

function rtaibah_theme_setup(): void
{
    load_theme_textdomain('rtaibah-theme', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    register_nav_menus([
        'primary' => __('القائمة الرئيسية', 'rtaibah-theme'),
    ]);
}
add_action('after_setup_theme', 'rtaibah_theme_setup');

function rtaibah_theme_assets(): void
{
    wp_enqueue_style('rtaibah-theme-style', get_stylesheet_uri(), [], '1.0.0');
    wp_enqueue_script('rtaibah-theme-main', get_template_directory_uri() . '/assets/js/main.js', [], '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'rtaibah_theme_assets');

function rtaibah_register_sidebar(): void
{
    register_sidebar([
        'name'          => __('الشريط الجانبي', 'rtaibah-theme'),
        'id'            => 'main-sidebar',
        'description'   => __('يمكنك إضافة ودجت في هذه المنطقة.', 'rtaibah-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
}
add_action('widgets_init', 'rtaibah_register_sidebar');

function rtaibah_customize_register(WP_Customize_Manager $wp_customize): void
{
    $wp_customize->add_section('rtaibah_home', [
        'title'    => __('إعدادات الصفحة الرئيسية', 'rtaibah-theme'),
        'priority' => 30,
    ]);

    $wp_customize->add_setting('rtaibah_hero_title', [
        'default'           => __('نحو تجربة رقمية تفاعلية', 'rtaibah-theme'),
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('rtaibah_hero_title', [
        'label'   => __('عنوان الهيرو', 'rtaibah-theme'),
        'section' => 'rtaibah_home',
        'type'    => 'text',
    ]);

    $wp_customize->add_setting('rtaibah_hero_description', [
        'default'           => __('يمكنك تعديل هذا النص من المخصص في ووردبريس.', 'rtaibah-theme'),
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);

    $wp_customize->add_control('rtaibah_hero_description', [
        'label'   => __('وصف الهيرو', 'rtaibah-theme'),
        'section' => 'rtaibah_home',
        'type'    => 'textarea',
    ]);
}
add_action('customize_register', 'rtaibah_customize_register');

function rtaibah_dashboard_menu(): void
{
    add_menu_page(
        __('لوحة تحكم رتائب', 'rtaibah-theme'),
        __('لوحة رتائب', 'rtaibah-theme'),
        'manage_options',
        'rtaibah-dashboard',
        'rtaibah_render_dashboard_page',
        'dashicons-chart-line',
        3
    );
}
add_action('admin_menu', 'rtaibah_dashboard_menu');

function rtaibah_admin_assets(string $hook): void
{
    if ($hook !== 'toplevel_page_rtaibah-dashboard') {
        return;
    }

    wp_enqueue_style('rtaibah-admin-style', get_template_directory_uri() . '/assets/css/admin-dashboard.css', [], '1.0.0');
    wp_enqueue_script('rtaibah-admin-dashboard', get_template_directory_uri() . '/assets/js/admin-dashboard.js', [], '1.0.0', true);

    wp_localize_script('rtaibah-admin-dashboard', 'rtaibahDashboardData', [
        'endpoint' => admin_url('admin-ajax.php?action=rtaibah_dashboard_metrics'),
        'nonce'    => wp_create_nonce('rtaibah_dashboard_nonce'),
    ]);
}
add_action('admin_enqueue_scripts', 'rtaibah_admin_assets');

function rtaibah_collect_dashboard_metrics(): array
{
    $published_posts = wp_count_posts('post')->publish;
    $published_pages = wp_count_posts('page')->publish;
    $users_total = count_users()['total_users'];
    $comments_total = wp_count_comments()->approved;

    $latest_posts = get_posts([
        'posts_per_page' => 5,
        'post_status'    => 'publish',
    ]);

    $labels = [];
    $values = [];

    foreach ($latest_posts as $post) {
        $labels[] = wp_trim_words($post->post_title, 4, '...');
        $values[] = (int) get_comments_number($post->ID);
    }

    return [
        'totals' => [
            'posts'    => (int) $published_posts,
            'pages'    => (int) $published_pages,
            'users'    => (int) $users_total,
            'comments' => (int) $comments_total,
        ],
        'chart' => [
            'labels' => $labels,
            'values' => $values,
        ],
    ];
}

function rtaibah_dashboard_metrics_ajax(): void
{
    if (! current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('غير مصرح.', 'rtaibah-theme')], 403);
    }

    $nonce = isset($_GET['nonce']) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : '';

    if (! wp_verify_nonce($nonce, 'rtaibah_dashboard_nonce')) {
        wp_send_json_error(['message' => __('فشل التحقق الأمني.', 'rtaibah-theme')], 401);
    }

    wp_send_json_success(rtaibah_collect_dashboard_metrics());
}
add_action('wp_ajax_rtaibah_dashboard_metrics', 'rtaibah_dashboard_metrics_ajax');

function rtaibah_render_dashboard_page(): void
{
    $metrics = rtaibah_collect_dashboard_metrics();
    ?>
    <div class="wrap rtaibah-dashboard-page">
      <h1><?php esc_html_e('لوحة تحكم رتائب التفاعلية', 'rtaibah-theme'); ?></h1>
      <p><?php esc_html_e('متابعة سريعة لأهم مؤشرات الموقع في مكان واحد.', 'rtaibah-theme'); ?></p>

      <section class="rtaibah-cards" id="rtaibah-dashboard-cards">
        <article class="card"><span><?php esc_html_e('المقالات المنشورة', 'rtaibah-theme'); ?></span><strong data-key="posts"><?php echo esc_html($metrics['totals']['posts']); ?></strong></article>
        <article class="card"><span><?php esc_html_e('الصفحات المنشورة', 'rtaibah-theme'); ?></span><strong data-key="pages"><?php echo esc_html($metrics['totals']['pages']); ?></strong></article>
        <article class="card"><span><?php esc_html_e('عدد المستخدمين', 'rtaibah-theme'); ?></span><strong data-key="users"><?php echo esc_html($metrics['totals']['users']); ?></strong></article>
        <article class="card"><span><?php esc_html_e('التعليقات المعتمدة', 'rtaibah-theme'); ?></span><strong data-key="comments"><?php echo esc_html($metrics['totals']['comments']); ?></strong></article>
      </section>

      <section class="rtaibah-chart" id="rtaibah-chart" data-labels="<?php echo esc_attr(wp_json_encode($metrics['chart']['labels'])); ?>" data-values="<?php echo esc_attr(wp_json_encode($metrics['chart']['values'])); ?>">
        <h2><?php esc_html_e('تفاعل آخر 5 مقالات (حسب التعليقات)', 'rtaibah-theme'); ?></h2>
        <ul id="rtaibah-chart-list"></ul>
      </section>
    </div>
    <?php
}
