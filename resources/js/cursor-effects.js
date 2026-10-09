const cursorDot = document.createElement('div');
cursorDot.className = 'cursor-dot';
document.body.appendChild(cursorDot);

document.addEventListener('mousemove', (e) => {
    cursorDot.style.left = e.clientX - 10 + 'px';
    cursorDot.style.top = e.clientY - 10 + 'px';
});

document.addEventListener('mouseleave', () => {
    cursorDot.style.opacity = '0';
});

document.addEventListener('mouseenter', () => {
    cursorDot.style.opacity = '1';
});

const hoverButtons = document.querySelectorAll('.new-ticket-btn, .action-btn, .mini-btn, .send-btn, button[type="submit"]');

hoverButtons.forEach(btn => {
    btn.addEventListener('mouseenter', () => {
        cursorDot.style.transform = 'scale(1.8)';
        cursorDot.style.borderColor = 'rgba(139, 92, 246, 0.8)';
    });
    btn.addEventListener('mouseleave', () => {
        cursorDot.style.transform = 'scale(1)';
        cursorDot.style.borderColor = 'rgba(139, 92, 246, 0.5)';
    });
});

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
        }
    });
}, { threshold: 0.5 });

document.querySelectorAll('.new-ticket-btn').forEach(btn => {
    observer.observe(btn);
});
