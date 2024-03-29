class Status {
  String deviceId;
  String lastFeed;
  int feedCount;
  String lastCalendar;
  int calendarCount;
  String lastResult;
  int resultCount;
  String lastRanking;

  Status()
      : deviceId = '',
        lastFeed = '',
        feedCount = 0,
        lastCalendar = '',
        calendarCount = 0,
        lastResult = '',
        resultCount = 0,
        lastRanking = '';

  Status.fromJson(Map<String, dynamic> values)
      : deviceId = values['id'] as String,
        lastFeed = values['feed']['last'] as String,
        feedCount = values['feed']['count'] as int,
        lastCalendar = values['calendar']['last'] as String,
        calendarCount = values['calendar']['count'] as int,
        lastResult = values['results']['last'] as String,
        resultCount = values['results']['count'] as int,
        lastRanking = values['ranking']['last'] as String;
}
