<?php
get_header();

$hero_title = get_theme_mod('rtaibah_hero_title', __('نحو تجربة رقمية تفاعلية', 'rtaibah-theme'));
$hero_description = get_theme_mod('rtaibah_hero_description', __('يمكنك تعديل هذا النص من المخصص في ووردبريس.', 'rtaibah-theme'));
?>
<section class="hero">
  <h2><?php echo esc_html($hero_title); ?></h2>
  <p><?php echo esc_html($hero_description); ?></p>
</section>

<section class="metrics-grid">
  <article class="metric-card"><span><?php esc_html_e('المقالات', 'rtaibah-theme'); ?></span><strong><?php echo esc_html((string) wp_count_posts('post')->publish); ?></strong></article>
  <article class="metric-card"><span><?php esc_html_e('الصفحات', 'rtaibah-theme'); ?></span><strong><?php echo esc_html((string) wp_count_posts('page')->publish); ?></strong></article>
  <article class="metric-card"><span><?php esc_html_e('المستخدمون', 'rtaibah-theme'); ?></span><strong><?php echo esc_html((string) count_users()['total_users']); ?></strong></article>
</section>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
  <article <?php post_class(); ?>>
    <h2><?php the_title(); ?></h2>
    <div><?php the_content(); ?></div>
  </article>
<?php endwhile; endif; ?>

<?php
get_footer();
