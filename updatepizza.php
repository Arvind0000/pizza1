<?php
/*
 * This php file enables users to edit a particular pizza
 * It obtains the id for the pizza to update from an id variable passed using the GET method (in the url)
 *
 */
    include_once('config.php');
    include_once('dbutils.php');
    
    /*
     * If the user submitted the form with updates, we process the form with this block of code
     *
     */
    if (isset($_POST['submit'])) {
        // process the update if the form was submitted
        
        // get data from form
        $id = $_POST['id'];
        if (!isset($id)) {
            // if for some reason the id didn't post, kick them back to pizza.php
            header('Location: pizza.php');
            exit;
        }

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
        
        // If there's an error, they'll go back to the form so they can fix it
        
        if($isComplete) {
            // if there's no error, then we need to update
            
            //
            // first update pizza record
            //
            // put together SQL statement to update pizza
            $query = "UPDATE pizza SET shapeid=$shapeid, crust='$crust', size='$size', cheese=$cheese, name='$name' WHERE id=$id;";
            
            // connect to the database
            $db = connectDB($DBHost, $DBUser, $DBPasswd, $DBName);
            
            // run the update
            $result = queryDB($query, $db);            
                    
            //
            // now we need to update the toppings
            //
            
            // first we'll delete all existing toppings for this pizza
            $query = "DELETE FROM pizzatopping WHERE pizzaid=$id;";
            queryDB($query, $db);
                        
            // now for each topping currently selected, enter a record in the pizzatopping table
            foreach ($toppings as $toppingid) {
                // set up insert query
                $query = "INSERT INTO pizzatopping(pizzaid, toppingid) VALUES ($id, $toppingid);";
                
                // run insert query
                $result = queryDB($query, $db);
            }
            
            // now that we are done, send user back to pizza.php and exit 
            header("Location: pizza.php?successmessage=Successfully updated pizza $name");
            exit;
        }        
    } else {
        //
        // if the form was not submitted (first time in)
        //
    
        /*
         * Check if a GET variable was passed with the id for the pizza
         *
         */
        if(!isset($_GET['id'])) {
            // if the id was not passed through the url
            
            // send them out to pizza.php and stop executing code in this page
            header('Location: pizza.php');
            exit;
        }
        
        /*
         * Now we'll check to make sure the id passed through the GET variable matches the id of a pizza in the database
         */
        
        // connect to the database
        $db = connectDB($DBHost, $DBUser, $DBPasswd, $DBName);
        
        // set up a query
        $id = $_GET['id'];
        $query = "SELECT * FROM pizza WHERE id=$id;";
        
        // run the query
        $result = queryDB($query, $db);
        
        // if the id is not in the pizza table, then we need to send the user back to pizza.php
        if (nTuples($result) == 0) {
            // send them out to pizza.php and stop executing code in this page
            header('Location: pizza.php');
            exit;
        }
        
        /*
         * Now we know we got a valid pizza id through the GET variable
         */
        
        // get data on pizza to fill out form with existing values
        $row = nextTuple($result);
        
        $name = $row['name'];
        $shapeid = $row['shapeid'];
        $crust = $row['crust'];
        $size = $row['size'];
        $cheese = $row['cheese'];
        
        // to get the toppings, we need a second query so we can put them in the $toppings array
        $queryToppings = "SELECT * FROM pizzatopping WHERE pizzaid=$id;";
        $toppingResult = queryDB($queryToppings, $db);
        
        $i = 0;
        while ($toppingRow = nextTuple($toppingResult)) {
            $toppings[$i] = $toppingRow['toppingid'];
            $i++;
        }
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
        
        <title>Update pizza <?php echo $name; ?></title>
    </head>
    
    <body>
       
<!-- Title -->
<div class="row">
    <div class="col-xs-12">
        <h1>Update pizza <?php echo $name ?></h1>        
    </div>
</div>


<!-- Showing errors, if any -->
<div class="row">
    <div class="col-xs-12">
<?php
    if (isset($isComplete) && !$isComplete) {
        // executes only if form was previously submitted (and therefore $isComplete is set) and isComplete was set to false
        // you'll never be here if the form wasn't submitted (the first time you get in)
        
        echo '<div class="alert alert-danger" role="alert">';
        echo ($errorMessage);
        echo '</div>';
    }
?>            
    </div>
</div>



<!-- form to update pizza -->
<div class="row">
    <div class="col-xs-12">
        
<form action="updatepizza.php" method="post">
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

<!-- hidden id (not visible to user, but need to be part of form submission so we know which pizza we are updating -->
<input type="hidden" name="id" value="<?php echo $id; ?>"/>

<button type="submit" class="btn btn-default" name="submit">Save</button>

</form>
        
        
    </div>
</div>

       
       
        
    </body>
</html>