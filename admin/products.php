<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
};

if(isset($_POST['add_product'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $category = $_POST['category'];
   $category = filter_var($category, FILTER_SANITIZE_STRING);
   $weight = $_POST['weight'];
//    $weight = filter_var($weight, FILTER_SANITIZE_STRING);
   
   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = '../uploaded_img/'.$image;

   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_products->execute([$name]);

   if($select_products->rowCount() > 0){
      $message[] = 'product name already exists!';
   }else{
      if($image_size > 2000000){
         $message[] = 'image size is too large';
      }else{
         move_uploaded_file($image_tmp_name, $image_folder);

         $insert_product = $conn->prepare("INSERT INTO `products`(name, category, price,image,weight) VALUES(?,?,?,?,?)");
         $insert_product->execute([$name, $category, $price, $image,$weight]);

         $message[] = 'new product added!';
      }

   }

}

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
   $delete_product_image->execute([$delete_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
   unlink('../uploaded_img/'.$fetch_delete_image['image']);
   $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_product->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->execute([$delete_id]);
   header('location:products.php');

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>products</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/admin_style.css">

</head>

<body>

    <?php include '../components/admin_header.php' ?>

    <!-- add products section starts  -->

    <section class="add-products">

        <form action="" method="POST" enctype="multipart/form-data">
            <h3>add product</h3>
            <input type="text" required placeholder="enter product name" name="name" maxlength="100" class="box">
            <input type="text" min="0" required placeholder="enter product price" name="price" class="box">
            <select name="category" class="box" required>
                <option value="" disabled selected>select category --</option>
                <option value="main dish">main dish</option>
                <option value="fast food">fast food</option>
                <option value="drinks">drinks</option>
                <option value="desserts">desserts</option>
            </select>
            <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png, image/webp" required>
            <input type="text" required placeholder="enter product weight" name="weight" class="box">
            <input type="submit" value="add product" name="add_product" class="btn">
        </form>

    </section>

    <!-- add products section ends -->

    <!-- show products section starts  -->

    <section class="show-products" style="padding-top: 0;">

        <div class="box-container">

            <?php
      $show_products = $conn->prepare("SELECT * FROM `products`");
      $show_products->execute();
      if($show_products->rowCount() > 0){
         while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){  
   ?>
            <div class="box">
                <img src="../uploaded_img/<?= $fetch_products['image']; ?>" alt="">
                <div class="flex">
                    <div class="price"><span>Tk</span><?= $fetch_products['price']; ?><span>/-</span></div>
                    <div class="category"><?= $fetch_products['category']; ?></div>
                </div>
                <div class="name"><?= $fetch_products['name']; ?></div>
                <div class="flex-btn">
                    <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a>
                    <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn"
                        onclick="return confirm('delete this product?');">delete</a>
                </div>
            </div>
            <?php
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
   ?>

        </div>

    </section>

    <!-- show products section ends -->

    <!-- custom js file link  -->
    <script src="../js/admin_script.js"></script>

</body>

</html>

<!-- include '../components/connect.php';: This line includes a PHP script that establishes a connection to the database.
It's assumed that this script contains the necessary code to connect to the database using PDO or a similar method.

session_start();: This starts a PHP session, allowing you to store and retrieve data across multiple pages for the same
user.

$admin_id = $_SESSION['admin_id'];: This line retrieves the 'admin_id' from the session data and assigns it to the
variable $admin_id.

if(!isset($admin_id)){ header('location:admin_login.php'); };: This block checks if the $admin_id variable is not set,
meaning the user is not logged in as an admin. If so, it redirects the user to the admin login page.

if(isset($_POST['add_product'])){ ... }: This block checks if the form to add a new product has been submitted.

Inside the if(isset($_POST['add_product'])){ ... } block:

It retrieves the submitted values of the product name, price, and category from the $_POST array and sanitizes them
using FILTER_SANITIZE_STRING.
It retrieves the uploaded image file details (name, size, tmp_name) from the $_FILES array and sanitizes the name.
It prepares a SELECT query to check if the product name already exists in the database.
If the product name already exists, it adds a message to the $message array indicating the duplication.
If the product name is unique, it checks if the image size is within the allowed limit.
If the image size is within the limit, it moves the uploaded image file to the designated folder.
It prepares an INSERT query to add the new product details to the database.
It adds a message to the $message array indicating the successful addition of the new product.
if(isset($_GET['delete'])){ ... }: This block checks if the delete parameter is set in the URL, indicating a request to
delete a product.

Inside the if(isset($_GET['delete'])){ ... } block:

It retrieves the product ID to be deleted from the URL.
It prepares a SELECT query to fetch the image filename associated with the product to be deleted.
It fetches the filename and deletes the associated image file from the server.
It prepares a DELETE query to remove the product from the database.
It prepares another DELETE query to remove any instances of the product from the 'cart' table.
After deleting the product and associated data, it redirects the user back to the 'products.php' page.
In the HTML section:

It includes an HTML form to add new products. This form includes fields for the product name, price, category, and an
image upload field.
It includes a section to display existing products retrieved from the database.
Each product is displayed in a box with its image, price, category, name, and options to update or delete the product.
If no products are available, it displays a message indicating that no products have been added yet.
This PHP script essentially handles the addition and deletion of products, as well as the display of existing products
with their details. It also performs necessary checks and sanitization of user input and file uploads. -->