<?php //子テーマ用関数
if ( !defined( 'ABSPATH' ) ) exit;

//子テーマ用のビジュアルエディタースタイルを適用
add_editor_style();

// 全角英数字を半角に
function convert_content( $data ) {
  $convert_fields = array( 'post_title', 'post_content' );
  foreach ( $convert_fields as $convert_field ) {
      $data[$convert_field] = mb_convert_kana( $data[$convert_field], 'aKV', 'UTF-8' );
  }
  return $data;
}
add_filter( 'wp_insert_post_data', 'convert_content' );

// デフォルトRSSフィードにアイキャッチ画像を含める
function rss_thumbnail_image($content) {
  global $post;
  if (has_post_thumbnail($post->ID)) {
    $image_url = get_the_post_thumbnail_url($post->ID, 'full');
    $content = '<div class="post__image"><img src="' . $image_url . '" alt="" /></div>' . $content;
  }
  return $content;
}
add_filter( 'the_excerpt_rss', 'rss_thumbnail_image');
add_filter( 'the_content_feed', 'rss_thumbnail_image');

// デフォルトのフィードを子テーマのファイルから出力する1
remove_filter('do_feed_rss2', 'do_feed_rss2', 10);

function custom_feed_rss2( $for_comments ) {
  $template_file = '/feed-rss2' . ( $for_comments ? '-comments' : '' ) . '.php';
  $template_file = ( file_exists( get_stylesheet_directory() . $template_file ) ? get_stylesheet_directory() : ABSPATH . WPINC ) . $template_file;
  load_template( $template_file );
}

add_action('do_feed_rss2', 'custom_feed_rss2', 10, 1);

add_action('admin_head-post-new.php', 'event_validation_publish_admin_hook'); // 新規イベント投稿画面でのみ関数を呼び出す
add_action('admin_head-post.php', 'event_validation_publish_admin_hook'); // 投稿イベント編集画面でのみ関数を呼び出す
function event_validation_publish_admin_hook(){
    global $post;

    if (is_admin() && $post->post_type == 'post') {
    ?>
    <script>
      jQuery(document).ready(function() {
        jQuery('#publish').on('click', function() {
          var post_status = jQuery('#post-status-display').text(),
              post_title  = jQuery('#title').val();

          if(jQuery(this).data("valid")) {
            return true;
          }

          if (post_status.indexOf('非公開') !== -1 &&  post_title.indexOf('テンプレート') !== -1) {
            return true;
          }

          var form_data = jQuery('#post').serializeArray();
          var data = {
              action: 'event_validation_pre_submit_validation',
              security: '<?php echo wp_create_nonce( 'pre_publish_validation' ); ?>',
              form_data: jQuery.param(form_data),
          };

          jQuery.post(ajaxurl, data, function(response) {
              if (response.indexOf('true') > -1 || response == true) {
                jQuery('#publish').data("valid", true).trigger('click');
              } else {
                alert("エラー: " + response);
                jQuery("#publish").data("valid", false);
              }
              jQuery('#ajax-loading').hide();
              jQuery('#publish').removeClass('button-primary-disabled');
              jQuery('#save-post').removeClass('button-disabled');
          });
          return false;
        });
      });
    </script>
    <?php
    }
}

add_action('wp_ajax_event_validation_pre_submit_validation', 'pre_submit_validation');

