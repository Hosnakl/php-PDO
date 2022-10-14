<?php
    error_reporting(E_ALL ^ E_NOTICE); 
    session_start();
    extract($_POST);
    if(!isset($_SESSION["login"])){
        header("Location: Login.php");
        exit();
    }

    //connect to DB
    include("./Common/config/db.php");
    $pdo = connect();

    $id = $_SESSION["id"] ?? "";

    //get a list of students from DB
    if($id){
        $sqlName = "SELECT Name FROM Student WHERE StudentId = '$id'";
        $nameSet = $pdo -> query($sqlName);
        $row = $nameSet -> fetch(PDO::FETCH_ASSOC);
        if($row){
            $name = $row['Name'];
        }
    }

    //get a list of semester
    $sqlSemester = "SELECT * FROM Semester";
    $semesterSet = $pdo -> query($sqlSemester);
    foreach($semesterSet as $row){
        $semesterList[] = $row['SemesterCode']; 
    }


    if(isset($semesterBtn)){
        $_SESSION["semester"] = $semester;
    } else{
        $semester = $_SESSION["semester"] ?? $semesterList[0]; 
    }


    //Weekly hours
    $sqlHours = "SELECT SUM(c.WeeklyHours) AS hours
                 FROM Registration r INNER JOIN Course c on r.CourseCode = c.CourseCode
                 WHERE r.StudentId = '$id' AND r.SemesterCode = '$semester';";
    $hoursSet = $pdo -> query($sqlHours);
    $row = $hoursSet -> fetch(PDO::FETCH_ASSOC);
    if($row){
        $hours = $row['hours'] ?? 0;
        $remainingHours = 16 - $hours;
    }


    $hoursChecked = 0;
    if(isset($submit)){
        if(!isset($checkbox)){
            $errorMsg = "You need select at least one course!";
        }else{
            foreach($checkbox as $name => $value){
                $hoursChecked += $value;
            }
            if($hoursChecked > $remainingHours){
                $errorMsg = "Your selection exceed the max weekly hours";
            }
            else{
                $errorMsg = "";
                $sqlRegister = "INSERT INTO Registration (StudentId, CourseCode, SemesterCode) VALUES (?,?,?)";
                
                $preparedStmt = $pdo -> prepare($sqlRegister);
                foreach($checkbox as $name => $value){
                    $preparedStmt -> execute([$id, $name, $semester]);
                }
                header("Location: CourseSelection.php");
            }
        }
    }


    if(isset($clear)){
        $errorMsg = "";
        $checkbox = "";
    }


    include("./Common/Header.php");
    print <<<HTML
    <div class="container">
        <h1>Course Selection</h1>
        <p>Welcome <span style='font-weight: bold;'>$name</span> (not you? change user <a href="Login.php">here</a>)</p>
        <p>You have registered <span style='font-weight: bold;'>$hours</span> hours for the selected semester.</p>
        <p>You can register <span style='font-weight: bold;'>$remainingHours</span> more hours of course(s) for the semester.</p>
        <p>Please note that the courses you have registered will not be displayed in the list</p>  
        <form action="CourseSelection.php" method="post"> 
        <div class="row col-md-4">
            <select name="semester" id="semester" class="form-control">
    HTML;

    //get a dropdown list of semesters from DB
    $sqlSemester = "SELECT * FROM Semester";
    $semesterSet = $pdo -> query($sqlSemester);
    foreach($semesterSet as $row){
        $selected = $semester == $row['SemesterCode'] ? "selected" : "";
        echo "<option value='{$row['SemesterCode']}' $selected>{$row['Year']} {$row['Term']}</option>";
    }


    print <<<HTML
            </select>
            <input type="submit" id="semesterBtn" name="semesterBtn" value="semesterBtn" hidden>  
            <span class="errorMsg">$errorMsg</span>       
            <script>
                document.getElementById("semester").addEventListener("change", function(){
                    document.getElementById("semesterBtn").click();
                })
            </script>            
        </div>
        <br>
        <table class='table' style='margin-top: 30px;'>
            <tr>
                <th>Code</th>
                <th>Course Title</th>
                <th>Hours</th>
                <th>Select</th>
            </tr>
    HTML;


    $sqlCourse = "SELECT co.CourseCode, c.Title, c.WeeklyHours
                  FROM CourseOffer co INNER JOIN Course c ON co.CourseCode = c.CourseCode
                  LEFT OUTER JOIN (SELECT * FROM Registration WHERE StudentId = '$id')  r on co.CourseCode = r.CourseCode
                  WHERE co.SemesterCode = '$semester'  AND r.StudentId IS null;";
    $courseSet = $pdo -> query($sqlCourse);
    foreach($courseSet as $row){
        print <<<table_body
            <tr>
                <td>{$row['CourseCode']}</td>
                <td>{$row['Title']}</td>
                <td>{$row['WeeklyHours']}</td>
                <td><input type="checkbox" name="checkbox[{$row['CourseCode']}]" value="{$row['WeeklyHours']}"></td>
                <!-- <input type="hidden" name="weeklyHours[{$row['CourseCode']}]" value="{$row['WeeklyHours']}"> -->
            </tr>
        table_body;
    }

    print <<<HTML
            </table>
            <input type="submit" name="submit" value="Submit" class="btn btn-primary">
            <input type="submit" name="clear" value="Clear" class="btn btn-primary">
        </form>
    </div>
    HTML;
    include("./Common/Footer.php");
?>