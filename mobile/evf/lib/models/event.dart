import 'competition.dart';

class Event {
  String name;
  int year;
  DateTime opens;
  DateTime closes;
  String location;
  String country;
  String website;
  List<Competition> competitions;

  Event()
      : name = '',
        year = 2000,
        opens = DateTime(2000, 1, 1),
        closes = DateTime(2000, 1, 1),
        location = '',
        country = '',
        website = '',
        competitions = [];

  Event.fromJson(Map<String, dynamic> doc)
      : name = doc['name'] as String,
        year = doc['year'] as int,
        opens = DateTime.parse(doc['opens'] as String),
        closes = DateTime.parse(doc['closes'] as String),
        location = doc['location'] as String,
        country = doc['country'] as String,
        website = doc['website'] as String,
        competitions = [] {
    for (var c in doc['competitions']) {
      competitions.add(Competition.fromJson(c));
    }
  }

  Map<String, dynamic> toJson() => {
        'name': name,
        'year': year,
        'opens': opens.toIso8601String(),
        'closes': closes.toIso8601String(),
        'location': location,
        'country': country,
        'website': website,
        'competitions': competitions,
      };
}
