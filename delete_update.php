<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>
<body>
<?php
    require('connection/connection.php');// ملف الربط من قاعدة البيانات
?>
<table border="2px">
  <tr style="background-color:#999999">
    <td>id</td>
    <td>user_name</td>
    <td>password</td>
    <td>user_type</td>
    <td>delete</td>
    <td>update</td>
  </tr>
 <?php
  // show data from table ok
  $query="SELECT *FROM users ORDER BY u_id";
  $result=mysqli_query($con_db,$query);
  
  $num_rows=mysqli_num_rows($result);
  echo $num_rows;
 if($num_rows>0)
 {
	 while($show_data=mysqli_fetch_assoc($result))
	 {
		   echo '
		         <tr>
					<td>'.$show_data['u_id'].'</td>
					<td>'.$show_data['u_name'].'</td>
					<td>'.$show_data['u_pass'].'</td>
					<td>'.$show_data['u_type'].'</td>
					<td><a href="delete_update.php"?action=update&id_update='.$show_data['u_id'].'> delete</a></td>
					<td><a href="delete_update.php"?action=delete&id_delete='.$show_data['u_id'].'> update</a></td>
				  </tr> 
		   
		   
		   
		      ';
	 }
 }
 ?> 

</table>
<?php
  // prosse of delete
  if(isset($_GET['action'] && $_GET['action']=='delete'))
  {
	  $query_delete="DELETE FROM users WHERE id=".$_GET['id_delete']."";
	  mysqli_query($con_db,$query_delete) or die(mysqli_error());
  }

?>
</body>
</html>