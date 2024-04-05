import 'package:evf/util/timestamp.dart';

class CacheLine {
  String timestamp = '';
  String path = '';
  DateTime date = DateTime.now();

  CacheLine({required this.path}) {
    date = DateTime.now();
    timestamp = makeTimestamp(date);
  }

  CacheLine.fromJson(Map<String, dynamic> values) {
    timestamp = values['ts'].toString();
    path = values['path'].toString();
    // unmake the timestamp so we get milliseconds
    date = unmakeTimestamp(timestamp);
  }

  Map<String, dynamic> toJson() => {'ts': timestamp, 'path': path};
}
