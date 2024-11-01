<?php
 
/*

    Plugin Name: Zmooz Stories V2
    
    Plugin URI: https://zmooz.com/
    
    Description: Ask ?.
    
    Version: 0.9.8.7
    
    Author: ZMOOZ
    
    Author URI: zmooz.com
    
    License: All right reserved
    
    Text Domain: ZMOOZ    
    
*/

//---------------BEGIN--------------//
// ZMOOZ WEB STORIES PLUGIN
// By Prince Nick BALLO
// 1/06/2022

global $ZMOOZ_API;
global $ZMOOZ_USED_DOMAIN;
global $ZMOOZ_USER_AUTH_TOKEN;
global $ZMOOZ_POST_TYPE ;


$ZMOOZ_API = 'https://api-v2.zmooz.com';
$ZMOOZ_USED_DOMAIN =  get_site_url();
// $ZMOOZ_API = 'https://api.zmoozy.com';
// $ZMOOZ_USED_DOMAIN = 'storizoom.com';
$ZMOOZ_POST_TYPE =  'web-story';
// $ZMOOZ_API = 'http://[::1]:3000';
    
if (!class_exists('mk_zmooz_stories_v2')):

    class mk_zmooz_stories_v2 {


        public function __construct(){
            add_action( 'init', array($this, 'zmooz_wp_plugin_function_story_post_type'));
            add_action( 'init', array($this, 'routing'), 0 );
            add_action( 'admin_init', array($this,'zmooz_custom_plugin_settings'));
            add_action( 'admin_menu', array($this,'zmooz_wp_plugin_function_admin_menu'));
            add_action( 'admin_menu', array($this,'zmooz_custom_plugin_settings_page'));
            add_filter( 'manage_edit-stories_columns', array($this, 'zmooz_stories_list_modify_column' ));
            add_action( 'manage_stories_posts_custom_column', array($this,'zmooz_stories_list_modified_column_display'), 10, 2 );
            add_action( 'before_delete_post', array($this,'zmooz_wp_plugin_function_delete_all_attached_media' ));
            add_filter( 'manage_edit-stories_sortable_columns', array($this,'zmooz_stories_list_modified_custom_column_sortable' ));
            add_action( 'wp_enqueue_scripts', array($this,'zmooz_add_custom_templates_styles' ));
            add_action( 'admin_head-edit.php', array($this,'zmooz_add_custom_button_import_stories'));
            add_filter( 'post_row_actions', array($this,'zmooz_wp_plugin_function_remove_bulk'),10,2);
            add_filter( 'get_edit_post_link', array($this, 'zmooz_wp_plugin_filter_function_name_6509'), 10, 3 );
            add_filter( 'bulk_actions-edit-stories', function($bulk_actions) {
                $bulk_actions['update-from-zmooz'] = __('Update Selected', 'txtdomain');
                return $bulk_actions;
            });
            add_filter( 'wp_kses_allowed_html', array($this, 'zmooz_custom_kses_allowed_tags' ));
            add_filter( 'handle_bulk_actions-edit-stories', function($redirect_url, $action, $post_ids) {
                if ($action == 'update-from-zmooz') {
                    global $current_screen;
                    if($current_screen->post_type!='stories') return;
                    // Not our post type, exit earlier
                    // You can remove this if condition if you don't have any specific post type to restrict to. 
                    require_once __DIR__ . '/admin/actions.php';
                    $action_collection = new zmooz_wp_actions_V2();
                    $count = array();
                    foreach ($post_ids as $post_id) {      
                        $response = $action_collection->zmooz_wp_plugin_update_story($post_id);
                        if(isset($response["status"]) and $response["status"]==200){
                            array_push($count,$post_id);
                        }
                    }
                    $redirect_url = add_query_arg('update-from-zmooz', count($count), $redirect_url);
                }
                return $redirect_url;
            }, 10, 3);
            add_action( 'rest_api_init', function () {

                register_rest_route( 'zmooz-stories-plugin/v1', '/new-story/(?P<storyId>\d+)', array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this,'zmooz_create_post_by_endpoint'),
                    'permission_callback' => '__return_true'
                ));

                register_rest_route( 'zmooz-stories-plugin/v1', '/update-story', array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this, 'zmooz_update_post_by_endpoint'),
                    'permission_callback' => '__return_true'
                ));

                register_rest_route( 'zmooz-stories-plugin/v1', '/checkup', array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this, 'zmooz_checkupPlugin'),
                    'permission_callback' => '__return_true'
                ));
            }); 
           
        }

        public function zmooz_get_post_type(){
            $google_plugin_active = in_array( 'web-stories/web-stories.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )));
            $value = get_option( 'zmooz_custom_post_type_variable' );
            $post_type = (isset($value) and $value === 'stories') ? $value : "";
            if($google_plugin_active === true ){
                return "web-story";
            }
            if($google_plugin_active === false){
                return "stories";
            }
            return $post_type;
        }

        public function zmooz_custom_plugin_settings_page() {
            add_options_page(
                'Settings',
                'Zmooz',
                'manage_options',
                'zmooz_custom_plugin_settings_group',
                array($this,'zmooz_custom_plugin_settings_page_callback')
            );
        }

        public function zmooz_custom_plugin_settings() {
            add_settings_section(
                'zmooz_custom_plugin_settings_section',
                'Default Web Story author',
                '',
                'zmooz_custom_plugin_settings'
            );
            add_settings_field(
                'zmooz_custom_plugin_default_user',
                'Selected default User:',
                array($this,'zmooz_custom_plugin_default_user_callback'),
                'zmooz_custom_plugin_settings',
                'zmooz_custom_plugin_settings_section'
            );
            register_setting(
                'zmooz_custom_plugin_settings_group',
                'zmooz_custom_plugin_default_user',
                array(
                    'type' => 'integer',
                    'default' => 1 // Set the default value for the field to 1
                )
            );
           
        }

        public function zmooz_custom_plugin_default_user_callback() {
            $default_user = get_option( 'zmooz_custom_plugin_default_user' );
            ?>
            <select name="zmooz_custom_plugin_default_user" id="zmooz_custom_plugin_default_user">
                <?php
                // Retrieve a list of all users
                $users = get_users();
                foreach ( $users as $user ) {
                    ?>
                    <option value="<?php echo $user->ID; ?>" <?php selected( $default_user, $user->ID ); ?>><?php echo $user->display_name; ?></option>
                    <?php
                }
                ?>
            </select>
            <?php
        }

        public function zmooz_custom_plugin_default_slug_callback() {
            $default_post_type = $this->zmooz_get_post_type();
            $value = get_option( 'zmooz_custom_plugin_default_slug' ) ? get_option( 'zmooz_custom_plugin_default_slug' ) : $default_post_type;
            ?>
            <label class="zmooz_settings_switch">
                <label><input type="radio" name="zmooz_custom_plugin_default_slug" value="stories" <?php checked( 'stories',  $value ); ?>> /stories</label><br><br>
                <label><input type="radio" name="zmooz_custom_plugin_default_slug" value="web-story" <?php checked( 'web-story',  $value ); ?>> /web-stories</label><br>
        
                <span class="zmooz_settings_slider zmooz_settings_round"></span>
            </label>
            <?php
        }

        public function zmooz_custom_plugin_settings_page_callback() {
           
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
        
            echo '<div class="wrap">';
            echo '<div style="display:flex;">';
            echo '<div style="height: 100%; min-height: 80vh; border-right: 1px solid #ccc;padding: 0 2rem"><h1>Zmooz Settings</h1></div>';
            echo '<form method="post" action="options.php" style="margin: 2rem;">';
        
            settings_fields('zmooz_custom_plugin_settings');
            do_settings_sections('zmooz_custom_plugin_settings');
        
            submit_button('Save Settings');
            echo '</form>';
            echo('</div>');
            echo '</div>';
        }

        public function zmooz_custom_post_type_settings_init(){
            // Register the settings fields
            global $ZMOOZ_USED_DOMAIN ;
            $post_type = $this->zmooz_get_post_type();
            register_setting(
                'zmooz_custom_post_type_settings_group',
                'zmooz_custom_post_type_variable',
                array(
                    'type' => 'string',
                    'default' => 'stories'
                )
            );

            add_settings_section(
                'zmooz_custom_post_type_settings_section',
                'You are using Google Web Stories.',
                array($this,'zmooz_custom_post_type_settings_section_callback'),
                'zmooz_custom_post_type_settings'
            );

            add_settings_field(
                'zmooz_custom_post_type_variable_field',
                "If you wish to publish Zmooz' stories at the slug https://".$ZMOOZ_USED_DOMAIN."/stories, then switch the following button",
                array($this,'zmooz_custom_post_type_variable_field_callback'),
                'zmooz_custom_post_type_settings',
                'zmooz_custom_post_type_settings_section'
            );
        }

        // Callback function to display the variable field
        public function zmooz_custom_post_type_variable_field_callback() {
            $value = get_option( 'zmooz_custom_post_type_variable' );
            ?>
            <label class="zmooz_settings_switch">
                <input type="checkbox" name="zmooz_custom_post_type_variable" value="stories" <?php checked( $value, 'stories' ); ?>>
                <span class="zmooz_settings_slider zmooz_settings_round"></span>
            </label>
            <?php
        }

        // Callback function to display the settings section
        public function zmooz_custom_post_type_settings_section_callback() {
            global $ZMOOZ_USED_DOMAIN ;
            ?>
            <p> Zmooz is going to publish stories at the same slug: https://<?php esc_html_e($ZMOOZ_USED_DOMAIN) ?>/web-stories</p>
            <?php
        }

       
        function zmooz_custom_post_type_settings_save() {
            
            try{
                $value = $this->zmooz_get_post_type();
                $google_plugin_active = in_array( 'web-stories/web-stories.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )));
                if (isset( $_POST['zmooz_custom_post_type_variable'] ) ) {
                    //  exit;
                    if( $google_plugin_active==false){
                        wp_redirect( admin_url( 'edit.php?post_type=stories&page=zmooz-stories-settings' ) ); 
                        exit;
                    }
                    $post_type =  sanitize_text_field($_POST['zmooz_custom_post_type_variable']);
                    if( $post_type == ""){
                        $post_type = "web-story";
                    }
                    update_option( 'zmooz_custom_post_type_variable', sanitize_text_field($_POST['zmooz_custom_post_type_variable']) );
                }
               
            }catch(Error $e){
                wp_redirect( admin_url( 'admin.php'));
            }
           
        }

        /**
         * Allow amp web stories cutom post type html tags for escape
         */
        public function zmooz_custom_kses_allowed_tags( $allowed_tags ) {
            $value = $this->zmooz_get_post_type();
            if($value === "web-story"){  
                $allowed_tags['html'] = array(
                    'amp' => true
                );
                $allowed_tags['head'] = array();
                $allowed_tags['link'] = array(
                    'rel' => true,
                    'as' => true,
                    'href' => true
                );
                $allowed_tags['meta'] = array(
                    'name' => true,
                    'content' => true,
                );
                $allowed_tags['style'] = array(
                    'amp-custom' => true,
                    'amp-boilerplate' => true,
                );
                $allowed_tags['body'] = array(
                    'class' => true,
                    'style' => true
                );
                $allowed_tags['noscript'] = true;
                $allowed_tags['amp-story'] = array(  
                    'id' => true,
                    'class' => true,
                    'src' => true,
                    'style' => true,
                    'width' => true,
                    'height' => true,
                    'src' => true,
                    'alt' => true,
                    'layout' => true,
                    'template' => true,
                    'title' => true,
                    'standalone' => true,
                    'publisher'=> true,
                    'publisher-logo-src'=> true,
                    'poster-portrait-src'=> true,
                    'poster-square-src'=> true,
                    'poster-landscape-src'=> true
                );
                $allowed_tags['amp-fit-text'] = array(  
                    'id' => true,
                    'class' => true,
                    'src' => true,
                    'style' => true,
                    'width' => true,
                    'height' => true,
                    'src' => true,
                    'alt' => true,
                    'layout' => true,
                    'template' => true,
                    'title' => true,
                    'max-font-size' => true,
                );
                $allowed_tags['amp-img'] = array(
                    'id' => true,
                    'class' => true,
                    'src' => true,
                    'style' => true,
                    'width' => true,
                    'height' => true,
                    'src' => true,
                    'alt' => true,
                    'layout' => true,
                    'template' => true,
                );
                $allowed_tags['amp-story-page'] = array(
                    'id' => true,
                    'class' => true,
                    'src' => true,
                    'style' => true,
                    'width' => true,
                    'height' => true,
                    'src' => true,
                    'alt' => true,
                    'layout' => true,
                    'template' => true,
                    'style' => true,
                    'auto-advance-after' => true,
                );
                $allowed_tags['amp-story-grid-layer'] = array(
                    'id' => true,
                    'src' => true,
                    'style' => true,
                    'width' => true,
                    'height' => true,
                    'src' => true,
                    'alt' => true,
                    'layout' => true,
                    'template' => true,
                );
                $allowed_tags[ 'amp-story-captions'] = [
                    'height' => true,
                ];
                $allowed_tags['amp-story-shopping-attachment'] = [
                    'cta-text' => true,
                    'theme'    => true,
                    'src'      => true,
                ];
                $allowed_tags['amp-story-shopping-config']    = [
                    'src' => true,
                ];
                $allowed_tags['amp-story-shopping-tag']        = [];
                $allowed_tags['amp-story-page-attachment']     = [
                    'href'  => true,
                    'theme' => true,
                ];
                $allowed_tags['amp-story-cta-layer']           = [];
                $allowed_tags['amp-story-animation']           = [
                    'trigger' => true,
                ];
              
                $allowed_tags['amp-story-auto-analytics'] = array(
                    'type' => true,
                    'data-credentials' => true,
                    'config' => true,
                    'height' => true,
                    'width' => true,
                    'id' => true,
                    'data-iframe-src' => true,
                    'data-transport' => true,
                    'data-serialized' => true,
                    'data-vars-analytics' => true,
                    'data-amp-report-errors-to' => true,
                    'data-amp-3p-sentinel' => true,
                    'data-amp-3p-iframe-src' => true,
                    'sandbox' => true,
                    'gtag-id'=> true,
    
                );
    
                $allowed_tags['amp-story-page-outlink'] = array(
                    'layout' => true,
                    'theme' => true,
                    'cta-accent-color' => true,
                    'cta-accent-element' => true,
                    'aria-hidden' => true,
                    'aria-live' => true,
                    'style' => true,
                );
                
            }
            return $allowed_tags;
        }

        
        /**
         * Register custom post type
         */
        public function zmooz_wp_plugin_function_story_post_type() {
            global $ZMOOZ_POST_TYPE ;
            $google_plugin_active = in_array( 'web-stories/web-stories.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )));
            $post_type = $this->zmooz_get_post_type();
     
            if( $post_type == "stories"){

                //var_dump($post_type);
                // On rentre les différentes dénominations de notre custom post type qui seront affichées dans l'administration
                $labels = array(
                    // Le nom au pluriel
                    'name'                => _x( 'Stories', 'Post Type General Name'),
                    // Le nom au singulier
                    'singular_name'       => _x( 'Story', 'Post Type Singular Name'),
                    // Le libellé affiché dans le menu
                    'menu_name'           => __( 'Zmooz Stories'),
                    // Les différents libellés de l'administration
                    'all_items'           => __( 'All stories'),
                    'view_item'           => __( 'View story'),
                    'add_new_item'        => __( 'Add new story'),
                    'add_new'             => __( 'Add'),
                    'edit_item'           => __( 'Edit story'),
                    'update_item'         => __( 'Update story'),
                    'search_items'        => __( 'Search story'),
                    'not_found'           => __( 'Story not found'),
                    'not_found_in_trash'  => __( 'No draft story'),
                );
                
                // On peut définir ici d'autres options pour notre custom post type
                
                $args = array(
                    'label'               => __( 'Story'),
                    'description'         => __( 'All stories'),
                    'labels'              => $labels,
                    'menu_icon'           => plugins_url('/images/logo/favvertical.png', __FILE__ ),
                    // On définit les options disponibles dans l'éditeur de notre custom post type ( un titre, un auteur...)
                    'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'revisions', 'custom-fields', ),
                    'capability_type' => 'post',
                    'capabilities' => array(
                    'create_posts' => 'do_not_allow', // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
                    ),
                    'map_meta_cap' => true,
                    'show_in_rest' => true,
                    'hierarchical'        => false,
                    'public'              => true,
                    'has_archive'         => true,
                    'rewrite'             => array( 'slug' => $post_type)

                );
                
                // On enregistre notre custom post type qu'on nomme ici "serietv" et ses arguments
                register_post_type($post_type, $args );
                add_post_type_support($post_type, 'schema' );

                // Catégorie de la story
    
                $labels_cat_story = array(
                    'name'                       => _x( 'Story category', 'taxonomy general name'),
                    'singular_name'              => _x( 'Story category', 'taxonomy singular name'),
                    'search_items'               => __( 'Search category'),
                    'popular_items'              => __( 'Catégories populaires'),
                    'all_items'                  => __( 'Popular categories'),
                    'edit_item'                  => __( 'Edit a category'),
                    'update_item'                => __( 'Update a category'),
                    'add_new_item'               => __( 'Add new category'),
                    'new_item_name'              => __( 'New category name'),
                    'add_or_remove_items'        => __( 'Add or remove category'),
                    'choose_from_most_used'      => __( 'Choose from the most used categories'),
                    'not_found'                  => __( 'No categories found'),
                    'menu_name'                  => __( 'Story categories')
                );
    
                $args_cat_story= array(
                    // Si 'hierarchical' est défini à true, notre taxonomie se comportera comme une catégorie standard
                    'hierarchical'          => true,
                    'labels'                => $labels_cat_story,
                    'show_ui'               => true,
                    'show_in_rest'          => true,
                    'show_admin_column'     => true,
                    'query_var'             => true,
                    'rewrite'               => array( 'slug' => 'story-category' )
                );
    
                register_taxonomy( 'storycategory', $post_type, $args_cat_story );
            }
            $this->zmooz_wp_plugin_create_template();
        }

        /**
         * BEGIN
         * Add Taxonomies
         */
        public function zmooz_wp_plugin_function_taxonomies() {

            // Catégorie de la story

            $labels_cat_story = array(
                'name'                       => _x( 'Story category', 'taxonomy general name'),
                'singular_name'              => _x( 'Story category', 'taxonomy singular name'),
                'search_items'               => __( 'Search category'),
                'popular_items'              => __( 'Catégories populaires'),
                'all_items'                  => __( 'Popular categories'),
                'edit_item'                  => __( 'Edit a category'),
                'update_item'                => __( 'Update a category'),
                'add_new_item'               => __( 'Add new category'),
                'new_item_name'              => __( 'New category name'),
                'add_or_remove_items'        => __( 'Add or remove category'),
                'choose_from_most_used'      => __( 'Choose from the most used categories'),
                'not_found'                  => __( 'No categories found'),
                'menu_name'                  => __( 'Story categories')
            );

            $args_cat_story= array(
            // Si 'hierarchical' est défini à true, notre taxonomie se comportera comme une catégorie standard
                'hierarchical'          => true,
                'labels'                => $labels_cat_story,
                'show_ui'               => true,
                'show_in_rest'          => true,
                'show_admin_column'     => true,
                'query_var'             => true,
                'rewrite'               => array( 'slug' => 'stories-category' )
            );

            register_taxonomy( 'storycategory', 'stories', $args_cat_story );
        }
        /** END */

        /** 
         * BEGIN 
         * Admin panel add columns
         */
        // Register the column for modified date
        public function zmooz_stories_list_modify_column( $columns ) {
            $columns['post_modified'] = __( 'Imported (Wordpress)', 'mytextdomain' );
            $columns['date'] = __( 'Published (ZMOOZ)', 'mytextdomain' );
            return $columns;
        }

        // Display the modified date column content
        public function zmooz_stories_list_modified_column_display( $column_name, $post_id ) {
            if ( 'post_modified' != $column_name ){
                return;
            }
            $post_modified = get_post_field('post_modified', $post_id);
            
            if ( !$post_modified ){
                $post_modified = '' . __( 'undefined', 'mytextdomain' ) . '';
            }
            $allowed_html  = array(
                'div' => array()
            );
            echo wp_kses('<div><div>Updated</div><div>'.date("Y/m/d \a\\t g:i a", strtotime($post_modified)).'</div></div>' ,$allowed_html);
        }

        // Register the modified date column as sortable
        public function zmooz_stories_list_modified_custom_column_sortable( $columns ) {
            $columns['post_modified'] = 'post_modified';
            return $columns;
        }
        /** END */


        /**
         * BEGIN
         * Remove all associated attachments in wp DB and associated row in ZMOOZ DB if a post is deleted
         */
        public function zmooz_wp_plugin_function_delete_all_attached_media( $post_id ) {
            global $ZMOOZ_POST_TYPE ;
            $post_type = $this->zmooz_get_post_type();
     
            if( get_post_type($post_id) == $post_type ) {

                $attachments = get_attached_media( '', $post_id );

                foreach ($attachments as $attachment) {
                    wp_delete_attachment( $attachment->ID, 'true' );
                }

                $this->zmooz_wp_plugin_function_remove_story_from_dp($post_id);

            }

        }

        public function zmooz_wp_plugin_function_remove_story_from_dp($post_id){

            global $ZMOOZ_API;
            global $ZMOOZ_USED_DOMAIN;

            $request = wp_remote_get(  $ZMOOZ_API.'/wp-plugin/delete_story/'.$post_id.'?domain='.$ZMOOZ_USED_DOMAIN);

        }
        /** END */

        /**
         * BEGIN
         * Create template
         */
        function  zmooz_wp_plugin_create_template(){

            global $allowedposttags;


            //Single story template
            // add_filter( 'safe_style_css', function( $styles ) {
            //     global $post;
            //     if($post->post_type=="stories"){
            //         $styles['top'] = 'top';
            //         $styles['left'] = 'left';
            //         $styles['position'] = 'position';
            //     }
            //     return $styles;
            // });

            file_put_contents(get_template_directory().'/single-stories.php', "
                <!DOCTYPE html>
                <?php
                if ( is_singular( 'stories' ) ) {
                    remove_all_filters( 'omgf_buffer_output' );
                }
                echo str_replace('src=\"/images/','src=\"https://m.amp.story.domains/images/',html_entity_decode(get_the_content())) ;
            ");


            file_put_contents(get_template_directory().'/archive-stories.php',   "
                <?php /* Template Name: Web Stories Grid */ ?>
                <?php
                    \$channelInfo = [];
                    if(is_post_type_archive(\"stories\")){
                        global \$ZMOOZ_API;
                        global \$ZMOOZ_USED_DOMAIN;
                        \$channelData = wp_remote_get( \$ZMOOZ_API.'/wp-plugin/getadon?domain='.\$ZMOOZ_USED_DOMAIN );
                        \$channelInfo = json_decode( wp_remote_retrieve_body( \$channelData ),true );
                    }
                    \$site_title = get_bloginfo( 'name' );
                    \$lang = get_bloginfo( 'language' );
                    \$post_pos = 0;
                    \$background_color = get_background_color();
                    \$header_text_color = get_header_textcolor();
                    if( get_query_var( 'paged' ) ){
                        \$my_page = get_query_var( 'paged' );
                    } else if( get_query_var( 'page' ) ){
                        \$my_page = get_query_var( 'page' );
                    } else{
                        \$my_page = 1;
                    }
                    set_query_var( 'paged', \$my_page );
                    \$paged = \$my_page;
                    \$args = array(
                        'post_type' => 'stories',
                        'orderby'   => 'post_date',
                        'order' => 'DESC',
                        'post_status' => 'publish',
                        'posts_per_page' => 10,
                        'paged' => \$paged
                    );
                    \$post_list = new WP_Query(\$args);
                ?>
                <!doctype html>
                <html <?php language_attributes(); ?> >
                <head>
                    <meta charset=\"<?php bloginfo( 'charset' ); ?>\">
                    <?php if(isset(\$channelInfo['gsv'])){ ?>
                    <meta name=\"google-site-verification\" content=\"<?php echo(esc_attr(\$channelInfo['gsv']))?>\"/>
                    <?php } ?>
                    <?php 
                        if(isset(\$channelInfo['languageId']) and \$channelInfo['languageId'] == 3){  
                    ?>
                        <?php if(isset(\$channelInfo[\"name\"])){ ?>
                            <title> <?php _e(\"Regarder \".\$channelInfo[\"name\"] .\" en Web Stories!\") ?> </title>
                        <?php } ?>
                        <meta name=\"description\" content=\"<?php _e(\"Notre portail d'histoires web vous permet d'accèder à un résumé de nos contenus dans un format vertical, visuel et interactif. Cliquez sur une web story  puis tapez sur le côté droit de l'écran pour avancer à l'ecran suivant et à gauche pour l'écran précédent. Les swipe up vous permettent d'avoir plus d'information ou une information plus approfondie sur un sujet donné.\")?>\"/>  
                    <?php
                        }else{
                    ?>
                        <?php if(isset(\$channelInfo[\"name\"])){ ?>
                            <title> <?php _e(\"Watch \".\$channelInfo[\"name\"] .\" in Web Stories!\") ?> </title>
                        <?php } ?>
                        <meta name=\"description\" content=\"<?php _e(\"Our web story portal allows you to access a summary of our content in a vertical, visual and interactive format. Click on a web story and then tap on the right side of the screen to advance to the next screen and on the left for the previous screen. Swiping up allows you to get more information or more in-depth information on a given topic\")?>\"/>  
                    <?php
                        }
                    ?>
                    <?php get_header(); ?>
                </head>
                <body <?php body_class(); ?>>
                    <div style = \"display : flex;margin:0;padding:0;\">
                    <div id=\"list-post-panel\" style=\"width:100%;\">
                            <br></br>
                            <?php 
                                if ( \$post_list->have_posts() ) : 
                                \$count = 0;
                            ?>
                            
                                <ul style=\"margin : 0; padding : 0\">
                                    <?php while ( \$post_list->have_posts() ) : 
                                        \$post_pos = \$post_pos + 1 ;
                                        \$post_list->the_post();
                                        \$post_id = get_the_ID();
                                        \$image = get_the_post_thumbnail_url( \$post_id); 
                                        \$post_type = get_post_type(\$post_id);   
                                        \$taxonomies = get_object_taxonomies(\$post_type);   
                                        \$taxonomy_names = wp_get_object_terms(get_the_ID(), \$taxonomies,  array(\"fields\" => \"names\")); 
                                        \$count = \$count + 1;
                                    ?>
                                        <a 
                                            style = \"margin: 5px; padding: 1%;\"
                                            href=\"<?php  the_permalink(); ?>\"
                                        >
                                            <li  
                                                <?php if ( \$post_pos < 5 ) :  ?>
                                                class = \"li-top\"
                                                <?php endif; ?>
                                                style=\"background-image:url(<?php  echo esc_url(\$image); ?>)\"
                                            >   
                                                <div class=\"post-list-title\">
                                                    <div style=\"color : #fff ; width:max-content; padding : 0 1rem ; border-radius : 5px;\">
                                                        <?php  isset(\$taxonomy_names[0]) ? esc_html_e(\$taxonomy_names[0]) : null ;?>
                                                    </div>
                                                    <?php
                                                        if(\$count == 1 ) :
                                                    ?>
                                                        <h1>
                                                            <?php the_title(); ?>
                                                        </h1>
                                                    <?php else : ?>
                                                        <h2>
                                                            <?php the_title(); ?>
                                                        </h2>
                                                    <?php endif; ?>
                                                    <p>
                                                        <?php esc_html_e(get_the_date())?>
                                                    </p>
                                                </div>
                                            </li>
                                        </a>
                                        
                                    <?php endwhile; ?>
                                </ul>
                                <?php wp_reset_postdata(); ?>
                                <div class=\"zmooz_plugin_pagination\">
                        
                                    <?php 
                                    \$allowed_html = array(
                                        'span' => array(
                                            'class'  => array(),
                                            'aria-curret' => array(),
                                        ),
                                        'a' => array(
                                            'class'  => array(),
                                            'href' => array(),
                                        ),
                                        'i' => array(),
                                        'div'=> array(),
                                    );
                                    \$pagination = paginate_links( array(
                                        'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                                        'total'        => \$post_list->max_num_pages,
                                        'current'      => max( 1, get_query_var( 'paged' ) ),
                                        'format'       => '?paged=%#%',
                                        'show_all'     => false,
                                        'type'         => 'plain',
                                        'end_size'     => 2,
                                        'mid_size'     => 1,
                                        'prev_next'    => true,
                                        'prev_text'    => sprintf( '<i> %1\$s</i>', __( 'Prev', 'text-domain' ) ),
                                        'next_text'    => sprintf( '<i>%1\$s </i>', __( 'Next', 'text-domain' ) ),
                                        'add_args'     => false,
                                        'add_fragment' => '',
                                    ) );
                                    echo wp_kses(\$pagination,\$allowed_html);
                                    ?>
                                </div>
                                
                            <?php else : ?>
                                <div style=\"width : max-content;margin:0 auto;\"><?php _e( 'There are no posts to display' ); ?></div>   
                            <?php endif; ?>
                        </div>
                    </div>
                
                    </body>
                    <?php get_footer(); ?>
            </html>"
            );

            //CSS
            file_put_contents(get_template_directory().'/zmooz_stories_style.css',   "
                #list-post-panel{
                    max-width : 1200px;
                    margin : 0 auto;
                }
                #list-post-panel ul { 
                    width: 100%;
                    margin- top : 3rem;
                    list-style: none;
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: flex-start;
                }
                #list-post-panel a{
                    width : 100%;
                    max-width : 300px;
                    max-height : calc( 300px * 16 / 9 );
                }
                @media (max-width: 480px) {
                    #list-post-panel a{
                        width : 100%;
                        max-width : 150px;
                        max-height : calc( 150px * 16 / 9 );
                    }
                }
                #list-post-panel li{ 
                    position : relative;
                    font-size : 1rem;
                    width: 100%;
                    max-width : 100%;
                    height: calc( 300px * 16 / 9 );
                    float: left;
                    background: #eee;
                    list-style: none;
                    text-align: center;
                    border: 1px solid #cfcfcf;
                    border-radius: 5px;
                    background-repeat: no-repeat;
                    background-size: cover;
                    margin : 0px;
                    width: 100%;
                    max-width: 300px;
                    max-height: calc( 300px * 16 / 9 );
                }
                @media (max-width: 680px) {
                    #zmooz_template_container{
                        flex-wrap: wrap !important;
                    }
                }
                @media (max-width: 480px) {
                    #list-post-panel li{
                        height: calc( 150px * 16 / 9 );
                    }
                }
                
                
                .post-list-featured-image img{ 
                    width: 100%;
                    height: 200px; 
                }
                .post-list-title{
                    height : max-content;
                    position: absolute;
                    padding: 5%;
                    bottom: 0;
                    color: #fff;
                    font-weight: bold;
                    text-align: left;
                    background: linear-gradient(180deg,transparent,rgba(0,0,0,.89));
                    background-position: bottom;
                    background-repeat: no-repeat;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                }
                @media (max-width: 480px) {
                    .post-list-title{
                        font-size: .8rem;
                    }
                }
                .post-list-title h1,.post-list-title h2{
                    width:100%;
                    overflow: hidden;
                    display: -webkit-box;
                    -webkit-line-clamp: 4;
                    -webkit-box-orient: vertical;
                    line-height : 1.5 !important;
                    margin : 0 !important;
                    font-size : 1.5em !important;
                    color : #fff !important;
                }
                @media (max-width: 480px) {
                    .post-list-title p{
                        -webkit-line-clamp: 2;
                    }
                }
                
                .zmooz_plugin_pagination{
                    display : flex;
                    justify-content : center;
                    width: max-content !important;
                    margin: 1rem auto !important;
                }
                .zmooz_plugin_pagination .page-numbers{
                    padding :5px;
                    white-space: nowrap !important;
                }
                .zmooz_plugin_pagination .current{
                    background-color : #ccc;
                }
                .zmooz_plugin_backlink{
                    display: flex !important;
                    justify-content: center !important;
                    width: max-content !important;
                    margin: 3rem auto !important;
                    text-overflow: ellipsis !important;
                    overflow: hidden !important;
                    white-space: nowrap !important;
                    font-size : 10px;
                }
            ");
            
        }

        public function zmooz_add_custom_templates_styles() {
            // if ( is_post_type_archive( "stories" ) ) {
                wp_register_style("zmooz_stories_style", get_template_directory_uri() . "/zmooz_stories_style.css", '', '1.0.0');
                wp_enqueue_style('zmooz_stories_style');  
            // }
        }
        /** END */
        
        public function zmooz_add_custom_button_import_stories(){

            
            $post_type = $this->zmooz_get_post_type();

            $count_posts = get_posts(array(
                'numberposts' => 5,
                'fields' => 'ids',
                'post_type' => $post_type,
                'post_status' => ['publish','draft','trash']
            ));

            global $current_screen;

            if($current_screen->post_type != $post_type) return;
            
            // Not our post type, exit earlier
            // You can remove this if condition if you don't have any specific post type to restrict to. 
            

            ?>
                <script type="text/javascript">
                    function zmooz_show_loading_Div() {
                        document.getElementById('loadingGif').style.display = "flex";
                        // setTimeout(function() {
                        //     document.getElementById('loadingGif').style.display = "none";
                        //     document.getElementById('showme').style.display = "block";
                        // },2000);
                        return 1;
                    }
                    jQuery(document).ready( function($)
                    {

                        var loading = jQuery('<div>',{
                            'id' : 'loadingGif',
                            'style':'display:none;width: 100vw;height: 100vh;margin-left: 1rem;justify-content: center;align-items: center;position: fixed;top: 0;left: 0;'
                        })

                        loading.append(jQuery('<img>',{
                            'src' : 'https://storage.googleapis.com/zmooz-media/zmooz-static-assets/wordpress-loading-giff.gif'
                        }))

                        var newForm1 = jQuery('<form>', {
                            'target': '_top',
                            'style' : 'margin-top : 1rem;margin-bottom : .5rem;display: flex;justify-content: center;align-items: center;',
                            'method' : 'POST' ,
                            'id' : 'import_all'
                        });

                        newForm1.append(jQuery('<input>', {
                            'name': 'import_all',
                            'value': 'Import new Zmooz stories',
                            'type': 'submit',
                            'class' : 'button',
                            'onclick' : 'zmooz_show_loading_Div()'
                        }));

                
                        var newForm2 = jQuery('<form>', {
                            'target': '_top',
                            'style' : 'margin-left: 2rem;margin-top : 1rem;margin-bottom : .5rem;display: flex;justify-content: center;align-items: center;',
                            'method' : 'POST' ,
                            'id' : 'update_all'
                        });
                        newForm2.append(jQuery('<input>', {
                            'name': 'update_all',
                            'value': 'Update imported Zmooz stories',
                            'type': 'submit',
                            'class' : 'button',
                            'onclick' : 'zmooz_show_loading_Div()'
                        }));

                        var newDiv = jQuery('<div>', {
                            'style' : 'margin-top : 1rem;margin-bottom : .5rem;display: flex;justify-content: center;align-items: center;',
                        });
                        newDiv.append(loading)
                        newDiv.append(newForm1)
                        newDiv.append(newForm2)

                        jQuery(jQuery(".wrap h1")[0]).append(newDiv);
                    });
                </script>
            <?php
            if(empty($count_posts )){
            ?>
            <script type="text/javascript">
                
                jQuery(document).ready( function($)
                {

                    var link1 = jQuery('<a>', {
                        'target': '_blank',
                        'href':'https://zmooz.com/studio/auth/login',
                        'style' : ' max-width: 400px; width:100%; white-space: nowrap;margin-top : 1rem;margin-bottom : .5rem;display: flex;justify-content: center;align-items: center;background-color: #ff471e;padding : calc(14 * 100vw / 1920);border-radius: 5px;color: #fff;text-decoration: none;font-weight: bold;font-size: calc(16 * 100vw / 1700);'
                    });
                    link1.text("Already Zmooz member, login here .")

                    var link2 = jQuery('<a>', {
                        'target': '_blank',
                        'href':'https://zmoozy.com/studio/auth/signup',
                        'style' : ' max-width: 400px; width:100%;  white-space: nowrap;margin-top : 1rem;margin-bottom : .5rem;display: flex;justify-content: center;align-items: center;background-color: #ff471e;padding: calc(14 * 100vw / 1920);border-radius: 5px;color: #fff;text-decoration: none;font-weight: bold;font-size: calc(16 * 100vw / 1700);'
                    });
                    link2.text("New on Zmooz ? Create your first story now !")

                    var banner = jQuery('<div>',{
                        'style':'margin-top: 1rem ; width:calc(100% - 2rem );height:300px; border-radius:5px; padding:1rem; background-color:#fff;display: flex;justify-content: space-between;'
                    });

                    var bannerFlexDiv = jQuery('<div>', {
                        'style' : 'width:calc(100% - 2rem );min-width: 20margin-bottom : .5rem;display: flex;flex-direction: column;justify-content: center;align-items: center;padding: 1rem',
                    });

                    var bannerInfoDiv = jQuery('<img>',{
                        'src':'<?php echo esc_url( (plugin_dir_url( __FILE__ ).'images/banner.png')) ?>',
                        'style' : 'max-width: calc(calc(100% - 300px) - 4rem ); height:auto;apsect-ratio:16/9;margin:1rem;'
                    })
                    
                    bannerFlexDiv.append(link1)
                    bannerFlexDiv.append(link2)
                    banner.append(bannerInfoDiv)
                    banner.append(bannerFlexDiv)
                

                    jQuery(jQuery(".wrap")[0]).prepend(banner);
                });
            </script>
            <?php
            }else{
            ?>
            <script type="text/javascript">
                
                jQuery(document).ready( function($)
                {

                    var link1 = jQuery('<a>', {
                        'target': '_blank',
                        'href':'https://zmooz.com/studio',
                        'style' : ' max-width: 400px; width:100%; white-space: nowrap;display: flex;justify-content: center;align-items: center;background-color: #ff471e;padding : calc(14 * 100vw / 1920);border-radius: 5px;color: #fff;text-decoration: none;font-weight: bold;font-size: calc(16 * 100vw / 1700);'
                    });
                    link1.text("Login to Zmooz")
                    var banner = jQuery('<div>',{
                        'style':'margin-top: 1rem ; width:calc(100% - 2rem ); border-radius:5px; padding:1rem; background-color:#fff;display: flex;justify-content: space-between;align-items:center;'
                    });

                    var bannerFlexDiv = jQuery('<div>', {
                        'style' : 'margin-right:1rem;width:max-content;display: flex;flex-direction: column;justify-content: center;align-items: center;',
                    });

                    var bannerInfoDiv = jQuery('<img>',{
                        'src':'<?php echo esc_url( (plugin_dir_url( __FILE__ ).'images/logo.png')) ?>',
                        'style' : 'max-width: calc(calc(100% - 300px) - 4rem ); height:50px;apsect-ratio:16/9;'
                    })
                    
                    bannerFlexDiv.append(link1)
                    banner.append(bannerInfoDiv)
                    banner.append(bannerFlexDiv)
                

                    jQuery(jQuery(".wrap")[0]).prepend(banner);
                });
            </script>
            <?php
            }
            return;
        }

        public function zmooz_wp_plugin_function_remove_bulk( $actions, $post ) { 
            if ($post->post_type=='stories') {
                unset($actions['edit']);
                // unset($actions['trash']);
                // unset($actions['view']);
                // unset($actions['inline hide-if-no-js']);
            }
            return $actions;
        }

        public function zmooz_wp_plugin_filter_function_name_6509( $link, $post_id, $context ){
        
            global $post;
            if($post->post_type=="stories"){
                $link = get_permalink( $post_id );
            }
            return $link;
        }

        public function zmooz_wp_plugin_function_admin_menu() {

            $post_type = $this->zmooz_get_post_type();

            if( $post_type != "stories"){

                // add_menu_page(
                //     'Zmooz', // page title
                //     'Zmooz Stories', // menu title
                //     'manage_options', // capability
                //     'zmooz-stories', // menu slug
                //     array($this,'zmooz_wp_plugin_function_admin_view'), // callback function
                //     plugins_url('/images/logo/favvertical.png', __FILE__ ), // icon URL or dashicon class
                //     30 // position
                // );
    
                // add_submenu_page(
                //     'zmooz-stories', //$parent_slug
                //     __( 'Dashboard', 'zmooz-dashboard' ),
                //     __( 'Dashboard', 'zmooz-dashboard' ),
                //     'manage_options',//$capability
                //     'zmooz-stories-dashboard',//$menu_slug
                //     array($this,'zmooz_wp_plugin_function_admin_view')//$function
                // );

                add_submenu_page(
                    'edit.php?post_type='.$post_type, //$parent_slug
                    __( 'Zmooz', 'zmooz-dashboard' ),
                    __( 'Zmooz', 'zmooz-dashboard' ),
                    'manage_options',//$capability
                    'zmooz-stories-dashboard',//$menu_slug
                    array($this,'zmooz_wp_plugin_function_admin_view'),//$function
                );

                // remove_submenu_page( 'zmooz-stories', 'zmooz-stories' );

            }else{
               
                add_submenu_page(
                    'edit.php?post_type='.$post_type, //$parent_slug
                    __( 'Dashboard', 'zmooz-dashboard' ),
                    __( 'Dashboard', 'zmooz-dashboard' ),
                    'manage_options',//$capability
                    'zmooz-stories-dashboard',//$menu_slug
                    array($this,'zmooz_wp_plugin_function_admin_view')//$function
                );
               
                // $google_plugin_active = in_array( 'web-stories/web-stories.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )));

                // if($google_plugin_active==true){

                //     add_submenu_page(
                //         'edit.php?post_type='.$post_type, //$parent_slug
                //         __( 'Settings', 'zmooz-dashboard' ),
                //         __( 'Settings', 'zmooz-dashboard' ),
                //         'manage_options',//$capability
                //         'zmooz-stories-settings',//$menu_slug
                //         array($this,'zmooz_wp_plugin_function_settings_page')//$function
                //     );
                // }
            }
           
        }

        public function zmooz_wp_plugin_function_admin_dashboard( $file, $args ){
            // ensure the file exists
            if ( !file_exists( $file ) ) {
                return '';
            }
            // Make values in the associative array easier to access by extracting them
            if ( is_array( $args ) ){
                extract( $args );
            }
            // buffer the output (including the file is "output")
            ob_start();
            include $file;
            return ob_get_clean();
        }

        public function zmooz_wp_plugin_function_admin_view() {


            global $ZMOOZ_API;
            global $ZMOOZ_USED_DOMAIN;
            global $ZMOOZ_USER_AUTH_TOKEN;

            $page = isset($_GET['paged']) ? $_GET['paged'] : 1;
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $nb_result = 10;
            $offset = ($page - 1) * $nb_result;
            $request = wp_remote_get(  $ZMOOZ_API.'/wp-plugin/get-stories?domain='.$ZMOOZ_USED_DOMAIN.'&filter[q]='.$search.'&filter[limit]='.$nb_result.'&filter[offset]='.$offset);
            $response     = wp_remote_retrieve_body( $request );
            $storiesList = json_decode($response, true);
            if($storiesList){
                extract($storiesList);
            }
            if ( is_admin() ) {
                // we are in admin mode
                include __DIR__ . '/admin/zmooz_wp_plugin_admin.php';
            }
            return ;
          
        }

        public function zmooz_wp_plugin_function_settings_page(){
            
            if ( is_admin() ) {
                // we are in admin mode
                include __DIR__ . '/admin/zmooz_wp_plugin_settings.php';
            }
            return ;
            
        }

        public function zmooz_wp_plugin_function_studio_view() {

            if ( is_admin() ) {
                // we are in admin mode
                include __DIR__ . '/admin/zmooz_wp_plugin_studio.php';
            }
            return ;
          
        }

        public function zmooz_checkupPlugin(){
            $response = new WP_REST_Response("OK", 200);

            // Set headers.
            $response->set_headers([ 'Cache-Control' => 'must-revalidate, no-cache, no-store, private' ]);

            return $response;
        }

        public function zmooz_create_post_by_endpoint( $data ) {
            
            global $ZMOOZ_API;
            global $ZMOOZ_USED_DOMAIN;
            if(!function_exists('zmooz_wp_plugin_page_data')){
                require_once __DIR__ . '/admin/actions.php';
                $action_collection = new zmooz_wp_actions_V2();
            }

            $storyId = $data['storyId'];
            $storyrequest = wp_remote_get( $ZMOOZ_API.'/wp-plugin/get-story/'.$storyId.'?domain='.$ZMOOZ_USED_DOMAIN);
            $storyResponse = json_decode(wp_remote_retrieve_body( $storyrequest ),true );
            $result = null;

            $story_url = isset($storyResponse['link'])?$storyResponse['link']:null;
            if($story_url !== null){
                $result = $action_collection->zmooz_create_single_story($story_url);
                $post = array(
                    "postId" => $result['data']['post_id'],
                    "storyTitle" => $result['data']['post_title'],
                    "storyId" => $storyId,
                    "storyUrl" => $result['data']['post_slug']
                );
                $result['data']=$post;
            }else{
                $result = array(
                    "data" => "No found story : ".$story_url,
                    "status" => 500
                );
            }

        
            $response = new WP_REST_Response(isset($result['data'])? $result['data'] : "An error occured");
            $response->set_headers([ 'Cache-Control' => 'must-revalidate, no-cache, no-store, private' ]);
            $response->set_status(isset($result['status'])? $result['status'] : 500);
            return $response;

        }

        public function zmooz_update_post_by_endpoint( $data ) {

            global $ZMOOZ_API;
            global $ZMOOZ_USED_DOMAIN;
            if(!function_exists('zmooz_wp_plugin_page_data')){
                require_once __DIR__ . '/admin/actions.php';
                $action_collection = new zmooz_wp_actions_V2();
            }
            $postId = $data->get_param('postId');
            $storyId = $data->get_param('storyId');


            $storyId = $data['storyId'];
            $storyrequest = wp_remote_get( $ZMOOZ_API.'/wp-plugin/get-story/'.$storyId.'?domain='.$ZMOOZ_USED_DOMAIN);
            $storyResponse = json_decode(wp_remote_retrieve_body( $storyrequest ),true );
            $result = null;
            
            $story_url = isset($storyResponse['link'])?$storyResponse['link']:null;
            if($story_url !== null){
                $result = $action_collection->zmooz_update_single_story($story_url,1,$postId,null);
                $post = array(
                    "postId" => $result['data']['post_id'],
                    "storyTitle" => $result['data']['post_title'],
                    "storyId" => $storyId,
                    "storyUrl" => $result['data']['post_slug']
                );
                $result['data']=$post;
            }else{
                $result = array(
                    "data" => "No found story : ".$story_url,
                    "status" => 500
                );
            }
            
            $response = new WP_REST_Response(isset($result['data'])? $result['data'] : "An error occured");
            $response->set_headers([ 'Cache-Control' => 'must-revalidate, no-cache, no-store, private' ]);
            $response->set_status(isset($result['status'])? $result['status'] : 500);
            return $response;
        }

        public function routing(){

            require_once __DIR__ . '/admin/actions.php'; 
            $action_collection = new zmooz_wp_actions_V2();

            if(isset($_POST['import_all'])){
                
                try{

                    return $action_collection->zmooz_wp_plugin_load_stories('import');
                }catch(\Exception $ex){

                }
            }else if(isset($_POST['update_all'])){

                try{
                    return $action_collection->zmooz_wp_plugin_load_stories('update');
                }catch(\Exception $ex){

                }
            }
        }
        
    }
    $zmooz_stories = new mk_zmooz_stories_v2();
    global $zmooz_stories;
endif;