<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="styles.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Trailing Whitespace</title>
  </head>
  <body>
    <h1>Pages</h1>
    <ul class="index-page-list">
<?php
include "common.php";

foreach (array_reverse($pages) as $page) {
?>
      <li>
        <a href="<?php echo $page["id"] ?>.html"><?php echo $page["title"] ?></a>
        <em><?php echo $page["date"] ?></em>
      </li>
<?php
}
?>
    </ul>
  </body>
</html>
