fetch("/content/home.json")
.then(res => res.json())
.then(data => {

  document.getElementById("title").innerText = data.title;

  document.getElementById("desc").innerText = data.description;

  document.getElementById("hero-img").src = data.image;

});
fetch("/content/home.json")
.then(res => res.json())
.then(data => {

    document.getElementById("web-title").innerText = data.title;

    document.getElementById("hero-sub-main").innerText = data.description;

});