import 'package:evf/models/flavor.dart';
import 'package:firebase_core/firebase_core.dart';
import 'dev_firebase_options.dart';

class Development extends Flavor {
  @override
  String get apiUrl => "https://ssi.muisit.nl";
  @override
  Duration get schedule => const Duration(seconds: 10);
  @override
  Duration get status => const Duration(seconds: 60);

  @override
  FirebaseOptions get fcm => DefaultFirebaseOptions.currentPlatform;
}
