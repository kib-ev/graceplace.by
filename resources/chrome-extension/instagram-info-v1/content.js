let lastFetchedUser = null;
let pendingUsernameFromLink = null;
const userCache = {}; // 🧠 Здесь будет храниться кэш

async function fetchUserInfo(username) {
    if (username === lastFetchedUser) return;

    lastFetchedUser = username;

    // 🔍 Проверка кэша
    if (userCache[username]) {
        console.log(`Используем кэш для ${username}`);
        displayInfo(userCache[username], username);
        return;
    }

    const apiUrl = `https://graceplace.by/api/user/${username}`;
    try {
        const response = await fetch(apiUrl);
        if (!response.ok) throw new Error(`Ошибка сервера: ${response.status}`);
        const data = await response.json();

        // 💾 Сохраняем в кэш
        userCache[username] = data;

        displayInfo(data, username);
    } catch (error) {
        console.error('Ошибка при получении данных:', error);
    }
}

function displayInfo(data, username) {
    const oldInfoBox = document.querySelector('.instagram-info-box');
    if (oldInfoBox) oldInfoBox.remove();

    const infoBox = document.createElement('div');
    infoBox.style.color = '#000';
    infoBox.className = 'instagram-info-box';

    if (data.status) {
        infoBox.innerHTML = `
            <strong>Информация о пользователе:</strong><br>
            Имя: <a href="${data.link}" target="_blank">${data.name}</a><br>
            Телефон: ${data.phone}<br>
            Профиль: ${username}
        `;
    } else {
        infoBox.innerHTML = `<strong>Нет информации</strong>`;
    }

    document.body.appendChild(infoBox);
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
