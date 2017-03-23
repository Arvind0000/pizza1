<!-- This file will enable you to enter new toppings, and see existing toppings in the database -->

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

        
        <title>Toppings</title>
    </head>
    
    <body>


<?php
// check if form data needs to be processed

// include config and utils files
include_once('config.php');
include_once('dbutils.php');

if (isset($_POST['submit'])) {
    // if we are here, it means that the form was submitted and we need to process form data
    
    // get data from form
    $name = $_POST['name'];
    $vegetarian = $_POST['vegetarian'];
    $vegan = $_POST['vegan'];
    $glutenfree = $_POST['glutenfree'];
    $lactosefree = $_POST['lactosefree'];
    
    // variable to keep track if the form is complete (set to false if there are any issues with data)
    $isComplete = true;
    
    // error message we'll give user in case there are issues with data
    $errorMessage = "";
    
    // check each of the required variables in the table
    if (!$name) {
        $errorMessage .= "Please enter a name for the topping.\n";
        $isComplete = false;
    } else {
        // if there's a name specified, make sure it's not already in the database for toppings
        
        // connect to the database
        $db = connectDB($DBHost, $DBUser, $DBPasswd, $DBName);
        
        // set up query to check if the name is already used
        $query = "SELECT name FROM topping WHERE name='$name';";
        
        // run the query
        $result = queryDB($query, $db);
        
        // check if we got any records returned
        if (nTuples($result) > 0) {
            // this means the name is already in use and we need to generate an error
            $isComplete = false;
            $errorMessage .= "The topping $name is already in the database.\n";
        }
    }
    // Stop execution and show error if the form is not complete
    if($isComplete) {
    
        // put together SQL statement to insert new record
        $query = "INSERT INTO topping(name, vegetarian, vegan, glutenfree, lactosefree) VALUES ('$name', $vegetarian, $vegan, $glutenfree, $lactosefree);";
                
        // run the insert statement
        $result = queryDB($query, $db);
        
        // we have successfully entered the data
        echo ("Successfully entered new topping: " . $name);
        
        // reset variables so we can reset the form since we've successfully added a record
        unset($isComplete, $errorMessage, $name, $vegetarian, $vegan, $glutenfree, $lactosefree);
    }
}

?>

<!-- Menu bar -->
<nav class="navbar navbar-default">
  <div class="container-fluid">
    <ul class="nav navbar-nav navbar-left">
        <li><a href="pizza.php">pizzas</a></li>
        <li  class="active"><a href="topping.php">toppings</a></li>
     </ul>
     <ul class="nav navbar-nav navbar-right">
        <li><a href="logout.php">log out</a></li>
     </ul>
  </div>
</nav>


<!-- Title -->
<div class="row">
    <div class="col-xs-12">
        <h1>Toppings</h1>        
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



<!-- form to enter new toppings -->
<div class="row">
    <div class="col-xs-12">
        
<form action="topping.php" method="post">
<!-- name -->
<div class="form-group">
    <label for="name">Name:</label>
    <input type="text" class="form-control" name="name" value="<?php if($name) { echo $name; } ?>"/>
</div>


<!-- vegetarian -->
<div class="form-group">
    <label for="vegetarian">Vegetarian:</label>
    <label class="radio-inline">
        <input type="radio" name="vegetarian" value="1" <?php if($vegetarian && isset($vegetarian)) { echo 'checked'; } ?>> Yes
    </label>    
    <label class="radio-inline">
        <input type="radio" name="vegetarian" value="0" <?php if(!$vegetarian || !isset($vegetarian)) { echo 'checked'; } ?>> No
    </label>    
</div>

<!-- vegan -->
<div class="form-group">
    <label for="vegan">Vegan:</label>
    <label class="radio-inline">
        <input type="radio" name="vegan" value="1" <?php if($vegan && isset($vegan)) { echo 'checked'; } ?>> Yes
    </label>    
    <label class="radio-inline">
        <input type="radio" name="vegan" value="0" <?php if(!$vegan || !isset($vegan)) { echo 'checked'; } ?>> No
    </label>    
</div>

<!-- glutenfree -->
<div class="form-group">
    <label for="glutenfree">Gluten Free:</label>
    <label class="radio-inline">
        <input type="radio" name="glutenfree" value="1" <?php if($glutenfree && isset($glutenfree)) { echo 'checked'; } ?>> Yes
    </label>    
    <label class="radio-inline">
        <input type="radio" name="glutenfree" value="0" <?php if(!$glutenfree || !isset($glutenfree)) { echo 'checked'; } ?>> No
    </label>    
</div>

<!-- lactosefree -->
<div class="form-group">
    <label for="lactosefree">Lactose Free:</label>
    <label class="radio-inline">
        <input type="radio" name="lactosefree" value="1" <?php if($lactosefree && isset($lactosefree)) { echo 'checked'; } ?>> Yes
    </label>    
    <label class="radio-inline">
        <input type="radio" name="lactosefree" value="0" <?php if(!$lactosefree || !isset($lactosefree)) { echo 'checked'; } ?>> No
    </label>    
</div>


<button type="submit" class="btn btn-default" name="submit">Save</button>

</form>
        
        
    </div>
</div>







<!-- show contents of toppings table -->
<div class="row">
    <div class="col-xs-12">
        
<!-- set up html table to show contents -->
<table class="table table-hover">
    <!-- headers for table -->
    <thead>
        <th>Name</th>
        <th>Vegetarian</th>
        <th>Vegan</th>
        <th>Gluten Free</th>
        <th>Lactose Free</th>
    </thead>

<?php
    /*
     * List all the toppings in the database
     *
     */
    
    // connect to the database
    $db = connectDB($DBHost, $DBUser, $DBPasswd, $DBName);
    
    // set up a query to get information on the toppings from the database
    $query = 'SELECT * FROM topping ORDER BY name;';
    
    // run the query
    $result = queryDB($query, $db);
    
    while($row = nextTuple($result)) {
        echo "\n <tr>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . printBoolean($row['vegetarian']) . "</td>";
        echo "<td>" . printBoolean($row['vegan']) . "</td>";
        echo "<td>" . printBoolean($row['glutenfree']) . "</td>";
        echo "<td>" . printBoolean($row['lactosefree']) . "</td>";
        echo "</tr> \n";
    }
?>        
    
</table>
        
    </div>
</div>


    </body>
</html>