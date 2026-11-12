document.addEventListener('DOMContentLoaded', function() {
    const mobileBtn = document.getElementById('mobile_btn');
    const mobileMenu = document.getElementById('mobile_menu');
    const icon = mobileBtn.querySelector('i');

    mobileBtn.addEventListener('click', function() {
        mobileMenu.classList.toggle('active');
        icon.classList.toggle('fa-x');
    });
});