<head>

    <style>
        #zmooz-plugin-admin{
            margin: 1rem;
        }

        #zmooz-plugin-admin .admin_form{
            display : flex;
            width : 100%;
            justify-content: space-between;
        }

        #zmooz-admin-pagination{
            display : flex;
            justify-content : space-between;
            align-items : center;
        }
        #zmooz-admin-pagination .page-numbers{
            
            padding: .5rem;
        }

        #zmooz-plugin-admin .btn_zone{
            display : flex;
            flex-direction : column;
        }

        #zmooz-wp-plugin-notif-zone{   
            display:none;
            border-left : 4px solid green;
            padding : 1rem;
            width : 70%;
            box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;
            justify-content : space-between;
            align-items : center;
            background-color : #fff;
            margin : 1rem auto;
        }
        #zmooz-wp-plugin-notif-text{
            max-width : calc( 100% - 40px);
        }
        #zmooz-wp-plugin-notif-loader {
            border: 5px solid #f3f3f3;
            border-radius: 50%;
            border-top: 5px solid #3498db;
            width: 30px;
            height: 30px;
            -webkit-animation: spin 2s linear infinite; /* Safari */
            animation: spin 2s linear infinite;
        }
        .zmooz_wp_plugin_nb_stories{
            background-color : #fff;
            border-radius : 5px;
            padding : 1rem;
            max-width:  max-content;
            height : max-content;
        }
        .zmooz_flex_center{
            display : flex;
            justify-content : center;
            align-items : center;
            padding: 6px !important;
        }
        .zmooz_wp_plugin_nb_settings{
            width : max-content;
            border-left : 1px solid #ccc;
            margin-left : 2rem;
            padding-left : 2rem;
        }

        /* Safari */
        @-webkit-keyframes spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

