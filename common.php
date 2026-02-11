<?php

$new_types_page =
  [ "title" => "Make new types more often."
  , "id" => "new-types"
  , "date" => "May 8, 2023" ];

$modularity_page = 
  [ "title" => "Modularity is about dependencies."
  , "id" => "modularity"
  , "date" => "May 8, 2023" ];

$ocaml_gadts_page = 
  [ "title" => "How to understand OCaml's GADTs."
  , "id" => "ocaml-gadts"
  , "date" => "May 9, 2023" ];

$parts_of_a_pl_implementation_page =
  [ "title" => "Parts of a programming language implementation."
  , "id" => "parts-of-a-pl-implementation"
  , "date" => "Apr 14, 2025" ];

$yet_wisdom_is_justified_by_her_deeds_page =
  [ "title" => "Yet wisdom is justified by her deeds."
  , "id" => "yet-wisdom-is-justified-by-her-deeds"
  , "date" => "Dec 3, 2025" ];

$the_making_of_missiles =
  [ "title" => "The making of 'missiles'."
  , "id" => "the-making-of-missiles"
  , "date" => "Feb 11, 2026" ];

$pages = [

  $new_types_page,
  $modularity_page,
  $ocaml_gadts_page,

  [ "title" => "On writing interactive UIs with straight-line code."
  , "id" => "straight-line-ui-code"
  , "date" => "Feb 28, 2024" ],

  [ "title" => "Bottlenecks."
  , "id" => "bottlenecks"
  , "date" => "Feb 29, 2024" ],

  [ "title" => "Opinions about crossword clues."
  , "id" => "opinions-about-crossword-clues"
  , "date" => "Apr 11, 2025" ],

  [ "title" => "2025 Cycling log."
  , "id" => "cycling-log-2025"
  , "date" => "Apr 12, 2025" ],

  $parts_of_a_pl_implementation_page,
  $yet_wisdom_is_justified_by_her_deeds_page,
  $the_making_of_missiles

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
