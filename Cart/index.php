<?php
session_start(); //session to hold information and data
$connect = mysqli_connect("localhost", "root", "", "products");

if (isset($_POST["Add"])): // function to add item in the checkout table
    $new_id = $_GET["id"];
//array to call items in the database
    $item_array = [
        'item_id' => $new_id,
        'item_name' => $_POST["Prod_name"],
        'item_price' => $_POST["Prod_price"],
        'item_quantity' => $_POST["quantity"]
    ];
    if (isset($_SESSION["cart"]) && isset($_SESSION["cart"][$new_id])): //if product already been added quantity increase by 1
        $_SESSION["cart"][$new_id]['item_quantity'] += $item_array['item_quantity'];
    else:
        $_SESSION["cart"][$new_id] = $item_array; // if no product in cart , add item in cart
    endif;
endif;

if (isset($_GET["action"]) && $_GET["action"] == "delete"): //delete item in cart
    foreach ($_SESSION["cart"] as $item_id => $item):
        if ($item_id == $_GET["id"]):
            unset($_SESSION["cart"][$item_id]);
        endif;
    endforeach;
endif;

if(isset($_POST["couponbtn"])): //add coupon for discount
  $coupon=$_POST["coupon"];
  $query="SELECT * from discounts where code='$coupon'";
  $result = mysqli_query($connect, $query);


 if(mysqli_num_rows($result) > 0)//get database results for coupon and to compare codes that have been applied
{
  $sql="SELECT percentage FROM discounts WHERE code='$coupon'";
  $result = mysqli_query($connect, $sql);
  $row = mysqli_fetch_array($result);

}
else {
  echo '<script>alert("invalid coupon")</script>'; // if the coupon code is invalid it will shows error and redirected back to the site
  echo '<script>window.location="index.php"</script>';
}
endif;


//to show date and time of checkout
if (isset($_POST["chkout"])) {
  $date_clicked = date('Y-m-d H:i:s');

  echo '<script>alert("Time of checkout : '.date('Y-m-d H:i:s').'")</script>';
}



?>
<!DOCTYPE html>
<html>
     <head>
          <title>Food Store</title>

          <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
          <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
     </head>
     <body>
          <br /> <!-- Get database results for items -->
          <div class="container" style="width:700px;">
               <h3 align="center">Food Store</h3><br />
               <?php
               $query = "SELECT * FROM product ORDER BY id ASC";
               $result = mysqli_query($connect, $query);
               if(mysqli_num_rows($result) > 0)
               {
                    while($row = mysqli_fetch_array($result))
                    {
               ?>
               <div class="col-md-9"> <!-- To show list of items -->
                    <form method="post" action="index.php?action=add&id=<?php echo $row["id"]; ?>">
                         <div style="border:2px solid #333; background-color:#ADD8E6; border-radius:5px; padding:16px;"  >
                              <img src="<?php echo $row["image"]; ?>" class="img-responsive" /><br />
                              <h4 class="text-info"><?php echo $row["name"]; ?></h4>
                              <h4 class="text-danger">RM <?php echo $row["price"]; ?></h4>
                              <input type="text" name="quantity" class="form-control" value="1" />
                              <input type="hidden" name="Prod_name" value="<?php echo $row["name"]; ?>" />
                              <input type="hidden" name="Prod_price" value="<?php echo $row["price"]; ?>" />
                              <input type="submit" name="Add" style="margin-top:5px;" class="btn btn-success" value="Add to Cart" />
                         </div>
                    </form>
               </div>
               <?php
                    }
               }
               ?>
               <div style="clear:both"></div>
               <br />
               <h3>Order Details</h3>
               <div class="table-responsive">
                    <table class="table table-bordered">
                         <tr>
                              <th width="40%">Item Name</th>
                              <th width="10%">Quantity</th>
                              <th width="20%">Price</th>
                              <th width="15%">Total</th>
                              <th width="5%">Action</th>
                         </tr>
                         <?php //
                         if(!empty($_SESSION["cart"]))
                         {
                              $total = 0;
                              foreach($_SESSION["cart"] as $keys => $values)
                              {

                         ?>
                         <tr>     <!-- Table For checkout -->
                              <td><?php echo $values["item_name"]; ?></td>
                              <td><?php echo $values["item_quantity"]; ?></td>
                              <td>RM <?php echo $values["item_price"]; ?></td>
                              <td>RM <?php echo number_format($values["item_quantity"] * $values["item_price"], 2); ?></td>
                              <td><a href="index.php?action=delete&id=<?php echo $values["item_id"]; ?>"><span class="text-danger">Remove</span></a></td>
                         </tr>
                         <?php
                                  //calculation for total and total price in gst if there is no discount
                                   $total = $total + ($values["item_quantity"] * $values["item_price"]);
                                      $totaltax = $total * 1.06;
                              }
                         ?>
                         <tr>
                              <td colspan="3" align="right">Total</td>
                              <td align="right">RM <?php echo number_format($total, 2); ?></td>
                              <td></td>
                         </tr>
                          <?php //Get databse results for coupon codes
                          if(isset($_POST["couponbtn"])):
                          $coupon=$_POST["coupon"];
                          $sql="SELECT percentage FROM discounts WHERE code='$coupon'";
                          $result = mysqli_query($connect, $sql);
                          $row = mysqli_fetch_array($result);
                        if(mysqli_num_rows($result) > 0)
                        {
                            ?>
                         <tr>  <!-- Discount calculation and preview -->
                           <td colspan="3" align="right">Discount </td>
                           <td align="right" ><?php echo ($row["percentage"] * 100 ); ?> %</td>
                           <td></td>
                         </tr>
                          <?php
                          $totaltax = (($total - ($total * $row["percentage"])) * 1.06);
                        }

                         endif;
                        ?>
                         <!-- area for total inclusive gst and if there any discount it will be calculated here -->
                         <tr>
                           <td colspan="3" align="right">Total inclusive of 6% GST</td>
                           <td align="right">RM <?php echo number_format($totaltax, 2); ?></td>
                           <td></td>
                         </tr>
                         <?php

                       }

                         ?>
                         <!-- area for coupon and checkout button-->
                    </table>
                    <form class="form-inline" method="post" action="index.php?action=couponbtn">
                      <div class="form-group mx-sm-3 mb-2">
                        <label for="coupon" class="sr-only">-</label>
                        <input type="textarea" class="form-control" name="coupon"  placeholder="Enter Coupon">
                      </div>
                      <button type="submit" name="couponbtn"class="btn btn-primary mb-2">Confirm</button>
                      <button type="submit" name="chkout" class="btn btn-success">Checkout</button>
                   </form>

               </div>
          </div>
          <br />
     </body>
</html>
