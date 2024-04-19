import 'dart:convert';

import 'package:evf/api/add_following.dart' as api;
import 'package:evf/api/remove_following.dart' as api2;
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

  // load definitive data from the back-end
  // This is only required if we want to interface with the settings for each
  // follower/following
  Future load() async {
    if (!_loadedFromCache) {
      Environment.debug("loading items from cache first");
      await loadItemsFromCache();
    }

    if (!isLoading) {
      await _loadFollowers();
    }
  }

  Future<bool> toggleFollowing(String uuid) async {
    Environment.debug("toggling following for $uuid");
    if (following.containsKey(uuid)) {
      return await removeFollowing(uuid);
    } else {
      return await addFollowing(uuid);
    }
  }

  Future<bool> addFollowing(String uuid) async {
    final network = await api.addFollowing(uuid);
    if (network) {
      final follower = Follower(uuid);
      follower.synced = true;
      following[uuid] = follower;
      await _storeItemsInCache();
      notifyListeners();
    }
    return network;
  }

  Future<bool> removeFollowing(String uuid) async {
    if (following.containsKey(uuid) && following[uuid]!.synced) {
      final network = await api2.removeFollowing(uuid);
      if (network) {
        following.remove(uuid);
        await _storeItemsInCache();
        notifyListeners();
      }
      return network;
    }
    return true;
  }

  void syncItems(List<String> followers, List<String> following) async {
    // this is called when the status was updated. We need to synchronize our database of
    // followers and following with the list of uuids in the status
    for (var uuid in followers) {
      if (!this.followers.containsKey(uuid)) {
        final follower = Follower(uuid);
        follower.synced = true;
        this.followers[uuid] = follower;
      }
    }
    for (var key in this.followers.keys) {
      if (!followers.contains(key)) {
        this.followers.remove(key);
      }
    }

    for (var uuid in following) {
      if (!this.following.containsKey(uuid)) {
        final follower = Follower(uuid);
        follower.synced = true;
        this.following[uuid] = follower;
      }
    }
    for (var key in this.following.keys) {
      if (!following.contains(key)) {
        this.following.remove(key);
      }
    }
    await _storeItemsInCache();
    notifyListeners();
  }

  Future _loadFollowers() async {
    isLoading = true;
    var listing = await loadFollowers();
    for (var follower in listing) {
      follower.synced = true;
      followers[follower.fencer] = follower;
    }

    // remove all items that are not in our loaded list
    var keys = listing.map<String>((f) => f.fencer).toList();
    for (var key in followers.keys) {
      if (!keys.contains(key)) {
        followers.remove(key);
      }
    }

    listing = await loadFollowing();
    for (var follower in listing) {
      follower.synced = true;
      following[follower.fencer] = follower;
    }

    // remove all items that are not in our loaded list
    keys = listing.map<String>((f) => f.fencer).toList();
    for (var key in following.keys) {
      if (!keys.contains(key)) {
        following.remove(key);
      }
    }

    await _storeItemsInCache();
    isLoading = false;
    notifyListeners();
  }

  Future loadItemsFromCache() async {
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
      _loadedFromCache = true;
      notifyListeners();
    } catch (e) {
      // just skip loading the items from cache, the cache is probably empty
    }
  }

  Future _storeItemsInCache() async {
    await Environment.instance.cache.setCache("followers.json", const Duration(days: 7), jsonEncode(followers));
    await Environment.instance.cache.setCache("followers.json", const Duration(days: 7), jsonEncode(following));
  }
}
