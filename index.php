<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$servername = "";
$username = "";
$password = "";
$database = "";
$cookie_name = "";
date_default_timezone_set("America/New_York");


// connect to database
$conn = mysqli_connect($servername, $username, $password, $database);
$query = "SET NAMES utf8";
mysqli_query($conn, $query);


// check user cookie
if(isset($_COOKIE[$cookie_name])) {

    // existing user
    $user_id = $_COOKIE[$cookie_name];
    
} else {

    // new user
    $query = "SELECT MAX(`user_id`) FROM `users`";
    $result = mysqli_query($conn, $query);
    $max_user_id = mysqli_fetch_array($result);
    $user_id = $max_user_id[0] + 1;
    $query = "INSERT INTO `users` VALUES ('', '$user_id', '', '1', now())";
    mysqli_query($conn, $query);
    $query = "INSERT INTO `users_collab` VALUES ('', '$user_id', '', '0')";
    mysqli_query($conn, $query);
    //mysqli_close($conn);
    $expire = time() + (10 * 365 * 24 * 60 * 60);
    setcookie($cookie_name, $user_id, $expire, "/", ".ezchx.com");

}


//echo "Value is: " . $_COOKIE[$cookie_name];


// set query
if(isset($_GET['q'])) {

  $q = $_GET['q'];
  $query = "SELECT * FROM `articles` WHERE (`description` LIKE '%{$q}%' || `title` LIKE '%{$q}%') ORDER BY publishedAt DESC";
     
} else {

  $query = "SELECT * FROM `articles` ORDER BY publishedAt DESC";
  $q = "Search";

}


// retrieve articles
$articles = mysqli_query($conn, $query);
$num_articles = mysqli_num_rows($articles);


// retrieve user information
$query = "SELECT * FROM `users_collab` WHERE `user_id` = $user_id ORDER BY `ref` ASC";
$user_keywords = mysqli_query($conn, $query);
$num_user_keywords = mysqli_num_rows($user_keywords);

mysqli_close($conn);


// retrieve article keywords
for ($i = 0; $i < $num_articles; $i++) {
    $row = mysqli_fetch_array($articles);
    $article_keywords[$i] = [$row['keyword1'], $row['keyword2'], $row['keyword3'], $row['keyword4'], $row['keyword5'], $row['keyword6'], $row['keyword7'], $row['keyword8'], $row['keyword9']];
    $article_data[$i] = $row;
}


// retrieve user keywords
for ($i = 0; $i < $num_user_keywords; $i++) {
    $row = mysqli_fetch_array($user_keywords);
    $user_kws[$i] = $row['keyword'];
    $user_data[$i] = $row;
}


// calculate weights for each article
for ($i = 0; $i < $num_articles; $i++) {

    $common = array_intersect($article_keywords[$i], $user_kws);
    $weight = 0;
    if (sizeof($common) != 0) {

        foreach ($common as $commonWord) {
            $key = array_keys(array_column($user_data, 'keyword'), $commonWord);
            if ($user_data[$key[0]]['keyword'] != "") {
                $weight = $weight + $user_data[$key[0]]['frequency'];
            }
        }

    }
    $weight = round($weight*10, 0);
    if ($weight > 10) {$weight = 10;}
    $article_data[$i] = [$article_data[$i], "weight" => $weight];
    
}


// sort the articles by weight
usort($article_data, function ($a, $b)
{
    if ($a['weight'] != $b['weight']) {
        return ($a['weight'] < $b['weight']);
    } else {
        return ($a[0]['publishedAt'] < $b[0]['publishedAt']);
    }
});


// populate article arrays
for ($i = 0; $i < $num_articles; $i++) {

    $article_id[$i] = $article_data[$i][0]['ref'];
    $source[$i] = $article_data[$i][0]['source'];
    $author[$i] = $article_data[$i][0]['author'];
    $title[$i] = $article_data[$i][0]['title'];
    $description[$i] = str_replace('\"', '&quot;', $article_data[$i][0]['description']);
    $url[$i] = $article_data[$i][0]['url'];
    $urlToImage[$i] = $article_data[$i][0]['urlToImage'];
    $publishedAt[$i] = $article_data[$i][0]['publishedAt'];
    if ($publishedAt[$i] != 0) {
      $publishedAt[$i] = date('M d, Y h:i a', strtotime($article_data[$i][0]['publishedAt'].' UTC'));
    } else {
      $publishedAt[$i] = "null";
    }
    $article_weight[$i] = $article_data[$i]['weight'];
    $rating[$i] = $article_data[$i][0]['rating'];
}


