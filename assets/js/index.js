document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('#username').value = localStorage.getItem('username');
    document.querySelector('#user_id').value = localStorage.getItem('user_id');

    const exitMessage = localStorage.getItem('exit_message');
    if (exitMessage !== null) {
        const exitMessageElement = document.querySelector('#exit_message');

        exitMessageElement.innerHTML = exitMessage;
        exitMessageElement.classList.remove('hidden');

        localStorage.removeItem('exit_message');
    }
});
