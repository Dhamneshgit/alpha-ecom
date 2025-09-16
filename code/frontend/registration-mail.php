<?php 


ini_set('SMTP','localhost' );
ini_set('smtp_port',25 ,443);
/*$servername = "localhost";
$username = "alphafk6_landing";
$password = "TDcu+}QEivNi";
$dbname = "alphafk6_landingpage";
$conn = mysqli_connect($servername, $username, $password, $dbname);*/
// Check connection
/*if (!$conn) {
    die("Connection failed: " . $conn->connect_error);
}*/

if($_POST['phone']){

	$sender = 'info@alphawizz.in';
	$recipient = 'info@alphawizz.in';
	
	
	$subject = "Leads";


	$headers = 'From:' . $sender;

	$name = $_POST['name'];

	$phone = $_POST['phone'];
	$state = $_POST['state'];
//	$country = $_POST['country'];
	// $mob = $_POST['intl_tel-891'];

	//$email = $_POST['email'];
	$Textmessage = $_POST['message'];

    
   

    
	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= "X-Priority: 3\r\n";
	 
	// Create email headers
	$headers .= "From: <'.$sender.'>\r\n".
	    'Reply-To: '.$sender."\r\n" .
         'Cc: dhamnesh003@gmail.com' . "\r\n".
	    'X-Mailer: PHP/' . phpversion(); 
	// Compose a simple HTML email message
	$message = '<html><body>';
	$message .= '<h1 style="color:#f40;">Hello Admin</h1>';
	$message .= '<p style="color:#080;font-size:18px;">You have recived an enquiry from Taxi. Please check below details.</p>';
	$message .= '<p style="color:#080;font-size:18px;">Domain: Alpha Taxi</p>';
	$message .= '<p style="color:#080;font-size:18px;">name:' . $name .'</p>';
	$message .= '<p style="color:#080;font-size:18px;">Phone: +91-' . $phone .'</p>';
	$message .= '<p style="color:#080;font-size:18px;">State:' . $state .'</p>';
	//$message .= '<p style="color:#080;font-size:18px;">Email: ' . $email .'</p>';
	$message .= '<p style="color:#080;font-size:18px;">Message: ' . $Textmessage .'</p>';
	$message .= '<p style="color:#080;font-size:18px;">Ip Address: ' . $_SERVER["REMOTE_ADDR"] .'</p>';
	


	$message .= '<p style="color:#080;font-size:18px;">Thank You</p>';
	$message .= '<p style="color:#080;font-size:18px;">Support Team</p>';
	$message .= '</body></html>';

	if (mail($recipient, $subject, $message, $headers)){

		/**
		 * Curl for Leads
		 */
		// $curl = curl_init();

		// curl_setopt_array($curl, array(
		// 	CURLOPT_URL => "https://alphawizzcrm.com/index.php/cron/curl?name=$name&phone=$phone&state=$state&message=$Textmessage&ip_address=".$_SERVER["REMOTE_ADDR"],
		// 	CURLOPT_RETURNTRANSFER => true,
		// 	CURLOPT_ENCODING => '',
		// 	CURLOPT_MAXREDIRS => 10,
		// 	CURLOPT_TIMEOUT => 0,
		// 	CURLOPT_FOLLOWLOCATION => true,
		// 	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		// 	CURLOPT_CUSTOMREQUEST => 'GET'
		// ));
	//	echo file_get_contents("https://alphawizzcrm.com/index.php/cron/curl?name=$name&phone=$phone&state=$state&message=$Textmessage&ip_address=".$_SERVER["REMOTE_ADDR"]);
		// $ch = curl_init();
		// curl_setopt($ch, CURLOPT_URL, "https://alphawizzcrm.com/index.php/cron/curl?name=$name&phone=$phone&state=$state&message=$Textmessage&ip_address=".$_SERVER["REMOTE_ADDR"]);
		// curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// curl_setopt($ch, CURLOPT_HEADER, 0);
		// $output = curl_exec($ch);
		// curl_close($ch);
		// print_r($output);

		// $response = curl_exec($curl);
  
		// curl_close($curl);

		// echo $response;
		// exit;
		/**
		 * Curl for Leads
		 */

	   /*$sql = "INSERT INTO user (name, phone, email,lookingfor,budget,skype_id,ipaddress)
       VALUES ('".$name."', '".$phone."', '','".$Textmessage."','','','".$_SERVER["REMOTE_ADDR"]."')";
        if (mysqli_query($conn, $sql)) {
            //echo "New record created successfully";
        } else {
            //echo "Error: " . $sql . "<br>" . $conn->error;
        }*/
		$Message = urlencode("Thanks for sharing your details");
		header("Location: https://alphawizzserver.com/Alpha_Taxi/ThankYouGrocery.php");
		// header("Location: http://alphawizz.in");
	    
	}else{
		header("Location: https://alphawizzserver.com/Alpha_Taxi"); 
		exit; 
	}
// 	curl_close ($ch);
  
}
?> 
