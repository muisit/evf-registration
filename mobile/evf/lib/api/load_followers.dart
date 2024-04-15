// Load all available follower data from the back-end.
// At this point, we assume that this list is not very huge, so we can just load everything at
// once.

import 'package:evf/models/follower.dart';
import 'interface.dart';

Future<List<Follower>> loadFollowers() async {
  final api = Interface.create(path: '/device/followers');
  var content = await api.get();
  var lst = content as List<dynamic>;
  List<Follower> retval = [];
  for (final item in lst) {
    retval.add(Follower.fromJson(item as Map<String, dynamic>));
  }
  return retval;
}

Future<List<Follower>> loadFollowing() async {
  final api = Interface.create(path: '/device/following');
  var content = await api.get();
  var lst = content as List<dynamic>;
  List<Follower> retval = [];
  for (final item in lst) {
    retval.add(Follower.fromJson(item as Map<String, dynamic>));
  }
  return retval;
}
