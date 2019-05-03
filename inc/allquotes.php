<?php get_header(); ?>

<?php
$author_id = $_GET['allquotes'];

$domain = get_option('rr_domain', null) . "/authors/" . $author_id;
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

$author = (json_decode($response, true));
?>
<h1>Quotes by <?php echo $author['name'] ?></h1>
<div>
<?php foreach ($author['quotes'] as $item) : ?>
<p>
    <?php echo $item['quote']; ?>
</p>
<?php endforeach; ?>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