function pre_submit_validation(){
  //簡単なセキュリティのチェック
  check_ajax_referer( 'pre_publish_validation', 'security' );

  parse_str( $_POST['form_data'], $vars);

  // 本文に含まれていたらNGなワード
  $ng_words_content = ['URLを入れる', '&copy; XXX', 'ここに広告アフィリエイトのショートコード', '抜粋を入れてください', 'XXXの記事一覧', 'href="XXX"', '「XXX」公式サイト', 'href="https://goo.gl/maps/XXX"', 'YYY</caption>'];
  // 抜粋に含まれていたらNGなワード
  $ng_words_excerpt = ['抜粋を入れてください', 'YYY'];
  // パーマリンクに含まれていたらNGなワード
  $ng_words_slug = ['-template'];

  // 本文チェック
  foreach ($ng_words_content as $ng) {
    //バリデーションの実行
    if (strpos($vars['content'], $ng) !== false) {
      echo '投稿記事中に"' . $ng . '"が見つかりました。';
      die();
    }
  }

  // 抜粋チェック
  foreach ($ng_words_excerpt as $ng) {
    //バリデーションの実行
    if (strpos($vars['excerpt'], $ng) !== false) {
      echo '投稿記事中に"' . $ng . '"が見つかりました。';
      die();
    }
  }

  // パーマリンクチェック
  foreach ($ng_words_slug as $ng) {
    //バリデーションの実行
    if (strpos($vars['post_name'], $ng) !== false) {
      echo 'パーマリンクに"' . $ng . '"が見つかりました。';
      die();
    }
  }

  //問題が無い場合はtrueを返す
  echo 'true';
  die();
}

//フロントページタイプの新着記事キャプションの変更
add_filter('new_entries_caption', function ($caption){
  return '新着グッズ';
});

// 除外するカテゴリーIDを配列で指定する
// add_filter( 'get_related_wp_query_args', function ($args){
//   $args['category__not_in'] = array(19);
//   return $args;
// } );


// 管理者以外はメニューを非表示
function hide_and_edit_event_submenu() {
// ユーザー情報取得
global $current_user, $menu;

function customize_admin_menus_for_writer() {
$css = <<< EOF

<style type="text/css">
  #adminmenu #menu-posts-elementor_library,
  #adminmenu .toplevel_page_wpcf7,
  #adminmenu #menu-links,
  #adminmenu #toplevel_page_templately,
  #adminmenu #menu-posts-wp_automatic,
  #adminmenu #menu-tools,
  #adminmenu #menu-posts-nft
  {
    display: none;
  }
</style>

EOF;

echo $css;
}

function customize_admin_script() {
$js = <<< EOF

<script>
window.addEventListener('DOMContentLoaded', function() {
  console.log('管理画面');
});
</script>

EOF;

echo $js;
}


// 管理者以外は一部サブメニューを削除
if ($current_user->user_level !== "10") {
  add_action('admin_head', 'customize_admin_menus_for_writer');
}

// 全員リンクを書き換え
add_action('admin_print_scripts', 'customize_admin_script');
}

add_action('admin_menu', 'hide_and_edit_event_submenu', 100);


