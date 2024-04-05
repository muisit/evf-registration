// The basic Calendar model to display items in the calendar

class Calendar {
  String id;
  String url;
  String feed;
  String title;
  String content;
  String location;
  String country;
  DateTime startDate;
  DateTime endDate;
  DateTime mutated;

  Calendar(this.id, this.title, this.content, this.location, this.country, this.url, this.feed, this.startDate,
      this.endDate, this.mutated);

  Calendar.fromJson(Map<String, dynamic> doc)
      : id = doc['id'] as String,
        title = doc['title'] as String,
        content = doc['content'] as String,
        location = doc['location'] as String,
        country = doc['country'] as String,
        url = doc['url'] as String,
        feed = doc['feed'] as String,
        startDate = DateTime.parse(doc['start'] as String),
        endDate = DateTime.parse(doc['end'] as String),
        mutated = DateTime.parse(doc['mutated'] as String);

  Map<String, dynamic> toJson() => {
        'id': id,
        'title': title,
        'content': content,
        'location': location,
        'country': country,
        'url': url,
        'start': startDate.toIso8601String(),
        'end': endDate.toIso8601String(),
        'mutated': mutated.toIso8601String()
      };
}
