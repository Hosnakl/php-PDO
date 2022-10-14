<?php
include_once 'upload.php';
?>
<!DOCTYPE html>
<html lang="en-US">
    <head>
        <title>Upload your pictures</title>
        <meta charset="utf-8">
    </head>
    <body>
        <div>
            <?php if(!empty($statusMsg)){ ?>
            <p><?php echo $statusMsg; ?></p>
            <?php } ?>
            
            <form action="" method="post" enctype="multipart/form-data">
                <label>Select image files...<label>
                        <input type="file" name="files[]" multiple>
                        <input type="submit" name="submit" value="UPLOAD">
    
            </form>
        </div>
        <!--Display images --->
        <div>
            <?php
            //get images from db
            $sql = "SELECT * FROM images ORDER BY id DESC";
            //$query = $db -> query($sql);
            $pdo -> prepare($sql);
            
            if($query -> num_rows > 0){
                while($row = $query -> fetch_assoc()){
                    $imageURL = 'uploads/'.$row["file_name"];
                    ?>
            <img src="<?php echo $imageURL; ?>" alt="" />
                    <?php
                }
            }else{
                echo '<p>No image found</p>';
            }
            ?>
        </div>
        
    </body>
</html>
