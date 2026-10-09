<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Ticket</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(180deg, #020817 0%, #0f172a 100%);
            color: #e2e8f0;
        }

        .wrap {
            max-width: 760px;
            margin: 40px auto;
            padding: 24px;
        }

        .card {
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid #243044;
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.32);
        }

        h2 {
            margin: 0 0 18px;
            font-size: 2rem;
        }

        .top-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            padding: 10px 18px;
            border-radius: 12px;
            background: linear-gradient(180deg, #1A1F2D, rgba(26, 31, 45, 0.7));
            border: 1px solid rgba(255, 255, 255, 0.05);
            color: #A0A6B4;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow:
                0 12px 40px rgba(0, 0, 0, 0.45),
                0 4px 12px rgba(124, 92, 255, 0.06),
                inset 0 1px 0 rgba(255, 255, 255, 0.04);
            transition: all 200ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        .top-link:hover {
            background: #1E2434;
            color: #FFFFFF;
            border-color: rgba(124, 92, 255, 0.2);
            transform: translateY(-2px);
            box-shadow:
                0 20px 50px rgba(0, 0, 0, 0.55),
                0 8px 20px rgba(124, 92, 255, 0.12),
                inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }

        .top-link:active {
            transform: translateY(0) scale(0.97);
        }

        .top-link svg {
            transition: transform 200ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        .top-link:hover svg {
            transform: translateX(-3px);
        }

        label {
            display: block;
            margin-top: 18px;
            margin-bottom: 8px;
            font-weight: 700;
            color: #e2e8f0;
        }

        input, select, textarea, button {
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid #374151;
            background: #0f172a;
            color: #e2e8f0;
            font-size: 15px;
        }

        input:focus, select:focus, textarea:focus {
            outline: 2px solid rgba(139, 92, 246, 0.4);
            border-color: #8b5cf6;
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        button {
            background: #8b5cf6;
            color: white;
            border: none;
            font-weight: 700;
            margin-top: 20px;
            cursor: pointer;
        }

        .error-message {
            margin-top: 6px;
            color: #fca5a5;
            font-size: 13px;
        }

        .field-error input,
        .field-error select,
        .field-error textarea {
            border-color: #f87171 !important;
            box-shadow: 0 0 0 2px rgba(248, 113, 113, 0.15);
        }

        .success-banner {
            background: #166534;
            color: #fff;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }

        .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            vertical-align: middle;
            margin-right: 8px;
        }

        button.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        button.loading .spinner {
            display: inline-block;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .suggestion {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 12px;
            padding: 10px 14px;
            border-radius: 12px;
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.3);
            color: #c4b5fd;
            font-size: 14px;
        }

        .suggestion[hidden] { display: none; }

        .suggestion .suggestion-apply {
            width: auto;
            margin: 0;
            padding: 6px 12px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <h2>New Ticket</h2>
        <a href="{{ route('dashboard') }}" class="top-link">
            <svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 4l-6 6 6 6M4 10h12" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Kembali ke Dashboard
        </a>

        <div class="card">
        @if(session('success'))
            <div class="success-banner">✅ {{ session('success') }}</div>
        @endif

        <x-notification />

            <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <label for="customer_search">Customer <span style="color:#f87171;">*</span></label>
                <div class="{{ $errors->has('customer_id') ? 'field-error' : '' }}">
                    <x-customer-picker :selected="$selectedCustomer" />
                </div>
                @error('customer_id')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <label for="title">Judul Tiket <span style="color:#f87171;">*</span></label>
                <div class="{{ $errors->has('title') ? 'field-error' : '' }}">
                    <input type="text" name="title" id="title" placeholder="Masukkan judul tiket" value="{{ old('title') }}" required>
                </div>
                @error('title')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <label for="category">Kategori</label>
                <div class="{{ $errors->has('category') ? 'field-error' : '' }}">
                    <select name="category" id="category">
                        <option value="Email" {{ old('category') == 'Email' ? 'selected' : '' }}>Email</option>
                        <option value="Portal" {{ old('category') == 'Portal' ? 'selected' : '' }}>Portal</option>
                        <option value="WhatsApp" {{ old('category') == 'WhatsApp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="Live Chat" {{ old('category') == 'Live Chat' ? 'selected' : '' }}>Live Chat</option>
                    </select>
                </div>
                @error('category')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <label for="priority">Prioritas</label>
                <div class="{{ $errors->has('priority') ? 'field-error' : '' }}">
                    <select name="priority" id="priority">
                        <option value="Low" {{ old('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Medium" {{ old('priority', 'Medium') == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="High" {{ old('priority') == 'High' ? 'selected' : '' }}>High</option>
                    </select>
                </div>
                @error('priority')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <div id="classify-suggestion" class="suggestion" hidden>
                    <span>Saran otomatis: <strong id="classify-text"></strong></span>
                    <button type="button" id="classify-apply" class="suggestion-apply">Terapkan</button>
                </div>

                <label for="description">Deskripsi <span style="color:#f87171;">*</span></label>
                <div class="{{ $errors->has('description') ? 'field-error' : '' }}">
                    <textarea name="description" id="description" placeholder="Jelaskan masalah pelanggan..." required>{{ old('description') }}</textarea>
                </div>
                @error('description')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <label for="attachment">Lampiran</label>
                <div class="{{ $errors->has('attachment') ? 'field-error' : '' }}">
                    <input type="file" name="attachment" id="attachment" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                </div>
                @error('attachment')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <button type="submit" id="submit-btn">
                    <span class="spinner"></span>
                    Simpan Ticket
                </button>
            </form>
        </div>
    </div>

    <script>
        document.querySelector('form')?.addEventListener('submit', function () {
            const btn = document.getElementById('submit-btn');
            if (btn) {
                btn.classList.add('loading');
            }
        });

        // Ask the classifier for a priority suggestion while the agent types.
        (function () {
            const form = document.querySelector('form');
            const title = document.getElementById('title');
            const description = document.getElementById('description');
            const box = document.getElementById('classify-suggestion');
            const text = document.getElementById('classify-text');
            const apply = document.getElementById('classify-apply');
            let timer = null;
            let suggestion = null;

            const classify = function () {
                if (title.value.trim().length < 5) {
                    box.hidden = true;
                    return;
                }

                fetch(@json(route('tickets.classify')), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify({ title: title.value, description: description.value }),
                })
                    .then(function (res) { return res.ok ? res.json() : null; })
                    .then(function (data) {
                        if (!data) return;
                        suggestion = data;
                        text.textContent = 'Prioritas ' + data.priority + ' · Topik ' + data.category;
                        box.hidden = false;
                    })
                    .catch(function () {});
            };

            const schedule = function () {
                clearTimeout(timer);
                timer = setTimeout(classify, 600);
            };

            title?.addEventListener('input', schedule);
            description?.addEventListener('input', schedule);

            apply?.addEventListener('click', function () {
                if (!suggestion) return;
                document.getElementById('priority').value = suggestion.priority;
            });
        })();
    </script>
</body>
</html>
