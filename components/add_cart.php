<?php

if(isset($_POST['add_to_cart'])){

   if($user_id == ''){
      header('location:login.php');
   }else{

      $pid = $_POST['pid'];
      $pid = filter_var($pid, FILTER_SANITIZE_STRING);
      $name = $_POST['name'];
      $name = filter_var($name, FILTER_SANITIZE_STRING);
      $price = $_POST['price'];
      $price = filter_var($price, FILTER_SANITIZE_STRING);
      $image = $_POST['image'];
      $image = filter_var($image, FILTER_SANITIZE_STRING);
      $qty = $_POST['qty'];
      $qty = filter_var($qty, FILTER_SANITIZE_STRING);
      $weight = $_POST['weight'];
      $weight = filter_var($weight, FILTER_SANITIZE_STRING);

      $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
      $check_cart_numbers->execute([$name, $user_id]);

      if($check_cart_numbers->rowCount() > 0){
         $message[] = 'already added to cart!';
      }else{
         $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image,weight) VALUES(?,?,?,?,?,?,?)");
         $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image,$weight]);
         $message[] = 'added to cart!';
         
      }

   }

}

?>

<!-- This line checks if the 'add_to_cart' key is set in the $_POST superglobal array. This typically indicates that a form
has been submitted with a button named 'add_to_cart'.


Here, it checks if the $user_id variable is empty. If it is, it redirects the user to the 'login.php' page. If the
$user_id is not empty, the execution continues.


This retrieves the value of 'pid' from the $_POST array, filters it to sanitize it as a string, and assigns it to the
variable $pid.



Similar to the previous line, this retrieves the value of 'name' from the $_POST array, sanitizes it as a string, and
assigns it to the variable $name.



This retrieves the value of 'price' from the $_POST array, sanitizes it as a string, and assigns it to the variable
$price.




This retrieves the value of 'image' from the $_POST array, sanitizes it as a string, and assigns it to the variable
$image.





This retrieves the value of 'qty' from the $_POST array, sanitizes it as a string, and assigns it to the variable
$qty.




This prepares a SQL query to select rows from the 'cart' table where the 'name' column matches the sanitized $name
and the 'user_id' column matches the $user_id. It then executes the query with the provided parameters.If the query
returns any rows (i.e., if the item is already in the cart), it adds a message to the $message array indicating that
it's already in the cart. Otherwise, it continues to the else block.




This prepares an SQL INSERT query to add a new row to the 'cart' table with the provided values. It then executes the
query with the provided parameters and adds a message to the $message array indicating that the item has been
successfully added to the cart.


The code seems to be handling the addition of items to a shopping cart, ensuring that the user is logged in, sanitizing
input, and checking for duplicate entries before adding a new item to the cart. -->