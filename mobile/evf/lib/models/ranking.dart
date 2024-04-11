import 'ranking_position.dart';

class Ranking {
  DateTime date;
  DateTime updated;
  String category;
  String weapon;
  List<RankingPosition> positions;

  Ranking(this.date, this.updated, this.category, this.weapon, this.positions);

  Ranking.fromJson(Map<String, dynamic> doc)
      : date = DateTime.parse(doc['date'] as String),
        updated = DateTime.parse(doc['updated'] as String),
        category = doc['category'] as String,
        weapon = doc['weapon'] as String,
        positions = doc['positions'] as List<RankingPosition>;

  Map<String, dynamic> toJson() => {
        'date': date.toIso8601String(),
        'updated': updated.toIso8601String(),
        'category': category,
        'weapon': weapon,
        'positions': positions,
      };

  String catWeapon() => "$category/$weapon";
}