// カスタム投稿追加
add_action( 'init', 'create_post_type' );
function create_post_type() {
  // 解説・考察のアソビバ
  // register_post_type( 'entertainment', [ // 投稿タイプ名の定義
  //     'labels' => [
  //         'name'          => '解説・考察', // 管理画面上で表示する投稿タイプ名
  //         'singular_name' => 'entertainment',    // カスタム投稿の識別名
  //         'all_items' => '解説・考察の記事一覧' //カスタム投稿タイプ一覧名
  //     ],
  //     'public'        => true,  // 投稿タイプをpublicにするか
  //     'has_archive'   => false, // アーカイブ機能ON/OFF
  //     'menu_position' => 5,     // 管理画面上での配置場所
  //     'show_in_rest'  => true,  // 5系から出てきた新エディタ「Gutenberg」を有効にする
  //     'supports' => array('title','editor','thumbnail','custom-fields')
  // ]);

  // register_taxonomy('enjoy-category', array('entertainment'), array(
  //   'hierarchical' => true,
  //   'label' => '解説・考察のカテゴリー',
  //   'show_ui' => true,
  //   'public' => true
  // ));

  // register_taxonomy('have-fun-tag', array('entertainment'), array(
  //   'hierarchical' => false,
  //   'label' => '解説・考察のタグ',
  //   'show_ui' => true,
  //   'public' => true
  // ));

  // ブログのアソビバ
  // register_post_type( 'blog', [ // 投稿タイプ名の定義
  //     'labels' => [
  //         'name'          => 'ブログ', // 管理画面上で表示する投稿タイプ名
  //         'singular_name' => 'blog',    // カスタム投稿の識別名
  //         'all_items' => 'ブログの記事一覧' //カスタム投稿タイプ一覧名
  //     ],
  //     'public'        => true,  // 投稿タイプをpublicにするか
  //     'has_archive'   => true, // アーカイブ機能ON/OFF
  //     'menu_position' => 5,     // 管理画面上での配置場所
  //     'show_in_rest'  => true,  // 5系から出てきた新エディタ「Gutenberg」を有効にする
  //     'supports' => array('title','editor','thumbnail','custom-fields')
  // ]);

  // register_taxonomy('blog-category', array('blog'), array(
  //   'hierarchical' => true,
  //   'label' => 'ブログのカテゴリー',
  //   'show_ui' => true,
  //   'public' => true
  // ));

  // register_taxonomy('blog-tag', array('blog'), array(
  //   'hierarchical' => false,
  //   'label' => 'ブログのタグ',
  //   'show_ui' => true,
  //   'public' => true
  // ));

  // 漫画のアソビバ
  register_post_type( 'comic', [ // 投稿タイプ名の定義
      'labels' => [
          'name'          => '漫画', // 管理画面上で表示する投稿タイプ名
          'singular_name' => 'comic',    // カスタム投稿の識別名
          'all_items' => '漫画の記事一覧' //カスタム投稿タイプ一覧名
      ],
      'public'        => true,  // 投稿タイプをpublicにするか
      'has_archive'   => false, // アーカイブ機能ON/OFF
      'menu_position' => 5,     // 管理画面上での配置場所
      'show_in_rest'  => true,  // 5系から出てきた新エディタ「Gutenberg」を有効にする
      'supports' => array('title','editor','thumbnail','custom-fields')
  ]);

  register_taxonomy('comic-category', array('comic'), array(
    'hierarchical' => true,
    'label' => '漫画のカテゴリー',
    'show_ui' => true,
    'public' => true
  ));

  register_taxonomy('comic-tag', array('comic'), array(
    'hierarchical' => false,
    'label' => '漫画のタグ',
    'show_ui' => true,
    'public' => true
  ));

  // Try - 重複して人気作品をツイートする用のカスタム投稿
  register_post_type( 'duplicate-works', [ // 投稿タイプ名の定義
      'labels' => [
          'name'          => '重複作品', // 管理画面上で表示する投稿タイプ名
          'singular_name' => 'duplicate-works',    // カスタム投稿の識別名
          'all_items' => '重複作品の記事一覧' //カスタム投稿タイプ一覧名
      ],
      'public'        => true,  // 投稿タイプをpublicにするか
      'has_archive'   => false, // アーカイブ機能ON/OFF
      'menu_position' => 5,     // 管理画面上での配置場所
      'show_in_rest'  => true,  // 5系から出てきた新エディタ「Gutenberg」を有効にする
      'supports' => array('title','editor','thumbnail','custom-fields')
  ]);

  register_taxonomy('duplicate-works-category', array('duplicate-works'), array(
    'hierarchical' => true,
    'label' => '重複作品のカテゴリー',
    'show_ui' => true,
    'public' => true
  ));

  register_taxonomy('duplicate-works-tag', array('duplicate-works'), array(
    'hierarchical' => false,
    'label' => '重複作品のタグ',
    'show_ui' => true,
    'public' => true
  ));

  // プライズのアソビバ
  // register_post_type( 'prize', [ // 投稿タイプ名の定義
  //     'labels' => [
  //         'name'          => 'プライズ', // 管理画面上で表示する投稿タイプ名
  //         'singular_name' => 'prize',    // カスタム投稿の識別名
  //         'all_items' => 'プライズの記事一覧' //カスタム投稿タイプ一覧名
  //     ],
  //     'public'        => true,  // 投稿タイプをpublicにするか
  //     'has_archive'   => false, // アーカイブ機能ON/OFF
  //     'menu_position' => 5,     // 管理画面上での配置場所
  //     'show_in_rest'  => true,  // 5系から出てきた新エディタ「Gutenberg」を有効にする
  //     'supports' => array('title','editor','thumbnail','custom-fields')
  // ]);

  // register_taxonomy('prize-category', array('prize'), array(
  //   'hierarchical' => true,
  //   'label' => 'プライズのカテゴリー',
  //   'show_ui' => true,
  //   'public' => true
  // ));

  // register_taxonomy('prize-tag', array('prize'), array(
  //   'hierarchical' => false,
  //   'label' => 'プライズのタグ',
  //   'show_ui' => true,
  //   'public' => true
  // ));

  // くじのアソビバ
  // register_post_type( 'kuji', [ // 投稿タイプ名の定義
  //     'labels' => [
  //         'name'          => 'くじ', // 管理画面上で表示する投稿タイプ名
  //         'singular_name' => 'kuji',    // カスタム投稿の識別名
  //         'all_items' => 'くじの記事一覧' //カスタム投稿タイプ一覧名
  //     ],
  //     'public'        => true,  // 投稿タイプをpublicにするか
  //     'has_archive'   => false, // アーカイブ機能ON/OFF
  //     'menu_position' => 5,     // 管理画面上での配置場所
  //     'show_in_rest'  => true,  // 5系から出てきた新エディタ「Gutenberg」を有効にする
  //     'supports' => array('title','editor','thumbnail','custom-fields')
  // ]);

  // register_taxonomy('kuji-category', array('kuji'), array(
  //   'hierarchical' => true,
  //   'label' => 'くじのカテゴリー',
  //   'show_ui' => true,
  //   'public' => true
  // ));

  // register_taxonomy('kuji-tag', array('kuji'), array(
  //   'hierarchical' => false,
  //   'label' => 'くじのタグ',
  //   'show_ui' => true,
  //   'public' => true
  // ));

  // ランキングのアソビバ
  // register_post_type( 'ranking', [ // 投稿タイプ名の定義
  //     'labels' => [
  //         'name'          => 'ランキング', // 管理画面上で表示する投稿タイプ名
  //         'singular_name' => 'ranking',    // カスタム投稿の識別名
  //         'all_items' => 'ランキングの記事一覧' //カスタム投稿タイプ一覧名
  //     ],
  //     'public'        => true,  // 投稿タイプをpublicにするか
  //     'has_archive'   => false, // アーカイブ機能ON/OFF
  //     'menu_position' => 5,     // 管理画面上での配置場所
  //     'show_in_rest'  => true,  // 5系から出てきた新エディタ「Gutenberg」を有効にする
  //     'supports' => array('title','editor','thumbnail','custom-fields')
  // ]);

  // register_taxonomy('ranking-category', array('ranking'), array(
  //   'hierarchical' => true,
  //   'label' => 'ランキングのカテゴリー',
  //   'show_ui' => true,
  //   'public' => true
  // ));

  // register_taxonomy('ranking-tag', array('ranking'), array(
  //   'hierarchical' => false,
  //   'label' => 'ランキングのタグ',
  //   'show_ui' => true,
  //   'public' => true
  // ));

  // バンダイ雑貨のアソビバ
  register_post_type( 'candy', [ // 投稿タイプ名の定義
      'labels' => [
          'name'          => 'お菓子グッズ', // 管理画面上で表示する投稿タイプ名
          'singular_name' => 'candy',    // カスタム投稿の識別名
          'all_items' => 'お菓子グッズの記事一覧' //カスタム投稿タイプ一覧名
      ],
      'public'        => true,  // 投稿タイプをpublicにするか
      'has_archive'   => false, // アーカイブ機能ON/OFF
      'menu_position' => 5,     // 管理画面上での配置場所
      'show_in_rest'  => true,  // 5系から出てきた新エディタ「Gutenberg」を有効にする
      'supports' => array('title','editor','thumbnail','custom-fields')
  ]);

  register_taxonomy('candy-category', array('candy'), array(
    'hierarchical' => true,
    'label' => 'お菓子グッズのカテゴリー',
    'show_ui' => true,
    'public' => true
  ));

  register_taxonomy('candy-tag', array('candy'), array(
    'hierarchical' => false,
    'label' => 'お菓子グッズのタグ',
    'show_ui' => true,
    'public' => true
  ));

  // プチプラのアソビバ
  // register_post_type( 'petit-price', [ // 投稿タイプ名の定義
  //     'labels' => [
  //         'name'          => 'プチプラ', // 管理画面上で表示する投稿タイプ名
  //         'singular_name' => 'petit-price',    // カスタム投稿の識別名
  //         'all_items' => 'プチプラの記事一覧' //カスタム投稿タイプ一覧名
  //     ],
  //     'public'        => true,  // 投稿タイプをpublicにするか
  //     'has_archive'   => false, // アーカイブ機能ON/OFF
  //     'menu_position' => 5,     // 管理画面上での配置場所
  //     'show_in_rest'  => true,  // 5系から出てきた新エディタ「Gutenberg」を有効にする
  //     'supports' => array('title','editor','thumbnail','custom-fields')
  // ]);

  // register_taxonomy('petit-price-category', array('petit-price'), array(
  //   'hierarchical' => true,
  //   'label' => 'プチプラのカテゴリー',
  //   'show_ui' => true,
  //   'public' => true
  // ));

  // register_taxonomy('petit-price-tag', array('petit-price'), array(
  //   'hierarchical' => false,
  //   'label' => 'プチプラのタグ',
  //   'show_ui' => true,
  //   'public' => true
  // ));

  // メタバースのアソビバ
  // register_post_type( 'satoshi-metaverse', [ // 投稿タイプ名の定義
  //     'labels' => [
  //         'name'          => '仮想通貨', // 管理画面上で表示する投稿タイプ名
  //         'singular_name' => 'satoshi-metaverse',    // カスタム投稿の識別名
  //         'all_items' => '仮想通貨の記事一覧' //カスタム投稿タイプ一覧名
  //     ],
  //     'public'        => true,  // 投稿タイプをpublicにするか
  //     'has_archive'   => false, // アーカイブ機能ON/OFF
  //     'menu_position' => 5,     // 管理画面上での配置場所
  //     'show_in_rest'  => true,  // 5系から出てきた新エディタ「Gutenberg」を有効にする
  //     'supports' => array('title','editor','thumbnail','custom-fields')
  // ]);

  // register_taxonomy('satoshi-metaverse-category', array('satoshi-metaverse'), array(
  //   'hierarchical' => true,
  //   'label' => '仮想通貨のカテゴリー',
  //   'show_ui' => true,
  //   'public' => true
  // ));

  // register_taxonomy('satoshi-metaverse-tag', array('satoshi-metaverse'), array(
  //   'hierarchical' => false,
  //   'label' => '仮想通貨のタグ',
  //   'show_ui' => true,
  //   'public' => true
  // ));

  // アフィリエイト用の踏み台記事
  // register_post_type( 'sns-goods', [ // 投稿タイプ名の定義
  //     'labels' => [
  //         'name'          => '踏み台 for SNS', // 管理画面上で表示する投稿タイプ名
  //         'singular_name' => 'sns-goods',    // カスタム投稿の識別名
  //         'all_items' => '踏み台記事の一覧' //カスタム投稿タイプ一覧名
  //     ],
  //     'public'        => true,  // 投稿タイプをpublicにするか
  //     'has_archive'   => true, // アーカイブ機能ON/OFF
  //     'menu_position' => 5,     // 管理画面上での配置場所
  //     'show_in_rest'  => true,  // 5系から出てきた新エディタ「Gutenberg」を有効にする
  //     'supports' => array('title','editor','thumbnail','custom-fields')
  // ]);

  // register_taxonomy('sns-goods-category', array('sns-goods'), array(
  //   'hierarchical' => true,
  //   'label' => '踏み台のカテゴリー',
  //   'show_ui' => true,
  //   'public' => true
  // ));
}

