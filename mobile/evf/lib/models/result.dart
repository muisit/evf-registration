class Result {
  final String event;
  final int year;
  final String location;
  final String country;
  final String category;
  final String weapon;
  final int entries;
  final int position;
  final double points;
  final double de;
  final double podium;
  final double total;
  final String status;

  Result.fromJson(Map<String, dynamic> doc)
      : event = doc['event'] as String,
        year = doc['year'] as int,
        location = doc['location'] as String,
        country = doc['country'] as String,
        category = doc['category'] as String,
        weapon = doc['weapon'] as String,
        entries = doc['entries'] as int,
        position = doc['position'] as int,
        points = doc['points'] as double,
        de = doc['de'] as double,
        podium = doc['podium'] as double,
        total = doc['total'] as double,
        status = doc['status'] as String;

  Map<String, dynamic> toJson() => {
        "event": event,
        "year": year,
        "location": location,
        "country": country,
        "category": category,
        "weapon": weapon,
        "entries": entries,
        "position": position,
        "points": points,
        "de": de,
        "podium": podium,
        "total": total,
        "status": status
      };
}
