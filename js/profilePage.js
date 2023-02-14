function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }

var edit = document.querySelector(".edit");
var username = document.querySelector(".username")
var name_surname = document.querySelector(".realname")
var profile_pic = document.querySelector(".select-file")
var save_btn = document.querySelector(".save-btn")
var friend_btn = document.querySelector('.friend')
var follow_btn = document.querySelector('.follow')
var decline_friend = document.querySelector('[data-type=received-request-decline]')

function changeTheme() {
    $.ajax({
        type: "POST",
        url: "changeTheme.php",
        data: {
            
        },
        success: (response) => {
            document.location.reload();
        }
    })
}

if(edit != null){
edit.addEventListener("click", e=>{
    e.preventDefault();
    profile_pic.style.visibility = "visible";
    name_surname.disabled = false;
    name_surname.style.border = "1px solid #000000"
    save_btn.style.visibility = "visible"
})
}

function lowerFirstLetter(string) {
    return string.charAt(0).toLowerCase() + string.slice(1);
  }

function createNewElements(type){
    var search = new URLSearchParams(window.location.search)
    //add the way to check if you need to update otherwise remove all and get
    var elements = new Array()
    document.querySelector('.'+type+'s').querySelectorAll('section').forEach(el=>{
        elements.push(el.getAttribute('id'))
    })
    axios.get('getLikedElementByUser.php?user='+search.get("user")+'&type='+type)
    .then(d=>{
        d["data"].forEach(element=>{
            if(!elements.includes(element["element_link"])){
                elements.slice(elements.indexOf(element["element_link"]),1)
                axios.get('fetch'+type+'Data.php?'+lowerFirstLetter(type)+'Id='+element["element_link"])
                .then(res=>{
                    var a = document.createElement('a',element["element_link"])
                    a.setAttribute('href', lowerFirstLetter(type)+'.php?id='+element["element_link"])
                    a.setAttribute('style','text-decoration:none;outline:none')
                    a.setAttribute('title',res["data"]["name"])
                    var section = document.createElement('section')
                    section.setAttribute('id',element["element_link"])
                    section.setAttribute('class', 'd-flex my-2 surface align-items-center')
                    section.setAttribute('style', 'border-radius:0.5rem;height:5rem;')
                    var img = document.createElement('img')
                    img.setAttribute('class', 'mx-2')
                    img.setAttribute('style',"width:4rem;border-radius:100%;object-fit: cover;")
                    img.setAttribute("src",res["data"]["images"][1]["url"])
                    var span = document.createElement('span')
                    span.setAttribute('class',"albumName ml-2 label-large")
                    span.innerHTML = res["data"]["name"]
                    section.appendChild(img)
                    section.appendChild(span)
                    a.appendChild(section)
                    document.querySelector('.'+type+'s').appendChild(a)
                
            })
        }
    })
        
    })
    .catch(err=>{console.log(err)})
}

var activeLinks = document.querySelector('.top-navigation');
activeLinks = activeLinks.querySelectorAll('a');

