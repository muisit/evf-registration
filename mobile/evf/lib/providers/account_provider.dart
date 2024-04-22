// RankingProvider can load items from disk or over the network

import 'dart:convert';

import 'package:evf/api/get_account_data.dart';
import 'package:evf/environment.dart';
import 'package:evf/models/account_data.dart';

import 'base_provider.dart';

class AccountProvider extends BaseProvider {
  bool _loadedFromCache = false;
  bool _isLoading = false;
  AccountData data;

  AccountProvider() : data = AccountData();

  Future loadItems() async {
    _loadItemsFromCache();

    if (!_isLoading) {
      debug("account: loading account data from server");
      await _loadNetworkData();
    }
    debug("account: end of loading $_isLoading");
  }

  Future _loadItemsFromCache() async {
    if (!_loadedFromCache) {
      try {
        final doc = jsonDecode(await Environment.instance.cache.getCache("account.json"));
        data = AccountData.fromJson(doc);
        Environment.debug("notifying listeners after updating account from cache");
        notifyListeners();
      } catch (e) {
        // just skip loading the items from cache, the cache is probably empty
      }
      _loadedFromCache = true;
    }
  }

  Future _loadNetworkData() async {
    try {
      _isLoading = true;
      debug("account: loading data over the network");
      data = await getAccountData();
      debug("account: received network data");
      await Environment.instance.cache.setCache(
        'account.json',
        const Duration(days: 21),
        jsonEncode(data),
      );
      debug("account: stored cache, notifying listeners");
      notifyListeners();
    } catch (e) {
      // probably returned a 404 and empty response
    }
    debug("account: setting isLoading to false");
    _isLoading = false;
  }

  void setData(AccountData accountData) {
    data = accountData;
    notifyListeners();
  }
}
