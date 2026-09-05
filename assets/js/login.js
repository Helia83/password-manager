const loginForm = document.getElementById('usernamePass');

loginForm.addEventListener('submit', async e => {
    e.preventDefault();

    const fd = new FormData(loginForm);
    fd.append('action', 'login');
    fd.append('csrf', window.APP.csrf);

    try {
        const r = await fetch('api/index.php', { method: 'POST', body: fd });
        const d = await r.json();

        if (!d.ok) {
            alert(d.message || 'ورود ناموفق بود.');
            return;
        }

        location.href = d.redirect;
    } catch (e) {
        alert('ارتباط با سرور برقرار نشد.');
    }
});
