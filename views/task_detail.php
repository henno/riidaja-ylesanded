<?php
$id = basename($_GET['task']);
$filename = __DIR__ . '/../exercises/' . $id . '.php';

if (preg_match('/^\d{3}$/', $id) && file_exists($filename)) {
  echo "<h2>Ülesanne $id</h2>";
  include $filename;
} else {
  echo "<p>Ülesannet ei leitud.</p>";
}
?>
