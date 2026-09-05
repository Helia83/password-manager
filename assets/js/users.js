const $ = id => document.getElementById(id);
const api = 'api/index.php';
let editId = 0;

const modal = new bootstrap.Modal($('userModal'));

$('addUserBtn').addEventListener('click', () => {
    editId = 0;
    $('userModalTitle').innerText = 'افزودن کاربر';
    $('username').value = '';
    $('password').value = '';
    $('role').value = 'User';
    modal.show();
});

$('saveUserBtn').addEventListener('click', async () => {
    const username = $('username').value.trim();
    const password = $('password').value;

    if (!username) {
        alert('نام کاربری الزامی است.');
        return;
    }
    if (!editId && !password) {
        alert('برای کاربر جدید، رمز عبور الزامی است.');
        return;
    }

    const f = base('user_save');
    f.append('id', editId);
    f.append('username', username);
    f.append('password', password);
    f.append('role', $('role').value);

    try {
        const d = await post(f);
        if (d.ok) {
            location.reload();
        } else {
            alert(d.message || 'ذخیره کاربر انجام نشد.');
        }
    } catch (error) {
        console.error(error);
        alert('ارتباط با سرور برقرار نشد.');
    }
});

$('userList').addEventListener('click', async e => {
    const r = e.target.closest('tr');
    if (!r) return;

    const id = r.dataset.id;

    if (e.target.classList.contains('edit-btn')) {
        editId = id;
        $('username').value = r.children[0].innerText.trim();
        $('password').value = '';
        $('role').value = r.querySelector('span').innerText.trim();
        $('userModalTitle').innerText = 'ویرایش کاربر';

        try {
            const f = base('user_get');
            f.append('id', id);
            const d = await post(f);
            if (d.ok) $('password').value = d.password || '';
        } catch (error) {
            console.error(error);
        }

        modal.show();
        return;
    }

    if (e.target.classList.contains('delete-btn') && confirm('آیا از حذف کاربر مطمئن هستید؟')) {
        const f = base('user_delete');
        f.append('id', id);

        try {
            const d = await post(f);
            if (d.ok) r.remove();
            else alert(d.message || 'حذف کاربر انجام نشد.');
        } catch (error) {
            console.error(error);
            alert('ارتباط با سرور برقرار نشد.');
        }
    }
});

function base(action) {
    const f = new FormData();
    f.append('action', action);
    f.append('csrf', APP.csrf);
    return f;
}

async function post(form) {
    const response = await fetch(api, { method: 'POST', body: form });
    return response.json();
}
