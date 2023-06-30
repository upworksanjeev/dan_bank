importScripts('https://www.gstatic.com/firebasejs/8.3.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.0/firebase-messaging.js');

	firebase.initializeApp({
        apiKey: "AIzaSyCLh18va6wEzABhELD34_frdut8QxnpftU",
        authDomain: "precocial-98f2a.firebaseapp.com",
        projectId: "precocial-98f2a",
        storageBucket: "precocial-98f2a.appspot.com",
        messagingSenderId: "695962327067",
        appId: "1:695962327067:web:25629b0e49d6a79e2c40be"
    });

	const messaging = firebase.messaging();
    messaging.setBackgroundMessageHandler(function(payload) {
        const noteTitle = payload.data.title;
        const noteOptions = {
            body: payload.data.body,
            icon: payload.data.icon,
            data: payload.data
        };

        return self.registration.showNotification(noteTitle,
            noteOptions);
    });
    self.addEventListener('notificationclick', function(event) {
        // var newUrl =  'https://discoveritech.org/staff-dashboard/connect-to-app' + '?room_name=' + event.notification.data.room_name;
        // clients.openWindow(newUrl);
    });