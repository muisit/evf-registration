import 'package:evf/models/flavor.dart';
import 'package:firebase_core/firebase_core.dart';
import 'prod_firebase_options.dart';

class Production extends Flavor {
  @override
  String get apiUrl => "https://api.veteransfencing.eu";
  @override
  Duration get schedule => const Duration(seconds: 60);
  @override
  Duration get status => const Duration(seconds: 590);
  @override
  FirebaseOptions get fcm => DefaultFirebaseOptions.currentPlatform;
}
