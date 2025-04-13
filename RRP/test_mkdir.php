<?php
if (!file_exists("images")) {
    mkdir("images", 0777, true);
    echo "images/ folder created successfully!";
} else {
    echo "images/ folder already exists.";
}
?>
