<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:home.php');
};

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $method = $_POST['method'];
   $method = filter_var($method, FILTER_SANITIZE_STRING);
   $address = $_POST['address'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);
   $total_products = $_POST['total_products'];
   $total_price = $_POST['total_price'];
   $delivery = $_POST['deliveryCharge'];
   $weight_charge = $_POST['weight'];


   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if($check_cart->rowCount() > 0){

      if($address == ''){
         $message[] = 'please add your address!';
      }else{
         
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, delivery,weight_charge) VALUES(?,?,?,?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price, $delivery,$weight_charge]);

         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);

         $message[] = 'order placed successfully!';
      }
      
   }else{
      $message[] = 'your cart is empty';
   }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>checkout</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <!-- header section starts  -->
    <?php include 'components/user_header.php'; ?>
    <!-- header section ends -->

    <div class="heading">
        <h3>checkout</h3>
        <p><a href="home.php">home</a> <span> / checkout</span></p>
    </div>

    <section class="checkout">

        <h1 class="title">order summary</h1>

        <form action="" method="post">

            <div class="cart-items">
                <h3>cart items</h3>
                <?php
         $grand_total = 0;
         $cart_items[] = '';
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $cart_items[] = $fetch_cart['name'].' ('.$fetch_cart['price'].' x '. $fetch_cart['quantity'].') - ';
               $total_products = implode($cart_items);
               $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
               $weight_charge = (floor($fetch_cart['weight'])*10);
      ?>
                <p><span class="name"><?= $fetch_cart['name']; ?></span><span
                        class="price">TK<?= $fetch_cart['price']; ?>
                        x <?= $fetch_cart['quantity']; ?></span></p>
                <?php
            }
         }else{
            echo '<p class="empty">your cart is empty!</p>';
         }
      ?>
                <p class="grand-total"><span class="name">grand total :</span><span
                        class="price">TK<?= $grand_total; ?></span></p>
                <p class="weight-charge"><span class="name">weight charge :</span><span
                        class="price">TK<?= $weight_charge; ?></span></p>
                <a href="cart.php" class="btn">veiw cart</a>
            </div>

            <input type="hidden" name="total_products" value="<?= $total_products; ?>">
            <input type="hidden" name="total_price" value="<?= $grand_total; ?>" value="">
            <input type="hidden" name="name" value="<?= $fetch_profile['name'] ?>">
            <input type="hidden" name="number" value="<?= $fetch_profile['number'] ?>">
            <input type="hidden" name="email" value="<?= $fetch_profile['email'] ?>">
            <input type="hidden" name="address" value="<?= $fetch_profile['address'] ?>">
            <input type="hidden" id="deliveryChargeInput" name="deliveryCharge" value="">
            <input type="hidden" id="weight" name="weight" value="<?= $weight_charge; ?>">


            <div class="user-info">
                <h3>your info</h3>
                <p><i class="fas fa-user"></i><span><?= $fetch_profile['name'] ?></span></p>
                <p><i class="fas fa-phone"></i><span><?= $fetch_profile['number'] ?></span></p>
                <p><i class="fas fa-envelope"></i><span><?= $fetch_profile['email'] ?></span></p>
                <a href="update_profile.php" class="btn">update info</a>
                <h3>delivery address</h3>
                <p><i
                        class="fas fa-map-marker-alt"></i><span><?php if($fetch_profile['address'] == ''){echo 'please enter your address';}else{echo $fetch_profile['address'];} ?></span>
                </p>
                <a href="update_address.php" class="btn">update address</a>

                <br>
                <br><br>
                <input type="radio" id="getLocationRadio" name="locationOption" value="get_location"
                    onclick="getLocation()" required>
                <label for="getLocationRadio">Confirm Your Location</label><br>


                <div id="distance"></div>
                <div id="deliveryCost"></div>
                <div id="weightCost"></div>



                <select name="method" class="box" required>
                    <option value="" disabled selected>select payment method --</option>
                    <option value="cash on delivery">cash on delivery</option>
                    <option value="credit card">credit card</option>
                    <option value="paytm">paytm</option>
                    <option value="paypal">paypal</option>
                </select>
                <input type="submit" value="place order"
                    class="btn <?php if($fetch_profile['address'] == ''){echo 'disabled';} ?>"
                    style="width:100%; background:var(--red); color:var(--white);" name="submit">
            </div>

        </form>

    </section>


    <script>
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showDistance);
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    }

    function showDistance(position) {
        const userLatitude = position.coords.latitude;
        const userLongitude = position.coords.longitude;

        const givenLatitude = 24.7105776 //Nirala
        const givenLongitude = 88.9413865 //Nirala

        const distance = calculateDistance(userLatitude, userLongitude, givenLatitude, givenLongitude);
        const deliveryCharge = calculateDeliveryCharge(distance);

        var wc_ = <?php echo $weight_charge; ?>;
        var gt = <?php echo $grand_total; ?>;

        const total = deliveryCharge + wc_ + gt;

        document.getElementById("distance").innerHTML = `<p> Distance: ${distance.toFixed(2)} kilometers </p>`;
        document.getElementById("deliveryCost").innerHTML = `<p> Delivery Charge: ${deliveryCharge}taka </p>`;
        document.getElementById("deliveryChargeInput").value = deliveryCharge;
        document.getElementById("weightCost").innerHTML = `<p> Total Charge: ${total}taka</p>`;
    }

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Radius of the Earth in kilometers
        const dLat = deg2rad(lat2 - lat1);
        const dLon = deg2rad(lon2 - lon1);
        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        const distance = (R * c) / 100; // Distance in km
        return distance;
    }

    function calculateDeliveryCharge(distance) {
        const chargePer1km = 20;
        let charge = 0;
        const x = Math.ceil(distance);
        if (x == 1) {
            charge = 0;
        } else {
            charge = x * chargePer1km;
        }

        return charge;
    }

    function deg2rad(deg) {
        return deg * (Math.PI / 180);
    }
    </script>

    <!-- footer section starts  -->
    <?php include 'components/footer.php'; ?>
    <!-- footer section ends -->

    <!-- custom js file link  -->
    <script src="js/script.js"></script>

</body>

</html>
<!-- 
var wc_ = <?php echo $weight_charge; ?>;
var gt = <?php echo $grand_total; ?>;

<div id="weightCost"></div> -->

<!-- const tot = deliveryCharge + wc_ + gt;
document.getElementById("weightCost").innerHTML = `<p> Total Charge: ${tot}taka </p>`; -->