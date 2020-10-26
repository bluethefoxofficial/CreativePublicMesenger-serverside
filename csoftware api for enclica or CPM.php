<?php

    //csoftware A.P.I

    //for messenger and login on the main web and CPM .



    function generateRandomString($length = 10) {

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $charactersLength = strlen($characters);

        $randomString = '';

        for ($i = 0; $i < $length; $i++) {

            $randomString .= $characters[rand(0, $charactersLength - 1)];

        }
        

        return $randomString;

    }



    //$user_id = $_GET['user_id']; currently unused

  

   





if( isset($_POST['key']) )
{
  $api_key = $_POST['key'];
   $function = $_POST['function'];
}else{
  $api_key = $_GET['key'];
   $function = $_GET['function'];
}


    if(!isset($api_key)){



        echo json_encode(

            array(

                'code' => 40956,

                'error' => 'No API key provided.',

                'data' => implode('', $_POST)

            ));



        die();

    }

    if(strlen($api_key) != 100){

        echo json_encode(

            array(

                'code' => 44344,

                'error' => 'API key is a invalid length.',

                'data' => null

            ));



        die();

    }

    include("../server/config.php");



$q = mysqli_query($link, "SELECT * FROM devkeys WHERE api_key='".$api_key."' AND is_valid=1");







if(mysqli_num_rows($q) < 0){

    echo json_encode(

        array(

            'code' => 44300,

            'error' => 'API key is a invalid.',

            'data' => null

        ));



    die();

}



if($function == ""){



    echo json_encode(

        array(

            'code' => 445656,

            'error' => 'Function is required.',

            'data' => null

        ));



    die();



}





//

//

//              Login  ===========================================

//

//





function login($link){

       

 //  include("../server/config.php");

    // Check if username is empty

    if(empty(trim($_GET["username"]))){

        

        echo json_encode(

            array(

                'code' => 1,

                'error' => 'Login | no username provided',

                'data' => null

            ));

    

        die();

    

    } else{

        $username = trim($_GET["username"]);

    }

    

    // Check if password is empty

    if(empty(trim($_GET["password"]))){

        echo json_encode(

            array(

                'code' => 2,

                'error' => 'Login | no password provided',

                'data' => null

            ));

    

        die();

    } else{

        $password = trim($_GET["password"]);

    }

    

    // Validate credentials

    if(empty($username_err) && empty($password_err)){

        // Prepare a select statement

        $sql = "SELECT id, username, password FROM users WHERE username = ?";

        

        if($stmt = mysqli_prepare($link, $sql)){

            // Bind variables to the prepared statement as parameters

            mysqli_stmt_bind_param($stmt, "s", $param_username);

            

            // Set parameters

            $param_username = $username;

            

            // Attempt to execute the prepared statement

            if(mysqli_stmt_execute($stmt)){

                // Store result

                mysqli_stmt_store_result($stmt);

                

                // Check if username exists, if yes then verify password

                if(mysqli_stmt_num_rows($stmt) == 1){                    

                    // Bind result variables

                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);

                    if(mysqli_stmt_fetch($stmt)){

                        if(password_verify($password, $hashed_password)){

                            // Password is correct, so start a new session

                            session_start();

                            

                            // Store data in session variables

                            $_SESSION["loggedin"] = true;

                            $_SESSION["id"] = $id;

                            $_SESSION["username"] = $username;                            

                            

                            // GIVES THE APPLICATION A TOKEN.



                            $randomtoken = generateRandomString(100);

                            $sql = "INSERT INTO tokens (userid, token, username) VALUES ('$id', '$randomtoken', '$username')";

                            if(mysqli_query($link, $sql)){

                                echo json_encode(

                                    array(

                                        'code' => 200,

                                        'data' => 'Successfully logged in.',

                                        'login_token' => $randomtoken

                                    ));

                            }else{

                                echo json_encode(

                                    array(

                                        'code' => 304,

                                        'error' => mysqli_error($link),

                                        'data' => null

                                    ));

                            }

                            

                        

                            die();

                        } else{

                            // Display an error message if password is not valid

                            echo json_encode(

                                array(

                                    'code' => 3,

                                    'error' => 'Login | password isnt valid',

                                    'data' => null

                                ));

                        

                            die();

                        }

                    }

                } else{

                    // Display an error message if username doesn't exist

                    echo json_encode(

                        array(

                            'code' => 3,

                            'error' => 'Login | no account with that username exists',

                            'data' => null

                        ));

                }

            } else{

                echo json_encode(

                    array(

                        'code' => 3,

                        'error' => 'Login | something went wrong try again later.',

                        'data' => null

                    ));

            }



            // Close statement

            mysqli_stmt_close($stmt);

        }

    }

    

    // Close connection

    mysqli_close($link);

    exit();

}





