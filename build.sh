#!/bin/env sh
set -eux
php -f index.php > index.html
php -f ocaml-gadts.php > ocaml-gadts.html
php -f modularity.php > modularity.html
php -f cycling-log-2025.php > cycling-log-2025.html
