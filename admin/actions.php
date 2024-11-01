<?php

if (!class_exists('zmooz_wp_actions_v2')):


    class zmooz_wp_actions_V2  {

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

        public function zmooz_wp_kses($html){
            
        // if($post_type!="web-story"){
            $allowed_tags = wp_kses_allowed_html( 'post' );
            $allowed_tags2 = array(
                'html' => array(
                    'amp' => true
                ),
                'head' => array(),
                'link' => array(
                    'rel' => true,
                    'as' => true,
                    'href' => true
                ),
                'meta' => array(
                    'name' => true,
                    'content' => true,
                    'charset' => true,
                ),
                'style' => array(
                    'amp-custom' => true,
                    'amp-boilerplate' => true,
                ),
                'title'=>array(

                ),
                'script' => array(
                    'src' => true,
                    'async' => true,
                    'custom-element'=>true,
                ),
                'noscript' => array(),
                'body' => array(
                    'class' => true,
                    'style' => true
                ),
                'amp-story' => array(  
                    'id' => true,
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
                ),
                'amp-fit-text' => array(  
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
                ),
                'amp-img' => array(
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
                ),
                'amp-story-page' => array(
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
                ),
                'amp-story-grid-layer' => array(
                    'id' => true,
                    'src' => true,
                    'style' => true,
                    'width' => true,
                    'height' => true,
                    'src' => true,
                    'alt' => true,
                    'layout' => true,
                    'template' => true,
                ),
                'amp-story-captions' => array(
                    'height' => true,
                ),
                'amp-story-shopping-attachment' => array(
                    'cta-text' => true,
                    'theme'    => true,
                    'src'      => true,
                ),
                'amp-story-shopping-config' => array(
                    'src' => true,
                ),
                'amp-story-shopping-tag' => array(),
                'amp-story-page-attachment' => array(
                    'href'  => true,
                    'theme' => true,
                ),
                'amp-story-cta-layer' => array(),
                'amp-story-animation' => array(
                    'trigger' => true,
                ),
                'amp-story-auto-analytics'=> array(
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
    
                ),
                'amp-story-page-outlink'=> array(
                    'layout' => true,
                    'theme' => true,
                    'cta-accent-color' => true,
                    'cta-accent-element' => true,
                    'aria-hidden' => true,
                    'aria-live' => true,
                    'style' => true,
                )
            );
            $allowed_tags_merged = array_merge($allowed_tags,$allowed_tags2);
            unset( $allowed_tags_merged['img'] );
            return wp_kses($html,$allowed_tags_merged);
        }

        public function zmooz_wp_plugin_page_data($url) {
            
            $query = wp_remote_get($url);
            $fp = wp_remote_retrieve_body( $query );
            
            if (!$fp) 
                return null;

            $titleRes = preg_match("/<title>(.*)<\/title>/siU", $fp, $title_matches);
            $ogImageRes = preg_match('/poster-portrait-src="(.*)"/siU', $fp, $ogImage_matches);
            $descRes = preg_match('/<meta name="description" content="(.*)"/siU', $fp, $desRes_match);
            if(!$desRes_match){
                $descRes = preg_match('/<meta name="description" content=\'(.*)\'/siU', $fp, $desRes_match);
            }
            
            //var_dump($title_matches,$titleRes);
            // Clean up title: remove EOL's and excessive whitespace.
            if( $title_matches){
                $title = preg_replace('/\s+/', ' ', $title_matches[1]);
                $title = trim($title);
            }else{
                $title = null;
            }

            if( $ogImage_matches){
                $ogImage = preg_replace('/\s+/', ' ', $ogImage_matches[1]);
                $ogImage = trim($ogImage);
            }else{
                $ogImage = null;
            }
            
            if( $desRes_match){
                $description = $desRes_match[1];
                $description = trim($description);
            }else{
                $description = null;
            }
            
            $res['title'] = $title;
            $res['ogImage'] = $ogImage;
            $res['description'] = $description;
            $res['content'] = $fp;

            return $res;
        }

        public function zmooz_wp_plugin_Generate_Featured_Image( $image_url, $post_id, $filename  ){
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            if ( ! function_exists( 'post_exists' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/post.php' );
            }
            $upload_dir = wp_upload_dir();
            $query = wp_remote_get($image_url);
            $image_data = wp_remote_retrieve_body( $query );
            // $filename = basename($image_url);
            $wp_filetype = wp_check_filetype($filename, null );
            $filename_with_ext = empty($wp_filetype['type']) ? $filename.'.jpeg' : $filename;

            if(wp_mkdir_p($upload_dir['path']))
            $file = $upload_dir['path'] . '/' . sanitize_file_name($filename_with_ext);
            else
            $file = $upload_dir['basedir'] . '/' . sanitize_file_name($filename_with_ext);

            $such_post_exists = post_exists( sanitize_file_name($filename),'','','attachment','');

            if($such_post_exists == 0 || $such_post_exists == null){

                file_put_contents($file, $image_data);
            
                $wp_filetype = wp_check_filetype($filename_with_ext, null );
                
                $attachment = array(
                    'post_mime_type' => !empty($wp_filetype['type']) ? $wp_filetype['type'] : 'image/jpeg',
                    'post_title' => sanitize_file_name($filename),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                $attach_id = wp_insert_attachment( $attachment, $file, $post_id, true);

                $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
                $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
                $res2= set_post_thumbnail( $post_id, $attach_id );

            }else{

                set_post_thumbnail( $post_id, $such_post_exists );

            }
        }

        public function zmooz_create_asset($image_url,$post_id){
            if ( ! function_exists( 'post_exists' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/post.php' );
            }
            global $zmooz_upload_dir;
            $fileNameMatch = [];
            $image_url  = trim($image_url,'"');

            preg_match("/https:\/\/(?:cdn\.zmooz\.com|storage\.googleapis\.com\/zmooz-estoryc-images|m\.amp\.story\.domains\/images\/stickers|zmooz\.com\/images\/stickers|media0\.giphy\.com\/media|ik\.imagekit\.io\/buulraa8c|zmooz-bucket\.s3\.bhs\.io\.cloud\.ovh\.net\/asset|zmooz-bucket\.s3\.bhs\.io\.cloud\.ovh\.net\/channel|zmooz-bucket\.s3\.bhs\.io\.cloud\.ovh\.net\/story)\/(.*?)\/?$/siU", $image_url, $fileNameMatch);
            
            $filename = isset($fileNameMatch[1]) ? $fileNameMatch[1] : null;
            // https:\/\/cdn.zmooz.com\/(.*?\.webp)
            // https:\/\/storage.googleapis.com\/zmooz-estoryc-images\/(.*?)"
            $upload_dir = wp_upload_dir();

            $query = wp_remote_get($image_url);
            $image_data = wp_remote_retrieve_body( $query );
            // $filename = basename($image_url);
            $wp_filetype = wp_check_filetype($filename, null );
            $filename_with_ext = empty($wp_filetype['type']) ? $filename.'.jpeg' : $filename;

            if(wp_mkdir_p($upload_dir['path']))
            $file = $upload_dir['path'] . '/' . sanitize_file_name($filename_with_ext);
            else
            $file = $upload_dir['basedir'] . '/' . sanitize_file_name($filename_with_ext);
            
            $such_post_exists = post_exists( sanitize_file_name($filename_with_ext),'','','attachment','');
            
            if($such_post_exists == 0 || $such_post_exists == null){
            
                file_put_contents($file, $image_data);
            
                $wp_filetype = wp_check_filetype($filename_with_ext, null );
                
                $attachment = array(
                    'post_mime_type' => !empty($wp_filetype['type']) ? $wp_filetype['type'] : 'image/jpeg',
                    'post_title' => sanitize_file_name($filename_with_ext),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                $attach_id = wp_insert_attachment( $attachment, $file,$post_id);
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once( ABSPATH . 'wp-admin/includes/media.php' );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $file ,$post_id,true);
                $res1= wp_update_attachment_metadata( $attach_id, $attach_data ); 
                return $zmooz_upload_dir.'/'.sanitize_file_name($filename_with_ext);
            }else{
                // var_dump("AlreadyExisted : ".$imgUrl);
                return wp_get_attachment_url($such_post_exists);
            }

            return null;
        }

        public function zmooz_save_story_assets($html,$post_id){
            //Replace all image slug with te

            try{
                
                preg_match_all('/"(https:\/\/(?:cdn\.zmooz\.com|storage\.googleapis\.com|media0.giphy.com|ik.imagekit.io|zmooz-bucket\.s3\.bhs\.io\.cloud\.ovh\.net|m\.amp\.story\.domains)\/.*?)"/i', $html, $matches);

                $urls = $matches[0];
                $uploadedUrls = array();

                foreach( $urls as $thisurl){

                    $uploaded_result = $this->zmooz_create_asset($thisurl,$post_id);
                    if(isset($uploaded_result) && $uploaded_result!= null){
                        array_push($uploadedUrls,array("/".preg_quote($thisurl,"/")."/","\"".$uploaded_result."\""));
                    }
                }

                return $uploadedUrls;

            }catch(\Exception $ex){

                return false;
            }

        }

        public function zmooz_rewrite_story_html($story_slug,$post_slug,$html,$post_id){
            global $zmooz_upload_dir;
            $get_upload_dir = wp_upload_dir();
            $zmooz_upload_dir = $get_upload_dir['url'] ;

            $savedAssetArray = $this->zmooz_save_story_assets($html,$post_id);
            
            if(isset($savedAssetArray) && sizeof($savedAssetArray) > 0){

                //Replace the story canonical url by the same url as the registered post
                $html = preg_replace("/".preg_quote($story_slug,"/")."/", $post_slug, $html);
                $html = preg_replace(array_column($savedAssetArray, 0), array_column($savedAssetArray, 1), $html);
                 
            
                return $html;
            }
            return $html;
        }

        public function zmooz_convert_styles_to_classes($html) {

            //get fonts : 
            $fonts = preg_match("/href=\"(https:\/\/fonts.googleapis.com\/css?.*)\"/siU", $html, $fontsMatch);

            // Use the DOMDocument class to parse the HTML
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_use_internal_errors(false);

            $newDom = new DOMDocument();
            
            // Use DOMXPath to select the <amp-story> element
            $xpath = new DOMXPath($dom);
            $ampStory = $xpath->query('//body//amp-story')->item(0);
            
            // Create a new document fragment to hold the modified HTML
            $fragment = $dom->createDocumentFragment();
            
            // Copy all elements in the head tag to the modified HTML
            $head = $dom->getElementsByTagName('head')->item(0);
            $html = $dom->createElement('html');
            $html->setAttribute('amp', "");
            $newHead = $dom->createElement('head');
            $newBody = $dom->createElement('body');
            
            if($this->zmooz_get_post_type() == "web-story"){
                //add font to the head
                $font = $dom->createElement('link');
                $font->setAttribute('rel', 'stylesheet');
                $font->setAttribute('href', $fontsMatch[1]);

                $ampruntime = $dom->createElement('script');
                $ampruntime->setAttribute('src', "https://cdn.ampproject.org/v0.js");
                $ampruntime->setAttribute('async', "");

                $storyruntime = $dom->createElement('script');
                $storyruntime->setAttribute('async', "");
                $storyruntime->setAttribute('custom-element', "amp-story");
                $storyruntime->setAttribute('src', "https://cdn.ampproject.org/v0/amp-story-1.0.js");

                //add rewrite head rules
                $rewriteHeadStart =  $dom->createElement('meta');
                $rewriteHeadEnd =  $dom->createElement('meta');
                $rewriteHeadStart->setAttribute('name','web-stories-replace-head-start');
                $rewriteHeadEnd->setAttribute('name','web-stories-replace-head-end');

                $newHead->appendChild($font);
                $newHead->appendChild($ampruntime);
                $newHead->appendChild($storyruntime);
                $newHead->appendChild($rewriteHeadStart);
                $newHead->appendChild($rewriteHeadEnd);
                
            }else{
                $styleBoliverPlate = $dom->createElement('style');
                $styleBoliverPlate->setAttribute('amp-boilerplate',"");
                $styleBoliverPlate->nodeValue = "
                    body {
                        -webkit-animation: -amp-start 8s steps(1, end) 0s 1 normal both;
                        -moz-animation: -amp-start 8s steps(1, end) 0s 1 normal both;
                        -ms-animation: -amp-start 8s steps(1, end) 0s 1 normal both;
                        animation: -amp-start 8s steps(1, end) 0s 1 normal both;
                    }
                    @-webkit-keyframes -amp-start {
                        from {
                            visibility: hidden;
                        }
                        to {
                            visibility: visible;
                        }
                    }
                    @-moz-keyframes -amp-start {
                        from {
                            visibility: hidden;
                        }
                        to {
                            visibility: visible;
                        }
                    }
                    @-ms-keyframes -amp-start {
                        from {
                            visibility: hidden;
                        }
                        to {
                            visibility: visible;
                        }
                    }
                    @-o-keyframes -amp-start {
                        from {
                            visibility: hidden;
                        }
                        to {
                            visibility: visible;
                        }
                    }       
                    @keyframes -amp-start {
                        from {
                            visibility: hidden;
                        }
                        to {
                            visibility: visible;
                        }
                    }";

                $noscriptBoliverPlate = $dom->createElement('noscript');
                $noscriptBoliverPlate->nodeValue ="
                <style amp-boilerplate>
                    body {
                        -webkit-animation: none;
                        -moz-animation: none;
                        -ms-animation: none;
                        animation: none;
                    }
                </style>";
                $child = $head->firstChild;
                while ($child !== null) {
                    $nextChild = $child->nextSibling;
                    if (($child->nodeName != 'style' || ($child->nodeName == 'style' and $child->hasAttribute('amp-boilerplate'))) and  !($child->nodeName == 'script' and !$child->hasAttribute('src'))) {
                        $newHead->appendChild($child);
                    } else {
                        // Do something else with the child node (optional)
                    }
                    $child = $nextChild;
                }

                $newHead->appendChild($styleBoliverPlate);
                $newHead->appendChild($noscriptBoliverPlate);
            }

            

            // Get all of the HTML elements that have a "style" attribute
            $elements = $xpath->query('//body//*[@style]');
            $usedClassNames = array();
            $customStyle = '';

            foreach ($elements as $element) {
                $style = $element->getAttribute('style');
                if (!empty($style)) {
                    // Generate a random class name that hasn't been used yet
                    do {
                        $class = 'class-' . rand(1000, 9999);
                    } while (in_array($class, $usedClassNames));
                    
                    $usedClassNames[] = $class;
        
                    // Add the new class to the element's "class" attribute as the first class
                    $classAttr = $element->getAttribute('class');
                    
                    if (empty($classAttr)) {
                        $element->setAttribute('class', $class);
                    } else {
                        $classes = explode(' ', $classAttr);
                        // remove all class names starting with 'i-amphtml'
                        $classes = array_filter($classes, function($className) {
                            return strpos($className, 'i-amphtml') !== 0;
                        });
                        array_unshift($classes, $class);
                        $classes = array_unique($classes);
                        $classAttr = implode(' ', $classes);
                        $element->setAttribute('class', $classAttr);
                    }
                    
                    // Remove the "style" attribute
                    $element->removeAttribute('style');
        
                    // Create a new style rule for the class
                    $newStyle = 'amp-story .' . $class . '{' . $style . '}';
        
                    // Add the new style rule to the custom style string
                    $customStyle .= $newStyle;
                }
            }


            // If there are styles to add, create a new "style" element with the "amp-custom" attribute
            if (!empty($customStyle)) {
                $ampCustomStyle = $xpath->query('//style[@amp-custom]')->item(0);
                $oldstyles = $ampCustomStyle->nodeValue;
                if ($ampCustomStyle) {
                    $ampCustomStyle->nodeValue = $oldstyles .' '.$customStyle;
                }
                $newHead->appendChild($ampCustomStyle);
                $html->appendChild($newHead);
            }


            // Add the "i-amphtml-layout" attribute to the <amp-story> element
            // $ampStory->setAttribute('i-amphtml-layout', '');
            $body = $xpath->query('//body')->item(0);
            // Remove all <amp-analytics> elements and their children
            $analyticsElements = $xpath->query('//amp-analytics');
            foreach ($analyticsElements as $analyticsElement) {
                $analyticsElement->parentNode->removeChild($analyticsElement);
            }
            // Move all of the modified elements to the document fragment
            while ($body->firstChild) {
                $newBody->appendChild($body->firstChild);
            }
            $html->appendChild($newBody);

            // Serialize the modified HTML fragment and return it
            return $dom->saveHTML($html);

        }
        

        public function zmooz_wp_plugin_patch_post_content($ID,$story_url,$post_slug){

            $post_data = $this->zmooz_wp_plugin_page_data($story_url);

            
            if(isset($post_data['title']) && isset($post_data['description']) && isset($post_data['content'])){

                $url_explode = explode("/",$story_url);
                $story_slug = $url_explode[array_key_last($url_explode)];
                $post_slug = strtolower($story_slug);
                $post_content = $post_data['content'];

                $post_content = $this->zmooz_rewrite_story_html($post_content);
                $post_content = $this->zmooz_convert_styles_to_classes($post_content);
        
                $my_post = array(
                    'ID' => $ID,
                    'post_title' => $post_data['title'],
                    'post_content' => $post_content,
                    'post_name' =>$post_slug,
                    'post_excerpt' => $post_data['description']
                );
                
                wp_update_post( $my_post );
                return array(
                    "post_id" => $ID,
                );
            }
        }

        public function zmooz_wp_plugin_such_post_exists($title,$content_filtered) {

            global $wpdb;
            
            $p_title = wp_unslash( sanitize_post_field( 'post_title', $title, 0, 'db' ) );
            $p_content_filtered = wp_unslash( sanitize_post_field( 'post_content_filtered', $content_filtered, 0, 'db' ) );
            if ( !empty ( $title ) ) {
                return (int) $wpdb->query("SELECT FROM $wpdb->posts WHERE post_title = $p_title AND post_content_filtered");
            }
            
            return 0;
        }

        public function zmooz_wp_plugin_create_poste($title,$post_content,$status,$post_type,$slug,$post_thumbnail,$post_description,$story_sid,$lastPublished){

            $fount_post = post_exists($title,'','',$post_type,'');

            if($fount_post == 0){
                
                $new_post = array(
                    'post_title' => $title,
                    'post_content' => $post_content,
                    'post_status' => $status,
                    'post_type' => $post_type,
                    'post_name' =>$slug,
                    'post_thumbnail' => $post_thumbnail,
                    'post_excerpt' => $post_description,
                    'post_content_filtered' => $story_sid,
                    'post_date' => $lastPublished,
                    'post_date_gmt' => $lastPublished
                );
                // Set the page ID so that we know the page was created successfully
                $post_id = wp_insert_post($new_post);
            
                
                if($post_id && $post_thumbnail){
                    $this->zmooz_wp_plugin_Generate_Featured_Image($post_thumbnail, $post_id,$title );
                }
                
                return array(
                    "post_id" => $post_id,
                );
            }

            return null;

        }

        public function zmooz_wp_plugin_insert_post(
            $title,
            $post_description,
            $post_thumbnail,
            $post_content,
            $status,
            $post_type,
            $slug,
            $story_url,
            $outdated ,
            $postId,
            $story_sid,
            $lastPublished){
            // If the post/page doesn't already exist, then create it


            if(!isset($postId) || $postId== null  ) {

                return $this->zmooz_wp_plugin_create_poste($title,$post_content,$status,$post_type,$slug,$post_thumbnail,$post_description,$story_sid,$lastPublished);
            
            }else if (isset($outdated) && $outdated == 1){

                $existing_post = get_post( $postId );

                return $this->zmooz_wp_plugin_patch_post_content( $existing_post->ID,$story_url,$slug);
            }

        }

        public function zmooz_create_single_story($story_url){

            if ( ! function_exists( 'post_exists' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/post.php' );
            }

            $outdated = 1;
            $postId = null;
            $lastPublished = null;
            $url_explode = null;
            $story_slug = null;
            $story_slug_explode = null;
            $story_sid = null;
            $post_slug = null;
            $post_data = null;
            $post_title = null;
            $such_post_exists = null;
            
            //Extract last story url part containing the story sId

            $url_explode = explode("/",$story_url);
            $story_slug = $url_explode[array_key_last($url_explode)];
            $story_slug_explode = explode("-",$story_slug);
            $story_sid = $story_slug_explode[array_key_last($story_slug_explode)];
            $post_slug = strtolower($story_slug);
            $post_data = $this->zmooz_wp_plugin_page_data($story_url);
            
            //GET POST META DATA
            
            try{

                $post_description = isset($post_data['description']) ? $post_data['description'] : null;
                $post_thumbnail = isset($post_data['ogImage']) ? $post_data['ogImage'] : null;
                $post_content = isset($post_data['content']) ? $post_data['content'] : null;
                $post_status = "draft";
                $post_type = $this->zmooz_get_post_type();
                $post_title =  isset($post_data['title']) ? $post_data['title'] : null;
                
                if($post_title != null && $post_content!= null ){

                    $such_post_exists = get_page_by_title($post_title, OBJECT, $post_type);

                    if($such_post_exists){
                        return $this->zmooz_update_single_story($story_url,$outdated,$such_post_exists->ID,$lastPublished);
                    }else{

                        $default_user_id = get_option( 'zmooz_custom_plugin_default_user' );

                        $new_api_post = array(
                            'post_title' => $post_title,
                            'post_author' => $default_user_id,
                            'post_content' =>  '',
                            'post_status' => $post_status,
                            'post_type' => $post_type,
                            'post_name' =>$post_slug,
                            'post_thumbnail' => $post_thumbnail,
                            'post_excerpt' => $post_description,
                            'post_content_filtered' => $story_sid,
                            'post_date' => $lastPublished,
                            'post_date_gmt' => $lastPublished,
                            'meta_input' => array(
                                'inserted_via_zmooz' => true
                            )
                        );
        
                        $post_id = wp_insert_post($new_api_post,true,true);
                        
                        if($post_id !==0 && $post_thumbnail){
                            $thumb = $this->zmooz_wp_plugin_Generate_Featured_Image($post_thumbnail, $post_id,$post_title );
                        }
                        
                        if($post_id !== 0){
        
                            //Rewrite tech story content
                            $post_content = $this->zmooz_rewrite_story_html($story_slug,$post_slug,$post_content,$post_id);
                            
                            if($post_type=="stories"){
                                $post_content = addslashes(htmlentities($post_content));
                            }else{
                                $post_content = $this->zmooz_convert_styles_to_classes($post_content);
                                $post_content = $this->zmooz_wp_kses($post_content);
                            }
                           
                            if(!is_null($post_content)){
                        
                                $my_post = array(
                                    'ID' => $post_id,
                                    'post_content' =>  $post_content,
                                    "post_status" => "publish"
                                );
                    
                                $update_response = wp_update_post( $my_post );  
                            
                                $result = array(
                                    "data" => array(
                                        "post_id" => $post_id,
                                        "post_title" =>$post_title,
                                        "post_slug" => $post_slug
                                    ),
                                    "status" => 200
                                );            
                        
                            }else{
                    
                                $result = array(
                                    "data" => "Failed to create story",
                                    "status" => 500
                                );
                    
                            }
                        
                            return $result;
                        }

                        return null;

                    }

                }else{

                    $result = array(
                        "data" => "Failed to create story",
                        "status" => 500
                    );

                }
            
            }catch(\Exception $ex){
                $result = array(
                    "data" => "Failed to create story",
                    "status" => 500
                );
            }

           

            return null;

        }

        public function zmooz_update_single_story($story_url,$outdated,$postId,$lastPublished){

            
            //Extract last story url part containing the story sId
            $url_explode = explode("/",$story_url);
            $story_slug = $url_explode[array_key_last($url_explode)];
            $story_slug_explode = explode("-",$story_slug);
            $story_sid = $story_slug_explode[array_key_last($story_slug_explode)];
            $post_slug = strtolower($story_slug);
            $post_data = $this->zmooz_wp_plugin_page_data($story_url);
            $result = null;
            $post_type = $this->zmooz_get_post_type();
            try{

                $post_title = isset($post_data['title'])? $post_data['title'] : null;
                $post_content = isset($post_data['content'])? $post_data['content'] : null;
                $post_description = isset($post_data['description'])? $post_data['description']:null;
                $post_thumbnail = isset($post_data['ogImage'])? $post_data['ogImage']:null;

                if($post_title != null && $post_content != null){
                   
                    $post_content = $this->zmooz_rewrite_story_html($story_slug,$post_slug,$post_content,$postId);
                    if($post_type=="stories"){
                        $post_content = addslashes(htmlentities($post_content));
                    }else{ 
                        $post_content = $this->zmooz_convert_styles_to_classes($post_content);
                        $post_content = $this->zmooz_wp_kses($post_content);
                    }

                    $my_post = array(
                        'ID' => $postId,
                        'post_title' => $post_title,
                        'post_name' =>$post_slug,
                        'post_excerpt' => $post_description,
                        'post_content' => $post_content,
                        'post_content_filtered' => $story_sid,
                    );
                    
                    wp_update_post( $my_post );
    
                    if($post_thumbnail){
                        $this->zmooz_wp_plugin_Generate_Featured_Image($post_thumbnail, $postId,$post_title );
                    }
    
                    $result = array(
                        "data" => array(
                            "post_id" => $postId,
                            "post_title" =>$post_title,
                            "post_slug" => $post_slug
                        ),
                        "status" => 200
                    );  

                }else{

                    $result = array(
                        "data" => null,
                        "status" => 500
                    );
                }
                    
            
            } catch (\Exception $ex) {
                $result = array(
                    "data" => null,
                    "status" => 500
                ); 

            }


            return $result;

        }

        public function zmooz_wp_plugin_load_stories($type){

            /* BEGIN : get stories list */
            global $ZMOOZ_API;
            global $ZMOOZ_USED_DOMAIN;

            $current_offset = 0;
            $limit = 1;
            
            
            do{
                $offset = $current_offset * $limit;
                //$domain = get_site_url();
                if($type == "import"){
                    $request = wp_remote_get($ZMOOZ_API.'/wp-plugin/get-stories-to-import?domain='.$ZMOOZ_USED_DOMAIN .'&filter[limit]='.$limit.'&filter[offset]=0');
                }else if($type == "update"){
                    $request = wp_remote_get( $ZMOOZ_API.'/wp-plugin/get-stories-to-patch?domain='.$ZMOOZ_USED_DOMAIN .'&filter[limit]='.$limit.'&filter[offset]='.$offset);   
                }else{
                    return null;
                }
                
            
                $response     = wp_remote_retrieve_body( $request );
                $responsesBody = json_decode($response, true);

                if( $responsesBody != null){
                    $company = $responsesBody['company'];
                    $storiesList = $responsesBody['stories'];
                    /* END : get stories list */
                
                    if(isset($storiesList) && sizeof($storiesList) > 0){
                        // var_dump($storiesList);
                        $posts = array();
                        
                        /* BEGIN : Generate or update stories */
                        foreach($storiesList as $story) { //array of data.

                            try{

                                $result = array();

                                if($type == "import"){

                                    $story_url = $story['ampLink'];
                                    $result = $this->zmooz_create_single_story($story_url);
                                    if(isset($result) and $result!=null and is_array($result["data"])){
                                        $result["data"]["storyId"] = isset($story['storyId'])?$story['storyId'] : null;
                                    }

                                }else if($type == "update"){

                                    $story_url = $story['ampLink'];
                                    $outdated = isset($story['outdated']) ? $story['outdated'] : null;
                                    $postId = isset($story['postId']) ? $story['postId'] : null;
                                    $lastPublished = isset($story['lastPublished']) ? $story['lastPublished'] : null;
                                    $result = $this->zmooz_update_single_story($story_url,$outdated,$postId,$lastPublished);
                                    if(isset($result) and $result!=null and isset($result['data'])){
                                        $result["data"]["storyId"] = isset($story['storyId']) ? $story['storyId'] : null;
                                    }
                                }
                                


                                if(isset($result) && $result!=null && isset($result['data']) && isset($result['data']['post_id']) && $result['data']['post_id']!= null){
                    
                                    $post = array(
                                        "postId" => $result['data']['post_id'],
                                        "storyTitle" => $result['data']['post_title'],
                                        "storyId" => $story['storyId'],
                                        "storyUrl" => $result['data']['post_slug']
                                    );
                                    
                                    array_push($posts, $post);
                                }
                            }catch( \Exception $ex){
                                
                            }
                
                        }
                        /* END : Generate or update stories */
                        //Patch the post created or updated in our database
                        if(isset($posts) && sizeof($posts) > 0){
                            $result = wp_remote_post($ZMOOZ_API.'/wp-plugin/patch/posts?domain='.$ZMOOZ_USED_DOMAIN,
                                array(
                                    'method'      => 'POST',
                                    'timeout'     => 45,
                                    'redirection' => 5,
                                    'httpversion' => '1.0',
                                    'blocking'    => false,
                                    'headers'     =>  array(
                                        'content-type' => 'application/json'
                                    ),
                                    'body' => json_encode( 
                                        array(
                                            "company" => $company,
                                            "posts" => $posts
                                        )
                                    )
                                )
                            );
                
                            if($type == "import"){
                                printf('<div id="message" class="updated notice is-dismissible"><p>' . __('%d stories imported.', 'txtdomain') . '</p></div>', count($posts));
                            }else if($type == "update"){
                                printf('<div id="message" class="updated notice is-dismissible"><p>' . __('%d stories updated.', 'txtdomain') . '</p></div>', count($posts));
                            }
                
                        }else{

                            if($type == "import"){
                                printf('<div id="message" class="updated notice is-dismissible"><p>' . __('%d stories imported.', 'txtdomain') . '</p></div>', 0);
                            }else if($type == "update"){
                                printf('<div id="message" class="updated notice is-dismissible"><p>' . __('%d stories updated.', 'txtdomain') . '</p></div>', 0);
                            }
                        }
                        
                    }
                }
                    
                $current_offset = $current_offset + 1;

            }while($current_offset < 1 && $responsesBody != null);
            
            return true;

            // var_dump( $ZMOOZ_API,$ZMOOZ_USED_DOMAIN,$type,$response);

            // var_dump('Res',$responsesBody);

        }

        //add_action( 'init', 'create_zmooz_stories');

        public function zmooz_wp_plugin_delete_story($ID){
            wp_delete_post( $ID, true);
        }

        public function zmooz_wp_plugin_update_story($ID){

            global $ZMOOZ_API;
            global $ZMOOZ_USED_DOMAIN;

            $result = null;
            $storyrequest = wp_remote_get( $ZMOOZ_API.'/wp-plugin/get-story-by-post-id/'.$ID.'?domain='.$ZMOOZ_USED_DOMAIN );
            $storyResponse = json_decode(wp_remote_retrieve_body( $storyrequest ),true );
            $story_url = isset($storyResponse['link']) ? $storyResponse['link'] : null;
            $post = array();
            if($story_url !== null){
                $result =$this->zmooz_update_single_story($story_url,1,$ID,null);
                $post = array(
                    "postId" => $result['data']['post_id'],
                    "storyTitle" => $result['data']['post_title'],
                    "storyId" => isset($storyResponse['storyId']) ? $storyResponse['storyId'] : null ,
                    "storyUrl" => $result['data']['post_slug']
                );
                $result = array(
                    "data" => "Updated sucessfully : ".$story_url,
                    "status" => 200
                );
            }else{
                $result = array(
                    "data" => "No found story : ".$story_url,
                    "status" => 500
                );
            }
            if(isset($post['postId'])){

                wp_remote_post($ZMOOZ_API.'/wp-plugin/patch/posts?domain='.$ZMOOZ_USED_DOMAIN,
                    array(
                        'method'      => 'POST',
                        'timeout'     => 45,
                        'redirection' => 5,
                        'httpversion' => '1.0',
                        'blocking'    => false,
                        'headers'     =>  array(
                            'content-type' => 'application/json'
                        ),
                        'body' => json_encode( 
                            array(
                                "posts" => array($post)
                            )
                        )
                    )
                );
            }
            return $result;

            
        }
    }
endif;
