import 'dart:convert';
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

  void setCached({required String key, required String path}) {
    timestamps[key] = CacheLine(path: path);
  }

  bool clearIfOlder(String key, DateTime dt) {
    if (timestamps.containsKey(key)) {
      if (timestamps[key]!.date.isBefore(dt)) {
        timestamps.remove(key);
        return true;
      }
    }
    return false;
  }

  bool containsKey(String key) => timestamps.containsKey(key);
}