function get_asin_from_amnazon_url($atts) {
  extract(shortcode_atts(array(
    'id' => '',
  ), $atts));
  $asin = '';
  $amazon_url = htmlspecialchars_decode(get_post_meta(get_the_ID(), 'book_asin', true));
  $query = parse_url($amazon_url, PHP_URL_QUERY);
  parse_str($query, $query_array);
  $asin = $query_array['creativeASIN'];
  $asin_len = mb_strlen($asin);

  if ($asin_len > 10) {
    $asin = substr($asin, 0, -1);
    // $asin = preg_replace('/[^0-9]/', '', $asin);
  }

  if (strpos($asin, '_') !== false) {
    $asin = substr($asin, 0, -1);
  }

  return $asin;
}

add_shortcode('the_asin', 'get_asin_from_amnazon_url');

function convert_isbn13_to_asin($atts) {
  extract(shortcode_atts(array(
    'id' => '',
  ), $atts));
  $asin = '';
  $isbn = get_post_meta(get_the_ID(), 'book_isbn13', true);

  // 「-」を取り除く
  if (strpos($isbn, '-') !== false) {
    $isbn = str_replace('-', '', $isbn);
  }

  // 「■」を取り除く
  if (strpos($isbn, '■') !== false) {
    $isbn = str_replace('■', '', $isbn);
  }

  // 「:」を取り除く
  if (strpos($isbn, ':') !== false) {
    $isbn = str_replace('：', '', $isbn);
  }

  // 「：」を取り除く
  if (strpos($isbn, '：') !== false) {
    $isbn = str_replace('：', '', $isbn);
  }

  // 「ISBN」文字列を取り除く
  if (strpos($isbn, 'ISBN') !== false) {
    $isbn = str_replace('ISBN', '', $isbn);
  }

  // 「コード」文字列を取り除く
  if (strpos($isbn, 'コード') !== false) {
    $isbn = str_replace('コード', '', $isbn);
  }

  // 半角の空白除去
  trim($isbn);
  // 全角含めて全部除去
  $isbn = preg_replace('/　|\s+/', '', $isbn);

  $asin = isbn2asin($isbn);

  return $asin;
}
add_shortcode('convert_isbn2asin', 'convert_isbn13_to_asin');