//

//

//              List groups  ===========================================

//

//



function listgroups($link){

   

  $token = strip_tags(stripslashes($_GET['token']));

    $sql = "SELECT * FROM tokens WHERE token = '$token'";

    $results = mysqli_query($link, $sql);

    $tempdataa = array();

    if (mysqli_num_rows($results) > 0) {

        // output data of each row

        while($row = mysqli_fetch_assoc($results)) {

$tempdataa = $row;

        }

        

    } else {

        echo json_encode(

            array(

                'code' => 58757,

                'error' => 'User token is invalid.',

                'data' => null

            ));

    }



    $groups = null;

    $un =  $tempdataa['username'];

    $groupsql = "SELECT * FROM `groups` WHERE `members` LIKE '%$un%'";

    $result = mysqli_query($link, $groupsql);



if (mysqli_num_rows($result) > 0) {

  // output data of each row

  while($roww = mysqli_fetch_assoc($result)) {

    $groups[] = $roww;

  }

  echo json_encode($groups);



} else {

    json_encode(

        array(

            'data' => 'you are not in any servers.'

        )

    );





}

}



//

//

//              user account control  ===========================================

//

//





function UAC($link){



    $token = strip_tags(stripslashes($_GET['token']));

    $sql = "SELECT * FROM tokens WHERE token = '$token'";

    $results = mysqli_query($link, $sql);

    $tempdata = array();

    if (mysqli_num_rows($results) > 0) {

        // output data of each row

        while($row = mysqli_fetch_assoc($results)) {

           $tempdata[] = $row;

        }

        

    } else {

        echo json_encode(

            array(

                'code' => 58757,

                'error' => 'User token is invalid.',

                'data' => null

            ));



    }

    $un =  $tempdata[0]['username'];

    $result = mysqli_query($link, "SELECT username, email, bio, ID FROM users WHERE username= '$un'");



    // output data of each row

    while($row = mysqli_fetch_assoc($result)) {

        echo json_encode($row);

            }



}













switch($function){


case "listgroups":

        listgroups($link);
    
break;

case "creategroup":

    groupcreation($link);
        
break;

case "joingroup":

    joingroup($link);
        
break;

case "UAC":

            UAC($link);

    break;

    case "login":

        login($link);

break;
case "test":

    test($link);

break;

case "updatebio":

updatebio($link);

break;
case "getmessages":

getmessages($link);

break;
case "sendmessage":

sendmessage($link);

break;
case "deletegroup":

    delete($link);
    
    break;
    case "pfpget":

        ppget($link);
        
    break;
    case "memberslist":

        memberslist($link);
        
    break;
    




default:

echo json_encode(

    array(

        'code' => 30000,

        'error' => 'invalid function',

        'data' => null

    ));


break;
}






//

//

//              update bio  ===========================================

//

//









function updatebio($link){

        $token = strip_tags(stripslashes($_GET['token']));

    $sql = "SELECT * FROM tokens WHERE token = '$token'";

    $results = mysqli_query($link, $sql);

    $tempdata = array();

    if (mysqli_num_rows($results) > 0) {

        // output data of each row

        while($row = mysqli_fetch_assoc($results)) {

           $tempdata[] = $row;

        }

        

    } else {

        echo json_encode(

            array(

                'code' => 58757,

                'error' => 'User token is invalid.',

                'data' => null

            ));

         

    }

    $un =  $tempdata[0]['username'];

    $biochanged = strip_tags(stripslashes($_GET['newbio']));

     mysqli_query($link, "UPDATE `users` SET `bio` = '$biochanged' WHERE `users`.`username` = `$un`; ");





        

}

//
//-----------------------
//
// Join group chat.
//
//



