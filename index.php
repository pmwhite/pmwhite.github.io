<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="styles.css?v=2" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Trailing Whitespace</title>
  </head>
  <body>
    <a class="masthead" href="/">Trailing Whitespace</a>
<?php
include "common.php";
?>
    <h1>Posts</h1>
    <ul class="index-page-list">
<?php
foreach (array_reverse($posts) as $page) {
?>
      <li>
        <a href="<?php echo $page["id"] ?>.html"><?php echo $page["title"] ?></a>
        <em><?php echo $page["date"] ?></em>
      </li>
<?php
}
?>
    </ul>
    <h1>Pages</h1>
    <ul class="index-page-list">
<?php
foreach ($pages as $page) {
?>
      <li>
        <a href="<?php echo $page["id"] ?>.html"><?php echo $page["title"] ?></a>
      </li>
<?php
}
?>
    </ul>
    <h1>Games</h1>
    <ul class="index-page-list">
<?php
foreach ($games as $page) {
?>
      <li>
        <a href="<?php echo $page["id"] ?>.html"><?php echo $page["title"] ?></a>
      </li>
<?php
}
?>
    </ul>
  </body>
</html>
