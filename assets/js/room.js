import '../styles/room.css';

document.addEventListener('DOMContentLoaded', () => {
    localStorage.setItem('username', USERNAME);
    localStorage.setItem('user_id', USER_ID);

    const usersListElement = document.querySelector('#room_users');


    const inviteLink = document.querySelector('#invite_link').value;
    document.querySelector('#copy_invite_link').addEventListener('click', async () => {
        await navigator.clipboard.writeText(inviteLink);
    });


    document.querySelector('#room_leave').addEventListener('click', event => {
        event.preventDefault();

        removeUser(USER_ID);
    });


    const publicEventSource = new EventSource(LOGIN_EVENT_SOURCE_URL);
    publicEventSource.onmessage = event => {
        const dataStruct = JSON.parse(event.data);

        switch (dataStruct.type) {
            case 'room.close':
                exitRoom(dataStruct.data);
                break;
            case 'room.users':
                refreshUsersList(dataStruct.data, usersListElement);
                break;
        }
    };

    const privateEventSource = new EventSource(EXIT_EVENT_SOURCE_URL, {
        withCredentials: true,
    });
    privateEventSource.onmessage = event => {
        exitRoom(event.data);
    };


    refreshUsersList(USERS, usersListElement);
});

function refreshUsersList(users, usersListElement)
{
    usersListElement.innerHTML = '';

    users.forEach(user => {
        const usernameElement = document.createElement('div');
        usernameElement.textContent = user.username;

        const liElement = document.createElement('li');
        liElement.id = 'user-' + user.id;
        liElement.className = 'w-full border-b border-gray-200 px-4 py-3 dark:border-gray-600 relative';
        liElement.appendChild(usernameElement);

        if (USER_ID === ROOM_OWNER_ID && user.id !== USER_ID) {
            const svgTrashElement = generateSvgTrashIcon();
            svgTrashElement.classList.add('absolute', 'bottom-3.5', 'right-2.5');

            const removeLinkElement = document.createElement('a');
            removeLinkElement.href = '#';
            removeLinkElement.className = 'user-remove';
            removeLinkElement.dataset.id = user.id;
            removeLinkElement.appendChild(svgTrashElement);
            removeLinkElement.addEventListener('click', removeUserOnClick);

            liElement.appendChild(removeLinkElement);
        }

        usersListElement.appendChild(liElement);
    });
}

function generateSvgTrashIcon()
{
    const faCopyrightElement = document.createComment('! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc.');

    const svgPathElement = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    svgPathElement.setAttributeNS(null, 'd', 'M170.5 51.6L151.5 80h145l-19-28.4c-1.5-2.2-4-3.6-6.7-3.6H177.1c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80H368h48 8c13.3 0 24 10.7 24 24s-10.7 24-24 24h-8V432c0 44.2-35.8 80-80 80H112c-44.2 0-80-35.8-80-80V128H24c-13.3 0-24-10.7-24-24S10.7 80 24 80h8H80 93.8l36.7-55.1C140.9 9.4 158.4 0 177.1 0h93.7c18.7 0 36.2 9.4 46.6 24.9zM80 128V432c0 17.7 14.3 32 32 32H336c17.7 0 32-14.3 32-32V128H80zm80 64V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16z');

    const svgElement = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svgElement.setAttributeNS(null, 'viewBox', '0 0 448 512');
    svgElement.setAttributeNS(null, 'height', '1em');
    svgElement.append(faCopyrightElement, svgPathElement);

    return svgElement;
}

function removeUserOnClick(event)
{
    event.preventDefault();

    const userLinkElement = event.target.closest('a.user-remove');
    const userId = userLinkElement.dataset.id;

    removeUser(userId);
}

function removeUser(userId)
{
    fetch('/room/' + ROOM_ID + '/users/' + userId, {
        method: 'DELETE',
    })
        .catch(error => {
            console.error('Error while removing the user: ', error);
        });
}

function exitRoom(message)
{
    localStorage.setItem('exit_message', message);
    window.location.href = '/';
}
