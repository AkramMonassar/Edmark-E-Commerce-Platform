/* ===== لوحة التحكم: حفظ AJAX بدون تحميل ===== */
async function adminApi(action, data = {}) {
    const body = new FormData();
    body.append('action', action);
    body.append('csrf_token', window.CSRF || '');
    for (const k in data) body.append(k, data[k]);
    const res = await fetch('api/admin.php', { method: 'POST', body });
    return res.json();
}

document.querySelectorAll('form').forEach(f => {
    const act = f.querySelector('input[name="action"]');
    if (!act) return;
    const v = act.value;


    if (v === 'toggle_user') {
        f.addEventListener('submit', async e => {
            e.preventDefault();
            const r = await adminApi('toggle_user', Object.fromEntries(new FormData(f).entries()));
            if (r.ok) {
                const b = f.closest('tr').querySelector('.badge');
                if (b) { b.textContent = r.type; b.className = 'badge ' + (r.type === 'admin' ? 'text-bg-danger' : 'text-bg-secondary'); }
                toast('تم تغيير الصلاحية');
            } else toast(r.error === 'self' ? 'لا يمكنك تغيير نفسك' : 'تعذر التغيير', 'danger');
        });
    }

    if (v === 'delete_user') {
        f.addEventListener('submit', async e => {
            e.preventDefault();
            if (!confirm('حذف هذا المستخدم نهائيًا؟')) return;
            const r = await adminApi('delete_user', Object.fromEntries(new FormData(f).entries()));
            if (r.ok) { f.closest('tr').remove(); toast('تم حذف المستخدم'); }
            else toast(r.error === 'self' ? 'لا يمكنك حذف نفسك' : 'تعذر الحذف', 'danger');
        });
    }

    if (v === 'toggle_coupon') {
        f.addEventListener('submit', async e => {
            e.preventDefault();
            const r = await adminApi('toggle_coupon', Object.fromEntries(new FormData(f).entries()));
            if (r.ok) {
                const b = f.closest('tr').querySelector('.badge');
                if (b) { b.textContent = r.active ? 'فعال' : 'معطل'; b.className = 'badge ' + (r.active ? 'text-bg-success' : 'text-bg-secondary'); }
                toast('تم تغيير حالة الكوبون');
            }
        });
    }

    if (v === 'delete_coupon') {
        f.addEventListener('submit', async e => {
            e.preventDefault();
            if (!confirm('حذف هذا الكوبون؟')) return;
            const r = await adminApi('delete_coupon', Object.fromEntries(new FormData(f).entries()));
            if (r.ok) { f.closest('tr').remove(); toast('تم حذف الكوبون'); }
        });
    }
});

/* ===== المنتجات: حفظ فردي + حفظ كلي من بيانات الصف (بدون فورمات) ===== */
function productRowData(tr) {
    return {
        p_id: tr.dataset.pid,
        p_name: tr.querySelector('.f-name').value,
        p_price: tr.querySelector('.f-price').value,
        p_quantity: tr.querySelector('.f-qty').value,
        category_id: tr.querySelector('.f-cat').value,
        p_describe: tr.querySelector('.f-desc').value
    };
}

// حفظ منتج واحد
document.querySelectorAll('.save-one').forEach(btn => {
    btn.addEventListener('click', async () => {
        const r = await adminApi('save_product', productRowData(btn.closest('tr')));
        toast(r.message || 'خطأ بالحفظ', r.ok ? 'success' : 'danger');
    });
});

// حفظ كل التعديلات
const saveAllBtn = document.getElementById('saveAllProducts');
if (saveAllBtn) saveAllBtn.addEventListener('click', async () => {
    const rows = [...document.querySelectorAll('tr[data-pid]')].map(productRowData);
    if (rows.length === 0) { toast('لا توجد منتجات للتحديث', 'warning'); return; }
    const r = await adminApi('save_products', { rows: JSON.stringify(rows) });
    toast(r.message || 'تم الحفظ', r.ok ? 'success' : 'danger');
});