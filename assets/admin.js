/* ===== لوحة التحكم: AJAX بدون تحميل ===== */
async function adminApi(action, data = {}) {
    const body = new FormData();
    body.append('action', action);
    body.append('csrf_token', window.CSRF || '');
    for (const k in data) body.append(k, data[k]);
    const res = await fetch('api/admin.php', { method: 'POST', body });
    return res.json();
}

/* ===== نافذة تأكيد احترافية ===== */
function confirmDialog(message, title = 'تأكيد الحذف') {
    return new Promise(resolve => {
        let modal = document.getElementById('adminConfirmModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'adminConfirmModal';
            modal.className = 'modal fade';
            modal.tabIndex = -1;
            modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                  <h6 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill"></i> <span id="acmTitle"></span></h6>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body pt-0"><p id="acmMsg" class="mb-0 text-muted"></p></div>
                <div class="modal-footer border-0 pt-0">
                  <button type="button" class="btn btn-light border" data-bs-dismiss="modal">إلغاء</button>
                  <button type="button" class="btn btn-danger" id="acmYes"><i class="bi bi-trash"></i> حذف نهائيًا</button>
                </div>
              </div>
            </div>`;
            document.body.appendChild(modal);
            modal.querySelector('#acmYes').addEventListener('click', () => {
                if (modal._resolve) modal._resolve(true);
                bootstrap.Modal.getOrCreateInstance(modal).hide();
            });
            modal.addEventListener('hidden.bs.modal', () => {
                if (modal._resolve) modal._resolve(false);
                modal._resolve = null;
            });
        }
        modal._resolve = resolve;
        modal.querySelector('#acmTitle').textContent = title;
        modal.querySelector('#acmMsg').textContent = message;
        bootstrap.Modal.getOrCreateInstance(modal).show();
    });
}

/* ===== شارة المخزون الحية ===== */
function stockBadge(q) {
    if (q <= 0) return '<span class="badge text-bg-danger">نفد</span>';
    if (q <= 5) return '<span class="badge text-bg-warning">منخفض: ' + q + '</span>';
    return '<span class="badge text-bg-success">متوفر</span>';
}
function refreshRowBadge(tr) {
    const cell = tr.querySelector('.stock-cell');
    if (!cell) return;
    const q = parseInt(tr.querySelector('.f-qty').value, 10) || 0;
    cell.innerHTML = stockBadge(q);
}

/* ===== المنتجات ===== */
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

document.querySelectorAll('.save-one').forEach(btn => {
    btn.addEventListener('click', async () => {
        const tr = btn.closest('tr');
        const r = await adminApi('save_product', productRowData(tr));
        if (r.ok) refreshRowBadge(tr);
        toast(r.message || 'خطأ بالحفظ', r.ok ? 'success' : 'danger');
    });
});

document.querySelectorAll('.delete-one').forEach(btn => {
    btn.addEventListener('click', async () => {
        const ok = await confirmDialog('سيُحذف المنتج نهائيًا ومن سلات العملاء أيضًا.');
        if (!ok) return;
        const r = await adminApi('delete_product', { p_id: btn.closest('tr').dataset.pid });
        if (r.ok) { btn.closest('tr').remove(); toast(r.message || 'تم حذف المنتج 🗑️'); }
        else toast('تعذر الحذف', 'danger');
    });
});

document.querySelectorAll('.f-qty').forEach(inp => {
    inp.addEventListener('input', () => refreshRowBadge(inp.closest('tr')));
});

const saveAllBtn = document.getElementById('saveAllProducts');
if (saveAllBtn) saveAllBtn.addEventListener('click', async () => {
    const rows = [...document.querySelectorAll('tr[data-pid]')].map(productRowData);
    if (rows.length === 0) { toast('لا توجد منتجات للتحديث', 'warning'); return; }
    const r = await adminApi('save_products', { rows: JSON.stringify(rows) });
    if (r.ok) document.querySelectorAll('tr[data-pid]').forEach(refreshRowBadge);
    toast(r.message || 'تم الحفظ', r.ok ? 'success' : 'danger');
});

/* ===== المستخدمون والكوبونات ===== */
document.querySelectorAll('form').forEach(f => {
    const act = f.querySelector('input[name="action"]');
    if (!act) return;
    const v = act.value;

    if (v === 'toggle_admin') {
        f.addEventListener('submit', async e => {
            e.preventDefault();
            const r = await adminApi('toggle_user', { user_id: f.querySelector('[name="user_id"]').value });
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
            const ok = await confirmDialog('سيُحذف هذا المستخدم نهائيًا.');
            if (!ok) return;
            const r = await adminApi('delete_user', { user_id: f.querySelector('[name="user_id"]').value });
            if (r.ok) { f.closest('tr').remove(); toast('تم حذف المستخدم'); }
            else toast(r.error === 'self' ? 'لا يمكنك حذف نفسك' : 'تعذر الحذف', 'danger');
        });
    }

    if (v === 'toggle_coupon') {
        f.addEventListener('submit', async e => {
            e.preventDefault();
            const r = await adminApi('toggle_coupon', { coupon_id: f.querySelector('[name="coupon_id"]').value });
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
            const ok = await confirmDialog('سيُحذف هذا الكوبون نهائيًا.');
            if (!ok) return;
            const r = await adminApi('delete_coupon', { coupon_id: f.querySelector('[name="coupon_id"]').value });
            if (r.ok) { f.closest('tr').remove(); toast('تم حذف الكوبون'); }
        });
    }
});