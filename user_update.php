<?

// initialize variables
$servername = "";
$username = "";
$password = "";
$database = "";


if (isset($_POST["user_id"]) && isset($_POST["article_id"])) {

    $user_id = $_POST["user_id"];
    $article_id = $_POST["article_id"];


    // connect to database
    $conn = mysqli_connect($servername, $username, $password, $database);
    
    // retrieve keywords
    $table = "articles";
    $query = "SELECT * FROM $table WHERE `ref` = '$article_id'";
    $result = mysqli_query($conn, $query);
    
    $row = mysqli_fetch_array($result);
    $keyword[1] = $row['keyword1'];
    $keyword[2] = $row['keyword2'];
    $keyword[3] = $row['keyword3'];
    $keyword[4] = $row['keyword4'];
    $keyword[5] = $row['keyword5'];
    $keyword[6] = $row['keyword6'];
    $keyword[7] = $row['keyword7'];
    $keyword[8] = $row['keyword8'];
    $keyword[9] = $row['keyword9'];
    
    // update user table
    $table = "users";
    for ($i = 1; $i <= 9; $i++) {
    
        if ($keyword[$i] != "") {    
            $query = "SELECT * FROM $table WHERE (`user_id` = '$user_id' && `keyword` = '$keyword[$i]')";
            $result = mysqli_query($conn, $query);
            $num = mysqli_num_rows($result);
        
            if ($num == 0) {
                #echo $keyword[$i] . "<br>";
                $query = "INSERT INTO $table VALUES ('', '$user_id', '$keyword[$i]', '1', now())";
            } else {
                #echo "match " . $keyword[$i] . "<br>";
                $query = "UPDATE $table SET `frequency` = `frequency` + 1, `lastClick` = now() WHERE (`user_id` = '$user_id' && `keyword` = '$keyword[$i]')";
            }
        
            mysqli_query($conn, $query);
        }
        
    }
           
    // close connection
    mysqli_close($conn);
    
}

?>