// ショートコードの実際の処理
function foobar_run_shortcode( $content ) {
    global $shortcode_tags;

    // 現在のショートコード群をバックアップをとってから、すべて削除する
    $orig_shortcode_tags = $shortcode_tags;
    remove_all_shortcodes();

    add_shortcode('convert_isbn2asin', 'convert_isbn13_to_asin');

    // ショートコードを実行 (直前の行で加えた当該のショートコードのみ)
    $content = do_shortcode( $content );

    // 元のショートコード群を復元する
    $shortcode_tags = $orig_shortcode_tags;

    return $content;
}

add_filter( 'the_content', 'foobar_run_shortcode', 7 );

/**
 * ISBNコードをASINコードに変換する
 * @param string $isbn ISBNコード（10進数10桁or 13桁）
 * @return string ASINコード（10進数10桁）／FALSE：変換に失敗
*/
function isbn2asin($isbn) {
    //旧ISBNコードの場合はそのまま返す
    if (preg_match('/^[0-9]{9}[0-9X]$/', $isbn) == 1) {
        if (cd11($isbn) != substr($isbn, 9, 1))      return FALSE;
        return $isbn;
    }

    //入力値チェック
    if (preg_match('/^[0-9]{13}$/', $isbn) != 1)   return FALSE;
    if (cd10($isbn) != substr($isbn, 12, 1))     return FALSE;
    if (preg_match('/^978/', $isbn) == 0)          return FALSE;

    $code = substr($isbn, 3, 10);        //10-1桁目を取り出す
    $cd = cd11($code);

    return substr($isbn, 3, 9) . $cd;
}

