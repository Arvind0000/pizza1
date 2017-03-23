<!-- This file will enable you to enter new pizzas, and see existing pizzas in the database -->

<?php
// this kicks users out if they are not logged in
    session_start();
    if (!isset($_SESSION['email'])) {
        header('Location: login.php');
        exit;
    }

?>


<html>
    <head>
<!-- Bootstrap links -->

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>        
        
        <title>Pizza!</title>
    </head>
    
    <body>

<!--

This is the php code to manage the data submitted by the form

-->
<?php
// check if form data needs to be processed

// include config and utils files
include_once('config.php');
include_once('dbutils.php');

if (isset($_POST['submit'])) {
    // if we are here, it means that the form was submitted and we need to process form data
    
    // get data from form
    $shapeid = $_POST['shape-id'];
    $crust = $_POST['crust'];
    $size = $_POST['size'];
    $cheese = $_POST['cheese'];
    $name = $_POST['name'];
    
    // get toppings selected by user in the form
    $toppings = $_POST['topping-id'];
    
    // variable to keep track if the form is complete (set to false if there are any issues with data)
    $isComplete = true;
    
    // error message we'll give user in case there are issues with data
    $errorMessage = "";
    
    // check each of the required variables in the table
    if (!isset($shapeid)) {
        $errorMessage .= "Please enter a shape for the pizza.\n";
        $isComplete = false;
    }
    
    if (!isset($crust) || (strlen($crust)==0)) {
        $errorMessage .= "Please enter a crust for the pizza.\n";
        $isComplete = false;
    }
    
    if (!isset($size)) {
        $errorMessage .= "Please enter a size for the pizza.\n";
        $isComplete = false;
    } else if ($size > 30 || $size < 6) {
        $errorMessage .="Please enter a size for a pizza between 6 and 30 inches.\n";
        $isComplete = false;
    }
    
    
    if (!isset($cheese)) {
        $errorMessage .= "Please enter whether the pizza has cheese.\n";
        $isComplete = false;
    }
    
    // Stop execution and show error if the form is not complete
    if($isComplete) {
        // if everything required is complete
                
        //
        // first enter record into pizza table
        //
        // put together SQL statement to insert new record
        $query = "INSERT INTO pizza(shapeid, crust, size, cheese, name) VALUES ('$shapeid', '$crust', $size, $cheese, '$name');";
        
        // connect to the database
        $db = connectDB($DBHost, $DBUser, $DBPasswd, $DBName);
        
        // run the insert statement
        $result = queryDB($query, $db);
        
        
                
        //
        // now we need to connect the new pizza record to its toppings        
        //
        
        // get the id for the pizza we just entered
        $pizzaid = mysqli_insert_id($db);
        
        // for each topping, enter a record in the pizzatopping table
        foreach ($toppings as $toppingid) {
            // set up insert query
            $query = "INSERT INTO pizzatopping(pizzaid, toppingid) VALUES ($pizzaid, $toppingid);";
            
            // run insert query
            $result = queryDB($query, $db);
        }
        
      	// check if there's a picture
        if ($_FILES['picture']['size'] > 0) {
            // if there is a picture
            
            // copy image to images directory
            $tmpName = $_FILES['picture']['tmp_name'];
            $fileName = $_FILES['picture']['name'];
            
            $newFileName = $imagesDir . $pizzaid . $fileName;
            
            // we create a filename that includes the pizza id, followed by the filename ($imagesDir comes from config.php)
            if (move_uploaded_file($tmpName, $newFileName)) {
                // since we successfully copied the file, we now enter its filename in the pizza table
                $query = "UPDATE pizza SET picture = '$newFileName' WHERE id=$pizzaid;";
            
                // run insert query
                queryDB($query, $db);
            } else {
                echo "error copying image";
            }
    	}


        // we have successfully entered the pizza and its toppings
        $success = "Successfully entered new pizza: " . $name;
        
        // reset values of variables so the form is cleared
        unset($shapeid, $crust, $size, $cheese, $name, $toppings);
    }
}

?>

<!-- Menu bar -->
<nav class="navbar navbar-default">
  <div class="container-fluid">
    <ul class="nav navbar-nav navbar-left">
        <li class="active"><a href="pizza.php">pizzas</a></li>
        <li><a href="topping.php">toppings</a></li>
     </ul>
     <ul class="nav navbar-nav navbar-right">
        <li><a href="logout.php">log out</a></li>
     </ul>
  </div>
</nav>

        
<!-- Title -->
<div class="row">
    <div class="col-xs-12">
        <h1>Pizzas</h1>        
    </div>
</div>

<!-- welcome user -->
<div class="row">
    <div class="col-xs-12">
        <p>Welcome <?php echo $_SESSION['email']; ?></p>
    </div>
</div>


<!-- Showing successfully entering pizza, if that actually happened -->
<div class="row">
    <div class="col-xs-12">
<?php
    if (isset($success)) {
        // for successes after the form was submitted
        echo '<div class="alert alert-success" role="alert">';
        echo ($success);
        echo '</div>';
    } elseif (isset($_GET['successmessage'])) {
        // for successes from another form that redirects users to this page
        echo '<div class="alert alert-success" role="alert">';
        echo ($_GET['successmessage']);
        echo '</div>';        
    }
