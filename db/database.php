<?php

require_once("sendEmail.php");

class DatabaseHelper{
    private $db;

    public function __construct($servername, $username, $password, $dbname, $port){
        $this->db = new mysqli($servername, $username, $password, $dbname, $port);
        if($this->db->connect_error){
            die("Connesione fallita al db");
        }
    }

    /* 
    public function getRandomPosts($n=2){
        $stmt = $this->db->prepare("SELECT idarticolo, titoloarticolo, imgarticolo FROM articolo ORDER BY RAND() LIMIT ?");
        $stmt->bind_param("i", $n);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }
    */

    public function getAlbumReviews($albumId){
        $stmt = $this->db->prepare("SELECT review_id, `text`, album, date, score, number_of_likes, number_of_dislikes, username FROM review WHERE album=? ORDER BY (number_of_likes)+(number_of_dislikes) DESC LIMIT 7");
        $stmt->bind_param("s", $albumId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getArtistLikes($artistId){
        $stmt = $this->db->prepare("SELECT username FROM `likes` WHERE element_link=?");
        $stmt->bind_param("s", $artistId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAlbumLikes($albumId){
        $stmt = $this->db->prepare("SELECT username FROM `likes` WHERE element_link=?");
        $stmt->bind_param("s", $albumId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getPostData($postId) {
        $stmt = $this->db->prepare("SELECT * FROM `post` WHERE post_id=?");
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getUserData($userId) {
        $stmt = $this->db->prepare("SELECT * FROM `user` WHERE username=?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getComments($postId) {
        $stmt = $this->db->prepare("SELECT * FROM `comment` WHERE post_id=?");
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getCommentReplies($commentId) {
        $stmt = $this->db->prepare("SELECT * FROM `reply` WHERE thread=?");
        $stmt->bind_param("i", $commentId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getPostLikes($postId) {
        $stmt = $this->db->prepare("SELECT username FROM `likes_post` WHERE post_id=?");
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getIsLikeOrCommentPost($postId, $notId){
        $stmt = $this->db->prepare("SELECT isLike FROM notification_from_post WHERE post_id=? AND notification_id=?");
        $stmt->bind_param("ii", $postId, $notId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllNotificationsOfUser($username){
        $stmt = $this->db->prepare("SELECT * FROM notification WHERE username_target=? AND deleted=false");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getFriendsCount($username) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM friends WHERE Friend_username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getFollowerCount($username) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM follows WHERE Follower_username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getFollowingCount($username) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM follows WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function followUser($sender, $receiver){
        $stmt = $this->db->prepare("INSERT INTO follows (Follower_username, username) VALUES (?,?) ");
        $stmt->bind_param("ss", $receiver, $sender);
        $stmt->execute();
        

        $stmt = $this->db->prepare("SELECT COUNT(*) as follows FROM notification WHERE username_target=? and username_source=? and mood_id is NULL and review_id is NULL and post_id is NULL and friend_request_id is NULL");
        $stmt->bind_param("ss", $receiver, $sender);
        $stmt->execute();
        $result = $stmt->get_result();
        $res = $result->fetch_all(MYSQLI_ASSOC);

        if($res[0]["follows"] == 0){
            $stmt = $this->db->prepare("INSERT INTO notification (username_target, username_source, date) VALUES (?,?,?)");
            $date = date("Y-m-d");
            $stmt->bind_param("sss", $receiver, $sender, $date);
            $stmt->execute();
        }
    }

    public function unfollowUser($sender, $receiver){
        $stmt = $this->db->prepare("DELETE FROM follows WHERE Follower_username=? AND username=? ");
        $stmt->bind_param("ss", $receiver, $sender);
        $stmt->execute();
        //eliminate the notification if present?
        $stmt = $this->db->prepare("DELETE FROM notification WHERE username_target=? AND username_source=? and mood_id is NULL and review_id is NULL and post_id is NULL and friend_request_id is NULL ");
        $stmt->bind_param("ss", $receiver, $sender);
        $stmt->execute();
    }

    public function sendFriendRequest($sender, $receiver){
        $stmt = $this->db->prepare("INSERT INTO friend_request (Friend_username, username) VALUES (?,?) ");
        $stmt->bind_param("ss", $receiver, $sender);
        $stmt->execute();

        $stmt = $this->db->prepare("SELECT friend_request_id FROM friend_request WHERE Friend_username=? and username=? ");
        $stmt->bind_param("ss", $receiver, $sender);
        $stmt->execute();
        $result = $stmt->get_result();
        $res = $result->fetch_all(MYSQLI_ASSOC);

        
        $stmt = $this->db->prepare("INSERT INTO notification (username_target, username_source, friend_request_id, date) VALUES (?,?,?,?)");
        $date = date("Y-m-d");
        $stmt->bind_param("ssis", $receiver, $sender, $res[0]["friend_request_id"], $date);
        $stmt->execute();

    }

    public function eliminateFriendRequest($sender, $receiver){
        $stmt = $this->db->prepare("SELECT friend_request_id FROM friend_request WHERE (Friend_username=? AND username=?) OR (Friend_username=? AND username=?) ");
        $stmt->bind_param("ssss", $receiver, $sender, $sender, $receiver);
        $stmt->execute();
        $result = $stmt->get_result();
        $res = $result->fetch_all(MYSQLI_ASSOC);

        $friend_request_id= $res[0]["friend_request_id"];
        $stmt = $this->db->prepare("DELETE FROM notification WHERE  friend_request_id =?");
        $stmt->bind_param("i", $friend_request_id);
        $stmt->execute();

        $stmt = $this->db->prepare("DELETE FROM `friend_request` WHERE friend_request_id=?");
        $stmt->bind_param("i", $friend_request_id);
        $stmt->execute();

    }

    public function acceptFriendRequest($sender, $receiver){
        $this->eliminateFriendRequest($sender, $receiver);
        $stmt = $this->db->prepare("INSERT INTO friends (Friend_username, username) VALUES (?,?) ");
        $stmt->bind_param("ss", $receiver, $sender);
        $stmt->execute();
        $stmt = $this->db->prepare("INSERT INTO friends (Friend_username, username) VALUES (?,?) ");
        $stmt->bind_param("ss", $sender, $receiver);
        $stmt->execute();
    }

    public function eliminateFriend($sender, $receiver){
        $stmt = $this->db->prepare("DELETE FROM friends WHERE (Friend_username=? AND username=?) OR (Friend_username=? AND username=?)");
        $stmt->bind_param("ssss", $receiver, $sender, $sender, $receiver);
        $stmt->execute();
    }

    public function isFollower($sender, $receiver){
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM follows WHERE Follower_username=? AND username=?");
        $stmt->bind_param("ss", $sender, $receiver);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function isFriend($sender, $receiver){
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM friends WHERE Friend_username=? AND username=?");
        $stmt->bind_param("ss", $sender, $receiver);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function sentFriendRequest($sender, $receiver){
        $stmt = $this->db->prepare("SELECT COUNT(*) AS requests FROM friend_request WHERE Friend_username=? AND username=?");
        $stmt->bind_param("ss", $receiver, $sender);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function receivedFriendRequest($sender, $receiver){
        $stmt = $this->db->prepare("SELECT COUNT(*) AS requests FROM friend_request WHERE Friend_username=? AND username=?");
        $stmt->bind_param("ss", $sender, $receiver);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getPostsOfUser($username){
        $stmt = $this->db->prepare("SELECT post_id, text, song, date, number_of_likes, username FROM post WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getReviewsOfUser($username){
        $stmt = $this->db->prepare("SELECT review_id, text, album, date, score, number_of_likes, number_of_dislikes, username FROM review WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getLikedElementByUser($username, $type){
        $stmt = $this->db->prepare("SELECT likes.element_link FROM likes, spotify_element WHERE (likes.username=?) AND (spotify_element.type=?) AND likes.element_link = spotify_element.element_link");
        $stmt->bind_param("ss", $username, $type);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updateUserData($username, $data){
        $command = "UPDATE user SET";
        $index = 0;
        $parametric = '';
        $upd = array();
        //potrei fare una funzione generale
        if(isset($data['first_name'])){
            if($index > 0){
                $command = $command . ",";
            }
            $command = $command . " first_name=?";
            $parametric = $parametric . 's';
            array_push($upd,$data['first_name']);
            $index += 1;
        }
        if(isset($data['last_name'])){
            if($index > 0){
                $command = $command . ",";
            }
            $command = $command . " last_name=?";
            $parametric = $parametric . 's';
            array_push($upd,$data['last_name']);
            $index += 1;
        }
        if(isset($data['profile_pic'])){
            if($index > 0){
                $command = $command . ",";
            }
            $command = $command . " profile_pic=?";
            $parametric = $parametric . 's';
            array_push($upd, $data['profile_pic']);
            $index += 1;
        }
        if($index == 0){
            return;
        }
        $parametric = $parametric . 's';
        array_push($upd, $username);
        $stmt = $this->db->prepare($command . " WHERE username=?");
        $stmt->bind_param($parametric, ...$upd);
        $stmt->execute();
    }

    public function eliminateNotificationComment($id){
        $stmt = $this->db->prepare("UPDATE notification SET deleted=? WHERE notification_id=?");
        $flag = true;
        $stmt->bind_param("ii", $flag, $id);
        $stmt->execute();
    }

    public function createReview($data){
        $stmt = $this->db->prepare("INSERT INTO review (text, album, date, score, number_of_likes, number_of_dislikes, username) VALUES
         (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiiis",$data["text"], $data["album"], $data["date"], $data["score"], $data["number_of_likes"], $data["number_of_dislikes"], $data["username"]);
        $stmt->execute();
    }
    
    public function addOrUpdateLikeReview($review_id, $username, $rating, $username_session){
        //check if a like already exist for the review and the user
        $stmt = $this->db->prepare("SELECT review_id, username, isLike FROM likes_review WHERE review_id=? AND username=?");
        $stmt->bind_param("ss", $review_id, $username_session);
        $stmt->execute();
        $result = $stmt->get_result();
        $res = $result->fetch_all(MYSQLI_ASSOC);
        //get from the review the number of likes and dislikes
        $stmt = $this->db->prepare("SELECT number_of_likes, number_of_dislikes FROM review WHERE review_id=? AND username=?");
        $stmt->bind_param("ss", $review_id, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $likes = $result->fetch_all(MYSQLI_ASSOC);
        // check if the like_review already exist and if it is.Is it different to respect to the new value
        if(isset($res[0]['isLike']) && $res[0]['isLike'] != $rating){
            $stmt = $this->db->prepare("DELETE FROM likes_review WHERE review_id=? AND username=?");
            $stmt->bind_param("is", $review_id, $username_session);
            $stmt->execute();

            if($rating){
                $number_of_likes = $likes[0]['number_of_likes'] + 1;
                $number_of_dislikes = ($likes[0]['number_of_dislikes'] - 1 )<0?0:$likes[0]['number_of_dislikes'] - 1 ;
            }else{
                $number_of_dislikes = $likes[0]['number_of_dislikes'] + 1;
                $number_of_likes = ($likes[0]['number_of_likes'] - 1 )<0?0:$likes[0]['number_of_likes'] - 1 ;
            }
            $stmt = $this->db->prepare("UPDATE `review` SET number_of_likes=?,number_of_dislikes=? WHERE review_id=? AND username=?");
            $stmt->bind_param("iiss", $number_of_likes, $number_of_dislikes, $review_id, $username);
            $stmt->execute();
        }else{//if not existing we modify the review
            if($rating){
                $number_of_likes = $likes[0]['number_of_likes'] + 1;
                $number_of_dislikes = $likes[0]['number_of_dislikes'];
            }else{
                $number_of_likes = $likes[0]['number_of_likes'];
                $number_of_dislikes = $likes[0]['number_of_dislikes'] + 1;
            }
            $stmt = $this->db->prepare("UPDATE `review` SET number_of_likes=?,number_of_dislikes=? WHERE review_id=? AND username=?");
            $stmt->bind_param("iiss", $number_of_likes, $number_of_dislikes, $review_id, $username);
            $stmt->execute();
        }//create the like to the review

        $stmt = $this->db->prepare("INSERT INTO likes_review (review_id, username, isLike) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $review_id, $username_session, $rating);
        $stmt->execute();

        if($username != $username_session){
            //verify if the notification exist in that case do not create a new one
            $stmt = $this->db->prepare("SELECT COUNT(*) AS nnot FROM notification WHERE username_target=? and username_source=? and review_id=?");
            $stmt->bind_param("ssi", $username, $username_session, $review_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $res = $result->fetch_all(MYSQLI_ASSOC);

            if($res[0]["nnot"] == 0){
                $stmt = $this->db->prepare("INSERT INTO notification (username_target, username_source, review_id, date) VALUES (?,?,?,?)");
                $date = date("Y-m-d");
                $stmt->bind_param("ssis", $username, $username_session, $review_id, $date);
                $stmt->execute();
            }
        }

    }

    public function eliminateLikeReview($username, $username_session, $review_id){
        $stmt = $this->db->prepare("SELECT isLike FROM likes_review WHERE review_id=? AND username=?");
        $stmt->bind_param("ss", $review_id, $username_session);
        $stmt->execute();
        $result = $stmt->get_result();
        $res = $result->fetch_all(MYSQLI_ASSOC);

        $stmt = $this->db->prepare("SELECT number_of_likes, number_of_dislikes FROM review WHERE review_id=? AND username=?");
        $stmt->bind_param("ss", $review_id, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $likes = $result->fetch_all(MYSQLI_ASSOC);

        $stmt = $this->db->prepare("DELETE FROM likes_review WHERE review_id=? AND username=?");
        $stmt->bind_param("is",$review_id, $username_session);
        $stmt->execute();

        if($res[0]["isLike"]){
            $number_of_likes = $likes[0]['number_of_likes'] - 1;
            $number_of_dislikes = $likes[0]['number_of_dislikes'];
        }else{
            $number_of_dislikes = $likes[0]['number_of_dislikes'] -1;
            $number_of_likes = $likes[0]['number_of_likes'];
        }
        //maybe we can even remove the notification if still existant?

        $stmt = $this->db->prepare("DELETE FROM notification WHERE review_id=?");
        $stmt->bind_param("i", $review_id);
        $stmt->execute();

        $stmt = $this->db->prepare("UPDATE `review` SET number_of_likes=?,number_of_dislikes=? WHERE review_id=? AND username=?");
        $stmt->bind_param("iiss", $number_of_likes, $number_of_dislikes, $review_id, $username);
        $stmt->execute();
    }

    public function eliminateNotification($id){
        $stmt = $this->db->prepare("DELETE FROM notification WHERE notification_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    public function getLikesAndDislikesOfReview($review_id, $username){
        $stmt = $this->db->prepare("SELECT number_of_likes, number_of_dislikes FROM review WHERE review_id=? AND username=?");
        $stmt->bind_param("ss", $review_id, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getIsLikedReview($review_id, $username){
        $stmt = $this->db->prepare("SELECT isLike FROM likes_review WHERE review_id=? AND username=?");
        $stmt->bind_param("ss", $review_id, $username);
        $stmt->execute();
        $result = $stmt->get_result();
       return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function isLikedSpotifyElement($id, $username){
        $stmt = $this->db->prepare("SELECT element_link FROM likes WHERE element_link=? AND username=?");
        $stmt->bind_param("ss", $id, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getUsersMatchRelationship($username_owner, $username_search, $type) {
        $regex_owner = "%" . $username_owner . "%";
        $regex_search = "%" . $username_search . "%";
        $stmt = null;
        if($type == 'Friend'){
            $stmt = $this->db->prepare("SELECT user.username, user.first_name, user.last_name, user.profile_pic FROM user, friends WHERE  (Friend_username LIKE ?) and (friends.username LIKE ?) and (user.username = friends.username)");
            $stmt->bind_param("ss", $regex_owner, $regex_search);
        }elseif($type == 'Follower'){
            $stmt = $this->db->prepare("SELECT user.username, user.first_name, user.last_name, user.profile_pic FROM user, follows WHERE (Follower_username LIKE ?) and (follows.username LIKE ?) and (user.username = follows.username)");
            $stmt->bind_param("ss", $regex_owner, $regex_search);
        }elseif($type == 'Following'){
            $stmt = $this->db->prepare("SELECT user.username, user.first_name, user.last_name, user.profile_pic FROM user, follows WHERE (follows.username LIKE ?) and (Follower_username LIKE ?) and (user.username = Follower_username)");
            $stmt->bind_param("ss", $regex_owner, $regex_search);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $result = $result->fetch_all(MYSQLI_ASSOC);
        return $result;
    }

    public function eliminateNotificationFromPost($id,$notId){
        $stmt = $this->db->prepare("DELETE FROM notification_from_post WHERE post_id=? AND notification_id=?");
        $stmt->bind_param("ii", $id, $notId);
        $stmt->execute();
    }

    function checkbrute($username) {
        // Recupero il timestamp
        $now = time();
        // Vengono analizzati tutti i tentativi di login a partire dalle ultime due ore.
        $valid_attempts = $now - (2 * 60 * 60); 
        if ($stmt = $this->db->prepare("SELECT time FROM login_attempts WHERE username = ? AND time > ?")) { 
           $stmt->bind_param('si', $username, $valid_attempts); 
           // Eseguo la query creata.
           $stmt->execute();
           $stmt->store_result();
           // Verifico l'esistenza di più di 5 tentativi di login falliti nelle ultime due ore.
           if($stmt->num_rows > 5) {
              return true;
           } else {
              return false;
           }
        }
    }

    function login($email, $password) {
        // Usando statement sql 'prepared' non sarà possibile attuare un attacco di tipo SQL injection.
        if ($stmt = $this->db->prepare("SELECT username, password, salt FROM user WHERE email = ? LIMIT 1")) { 
           $stmt->bind_param('s', $email); // esegue il bind dei parametri.
           $stmt->execute(); // esegue la query appena creata.
           $stmt->store_result();
           $stmt->bind_result($username, $db_password, $salt); // recupera il risultato della query e lo memorizza nelle relative variabili.
           $stmt->fetch();
           $password = hash('sha512', $password.$salt); // codifica la password usando una chiave univoca.
           if($stmt->num_rows == 1) { // se l'utente esiste
              // verifichiamo che non sia disabilitato in seguito all'esecuzione di troppi tentativi di accesso errati.
                if($this->checkbrute($username) == true) { 
                 // Account disabilitato
                 // Invia un e-mail all'utente avvisandolo che il suo account è stato disabilitato.
                    $body = "We inform you that your account is currently suspended due to too many failed logins using your email.<br>The Spotlight Team";
                    $emailData = array(
                        "toEmail" => $email,
                        "toName" => $username,
                        "subject" => "Spotlight: Security Warning",
                        "body" => wordwrap($body,70)
                    );
                    sendEmail($emailData);
                    return 0;
            } else {
                if($db_password == $password) { // Verifica che la password memorizzata nel database corrisponda alla password fornita dall'utente.
                 // Password corretta!         
                    $user_browser = $_SERVER['HTTP_USER_AGENT']; // Recupero il parametro 'user-agent' relativo all'utente corrente.
                    $username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username); // ci proteggiamo da un attacco XSS
                    $_SESSION['username'] = $username;
                    $_SESSION['login_string'] = hash('sha512', $password.$user_browser);
                    registerLoggedUser($username);
                    // Login eseguito con successo.
                    return 1;    
                } else {
                 // Password incorretta.
                 // Registriamo il tentativo fallito nel database.   
                 $now = time();
                 $this->db->query("INSERT INTO login_attempts (username, time) VALUES ('$username', '$now')");
                 return 2;
              }
            }
            } else {
                // L'utente inserito non esiste.
                return 2;
            }
        }
    }

     function login_check() {
        // Verifica che tutte le variabili di sessione siano impostate correttamente
        if(isset($_SESSION['username'], $_SESSION['login_string'])) {
          $login_string = $_SESSION['login_string'];
          $username = $_SESSION['username'];     
          $user_browser = $_SERVER['HTTP_USER_AGENT']; // reperisce la stringa 'user-agent' dell'utente.
          if ($stmt = $this->db->prepare("SELECT password FROM user WHERE username = ? LIMIT 1")) { 
             $stmt->bind_param('i', $username); // esegue il bind del parametro '$user_id'.
             $stmt->execute(); // Esegue la query creata.
             $stmt->store_result();
      
             if($stmt->num_rows == 1) { // se l'utente esiste
                $stmt->bind_result($password); // recupera le variabili dal risultato ottenuto.
                $stmt->fetch();
                $login_check = hash('sha512', $password.$user_browser);
                if($login_check == $login_string) {
                   // Login eseguito!!!!
                   return true;
                }
             }
          }
        } 
        return false;
    }

    public function checkUser($username, $email){
        $query = "SELECT * FROM user WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ss',$username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function createFullUser($username, $email, $password, $salt, $first_name, $last_name, $propic){
        $query = "INSERT INTO `user` (`username`, `email`, `password`, `salt`, `first_name`, `last_name`, `profile_pic`) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('sssssss',$username, $email, $password, $salt, $first_name, $last_name, $propic);
        $stmt->execute();
    }

    public function createUser($username, $email, $password, $salt, $first_name, $last_name){
        $query = "INSERT INTO `user` (`username`, `email`, `password`, `salt`, `first_name`, `last_name`) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ssssss',$username, $email, $password, $salt, $first_name, $last_name);
        $stmt->execute();
    }


    public function toggleSpotifyElementLike($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as `num` FROM `likes` WHERE `username` = ? AND `element_link`= ?");
        $stmt->bind_param("ss", $_COOKIE["username"], $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $result = $result->fetch_all(MYSQLI_ASSOC);

        if($result[0]["num"] > 0) {
            $stmt = $this->db->prepare("DELETE FROM `likes` WHERE `username` = ? AND `element_link`= ?");
            $stmt->bind_param("ss", $_COOKIE["username"], $id);
            $stmt->execute();
            return 0;
        } else {
            $stmt = $this->db->prepare("INSERT INTO `likes` VALUES (?, ?)");
            $stmt->bind_param("ss", $id, $_COOKIE["username"]);
            $stmt->execute();
            return 1;
        } 

    }

    public function createSpotifyElement($type, $id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as `num` FROM `spotify_element` WHERE `type` = ? AND `element_link`= ?");
        $stmt->bind_param("ss", $type, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $result = $result->fetch_all(MYSQLI_ASSOC);

        if($result[0]["num"] == 0) {
            $stmt = $this->db->prepare("INSERT INTO `spotify_element` VALUES (?, ?)");
            $stmt->bind_param("ss", $type, $id);
            $stmt->execute();
        } 

    }

    public function createPost($text, $song){
        $likes = 0;
        $date = date("Y/m/d");
        $username = $_COOKIE["username"];
        echo $text;
        $stmt = $this->db->prepare("INSERT INTO `post` VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssis", $likes, $text, $song, $date, $likes, $username);
        $stmt->execute();
    }

    private function getLastInsertId() {
        $stmt = $this->db->prepare("SELECT LAST_INSERT_ID() as `last_id`;");
        $stmt->execute();
        $result = $stmt->get_result();
        $result = $result->fetch_all(MYSQLI_ASSOC);
        return $result[0]["last_id"];
    }

    public function createComment($postId, $user, $text) {
        $date = date("Y/m/d");

        $r = $this->getPostData($postId);

        $stmt = $this->db->prepare("INSERT INTO `notification` (`date`,`username_source`, username_target) VALUES (?,?,?)");
        $stmt->bind_param("sss", $date,$user, $r[0]["username"]);
        $stmt->execute();

        $notId = $this->getLastInsertId();
        

        $stmt = $this->db->prepare("INSERT INTO `comment` (`text`,`date`,`post_id`,`username`,`notification_id`) VALUES (?,?,?,?,?)");
        $stmt->bind_param("ssisi", $text,$date, $postId, $user, $notId);
        $stmt->execute();

        $stmt = $this->db->prepare("UPDATE `notification` SET `post_id` = ? WHERE `notification_id` = ?");
        $stmt->bind_param("ii", $postId,$notId);
        $stmt->execute();

        //add to notification_from_post
        $stmt = $this->db->prepare("INSERT INTO notification_from_post (post_id, notification_id, isLike) VALUES (?, ?, ?)");
        $isLike = false;
        $stmt->bind_param("iii", $postId,$notId, $isLike);
        $stmt->execute();

    }

    public function createReply($user, $text, $replyTo, $thread) {
        $date = date("Y/m/d");

        $stmt = $this->db->prepare("INSERT INTO `reply` (`to_user`,`date`,`thread`,`text`,`username` ) VALUES (?,?,?,?,?)");
        $stmt->bind_param("ssiss", $replyTo,$date, $thread, $text, $user);
        $stmt->execute();
    }

    public function togglePostLike($id){
        $stmt = $this->db->prepare("SELECT COUNT(*) as `num` FROM `likes_post` WHERE `username` = ? AND `post_id`= ?");
        $stmt->bind_param("ss", $_COOKIE["username"], $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $result = $result->fetch_all(MYSQLI_ASSOC);

        if($result[0]["num"] > 0) {
            $stmt = $this->db->prepare("DELETE FROM `likes_post` WHERE `username` = ? AND `post_id`= ?");
            $stmt->bind_param("ss", $_COOKIE["username"], $id);
            $stmt->execute();
            return 0;
        } else {
            $stmt = $this->db->prepare("INSERT INTO `likes_post` VALUES (?, ?)");
            $stmt->bind_param("ss", $id, $_COOKIE["username"]);
            $stmt->execute();

            //insert notification after like of post
            $r = $this->getPostData($id);

            $stmt = $this->db->prepare("INSERT INTO notification (username_source, username_target, post_id, date) VALUES (?,?,?,?)");
            $date = date("Y/m/d");
            $stmt->bind_param("ssis", $_COOKIE["username"], $r[0]["username"], $id, $date);
            $stmt->execute();

            $notId = $this->getLastInsertId();

            $stmt = $this->db->prepare("INSERT INTO notification_from_post (post_id, notification_id, isLike) VALUES (?, ?, ?)");
            $isLike = true;
            $stmt->bind_param("iii", $id, $notId, $isLike);
            $stmt->execute();

            return 1;
        } 
    }

    public function getUsersMatch($username) {
        $stmt = $this->db->prepare("SELECT * FROM `user` WHERE `username` LIKE ? or `first_name` LIKE ? or `last_name` LIKE ?");
        $regex = "%" . $username . "%";
        $stmt->bind_param("sss", $regex, $regex, $regex);
        $stmt->execute();
        $result = $stmt->get_result();
        $result = $result->fetch_all(MYSQLI_ASSOC);
        return $result;
    }

    public function getFollowersReviews($username, $offset){
        $query = "SELECT * FROM `review` WHERE `username` IN (SELECT `follower_username` FROM `follows` WHERE `username`= ?) ORDER BY `date`,`review_id`  DESC LIMIT ?,5";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('si',$username, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getFriendsPosts($username, $offset){
        $query = "SELECT * FROM `post` WHERE `username` IN (SELECT `friend_username` FROM `friends` WHERE `username`= ?) ORDER BY `date`,`post_id` DESC LIMIT ?,5";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('si',$username, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function createMood($username, $song, $emoji, $gradient) {
        $date = date("Y/m/d");
        $stmt = "INSERT INTO `mood` (`username`,`emoji`,`song`,`date`,`gradient`) VALUES (?,?,?,?,?)";
        $stmt = $this->db->prepare($stmt);
        $stmt->bind_param('ssssi',$username, $emoji, $song, $date, $gradient);
        $stmt->execute();
    }

    public function getFriendsMoods($username){
        $date = date("Y/m/d");
        $query = "SELECT * FROM `mood` WHERE DATEDIFF(?,`date`)=0 AND `username` IN (SELECT `friend_username` FROM `friends` WHERE `username`= ?) ORDER BY `date` DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ss',$date,$username);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getLastMood($username){
        $date = date("Y/m/d");
        $query = "SELECT * FROM `mood` WHERE `username`= ? AND `date`=?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ss', $username, $date);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getTotalPosts($username){
        $query = "SELECT * FROM `post` WHERE `username` IN (SELECT `friend_username` FROM `friends` WHERE `username`= ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s',$username);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getTotalReviews($username){
        $query = "SELECT * FROM `review` WHERE `username` IN (SELECT `follower_username` FROM `follows` WHERE `username`= ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s',$username);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>