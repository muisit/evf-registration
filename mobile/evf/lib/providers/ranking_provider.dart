// RankingProvider can load items from disk or over the network

import 'dart:convert';
import 'package:evf/api/load_ranking.dart';
import 'package:evf/models/ranking.dart';
import 'package:flutter/foundation.dart';
import 'package:evf/environment.dart';

class RankingProvider extends ChangeNotifier {
  bool _loadedFromCache = false;
  DateTime _lastMutation = DateTime(2000, 1, 1);
  List<Ranking> _items;

  RankingProvider() : _items = [];

  List<Ranking> get list => _items;

  void _add(Ranking item) {
    final itemId = item.catWeapon();
    if (_items.map((e) => e.catWeapon()).toList().contains(itemId)) {
      _items = _items.map((e) => e.catWeapon() == itemId ? item : e).toList();
    } else {
      _items.add(item);
      // sorting should be quick, list is not that large
      _items.sort((a, b) => a.catWeapon().compareTo(b.catWeapon()));
    }
    if (item.updated.isBefore(_lastMutation)) {
      _lastMutation = item.updated;
    }
  }

  void add(Ranking item) {
    _add(item);
    notifyListeners();
  }

  void addList(List<Ranking> items) {
    for (final item in items) {
      _add(item);
    }
    Environment.debug("ranking: notifying listeners after adding ranking");
    notifyListeners();
  }

  // load the feed items from our cached storage, if we haven't loaded them yet
  Future loadItems({bool doForce = false}) async {
    Environment.debug("loading ranking items");
    if (!_loadedFromCache) {
      Environment.debug("loading ranking items from cache first");
      await loadItemsFromCache();
      _loadedFromCache = true;
      Environment.debug("loaded ranking items");
    }

    // see if we may need to load new items from the back-end
    // status is set immediately during environment initialization, so is never null at this stage
    final status = Environment.instance.statusProvider.status!;
    Environment.debug("status lastRanking ${status.lastRanking}");
    // if we have no date, we have no feeds. Just set a very old date as default
    final lastDate = status.lastRanking == '' ? DateTime(2000, 1, 1) : DateTime.parse(status.lastRanking);

    Environment.debug("ranking: testing ${_lastMutation.toIso8601String()} vs ${lastDate.toIso8601String()}");
    if (_lastMutation.isBefore(lastDate)) {
      Environment.debug("ranking: setting doForce because last mutation is before last date");
      // there are pending feed items on the server with a more recent mutation date
      doForce = true;
    }
    // we're not going to check the ranking count: if the mutation date has changed, we can reload all of the
    // data, which should not be too much
    if (doForce) {
      Environment.debug("loading ranking items from network");
      await loadRankingItems();
    }
  }

  Future loadItemsFromCache() async {
    try {
      final doc = jsonDecode(await Environment.instance.cache.getCache("ranking.json")) as List<dynamic>;
      List<Ranking> retval = [];
      for (var content in doc) {
        retval.add(Ranking.fromJson(content as Map<String, dynamic>));
      }
      addList(retval);
    } catch (e) {
      // just skip loading the items from cache, the cache is probably empty
    }
  }

  Future loadRankingItems() async {
    final originalMutation = _lastMutation;
    final networkItems = await loadRanking(lastDate: _lastMutation);
    addList(networkItems);
    if (originalMutation.isBefore(_lastMutation)) {
      await Environment.instance.cache.setCache('ranking.json', jsonEncode(_items));
    }
  }

  Ranking getRankingFor(String category, String weapon) {
    for (var ranking in _items) {
      if (ranking.category == category && ranking.weapon == weapon) {
        Environment.debug("found a ranking ${ranking.category} ${ranking.weapon}");
        return ranking;
      }
    }
    Environment.debug("returning empty ranking for $category $weapon");
    return Ranking(DateTime(2000, 1, 1), DateTime(2000, 1, 1), category, weapon, []);
  }
}
