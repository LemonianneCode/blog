window.addEventListener('pageshow', function (event) {
    var navigationEntries = window.performance &&
        typeof window.performance.getEntriesByType === 'function'
        ? window.performance.getEntriesByType('navigation')
        : [];
    var navigation = navigationEntries.length > 0 ? navigationEntries[0] : null;

    if (event.persisted || (navigation && navigation.type === 'back_forward')) {
        var loginForm = document.querySelector('form input[name="login"]');

        if (loginForm) {
            var username = document.querySelector('input[name="username"]');
            var password = document.querySelector('input[type="password"]');

            if (username) {
                username.value = '';
            }
            if (password) {
                password.value = '';
            }
        } else {
            window.location.reload();
        }
    }
});
