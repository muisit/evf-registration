import 'competition_result.dart';

class Competition {
  DateTime opens;
  String category;
  String weapon;
  List<CompetitionResult> results;

  Competition()
      : opens = DateTime.now(),
        category = '1',
        weapon = 'MF',
        results = [];

  Competition.fromJson(Map<String, dynamic> doc)
      : opens = DateTime.parse(doc['opens'] as String),
        category = doc['category'] as String,
        weapon = doc['weapon'] as String,
        results = [] {
    for (var el in doc['results']) {
      results.add(CompetitionResult.fromJson(el as Map<String, dynamic>));
    }
  }

  Map<String, dynamic> toJson() => {
        'opens': opens.toIso8601String(),
        'category': category,
        'weapon': weapon,
        'results': results,
      };
}
