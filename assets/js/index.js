const $ = id => document.getElementById(id);
const api = 'api/index.php';
let editId = 0;

const siteModal = new bootstrap.Modal($('siteModal'));
const historyModal = new bootstrap.Modal($('historyModal'));

$('addBtn').onclick = () => {
    editId = 0;
    $('siteModalTitle').innerText = 'افزودن سایت';
    clearForm();
    siteModal.show();
};

$('saveSite').onclick = async () => {
    const fd = new FormData();
    fd.append('action', 'site_save');
    fd.append('csrf', APP.csrf);
    fd.append('id', editId);
    fd.append('url_name', $('siteName').value.trim());
    fd.append('url', $('siteUrl').value.trim());
    fd.append('account', $('siteUser').value.trim());
    fd.append('password', $('sitePass').value);

    if (!fd.get('url_name') || !fd.get('url') || !fd.get('account') || !fd.get('password')) {
        return alert('لطفاً همه اطلاعات را وارد کنید.');
    }

    const d = await post(fd);
    if (!d.ok) return alert(d.message);
    location.reload();
};

$('siteList').onclick = async e => {
    const row = e.target.closest('tr');
    if (!row) return;

    const id = row.dataset.id;

    if (e.target.classList.contains('edit-btn')) {
        editId = id;
        $('siteModalTitle').innerText = 'ویرایش سایت';
        $('siteName').value = row.querySelector('.site-name').innerText.trim();
        $('siteUrl').value = row.children[1].innerText.trim();
        $('siteUser').value = row.querySelector('.account-text').innerText.trim();
        $('sitePass').value = row.querySelector('.password').innerText.trim();
        siteModal.show();
    } else if (e.target.classList.contains('delete-btn')) {
        if (confirm('آیا از حذف سایت مطمئن هستید؟')) {
            const fd = base('site_delete');
            fd.append('id', id);
            const d = await post(fd);
            if (d.ok) row.remove();
            else alert(d.message);
        }
    } else if (e.target.classList.contains('history-btn')) {
        const r = await fetch(api + '?action=site_history&id=' + encodeURIComponent(id));
        const d = await r.json();
        if (!d.ok) return alert(d.message);

        $('historyTitle').innerText = 'تاریخچه رمز ' + row.querySelector('.site-name').innerText.trim();
        $('historyList').innerHTML = d.items.length
            ? d.items.map(x => `<tr><td>${fmt(x.changed_at)}</td><td>${esc(x.username)}</td><td>${esc(x.old_password)}</td><td>${esc(x.new_password)}</td></tr>`).join('')
            : '<tr><td colspan="4">تاریخچه‌ای ثبت نشده است.</td></tr>';
        historyModal.show();
    }
};

$('search').addEventListener('keydown', e => {
    if (e.key === 'Enter') {
        e.preventDefault();
        filterRows($('search'), $('siteList'), $('noResult'));
    }
});

$('search').addEventListener('input', () => {
    if (!$('search').value.trim()) showAll($('siteList'));
});

$('logoutBtn').addEventListener('click', () => {});

function filterRows(input, list, no) {
    const q = input.value.trim().toLowerCase();
    let found = false;

    list.querySelectorAll('tr').forEach(r => {
        const yes = r.innerText.toLowerCase().includes(q);
        r.style.display = yes ? '' : 'none';
        found ||= yes;
    });

    no.style.display = found ? 'none' : 'block';
}

function showAll(list) {
    list.querySelectorAll('tr').forEach(r => r.style.display = '');
    $('noResult').style.display = 'none';
}

function clearForm() {
    ['siteName', 'siteUrl', 'siteUser', 'sitePass'].forEach(x => $(x).value = '');
}

function base(a) {
    const f = new FormData();
    f.append('action', a);
    f.append('csrf', APP.csrf);
    return f;
}

async function post(f) {
    const r = await fetch(api, { method: 'POST', body: f });
    return r.json();
}

function esc(s) {
    return String(s ?? '').replace(/[&<>'"]/g, m => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#039;',
        '"': '&quot;',
    }[m]));
}

function fmt(s) {
    const d = new Date(String(s).replace(' ', 'T'));
    return isNaN(d) ? esc(s) : d.toLocaleString('fa-IR');
}