/**
 * チェックデジットの計算（モジュラス11ウェイト10-2）ASIN用
 * @param string $code 計算するコード（最下位桁がチェックデジット）
 * @return intチェックデジット
*/
function cd11($code) {
    $cd = 0;
    for ($pos = 10; $pos >= 2; $pos--) {
        $n = substr($code, (10 - $pos), 1);
        $cd += $n * $pos;
    }
    $cd = $cd % 11;
    $cd = 11 - $cd;
    if ($cd == 10)  $cd = 'X';
    if ($cd == 11)  $cd = '0';
    return $cd;
}

/**
 * チェックデジットの計算（モジュラス10ウェイト3）
 * @param string $code 計算するコード（最下位桁がチェックデジット）
 * @return intチェックデジット
*/
function cd10($code) {
    $cd = 0;
    for ($pos = 13; $pos >= 2; $pos--) {
        $n = substr($code, (13 - $pos), 1);
        $cd += $n * (($pos % 2) == 0 ? 3 : 1);
    }
    $cd = $cd % 10;
    return ($cd == 0) ? 0 : 10 - $cd;
}

// 作品から探すセレクトメニュー
function get_goods_categories($atts) {
  // 引数設定
  extract(shortcode_atts(array(
    'id' => '',
    'class' => '',
  ), $atts));

  // HTML生成
  // memo : id(sp) = search__works--sp / class(sp) search__works__form-sp
  $html =  '';
  $html .= '<form class="search__works__form ' . $class. '" method="post" name="search_works" id="' . $id . '">';
  $html .= '<select name="form_fields[work_name]" id="form-field-work_name">';
  $html .= '<option value="/">作品から探す</option>';

  // 親カテゴリ
  $parent_args = array(
    'parent' => 0,
    'exclude' => [1, 6, 19, 1225, 1643, 2620],
    'hide_empty'    => false,
  );
  $parent_id = 0;
  $parent_slug = array();
  $parent_terms = get_terms('category', $parent_args);

  foreach( $parent_terms as $term ) {
    $name = $term->name;
    $parent_id = $term->term_id;
    array_push($parent_slug, $term->slug);
    $html .= '<optgroup label="' . $name . '">';

    // 子カテゴリ
    $child_args = array(
      'parent' => $parent_id,
      'hide_empty'    => true,
    );
    $child_terms = get_terms('category', $child_args);
    foreach( $child_terms as $term ) {
      $name = $term->name;
      $slug = $term->slug;
      $html .= '<option value="/category/' . $slug . '">' . $name . '</option>';

      if ($term === end($child_terms)) {
        $html .= '</optgroup>';
      }
    }
  }

  $html .= '</select>';
  $html .= '<div class="search__works__form__icon"><i class="fas fa-chevron-down"></i></div>';
  $html .= '</form>';

  return $html;
}

