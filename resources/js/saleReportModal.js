export default function saleReportModal({ routePrefix }) {
    const loadingRow = '<tr><td colspan="5" class="text-center text-muted"><div class="spinner-border spinner-border-sm text-secondary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
    const hideFlags = new Set(['No Vegetables','No Sweet','No Salty','No Spicy']);

    document.querySelectorAll('.view-invoice').forEach(btn => {
        btn.addEventListener('click', function () {
            const saleId = this.dataset.saleId;
            const docTbody = document.querySelector('#docTable tbody');
            docTbody.innerHTML = loadingRow;

            document.getElementById('reportPdfLink').href = `/${routePrefix}/invoice/${saleId}/pdf`;

            fetch(`/${routePrefix}/sales/${saleId}/invoice`)
                .then(res => res.text())
                .then(html => {
                    const parser   = new DOMParser();
                    const doc      = parser.parseFromString(html, 'text/html');
                    const bodyText = doc.body ? doc.body.innerText : '';

                    const grab = (label) => {
                        const rx = new RegExp(label + '\\s*:?\\s*(.+)', 'i');
                        const m  = bodyText.match(rx);
                        return (m && m[1]) ? m[1].trim() : '—';
                    };
                    const parseAmountLoose = (line) => {
                        const num = (line.match(/[0-9][0-9.,]*/) || [''])[0].replace(/,/g,'');
                        const sym = (line.match(/[\$៛]/) || [''])[0];
                        return num ? `${sym || ''}${Number(num).toFixed(2)}` : '—';
                    };
                    const hasLetters = (s) => /[\p{L}]/u.test(s || '');

                    const mNo = bodyText.match(/No\:\s*([A-Za-z0-9\-\_]+)/);
                    document.getElementById('docInvoiceNo').textContent = mNo ? mNo[1] : '—';
                    document.getElementById('docDate').textContent     = grab('Date');
                    document.getElementById('docCashier').textContent  = grab('Cashier');
                    document.getElementById('docTableName').textContent= grab('Table');

                    document.getElementById('docGrand').textContent    = grab('Grand Total');
                    document.getElementById('docSubtotal').textContent = grab('Subtotal');
                    const d = grab('Discount');
                    document.getElementById('docDiscount').textContent = d === '—' ? '$0.00' : d;
                    document.getElementById('docPayment').textContent  = grab('Payment Method');

                    const cashUsdMatch    = bodyText.match(/Cash\s*Received\s*\((?:USD|\$)\)\s*:\s*([^\n]+)/i);
                    const cashRielMatch   = bodyText.match(/Cash\s*Received\s*\((?:Riel|៛)\)\s*:\s*([^\n]+)/i);
                    const changeUsdMatch  = bodyText.match(/Change\s*\((?:USD|\$)\)\s*:\s*([^\n]+)/i);
                    const changeRielMatch = bodyText.match(/Change\s*\((?:Riel|៛)\)\s*:\s*([^\n]+)/i);

                    document.getElementById('docCashUsd').textContent   = cashUsdMatch    ? parseAmountLoose(cashUsdMatch[0]) : '$0.00';
                    document.getElementById('docCashRiel').textContent  = cashRielMatch   ? parseAmountLoose(cashRielMatch[0]) : '៛0.00';
                    document.getElementById('docChangeUsd').textContent = changeUsdMatch  ? parseAmountLoose(changeUsdMatch[0]) : '$0.00';
                    document.getElementById('docChangeRiel').textContent= changeRielMatch ? parseAmountLoose(changeRielMatch[0]) : '៛0.00';

                    docTbody.innerHTML = '';

                    const tables = Array.from(doc.querySelectorAll('table'));
                    const norm   = s => (s || '').replace(/\s+/g, ' ').trim().toLowerCase();

                    let invoiceTable = null;
                    for (const t of tables) {
                        let hs = Array.from(t.querySelectorAll('thead th'));
                        if (!hs.length) {
                            const fr = t.querySelector('tr');
                            if (fr) hs = Array.from(fr.querySelectorAll('th,td'));
                        }
                        const headerText = hs.map(h => norm(h.textContent));
                        const hasItem  = headerText.some(h => /(item|ឥវ៉ាន់|មុខទំនិញ)/.test(h));
                        const hasQty   = headerText.some(h => /(qty|quantity|បរិមាណ)/.test(h));
                        const hasPrice = headerText.some(h => /(price|unit|តម្លៃ)/.test(h));
                        const hasTot   = headerText.some(h => /(total|សរុប)/.test(h));
                        if (hasItem && hasQty && hasPrice && hasTot) { invoiceTable = t; break; }
                    }
                    if (!invoiceTable) {
                        invoiceTable = tables.sort((a,b) => {
                            const ca = (a.querySelector('tr')?.querySelectorAll('td,th').length) || 0;
                            const cb = (b.querySelector('tr')?.querySelectorAll('td,th').length) || 0;
                            return cb - ca;
                        })[0];
                    }

                    let rows = Array.from(invoiceTable?.querySelectorAll('tbody tr') || []);
                    if (!rows.length) {
                        const all = Array.from(invoiceTable?.querySelectorAll('tr') || []);
                        rows = all.slice(1);
                    }

                    let snAuto = 0;
                    rows.forEach(r => {
                        const tds = Array.from(r.querySelectorAll('td,th'));
                        if (!tds.length) return;

                        const texts = tds.map(td => td.innerText.trim());

                        let sn = texts.find(x => /^\d+$/.test(x));
                        if (!sn) sn = String(++snAuto);

                        let itemTdEl = tds.find(td => hasLetters(td.innerText) && !/^[\d\$\s.,៛]+$/.test(td.innerText)) || tds[1];
                        let itemName = '—';
                        if (itemTdEl) {
                            const clone = itemTdEl.cloneNode(true);
                            clone.querySelectorAll('ul,ol,li,small,span.badge').forEach(n => n.remove());
                            itemName = (clone.innerText || '').replace(/\s+/g,' ').trim()
                                .replace(/\b(Small|Medium|Large)\s*Size\b/ig,'')
                                .replace(/\bSugar\s*:?\s*\d+%/ig,'')
                                .replace(/\bLess\s*Ice\b/ig,'')
                                .replace(/\bNo\s+(Salty|Spicy|Sweet|Vegetables)\b/ig,'')
                                .replace(/\s{2,}/g,' ')
                                .trim();
                        }

                        let qty = texts.find(x => /^\d+$/.test(x) && x !== sn) || '1';

                        const moneyCells = texts.filter(x => /[\$៛]|^\d+([.,]\d{2,})$/.test(x));
                        const price = moneyCells[0] || '';
                        const total = moneyCells[moneyCells.length - 1] || price;

                        const bullets = Array.from(r.querySelectorAll('li')).map(li => li.textContent.trim()).filter(Boolean);
                        let modifiersHtml = '';
                        if (bullets.length) {
                            let size = '';
                            const sugarIce = [];
                            const others = [];
                            bullets.forEach(b => {
                                const lower = b.toLowerCase();
                                if (/small/.test(lower)) size = 'S';
                                else if (/medium/.test(lower)) size = 'M';
                                else if (/large/.test(lower)) size = 'L';
                                else if (/sugar|ice/.test(lower)) sugarIce.push(b);
                                else others.push(b);
                            });
                            const chips = [];
                            if (size) chips.push(`<span class="badge rounded-pill bg-body-secondary text-muted border lh-sm">${size}</span>`);
                            if (sugarIce.length) chips.push(`<span class="small text-muted lh-sm">${sugarIce.join(' • ')}</span>`);
                            if (others.length) chips.push(others.map(o => `<span class="badge rounded-pill bg-body-secondary text-muted border lh-sm">${o}</span>`).join(''));
                            modifiersHtml = chips.length ? `<div class="chipline mt-1">${chips.join('')}</div>` : '';
                        }

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${sn}</td>
                            <td>${itemName}${modifiersHtml}</td>
                            <td>${qty}</td>
                            <td>${price}</td>
                            <td>${total}</td>
                        `;
                        docTbody.appendChild(tr);
                    });

                    if (!docTbody.children.length) {
                        docTbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No items found in invoice.</td></tr>`;
                    }

                    const allBullets = Array.from(doc.querySelectorAll('li'))
                        .filter(li => !invoiceTable?.contains(li))
                        .map(li => li.textContent.trim())
                        .filter(t => t && !hideFlags.has(t));
                    document.getElementById('docOptions').innerHTML = allBullets.length
                        ? `<ul class="mb-0">${allBullets.map(b => `<li>${b}</li>`).join('')}</ul>` : '—';

                    const subtotalEl = document.getElementById('docSubtotal');
                    if (subtotalEl.textContent === '—') {
                        let subtotal = 0;
                        document.querySelectorAll('#docTable tbody tr').forEach(tr => {
                            const t = tr.children[4];
                            if (t) subtotal += parseFloat((t.textContent || '').replace(/[^0-9.\-]/g,'')) || 0;
                        });
                        const sym = (document.getElementById('docGrand').textContent.match(/[^0-9.,\-\s]/) || [''])[0];
                        subtotalEl.textContent = `${sym || ''}${subtotal.toFixed(2)}`;
                    }

                    new bootstrap.Modal(document.getElementById('reportModal')).show();
                })
                .catch(() => {
                    docTbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Failed to load sale details.</td></tr>`;
                    new bootstrap.Modal(document.getElementById('reportModal')).show();
                });
        });
    });

    const reportModal = document.getElementById('reportModal');
    if (reportModal) {
        reportModal.addEventListener('hidden.bs.modal', function () {
            document.querySelector('#docTable tbody').innerHTML = loadingRow;
            document.getElementById('docOptions').textContent = '—';
            ['docInvoiceNo','docDate','docCashier','docGrand','docSubtotal','docDiscount',
             'docCashUsd','docCashRiel','docChangeUsd','docChangeRiel','docPayment','docTableName']
             .forEach(id => document.getElementById(id).textContent = '—');
        });
    }
}

window.saleReportModal = saleReportModal;