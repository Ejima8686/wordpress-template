#!/bin/bash

script_dir=$(dirname "$0")  

if [ ! -d "$script_dir/vendor" ]; then
    cd $script_dir && composer install 
fi

php "$script_dir/setup-dummy-posts.php" --allow-root
