@if(session('success'))
    <div id="notification" style="position: fixed; top: 20px; right: 20px; background: #166534; color: #fff; padding: 14px 20px; border-radius: 10px; font-weight: 700; display: flex; align-items: center; gap: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); z-index: 10000; animation: slideIn 0.3s ease;">
        <span>✅</span>
        <span>{{ session('success') }}</span>
    </div>
    <script>
        setTimeout(() => {
            const el = document.getElementById('notification');
            if (el) {
                el.style.opacity = '0';
                el.style.transform = 'translateY(-10px)';
                el.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                setTimeout(() => el.remove(), 300);
            }
        }, 3000);
    </script>
    <style>
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>
@endif
