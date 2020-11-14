 importScripts('https://www.gstatic.com/firebasejs/4.8.1/firebase-app.js');
 importScripts('https://www.gstatic.com/firebasejs/4.8.1/firebase-messaging.js');

 var config = {
        apiKey: "AIzaSyDJDVaYMMxRGQbB8JSs8WGtL55G8MD-WSY",
        authDomain: "m3alig-22b33.firebaseapp.com",
        databaseURL: "https://m3alig-22b33.firebaseio.com",
        projectId: "m3alig-22b33",
        storageBucket: "m3alig-22b33.appspot.com",
        messagingSenderId: "1062869959006"
    };
    firebase.initializeApp(config);

  const messaging = firebase.messaging();


 messaging.setBackgroundMessageHandler(function(payload) {
     //console.log('[firebase-messaging-sw.js] Received background message ', payload);
     // Customize notification here

     console.log("title : "+payload.data.title);

     const notificationTitle = 'تنبيهات معالج ';
     const notificationOptions = {
         body: payload.data.title,
         icon: '/firebase-logo.png'
     };

         //$("#noti").append('<li> '+payload.data.title+ ' </li>');

     return self.registration.showNotification(notificationTitle,
         notificationOptions);
 });



