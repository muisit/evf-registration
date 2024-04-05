import 'ranking_position.dart';

class Ranking {
  DateTime date;
  String category;
  String weapon;
  List<RankingPosition> positions;

  Ranking(this.date, this.category, this.weapon, List<RankingPosition> this.positions);

  Ranking.fromJson(Map<String, dynamic> doc)
      : date = DateTime.parse(doc['date'] as String),
        category = doc['category'] as String,
        weapon = doc['weapon'] as String,
        positions = doc['positions'] as List<RankingPosition>;

  Map<String, dynamic> toJson() => {
        'category': category,
        'weapon': weapon,
        'positions': positions,
      };
}
