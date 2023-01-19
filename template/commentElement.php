<div class="d-flex my-3 w-100">
    <div style="padding-top: 0.4rem;">
        <img class="profile-pic" src=<?php echo $commentData["profilePic"] ?>  alt="Profile picture not found">
    </div>
    <div class="comment surface w-100">
        <span class="label-large"><?php echo $commentData["username"] ?></span>
        <p><?php echo $commentData["text"] ?></p>
        <div class="d-flex justify-content-end">
            <span class="text-small ml-auto" onclick=<?php echo "toggleReply('", $commentData["username"], "',", $commentData["comment_id"], ")" ?>>reply</span>
        </div>
    </div>
    
</div>