import 'dart:convert';

import 'package:evf/api/load_followers.dart';
import 'package:evf/environment.dart';
import 'package:evf/models/follower.dart';
import 'package:flutter/material.dart';

class FollowerProvider extends ChangeNotifier {
  Map<String, Follower> followers = {};
  Map<String, Follower> following = {};

  bool isLoading = false;
  DateTime wasLoaded = DateTime.now();
  bool _loadedFromCache = false;

  Future load() async {
    if (!_loadedFromCache) {
      Environment.debug("loading items from cache first");
      await _loadItemsFromCache();
      _loadedFromCache = true;
    }

    if (!isLoading) {
      await _loadFollowers();
    }
  }

  void syncItems(List<String> followers, List<String> following) {
    // this is called when the status was updated. We need to synchronize our database of
    // followers and following with the list of uuids in the status
    for (var uuid in followers) {
      if (!this.followers.containsKey(uuid)) {
        this.followers[uuid] = Follower(uuid);
      }
    }

    for (var uuid in following) {
      if (!this.following.containsKey(uuid)) {
        this.following[uuid] = Follower(uuid);
      }
    }
  }

  Future _loadFollowers() async {
    isLoading = true;
    var listing = await loadFollowers();
    for (var follower in listing) {
      followers[follower.fencer] = follower;
    }

    listing = await loadFollowing();
    for (var follower in listing) {
      following[follower.fencer] = follower;
    }
    isLoading = false;
    notifyListeners();
  }

  Future _loadItemsFromCache() async {
    try {
      final doc1 = jsonDecode(await Environment.instance.cache.getCache("followers.json")) as List<dynamic>;
      for (var content in doc1) {
        var follower = Follower.fromJson(content as Map<String, dynamic>);
        followers[follower.fencer] = follower;
      }

      final doc2 = jsonDecode(await Environment.instance.cache.getCache("following.json")) as List<dynamic>;
      for (var content in doc2) {
        var follower = Follower.fromJson(content as Map<String, dynamic>);
        following[follower.fencer] = follower;
      }
      notifyListeners();
    } catch (e) {
      // just skip loading the items from cache, the cache is probably empty
    }
  }
}
