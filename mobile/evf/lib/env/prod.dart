import 'package:evf/models/flavor.dart';

class Production extends Flavor {
  @override
  String get apiUrl => "https://api.veteransfencing.eu";
  @override
  Duration get schedule => const Duration(seconds: 60);
  @override
  Duration get status => const Duration(seconds: 590);
}
