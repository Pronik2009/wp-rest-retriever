<?php get_header(); ?>

<?php
$author_id = $_GET['allquotes'];

$domain = get_option('rr_domain', null) . "/quotes";
//    $domain = "http://www.famousquotes.dev/author/".$author_id;
$user = get_option('rr_user', null);
$password = get_option('rr_pass', null);

$ch = curl_init($domain);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $password);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$items = (json_decode($response, true));
foreach ($items as $item) {
    echo $item['quote'];
    echo ' - ';
    echo $item['author']['name'];
    echo '<br>';
}
?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
