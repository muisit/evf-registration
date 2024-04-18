class CompetitionResult {
  final int entries;
  final int position;
  final double points;
  final double de;
  final double podium;
  final double total;
  final String status;

  CompetitionResult.fromJson(Map<String, dynamic> doc)
      : entries = doc['entries'] as int,
        position = doc['position'] as int,
        points = (doc['points'] as num).toDouble(),
        de = (doc['de'] as num).toDouble(),
        podium = (doc['podium'] as num).toDouble(),
        total = (doc['total'] as num).toDouble(),
        status = doc['status'] as String;

  Map<String, dynamic> toJson() => {
        "entries": entries,
        "position": position,
        "points": points,
        "de": de,
        "podium": podium,
        "total": total,
        "status": status
      };
}
