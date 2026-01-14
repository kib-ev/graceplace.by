document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('apiToken');
    const btn   = document.getElementById('saveBtn');
    const status= document.getElementById('status');
    const saved = document.getElementById('savedToken');

    // Обертка для проверки chrome.runtime.lastError
    function checkError() {
        if (chrome.runtime.lastError) {
            status.textContent = 'Ошибка: ' + chrome.runtime.lastError.message;
            status.style.color = 'red';
            console.error(chrome.runtime.lastError.message);
            return true;
        }
        return false;
    }

    chrome.storage.local.get(['apiToken'], (data) => {
        if (checkError()) return;
        if (data.apiToken) {
            input.value = data.apiToken;
        }
    });

    btn.onclick = () => {
        const token = input.value.trim();
        chrome.storage.local.set({ apiToken: token });
    };
});
