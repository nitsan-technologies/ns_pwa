window.addEventListener('load', async () => {
    if ('serviceWorker' in navigator) {
      try {
        const registration = await navigator.serviceWorker.register('/service-worker.js');
        console.log('ServiceWorker registration successful with scope: ', registration.scope);
        const notificationPermission = await Notification.requestPermission();
        if (notificationPermission !== 'granted') {
          console.log('Notification permission not granted');
        } else {
          console.log('Add Notification Code');
        }
      } catch (err) {
        console.error(err);
      }
    }
  });
  