<?php
if (!defined('ABSPATH')) exit;

class WPLP_LanguageContent
{
    public function __construct()
    {
        add_filter('wplp_get_posts_by_language', array($this, 'get_posts_by_language'), 10, 3);
        add_filter('wplp_get_category_by_language',array($this,'get_category_by_language'), 10 ,2);
        add_filter('wplp_get_pages_by_language',array($this,'get_pages_by_language'), 10 ,2);
        add_filter('wplp_get_tags_by_language',array($this,'get_tags_by_language'), 10 ,2);
        add_filter('wplp_get_custom_taxonomy_by_language',array($this,'get_custom_taxonomy_by_language'), 10 ,4);
    }

    /*
     * Get posts by language via WPML
     */
    public function get_posts_by_language($posts, $post_type, $language)
    {
        if (function_exists('icl_object_id')) {
            $check = $this->check_wpml_config();
            if($check) {
                $trid = array();
                $blog_id = '';
                if (!empty($posts)) {
                    foreach ($posts as $k => $post) {
                        if (empty($language)) continue;
                        $id = icl_object_id($post->ID, $post_type, false, $language);

                        if (is_multisite()) {
                            $blog_id = '_BLOG_ID_' . $post->curent_blog_id;
                        }
                        // Remove old post in language
                        unset($posts[$k]);
                        // Remove post in another language
                        if (empty($id)) continue;

                        $trid[] = $id . $blog_id;

                    }
                    // Check duplicate post
                    $trid = array_unique($trid);

                    foreach ($trid as $id) {
                        if (is_multisite()) {
                            $str = substr($id, strpos($id, '_BLOG_ID_'));
                            $blog_id = substr($str, strlen('_BLOG_ID_'));
                            $id = substr($id, 0, strpos($id, '_BLOG_ID_'));
                        }

                        // Get post in selected language
                        $post = get_post((int)$id);

                        if (!empty($blog_id)) $post->curent_blog_id = (int)$blog_id;

                        $posts[] = $post;
                    }
                }
            }

        }
        return $posts;
    }
    /*
     * Get category by selected language
     */
    public static function get_category_by_language($cats,$language){
        if (function_exists('icl_object_id')) {
            global $sitepress;
            if (!empty($language)) {
                $sitepress->switch_lang($language);
                $cats = get_categories();
                $sitepress->switch_lang(ICL_LANGUAGE_CODE);
            }
        }
        return $cats;
    }
    /*
     * Get pages by selected language
     */
    public static function get_pages_by_language($pages,$language){
        if (function_exists('icl_object_id')) {
            global $sitepress;
            if (!empty($language)) {
                $sitepress->switch_lang($language);
                $pages = get_pages();
                $sitepress->switch_lang(ICL_LANGUAGE_CODE);
            }
        }
        return $pages;
    }
    /*
     * Get pages by selected language
     */
    public static function get_tags_by_language($tags,$language){
        if (function_exists('icl_object_id')) {
            global $sitepress;
            if (!empty($language)) {
                $sitepress->switch_lang($language);
                $tags = get_tags();
                $sitepress->switch_lang(ICL_LANGUAGE_CODE);
            }
        }
        return $tags;
    }

    /*
    * Get taxonomy by selected language
    */
    public static function get_custom_taxonomy_by_language($terms,$taxname,$postType,$language){
        if (function_exists('icl_object_id')) {
            global $sitepress;
            if (!empty($language)) {
                $sitepress->switch_lang($language);
                $terms = get_terms($taxname, array(
                    'post_type' => array($postType),
                    'hide_empty' => false,
                ));
                $sitepress->switch_lang(ICL_LANGUAGE_CODE);
            }
        }
        return $terms;
    }





