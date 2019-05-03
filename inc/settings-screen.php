<?php

add_action( 'admin_init', 'wp_rest_retriever_do_activation_redirect' );
function wp_rest_retriever_do_activation_redirect() {
  // Bail if no activation redirect
    if ( ! get_transient( '_wp_rest_retriever_activation_redirect' ) ) {
        return;
    }

    // Delete the redirect transient
    delete_transient( '_wp_rest_retriever_activation_redirect' );

    // Bail if activating from network, or bulk
    if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
        return;
    }

    // Redirect to plugin settings page
    wp_safe_redirect( add_query_arg( [ 'page' => 'wp-rest-retriever-settings' ], admin_url( 'index.php' ) ) );
}

add_action('admin_menu', 'wp_rest_retriever_pages');

function wp_rest_retriever_pages() {
    add_dashboard_page(
        'Welcome To REST Retriever',
        'WordPress REST Retriever',
        'read',
        'wp-rest-retriever-settings',
        'wp_rest_retriever_settings'
    );
    add_dashboard_page(
        'Welcome To REST Retriever',
        'WordPress REST Retriever',
        'read',
        'wp-rest-retriever-examples',
        'wp_rest_retriever_examples'
    );
}

add_filter( 'init', function( $template ) {
    if ( isset( $_GET['allquotes'] ) ) {
        $allquotes = $_GET['allquotes'];
        include plugin_dir_path( __FILE__ ) . 'allquotes.php';
        die;
    }
} );


function wp_rest_retriever_settings() {
    wp_rest_retriever_settings_header();
    ?>

    <div class="full-width">
        <form action="" method="POST">
            <label for="domain">Domain:</label>
            <input name="domain" value="<?php echo get_option('rr_domain', null) ?>"/> <br>
            <label for="user">API User:</label>
            <input name="user" value="<?php echo get_option('rr_user', null) ?>"/><br>
            <label for="pass">API Password:</label>
            <input name="pass" value="<?php echo get_option('rr_pass', null) ?>"/><br><br>

            <input type="submit" value='Save' />
        </form>
    </div>
  </div>
  <?php
    if (!empty($_POST)) {
        update_option('rr_domain', $_POST['domain']);
        update_option('rr_user', $_POST['user']);
        update_option('rr_pass', $_POST['pass']);
    }
}

function wp_rest_retriever_examples() {
    wp_rest_retriever_settings_header();

    $domain = get_option('rr_domain', null);
    //    $domain = "http://www.famousquotes.dev/author/".$author_id;
    $user = get_option('rr_user', null);
    $password = get_option('rr_pass', null);

    if (!empty($_POST)) {
        if (!empty($_POST['add'])) {
            $data = json_encode([
                'quote'=> $_POST['quote'],
                'author' => $_POST['author'],
            ]);

            $url = $domain . "/quotes";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [                                                                          
                'Content-Type: application/json',                                                                                
                'Content-Length: ' . strlen($data)
            ]); 

            $response = curl_exec($ch);
            curl_close($ch);
        }

        if (!empty($_POST['save'])) {
            $data = json_encode([
                'quote'=> $_POST['quote'],
                'author' => $_POST['author'],
            ]);

            $url = $domain . "/quotes/" . $_POST['id'];
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [                                                                          
                'Content-Type: application/json',                                                                                
                'Content-Length: ' . strlen($data)
            ]); 

            $response = curl_exec($ch);
            curl_close($ch);
        }

        if (!empty($_POST['delete'])) {
            $url = $domain . "/quotes/" . $_POST['id'];
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            curl_close($ch);
        }
    }

    $url = $domain . "/quotes";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $password);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $items = (json_decode($response, true));
    ?>
    <div class="feature-section one-col">
      <div class="col">
        <h2>Example Shortcode</h2>
        <p>[wp_rest_retriever]</p>
      </div>
    </div>

    <div class="feature-section one-col">
      <div class="col">
        <form action="" method="POST">
            <label for="author">Author:</label>
            <input name="author" value=""/>
            <label for="quote">Quote:</label>
            <textarea name="quote"></textarea>

            <input type="submit" name="add" value='Add' />
        </form>
        <hr>
<?php foreach ($items as $item) : ?>
        <form action="" method="POST">
            <input type="hidden" name="id" value="<?php echo $item['id'] ?>" />
            <label for="author">Author:</label>
            <input name="author" value="<?php echo $item['author']['name'] ?>"/>
            <label for="quote">Quote:</label>
            <textarea name="quote"><?php echo $item['quote'] ?></textarea>

            <input type="submit" name="save" value='Save' />
            <input type="submit" name="delete" value='Delete' />
        </form>
        <hr>
<?php endforeach; ?>
      </div>
  </div>
  <?php
}

function wp_rest_retriever_settings_header() {
    $screen = get_current_screen();
  ?>
  <div class="wrap about-wrap full-width-layout">
    <h1>Welcome to REST Quotes Retriever v<?php echo WP_REST_RETRIEVER_VER; ?></h1>

    
    <p class="about-text">
      Something about this...
    </p>
    
    <h2 class="nav-tab-wrapper wp-clearfix">
      <a href="<?php echo admin_url( 'index.php?page=wp-rest-retriever-settings') ?>" class="nav-tab<?php echo ($screen->id == 'dashboard_page_wp-rest-retriever-settings' ? ' nav-tab-active' : ''); ?>">Config</a>
      <a href="<?php echo admin_url( 'index.php?page=wp-rest-retriever-examples') ?>" class="nav-tab<?php echo ($screen->id == 'dashboard_page_wp-rest-retriever-examples' ? ' nav-tab-active' : ''); ?>">Quotes</a>
    </h2>
  <?php
}

add_action( 'admin_head', 'wp_rest_retriever_remove_menus', 999 );
function wp_rest_retriever_remove_menus() {
    remove_submenu_page( 'index.php', 'wp-rest-retriever-settings' );
    remove_submenu_page( 'index.php', 'wp-rest-retriever-examples' );
}
