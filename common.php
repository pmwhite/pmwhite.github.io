<?php

$ocaml_gadts_page = 
  [ "title" => "How to understand OCaml's GADTs."
  , "id" => "ocaml-gadts" ];

$modularity_page = 
  [ "title" => "Modularity is about dependencies."
  , "id" => "modularity" ];

$new_types_page =
  [ "title" => "Make new types more often."
  , "id" => "new-types" ];


$pages = [

  $ocaml_gadts_page,
  $modularity_page,
  $new_types_page,

  [ "title" => "On writing interactive UIs with straight-line code."
  , "id" => "straight-line-ui-code" ],

  [ "title" => "Bottlenecks."
  , "id" => "bottlenecks" ],

  [ "title" => "Opinions about crossword clues."
  , "id" => "opinions-about-crossword-clues" ],

  [ "title" => "2025 Cycling log."
  , "id" => "cycling-log-2025" ],

];

function essay_begin($page) {
?>
<!DOCTYPE>
<html>
<head>
  <link rel="stylesheet" href="styles.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo $page["title"] ?></title>
</head>
<body>
<h1><?php echo $page["title"] ?></h1>
<?php
}

function essay_end() {
?>
</body>
</html>
<?php
}

?>
