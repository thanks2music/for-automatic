<?php //カテゴリタグの取得
/**
 * Cocoon WordPress Theme
 * @author: yhira
 * @link: https://wp-cocoon.com/
 * @license: http://www.gnu.org/licenses/gpl-2.0.html GPL v2 or later
 */
if ( !defined( 'ABSPATH' ) ) exit; ?>

<div class="entry-categories-tags<?php echo get_additional_categories_tags_area_classes(); ?>">
<?php $post_type = get_post_type();
if ($post_type === 'comic') {
  // Heading
	echo '<h5>タグ</h5>';
  // Category
  $terms = get_the_terms($post->ID,'comic-category');

  if (! empty($terms)) {
    foreach( $terms as $term ) {
      $html = '<div class="entry-categories asobiba__comic__categories"><a class="cat-link" href="';
      $linkurl = get_term_link($term->term_id , 'comic-category');
      $html .= $linkurl . '">';
      $html .= '<span class="fas fa-folder" aria-hidden="true"></span> ';
      $html .= $term->name . '</a></div>';
    }
    echo $html;
  }

  // Tags
  $terms = get_the_terms($post->ID,'comic-tag');
  if (! empty($terms)) {
    foreach( $terms as $term ) {
      $html = '<div class="entry-categories-tags ctdt-one-row asobiba__comic__tags"><div class="entry-tags"><a class="tag-link" href="';
      $linkurl = get_term_link($term->term_id , 'comic-tag');
      $html .= $linkurl . '">';
      $html .= '<span class="fas fa-tag" aria-hidden="true"></span> ';
      $html .= $term->name . '</a></div></div>';
    }
    echo $html;
  }
?>

<?php } else { ?>
  <div class="entry-categories"><?php the_category_links() ?></div>
  <?php if (get_the_tags()): ?>
  <div class="entry-tags"><?php the_tag_links() ?></div>
  <?php endif; ?>
<?php } ?>
</div>
