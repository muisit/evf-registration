import 'dart:convert';
import 'dart:io';
import 'package:evf/environment.dart';
import 'package:path_provider/path_provider.dart';
import 'package:evf/util/random_string.dart';
import 'cache_data.dart';

typedef CacheMiss = Future<String> Function();

class FileCache {
  CacheData? _cache;

  Future initialize() async {
    Environment.debug("initializing cache");
    var content = await loadJson('cache.json');
    try {
      _cache = CacheData.fromJson(content);
    } on Exception {
      Environment.debug("caught error on reading cache, creating empty cache");
      _cache = CacheData.fromJson('{}');
    }
  }

  Future<String> getDirectory(bool persists) async {
    final directory = persists ? await getApplicationDocumentsDirectory() : await getApplicationCacheDirectory();
    if (persists && (Platform.isLinux || Platform.isWindows || Platform.isMacOS)) {
      //
      return "${directory.path}/evf";
    }
    return directory.path;
  }

  Future<String> loadJson(String path, {bool persists = false}) async {
    try {
      Environment.debug("loading json $path");
      final directory = await getDirectory(persists);
      Environment.debug("loading from $directory/$path");
      var file = File('$directory/$path');
      Environment.debug("reading as string");
      return await file.readAsString();
    } on Exception catch (e) {
      // caught an error, prevent the fail by providing an empty string
      Environment.debug("caught exception ${e.toString()}");
    }
    Environment.debug("returning empty json document as fail-over");
    return '{}';
  }

  Future storeJson(String destination, String content, {bool persists = false}) async {
    try {
      final directory = await getDirectory(persists);

      var file = File('$directory/$destination');
      file.writeAsString(content);
    } on Exception {
      // caught an error, don't store the new cache data
    }
  }

  Future<String> getCacheOrLoad(String path, CacheMiss? callback, {bool persists = false}) async {
    if (_cache != null && _cache!.containsKey(path)) {
      var localpath = _cache!.timestamps[path]!.path;
      return await loadJson(localpath, persists: persists);
    }
    Environment.debug("cache miss on getCacheOrLoad for $path");
    if (callback != null) {
      Environment.debug("awaiting cache callback");
      var content = await callback();
      Environment.debug("content is $content");
      Environment.debug("storing content in cache");
      final directory = await getDirectory(persists);
      var destination = '$directory/${getRandomString(24)}.json';
      Environment.debug("trying $destination");
      while (File(destination).existsSync()) {
        destination = '$directory/${getRandomString(24)}.json';
        Environment.debug("trying $destination");
      }
      Environment.debug("storing cached file at $destination");
      await storeJson(destination, content, persists: persists);
      Environment.debug("cached file stored");
      if (_cache != null) {
        _cache!.setCached(key: path, path: destination);
        Environment.debug("updating cache");
        await storeJson('cache.json', jsonEncode(_cache!.timestamps));
        Environment.debug("cache file updated");
      }
      return content;
    }
    return '';
  }

  Future clearCacheIfOlder(String path, DateTime ts) async {
    if (_cache != null && _cache!.clearIfOlder(path, ts)) {
      await storeJson('cache.json', jsonEncode(_cache!.timestamps));
    }
  }
}
