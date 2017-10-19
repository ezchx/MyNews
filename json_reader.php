<?


// verify password
$pass = $_GET['pass'];

if($pass != '***') {
  header('Location: https://www.google.com/');
}


// initialize variables
$api_key = "";
$servername = "";
$username = "";
$password = "";
$database = "";
$table = "";


// retrieve sources
$rows   = array_map('str_getcsv', file('/home/ezchecks/public_html/mynews/sources.csv'));
$header = array_shift($rows);
$csv    = array();
foreach($rows as $row) {
  $sources[] = array_combine($header, $row);
}


// connect to database
$conn = mysqli_connect($servername, $username, $password, $database);
$query = "SET NAMES utf8";
mysqli_query($conn, $query);


// clear old articles
$query = "TRUNCATE TABLE $table";
mysqli_query($conn, $query);


// upload new articles
foreach ($sources as $source) {

  // retrieve JSON data
  $json = file_get_contents("https://newsapi.org/v1/articles?source=$source[url]&sortBy=top&apiKey=$api_key");
  $obj = json_decode($json);
  
  // upload to database
  foreach ($obj->articles as $value) {

    $author = $value->author;
    $title = mysqli_real_escape_string($conn, $value->title);
    $description = mysqli_real_escape_string($conn, $value->description);
    $url = $value->url;
    $urlToImage = $value->urlToImage;
    // if (!@getimagesize($urlToImage)) {$urlToImage = "";}
    if (strtotime(gmdate("Y-m-d H:i:s").' UTC') > strtotime($value->publishedAt.' UTC') and strtotime(gmdate("Y-m-d H:i:s", strtotime('-24 hours')).' UTC') < strtotime($value->publishedAt.' UTC')) {
      $publishedAt = $value->publishedAt;
    } else {
      $publishedAt = 0;
    }
    
    $query = "SELECT * FROM $table WHERE `title` = '$title'";
    $result = mysqli_query($conn, $query);
    $num = mysqli_num_rows($result);
    
    if ($num == 0 and $publishedAt != 0) {
        $query = "INSERT INTO $table VALUES ('', '$source[display]', '$author', '$title', '$description', '$url', '$urlToImage', '$publishedAt', '', '', '', '', '', '', '', '', '', 0)";
        mysqli_query($conn, $query);
    }
  
  }
  
}


// show number of uploaded articles
$query = "SELECT COUNT(*) FROM $table";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_array($result);
echo "Total articles: " . $row[0];


// close connection
mysqli_close($conn);




?>

