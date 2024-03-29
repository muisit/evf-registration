import 'dart:convert';
import 'package:sprintf/sprintf.dart';
import 'cache_line.dart';

class CacheData {
  Map<String, CacheLine> timestamps = {};

  CacheData.fromJson(String json) {
    final parsed = jsonDecode(json);
    for (String key in parsed.keys) {
      timestamps[key] = CacheLine.fromJson(parsed[key] as Map<String, dynamic>);
    }
  }

  String isCached(String key) {
    if (timestamps.containsKey(key)) {
      return timestamps[key]!.path;
    }
    return '';
  }

  String makeTs(DateTime dt) {
    return sprintf('%04d%02d%02d%02d%02d%02d%03d', [
      dt.year,
      dt.month,
      dt.day,
      dt.hour,
      dt.minute,
      dt.second,
      dt.millisecond
    ]);
  }

  void setCached({required String key, required String path, DateTime? dt}) {
    final stamp = makeTs(dt ?? DateTime.now());
    timestamps[key] = CacheLine(timestamp: stamp, path: path);
  }

  bool clearIfOlder(String key, DateTime dt) {
    final stamp = makeTs(dt);
    if (timestamps.containsKey(key)) {
      if (timestamps[key]!.timestamp.compareTo(stamp) < 0) {
        timestamps.remove(key);
        return true;
      }
    }
    return false;
  }

  bool containsKey(String key) => timestamps.containsKey(key);
}
