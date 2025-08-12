<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8" />
  <title>{{ __('messages.cart') }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Hanuman:wght@400;700&family=Noto+Sans+Khmer:wght@400;700&display=swap" rel="stylesheet">
  <style>
    
    :root{
      --brand:#05669f;
      --panel:#ffffff;
      --bg:#f2f5fa;
      --head:#e9f1ff;
      --text:#24364d;
      --muted:#8aa0b6;
      --value:#0f766e;
      --danger:#b91c1c;
      --shadow:0 8px 22px rgba(0,0,0,.06);
    }
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);font-family:'Noto Sans Khmer','Hanuman',system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:var(--text)}
    .header-bar{background:var(--brand);color:#fff;font-weight:700;padding:16px 24px;font-size:28px;display:flex;justify-content:center;align-items:center;box-shadow:0 2px 8px rgba(0,0,0,.08);overflow:hidden}
    .header-title{white-space:nowrap;display:inline-block;animation:headerMarquee 16s linear infinite}
    .header-bar:hover .header-title{animation-play-state:paused}
    @keyframes headerMarquee{0%{transform:translateX(100%)}100%{transform:translateX(-100%)}}
    @media (prefers-reduced-motion:reduce){.header-title{animation:none}}

    .screen{max-width:1280px;margin:24px auto;padding:0 16px;display:flex;gap:18px}
    .left,.right{background:var(--panel);border-radius:14px;box-shadow:var(--shadow);flex:0 0 50%;padding:18px;min-height:520px}
    .right{display:flex;flex-direction:column}
    .section-title{font-size:22px;font-weight:700;margin:4px 0 14px}

    table{width:100%;border-collapse:collapse;border-radius:10px;overflow:hidden}
    thead th{background:var(--head);color:var(--brand);font-weight:700;font-size:16px;padding:12px 10px;text-align:center}
    tbody td{padding:14px 10px;text-align:center;border-bottom:1px solid #edf2f7;font-size:16px}
    tbody tr:last-child td{border-bottom:none}
    .name{text-align:left;font-weight:500}
    .num{color:#334155}

    .summary{margin-top:12px;display:flex;flex-direction:column;gap:6px;align-items:flex-end;font-weight:700}
    .summary .row{display:flex;gap:10px;align-items:baseline}
    .summary .label{opacity:.9}
    .summary .value{color:var(--value);font-weight:800;min-width:140px;text-align:right}

    .thumb-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;overflow-y:auto;padding-right:4px;scrollbar-width:thin;max-height:100%;min-height:140px}
    .thumb{position:relative;border-radius:10px;overflow:hidden;background:#f7fafc;box-shadow:0 2px 8px rgba(0,0,0,.06)}
    .thumb img{display:block;width:100%;height:100px;object-fit:cover}
    .tag{position:absolute;right:6px;bottom:6px;padding:2px 6px;font-size:11px;background:rgba(0,0,0,.65);color:#fff;border-radius:999px}
    .hint{color:var(--muted);font-size:14px;margin-top:8px;text-align:center}

    @media (max-width: 992px){.screen{flex-direction:column}.left,.right{flex:1}}
    @media (max-width: 620px){thead th,tbody td{font-size:14px;padding:10px}.section-title{font-size:20px}.thumb-grid{grid-template-columns:repeat(2,1fr)}.thumb img{height:90px}}
  </style>
</head>
<body>

  <div class="header-bar"><span class="header-title">{{ __('messages.your_order') }}</span></div>

  <div class="screen">
    <div class="left">
      <div class="section-title">{{ __('messages.cart') }}</div>
      <table>
        <thead>
          <tr>
            <th style="width:70px">{{ __('messages.no') }}</th>
            <th style="text-align:left">{{ __('messages.product') }}</th>
            <th style="width:110px">{{ __('messages.qty') ?? 'Qty' }}</th>
            <th style="width:130px">{{ __('messages.unit_price') ?? 'Unit Price' }}</th>
            <th style="width:150px">{{ __('messages.price') }}</th>
          </tr>
        </thead>
        <tbody id="orderItems"></tbody>
      </table>


      <div class="summary">
        <div class="row"><span class="label">{{ __('messages.total_items') }}:</span><span class="value" id="totalItems">0</span></div>
        <div class="row"><span class="label">{{ __('messages.grand_total') }}:</span><span class="value" id="totalAmount">$0.00</span></div>
      </div>
    </div>

    <div class="right">
      <div class="section-title">{{ __('messages.image') }}</div>
      <div class="thumb-grid" id="thumbGrid"></div>
      <div class="hint" id="thumbHint"></div>
    </div>
  </div>

  <script>
    const transCartEmpty = @js(__('messages.cart_empty'));

    function toArray(payload){
      if (Array.isArray(payload)) return payload;
      if (payload && Array.isArray(payload.items)) return payload.items;
      if (payload && Array.isArray(payload.data)) return payload.data;
      return [];
    }
    function asNumber(v, fallback=0){
      const n = Number(v);
      return Number.isFinite(n) ? n : fallback;
    }
    function normItem(raw){
      const name = raw.name ?? raw.product_name ?? raw.title ?? '—';
      const quantity = asNumber(raw.quantity ?? raw.qty ?? raw.count ?? 1, 1);
      let unit = asNumber(raw.unit_price ?? raw.price_per_unit ?? raw.priceEach ?? raw.price_unit ?? raw.price, 0);
      let line = asNumber(raw.line_total ?? raw.total ?? raw.amount ?? raw.subtotal, 0);
      if (!line) {
        const p = asNumber(raw.price, 0);
        if (!unit && p && quantity) {
          unit = p;
        }
        line = unit * quantity;
      }
      const image = raw.image ?? raw.thumbnail ?? raw.photo ?? raw.image_url ?? raw.thumbnail_url ?? '';
      return { name, quantity, unit, line, image };
    }

    async function fetchCartAndSummary(){
      const cartRaw = await fetch('/api/cart').then(r=>r.json()).catch(()=>[]);
      const rowsRaw = toArray(cartRaw);
      const items = rowsRaw.map(normItem);

      const tbody = document.getElementById('orderItems');
      const totalAmountEl = document.getElementById('totalAmount');
      const totalItemsEl = document.getElementById('totalItems');
      const grid = document.getElementById('thumbGrid');
      const hint = document.getElementById('thumbHint');

      if(items.length===0){
        tbody.innerHTML = `<tr><td colspan="5" style="padding:18px;text-align:center;color:#64748b">${transCartEmpty}</td></tr>`;
        totalAmountEl.textContent = "$0.00";
        totalItemsEl.textContent = "0";
        grid.innerHTML = "";
        hint.textContent = "";
        return;
      }

      let rowsHtml='', subtotal=0, count=0;
      items.forEach((it, i)=>{
        subtotal += it.line;
        count += it.quantity;
        rowsHtml += `
          <tr>
            <td class="num">${i+1}</td>
            <td class="name">${it.name}</td>
            <td>${it.quantity}</td>
            <td>$${it.unit.toFixed(2)}</td>
            <td>$${it.line.toFixed(2)}</td>
          </tr>`;
      });
      tbody.innerHTML = rowsHtml;

      totalItemsEl.textContent = count;
      totalAmountEl.textContent = `$${subtotal.toFixed(2)}`;

      grid.innerHTML = items.map(it => `
        <div class="thumb">
          <img src="/storage/${it.image}" alt="${it.name}">
          <div class="tag">$${it.line.toFixed(2)}</div>
        </div>`).join('');
      hint.textContent = '';
    }

    setInterval(fetchCartAndSummary, 2000);
    fetchCartAndSummary();
  </script>
</body>
</html>
