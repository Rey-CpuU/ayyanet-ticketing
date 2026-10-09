@props(['selected' => null, 'name' => 'customer_id', 'inputId' => 'customer_search'])

{{-- Searchable customer select: queries customers.search instead of rendering every customer. --}}
<div class="customer-picker" data-search-url="{{ route('customers.search') }}">
    <input type="hidden" name="{{ $name }}" value="{{ $selected?->id }}" class="cp-value">
    <input
        type="text"
        id="{{ $inputId }}"
        class="cp-input"
        placeholder="Cari nama, no HP, email, atau ID customer..."
        value="{{ $selected ? $selected->name.' - '.$selected->phone : '' }}"
        autocomplete="off"
        role="combobox"
        aria-autocomplete="list"
        aria-expanded="false"
        aria-controls="{{ $inputId }}-list"
        required
    >
    <ul class="cp-list" id="{{ $inputId }}-list" role="listbox" hidden></ul>
</div>

@once
    <style>
        .customer-picker { position: relative; }
        .customer-picker .cp-list {
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 4px);
            z-index: 50;
            margin: 0;
            padding: 4px;
            list-style: none;
            max-height: 260px;
            overflow-y: auto;
            background: #0f172a;
            border: 1px solid #374151;
            border-radius: 12px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
        }
        .customer-picker .cp-list li {
            padding: 10px 12px;
            border-radius: 8px;
            cursor: pointer;
            color: #e2e8f0;
        }
        .customer-picker .cp-list li[aria-selected="true"],
        .customer-picker .cp-list li:hover { background: rgba(139, 92, 246, 0.18); }
        .customer-picker .cp-list li.cp-empty { cursor: default; color: #94a3b8; background: none; }
        .customer-picker .cp-meta { display: block; margin-top: 2px; font-size: 12px; color: #94a3b8; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.customer-picker').forEach(function (picker) {
                const url = picker.dataset.searchUrl;
                const hidden = picker.querySelector('.cp-value');
                const input = picker.querySelector('.cp-input');
                const list = picker.querySelector('.cp-list');
                let items = [];
                let active = -1;
                let timer = null;
                let controller = null;

                const syncValidity = function () {
                    input.setCustomValidity(hidden.value ? '' : 'Pilih customer dari daftar hasil pencarian.');
                };

                const close = function () {
                    list.hidden = true;
                    input.setAttribute('aria-expanded', 'false');
                    active = -1;
                };

                const highlight = function (index) {
                    active = index;
                    list.querySelectorAll('li').forEach(function (li, i) {
                        li.setAttribute('aria-selected', i === index ? 'true' : 'false');
                        if (i === index) li.scrollIntoView({ block: 'nearest' });
                    });
                };

                const choose = function (customer) {
                    hidden.value = customer.id;
                    input.value = customer.name + ' - ' + customer.phone;
                    syncValidity();
                    close();
                };

                const render = function () {
                    list.replaceChildren();

                    if (items.length === 0) {
                        const li = document.createElement('li');
                        li.className = 'cp-empty';
                        li.textContent = 'Customer tidak ditemukan.';
                        list.appendChild(li);
                    }

                    items.forEach(function (customer, index) {
                        const li = document.createElement('li');
                        li.setAttribute('role', 'option');
                        li.textContent = customer.name;
                        const meta = document.createElement('span');
                        meta.className = 'cp-meta';
                        meta.textContent = customer.customer_id + ' · ' + customer.phone;
                        li.appendChild(meta);
                        li.addEventListener('mousedown', function (e) {
                            e.preventDefault();
                            choose(customer);
                        });
                        li.addEventListener('mouseenter', function () { highlight(index); });
                        list.appendChild(li);
                    });

                    list.hidden = false;
                    input.setAttribute('aria-expanded', 'true');
                    active = -1;
                };

                const search = function () {
                    if (controller) controller.abort();
                    controller = new AbortController();

                    fetch(url + '?q=' + encodeURIComponent(input.value.trim()), {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                        signal: controller.signal,
                    })
                        .then(function (res) { return res.ok ? res.json() : []; })
                        .then(function (data) { items = data; render(); })
                        .catch(function () {});
                };

                input.addEventListener('input', function () {
                    hidden.value = '';
                    syncValidity();
                    clearTimeout(timer);
                    timer = setTimeout(search, 250);
                });

                input.addEventListener('focus', function () {
                    if (!hidden.value) search();
                });

                input.addEventListener('blur', close);

                input.addEventListener('keydown', function (e) {
                    if (list.hidden || items.length === 0) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        highlight(Math.min(active + 1, items.length - 1));
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        highlight(Math.max(active - 1, 0));
                    } else if (e.key === 'Enter' && active >= 0) {
                        e.preventDefault();
                        choose(items[active]);
                    } else if (e.key === 'Escape') {
                        close();
                    }
                });

                syncValidity();
            });
        });
    </script>
@endonce