add_shortcode('select_work_list', 'get_goods_categories');

function ignore_asobiba_global_navigation_pages() {
  if (is_singular('blog') || is_post_type_archive('blog')) {
    return false;
  } else {
    return true;
  }
}

function before_wp_body_hook(){
  if (ignore_asobiba_global_navigation_pages()) {
    get_template_part('tmp-user/asobiba-templates/global_navigation');
  }
}

add_action('wp_body_open','before_wp_body_hook');

if (! function_exists('wpml_current_language')) {
  function get_language() {
    return apply_filters( 'wpml_current_language', null );
  }

  function is_language($lan_str) {
    if (get_language() === $lan_str) {
      return true;
    } else {
      return false;
    }
  }
}

// リニューアルデザインのグローバルナビゲーション
function asobiba_global_navigation($atts) {
  extract(shortcode_atts(array(
    'id' => '',
    'class' => '',
  ), $atts));

  $global_navigation = array();
  $pages = array();

  // グローバルナビゲーションの名称
  $global_navigation['top'] = 'ホーム';
  $global_navigation['goods'] = 'グッズ';
  $global_navigation['anime'] = 'アニメ';
  $global_navigation['game'] = 'ゲーム';
  $global_navigation['youtuber'] = 'YouTuber';
  $global_navigation['comic'] = '漫画';

  // 固定ページのスラッグ
  $pages['anime']    = 'goods-feature-popular-animes';
  $pages['game']    = 'goods-feature-popular-games';
  $pages['youtuber'] = 'goods-feature-popular-youtuber-vtuber';

  // HTML生成
  $slug = '';
  $html = '';
  $post_type = '';
  $current_class_name = ' asobiba__navigation__tabs__list--current';
  $post_type = get_post_type();

  // ページスラッグ取得のためWPオブジェクトを参照
  if (is_page()) {
    global $wp_query;

    $post_obj = $wp_query->get_queried_object();
    $slug = $post_obj->post_name;
  }


  $html .= '<nav class="asobiba__navigation';
  $html .= wp_is_mobile() ? ' sp">' : ' pc">';
  $html .= '<div class="asobiba__navigation__tabs">';
  $html .= '<menu class="asobiba__navigation__tabs__list" role="navigation">';

  // li要素
  foreach($global_navigation as $key => $value) {
    $html .= '<li class="asobiba__navigation__tabs__list__item';
    // プチプラのkey名上書き
    if ($key === 'petit') {
      $key = 'petit-price';
    }

    // トップページ
    $html .= is_home() || is_front_page() && $key === 'top' ? $current_class_name : '';
    // 固定ページ
    $html .= is_page($key) && $key === $slug ? $current_class_name : '';
    $html .= is_page($pages[$key]) && $pages[$key] === $slug ? $current_class_name : '';
    // カテゴリーページ
    $html .= is_category() && $key === 'goods' ? $current_class_name : '';
    // タグページ
    $html .= is_tag() && $key === 'goods' ? $current_class_name : '';
    // カスタムタクソノミーページ
    // 投稿詳細ページ
    $html .= is_singular('post') && $key === 'goods' ? $current_class_name : '';
    $html .= is_singular($key) && $key === $post_type ? $current_class_name : '';
    // ランキングだけエンタメで表示させているので例外的に直指定
    $html .= is_singular('ranking') && $key === 'entertainment' ? $current_class_name : '';
    $html .= '">';

    // a要素
    if ($key ==='top') {
      $html .= '<a href="/">';
    } else if ($key === 'anime' || $key === 'game' || $key === 'youtuber') {
      $html .= '<a href="/' .  $pages[$key] . '/">';
    } else {
      $html .= '<a href="/' . $key . '/">';
    }

    $html .= $value . '</a>';
  }

  $html .= '</menu>';
  $html .= '</div>';
  $html .= '</nav>';

  return $html;
}

add_shortcode('the_asobiba_gnavi', 'asobiba_global_navigation');

// 画像のsrcsetを出力しない
add_filter('wp_calculate_image_srcset_meta', '__return_null');

// 404ページはトップページへリダイレクトさせる
add_action('template_redirect', 'is404_redirect');

function is404_redirect() {
  if (is_404()) {
    wp_safe_redirect(home_url( '/' ), 301);
    exit();
  }
}
