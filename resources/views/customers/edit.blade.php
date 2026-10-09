<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
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
            min-height: 100px;
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
            margin-top: 12px;
            color: #fca5a5;
            font-size: 14px;
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
    </style>
</head>
<body>
    <div class="wrap">
        <h2>Edit Customer</h2>
        <a href="{{ route('customers.index') }}" class="top-link">
            <svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 4l-6 6 6 6M4 10h12" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Kembali ke Daftar Customer
        </a>

        <div class="card">
            @if(session('success'))
                <div style="background:#166534; color:#fff; padding:12px 16px; border-radius:10px; margin-bottom:18px; display:flex; align-items:center; gap:10px; font-weight:700;">✅ {{ session('success') }}</div>
            @endif

            <x-notification />

            <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                @csrf
                @method('PUT')

                <label for="name">Nama</label>
                <input type="text" name="name" id="name" value="{{ old('name', $customer->name) }}" required>

                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $customer->email) }}">

                <label for="phone">No HP</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}" required>

                <label for="address">Alamat</label>
                <textarea name="address" id="address" required>{{ old('address', $customer->address) }}</textarea>

                <label for="package">Paket Internet</label>
                <input type="text" name="package" id="package" value="{{ old('package', $customer->package) }}">

                @if ($errors->any())
                    <div class="error-message">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <button type="submit" id="submit-btn">
                    <span class="spinner"></span>
                    Update Customer
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
    </script>
</body>
</html>
