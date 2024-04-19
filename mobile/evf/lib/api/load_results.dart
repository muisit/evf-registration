// Load the results of a specific competition

import 'package:evf/models/competition.dart';
import 'interface.dart';

Future<Competition> loadResults(int competitionId) async {
  final api = Interface.create(path: "/device/results/$competitionId");
  var content = await api.get();
  return Competition.fromJson(content);
}

Future<String> loadResultsRaw(int competitionId) async {
  final api = Interface.create(path: "/device/results/$competitionId");
  return await api.getRaw();
}
