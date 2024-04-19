// RankingProvider can load items from disk or over the network

import 'base_provider.dart';

class AccountProvider extends BaseProvider {
  bool _loadedFromCache = false;
  bool _isLoading = false;
  DateTime _lastMutated = DateTime(2000, 1, 1);

  AccountProvider();
}
