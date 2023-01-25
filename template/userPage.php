<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/sliding_bar.css">
    <link rel="stylesheet" href="css/profile-page.css">
    <title><?php echo $templateParams["title"] ?></title>
</head>

<body>
    <nav class ="containter-fluid">
    <div class ="d-flex flex-row-reverse">
        <button class=" btn-profile" type="button">Follow</button>
        <button class=" btn btn-profile" type="button">Friend Request</button>
    </div>

        <div class="row d-flex justify-content-between m-3">
            <div class="col-3"><img class="profile-pic" alt='<?php UPLOAD_DIR . "default.jpg"?>' src='<?php echo $templateParams["profilePicPath"] == ""? UPLOAD_DIR . "default.jpg": UPLOAD_DIR . $templateParams["profilePicPath"]?>' /></div>
            <div class = "col-9">
                <input class="name-holder username" id="user" value="<?php echo $templateParams["username"];?>" type="text" disabled/>
                <label class="edit" for="user"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
                    <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/>
                </svg></label>
            <input class="name-holder realname" value="<?php echo $templateParams["firstname"]; echo " ";echo $templateParams["lastname"];?>" disabled/>

            </div>    
        </div>

        <input class="select-file " type="file"/>
        <button class="btn-profile save">save</button>
        <div class = "d-flex justify-content-center">
            <a class=" info"><?php echo $templateParams["FriendsCount"];?> Friends</a>
            <a class=" info"><?php  echo $templateParams["FollowerCount"];?> Followers</a>
            <a class=" info"><?php echo $templateParams["FollowingCount"]?> Following</a>
        </div>
        <div class="">
        <div class="top-navigation">
            <div class="active-link"></div>
                <a class = "top-links active"href="#" >Posts</a> 
                <a class = "top-links" href="#">Reviews</a>
                <a class = "top-links" href="#">Artists</a>
                <a class = "top-links" href="#">Albums</a> 
        </div>
        </div>
    </nav>

    <main>
        Posts, reviews, ...
    </main>

    <footer></footer>

    <script src="./js/sliding_bar.js"></script>
    <script src="./js/profilePage.js"></script>
</body>

</html>