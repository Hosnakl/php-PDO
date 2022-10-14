<?php
$cssFile="Login.css";
include "./Common/header.php";
include "./Common/config/db.php";

$StudentIdErr = '';
$PasswordErr = '';
$msgErr = '';
$StudentId = '';
$Password = '';

if (isset($_POST['_submit'])) {
    extract($_POST);
    if (isBlank($StudentId)) {
        $StudentIdErr = 'Student ID is not blank';
    } else if (isBlank($Password)) {
        $PasswordErr = 'Password is not blank';
    }else{
        $pdo = connect();
        if ($pdo != null) {
            $students = 0;
            try {
                $Password=hash("md5",$Password);
                $sql = "SELECT StudentId,Name FROM Student WHERE StudentId='{$StudentId}' AND Password='{$Password}' ";
                
                $studentSet = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                
                if (sizeof($studentSet) <1) {
                    $msgErr = 'Incorrect student ID and/or password!';
                    
                } else {
                    $_SESSION["studentSN"]=$StudentId;
                    $_SESSION["StudentId"]=$StudentId;
                    $_SESSION["StudentSet"]=$studentSet;
                    $_SESSION["Name"]=$studentSet[0]['Name'];
                    header("Location:course_select.php");
                    exit();
                }
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
    }
}


?>
    <div class="container">
        <h1>Log In</h1>
        <p>You need to <a href = "NewUser.php">sign up</a> if you are a new user</p>     
        <br/>
        <div class="warning">
            <span style="color:red"></span>
        </div>
    <?php
        print <<<HTML
        <form action="{$_SERVER['PHP_SELF']}" method="post">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label"> Student ID: </label>
                <input class="col-sm-2" type="text" name="StudentId" value="$StudentId"/>
                <span id="studentError" class="col-sm-4" style="color:red">$StudentIdErr</span>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label"> Password: </label>
                <input class="col-sm-2" type="password" name="Password" value="$Password"/> 
                <span id="passwordError" class="col-sm-4" style="color:red">$PasswordErr</span>                
            </div> 

            <div class="row">
                <div class="col-md-offset-1 col-md-2">
                    <input type="submit" name="_submit" value="Submit" class="btn btn-primary" />
                </div>
                <div class="col-md-offset-1 col-md-2">
                    <input type="submit" name="clear" value="Clear" class="btn btn-primary" />
                </div>
            </div>        
        </form>
        </div>
    </div>
 HTML;
    ?>
<?php
include "./Common/footer.php";
function isBlank($str)
{
    if (strlen($str) == 0) {
        return true;
    }
    return false;
}
?>