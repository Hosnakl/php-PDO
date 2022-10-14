<?php
    error_reporting(E_ALL ^ E_NOTICE); 
    session_start();
    extract($_POST);
    $idErr = "";
    $nameErr = "";
    $phoneErr = "";
    $passwordErr = "";
    $passwordAgainErr = "";

    //connect to  DB
    include("./Common/config/db.php");
    $pdo = connect();

    if(isset($submit)){  
        $sqlId = "SELECT StudentId FROM Student WHERE StudentId = :id";
        $preparedStatement1 = $pdo -> prepare($sqlId);
        $preparedStatement1 -> execute(['id'=>$id]);
        $row = $preparedStatement1 -> fetch(PDO::FETCH_ASSOC);
        if($row){
            $idErr = "A student with this ID has already signed up";
        }
        else{
            $idErr = ValidateId($id);
        }
        $nameErr = ValidateName($name);
        $phoneErr = ValidatePhone($phone);
        $passwordErr = ValidatePassword($Password);
        $passwordAgainErr = ValidatePasswordAgain($passwordAgain, $Password);

        if(!$idErr && !$nameErr && !$phoneErr && !$passwordErr && !$passwordAgainErr)
        {
            //store data in session
            $_SESSION["id"] = $id;
            $_SESSION["name"] = $name;
            $_SESSION["phone"] = $phone;
            $_SESSION["Password"] = $Password;
            $_SESSION["passwordAgain"] = $passwordAgain;

            //hash password
            $hashedPassword = hash("SHA1", $Password);

            //Inject to Db
            $sqlInsert = "INSERT INTO Student(StudentId,Name,Phone,Password) VALUES(:id,:name,:phone,:hashedPassword)";
            $preparedStatement2 = $pdo -> prepare($sqlInsert);
            $preparedStatement2 -> execute(['id'=>$id, 'name'=>$name, 'phone'=>$phone, 'hashedPassword'=>$hashedPassword]);
            $_SESSION["login"] = "true";
            header("Location: CourseSelection.php");
        }
    }
    else{
        $id = $_SESSION["id"] ?? "";
        $name = $_SESSION["name"] ?? "";
        $phone = $_SESSION["phone"] ?? "";
        $Password = $_SESSION["Password"] ?? "";
        $passwordAgain = $_SESSION["passwordAgain"] ?? "";
    }


    if(isset($clear)) {
        $id = '';
        $name = '';
        $phone = '';
        $Password = '';
        $passwordAgain = '';
    }


    include("./Common/Header.php");
    print <<<HTML
        <div class="container">
            <h1>Sign Up</h1>
            <p>All fields are required</p>
            <form action="NewUser.php" method="post">
                <div class="row form-group form-inline">
                    <label for="id" class="col-md-2">Student ID: </label>
                    <input type="text" id="id" name="id" class="form-control col-md-3" value="$id">
                    <span class="errorMsg">$idErr</span>
                </div>
                <div class="row form-group form-inline">
                    <label for="name" class="col-md-2">Name: </label>
                    <input type="text" id="name" name="name" class="form-control col-md-3" value="$name">
                    <span class="errorMsg">$nameErr</span>
                </div>
                <div class="row form-group form-inline">
                    <label for="phone" class="col-md-2">Phone Number: <br>(nnn-nnn-nnnn)</label>
                    <input type="text" id="phone" name="phone" class="form-control col-md-3" value="$phone">
                    <span class="errorMsg">$phoneErr</span>
                </div>
                <div class="row form-group form-inline">
                    <label for="Password" class="col-md-2">Password: </label>
                    <input type="password" id="Password" name="Password" class="form-control col-md-3" value="$Password">
                    <span class="errorMsg">$passwordErr</span>
                </div>
                <div class="row form-group form-inline">
                    <label for="passwordAgain" class="col-md-2">Password Again: </label>
                    <input type="password" id="passwordAgain" name="passwordAgain" class="form-control col-md-3" value="$passwordAgain">
                    <span class="errorMsg">$passwordAgainErr</span>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                <button type="submit" name="clear" class="btn btn-primary">Clear</button>
            </form>
        </div>
    HTML;
    include("./Common/Footer.php");

    function ValidateId($id): string
    {
        if(!trim($id))
        {
            return "Student ID can not be blank";
        }
        else
        {
            return "";
        }
    }

    function ValidateName($name): string
    {
        if(!trim($name))
        {
            return "Name can not be blank";
        }
        else
        {
            return "";
        }
    }

    function ValidatePhone($phone): string
    {
        $regex = "/^([2-9]\d{2})-([2-9]{3})-(\d{4})$/";
        if(!trim($phone))
        {
            return "Phone number can not be blank";
        }
        elseif(!preg_match($regex, $phone))
        {
            return "Incorrect phone number";
        }
        else
        {
            return "";
        }
    }

    function ValidatePassword($Password): string
    {
        $regex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/";
        if(!trim($Password))
        {
            return "Password can not be blank";
        }
        elseif(!preg_match($regex, $Password))
        {
            return "Password must be at least 6 characters long, contains at least one upper case, one lowercase and one digit";
        }
        else
        {
            return "";
        }
    }

    function ValidatePasswordAgain($passwordAgain, $Password): string
    {
        if($passwordAgain != $Password)
        {
            return "Password does not match";
        }
        else
        {
            return "";
        }
    }
?>
