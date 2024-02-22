document.addEventListener("DOMContentLoaded", async () => {
    const linkElement = document.getElementById('pwa-app');

    if (linkElement) {
        const entryPoint = linkElement.getAttribute('data-entryPoint') === '/' ? '?type=1707836619' : linkElement.getAttribute('href');
        const newURL = new URL(entryPoint, window.location.href);

        fetch(newURL)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });
    }
});