function joingroup($link){







    $token = strip_tags(stripslashes($_GET['token']));

    $sql = "SELECT * FROM tokens WHERE token = '$token'";

    $results = mysqli_query($link, $sql);

    $tempdata = array();

    if (mysqli_num_rows($results) > 0) {

        // output data of each row

        while($row = mysqli_fetch_assoc($results)) {

           $tempdata[] = $row;

        }

        

    } else {

        echo json_encode(

            array(

                'code' => 58757,

                'error' => 'User token is invalid.',

                'data' => null

            ));



    }

    $un =  $tempdata[0]['username'];
    $row = null;
    $invite = stripslashes(strip_tags($_GET['invite']));
    $result = mysqli_query($link, "SELECT * FROM `groups` WHERE `invite` LIKE '$invite'");
    
    

    $row = mysqli_fetch_assoc($result);
    $groupid = $row['ID'];
    $result = null;

    if(str_contains($row['members'], $un)){
        echo json_encode(

            array(
    
                'code' => 568999,
    
                'error' => 'Failed to join group you are already in it.',
    
                'data' => null,
    
                'refresh' => false,
    
            ));

        return;
    }

if ( mysqli_query($link, "UPDATE groups set members= concat(members,' $un') WHERE ID LIKE $groupid;")) {
    echo json_encode(

        array(


            'code' => 200,

            'data' => 'join successful',

            'refresh' => true

        ));
  } else {
    echo json_encode(

        array(

            'code' => 568999,

            'error' => 'Failed to join group you might of been banned or the group doesnt exist.',

            'data' => null,

            'refresh' => false,

        ));
  }
}


//

//

//              get messages  ===========================================

//

//



function getmessages($link){

   

  $token = strip_tags(stripslashes($_GET['token']));

    $sql = "SELECT * FROM tokens WHERE token = '$token' LIMIT 30";

    $results = mysqli_query($link, $sql);

    $tempdataa = array();

    if (mysqli_num_rows($results) > 0) {

        // output data of each row

        while($row = mysqli_fetch_assoc($results)) {

$tempdataa = $row;

        }

        

    } else {

        echo json_encode(

            array(

                'code' => 58757,

                'error' => 'User token is invalid.',

                'data' => null

            ));

    }



    $groups = null;

    $un =  $tempdataa['username'];
    $currentserver = strip_tags(stripcslashes($_GET['serverid']));

    $msgsql = "SELECT * FROM `messages` WHERE `serverid` LIKE '$currentserver'  ORDER BY `messages`.`time` ASC";

    $result = mysqli_query($link, $msgsql);



if (mysqli_num_rows($result) > 0) {

  // output data of each row

  while($roww = mysqli_fetch_assoc($result)) {

    $messages[] = $roww;

  }

  echo json_encode($messages);



} else {

    json_encode(

        array(

            'data' => 'No messages.'

        )

    );





}

}

//

//

//              send message  ===========================================

//

//



function sendmessage($link){

   

  $token = strip_tags(stripslashes($_POST['token']));

    $sql = "SELECT * FROM tokens WHERE token = '$token'";

    $results = mysqli_query($link, $sql);

    $tempdataa = array();

    if (mysqli_num_rows($results) > 0) {

        // output data of each row

        while($row = mysqli_fetch_assoc($results)) {

$tempdataa = $row;

        }

        

    } else {

        echo json_encode(

            array(

                'code' => 58757,

                'error' => 'User token is invalid.',

                'data' => null

            ));

    }



    $groups = null;

    $un =  $tempdataa['username'];

    $currentserver = strip_tags(stripcslashes($_POST['serverid']));
    $msg = strip_tags(stripcslashes($_POST['message']));
    if(msg == ""){
        return;
    }
    $randomNumber = rand(0,9999999); 
    $time = time();

    $msgsql = "INSERT INTO `messages` (`ID`, `sender`, `time`, `edited`, `chat_type`, `serverid`, `message`) VALUES ('$randomNumber', '$un', $time, '0', '1', '$currentserver', '$msg'); ";

    mysqli_query($link, $msgsql);


    echo json_encode($_POST);

}



//
//----------------------------------------------
//
//create group
//
//