    /*
     * AJAX change source type
     */
    public static function change_source_type_by_language(){
        check_ajax_referer( '_change_content_language', 'security' );
        $html = '';
        if(isset($_POST['language'])) $language = $_POST['language'];
        if(isset($_POST['page'])) $type = $_POST['page'];
        if(isset($_POST['blog_post'])) $blog_post = $_POST['blog_post'];
        if(isset($_POST['blog_page'])) $blog_page = $_POST['blog_page'];
        if(isset($_POST['blog_tags'])) $blog_tags= $_POST['blog_tags'];

        if($type == 'src_category'){
            $html .= '<li><input id="cat_all" type="checkbox" name="wplp_source_category[]" value="_all" checked="checked" /><label for="cat_all" class="post_cb">All</li>';
            if(is_multisite()) {
                if ('all_blog' == $blog_post) {
                    $blogs = get_sites();
                    foreach ($blogs as $blog) {
                        switch_to_blog((int)$blog->blog_id);
                        $allcats = get_categories();
                        foreach ($allcats as $allcat) {
                            $cats[] = $allcat;
                        }
                        restore_current_blog();
                    }
                } else {
                    switch_to_blog((int)$blog_post);
                    $cats = get_categories();
                    restore_current_blog();

                }

                $cats = apply_filters('wplp_get_category_by_language', $cats, $language);

                foreach ($cats as $k => $cat) {
                    $html .= '<li>';
                    $html .= '<input id="ccb_' . $k . '" type="checkbox" name="wplp_source_category[]" value="' . $k . '_' . $cat->term_id . '"  class="post_cb" />';
                    $html .= '<label for="ccb_' . $k . '" class="post_cb">' . $cat->name . '</label>';
                    $html .= '</li>';
                }
            }else{
                $cats = self::get_category_by_language('',$language);
                foreach ($cats as $k => $cat) {
                    $html .= '<li>';
                    $html .= '<input id="ccb_' . $k . '" type="checkbox" name="wplp_source_category[]" value="' . $cat->term_id . '"  class="post_cb" />';
                    $html .= '<label for="ccb_' . $k . '" class="post_cb">' . $cat->name . '</label>';
                    $html .= '</li>';
                }
            }
        }elseif ($type == 'src_page'){
            $html .= '<li><input id="page_all" type="checkbox" name="wplp_source_pages[]" value="_all" checked="checked" /><label for="page_all" class="page_cb">All Pages</li>';
            if(is_multisite()){
                if ('all_blog' == $blog_page) {
                    $blogs = get_sites();
                    foreach ($blogs as $blog) {
                        switch_to_blog((int)$blog->blog_id);
                        $allcats = get_pages();
                        foreach ($allcats as $allcat) {
                            $pages[] = $allcat;
                        }
                        restore_current_blog();
                    }
                } else {
                    switch_to_blog((int)$blog_page);
                    $pages = get_pages();
                    restore_current_blog();

                }

                $pages = apply_filters('wplp_get_pages_by_language', $pages, $language);

                foreach ($pages as $k => $page) {
                    $html .= '<li>';
                    $html .= '<input id="pcb_' . $k . '" type="checkbox" name="wplp_source_pages[]" value="' . $k . '_' . $page->ID . '" class="page_cb" />';
                    $html .= '<label for="pcb_' . $k . '" class="page_cb">' . $page->post_title . '</label>';
                    $html .= '</li>';
                }
            }else{
                $pages = self::get_pages_by_language('',$language);

                foreach ($pages as $k => $page) {
                    $html .= '<li>';
                    $html .= '<input id="pcb_' . $k . '" type="checkbox" name="wplp_source_pages[]" value="' . $page->ID . '"  class="page_cb" />';
                    $html .= '<label for="pcb_' . $k . '" class="page_cb">' . $page->post_title . '</label>';
                    $html .= '</li>';
                }
            }
        }elseif ($type == 'src_tags'){
            $html .= '<li><input id="tags_all" type="checkbox" name="wplp_source_tags[]" value="_all" checked="checked" /><label for="tags_all" class="tag_cb">All tags</li>';
            if (is_multisite()) {
                if ('all_blog' == $blog_tags) {
                    $blogs = get_sites();
                    foreach ($blogs as $blog) {
                        switch_to_blog((int)$blog->blog_id);
                        $allcats = get_tags();
                        if (!empty($allcats)) {
                            foreach ($allcats as $allcat) {
                                $tags[] = $allcat;
                            }
                        }
                        restore_current_blog();
                    }
                } else {
                    switch_to_blog((int)$blog_tags);
                    $tags = get_tags();
                    restore_current_blog();

                }
                $tags = apply_filters('wplp_get_tags_by_language', $tags , $language);

                foreach ($tags as $k => $tag) {
                    $html .= '<li>';
                    $html .= '<input id="tcb_' . $k . '" type="checkbox" name="wplp_source_tags[]" value="' . $k . '_' . $tag->term_id . '" class="tag_cb" />';
                    $html .= '<label for="tcb_' . $k . '" class="tag_cb">' . $tag->name . '</label>';
                    $html .= '</li>';
                }
            }else{
                $tags = self::get_tags_by_language('',$language);

                foreach ($tags as $k => $tag) {
                    $html .= '<li>';
                    $html .= '<input id="tcb_' . $k . '" type="checkbox" name="wplp_source_tags[]" value="' . $tag->term_id . '" class="tag_cb" />';
                    $html .= '<label for="tcb_' . $k . '" class="tag_cb">' . $tag->name . '</label></li>';
                    $html .= '</li>';
                }
            }
        }

        wp_send_json(array('output' => $html, 'type' => $type));
    }

    /*
     * Check WPML configuation
     */
    public function check_wpml_config(){
        global $wpdb;

        $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}icl_languages WHERE active = 1" );
        if(!empty($count)){
            return true;
        }
        return false;
    }

}