?>

<!doctype html>

<html lang="en">

  <head>
  
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script type="text/javascript">

    // update user table for keywords on clicked articles
    $(document).ready(function(){
        $(".newbox").click(function(){

            $.ajax({
                url: "user_update.php",
                type: "POST",
                data: {user_id: <? echo $user_id; ?>, article_id: this.id}
            });
            
        });
    });

  </script>
  
  
    <title>MyNews</title>
    <meta name="description" content="Custom news feed.">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <link rel="stylesheet" type="text/css" href="stylesheet2.css">
    
    <style>
    
      
    </style>

  </head>
  

  <body>

    <div id="pagewrap">


        <table>
          <td>
            <a href="http://ezchx.com/mynews/"><h1>MyNews | <? echo $num_articles; ?> results</h1></a>
          </td>
          <td align="right">
            <form action="" method="get">
              <input type="text" size="25" name="q" id="site-search-input" autocomplete="off" <? if ($q == "Search") {echo "placeholder=\"$q\"";} else {echo "value=\"$q\"";} ?> class="gray" />
              <span id="g-search-button"></span>
            </form>
          </td>
        </table>

		
      <section id="content">
      
        <? for ($i = 0; $i < $num_articles; $i+=3) { ?>
      
          <div class="newbox" id="<? echo $article_id[$i]; ?>">
            <a href="<? echo $url[$i]; ?>" target="_blank">
              <img src="<? echo $urlToImage[$i]; ?>"></img>
              <h2><? echo $title[$i]; ?></h2>
            </a>
            <h5><? echo $source[$i]; if ($author[$i] != "") {echo " | " . $author[$i];} ?></h5>
            <h5><? if ($publishedAt[$i] != "null") {echo $publishedAt[$i] . " ET";} ?></h5>
            <h5><? echo "MyNews Score: " . $article_weight[$i]; ?></h5>
            <h5><? echo "Bias Score: " . $rating[$i]; ?></h5>
            <p><? echo $description[$i] . "..."; ?></p>
          </div>
		
        <? } ?>
		
      </section>
	
	
      <section id="middle">

        <? for ($i = 1; $i < $num_articles; $i+=3) { ?>
      
          <div class="newbox" id="<? echo $article_id[$i]; ?>">
            <a href="<? echo $url[$i]; ?>" target="_blank">
              <img src="<? echo $urlToImage[$i]; ?>"></img>
              <h2><? echo $title[$i]; ?></h2>
            </a>
            <h5><? echo $source[$i]; if ($author[$i] != "") {echo " | " . $author[$i];} ?></h5>
            <h5><? if ($publishedAt[$i] != "null") {echo $publishedAt[$i] . " ET";} ?></h5>
            <h5><? echo "MyNews Score: " . $article_weight[$i]; ?></h5>
            <h5><? echo "Bias Score: " . $rating[$i]; ?></h5>
            <p><? echo $description[$i] . "..."; ?></p>
          </div>
		
        <? } ?>

      </section>


      <aside id="sidebar">

        <? for ($i = 2; $i < $num_articles; $i+=3) { ?>
      
          <div class="newbox" id="<? echo $article_id[$i]; ?>">
            <a href="<? echo $url[$i]; ?>" target="_blank">
              <img src="<? echo $urlToImage[$i]; ?>"></img>
              <h2><? echo $title[$i]; ?></h2>
            </a>
            <h5><? echo $source[$i]; if ($author[$i] != "") {echo " | " . $author[$i];} ?></h5>
            <h5><? if ($publishedAt[$i] != "null") {echo $publishedAt[$i] . " ET";} ?></h5>
            <h5><? echo "MyNews Score: " . $article_weight[$i]; ?></h5>
            <h5><? echo "Bias Score: " . $rating[$i]; ?></h5>
            <p><? echo $description[$i] . "..."; ?></p>
          </div>
		
        <? } ?>

      </aside>
      
    
    <footer>
    
      <div align="center">
        <p id="footy">2017 Nitwit AI<br>
        <a href="https://newsapi.org/" target="_blank">News feed courtesy of News API</a></p>
      </div>
      
    </footer>
    
    </div>
    

  </body>
</html>

