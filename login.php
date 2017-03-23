<!-- This file enables users to login to the system -->
<!-- It also lists the contents of the table -->
<!-- It uses bootstrap for formatting -->

<!-- Author: Juan Pablo Hourcade -->

<?php
    include_once('config.php');
    include_once('dbutils.php');
    
?>

<html>
    <head>

<title>Login</title>

<!-- This is the code from bootstrap -->        
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        
    </head>
    
    <body>

<!-- Visible title -->
        <div class="row">
            <div class="col-xs-12">
                <h1>Login</h1>
            </div>
        </div>
        
<!-- Processing form input -->        
        <div class="row">
            <div class="col-xs-12">
<?php
//
// Code to handle input from form
//

if (isset($_POST['submit'])) {
    // only run if the form was submitted
    
    // get data from form
    $email = $_POST['email'];
	$password = $_POST['password'];
    
   // connect to the database
    $db = connectDB($DBHost, $DBUser, $DBPasswd, $DBName);    
    
    // check for required fields
    $isComplete = true;
    $errorMessage = "";
    
    if (!$email) {
        $errorMessage .= " Please enter an email.";
        $isComplete = false;
    } else {
        $email = makeStringSafe($db, $email);
    }

    if (!$password) {
        $errorMessage .= " Please enter a password.";
        $isComplete = false;
    }	    
	
    if (!$isComplete) {
        punt($errorMessage);
    }
    
    // get the hashed password from the user with the email that got entered
    $query = "SELECT hashedpass FROM users WHERE email='" . $email . "';";
    $result = queryDB($query, $db);
    if (nTuples($result) > 0) {
        // there is an account that corresponds to the email that the user entered
		// get the hashed password for that account
		$row = nextTuple($result);
		$hashedpass = $row['hashedpass'];
		
		// compare entered password to the password on the database
		if ($hashedpass == crypt($password, $hashedpass)) {
			// password was entered correctly
			
			// start a session
			if (session_start()) {
				$_SESSION['email'] = $email;
				header('Location: pizza.php');
				exit;
			} else {
				// if we can't start a session
				punt("Unable to start session when loggin in.");
			}
		} else {
			// wrong password
			punt("Wrong password. <a href='login.php'>Try again</a>.");
		}
    } else {
		// email entered is not in the users table
		punt("This email is not in our system. <a href='login.php'>Try again</a>.");
	}
}
?>
            </div>
        </div>

<!-- form for inputting data -->
        <div class="row">
            <div class="col-xs-12">
                
<form action="login.php" method="post">
<!-- email -->
    <div class="form-group">
        <label for="email">email</label>
        <input type="email" class="form-control" name="email"/>
    </div>

<!-- password1 -->
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" class="form-control" name="password"/>
    </div>

    <button type="submit" class="btn btn-default" name="submit">Login</button>
</form>
                
            </div>
        </div>
            
</div>        

        
    </body>
    
</html>