activeLinks.forEach(el=>{
    el.addEventListener("click", e=>{
        e.preventDefault();
        if(document.querySelector('a.active').getAttribute('data-value') == "Posts" && document.querySelector('.Posts') != null){
            document.querySelector('.Posts').setAttribute('style', 'display:block;visibility:visible')
            document.querySelector('.Reviews').setAttribute('style', 'display:none;visibility:hidden')
            document.querySelector('.Artists').setAttribute('style', 'display:none;visibility:hidden')
            document.querySelector('.Albums').setAttribute('style', 'display:none;visibility:hidden')
        }else if(document.querySelector('a.active').getAttribute('data-value') == "Reviews" && document.querySelector('.Posts') != null){

            document.querySelector('.Posts').setAttribute('style', 'display:none;visibility:hidden' )
            document.querySelector('.Reviews').setAttribute('style', 'display:block;visibility:visible')
            document.querySelector('.Artists').setAttribute('style', 'display:none;visibility:hidden')
            document.querySelector('.Albums').setAttribute('style', 'display:none;visibility:hidden')
        }else if(document.querySelector('a.active').getAttribute('data-value') == "Artists" && document.querySelector('.Posts') != null){
            document.querySelector('.Posts').setAttribute('style', 'display:none;visibility:hidden')
            document.querySelector('.Reviews').setAttribute('style', 'display:none;visibility:hidden')
            document.querySelector('.Artists').setAttribute('style', 'display:block;visibility:visible')
            document.querySelector('.Albums').setAttribute('style', 'display:none;visibility:hidden')

            createNewElements('Artist')
        }else if(document.querySelector('a.active').getAttribute('data-value') == "Albums" && document.querySelector('.Posts') != null){
            document.querySelector('.Posts').setAttribute('style', 'display:none;visibility:hidden')
            document.querySelector('.Reviews').setAttribute('style', 'display:none;visibility:hidden')
            document.querySelector('.Artists').setAttribute('style', 'display:none;visibility:hidden')
            document.querySelector('.Albums').setAttribute('style', 'display:block;visibility:visible')

            createNewElements('Album')
        }
    })
})
if(friend_btn != null){
    friend_btn.addEventListener("click", e=>{
        e.preventDefault()
        if(friend_btn.getAttribute('data-type') == 'not_friend'){
            axios.get("/Spotlight/userRequest.php?type=2&user="+username.value).then(res=>{
                friend_btn.innerHTML = "Cancel request";
                friend_btn.setAttribute("data-type","wait-acceptance");

            })
        }else if(friend_btn.getAttribute('data-type') == 'wait-acceptance'){
            axios.get("/Spotlight/userRequest.php?type=3&user="+username.value).then(res=>{
                friend_btn.innerHTML = "Friend request";
                friend_btn.setAttribute("data-type","not_friend");

            })
        }else if(friend_btn.getAttribute('data-type') == 'friend'){
            axios.get("/Spotlight/userRequest.php?type=4&user="+username.value).then(res=>{
                friend_btn.innerHTML = "Friend request"
                friend_btn.setAttribute("data-type","not_friend");
            })
        }else if(friend_btn.getAttribute('data-type') == 'request-received'){
            axios.get("/Spotlight/userRequest.php?type=5&user="+username.value).then(res=>{
                friend_btn.innerHTML = "Remove friend"
                friend_btn.setAttribute("data-type","friend")
                if(decline_friend != null){
                    decline_friend.remove()
                }
                window.location.replace("/Spotlight/profile.php?user="+username.value)
            })
        }
    })
}

if(decline_friend != null){
    decline_friend.addEventListener("click", e=>{
        e.preventDefault()
        axios.get("/Spotlight/userRequest.php?type=3&user="+username.value).then(res=>{
                if(friend_btn != null){
                    friend_btn.innerHTML = "Friend request"
                    friend_btn.setAttribute("data-type","not_friend")
                }
                decline_friend.remove()
                window.location.replace("/Spotlight/profile.php?user="+username.value)
        })
    })
}

if(follow_btn != null){
    follow_btn.addEventListener("click", e=>{
        e.preventDefault()
        if(follow_btn.classList.contains('not_follow')){
            axios.get("/Spotlight/userRequest.php?type=0&user="+username.value).then(res=>{
                follow_btn.innerHTML = "Unfollow";
                follow_btn.classList.remove("not_follow");
                follow_btn.classList.add("following");
                location.reload()
            }).catch(err=>{console.log(err)})
        }else if(follow_btn.classList.contains('following')){
            axios.get("/Spotlight/userRequest.php?type=1&user="+username.value).then(res=>{
                follow_btn.innerHTML = "Follow";
                follow_btn.classList.remove("following");
                follow_btn.classList.add("not_follow");
                location.reload()
            })
        }
    })
}
save_btn.addEventListener("click", e=>{
    e.preventDefault();
    var data = new FormData()
    const names = name_surname.value.split(" ")
    data.append('username', username.value)
    data.append('first_name',names[0])
    data.append("last_name",names[1])
    data.append('profile_pic', profile_pic.files[0])
    profile_pic.style.visibility = "hidden";
    username.disabled=true;
    username.style.border = "none"
    name_surname.disabled = true;
    name_surname.style.border = "none"
    save_btn.style.visibility = "hidden"
    axios.post('/Spotlight/save_update.php', data, {headers:{'Content-Type':'multipart/form-data'}}).then(res=>{
        location.reload()
    }).catch(err=>{
        console.log(err)
    })
})
