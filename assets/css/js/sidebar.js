// Get elements
const menuBtn = document.getElementById("menuBtn");
const sidebar = document.getElementById("sidebar");
const overlay = document.getElementById("sidebarOverlay");

// Open / Close Sidebar
menuBtn.addEventListener("click", function () {
  sidebar.classList.toggle("active");
  overlay.classList.toggle("active");
});

// Close Sidebar when clicking overlay
overlay.addEventListener("click", function () {
  sidebar.classList.remove("active");
  overlay.classList.remove("active");
});

// Close Sidebar when clicking a menu item on mobile
const menuItems = document.querySelectorAll(".menu-item");

menuItems.forEach(function (item) {
  item.addEventListener("click", function () {
    if (window.innerWidth <= 768) {
      sidebar.classList.remove("active");
      overlay.classList.remove("active");
    }
  });
});



