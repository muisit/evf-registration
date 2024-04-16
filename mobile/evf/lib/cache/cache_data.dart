import 'dart:convert';
import 'package:evf/environment.dart';

import 'cache_line.dart';

class CacheData {
  Map<String, CacheLine> timestamps = {};

  CacheData.fromJson(Map<String, dynamic> doc) {
    Environment.debug("parsing Json code");
    for (String key in doc.keys) {
      timestamps[key] = CacheLine.fromJson(doc[key] as Map<String, dynamic>);
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
