const $ = id => document.getElementById(id);
const api = 'api/index.php';
let editId = 0;

const modal = new bootstrap.Modal($('contactModal'));

$('addContactBtn').addEventListener('click', () => {
    editId = 0;
    $('contactModalTitle').innerText = 'افزودن مخاطب';
    clearForm();
    modal.show();
});

$('saveContactBtn').addEventListener('click', async () => {
    const f = base('contact_save');
    f.append('id', editId);
    f.append('contact_name', $('contactName').value.trim());
    f.append('department', $('department').value.trim());
    f.append('mobile', $('mobile').value.trim());
    f.append('phone', $('phone').value.trim());
    f.append('email', $('email').value.trim());

    if (!f.get('contact_name') || !f.get('mobile')) {
        alert('نام مخاطب و شماره موبایل را وارد کنید.');
        return;
    }

    try {
        const d = await post(f);
        if (!d.ok) {
            alert(d.message || 'ذخیره مخاطب انجام نشد.');
            return;
        }
        location.reload();
    } catch (error) {
        console.error(error);
        alert('ارتباط با سرور برقرار نشد.');
    }
});

$('contactList').addEventListener('click', async e => {
    const r = e.target.closest('tr');
    if (!r) return;

    const id = r.dataset.id;

    if (e.target.classList.contains('edit-btn')) {
        editId = id;
        $('contactName').value = r.children[0].innerText.trim();
        $('department').value = r.children[1].innerText.trim();
        $('mobile').value = r.children[2].innerText.trim();
        $('phone').value = r.children[3].innerText.trim();
        $('email').value = r.children[4].innerText.trim();
        $('contactModalTitle').innerText = 'ویرایش مخاطب';
        modal.show();
        return;
    }

    if (e.target.classList.contains('delete-btn')) {
        if (!confirm('آیا از حذف مخاطب مطمئن هستید؟')) return;

        const f = base('contact_delete');
        f.append('id', id);

        try {
            const d = await post(f);
            if (!d.ok) {
                alert(d.message || 'حذف مخاطب انجام نشد.');
                return;
            }
            r.remove();
            if (!$('contactList').querySelector('tr')) $('noContact').style.display = 'block';
        } catch (error) {
            console.error(error);
            alert('ارتباط با سرور برقرار نشد.');
        }
    }
});

$('searchContact').addEventListener('keydown', e => {
    if (e.key === 'Enter') {
        e.preventDefault();
        filter();
    }
});

$('searchContact').addEventListener('input', () => {
    if (!$('searchContact').value.trim()) show();
});

function filter() {
    const q = $('searchContact').value.trim().toLowerCase();
    let found = false;

    $('contactList').querySelectorAll('tr').forEach(r => {
        const matches = r.innerText.toLowerCase().includes(q);
        r.style.display = matches ? '' : 'none';
        if (matches) found = true;
    });

    $('noContact').style.display = found ? 'none' : 'block';
}

function show() {
    $('contactList').querySelectorAll('tr').forEach(r => r.style.display = '');
    $('noContact').style.display = 'none';
}

function clearForm() {
    ['contactName', 'department', 'mobile', 'phone', 'email'].forEach(id => $(id).value = '');
}

function base(action) {
    const f = new FormData();
    f.append('action', action);
    f.append('csrf', APP.csrf);
    return f;
}

async function post(form) {
    const response = await fetch(api, { method: 'POST', body: form });
    const data = await response.json();
    if (!response.ok && !data) throw new Error('HTTP ' + response.status);
    return data;
}