function groupcreation($link){

    $token = strip_tags(stripslashes($_GET['token']));

    $sql = "SELECT * FROM tokens WHERE token = '$token'";

    $results = mysqli_query($link, $sql);

    $tempdata = array();

    if (mysqli_num_rows($results) > 0) {

        // output data of each row

        while($row = mysqli_fetch_assoc($results)) {

           $tempdata[] = $row;

        }

        

    } else {

        echo json_encode(

            array(

                'code' => 58757,

                'error' => 'User token is invalid. GROUP CREATION ERROR PLEASE CONTACT CSOFTWARE FOR MORE INFO.',

                'data' => null

            ));

            return;



    }

    $un =  $tempdata[0]['username'];
    $row = null;

    $result = mysqli_query($link, "SELECT username, email, bio, ID FROM users WHERE username= '$un'");
    $groupname = strip_tags((stripslashes($_GET['name'])));
    $random = generateRandomString(8);
    $RANDOMN = rand(0,999999999);
    

    $row = mysqli_fetch_assoc($result);
    $result = null;

   

if ( mysqli_query($link, "INSERT 
INTO 
groups 
(
    name,
    members,
    invite,
    owner,
     ID
) 

VALUES 

(
'$groupname', 
'$un ', 
'$random',
'$un',
'$RANDOMN'
);")) {
    echo json_encode(

        array(


            'code' => 56899,

            'data' => 'group creation successfull',

            'refresh' => true,

            'sql-error' => mysqli_error($link)

        ));
  } else {
    echo json_encode(

        array(


            'code' => 568999,

            'error' => 'group creation failed',

            'data' => null,

            'refresh' => true,

            'sql-error' => mysqli_error($link)

        ));
  }

}


//
//
//              leave group/server==================
//
//
//


function leave($link){
    
    $token = strip_tags(stripslashes($_GET['token']));

    $sql = "SELECT * FROM tokens WHERE token = '$token'";

    $results = mysqli_query($link, $sql);

    $tempdata = array();

    if (mysqli_num_rows($results) > 0) {

        // output data of each row

        while($row = mysqli_fetch_assoc($results)) {

           $tempdata[] = $row;

        }

        

    } else {

        echo json_encode(

            array(

                'code' => 58757,

                'error' => 'User token is invalid.',

                'data' => null

            ));

         

    }
    
    

    $un =  $tempdata[0]['username'];

    $serverid = strip_tags(stripslashes($_GET['serverid']));

    $sql = "UPDATE groups SET members=REPLACE(members,'$un ','') WHERE ID=$serverid";
     mysqli_query($link, $sql);

     echo json_encode(

        array(

            'code' => 200,

            'error' => 'Success hopefully',

            'data' => $sql

        ));



        
}


//
//
//              delete server==================
//              (server owners only)
//
//


function delete($link){
    
    $token = strip_tags(stripslashes($_GET['token']));

    $sql = "SELECT * FROM tokens WHERE token = '$token'";

    $results = mysqli_query($link, $sql);

    $tempdata = array();

    if (mysqli_num_rows($results) > 0) {

        // output data of each row

        while($row = mysqli_fetch_assoc($results)) {

           $tempdata[] = $row;

        }

        

    } else {

        echo json_encode(

            array(

                'code' => 58757,

                'error' => 'User token is invalid.',

                'data' => null

            ));

         

    }
    $serverid = strip_tags(stripslashes($_GET['serverid']));
    $sql = "SELECT * FROM groups WHERE ID = '$serverid'";

    $results = mysqli_query($link, $sql);

    $tempdata = array();

    if (mysqli_num_rows($results) > 0) {

        // output data of each row

        while($row = mysqli_fetch_assoc($results)) {

           if($row['owner'] == $un){

           }else{
            echo json_encode(

                array(
    
                    'code' => 58757,
    
                    'error' => 'Invalid Owner(You are not the server owner.)',
    
                    'data' => null
    
                ));
           }

        }

        

    } else {

        echo json_encode(

            array(

                'code' => 58757,

                'error' => 'Invalid serverID.',

                'data' => null

            ));

         return;

    }
    
    

    $un =  $tempdata[0]['username'];

    $serverid = strip_tags(stripslashes($_GET['serverid']));

    $sql = "DELETE FROM `groups` WHERE `groups`.`ID` = $serverid";
     mysqli_query($link, $sql);
     $sql = "DELETE FROM `messages` WHERE `messages`.`serverid` = $serverid;";
     mysqli_query($link, $sql);

     echo json_encode(

        array(

            'code' => 200,

            'error' => 'Success hopefully',

            'data' => $sql

        ));



        
}
///
///
///     TEST PING
///
///
///
function test($link){

    echo json_encode(

        array(

            'code' => 200,

            'message' => 'Connection successful'

        ));

}



//
// PPGET (PROFILEPICTURE GET for user)
// 
// function name pfpget
//
//





function ppget($link){

$usernameget = strip_tags(stripslashes($_GET['username']));

 $sql = "SELECT * FROM users WHERE username = '$usernameget'";

    $results = mysqli_query($link, $sql);

    $tempdata = array();

    if (mysqli_num_rows($results) > 0) {

        // output data of each row

        while($row = mysqli_fetch_assoc($results)) {

           $emailget = $row['email'];

        }
    }
    $im=imagecreatefromjpeg("https://www.gravatar.com/avatar/". md5($emailget) ."?s=500");
    header('Content-type:image/jpeg');
    imagejpeg($im);

}


//

//

//              members  ===========================================

//

//



function memberslist($link){

   

    $token = strip_tags(stripslashes($_GET['token']));
  
      $sql = "SELECT * FROM tokens WHERE token = '$token' LIMIT 30";
  
      $results = mysqli_query($link, $sql);
  
      $tempdataa = array();
  
      if (mysqli_num_rows($results) > 0) {
  
          // output data of each row
  
          while($row = mysqli_fetch_assoc($results)) {
  
  $tempdataa[] = $row;
  
          }
  
          
  
      } else {
  
          echo json_encode(
  
              array(
  
                  'code' => 58757,
  
                  'error' => 'User token is invalid.',
  
                  'data' => null
  
              ));
  
      }
  
  
  
      $groups = null;
      $members = null;
  
      //$un =  $tempdataa['username'];
      $currentserver = strip_tags(stripcslashes($_GET['serverid']));
  
      $msgsql = "SELECT * FROM `groups` WHERE `ID` LIKE '$currentserver'";
  
      $result = mysqli_query($link, $msgsql);
  
  
  
  if (mysqli_num_rows($result) > 0) {
  
    // output data of each row
  
    while($roww = mysqli_fetch_assoc($result)) {
  
      $members[] = explode(' ', $roww['members']);
  
    }

echo json_encode($members);
  
  }
}

//

//

//              members status  ===========================================

//

//



function memberstatus($link){

   

    $token = strip_tags(stripslashes($_GET['token']));
  
      $sql = "SELECT * FROM tokens WHERE token = '$token' LIMIT 30";
  
      $results = mysqli_query($link, $sql);
  
      $tempdataa = array();
  
      if (mysqli_num_rows($results) > 0) {
  
          // output data of each row
  
          while($row = mysqli_fetch_assoc($results)) {
  
  $tempdataa = $row;
  
          }
  
          
  
      } else {
  
          echo json_encode(
  
              array(
  
                  'code' => 58757,
  
                  'error' => 'User token is invalid.',
  
                  'data' => null
  
              ));
  
      }
  
  
  
      $groups = null;
      $members = null;
  
      $un =  $tempdataa['username'];
      $currentserver = strip_tags(stripcslashes($_GET['serverid']));
  
      $msgsql = "SELECT * FROM `groups` WHERE `ID` LIKE '$currentserver'";
  
      $result = mysqli_query($link, $msgsql);
  
  
  
  if (mysqli_num_rows($result) > 0) {
  
    // output data of each row
  
    while($roww = mysqli_fetch_assoc($result)) {
  
      $members = explode(' ', $roww['members']);
  
    }
  
    foreach ($members as $values)
{
    
    $msgsql = "SELECT lastseen,username FROM `users` WHERE `username` LIKE '$values'";
    $result = mysqli_query($link, $msgsql);

    while($roww = mysqli_fetch_assoc($result)) {
        
     //   if($roww['lastseen'] >= time()-20) { echo "online"; } else {echo "offline"; }
        echo json_encode($roww);
    
      }
     
}
  
  
  } else {
  
      json_encode(
  
          array(
  
              'data' => 'No members.'
  
          )
  
      );
  
  
  
  
  
  }
  
  }