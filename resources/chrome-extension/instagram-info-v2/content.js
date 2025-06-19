const userCache = {};
let lastFetchedUser;
let pendingUsernameFromLink;

async function fetchUserInfo(username) {
    if (username === lastFetchedUser) return;
    lastFetchedUser = username;

    if (userCache[username]) {
        console.log(`Используем кэш для ${username}`);
        displayInfo(userCache[username], username);
        return;
    }

    // Получаем токен только из chrome.storage
    const settings = await chrome.storage.local.get(['apiToken']);
    const apiToken = settings.apiToken;
    const apiUrl = `https://graceplace.by/api/v2/user/${username}`;
    if (!apiToken) {
        displayInfo({ error: 'API Token не настроен. Пожалуйста, настройте токен в расширении.' }, username);
        return;
    }
    try {
        const response = await fetch(apiUrl, {
            headers: {
                'X-API-Token': apiToken,
                'Accept': 'application/json'
            }
        });
        if (!response.ok) throw new Error(`Ошибка сервера: ${response.status}`);
        const data = await response.json();
        userCache[username] = data;
        displayInfo(data, username);
    } catch (error) {
        console.error('Ошибка при получении данных:', error);
        displayInfo({ error: error.message }, username);
    }
}

function displayInfo(data, username) {
    const oldInfoBox = document.querySelector('.instagram-info-box');
    if (oldInfoBox) oldInfoBox.remove();

    const infoBox = document.createElement('div');
    infoBox.style.color = '#000';
    infoBox.className = 'instagram-info-box';

    // Вставляем кнопку прямо в HTML-шаблон
    const closeBtnHTML = `<button class="close-btn-zoltan" style="position:absolute; top:5px; right:10px; background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>`;

    if (data.status) {
        infoBox.innerHTML = `
            ${closeBtnHTML}
            <strong>Информация о пользователе:</strong><br>
            Имя: <a href="${data.link}" target="_blank">${data.name}</a><br>
            Телефон: ${data.phone}<br>
            Профиль: ${username}
        `;
    } else if (data.error) {
        infoBox.innerHTML = `${closeBtnHTML}<strong style="color:#a94442;">${data.error}</strong>`;
    } else {
        infoBox.innerHTML = `${closeBtnHTML}<strong>Нет информации</strong>`;
    }
    document.body.appendChild(infoBox);

    // Добавляем обработчик после вставки в DOM
    const closeBtn = infoBox.querySelector('.close-btn-zoltan');
    if (closeBtn) {
        closeBtn.onclick = () => infoBox.remove();
    }
}

function getUsernameFromDOM() {
    const usernameElement = document.querySelector('a[href^="/"][role="link"]');
    if (usernameElement) {
        return usernameElement.getAttribute('href').replace(/\//g, '');
    }
    return null;
}

function handleDOMChange() {
    const username = getUsernameFromDOM();
    if (username) {
        fetchUserInfo(username);
    } else if (pendingUsernameFromLink) {
        fetchUserInfo(pendingUsernameFromLink);
        pendingUsernameFromLink = null;
    }
}

const observer = new MutationObserver(() => {
    handleDOMChange();
});
observer.observe(document.body, { subtree: true, childList: true });

window.addEventListener('load', () => {
    const match = window.location.href.match(/ig\.me\/m\/([\w\.]+)/);
    if (match) {
        pendingUsernameFromLink = match[1];
        handleDOMChange();
    } else {
        handleDOMChange();
    }
});
