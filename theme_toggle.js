
(function() {
    const toggle = () => {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('prefers-dark', document.body.classList.contains('dark-mode') ? '1' : '0');
    };
    document.addEventListener('DOMContentLoaded', () => {
        if (localStorage.getItem('prefers-dark') === '1') {
            document.body.classList.add('dark-mode');
        }
        const btn = document.createElement('button');
        btn.innerText = '🌓';
        btn.style.position = 'fixed';
        btn.style.top = '10px';
        btn.style.right = '10px';
        btn.style.zIndex = 9999;
        btn.addEventListener('click', toggle);
        document.body.appendChild(btn);
    });
})();
