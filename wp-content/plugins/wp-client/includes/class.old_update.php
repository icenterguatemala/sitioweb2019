<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( !class_exists( "WPC_Old_Update" ) ) {

    class WPC_Old_Update {

        /**
        * PHP 5 constructor
        **/
        function __construct() {
        }


        /*
        * Updating to new version
        */
        function updating( $ver ) {
            global $wpdb;

            if ( version_compare( $ver, '2.6.7', '<' ) ) {

                //delete deprecated options
                delete_option( 'parent_page_id' );
                delete_option( 'parent_title' );

                //tables name
                $tables = array(
                    'clients_page'          => 'wpc_clients_page',
                    'login_redirects'       => 'wpc_login_redirects',
                    'pcc_comments'          => 'wpc_comments',
                    'pcc_files'             => 'wpc_files',
                    'pcc_groups'            => 'wpc_groups',
                    'pcc_group_clients'     => 'wpc_group_clients',
                );

                //rename old tables
                foreach( $tables as $key => $value ) {
                    if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}{$key}'" ) == "{$wpdb->prefix}{$key}"
                    && $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}{$value}'" ) == "{$wpdb->prefix}{$value}" ) {

                        $wpdb->query( "DROP TABLE {$wpdb->prefix}{$value}" );
                        $wpdb->query( "RENAME TABLE {$wpdb->prefix}{$key} TO {$wpdb->prefix}{$value}" );

                    } elseif ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}{$key}'" ) == "{$wpdb->prefix}{$key}" ) {
                        $wpdb->query( "RENAME TABLE {$wpdb->prefix}{$key} TO {$wpdb->prefix}{$value}" );
                    }

                }


                if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wpc_files'" ) == "{$wpdb->prefix}wpc_files" ) {
                //adding extra column to table
                if ( 1 != $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_files clients_id" ) )
                    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_files ADD clients_id text NULL" );

                if ( 1 != $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_files groups_id" ) )
                    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_files ADD groups_id text NULL" );

                if ( 1 != $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_files category" ) )
                    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_files ADD category text NULL" );

                if ( 1 != $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_files `order`" ) )
                    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_files ADD `order` int NULL" );


                //set default category for all exist files
                $wpdb->query( "UPDATE {$wpdb->prefix}wpc_files SET category='General'" );

                }

                //options
                $options = array(
                    'pcc_show_link'          => 'wpc_show_link',
                    'pcc_create_client'      => 'wpc_create_client',
                    'pcc_link_text'          => 'wpc_link_text',
                    'pcc_custom_menu'        => 'wpc_custom_menu',
                    'pcc_notify_message'     => 'wpc_notify_message',
                    'pcc_login_alerts'       => 'wpc_login_alerts',
                );

                //rename old options
                foreach( $options as $key => $value ) {
                    $opt = get_option( $key );
                    update_option( $value , $opt );
                    delete_option( $key );
                }


                //Update post content - shortcodes name
                $args = array(
                    'numberposts'   => '',
                    'post_type'     => 'hubpage',
                );
                $hubpages = get_posts( $args );
                if ( is_array( $hubpages ) && 0 < count( $hubpages ) )
                    foreach( $hubpages as $hubpage ) {
                        $hubpage->post_content = str_replace( 'pcc_com', 'wpc_com', $hubpage->post_content );
                        wp_insert_post( $hubpage );
                    }

                $args = array(
                    'numberposts' => '',
                    'post_type' => 'clientspage',
                );
                $clients_pages = get_posts( $args );
                if ( is_array( $clients_pages ) && 0 < count( $clients_pages ) )
                    foreach( $clients_pages as $client_page ) {
                        $client_page->post_content = str_replace( 'pcc_com', 'wpc_com', $client_page->post_content );

                        //clientpage id for get list of users
                        $client_page_ids[] = $client_page->ID;

                        wp_insert_post( $client_page );
                    }


                //Update templates content - shortcodes name
                $template_content = get_option( 'client_template' );
                $template_content = str_replace( 'pcc_com', 'wpc_com', $template_content );
                update_option( 'client_template', $template_content );

                $template_content = get_option( 'hub_template' );
                $template_content = str_replace( 'pcc_com', 'wpc_com', $template_content );
                update_option( 'hub_template', $template_content );



                //set new role for exist clients
                if ( isset( $client_page_ids ) && 0 < count( $client_page_ids ) ) {
                    $users_id = array();
                    //get all users id
                    foreach( $client_page_ids as $client_page_id ) {
                        $ids = get_post_meta( $client_page_id, 'user_ids', true );
                        if ( is_array( $ids ) && 0 < count( $ids ) ) {
                            foreach( $ids as $id )
                                $users_id[] = $id;
                        }
                    }

                    $users_id = array_unique( $users_id );

                    //set role
                    if ( is_array( $users_id ) && 0 < count( $users_id ) ) {
                        foreach( $users_id as $user_id )
                            update_user_meta( $user_id, $wpdb->prefix . 'capabilities', array( 'wpc_client' => '1' ) );
                    }
                }


            }



            if ( version_compare( $ver, '2.6.8.1', '<' ) ) {
                if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wpc_files'" ) == "{$wpdb->prefix}wpc_files" ) {
                    //set default category for all exist files with empty category - for fix bug with add cat
                    $wpdb->query( "UPDATE {$wpdb->prefix}wpc_files SET category='General' WHERE category='' " );
                }

            }


            if ( version_compare( $ver, '2.7', '<' ) ) {

                //update clients role
                $args = array(
                    'role'      => 'pcc_client',
                    'fields'    => 'ID',
                );

                $old_client_ids = get_users( $args );
                foreach( $old_client_ids as $old_client_id ) {
                    update_user_meta( $old_client_id, $wpdb->prefix . 'capabilities', array( 'wpc_client' => '1' ) );
                }


                //tables name
                $tables = array(
                    'wpc_clients_page'      => 'wpc_client_clients_page',
                    'wpc_login_redirects'   => 'wpc_client_login_redirects',
                    'wpc_comments'          => 'wpc_client_comments',
                    'wpc_files'             => 'wpc_client_files',
                    'wpc_groups'            => 'wpc_client_groups',
                    'wpc_group_clients'     => 'wpc_client_group_clients',
                );

                //rename old tables
                foreach( $tables as $key => $value ) {
                    if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}{$key}'" ) == "{$wpdb->prefix}{$key}"
                    && $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}{$value}'" ) == "{$wpdb->prefix}{$value}" ) {

                        $wpdb->query( "DROP TABLE {$wpdb->prefix}{$value}" );
                        $wpdb->query( "RENAME TABLE {$wpdb->prefix}{$key} TO {$wpdb->prefix}{$value}" );

                    } elseif ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}{$key}'" ) == "{$wpdb->prefix}{$key}" ) {
                        $wpdb->query( "RENAME TABLE {$wpdb->prefix}{$key} TO {$wpdb->prefix}{$value}" );
                    }

                }


                //delete deprecated options
                delete_option( 'clients_page' );


                //set default category for all files without category
                $categories = null;
                if ( 1 == $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_client_files category" ) ) {
                    $wpdb->query( "UPDATE {$wpdb->prefix}wpc_client_files SET category='General' WHERE category = '' " );


                    //move categories to new table
                    $categories = $wpdb->get_col( "SELECT category FROM {$wpdb->prefix}wpc_client_files WHERE 1=1 AND category != '' GROUP BY category" );
                }

                if ( $categories ) {
                    $i = 1;
                    foreach( $categories as $category ) {
                        $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_file_categories SET cat_name = '%s', cat_order = %d", $category, $i ) );
                        $i++;
                    }
                } else {
                    $isset_general = $wpdb->get_var( "SELECT cat_id FROM {$wpdb->prefix}wpc_client_file_categories WHERE cat_name='General'" );
                    if ( !$isset_general ) {
                        $wpdb->query( "INSERT INTO {$wpdb->prefix}wpc_client_file_categories SET cat_name = 'General', cat_order = 1" );
                    }
                }


                //add new field for cat ID
                if ( 1 != $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_client_files cat_id" ) )
                    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_client_files ADD cat_id int NULL" );


                //set for cat_id for files
                if ( 1 == $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_client_files category" ) ) {
                    $files = $wpdb->get_results( "SELECT id, category FROM {$wpdb->prefix}wpc_client_files", "ARRAY_A" );
                    if ( is_array( $files ) && 0 < count( $files ) ) {
                        foreach( $files as $file ) {
                            $cat_id = $wpdb->get_var( $wpdb->prepare( "SELECT cat_id FROM {$wpdb->prefix}wpc_client_file_categories WHERE cat_name = '%s' ", $file['category'] ) );
                            if ( 0 < $cat_id ) {
                                $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_files SET cat_id = '%d' WHERE id = '%d' ", $cat_id, $file['id'] ) );
                            }
                        }
                    }
                }

                //delete old colomns for category
                if ( 1 == $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_client_files category" ) )
                    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_client_files DROP COLUMN category " );

                if ( 1 == $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_client_files `order`" ) )
                    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_client_files DROP COLUMN `order` " );


                /*
                * New Message system
                */
                //add new fields to wpc_client_comments table
                if ( 1 != $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_client_comments sent_from" ) )
                    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_client_comments ADD sent_from int NULL" );
                if ( 1 != $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_client_comments sent_to" ) )
                    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_client_comments ADD sent_to int NULL" );
                if ( 1 != $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_client_comments new_flag" ) )
                    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_client_comments ADD new_flag int NULL" );

                //set value for new fields
                $wpdb->query(  $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_comments SET sent_from=user_id, sent_to=%d WHERE page_id > 0 ", 0 ) );
                $wpdb->query(  $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_comments SET sent_from=%d, sent_to=user_id WHERE page_id < 1 ", 0 ) );

                //delete deprecated options
                delete_option( 'client_com' );
                delete_option( 'hub_com' );

                //set default for custom_login_options
                $custom_login_options = get_option( 'custom_login_options' );
                if ( false === $custom_login_options ) {
                    $args = array(
                        'cl_enable'             => 'yes',
                        'cl_background'         => plugins_url() .'/wp-client/images/logo.png',
                        'cl_backgroundColor'    => 'ffffff',
                        'cl_color'              => '000033',
                        'cl_linkColor'          => '00A5E2',
                        'cl_login_url'          => '',
                        'cl_hide_admin'         => 'no'
                    );

                    update_option( 'custom_login_options', $args );
                } else {
                    if ( isset( $custom_login_options['cl_background'] ) ) {
                        $custom_login_options['cl_background'] = str_replace( 'WP-Clients', 'wp-client', $custom_login_options['cl_background'] );
                        update_option( 'custom_login_options', $custom_login_options );
                    }

                }




                //Update post content - change shortcodes name
                $short_codes = array(
                    'wpclient'      => 'wpc_client',
                    'private'       => 'wpc_client_private',
                    'hubpage'       => 'wpc_client_hubpage',
                    'theme'         => 'wpc_client_theme',
                    'loginf'        => 'wpc_client_loginf',
                    'logoutb'       => 'wpc_client_logoutb',
                    'filesla'       => 'wpc_client_filesla',
                    'uploadf'       => 'wpc_client_uploadf',
                    'fileslu'       => 'wpc_client_fileslu',
                    'pagel'         => 'wpc_client_pagel',
                    'wpc_com'       => 'wpc_client_com',
                    'graphic'       => 'wpc_client_graphic',
                );

                 //Update post content - shortcodes name
                $args = array(
                    'numberposts'   => -1,
                    'post_type'     => 'hubpage',
                );
                $hub_pages = get_posts( $args );
                if ( is_array( $hub_pages ) && 0 < count( $hub_pages ) ) {
                    foreach( $hub_pages as $hub_page ) {
                        foreach( $short_codes as $key => $value ) {
                            $hub_page->post_content = str_replace( '[' . $key . ']', '[' . $value . ']', $hub_page->post_content );
                            $hub_page->post_content = str_replace( '[/' . $key . ']', '[/' . $value . ']', $hub_page->post_content );
                        }
                        wp_update_post( $hub_page );
                    }
                }


                $args = array(
                    'numberposts' => -1,
                    'post_type' => 'clientspage',
                );
                $clint_pages = get_posts( $args );
                if ( is_array( $clint_pages ) && 0 < count( $clint_pages ) ) {
                    foreach( $clint_pages as $clint_page ) {
                        foreach( $short_codes as $key => $value ) {
                            $clint_page->post_content = str_replace( '[' . $key . ']', '[' . $value . ']', $clint_page->post_content );
                            $clint_page->post_content = str_replace( '[/' . $key . ']', '[/' . $value . ']', $clint_page->post_content );
                        }
                        wp_update_post( $clint_page );
                    }
                }



                //Update templates content - shortcodes name
                $client_template = get_option( 'client_template' );
                $hub_template = get_option( 'hub_template' );
                foreach( $short_codes as $key => $value ) {
                    $client_template    = str_replace( '[' .$key . ']',  '[' . $value . ']', $client_template );
                    $client_template    = str_replace( '[/' .$key . ']',  '[/' . $value . ']', $client_template );
                    $hub_template       = str_replace( '[' .$key . ']',  '[' . $value . ']', $hub_template );
                    $hub_template       = str_replace( '[/' .$key . ']',  '[/' . $value . ']', $hub_template );
                }
                update_option( 'client_template', $client_template );
                update_option( 'hub_template', $hub_template );



            }

            if ( version_compare( $ver, '2.7.8', '<' ) ) {

                /*
                * Update email templates
                */
                $wpc_templates = get_option( 'wpc_templates' );

                //email when Client created
                if ( !isset( $wpc_templates['emails']['new_client_password'] ) ) {
                    if ( '' != get_option( 'new_subject' ) ) {
                        $wpc_templates['emails']['new_client_password']['subject'] = get_option( 'new_subject' );
                    } else {
                        $wpc_templates['emails']['new_client_password']['subject'] = 'Your Private and Unique Client Portal has been created';
                    }

                    if ( '' != get_option( 'new_email_client_template' ) ) {
                        $wpc_templates['emails']['new_client_password']['body'] = get_option( 'new_email_client_template' );
                    } else {
                        $wpc_templates['emails']['new_client_password']['body'] = '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
        <p>Your private and secure Client Portal has been created. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
        <p>Thanks, and please contact us if you experience any difficulties,</p>
        <p>{business_name}</p>';
                    }
                }

                //email when Client updated
                if ( !isset( $wpc_templates['emails']['client_updated'] ) ) {

                    $wpc_templates['emails']['client_updated']['subject'] = 'Your Client Password has been updated';

                    if ( '' != get_option( 'new_email_client_template' ) ) {
                        $wpc_templates['emails']['client_updated']['body'] = get_option( 'new_email_client_template' );
                    } else {
                        $wpc_templates['emails']['client_updated']['body'] = '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
        <p>Your password has been updated. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
        <p>Thanks, and please contact us if you experience any difficulties,</p>
        <p>{business_name}</p>';
                    }
                }


                //email when Portal Page is updated
                if ( !isset( $wpc_templates['emails']['client_page_updated'] ) ) {
                    if ( '' != get_option( 'update_subject' ) ) {
                        $wpc_templates['emails']['client_page_updated']['subject'] = get_option( 'update_subject' );
                    } else {
                        $wpc_templates['emails']['client_page_updated']['subject'] = 'Your ' . WPC()->custom_titles['portal_page']['s'] . ' has been updated';
                    }

                    if ( '' != get_option( 'update_client_page_email_template' ) ) {
                        $wpc_templates['emails']['client_page_updated']['body'] = get_option( 'update_client_page_email_template' );
                    } else {
                        $wpc_templates['emails']['client_page_updated']['body'] = '<p>Hello {contact_name},</p>
                    <p>Your ' . WPC()->custom_titles['portal_page']['s'] . ', {page_title} has been updated | <a href="{page_id}">Click HERE to visit</a></p>
                    <p>Thanks, and please contact us if you experience any difficulties,</p>
                    <p>{business_name}</p>';
                    }
                }


                //email when Admin/Manager uploaded file
                if ( !isset( $wpc_templates['emails']['new_file_for_client_staff'] ) ) {
                    $wpc_templates['emails']['new_file_for_client_staff']['subject']  = 'New file at {site_title}';
                    $wpc_templates['emails']['new_file_for_client_staff']['body']     = '<p>You have been given access to a file at {site_title}</p>
                    <p>Click <a href="{login_url}">HERE</a> to access the file.</p>';
                }

                //email when Client registered
                if ( !isset( $wpc_templates['emails']['new_client_registered'] ) ) {
                    $wpc_templates['emails']['new_client_registered']['subject']  = 'A new client has registered on your site | {site_title}';
                    $wpc_templates['emails']['new_client_registered']['body']     = '<p>To approve this new client, you will need to login and navigate to > Clients > <strong><a href="{approve_url}">Approve Clients</a></strong></p>';
                }

                //email to Admin and Managers when Client uploaded the file
                if ( !isset( $wpc_templates['emails']['client_uploaded_file'] ) ) {
                    $wpc_templates['emails']['client_uploaded_file']['subject']  = 'The user {user_name} uploaded a file at {site_title}';
                    $wpc_templates['emails']['client_uploaded_file']['body']     = '<p>The user {user_name} uploaded a file. To view/download the file, click <a href="{admin_file_url}">HERE</a>"</p>';
                }

                //email when Staff created
                if ( !isset( $wpc_templates['emails']['staff_created'] ) ) {
                    $wpc_templates['emails']['staff_created']['subject']  = 'Your Staff account has been created';
                    $wpc_templates['emails']['staff_created']['body']     = '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
        <p>You have been granted access to a private and secure Client Portal. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
        <p>Thanks, and please contact us if you experience any difficulties,</p>
        <p>{business_name}</p>';
                }

                //email when Client registered Staff
                if ( !isset( $wpc_templates['emails']['staff_registered'] ) ) {
                    $wpc_templates['emails']['staff_registered']['subject']  = 'Your Staff account has been registered';
                    $wpc_templates['emails']['staff_registered']['body']     = '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
        <p>You have been granted access to our private and secure Client Portal. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
        <p>Thanks, and please contact us if you experience any difficulties,</p>
        <p>{business_name}</p>';
                }

                //email when Manager created
                if ( !isset( $wpc_templates['emails']['manager_created'] ) ) {
                    $wpc_templates['emails']['manager_created']['subject']  = 'Your Manager account has been created';
                    $wpc_templates['emails']['manager_created']['body']     = '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
        <p>Your manager account has been created. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
        <p>Thanks, and please contact us if you experience any difficulties,</p>
        <p>{business_name}</p>';
                }

                //email when Admin send message to Client
                if ( !isset( $wpc_templates['emails']['notify_client_about_message'] ) ) {
                    $wpc_templates['emails']['notify_client_about_message']['subject']  = 'A user: {user_name} from {site_title} has sent you a private message';
                    $wpc_templates['emails']['notify_client_about_message']['body']     = '<p>A user: {user_name} has sent you a private message. To see the message login <a href="{login_url}">here</a>.';
                }

                //email when Client send message to Admin/Manager
                if ( !isset( $wpc_templates['emails']['notify_admin_about_message'] ) ) {
                    $wpc_templates['emails']['notify_admin_about_message']['subject']  = "You've received a new private message from {user_name}, sent from {site_title}";
                    $wpc_templates['emails']['notify_admin_about_message']['body']     = '<p>{user_name} says,
                    <br/>
                    {message}
                    <br/>
                    <br/>
                    To view the entire thread of messages and send a reply, click <a href="{admin_url}">HERE</a>';
                }

                //delete old options
                delete_option( 'new_subject' );
                delete_option( 'new_email_client_template' );
                delete_option( 'update_subject' );
                delete_option( 'update_client_page_email_template' );


                update_option( 'wpc_templates' , $wpc_templates );

            }


            if ( version_compare( $ver, '2.7.9.6', '<' ) ) {

                /*
                * Remove some plugin pages which not needed
                */
                $args = array(
                    'hierarchical'  => 0,
                    'meta_key'      => 'wpc_client_page',
                    'post_type'     => 'page',
                    'post_status'   => 'publish,trash,pending,draft,auto-draft,future,private,inherit',
                );
                $wpc_client_pages = get_pages( $args );
                if ( is_array( $wpc_client_pages ) && 0 < count( $wpc_client_pages ) ) {
                    $for_delete = array( 'client_registration', 'registration_successful', 'edit_clientpage', 'staff_registration', 'staff_directory' );
                    foreach( $wpc_client_pages as $wpc_client_page )
                        if ( in_array( get_post_meta( $wpc_client_page->ID, 'wpc_client_page', true ), $for_delete ) ) {
                            wp_delete_post( $wpc_client_page->ID, true );
                        }
                }


            }


            if ( version_compare( $ver, '2.8.0', '<' ) ) {

                //replace E/p> to E</p>
                $wpc_templates = get_option( 'wpc_templates' );

                $emails = array();
                foreach ( $wpc_templates['emails'] as $k => $v ) {
                    $emails[$k] = str_replace( 'E/p>', 'E</p>', $v );
                }
                $wpc_templates['emails'] = $emails;

                update_option( 'wpc_templates', $wpc_templates );

            }


            if ( version_compare( $ver, '2.8.1.3', '<' ) ) {

                // field for last download
                if ( 1 != $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_client_files last_download" ) )
                    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_client_files ADD last_download text NULL" );

                // field for title
                if ( 1 != $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_client_files title" ) )
                    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_client_files ADD title varchar(255) NULL" );

                // field for description
                if ( 1 != $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_client_files description" ) )
                    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_client_files ADD description text NULL" );

                //update title
                if ( 1 == $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_client_files title" ) )
                    $wpdb->query( "UPDATE {$wpdb->prefix}wpc_client_files SET title = name " );


                //update template for fix bug with php code
                $templates_data = get_option( 'wpc_templates' );
                if ( isset( $templates_data['wpc_shortcodes']['wpc_client_com'] ) && '' != $templates_data['wpc_shortcodes']['wpc_client_com'] ) {
                    $templates_data['wpc_shortcodes']['wpc_client_com'] = str_replace(
                        "<?php _e( 'Show more messages', WPC_CLIENT_TEXT_DOMAIN ) ?>",
                        '{$more_messages}',
                        $templates_data['wpc_shortcodes']['wpc_client_com']
                    );

                    update_option( 'wpc_templates', $templates_data );

                }
            }

            if ( version_compare( $ver, '2.8.3', '<' ) ) {

                /*
                * Update email templates
                */
                $wpc_templates = get_option( 'wpc_templates' );

                //email when Client created
                if ( !isset( $wpc_templates['emails']['account_is_approved'] ) ) {

                    $wpc_templates['emails']['account_is_approved']['subject'] = 'Your account is approved';


                    $wpc_templates['emails']['account_is_approved']['body'] = '<p>Hello {contact_name},<br /> <br /> Your account is approved.</p>
        <p>You can login by clicking <strong><a href="{login_url}">HERE</a></strong></p>
        <p>Thanks, and please contact us if you experience any difficulties,</p>
        <p>{business_name}</p>';

                    update_option( 'wpc_templates', $wpc_templates );
                }

            }


            if ( version_compare( $ver, '2.9.0', '<' ) ) {

                //add new field in circle table
                if ( 1 != $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_client_groups `auto_add_clients`" ) )
                $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_client_groups ADD `auto_add_clients` varchar(1) NULL" );

                /*
                * set HUBpage ID in usermeta value
                */
                $args = array(
                    'role'      => 'wpc_client',
                    'fields'    => array( 'ID' ),
                );

                $clients = get_users( $args );
                if ( is_array( $clients) && 0 < count( $clients ) ) {
                    foreach( $clients as $client ) {
                        $clients_array[] = $client->ID;
                        //get rule for client
                        $rul_urls = $wpdb->get_row( $wpdb->prepare( "SELECT rul_url FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_value = %s", get_userdata( $client->ID )->get( 'user_login' ) ), "ARRAY_A" );
                        if ( is_array( $rul_urls ) && 0 < count( $rul_urls ) ) {
                            foreach ( $rul_urls as $rul_url ) {
                                preg_match( '/.*?hubpage=(.*?)(&.*?)?$/is', $rul_url, $matches );
                                if ( '' != $matches[1] ) {
                                    //get hub id
                                    $sql = "SELECT ID FROM {$wpdb->prefix}posts WHERE 1=1 AND post_name = '" . $matches[1] . "' AND post_type = 'hubpage' ORDER BY post_date DESC ";
                                    $postid = $wpdb->get_var( $sql );
                                    if ( 0 < $postid )
                                        update_user_meta( $client->ID, 'wpc_cl_hubpage_id', $postid );

                                    break;
                                }
                            }
                        }
                    }
                }

            }


            if ( version_compare( $ver, '2.9.7', '<' ) ) {

                $wpc_settings = get_option( 'wpc_settings' );

                if ( isset( $wpc_settings['slugs'] ) && !isset( $wpc_settings['pages'] ) ) {

                    $parent_page_id = 0;
                    if ( isset( $wpc_settings['slugs']['base'] ) && '' != $wpc_settings['slugs']['base'] ) {
                        $current_user = wp_get_current_user();
                        //Construct args for the new page
                        $args = array(
                            'post_title'     => ucwords( str_replace( '-', ' ', $wpc_settings['slugs']['base'] ) ),
                            'post_status'    => 'publish',
                            'post_author'    => $current_user->ID,
                            'post_content'   => '[wpc_redirect_on_login_hub]',
                            'post_type'      => 'page',
                            'ping_status'    => 'closed',
                            'comment_status' => 'closed'
                        );
                        $parent_page_id = wp_insert_post( $args );

                    }


                    $wpc_pre_pages = WPC()->install()->pre_set_pages();

                    $pages = array();

                    foreach( $wpc_pre_pages as $wpc_page ) {

                        $page_title = $wpc_page['name'];
                        if ( '' != $wpc_page['old_id'] && isset( $wpc_settings['slugs'][$wpc_page['old_id']] ) ) {
                            $page_title = ucwords( str_replace( '-', ' ', $wpc_settings['slugs'][$wpc_page['old_id']] ) );
                        }


                        $current_user = wp_get_current_user();
                        //Construct args for the new page
                        $args = array(
                            'post_title'        => $page_title,
                            'post_status'       => 'publish',
                            'post_author'       => $current_user->ID,
                            'post_content'      => $wpc_page['content'],
                            'post_type'         => 'page',
                            'ping_status'       => 'closed',
                            'comment_status'    => 'closed',
                            'post_parent'       => $parent_page_id,
                        );
                        $page_id = wp_insert_post( $args );

                        $pages[$wpc_page['id']] = $page_id;

                    }

                    WPC()->settings()->update( $pages, 'pages' );

                    //flush rewrite rules due to slugs
                    flush_rewrite_rules( false );

                }
            }


            if ( version_compare( $ver, '3.0.4', '<' ) ) {
                /*
                * move business name from first_name to wpc_cl_business_name meta
                */
                $args = array(
                    'role'      => 'wpc_client',
                    'fields'    => array( 'ID' ),
                );
                $clients = get_users( $args );
                if ( is_array( $clients) && 0 < count( $clients ) ) {
                    foreach( $clients as $client ) {
                        $business_name = '';
                        $first_name = get_user_meta( $client->ID, 'first_name', true );
                        if ( $first_name ) {
                            $business_name = $first_name;
                        }
                        update_user_meta( $client->ID, 'wpc_cl_business_name', $business_name );
                    }
                }
            }


            if ( version_compare( $ver, '3.1.6', '<' ) ) {

                if ( 1 != $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_client_file_categories `clients_id`" ) )
                    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_client_file_categories ADD `clients_id` text NULL" );

                if ( 1 != $wpdb->query( "DESCRIBE {$wpdb->prefix}wpc_client_file_categories `groups_id`" ) )
                    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_client_file_categories ADD `groups_id` text NULL" );

            }


            if ( version_compare( $ver, '3.1.7', '<' ) ) {

                $wpc_templates = get_option( 'wpc_templates' );

                //email when Client reset it`s password
                if ( !isset( $wpc_templates['emails']['reset_password'] ) || '' == $wpc_templates['emails']['reset_password']['body'] ) {
                    $wpc_templates['emails']['reset_password']['subject']  = "{site_title}]Password Reset";
                    $wpc_templates['emails']['reset_password']['body']     = '<p>Hi {user_name},</p>
                    <p>You have requested to reset your password.</p>
                    <p>Please follow the link below.</p>
                    <p><a href="{reset_address}">Reset Your Password</a></p>
                    <p>Thanks,</p>
                    <p>{business_name}</p>
                    ';

                    update_option( 'wpc_templates', $wpc_templates );
                }

                //email when new item to assigned clients
                if ( !isset( $wpc_templates['emails']['private_post_type'] ) ) {
                    $wpc_templates['emails']['private_post_type']['subject']  = "You have been given access to {page_title}";
                    $wpc_templates['emails']['private_post_type']['body']     = '<p>Hello {contact_name},</p>
                    <p>You have been given access to {page_title} | <a href="{page_id}">Click HERE to visit</a></p>
                    <p>Thanks, and please contact us if you experience any difficulties,</p>
                    <p>{business_name}</p>
                    ';
                }

                update_option( 'wpc_templates', $wpc_templates );

            }


            if ( version_compare( $ver, '3.1.8', '<' ) ) {
                delete_option( 'wpc_show_link' );
                delete_option( 'wpc_link_text' );
                delete_option( 'show_sort' );
                delete_option( 'wpc_create_client' );

            }


            if ( version_compare( $ver, '3.2.0', '<' ) ) {
                $wpc_settings = get_option( 'wpc_settings' );

                if ( !empty( $wpc_settings ) ) {

                    /*
                    * general block
                    */
                    $wpc_general = WPC()->get_settings( 'general' );

                    if ( false !== get_option( 'wpc_graphic' ) ) {
                        $wpc_general['graphic'] = get_option( 'wpc_graphic' );
                    }

                    if ( isset( $wpc_settings['show_hub_title'] ) ) {
                        $wpc_general['show_hub_title'] = $wpc_settings['show_hub_title'];
                    }

                    if ( isset( $wpc_settings['show_custom_menu'] ) ) {
                        $wpc_general['show_custom_menu'] = $wpc_settings['show_custom_menu'];
                    }

                    if ( isset( $wpc_settings['custom_menu_logged_in'] ) ) {
                        $wpc_general['custom_menu_logged_in'] = $wpc_settings['custom_menu_logged_in'];
                    }

                    if ( isset( $wpc_settings['custom_menu_logged_out'] ) ) {
                        $wpc_general['custom_menu_logged_out'] = $wpc_settings['custom_menu_logged_out'];
                    }

                    WPC()->settings()->update( $wpc_general, 'general' );


                    /*
                    * clients/staff block
                    */
                    $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );

                    if ( isset( $wpc_settings['hide_dashboard'] ) ) {
                        $wpc_clients_staff['hide_dashboard'] = $wpc_settings['hide_dashboard'];
                    }

                    if ( false !== get_option( 'wpc_create_client' ) ) {
                        $wpc_clients_staff['create_portal_page'] = get_option( 'wpc_create_client' );
                    }

                    if ( isset( $wpc_settings['hide_admin_bar'] ) ) {
                        $wpc_clients_staff['hide_admin_bar'] = $wpc_settings['hide_admin_bar'];
                    }

                    if ( isset( $wpc_settings['lost_password'] ) ) {
                        $wpc_clients_staff['lost_password'] = $wpc_settings['lost_password'];
                    }

                    if ( isset( $wpc_settings['client_registration'] ) ) {
                        $wpc_clients_staff['client_registration'] = $wpc_settings['client_registration'];
                    }

                    if ( isset( $wpc_settings['auto_client_approve'] ) ) {
                        $wpc_clients_staff['auto_client_approve'] = ( '1' == $wpc_settings['auto_client_approve'] ) ? 'yes' : 'no';
                    }

                    if ( isset( $wpc_settings['new_client_admin_notify'] ) ) {
                        $wpc_clients_staff['new_client_admin_notify'] = ( '1' == $wpc_settings['new_client_admin_notify'] ) ? 'yes' : 'no';
                    }

                    if ( isset( $wpc_settings['send_approval_email'] ) ) {
                        $wpc_clients_staff['send_approval_email'] = ( '1' == $wpc_settings['send_approval_email'] ) ? 'yes' : 'no';
                    }

                    if ( isset( $wpc_settings['staff_registration'] ) ) {
                        $wpc_clients_staff['staff_registration'] = $wpc_settings['staff_registration'];
                    }

                    if ( isset( $wpc_settings['registration_using_captcha'] ) ) {
                        $wpc_clients_staff['registration_using_captcha'] = $wpc_settings['registration_using_captcha'];
                    }

                    if ( isset( $wpc_settings['captcha_publickey'] ) ) {
                        $wpc_clients_staff['captcha_publickey'] = $wpc_settings['captcha_publickey'];
                    }

                    if ( isset( $wpc_settings['captcha_privatekey'] ) ) {
                        $wpc_clients_staff['captcha_privatekey'] = $wpc_settings['captcha_privatekey'];
                    }

                    if ( isset( $wpc_settings['captcha_theme'] ) ) {
                        $wpc_clients_staff['captcha_theme'] = $wpc_settings['captcha_theme'];
                    }

                    WPC()->settings()->update( $wpc_clients_staff, 'clients_staff' );



                    /*
                    * file_sharing block
                    */
                    $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

                    if ( false !== get_option( 'show_sort' ) ) {
                        $wpc_file_sharing['show_sort'] = get_option( 'show_sort' );
                    }

                    if ( isset( $wpc_settings['show_file_cats'] ) ) {
                        $wpc_file_sharing['show_file_cats'] = ( '1' == $wpc_settings['show_file_cats'] ) ? 'yes' : 'no';
                    }

                    if ( isset( $wpc_settings['deny_file_cats'] ) ) {
                        $wpc_file_sharing['deny_file_cats'] = ( '1' == $wpc_settings['deny_file_cats'] ) ? 'yes' : 'no';
                    }

                    if ( isset( $wpc_settings['flash_uplader_admin'] ) ) {
                        $wpc_file_sharing['flash_uplader_admin'] = ( '1' == $wpc_settings['flash_uplader_admin'] ) ? 'yes' : 'no';
                    }

                    if ( isset( $wpc_settings['flash_uplader_client'] ) ) {
                        $wpc_file_sharing['flash_uplader_client'] = ( '1' == $wpc_settings['flash_uplader_client'] ) ? 'yes' : 'no';
                    }

                    if ( isset( $wpc_settings['file_size_limit'] ) ) {
                        $wpc_file_sharing['file_size_limit'] = $wpc_settings['file_size_limit'];
                    }

                    if ( isset( $wpc_settings['attach_file_admin'] ) ) {
                        $wpc_file_sharing['attach_file_admin'] = ( '1' == $wpc_settings['attach_file_admin'] ) ? 'yes' : 'no';
                    }

                    WPC()->settings()->update( $wpc_file_sharing, 'file_sharing' );


                    /*
                    * custom login block
                    */
                    $old_custom_login_options = get_option( 'custom_login_options' );

                    if ( !empty( $old_custom_login_options ) ) {
                        $wpc_custom_login = WPC()->get_settings( 'custom_login' );

                        $wpc_custom_login = array_merge( $wpc_custom_login, $old_custom_login_options );

                        WPC()->settings()->update( $wpc_custom_login, 'custom_login' );
                    }


                    /*
                    * capabilities block
                    */
                    if ( isset( $wpc_settings['capabilities_maps'] ) ) {
                        $wpc_capabilities = $wpc_settings['capabilities_maps'];

                        WPC()->settings()->update( $wpc_capabilities, 'capabilities' );
                    }


                    /*
                    * pages block
                    */
                    if ( isset( $wpc_settings['pages'] ) ) {
                        $wpc_pages = $wpc_settings['pages'];

                        WPC()->settings()->update( $wpc_pages, 'pages' );
                    }


                    /*
                    * gateways block
                    */
                    if ( isset( $wpc_settings['gateways'] ) ) {
                        $wpc_gateways = $wpc_settings['gateways'];

                        WPC()->settings()->update( $wpc_gateways, 'gateways' );
                    }


                    /*
                    * gateways block
                    */
                    if ( isset( $wpc_settings['custom_titles'] ) ) {
                        $wpc_custom_titles = $wpc_settings['custom_titles'];

                        WPC()->settings()->update( $wpc_custom_titles, 'custom_titles' );
                    }


                    /*
                    * skins block
                    */
                    $old_skins = get_option( 'wpclients_theme' );

                    if ( !empty( $old_skins ) ) {
                        WPC()->settings()->update( $old_skins, 'skins' );
                    }



                    /*********
                    *  Templates
                    *********/

                    /*
                    * hub template block
                    */
                    $old_hub_template = get_option( 'hub_template' );

                    if ( !empty( $old_hub_template ) ) {
                        WPC()->settings()->update( $old_hub_template, 'templates_hubpage' );
                    }


                    /*
                    * client template block
                    */
                    $old_client_template = get_option( 'client_template' );

                    if ( !empty( $old_client_template ) ) {
                        WPC()->settings()->update( $old_client_template, 'templates_clientpage' );
                    }


                    /*
                    * templates emails block
                    */
                    $wpc_templates = get_option( 'wpc_templates' );

                    if ( isset( $wpc_templates['emails'] ) ) {
                        $wpc_templates_emails = WPC()->get_settings( 'templates_emails' );

                        $wpc_templates_emails = array_merge( $wpc_templates_emails, $wpc_templates['emails'] );

                        WPC()->settings()->update( $wpc_templates_emails, 'templates_emails' );
                    }


                    if ( isset( $wpc_templates['wpc_shortcodes'] ) ) {
                        $wpc_templates_shortcodes = WPC()->get_settings( 'templates_shortcodes' );

                        $wpc_templates_shortcodes = array_merge( $wpc_templates_shortcodes, $wpc_templates['wpc_shortcodes'] );

                        WPC()->settings()->update( $wpc_templates_shortcodes, 'templates_shortcodes' );
                    }



                    /*********
                    * Extension: Feedback wizard
                    *********/

                    //Feedback wizard templates
                    $wpc_fbw_templates = get_option( 'wpc_fbw_templates' );

                    if ( isset( $wpc_fbw_templates['emails'] ) ) {
                        $wpc_templates_emails = WPC()->get_settings( 'templates_emails' );

                        $wpc_templates_emails = array_merge( $wpc_templates_emails, $wpc_fbw_templates['emails'] );

                        WPC()->settings()->update( $wpc_templates_emails, 'templates_emails' );
                    }


                    /*********
                    * Extension: Invoicing
                    *********/
                    $wpc_invoice_settings = get_option( 'wpc_invoice_settings' );

                    //settings
                    if ( isset( $wpc_invoice_settings['preferences'] ) ) {
                        $preferences = $wpc_invoice_settings['preferences'];

                        $wpc_invoicing = WPC()->get_settings( 'invoicing' );
                        $wpc_invoicing['send_for_review'] = ( isset( $preferences['send_for_review'] ) && '1' == $preferences['send_for_review'] ) ? 'yes' : 'no';
                        $wpc_invoicing['prefix'] = ( isset( $preferences['prefix'] ) ) ? $preferences['prefix'] : '';
                        $wpc_invoicing['next_number'] = ( isset( $preferences['next_number'] ) ) ? $preferences['next_number'] : '';
                        $wpc_invoicing['rate_capacity'] = ( isset( $preferences['rate_capacity'] ) ) ? $preferences['rate_capacity'] : 2;
                        $wpc_invoicing['reminder_days'] = ( isset( $preferences['reminder_days'] ) ) ? $preferences['reminder_days'] : 1;
                        $wpc_invoicing['display_zeros'] = ( !isset( $preferences['display_zeros'] ) || '1' == $preferences['display_zeros'] ) ? 'yes' : 'no';
                        $wpc_invoicing['digits_count'] = ( isset( $preferences['digits_count'] ) ) ? $preferences['digits_count'] : 8;
                        $wpc_invoicing['notify_payment_made'] = ( isset( $preferences['notify_payment_made'] ) && '1' == $preferences['notify_payment_made'] ) ? 'yes' : 'no';
                        $wpc_invoicing['currency_symbol'] = ( isset( $preferences['currency_symbol'] ) ) ? $preferences['currency_symbol'] : '$';

                        if ( isset( $wpc_invoice_settings['templates']['ter_con'] ) ) {
                            $wpc_invoicing['ter_con'] = $wpc_invoice_settings['templates']['ter_con'];
                        }

                        if ( isset( $wpc_invoice_settings['templates']['not_cus'] ) ) {
                            $wpc_invoicing['not_cus'] = $wpc_invoice_settings['templates']['not_cus'];
                        }

                        WPC()->settings()->update( $wpc_invoicing, 'invoicing' );
                    }

                    //email templates
                    if ( isset( $wpc_invoice_settings['templates'] ) ) {
                        $wpc_templates_emails = WPC()->get_settings( 'templates_emails' );

                        if ( isset( $wpc_invoice_settings['templates']['inv_not'] ) ) {
                            $wpc_templates_emails['inv_not'] = $wpc_invoice_settings['templates']['inv_not'];
                        }

                        if ( isset( $wpc_invoice_settings['templates']['est_not'] ) ) {
                            $wpc_templates_emails['est_not'] = $wpc_invoice_settings['templates']['est_not'];
                        }

                        if ( isset( $wpc_invoice_settings['templates']['pay_tha'] ) ) {
                            $wpc_templates_emails['pay_tha'] = $wpc_invoice_settings['templates']['pay_tha'];
                        }

                        if ( isset( $wpc_invoice_settings['templates']['admin_notify'] ) ) {
                            $wpc_templates_emails['admin_notify'] = $wpc_invoice_settings['templates']['admin_notify'];
                        }

                        if ( isset( $wpc_invoice_settings['templates']['pay_rem'] ) ) {
                            $wpc_templates_emails['pay_rem'] = $wpc_invoice_settings['templates']['pay_rem'];
                        }

                        WPC()->settings()->update( $wpc_templates_emails, 'templates_emails' );
                    }

                    //pages templates
                    if ( isset( $wpc_invoice_settings['templates'] ) ) {
                        $wpc_templates_shortcodes = WPC()->get_settings( 'templates_shortcodes' );

                        if ( isset( $wpc_invoice_settings['templates']['inv'] ) ) {
                            $wpc_templates_shortcodes['wpc_client_inv_inv'] = $wpc_invoice_settings['templates']['inv'];
                        }

                        if ( isset( $wpc_invoice_settings['templates']['est'] ) ) {
                            $wpc_templates_shortcodes['wpc_client_inv_est'] = $wpc_invoice_settings['templates']['est'];
                        }

                        WPC()->settings()->update( $wpc_templates_shortcodes, 'templates_shortcodes' );
                    }


                    /*********
                    * Extension: Paid Registration
                    *********/
                    $p_registration_settings = get_option( 'wpc_p_registration_settings' );

                    if ( !empty( $p_registration_settings ) ) {
                        $wpc_paid_registration = WPC()->get_settings( 'paid_registration' );


                        $wpc_paid_registration['enable'] = ( isset( $p_registration_settings['enable'] ) && '1' == $p_registration_settings['enable'] ) ? 'yes' : 'no';

                        if ( isset( $p_registration_settings['gateways'] ) ) {
                            $wpc_paid_registration['gateways'] = $p_registration_settings['gateways'];
                        }

                        $wpc_paid_registration['cost'] = ( isset( $p_registration_settings['cost'] ) ) ? $p_registration_settings['cost'] : '';
                        $wpc_paid_registration['description'] = ( isset( $p_registration_settings['description'] ) ) ? $p_registration_settings['description'] : '';

                        WPC()->settings()->update( $wpc_paid_registration, 'paid_registration' );
                    }


                    /*********
                    * Extension: Time Limited Clients
                    *********/
                    if ( isset( $wpc_settings['tlc_error_text'] ) ) {
                        $wpc_time_limited_clients = WPC()->get_settings( 'time_limited_clients' );

                        $wpc_time_limited_clients['tlc_error_text'] = $wpc_settings['tlc_error_text'];

                        WPC()->settings()->update( $wpc_time_limited_clients, 'time_limited_clients' );
                    }

                }

            }

            update_option( 'wp_client_ver', WPC_CLIENT_VER );


        }


    //end class
    }

}