import 'dart:io';

import 'package:evf/firebase_options.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

import 'environment.dart';
import 'package:intl/date_symbol_data_local.dart';

Future initialization() async {
  try {
    Environment.debug("calling initialize on environment");
    await Environment.instance.initialize();

    // load locale files
    await initializeDateFormatting();

    // Preload what we have regarding followers from cache
    // It will be updated as soon as we get our initial status message
    await Environment.instance.followerProvider.loadItemsFromCache();

    await Firebase.initializeApp(options: DefaultFirebaseOptions.currentPlatform);
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      Environment.instance.notificationProvider.handleMessage(message);
    });

    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

    // do not await this, just let it load
    Environment.instance.statusProvider.loadStatus();
    Environment.debug("end of initialization");
  } on Exception catch (e) {
    Environment.debug("caught exception during initialization: ${e.toString()}");
    // pass, do not allow exceptions to block the application
  }
}

Future scheduledTasks() async {
  Environment.debug("running scheduler");
  var data = Environment.instance.statusProvider.wasLoaded;
  if (data.isBefore(DateTime.now().subtract(Environment.instance.flavor.status))) {
    await Environment.instance.statusProvider.loadStatus();
  }

  // call the schedule every minute
  await Future.delayed(Environment.instance.flavor.schedule, scheduledTasks);
}

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  Environment.instance.notificationProvider.handleMessage(message);
}
