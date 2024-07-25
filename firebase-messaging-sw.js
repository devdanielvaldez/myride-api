importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js");

firebase.initializeApp({
  apiKey: "AIzaSyBaGdE9AAEaXoAJbed4X8-btY7XmCDEGOY",
  authDomain: "myride-capcana.firebaseapp.com",
  databaseURL: "https://myride-capcana-default-rtdb.firebaseio.com",
  projectId: "myride-capcana",
  storageBucket: "myride-capcana.appspot.com",
  messagingSenderId: "552752569147",
  appId: "1:552752569147:web:0ea67235d0aa9e971bf51e",
  measurementId: "G-T3CK7LG81J"
});

const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function (payload) {
    const promiseChain = clients
        .matchAll({
            type: "window",
            includeUncontrolled: true
        })
        .then(windowClients => {
            for (let i = 0; i < windowClients.length; i++) {
                const windowClient = windowClients[i];
                windowClient.postMessage(payload);
            }
        })
        .then(() => {
            const title = payload.notification.title;
            const options = {
                body: payload.notification.score
              };
            return registration.showNotification(title, options);
        });
    return promiseChain;
});
self.addEventListener('notificationclick', function (event) {
    console.log('notification received: ', event)
});