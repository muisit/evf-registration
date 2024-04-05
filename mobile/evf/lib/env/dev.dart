import 'package:evf/models/flavor.dart';

class Development extends Flavor {
  @override
  String get apiUrl => "http://localhost:9154";
  @override
  Duration get schedule => const Duration(seconds: 10);
  @override
  Duration get status => const Duration(seconds: 60);
}