?>            
    </div>
</div>


<!-- Showing errors, if any -->
<div class="row">
    <div class="col-xs-12">
<?php
    if (isset($isComplete) && !$isComplete) {
        echo '<div class="alert alert-danger" role="alert">';
        echo ($errorMessage);
        echo '</div>';
    }
?>            
    </div>
</div>



<!-- form to enter new pizzas -->
<div class="row">
    <div class="col-xs-12">
        
<form action="pizza.php" method="post" enctype="multipart/form-data">
<!-- name -->
<div class="form-group">
    <label for="name">Name:</label>
    <input type="text" class="form-control" name="name" value="<?php if($name) { echo $name; } ?>"/>
</div>

<!-- shape -->
<div class="form-group">
    <label for="shape-id">Shape:</label>
    <?php
    // connect to the database
    if (!isset($db)) {
        $db = connectDB($DBHost, $DBUser, $DBPasswd, $DBName);
    }
    echo (generateDropdown($db, "shape", "name", "id", $shapeid));        
    ?>
</div>


<!-- crust -->
<div class="form-group">
    <label for="crust">Crust:</label>
    <input type="text" class="form-control" name="crust" value="<?php if($crust) { echo $crust; } ?>"/>
</div>


<!-- size -->
<div class="form-group">
    <label for="size">Size in inches:</label>
    <input type="number" class="form-control" name="size" value="<?php if($size) { echo $size; } ?>"/>
</div>


<!-- cheese -->
<div class="form-group">
    <label for="cheese">Cheese:</label>
    <label class="radio-inline">
        <input type="radio" name="cheese" value="1" <?php if($cheese || !isset($cheese)) { echo 'checked'; } ?>> Yes
    </label>    
    <label class="radio-inline">
        <input type="radio" name="cheese" value="0" <?php if(!$cheese && isset($cheese)) { echo 'checked'; } ?>> No
    </label>    
</div>

<!-- toppings -->
<?php
    // connect to the database
    if (!isset($db)) {
        $db = connectDB($DBHost, $DBUser, $DBPasswd, $DBName);
    }
    echo (generateCheckboxes($db, "topping", "name", "id", $toppings));
?>

<!-- picture -->
<div class="form-group">
    <label for="picture">Picture of pizza</label>
    <input type="file" class="form-control" name="picture"/>
</div>

<button type="submit" class="btn btn-default" name="submit">Save</button>

</form>
        
        
    </div>
</div>



<!-- show contents of pizza table -->
<div class="row">
    <div class="col-xs-12">
        
<!-- set up html table to show contents -->
<table class="table table-hover">
    <!-- headers for table -->
    <thead>
        <th>Name</th>
        <th>Shape</th>
        <th>Crust</th>
        <th>Size</th>
        <th>Cheese</th>
        <th>Toppings</th>
        <th> </th>
        <th> </th>
        <th> </th>
    </thead>

<?php
    /*
     * List all the pizzas in the database
     *
     */
    
    // connect to the database
    $db = connectDB($DBHost, $DBUser, $DBPasswd, $DBName);
    
    // set up a query to get information on the pizzas from the database
    $query = 'SELECT pizza.id, pizza.name, shape.name as shape, crust, size, cheese, picture FROM pizza, shape WHERE pizza.shapeid = shape.id;';
    
    // run the query
    $result = queryDB($query, $db);
    
    while($row = nextTuple($result)) {
        echo "\n <tr>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['shape'] . "</td>";
        echo "<td>" . $row['crust'] . "</td>";
        echo "<td>" . $row['size'] . "</td>";
        if ($row['cheese']) {
            $cheese = 'Yes';
        } else {
            $cheese = 'No';
        }
        echo "<td>" . $cheese . "</td>";
        
        // get toppings
        $queryToppings = "SELECT topping.name FROM topping, pizzatopping WHERE pizzatopping.pizzaid=" . $row['id'] . " AND pizzatopping.toppingid = topping.id";
        $resultToppings = queryDB($queryToppings, $db);
             
        echo "<td>";
        
        $firstTopping = true;
        foreach($resultToppings as $toppingRow) {
            if (!$firstTopping) {
                // if it's not the first topping, add a comma and a space before listing the topping
                echo ", ";
            }
            echo $toppingRow['name'];
            $firstTopping = false;
        }
        
        echo "</td>";
        
        // picture
        echo "<td>";
        if ($row['picture']) {
            $imageLocation = $row['picture'];
            $altText = 'pizza ' . $row['name'];
            echo "<img src='$imageLocation' width='150' alt='$altText'>";
        }
        echo "</td>";
                
        // link to update record (pizza)
        echo "<td><a href='updatepizza.php?id=" . $row['id']  .  "'>edit</a></td>";
        
        // link to delete record (pizza)
        echo "<td><a href='deletepizza.php?id=" . $row['id']  .  "'>delete</a></td>";
        
        echo "</tr> \n";
    }
?>        
    
</table>
        
    </div>
</div>

    </body>
</html>