</head>
<div id="zmooz-plugin-admin">
    <!--
        <div class="btn_zone">
            <div class="btn_zone_flex">
                <form method="POST" id="update_all">
                    <input class="wp-core-ui button-primary"  type="submit" name="update_all" value="Update All" />
                </form>
            <div>
            <br/>
            <br/>
        </div>
    -->
    <h1>Zmooz stories admin</h1>
    <?php
    if(!is_null($storiesList) && isset( $storiesList['count']) &&  $storiesList['count'] > 0){
    ?>
        <div style="margin: 1rem 0; width:calc(100% - 2rem );height:70px; border-radius:5px; padding:.5rem; background-color:#fff;display: flex;justify-content: space-between;">
            <img src="<?php echo esc_url( (plugin_dir_url(dirname( __FILE__ ) ).'images/logo.png')) ?>" style="max-width: calc(calc(100% - 300px) - 4rem ); height:auto;apsect-ratio:16/9;margin:1rem;"/>
            <a href="https://zmooz.com/studio/auth/login" target="_blank" style=" max-width: 400px; width:100%; white-space: nowrap;margin-top : .5rem;margin-bottom : .5rem;display: flex;justify-content: center;align-items: center;background-color: #ff471e;padding : calc(14 * 100vw / 1920);border-radius: 5px;color: #fff;text-decoration: none;font-weight: bold;font-size: calc(16 * 100vw / 1700);">Login to Zmooz</a>
        </div>
    <?php
    }else{
    ?>
        <div style="margin: 1rem 0; width:calc(100% - 2rem );height:300px; border-radius:5px; padding:.5rem; background-color:#fff;display: flex;justify-content: space-between;">
            <img src="<?php echo esc_url( (plugin_dir_url(dirname( __FILE__ ) ).'images/banner.png')) ?>" style="max-width: calc(calc(100% - 300px) - 4rem ); height:auto;apsect-ratio:16/9;margin:1rem;"/>
            <div style="width:calc(100% - 2rem );min-width: 20margin-bottom : .5rem;display: flex;flex-direction: column;justify-content: center;align-items: center;padding: 1rem">
                <a href="https://zmooz.com/studio/auth/login" target="_blank" style=" max-width: 400px; width:100%; white-space: nowrap;margin-top : .5rem;margin-bottom : .5rem;display: flex;justify-content: center;align-items: center;background-color: #ff471e;padding : calc(14 * 100vw / 1920);border-radius: 5px;color: #fff;text-decoration: none;font-weight: bold;font-size: calc(16 * 100vw / 1700);">Already Zmooz member, login here .</a>
                <a href="https://zmooz.com/studio/auth/signup" target="_blank" style=" max-width: 400px; width:100%; white-space: nowrap;margin-top : .5rem;margin-bottom : .5rem;display: flex;justify-content: center;align-items: center;background-color: #ff471e;padding : calc(14 * 100vw / 1920);border-radius: 5px;color: #fff;text-decoration: none;font-weight: bold;font-size: calc(16 * 100vw / 1700);">New on Zmooz ? Create your first story now !</a>                
            </div>    

        </div>
    <?php
     }
    ?>
    <div style="display:flex;">
        <div class="zmooz_wp_plugin_nb_stories">
            <h3>Total stories imported</h3>
            <div><?php esc_html_e(!is_null($storiesList) &&  isset($storiesList['count']) ? $storiesList['count']:0)?></div>
        </div>
    
        <div class="zmooz_wp_plugin_nb_settings">
            <h1>Quick Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'zmooz_custom_plugin_settings_group' ); ?>
                <?php do_settings_sections( 'zmooz_custom_plugin_settings' ); ?>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>

    <br></br>

    <div id="zmooz-admin-pagination">
        <div>

        </div>
        <div>
        <?php 
            if(!is_null($storiesList) && $storiesList['list']){

                $allowed_html = array(
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
                $pagination = paginate_links( array(
                    'base'         => add_query_arg(array( 'page' => 'zmooz_stories_dashboard', 'paged' => '%#%' ) ),
                    'total'        => ceil($storiesList['count']/10),
                    'current'      =>$page,
                    'format'       => '',
                    'show_all'     => false,
                    'type'         => 'plain',
                    'end_size'     => 2,
                    'mid_size'     => 1,
                    'prev_next'    => true,
                    'prev_text'    => sprintf( '<i>%1$s</i>', __( 'Prev', 'text-domain' ) ),
                    'next_text'    => sprintf( '<i>%1$s</i>', __( 'Next', 'text-domain' ) ),
                    'add_args'     => false,
                    'add_fragment' => '',
                ));
                
                echo wp_kses($pagination,$allowed_html);

            }
        ?>
        </div>
    </div>
    <br></br>

    <table class="wp-list-table widefat fixed striped table-view-list posts">
        <thead>
            <tr>
                <th  id="cb" style="padding:6px;margin:0;" class="manage-column column-cb check-column ">
                    <input style="margin:8px" id="cb-select-all-1" type="checkbox">
                </th>
                <th scope="col" id="title" class="manage-column column-title column-primary sortable desc">
                    <a href="#">
                        <span>Title</span><span class="sorting-indicator"></span>
                    </a>
                </th>
                <th scope="col" id="date" class="manage-column column-date sortable asc">
                    <a href="#">
                        <span>CreatedAt</span><span class="sorting-indicator"></span>
                    </a>

                </th>
                <th scope="col" id="date2" class="manage-column column-date sortable asc">
                    <a href="#">
                        <span>UpdatedAt</span><span class="sorting-indicator"></span>
                    </a>
                </th>
                
            </tr>
        </thead>

        <tbody id="the-list">
           
            <?php
           
            if (!is_null($storiesList) and $storiesList['list'] ) {
                foreach ( $storiesList['list'] as $story ) :

                    $story_id = $story['id'];
                    $title =  $story['title'];
                    $link = $story['storyUrl'] ; 
                    $createdAt = $story['createdAt'];
                    $updatedAt = $story['updatedAt'];

                ?>
                <tr id="post-36" class="iedit author-self level-0 post-36 type-stories status-publish has-post-thumbnail hentry">
                    
                    <td scope="row" class="check-column zmooz_flex_center">			
                        <input  style="margin:8px" id="cb-select-36" type="checkbox" name="post[]" value="36">
                    </td>
                
                    <td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
                        <div class="locked-info">
                            <span class="locked-avatar"></span> 
                            <span class="locked-text"></span>
                        </div>
                        <strong>
                            <a class="row-title" href="<?php esc_html_e(get_site_url())?><?php esc_html_e("/web-stories/".$link) ?>" ><?php esc_html_e($title) ?></a>
                        </strong>
                    </td>

                    <td class="date column-date" data-colname="Title">
                        <strong>
                            <?php esc_html_e(date_i18n( get_option( 'date_format' ), strtotime( $createdAt ) )) ?>
                        </strong>
                    </td>

                    <td class="date column-date" data-colname="Title">
                        <strong>
                            <?php esc_html_e(date_i18n( get_option( 'date_format' ), strtotime( $updatedAt ) ))  ?>
                        </strong>
                    </td>
                    
                </tr>
                <?php
                endforeach; 
            }else{
                ?>
                <tr>
                    <td colspan="2">No stories imported</td>
                </tr>
                <?php
            } ?>
               
            </tbody>



    </table>
    <br/>
    <div id="zmooz-admin-pagination">
        <div>

        </div>
        <div>
        <?php 
            if(!is_null($storiesList) && $storiesList['list']){

                $allowed_html = array(
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
                $pagination = paginate_links( array(
                    'base'         => add_query_arg(array( 'page' => 'zmooz_stories_dashboard', 'paged' => '%#%' ) ),
                    'total'        => ceil($storiesList['count']/10),
                    'current'      =>$page,
                    'format'       => '',
                    'show_all'     => false,
                    'type'         => 'plain',
                    'end_size'     => 2,
                    'mid_size'     => 1,
                    'prev_next'    => true,
                    'prev_text'    => sprintf( '<i>%1$s</i>', __( 'Prev', 'text-domain' ) ),
                    'next_text'    => sprintf( '<i>%1$s</i>', __( 'Next', 'text-domain' ) ),
                    'add_args'     => false,
                    'add_fragment' => '',
                ));
                
                echo wp_kses($pagination,$allowed_html);

            }
        ?>
        </div>
    </div>
</div>
