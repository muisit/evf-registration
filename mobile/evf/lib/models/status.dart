class Status {
  String deviceId;
  String lastFeed;
  int feedCount;
  String lastCalendar;
  int calendarCount;
  String lastResult;
  int resultCount;
  String lastRanking;
  List<String> followers;

  Status()
      : deviceId = '',
        lastFeed = '',
        feedCount = 0,
        lastCalendar = '',
        calendarCount = 0,
        lastResult = '',
        resultCount = 0,
        lastRanking = '',
        followers = [];

  Status.fromJson(Map<String, dynamic> values)
      : deviceId = values['id'] as String,
        lastFeed = values['feed']['last'] as String,
        feedCount = values['feed']['count'] as int,
        lastCalendar = values['calendar']['last'] as String,
        calendarCount = values['calendar']['count'] as int,
        lastResult = values['results']['last'] as String,
        resultCount = values['results']['count'] as int,
        lastRanking = values['ranking']['last'] as String,
        followers = [] {
    var lst = values['followers'] as List<dynamic>;
    followers = lst.map<String>((e) => e.toString()).toList();
  }

  Map<String, dynamic> toJson() => {
        'id': deviceId,
        'feed': {'last': lastFeed, 'count': feedCount},
        'calendar': {'last': lastCalendar, 'count': calendarCount},
        'results': {'last': lastResult, 'count': resultCount},
        'ranking': {'last': lastRanking, 'count': 0},
        'followers': followers,
      };
}
