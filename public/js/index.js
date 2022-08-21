console.log("Hello !");

function openNav() {
    document.getElementById("mySidenav").style.width = "250px";
    document.getElementById("main").style.marginLeft = "250px";
}

function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
    document.getElementById("main").style.marginLeft = "0";
}


function openItemForm() {
    console.log("open");
    var ob = document.getElementById("ItemForm");
    //ob.classList.add('itemForm-active');
    ob.style.display = "block";
}

function closeItemForm() {
    console.log("close");
    var ob = document.getElementById("ItemForm");
    //ob.classList.remove('itemForm-active');
    ob.style.display = "none";
}

// When the user scrolls the page, execute myFunction
window.onscroll = function() {myFunction()};

// Get the navbar
var nav = document.getElementById("nav");

// Get the offset position of the navbar
var sticky = nav.offsetTop;

// Add the sticky class to the navbar when you reach its scroll position. Remove "sticky" when you leave the scroll position
function myFunction() {
 if (window.pageYOffset >= sticky) {
   nav.classList.add("sticky")
  } else {
   nav.classList.remove("sticky");
  }
}
