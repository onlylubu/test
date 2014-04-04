<?php

echo "result is";
echo"<pre>"; print_r($_POST);
$Status=$_POST['Status'];
add_option('temp_value',$_POST);
add_option('temp_status',$Status);
?>