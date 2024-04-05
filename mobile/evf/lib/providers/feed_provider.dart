// FeedProvider can load new feed items from disk and over the network, trying to keep
// both stores synchronized. FeedProvider exports the item list for display purposes.
// Whenever a new item is added to the list, all subscribers are notified of the change

import 'dart:convert';
import 'package:evf/api/load_feed.dart';
import 'package:evf/models/feed_inventory.dart';
import 'package:flutter/foundation.dart';
import 'package:evf/models/feed_item.dart';
import 'package:evf/models/feed_list.dart';
import 'package:evf/environment.dart';

class FeedProvider extends ChangeNotifier {
  bool _loadedFromCache = false;
  final FeedList _items = FeedList();
  FeedInventory inventory;

  FeedProvider() : inventory = FeedInventory();

  FeedList get list => _items;

  void add(List<FeedItem> items) {
    for (final item in items) {
      _items.add(item);
    }
    notifyListeners();
  }

  // load the feed items from our cached storage, if we haven't loaded them yet
  Future loadItems({bool doForce = false}) async {
    if (!_loadedFromCache) {
      await loadItemInventory();
      await loadItemBlocks();
      _loadedFromCache = true;
    }

    // see if we may need to load new items from the back-end
    // status is set immediately during environment initialization, so is never null at this stage
    final status = Environment.instance.statusProvider.status!;

    // if we have no date, we have no feeds. Just set a very old date as default
    final lastDate = status.lastFeed == '' ? DateTime(2000, 1, 1) : DateTime.parse(status.lastFeed);

    if (_items.mostRecentDate.isBefore(lastDate)) {
      // there are pending feed items on the server with a more recent mutation date
      doForce = true;
    }
    // we're not going to check the feed count, because we only get a restricted number of feeds from
    // the back-end (last 2 years) and we might have more feeds stored locally.

    if (doForce) {
      await loadFeedItems();
    }
  }

  Future loadItemInventory() async {
    try {
      final doc = await Environment.instance.cache.getCache("feeds.json");
      inventory = FeedInventory.fromJson(jsonDecode(doc) as List<dynamic>);
    } catch (e) {
      // if there are problems, start with a clean feed
      inventory = FeedInventory();
    }
  }

  Future loadItemBlocks() async {
    for (final block in inventory.blocks) {
      try {
        final doc = await Environment.instance.cache.getCache(block.path);
        block.load(jsonDecode(doc) as List<dynamic>);
        // add it to our display list
        add(block.items);
      } catch (e) {
        // if we failed to load the items, just skip the block
      }
    }
  }

  Future saveItemBlock(FeedBlock block) async {
    final content = block.export();
    await Environment.instance.cache.setCache(block.path, jsonEncode(content));
  }

  Future saveItemInventory() async {
    final content = jsonEncode(inventory.toJson());
    await Environment.instance.cache.setCache("feeds.json", content);
  }

  Future loadFeedItems() async {
    final networkItems = await loadFeed(lastDate: _items.mostRecentDate);
    add(networkItems.list);
    for (final item in networkItems.list) {
      final block = inventory.findBlockForFeed(item);
      block.add(item);
    }

    var wasChanged = false;
    for (final block in inventory.blocks) {
      if (block.wasChanged) {
        wasChanged = true;
        saveItemBlock(block);
      }
    }
    if (wasChanged) {
      saveItemInventory();
    }
  }
}
