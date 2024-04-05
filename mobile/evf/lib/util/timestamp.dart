import 'package:sprintf/sprintf.dart';

String makeTimestamp(DateTime dt) {
  return sprintf(
      '%04d%02d%02d%02d%02d%02d%03d', [dt.year, dt.month, dt.day, dt.hour, dt.minute, dt.second, dt.millisecond]);
}

DateTime unmakeTimestamp(String ts) {
  if (ts.length != 17 || ts[0] != '2') {
    return DateTime.now();
  }
  var year = ts.substring(0, 4);
  var month = ts.substring(4, 2);
  var day = ts.substring(6, 2);
  var hour = ts.substring(8, 2);
  var mins = ts.substring(10, 2);
  var secs = ts.substring(12, 2);
  var mills = ts.substring(14, 3);
  return DateTime(int.parse(year), int.parse(month), int.parse(day), int.parse(hour), int.parse(mins), int.parse(secs),
      int.parse(mills));
}
