import 'result.dart';

class RankDetails {
  final String fencer;
  final String weapon;
  final String category;
  final DateTime date;
  final int position;
  final double points;
  final List<Result> results;

  RankDetails()
      : fencer = '',
        weapon = '',
        category = '',
        date = DateTime.now(),
        position = 0,
        points = 0.0,
        results = [];

  RankDetails.fromJson(Map<String, dynamic> doc)
      : fencer = doc['fencer'] as String,
        weapon = doc['weapon'] as String,
        category = doc['category'] as String,
        date = DateTime.parse(doc['date'] as String),
        position = doc['position'] as int,
        points = doc['points'] as double,
        results = doc['results'] as List<Result>;

  Map<String, dynamic> toJson() => {
        "fencer": fencer,
        "weapon": weapon,
        "category": category,
        "date": date.toIso8601String(),
        "position": position,
        "points": points,
        "results": results
      };
}
