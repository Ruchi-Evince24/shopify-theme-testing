document.addEventListener('DOMContentLoaded', function() {
    var popup = document.getElementById('popup1');
    var closeButton = document.getElementById('close');
    
    // Function to set cookie
    function setCookie(name, value, minutes) {
        var expires = "";
        if (minutes) {
            var date = new Date();
            date.setTime(date.getTime() + (minutes * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
        //console.log('Cookie set:', name, '=', value);
    }
    
    // Function to close popup and set cookie
    function closePopupAndSetCookie() {
        popup.style.display = 'none';
        setCookie('popup_closed', 'true', 1); // Set cookie for 1 day
    }

    var popupClosedCookie = document.cookie.split(';').some((item) => item.trim().startsWith('popup_closed='));
    if (!popupClosedCookie) {
        popup.style.display = 'block';
    } else {
        popup.style.display = 'none';
    }

    closeButton.addEventListener('click', closePopupAndSetCookie);
});
