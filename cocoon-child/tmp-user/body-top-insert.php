<?php
//<body>タグ直後編集用のテンプレートです。
//子テーマのカスタマイズ部分を最小限に抑えたい場合に有効なテンプレートとなります。
?>

<?php if (!is_user_administrator()) :
//管理者を除外してカウントする場合は以下に挿入 ?>
<?php endif; ?>
<?php //全ての訪問者をカウントする場合は以下に挿入 ?>

<?php if (is_singular('post') || is_category() || is_tag() || is_search()) {
  echo '<div class="asobiba__header__work__list">';
  // ElementorのSingle Header を読み込み
  // echo do_shortcode('[elementor-template id="209253"]');
  echo do_shortcode('[select_work_list class=search__works__form-sp id=search__works--sp]');
  echo '</div>';
} ?>
