// Profile dropdown and mobile menu functionality
document.addEventListener("DOMContentLoaded", function () {
  const dropdownBtn = document.getElementById("profileDropdownBtn");
  const dropdown = document.getElementById("profileDropdown");
  const mobileMenuBtn = document.getElementById("mobileMenuBtn");
  const mobileMenu = document.getElementById("mobileMenu");

  // Profile dropdown
  if (dropdownBtn && dropdown) {
    dropdownBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      dropdown.classList.toggle("hidden");
      console.log("Dropdown toggled:", dropdown.classList.contains("hidden"));
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function (e) {
      if (!dropdown.contains(e.target) && !dropdownBtn.contains(e.target)) {
        dropdown.classList.add("hidden");
      }
    });

    // Close dropdown on Escape key
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") {
        dropdown.classList.add("hidden");
      }
    });
  }

  // Mobile menu toggle
  if (mobileMenuBtn && mobileMenu) {
    mobileMenuBtn.addEventListener("click", function () {
      mobileMenu.classList.toggle("hidden");
    });

    // Close mobile menu when clicking outside
    document.addEventListener("click", function (e) {
      if (!mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
        mobileMenu.classList.add("hidden");
      }
    });
  }
});
